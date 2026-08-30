/*******************************
 * 1. IMPROVE MENU ACTIVATION
 ******************************/
// Combine selectors instead of running jQuery 3 times
$("ul#sale")
    .addClass("show")
    .siblings("a")
    .attr("aria-expanded", "true");
$("#sale-pos-menu").addClass("active");

/*******************************
 * 2. GLOBAL AJAX CSRF SETUP
 ******************************/
$.ajaxSetup({
    headers: {
        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content")
    }
});



/*******************************
 * 4. CLEAR LOCALSTORAGE ON HOLD
 ******************************/
// @if (session()->get('message') == 'Sale successfully added to Hold')
//     localStorage.clear(); 
// @endif


 * 5. LOAD POS SETTINGS
 ******************************/
const {
    without_stock,
    discount_value,
    alert_product,
    currency,
    deposit,
    points,
    reward_point_setting,
    keyboard_active,
    role_id,
    warehouse_id,
    biller_id,
    coupon_list,
    pos_settings,
    stripe_public_key,
    stripe_secret_key,
    product_row_number
} = window.POsConfig;

// Prevent duplicated currency variable
$("#currency").val(currency.id);

/*******************************
 * 6. HANDLE CURRENCY CHANGE
 ******************************/
$("#currency").change(function () {
    const selected = $(this).find(":selected");
    const rate = selected.data("rate");

    $("#exchange_rate").val(rate);
    window.PosConfig.currency.exchange_rate = rate;

    // Recalculate all rows once    
    $("table.order-list tbody .qty").each(function (index) {
        rowindex = index;
        currencyChange = true;
        checkDiscount($(this).val(), true);
        couponDiscount();
    });
});

/*******************************
 * 7. LOCAL STORAGE RESTORATION
 ******************************/

// DRY helper: return "" if no value in storage
function getSavedValue(id) {
    return localStorage.getItem(id) || "";
}

// Use a common function to restore inputs
function restoreField(id) {
    $("#" + id).val(getSavedValue(id));
}

["reference-no", "order-discount", "order-discount-val", "order-discount-type",
 "order-tax-rate-select", "shipping-cost-val"].forEach(restoreField);

if (localStorage.getItem("tbody-id")) {
    $("#tbody-id").html(localStorage.getItem("tbody-id"));
}

function saveValue(e) {
    localStorage.setItem(e.id, e.value);
}

/****************************************
 * 8. RESTORE TABLE DATA FROM LOCALSTORAGE
 ****************************************/

// Use one object to group related arrays for cleaner management
const ls = {
    qty: getSavedValue("localStorageQty").split(","),
    productId: getSavedValue("localStorageProductId").split(","),
    discount: getSavedValue("localStorageProductDiscount").split(","),
    taxRate: getSavedValue("localStorageTaxRate").split(",")
    // ... repeat for others
};

if (ls.qty[0] !== "") {

    ls.qty.forEach((qtyValue, i) => {
        const row = $(`table.order-list tbody tr:nth-child(${i + 1})`);

        // Reduce repeated DOM calls by caching "row"
        row.find(".qty").val(ls.qty[i]);
        row.find(".discount-value").val(ls.discount[i]);
        row.find(".tax-rate").val(ls.taxRate[i]);

        // Load more fields (similar pattern)
        // ...

        // Push product price + discount (more readable formula)
        const price = parseFloat(row.find(".product_price").val());
        const qty = parseFloat(ls.qty[i]);

        product_price.push(price);
        product_discount.push((ls.discount[i] / qty).toFixed(discount_value));

        // Push tax info
        tax_rate.push(parseFloat(row.find(".tax-rate").val()));
        tax_name.push(row.find(".tax-name").val());
        tax_method.push(row.find(".tax-method").val());

        // Restore units
        const unitParts = row.find(".sale-unit").val().split(",");
        row.find(".sale-unit").val(unitParts[0]);

        calculateTotal();
    });
}

/*******************************
 * 9. FILTER TOGGLE BUTTON
 ******************************/
document.getElementById("toggle-filters").addEventListener("click", function () {
    console.log("Toggle filter button clicked");
    const filterCol = document.getElementById("filter-column");
    const mainCol = document.getElementById("main-column");

    // Toggle using a cleaner approach
    const isHidden = filterCol.classList.contains("d-none");

    filterCol.classList.toggle("d-none");
    mainCol.classList.toggle("col-md-10", !isHidden);
    mainCol.classList.toggle("col-lg-10", !isHidden);
    mainCol.classList.toggle("col-md-5", isHidden);
    mainCol.classList.toggle("col-lg-5", isHidden);
});



//selectpicker select box
$('.selectpicker').selectpicker({
    style: 'btn-link',
});

if (window.PosConfig.keyboard_active == 1) {

    // COMMON CSS CONFIG
    const keyboardCss = {
        container: 'center-block dropdown-menu',
        buttonDefault: 'btn btn-default',
        buttonHover: 'btn-primary',
        buttonAction: 'active',
        buttonDisabled: 'disabled'
    };

    // COMMON change handler
    const syncInput = function (e, keyboard) {
        keyboard.$el.val(keyboard.$preview.val());
        keyboard.$el.trigger('propertychange');
    };

    // COMMON validation
    const validateSearch = function () {
        const customer_id = $('#customer_id').val();
        const warehouse_id = $('select[name="warehouse_id"]').val();
        const temp = $('#lims_productcodeSearch').val();

        if (!customer_id) {
            $('#lims_productcodeSearch').val(temp.slice(0, -1));
            alert('Please select Customer!');
            return false;
        }
        if (!warehouse_id) {
            $('#lims_productcodeSearch').val(temp.slice(0, -1));
            alert('Please select Warehouse!');
            return false;
        }
        return true;
    };

    // NUMERIC KEYBOARD
    $("input.numkey:text").keyboard({
        usePreview: false,
        layout: 'custom',
        display: {
            'accept': '&#10004;',
            'cancel': '&#10006;'
        },
        customLayout: {
            'normal': ['1 2 3', '4 5 6', '7 8 9', '0 {dec} {bksp}', '{clear} {cancel} {accept}']
        },
        restrictInput: true,
        preventPaste: true,
        autoAccept: true,
        css: keyboardCss
    });

    // TEXT INPUT KEYBOARD
    $('input[type="text"]').keyboard({
        usePreview: false,
        autoAccept: true,
        autoAcceptOnEsc: true,
        css: keyboardCss,
        change: syncInput
    });

    // TEXTAREA KEYBOARD
    $('textarea').keyboard({
        usePreview: false,
        autoAccept: true,
        autoAcceptOnEsc: true,
        css: keyboardCss,
        change: syncInput
    });

    // PRODUCT SEARCH KEYBOARD + AUTOCOMPLETE
    $('#lims_productcodeSearch')
        .keyboard()
        .autocomplete()
        .addAutocomplete({
            position: {
                of: '#lims_productcodeSearch',
                my: 'top+18px',
                at: 'center',
                collision: 'flip'
            }
        })
        .bind('keyboardChange', validateSearch);

} else {

    // NO KEYBOARD MODE → normal input validation
    $('#lims_productcodeSearch').on('input', function () {
        const customer_id = $('#customer_id').val();
        const warehouse_id = $('#warehouse_id').val();
        const temp = $(this).val();

        if (!customer_id || !warehouse_id) {
            $(this).val(temp.slice(0, -1));
            alert(!customer_id ? 'Please select Customer!' : 'Please select Warehouse!');
        }
    });

}


$('.customer-submit-btn').on("click", function (e) {
    e.preventDefault();
    const toastMagic = new ToastMagic();

    $.ajax({
        type: 'POST',
        url: '{{ route('customer.storeCustomer') }}',
        data: $("#customer-form").serialize(),
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function (response) {
            // Success message
            toastMagic.success('Customer added successfully!');

            // Append new customer to select
            let key = response['id'];
            let value = response['name'] + ' [' + response['phone_number'] + ']';
            $('select[name="customer_id"]').append('<option value="' + key + '">' + value + '</option>');
            $('select[name="customer_id"]').val(key);
            $('.selectpicker').selectpicker('refresh');

            // Hide modal
            $("#addCustomer").modal('hide');

            // Optionally reset the form
            // $('#customer-form')[0].reset();
        },
        error: function (xhr) {
            if (xhr.status === 422) {
                // Validation error
                const errors = xhr.responseJSON.errors;
                $.each(errors, function (field, messages) {
                    toastMagic.error(messages[0]); // Show the first validation message per field
                });
            } else {
                // Other errors (e.g. 500)
                toastMagic.error('An unexpected error occurred. Please try again.');
                console.error('Error details:', xhr.responseText);
            }
        }
    });
});


$("li#notification-icon").on("click", function (argument) {
    $.get('notifications/mark-as-read', function (data) {
        $("span.notification-number").text(window.PosConfig.alert_product);
    });
});

