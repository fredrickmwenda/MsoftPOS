@extends('backend.layout.main')
@section('content')

<section>
    <div class="container-fluid">
        {{-- <button class="btn btn-info" data-toggle="modal" data-target="#createModal"><i class="dripicons-plus"></i>
            {{ __('file.Add Payroll') }} </button> --}}

        <!-- Add Multiple Payroll Button -->
        <button class="btn btn-success" data-toggle="modal" data-target="#addMultipleModal">
            <i class="dripicons-plus"></i> {{ __("file.Generate Payroll") }}
        </button>

        <div class="d-inline-block ml-2">
            <button class="btn btn-secondary" type="button" data-toggle="collapse" data-target="#filterCollapse"
                aria-expanded="false" aria-controls="filterCollapse">
                <i class="dripicons-filter"></i> {{ __('file.Filter') }}
            </button>
        </div>

        <div class="collapse mt-3" id="filterCollapse">
            <div class="card card-body">
                <div class="row g-3">
                    <!-- Employee Filter -->
                    <div class="col-md-4">
                        <label>{{ __('file.Employee') }}</label>
                        <select id="filterEmployee" class="form-control selectpicker" data-live-search="true"
                            title="Select Employee">
                            <option value="">{{ __('file.All') }}</option>
                            @foreach ($lims_employee_list as $employee)
                                <option value="{{ $employee->id }}">{{ $employee->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Month Filter -->
                    <div class="col-md-4">
                        <label>{{ __('file.Month') }}</label>
                        <input type="month" id="filterMonth" class="form-control">
                    </div>

                    <!-- Date Filter -->
                    <div class="col-md-4">
                        <label>{{ __('file.date') }}</label>
                        <input type="date" id="filterDate" class="form-control">
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- Add Multiple Payroll Modal -->
    <div id="addMultipleModal" class="modal fade text-left" tabindex="-1" role="dialog"
        aria-labelledby="multiplePayrollLabel" aria-hidden="true">
        <div role="document" class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-light">
                    <h5 class="modal-title">{{ __("file.Generate Payroll") }}</h5>
                    <button type="button" data-dismiss="modal" aria-label="Close" class="close">
                        <span aria-hidden="true"><i class="dripicons-cross"></i></span>
                    </button>
                </div>
                <div class="modal-body">

                    {!! Form::open(['route' => 'payroll.generateCards', 'method' => 'POST']) !!}
                    <div class="row g-3">

                        {{-- Warehouse --}}
                        <div class="col-md-6 form-group">
                            <label>{{ __('file.Warehouse') }} *</label>
                            <select id="warehouseSelect" name="warehouse_id" class="form-control select2" required>
                                <option value="0">{{ __('file.All Warehouse') }} </option>
                                @foreach ($lims_warehouse_list as $warehouse)
                                    <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Month --}}
                        <div class="col-md-6 form-group">
                            <label>{{ __('file.Month') }} *</label>
                            <input type="month" name="month" id="monthMultiple" class="form-control" required>
                        </div>

                        {{-- Payroll Template Select With Add Button (Inside Generate Payroll) --}}
                        <div class="col-md-12 form-group">
                            <label>{{ __('file.Payroll Template') }}</label>
                            <div class="input-group">
                                <div style="width:calc(100% - 40px);" class="input-group-prepend">
                                    <select class="form-control selectpicker" name="payroll_template_id" id="payroll_template_select_multiple" data-live-search="true" title="Select Template...">
                                        <option value="">{{ __('file.None') }}</option>
                                    </select>
                                </div>
                                <span class="input-group-prepend">
                                    <button type="button" class="btn btn-primary add-template-btn" data-source="multiple" title="{{ __('file.Add Payroll Template') }}">
                                        <i class="dripicons-plus"></i>
                                    </button>
                                </span>
                            </div>
                        </div>

                        {{-- Employee Multi Select --}}
                        <div class="col-md-12 form-group">
                            <label>{{ __("file.Employees") }} *</label>
                            <div class="mb-2">
                                <button type="button" class="btn btn-sm btn-outline-primary"
                                    id="selectAllEmployees">{{ __('file.Select All') }}</button>
                                <button type="button" class="btn btn-sm btn-outline-danger"
                                    id="deselectAllEmployees">{{ __('file.Deselect All') }}</button>
                            </div>
                            <select id="employeeMultiple" name="employee_ids[]" class="form-control" multiple required>
                                <!-- Employees will load dynamically -->
                            </select>
                        </div>
                    </div>

                    <div class="text-right mt-3">
                        <button type="submit" class="btn btn-primary">
                            <i class="dripicons-checkmark"></i> {{ __('file.Submit Payrolls') }}
                        </button>
                    </div>
                    {!! Form::close() !!}
                </div>
            </div>
        </div>
    </div>

    <!-- Create Payroll Template Modal -->
    <!-- Create Payroll Template Modal -->
