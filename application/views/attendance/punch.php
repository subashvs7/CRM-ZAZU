<!-- Page Header -->
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
    <div class="flex items-center gap-3">
        <div class="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center flex-shrink-0">
            <i class="fa fa-clock-o text-blue-600 text-lg"></i>
        </div>
        <div>
            <h1 class="text-xl font-bold text-gray-800">Punch In / Out</h1>
            <nav class="text-xs text-gray-400 flex items-center gap-1 mt-0.5">
                <a href="<?= base_url('dashboard') ?>" class="hover:text-blue-600 transition-colors">Home</a>
                <i class="fa fa-angle-right text-[10px]"></i>
                <a href="<?= base_url('attendance') ?>" class="hover:text-blue-600 transition-colors">Attendance</a>
                <i class="fa fa-angle-right text-[10px]"></i>
                <span class="text-gray-600">Punch</span>
            </nav>
        </div>
    </div>
</div>

<div class="max-w-md mx-auto">
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">

        <!-- Clock Header -->
        <div class="bg-gradient-to-br from-blue-600 to-blue-800 px-6 py-8 text-center text-white">
            <h3 id="current-time" class="text-5xl font-bold tracking-widest mb-1">00:00:00</h3>
            <p class="text-blue-200 text-sm"><?= date('l, d F Y') ?></p>
        </div>

        <div class="p-6 space-y-4">
            <!-- Location -->
            <div>
                <p class="text-xs font-semibold text-gray-600 uppercase tracking-wide mb-2">
                    <i class="fa fa-map-marker mr-1"></i> Your Location
                </p>
                <div id="location-box"
                     class="flex items-start gap-2.5 p-3.5 bg-amber-50 border border-amber-200 text-amber-800 rounded-xl text-sm">
                    <i class="fa fa-spinner fa-spin mt-0.5 flex-shrink-0"></i>
                    <span>Detecting location...</span>
                </div>
                <button type="button" id="btn-redetect-punch-loc"
                        class="mt-2 w-full inline-flex items-center justify-center gap-2 px-3 py-2 text-xs bg-white border border-gray-200 text-gray-700 font-medium rounded-xl hover:bg-gray-50 transition-colors">
                    <i class="fa fa-crosshairs text-blue-500"></i> Re-detect Location
                </button>
                <div id="punch-map-preview" class="hidden mt-3 rounded-xl overflow-hidden border border-gray-100"></div>
            </div>

            <input type="hidden" id="punch-lat">
            <input type="hidden" id="punch-lng">

            <!-- Status & Action -->
            <?php if($att && $att['punch_in_at'] && !$att['punch_out_at']): ?>
            <div class="flex items-center gap-2.5 p-3.5 bg-cyan-50 border border-cyan-200 text-cyan-800 rounded-xl text-sm">
                <i class="fa fa-check-circle flex-shrink-0 text-cyan-500"></i>
                <div>
                    <span>Punched in at <strong><?= date('d M Y, H:i', strtotime($att['punch_in_at'])) ?></strong></span>
                    <?php if($att['punch_in_lat']): ?>
                    <p class="text-xs text-gray-400 mt-0.5"><?= round($att['punch_in_lat'], 4) ?>, <?= round($att['punch_in_lng'], 4) ?></p>
                    <?php endif; ?>
                </div>
            </div>
            <button id="btn-punch-out"
                    class="w-full flex items-center justify-center gap-2 px-4 py-4 bg-red-600 text-white text-sm font-bold rounded-xl hover:bg-red-700 transition-colors shadow-sm">
                <i class="fa fa-sign-out text-lg"></i> Punch Out
            </button>

            <?php elseif(!$att || !$att['punch_in_at']): ?>
            <div class="flex items-center gap-2.5 p-3.5 bg-amber-50 border border-amber-200 text-amber-800 rounded-xl text-sm">
                <i class="fa fa-exclamation-circle flex-shrink-0"></i>
                <span>Not punched in yet today</span>
            </div>
            <button id="btn-punch-in"
                    class="w-full flex items-center justify-center gap-2 px-4 py-4 bg-green-600 text-white text-sm font-bold rounded-xl hover:bg-green-700 transition-colors shadow-sm">
                <i class="fa fa-sign-in text-lg"></i> Punch In
            </button>

            <?php else: ?>
            <div class="bg-green-50 border border-green-200 rounded-xl p-4">
                <div class="flex items-center gap-2.5 text-green-800 text-sm mb-3">
                    <i class="fa fa-check-circle flex-shrink-0 text-green-500"></i>
                    <span>Attendance recorded for today</span>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div class="bg-white rounded-xl p-3 text-center border border-green-100">
                        <p class="text-xs text-gray-400 mb-1">Punch In</p>
                        <p class="text-sm font-bold text-green-700"><?= date('H:i', strtotime($att['punch_in_at'])) ?></p>
                        <p class="text-xs text-gray-400 mt-0.5"><?= date('d M Y', strtotime($att['punch_in_at'])) ?></p>
                    </div>
                    <div class="bg-white rounded-xl p-3 text-center border border-green-100">
                        <p class="text-xs text-gray-400 mb-1">Punch Out</p>
                        <p class="text-sm font-bold text-red-600"><?= date('H:i', strtotime($att['punch_out_at'])) ?></p>
                        <p class="text-xs text-gray-400 mt-0.5"><?= date('d M Y', strtotime($att['punch_out_at'])) ?></p>
                    </div>
                </div>
                <div class="mt-3 text-center">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-blue-50 text-blue-700 rounded-xl text-xs font-semibold">
                        <i class="fa fa-clock-o"></i> <?= $att['working_hours'] ?>h working hours
                    </span>
                </div>
            </div>
            <?php endif; ?>

            <a href="<?= base_url('attendance') ?>" class="flex items-center justify-center gap-1.5 text-xs text-gray-400 hover:text-blue-600 transition-colors">
                <i class="fa fa-list"></i> View Full Attendance Log
            </a>
        </div>
    </div>