$("#register-details-btn").on("click", function (e) {
    e.preventDefault();
    $.ajax({
        url: 'cash-register/showDetails/' + window.PosConfig.warehouse_id,
        type: "GET",
        success: function (data) {
            $('#register-details-modal #cash_in_hand').text(data['cash_in_hand']);
            $('#register-details-modal #total_sale_amount').text(data['total_sale_amount']);
            $('#register-details-modal #total_payment').text(data['total_payment']);
            $('#register-details-modal #cash_payment').text(data['cash_payment']);
            $('#register-details-modal #credit_card_payment').text(data['credit_card_payment']);
            $('#register-details-modal #cheque_payment').text(data['cheque_payment']);
            $('#register-details-modal #gift_card_payment').text(data['gift_card_payment']);
            $('#register-details-modal #deposit_payment').text(data['deposit_payment']);
            $('#register-details-modal #paypal_payment').text(data['paypal_payment']);
            $('#register-details-modal #total_sale_return').text(data['total_sale_return']);
            $('#register-details-modal #total_expense').text(data['total_expense']);
            $('#register-details-modal #total_cash').text(data['total_cash']);
            $('#register-details-modal input[name=cash_register_id]').val(data['id']);
        }
    });
    $('#register-details-modal').modal('show');
});

$("#today-sale-btn").on("click", function (e) {
    e.preventDefault();
    $.ajax({
        url: 'sales/today-sale/',
        type: "GET",
        success: function (data) {
            $('#today-sale-modal .total_sale_amount').text(data['total_sale_amount']);
            $('#today-sale-modal .total_payment').text(data['total_payment']);
            $('#today-sale-modal .cash_payment').text(data['cash_payment']);
            $('#today-sale-modal .credit_card_payment').text(data['credit_card_payment']);
            $('#today-sale-modal .cheque_payment').text(data['cheque_payment']);
            $('#today-sale-modal .gift_card_payment').text(data['gift_card_payment']);
            $('#today-sale-modal .deposit_payment').text(data['deposit_payment']);
            $('#today-sale-modal .paypal_payment').text(data['paypal_payment']);
            $('#today-sale-modal .total_sale_return').text(data['total_sale_return']);
            $('#today-sale-modal .total_expense').text(data['total_expense']);
            $('#today-sale-modal .total_cash').text(data['total_cash']);
            //   changes by yogesh
            $('#today-sale-modal .mobile_payment').text(data['mobile_payment']);
            //   changes end by yogesh
        }
    });
    $('#today-sale-modal').modal('show');
});

$("#today-profit-btn").on("click", function (e) {
    e.preventDefault();
    calculateTodayProfit(0);
});

$("#today-profit-modal select[name=warehouseId]").on("change", function () {
    calculateTodayProfit($(this).val());
});
 const use_warehouse = window.PosConfig.warehouse_id;
function calculateTodayProfit(use_warehouse) {
    $.ajax({
        url: 'sales/today-profit/' + use_warehouse,
        type: "GET",
        success: function (data) {
            $('#today-profit-modal .product_revenue').text(data['product_revenue']);
            $('#today-profit-modal .product_cost').text(data['product_cost']);
            $('#today-profit-modal .expense_amount').text(data['expense_amount']);
            $('#today-profit-modal .profit').text(data['profit']);
        }
    });
    $('#today-profit-modal').modal('show');
}

if (window.PosConfig.role_id > 2) {
    $('#biller_id').addClass('d-none');
    $('#warehouse_id').addClass('d-none');
    $('select[name=warehouse_id]').val(use_warehouse);
    $('select[name=biller_id]').val(biller_id);
    isCashRegisterAvailable(use_warehouse);
}
else {
    if (getSavedValue("warehouse_id")) {
        warehouse_id = getSavedValue("warehouse_id");
    }
    else {
        warehouse_id = $("input[name='warehouse_id_hidden']").val();
    }

    if (getSavedValue("biller_id")) {
        biller_id = getSavedValue("biller_id");
    }
    else {
        biller_id = $("input[name='biller_id_hidden']").val();
    }
    $('select[name=warehouse_id]').val(warehouse_id);
    $('select[name=biller_id]').val(biller_id);
}

if (getSavedValue("biller_id")) {
    $('select[name=customer_id]').val(getSavedValue("customer_id"));
}
else {
    $('select[name=customer_id]').val($("input[name='customer_id_hidden']").val());
}

$('.selectpicker').selectpicker('refresh');

var id = $("#customer_id").val();
$.get('sales/getcustomergroup/' + id, function (data) {
    customer_group_rate = (data / 100);
});

var id = $("#warehouse_id").val();
$.get('sales/getproduct/' + id, function (data) {
    lims_product_array = [];
    product_code = data[0];
    product_name = data[1];
    product_qty = data[2];
    product_type = data[3];
    product_id = data[4];
    product_list = data[5];
    qty_list = data[6];
    product_warehouse_price = data[7];
    batch_no = data[8];
    product_batch_id = data[9];
    is_embeded = data[11];
    product_shelf = data[12];
    $.each(product_code, function (index) {
        if (is_embeded[index])
            lims_product_array.push(
                'Code: ' + product_code[index] +
                ' | Name: ' + product_name[index] +
                ' | Price: ' + product_warehouse_price[index] +
                ' | Qty: ' + product_qty[index] +
                ' | Shelf: ' + product_shelf[index] +
                ' | Embeded: ' + is_embeded[index]
            );

        // lims_product_array.push(product_code[index] + ' (' + product_name[index] + ')' + ' (' + product_qty[index] + ')' + ' (' + product_warehouse_price[index] + ')'+ ' (' + product_shelf[index] + ')|'+ is_embeded[index]);
        else
            lims_product_array.push(
                'Code: ' + product_code[index] +
                ' | Name: ' + product_name[index] +
                ' | Price: ' + product_warehouse_price[index] +
                ' | Qty: ' + product_qty[index] +
                ' | Shelf: ' + product_shelf[index]
            );
    });
});

isCashRegisterAvailable(id);

function isCashRegisterAvailable(use_warehouse) {
    $.ajax({
        url: 'cash-register/check-availability/' + use_warehouse,
        type: "GET",
        success: function (data) {
            if (data == 'false') {
                $("#register-details-btn").addClass('d-none');
                $('#cash-register-modal select[name=warehouse_id]').val(use_warehouse);

                if (window.PosConfig.role_id <= 2)
                    $("#cash-register-modal .warehouse-section").removeClass('d-none');
                else
                    $("#cash-register-modal .warehouse-section").addClass('d-none');

                $('.selectpicker').selectpicker('refresh');
                $("#cash-register-modal").modal('show');
            }
            else
                $("#register-details-btn").removeClass('d-none');
        }
    });
}


$(document).ready(function () {

    // Remove payment row
    $(document).on("click", ".remove-payment", function () {
        $(this).closest(".payment-row").remove();
        // refreshPaymentMethodOptions();
        calculateTotal();
        // Get updated values
        let payingAmount = parseFloat($('input[name="paying_amount"]').val()) || 0;
        let mainPaid = parseFloat($('input[name="paid_amount"]').val()) || 0;
        let totalSplit = getTotalSplitAmount();
        let totalPaid = mainPaid + totalSplit;

        // Update change
        change(payingAmount, totalPaid);
    });

    function refreshPaymentMethodOptions() {
        let usedMethods = getSelectedPaymentMethods();

        let options = [
            { value: 1, label: 'Cash' },
            { value: 2, label: 'Gift Card' },
            { value: 3, label: 'Credit Card' },
            { value: 4, label: 'Cheque' },
            { value: 5, label: 'Paypal' },
            { value: 6, label: 'Deposit' },
            { value: 7, label: 'Points' },
            { value: 8, label: 'Mobile Money' }
        ];

        $(".payment-method-select").each(function () {
            let currentVal = $(this).val();
            let selectOptions = options.filter(opt => {
                return !usedMethods.includes(opt.value) || opt.value == currentVal;
            }).map(opt => {
                let selected = opt.value == currentVal ? 'selected' : '';
                return `<option value="${opt.value}" ${selected}>${opt.label}</option>`;
            }).join('');

            $(this).html(selectOptions).selectpicker('refresh');
        });
    }

});
$("#print-btn").on("click", function () {
    var divToPrint = document.getElementById('sale-details');
    var newWin = window.open('', 'Print-Window');
    newWin.document.open();
    newWin.document.write('<link rel="stylesheet" href="<?php echo asset('vendor / bootstrap / css / bootstrap.min.css') ?>" type="text/css"><style type="text/css">@media print {.modal-dialog { max-width: 1000px;} }</style><body onload="window.print()">' + divToPrint.innerHTML + '</body>');
    newWin.document.close();
    setTimeout(function () { newWin.close(); }, 10);
});

$('body').on('click', function (e) {
    $('.filter-window').hide('slide', { direction: 'right' }, 'fast');
});

$('#category-filter').on('click', function (e) {
    e.stopPropagation();
    $('.filter-window').show('slide', { direction: 'right' }, 'fast');
    $('.category').show();
    $('.brand').hide();
});

$('.category-img').on('click', function () {
    var category_id = $(this).data('category');
    var brand_id = 0;

    $(".table-container").children().remove();
    $.get('sales/getproduct/' + category_id + '/' + brand_id, function (data) {
        populateProduct(data);
    });
});

$('#brand-filter').on('click', function (e) {
    e.stopPropagation();
    $('.filter-window').show('slide', { direction: 'right' }, 'fast');
    $('.brand').show();
    $('.category').hide();
});

$('.brand-img').on('click', function () {
    var brand_id = $(this).data('brand');
    var category_id = 0;

    $(".table-container").children().remove();
    $.get('sales/getproduct/' + category_id + '/' + brand_id, function (data) {
        populateProduct(data);
    });
});

