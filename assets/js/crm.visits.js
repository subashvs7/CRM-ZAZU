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

    function openVisitModal(data) {
        var $f = $('#visit-form');
        $f[0].reset();
        CRM.clear_errors($f);
        $('#visit-id').val(data ? data.id : 0);
        // Init plugins BEFORE setting values so Select2 instances are ready to receive .trigger('change')
        CRM.init_plugins($('#visit-modal'));
        if (data) {
            $f.find('[name="customer_id"]').val(data.customer_id || '').trigger('change');
            // Use datepicker('setDate') to properly sync the calendar state
            var $pd = $f.find('[name="planned_date"]');
            if (data.planned_date) {
                var dp = data.planned_date.split('-');
                $pd.datepicker('setDate', new Date(dp[0], dp[1] - 1, dp[2]));
            } else {
                $pd.datepicker('setDate', null);
            }
            // DB returns HH:MM:SS — <input type="time"> needs HH:MM
            $f.find('[name="planned_time"]').val(data.planned_time ? data.planned_time.substr(0, 5) : '');
            $f.find('[name="purpose"]').val(data.purpose || '');
            if ($f.find('[name="user_id"]').length) $f.find('[name="user_id"]').val(data.user_id || '').trigger('change');
            $('#visit-modal .modal-title').text('Edit Visit');
        } else {
            $f.find('[name="planned_date"]').datepicker('setDate', null);
            $('#visit-modal .modal-title').text('Plan Visit');
        }
        $('#visit-modal').modal('show');
    }

    $('#btn-plan-visit').click(function() { openVisitModal(null); });

    $(document).on('click', '.btn-edit-visit', function() {
        $.getJSON(BASE_URL + 'visits/get/' + $(this).data('id'), function(res) {
            if (res.status === 'success') openVisitModal(res.data);
            else CRM.toast('error', res.message || 'Failed to load.');
        });
    });

    var _visitSaving = false;
    $(document).off('click.visitsave').on('click.visitsave', '#btn-save-visit', function() {
        if (_visitSaving) return;
        _visitSaving = true;
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
            complete: function() { CRM.btn_reset($btn); _visitSaving = false; }
        });
    });

    $(document).on('click', '.btn-visit-status', function() {
        var action = $(this).data('action'), id = $(this).data('id');
        CRM.handle_status(action, id, BASE_URL + 'visits/status', window.mainTable,
            action === 'delete' ? 'Delete this visit plan?' : 'Are you sure?');
    });
});