<!-- Create Payroll Template Modal -->
<div id="createTemplateModal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form id="payrollTemplateAddForm">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('file.Add Payroll Template') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label>{{ __('file.Name') }} *</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="col-md-6 form-group">
                            <label>{{ __('file.Description') }}</label>
                            <input type="text" name="description" class="form-control">
                        </div>
                    </div>

                    <div class="row">
                        <!-- Work Duration -->
                        <div class="col-md-6 form-group">
                            <label>Work Duration (Hours/Days) *</label>
                            <input type="number" step="any" name="work_duration" id="work_duration" class="form-control" value="1" required>
                        </div>
                        
                        <!-- Multi-Tax Selection -->
                        <div class="col-md-6 form-group">
                            <label>{{ __('file.Tax') }} (Multi-select)</label>
                            <select name="tax_ids[]" id="template_tax_select" class="form-control selectpicker" multiple data-live-search="true" title="Select applicable taxes...">
                                @foreach ($lims_tax_list as $tax)
                                    <option value="{{ $tax->id }}" data-rate="{{ $tax->rate }}">{{ $tax->name }} ({{ $tax->rate }}%)</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <hr>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6><i class="dripicons-list"></i> Template Items (Amount Per Duration)</h6>
                        <button type="button" class="btn btn-sm btn-info" id="addTemplateItemBtn"><i class="dripicons-plus"></i> Add Item</button>
                    </div>

                    <div id="templateItemsContainer">
                        <!-- Default Item Row -->
                        <div class="template-item-row border p-3 mb-2 rounded bg-light">
                            <div class="row">
                                <div class="col-md-4">
                                    <label>Item Name</label>
                                    <input type="text" name="items[0][name]" class="form-control item-name" placeholder="e.g. Basic Salary" required>
                                </div>
                                <div class="col-md-3">
                                    <label>Type</label>
                                    <select name="items[0][type]" class="form-control item-type">
                                        <option value="allowance">Allowance (Earning)</option>
                                        <option value="deduction">Deduction</option>
                                    </select>
                                </div>
                                <div class="col-md-5">
                                    <label>Amount (Per Duration)</label>
                                    <input type="number" step="any" name="items[0][amount]" class="form-control item-amount" placeholder="e.g. 50.00" required>
                                </div>
                                <div class="col-md-6 mt-2">
                                    <label>Description</label>
                                    <input type="text" name="items[0][description]" class="form-control">
                                </div>
                                <div class="col-md-4 mt-2 d-flex align-items-center">
                                    <div class="form-check">
                                        <input type="checkbox" name="items[0][taxable]" value="1" class="form-check-input item-taxable" id="taxable_0">
                                        <label class="form-check-label" for="taxable_0">Taxable</label>
                                    </div>
                                </div>
                                <div class="col-md-2 mt-2 text-right">
                                    <button type="button" class="btn btn-danger btn-sm remove-item-btn"><i class="dripicons-trash"></i></button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Live Calculation Summary -->
                    <div class="mt-3 p-3 bg-light border rounded">
                        <h6 class="mb-3"><i class="dripicons-calculator"></i> Calculation Summary</h6>
                        <div class="d-flex justify-content-between mb-1">
                            <span>Total Amount Per Duration:</span>
                            <strong id="sum_amount_per_duration">0.00</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-1">
                            <span>Gross Amount (Total × Duration):</span>
                            <strong id="sum_gross_amount">0.00</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-1">
                            <span>Tax Amount (Gross × Tax %):</span>
                            <strong id="sum_tax_amount">0.00</strong>
                        </div>
                        <div class="d-flex justify-content-between mt-2 pt-2 border-top">
                            <span><h5 class="mb-0">Net Amount:</h5></span>
                            <h5 class="mb-0 text-success" id="sum_net_amount">0.00</h5>
                        </div>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('file.Close') }}</button>
                    <button type="submit" class="btn btn-primary">{{ __('file.Save Template') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

    <div class="table-responsive">
        <table id="payroll-table" class="table">
            <thead>
                <tr>
                    <th class="not-exported"></th>
                    <th>{{ __('file.date') }}</th>
                    <th>{{ __('file.reference') }}</th>
                    <th>{{ __('file.Employee') }}</th>
                    <th>{{ __('file.Account') }}</th>
                    <th>{{ __('file.Amount') }}</th>
                    <th>{{ __('file.Method') }}</th>
                    <th>{{ __('file.Month') }}</th>
                    <th class="not-exported">{{ __('file.action') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($lims_payroll_all as $key => $payroll)
                    @php
                        $employee = \App\Models\Employee::find($payroll->employee_id);
                        $account = \App\Models\Account::find($payroll->account_id);
                        $monthDate = null;
                        if (!empty($payroll->month)) {
                            try {
                                $monthDate = \Carbon\Carbon::createFromFormat('Y-m', $payroll->month);
                            } catch (\Exception $e) {
                                $monthDate = null;
                            }
                        }
                    @endphp
                    <tr  data-id="{{ $payroll->id }}" data-employee_id="{{ $payroll->employee_id }}"
                    data-month="{{ $payroll->month }}" data-date="{{ $payroll->created_at->format('Y-m-d') }}">
                        <td>{{ $key }}</td>
                        <td>{{ date($general_setting->date_format, strtotime($payroll->created_at->toDateString())) }}
                        </td>
                        <td>{{ $payroll->reference_no }}</td>
                        <td>{{ $employee->name }}</td>
                        <td>{{ @$account->name }}</td>
                        <td>{{ number_format((float) $payroll->amount, $general_setting->decimal, '.', '') }}</td>
                        @if ($payroll->paying_method == 0)
                            <td>{{ __("file.Cash") }}</td>
                        @elseif($payroll->paying_method == 1)
                            <td>{{ __("file.Cheque")}}</td>
                        @else
                            <td>{{ __("file.Credit Card")}}</td>
                        @endif
                        <td>{{ $monthDate ? $monthDate->format('F Y') : $payroll->month }}</td>
                        <td>
                            <div class="btn-group">
                                <button type="button" class="btn btn-default btn-sm dropdown-toggle"
                                    data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    {{ __('file.action') }}
                                    <span class="caret"></span>
                                    <span class="sr-only">Toggle Dropdown</span>
                                </button>

                                <ul class="dropdown-menu edit-options dropdown-menu-right dropdown-default"
                                    user="menu">

                                    <!-- Edit Payroll -->
                                    <li>
                                        <button type="button" class="btn btn-link edit-btn"
                                            data-id="{{ $payroll->id }}"
                                            data-date="{{ $payroll->created_at->format('Y-m-d') }}"
                                            data-employee="{{ $payroll->employee_id }}"
                                            data-account="{{ $payroll->account_id }}"
                                            data-amount="{{ $payroll->amount }}"
                                            data-paying_method="{{ $payroll->paying_method }}"
                                            data-note="{{ $payroll->note }}" data-month="{{ $payroll->month }}"
                                            @php $amountArray = json_decode($payroll->amount_array, true); @endphp
                                            data-salary="{{ $amountArray['salary'] ?? 0 }}"
                                            data-commission="{{ $amountArray['commission'] ?? 0 }}"
                                            data-prev="{{ $amountArray['previous'] ?? 0 }}" data-toggle="modal"
                                            data-target="#editModal">
                                            <i class="dripicons-document-edit"></i> {{ __('file.Edit') }}
                                        </button>
                                    </li>

                                    @php
                                        $amountArray = json_decode($payroll->amount_array, true);
                                    @endphp

                                    <li>
                                        <button type="button" class="btn btn-link view-btn"
                                            data-id="{{ $payroll->id }}" data-employee_name="{{ $employee->name }}"
                                            data-leaves="{{ $payroll->leaves ?? 0 }}"
                                            data-work_duration="{{ $payroll->work_duration ?? 0 }}"
                                            data-attendance="{{ $payroll->attendance ?? 0 }}"
                                            data-month="{{ $monthDate ? $monthDate->format('F Y') : $payroll->month }}"
                                            data-salary="{{ $amountArray['salary'] ?? 0 }}"
                                            data-commission="{{ $amountArray['commission'] ?? 0 }}"
                                            data-transactions="{{ $amountArray['previous'] ?? 0 }}"
                                            data-amount="{{ $amountArray['total'] ?? $payroll->amount }}"
                                            data-paying_method_text="@if ($payroll->paying_method == 0) Cash @elseif($payroll->paying_method == 1) Cheque @else Credit Card @endif"
                                            data-note="{{ $payroll->note }}" data-toggle="modal"
                                            data-target="#viewModal">
                                            <i class="dripicons-preview"></i> {{ __('file.View') }}
                                        </button>
                                    </li>

                                    <li class="divider"></li>

                                    <!-- Delete -->
                                    {{ Form::open(['route' => ['payroll.destroy', $payroll->id], 'method' => 'DELETE']) }}
                                    <li>
                                        <button type="submit" class="btn btn-link" onclick="return confirmDelete()">
                                            <i class="dripicons-trash"></i> {{ __('file.Delete') }}
                                        </button>
                                    </li>
                                    {{ Form::close() }}

                                </ul>
                            </div>
                        </td>

                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <th></th>
                    <th>{{ __("file.Total") }}:</th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                </tr>
            </tfoot>
        </table>
    </div>
</section>

<!-- Add Payroll Modal -->
<div id="createModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true"
    class="modal fade text-left">
    <div role="document" class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 id="exampleModalLabel" class="modal-title">
                    <i class="dripicons-wallet"></i> {{ __('file.Add Payroll') }}
                </h5>
                <button type="button" data-dismiss="modal" aria-label="Close" class="close">
                    <span aria-hidden="true"><i class="dripicons-cross"></i></span>
                </button>
            </div>

            <div class="modal-body">
                <p class="italic text-muted">
                    <small>{{ __('file.The field labels marked with * are required input fields') }}.</small>
                </p>

                {!! Form::open(['route' => 'payroll.store', 'method' => 'post', 'files' => true]) !!}
                <div class="row g-3">

                    {{-- Employee --}}
                    <div class="col-md-6 form-group">
                        <label>
                            {{ __('file.Employee') }} *
                            <i class="dripicons-information" data-toggle="tooltip" data-placement="right" title="Select the employee for whom this payroll is being added"></i>
                        </label>
                        <select class="form-control selectpicker" name="employee_id" id="employee_id" required
                            data-live-search="true" title="Select Employee...">
                            @foreach ($lims_employee_list as $employee)
                                <option value="{{ $employee->id }}">{{ $employee->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Month --}}
                    <div class="col-md-6 form-group">
                        <label>
                            {{ __('file.Month') }} *
                            <i class="dripicons-information" data-toggle="tooltip" data-placement="right" title="Select the month for which payroll is being processed"></i>
                        </label>
                        <input type="month" name="month" id="monthSelect" class="form-control" required>
                    </div>

                    {{-- Payroll Template Select With Add Button (Inside Add Payroll) --}}
                    <div class="col-md-6 form-group">
                        <label>{{ __('file.Payroll Template') }}</label>
                        <div class="input-group">
                            <div style="width:calc(100% - 40px);" class="input-group-prepend">
                                <select class="form-control selectpicker" name="payroll_template_id" id="payroll_template_select" data-live-search="true" title="Select Template...">
                                    <option value="">{{ __('file.None') }}</option>
                                </select>
                            </div>
                            <span class="input-group-prepend">
                                <button type="button" class="btn btn-primary add-template-btn" data-source="single" title="{{ __('file.Add Payroll Template') }}">
                                    <i class="dripicons-plus"></i>
                                </button>
                            </span>
                        </div>
                    </div>

                    {{-- Salary Amount --}}
                    <div class="col-md-6 form-group">
                        <label>
                            {{ __('file.Salary Amount') }}
                            <i class="dripicons-information" data-toggle="tooltip" data-placement="right" title="Salary Amount = Basic Salary + (Allowances - Deductions)"></i>
                        </label>
                        <input type="number" step="any" name="salary_amount" id="salaryAmount"
                            class="form-control">
                    </div>

                    {{-- Previous Transactions --}}
                    <div class="col-md-6 form-group">
                        <label>{{ __('file.Expense') }} 
                            <i class="dripicons-information" data-toggle="tooltip" data-placement="right" title="{{ __('file.Loan/Advance/Expense') }}"></i>
                        </label>
                        <input type="number" step="any" name="previous_transactions" id="previousTransactions"
                            class="form-control">
                    </div>

                    <div class="col-md-6 form-group">
                        <label>{{ __('file.Sale Commission') }} 
                            <i class="dripicons-information" data-toggle="tooltip" data-placement="right" title="Sale Commission = (Total Sale × Target Commission %) / 100"></i>
                        </label>
                        <input type="number" step="any" name="commission" id="commissionAmount"
                            class="form-control">
                    </div>

                    {{-- Total Payable --}}
                    <div class="col-md-6 form-group">
                        <label>
                            {{ __('file.Total') }}
                            <i class="dripicons-information" data-toggle="tooltip" data-placement="right" title="Total Payable = (Salary + Sale Commission) - Previous Transactions"></i>
                        </label>
                        <input type="number" step="any" name="amount" id="totalPayable" class="form-control">
                    </div>

                    {{-- Date --}}
                    <div class="col-md-6 form-group">
                        <label>{{ __('file.date') }}</label>
                        <input type="text" name="created_at" class="form-control date"
                            placeholder="{{ __('file.Choose date') }}" value="{{ date('d-m-Y') }}" />
                    </div>

                    {{-- Account --}}
                    <div class="col-md-6 form-group">
                        <label>{{ __('file.Account') }} *</label>
                        <select class="form-control selectpicker" name="account_id">
                            @foreach ($lims_account_list as $account)
                                <option value="{{ $account->id }}" {{ $account->is_default ? 'selected' : '' }}>
                                    {{ $account->name }} [{{ $account->account_no }}]
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Payment Method --}}
                    <div class="col-md-6 form-group">
                        <label>{{ __('file.Method') }} *</label>
                        <select class="form-control selectpicker" name="paying_method" required>
                            <option value="0">{{ __("file.Cash") }}</option>
                            <option value="1">{{ __("file.Cheque") }}</option>
                            <option value="2">{{ __("file.Credit Card") }}</option>
                        </select>
                    </div>

                    {{-- Note --}}
                    <div class="col-md-12 form-group">
                        <label>{{ __('file.Note') }}</label>
                        <textarea name="note" rows="3" class="form-control" placeholder="Write any note about this payroll..."></textarea>
                    </div>

                </div>

                <div class="text-end mt-3">
                    <button type="submit" class="btn btn-primary">
                        <i class="dripicons-checkmark"></i> {{ __('file.Submit') }}
                    </button>
                </div>

                {{ Form::close() }}
            </div>
        </div>
    </div>
</div>

<!-- Edit Payroll Modal -->
<div id="editModal" tabindex="-1" role="dialog" aria-labelledby="editModalLabel" aria-hidden="true"
    class="modal fade text-left">
    <div role="document" class="modal-dialog modal-lg">
        <div class="modal-content border-0 shadow-sm">
            <div class="modal-header">
                <h5 id="editModalLabel" class="modal-title">
                    <i class="dripicons-wallet"></i> {{ __('file.Update Payroll') }}
                </h5>
                <button type="button" data-dismiss="modal" aria-label="Close" class="close">
                    <span aria-hidden="true"><i class="dripicons-cross"></i></span>
                </button>
            </div>

            <div class="modal-body">
                <p class="italic text-muted">
                    <small>{{ __('file.The field labels marked with * are required input fields') }}.</small>
                </p>

                {!! Form::open(['route' => ['payroll.update', 1], 'method' => 'put', 'files' => true]) !!}
                <input type="hidden" name="payroll_id" id="editPayrollId">

                <div class="row g-3">
                    <!-- Employee -->
                    <div class="col-md-6 form-group">
                        <label>{{ __('file.Employee') }} *</label>
                        <select class="form-control selectpicker" name="employee_id" id="editEmployee" required
                            data-live-search="true" title="Select Employee...">
                            @foreach ($lims_employee_list as $employee)
                                <option value="{{ $employee->id }}">{{ $employee->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Month -->
                    <div class="col-md-6 form-group">
                        <label>{{ __('file.Month') }} *</label>
                        <input type="month" name="month" id="editMonth" class="form-control" required>
                    </div>

                    <!-- Salary Amount -->
                    <div class="col-md-6 form-group">
                        <label>{{ __('file.Salary Amount') }}</label>
                        <input type="number" step="any" name="salary_amount" id="editSalaryAmount"
                            class="form-control salary-input" data-emp="editEmp">
                    </div>

                    <!-- Previous Transactions -->
                    <div class="col-md-6 form-group">
                        <label>{{ __('file.Transactions') }}</label>
                        <input type="number" step="any" name="previous_transactions"
                            id="editPreviousTransactions" class="form-control prev-input" data-emp="editEmp">
                    </div>

                    <!-- Sale Commission -->
                    <div class="col-md-6 form-group">
                        <label>{{ __('file.Sale Commission') }}</label>
                        <input type="number" step="any" name="commission" id="editCommissionAmount"
                            class="form-control comm-input" data-emp="editEmp" data-percent="0">
                    </div>

                    <!-- Total Payable -->
                    <div class="col-md-6 form-group">
                        <label>{{ __('file.Total') }}</label>
                        <input type="number" step="any" name="amount" id="editTotalPayable"
                            class="form-control total-output" data-emp="editEmp" readonly>
                    </div>

                    <!-- Date -->
                    <div class="col-md-6 form-group">
                        <label>{{ __('file.date') }}</label>
                        <input type="text" name="created_at" id="editDate" class="form-control date"
                            placeholder="{{ __('file.Choose date') }}">
                    </div>

                    <!-- Account -->
                    <div class="col-md-6 form-group">
                        <label>{{ __('file.Account') }} *</label>
                        <select class="form-control selectpicker" name="account_id" id="editAccount">
                            @foreach ($lims_account_list as $account)
                                <option value="{{ $account->id }}">{{ $account->name }}
                                    [{{ $account->account_no }}]</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Payment Method -->
                    <div class="col-md-6 form-group">
                        <label>{{ __('file.Method') }} *</label>
                        <select class="form-control selectpicker" name="paying_method" id="editPayingMethod"
                            required>
                            <option value="0">{{ __("file.Cash") }}</option>
                            <option value="1">{{ __("Cheque") }}</option>
                            <option value="2">{{ __("Credit Card") }}</option>
                        </select>
                    </div>

                    <!-- Note -->
                    <div class="col-md-12 form-group">
                        <label>{{ __('file.Note') }}</label>
                        <textarea name="note" id="editNote" rows="3" class="form-control"></textarea>
                    </div>

                </div>

                <div class="text-end mt-3">
                    <button type="submit" class="btn btn-primary">
                        <i class="dripicons-checkmark"></i> {{ __('file.Submit') }}
                    </button>
                </div>

                {{ Form::close() }}
            </div>
        </div>
    </div>
</div>

<!-- View Payroll Modal -->
<div id="viewModal" tabindex="-1" role="dialog" aria-labelledby="viewModalLabel" aria-hidden="true"
    class="modal fade text-left">
    <div role="document" class="modal-dialog modal-lg">
        <div class="modal-content border-0 shadow-sm">
            <div class="modal-header">

                <button type="button" data-dismiss="modal" aria-label="Close" class="close">
                    <span aria-hidden="true"><i class="dripicons-cross"></i></span>
                </button>
            </div>

            <div class="modal-body p-4">
                <!-- Employee Info -->
                <div class="mb-4 text-center">
                    <h4 id="viewEmployee" class="fw-bold">-----</h4>
                    <p class="text-muted mb-0">{{ __("file.Payroll & Attendance Overview") }}</p>
                </div>

                <div class="row text-center">
                    <!-- Leaves -->
                    <div class="col-md-4 mb-3">
                        <div class="card shadow-sm border-0">
                            <div class="card-body">
                                <h6 class="text-muted">{{ __("file.Leaves") }}</h6>
                                <h3 class="fw-bold text-danger" id="viewLeaves">0 {{ __("file.days") }}</h3>
                            </div>
                        </div>
                    </div>

                    <!-- Work Duration -->
                    <div class="col-md-4 mb-3">
                        <div class="card shadow-sm border-0">
                            <div class="card-body">
                                <h6 class="text-muted">{{ __("file.Work Duration") }}</h6>
                                <h3 class="fw-bold text-success" id="viewWorkDuration">0.00 {{ __("file.hour") }}</h3>
                            </div>
                        </div>
                    </div>

                    <!-- Attendance -->
                    <div class="col-md-4 mb-3">
                        <div class="card shadow-sm border-0">
                            <div class="card-body">
                                <h6 class="text-muted">{{ __("file.Attendance") }}</h6>
                                <h3 class="fw-bold text-primary" id="viewAttendance">0 {{ __("file.days") }}</h3>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Payroll Info -->
                <hr>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="fw-bold">{{ __("file.Month") }}:</label>
                        <p id="viewMonth">--</p>
                    </div>
                    <div class="col-md-6">
                        <label class="fw-bold">{{ __("file.Salery Amount") }}:</label>
                        <p id="viewSalaryAmount">--</p>
                    </div>
                    <div class="col-md-6">
                        <label class="fw-bold">{{ __("file.Sale Commission") }}:</label>
                        <p id="viewCommissionAmount">--</p>
                    </div>
                    <div class="col-md-6">
                        <label class="fw-bold">{{ __("file.Expense") }}:</label>
                        <p id="viewPreviousTransactions">--</p>
                    </div>
                    <div class="col-md-6">
                        <label class="fw-bold">{{ __("file.Total Payable") }}:</label>
                        <p id="viewTotalPayable">--</p>
                    </div>
                    <div class="col-md-6">
                        <label class="fw-bold">{{ __("file.Payment Method") }}:</label>
                        <p id="viewPayingMethod">--</p>
                    </div>
                    <div class="col-md-12">
                        <label class="fw-bold">{{ __("file.Note") }}:</label>
                        <p id="viewNote">--</p>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<style>
    .select2-container {
        width: 100% !important;
    }

    span.selection {
        width: 100%;
    }
</style>
@endsection

@push('scripts')
    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script type="text/javascript">
        $("ul#hrm").siblings('a').attr('aria-expanded', 'true');
        $("ul#hrm").addClass("show");
        $("ul#hrm #payroll-menu").addClass("active");

        var payroll_id = [];
        var user_verified = <?php echo json_encode(env('USER_VERIFIED')); ?>;

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        function confirmDelete() {
            if (confirm("Are you sure want to delete?")) {
                return true;
            }
            return false;
        }

        $(document).ready(function() {
            // Initialize tooltips
            $('[data-toggle="tooltip"]').tooltip();

            let activeModal = null; // Tracks which modal triggered the template modal

            /*========================================
              WHEN PAYROLL TEMPLATE ADD BUTTON IS CLICKED
              → HIDE CURRENT MODAL (Generate or Add)
              → SHOW TEMPLATE MODAL
            ==========================================*/
            $(".add-template-btn").on("click", function() {
                let source = $(this).data('source');
                if (source === 'multiple') {
                    activeModal = "#addMultipleModal";
                } else {
                    activeModal = "#createModal";
                }
                
                // Hide the current modal
                $(activeModal).modal("hide");
                
                // Wait for the hide transition to finish, then show the template modal
                $(activeModal).one("hidden.bs.modal", function() {
                    $("#createTemplateModal").modal("show");
                });
            });

            /*========================================
              WHEN TEMPLATE MODAL IS CLOSED
              → SHOW THE CORRECT PREVIOUS MODAL AGAIN
            ==========================================*/
            $("#createTemplateModal").on("hidden.bs.modal", function() {
                if (activeModal) {
                    $(activeModal).modal("show");
                }
            });


            let itemIndex = 1;

            // Add new item row to the template modal

    // Add new item row to the template modal
    $("#addTemplateItemBtn").on("click", function() {
        let html = `
        <div class="template-item-row border p-3 mb-2 rounded bg-light">
            <div class="row">
                <div class="col-md-4">
                    <label>Item Name</label>
                    <input type="text" name="items[${itemIndex}][name]" class="form-control item-name" placeholder="e.g. Housing" required>
                </div>
                <div class="col-md-3">
                    <label>Type</label>
                    <select name="items[${itemIndex}][type]" class="form-control item-type">
                        <option value="allowance">Allowance (Earning)</option>
                        <option value="deduction">Deduction</option>
                    </select>
                </div>
                <div class="col-md-5">
                    <label>Amount (Per Duration)</label>
                    <input type="number" step="any" name="items[${itemIndex}][amount]" class="form-control item-amount" placeholder="e.g. 50.00" required>
                </div>
                <div class="col-md-6 mt-2">
                    <label>Description</label>
                    <input type="text" name="items[${itemIndex}][description]" class="form-control">
                </div>
                <div class="col-md-4 mt-2 d-flex align-items-center">
                    <div class="form-check">
                        <input type="checkbox" name="items[${itemIndex}][taxable]" value="1" class="form-check-input item-taxable" id="taxable_${itemIndex}">
                        <label class="form-check-label" for="taxable_${itemIndex}">Taxable</label>
                    </div>
                </div>
                <div class="col-md-2 mt-2 text-right">
                    <button type="button" class="btn btn-danger btn-sm remove-item-btn"><i class="dripicons-trash"></i></button>
                </div>
            </div>
        </div>`;
        $("#templateItemsContainer").append(html);
        itemIndex++;
    });

    // Remove item row
    $(document).on("click", ".remove-item-btn", function() {
        $(this).closest(".template-item-row").remove();
        calculateTemplateTotals(); // Recalculate after removing
    });

    // Live Calculation Function
    function calculateTemplateTotals() {
        let totalPerDuration = 0;
        let workDuration = parseFloat($("#work_duration").val()) || 0;
        let taxPercentage = 0;

        // Sum all item amounts
        $(".template-item-row").each(function() {
            let amount = parseFloat($(this).find('.item-amount').val()) || 0;
            let type = $(this).find('.item-type').val();
            
            if (type == 'allowance') {
                totalPerDuration += amount;
            } else {
                totalPerDuration -= amount;
            }
        });

        // Get Tax Percentage from selected taxes
        $("#template_tax_select option:selected").each(function() {
            taxPercentage += parseFloat($(this).data('rate')) || 0;
        });

        let grossAmount = totalPerDuration * workDuration;
        let taxAmount = grossAmount * (taxPercentage / 100);
        let netAmount = grossAmount - taxAmount;

        // Update UI
        $("#sum_amount_per_duration").text(totalPerDuration.toFixed(2));
        $("#sum_gross_amount").text(grossAmount.toFixed(2));
        $("#sum_tax_amount").text(taxAmount.toFixed(2));
        $("#sum_net_amount").text(netAmount.toFixed(2));
    }

    // Trigger live calculation on any input change
    $(document).on('input change', '#work_duration, .item-amount, .item-type', function() {
        calculateTemplateTotals();
    });

    // Trigger calculation when tax dropdown changes
    $('#template_tax_select').on('changed.bs.select', function() {
        calculateTemplateTotals();
    });

    // ==========================================
    // AJAX SUBMIT FOR TEMPLATE
    // ==========================================
    $("#payrollTemplateAddForm").on("submit", function(e) {
        e.preventDefault();

        $.ajax({
            url: "{{ url('payroll-templates/store') }}", 
            type: "POST",
            data: $(this).serialize(),
            success: function(res) {
                if(res.success) {
                    // Add new template to BOTH select dropdowns
                    let newOption = `<option value="${res.id}" selected>${res.name}</option>`;
                    $("#payroll_template_select").append(newOption);
                    $("#payroll_template_select_multiple").append(newOption);

                    // Refresh selectpicker
                    $('#payroll_template_select').selectpicker('refresh');
                    $('#payroll_template_select_multiple').selectpicker('refresh');

                    // Hide modal and reset form
                    $("#createTemplateModal").modal('hide');
                    $("#payrollTemplateAddForm")[0].reset();
                    $("#templateItemsContainer").html(''); // Clear items
                    
                    toastr.success("Payroll Template & Items Added Successfully");
                }
            },
            error: function(err) {
                toastr.error("Something went wrong while adding the template!");
            }
        });
    });


            // Remove item row
            $(document).on("click", ".remove-item-btn", function() {
                $(this).closest(".template-item-row").remove();
            });

            /*========================================
            AJAX — ADD PAYROLL TEMPLATE + ITEMS
            ==========================================*/
            $("#payrollTemplateAddForm").on("submit", function(e) {
                e.preventDefault();

                $.ajax({
                    url: "{{ url('payroll-templates/store') }}", 
                    type: "POST",
                    data: $(this).serialize(),
                    success: function(res) {
                        if(res.success) {
                            // Add new template to BOTH select dropdowns
                            let newOption = `<option value="${res.id}" selected>${res.name}</option>`;
                            $("#payroll_template_select").append(newOption);
                            $("#payroll_template_select_multiple").append(newOption);

                            // Refresh selectpicker
                            $('#payroll_template_select').selectpicker('refresh');
                            $('#payroll_template_select_multiple').selectpicker('refresh');

                            // Hide modal and reset form
                            $("#createTemplateModal").modal('hide');
                            $("#payrollTemplateAddForm")[0].reset();
                            // Reset items back to one
                            $("#templateItemsContainer").html(''); 
                            
                            toastr.success("Payroll Template & Items Added Successfully");
                        }
                    },
                    error: function(err) {
                        toastr.error("Something went wrong while adding the template!");
                    }
                });
            });


            // Function to load employees via AJAX
            function loadEmployees(warehouse_id = 0) {
                $.ajax({
                    url: "{{ route('payroll.getEmployeesByWarehouse') }}",
                    type: "GET",
                    data: {
                        warehouse_id
                    },
                    success: function(data) {
                        let $select = $('#employeeMultiple');

                        // Destroy previous Select2 instance if exists
                        if ($select.hasClass('select2-hidden-accessible')) {
                            $select.select2('destroy');
                        }

                        // Clear existing options
                        $select.empty();

                        if (data.length > 0) {
                            data.forEach(emp => {
                                $select.append(new Option(emp.name, emp.id, false, false));
                            });
                            $select.prop('disabled', false);
                        } else {
                            $select.append(new Option('No employees available', '', false, false));
                            $select.prop('disabled', true);
                        }

                        // Initialize Select2
                        $select.select2({
                            placeholder: data.length > 0 ? 'Select Employees...' :
                                'No employees available',
                            width: '100%',
                            allowClear: true
                        });
                    },
                    error: function() {
                        alert('Error loading employees!');
                    }
                });
            }

            // On page load, load all employees
            loadEmployees(0);

            // On warehouse change
            $('#warehouseSelect').on('change', function() {
                let warehouse_id = $(this).val() || 0; // 0 = all warehouses
                loadEmployees(warehouse_id);
            });

            // Select All Employees
            $('#selectAllEmployees').click(function() {
                let $select = $('#employeeMultiple');
                if (!$select.prop('disabled')) {
                    $select.find('option').prop('selected', true);
                    $select.trigger('change');
                }
            });

            // Deselect All Employees
            $('#deselectAllEmployees').click(function() {
                let $select = $('#employeeMultiple');
                if (!$select.prop('disabled')) {
                    $select.find('option').prop('selected', false);
                    $select.trigger('change');
                }
            });

            $(document).on('click', '.view-btn', function() {
                let payroll = $(this).data();
                $('#viewEmployee').text(payroll.employee_name);
                $('#viewLeaves').text(payroll.leaves + ' days');
                $('#viewWorkDuration').text(payroll.work_duration + ' hour');
                $('#viewAttendance').text(payroll.attendance + ' Days');
                $('#viewMonth').text(payroll.month);
                $('#viewSalaryAmount').text(payroll.salary);
                $('#viewCommissionAmount').text(payroll.commission);
                $('#viewPreviousTransactions').text(payroll.transactions);
                $('#viewTotalPayable').text(payroll.amount);
                $('#viewPayingMethod').text(payroll.paying_method_text);
                $('#viewNote').text(payroll.note);
            });

            function calculateEditTotal(empId) {
                let salary = parseFloat($(`input.salary-input[data-emp='${empId}']`).val()) || 0;
                let prev = parseFloat($(`input.prev-input[data-emp='${empId}']`).val()) || 0;
                let commission = parseFloat($(`input.comm-input[data-emp='${empId}']`).val()) || 0;
                if (commission > 0) {
                    commission = (salary * commission) / 100;
                }

                let total = salary + commission - prev;
                $(`input.total-output[data-emp='${empId}']`).val(total.toFixed(2));
            }

            // Trigger calculation on input change
            $('#editSalaryAmount, #editPreviousTransactions, #editCommissionAmount').on('input', function() {
                let empId = $(this).data('emp');
                calculateEditTotal(empId);
            });

            // Open modal and populate data
            $('.edit-btn').on('click', function() {
                let empId = 'editEmp'; // single modal identifier

                $('#editPayrollId').val($(this).data('id'));
                $('#editEmployee').val($(this).data('employee')).selectpicker('refresh');
                $('#editMonth').val($(this).data('month'));
                $('#editSalaryAmount').val($(this).data('salary')).data('emp', empId);
                $('#editPreviousTransactions').val($(this).data('prev')).data('emp', empId);
                $('#editCommissionAmount').val($(this).data('commission')).data('emp', empId);
                $('#editTotalPayable').data('emp', empId);
                $('#editDate').val($(this).data('date'));
                $('#editAccount').val($(this).data('account')).selectpicker('refresh');
                $('#editPayingMethod').val($(this).data('paying_method')).selectpicker('refresh');
                $('#editNote').val($(this).data('note'));

                // calculate total immediately
                calculateEditTotal(empId);
            });

            // Filter DataTable based on inputs
            $('#filterEmployee, #filterMonth, #filterDate').on('change keyup', function() {
                let employee = $('#filterEmployee').val();
                let month = $('#filterMonth').val();
                let date = $('#filterDate').val();

                let table = $('#payroll-table').DataTable();

                table.rows().every(function() {
                    let row = this.node();
                    let rowData = $(row).data();

                    let show = true;

                    // Employee filter
                    if (employee && rowData.employee_id != employee) show = false;

                    // Month filter
                    if (month && rowData.month != month) show = false;

                    // Date filter
                    if (date && rowData.date != date) show = false;

                    $(row).toggle(show);
                });
            });

        });

        $('#payroll-table').DataTable({
            "order": [],
            'language': {
                'lengthMenu': '_MENU_ {{ __('file.records per page') }}',
                "info": '<small>{{ __('file.Showing') }} _START_ - _END_ (_TOTAL_)</small>',
                "search": '{{ __('file.Search') }}',
                'paginate': {
                    'previous': '<i class="dripicons-chevron-left"></i>',
                    'next': '<i class="dripicons-chevron-right"></i>'
                }
            },
            'columnDefs': [{
                    "orderable": false,
                    'targets': [0, 1, 6]
                },
                {
                    'render': function(data, type, row, meta) {
                        if (type === 'display') {
                            data =
                                '<div class="checkbox"><input type="checkbox" class="dt-checkboxes"><label></label></div>';
                        }

                        return data;
                    },
                    'checkboxes': {
                        'selectRow': true,
                        'selectAllRender': '<div class="checkbox"><input type="checkbox" class="dt-checkboxes"><label></label></div>'
                    },
                    'targets': [0]
                }
            ],
            'select': {
                style: 'multi',
                selector: 'td:first-child'
            },
            'lengthMenu': [
                [10, 25, 50, -1],
                [10, 25, 50, "All"]
            ],
            dom: '<"row"lfB>rtip',
            buttons: [{
                    extend: 'pdf',
                    text: '<i title="export to pdf" class="fa fa-file-pdf-o"></i>',
                    exportOptions: {
                        columns: ':visible:Not(.not-exported)',
                        rows: ':visible',
                    },
                    action: function(e, dt, button, config) {
                        datatable_sum(dt, true);
                        $.fn.dataTable.ext.buttons.pdfHtml5.action.call(this, e, dt, button, config);
                        datatable_sum(dt, false);
                    },
                    footer: true
                },
                {
                    extend: 'excel',
                    text: '<i title="export to excel" class="dripicons-document-new"></i>',
                    exportOptions: {
                        columns: ':visible:Not(.not-exported)',
                        rows: ':visible',
                    },
                    action: function(e, dt, button, config) {
                        datatable_sum(dt, true);
                        $.fn.dataTable.ext.buttons.excelHtml5.action.call(this, e, dt, button, config);
                        datatable_sum(dt, false);
                    },
                    footer: true
                },
                {
                    extend: 'csv',
                    text: '<i title="export to csv" class="fa fa-file-text-o"></i>',
                    exportOptions: {
                        columns: ':visible:Not(.not-exported)',
                        rows: ':visible',
                    },
                    action: function(e, dt, button, config) {
                        datatable_sum(dt, true);
                        $.fn.dataTable.ext.buttons.csvHtml5.action.call(this, e, dt, button, config);
                        datatable_sum(dt, false);
                    },
                    footer: true
                },
                {
                    extend: 'print',
                    text: '<i title="print" class="fa fa-print"></i>',
                    exportOptions: {
                        columns: ':visible:Not(.not-exported)',
                        rows: ':visible',
                    },
                    action: function(e, dt, button, config) {
                        datatable_sum(dt, true);
                        $.fn.dataTable.ext.buttons.csvHtml5.action.call(this, e, dt, button, config);
                        datatable_sum(dt, false);
                    },
                    footer: true
                },
                {
                    text: '<i title="delete" class="dripicons-cross"></i>',
                    className: 'buttons-delete',
                    action: function(e, dt, node, config) {
                        if (user_verified == '1') {
                            payroll_id.length = 0;
                            $(':checkbox:checked').each(function(i) {
                                if (i) {
                                    payroll_id[i - 1] = $(this).closest('tr').data('id');
                                }
                            });
                            if (payroll_id.length && confirm("Are you sure want to delete?")) {
                                $.ajax({
                                    type: 'POST',
                                    url: 'payroll/deletebyselection',
                                    data: {
                                        payrollIdArray: payroll_id
                                    },
                                    success: function(data) {
                                        $(':checkbox:checked').each(function(i) {
                                            if (i) {
                                                dt.row($(this).closest('tr')).remove()
                                                    .draw(false);
                                            }
                                        });
                                        alert(data);
                                    }
                                });
                            } else if (!payroll_id.length)
                                alert('No payroll is selected!');
                        } else
                            alert('This feature is disable for demo!');
                    }
                },
                {
                    extend: 'colvis',
                    text: '<i title="column visibility" class="fa fa-eye"></i>',
                    columns: ':gt(0)'
                },
            ],
            drawCallback: function() {
                var api = this.api();
                datatable_sum(api, false);
            }
        });

        function datatable_sum(dt_selector, is_calling_first) {
            if (dt_selector.rows('.selected').any() && is_calling_first) {
                var rows = dt_selector.rows('.selected').indexes();

                $(dt_selector.column(5).footer()).html(dt_selector.cells(rows, 5, {
                    page: 'current'
                }).data().sum().toFixed({{ $general_setting->decimal }}));
            } else {
                $(dt_selector.column(5).footer()).html(dt_selector.cells(rows, 5, {
                    page: 'current'
                }).data().sum().toFixed({{ $general_setting->decimal }}));
            }
        }

        // Fetch payroll data via AJAX when both employee and month are selected
        function fetchPayrollData() {
            let employee_id = $('#employee_id').val();
            let month = $('#monthSelect').val();

            if (employee_id && month) {
                $.ajax({
                    url: "{{ route('payroll.monthlyData') }}",
                    type: "GET",
                    data: {
                        employee_id: employee_id,
                        month: month
                    },
                    success: function(data) {
                        // Fill salary, transactions, and commission fields with fetched data
                        $('#salaryAmount').val(data.salary);
                        $('#previousTransactions').val(data.transactions);
                        $('#commissionAmount').val(data.commission);

                        // Calculate total payable
                        calculateTotal();
                    },
                    error: function() {
                        alert('Error loading payroll data!');
                    }
                });
            }
        }

        // Calculate total payable based on salary, commission, and previous transactions
        function calculateTotal() {
            let salary = parseFloat($('#salaryAmount').val()) || 0;
            let transactions = parseFloat($('#previousTransactions').val()) || 0;
            let commission = parseFloat($('#commissionAmount').val()) || 0;

            let total = (salary + commission) - transactions;
            $('#totalPayable').val(total.toFixed(2));
        }

        // Trigger fetch when both employee and month are selected
        $('#employee_id, #monthSelect').on('change', function() {
            fetchPayrollData();
        });

        // Trigger total calculation on keyup in salary, transactions, or commission fields
        $('#salaryAmount, #previousTransactions, #commissionAmount').on('keyup change', function() {
            calculateTotal();
        });
    </script>
@endpush