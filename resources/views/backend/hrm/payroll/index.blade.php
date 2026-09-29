@extends('backend.layout.main')
@section('content')

<section>
    <div class="container-fluid">
        <button class="btn btn-success" data-toggle="modal" data-target="#addMultipleModal">
            <i class="dripicons-plus"></i> {{ __("file.Generate Payroll") }}
        </button>

        <div class="d-inline-block ml-2">
            <button class="btn btn-secondary" type="button" data-toggle="collapse"
                data-target="#filterCollapse" aria-expanded="false" aria-controls="filterCollapse">
                <i class="dripicons-filter"></i> {{ __('file.Filter') }}
            </button>
        </div>

        <div class="collapse mt-3" id="filterCollapse">
            <div class="card card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label>{{ __('file.Employee') }}</label>
                        <select id="filterEmployee" class="form-control selectpicker"
                            data-live-search="true" title="Select Employee">
                            <option value="">{{ __('file.All') }}</option>
                            @foreach ($lims_employee_list as $employee)
                                <option value="{{ $employee->id }}">{{ $employee->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label>{{ __('file.Month') }}</label>
                        <input type="month" id="filterMonth" class="form-control">
                    </div>
                    <div class="col-md-4">
                        <label>{{ __('file.date') }}</label>
                        <input type="date" id="filterDate" class="form-control">
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ============================ --}}
    {{-- GENERATE PAYROLL MODAL       --}}
    {{-- ============================ --}}
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
                    @csrf
                    <div class="row g-3">

                        <div class="col-md-6 form-group">
                            <label>{{ __('file.Warehouse') }} *</label>
                            <select id="warehouseSelect" name="warehouse_id"
                                class="form-control select2" required>
                                <option value="0">{{ __('file.All Warehouse') }}</option>
                                @foreach ($lims_warehouse_list as $warehouse)
                                    <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6 form-group">
                            <label>{{ __('file.Month') }} *</label>
                            <input type="month" name="month" id="monthMultiple"
                                class="form-control" required>
                        </div>

                        <div class="col-md-6 form-group">
                            <label>{{ __('file.Account') }} *</label>
                            <select name="account_id" class="form-control select2"
                                required title="Select Account...">
                                @foreach ($lims_account_list as $account)
                                    <option value="{{ $account->id }}"
                                        {{ $account->is_default ? 'selected' : '' }}>
                                        {{ $account->name }} [{{ $account->account_no }}]
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Payroll Template Select + Add Button --}}
                        <div class="col-md-6 form-group">
                            <label>{{ __('file.Payroll Template') }}</label>
                            <div class="input-group">
                                <div style="width:calc(100% - 40px);" class="input-group-prepend">
                                    <select class="form-control selectpicker"
                                        name="payroll_template_id"
                                        id="payroll_template_select_multiple"
                                        data-live-search="true" title="Select Template...">
                                        <option value="">{{ __('file.None') }}</option>
                                        @foreach ($payroll_templates as $template)
                                            <option value="{{ $template->id }}">{{ $template->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <span class="input-group-prepend">
                                    <button type="button" class="btn btn-primary add-template-btn"
                                        data-source="multiple"
                                        title="{{ __('file.Add Payroll Template') }}">
                                        <i class="dripicons-plus"></i>
                                    </button>
                                </span>
                            </div>
                        </div>

                        <div class="col-md-12 form-group">
                            <label>{{ __("file.Employees") }} *</label>
                            <div class="mb-2">
                                <button type="button" class="btn btn-sm btn-outline-primary"
                                    id="selectAllEmployees">{{ __('file.Select All') }}</button>
                                <button type="button" class="btn btn-sm btn-outline-danger"
                                    id="deselectAllEmployees">{{ __('file.Deselect All') }}</button>
                            </div>
                            <select id="employeeMultiple" name="employee_ids[]"
                                class="form-control" multiple required>
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

    {{-- ============================ --}}
    {{-- CREATE TEMPLATE MODAL         --}}
    {{-- ============================ --}}
    <div id="createTemplateModal" class="modal fade" tabindex="-1" role="dialog"
        aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <form id="payrollTemplateAddForm">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">{{ __('file.Add Payroll Template') }}</h5>
                        <button type="button" class="close" data-dismiss="modal"
                            aria-label="Close">
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
                            <div class="col-md-6 form-group">
                                <label>Work Duration *</label>
                                <input type="number" step="any" name="work_duration"
                                    id="work_duration" class="form-control" value="1" required>
                            </div>
                            <div class="col-md-6 form-group">
                                <label>Duration Unit</label>
                                <select name="duration_unit" id="duration_unit"
                                    class="form-control">
                                    <option value="hour">Hour</option>
                                    <option value="day" selected>Day</option>
                                    <option value="week">Week</option>
                                    <option value="month">Month</option>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label>Amount Per Duration</label>
                                <input type="number" step="any" name="amount_per_duration"
                                    id="amount_per_duration" class="form-control" value="0">
                            </div>
                            <div class="col-md-6 form-group">
                                <label>{{ __('file.Tax') }} (Multi-select)</label>
                                <select name="tax_ids[]" id="template_tax_select"
                                    class="form-control selectpicker" multiple
                                    data-live-search="true"
                                    title="Select applicable taxes...">
                                    @foreach ($lims_tax_list as $tax)
                                        <option value="{{ $tax->id }}"
                                            data-rate="{{ $tax->rate }}">
                                            {{ $tax->name }} ({{ $tax->rate }}%)
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <hr>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6><i class="dripicons-list"></i> Template Items</h6>
                            <button type="button" class="btn btn-sm btn-info"
                                id="addTemplateItemBtn">
                                <i class="dripicons-plus"></i> Add Item
                            </button>
                        </div>

                        {{-- Items container — default row is injected by JS on modal open --}}
                        <div id="templateItemsContainer"></div>

                        {{-- Live Calculation Summary --}}
                        <div class="mt-3 p-3 bg-light border rounded">
                            <h6 class="mb-3">
                                <i class="dripicons-calculator"></i> Calculation Summary
                            </h6>
                            <div class="d-flex justify-content-between mb-1">
                                <span>Items Total Per Duration:</span>
                                <strong id="sum_items_total">0.00</strong>
                            </div>
                            <div class="d-flex justify-content-between mb-1">
                                <span>Total Per Duration (items + base):</span>
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
                        <button type="button" class="btn btn-secondary"
                            data-dismiss="modal">{{ __('file.Close') }}</button>
                        <button type="submit" class="btn btn-primary">
                            {{ __('file.Save Template') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ============================ --}}
    {{-- PAYROLL TABLE                --}}
    {{-- ============================ --}}
    <div class="table-responsive mt-3">
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
                        $employee = $payroll->employee;
                        $account  = $payroll->account;
                        $monthName = '';
                        if ($payroll->month && $payroll->year) {
                            try {
                                $monthName = \Carbon\Carbon::create(
                                    $payroll->year, $payroll->month, 1
                                )->format('F Y');
                            } catch (\Exception $e) {
                                $monthName = $payroll->month . '/' . $payroll->year;
                            }
                        }
                        $dateStr = $payroll->date
                            ? date($general_setting->date_format, strtotime($payroll->date))
                            : date($general_setting->date_format, strtotime($payroll->created_at));
                    @endphp
                    <tr data-id="{{ $payroll->id }}"
                        data-employee_id="{{ $payroll->employee_id }}"
                        data-year="{{ $payroll->year }}"
                        data-month="{{ $payroll->month }}"
                        data-date="{{ $payroll->date ? $payroll->date->format('Y-m-d') : '' }}">
                        <td>{{ $key }}</td>
                        <td>{{ $dateStr }}</td>
                        <td>{{ $payroll->reference_no }}</td>
                        <td>{{ $employee ? $employee->name : '—' }}</td>
                        <td>{{ @$account->name }}</td>
                        <td>{{ number_format((float) $payroll->amount,
                            $general_setting->decimal, '.', '') }}</td>
                        <td>
                            @if ($payroll->paying_method == 0)
                                {{ __("file.Cash") }}
                            @elseif($payroll->paying_method == 1)
                                {{ __("file.Cheque") }}
                            @else
                                {{ __("file.Credit Card") }}
                            @endif
                        </td>
                        <td>{{ $monthName }}</td>
                        <td>
                            <div class="btn-group">
                                <button type="button"
                                    class="btn btn-default btn-sm dropdown-toggle"
                                    data-toggle="dropdown" aria-haspopup="true"
                                    aria-expanded="false">
                                    {{ __('file.action') }}
                                    <span class="caret"></span>
                                </button>
                                <ul class="dropdown-menu edit-options dropdown-menu-right dropdown-default">
                                    <li>
                                        <button type="button" class="btn btn-link edit-btn"
                                            data-id="{{ $payroll->id }}"
                                            data-date="{{ $payroll->date ? $payroll->date->format('Y-m-d') : '' }}"
                                            data-employee="{{ $payroll->employee_id }}"
                                            data-account="{{ $payroll->account_id }}"
                                            data-amount="{{ $payroll->amount }}"
                                            data-paying_method="{{ $payroll->paying_method }}"
                                            data-note="{{ $payroll->note }}"
                                            data-year="{{ $payroll->year }}"
                                            data-month="{{ $payroll->month }}"
                                            data-template="{{ $payroll->payroll_template_id }}"
                                            data-toggle="modal"
                                            data-target="#editModal">
                                            <i class="dripicons-document-edit"></i> {{ __('file.Edit') }}
                                        </button>
                                    </li>
                                    <li>
                                        <button type="button" class="btn btn-link view-btn"
                                            data-id="{{ $payroll->id }}"
                                            data-employee_name="{{ $employee ? $employee->name : '—' }}"
                                            data-month="{{ $monthName }}"
                                            data-amount="{{ $payroll->amount }}"
                                            data-gross="{{ $payroll->gross_amount }}"
                                            data-allowances="{{ $payroll->total_allowances }}"
                                            data-deductions="{{ $payroll->total_deductions }}"
                                            data-paying_method_text="@if ($payroll->paying_method == 0) Cash @elseif($payroll->paying_method == 1) Cheque @else Credit Card @endif"
                                            data-note="{{ $payroll->note }}"
                                            data-toggle="modal"
                                            data-target="#viewModal">
                                            <i class="dripicons-preview"></i> {{ __('file.View') }}
                                        </button>
                                    </li>
                                    <li class="divider"></li>
                                    {{ Form::open(['route' => ['payroll.destroy', $payroll->id], 'method' => 'DELETE']) }}
                                    <li>
                                        <button type="submit" class="btn btn-link"
                                            onclick="return confirmDelete()">
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
                    <th></th><th></th><th></th><th></th><th></th><th></th><th></th>
                </tr>
            </tfoot>
        </table>
    </div>
</section>

{{-- ============================ --}}
{{-- ADD PAYROLL MODAL             --}}
{{-- ============================ --}}
<div id="createModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
    aria-hidden="true" class="modal fade text-left">
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
                @csrf
                <div class="row g-3">
                    <div class="col-md-6 form-group">
                        <label>{{ __('file.Employee') }} *</label>
                        <select class="form-control selectpicker" name="employee_id"
                            id="employee_id" required data-live-search="true"
                            title="Select Employee...">
                            @foreach ($lims_employee_list as $employee)
                                <option value="{{ $employee->id }}">{{ $employee->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6 form-group">
                        <label>{{ __('file.Month') }} *</label>
                        <input type="month" name="month" id="monthSelect"
                            class="form-control" required>
                    </div>
                    <div class="col-md-6 form-group">
                        <label>{{ __('file.Payroll Template') }}</label>
                        <div class="input-group">
                            <div style="width:calc(100% - 40px);" class="input-group-prepend">
                                <select class="form-control selectpicker"
                                    name="payroll_template_id"
                                    id="payroll_template_select"
                                    data-live-search="true" title="Select Template...">
                                    <option value="">{{ __('file.None') }}</option>
                                    @foreach ($payroll_templates as $template)
                                        <option value="{{ $template->id }}">{{ $template->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <span class="input-group-prepend">
                                <button type="button" class="btn btn-primary add-template-btn"
                                    data-source="single"
                                    title="{{ __('file.Add Payroll Template') }}">
                                    <i class="dripicons-plus"></i>
                                </button>
                            </span>
                        </div>
                    </div>
                    <div class="col-md-6 form-group">
                        <label>{{ __('file.Salary Amount') }}</label>
                        <input type="number" step="any" name="salary_amount"
                            id="salaryAmount" class="form-control">
                    </div>
                    <div class="col-md-6 form-group">
                        <label>{{ __('file.Expense') }}</label>
                        <input type="number" step="any" name="previous_transactions"
                            id="previousTransactions" class="form-control">
                    </div>
                    <div class="col-md-6 form-group">
                        <label>{{ __('file.Sale Commission') }}</label>
                        <input type="number" step="any" name="commission"
                            id="commissionAmount" class="form-control">
                    </div>
                    <div class="col-md-6 form-group">
                        <label>{{ __('file.Total') }}</label>
                        <input type="number" step="any" name="amount"
                            id="totalPayable" class="form-control">
                    </div>
                    <div class="col-md-6 form-group">
                        <label>{{ __('file.date') }}</label>
                        <input type="text" name="created_at" class="form-control date"
                            placeholder="{{ __('file.Choose date') }}"
                            value="{{ date('d-m-Y') }}" />
                    </div>
                    <div class="col-md-6 form-group">
                        <label>{{ __('file.Account') }} *</label>
                        <select class="form-control selectpicker" name="account_id">
                            @foreach ($lims_account_list as $account)
                                <option value="{{ $account->id }}"
                                    {{ $account->is_default ? 'selected' : '' }}>
                                    {{ $account->name }} [{{ $account->account_no }}]
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6 form-group">
                        <label>{{ __('file.Method') }} *</label>
                        <select class="form-control selectpicker" name="paying_method" required>
                            <option value="0">{{ __("file.Cash") }}</option>
                            <option value="1">{{ __("file.Cheque") }}</option>
                            <option value="2">{{ __("file.Credit Card") }}</option>
                        </select>
                    </div>
                    <div class="col-md-12 form-group">
                        <label>{{ __('file.Note') }}</label>
                        <textarea name="note" rows="3" class="form-control"
                            placeholder="Write any note about this payroll..."></textarea>
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

{{-- ============================ --}}
{{-- EDIT PAYROLL MODAL            --}}
{{-- ============================ --}}
<div id="editModal" tabindex="-1" role="dialog" aria-labelledby="editModalLabel"
    aria-hidden="true" class="modal fade text-left">
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
                @csrf
                <input type="hidden" name="payroll_id" id="editPayrollId">
                <div class="row g-3">
                    <div class="col-md-6 form-group">
                        <label>{{ __('file.Employee') }} *</label>
                        <select class="form-control selectpicker" name="employee_id"
                            id="editEmployee" required data-live-search="true"
                            title="Select Employee...">
                            @foreach ($lims_employee_list as $employee)
                                <option value="{{ $employee->id }}">{{ $employee->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6 form-group">
                        <label>{{ __('file.Month') }} *</label>
                        <input type="month" name="month" id="editMonth"
                            class="form-control" required>
                    </div>
                    <div class="col-md-6 form-group">
                        <label>{{ __('file.Payroll Template') }}</label>
                        <select class="form-control selectpicker"
                            name="payroll_template_id" id="editTemplate"
                            data-live-search="true" title="Select Template...">
                            <option value="">{{ __('file.None') }}</option>
                            @foreach ($payroll_templates as $template)
                                <option value="{{ $template->id }}">{{ $template->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6 form-group">
                        <label>{{ __('file.Total') }}</label>
                        <input type="number" step="any" name="amount"
                            id="editTotalPayable" class="form-control">
                    </div>
                    <div class="col-md-6 form-group">
                        <label>{{ __('file.date') }}</label>
                        <input type="text" name="created_at" id="editDate"
                            class="form-control date"
                            placeholder="{{ __('file.Choose date') }}">
                    </div>
                    <div class="col-md-6 form-group">
                        <label>{{ __('file.Account') }} *</label>
                        <select class="form-control selectpicker" name="account_id"
                            id="editAccount">
                            @foreach ($lims_account_list as $account)
                                <option value="{{ $account->id }}">
                                    {{ $account->name }} [{{ $account->account_no }}]
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6 form-group">
                        <label>{{ __('file.Method') }} *</label>
                        <select class="form-control selectpicker" name="paying_method"
                            id="editPayingMethod" required>
                            <option value="0">{{ __("file.Cash") }}</option>
                            <option value="1">{{ __("Cheque") }}</option>
                            <option value="2">{{ __("Credit Card") }}</option>
                        </select>
                    </div>
                    <div class="col-md-12 form-group">
                        <label>{{ __('file.Note') }}</label>
                        <textarea name="note" id="editNote" rows="3"
                            class="form-control"></textarea>
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

{{-- ============================ --}}
{{-- VIEW PAYROLL MODAL            --}}
{{-- ============================ --}}
<div id="viewModal" tabindex="-1" role="dialog" aria-hidden="true"
    class="modal fade text-left">
    <div role="document" class="modal-dialog modal-lg">
        <div class="modal-content border-0 shadow-sm">
            <div class="modal-header">
                <h5 class="modal-title">Payroll Details</h5>
                <button type="button" data-dismiss="modal" aria-label="Close" class="close">
                    <span aria-hidden="true"><i class="dripicons-cross"></i></span>
                </button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-4 text-center">
                    <h4 id="viewEmployee" class="fw-bold">-----</h4>
                    <p class="text-muted mb-0">Payroll Overview</p>
                </div>
                <hr>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="fw-bold">Month:</label>
                        <p id="viewMonth">--</p>
                    </div>
                    <div class="col-md-6">
                        <label class="fw-bold">Gross Amount:</label>
                        <p id="viewGross">--</p>
                    </div>
                    <div class="col-md-6">
                        <label class="fw-bold">Total Allowances:</label>
                        <p id="viewAllowances">--</p>
                    </div>
                    <div class="col-md-6">
                        <label class="fw-bold">Total Deductions:</label>
                        <p id="viewDeductions">--</p>
                    </div>
                    <div class="col-md-6">
                        <label class="fw-bold">Net Payable:</label>
                        <p id="viewTotalPayable">--</p>
                    </div>
                    <div class="col-md-6">
                        <label class="fw-bold">Payment Method:</label>
                        <p id="viewPayingMethod">--</p>
                    </div>
                    <div class="col-md-12">
                        <label class="fw-bold">Note:</label>
                        <p id="viewNote">--</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .bootstrap-select .dropdown-menu { z-index: 99999 !important; }
    .bootstrap-select.open .dropdown-menu { z-index: 99999 !important; }
    .modal-backdrop { z-index: 9990 !important; }
    .modal { z-index: 9995 !important; }
    .select2-container--open { z-index: 99999 !important; }
    .select2-dropdown { z-index: 99999 !important; }
    .select2-container { width: 100% !important; }
    span.selection { width: 100%; }
</style>
@endsection

@push('scripts')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script type="text/javascript">
    $("ul#hrm").siblings('a').attr('aria-expanded', 'true');
    $("ul#hrm").addClass("show");
    $("ul#hrm #payroll-menu").addClass("active");

    var payroll_id = [];
    var user_verified = <?php echo json_encode(env('USER_VERIFIED')); ?>;

    $.ajaxSetup({
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
    });

    function confirmDelete() { return confirm("Are you sure want to delete?"); }

    $(document).ready(function() {
        $('[data-toggle="tooltip"]').tooltip();

        let activeModal = null;
        let itemIndex   = 0;

        /* ========================================================
         |  TEMPLATE ITEM ROW BUILDER
         |  ✅ BUG FIX: Always has `selected` on "allowance" option
         +====================================================== */
        function getItemRowHTML(index) {
            return `
            <div class="template-item-row border p-3 mb-2 rounded bg-light">
                <div class="row">
                    <div class="col-md-4">
                        <label>Item Name</label>
                        <input type="text" name="items[${index}][name]"
                            class="form-control item-name"
                            placeholder="e.g. Basic Salary" required>
                    </div>
                    <div class="col-md-3">
                        <label>Type</label>
                        <select name="items[${index}][type]"
                            class="form-control item-type">
                            <option value="allowance" selected>Allowance (Earning)</option>
                            <option value="deduction">Deduction</option>
                        </select>
                    </div>
                    <div class="col-md-5">
                        <label>Amount</label>
                        <input type="number" step="any"
                            name="items[${index}][amount]"
                            class="form-control item-amount"
                            placeholder="e.g. 50.00" required>
                    </div>
                    <div class="col-md-6 mt-2">
                        <label>Description</label>
                        <input type="text"
                            name="items[${index}][description]"
                            class="form-control">
                    </div>
                    <div class="col-md-4 mt-2 d-flex align-items-center">
                        <div class="form-check">
                            <input type="checkbox"
                                name="items[${index}][taxable]" value="1"
                                class="form-check-input item-taxable"
                                id="taxable_${index}">
                            <label class="form-check-label"
                                for="taxable_${index}">Taxable</label>
                        </div>
                    </div>
                    <div class="col-md-2 mt-2 text-right">
                        <button type="button"
                            class="btn btn-danger btn-sm remove-item-btn">
                            <i class="dripicons-trash"></i>
                        </button>
                    </div>
                </div>
            </div>`;
        }

        /* ========================================================
         |  ADD ITEM BUTTON
         +====================================================== */
        $("#addTemplateItemBtn").on("click", function() {
            $("#templateItemsContainer").append(getItemRowHTML(itemIndex));
            itemIndex++;
        });

        /* ========================================================
         |  REMOVE ITEM BUTTON  (✅ SINGLE HANDLER ONLY)
         +====================================================== */
        $(document).on("click", ".remove-item-btn", function() {
            $(this).closest(".template-item-row").remove();
            calculateTemplateTotals();
        });

        /* ========================================================
         |  TEMPLATE MODAL: OPEN → INJECT FRESH DEFAULT ROW
         |  ✅ BUG FIX: Ensures first item is always "allowance"
         +====================================================== */
        $("#createTemplateModal").on("show.bs.modal", function() {
            $("#templateItemsContainer").html(getItemRowHTML(0));
            itemIndex = 1;
            calculateTemplateTotals();
        });

        /* ========================================================
         |  TEMPLATE MODAL: CLOSE → RESET FORM + RESTORE DEFAULT
         +====================================================== */
        $("#createTemplateModal").on("hidden.bs.modal", function() {
            $("#payrollTemplateAddForm")[0].reset();
            $("#templateItemsContainer").empty();
            itemIndex = 0;
            calculateTemplateTotals();

            if (activeModal) {
                $(activeModal).modal("show");
            }
        });

        /* ========================================================
         |  ADD-TEMPLATE BUTTON (from Generate or Add Payroll)
         +====================================================== */
        $(".add-template-btn").on("click", function() {
            activeModal = $(this).data('source') === 'multiple'
                ? "#addMultipleModal"
                : "#createModal";
            $(activeModal).modal("hide");
            $(activeModal).one("hidden.bs.modal", function() {
                $("#createTemplateModal").modal("show");
            });
        });

        /* ========================================================
         |  LIVE CALCULATION
         +====================================================== */
        function calculateTemplateTotals() {
            let itemsTotal    = 0;
            let workDuration  = parseFloat($("#work_duration").val()) || 0;
            let perDuration   = parseFloat($("#amount_per_duration").val()) || 0;
            let taxPercentage = 0;

            $(".template-item-row").each(function() {
                let amount = parseFloat($(this).find('.item-amount').val()) || 0;
                let type   = $(this).find('.item-type').val();
                if (type === 'allowance') itemsTotal += amount;
                else                       itemsTotal -= amount;
            });

            $("#template_tax_select option:selected").each(function() {
                taxPercentage += parseFloat($(this).data('rate')) || 0;
            });

            let totalPerDuration = perDuration + itemsTotal;
            let grossAmount      = totalPerDuration * workDuration;
            let taxAmount        = grossAmount * (taxPercentage / 100);
            let netAmount        = grossAmount - taxAmount;

            $("#sum_items_total").text(itemsTotal.toFixed(2));
            $("#sum_amount_per_duration").text(totalPerDuration.toFixed(2));
            $("#sum_gross_amount").text(grossAmount.toFixed(2));
            $("#sum_tax_amount").text(taxAmount.toFixed(2));
            $("#sum_net_amount").text(netAmount.toFixed(2));
        }

        $(document).on('input change',
            '#work_duration, #amount_per_duration, .item-amount, .item-type',
            calculateTemplateTotals);

        $('#template_tax_select').on('changed.bs.select', calculateTemplateTotals);

        /* ========================================================
         |  AJAX SUBMIT TEMPLATE  (✅ SINGLE HANDLER ONLY)
         +====================================================== */
        $("#payrollTemplateAddForm").on("submit", function(e) {
            e.preventDefault();
            $.ajax({
                url:  "{{ url('payroll-templates/store') }}",
                type: "POST",
                data: $(this).serialize(),
                success: function(res) {
                    if (res.success) {
                        let opt = `<option value="${res.id}">${res.name}</option>`;
                        $("#payroll_template_select").append(opt);
                        $("#payroll_template_select_multiple").append(opt);
                        $("#editTemplate").append(opt);

                        $('#payroll_template_select').val(res.id).selectpicker('refresh');
                        $('#payroll_template_select_multiple').val(res.id).selectpicker('refresh');
                        $('#editTemplate').selectpicker('refresh');

                        $("#createTemplateModal").modal('hide');
                        toastr.success("Payroll Template saved successfully");
                    }
                },
                error: function() {
                    toastr.error("Something went wrong while adding the template!");
                }
            });
        });

        /* ========================================================
         |  EMPLOYEE LOADING (AJAX)
         +====================================================== */
        function loadEmployees(warehouse_id = 0) {
            $.ajax({
                url: "{{ route('payroll.getEmployeesByWarehouse') }}",
                type: "GET",
                data: { warehouse_id },
                success: function(data) {
                    let $select = $('#employeeMultiple');
                    if ($select.hasClass('select2-hidden-accessible')) {
                        $select.select2('destroy');
                    }
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
                    $select.select2({
                        placeholder: data.length > 0 ? 'Select Employees...' : 'No employees available',
                        width: '100%',
                        allowClear: true
                    });
                },
                error: function() { alert('Error loading employees!'); }
            });
        }

        loadEmployees(0);

        $('#warehouseSelect').on('change', function() {
            loadEmployees($(this).val() || 0);
        });

        $('#selectAllEmployees').click(function() {
            let $s = $('#employeeMultiple');
            if (!$s.prop('disabled')) {
                $s.find('option').prop('selected', true);
                $s.trigger('change');
            }
        });

        $('#deselectAllEmployees').click(function() {
            let $s = $('#employeeMultiple');
            if (!$s.prop('disabled')) {
                $s.find('option').prop('selected', false);
                $s.trigger('change');
            }
        });

        /* ========================================================
         |  VIEW BUTTON
         +====================================================== */
        $(document).on('click', '.view-btn', function() {
            let d = $(this).data();
            $('#viewEmployee').text(d.employee_name);
            $('#viewMonth').text(d.month);
            $('#viewGross').text(d.gross);
            $('#viewAllowances').text(d.allowances);
            $('#viewDeductions').text(d.deductions);
            $('#viewTotalPayable').text(d.amount);
            $('#viewPayingMethod').text(d.paying_method_text);
            $('#viewNote').text(d.note);
        });

        /* ========================================================
         |  EDIT BUTTON — POPULATE MODAL
         +====================================================== */
        $('.edit-btn').on('click', function() {
            let d = $(this).data();
            $('#editPayrollId').val(d.id);
            $('#editEmployee').val(d.employee).selectpicker('refresh');

            // Build "YYYY-MM" from year + month integers
            let m = String(d.month).padStart(2, '0');
            $('#editMonth').val(d.year + '-' + m);

            $('#editTemplate').val(d.template).selectpicker('refresh');
            $('#editTotalPayable').val(d.amount);
            $('#editDate').val(d.date);
            $('#editAccount').val(d.account).selectpicker('refresh');
            $('#editPayingMethod').val(d.paying_method).selectpicker('refresh');
            $('#editNote').val(d.note);
        });

        /* ========================================================
         |  FILTER
         +====================================================== */
        $('#filterEmployee, #filterMonth, #filterDate').on('change keyup', function() {
            let employee = $('#filterEmployee').val();
            let month    = $('#filterMonth').val();
            let date     = $('#filterDate').val();
            let table    = $('#payroll-table').DataTable();

            table.rows().every(function() {
                let row      = this.node();
                let rowData  = $(row).data();
                let show     = true;
                if (employee && rowData.employee_id != employee) show = false;
                if (month) {
                    let filterYM = month;
                    let rowYM   = rowData.year + '-' + String(rowData.month).padStart(2, '0');
                    if (rowYM != filterYM) show = false;
                }
                if (date && rowData.date != date) show = false;
                $(row).toggle(show);
            });
        });
    });

    /* ========================================================
     |  DATATABLE
     +====================================================== */
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
        'columnDefs': [
            { "orderable": false, 'targets': [0, 1, 6] },
            {
                'render': function(data, type, row, meta) {
                    if (type === 'display') {
                        data = '<div class="checkbox"><input type="checkbox" class="dt-checkboxes"><label></label></div>';
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
        'select': { style: 'multi', selector: 'td:first-child' },
        'lengthMenu': [[10, 25, 50, -1], [10, 25, 50, "All"]],
        dom: '<"row"lfB>rtip',
        buttons: [
            {
                extend: 'pdf', text: '<i title="export to pdf" class="fa fa-file-pdf-o"></i>',
                exportOptions: { columns: ':visible:Not(.not-exported)', rows: ':visible' },
                action: function(e, dt, button, config) {
                    datatable_sum(dt, true);
                    $.fn.dataTable.ext.buttons.pdfHtml5.action.call(this, e, dt, button, config);
                    datatable_sum(dt, false);
                },
                footer: true
            },
            {
                extend: 'excel', text: '<i title="export to excel" class="dripicons-document-new"></i>',
                exportOptions: { columns: ':visible:Not(.not-exported)', rows: ':visible' },
                action: function(e, dt, button, config) {
                    datatable_sum(dt, true);
                    $.fn.dataTable.ext.buttons.excelHtml5.action.call(this, e, dt, button, config);
                    datatable_sum(dt, false);
                },
                footer: true
            },
            {
                extend: 'csv', text: '<i title="export to csv" class="fa fa-file-text-o"></i>',
                exportOptions: { columns: ':visible:Not(.not-exported)', rows: ':visible' },
                action: function(e, dt, button, config) {
                    datatable_sum(dt, true);
                    $.fn.dataTable.ext.buttons.csvHtml5.action.call(this, e, dt, button, config);
                    datatable_sum(dt, false);
                },
                footer: true
            },
            {
                extend: 'print', text: '<i title="print" class="fa fa-print"></i>',
                exportOptions: { columns: ':visible:Not(.not-exported)', rows: ':visible' },
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
                            if (i) payroll_id[i - 1] = $(this).closest('tr').data('id');
                        });
                        if (payroll_id.length && confirm("Are you sure want to delete?")) {
                            $.ajax({
                                type: 'POST',
                                url: 'payroll/deletebyselection',
                                data: { payrollIdArray: payroll_id },
                                success: function(data) {
                                    $(':checkbox:checked').each(function(i) {
                                        if (i) dt.row($(this).closest('tr')).remove().draw(false);
                                    });
                                    alert(data);
                                }
                            });
                        } else if (!payroll_id.length) alert('No payroll is selected!');
                    } else alert('This feature is disable for demo!');
                }
            },
            {
                extend: 'colvis', text: '<i title="column visibility" class="fa fa-eye"></i>',
                columns: ':gt(0)'
            }
        ],
        drawCallback: function() { datatable_sum(this.api(), false); }
    });

    function datatable_sum(dt_selector, is_calling_first) {
        if (dt_selector.rows('.selected').any() && is_calling_first) {
            var rows = dt_selector.rows('.selected').indexes();
            $(dt_selector.column(5).footer()).html(
                dt_selector.cells(rows, 5, { page: 'current' }).data().sum()
                    .toFixed({{ $general_setting->decimal }})
            );
        } else {
            $(dt_selector.column(5).footer()).html(
                dt_selector.column(5, { page: 'current' }).data().sum()
                    .toFixed({{ $general_setting->decimal }})
            );
        }
    }

    /* ========================================================
     |  FETCH EMPLOYEE MONTHLY DATA (AJAX)
     +====================================================== */
    function fetchPayrollData() {
        let employee_id = $('#employee_id').val();
        let month = $('#monthSelect').val();
        if (employee_id && month) {
            $.ajax({
                url: "{{ route('payroll.monthlyData') }}",
                type: "GET",
                data: { employee_id, month },
                success: function(data) {
                    $('#salaryAmount').val(data.salary);
                    $('#previousTransactions').val(data.transactions);
                    $('#commissionAmount').val(data.commission);
                    calculateTotal();
                },
                error: function() { alert('Error loading payroll data!'); }
            });
        }
    }

    function calculateTotal() {
        let salary = parseFloat($('#salaryAmount').val()) || 0;
        let transactions = parseFloat($('#previousTransactions').val()) || 0;
        let commission = parseFloat($('#commissionAmount').val()) || 0;
        let total = (salary + commission) - transactions;
        $('#totalPayable').val(total.toFixed(2));
    }

    $('#employee_id, #monthSelect').on('change', fetchPayrollData);
    $('#salaryAmount, #previousTransactions, #commissionAmount').on('keyup change', calculateTotal);
</script>
@endpush