</div>

<script>
function tick() {
    $('#current-time').text(new Date().toLocaleTimeString('en-IN', {hour:'2-digit', minute:'2-digit', second:'2-digit', hour12:false}));
}
tick(); setInterval(tick, 1000);

function detectPunchLocation() {
    var $box = $('#location-box'), $btn = $('#btn-redetect-punch-loc');
    CRM.btn_loading($btn);
    $box.attr('class', 'flex items-start gap-2.5 p-3.5 bg-amber-50 border border-amber-200 text-amber-800 rounded-xl text-sm')
        .html('<i class="fa fa-spinner fa-spin mt-0.5 flex-shrink-0"></i><span>Detecting location...</span>');
    $('#punch-map-preview').addClass('hidden');
    if (!navigator.geolocation) {
        $box.attr('class', 'flex items-start gap-2.5 p-3.5 bg-red-50 border border-red-200 text-red-800 rounded-xl text-sm')
            .html('<i class="fa fa-times mt-0.5 flex-shrink-0"></i><span>Geolocation not supported.</span>');
        CRM.btn_reset($btn); return;
    }
    navigator.geolocation.getCurrentPosition(function(pos) {
        var lat = pos.coords.latitude.toFixed(7), lng = pos.coords.longitude.toFixed(7), acc = Math.round(pos.coords.accuracy);
        $('#punch-lat').val(lat); $('#punch-lng').val(lng);
        $box.attr('class', 'flex items-start gap-2.5 p-3.5 bg-green-50 border border-green-200 text-green-800 rounded-xl text-sm')
            .html('<i class="fa fa-check-circle mt-0.5 flex-shrink-0"></i><span><strong>' + lat + ', ' + lng + '</strong> <span class="opacity-70">(±' + acc + 'm)</span></span>');
        var bbox = (parseFloat(lng)-0.003)+'%2C'+(parseFloat(lat)-0.003)+'%2C'+(parseFloat(lng)+0.003)+'%2C'+(parseFloat(lat)+0.003);
        $('#punch-map-preview').html('<iframe src="https://www.openstreetmap.org/export/embed.html?bbox='+bbox+'&layer=mapnik&marker='+lat+'%2C'+lng+'" style="width:100%;height:180px;border:0" loading="lazy"></iframe>').removeClass('hidden');
        CRM.btn_reset($btn);
    }, function(err) {
        var msgs = {1:'Permission denied.', 2:'Position unavailable.', 3:'Timed out.'};
        $box.attr('class', 'flex items-start gap-2.5 p-3.5 bg-red-50 border border-red-200 text-red-800 rounded-xl text-sm')
            .html('<i class="fa fa-exclamation-triangle mt-0.5 flex-shrink-0"></i><span>' + (msgs[err.code] || 'Error.') + ' Tap Re-detect to retry.</span>');
        CRM.btn_reset($btn);
    }, {enableHighAccuracy: true, timeout: 12000, maximumAge: 0});
}

detectPunchLocation();
$('#btn-redetect-punch-loc').click(detectPunchLocation);

function doPunch(type) {
    var $btn = type === 'in' ? $('#btn-punch-in') : $('#btn-punch-out');
    if (!$('#punch-lat').val() && !confirm('Location not captured. Punch ' + type + ' anyway?')) return;
    CRM.btn_loading($btn);
    $.post(BASE_URL + 'attendance/do_punch', {
        type: type,
        latitude: $('#punch-lat').val(),
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
