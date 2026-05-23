<!-- Page Header -->
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
    <div class="flex items-center gap-3">
        <div class="w-10 h-10 bg-green-100 rounded-xl flex items-center justify-center flex-shrink-0">
            <i class="fa fa-sign-in text-green-600 text-lg"></i>
        </div>
        <div>
            <h1 class="text-xl font-bold text-gray-800">Check In</h1>
            <nav class="text-xs text-gray-400 flex items-center gap-1 mt-0.5">
                <a href="<?= base_url('dashboard') ?>" class="hover:text-blue-600 transition-colors">Home</a>
                <i class="fa fa-angle-right text-[10px]"></i>
                <a href="<?= base_url('visits') ?>" class="hover:text-blue-600 transition-colors">Visits</a>
                <i class="fa fa-angle-right text-[10px]"></i>
                <span class="text-gray-600">Check In</span>
            </nav>
        </div>
    </div>
</div>

<div class="max-w-xl mx-auto space-y-4">

    <?php if(!empty($open_visit)): ?>
    <!-- Active Check-in Warning -->
    <div class="bg-amber-50 border border-amber-200 rounded-2xl p-5">
        <div class="flex items-start gap-3 mb-4">
            <div class="w-9 h-9 bg-amber-100 rounded-xl flex items-center justify-center flex-shrink-0">
                <i class="fa fa-exclamation-triangle text-amber-600"></i>
            </div>
            <div>
                <p class="text-sm font-bold text-amber-800">Already Checked In</p>
                <p class="text-xs text-amber-600 mt-0.5">You have an active check-in that hasn't been closed yet.</p>
                <p class="text-xs text-amber-700 mt-1 font-medium">
                    <i class="fa fa-clock-o mr-1"></i> Since: <?= date('d M Y H:i', strtotime($open_visit['check_in_at'])) ?>
                </p>
            </div>
        </div>
        <button type="button" id="btn-do-checkout" data-id="<?= $open_visit['id'] ?>"
                class="w-full flex items-center justify-center gap-2 px-4 py-3 bg-red-600 text-white text-sm font-bold rounded-xl hover:bg-red-700 transition-colors shadow-sm">
            <i class="fa fa-sign-out"></i> Check Out Now
        </button>
        <div id="checkout-loc-preview" class="hidden mt-3 p-3 bg-amber-50 border border-amber-200 text-amber-800 rounded-xl text-sm"></div>
        <div id="checkout-map-preview" class="hidden mt-3 rounded-xl overflow-hidden"></div>
        <p class="text-xs text-amber-500 text-center mt-3">— or —</p>
        <p class="text-xs text-amber-600 text-center mt-1">Check out first, then check in to another customer below.</p>
    </div>
    <?php endif; ?>

    <?php if($plan): ?>
    <!-- Planned Visit Banner -->
    <div class="flex items-center gap-3 p-4 bg-cyan-50 border border-cyan-200 rounded-2xl">
        <div class="w-8 h-8 bg-cyan-100 rounded-lg flex items-center justify-center flex-shrink-0">
            <i class="fa fa-calendar text-cyan-600 text-sm"></i>
        </div>
        <div>
            <p class="text-xs text-cyan-600 font-semibold uppercase tracking-wide">Planned Visit</p>
            <p class="text-sm font-semibold text-cyan-800 mt-0.5"><?= esc_html($plan['customer_name']) ?></p>
        </div>
    </div>
    <?php endif; ?>

    <!-- Check-in Form -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-2">
            <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
                <i class="fa fa-map-marker text-green-600 text-sm"></i>
            </div>
            <div>
                <h3 class="text-sm font-bold text-gray-800">Check In to Customer</h3>
                <p class="text-xs text-gray-400">Select customer and capture your location</p>
            </div>
        </div>
        <div class="p-6">
            <form id="checkin-form" method="POST" action="<?= base_url('visits/do_checkin') ?>">
                <input type="hidden" name="<?= $csrf_name ?>" value="<?= $csrf_hash ?>">
                <input type="hidden" name="visit_plan_id" value="<?= $plan['id'] ?? 0 ?>">
                <input type="hidden" name="latitude"  id="checkin-lat">
                <input type="hidden" name="longitude" id="checkin-lng">

                <div class="space-y-5">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">Customer *</label>
                        <select name="customer_id" id="customer_id" class="w-full px-3 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 select2" required>
                            <option value="">— Select Customer —</option>
                            <?php foreach($customers as $c): ?>
                            <option value="<?= $c['id'] ?>" <?= (isset($plan) && $plan['customer_id'] == $c['id']) ? 'selected' : '' ?>>
                                <?= esc_html($c['name']) ?><?php if($c['city']): ?> — <?= esc_html($c['city']) ?><?php endif; ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                        <p class="hidden crm-field-error text-xs text-red-600 mt-1" id="customer-error">Please select a customer.</p>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">
                            <i class="fa fa-map-marker mr-1"></i> Location
                        </label>
                        <div id="checkin-location-status"
                             class="flex items-center gap-2.5 p-3.5 bg-amber-50 border border-amber-200 text-amber-800 rounded-xl text-sm mb-3">
                            <i class="fa fa-spinner fa-spin flex-shrink-0"></i>
                            <span>Detecting your location automatically...</span>
                        </div>
                        <button type="button" id="btn-redetect-location"
                                class="w-full flex items-center justify-center gap-2 px-3 py-2.5 text-sm bg-white border border-gray-200 text-gray-700 font-medium rounded-xl hover:bg-gray-50 transition-colors">
                            <i class="fa fa-crosshairs text-blue-500"></i> Re-detect / Refresh Location
                        </button>
                        <div id="checkin-map-preview" class="hidden mt-3 rounded-xl overflow-hidden border border-gray-100"></div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">
                            Notes <span class="text-gray-400 font-normal text-xs normal-case">(optional)</span>
                        </label>
                        <textarea name="notes" rows="2" class="w-full px-3 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none" placeholder="Add any notes about this visit..."></textarea>
                    </div>

                    <button type="submit" id="btn-checkin"
                            class="w-full flex items-center justify-center gap-2 px-4 py-3.5 bg-green-600 text-white text-sm font-bold rounded-xl hover:bg-green-700 transition-colors shadow-sm">
                        <i class="fa fa-sign-in text-lg"></i> Check In Now
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function doDetectCheckin() {
    var $status = $('#checkin-location-status');
    var $btn    = $('#btn-redetect-location');
    var $map    = $('#checkin-map-preview');
    CRM.btn_loading($btn);
    $status.attr('class', 'flex items-center gap-2.5 p-3.5 bg-amber-50 border border-amber-200 text-amber-800 rounded-xl text-sm mb-3')
           .html('<i class="fa fa-spinner fa-spin flex-shrink-0"></i> <span>Detecting location...</span>');
    $map.addClass('hidden');
    if (!navigator.geolocation) {
        $status.attr('class', 'flex items-center gap-2.5 p-3.5 bg-red-50 border border-red-200 text-red-800 rounded-xl text-sm mb-3')
               .html('<i class="fa fa-times flex-shrink-0"></i> <span>Geolocation not supported by your browser.</span>');
        CRM.btn_reset($btn); return;
    }
    navigator.geolocation.getCurrentPosition(function(pos) {
        var lat = pos.coords.latitude.toFixed(7), lng = pos.coords.longitude.toFixed(7), acc = Math.round(pos.coords.accuracy);
        $('#checkin-lat').val(lat); $('#checkin-lng').val(lng);
        $status.attr('class', 'flex items-center gap-2.5 p-3.5 bg-green-50 border border-green-200 text-green-800 rounded-xl text-sm mb-3')
               .html('<i class="fa fa-check-circle flex-shrink-0"></i> <span><strong>' + lat + ', ' + lng + '</strong> <span class="opacity-70">(±' + acc + 'm)</span></span>');
        var bbox = (parseFloat(lng)-0.003)+'%2C'+(parseFloat(lat)-0.003)+'%2C'+(parseFloat(lng)+0.003)+'%2C'+(parseFloat(lat)+0.003);
        $map.html('<iframe src="https://www.openstreetmap.org/export/embed.html?bbox='+bbox+'&layer=mapnik&marker='+lat+'%2C'+lng+'" style="width:100%;height:200px;border:0" loading="lazy"></iframe>').removeClass('hidden');
        CRM.btn_reset($btn);
    }, function(err) {
        var msgs = {1:'Permission denied.',2:'Position unavailable.',3:'Timed out.'};
        $status.attr('class', 'flex items-center gap-2.5 p-3.5 bg-red-50 border border-red-200 text-red-800 rounded-xl text-sm mb-3')
               .html('<i class="fa fa-exclamation-triangle flex-shrink-0"></i> <span>'+(msgs[err.code]||'Location error.')+' Tap Re-detect to retry.</span>');
        CRM.btn_reset($btn);
    }, {enableHighAccuracy:true, timeout:12000, maximumAge:0});
}

