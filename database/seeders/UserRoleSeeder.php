<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Roles;
use App\Models\Permission;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class UserRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // ─────────────────────────────────────────────────────────────
        // 1. Find or create the primary Admin role (ID = 1)
        // ─────────────────────────────────────────────────────────────
        $adminRole = Roles::firstOrCreate(
            ['id' => 1],
            ['name' => 'Admin', 'is_active' => true]
        );
        $this->command->info("Admin role ID: {$adminRole->id} - Name: {$adminRole->name}");

        // ─────────────────────────────────────────────────────────────
        // 2. Find or create the secondary Super Admin role (ID = 2)
        // ─────────────────────────────────────────────────────────────
        $superAdminRole = Roles::firstOrCreate(
            ['id' => 2],
            ['name' => 'Super Admin', 'is_active' => true]
        );
        $this->command->info("Secondary admin role ID: {$superAdminRole->id} - Name: {$superAdminRole->name}");

        // ─────────────────────────────────────────────────────────────
        // 3. Find an existing admin user, or create one
        //    NOTE: users table does NOT have role_id — roles are
        //    assigned via the role_user pivot (roles_id, user_id)
        // ─────────────────────────────────────────────────────────────
        $adminUser = User::find(1) ?? User::first();

        if (!$adminUser) {
            $this->command->warn('No admin user found. Creating one...');
            $adminUser = $this->createAdminUser();
        }
        $this->command->info("Admin user ID: {$adminUser->id} - Email: {$adminUser->email}");

        // ─────────────────────────────────────────────────────────────
        // 4. Attach the Admin role to the user via the role_user pivot
        //    Go straight to the pivot table — the User model's roles()
        //    relationship may be misconfigured (e.g. pointing at
        //    'roles_user' instead of 'role_user').
        // ─────────────────────────────────────────────────────────────
        $this->syncUserRolePivot($adminUser->id, $adminRole->id);

        // 5. Verify
        $userRoleCount = DB::table('role_user')->count();
        $this->command->info("Total records in 'role_user': {$userRoleCount}");

        // ─────────────────────────────────────────────────────────────
        // 6. Delta-sync all permissions onto admin role(s)
        // ─────────────────────────────────────────────────────────────
        $this->syncPermissionsToAdminRoles([$adminRole, $superAdminRole]);

        $this->command->info(str_repeat('─', 60));
        $this->command->info('UserRoleSeeder completed successfully.');
    }

    /**
     * Create an admin user, filling all required (NOT NULL without
     * default) columns in the users table.
     */
    private function createAdminUser(): User
    {
        $userData = [
            'name'        => 'Admin',
            'email'       => 'admin@example.com',
            'password'    => Hash::make('password'),
            'phone'       => '0000000000',
            'is_active'   => true,
            'is_deleted'  => false,
        ];

        try {
            return User::create($userData);
        } catch (\Illuminate\Database\QueryException $e) {
            $this->command->warn("Standard create failed (likely missing NOT NULL columns).");
            $this->command->warn("Error: {$e->getMessage()}");
            $this->command->info('Falling back to schema introspection...');

            return $this->createUserWithIntrospection($userData);
        }
    }

    /**
     * Introspect the users table and fill every NOT NULL column
     * that doesn't have a default value.
     */
    private function createUserWithIntrospection(array $baseData): User
    {
        $columns = DB::select('SHOW COLUMNS FROM users');

        $fallbackDefaults = [
            'name'          => 'Admin',
            'email'         => 'admin@example.com',
            'username'      => 'admin',
            'password'      => Hash::make('password'),
            'phone'         => '0000000000',
            'biller_id'     => 0,
            'warehouse_id'  => 0,
            'company_id'    => 0,
            'is_active'     => 1,
            'is_deleted'    => 0,
            'is_activated'  => 1,
        ];

        $insertData = [];

        foreach ($columns as $column) {
            $field   = $column->Field;
            $null    = $column->Null;
            $default = $column->Default;
            $type    = $column->Type;
            $extra   = $column->Extra;

            if ($extra === 'auto_increment') {
                continue;
            }
            if ($default !== null) {
                continue;
            }
            if ($null === 'YES') {
                continue;
            }
            if (in_array($field, ['created_at', 'updated_at', 'deleted_at'], true)) {
                $insertData[$field] = now();
                continue;
            }
            if (array_key_exists($field, $baseData)) {
                $insertData[$field] = $baseData[$field];
                continue;
            }
            if (array_key_exists($field, $fallbackDefaults)) {
                $insertData[$field] = $fallbackDefaults[$field];
                continue;
            }

            if (preg_match('/tinyint/i', $type)) {
                $insertData[$field] = 1;
            } elseif (preg_match('/int|bit|decimal|float|double/i', $type)) {
                $insertData[$field] = 0;
            } elseif (preg_match('/varchar|char|text|enum|set/i', $type)) {
                $insertData[$field] = '';
            } elseif (preg_match('/date|time/i', $type)) {
                $insertData[$field] = now();
            } else {
                $insertData[$field] = '';
            }
        }

        $insertData = array_merge($insertData, $baseData);

        $this->command->info(
            'Inserting user with columns: ' . implode(', ', array_keys($insertData))
        );

        $id = DB::table('users')->insertGetId($insertData);

        return User::find($id);
    }

    /**
     * Insert directly into the role_user pivot table.
     * Auto-detects the column names (roles_id vs role_id, etc.)
     */
    private function syncUserRolePivot(int $userId, int $roleId): void
    {
        $pivotTable = 'role_user';

        try {
            DB::table($pivotTable)->limit(1)->get();
        } catch (\Throwable $e) {
            $this->command->error("Pivot table '{$pivotTable}' not found: {$e->getMessage()}");
            return;
        }

        // Detect the correct column names
        [$roleCol, $userCol] = $this->detectPivotColumns($pivotTable, 'role', 'user');

        // Check if the row already exists
        $exists = DB::table($pivotTable)
            ->where($roleCol, $roleId)
            ->where($userCol, $userId)
            ->exists();

        if ($exists) {
            $this->command->info("Role #{$roleId} already linked to user #{$userId} via {$pivotTable}.");
            return;
        }

        // Detect if the pivot has timestamps
        $hasTimestamps = $this->tableHasTimestamps($pivotTable);

        $insertData = [
            $roleCol => $roleId,
            $userCol => $userId,
        ];

        if ($hasTimestamps) {
            $insertData['created_at'] = now();
            $insertData['updated_at'] = now();
        }

        DB::table($pivotTable)->insert($insertData);

        $this->command->info("Inserted pivot row: {$roleCol}={$roleId}, {$userCol}={$userId} into {$pivotTable}.");
    }

    /**
     * Delta-sync every permission onto the supplied admin roles.
     * Introspects the role_has_permissions pivot to detect:
     *   - The correct column names (role_id, permission_id)
     *   - Whether timestamp columns exist (created_at, updated_at)
     *     → only includes them if the table actually has them
     */
    private function syncPermissionsToAdminRoles(array $roles): void
    {
        $allPermissions = Permission::all();

        $totalPermissions = $allPermissions->count();
        if ($totalPermissions === 0) {
            $this->command->warn('No permissions found in the permissions table. Run PermissionSeeder first.');
            return;
        }

        $this->command->info("Found {$totalPermissions} permission(s) in the catalog.");

        // Detect the role-permission pivot table
        $pivotTable = $this->detectRolePermissionPivotTable();
        if ($pivotTable === null) {
            $this->command->warn('Could not find the role-permission pivot table. Skipping permission sync.');
            return;
        }
        $this->command->info("Using role-permission pivot: {$pivotTable}");

        // Detect the correct column names
        [$roleCol, $permCol] = $this->detectPivotColumns($pivotTable, 'role', 'permission');

        // ★ KEY FIX: Detect whether the pivot table has timestamp columns.
        // Spatie's default role_has_permissions does NOT have them.
        // If we blindly include created_at/updated_at in the insert,
        // MySQL throws "Unknown column 'created_at'".
        $hasTimestamps = $this->tableHasTimestamps($pivotTable);
        $this->command->info("  Pivot [{$pivotTable}] has timestamps: " . ($hasTimestamps ? 'YES' : 'NO'));

        $allPermissionIds = $allPermissions->pluck('id')->toArray();
        $now = now();
        $totalAssigned = 0;

        foreach ($roles as $role) {
            if (!$role instanceof Roles) {
                continue;
            }

            // Get permission IDs already attached to this role
            $existingPermissionIds = DB::table($pivotTable)
                ->where($roleCol, $role->id)
                ->pluck($permCol)
                ->toArray();

            // Compute the delta
            $missingPermissionIds = array_values(array_diff($allPermissionIds, $existingPermissionIds));

            $alreadyAssigned = count($existingPermissionIds);
            $missingCount    = count($missingPermissionIds);

            $this->command->info(
                sprintf(
                    '  Role [%s] (ID:%d) — %d already assigned, %d missing.',
                    $role->name,
                    $role->id,
                    $alreadyAssigned,
                    $missingCount
                )
            );

            if ($missingCount === 0) {
                $this->command->info('    → All permissions already synced. Nothing to do.');
                continue;
            }

            // Build insert rows — only include timestamps if the
            // pivot table actually has those columns
            $insertRows = [];
            foreach ($missingPermissionIds as $permissionId) {
                $row = [
                    $permCol => $permissionId,
                    $roleCol => $role->id,
                ];

                if ($hasTimestamps) {
                    $row['created_at'] = $now;
                    $row['updated_at'] = $now;
                }

                $insertRows[] = $row;
            }

            // Chunk-insert (200 rows per batch)
            foreach (array_chunk($insertRows, 200) as $chunk) {
                DB::table($pivotTable)->insert($chunk);
            }

            $this->command->info("    → Inserted {$missingCount} missing permission(s).");
            $totalAssigned += $missingCount;
        }

        $this->command->info("Permission sync complete. Total new pivot rows inserted: {$totalAssigned}");
    }

    // ═══════════════════════════════════════════════════════════════
    //  Schema introspection helpers
    // ═══════════════════════════════════════════════════════════════

    /**
     * Check if a table has created_at and/or updated_at columns.
     */
    private function tableHasTimestamps(string $table): bool
    {
        try {
            $columns = DB::select("SHOW COLUMNS FROM `{$table}`");
            $columnNames = array_map(fn($c) => $c->Field, $columns);
            return in_array('created_at', $columnNames, true)
                || in_array('updated_at', $columnNames, true);
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Detect the role-permission pivot table name.
     */
    private function detectRolePermissionPivotTable(): ?string
    {
        $candidates = [
            'role_has_permissions',
            'permission_role',
            'role_permission',
            'roles_permissions',
            'permissions_role',
            'role_permissions',
        ];

        foreach ($candidates as $candidate) {
            try {
                DB::table($candidate)->limit(1)->get();
                return $candidate;
            } catch (\Throwable $e) {
                // try next
            }
        }

        return null;
    }

    /**
     * Detect the actual column names on a pivot table.
     *
     * For example, role_user might use (roles_id, user_id) instead
     * of the standard (role_id, user_id).
     *
     * @return array{0: string, 1: string}
     */
    private function detectPivotColumns(string $table, string $firstHint, string $secondHint): array
    {
        $columns = DB::select("SHOW COLUMNS FROM `{$table}`");
        $columnNames = array_map(fn($c) => $c->Field, $columns);

        $firstCandidates  = [
            "{$firstHint}s_id",
            "{$firstHint}_id",
        ];
        $secondCandidates = [
            "{$secondHint}s_id",
            "{$secondHint}_id",
        ];

        $firstCol = null;
        $secondCol = null;

        foreach ($firstCandidates as $candidate) {
            if (in_array($candidate, $columnNames, true)) {
                $firstCol = $candidate;
                break;
            }
        }

        foreach ($secondCandidates as $candidate) {
            if (in_array($candidate, $columnNames, true)) {
                $secondCol = $candidate;
                break;
            }
        }

        // Fallback: any column containing the hint string
        if ($firstCol === null) {
            foreach ($columnNames as $name) {
                if (str_contains($name, $firstHint)) {
                    $firstCol = $name;
                    break;
                }
            }
        }

        if ($secondCol === null) {
            foreach ($columnNames as $name) {
                if (str_contains($name, $secondHint)) {
                    $secondCol = $name;
                    break;
                }
            }
        }

        // Last resort: first two non-timestamp columns
        if ($firstCol === null || $secondCol === null) {
            $nonTimestamp = array_filter($columnNames, function ($name) {
                return !in_array($name, ['id', 'created_at', 'updated_at', 'deleted_at'], true);
            });
            $nonTimestamp = array_values($nonTimestamp);
            if (count($nonTimestamp) >= 2) {
                $firstCol  ??= $nonTimestamp[0];
                $secondCol ??= $nonTimestamp[1];
            }
        }

        $this->command->info(
            "  Pivot [{$table}] columns: first={$firstCol}, second={$secondCol}"
        );

        return [$firstCol ?? 'role_id', $secondCol ?? 'user_id'];
    }
}