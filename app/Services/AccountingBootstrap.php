<?php

namespace App\Services;

use App\Models\AccountingAccount;
use App\Models\AccountingConfig;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;      // FIX: was missing → DB::table() crash
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class AccountingBootstrap
{
    protected static bool $booted = false;
    protected static ?array $allowedAccountTypes = null;

    /**
     * Idempotent. Runs once per request.
     * Creates missing tables, an enabled+activated config row and the
     * default chart of accounts.
     */
    public static function ensure(): void
    {
        if (self::$booted) {
            return;
        }

        self::ensureTables();
        self::ensureConfig();

        foreach (self::defaultAccounts() as $definition) {
            self::ensureAccount($definition['code']);
        }

        self::$booted = true;
    }

    /* ================================================================
        TABLES
    ================================================================ */

    protected static function ensureTables(): void
    {
        if (!Schema::hasTable('accounting_configs')) {
            // Created to MATCH the app's real schema (see phpMyAdmin):
            // status / activation_mode / opening_journal_entry_id / activated_by / activated_at
            Schema::create('accounting_configs', function (Blueprint $table) {
                $table->id();
                $table->boolean('enabled')->default(false);
                $table->string('status')->default('not_activated');
                $table->string('activation_mode')->nullable();
                $table->date('start_date')->nullable();
                $table->unsignedBigInteger('opening_journal_entry_id')->nullable();
                $table->unsignedBigInteger('activated_by')->nullable();
                $table->timestamp('activated_at')->nullable();
                $table->timestamps();
            });
            Log::info('AccountingBootstrap: created table accounting_configs.');
        }

        if (!Schema::hasTable('accounting_accounts')) {
            Schema::create('accounting_accounts', function (Blueprint $table) {
                $table->id();
                $table->string('code')->unique();
                $table->string('name');
                $table->string('account_type');
                $table->unsignedBigInteger('parent_id')->nullable();
                $table->boolean('is_control_account')->default(false);
                $table->boolean('is_system')->default(false);
                $table->boolean('is_active')->default(true);
                $table->boolean('is_cash_account')->default(false);
                $table->timestamps();
            });
            Log::info('AccountingBootstrap: created table accounting_accounts.');
        }

        if (!Schema::hasTable('account_mappings')) {
            Schema::create('account_mappings', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('accounting_account_id');
                $table->string('mapped_type');
                $table->unsignedBigInteger('mapped_id');
                $table->timestamps();

                $table->unique(['mapped_type', 'mapped_id']);
                $table->foreign('accounting_account_id')
                      ->references('id')->on('accounting_accounts')
                      ->cascadeOnDelete();
            });
            Log::info('AccountingBootstrap: created table account_mappings.');
        }

        if (!Schema::hasTable('journal_entries')) {
            Schema::create('journal_entries', function (Blueprint $table) {
                $table->id();
                $table->string('reference_no')->nullable();
                $table->date('entry_date')->index();
                $table->string('source_type')->nullable()->index();
                $table->unsignedBigInteger('source_id')->nullable()->index();
                $table->string('source_subtype')->nullable();
                $table->string('event_type')->nullable()->index();
                $table->text('note')->nullable();
                $table->unsignedBigInteger('related_journal_entry_id')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->timestamps();
            });
            Log::info('AccountingBootstrap: created table journal_entries.');
        }

        if (!Schema::hasTable('journal_lines')) {
            Schema::create('journal_lines', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('journal_entry_id');
                $table->unsignedBigInteger('accounting_account_id');
                $table->decimal('debit', 20, 4)->default(0);
                $table->decimal('credit', 20, 4)->default(0);
                $table->string('description')->nullable();
                $table->timestamps();

                $table->foreign('journal_entry_id')
                      ->references('id')->on('journal_entries')
                      ->cascadeOnDelete();
                $table->foreign('accounting_account_id')
                      ->references('id')->on('accounting_accounts');
                $table->index(['accounting_account_id']);
            });
            Log::info('AccountingBootstrap: created table journal_lines.');
        }

        // ── Migrate installs created with the OLD bootstrap schema ──

        if (Schema::hasTable('journal_entries')
            && Schema::hasColumn('journal_entries', 'date')
            && !Schema::hasColumn('journal_entries', 'entry_date')) {
            Schema::table('journal_entries', fn (Blueprint $t) => $t->date('entry_date')->nullable());
            DB::table('journal_entries')->update(['entry_date' => DB::raw('`date`')]);
            Log::info('AccountingBootstrap: migrated journal_entries.date → entry_date.');
        }

        if (Schema::hasTable('journal_lines')
            && Schema::hasColumn('journal_lines', 'type')
            && !Schema::hasColumn('journal_lines', 'debit')) {
            Schema::table('journal_lines', function (Blueprint $t) {
                $t->decimal('debit', 20, 4)->default(0);
                $t->decimal('credit', 20, 4)->default(0);
            });
            DB::table('journal_lines')->where('type', 'debit')->update(['debit' => DB::raw('`amount`')]);
            DB::table('journal_lines')->where('type', 'credit')->update(['credit' => DB::raw('`amount`')]);
            Log::info('AccountingBootstrap: migrated journal_lines type/amount → debit/credit.');
        }

        // Column patches for older installations
        self::ensureColumn('accounting_configs', 'status', fn (Blueprint $t) => $t->string('status')->default('not_activated'));
        self::ensureColumn('accounting_configs', 'activation_mode', fn (Blueprint $t) => $t->string('activation_mode')->nullable());
        self::ensureColumn('accounting_configs', 'opening_journal_entry_id', fn (Blueprint $t) => $t->unsignedBigInteger('opening_journal_entry_id')->nullable());
        self::ensureColumn('accounting_configs', 'activated_by', fn (Blueprint $t) => $t->unsignedBigInteger('activated_by')->nullable());
        self::ensureColumn('accounting_configs', 'activated_at', fn (Blueprint $t) => $t->timestamp('activated_at')->nullable());
        self::ensureColumn('journal_entries', 'source_subtype', fn (Blueprint $t) => $t->string('source_subtype')->nullable());
        self::ensureColumn('journal_entries', 'related_journal_entry_id', fn (Blueprint $t) => $t->unsignedBigInteger('related_journal_entry_id')->nullable());
        self::ensureColumn('journal_entries', 'reference_no', fn (Blueprint $t) => $t->string('reference_no')->nullable());
        self::ensureColumn('journal_entries', 'created_by', fn (Blueprint $t) => $t->unsignedBigInteger('created_by')->nullable());
        self::ensureColumn('journal_entries', 'entry_date', fn (Blueprint $t) => $t->date('entry_date')->nullable());
        self::ensureColumn('journal_lines', 'debit', fn (Blueprint $t) => $t->decimal('debit', 20, 4)->default(0));
        self::ensureColumn('journal_lines', 'credit', fn (Blueprint $t) => $t->decimal('credit', 20, 4)->default(0));
        self::ensureColumn('journal_lines', 'description', fn (Blueprint $t) => $t->string('description')->nullable());
        self::ensureColumn('accounting_accounts', 'is_cash_account', fn (Blueprint $t) => $t->boolean('is_cash_account')->default(false));
        self::ensureColumn('accounting_accounts', 'parent_id', fn (Blueprint $t) => $t->unsignedBigInteger('parent_id')->nullable());
    }

    protected static function ensureColumn(string $table, string $column, \Closure $definition): void
    {
        if (Schema::hasTable($table) && !Schema::hasColumn($table, $column)) {
            Schema::table($table, fn (Blueprint $t) => $definition($t));
            Log::info("AccountingBootstrap: added missing column [{$column}] to [{$table}].");
        }
    }

    /* ================================================================
        CONFIG — enabled=1 + full activation details, self-healed
    ================================================================ */

    protected static function ensureConfig(): void
    {
        $columns = Schema::hasTable('accounting_configs')
            ? Schema::getColumnListing('accounting_configs')
            : [];

        $config = AccountingConfig::first();

        /* ---- Case 1: no row yet → create a fully activated one ---- */
        if (!$config) {
            $attributes = array_intersect_key([
                'enabled'         => true,                       // ON out of the box
                'status'          => 'activated',                // fill the app's activation flow
                'activation_mode' => 'auto_bootstrap',
                'start_date'      => null,                       // null = accept documents from ANY date
                'activated_by'    => Auth::id(),
                'activated_at'    => now(),
                'opening_journal_entry_id' => null,
            ], array_flip($columns));

            AccountingConfig::create($attributes);

            Log::info('AccountingBootstrap: created & activated accounting config.', $attributes);
            return;
        }

        /* ---- Case 2: row exists but was auto-created disabled
               (enabled=0 + status='not_activated' = never touched by a human,
                e.g. legacy firstOrCreate() artifact) → force-enable + activate ---- */
        $dirty = [];

        if (!$config->enabled) {
            $neverActivated = in_array('status', $columns) && $config->status === 'not_activated';

            if ($neverActivated || !in_array('status', $columns)) {
                $config->enabled = true;
                $dirty[] = 'enabled 0→1';

                if (in_array('status', $columns) && $config->status === 'not_activated') {
                    $config->status          = 'activated';
                    $config->activation_mode = $config->activation_mode ?: 'auto_bootstrap';
                    $config->activated_by    = $config->activated_by ?: Auth::id();
                    $config->activated_at    = $config->activated_at ?: now();
                    $dirty[] = 'activation details filled';
                }
            }
            // else: status='activated' but enabled=0 → deliberate disable by admin → RESPECT it
        }

        /* ---- Case 3: enabled but activation details incomplete ---- */
        if (in_array('status', $columns) && $config->enabled && $config->status === 'not_activated') {
            $config->status          = 'activated';
            $config->activation_mode = $config->activation_mode ?: 'auto_bootstrap';
            $config->activated_by    = $config->activated_by ?: Auth::id();
            $config->activated_at    = $config->activated_at ?: now();
            $dirty[] = 'activation details completed';
        }

        if ($dirty) {
            $config->save();
            Log::info('AccountingBootstrap: self-healed accounting config → ' . implode('; ', $dirty) . '.');
        }
    }

    /* ================================================================
        CHART OF ACCOUNTS (ENUM-dialect safe)
    ================================================================ */

    public static function ensureAccount(string $code): AccountingAccount
    {
        $account = AccountingAccount::where('code', $code)->first();
        if ($account) {
            return $account;
        }

        $definition = self::defaultAccount($code);

        if (!$definition) {
            throw new \Exception("No default definition exists for accounting account code [{$code}].");
        }

        $definition['is_system'] = true;
        $definition['is_active'] = true;

        // Make account_type speak THIS database's dialect (enum-safe)
        $definition['account_type'] = self::normalizeAccountType($definition['account_type']);

        $attributes = array_intersect_key(
            $definition,
            array_flip(Schema::getColumnListing((new AccountingAccount)->getTable()))
        );

        $account = AccountingAccount::create($attributes);
        Log::info("AccountingBootstrap: created missing accounting account [{$code}] {$definition['name']} (type: {$definition['account_type']}).");

        return $account;
    }

    /**
     * Allowed ENUM values for account_type, or [] if free-form (varchar).
     */
    protected static function allowedAccountTypes(): array
    {
        if (self::$allowedAccountTypes !== null) {
            return self::$allowedAccountTypes;
        }

        self::$allowedAccountTypes = [];

        try {
            if (!Schema::hasTable('accounting_accounts')) {
                return self::$allowedAccountTypes;
            }

            $column = DB::select("SHOW COLUMNS FROM `accounting_accounts` WHERE Field = 'account_type'");

            if (!empty($column) && preg_match('/^enum\((.*)\)$/i', $column[0]->Type, $m)) {
                preg_match_all("/'([^']*)'/", $m[1], $values);
                self::$allowedAccountTypes = $values[1];
            }
        } catch (\Throwable $e) {
            Log::warning('AccountingBootstrap: could not inspect account_type column — ' . $e->getMessage());
        }

        return self::$allowedAccountTypes;
    }

    /**
     * Maps desired type onto what this DB accepts ('income' → 'revenue' etc.).
     * Widens the enum as a last resort.
     */
    protected static function normalizeAccountType(string $type): string
    {
        $allowed = self::allowedAccountTypes();

        if (empty($allowed)) {
            return $type;
        }

        $allowedLower = array_map('strtolower', $allowed);

        if (in_array(strtolower($type), $allowedLower, true)) {
            return $allowed[array_search(strtolower($type), $allowedLower, true)];
        }

        $synonyms = [
            'income'    => ['revenue'],
            'revenue'   => ['income'],
            'expense'   => ['expenses', 'operating_expense'],
            'asset'     => ['assets'],
            'liability' => ['liabilities'],
            'equity'    => ['equities', 'capital'],
        ];

        foreach ($synonyms[strtolower($type)] ?? [] as $candidate) {
            if (in_array(strtolower($candidate), $allowedLower, true)) {
                return $allowed[array_search(strtolower($candidate), $allowedLower, true)];
            }
        }

        self::widenAccountTypeEnum($type);

        return $type;
    }

    protected static function widenAccountTypeEnum(string $type): void
    {
        $allowed = array_values(array_unique(array_merge(self::allowedAccountTypes(), [$type])));
        $values  = implode(',', array_map(fn ($v) => "'" . str_replace("'", "''", $v) . "'", $allowed));

        try {
            DB::statement("ALTER TABLE `accounting_accounts` MODIFY `account_type` ENUM({$values}) NOT NULL");
            self::$allowedAccountTypes = $allowed;
            Log::info("AccountingBootstrap: widened account_type enum to include '{$type}'.");
        } catch (\Throwable $e) {
            throw new \Exception("Could not widen account_type enum for '{$type}': " . $e->getMessage());
        }
    }

    /* ================================================================
        DEFINITIONS
    ================================================================ */

    public static function defaultAccount(string $code): ?array
    {
        return collect(self::defaultAccounts())->firstWhere('code', $code);
    }

    public static function defaultAccounts(): array
    {
        return [
            ['code' => '1000', 'name' => 'Cash & Bank',                    'account_type' => 'asset',     'is_cash_account' => true, 'is_control_account' => true],
            ['code' => '1100', 'name' => 'Accounts Receivable',            'account_type' => 'asset',     'is_control_account' => true],
            ['code' => '1200', 'name' => 'Inventory',                      'account_type' => 'asset',     'is_control_account' => true],
            ['code' => '1250', 'name' => 'Input VAT (Recoverable)',        'account_type' => 'asset'],
            ['code' => '1300', 'name' => 'Cash (Legacy)',                  'account_type' => 'asset',     'is_cash_account' => true],
            ['code' => '2100', 'name' => 'Accounts Payable',               'account_type' => 'liability', 'is_control_account' => true],
            ['code' => '2210', 'name' => 'Customer Deposits Held',         'account_type' => 'liability'],
            ['code' => '2220', 'name' => 'Reward Points Liability',        'account_type' => 'liability'],
            ['code' => '2250', 'name' => 'Gift Cards Liability',           'account_type' => 'liability'],
            ['code' => '3900', 'name' => 'Opening Balance Equity',         'account_type' => 'equity'],
            ['code' => '4100', 'name' => 'Sales Revenue',                  'account_type' => 'income',    'is_control_account' => true],
            ['code' => '4150', 'name' => 'Sales Returns (Contra-Revenue)', 'account_type' => 'income'],
            ['code' => '4300', 'name' => 'Other Income',                   'account_type' => 'income'],
            ['code' => '5100', 'name' => 'Payroll Expense',                'account_type' => 'expense'],
            ['code' => '5150', 'name' => 'Purchase Returns (Contra)',      'account_type' => 'expense'],
            ['code' => '5200', 'name' => 'Freight-In / Shipping',          'account_type' => 'expense'],
            ['code' => '5210', 'name' => 'Loyalty Rewards Expense',        'account_type' => 'expense'],
            ['code' => '5300', 'name' => 'Purchase Discounts Received',    'account_type' => 'income'],
            ['code' => '6100', 'name' => 'Operating Expenses',             'account_type' => 'expense',   'is_control_account' => true],
        ];
    }
}
