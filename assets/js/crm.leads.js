/**
 * Leads module JS
 */
$(function() {
    if ($('#leads-table').length && !$.fn.DataTable.isDataTable('#leads-table')) {
        window.mainTable = $('#leads-table').DataTable({
            processing: true, serverSide: true,
            ajax: { url: BASE_URL + 'leads/datatable', data: function(d) { d.status_filter = window.currentStatusFilter || ''; } },
            columns: [{data:0},{data:1},{data:2},{data:3},{data:4},{data:5},{data:6},{data:7},{data:8},{data:9},{data:10,orderable:false}],
            order: [[0, 'desc']]
        });
    }

    $('#btn-add-lead').click(function() {
        $('#lead-form')[0].reset();
        $('#lead-id').val(0);
        CRM.clear_errors($('#lead-form'));
        $('#lead-modal').modal('show');
        CRM.init_plugins($('#lead-modal'));
    });

    $(document).on('click', '.btn-edit-lead', function() {
        var id = $(this).data('id');
        $.getJSON(BASE_URL + 'leads/get/' + id, function(res) {
            var l = res.data;
            $('#lead-id').val(l.id);
            $.each(['title','description','source','lead_status','expected_close_date'], function(i,f) {
                $('[name="'+f+'"]').val(l[f]||'');
            });
            $('[name="expected_value"]').val(l.expected_value ? (l.expected_value / 100).toFixed(2) : '');
            $('[name="customer_id"]').val(l.customer_id).trigger('change');
            if (l.assigned_to) $('[name="assigned_to"]').val(l.assigned_to).trigger('change');
            CRM.clear_errors($('#lead-form'));
            $('#lead-modal').modal('show');
            CRM.init_plugins($('#lead-modal'));
        });
    });

    $('#btn-save-lead').click(function() {
        var $btn = $(this); CRM.btn_loading($btn);
        $.ajax({
            url: BASE_URL + 'leads/save', method: 'POST',
            data: new FormData($('#lead-form')[0]), processData: false, contentType: false,
            success: function(res) {
                if (res.status === 'success') {
                    CRM.toast('success', res.message);
                    $('#lead-modal').modal('hide');
                    if (window.mainTable) window.mainTable.ajax.reload(null, false);
                } else {
                    CRM.show_errors($('#lead-form'), res.errors || {});
                    CRM.toast('error', res.message);
                }
            },
            complete: function() { CRM.btn_reset($btn); }
        });
    });

    $(document).on('click', '.btn-lead-status', function() {
        var action = $(this).data('action'), id = $(this).data('id');
        CRM.handle_status(action, id, BASE_URL + 'leads/status', window.mainTable,
            (action === 'delete' ? 'Delete this lead?' : 'Are you sure?'));
    });
});
