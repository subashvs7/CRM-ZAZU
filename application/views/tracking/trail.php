<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
    <div class="flex items-center gap-3">
        <div class="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center flex-shrink-0">
            <i class="fa fa-road text-blue-600 text-lg"></i>
        </div>
        <div>
            <h1 class="text-xl font-bold text-gray-800">GPS Trail — <?= esc_html($user['name']) ?></h1>
            <nav class="text-xs text-gray-400 flex items-center gap-1 mt-0.5">
                <a href="<?= base_url('tracking/live') ?>" class="hover:text-blue-600 transition-colors">Live Tracking</a>
                <i class="fa fa-angle-right text-[10px]"></i>
                <span class="text-gray-600">GPS Trail</span>
            </nav>
        </div>
    </div>
</div>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
<style>#trail-map { height: 500px; border-radius: 0 0 1rem 1rem; z-index: 0; }</style>

<div class="bg-white rounded-2xl shadow-sm border border-gray-100 mb-5">
    <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between flex-wrap gap-3">
        <div class="flex items-center gap-2">
            <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                <i class="fa fa-road text-blue-600 text-sm"></i>
            </div>
            <h3 class="text-sm font-bold text-gray-800">GPS Trail &mdash; <?= esc_html($user['name']) ?></h3>
        </div>
        <div class="flex items-center gap-2">
            <input type="text" id="trail-date" class="px-3 py-2 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 datepicker" value="<?= date('Y-m-d') ?>" readonly>
            <button id="btn-load-trail" class="inline-flex items-center gap-1.5 px-4 py-2 text-sm bg-blue-600 text-white font-semibold rounded-xl hover:bg-blue-700 transition-colors shadow-sm">
                <i class="fa fa-search"></i> Load
            </button>
            <span id="trail-info" class="text-xs text-gray-500"></span>
        </div>
    </div>
    <div id="trail-map"></div>
</div>

<!-- Stats row (hidden until data loads) -->
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4" id="trail-stats" style="display:none">
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 text-center">
        <p class="text-3xl font-bold text-blue-600" id="stat-points">0</p>
        <p class="text-xs text-gray-400 mt-1.5 font-medium uppercase tracking-wide">GPS Points</p>
    </div>
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 text-center">
        <p class="text-3xl font-bold text-green-600" id="stat-start">—</p>
        <p class="text-xs text-gray-400 mt-1.5 font-medium uppercase tracking-wide">First Ping</p>
    </div>
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 text-center">
        <p class="text-3xl font-bold text-amber-500" id="stat-end">—</p>
        <p class="text-xs text-gray-400 mt-1.5 font-medium uppercase tracking-wide">Last Ping</p>
    </div>
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 text-center">
        <p class="text-3xl font-bold text-red-500" id="stat-dist">—</p>
        <p class="text-xs text-gray-400 mt-1.5 font-medium uppercase tracking-wide">Est. Distance</p>
    </div>
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
var TRAIL_USER_ID = <?= (int)$user['id'] ?>;
var trailMap, trailLine, startMarker, endMarker;

var greenIcon = L.icon({
    iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-green.png',
    shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
    iconSize: [25,41], iconAnchor: [12,41], popupAnchor: [1,-34], shadowSize: [41,41]
});
var redIcon = L.icon({
    iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-red.png',
    shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
    iconSize: [25,41], iconAnchor: [12,41], popupAnchor: [1,-34], shadowSize: [41,41]
});

trailMap = L.map('trail-map').setView([20.5937, 78.9629], 5);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; <a href="https://openstreetmap.org">OpenStreetMap</a>', maxZoom: 19
}).addTo(trailMap);

function haversineKm(lat1, lng1, lat2, lng2) {
    var R = 6371, dLat = (lat2-lat1)*Math.PI/180, dLng = (lng2-lng1)*Math.PI/180;
    var a = Math.sin(dLat/2)*Math.sin(dLat/2) + Math.cos(lat1*Math.PI/180)*Math.cos(lat2*Math.PI/180)*Math.sin(dLng/2)*Math.sin(dLng/2);
    return R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
}

function loadTrail() {
    var date = $('#trail-date').val();
    var $btn = $('#btn-load-trail');
    CRM.btn_loading($btn);
    $('#trail-info').text('Loading...');

    $.getJSON(BASE_URL+'tracking/trail_data', {user_id: TRAIL_USER_ID, date: date}, function(res) {
        var pts = res.data || [];
        if (trailLine)   { trailMap.removeLayer(trailLine); trailLine = null; }
        if (startMarker) { trailMap.removeLayer(startMarker); startMarker = null; }
        if (endMarker)   { trailMap.removeLayer(endMarker); endMarker = null; }

        if (!pts.length) {
            $('#trail-info').text('No data for this date').css('color','#d97706');
            $('#trail-stats').hide();
            CRM.btn_reset($btn);
            return;
        }

        var latlngs = pts.map(function(p){ return [parseFloat(p.latitude), parseFloat(p.longitude)]; });
        trailLine = L.polyline(latlngs, {color:'#3b82f6', weight:4, opacity:0.85}).addTo(trailMap);
        trailMap.fitBounds(trailLine.getBounds(), {padding:[40,40]});

        startMarker = L.marker(latlngs[0], {icon:greenIcon})
            .bindPopup('<b>Start</b><br>'+pts[0].recorded_at).addTo(trailMap);

        var last = latlngs[latlngs.length-1];
        endMarker = L.marker(last, {icon:redIcon})
            .bindPopup('<b>End</b><br>'+pts[pts.length-1].recorded_at).addTo(trailMap);

        trailLine.on('click', function(e){
            var idx = Math.round((pts.length-1) * (e.latlng.lat - latlngs[0][0]) / (last[0] - latlngs[0][0]));
            idx = Math.max(0, Math.min(pts.length-1, idx));
            L.popup().setLatLng(e.latlng).setContent(pts[idx].recorded_at).openOn(trailMap);
        });

        var dist = 0;
        for (var i = 1; i < latlngs.length; i++) {
            dist += haversineKm(latlngs[i-1][0], latlngs[i-1][1], latlngs[i][0], latlngs[i][1]);
        }
        $('#stat-points').text(pts.length);
        $('#stat-start').text(pts[0].recorded_at.substr(11,5));
        $('#stat-end').text(pts[pts.length-1].recorded_at.substr(11,5));
        $('#stat-dist').text(dist.toFixed(2)+' km');
        $('#trail-stats').show();
        $('#trail-info').text(pts.length+' points').css('color','#16a34a');
        CRM.btn_reset($btn);
    }).fail(function(){
        $('#trail-info').text('Error loading trail').css('color','#dc2626');
        CRM.btn_reset($btn);
    });
}

$('#btn-load-trail').on('click', loadTrail);
loadTrail();
</script>
