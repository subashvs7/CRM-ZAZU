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

    function openLeadModal(data) {
        var $f = $('#lead-form');
        $f[0].reset();
        CRM.clear_errors($f);
        $('#lead-id').val(data ? data.id : 0);
        CRM.init_plugins($('#lead-modal'));
        if (data) {
            $f.find('[name="title"]').val(data.title || '');
            $f.find('[name="description"]').val(data.description || '');
            $f.find('[name="source"]').val(data.source || '');
            $f.find('[name="lead_status"]').val(data.lead_status || 'new');
            $f.find('[name="expected_value"]').val(data.expected_value ? (data.expected_value / 100).toFixed(2) : '');
            $f.find('[name="expected_close_date"]').datepicker('update', data.expected_close_date || '');
            $f.find('[name="customer_id"]').val(data.customer_id || '').trigger('change');
            if ($f.find('[name="assigned_to"]').length) $f.find('[name="assigned_to"]').val(data.assigned_to || '').trigger('change');
            $('#lead-modal .modal-title').text('Edit Lead');
        } else {
            $f.find('[name="expected_close_date"]').datepicker('update', '');
            $('#lead-modal .modal-title').text('Add Lead');
        }
        $('#lead-modal').modal('show');
    }

    $('#btn-add-lead').click(function() { openLeadModal(null); });

    $(document).on('click', '.btn-edit-lead', function() {
        $.getJSON(BASE_URL + 'leads/get/' + $(this).data('id'), function(res) {
            if (res.status === 'success') openLeadModal(res.data);
            else CRM.toast('error', res.message || 'Failed to load.');
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
            action === 'delete' ? 'Delete this lead?' : 'Are you sure?');
    });
});
