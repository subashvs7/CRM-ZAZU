/**
 * CRM Core JS — Global AJAX setup, UI helpers, utilities
 */

$.ajaxSetup({
    headers: {'X-Requested-With': 'XMLHttpRequest'},
    error: function(xhr) {
        var res = xhr.responseJSON || {};
        if (xhr.status === 401) window.location = BASE_URL + 'auth/login';
        else if (xhr.status === 403) CRM.toast('error', res.message || 'Access denied');
        else CRM.toast('error', res.message || 'Server error. Try again.');
    }
});

var CRM = {

    toast: function(type, msg) {
        toastr[type](msg);
    },

    btn_loading: function($btn) {
        $btn.data('orig', $btn.html()).html('<i class="fa fa-spinner fa-spin"></i> Processing...').prop('disabled', true);
    },

    btn_reset: function($btn) {
        $btn.html($btn.data('orig')).prop('disabled', false);
    },

    load_modal: function(url, modal_id) {
        modal_id = modal_id || '#ajax-modal';
        $(modal_id + ' .modal-content').html('<div class="text-center p-4" style="padding:40px"><i class="fa fa-spinner fa-spin fa-2x"></i></div>');
        $(modal_id).modal('show');
        $.get(url, function(html) {
            $(modal_id + ' .modal-content').html(html);
            CRM.init_plugins($(modal_id + ' .modal-content'));
        });
    },

    submit_form: function($form, callback) {
        var $btn = $form.find('[type=submit]');
        CRM.btn_loading($btn);
        $.ajax({
            url: $form.attr('action'),
            method: $form.attr('method') || 'POST',
            data: new FormData($form[0]),
            processData: false,
            contentType: false,
            success: function(res) {
                if (res.status === 'success') {
                    CRM.toast('success', res.message);
                    CRM.btn_reset($btn);   // always reset — even when no callback
                    if (callback) callback(res);
                } else {
                    CRM.show_errors($form, res.errors || {});
                    CRM.toast('error', res.message);
                    CRM.btn_reset($btn);
                }
            },
            // Handle 4xx/5xx: parse JSON body for field errors + show toast
            error: function(xhr) {
                var res = xhr.responseJSON || {};
                if (res.errors && Object.keys(res.errors).length) {
                    CRM.show_errors($form, res.errors);
                }
                CRM.toast('error', res.message || 'Request failed. Please try again.');
                CRM.btn_reset($btn);
            }
        });
    },

    show_errors: function($form, errors) {
        $form.find('.has-error').removeClass('has-error');
        $form.find('.help-block.err').remove();
        $.each(errors, function(field, msg) {
            $form.find('[name="' + field + '"]').closest('.form-group')
                .addClass('has-error')
                .append('<span class="help-block err">' + msg + '</span>');
        });
    },

    clear_errors: function($form) {
        $form.find('.has-error').removeClass('has-error');
        $form.find('.help-block.err').remove();
    },

    status_badge: function(s) {
        var m = {
            active:   '<span class="label label-success">Active</span>',
            inactive: '<span class="label label-warning">Inactive</span>',
            deleted:  '<span class="label label-danger">Deleted</span>'
        };
        return m[s] || '<span class="label label-default">' + CRM.esc(s) + '</span>';
    },

    lead_badge: function(s) {
        var m = {
            new:         '<span class="label label-default">New</span>',
            contacted:   '<span class="label label-info">Contacted</span>',
            qualified:   '<span class="label label-primary">Qualified</span>',
            proposal:    '<span class="label label-warning">Proposal</span>',
            negotiation: '<span class="label label-warning" style="background:#e67e22">Negotiation</span>',
            won:         '<span class="label label-success">Won</span>',
            lost:        '<span class="label label-danger">Lost</span>'
        };
        return m[s] || s;
    },

    order_badge: function(s) {
        var m = {
            draft:            '<span class="label label-default">Draft</span>',
            pending_approval: '<span class="label label-warning">Pending Approval</span>',
            approved:         '<span class="label label-success">Approved</span>',
            dispatched:       '<span class="label label-info">Dispatched</span>',
            delivered:        '<span class="label label-success" style="background:#1abc9c">Delivered</span>',
            cancelled:        '<span class="label label-danger">Cancelled</span>'
        };
        return m[s] || s;
    },

    att_badge: function(s) {
        var m = {
            present:   '<span class="label label-success">Present</span>',
            absent:    '<span class="label label-danger">Absent</span>',
            half_day:  '<span class="label label-warning">Half Day</span>',
            on_leave:  '<span class="label label-info">On Leave</span>',
            holiday:   '<span class="label label-primary">Holiday</span>',
            week_off:  '<span class="label label-default">Week Off</span>'
        };
        return m[s] || '<span class="label label-default">' + s + '</span>';
    },

    action_btns: function(id, resource, status, opts) {
        opts = opts || {};
        var b = '<div class="btn-group">';
        if (opts.view)  b += '<a href="' + BASE_URL + resource + '/detail/' + id + '" class="btn btn-xs btn-info"><i class="fa fa-eye"></i></a> ';
        if (opts.edit && status !== 'deleted') b += '<button class="btn btn-xs btn-primary btn-edit" data-id="' + id + '"><i class="fa fa-pencil"></i></button> ';
        if (status === 'active')   b += '<button class="btn btn-xs btn-warning btn-deactivate" data-id="' + id + '"><i class="fa fa-ban"></i></button> <button class="btn btn-xs btn-danger btn-delete" data-id="' + id + '"><i class="fa fa-trash"></i></button>';
        if (status === 'inactive') b += '<button class="btn btn-xs btn-success btn-activate" data-id="' + id + '"><i class="fa fa-check"></i></button> <button class="btn btn-xs btn-danger btn-delete" data-id="' + id + '"><i class="fa fa-trash"></i></button>';
        if (status === 'deleted')  b += '<button class="btn btn-xs btn-success btn-restore" data-id="' + id + '"><i class="fa fa-undo"></i></button>';
        return b + '</div>';
    },

    handle_status: function(action, id, url, table, msg) {
        if (action === 'delete' || action === 'deactivate') {
            if (!confirm(msg || 'Are you sure?')) return;
        }
        $.post(url, {id: id, action: action, [CI3_CSRF_NAME]: CI3_CSRF_HASH}, function(res) {
            if (res.status === 'success') {
                CRM.toast('success', res.message);
                if (table && table.ajax) table.ajax.reload(null, false);
            }
        });
    },

    init_plugins: function(ctx) {
        $('select.select2', ctx || document).select2({width: '100%'});
        $('.datepicker', ctx || document).datepicker({format: 'yyyy-mm-dd', autoclose: true, todayHighlight: true});
    },

    format_inr: function(paise) {
        var inr = (paise || 0) / 100;
        return '₹' + inr.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    },

    time_ago: function(dt) {
        if (!dt) return '';
        var diff = Math.floor((Date.now() - new Date(dt)) / 1000);
        if (diff < 60)    return diff + 's ago';
        if (diff < 3600)  return Math.floor(diff / 60) + 'm ago';
        if (diff < 86400) return Math.floor(diff / 3600) + 'h ago';
        return Math.floor(diff / 86400) + 'd ago';
    },

    esc: function(s) {
        if (s === null || s === undefined) return '';
        return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }
};

