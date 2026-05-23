<section class="content-header">
    <h1>Punch In / Out</h1>
    <ol class="breadcrumb"><li><a href="<?= base_url('attendance') ?>">Attendance</a></li><li class="active">Punch</li></ol>
</section>
<section class="content">
<div class="row"><div class="col-md-6 col-md-offset-3">
<div class="box box-primary">
    <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-clock-o"></i> Today's Attendance</h3></div>
    <div class="box-body text-center">
        <h3 id="current-time" class="text-primary" style="font-size:48px;font-family:monospace;letter-spacing:2px"></h3>
        <p class="text-muted"><?= date('l, d F Y') ?></p>

        <!-- Location Detection Block -->
        <div id="location-box" class="alert alert-warning text-left" style="font-size:13px">
            <i class="fa fa-spinner fa-spin"></i> Detecting location...
        </div>
        <button type="button" id="btn-redetect-punch-loc" class="btn btn-xs btn-default" style="margin-top:-6px;margin-bottom:10px">
            <i class="fa fa-crosshairs"></i> Re-detect Location
        </button>
        <div id="punch-map-preview" style="display:none;margin-bottom:12px;border-radius:4px;overflow:hidden"></div>

        <input type="hidden" id="punch-lat">
        <input type="hidden" id="punch-lng">

        <?php if ($att && $att['punch_in_at'] && !$att['punch_out_at']): ?>
        <div class="alert alert-info">
            <i class="fa fa-check-circle"></i> Punched in at <strong><?= date('H:i', strtotime($att['punch_in_at'])) ?></strong>
            <?php if($att['punch_in_lat']): ?>
            &nbsp;<small class="text-muted">(<?= round($att['punch_in_lat'],4) ?>, <?= round($att['punch_in_lng'],4) ?>)</small>
            <?php endif; ?>
        </div>
        <button class="btn btn-danger btn-lg btn-block" id="btn-punch-out">
            <i class="fa fa-sign-out"></i> Punch Out
        </button>

        <?php elseif (!$att || !$att['punch_in_at']): ?>
        <div class="alert alert-warning"><i class="fa fa-exclamation-circle"></i> Not punched in yet today</div>
        <button class="btn btn-success btn-lg btn-block" id="btn-punch-in">
            <i class="fa fa-sign-in"></i> Punch In
        </button>

        <?php else: ?>
        <div class="alert alert-success">
            <i class="fa fa-check-circle"></i>
            Punched in: <strong><?= date('H:i', strtotime($att['punch_in_at'])) ?></strong>
            &mdash; Out: <strong><?= date('H:i', strtotime($att['punch_out_at'])) ?></strong>
        </div>
        <div class="alert alert-info">Working hours: <strong><?= $att['working_hours'] ?>h</strong></div>
        <?php endif; ?>
    </div>
</div>
</div></div>
</section>
<script>
// Clock
function tick() { $('#current-time').text(new Date().toLocaleTimeString('en-IN',{hour:'2-digit',minute:'2-digit',second:'2-digit',hour12:false})); }
tick(); setInterval(tick, 1000);

// Location detection
function detectPunchLocation() {
    var $box = $('#location-box');
    var $btn = $('#btn-redetect-punch-loc');
    CRM.btn_loading($btn);
    $box.removeClass('alert-success alert-danger').addClass('alert-warning')
        .html('<i class="fa fa-spinner fa-spin"></i> Detecting location...');
    $('#punch-map-preview').hide();

    if (!navigator.geolocation) {
        $box.removeClass('alert-warning').addClass('alert-danger')
            .html('<i class="fa fa-times"></i> Geolocation not supported.');
        CRM.btn_reset($btn);
        return;
    }

    navigator.geolocation.getCurrentPosition(function(pos) {
        var lat = pos.coords.latitude.toFixed(7);
        var lng = pos.coords.longitude.toFixed(7);
        var acc = Math.round(pos.coords.accuracy);

        $('#punch-lat').val(lat);
        $('#punch-lng').val(lng);

        $box.removeClass('alert-warning alert-danger').addClass('alert-success text-left')
            .html('<i class="fa fa-check-circle"></i> <strong>' + lat + ', ' + lng + '</strong>' +
                  ' <small class="text-muted">(±' + acc + 'm)</small>');

        var bbox = (parseFloat(lng)-0.003) + '%2C' + (parseFloat(lat)-0.003) + '%2C' +
                   (parseFloat(lng)+0.003) + '%2C' + (parseFloat(lat)+0.003);
        $('#punch-map-preview')
            .html('<iframe src="https://www.openstreetmap.org/export/embed.html?bbox=' + bbox +
                  '&layer=mapnik&marker=' + lat + '%2C' + lng +
                  '" style="width:100%;height:180px;border:0" loading="lazy"></iframe>')
            .slideDown(200);

        CRM.btn_reset($btn);
    }, function(err) {
        var msgs = {1:'Permission denied.', 2:'Position unavailable.', 3:'Timed out.'};
        $box.removeClass('alert-warning').addClass('alert-danger')
            .html('<i class="fa fa-exclamation-triangle"></i> ' + (msgs[err.code] || 'Error.') + ' Tap Re-detect to retry.');
        CRM.btn_reset($btn);
    }, { enableHighAccuracy: true, timeout: 12000, maximumAge: 0 });
}

// Auto-detect on load
detectPunchLocation();
$('#btn-redetect-punch-loc').click(detectPunchLocation);

function doPunch(type) {
    var $btn = type === 'in' ? $('#btn-punch-in') : $('#btn-punch-out');
    if (!$('#punch-lat').val()) {
        if (!confirm('Location not captured. Punch ' + type + ' anyway?')) return;
    }
    CRM.btn_loading($btn);
    $.post(BASE_URL + 'attendance/do_punch', {
        type:      type,
        latitude:  $('#punch-lat').val(),
        longitude: $('#punch-lng').val(),
        [CI3_CSRF_NAME]: CI3_CSRF_HASH
    }, function(res) {
        if (res.status === 'success') {
            CRM.toast('success', res.message);
            setTimeout(function() { location.reload(); }, 1200);
        } else {
            CRM.toast('error', res.message);
            CRM.btn_reset($btn);
        }
    });
}

$('#btn-punch-in').click(function() { doPunch('in'); });
$('#btn-punch-out').click(function() { if (confirm('Punch out now?')) doPunch('out'); });
</script>
