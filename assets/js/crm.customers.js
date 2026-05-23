/**
 * Customers module JS
 */
$(function() {
    // Guard prevents double-init if somehow loaded twice
    if ($('#customers-table').length && !$.fn.DataTable.isDataTable('#customers-table')) {
        window.mainTable = $('#customers-table').DataTable({
            processing: true, serverSide: true,
            ajax: { url: BASE_URL + 'customers/datatable', data: function(d) { d.status_filter = window.currentStatusFilter || ''; } },
            columns: [{data:0},{data:1},{data:2},{data:3},{data:4},{data:5},{data:6},{data:7},{data:8},{data:9,orderable:false}],
            order: [[0, 'desc']]
        });
    }

    $('#btn-add-customer').click(function() {
        $('#customer-form')[0].reset();
        $('#customer-id').val(0);
        $('#customer-modal-title').text('Add Customer');
        CRM.clear_errors($('#customer-form'));
        $('#customer-modal').modal('show');
        CRM.init_plugins($('#customer-modal'));
    });

    $(document).on('click', '.btn-edit', function() {
        var id = $(this).data('id');
        $.getJSON(BASE_URL + 'customers/get/' + id, function(res) {
            var c = res.data;
            $('#customer-id').val(c.id);
            $.each(['name','phone','email','address','city','state','pincode','gst_number','notes','latitude','longitude'], function(i, f) {
                $('[name="'+f+'"]').val(c[f] || '');
            });
            if (c.assigned_to) $('[name="assigned_to"]').val(c.assigned_to).trigger('change');
            $('#customer-modal-title').text('Edit Customer');
            $('#customer-modal').modal('show');
            CRM.init_plugins($('#customer-modal'));
        });
    });

    $('#btn-save-customer').click(function() {
        var $btn = $(this); CRM.btn_loading($btn);
        $.ajax({
            url: BASE_URL + 'customers/save', method: 'POST',
            data: new FormData($('#customer-form')[0]), processData: false, contentType: false,
            success: function(res) {
                if (res.status === 'success') {
                    CRM.toast('success', res.message);
                    $('#customer-modal').modal('hide');
                    if (window.mainTable) window.mainTable.ajax.reload(null, false);
                } else {
                    CRM.show_errors($('#customer-form'), res.errors || {});
                    CRM.toast('error', res.message);
                }
            },
            complete: function() { CRM.btn_reset($btn); }
        });
    });

    $(document).on('click', '.btn-deactivate', function() {
        CRM.handle_status('deactivate', $(this).data('id'), BASE_URL + 'customers/status', window.mainTable, 'Deactivate this customer?');
    });
    $(document).on('click', '.btn-activate', function() {
        CRM.handle_status('activate', $(this).data('id'), BASE_URL + 'customers/status', window.mainTable);
    });
    $(document).on('click', '.btn-delete', function() {
        CRM.handle_status('delete', $(this).data('id'), BASE_URL + 'customers/status', window.mainTable, 'Delete this customer?');
    });
    $(document).on('click', '.btn-restore', function() {
        CRM.handle_status('restore', $(this).data('id'), BASE_URL + 'customers/status', window.mainTable);
    });
});