$('#featured-filter').on('click', function () {
    $(".table-container").children().remove();
    $.get('sales/getfeatured', function (data) {
        populateProduct(data);
    });
});

function populateProduct(data) {
    var tableData = '<table id="product-table" class="table no-shadow product-list"> <thead class="d-none"> <tr> <th></th> <th></th> <th></th> <th></th> <th></th> </tr></thead> <tbody><tr>';

    if (Object.keys(data).length != 0) {
        $.each(data['name'], function (index) {
            var product_info = data['code'][index] + ' (' + data['name'][index] + ')';

            // Determine the correct image path
            var imagePath = getProductImagePathAsync(data['image'][index]);

            if (index % 5 == 0 && index != 0)
                tableData += '</tr><tr><td class="product-img sound-btn" title="' + data['name'][index] + '" data-product = "' + product_info + '"><img src="' + imagePath + '" width="100%" /><p>' + data['name'][index] + '</p><span>' + data['code'][index] + '</span></td>';
            else
                tableData += '<td class="product-img sound-btn" title="' + data['name'][index] + '" data-product = "' + product_info + '"><img src="' + imagePath + '" width="100%" /><p>' + data['name'][index] + '</p><span>' + data['code'][index] + '</span></td>';
        });

        if (data['name'].length % 5) {
            var number = 5 - (data['name'].length % 5);
            while (number > 0) {
                tableData += '<td style="border:none;"></td>';
                number--;
            }
        }

        tableData += '</tr></tbody></table>';
        $(".table-container").html(tableData);
        $('#product-table').DataTable({
            "order": [[0, 'desc']],
            'pageLength': window.PosConfig.product_row_number,
            'language': {
                'paginate': {
                    'previous': '<i class="fa fa-angle-left"></i>',
                    'next': '<i class="fa fa-angle-right"></i>'
                }
            },
            dom: 'tp'
        });
        $('table.product-list').hide();
        $('table.product-list').show(500);
    }
    else {
        tableData += '<td class="text-center">No data avaialable</td></tr></tbody></table>'
        $(".table-container").html(tableData);
    }
}

/**
 * Helper function to determine the correct image path
 * Priority: small > medium > product > default fallback
 */
// function getProductImagePath(imageName) {
//     if (!imageName) {
//         return 'images/product/zummXD2dvAtI.png'; // Default fallback
//     }

//     // Try small folder first
//     return 'images/product/small/' + imageName;
//     // Note: In a real implementation, you'd want to check if the file exists
//     // For now, we'll assume small exists, or you can use the AJAX approach below
// }

/**
 * Alternative: Async approach using AJAX to check file existence
 * Uncomment if you want to check file existence before displaying
 */
function getProductImagePathAsync(imageName, callback) {
    if (!imageName) {
        callback('images/product/zummXD2dvAtI.png');
        return;
    }
    console.log("Checking image paths for:", imageName);

    var paths = [
        'images/product/small/' + imageName,
        'images/product/medium/' + imageName,
        'images/product/' + imageName,
        'images/product/zummXD2dvAtI.png' // fallback
    ];

    function checkPath(index) {
        if (index >= paths.length) {
            callback(paths[paths.length - 1]); // Use fallback
            return;
        }

        var img = new Image();
        img.onload = function () {
            callback(paths[index]); // Image found
        };
        img.onerror = function () {
            checkPath(index + 1); // Try next path
        };
        img.src = paths[index];
    }

    checkPath(0);
}

$('select[name="customer_id"]').on('change', function () {
    saveValue(this);
    var id = $(this).val();
    $.get('sales/getcustomergroup/' + id, function (data) {
        customer_group_rate = (data / 100);
    });
});

$('select[name="biller_id"]').on('change', function () {
    saveValue(this);
});

$('select[name="warehouse_id"]').on('change', function () {
    saveValue(this);
    warehouse_id = $(this).val();
    $.get('sales/getproduct/' + warehouse_id, function (data) {
        lims_product_array = [];
        product_code = data[0];
        product_name = data[1];
        product_qty = data[2];
        product_type = data[3];
        product_id = data[4];
        product_list = data[5];
        qty_list = data[6];
        product_warehouse_price = data[7];
        batch_no = data[8];
        product_batch_id = data[9];
        is_embeded = data[11];
        product_shelf = data[12];
        $.each(product_code, function (index) {
            if (is_embeded[index])
                lims_product_array.push(
                    'Code: ' + product_code[index] +
                    ' | Name: ' + product_name[index] +
                    ' | Price: ' + product_warehouse_price[index] +
                    ' | Qty: ' + product_qty[index] +
                    ' | Shelf: ' + product_shelf[index] +
                    ' | Embeded: ' + is_embeded[index]
                );
            else
                lims_product_array.push(
                    'Code: ' + product_code[index] +
                    ' | Name: ' + product_name[index] +
                    ' | Price: ' + product_warehouse_price[index] +
                    ' | Qty: ' + product_qty[index] +
                    ' | Shelf: ' + product_shelf[index]

                );
        });
    });

    isCashRegisterAvailable(warehouse_id);
});

var lims_productcodeSearch = $('#lims_productcodeSearch');

lims_productcodeSearch.autocomplete({
    source: function (request, response) {
        var matcher = new RegExp(".?" + $.ui.autocomplete.escapeRegex(request.term), "i");
        response($.grep(lims_product_array, function (item) {
            return matcher.test(item);
        }));
    },
    response: function (event, ui) {
        if (ui.content.length == 1) {
            var data = ui.content[0].value;
            $(this).autocomplete("close");
            productSearch(data);
        }
        else if (ui.content.length == 0 && $('#lims_productcodeSearch').val().length == 13) {
            productSearch($('#lims_productcodeSearch').val() + '|' + 1);
        }
    },
    select: function (event, ui) {
        var data = ui.item.value;
        ui.item.value = '';
        productSearch(data);
    },
});

$('#myTable').keyboard({
    accepted: function (event, keyboard, el) {
        checkQuantity(el.value, true);
    }
});

$("#myTable").on('click', '.plus', function () {
    rowindex = $(this).closest('tr').index();
    var qty = $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ') .qty').val();
    if (!qty)
        qty = 1;
    else
        qty = parseFloat(qty) + 1;
    if (is_variant[rowindex])
        checkQuantity(String(qty), true);
    else
        checkDiscount(qty, true);
});

$("#myTable").on('click', '.minus', function () {
    rowindex = $(this).closest('tr').index();
    var qty = parseFloat($('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ') .qty').val()) - 1;
    if (qty > 0) {
        $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ') .qty').val(qty);
    } else {
        qty = 1;
    }
    if (is_variant[rowindex])
        checkQuantity(String(qty), true);
    else
        checkDiscount(qty, true);
});

$("select[name=price_option]").on("change", function () {
    console.log($(this).val());
    $("#editModal input[name=edit_unit_price]").val($(this).val());
});

$("#myTable").on("change", ".batch-no", function () {
    rowindex = $(this).closest('tr').index();
    var product_id = $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.product-id').val();
    var warehouse_id = $('#warehouse_id').val();
    $.get('check-batch-availability/' + product_id + '/' + $(this).val() + '/' + warehouse_id, function (data) {
        if (data['message'] != 'ok') {
            alert(data['message']);
            $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.batch-no').val('');
            $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.product-batch-id').val('');
        }
        else {
            $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.product-batch-id').val(data['product_batch_id']);
            code = $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.product-code').val();
            pos = product_code.indexOf(code);
            product_qty[pos] = data['qty'];
        }
    });
});

//Change quantity
$("#myTable").on('input', '.qty', function () {
    rowindex = $(this).closest('tr').index();
    if ($(this).val() < 0 && $(this).val() != '') {
        $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ') .qty').val(1);
        alert("Quantity can't be less than 0");
    }
    if (is_variant[rowindex])
        checkQuantity($(this).val(), true);
    else
        checkDiscount($(this).val(), true);
});

$("#myTable").on('click', '.qty', function () {
    rowindex = $(this).closest('tr').index();
});

$(document).on('click', '.sound-btn', function () {
    var audio = $("#mysoundclip1")[0];
    audio.play();
});

