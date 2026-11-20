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

@if($errors->any())
    <div class="alert alert-danger alert-dismissible text-center">
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
        <ul class="mb-0 list-unstyled">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<section class="forms">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header d-flex align-items-center">
                        <h4>Add Variant Type</h4>
                    </div>
                    <div class="card-body">
                        <p class="italic">
                            <small>The field labels marked with * are required input fields.</small>
                        </p>

                        {!! Form::open(['route' => 'variant-types.store', 'method' => 'post']) !!}
                            <div class="row">
                                <div class="col-md-6">

                                    <div class="form-group">
                                        <label><strong>Name *</strong></label>
                                        <input type="text"
                                               name="name"
                                               value="{{ old('name') }}"
                                               required
                                               class="form-control"
                                               placeholder="e.g. Color, Size, Material">

                                        @if($errors->has('name'))
                                            <small class="text-danger">
                                                {{ $errors->first('name') }}
                                            </small>
                                        @endif
                                    </div>

                                </div>
                                <div class="col-md-6">

                                    <div class="form-group">
                                        <label><strong>Order *</strong></label>
                                        <input type="number"
                                               name="order"
                                               value="{{ old('order') }}"
                                               required
                                               class="form-control"
                                               placeholder="Display order (must be unique)">

                                        @if($errors->has('order'))
                                            <small class="text-danger">
                                                {{ $errors->first('order') }}
                                            </small>
                                        @endif
                                    </div>

                                </div>
                            </div>

                            <div class="form-group">
                                <input type="submit" value="Submit" class="btn btn-primary">
                                <a href="{{ route('variant-types.index') }}" class="btn btn-default">Cancel</a>
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

    // Expand sidebar menu (if a variant-type menu group exists)
    // $("ul#catalog").siblings('a').attr('aria-expanded', 'true');
    // $("ul#catalog").addClass("show");
    // $("ul#catalog #variant-type-create-menu").addClass("active");

</script>
@endpush
