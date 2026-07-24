@extends('backend.layout.main') 

@section('content')
@if(session()->has('message'))
  <div class="alert alert-success alert-dismissible text-center"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>{{ session()->get('message') }}</div>
@endif
@if(session()->has('not_permitted'))
  <div class="alert alert-danger alert-dismissible text-center"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>{{ session()->get('not_permitted') }}</div>
@endif
@push('css')
<style>
.stock-header {
    background: linear-gradient(90deg,#2d7cb7,#3d97cb);
    color:white;
    padding:30px;
    border-radius:15px;
    display:flex;
    justify-content:space-between;
    align-items:center;
}
.page-header-modern {
    background: #1ec068;
    box-shadow: 0 8px 22px rgba(60, 141, 188, 0.22);
    border: 1px solid rgba(255, 255, 255, 0.2);
    border-radius: 15px;
    margin-top: 36px;
    margin-bottom: 24px;
    color: white;
}



.stock-header-content {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 20px;
}
.stock-header-title {
    font-size: 32px;
    font-weight: 700;
    margin: 0 0 8px 0;
    display: flex;
    align-items: center;
    gap: 12px;
}
.stock-header-subtitle {
    font-size: 16px;
    opacity: 0.9;
    margin: 0;
}

.stock-header-actions
 {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
}

.btn-header {
    padding: 12px 24px;
    border-radius: 10px;
    font-weight: 600;
    font-size: 14px;
    border: 2px solid rgba(255, 255, 255, 0.3);
    border-color: rgba(255, 255, 255, 0.42);
    background: rgba(255, 255, 255, 0.15);
    color: white;
    transition: all 0.3s;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    text-decoration: none;
    backdrop-filter: blur(10px);
}

.stock-header-left
 {
    flex: 1;
}

.warehouse-card {
    border:1px solid #dbe2ea;
    border-radius:15px;
    padding:25px;
    display:flex;
    gap:20px;
    cursor:pointer;
    transition:.3s;
}

.warehouse-card:hover {
    border-color:#2d7cb7;
}

.warehouse-card.active {
    border:2px solid #2d7cb7;
    background:#eef8ff;
}

.warehouse-icon {
    width:60px;
    height:60px;
    border-radius:15px;
    background:#e8f4fb;
    display:flex;
    justify-content:center;
    align-items:center;
    font-size:22px;
}

.info-box {
    background:white;
    padding:18px;
    border-radius:12px;
    box-shadow:0 2px 10px rgba(0,0,0,.08);
}

.variance-positive {
    color:green;
    font-weight:600;
}

.variance-negative {
    color:red;
    font-weight:600;
}
</style>
@endpush

<section>
<div class="stock-count-wrapper">


    <div class="page-header-modern">
        <div class="stock-header-content">
            <div class="stock-header-left">
                <h1 class="stock-header-title">
                    <i class="fa fa-clipboard-check"></i>
                    Physical Stock Count
                </h1>
                <p class="stock-header-subtitle">Count inventory and reconcile system stock</p>
            </div>
            <div class="stock-header-actions">
                <button onclick="window.print()" class="btn-header">
                    <i class="fa fa-print"></i> Print Report
                </button>
                <button onclick="refreshData()" class="btn-header">
                    <i class="fa fa-refresh"></i> Refresh
                </button>
            </div>
        </div>
    </div>

    {{-- Warehouse Selection --}}
    <div class="card shadow-sm mt-4">
        <div class="card-body">

            <h5 class="mb-4">
                SELECT WAREHOUSE
            </h5>

            <div class="row">

                @foreach($lims_warehouse_list as $warehouse)
                <div class="col-md-4 mb-3">

                    <div class="warehouse-card"
                         data-id="{{ $warehouse->id }}">

                        <div class="warehouse-icon">
                            <i class="fa fa-warehouse"></i>
                        </div>

                        <div>
                            <h5>{{ $warehouse->name }}</h5>
                            <small>
                                Inventory Location
                            </small>
                        </div>

                    </div>

                </div>
                @endforeach

            </div>

        </div>
    </div>

    {{-- Information Bar --}}
    <div class="row mt-4">
        <input type="hidden" id="warehouse_id">

        <div class="col-md-4">
            <div class="info-box">
                <strong>Location:</strong>
                <span id="selectedWarehouse">
                    Select Warehouse
                </span>
            </div>
        </div>

        <div class="col-md-4">
            <div class="info-box">
                <strong>Counter:</strong>
                {{ Auth::user()->name }}
            </div>
        </div>

        <div class="col-md-4">
            <div class="info-box">
                <strong>Date:</strong>
                {{ now()->format('d M Y') }}
            </div>
        </div>

    </div>

    {{-- Count Section --}}
    <div class="card mt-4">

        <div class="card-header">
            <h4>Physical Inventory Count</h4>
        </div>

        <div class="card-body">

            <div class="row mb-4">

                <div class="col-md-3">

                    <label>Product Category</label>

                    <select id="category"
                            class="form-control">
                        
                        <option value="">Choose Category</option>


                        <option value="all">All Categories</option>

                        @foreach($lims_category_list as $category)
                            <option value="{{ $category->id }}">
                                {{ $category->name }}
                            </option>
                        @endforeach

                    </select>

                </div>

                <div class="col-md-3">
                    <label>Batch No</label>

                    <select id="batch_no" class="form-control" disabled>
                        <option value="">Select Category First</option>
                    </select>
                </div>

                <div class="col-md-3">

                    <label>Search Product</label>

                    <input type="text"
                           class="form-control"
                           id="searchProduct">

                </div>

                <div class="col-md-3">

                    <label>&nbsp;</label>

                    <button class="btn btn-info btn-block"
                            id="loadProducts">

                        Load Products

                    </button>

                </div>

            </div>

            <table class="table table-bordered">

                <thead>

                <tr>
                    <th width="50">
                        <input type="checkbox" id="checkAll">
                    </th>
                    <th>ID</th>
                    <th>Product Name</th>
                    <th>Expiry Date</th>
                    <th>System Count</th>
                    <th>Physical Count</th>
                    <th>Variance</th>
                    <th>Save</th>
                </tr>


                </thead>

                <tbody id="productTable">

                </tbody>

            </table>

            <thead>


        </div>

    </div>

</div>
</section>





@endsection
@push('scripts')
<script>

    // Sidebar Active Menu
    $("ul#product").siblings('a').attr('aria-expanded', 'true');
    $("ul#product").addClass("show");
    $("ul#product #stock-count-menu").addClass("active");

    let selectedWarehouse = null;

    $(document).ready(function () {
        let firstWarehouse = $('.warehouse-card').first();

        if (firstWarehouse.length) {
            firstWarehouse.trigger('click');
        }
    });

    // Warehouse Selection
    // $(document).on('click', '.warehouse-card', function () {

    //     $('.warehouse-card').removeClass('active');

    //     $(this).addClass('active');

    //     selectedWarehouse = $(this).data('id');

    //     $('#warehouse_id').val(selectedWarehouse);

    //     $('#selectedWarehouse').text(
    //         $(this).find('h5').text()
    //     );

    //     $('#category').val('all');

    //     loadProducts(
    //         selectedWarehouse,
    //         'all'
    //     );
    // });

    $(document).on('click', '.warehouse-card', function () {

        $('.warehouse-card').removeClass('active');
        $(this).addClass('active');

        selectedWarehouse = $(this).data('id');

        $('#warehouse_id').val(selectedWarehouse);

        $('#selectedWarehouse').text(
            $(this).find('h5').text()
        );

        // Reload products if category already selected
        let categoryId = $('#category').val();

        if (categoryId !== '') {
            loadProducts(selectedWarehouse, categoryId);
        }
    });

    
   $(document).on('change', '#category', function () {

    let categoryId = $(this).val();

    $('#batch_no')
        .prop('disabled', true)
        .html('<option value="">Loading...</option>');

    $('#productTable').html('');

    if (!selectedWarehouse) {
        alert('Please select a warehouse first');
        return;
    }

    if (categoryId === '') {
        $('#batch_no')
            .html('<option value="">Select Category First</option>')
            .prop('disabled', true);

        $('#productTable').html('');
        return;
    }

    $.ajax({
        url: "{{ route('stock-count.batches') }}",
        type: "GET",
        data: {
            warehouse_id: selectedWarehouse,
            category_id: categoryId
        },

        success: function (response) {

            console.log(response);

            let batches = response.data ?? response;

            let options =
                '<option value="">All Batches</option>';

            batches.forEach(function (batch) {
                options += `
                    <option value="${batch.id}">
                        ${batch.batch_no}
                    </option>
                `;
            });

            $('#batch_no')
                .html(options)
                .prop('disabled', false);

            // LOAD PRODUCTS AFTER BATCHES HAVE LOADED
            loadProducts(
                selectedWarehouse,
                categoryId,
                ''
            );
        },

        error: function (xhr) {

            console.log(xhr.responseText);

            $('#batch_no')
                .html('<option value="">No Batches Found</option>')
                .prop('disabled', true);

            // Even if there are no batches, still load products
            loadProducts(
                selectedWarehouse,
                categoryId,
                ''
            );
        }
    });
});



    $('#batch_no').on('change', function () {
        let categoryId = $('#category').val();
        let batchId = $(this).val();

        loadProducts(
            selectedWarehouse,
            categoryId,
            batchId
        );
    });

  

    // Manual Load Button
    $('#loadProducts').on('click', function () {

        let categoryId = $('#category').val();
        let batchId = $('#batch_no').val();

        if (!selectedWarehouse) {
            alert('Please select a warehouse first');
            return;
        }

        if (!categoryId) {
            alert('Please select a category');
            return;
        }

        loadProducts(
            selectedWarehouse,
            categoryId,
            batchId
        );
    });

    // Load Products Function
    function loadProducts( warehouseId,categoryId,batchId = '') {

        $('#productTable').html(`
            <tr>
                <td colspan="8" class="text-center">
                    Loading products...
                </td>
            </tr>
        `);

        $.ajax({
            url: "{{ route('stock-count.products') }}",
            type: "GET",
            data: {
                warehouse_id: warehouseId,
                category_id: categoryId,
                batch_id: batchId
            },

            success: function (products) {

                let html = '';

                if (products.length === 0) {
                    html = `
                        <tr>
                            <td colspan="8" class="text-center">
                                No products found
                            </td>
                        </tr>
                    `;

                    $('#productTable').html(html);
                    return;
                }

                products.forEach((product) => {

                    let expiry =
                        product.expiry_date
                            ? product.expiry_date
                            : 'N/A';

                    let systemQty =
                        parseFloat(
                            product.system_qty
                        ).toFixed(2);

                    html += `
                    <tr>

                        <td>
                            <input
                                type="checkbox"
                                class="product-check"
                                value="${product.id}">
                        </td>

                        <td>${product.id}</td>

                        <td>
                            <strong>${product.name}</strong>
                            <br>
                            <small>${product.code}</small>
                            ${product.batch_no
                                ? `<br><span class="badge badge-info">${product.batch_no}</span>`
                                : ''
                            }
                        </td>

                        <td>${expiry}</td>

                        <td>${systemQty}</td>

                        <td>
                            <input
                                type="number"
                                step="0.01"
                                min="0"
                                class="form-control physical-count"
                                data-system="${systemQty}"
                                data-product="${product.id}"
                                data-batch="${product.batch_id ?? ''}"
                                value="0.00">
                        </td>

                        <td class="variance text-danger">
                            -${systemQty}
                        </td>

                        <td>
                            <button
                                class="btn btn-success save-row"
                                data-product="${product.id}"
                                data-batch="${product.batch_id ?? ''}">
                                <i class="fa fa-save"></i>
                            </button>
                        </td>

                    </tr>`;
                });

                $('#productTable').html(html);
            },

            error: function () {

                $('#productTable').html(`
                    <tr>
                        <td colspan="8"
                            class="text-center text-danger">
                            Failed to load products
                    </td>
                    </tr>
                `);
            }
        });
    }
    // function loadProducts(warehouseId, categoryId) {

    //     $('#productTable').html(`
    //         <tr>
    //             <td colspan="7" class="text-center">
    //                 Loading products...
    //             </td>
    //         </tr>
    //     `);

    //     $.ajax({
    //         url: "{{ route('stock-count.products') }}",
    //         type: "GET",
    //         data: {
    //             warehouse_id: warehouseId,
    //             category_id: categoryId
    //         },

    //         success: function (products) {

    //             let html = '';

    //             if(products.length === 0){
    //                 html = `
    //                     <tr>
    //                         <td colspan="7" class="text-center">
    //                             No products found
    //                         </td>
    //                     </tr>
    //                 `;

    //                 $('#productTable').html(html);
    //                 return;
    //             }

    //             // Inside loadProducts success callback, replace the row template:
    //             products.forEach((product) => {
    //                 let expiry = product.expiry_date ? product.expiry_date : 'N/A';
    //                 let systemQty = parseFloat(product.system_qty).toFixed(2);

    //                 html += `
    //                 <tr>
    //                     <td>
    //                         <input type="checkbox" class="product-check" value="${product.id}">
    //                     </td>
    //                     <td>${product.id}</td>
    //                     <td>
    //                         <strong>${product.name}</strong><br>
    //                         <small>${product.code}</small>
    //                     </td>
    //                     <td>${expiry}</td>
    //                     <td>${systemQty}</td>
    //                     <td>
    //                         <input type="number" 
    //                             class="form-control physical-count" 
    //                             data-system="${systemQty}" 
    //                             data-product="${product.id}" 
    //                             value="0.00">
    //                     </td>
    //                     <td class="variance">${systemQty}</td>   <!-- variance = system + 0 -->
    //                     <td>
    //                         <button class="btn btn-success save-row" data-product="${product.id}">
    //                             <i class="fa fa-save"></i>
    //                         </button>
    //                     </td>
    //                 </tr>`;
    //             });

    //             $('#productTable').html(html);
    //         },

    //         error: function () {

    //             $('#productTable').html(`
    //                 <tr>
    //                     <td colspan="8" class="text-center text-danger">
    //                         Failed to load products
    //                     </td>
    //                 </tr>
    //             `);
    //         }
    //     });
    // }


    // Live Variance Calculation
    $(document).on('keyup change', '.physical-count', function () {

        let row = $(this).closest('tr');

        let systemQty = parseFloat(
            $(this).data('system')
        ) || 0;

        let physicalQty = parseFloat(
            $(this).val()
        ) || 0;

        let variance = physicalQty - systemQty;

        let varianceCell = row.find('.variance');

        varianceCell.removeClass(
            'text-success text-danger text-secondary'
        );

        if (variance > 0) {
            varianceCell
                .addClass('text-success')
                .html('+' + variance);
        }
        else if (variance < 0) {
            varianceCell
                .addClass('text-danger')
                .html(variance);
        }
        else {
            varianceCell
                .addClass('text-secondary')
                .html('0');
        }
    });

    // Product Search
    $('#searchProduct').on('keyup', function () {

        let value = $(this)
            .val()
            .toLowerCase();

        $('#productTable tr').filter(function () {

            $(this).toggle(
                $(this)
                    .text()
                    .toLowerCase()
                    .indexOf(value) > -1
            );

        });

    });

    // Save Single Row
    $(document).on('click', '.save-row', function () {

        let button = $(this);

        let row = button.closest('tr');

        let productId = button.data('product');

        let physicalQty = row.find('.physical-count').val();

        let variance = row.find('.variance').text().trim();



        console.log(variance);

        $.ajax({

            url: "{{ route('stock-count.save') }}",

            type: "POST",

            data: {

                _token: "{{ csrf_token() }}",

                warehouse_id: selectedWarehouse,

                product_id: productId,

                physical_qty: physicalQty,

                variance: variance

            },

            beforeSend: function () {

                button.prop('disabled', true);

                button.html(
                    '<i class="fa fa-spinner fa-spin"></i>'
                );
            },

            success: function (response) {

                row.find('.variance')
                .removeClass('text-danger text-success')
                .addClass('text-success')
                .html('Saved');

                row.find('.physical-count')
                .attr('data-system', physicalQty);

                button.html(
                    '<i class="fa fa-check"></i>'
                );

                setTimeout(function () {

                    button.html(
                        '<i class="fa fa-save"></i>'
                    );

                    button.prop('disabled', false);

                }, 1500);

            },

            error: function (xhr) {

                alert(
                    xhr.responseJSON?.message ??
                    'Failed to save stock'
                );

                button.html(
                    '<i class="fa fa-save"></i>'
                );

                button.prop('disabled', false);
            }
        });

    });

    $(document).on('change', '#checkAll', function () {
        $('.product-check').prop(
            'checked',
            $(this).prop('checked')
        );
    });

</script>
@endpush

