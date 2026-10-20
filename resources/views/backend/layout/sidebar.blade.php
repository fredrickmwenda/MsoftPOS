

<ul id="side-main-menu" class="side-menu list-unstyled">

    <!-- ══════════════════════════════════════════════════════════════════
         1. DASHBOARD
         ══════════════════════════════════════════════════════════════════ -->
    <li><a href="{{url('/dashboard')}}"> <i class="dripicons dripicons-meter"></i><span>{{ __('file.dashboard') }}</span></a></li>

    <!-- ══════════════════════════════════════════════════════════════════
         2. PRODUCTS  (Add Product REMOVED, Add Adjustment REMOVED)
         ══════════════════════════════════════════════════════════════════ -->
    <?php
        $index_permission_active       = $role_has_permissions_list->where('name', 'products-index')->first();
        $category_permission_active    = $role_has_permissions_list->where('name', 'category')->first();
        $print_barcode_active          = $role_has_permissions_list->where('name', 'print_barcode')->first();
        $stock_count_active            = $role_has_permissions_list->where('name', 'stock_count')->first();
        $adjustment_active             = $role_has_permissions_list->where('name', 'adjustment')->first();
        $damage_stock_active           = $role_has_permissions_list->where('name', 'damage-stock')->first();
        $product_history_active        = $role_has_permissions_list->where('name', 'product_history')->first();
    ?>
    @if($category_permission_active || $index_permission_active || $print_barcode_active || $stock_count_active || $adjustment_active || $damage_stock_active || $product_history_active)
    <li><a href="#product" aria-expanded="false" data-toggle="collapse"> <i class="dripicons dripicons-list"></i><span> {{__('file.product')}}s</span></a>
        <ul id="product" class="collapse list-unstyled">
            @if($category_permission_active)
            <li id="category-menu"><a href="{{route('category.index')}}">{{__('file.category')}}</a></li>
            @endif
            @if($category_permission_active)
            <li id="category-department-menu"><a href="{{route('category-department.index')}}">Department</a></li>
            @endif
            @if($index_permission_active)
            <li id="product-list-menu"><a href="{{route('products.index')}}">{{__('file.product_list')}}</a></li>
            @endif

            @if($print_barcode_active)
            <li id="printBarcode-menu"><a href="{{route('product.printBarcode')}}">{{__('file.print_barcode')}}</a></li>
            @endif
            @if($adjustment_active)
            <li id="adjustment-list-menu"><a href="{{route('qty_adjustment.index')}}">{{trans('file.Adjustment List')}}</a></li>
            @endif
            @if($stock_count_active)
            <li id="stock-count-menu"><a href="{{route('stock-count.index')}}">{{trans('file.Stock Count')}}</a></li>
            @endif
            @if($damage_stock_active)
            <li id="damage-stock-menu"><a href="{{route('damage-stock.index')}}">Damage Stock List</a></li>
            @endif
        </ul>
    </li>
    @endif

    <!-- ══════════════════════════════════════════════════════════════════
         3. PURCHASES  (Add Purchase REMOVED, Import kept)
         ══════════════════════════════════════════════════════════════════ -->
    <?php
        $index_permission_active = $role_has_permissions_list->where('name', 'purchases-index')->first();
        $purchase_import_active  = $role_has_permissions_list->where('name', 'purchases-import')->first();
    ?>
    @if($index_permission_active)
    <li><a href="#purchase" aria-expanded="false" data-toggle="collapse"> <i class="dripicons dripicons-card"></i><span> {{trans('file.Purchase')}}s</span></a>
        <ul id="purchase" class="collapse list-unstyled">
            <li id="purchase-list-menu"><a href="{{route('purchases.index')}}">{{trans('file.Purchase List')}}</a></li>
            @if($purchase_import_active)
            <li id="purchase-import-menu"><a href="{{url('purchases/purchase_by_csv')}}">{{trans('file.Import Purchase By CSV')}}</a></li>
            @endif
        </ul>
    </li>
    @endif

    <!-- ══════════════════════════════════════════════════════════════════
         4. SALES / POS  (Add Sale REMOVED)
         ══════════════════════════════════════════════════════════════════ -->
    <?php
        $sale_index_permission_active      = $role_has_permissions_list->where('name', 'sales-index')->first();
        $gift_card_permission_active       = $role_has_permissions_list->where('name', 'gift_card')->first();
        $coupon_permission_active          = $role_has_permissions_list->where('name', 'coupon')->first();
        $delivery_permission_active         = $role_has_permissions_list->where('name', 'delivery')->first();
        $sale_add_permission_active        = $role_has_permissions_list->where('name', 'sales-add')->first();
        $sale_import_active                = $role_has_permissions_list->where('name', 'sales-import')->first();
        $hire_purchase_active              = $role_has_permissions_list->where('name', 'booking')->first();
    ?>
    @if($sale_index_permission_active || $gift_card_permission_active || $coupon_permission_active || $delivery_permission_active || $sale_add_permission_active)
    <li><a href="#sale" aria-expanded="false" data-toggle="collapse"> <i class="dripicons dripicons-cart"></i><span> {{trans('file.Sale')}}s</span></a>
        <ul id="sale" class="collapse list-unstyled">
            @if($sale_index_permission_active)
            <li id="sale-list-menu"><a href="{{route('sales.index')}}">{{trans('file.Sale List')}}</a></li>
            @endif
            @if($sale_add_permission_active)
            <li><a href="{{route('sale.pos')}}">POS</a></li>
            @endif
            @if($sale_import_active)
            <li id="sale-import-menu"><a href="{{url('sales/sale_by_csv')}}">{{trans('file.Import Sale By CSV')}}</a></li>
            @endif
            @if($gift_card_permission_active)
            <li id="gift-card-menu"><a href="{{route('gift_cards.index')}}">{{trans('file.Gift Card List')}}</a></li>
            @endif
            @if($coupon_permission_active)
            <li id="coupon-menu"><a href="{{route('coupons.index')}}">{{trans('file.Coupon List')}}</a></li>
            @endif
            <li id="courier-menu"><a href="{{route('couriers.index')}}">{{trans('file.Courier List')}}</a></li>
            @if($delivery_permission_active)
            <li id="delivery-menu"><a href="{{route('delivery.index')}}">{{trans('file.Delivery List')}}</a></li>
            @endif
            <li id="hire-purchase-menu"><a href="#hire-purchase" aria-expanded="false" data-toggle="collapse">Hire Purchase</a>
                <ul id="hire-purchase" class="collapse list-unstyled">
                    <li><a href="{{route('hire_purchase.dashboard')}}">Dashboard</a></li>
                    <li><a href="{{route('hire_purchase.index')}}">All Contracts</a></li>
                    <li><a href="{{route('hire_purchase.pending')}}">Pending Installments</a></li>
                    <li><a href="{{route('hire_purchase.overdue')}}">Overdue Installments</a></li>
                </ul>
            </li>
        </ul>
    </li>
    @endif

    <!-- ══════════════════════════════════════════════════════════════════
         5. SALE EXCHANGE  (NEW)
         ══════════════════════════════════════════════════════════════════ -->
    <?php $exchange_index_active = $role_has_permissions_list->where('name', 'exchange-index')->first(); ?>
    @if($exchange_index_active)
    <li><a href="{{route('exchange.index')}}"> <i class="dripicons dripicons-return"></i><span>Sale Exchanges</span></a></li>
    @endif

    <!-- ══════════════════════════════════════════════════════════════════
         6. EXPENSES  (Add Expense REMOVED)
         ══════════════════════════════════════════════════════════════════ -->
    <?php
        $expense_index_active       = $role_has_permissions_list->where('name', 'expenses-index')->first();
        $expense_category_active    = $role_has_permissions_list->where('name', 'expense-categories')->first();
    ?>
    @if($expense_index_active || $expense_category_active)
    <li><a href="#expense" aria-expanded="false" data-toggle="collapse"> <i class="dripicons dripicons-wallet"></i><span> {{trans('file.Expense')}}s</span></a>
        <ul id="expense" class="collapse list-unstyled">
            @if($expense_category_active)
            <li id="exp-cat-menu"><a href="{{route('expense_categories.index')}}">{{trans('file.Expense Category')}}</a></li>
            @endif
            @if($expense_index_active)
            <li id="exp-list-menu"><a href="{{route('expenses.index')}}">{{trans('file.Expense List')}}</a></li>
            @endif
        </ul>
    </li>
    @endif

    <!-- ══════════════════════════════════════════════════════════════════
         7. APPROVALS
         ══════════════════════════════════════════════════════════════════ -->
    <?php $approvals_index_active = $role_has_permissions_list->where('name', 'approvals-index')->first(); ?>
    @if($approvals_index_active)
    <li id="approvals-menu"><a href="{{ route('approvals.index') }}"> <i class="dripicons dripicons-checkmark"></i><span> Approvals</span></a></li>
    @endif

    <!-- ══════════════════════════════════════════════════════════════════
         8. QUOTATIONS  (Add Quotation REMOVED)
         ══════════════════════════════════════════════════════════════════ -->
    <?php $index_permission_active = $role_has_permissions_list->where('name', 'quotes-index')->first(); ?>
    @if($index_permission_active)
    <li><a href="#quotation" aria-expanded="false" data-toggle="collapse"> <i class="dripicons dripicons-document"></i><span> {{trans('file.Quotation')}}s</span></a>
        <ul id="quotation" class="collapse list-unstyled">
            <li id="quotation-list-menu"><a href="{{route('quotations.index')}}">{{trans('file.Quotation List')}}</a></li>
        </ul>
    </li>
    @endif

    <!-- ══════════════════════════════════════════════════════════════════
         9. TRANSFERS  (Add Transfer REMOVED, Import kept)
         ══════════════════════════════════════════════════════════════════ -->
    <?php
        $index_permission_active = $role_has_permissions_list->where('name', 'transfers-index')->first();
        $transfer_import_active  = $role_has_permissions_list->where('name', 'transfers-import')->first();
    ?>
    @if($index_permission_active)
    <li><a href="#transfer" aria-expanded="false" data-toggle="collapse"> <i class="dripicons dripicons-export"></i><span> {{trans('file.Transfer')}}s</span></a>
        <ul id="transfer" class="collapse list-unstyled">
            <li id="transfer-list-menu"><a href="{{route('transfers.index')}}">{{trans('file.Transfer List')}}</a></li>
            @if($transfer_import_active)
            <li id="transfer-import-menu"><a href="{{url('transfers/transfer_by_csv')}}">{{trans('file.Import Transfer By CSV')}}</a></li>
            @endif
        </ul>
    </li>
    @endif

    <!-- ══════════════════════════════════════════════════════════════════
         10. RETURNS  (Sale + Purchase)
         ══════════════════════════════════════════════════════════════════ -->
    <?php
        $sale_return_index_permission_active      = $role_has_permissions_list->where('name', 'returns-index')->first();
        $purchase_return_index_permission_active   = $role_has_permissions_list->where('name', 'purchase-return-index')->first();
    ?>
    @if($sale_return_index_permission_active || $purchase_return_index_permission_active)
    <li><a href="#return" aria-expanded="false" data-toggle="collapse"> <i class="dripicons dripicons-return"></i><span> {{trans('file.return')}}s</span></a>
        <ul id="return" class="collapse list-unstyled">
            @if($sale_return_index_permission_active)
            <li id="sale-return-menu"><a href="{{route('return-sale.index')}}">{{trans('file.Sale')}}</a></li>
            @endif
            @if($purchase_return_index_permission_active)
            <li id="purchase-return-menu"><a href="{{route('return-purchase.index')}}">{{trans('file.Purchase')}}</a></li>
            @endif
        </ul>
    </li>
    @endif


    <!-- ══════════════════════════════════════════════════════════════════
     11. ACCOUNTING  (Expanded with Reconciliation, Trial Balance, General Ledger, Balance Sheet, Cash Flow)
     ══════════════════════════════════════════════════════════════════ -->
    <?php
        $index_permission_active              = $role_has_permissions_list->where('name', 'account-index')->first();
        $money_transfer_permission_active      = $role_has_permissions_list->where('name', 'money-transfer')->first();
        $balance_sheet_permission_active       = $role_has_permissions_list->where('name', 'balance-sheet')->first();
        $account_statement_permission_active   = $role_has_permissions_list->where('name', 'account-statement')->first();
        $reconciliation_permission_active      = $role_has_permissions_list->where('name', 'accounting-reconciliation')->first();
        $trial_balance_permission_active       = $role_has_permissions_list->where('name', 'trial-balance')->first();
        $general_ledger_permission_active      = $role_has_permissions_list->where('name', 'general-ledger')->first();
        $cash_flow_permission_active           = $role_has_permissions_list->where('name', 'cash-flow')->first();
    ?>
    @if($index_permission_active || $balance_sheet_permission_active || $account_statement_permission_active || $money_transfer_permission_active || $reconciliation_permission_active || $trial_balance_permission_active || $general_ledger_permission_active || $cash_flow_permission_active)
    <li class=""><a href="#account" aria-expanded="false" data-toggle="collapse"> <i class="dripicons dripicons-briefcase"></i><span>{{trans('file.Accounting')}}</span></a>
        <ul id="account" class="collapse list-unstyled">
            @if($reconciliation_permission_active)
            <li id="reconciliation-menu"><a href="{{route('accounting.reconciliation.index')}}">Reconciliation Dashboard</a></li>
            @endif
            @if($index_permission_active)
            <li id="account-list-menu"><a href="{{route('accounts.index')}}">{{trans('file.Account List')}}</a></li>
            @endif
            @if($money_transfer_permission_active)
            <li id="money-transfer-menu"><a href="{{route('money-transfers.index')}}">{{trans('file.Money Transfer')}}</a></li>
            @endif
            @if($trial_balance_permission_active)
            <li id="trial-balance-menu"><a href="{{route('accounting.trial-balance')}}">Trial Balance</a></li>
            @endif
            @if($general_ledger_permission_active)
            <li id="general-ledger-menu"><a href="{{route('accounting.general-ledger')}}">General Ledger</a></li>
            @endif
            @if($balance_sheet_permission_active)
            <li id="balance-sheet-menu"><a href="{{route('accounting.balance-sheet')}}">{{trans('file.Balance Sheet')}}</a></li>
            @endif
            @if($cash_flow_permission_active)
            <li id="cash-flow-menu"><a href="{{route('accounting.cash-flow')}}">Cashflow Statement</a></li>
            @endif
            @if($account_statement_permission_active)
            <li id="account-statement-menu"><a id="account-statement" href="">{{trans('file.Account Statement')}}</a></li>
            @endif
        </ul>
    </li>
    @endif

    <!-- ══════════════════════════════════════════════════════════════════
         12. HRMIS  (Payroll threaded down → 5 sub-menus)
         ══════════════════════════════════════════════════════════════════ -->
    <?php
        $department_active     = $role_has_permissions_list->where('name', 'department')->first();
        $designation_active    = $role_has_permissions_list->where('name', 'designations')->first();
        $index_employee_active = $role_has_permissions_list->where('name', 'employees-index')->first();
        $attendance_active     = $role_has_permissions_list->where('name', 'attendance')->first();
        $payroll_active        = $role_has_permissions_list->where('name', 'payroll')->first();
        $holiday_active        = $role_has_permissions_list->where('name', 'holiday')->first();
        $shift_active          = $role_has_permissions_list->where('name', 'shift')->first();
        $leave_type_active     = $role_has_permissions_list->where('name', 'leave-type')->first();
        $leave_active          = $role_has_permissions_list->where('name', 'leave')->first();
        $hrm_panel_active      = $role_has_permissions_list->where('name', 'hrm-panel')->first();
    ?>
    @if($department_active || $designation_active || $index_employee_active || $attendance_active || $payroll_active || $holiday_active || $shift_active || $leave_type_active || $leave_active || $hrm_panel_active)

        @php
            /* Active if ANY of the 5 payroll sub-routes is hit */
            $payrollSectionActive = request()->routeIs([
                'salary-template.*',
                'payroll.index', 'payroll.generate', 'payroll.generate-store',
                'payroll-payment.*',
                'overtime.*',
            ]);
        @endphp

        <li class="{{ $payrollSectionActive ? 'active open' : '' }}">
            <a href="#hrm" aria-expanded="{{ $payrollSectionActive ? 'true' : 'false' }}" data-toggle="collapse">
                <i class="dripicons dripicons-user-group"></i><span>HRMIS</span>
            </a>
            <ul id="hrm" class="collapse list-unstyled {{ $payrollSectionActive ? 'show' : '' }}">

                @if($department_active)
                <li id="dept-menu"><a href="{{route('departments.index')}}">{{trans('file.Department')}}</a></li>
                @endif

                @if($designation_active)
                <li id="designation-menu"><a href="{{route('designations.index')}}">Designations</a></li>
                @endif

                @if($index_employee_active)
                <li id="employee-menu"><a href="{{route('employees.index')}}">{{trans('file.Employee')}}</a></li>
                @endif

                @if($attendance_active)
                <li id="attendance-menu"><a href="{{route('attendance.index')}}">{{trans('file.Attendance')}}</a></li>
                @endif

                @if($shift_active)
                <li id="shift-menu"><a href="{{route('shift.index')}}">Shifts</a></li>
                @endif

                {{-- ══════════════════════════════════════════════════════════
                     PAYROLL — threaded down with 5 sub-menus
                ═══════════════════════════════════════════════════════════════ --}}
                @if($payroll_active)
                <li id="payroll-menu" class="{{ $payrollSectionActive ? 'active open' : '' }}">
                    <a href="#payroll" aria-expanded="{{ $payrollSectionActive ? 'true' : 'false' }}" data-toggle="collapse">
                        {{trans('file.Payroll')}}
                    </a>
                    <ul id="payroll" class="collapse list-unstyled {{ $payrollSectionActive ? 'show' : '' }}">

                        {{-- 1. Salary Template (monthly) --}}
                        <li id="salary-template-menu"
                            class="{{ request()->routeIs('salary-template.index') ? 'active' : '' }}">
                            <a href="{{route('salary-template.index')}}">{{trans('file.Salary Template')}}</a>
                        </li>

                        {{-- 2. Hourly Template --}}
                        <li id="hourly-template-menu"
                            class="{{ request()->routeIs('salary-template.hourly') ? 'active' : '' }}">
                            <a href="{{route('salary-template.hourly')}}">{{trans('file.Hourly Template')}}</a>
                        </li>

                        {{-- 3. Manage Salary --}}
                        <li id="manage-salary-menu"
                            class="{{ request()->routeIs('payroll.index', 'payroll.generate', 'payroll.generate-store') ? 'active' : '' }}">
                            <a href="{{route('payroll.index')}}">{{trans('file.Manage Salary')}}</a>
                        </li>

                        {{-- 4. Make Payment --}}
                        <li id="make-payment-menu"
                            class="{{ request()->routeIs('payroll-payment.index') ? 'active' : '' }}">
                            <a href="{{route('payroll-payment.index')}}">{{trans('file.Make Payment')}}</a>
                        </li>

                        {{-- 5. Overtime --}}
                        <li id="overtime-menu"
                            class="{{ request()->routeIs('overtime.index', 'overtime.store') ? 'active' : '' }}">
                            <a href="{{route('overtime.index')}}">{{trans('file.Overtime')}}</a>
                        </li>

                    </ul>
                </li>
                @endif

                @if($leave_type_active)
                <li id="leave-type-menu"><a href="{{route('leave-type.index')}}">Leave Types</a></li>
                @endif

                @if($leave_active)
                <li id="leave-menu"><a href="{{route('leave.index')}}">Leaves</a></li>
                @endif

                @if($holiday_active)
                <li id="holiday-menu"><a href="{{route('holidays.index')}}">{{trans('file.Holiday')}}</a></li>
                @endif

            </ul>
        </li>
    @endif

    <!-- ══════════════════════════════════════════════════════════════════
         13. PEOPLE  (All Add items REMOVED, Sale Agents ADDED)
         ══════════════════════════════════════════════════════════════════ -->
    <?php
        $user_index_permission_active      = $role_has_permissions_list->where('name', 'users-index')->first();
        $customer_index_permission_active  = $role_has_permissions_list->where('name', 'customers-index')->first();
        $biller_index_permission_active    = $role_has_permissions_list->where('name', 'billers-index')->first();
        $supplier_index_permission_active  = $role_has_permissions_list->where('name', 'suppliers-index')->first();
        $sale_agents_active                = $role_has_permissions_list->where('name', 'sale-agents')->first();
    ?>
    @if($user_index_permission_active || $customer_index_permission_active || $biller_index_permission_active || $supplier_index_permission_active || $sale_agents_active)
    <li><a href="#people" aria-expanded="false" data-toggle="collapse"> <i class="dripicons dripicons-user"></i><span> {{trans('file.People')}}</span></a>
        <ul id="people" class="collapse list-unstyled">
            @if($user_index_permission_active)
            <li id="user-list-menu"><a href="{{route('user.index')}}">{{trans('file.User List')}}</a></li>
            @endif
            @if($customer_index_permission_active)
            <li id="customer-list-menu"><a href="{{route('customer.index')}}">{{trans('file.Customer List')}}</a></li>
            @endif
            @if($biller_index_permission_active)
            <li id="biller-list-menu"><a href="{{route('biller.index')}}">{{trans('file.Biller List')}}</a></li>
            @endif
            @if($supplier_index_permission_active)
            <li id="supplier-list-menu"><a href="{{route('supplier.index')}}">{{trans('file.Supplier List')}}</a></li>
            @endif
            @if($sale_agents_active)
            <li id="sale-agents-menu"><a href="{{route('sale-agents.index')}}">Sale Agents</a></li>
            @endif
        </ul>
    </li>
    @endif

    <!-- ══════════════════════════════════════════════════════════════════
         14. CASH REGISTER  (NEW)
         ══════════════════════════════════════════════════════════════════ -->
    @php $is_admin = \Auth::user()->roles->contains(fn($r) => $r->id <= 2); @endphp
    @if($is_admin)
    <li><a href="{{route('cashRegister.index')}}"> <i class="dripicons dripicons-wallet"></i><span>Cash Register</span></a></li>
    @endif

    <!-- ══════════════════════════════════════════════════════════════════
         15. TABLES  (NEW — for restaurant/hotel POS)
         ══════════════════════════════════════════════════════════════════ -->
    <!-- @if($is_admin)
    <li><a href="{{route('tables.index')}}"> <i class="dripicons dripicons-view-apps"></i><span>Tables</span></a></li>
    @endif -->

    <!-- ══════════════════════════════════════════════════════════════════
         16. REPORTS  (Expanded with sub-items)
         ══════════════════════════════════════════════════════════════════ -->
    <?php
        $profit_loss_active              = $role_has_permissions_list->where('name', 'profit-loss')->first();
        $best_seller_active              = $role_has_permissions_list->where('name', 'best-seller')->first();
        $warehouse_report_active         = $role_has_permissions_list->where('name', 'warehouse-report')->first();
        $warehouse_stock_report_active   = $role_has_permissions_list->where('name', 'warehouse-stock-report')->first();
        $product_report_active           = $role_has_permissions_list->where('name', 'product-report')->first();
        $daily_sale_active               = $role_has_permissions_list->where('name', 'daily-sale')->first();
        $monthly_sale_active             = $role_has_permissions_list->where('name', 'monthly-sale')->first();
        $daily_purchase_active           = $role_has_permissions_list->where('name', 'daily-purchase')->first();
        $monthly_purchase_active         = $role_has_permissions_list->where('name', 'monthly-purchase')->first();
        $purchase_report_active          = $role_has_permissions_list->where('name', 'purchase-report')->first();
        $sale_report_active              = $role_has_permissions_list->where('name', 'sale-report')->first();
        $sale_report_chart_active        = $role_has_permissions_list->where('name', 'sale-report-chart')->first();
        $payment_report_active           = $role_has_permissions_list->where('name', 'payment-report')->first();
        $product_expiry_report_active    = $role_has_permissions_list->where('name', 'product-expiry-report')->first();
        $product_qty_alert_active        = $role_has_permissions_list->where('name', 'product-qty-alert')->first();
        $dso_report_active               = $role_has_permissions_list->where('name', 'dso-report')->first();
        $user_report_active              = $role_has_permissions_list->where('name', 'user-report')->first();
        $customer_report_active          = $role_has_permissions_list->where('name', 'customer-report')->first();
        $supplier_report_active          = $role_has_permissions_list->where('name', 'supplier-report')->first();
        $due_report_active               = $role_has_permissions_list->where('name', 'due-report')->first();
        $supplier_due_report_active      = $role_has_permissions_list->where('name', 'supplier-due-report')->first();
        $stock_report_active             = $role_has_permissions_list->where('name', 'stock-report')->first();
    ?>
    @if($profit_loss_active || $best_seller_active || $warehouse_report_active || $warehouse_stock_report_active || $product_report_active || $daily_sale_active || $monthly_sale_active || $daily_purchase_active || $monthly_purchase_active || $purchase_report_active || $sale_report_active || $sale_report_chart_active || $payment_report_active || $product_expiry_report_active || $product_qty_alert_active || $dso_report_active || $user_report_active || $customer_report_active || $supplier_report_active || $due_report_active || $supplier_due_report_active || $stock_report_active)
    <li><a href="#reports" aria-expanded="false" data-toggle="collapse"> <i class="dripicons dripicons-document-remove"></i><span>Reports</span></a>
        <ul id="reports" class="collapse list-unstyled">
            <li><a href="{{route('report.dashboard')}}">Report Dashboard</a></li>
        </ul>
    </li>
    @endif


    <!-- ══════════════════════════════════════════════════════════════════
        17. AI ASSISTANT  (Collapsible: AI Conversation + AI Provider Settings)
        ══════════════════════════════════════════════════════════════════ -->
    <?php
        // AI Conversation (chat) — gated by ai-assistant-index permission
        $ai_conversation_active = $role_has_permissions_list->where('name', 'ai-assistant-index')->first();

        // AI Provider Settings (admin config) — gated by super-admin permission
        $ai_provider_settings_active = $role_has_permissions_list->where('name', 'super-admin')->first();
        $is_admin = Auth::user()->roles->contains(fn($r) => $r->id <= 2);
    ?>
    @if($ai_conversation_active || $ai_provider_settings_active || $is_admin)
    <li>
        <a href="#ai-assistant" aria-expanded="false" data-toggle="collapse">
            <i class="dripicons dripicons-forecast"></i><span>AI Assistant</span>
        </a>
        <ul id="ai-assistant" class="collapse list-unstyled">
            @if($ai_conversation_active)
            <li id="ai-conversation-menu">
                <a href="{{ route('ai-assistant.index') }}">AI Conversation</a>
            </li>
            @endif

            @if($ai_provider_settings_active || $is_admin)
            <li id="ai-provider-settings-menu">
                <a href="{{ route('ai-assistant.providers.index') }}">AI Provider Settings</a>
            </li>
            @endif
        </ul>
    </li>
    @endif

    <!-- ══════════════════════════════════════════════════════════════════
         18. COMMUNICATION  (WhatsApp + SMS Templates — NEW)
         ══════════════════════════════════════════════════════════════════ -->
    <?php
        $whatsapp_active   = $role_has_permissions_list->where('name', 'sidebar_whatsapp')->first();
        $create_sms_active  = $role_has_permissions_list->where('name', 'create_sms')->first();
    ?>
    @if($whatsapp_active || $is_admin)
    <li><a href="#communication" aria-expanded="false" data-toggle="collapse"> <i class="dripicons dripicons-message"></i><span>Communication</span></a>
        <ul id="communication" class="collapse list-unstyled">
            @if($whatsapp_active || $is_admin)
            <li><a href="{{route('whatsapp.settings')}}">WhatsApp Settings</a></li>
            <li><a href="{{route('whatsapp.templates')}}">WhatsApp Templates</a></li>
            <li><a href="{{route('whatsapp.send.page')}}">Send WhatsApp</a></li>
            @endif
            @if($is_admin)
            <li><a href="{{route('smstemplates.index')}}">SMS Templates</a></li>
            @endif
            @if($create_sms_active)
            <li id="create-sms-menu"><a href="{{route('setting.createSms')}}">{{trans('file.Create SMS')}}</a></li>
            @endif
        </ul>
    </li>
    @endif

    <!-- ══════════════════════════════════════════════════════════════════
         19. SETTINGS  (Same as before, no Add items)
         ══════════════════════════════════════════════════════════════════ -->
    <?php
        $all_notification_permission_active    = $role_has_permissions_list->where('name', 'all_notification')->first();
        $send_notification_permission_active   = $role_has_permissions_list->where('name', 'send_notification')->first();
        $warehouse_permission_active           = $role_has_permissions_list->where('name', 'warehouse')->first();
        $customer_group_permission_active      = $role_has_permissions_list->where('name', 'customer_group')->first();
        $brand_permission_active               = $role_has_permissions_list->where('name', 'brand')->first();
        $unit_permission_active                = $role_has_permissions_list->where('name', 'unit')->first();
        $currency_permission_active            = $role_has_permissions_list->where('name', 'currency')->first();
        $tax_permission_active                 = $role_has_permissions_list->where('name', 'tax')->first();
        $general_setting_permission_active    = $role_has_permissions_list->where('name', 'general_setting')->first();
        $backup_database_permission_active     = $role_has_permissions_list->where('name', 'backup_database')->first();
        $mail_setting_permission_active        = $role_has_permissions_list->where('name', 'mail_setting')->first();
        $sms_setting_permission_active         = $role_has_permissions_list->where('name', 'sms_setting')->first();
        $pos_setting_permission_active         = $role_has_permissions_list->where('name', 'pos_setting')->first();
        $hrm_setting_permission_active         = $role_has_permissions_list->where('name', 'hrm_setting')->first();
        $reward_point_setting_permission_active = $role_has_permissions_list->where('name', 'reward_point_setting')->first();
        $discount_plan_permission_active       = $role_has_permissions_list->where('name', 'discount_plan')->first();
        $discount_permission_active            = $role_has_permissions_list->where('name', 'discount')->first();
        $custom_field_permission_active        = $role_has_permissions_list->where('name', 'custom_field')->first();
        $social_media_permission_active = $role_has_permissions_list->where('name', 'social-media')->first();
    ?>
    <li><a href="#setting" aria-expanded="false" data-toggle="collapse"> <i class="dripicons dripicons-gear"></i><span>{{trans('file.settings')}}</span></a>
        <ul id="setting" class="collapse list-unstyled">

            @if($is_admin)
            <li id="role-menu"><a href="{{route('role.index')}}">{{trans('file.Role Permission')}}</a></li>
            <li id="policies-menu"><a href="{{route('policies.index')}}">Policies</a></li>

            @endif
            @if($custom_field_permission_active)
            <li id="custom-field-list-menu"><a href="{{route('custom-fields.index')}}">{{trans('file.Custom Field List')}}</a></li>
            @endif
            @if($discount_plan_permission_active)
            <li id="discount-plan-list-menu"><a href="{{route('discount-plans.index')}}">{{trans('file.Discount Plan')}}</a></li>
            @endif
            @if($discount_permission_active)
            <li id="discount-list-menu"><a href="{{route('discounts.index')}}">{{trans('file.Discount')}}</a></li>
            @endif
            @if($all_notification_permission_active)
            <li id="notification-list-menu"><a href="{{route('notifications.index')}}">{{trans('file.All Notification')}}</a></li>
            @endif
            @if($send_notification_permission_active)
            <li id="notification-menu"><a href="" id="send-notification">{{trans('file.Send Notification')}}</a></li>
            @endif
            @if($warehouse_permission_active)
            <li id="warehouse-menu"><a href="{{route('warehouse.index')}}">{{trans('file.Warehouse')}}</a></li>
            @endif
            @if($customer_group_permission_active)
            <li id="customer-group-menu"><a href="{{route('customer_group.index')}}">{{trans('file.Customer Group')}}</a></li>
            @endif
            @if($brand_permission_active)
            <li id="brand-menu"><a href="{{route('brand.index')}}">{{trans('file.Brand')}}/Make</a></li>
            @endif
            @if($unit_permission_active)
            <li id="unit-menu"><a href="{{route('unit.index')}}">{{trans('file.Unit')}}</a></li>
            @endif
            @if($currency_permission_active)
            <li id="currency-menu"><a href="{{route('currency.index')}}">{{trans('file.Currency')}}</a></li>
            @endif
            @if($tax_permission_active)
            <li id="tax-menu"><a href="{{route('tax.index')}}">{{trans('file.Tax')}}</a></li>
            @endif
            <li id="user-menu"><a href="{{route('user.profile', ['id' => Auth::id()])}}">{{trans('file.User Profile')}}</a></li>
            @if($social_media_permission_active)
            <li id="social-media-menu"><a href="{{route('social-media.index')}}">{{trans('file.Social Media')}}</a></li>
            @endif
            @if($backup_database_permission_active)
            <li><a href="{{route('setting.backup')}}">{{trans('file.Backup Database')}}</a></li>
            @endif
            @if($general_setting_permission_active)
            <li id="general-setting-menu"><a href="{{route('setting.general')}}">{{trans('file.General Setting')}}</a></li>
            @endif
            @if($mail_setting_permission_active)
            <li id="mail-setting-menu"><a href="{{route('setting.mail')}}">{{trans('file.Mail Setting')}}</a></li>
            @endif
            @if($reward_point_setting_permission_active)
            <li id="reward-point-setting-menu"><a href="{{route('setting.rewardPoint')}}">{{trans('file.Reward Point Setting')}}</a></li>
            @endif
            @if($sms_setting_permission_active)
            <li id="sms-setting-menu"><a href="{{route('setting.sms')}}">{{trans('file.SMS Setting')}}</a></li>
            @endif

            @if($pos_setting_permission_active)
            <li id="pos-setting-menu"><a href="{{route('setting.pos')}}">POS {{trans('file.settings')}}</a></li>
            @endif
            @if($hrm_setting_permission_active)
            <li id="hrm-setting-menu"><a href="{{route('setting.hrm')}}"> {{trans('file.HRM Setting')}}</a></li>
            @endif
        </ul>
    </li>

</ul>