// CSRF auto-refresh after every POST
$(document).ajaxComplete(function(e, xhr) {
    var t = xhr.getResponseHeader('X-CSRF-Token');
    if (t) {
        CI3_CSRF_HASH = t;
        $('[name="' + CI3_CSRF_NAME + '"]').val(t);
    }
});

// Toastr default options
toastr.options = {
    positionClass: 'toast-top-right',
    timeOut: 4000,
    progressBar: true,
    closeButton: true
};

// ── Location Detection ────────────────────────────────────────────────────────
CRM.detect_location = function(opts) {
    /**
     * opts = {
     *   lat_selector:      CSS selector for latitude input  (default '[name="latitude"]')
     *   lng_selector:      CSS selector for longitude input (default '[name="longitude"]')
     *   feedback_selector: CSS selector for feedback element (default '#location-feedback')
     *   map_selector:      CSS selector for map preview container (default '#map-preview')
     *   btn:               jQuery button element (optional, for loading state)
     *   context:           DOM context to scope selectors (default document)
     * }
     */
    opts = $.extend({
        lat_selector:      '[name="latitude"]',
        lng_selector:      '[name="longitude"]',
        feedback_selector: '#location-feedback',
        map_selector:      '#map-preview',
        btn:               null,
        context:           document
    }, opts || {});

    var ctx  = opts.context;
    var $lat = $(opts.lat_selector, ctx);
    var $lng = $(opts.lng_selector, ctx);
    var $fb  = $(opts.feedback_selector, ctx);
    var $map = $(opts.map_selector, ctx);
    var $btn = opts.btn;

    if ($btn) CRM.btn_loading($btn);
    $fb.html('<span class="text-muted"><i class="fa fa-spinner fa-spin"></i> Detecting location...</span>');
    $map.hide();

    if (!navigator.geolocation) {
        $fb.html('<span class="text-danger"><i class="fa fa-times"></i> Geolocation is not supported by your browser.</span>');
        if ($btn) CRM.btn_reset($btn);
        return;
    }

    navigator.geolocation.getCurrentPosition(
        function(pos) {
            var lat = pos.coords.latitude.toFixed(7);
            var lng = pos.coords.longitude.toFixed(7);
            var acc = Math.round(pos.coords.accuracy);

            $lat.val(lat).trigger('change');
            $lng.val(lng).trigger('change');

            $fb.html(
                '<span class="text-success"><i class="fa fa-check-circle"></i> ' +
                'Location detected: <strong>' + lat + ', ' + lng + '</strong>' +
                ' <span class="text-muted">(±' + acc + 'm accuracy)</span>' +
                '</span> ' +
                '<a href="https://www.openstreetmap.org/?mlat=' + lat + '&mlon=' + lng + '#map=16/' + lat + '/' + lng + '" target="_blank" class="text-muted" style="font-size:11px"><i class="fa fa-external-link"></i> View on map</a>'
            );

            // Embedded OpenStreetMap preview (no API key required)
            var bbox = [
                parseFloat(lng) - 0.005,
                parseFloat(lat) - 0.005,
                parseFloat(lng) + 0.005,
                parseFloat(lat) + 0.005
            ].join('%2C');
            var mapUrl = 'https://www.openstreetmap.org/export/embed.html?bbox=' + bbox +
                '&layer=mapnik&marker=' + lat + '%2C' + lng;
            $map.html(
                '<iframe src="' + mapUrl + '" style="width:100%;height:220px;border:0" loading="lazy" allowfullscreen></iframe>'
            ).slideDown(200);

            if ($btn) CRM.btn_reset($btn);
        },
        function(err) {
            var msgs = {
                1: 'Permission denied — please allow location access in your browser.',
                2: 'Position unavailable. Try again or enter coordinates manually.',
                3: 'Request timed out. Try again.'
            };
            $fb.html('<span class="text-danger"><i class="fa fa-exclamation-triangle"></i> ' + (msgs[err.code] || 'Unknown error.') + '</span>');
            if ($btn) CRM.btn_reset($btn);
        },
        { enableHighAccuracy: true, timeout: 12000, maximumAge: 0 }
    );
};

// ── Global click handler for .btn-detect-location buttons ───────────────────
$(document).on('click', '.btn-detect-location', function(e) {
    e.preventDefault();
    var ctx = $(this).closest('.location-picker-group').length
        ? $(this).closest('.location-picker-group')[0]
        : $(this).closest('form')[0] || document;
    CRM.detect_location({
        btn:               $(this),
        context:           ctx,
        lat_selector:      $(this).data('lat') || '[name="latitude"]',
        lng_selector:      $(this).data('lng') || '[name="longitude"]',
        feedback_selector: $(this).data('feedback') || '#location-feedback',
        map_selector:      $(this).data('map')      || '#map-preview'
    });
});

// Global status tab handler (used by all list pages)
$(document).on('click', '#status-tabs a', function(e) {
    e.preventDefault();
    $('#status-tabs li').removeClass('active');
    $(this).parent().addClass('active');
    window.currentStatusFilter = $(this).data('status');
    $('#deleted-banner').toggle(window.currentStatusFilter === 'deleted');
    if (window.mainTable && window.mainTable.ajax) {
        window.mainTable.ajax.reload();
    }
});
