/**
 * Visits module JS
 */
$(function() {
    if ($('#visits-table').length && !$.fn.DataTable.isDataTable('#visits-table')) {
        window.mainTable = $('#visits-table').DataTable({
            processing: true, serverSide: true,
            ajax: { url: BASE_URL + 'visits/datatable', data: function(d) { d.status_filter = window.currentStatusFilter || ''; } },
            columns: [{data:0},{data:1},{data:2},{data:3},{data:4},{data:5},{data:6},{data:7},{data:8,orderable:false}],
            order: [[1, 'desc']]
        });
    }

    $('#btn-plan-visit').click(function() {
        $('#visit-form')[0].reset();
        $('#visit-id').val(0);
        $('#visit-modal').modal('show');
        CRM.init_plugins($('#visit-modal'));
    });

    $('#btn-save-visit').click(function() {
        var $btn = $(this); CRM.btn_loading($btn);
        $.ajax({
            url: BASE_URL + 'visits/save', method: 'POST',
            data: new FormData($('#visit-form')[0]), processData: false, contentType: false,
            success: function(res) {
                if (res.status === 'success') {
                    CRM.toast('success', res.message);
                    $('#visit-modal').modal('hide');
                    if (window.mainTable) window.mainTable.ajax.reload(null, false);
                } else {
                    CRM.show_errors($('#visit-form'), res.errors || {});
                    CRM.toast('error', res.message);
                }
            },
            complete: function() { CRM.btn_reset($btn); }
        });
    });

    $(document).on('click', '.btn-visit-status', function() {
        var action = $(this).data('action'), id = $(this).data('id');
        CRM.handle_status(action, id, BASE_URL + 'visits/status', window.mainTable);
    });
});