$(document).on('click', '.product-img', function () {
    var customer_id = $('#customer_id').val();
    var warehouse_id = $('select[name="warehouse_id"]').val();
    if (!customer_id)
        alert('Please select Customer!');
    else if (!warehouse_id)
        alert('Please select Warehouse!');
    else {
        var data = $(this).data('product');
        product_info = data.split(" ");
        pos = product_code.indexOf(product_info[0]);
        if (pos < 0)
            alert('Product is not avaialable in the selected warehouse');
        else {
            productSearch(data);
        }
    }
});
//Delete product
$("table.order-list tbody").on("click", ".ibtnDel", function (event) {
    var audio = $("#mysoundclip2")[0];
    audio.play();
    rowindex = $(this).closest('tr').index();
    product_price.splice(rowindex, 1);
    wholesale_price.splice(rowindex, 1);
    product_discount.splice(rowindex, 1);
    tax_rate.splice(rowindex, 1);
    tax_name.splice(rowindex, 1);
    tax_method.splice(rowindex, 1);
    unit_name.splice(rowindex, 1);
    unit_operator.splice(rowindex, 1);
    unit_operation_value.splice(rowindex, 1);

    localStorageProductId.splice(rowindex, 1);
    localStorageQty.splice(rowindex, 1);
    localStorageSaleUnit.splice(rowindex, 1);
    localStorageProductDiscount.splice(rowindex, 1);
    localStorageTaxRate.splice(rowindex, 1);
    localStorageNetUnitPrice.splice(rowindex, 1);
    localStorageTaxValue.splice(rowindex, 1);
    localStorageSubTotalUnit.splice(rowindex, 1);
    localStorageSubTotal.splice(rowindex, 1);
    localStorageProductCode.splice(rowindex, 1);

    localStorageTaxName.splice(rowindex, 1);
    localStorageTaxMethod.splice(rowindex, 1);
    localStorageTempUnitName.splice(rowindex, 1);
    localStorageSaleUnitOperator.splice(rowindex, 1);
    localStorageSaleUnitOperationValue.splice(rowindex, 1);

    localStorage.setItem("localStorageProductId", localStorageProductId);
    localStorage.setItem("localStorageQty", localStorageQty);
    localStorage.setItem("localStorageSaleUnit", localStorageSaleUnit);
    localStorage.setItem("localStorageProductCode", localStorageProductCode);
    localStorage.setItem("localStorageProductDiscount", localStorageProductDiscount);
    localStorage.setItem("localStorageTaxRate", localStorageTaxRate);
    localStorage.setItem("localStorageTaxName", localStorageTaxName);
    localStorage.setItem("localStorageTaxMethod", localStorageTaxMethod);
    localStorage.setItem("localStorageTempUnitName", localStorageTempUnitName);
    localStorage.setItem("localStorageSaleUnitOperator", localStorageSaleUnitOperator);
    localStorage.setItem("localStorageSaleUnitOperationValue", localStorageSaleUnitOperationValue);
    localStorage.setItem("localStorageNetUnitPrice", localStorageNetUnitPrice);
    localStorage.setItem("localStorageTaxValue", localStorageTaxValue);
    localStorage.setItem("localStorageSubTotalUnit", localStorageSubTotalUnit);
    localStorage.setItem("localStorageSubTotal", localStorageSubTotal);

    $(this).closest("tr").remove();
    localStorage.setItem("tbody-id", $("table.order-list tbody").html());
    calculateTotal();
});

//Edit product
$("table.order-list").on("click", ".edit-product", function () {
    rowindex = $(this).closest('tr').index();
    edit();
});

//Update product
$('button[name="update_btn"]').on("click", function () {
    // Always coerce to numbers safely
    let edit_discount = parseFloat($('#editModal input[name="edit_discount"]').val()) || 0;
    let edit_qty = parseFloat($('#editModal input[name="edit_qty"]').val()) || 0;
    let edit_unit_price = parseFloat($('#editModal input[name="edit_unit_price"]').val()) || 0;

    let new_unit_price = edit_unit_price - edit_discount;
    console.log("New unit price:", new_unit_price);

    if (edit_discount > edit_unit_price) {
        alert('Invalid Discount Input!');
        return;
    }

    if (edit_qty <= 0) {
        $('input[name="edit_qty"]').val(1);
        edit_qty = 1;
        alert("Quantity can't be less than 0");
    }

    var tax_rate_all = <? php echo json_encode($tax_rate_all) ?>;
    let selectedTaxId = $('select[name="edit_tax_rate"]').val();
    let currentTaxRate = parseFloat(tax_rate_all[selectedTaxId]) || 0;

    tax_rate[rowindex] = localStorageTaxRate[rowindex] = currentTaxRate;
    tax_name[rowindex] = localStorageTaxName[rowindex] = $('select[name="edit_tax_rate"] option:selected').text();

    // ✅ Always store as numeric values
    product_discount[rowindex] = edit_discount;
    product_price[rowindex] = new_unit_price;

    console.log({
        edit_qty,
        edit_unit_price,
        edit_discount,
        new_unit_price,
        row_product_price: product_price[rowindex]
    });

    // Call checkDiscount
    if (edit_discount > 0) {
        checkDiscount(edit_qty, true, edit_discount);
    } else {
        checkDiscount(edit_qty, false);
    }

    // ✅ After discount changes, recalc total & tax safely
    calculateTotal();
});


$('button[name="order_discount_btn"]').on("click", function () {
    calculateGrandTotal();
});

$('button[name="shipping_cost_btn"]').on("click", function () {
    calculateGrandTotal();
});

$('button[name="order_tax_btn"]').on("click", function () {
    calculateGrandTotal();
});

$(".coupon-check").on("click", function () {
    couponDiscount();
});

$(".payment-btn").on("click", function () {
    var audio = $("#mysoundclip2")[0];
    audio.play();
    $('input[name="paid_amount"]').val($("#grand-total").text());
    $('input[name="paying_amount"]').val($("#grand-total").text());
    $('.qc').data('initial', 1);
});

$("#draft-btn").on("click", function () {
    console.log('draft');
    var audio = $("#mysoundclip2")[0];
    audio.play();
    $('input[name="sale_status"]').val(3);
    $('input[name="paying_amount"]').prop('required', false);
    $('input[name="paid_amount"]').prop('required', false);
    var rownumber = $('table.order-list tbody tr:last').index();
    if (rownumber < 0) {
        alert("Please insert product to order table!")
    }
    else
        console.log('submit');
    $('.payment-form').submit();
});

$("#submit-btn").on("click", function () {
    $('.payment-form').submit();
});

document.addEventListener("DOMContentLoaded", function () {
    // ✅ Function: show or hide global .mobile_money_fields depending on selections
    function toggleGlobalMobileMoneyFields() {
        // Check if any select currently has value '8'
        const hasMobileMoney = Array.from(document.querySelectorAll('select[name="paid_by_id_select[]"]'))
            .some(select => select.value === '8');
        console.log('Has Mobile Money:', hasMobileMoney);

        const fields = document.querySelectorAll('.mobile_money_fields');
        fields.forEach(field => {
            field.style.display = hasMobileMoney ? 'block' : 'none';
        });
    }

    // ✅ Listen to change event on all current & future selects
    $(document).on("change", 'select[name="paid_by_id_select[]"].payment-method-select', function () {
        toggleGlobalMobileMoneyFields();
    });

    // ✅ Initialize state on load
    toggleGlobalMobileMoneyFields();

    $("#multiplePaymentBtn").on("click", function () {
        let usedMethods = getSelectedPaymentMethods();

        let options = [
            { value: 1, label: 'Cash' },
            { value: 2, label: 'Gift Card' },
            { value: 3, label: 'Credit Card' },
            { value: 4, label: 'Cheque' },
            { value: 5, label: 'Paypal' },
            { value: 6, label: 'Deposit' },
            { value: 7, label: 'Points' },
            { value: 8, label: 'Mobile Money' }
        ];

        let availableOptions = options.filter(opt => !usedMethods.includes(opt.value));

        if (availableOptions.length === 0) {
            alert("All payment methods have been used.");
            return;
        }

        let selectOptions = availableOptions.map(opt => `<option value="${opt.value}">${opt.label}</option>`).join('');

        let paymentRow = `
            <div class="row mt-2 payment-row align-items-center">
                <div class="col-md-4">
                    <label>Payment Amount *</label>
                    <input type="number" name="split_amount[]" class="form-control numkey" step="any" required>
                </div>
                <div class="col-md-4">
                    <label>Payment Method</label>
                    <div class="d-flex align-items-center">
                        <select name="paid_by_id_select[]" class="form-control selectpicker payment-method-select">
                            ${selectOptions}
                        </select>
                        <button type="button" class="btn btn-danger ml-2 remove-payment"><i class="fa fa-trash"></i></button>
                    </div>
                </div>
            </div>
        `;

        $("#paymentMethodsContainer").append(paymentRow);
        $('.selectpicker').selectpicker('refresh');
        toggleGlobalMobileMoneyFields();
    });

});
function getSelectedPaymentMethods() {
    let methods = [];

    let mainMethod = $("select[name='paid_by_id_select']").val();
    if (mainMethod) methods.push(parseInt(mainMethod));

    $("select[name='paid_by_id[]']").each(function () {
        let val = $(this).val();
        if (val) methods.push(parseInt(val));
    });


    return methods;
}

$("#gift-card-btn").on("click", function () {
    $('select[name="paid_by_id"]').val(2);
    $('.selectpicker').selectpicker('refresh');
    $('div.qc').hide();
    $('.mobile_money').hide();

    giftCard();
});

$("#credit-card-btn").on("click", function () {
    $('select[name="paid_by_id"]').val(3);
    $('.selectpicker').selectpicker('refresh');
    $('div.qc').hide();
    creditCard();
});

$("#cheque-btn").on("click", function () {
    $('select[name="paid_by_id_select"]').val(4);
    $('.selectpicker').selectpicker('refresh');
    $('div.qc').hide();
    cheque();
});

$("#cash-btn").on("click", function () {
    $('select[name="paid_by_id"]').val(1);
    $('.selectpicker').selectpicker('refresh');
    $('div.qc').show();
    hide();
});

$("#paypal-btn").on("click", function () {
    $('select[name="paid_by_id"]').val(5);
    $('.selectpicker').selectpicker('refresh');
    $('div.qc').hide();
    hide();
});

