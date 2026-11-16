
@extends('backend.layout.main')

@section('content')

@if(session()->has('not_permitted'))
    <div class="alert alert-danger alert-dismissible text-center">
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
        {{ session()->get('not_permitted') }}
    </div>
@endif

<section class="forms">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card">

                    <div class="card-header d-flex align-items-center">
                        <h4>{{ trans('file.Update User') }}</h4>
                    </div>

                    <div class="card-body">

                        <p class="italic">
                            <small>
                                {{ trans('file.The field labels marked with * are required input fields') }}.
                            </small>
                        </p>

                        {!! Form::open([
                            'route' => ['user.update', $lims_user_data->id],
                            'method' => 'put',
                            'files' => true
                        ]) !!}

                        <div class="row">

                            <div class="col-md-6">

                                {{-- FIRST NAME --}}
                                <div class="form-group">
                                    <label><strong>First Name *</strong></label>

                                    <input type="text"
                                           name="first_name"
                                           value="{{ old('first_name', $lims_user_data->first_name) }}"
                                           required
                                           class="form-control">

                                    @if($errors->has('first_name'))
                                        <small class="text-danger">
                                            {{ $errors->first('first_name') }}
                                        </small>
                                    @endif
                                </div>

                                {{-- LAST NAME --}}
                                <div class="form-group">
                                    <label><strong>Last Name *</strong></label>

                                    <input type="text"
                                           name="last_name"
                                           value="{{ old('last_name', $lims_user_data->last_name) }}"
                                           required
                                           class="form-control">

                                    @if($errors->has('last_name'))
                                        <small class="text-danger">
                                            {{ $errors->first('last_name') }}
                                        </small>
                                    @endif
                                </div>

                                {{-- POSITION --}}
                                <div class="form-group">
                                    <label><strong>Position *</strong></label>

                                    <input type="text"
                                           name="position"
                                           value="{{ old('position', $lims_user_data->position) }}"
                                           required
                                           class="form-control"
                                           placeholder="e.g. Manager, Accountant, Cashier">

                                    @if($errors->has('position'))
                                        <small class="text-danger">
                                            {{ $errors->first('position') }}
                                        </small>
                                    @endif
                                </div>

                                {{-- USERNAME --}}
                                <div class="form-group">
                                    <label><strong>Username *</strong></label>

                                    <input type="text"
                                           name="name"
                                           value="{{ old('name', $lims_user_data->name) }}"
                                           required
                                           class="form-control"
                                           placeholder="Enter login username">

                                    @if($errors->has('name'))
                                        <small class="text-danger">
                                            {{ $errors->first('name') }}
                                        </small>
                                    @endif
                                </div>

                                {{-- CHANGE PASSWORD --}}
                                <div class="form-group">
                                    <label>
                                        <strong>{{ trans('file.Change Password') }}</strong>
                                    </label>

                                    <div class="input-group">
                                        <input type="password"
                                               name="password"
                                               class="form-control"
                                               placeholder="Leave blank to keep current password">

                                        <div class="input-group-append">
                                            <button id="genbutton"
                                                    type="button"
                                                    class="btn btn-default">
                                                {{ trans('file.Generate') }}
                                            </button>
                                        </div>
                                    </div>

                                    @if($errors->has('password'))
                                        <small class="text-danger">
                                            {{ $errors->first('password') }}
                                        </small>
                                    @endif
                                </div>

                                {{-- EMAIL --}}
                                <div class="form-group">
                                    <label><strong>{{ trans('file.Email') }} *</strong></label>

                                    <input type="email"
                                           name="email"
                                           value="{{ old('email', $lims_user_data->email) }}"
                                           placeholder="example@example.com"
                                           required
                                           class="form-control">

                                    @if($errors->has('email'))
                                        <small class="text-danger">
                                            {{ $errors->first('email') }}
                                        </small>
                                    @endif
                                </div>

                                {{-- PHONE NUMBER --}}
                                <div class="form-group">
                                    <label><strong>{{ trans('file.Phone Number') }} *</strong></label>

                                    <input type="text"
                                           name="phone"
                                           value="{{ old('phone', $lims_user_data->phone) }}"
                                           required
                                           class="form-control">
                                </div>

                            </div>

                            <div class="col-md-6">

                                {{-- COMPANY NAME --}}
                                <div class="form-group">
                                    <label>
                                        <strong>{{ trans('file.Company Name') }}</strong>
                                    </label>

                                    <input type="text"
                                           name="company_name"
                                           value="{{ old('company_name', $lims_user_data->company_name) }}"
                                           class="form-control">
                                </div>

                                {{-- ROLE --}}
                                <div class="form-group">
                                    <label><strong>{{ trans('file.Role') }} *</strong></label>

                                    <select name="roles[]"
                                            required
                                            class="selectpicker form-control"
                                            data-live-search="true"
                                            data-live-search-style="begins"
                                            title="Select Role..."
                                            multiple>

                                        @foreach($lims_role_list as $role)
                                            <option value="{{ $role->id }}"
                                                {{ in_array($role->id, old('roles', $lims_user_data->roles->pluck('id')->toArray())) ? 'selected' : '' }}>
                                                {{ $role->name }}
                                            </option>
                                        @endforeach

                                    </select>

                                    @if($errors->has('roles'))
                                        <small class="text-danger">
                                            {{ $errors->first('roles') }}
                                        </small>
                                    @endif
                                </div>

                                {{-- BILLER --}}
                                <div class="form-group" id="biller-id">

                                    <label>
                                        <strong>{{ trans('file.Biller') }} *</strong>
                                    </label>

                                    <input type="hidden"
                                           name="biller_id_hidden"
                                           value="{{ $lims_user_data->biller_id }}">

                                    <select name="biller_id"
                                            class="selectpicker form-control"
                                            data-live-search="true"
                                            data-live-search-style="begins"
                                            title="Select Biller...">

                                        @foreach($lims_biller_list as $biller)
                                            <option value="{{ $biller->id }}"
                                                {{ old('biller_id', $lims_user_data->biller_id) == $biller->id ? 'selected' : '' }}>
                                                {{ $biller->name }}
                                            </option>
                                        @endforeach

                                    </select>
                                </div>

                                {{-- WAREHOUSE --}}
                                <div class="form-group" id="warehouseId">

                                    <label>
                                        <strong>{{ trans('file.Warehouse') }} *</strong>
                                    </label>

                                    <input type="hidden"
                                           name="warehouse_id_hidden"
                                           value="{{ $lims_user_data->warehouse_id }}">

                                    <select name="warehouse_id"
                                            class="selectpicker form-control"
                                            data-live-search="true"
                                            data-live-search-style="begins"
                                            title="Select Warehouse...">

                                        @foreach($lims_warehouse_list as $warehouse)
                                            <option value="{{ $warehouse->id }}"
                                                {{ old('warehouse_id', $lims_user_data->warehouse_id) == $warehouse->id ? 'selected' : '' }}>
                                                {{ $warehouse->name }}
                                            </option>
                                        @endforeach

                                    </select>
                                </div>

                                {{-- ACTIVE STATUS --}}
                                <div class="form-group mt-3">

                                    <input class="mt-2"
                                           type="checkbox"
                                           name="is_active"
                                           value="1"
                                           {{ old('is_active', $lims_user_data->is_active) ? 'checked' : '' }}>

                                    <label class="mt-2">
                                        <strong>{{ trans('file.Active') }}</strong>
                                    </label>

                                </div>

                                {{-- SUBMIT --}}
                                <div class="form-group mt-3">
                                    <input type="submit"
                                           value="{{ trans('file.submit') }}"
                                           class="btn btn-primary">
                                </div>

                            </div>

                        </div>

                        {!! Form::close() !!}

                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

