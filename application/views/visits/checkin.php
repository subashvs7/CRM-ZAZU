<section class="content-header">
    <h1>Check In <small>Field visit check-in</small></h1>
    <ol class="breadcrumb">
        <li><a href="<?= base_url('visits') ?>">Visits</a></li>
        <li class="active">Check In</li>
    </ol>
</section>
<section class="content">
<div class="row">
<div class="col-md-6 col-md-offset-3">

<div class="box box-success">
    <div class="box-header with-border">
        <h3 class="box-title"><i class="fa fa-sign-in"></i> Check In to Customer</h3>
    </div>
    <div class="box-body">

        <?php if(!empty($open_visit)): ?>
        <!-- Already checked in — show checkout prompt first -->
        <div class="alert alert-warning">
            <h4><i class="fa fa-exclamation-triangle"></i> Already Checked In</h4>
            <p>You have an active check-in that hasn't been checked out yet.</p>
            <strong>Check-in time:</strong> <?= date('d M Y H:i', strtotime($open_visit['check_in_at'])) ?>
        </div>
        <button type="button" class="btn btn-danger btn-block btn-lg" id="btn-do-checkout"
                data-id="<?= $open_visit['id'] ?>">
            <i class="fa fa-sign-out"></i> Check Out Now
        </button>
        <div id="checkout-loc-preview" class="alert alert-warning" style="display:none;margin-top:8px;font-size:13px"></div>
        <div id="checkout-map-preview" style="display:none;margin-top:8px;border-radius:4px;overflow:hidden"></div>
        <hr style="margin:20px 0">
        <p class="text-muted text-center"><small>Or check out first, then check in to another customer below.</small></p>
        <?php endif; ?>

        <?php if($plan): ?>
        <div class="alert alert-info">
            <i class="fa fa-info-circle"></i> Planned visit to <strong><?= esc_html($plan['customer_name']) ?></strong>
        </div>
        <?php endif; ?>

        <form id="checkin-form" method="POST" action="<?= base_url('visits/do_checkin') ?>">
            <input type="hidden" name="<?= $csrf_name ?>" value="<?= $csrf_hash ?>">
            <input type="hidden" name="visit_plan_id" value="<?= $plan['id'] ?? 0 ?>">
            <input type="hidden" name="latitude"  id="checkin-lat">
            <input type="hidden" name="longitude" id="checkin-lng">

            <!-- Customer Select -->
            <div class="form-group" id="customer-field-group">
                <label>Customer <span class="text-danger">*</span></label>
                <select name="customer_id" id="customer_id" class="form-control select2">
                    <option value="">— Select Customer —</option>
                    <?php foreach($customers as $c): ?>
                    <option value="<?= $c['id'] ?>"
                        <?= (isset($plan) && $plan['customer_id'] == $c['id']) ? 'selected' : '' ?>>
                        <?= esc_html($c['name']) ?>
                        <?php if($c['city']): ?> — <?= esc_html($c['city']) ?><?php endif; ?>
                    </option>
                    <?php endforeach; ?>
                </select>
                <span class="help-block err" id="customer-error" style="display:none">
                    <i class="fa fa-exclamation-circle"></i> Please select a customer.
                </span>
            </div>

            <!-- Location section -->
            <div class="form-group">
                <label><i class="fa fa-map-marker"></i> Location</label>
                <div id="checkin-location-status" class="alert alert-warning" style="margin-bottom:6px;font-size:13px">
                    <i class="fa fa-spinner fa-spin"></i> Detecting location automatically...
                </div>
                <button type="button" id="btn-redetect-location"
                        class="btn btn-default btn-sm btn-block">
                    <i class="fa fa-crosshairs"></i> Re-detect / Refresh Location
                </button>
                <div id="checkin-map-preview" style="display:none;margin-top:8px;border-radius:4px;overflow:hidden"></div>
            </div>

            <!-- Notes -->
            <div class="form-group">
                <label>Notes <small class="text-muted">(optional)</small></label>
                <textarea name="notes" class="form-control" rows="2"
                          placeholder="Add any notes about this visit..."></textarea>
            </div>

            <button type="submit" class="btn btn-success btn-block btn-lg" id="btn-checkin">
                <i class="fa fa-sign-in"></i> Check In Now
            </button>
        </form>

    </div><!-- /.box-body -->
</div><!-- /.box -->

</div>
</div>
</section>