$("#deposit-btn").on("click", function () {
    $('select[name="paid_by_id"]').val(6);
    $('.selectpicker').selectpicker('refresh');
    $('div.qc').hide();
    hide();
    deposits();
});

$("#point-btn").on("click", function () {
    $('select[name="paid_by_id"]').val(7);
    $('.selectpicker').selectpicker('refresh');
    $('div.qc').hide();
    hide();
    pointCalculation();
});


$("#mobile_money-btn").on("click", function () {
    $('select[name="paid_by_id"]').val(8);
    $('.selectpicker').selectpicker('refresh');
    $('div.qc').hide();
    hide();
    mobile_money();
});


$('select[name="paid_by_id"]').on("change", function () {
    var id = $(this).val();
    $(".payment-form").off("submit");
    if (id == 2) {
        $('div.qc').hide();
        giftCard();
    }
    else if (id == 3) {
        $('div.qc').hide();
        creditCard();
    }
    else if (id == 4) {
        $('div.qc').hide();
        cheque();
    }
    else if (id == 8) {
        $('div.qc').hide();
        mobile_money();
    }
    else {
        hide();
        if (id == 1)
            $('div.qc').show();
        else if (id == 6) {
            $('div.qc').hide();
            deposits();
        }
        else if (id == 7) {
            $('div.qc').hide();
            pointCalculation();
        }
    }
    $("#paymentMethodsContainer").empty();
    refreshPaymentMethodOptions();
});

$('#add-payment select[name="gift_card_id_select"]').on("change", function () {
    var balance = gift_card_amount[$(this).val()] - gift_card_expense[$(this).val()];
    $('#add-payment input[name="gift_card_id"]').val($(this).val());
    if ($('input[name="paid_amount"]').val() > balance) {
        alert('Amount exceeds card balance! Gift Card balance: ' + balance);
    }
});

$('#add-payment input[name="paying_amount"]').on("input", function () {
    change($(this).val(), $('input[name="paid_amount"]').val());
});

$('input[name="paid_amount"]').on("input", function () {
    if ($(this).val() > parseFloat($('input[name="paying_amount"]').val())) {
        alert('Paying amount cannot be bigger than recieved amount');
        $(this).val('');
    }
    else if ($(this).val() > parseFloat($('#grand-total').text())) {
        alert('Paying amount cannot be bigger than grand total');
        $(this).val('');
    }

    change($('input[name="paying_amount"]').val(), $(this).val());
    var id = $('select[name="paid_by_id"]').val();
    if (id == 2) {
        var balance = gift_card_amount[$("#gift_card_id_select").val()] - gift_card_expense[$("#gift_card_id_select").val()];
        if ($(this).val() > balance)
            alert('Amount exceeds card balance! Gift Card balance: ' + balance);
    }
    else if (id == 6) {
        if ($('input[name="paid_amount"]').val() > window.PosConfig.deposit[$('#customer_id').val()])
            alert('Amount exceeds customer deposit! Customer deposit : ' + window.PosConfig.deposit[$('#customer_id').val()]);
    }
});




$('.transaction-btn-plus').on("click", function () {
    $(this).addClass('d-none');
    $('.transaction-btn-close').removeClass('d-none');
});

$('.transaction-btn-close').on("click", function () {
    $(this).addClass('d-none');
    $('.transaction-btn-plus').removeClass('d-none');
});

$('.coupon-btn-plus').on("click", function () {
    $(this).addClass('d-none');
    $('.coupon-btn-close').removeClass('d-none');
});

$('.coupon-btn-close').on("click", function () {
    $(this).addClass('d-none');
    $('.coupon-btn-plus').removeClass('d-none');
});

$(document).on('click', '.qc-btn', function (e) {
    if ($(this).data('amount')) {
        if ($('.qc').data('initial')) {
            $('input[name="paying_amount"]').val($(this).data('amount').toFixed(discount_value) );
$('.qc').data('initial', 0);
        }
        else {
    $('input[name="paying_amount"]').val((parseFloat($('input[name="paying_amount"]').val()) + $(this).data('amount')).toFixed(discount_value) );
        }
    }
    else
$('input[name="paying_amount"]').val('{{number_format(0, discount_value, '.', '')}}');
change($('input[name="paying_amount"]').val(), $('input[name="paid_amount"]').val());
});

$(document).on("input", 'input[name="split_amount[]"]', function () {
    let payingAmount = parseFloat($('input[name="paying_amount"]').val()) || 0;
    console.log('Paying Amount:', payingAmount);
    let totalSplit = getTotalSplitAmount();
    let mainPaid = parseFloat($('input[name="paid_amount"]').val()) || 0;
    console.log('Main Paid:', mainPaid);
    let totalPaid = totalSplit + payingAmount;
    console.log('Total Paid (main + split):', totalPaid);

    if (totalPaid > mainPaid) {
        alert('Total paid (main + split) cannot be greater than paying amount');
        $(this).val('');
        totalSplit = getTotalSplitAmount(); // recalculate after clearing this input
        totalPaid = totalSplit + payingAmount;
    }

    change(mainPaid, totalPaid);
});

document.addEventListener("DOMContentLoaded", function () {
    const dropdown = document.getElementById("mobile_money_operator");
    const hiddenField = document.getElementById("selected_mobile_op");
    dropdown.addEventListener("change", function () {
        hiddenField.value = dropdown.value;
    });
    hiddenField.value = dropdown.value;
});

$(document).on('submit', '.payment-form', function (e) {
    $("table.order-list tbody .qty").each(function (index) {
        if ($(this).val() == '') {
            alert('One of products has no quantity!');
            e.preventDefault();
        }
    });
    var rownumber = $('table.order-list tbody tr:last').index();
    if (rownumber < 0) {
        alert("Please insert product to order table!")
        e.preventDefault();
    }
    else if (parseFloat($('input[name="total_qty"]').val()) <= 0) {
        alert('Product quantity is 0');
        e.preventDefault();
    }
    // else if( parseFloat( $('input[name="paying_amount"]').val() ) < parseFloat( $('input[name="paid_amount"]').val() ) ){
    //     alert('Paying amount cannot be bigger than recieved amount');
    //     e.preventDefault();
    // }
    else {
        $("#submit-button").prop('disabled', true);
    }
    $('input[name="paid_by_id"]').val($('select[name="paid_by_id"]').val());
    $('input[name="order_tax_rate"]').val($('select[name="order_tax_rate_select"]').val());

});


function change(paying_amount, paid_amount) {
    $("#change").text(parseFloat(paying_amount - paid_amount).toFixed() );
}

function getTotalSplitAmount() {
    let total = 0;
    $('input[name="split_amount[]"]').each(function () {
        total += parseFloat($(this).val()) || 0;
    });
    return total;
}

function productSearch(data) {
    var code_match = data.match(/Code:\s*([^|]+)/);
    console.log(code_match);
    var product_info = data.split(" ");
    var product_code = code_match ? code_match[1].trim() : data.split(" ")[0];

    if (product_code.length < 1) {
        alert('Please insert product code!');
        return;
    }
    var pre_qty = 0;
    $(".product-code").each(function (i) {
        if ($(this).val() == product_code) {
            rowindex = i;
            pre_qty = $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ') .qty').val();
        }
    });
    var ajaxData = product_code + '?' + $('#customer_id').val() + '?' + (parseFloat(pre_qty) + 1);
    console.log(ajaxData);
    //  var ajaxData = 'Code: ' + product_code + '?' + $('#customer_id').val() + '?' + (parseFloat(pre_qty) + 1);
    // data += '?'+$('#customer_id').val()+'?'+(parseFloat(pre_qty) + 1);
    $.ajax({
        type: 'GET',
        async: false,
        url: 'sales/lims_product_search',
        data: {
            data: ajaxData
        },
        success: function (data) {
            var flag = 1;
            if (pre_qty > 0) {
                var qty = data[15];
                $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ') .qty').val(qty);
                pos = product_code.indexOf(data[1]);
                if (!data[11] && product_warehouse_price[pos]) {
                    product_price[rowindex] = parseFloat(product_warehouse_price[pos] * currency['exchange_rate']) + parseFloat(product_warehouse_price[pos] * currency['exchange_rate'] * customer_group_rate);
                }
                else {
                    product_price[rowindex] = parseFloat(data[2] * currency['exchange_rate']) + parseFloat(data[2] * currency['exchange_rate'] * customer_group_rate);
                }
                flag = 0;
                checkQuantity(String(qty), true);
                flag = 0;
                localStorage.setItem("tbody-id", $("table.order-list tbody").html());
            }
            $("input[name='product_code_name']").val('');
            if (flag) {
                addNewProduct(data);
            }
            else if (data[18] != 'null' && data[18] != '') {
                var imeiNumbers = $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.imei-number').val();
                if (imeiNumbers)
                    imeiNumbers += ',' + data[18];
                else
                    imeiNumbers = data[18];
                $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.imei-number').val(imeiNumbers);
            }
        }
    });
}

