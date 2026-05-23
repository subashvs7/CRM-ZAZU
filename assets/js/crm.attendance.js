/**
 * Attendance module JS
 */
$(function() {
    // Attendance index page — init only if NOT already initialised
    if ($('#att-table').length && !$.fn.DataTable.isDataTable('#att-table')) {
        window.mainTable = $('#att-table').DataTable({
            processing:  true,
            serverSide:  true,
            ajax: {
                url:  BASE_URL + 'attendance/datatable',
                data: function(d) { d.status_filter = window.currentStatusFilter || ''; }
            },
            columns: [
                {data:0}, {data:1}, {data:2}, {data:3}, {data:4},
                {data:5}, {data:6}, {data:7}, {data:8}, {data:9, orderable:false}
            ],
            order: [[1, 'desc']]
        });
    }

    // ---- Regularize button ----
    $(document).on('click', '.btn-regularize', function() {
        var $btn = $(this);
        $('#reg-att-id').val($btn.data('id'));
        $('#reg-att-date').text($btn.data('date') || '—');
        $('#reg-in-display').text($btn.data('in') || '—');
        $('#reg-out-display').text($btn.data('out') || '—');
        $('#reg-corrected-in').val($btn.data('in') || '');
        $('#reg-corrected-out').val($btn.data('out') || '');
        $('#reg-corrected-date').val('');
        $('#reg-reason').val('');
        CRM.init_plugins($('#regularize-modal'));
        $('#regularize-modal').modal('show');
    });

    $('#btn-save-regularize').click(function() {
        var reason = $.trim($('#reg-reason').val());
        if (!reason) { CRM.toast('error', 'Please enter a reason.'); return; }
        var $btn = $(this); CRM.btn_loading($btn);
        $.post(BASE_URL + 'attendance/request_correction', {
            id:             $('#reg-att-id').val(),
            corrected_date: $('#reg-corrected-date').val(),
            corrected_in:   $('#reg-corrected-in').val(),
            corrected_out:  $('#reg-corrected-out').val(),
            reason:         reason,
            [CI3_CSRF_NAME]: CI3_CSRF_HASH
        }, function(res) {
            if (res.status === 'success') {
                CRM.toast('success', res.message);
                $('#regularize-modal').modal('hide');
                if (window.mainTable) window.mainTable.ajax.reload(null, false);
            } else {
                CRM.toast('error', res.message || 'Failed.');
            }
        }).always(function() { CRM.btn_reset($btn); });
    });
});