<script>
// ── Location detection ─────────────────────────────────────────────
function doDetectCheckin() {
    var $status = $('#checkin-location-status');
    var $btn    = $('#btn-redetect-location');
    var $map    = $('#checkin-map-preview');

    CRM.btn_loading($btn);
    $status.removeClass('alert-success alert-danger').addClass('alert-warning')
           .html('<i class="fa fa-spinner fa-spin"></i> Detecting location...');
    $map.hide();

    if (!navigator.geolocation) {
        $status.removeClass('alert-warning').addClass('alert-danger')
               .html('<i class="fa fa-times"></i> Geolocation not supported by your browser.');
        CRM.btn_reset($btn);
        return;
    }

    navigator.geolocation.getCurrentPosition(
        function(pos) {
            var lat = pos.coords.latitude.toFixed(7);
            var lng = pos.coords.longitude.toFixed(7);
            var acc = Math.round(pos.coords.accuracy);

            $('#checkin-lat').val(lat);
            $('#checkin-lng').val(lng);

            $status.removeClass('alert-warning alert-danger').addClass('alert-success')
                   .html('<i class="fa fa-check-circle"></i> <strong>' + lat + ', ' + lng + '</strong>'
                       + ' <span class="text-muted">(±' + acc + 'm)</span>');

            var bbox = (parseFloat(lng) - 0.003) + '%2C' + (parseFloat(lat) - 0.003) + '%2C'
                     + (parseFloat(lng) + 0.003) + '%2C' + (parseFloat(lat) + 0.003);
            $map.html('<iframe src="https://www.openstreetmap.org/export/embed.html?bbox='
                + bbox + '&layer=mapnik&marker=' + lat + '%2C' + lng
                + '" style="width:100%;height:200px;border:0" loading="lazy"></iframe>')
                .slideDown(200);

            CRM.btn_reset($btn);
        },
        function(err) {
            var msgs = { 1: 'Permission denied.', 2: 'Position unavailable.', 3: 'Timed out.' };
            $status.removeClass('alert-warning').addClass('alert-danger')
                   .html('<i class="fa fa-exclamation-triangle"></i> '
                       + (msgs[err.code] || 'Location error.') + ' Tap Re-detect to retry.');
            CRM.btn_reset($btn);
        },
        { enableHighAccuracy: true, timeout: 12000, maximumAge: 0 }
    );
}

// Auto-detect on load + manual re-detect
doDetectCheckin();
$(document).on('click', '#btn-redetect-location', doDetectCheckin);

// ── Select2 init ───────────────────────────────────────────────────
CRM.init_plugins();

// Clear customer error when user makes a selection
$('#customer_id').on('change', function() {
    if ($(this).val()) {
        $('#customer-field-group').removeClass('has-error');
        $('#customer-error').hide();
    }
});

// ── Form submit ────────────────────────────────────────────────────
$('#checkin-form').on('submit', function(e) {
    e.preventDefault();

    // 1. Client-side: customer is required
    var custId = $('#customer_id').val();
    if (!custId || custId === '') {
        $('#customer-field-group').addClass('has-error');
        $('#customer-error').show();
        CRM.toast('error', 'Please select a customer before checking in.');
        return false;                     // stop — do NOT fire AJAX
    }
    $('#customer-field-group').removeClass('has-error');
    $('#customer-error').hide();

    // 2. Warn if no GPS (but allow)
    if (!$('#checkin-lat').val()) {
        if (!confirm('Location not captured yet.\n\nCheck in without location?')) return false;
    }

    // 3. Submit via AJAX
    var $btn = $('#btn-checkin');
    CRM.btn_loading($btn);

    $.ajax({
        url:         BASE_URL + 'visits/do_checkin',
        method:      'POST',
        data:        new FormData(this),
        processData: false,
        contentType: false,
        success: function(res) {
            if (res.status === 'success') {
                CRM.toast('success', res.message);
                setTimeout(function() { window.location = BASE_URL + 'visits'; }, 1000);
            } else {
                CRM.toast('error', res.message);
                CRM.btn_reset($btn);
            }
        },
        error: function(xhr) {
            var res = xhr.responseJSON || {};
            // Show field-level errors
            if (res.errors && res.errors.customer_id) {
                $('#customer-field-group').addClass('has-error');
                $('#customer-error').text(res.errors.customer_id).show();
            }
            CRM.toast('error', res.message || 'Check-in failed. Please try again.');
            CRM.btn_reset($btn);
        }
    });
});

// ── Checkout button (when open visit exists) ──────────────────────
$('#btn-do-checkout').on('click', function() {
    var id   = $(this).data('id');
    var $btn = $(this);
    var lat  = $('#checkin-lat').val();
    var lng  = $('#checkin-lng').val();

    CRM.btn_loading($btn);

    $.post(BASE_URL + 'visits/do_checkout/' + id, {
        latitude:  lat  || '',
        longitude: lng  || '',
        [CI3_CSRF_NAME]: CI3_CSRF_HASH
    })
    .done(function(res) {
        if (res.status === 'success') {
            CRM.toast('success', 'Checked out successfully.');
            // Reload page — open_visit is now gone, check-in form becomes active
            setTimeout(function() { location.reload(); }, 800);
        } else {
            CRM.toast('error', res.message);
            CRM.btn_reset($btn);
        }
    })
    .fail(function(xhr) {
        var res = xhr.responseJSON || {};
        CRM.toast('error', res.message || 'Checkout failed.');
        CRM.btn_reset($btn);
    });
});
</script>
