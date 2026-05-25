/**
 * Geofence module JS
 */
$(function() {
    if ($('#zones-table').length && !$.fn.DataTable.isDataTable('#zones-table')) {
        window.mainTable = $('#zones-table').DataTable({
            processing: true, serverSide: true,
            ajax: { url: BASE_URL + 'geofence/datatable', data: function(d) { d.status_filter = window.currentStatusFilter || ''; } },
            columns: [{data:0},{data:1},{data:2},{data:3},{data:4},{data:5},{data:6},{data:7},{data:8,orderable:false}],
            order: [[0, 'desc']]
        });
    }

    $('#btn-add-zone').click(function() {
        $('#zone-form')[0].reset();
        $('#zone-id').val(0);
        CRM.clear_errors($('#zone-form'));
        $('#zone-modal').modal('show');
        CRM.init_plugins($('#zone-modal'));
    });

    $(document).on('click', '.btn-edit-zone', function() {
        var id = $(this).data('id');
        $.getJSON(BASE_URL + 'geofence/get/' + id, function(res) {
            var z = res.data;
            $('#zone-id').val(z.id);
            $.each(['name','zone_type','center_lat','center_lng','radius_meters'], function(i, f) {
                $('[name="'+f+'"]').val(z[f] || '');
            });
            $('[name="auto_checkin"]').prop('checked',    z.auto_checkin   == 1);
            $('[name="alert_on_enter"]').prop('checked',  z.alert_on_enter == 1);
            $('[name="alert_on_exit"]').prop('checked',   z.alert_on_exit  == 1);
            if (z.customer_id) $('[name="customer_id"]').val(z.customer_id).trigger('change');
            CRM.clear_errors($('#zone-form'));
            $('#zone-modal').modal('show');
            CRM.init_plugins($('#zone-modal'));
        });
    });

    var _zoneSaving = false;
    $(document).off('click.zonesave').on('click.zonesave', '#btn-save-zone', function() {
        if (_zoneSaving) return;
        _zoneSaving = true;
        var $btn = $(this); CRM.btn_loading($btn);
        $.ajax({
            url: BASE_URL + 'geofence/save', method: 'POST',
            data: new FormData($('#zone-form')[0]), processData: false, contentType: false,
            success: function(res) {
                if (res.status === 'success') {
                    CRM.toast('success', res.message);
                    $('#zone-modal').modal('hide');
                    if (window.mainTable) window.mainTable.ajax.reload(null, false);
                } else {
                    CRM.show_errors($('#zone-form'), res.errors || {});
                    CRM.toast('error', res.message);
                }
            },
            complete: function() { CRM.btn_reset($btn); _zoneSaving = false; }
        });
    });

    $(document).on('click', '.btn-zone-status', function() {
        var action = $(this).data('action'), id = $(this).data('id');
        CRM.handle_status(action, id, BASE_URL + 'geofence/status', window.mainTable);
    });
});
