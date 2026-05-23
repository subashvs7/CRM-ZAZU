/**
 * Orders module JS
 */
$(function() {
    if ($('#orders-table').length && !$.fn.DataTable.isDataTable('#orders-table')) {
        window.mainTable = $('#orders-table').DataTable({
            processing: true, serverSide: true,
            ajax: { url: BASE_URL + 'orders/datatable', data: function(d) { d.status_filter = window.currentStatusFilter || ''; } },
            columns: [{data:0},{data:1},{data:2},{data:3},{data:4},{data:5},{data:6},{data:7},{data:8,orderable:false}],
            order: [[0, 'desc']]
        });
    }

    // Use product list injected by PHP
    window.productList = window.PRODUCT_LIST || [];

    // ---- Item row helpers ----
    function productSelectHtml(selectedId) {
        var opts = '<option value="">— Product —</option>';
        $.each(window.productList, function(i, p) {
            opts += '<option value="' + p.id + '" data-price="' + p.price + '"' +
                (p.id == selectedId ? ' selected' : '') + '>' +
                CRM.esc(p.name) + ' (' + CRM.esc(p.sku) + ')</option>';
        });
        return opts;
    }

    function addItemRow(item) {
        item = item || {};
        var $tr = $('<tr class="border-t border-gray-100">').append(
            $('<td class="px-2 py-1.5">').append(
                $('<select class="w-full px-2 py-1 border border-gray-200 rounded-lg text-xs focus:outline-none focus:ring-1 focus:ring-blue-500 prod-select">').html(productSelectHtml(item.product_id || ''))
            ),
            '<td class="px-2 py-1.5"><input type="number" min="1" value="' + (item.qty || 1) + '" class="w-16 px-2 py-1 border border-gray-200 rounded-lg text-xs focus:outline-none item-qty"></td>',
            '<td class="px-2 py-1.5"><input type="number" step="0.01" value="' + (item.unit_price_inr || '') + '" placeholder="auto" class="w-24 px-2 py-1 border border-gray-200 rounded-lg text-xs focus:outline-none item-price"></td>',
            '<td class="px-2 py-1.5"><input type="number" step="0.01" min="0" max="100" value="' + (item.discount_pct || 0) + '" class="w-16 px-2 py-1 border border-gray-200 rounded-lg text-xs focus:outline-none item-disc"></td>',
            '<td class="px-2 py-1.5 item-line-total text-xs font-medium text-gray-700">₹0.00</td>',
            '<td class="px-2 py-1.5 text-center"><button type="button" class="text-red-400 hover:text-red-600 btn-remove-item"><i class="fa fa-times"></i></button></td>'
        );
        $('#order-items-body').append($tr);
        recalcTotals();
    }

    function recalcTotals() {
        var subtotal = 0;
        $('#order-items-body tr').each(function() {
            var pid   = $(this).find('.prod-select').val();
            var prod  = window.productList.filter(function(p) { return p.id == pid; })[0];
            var price = parseFloat($(this).find('.item-price').val()) || (prod ? prod.price / 100 : 0);
            var qty   = parseInt($(this).find('.item-qty').val()) || 1;
            var disc  = parseFloat($(this).find('.item-disc').val()) || 0;
            var line  = Math.round(qty * price * (1 - disc / 100) * 100) / 100;
            $(this).find('.item-line-total').text('₹' + line.toFixed(2));
            subtotal += line;
        });
        $('#order-subtotal').text('₹' + subtotal.toFixed(2));
        var discount = parseFloat($('#order-discount').val()) || 0;
        $('#order-total').text('₹' + (Math.max(0, subtotal - discount)).toFixed(2));
    }

    function serializeItems() {
        var items = [];
        $('#order-items-body tr').each(function() {
            var pid = $(this).find('.prod-select').val();
            if (!pid) return;
            items.push({
                product_id:   parseInt(pid),
                qty:          parseInt($(this).find('.item-qty').val()) || 1,
                unit_price:   Math.round((parseFloat($(this).find('.item-price').val()) || 0) * 100),
                discount_pct: parseFloat($(this).find('.item-disc').val()) || 0
            });
        });
        return items;
    }

    $(document).on('change input', '#order-items-body .item-qty, #order-items-body .item-price, #order-items-body .item-disc, #order-discount', recalcTotals);

    $(document).on('change', '#order-items-body .prod-select', function() {
        var pid  = $(this).val();
        var prod = window.productList.filter(function(p) { return p.id == pid; })[0];
        if (prod) $(this).closest('tr').find('.item-price').val((prod.price / 100).toFixed(2));
        recalcTotals();
    });

    $(document).on('click', '.btn-remove-item', function() {
        $(this).closest('tr').remove();
        recalcTotals();
    });

    $('#btn-add-item').click(function() { addItemRow(); });

    // ---- Open modal ----
    function openOrderModal(data) {
        var $f = $('#order-form');
        $f[0].reset();
        $('#order-id').val(data ? data.id : 0);
        $('#order-items-body').empty();
        recalcTotals();
        CRM.init_plugins($('#order-modal'));
        if (data) {
            $f.find('[name="customer_id"]').val(data.customer_id || '').trigger('change');
            $f.find('[name="delivery_date"]').datepicker('update', data.delivery_date || '');
            $f.find('[name="notes"]').val(data.notes || '');
            $('#order-discount').val(data.discount_inr || 0);
            if (data.items && data.items.length) {
                $.each(data.items, function(i, item) { addItemRow(item); });
            } else {
                addItemRow();
            }
            $('#order-modal .modal-title').text('Edit Order');
        } else {
            $f.find('[name="delivery_date"]').datepicker('update', '');
            addItemRow();
            $('#order-modal .modal-title').text('New Order');
        }
        $('#order-modal').modal('show');
    }

    $('#btn-add-order').click(function() { openOrderModal(null); });

    $(document).on('click', '.btn-edit-order', function() {
        $.getJSON(BASE_URL + 'orders/get/' + $(this).data('id'), function(res) {
            if (res.status !== 'success') { CRM.toast('error', res.message || 'Failed to load.'); return; }
            var o = res.data.order;
            openOrderModal({
                id:            o.id,
                customer_id:   o.customer_id,
                delivery_date: o.delivery_date || '',
                notes:         o.notes || '',
                discount_inr:  (o.discount_amount || 0) / 100,
                items: $.map(res.data.items || [], function(i) {
                    return { product_id: i.product_id, qty: i.qty, unit_price_inr: (i.unit_price || 0) / 100, discount_pct: i.discount_pct || 0 };
                })
            });
        });
    });

    // ---- Save ----
    function submitOrder(forApproval) {
        var items = serializeItems();
        if (!items.length) { CRM.toast('error', 'Add at least one product.'); return; }
        $('#order-items-json').val(JSON.stringify(items));
        if (forApproval) $('<input type="hidden" name="submit_for_approval" value="1">').appendTo('#order-form');

        var $btn = forApproval ? $('#btn-submit-approval') : $('#btn-save-draft');
        CRM.btn_loading($btn);
        $.ajax({
            url: BASE_URL + 'orders/save', method: 'POST',
            data: new FormData($('#order-form')[0]), processData: false, contentType: false,
            success: function(res) {
                if (res.status === 'success') {
                    CRM.toast('success', res.message);
                    $('#order-modal').modal('hide');
                    if (window.mainTable) window.mainTable.ajax.reload(null, false);
                } else {
                    CRM.toast('error', res.message);
                }
            },
            complete: function() {
                CRM.btn_reset($btn);
                $('#order-form [name="submit_for_approval"]').remove();
            }
        });
    }

    $('#btn-save-draft').click(function() { submitOrder(false); });
    $('#btn-submit-approval').click(function() { submitOrder(true); });

    $(document).on('click', '.btn-order-status', function() {
        var action = $(this).data('action'), id = $(this).data('id');
        CRM.handle_status(action, id, BASE_URL + 'orders/status', window.mainTable,
            action === 'delete' ? 'Delete this order?' : 'Are you sure?');
    });
});