@endsection

@push('scripts')

<script type="text/javascript">

    // Expand Users sidebar menu
    $("ul#people").siblings('a').attr('aria-expanded', 'true');
    $("ul#people").addClass("show");

    // Hide Biller and Warehouse initially
    $('#biller-id').hide();
    $('#warehouseId').hide();

    // Initialize select pickers
    $('.selectpicker').selectpicker({
        style: 'btn-link'
    });

    // Generate password
    $('#genbutton').on("click", function() {

        $.get('../genpass', function(data) {
            $("input[name='password']").val(data);
        });

    });

    // Handle role-dependent fields
    function handleUserRoleFields() {

        var selectedRoles = $('select[name="roles[]"]').val() || [];

        var hasCustomerRole = selectedRoles.indexOf('5') !== -1;

        var hasBillerWarehouseRole = selectedRoles.some(function(value) {

            var intVal = parseInt(value, 10);

            return intVal > 2 && intVal !== 5;

        });

        if (hasBillerWarehouseRole) {

            $('#biller-id').show(300);
            $('#warehouseId').show(300);

            $('select[name="warehouse_id"]')
                .prop('required', true)
                .prop('disabled', false);

            $('select[name="biller_id"]')
                .prop('required', true)
                .prop('disabled', false);

        } else {

            $('#biller-id').hide(300);
            $('#warehouseId').hide(300);

            $('select[name="warehouse_id"]')
                .prop('required', false)
                .prop('disabled', true);

            $('select[name="biller_id"]')
                .prop('required', false)
                .prop('disabled', true);

        }

        $('.selectpicker').selectpicker('refresh');

    }

    // Listen for role changes
    $('select[name="roles[]"]').on(
        'changed.bs.select change',
        handleUserRoleFields
    );

    // Run when page loads
    handleUserRoleFields();

</script>

@endpush