function addNewProduct(data) {
    var newRow = $('<tr id=' + data[1] + '>');
    var cols = '';
    temp_unit_name = (data[6]).split(',');
    pos = product_code.indexOf(data[1]);
    cols += '<td class="col-sm-3 product-title"><strong class="edit-product btn btn-link" data-toggle="modal" data-target="#editModal"><span style="margin-left: -19px; white-space: break-spaces;"><strong>' + data[0] + ' <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" /></svg></strong><br><span>' + data[1] + '</span>' + '<p>In Stock: <span class="in-stock"></span></p></td>';
    if (data[12]) {
        cols += '<td class="col-sm-2"><input type="text" class="form-control batch-no" value="' + batch_no[pos] + '" required/> <input type="hidden" class="product-batch-id" name="product_batch_id[]" value="' + product_batch_id[pos] + '"/> </td>';
    }
    else {
        cols += '<td class="col-sm-2"><input type="text" class="form-control batch-no" disabled/> <input type="hidden" class="product-batch-id" name="product_batch_id[]"/> </td>';
    }
    cols += '<td class="col-sm-2 product-price"></td>';
    cols += '<td class="col-sm-2"><div class="input-group"><span class="input-group-btn"><button type="button" class="btn btn-default minus"><span class="dripicons-minus"></span></button></span><input type="text" name="qty[]" class="form-control qty numkey input-number" step="any" value="' + data[15] + '" required><span class="input-group-btn"><button type="button" class="btn btn-default plus"><span class="dripicons-plus"></span></button></span></div></td>';
    cols += '<td class="col-sm-2 sub-total"></td>';
    cols += '<td class="col-sm-1"><button type="button" class="ibtnDel btn btn-danger btn-sm"><i class="dripicons-cross"></i></button></td>';
    cols += '<input type="hidden" class="product-code" name="product_code[]" value="' + data[1] + '"/>';
    cols += '<input type="hidden" class="product-id" name="product_id[]" value="' + data[9] + '"/>';
    cols += '<input type="hidden" class="product_price" />';
    cols += '<input type="hidden" class="sale-unit" name="sale_unit[]" value="' + temp_unit_name[0] + '"/>';
    cols += '<input type="hidden" class="net_unit_price" name="net_unit_price[]" />';
    cols += '<input type="hidden" class="discount-value" name="discount[]" />';
    cols += '<input type="hidden" class="tax-rate" name="tax_rate[]" value="' + data[3] + '"/>';
    cols += '<input type="hidden" class="tax-value" name="tax[]" />';
    cols += '<input type="hidden" class="tax-name" value="' + data[4] + '" />';
    cols += '<input type="hidden" class="tax-method" value="' + data[5] + '" />';
    cols += '<input type="hidden" class="sale-unit-operator" value="' + data[7] + '" />';
    cols += '<input type="hidden" class="sale-unit-operation-value" value="' + data[8] + '" />';
    cols += '<input type="hidden" class="subtotal-value" name="subtotal[]" />';
    cols += '<input type="hidden" class="imei-number" name="imei_number[]" />';

    newRow.append(cols);
    if (window.PosConfig.keyboard_active == 1) {
        $("table.order-list tbody").prepend(newRow).find('.qty').keyboard({
            usePreview: false, layout: 'custom', display: { 'accept': '&#10004;', 'cancel': '&#10006;' }, customLayout: {
                'normal': ['1 2 3', '4 5 6', '7 8 9', '0 {dec} {bksp}', '{clear} {cancel} {accept}']
            }, restrictInput: true, preventPaste: true, autoAccept: true, css: { container: 'center-block dropdown-menu', buttonDefault: 'btn btn-default', buttonHover: 'btn-primary', buttonAction: 'active', buttonDisabled: 'disabled' },
        });
    }
    else
        $("table.order-list tbody").prepend(newRow);

    rowindex = newRow.index();

    if (!data[11] && product_warehouse_price[pos]) {
        product_price.splice(rowindex, 0, parseFloat(product_warehouse_price[pos] * currency['exchange_rate']) + parseFloat(product_warehouse_price[pos] * currency['exchange_rate'] * customer_group_rate));
    }
    else {
        product_price.splice(rowindex, 0, parseFloat(data[2] * currency['exchange_rate']) + parseFloat(data[2] * currency['exchange_rate'] * customer_group_rate));
    }

    if (data[16])
        wholesale_price.splice(rowindex, 0, parseFloat(data[16] * currency['exchange_rate']) + parseFloat(data[16] * currency['exchange_rate'] * customer_group_rate));
    else
        wholesale_price.splice(rowindex, 0, Number(0).toFixed(discount_value));


    //cost.splice(rowindex, 0, parseFloat(data[17] * currency['exchange_rate']));
    product_discount.splice(rowindex, 0,  Number(0).toFixed(discount_value));

    tax_rate.splice(rowindex, 0, parseFloat(data[3]));
    tax_name.splice(rowindex, 0, data[4]);
    tax_method.splice(rowindex, 0, data[5]);
    unit_name.splice(rowindex, 0, data[6]);
    unit_operator.splice(rowindex, 0, data[7]);
    unit_operation_value.splice(rowindex, 0, data[8]);
    is_imei.splice(rowindex, 0, data[13]);
    is_variant.splice(rowindex, 0, data[14]);
    $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.product_price').val(product_price[rowindex]);
    localStorageQty.splice(rowindex, 0, data[15]);
    localStorageProductId.splice(rowindex, 0, data[9]);
    localStorageProductCode.splice(rowindex, 0, data[1]);
    localStorageSaleUnit.splice(rowindex, 0, temp_unit_name[0]);
    localStorageProductDiscount.splice(rowindex, 0, product_discount[rowindex]);
    localStorageTaxRate.splice(rowindex, 0, tax_rate[rowindex].toFixed(discount_value));
localStorageTaxName.splice(rowindex, 0, data[4]);
localStorageTaxMethod.splice(rowindex, 0, data[5]);
localStorageTempUnitName.splice(rowindex, 0, data[6]);
localStorageSaleUnitOperator.splice(rowindex, 0, data[7]);
localStorageSaleUnitOperationValue.splice(rowindex, 0, data[8]);
//put some dummy value
localStorageNetUnitPrice.splice(rowindex, 0,  Number(0).toFixed(discount_value));

localStorageTaxValue.splice(rowindex, 0,  Number(0).toFixed(discount_value));

localStorageSubTotalUnit.splice(rowindex, 0,  Number(0).toFixed(discount_value));

localStorageSubTotal.splice(rowindex, 0,  Number(0).toFixed(discount_value));

localStorage.setItem("localStorageProductId", localStorageProductId);
localStorage.setItem("localStorageSaleUnit", localStorageSaleUnit);
localStorage.setItem("localStorageProductCode", localStorageProductCode);
localStorage.setItem("localStorageTaxName", localStorageTaxName);
localStorage.setItem("localStorageTaxMethod", localStorageTaxMethod);
localStorage.setItem("localStorageTempUnitName", localStorageTempUnitName);
localStorage.setItem("localStorageSaleUnitOperator", localStorageSaleUnitOperator);
localStorage.setItem("localStorageSaleUnitOperationValue", localStorageSaleUnitOperationValue);
checkQuantity(data[15], true);
checkDiscount(data[15], true);
localStorage.setItem("tbody-id", $("table.order-list tbody").html());
if (data[16]) {
    populatePriceOption();
    $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.edit-product').click();
}
}


function populatePriceOption() {
    $('#editModal select[name=price_option]').empty();
    $('#editModal select[name=price_option]').append('<option value="' + product_price[rowindex] + '">' + product_price[rowindex] + '</option>');
    if (wholesale_price[rowindex] > 0)
        $('#editModal select[name=price_option]').append('<option value="' + wholesale_price[rowindex] + '">' + wholesale_price[rowindex] + '</option>');
    $('.selectpicker').selectpicker('refresh');
}

function edit() {
    console.log("Editing row index: " + rowindex);
    $(".imei-section").remove();
    if (is_imei[rowindex]) {
        var imeiNumbers = $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.imei-number').val();

        if (imeiNumbers.length) {
            imeiArrays = [...new Set(imeiNumbers.split(","))];
            htmlText = `<div class="col-md-8 form-group imei-section">
                        <label>IMEI or Serial Numbers</label>
                        <div class="table-responsive">
                            <table id="imei-table" class="table table-hover">
                                <tbody>`;
            for (var i = 0; i < imeiArrays.length; i++) {
                htmlText += `<tr>
                                <td>
                                    <input type="text" class="form-control imei-numbers" name="imei_numbers[]" value="`+ imeiArrays[i] + `" />
                                </td>
                                <td>
                                    <button type="button" class="imei-del btn btn-sm btn-danger">X</button>
                                </td>
                            </tr>`;
            }
            htmlText += `</tbody>
                            </table>
                        </div>
                    </div>`;
            $("#editModal .modal-element").append(htmlText);
        }
    }


    populatePriceOption();
    //$("#product-cost").text(cost[rowindex]);
    var row_product_name_code = $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('td:nth-child(1)').text();
    $('#modal_header').text(row_product_name_code);

    var qty = $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.qty').val();
    $('input[name="edit_qty"]').val(qty);

    cur_product_id = $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ') .product-id').val();

    $('input[name="edit_discount"]').val(parseFloat(product_discount[rowindex]).toFixed(discount_value));



