/**
 * Orders module JS
 */
var orderItems = [];
var productCache = {};

$(function() {
    if ($('#orders-table').length && !$.fn.DataTable.isDataTable('#orders-table')) {
        window.mainTable = $('#orders-table').DataTable({
            processing: true, serverSide: true,
            ajax: { url: BASE_URL + 'orders/datatable', data: function(d) { d.status_filter = window.currentStatusFilter || ''; } },
            columns: [{data:0},{data:1},{data:2},{data:3},{data:4},{data:5},{data:6},{data:7},{data:8,orderable:false}],
            order: [[0, 'desc']]
        });
    }

    $('#btn-add-order').click(function() {
        $('#order-form')[0].reset();
        $('#order-id').val(0);
        orderItems = [];
        renderItems();
        $('#order-modal').modal('show');
        CRM.init_plugins($('#order-modal'));
    });

    $('#btn-add-item').click(function() {
        orderItems.push({ product_id: '', product_name: 'Select...', qty: 1, unit_price: 0, discount_pct: 0, line_total: 0 });
        renderItems();
    });

    function renderItems() {
        var html = '';
        $.each(orderItems, function(i, item) {
            html += '<tr data-idx="' + i + '">' +
                '<td><select class="form-control input-sm item-product" data-idx="' + i + '">' +
                '<option value="">-- Product --</option>' +
                getProductOptions(item.product_id) + '</select></td>' +
                '<td><input type="number" class="form-control input-sm item-qty" value="' + item.qty + '" min="1" data-idx="' + i + '"></td>' +
                '<td><input type="number" class="form-control input-sm item-price" value="' + (item.unit_price / 100).toFixed(2) + '" step="0.01" data-idx="' + i + '"></td>' +
                '<td><input type="number" class="form-control input-sm item-disc" value="' + item.discount_pct + '" step="0.01" min="0" max="100" data-idx="' + i + '"></td>' +
                '<td>' + CRM.format_inr(item.line_total) + '</td>' +
                '<td><button class="btn btn-xs btn-danger btn-remove-item" data-idx="' + i + '"><i class="fa fa-times"></i></button></td>' +
                '</tr>';
        });
        $('#order-items-body').html(html);
        recalculate();
    }

    function getProductOptions(selectedId) {
        var opts = '';
        $.each(window.productList || [], function(i, p) {
            opts += '<option value="' + p.id + '" data-price="' + p.price + '" ' + (p.id == selectedId ? 'selected' : '') + '>' + CRM.esc(p.name) + ' (' + CRM.esc(p.sku) + ')</option>';
        });
        return opts;
    }

    // Use product list injected by PHP (window.PRODUCT_LIST) to avoid admin-only endpoint
    window.productList = window.PRODUCT_LIST || [];

    $(document).on('change', '.item-product', function() {
        var idx = $(this).data('idx');
        var price = parseFloat($(this).find(':selected').data('price') || 0);
        orderItems[idx].product_id = $(this).val();
        orderItems[idx].unit_price = price;
        recalcTotals(idx);
        renderItems();
    });

    $(document).on('input', '.item-qty, .item-price, .item-disc', function() {
        var idx = $(this).data('idx');
        if ($(this).hasClass('item-qty'))   orderItems[idx].qty          = parseInt($(this).val()) || 1;
        if ($(this).hasClass('item-price')) orderItems[idx].unit_price   = Math.round(parseFloat($(this).val()) * 100) || 0;
        if ($(this).hasClass('item-disc'))  orderItems[idx].discount_pct = parseFloat($(this).val()) || 0;
        recalcTotals(idx);
        recalculate();
    });

    $(document).on('click', '.btn-remove-item', function() {
        orderItems.splice($(this).data('idx'), 1);
        renderItems();
    });

    function recalcTotals(idx) {
        var item = orderItems[idx];
        item.line_total = Math.round(item.qty * item.unit_price * (1 - item.discount_pct / 100));
    }

    function recalculate() {
        var subtotal = 0;
        $.each(orderItems, function(i, item) { subtotal += item.line_total; });
        var disc = Math.round(parseFloat($('#order-discount').val()) * 100) || 0;
        var total = Math.max(0, subtotal - disc);
        $('#order-subtotal').text(CRM.format_inr(subtotal));
        $('#order-total').text(CRM.format_inr(total));
        $('#order-items-json').val(JSON.stringify(orderItems));
    }

    $('#order-discount').on('input', recalculate);

    function submitOrder(forApproval) {
        if (forApproval) $('<input type="hidden" name="submit_for_approval" value="1">').appendTo('#order-form');
        else $('[name="submit_for_approval"]').remove();

        var $btn = forApproval ? $('#btn-submit-approval') : $('#btn-save-draft');
        CRM.btn_loading($btn);
        $.ajax({
            url: BASE_URL + 'orders/save', method: 'POST',
            data: new FormData($('#order-form')[0]), processData: false, contentType: false,
            success: function(res) {
                if (res.status === 'success') {
                    CRM.toast('success', res.message);
                    $('#order-modal').modal('hide');
                    window.mainTable.ajax.reload(null, false);
                } else {
                    CRM.show_errors($('#order-form'), res.errors || {});
                    CRM.toast('error', res.message);
                }
            },
            complete: function() { CRM.btn_reset($btn); }
        });
    }

    $('#btn-save-draft').click(function() { submitOrder(false); });
    $('#btn-submit-approval').click(function() { submitOrder(true); });

    $(document).on('click', '.btn-order-status', function() {
        var action = $(this).data('action'), id = $(this).data('id');
        CRM.handle_status(action, id, BASE_URL + 'orders/status', window.mainTable);
    });
});
