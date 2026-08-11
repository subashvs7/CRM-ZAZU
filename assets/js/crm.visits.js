/**
 * Visits module JS
 */
$(function() {
    if ($('#visits-table').length && !$.fn.DataTable.isDataTable('#visits-table')) {
        window.mainTable = $('#visits-table').DataTable({
            processing: true, serverSide: true,
            ajax: { 
                url: BASE_URL + 'visits/datatable', 
                data: function(d) { 
                    d.status_filter = window.currentStatusFilter || ''; 
                    d.staff_filter = $('#filter-staff').val() || '';
                    d.date_filter = $('#filter-date').val() || '';
                    d.customer_type = $('#filter-customer-type').val() || '';
                } 
            },
            columns: [{data:0},{data:1},{data:2},{data:3},{data:4},{data:5},{data:6,orderable:false}],
            order: [[3, 'desc']] // Date is now column index 3
        });

        // Trigger AJAX reload when filters change
        $('#filter-staff, #filter-date, #filter-customer-type').on('change', function() {
            window.mainTable.ajax.reload();
        });
    }

    if ($('#history-table').length && !$.fn.DataTable.isDataTable('#history-table')) {
        window.historyTable = $('#history-table').DataTable({
            processing: true, serverSide: true,
            ajax: { url: BASE_URL + 'visits/history_datatable' },
            columns: [{data:0},{data:1},{data:2},{data:3},{data:4},{data:5},{data:6},{data:7}],
            order: [[0, 'desc']]
        });
    }

    // Customer Type Tabs Logic
    function populateCustomers(type) {
        var $select = $('#plan-customer-select');
        $select.empty().append('<option value="">-- Select Customer --</option>');
        
        if (typeof all_customers !== 'undefined') {
            $.each(all_customers, function(i, c) {
                if (c.customer_type === type) {
                    $select.append('<option value="' + c.id + '">' + c.name + '</option>');
                }
            });
        }
        
        if ($select.hasClass('select2-hidden-accessible')) {
            $select.trigger('change');
        }
    }

    if ($('#plan-customer-select').length) {
        // Initial load
        populateCustomers('primary');

        $('#plan-customer-select').select2({
            placeholder: '-- Select Customer --',
            allowClear: true,
            width: '100%'
        });
        
        $('.customer-type-tab').click(function() {
            var type = $(this).data('type');
            $('#customer-type-selection').val(type);
            
            // UI update
            $('.customer-type-tab').removeClass('bg-white text-gray-800 shadow-sm').addClass('text-gray-500 hover:text-gray-700');
            $(this).removeClass('text-gray-500 hover:text-gray-700').addClass('bg-white text-gray-800 shadow-sm');
            
            populateCustomers(type);
        });
    }

    // Right-side inline "Plan New Visit" form save
    var _rightVisitSaving = false;
    $(document).off('click.rightvisitsave').on('click.rightvisitsave', '#btn-save-right-visit', function() {
        if (_rightVisitSaving) return;
        _rightVisitSaving = true;
        var $btn = $(this); CRM.btn_loading($btn);
        $.ajax({
            url: BASE_URL + 'visits/save', method: 'POST',
            data: new FormData($('#right-visit-form')[0]), processData: false, contentType: false,
            success: function(res) {
                if (res.status === 'success') {
                    CRM.toast('success', res.message);
                    $('#right-visit-form')[0].reset();
                    if (window.mainTable) window.mainTable.ajax.reload(null, false);
                } else {
                    CRM.show_errors($('#right-visit-form'), res.errors || {});
                    CRM.toast('error', res.message);
                }
            },
            complete: function() { CRM.btn_reset($btn); _rightVisitSaving = false; }
        });
    });

    function openModalWithLocation(modalId, formId, timeField, locField, mapIframe, plan_id, customer_id, $btnOriginal) {
        var $f = $('#' + formId);
        $f[0].reset();
        
        var now = new Date();
        $('#' + timeField).val(now.toLocaleDateString() + ' ' + now.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'}));
        
        if(formId === 'checkin-form') {
            $('#chk-in-plan-id').val(plan_id);
            $('#chk-in-customer-id').val(customer_id);
        }
        
        var oldHtml = $btnOriginal ? $btnOriginal.html() : '';
        if ($btnOriginal) $btnOriginal.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');
        
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(function(pos) {
                if ($btnOriginal) $btnOriginal.prop('disabled', false).html(oldHtml);
                var lat = pos.coords.latitude;
                var lng = pos.coords.longitude;
                $f.data('lat', lat);
                $f.data('lng', lng);
                $('#' + locField).val(lat.toFixed(5) + ', ' + lng.toFixed(5));
                
                var mapUrl = 'https://maps.google.com/maps?q=' + lat + ',' + lng + '&z=15&output=embed';
                $('#' + mapIframe).attr('src', mapUrl).removeClass('hidden');
                
                $('#' + modalId).modal('show');
            }, function() {
                if ($btnOriginal) $btnOriginal.prop('disabled', false).html(oldHtml);
                $('#' + locField).val('Location disabled');
                $('#' + mapIframe).addClass('hidden');
                $('#' + modalId).modal('show');
            });
        } else {
            if ($btnOriginal) $btnOriginal.prop('disabled', false).html(oldHtml);
            $('#' + locField).val('Not supported');
            $('#' + mapIframe).addClass('hidden');
            $('#' + modalId).modal('show');
        }
    }

    // Check-in trigger
    $(document).on('click', '.btn-checkin', function() {
        openModalWithLocation('checkin-modal', 'checkin-form', 'chk-in-time-display', 'chk-in-location-display', 'chk-in-map', $(this).data('id'), $(this).data('customer'), $(this));
    });
    
    // Check-in save
    $('#btn-save-checkin').click(function() {
        var $btn = $(this); CRM.btn_loading($btn);
        var fd = new FormData($('#checkin-form')[0]);
        var lat = $('#checkin-form').data('lat');
        var lng = $('#checkin-form').data('lng');
        if (lat) fd.append('latitude', lat);
        if (lng) fd.append('longitude', lng);
        
        $.ajax({
            url: BASE_URL + 'visits/do_checkin', method: 'POST', data: fd, processData: false, contentType: false,
            success: function(res) {
                if (res.status === 'success') {
                    CRM.toast('success', res.message);
                    $('#checkin-modal').modal('hide');
                    if (window.mainTable) window.mainTable.ajax.reload(null, false);
                } else {
                    CRM.toast('error', res.message);
                    if (res.errors && res.errors.open_log_id) {
                        $('#checkin-modal').modal('hide');
                        activeLogId = res.errors.open_log_id;
                        openModalWithLocation('checkout-modal', 'checkout-form', 'chk-out-time-display', 'chk-out-location-display', 'chk-out-map', null, null, null);
                    }
                }
            },
            complete: function() { CRM.btn_reset($btn); }
        });
    });

    // Check-out trigger
    var activeLogId = 0;
    $(document).on('click', '.btn-checkout', function() {
        activeLogId = $(this).data('log-id');
        openModalWithLocation('checkout-modal', 'checkout-form', 'chk-out-time-display', 'chk-out-location-display', 'chk-out-map', null, null, $(this));
    });
    
    // Check-out save
    $('#btn-save-checkout').click(function() {
        if (!$('[name="visit_outcome"]:checked').length) {
            CRM.toast('error', 'Please select a Status.');
            return;
        }
        var $btn = $(this); CRM.btn_loading($btn);
        var fd = new FormData($('#checkout-form')[0]);
        var lat = $('#checkout-form').data('lat');
        var lng = $('#checkout-form').data('lng');
        if (lat) fd.append('latitude', lat);
        if (lng) fd.append('longitude', lng);
        
        $.ajax({
            url: BASE_URL + 'visits/do_checkout/' + activeLogId, method: 'POST', data: fd, processData: false, contentType: false,
            success: function(res) {
                if (res.status === 'success') {
                    CRM.toast('success', res.message);
                    $('#checkout-modal').modal('hide');
                    if (window.mainTable) window.mainTable.ajax.reload(null, false);
                    if (window.historyTable) window.historyTable.ajax.reload(null, false);
                } else CRM.toast('error', res.message);
            },
            complete: function() { CRM.btn_reset($btn); }
        });
    });

    // Re-plan trigger
    $(document).on('click', '.btn-replan', function() {
        var id = $(this).data('id');
        var date = $(this).data('date');
        var time = $(this).data('time');
        
        $('#replan-form')[0].reset();
        $('#replan-plan-id').val(id);
        $('#replan-date').val(date);
        $('#replan-time').val(time);
        
        $('#replan-modal').modal('show');
    });

    // Re-plan save
    $('#btn-save-replan').click(function() {
        var $btn = $(this); 
        CRM.btn_loading($btn);
        
        $.ajax({
            url: BASE_URL + 'visits/save', 
            method: 'POST', 
            data: new FormData($('#replan-form')[0]), 
            processData: false, 
            contentType: false,
            success: function(res) {
                if (res.status === 'success') {
                    CRM.toast('success', 'Visit re-planned successfully.');
                    $('#replan-modal').modal('hide');
                    if (window.mainTable) window.mainTable.ajax.reload(null, false);
                } else {
                    CRM.toast('error', res.message);
                }
            },
            complete: function() { 
                CRM.btn_reset($btn); 
            }
        });
    });

});