var row_product_code = $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.product-code').val();
pos = product_code.indexOf(row_product_code);
if (product_type[pos] == 'standard') {
    unitConversion();
    temp_unit_name = (unit_name[rowindex]).split(',');
    temp_unit_name.pop();
    temp_unit_operator = (unit_operator[rowindex]).split(',');
    temp_unit_operator.pop();
    temp_unit_operation_value = (unit_operation_value[rowindex]).split(',');
    temp_unit_operation_value.pop();
    $('select[name="edit_unit"]').empty();
    $.each(temp_unit_name, function (key, value) {
        $('select[name="edit_unit"]').append('<option value="' + key + '">' + value + '</option>');
    });
    $("#edit_unit").show();
}
else {
    row_product_price = product_price[rowindex];
    $("#edit_unit").hide();
}
$('input[name="edit_unit_price"]').val(row_product_price.toFixed(discount_value));
$('.selectpicker').selectpicker('refresh');
}

function couponDiscount() {
    var rownumber = $('table.order-list tbody tr:last').index();
    if (rownumber < 0) {
        alert("Please insert product to order table!")
    }
    else if ($("#coupon-code").val() != '') {
        valid = 0;
        $.each(coupon_list, function (key, value) {
            if ($("#coupon-code").val() == value['code']) {
                valid = 1;
                todyDate = <? php echo json_encode(date('Y-m-d')) ?>;
                if (parseFloat(value['quantity']) <= parseFloat(value['used']))
                    alert('This Coupon is no longer available');
                else if (todyDate > value['expired_date'])
                    alert('This Coupon has expired!');
                else if (value['type'] == 'fixed') {
                    if (parseFloat($('input[name="grand_total"]').val()) >= value['minimum_amount']) {
                        $('input[name="grand_total"]').val($('input[name="grand_total"]').val() - (value['amount'] * currency['exchange_rate']));
                        $('#grand-total').text(parseFloat($('input[name="grand_total"]').val()).toFixed(discount_value));
        if (!$('input[name="coupon_active"]').val())
            alert('Congratulation! You got ' + (value['amount'] * currency['exchange_rate']) + ' ' + currency['code'] + ' discount');
        $(".coupon-check").prop("disabled", true);
        $("#coupon-code").prop("disabled", true);
        $('input[name="coupon_active"]').val(1);
        $("#coupon-modal").modal('hide');
        $('input[name="coupon_id"]').val(value['id']);
        $('input[name="coupon_discount"]').val(value['amount'] * currency['exchange_rate']);
        $('#coupon-text').text(parseFloat(value['amount'] * currency['exchange_rate']).toFixed(discount_value)); 
                    }
                    else
alert('Grand Total is not sufficient for discount! Required ' + value['minimum_amount'] + ' ' + currency['code']);
                }
                else {
    var grand_total = $('input[name="grand_total"]').val();
    var coupon_discount = grand_total * (value['amount'] / 100);
    grand_total = grand_total - coupon_discount;
    $('input[name="grand_total"]').val(grand_total);
    $('#grand-total').text(parseFloat(grand_total).toFixed(discount_value));
if (!$('input[name="coupon_active"]').val())
    alert('Congratulation! You got ' + value['amount'] + '% discount');
$(".coupon-check").prop("disabled", true);
$("#coupon-code").prop("disabled", true);
$('input[name="coupon_active"]').val(1);
$("#coupon-modal").modal('hide');
$('input[name="coupon_id"]').val(value['id']);
$('input[name="coupon_discount"]').val(coupon_discount);
$('#coupon-text').text(parseFloat(coupon_discount).toFixed(discount_value));
                }
            }
        });
if (!valid)
    alert('Invalid coupon code!');
    }
}

function checkDiscount(qty, flag, manualDiscount = null) {
    console.log("Discount identifier");
    var customer_id = $('#customer_id').val();
    var product_id = $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ') .product-id').val();

    if (flag) {
        console.log("Checking discount for qty: " + qty + ", customer_id: " + customer_id + ", product_id: " + product_id);
        $.ajax({
            type: 'GET',
            async: false,
            url: 'sales/check-discount?qty=' + qty + '&customer_id=' + customer_id + '&product_id=' + product_id,
            success: function (data) {
                console.log(data);
                pos = product_code.indexOf($('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ') .product-code').val());
                product_price[rowindex] = parseFloat(data[0] * currency['exchange_rate']) + parseFloat(data[0] * currency['exchange_rate'] * customer_group_rate);
                let serverDiscount = data[2];
                let finalDiscount = (serverDiscount !== null && !isNaN(serverDiscount))
                    ? parseFloat(serverDiscount)
                    : parseFloat(manualDiscount || 0);
                console.log("Final discount to apply:", finalDiscount);

                // Sanitize current discount value
                let discountText = $('#discount').text().replace(/,/g, '').trim();
                let productDiscount = parseFloat(discountText);
                if (isNaN(productDiscount)) {
                    console.warn("Resetting non-numeric discount:", discountText);
                    productDiscount = 0;
                }

                // Apply the new discount
                let updatedDiscount = productDiscount;
                if (flag === true)
                    updatedDiscount = productDiscount + finalDiscount;
                else if (flag === false)
                    updatedDiscount = productDiscount - (finalDiscount * qty);
                else if (flag === 'input')
                    updatedDiscount = productDiscount - finalDiscount * previousqty + finalDiscount * qty;
                else
                    updatedDiscount = productDiscount - finalDiscount;

                // Ensure it's numeric and formatted
                if (isNaN(updatedDiscount)) updatedDiscount = 0;

                $('#discount').text(updatedDiscount.toFixed(decimals));
                console.log("Updated discount:", updatedDiscount);
            }
        });
    }

    $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ') .qty').val(qty);
    flag = true;
    checkQuantity(String(qty), flag);
    localStorage.setItem("tbody-id", $("table.order-list tbody").html());
}


function checkQuantity(sale_qty, flag) {
    var row_product_code = $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.product-code').val();
    pos = product_code.indexOf(row_product_code);
    $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.in-stock').text(product_qty[pos]);
    localStorageQty[rowindex] = sale_qty;
    localStorage.setItem("localStorageQty", localStorageQty);
    if (without_stock == 'no') {
        if (product_type[pos] == 'standard') {
            var operator = unit_operator[rowindex].split(',');
            var operation_value = unit_operation_value[rowindex].split(',');
            if (operator[0] == '*')
                total_qty = sale_qty * operation_value[0];
            else if (operator[0] == '/')
                total_qty = sale_qty / operation_value[0];
            if (total_qty > parseFloat(product_qty[pos])) {
                alert('Quantity exceeds stock quantity!');
                if (flag) {
                    sale_qty = sale_qty.substring(0, sale_qty.length - 1);
                    localStorageQty[rowindex] = sale_qty;
                    localStorage.setItem("localStorageQty", localStorageQty);
                    checkQuantity(sale_qty, true);
                }
                else {
                    localStorageQty[rowindex] = sale_qty;
                    localStorage.setItem("localStorageQty", localStorageQty);
                    edit();
                    return;
                }
            }
            $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.qty').val(sale_qty);
        }
        else if (product_type[pos] == 'combo') {
            child_id = product_list[pos].split(',');
            child_qty = qty_list[pos].split(',');
            $(child_id).each(function (index) {
                var position = product_id.indexOf(parseInt(child_id[index]));
                //console.log(position);
                if (position == -1 || parseFloat(sale_qty * child_qty[index]) > product_qty[position]) {
                    alert('Quantity exceeds stock quantity!');
                    if (flag) {
                        sale_qty = sale_qty.substring(0, sale_qty.length - 1);
                        $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.qty').val(sale_qty);
                    }
                    else {
                        edit();
                        flag = true;
                        return false;
                    }
                }
            });
        }
    }
    else
        $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.qty').val(sale_qty);
    if (!flag) {
        $('#editModal').modal('hide');
        $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.qty').val(sale_qty);
    }
    calculateRowProductData(sale_qty);
}

function unitConversion() {
    var row_unit_operator = unit_operator[rowindex].slice(0, unit_operator[rowindex].indexOf(","));
    var row_unit_operation_value = unit_operation_value[rowindex].slice(0, unit_operation_value[rowindex].indexOf(","));

    if (row_unit_operator == '*') {
        row_product_price = product_price[rowindex] * row_unit_operation_value;
    } else {
        row_product_price = product_price[rowindex] / row_unit_operation_value;
    }
}

