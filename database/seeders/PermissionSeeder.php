<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PermissionSeeder extends Seeder
{
    /**
     * Default guard for all seeded permissions. Override per-row
     * in the $permissions array below if you ever need an API-guard
     * permission (e.g. for token-authenticated mobile clients).
     */
    private const DEFAULT_GUARD = 'web';

    /**
     * The full catalog of permissions, sorted alphabetically by key.
     *
     * Byte-ordering is used, so '-' (ASCII 45) always sorts before
     * '_' (ASCII 95). That keeps '<module>-index' style keys grouped
     * together, with the legacy snake_case keys ('<module>_<verb>')
     * appearing later in the same letter block.
     *
     * Convention used throughout the codebase:
     *   - '<module>-index'    — list / view listing
     *   - '<module>-create'   — create form + store
     *   - '<module>-add'      — legacy alias of -create (kept for back-compat)
     *   - '<module>-edit'     — edit form + update
     *   - '<module>-delete'   — soft/hard delete
     *   - '<module>-show'     — view single record
     *   - '<module>-view'     — legacy alias of -show
     *   - '<module>-<verb>'   — custom action (e.g. returns-approve)
     *   - 'all_<module>'      — super-permission granting ALL actions in a module
     *   - '<module>_<verb>'  — legacy snake_case action (kept for back-compat)
     *
     * Add new rows freely — `firstOrCreate` keeps the seeder idempotent.
     */
    private const PERMISSIONS = [

        // ═══════════════════════════════════════════════════════════════
        //  A
        // ═══════════════════════════════════════════════════════════════

        // Create a new chart-of-accounts record (legacy add form)
        'account-add'                    => 'Create a new account (legacy add form)',
        // View the chart of accounts listing (legacy naming)
        'account-index'                  => 'View chart of accounts listing (legacy)',
        // Pick / switch the active accounting account in the UI
        'account-selection'              => 'Select the active accounting account',
        // View a single account statement (ledger detail)
        'account-statement'              => 'View a single account statement',
        // View a single account record
        'account-view'                   => 'View a single account',
        // Create a new account in the chart of accounts
        'accounts-create'                => 'Create a new account',
        // Delete an account from the chart of accounts
        'accounts-delete'                => 'Delete an account',
        // Edit an existing account
        'accounts-edit'                  => 'Edit an existing account',
        // View the chart of accounts (modern naming)
        'accounts-index'                 => 'View chart of accounts',
        // Manage third-party addons / integrations
        'addons'                         => 'Manage addons and integrations',
        // Perform a stock adjustment (manual qty correction)
        'adjustment'                     => 'Perform stock adjustments',
        // View the listing of stock adjustments
        'adjustments-index'              => 'View stock adjustments listing',
        // Delete an AI conversation thread
        'ai-assistant-delete'            => 'Delete AI conversations',
        // View past AI assistant conversation history
        'ai-assistant-history'           => 'View AI conversation history',
        // Access the AI assistant landing page
        'ai-assistant-index'             => 'Access the AI assistant',
        // Submit a prompt to the AI assistant
        'ai-assistant-prompt'            => 'Submit prompts to the AI assistant',
        // Super-permission: full access to the accounting module
        'all_accounting'                 => 'Full access to accounting module',
        // Super-permission: full access to the AI assistant module
        'all_ai_assistant'               => 'Full access to AI assistant module',
        // Super-permission: full access to the approvals module
        'all_approvals'                  => 'Full access to approvals module',
        // Super-permission: full access to the billers module
        'all_billers'                    => 'Full access to billers module',
        // Super-permission: full access to the brands module
        'all_brands'                     => 'Full access to brands module',
        // Super-permission: full access to the categories module
        'all_categories'                 => 'Full access to categories module',
        // Super-permission: full access to the category-departments module
        'all_category_departments'       => 'Full access to category departments module',
        // Super-permission: full access to the customers module
        'all_customers'                  => 'Full access to customers module',
        // Super-permission: full access to the employees module
        'all_employees'                  => 'Full access to employees module',
        // Super-permission: full access to the expenses module
        'all_expenses'                   => 'Full access to expenses module',
        // Super-permission: full access to the notifications module
        'all_notification'               => 'Full access to notifications module',
        // Super-permission: full access to the payments module
        'all_payments'                   => 'Full access to payments module',
        // Super-permission: full access to the permissions module
        'all_permissions'                => 'Full access to permissions module',
        // Super-permission: full access to the products module
        'all_products'                   => 'Full access to products module',
        // Super-permission: full access to the purchases module
        'all_purchases'                  => 'Full access to purchases module',
        // Super-permission: full access to the quotations module
        'all_quotations'                 => 'Full access to quotations module',
        // Super-permission: full access to the reports module
        'all_reports'                    => 'Full access to reports module',
        // Super-permission: full access to the returns module
        'all_returns'                    => 'Full access to returns module',
        // Super-permission: full access to the roles module
        'all_roles'                      => 'Full access to roles module',
        // Super-permission: full access to the sales module
        'all_sales'                      => 'Full access to sales module',
        // Super-permission: full access to system settings
        'all_settings'                   => 'Full access to system settings',
        // Super-permission: full access to the suppliers module
        'all_suppliers'                  => 'Full access to suppliers module',
        // Super-permission: full access to the transfers module
        'all_transfers'                  => 'Full access to transfers module',
        // Super-permission: full access to the units module
        'all_units'                      => 'Full access to units module',
        // Super-permission: full access to the user module
        'all_users'                      => 'Full access to user module',
        // Super-permission: full access to the warehouses module
        'all_warehouses'                 => 'Full access to warehouses module',
        // Create a new approval request (e.g. payment approval)
        'approvals-add'                  => 'Create an approval request',
        // Delete an approval request
        'approvals-delete'               => 'Delete an approval request',
        // Edit an approval request
        'approvals-edit'                 => 'Edit an approval request',
        // View the approvals listing
        'approvals-index'                => 'View approvals listing',
        // Approve pending payments
        'approve-payments'               => 'Approve pending payments',
        // Manage employee attendance records
        'attendance'                     => 'Manage employee attendance',

        // ═══════════════════════════════════════════════════════════════
        //  B
        // ═══════════════════════════════════════════════════════════════

        // Backup and restore the application database
        'backup_database'                => 'Backup and restore the database (legacy)',
        // View the balance sheet report
        'balance-sheet'                  => 'View the balance sheet',
        // Manage barcode label generation settings
        'barcode_setting'                => 'Manage barcode settings',
        // View the best-seller report
        'best-seller'                    => 'View best-seller report',
        // View the biller-wise sales report
        'biller-report'                  => 'View biller report',
        // Create a new biller (legacy add form)
        'billers-add'                    => 'Add a biller (legacy)',
        // Delete a biller
        'billers-delete'                 => 'Delete a biller',
        // Edit an existing biller
        'billers-edit'                   => 'Edit an existing biller',
        // Import billers via CSV/Excel
        'billers-import'                 => 'Import billers via CSV',
        // View the billers listing
        'billers-index'                  => 'View billers listing',
        // View a single biller record
        'billers-view'                   => 'View a single biller',
        // Manage bookings / reservations
        'booking'                        => 'Manage bookings',
        // Legacy access to the brand module (alias of brands-index)
        'brand'                          => 'Access brand module (legacy)',
        // Create a new brand
        'brands-create'                  => 'Create a new brand',
        // Delete a brand
        'brands-delete'                  => 'Delete a brand',
        // Edit an existing brand
        'brands-edit'                    => 'Edit an existing brand',
        // View the brands listing
        'brands-index'                   => 'View brands listing',

        // ═══════════════════════════════════════════════════════════════
        //  C
        // ═══════════════════════════════════════════════════════════════

        // Update cart product line items in POS/sale
        'cart-product-update'            => 'Update cart product line items',
        // View the cash flow report
        'cash_flow'                      => 'View cash flow report',
        // Create a new category (legacy add form)
        'categories-add'                 => 'Add a category (legacy)',
        // Create a new category
        'categories-create'              => 'Create a new category',
        // Delete a category
        'categories-delete'              => 'Delete a category',
        // Edit an existing category
        'categories-edit'                => 'Edit an existing category',
        // Import categories via CSV/Excel
        'categories-import'              => 'Import categories via CSV',
        // View the categories listing
        'categories-index'               => 'View categories listing',
        // View a single category record
        'categories-view'                => 'View a single category',
        // Legacy access to the category module (alias of categories-index)
        'category'                       => 'Access category module (legacy)',
        // Create a new category department
        'category-department-create'     => 'Create a new category department',
        // Delete a category department
        'category-department-delete'     => 'Delete a category department',
        // Edit an existing category department
        'category-department-edit'       => 'Edit an existing category department',
        // View the category departments listing
        'category-department-index'      => 'View category departments listing',
        // Change the sale date on an existing sale
        'change_sale_date'               => 'Change sale date on existing sales',
        // Edit product cost price directly from the products screen
        'cost_edit_in_products'          => 'Edit product cost from products screen',
        // Manage discount coupons
        'coupon'                         => 'Manage coupons',
        // Compose and send SMS messages
        'create_sms'                     => 'Compose and send SMS',
        // Manage system currencies
        'currency'                       => 'Manage currencies',
        // Manage custom fields across modules
        'custom_field'                   => 'Manage custom fields',
        // View the customer-wise report
        'customer-report'                => 'View customer report',
        // Export customers to CSV/Excel
        'customer_export'                => 'Export customers to CSV',
        // Manage customer groups
        'customer_group'                 => 'Manage customer groups',
        // Create a new customer (legacy add form)
        'customers-add'                  => 'Add a customer (legacy)',
        // Create a new customer
        'customers-create'               => 'Create a new customer',
        // Delete a customer
        'customers-delete'               => 'Delete a customer',
        // Edit an existing customer
        'customers-edit'                 => 'Edit an existing customer',
        // Import customers via CSV/Excel
        'customers-import'               => 'Import customers via CSV',
        // View the customers listing
        'customers-index'                => 'View customer listing',
        // View a single customer record
        'customers-show'                 => 'View a single customer',
        // View a single customer (legacy alias of customers-show)
        'customers-view'                 => 'View a single customer (legacy)',

        // ═══════════════════════════════════════════════════════════════
        //  D
        // ═══════════════════════════════════════════════════════════════

        // View the daily purchase report
        'daily-purchase'                 => 'View daily purchase report',
        // View the daily sale report
        'daily-sale'                     => 'View daily sale report',
        // Manage damaged stock entries
        'damage-stock'                   => 'Manage damaged stock',
        // Manage deliveries / shipment dispatch
        'delivery'                       => 'Manage deliveries',
        // Manage HR departments
        'department'                     => 'Manage departments',
        // Manage HR designations / job titles
        'designations'                   => 'Manage designations',
        // Apply discounts at sale / line-item level
        'discount'                       => 'Manage discounts',
        // Manage discount plans / tiers
        'discount_plan'                  => 'Manage discount plans',
        // View the DSO (Days Sales Outstanding) report
        'dso-report'                     => 'View DSO report',
        // View the due / receivables report
        'due-report'                     => 'View due report',

        // ═══════════════════════════════════════════════════════════════
        //  E
        // ═══════════════════════════════════════════════════════════════

        // Create a new employee (legacy add form)
        'employees-add'                  => 'Add an employee (legacy)',
        // Create a new employee
        'employees-create'               => 'Create a new employee',
        // Delete an employee
        'employees-delete'               => 'Delete an employee',
        // Edit an existing employee
        'employees-edit'                 => 'Edit an existing employee',
        // View the employees listing
        'employees-index'                => 'View employees listing',
        // View a single employee record
        'employees-view'                 => 'View a single employee',
        // Empty / wipe the application database (dangerous)
        'empty_database'                 => 'Empty / wipe the database (dangerous)',
        // Create a new exchange transaction
        'exchange-add'                   => 'Create an exchange',
        // Delete an exchange transaction
        'exchange-delete'                => 'Delete an exchange',
        // Edit an existing exchange transaction
        'exchange-edit'                  => 'Edit an exchange',
        // View the exchanges listing
        'exchange-index'                 => 'View exchanges listing',
        // View a single exchange transaction
        'exchange-view'                  => 'View a single exchange',
        // Manage expense categories
        'expense-categories'             => 'Manage expense categories',
        // Create an expense (legacy add form)
        'expenses-add'                   => 'Add an expense (legacy)',
        // Create an expense
        'expenses-create'                => 'Create an expense',
        // Delete an expense
        'expenses-delete'                => 'Delete an expense',
        // Edit an expense
        'expenses-edit'                  => 'Edit an expense',
        // View the expenses listing
        'expenses-index'                 => 'View expense listing',
        // View a single expense record
        'expenses-view'                  => 'View a single expense',

        // ═══════════════════════════════════════════════════════════════
        //  G
        // ═══════════════════════════════════════════════════════════════

        // Manage general system settings (legacy alias of settings-general)
        'general_setting'                => 'Manage general settings (legacy)',
        // Manage gift cards
        'gift_card'                      => 'Manage gift cards',

        // ═══════════════════════════════════════════════════════════════
        //  H
        // ═══════════════════════════════════════════════════════════════

        // Apply / handle manual discounts in the cart
        'handle_discount'                => 'Handle manual discounts in cart',
        // Manage HR holidays
        'holiday'                        => 'Manage holidays',
        // Access the HRM panel / dashboard
        'hrm-panel'                      => 'Access the HRM panel',
        // Manage HRM module settings
        'hrm_setting'                    => 'Manage HRM settings',

        // ═══════════════════════════════════════════════════════════════
        //  I
        // ═══════════════════════════════════════════════════════════════

        // Manage income categories
        'income-categories'              => 'Manage income categories',
        // Create a new income record (legacy add form)
        'incomes-add'                    => 'Add an income (legacy)',
        // Delete an income record
        'incomes-delete'                 => 'Delete an income',
        // Edit an income record
        'incomes-edit'                   => 'Edit an income',
        // View the incomes listing
        'incomes-index'                  => 'View income listing',
        // View a single income record
        'incomes-view'                   => 'View a single income',
        // Create / edit / delete invoice templates
        'invoice_create_edit_delete'     => 'Create, edit, and delete invoices',
        // Manage invoice template settings
        'invoice_setting'                => 'Manage invoice settings',

        // ═══════════════════════════════════════════════════════════════
        //  J
        // ═══════════════════════════════════════════════════════════════

        // Create a new journal entry
        'journals-create'                => 'Create a journal entry',
        // View the journal entries listing
        'journals-index'                 => 'View journal entries',
        // Post a draft journal entry
        'journals-post'                  => 'Post a draft journal entry',
        // Reverse a posted journal entry
        'journals-reverse'               => 'Reverse a posted journal entry',

        // ═══════════════════════════════════════════════════════════════
        //  L
        // ═══════════════════════════════════════════════════════════════

        // Manage the system language settings
        'language_setting'               => 'Manage language settings',
        // Manage HR leave records
        'leave'                          => 'Manage leaves',
        // Manage HR leave types
        'leave-type'                     => 'Manage leave types',

        // ═══════════════════════════════════════════════════════════════
        //  M
        // ═══════════════════════════════════════════════════════════════

        // Manage mail configuration (legacy alias of settings-mail)
        'mail_setting'                   => 'Manage mail configuration (legacy)',
        // Transfer money between accounts
        'money-transfer'                 => 'Transfer money between accounts',
        // View the monthly purchase report
        'monthly-purchase'               => 'View monthly purchase report',
        // View the monthly sale report
        'monthly-sale'                   => 'View monthly sale report',
        // View the monthly sales chart widget
        'monthly_sales'                  => 'View monthly sales report',
        // View the monthly summary dashboard widget
        'monthly_summary'                => 'View monthly summary report',

        // ═══════════════════════════════════════════════════════════════
        //  N
        // ═══════════════════════════════════════════════════════════════

        // View the notifications listing
        'notifications-index'            => 'View notifications listing',
        // Mark notifications as read
        'notifications-mark-read'        => 'Mark notifications as read',
        // Dispatch a notification manually
        'notifications-send'             => 'Dispatch a notification manually',

        // ═══════════════════════════════════════════════════════════════
        //  O
        // ═══════════════════════════════════════════════════════════════

        // Manage HR overtime records
        'overtime'                       => 'Manage overtime',

        // ═══════════════════════════════════════════════════════════════
        //  P
        // ═══════════════════════════════════════════════════════════════

        // Generate packing slips / delivery challans
        'packing_slip_challan'           => 'Generate packing slips and challans',
        // Manage HR payroll
        'payroll'                        => 'Manage payroll',
        // View the payment-wise report
        'payment-report'                 => 'View payment report',
        // Configure payment gateway credentials
        'payment_gateway_setting'        => 'Manage payment gateway settings',
        // Record a payment
        'payments-create'                => 'Record a payment',
        // Delete a payment
        'payments-delete'                => 'Delete a payment',
        // Edit a payment
        'payments-edit'                  => 'Edit a payment',
        // Initiate a gateway payment (Paystack, Stripe, etc.)
        'payments-gateway'               => 'Initiate a gateway payment',
        // View the payments listing
        'payments-index'                 => 'View payment listing',
        // Refund a payment
        'payments-refund'                => 'Refund a payment',
        // Access the POS terminal
        'pos-access'                     => 'Access the POS terminal',
        // Manage POS settings (legacy alias of settings-pos)
        'pos_setting'                    => 'Manage POS settings (legacy)',
        // Edit sale price inline during sale creation
        'price_edit_in_sale'             => 'Edit sale price in sale screen',
        // Print product barcodes
        'print_barcode'                  => 'Print product barcodes',
        // View the product expiry report
        'product-expiry-report'          => 'View product expiry report',
        // View the product quantity alert dashboard
        'product-qty-alert'              => 'View product quantity alerts',
        // View the product-wise report
        'product-report'                 => 'View product report',
        // Export products to CSV/Excel (singular naming)
        'product_export'                 => 'Export products to CSV (legacy)',
        // View a single product's history / movement log
        'product_history'                => 'View product history / movement log',
        // Add a production batch (manufacturing)
        'production-add'                 => 'Create a production batch',
        // Delete a production batch
        'production-delete'              => 'Delete a production batch',
        // Edit a production batch
        'production-edit'                => 'Edit a production batch',
        // View a single production batch
        'production-view'                => 'View a single production batch',
        // Create a new product (legacy add form)
        'products-add'                   => 'Add a product (legacy)',
        // Adjust stock quantities of a product
        'products-adjust'                => 'Adjust stock quantities',
        // Create a new product
        'products-create'                => 'Create a new product',
        // Delete a product
        'products-delete'                => 'Delete a product',
        // Edit an existing product
        'products-edit'                  => 'Edit an existing product',
        // Export products to CSV/Excel
        'products-export'                => 'Export products to CSV',
        // Import products via CSV/Excel
        'products-import'                => 'Import products via CSV',
        // View the products listing
        'products-index'                 => 'View product listing',
        // View a single product record
        'products-show'                  => 'View a single product',
        // View a single product (legacy alias of products-show)
        'products-view'                  => 'View a single product (legacy)',
        // View the profit & loss report (legacy alias of reports-profit-loss)
        'profit-loss'                    => 'View profit & loss report (legacy)',
        // Add a new project category
        'project_category_add'           => 'Create a project category',
        // Delete a project category
        'project_category_delete'        => 'Delete a project category',
        // Edit a project category
        'project_category_edit'          => 'Edit a project category',
        // View the project categories listing
        'project_category_list'          => 'View project categories listing',
        // Add a new project
        'project_project_add'            => 'Create a new project',
        // Delete a project
        'project_project_delete'         => 'Delete a project',
        // Edit an existing project
        'project_project_edit'           => 'Edit an existing project',
        // View the projects listing
        'project_project_list'           => 'View projects listing',
        // View a single project record
        'project_project_show'           => 'View a single project',
        // Add a new task to a project
        'project_task_add'               => 'Create a project task',
        // Delete a project task
        'project_task_delete'            => 'Delete a project task',
        // Edit a project task
        'project_task_edit'              => 'Edit a project task',
        // View the project tasks listing
        'project_task_list'             => 'View project tasks listing',
        // View a single project task
        'project_task_show'              => 'View a single project task',
        // Record a payment against a purchase (purchase-payment)
        'purchase-payment-add'           => 'Create a purchase payment',
        // Delete a purchase payment
        'purchase-payment-delete'        => 'Delete a purchase payment',
        // Edit a purchase payment
        'purchase-payment-edit'          => 'Edit a purchase payment',
        // View the purchase payments listing
        'purchase-payment-index'         => 'View purchase payment listing',
        // View a single purchase payment
        'purchase-payment-view'          => 'View a single purchase payment',
        // View the purchase-wise report
        'purchase-report'                => 'View purchase report',
        // Create a new purchase return
        'purchase-return-add'            => 'Create a purchase return',
        // Delete a purchase return
        'purchase-return-delete'         => 'Delete a purchase return',
        // Edit an existing purchase return
        'purchase-return-edit'           => 'Edit a purchase return',
        // View the purchase returns listing
        'purchase-return-index'          => 'View purchase return listing',
        // View a single purchase return
        'purchase-return-view'           => 'View a single purchase return',
        // Export purchases to CSV/Excel (singular naming)
        'purchase_export'                => 'Export purchases to CSV (legacy)',
        // Create a new purchase (legacy add form)
        'purchases-add'                  => 'Add a purchase (legacy)',
        // Create a new purchase
        'purchases-create'               => 'Create a new purchase',
        // Delete a purchase
        'purchases-delete'               => 'Delete a purchase',
        // Edit an existing purchase
        'purchases-edit'                 => 'Edit an existing purchase',
        // Export purchases to CSV/Excel
        'purchases-export'               => 'Export purchases to CSV',
        // Import purchases via CSV/Excel
        'purchases-import'               => 'Import purchases via CSV',
        // View the purchases listing
        'purchases-index'                => 'View purchase listing',
        // Record a payment against a purchase
        'purchases-pay'                  => 'Record a payment against a purchase',
        // Legacy alias of purchase-payment-add
        'purchases-payment-add'          => 'Legacy alias of purchase-payment-add',
        // Legacy alias of purchase-payment-delete
        'purchases-payment-delete'       => 'Legacy alias of purchase-payment-delete',
        // Legacy alias of purchase-payment-edit
        'purchases-payment-edit'         => 'Legacy alias of purchase-payment-edit',
        // Legacy alias of purchase-payment-index
        'purchases-payment-index'        => 'Legacy alias of purchase-payment-index',
        // Legacy alias of purchase-return-add
        'purchases-returns-add'          => 'Legacy alias of purchase-return-add',
        // Legacy alias of purchase-return-delete
        'purchases-returns-delete'       => 'Legacy alias of purchase-return-delete',
        // Legacy alias of purchase-return-edit
        'purchases-returns-edit'         => 'Legacy alias of purchase-return-edit',
        // Legacy alias of purchase-return-index
        'purchases-returns-index'        => 'Legacy alias of purchase-return-index',
        // View a single purchase record
        'purchases-show'                 => 'View a single purchase',

        // ═══════════════════════════════════════════════════════════════
        //  Q
        // ═══════════════════════════════════════════════════════════════

        // Convert a quotation into a sale
        'quotations-convert'             => 'Convert a quotation into a sale',
        // Create a new quotation
        'quotations-create'              => 'Create a new quotation',
        // Delete a quotation
        'quotations-delete'              => 'Delete a quotation',
        // Edit an existing quotation
        'quotations-edit'                => 'Edit an existing quotation',
        // View the quotations listing
        'quotations-index'               => 'View quotations listing',
        // Send a quotation to a customer
        'quotations-send'                => 'Send a quotation to a customer',
        // Legacy alias of quotations-create
        'quotes-add'                     => 'Legacy alias of quotations-create',
        // Legacy alias of quotations-delete
        'quotes-delete'                  => 'Legacy alias of quotations-delete',
        // Legacy alias of quotations-edit
        'quotes-edit'                    => 'Legacy alias of quotations-edit',
        // Legacy alias of quotations-index
        'quotes-index'                   => 'Legacy alias of quotations-index',
        // View a single quotation (legacy alias)
        'quotes-view'                    => 'View a single quotation (legacy)',

        // ═══════════════════════════════════════════════════════════════
        //  R
        // ═══════════════════════════════════════════════════════════════

        // Create a new recipe / bill of materials
        'recipe-add'                     => 'Create a new recipe',
        // Delete a recipe
        'recipe-delete'                  => 'Delete a recipe',
        // Edit an existing recipe
        'recipe-edit'                    => 'Edit an existing recipe',
        // View a single recipe
        'recipe-view'                    => 'View a single recipe',
        // Edit repair charges on a service ticket
        'repair-charges-edit'            => 'Edit repair charges',
        // Access the repair module dashboard
        'repair-dashboard'               => 'Access the repair dashboard',
        // Manage repair device types
        'repair-device-type'             => 'Manage repair device types',
        // Add repair parts to a service ticket
        'repair-parts-add'               => 'Add repair parts',
        // Delete repair parts from a service ticket
        'repair-parts-delete'            => 'Delete repair parts',
        // Edit repair parts on a service ticket
        'repair-parts-edit'              => 'Edit repair parts',
        // View repair parts on a service ticket
        'repair-parts-view'              => 'View repair parts',
        // Add a repair payment
        'repair-payment-add'             => 'Create a repair payment',
        // Delete a repair payment
        'repair-payment-delete'          => 'Delete a repair payment',
        // Create a new repair service ticket
        'repair-service-add'             => 'Create a repair service',
        // Delete a repair service ticket
        'repair-service-delete'          => 'Delete a repair service',
        // Edit an existing repair service ticket
        'repair-service-edit'            => 'Edit a repair service',
        // View the repair services listing
        'repair-service-index'           => 'View repair services listing',
        // View a single repair service ticket
        'repair-service-view'            => 'View a single repair service',
        // View the customer-wise report (modern naming)
        'reports-customer'               => 'View customer statements',
        // View the reports index page
        'reports-index'                  => 'View the reports index',
        // View the inventory reports
        'reports-inventory'              => 'View inventory reports',
        // View the profit & loss report (modern naming)
        'reports-profit-loss'            => 'View profit & loss reports',
        // View the purchase reports
        'reports-purchases'              => 'View purchase reports',
        // View the sales reports
        'reports-sales'                  => 'View sales reports',
        // View the supplier statements
        'reports-supplier'               => 'View supplier statements',
        // View the tax reports
        'reports-tax'                    => 'View tax reports',
        // Create a new return (legacy add form)
        'returns-add'                    => 'Add a return (legacy)',
        // Approve a pending return
        'returns-approve'                => 'Approve a pending return',
        // Create a new return
        'returns-create'                 => 'Create a new return',
        // Delete a return
        'returns-delete'                 => 'Delete a return',
        // Edit an existing return
        'returns-edit'                   => 'Edit an existing return',
        // View the returns / exchanges listing
        'returns-index'                  => 'View returns / exchanges listing',
        // View a single return record
        'returns-view'                   => 'View a single return',
        // View the revenue & profit summary dashboard widget
        'revenue_profit_summary'         => 'View revenue & profit summary',
        // Manage reward point settings
        'reward_point_setting'           => 'Manage reward point settings',
        // Manage role-permission assignments
        'role_permission'                => 'Manage role permissions',
        // Assign roles to users
        'roles-assign'                   => 'Assign roles to users',
        // Create a new role
        'roles-create'                   => 'Create a new role',
        // Delete a role
        'roles-delete'                   => 'Delete a role',
        // Edit an existing role
        'roles-edit'                     => 'Edit an existing role',
        // View the roles listing
        'roles-index'                    => 'View roles listing',

        // ═══════════════════════════════════════════════════════════════
        //  S
        // ═══════════════════════════════════════════════════════════════

        // Manage sale agents / sales representatives
        'sale-agents'                    => 'Manage sale agents',
        // Create a sale payment (legacy add form)
        'sale-payment-add'               => 'Create a sale payment',
        // Delete a sale payment
        'sale-payment-delete'            => 'Delete a sale payment',
        // Edit a sale payment
        'sale-payment-edit'              => 'Edit a sale payment',
        // View the sale payments listing
        'sale-payment-index'             => 'View sale payment listing',
        // View a single sale payment
        'sale-payment-view'              => 'View a single sale payment',
        // Filter sales by percentage (dashboard widget)
        'sale-percentage-filter'         => 'Filter sales by percentage',
        // View the sale-wise report (legacy alias of reports-sales)
        'sale-report'                    => 'View sale report (legacy)',
        // View the sale report chart widget
        'sale-report-chart'              => 'View sale report chart',
        // Export sales to CSV/Excel (legacy singular naming)
        'sale_export'                    => 'Export sales to CSV (legacy)',
        // Create a new sale (legacy add form)
        'sales-add'                      => 'Add a sale (legacy)',
        // Create a new sale
        'sales-create'                   => 'Create a new sale',
        // Delete a sale
        'sales-delete'                   => 'Delete a sale',
        // Edit an existing sale
        'sales-edit'                     => 'Edit an existing sale',
        // Import sales via CSV/Excel
        'sales-import'                   => 'Import sales via CSV',
        // View the sales listing
        'sales-index'                    => 'View sales listing',
        // Record a payment against a sale
        'sales-pay'                      => 'Record a payment against a sale',
        // View a single sale record
        'sales-show'                     => 'View a single sale',
        // View a single sale (legacy alias of sales-show)
        'sales-view'                     => 'View a single sale (legacy)',
        // Manually send/dispatch a notification
        'send_notification'              => 'Send a notification manually',
        // View the audit log
        'settings-audit'                 => 'View audit log',
        // Backup and restore the database
        'settings-backup'               => 'Backup and restore the database',
        // Manage general system settings
        'settings-general'              => 'Manage general settings',
        // Manage mail configuration
        'settings-mail'                 => 'Manage mail configuration',
        // Manage payment gateway configuration
        'settings-payment'              => 'Manage payment gateway configuration',
        // Manage POS settings
        'settings-pos'                  => 'Manage POS settings',
        // Manage HR work shifts
        'shift'                         => 'Manage shifts',
        // Sidebar toggle: show Accounting menu
        'sidebar_accounting'            => 'Show Accounting sidebar',
        // Sidebar toggle: show Expense menu
        'sidebar_expense'               => 'Show Expense sidebar',
        // Sidebar toggle: show HRM menu
        'sidebar_hrm'                   => 'Show HRM sidebar',
        // Sidebar toggle: show Income menu
        'sidebar_income'                => 'Show Income sidebar',
        // Sidebar toggle: show Manufacturing menu
        'sidebar_manufacturing'         => 'Show Manufacturing sidebar',
        // Sidebar toggle: show People (customers/suppliers/billers) menu
        'sidebar_people'                => 'Show People sidebar',
        // Sidebar toggle: show Product menu
        'sidebar_product'               => 'Show Product sidebar',
        // Sidebar toggle: show Project menu
        'sidebar_project'               => 'Show Project sidebar',
        // Sidebar toggle: show Purchase menu
        'sidebar_purchase'              => 'Show Purchase sidebar',
        // Sidebar toggle: show Quotation menu
        'sidebar_quotation'             => 'Show Quotation sidebar',
        // Sidebar toggle: show Repair menu
        'sidebar_repair'                => 'Show Repair sidebar',
        // Sidebar toggle: show Reports menu
        'sidebar_reports'               => 'Show Reports sidebar',
        // Sidebar toggle: show Sale menu
        'sidebar_sale'                  => 'Show Sale sidebar',
        // Sidebar toggle: show Transfer menu
        'sidebar_transfer'              => 'Show Transfer sidebar',
        // Sidebar toggle: show WhatsApp menu
        'sidebar_whatsapp'              => 'Show WhatsApp sidebar',
        // Manage SMS configuration (legacy)
        'sms_setting'                   => 'Manage SMS configuration',
        // View the stock report
        'stock-report'                  => 'View stock report',
        // Perform stock counts
        'stock_count'                   => 'Perform stock counts',
        // Bypasses all permission checks (grant to Admin role only)
        'super-admin'                   => 'Bypasses all permission checks',
        // View the supplier due report
        'supplier-due-report'           => 'View supplier due report',
        // View the supplier-wise report
        'supplier-report'               => 'View supplier report',
        // Create a new supplier (legacy add form)
        'suppliers-add'                 => 'Add a supplier (legacy)',
        // Create a new supplier
        'suppliers-create'              => 'Create a new supplier',
        // Delete a supplier
        'suppliers-delete'             => 'Delete a supplier',
        // Edit an existing supplier
        'suppliers-edit'               => 'Edit an existing supplier',
        // View the suppliers listing
        'suppliers-index'              => 'View supplier listing',
        // View a single supplier record
        'suppliers-show'               => 'View a single supplier',
        // View a single supplier (legacy alias of suppliers-show)
        'suppliers-view'               => 'View a single supplier (legacy)',

        // ═══════════════════════════════════════════════════════════════
        //  T
        // ═══════════════════════════════════════════════════════════════

        // Manage taxes
        'tax'                          => 'Manage taxes',
        // Manage UI theme settings
        'theme_settings'               => 'Manage theme settings',
        // View today's profit dashboard widget
        'today_profit'                 => "View today's profit widget",
        // View today's sale dashboard widget
        'today_sale'                   => "View today's sale widget",
        // Create a new transfer (legacy add form)
        'transfers-add'                => 'Add a transfer (legacy)',
        // Create a new transfer
        'transfers-create'             => 'Create a new transfer',
        // Delete a transfer
        'transfers-delete'             => 'Delete a transfer',
        // Edit an existing transfer
        'transfers-edit'               => 'Edit an existing transfer',
        // Import transfers via CSV/Excel
        'transfers-import'             => 'Import transfers via CSV',
        // View the transfers listing
        'transfers-index'              => 'View transfers listing',
        // View a single transfer record
        'transfers-view'               => 'View a single transfer',

        // ═══════════════════════════════════════════════════════════════
        //  U
        // ═══════════════════════════════════════════════════════════════

        // Legacy access to the unit module (alias of units-index)
        'unit'                         => 'Access unit module (legacy)',
        // Create a new unit of measure
        'units-create'                  => 'Create a new unit',
        // Delete a unit
        'units-delete'                  => 'Delete a unit',
        // Edit an existing unit
        'units-edit'                    => 'Edit an existing unit',
        // View the units listing
        'units-index'                  => 'View units listing',
        // View the user-wise report
        'user-report'                  => 'View user report',
        // Activate or deactivate a user account
        'users-activate'               => 'Activate or deactivate a user',
        // Create a new user (legacy add form)
        'users-add'                    => 'Add a user (legacy)',
        // Create a new user
        'users-create'                 => 'Create a new user',
        // Delete a user
        'users-delete'                => 'Delete a user',
        // Edit an existing user
        'users-edit'                  => 'Edit an existing user',
        // View the users listing
        'users-index'                 => 'View user listing',
        // View a single user record
        'users-show'                  => 'View a single user',
        // View a single user (legacy alias of users-show)
        'users-view'                  => 'View a single user (legacy)',

        // ═══════════════════════════════════════════════════════════════
        //  W
        // ═══════════════════════════════════════════════════════════════

        // Legacy access to the warehouse module (alias of warehouses-index)
        'warehouse'                    => 'Access warehouse module (legacy)',
        // View the warehouse report
        'warehouse-report'             => 'View warehouse report',
        // View the warehouse stock report
        'warehouse-stock-report'       => 'View stock report for a warehouse',
        // Create a new warehouse
        'warehouses-create'            => 'Create a new warehouse',
        // Delete a warehouse
        'warehouses-delete'            => 'Delete a warehouse',
        // Edit an existing warehouse
        'warehouses-edit'              => 'Edit an existing warehouse',
        // View the warehouses listing
        'warehouses-index'             => 'View warehouse listing',

        // ═══════════════════════════════════════════════════════════════
        //  Y
        // ═══════════════════════════════════════════════════════════════

        // View the yearly report
        'yearly_report'                => 'View yearly report',
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Disable FK checks during seeding so a fresh DB doesn't blow up
        // on any pivot table that references permissions.id.
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        $created = 0;
        $skipped = 0;

        foreach (self::PERMISSIONS as $name => $description) {
            // firstOrCreate is the idempotency guarantee — running this seeder
            // 100 times produces exactly one row per permission name.
            [$wasCreated] = $this->upsertPermission($name, $description);

            $wasCreated ? $created++ : $skipped++;
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $this->command->info(
            sprintf(
                'PermissionSeeder: %d created, %d already present, %d total.',
                $created,
                $skipped,
                count(self::PERMISSIONS)
            )
        );
    }

    /**
     * Insert or update a single permission row.
     *
     * Returns [$wasCreated, $model] where $wasCreated is true if a new row
     * was inserted (vs. just touching an existing one).
     *
     * @return array{0: bool, 1: Permission}
     */
    private function upsertPermission(string $name, string $description): array
    {
        $existing = Permission::where('name', $name)->first();

        if ($existing) {
            // Optionally update the guard_name if it has drifted.
            // We DON'T overwrite name (it's the lookup key), and we don't
            // store description in this model's fillable list, so there's
            // nothing to refresh — just count it as a "skip".
            return [false, $existing];
        }

        $model = Permission::create([
            'name'       => $name,
            'guard_name' => self::DEFAULT_GUARD,
        ]);

        return [true, $model];
    }
}