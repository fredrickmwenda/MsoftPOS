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

<section>
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-8">

                {{-- DETAILS CARD --}}
                <div class="card">
                    <div class="card-header d-flex align-items-center">
                        <h4>Variant Type Details</h4>
                        <div class="ml-auto">
                            <a href="{{ route('variant-types.edit', $variantType->id) }}" class="btn btn-warning btn-sm">
                                <i class="dripicons-document-edit"></i> Edit
                            </a>
                            <a href="{{ route('variant-types.index') }}" class="btn btn-default btn-sm">
                                <i class="dripicons-arrow-thin-left"></i> Back
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <table class="table table-bordered">
                            <tbody>
                                <tr>
                                    <th style="width: 30%">ID</th>
                                    <td>{{ $variantType->id }}</td>
                                </tr>
                                <tr>
                                    <th>Name</th>
                                    <td>{{ $variantType->name }}</td>
                                </tr>
                                <tr>
                                    <th>Order</th>
                                    <td>{{ $variantType->order }}</td>
                                </tr>
                                <tr>
                                    <th>Created At</th>
                                    <td>{{ $variantType->created_at ? $variantType->created_at->format('Y-m-d H:i:s') : '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Updated At</th>
                                    <td>{{ $variantType->updated_at ? $variantType->updated_at->format('Y-m-d H:i:s') : '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Product Variants Count</th>
                                    <td>
                                        @if(method_exists($variantType, 'productVariants'))
                                            {{ $variantType->productVariants ? $variantType->productVariants->count() : 0 }}
                                        @else
                                            0
                                        @endif
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- PRODUCT VARIANTS USING THIS TYPE --}}
                @if(method_exists($variantType, 'productVariants') && $variantType->productVariants && $variantType->productVariants->count() > 0)
                    <div class="card mt-3">
                        <div class="card-header d-flex align-items-center">
                            <h4>Product Variants Using This Type</h4>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Product Variant</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($variantType->productVariants as $key => $productVariant)
                                            <tr>
                                                <td>{{ $key + 1 }}</td>
                                                <td>{{ $productVariant->name ?? ('Variant #' . $productVariant->id) }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                @endif

            </div>

            {{-- QUICK CHANGE ORDER CARD --}}
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header d-flex align-items-center">
                        <h4>Quick Change Order</h4>
                    </div>
                    <div class="card-body">
                        <p class="italic">
                            <small>Update only the display order without editing the name.</small>
                        </p>

                        {{-- Adjust the route name below to match your routes/web.php --}}
                        {!! Form::open([
                            'route' => ['variant-types.changeOrder', $variantType->id],
                            'method' => 'patch'
                        ]) !!}
                            <div class="form-group">
                                <label><strong>New Order *</strong></label>
                                <input type="number"
                                       name="order"
                                       value="{{ old('order', $variantType->order) }}"
                                       required
                                       class="form-control">
                            </div>
                            <div class="form-group">
                                <input type="submit" value="Update Order" class="btn btn-primary">
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
    // $("ul#catalog #variant-type-list-menu").addClass("active");

</script>
@endpush