function calculateRowProductData(quantity) {
    if (product_type[pos] == 'standard')
        unitConversion();
    else
        row_product_price = product_price[rowindex];
    if (tax_method[rowindex] == 1) {
        var net_unit_price = row_product_price - product_discount[rowindex];
        var tax = net_unit_price * quantity * (tax_rate[rowindex] / 100);
        var sub_total = (net_unit_price * quantity) + tax;

        if (parseFloat(quantity))
            var sub_total_unit = sub_total / quantity;
        else
            var sub_total_unit = sub_total;
    }
    else {
        var sub_total_unit = row_product_price - product_discount[rowindex];
        var net_unit_price = (100 / (100 + tax_rate[rowindex])) * sub_total_unit;
        var tax = (sub_total_unit - net_unit_price) * quantity;
        var sub_total = sub_total_unit * quantity;
    }

    $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.discount-value').val((product_discount[rowindex] * quantity).toFixed(discount_value));
$('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.tax-rate').val(tax_rate[rowindex].toFixed(discount_value));
$('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.net_unit_price').val(net_unit_price.toFixed(discount_value));
$('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.tax-value').val(tax.toFixed(discount_value));
$('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.product-price').text(sub_total_unit.toFixed(discount_value));
$('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.sub-total').text(sub_total.toFixed(discount_value));
$('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.subtotal-value').val(sub_total.toFixed(discount_value));

localStorageProductDiscount.splice(rowindex, 1, (product_discount[rowindex] * quantity).toFixed(discount_value));
localStorageTaxRate.splice(rowindex, 1, tax_rate[rowindex].toFixed(discount_value));
localStorageNetUnitPrice.splice(rowindex, 1, net_unit_price.toFixed(discount_value));
localStorageTaxValue.splice(rowindex, 1, tax.toFixed(discount_value));
localStorageSubTotalUnit.splice(rowindex, 1, sub_total_unit.toFixed(discount_value));
localStorageSubTotal.splice(rowindex, 1, sub_total.toFixed(discount_value));
localStorage.setItem("localStorageProductDiscount", localStorageProductDiscount);
localStorage.setItem("localStorageTaxRate", localStorageTaxRate);
localStorage.setItem("localStorageNetUnitPrice", localStorageNetUnitPrice);
localStorage.setItem("localStorageTaxValue", localStorageTaxValue);
localStorage.setItem("localStorageSubTotalUnit", localStorageSubTotalUnit);
localStorage.setItem("localStorageSubTotal", localStorageSubTotal);

calculateTotal();
}

function calculateTotal() {
    //Sum of quantity
    var total_qty = 0;
    $("table.order-list tbody .qty").each(function (index) {
        if ($(this).val() == '') {
            total_qty += 0;
        } else {
            total_qty += parseFloat($(this).val());
        }
    });
    $('input[name="total_qty"]').val(total_qty);

    //Sum of discount
    var total_discount = 0;
    $("table.order-list tbody .discount-value").each(function () {
        total_discount += parseFloat($(this).val());
    });

    $('input[name="total_discount"]').val(total_discount.toFixed(discount_value));

//Sum of tax
var total_tax = 0;
$(".tax-value").each(function () {
    total_tax += parseFloat($(this).val());
});

$('input[name="total_tax"]').val(total_tax.toFixed(discount_value));

//Sum of subtotal
var total = 0;
$(".sub-total").each(function () {
    total += parseFloat($(this).text());
});
$('input[name="total_price"]').val(total.toFixed(discount_value));

calculateGrandTotal();
}

function calculateGrandTotal() {
    var item = $('table.order-list tbody tr:last').index();
    var total_qty = parseFloat($('input[name="total_qty"]').val());
    var subtotal = parseFloat($('input[name="total_price"]').val());
    var order_tax = parseFloat($('select[name="order_tax_rate_select"]').val());
    var order_discount_type = $('select[name="order_discount_type_select"]').val();
    var order_discount_value = parseFloat($('input[name="order_discount_value"]').val());

    if (!order_discount_value)
        order_discount_value =  Number(0).toFixed(discount_value);

};

if (order_discount_type == 'Flat') {
    if (!currencyChange) {
        var order_discount = parseFloat(order_discount_value);
    }
    else
        var order_discount = parseFloat(order_discount_value * currency['exchange_rate']);
}
else
    var order_discount = parseFloat(subtotal * (order_discount_value / 100));

localStorage.setItem("order-tax-rate-select", order_tax);
localStorage.setItem("order-discount-type", order_discount_type);
$("#discount").text(order_discount.toFixed(discount_value));
$('input[name="order_discount"]').val(order_discount);
$('input[name="order_discount_type"]').val(order_discount_type);
if (!currencyChange)
    var shipping_cost = parseFloat($('input[name="shipping_cost"]').val());
else
    var shipping_cost = parseFloat($('input[name="shipping_cost"]').val() * currency['exchange_rate']);
if (!shipping_cost)
    shipping_cost =  Number(0).toFixed(discount_value);


item = ++item + '(' + total_qty + ')';
order_tax = (subtotal - order_discount) * (order_tax / 100);
var grand_total = (subtotal + order_tax + shipping_cost) - order_discount;
$('input[name="grand_total"]').val(grand_total.toFixed(discount_value));

couponDiscount();
if (!currencyChange)
    var coupon_discount = parseFloat($('input[name="coupon_discount"]').val());
else
    var coupon_discount = parseFloat($('input[name="coupon_discount"]').val() * currency['exchange_rate']);
if (!coupon_discount)
    coupon_discount =  Number(0).toFixed(discount_value);

grand_total -= coupon_discount;

$('#item').text(item);
$('input[name="item"]').val($('table.order-list tbody tr:last').index() + 1);
$('#subtotal').text(subtotal.toFixed(discount_value));
$('#tax').text(order_tax.toFixed(discount_value));
$('input[name="order_tax"]').val(order_tax.toFixed(discount_value));
$('#shipping-cost').text(shipping_cost.toFixed(discount_value));
$('input[name="shipping_cost"]').val(shipping_cost);
$('#grand-total').text(grand_total.toFixed(discount_value));
$('input[name="grand_total"]').val(grand_total.toFixed(discount_value));
currencyChange = false;


function hide() {
    $(".card-element").hide();
    $(".card-errors").hide();
    $(".cheque").hide();
    $(".gift-card").hide();
    $(".mobile_money_fields").hide();

    $('input[name="cheque_no"]').attr('required', false);
}

function giftCard() {
    $(".gift-card").show();
    $.ajax({
        url: 'sales/get_gift_card',
        type: "GET",
        dataType: "json",
        success: function (data) {
            $('#add-payment select[name="gift_card_id_select"]').empty();
            $.each(data, function (index) {
                gift_card_amount[data[index]['id']] = data[index]['amount'];
                gift_card_expense[data[index]['id']] = data[index]['expense'];
                $('#add-payment select[name="gift_card_id_select"]').append('<option value="' + data[index]['id'] + '">' + data[index]['card_no'] + '</option>');
            });
            $('.selectpicker').selectpicker('refresh');
            $('.selectpicker').selectpicker();
        }
    });
    $(".card-element").hide();
    $(".card-errors").hide();
    $(".cheque").hide();
    $('input[name="cheque_no"]').attr('required', false);
}


function mobile_money() {
    $(".mobile_money_fields").show();
    $(".gift-card").hide();
    $(".card-element").hide();
    $(".card-errors").hide();
    $(".cheque").hide();
    $('input[name="cheque_no"]').attr('required', false);
}

function cheque() {
    $(".cheque").show();
    $('input[name="cheque_no"]').attr('required', true);
    $(".card-element").hide();
    $(".card-errors").hide();
    $(".gift-card").hide();
}

function creditCard() {
    const settings = PosConfig.pos_settings;
    const pub = PosConfig.stripe_public_key;
    const sec = PosConfig.stripe_secret_key;

    const stripeEnabled =
        settings &&
        pub && pub.length > 0 &&
        sec && sec.length > 0;

    // Load Stripe Checkout only when keys exist
    if (stripeEnabled) {
        $.getScript("vendor/stripe/checkout.js");
        $(".card-element").show();
        $(".card-errors").show();
    } else {
        console.warn("Stripe not configured: public/secret keys missing.");
        $(".card-element").hide();
        $(".card-errors").hide();
    }

    // Hide other payment elements
    $(".cheque").hide();
    $(".gift-card").hide();
    $('input[name="cheque_no"]').attr('required', false);
}


function deposits() {
    if ($('input[name="paid_amount"]').val() > window.PosConfig.deposit[$('#customer_id').val()]) {
        alert('Amount exceeds customer deposit! Customer deposit : ' + window.PosConfig.deposit[$('#customer_id').val()]);
    }
    $('input[name="cheque_no"]').attr('required', false);
    $('#add-payment select[name="gift_card_id_select"]').attr('required', false);
}

function pointCalculation() {
    paid_amount = $('input[name=paid_amount]').val();
    required_point = Math.ceil(paid_amount / window.PosConfig.reward_point_setting['per_point_amount']);
    if (required_point > window.PosConfig.points[$('#customer_id').val()]) {
        alert('Customer does not have sufficient points. Available points: ' + window.PosConfig.points[$('#customer_id').val()]);
    }
    else {
        $("input[name=used_points]").val(required_point);
    }
}

function cancel(rownumber) {
    while (rownumber >= 0) {
        product_price.pop();
        wholesale_price.pop();
        product_discount.pop();
        tax_rate.pop();
        tax_name.pop();
        tax_method.pop();
        unit_name.pop();
        unit_operator.pop();
        unit_operation_value.pop();
        $('table.order-list tbody tr:last').remove();
        rownumber--;
    }
    $('input[name="shipping_cost"]').val('');
    $('input[name="order_discount_value"]').val('');
    $('select[name="order_tax_rate_select"]').val(0);
    calculateTotal();
}

function confirmCancel() {
    var audio = $("#mysoundclip2")[0];
    audio.play();
    if (confirm("Are you sure want to cancel?")) {
        cancel($('table.order-list tbody tr:last').index());
    }
    return false;
}

function confirmDelete() {
    if (confirm("Are you sure want to delete?")) {
        return true;
    }
    return false;
}




$('#product-table').DataTable({
    "order": [[0, 'desc']],
    'pageLength': window.PosConfig.product_row_number,
    'language': {
        'paginate': {
            'previous': '<i class="fa fa-angle-left"></i>',
            'next': '<i class="fa fa-angle-right"></i>'
        }
    },
    dom: 'tp'
});