doDetectCheckin();
$(document).on('click', '#btn-redetect-location', doDetectCheckin);
CRM.init_plugins();

$('#customer_id').on('change', function() {
    if ($(this).val()) $('#customer-error').addClass('hidden');
});

$('#checkin-form').on('submit', function(e) {
    e.preventDefault();
    if (!$('#customer_id').val()) {
        $('#customer-error').removeClass('hidden');
        CRM.toast('error', 'Please select a customer.');
        return false;
    }
    $('#customer-error').addClass('hidden');
    if (!$('#checkin-lat').val() && !confirm('Location not captured yet.\n\nCheck in without location?')) return false;
    var $btn = $('#btn-checkin');
    CRM.btn_loading($btn);
    $.ajax({
        url: BASE_URL + 'visits/do_checkin', method: 'POST',
        data: new FormData(this), processData: false, contentType: false,
        success: function(res) {
            if (res.status === 'success') {
                CRM.toast('success', res.message);
                setTimeout(function() { window.location = BASE_URL + 'visits'; }, 1000);
            } else {
                CRM.toast('error', res.message); CRM.btn_reset($btn);
            }
        },
        error: function(xhr) {
            var res = xhr.responseJSON || {};
            if (res.errors && res.errors.customer_id) $('#customer-error').text(res.errors.customer_id).removeClass('hidden');
            CRM.toast('error', res.message || 'Check-in failed.'); CRM.btn_reset($btn);
        }
    });
});

$('#btn-do-checkout').on('click', function() {
    var id = $(this).data('id'), $btn = $(this);
    CRM.btn_loading($btn);
    $.post(BASE_URL + 'visits/do_checkout/' + id, {
        latitude: $('#checkin-lat').val() || '',
        longitude: $('#checkin-lng').val() || '',
        [CI3_CSRF_NAME]: CI3_CSRF_HASH
    }).done(function(res) {
        if (res.status === 'success') {
            CRM.toast('success', 'Checked out successfully.');
            setTimeout(function() { location.reload(); }, 800);
        } else {
            CRM.toast('error', res.message); CRM.btn_reset($btn);
        }
    }).fail(function(xhr) {
        CRM.toast('error', (xhr.responseJSON || {}).message || 'Checkout failed.'); CRM.btn_reset($btn);
    });
});
</script>
