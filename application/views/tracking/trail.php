<section class="content-header">
    <h1>GPS Trail — <?= esc_html($user['name']) ?></h1>
    <ol class="breadcrumb">
        <li><a href="<?= base_url('tracking/live') ?>">Live Tracking</a></li>
        <li class="active">GPS Trail</li>
    </ol>
</section>
<section class="content">

<!-- Leaflet CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
<style>
#trail-map { height: 500px; border-radius: 0 0 6px 6px; z-index: 0; }
</style>

<div class="box box-primary">
    <div class="box-header with-border">
        <h3 class="box-title">
            <i class="fa fa-road"></i> GPS Trail &mdash; <?= esc_html($user['name']) ?>
        </h3>
        <div class="box-tools pull-right">
            <input type="date" id="trail-date" class="form-control input-sm"
                   style="width:140px;display:inline-block"
                   value="<?= date('Y-m-d') ?>">
            <button class="btn btn-primary btn-sm" id="btn-load-trail">
                <i class="fa fa-search"></i> Load
            </button>
            <span id="trail-info" class="label label-default" style="margin-left:6px"></span>
        </div>
    </div>
    <div class="box-body p-0">
        <div id="trail-map"></div>
    </div>
</div>

<!-- Stats row -->
<div class="row" id="trail-stats" style="display:none">
    <div class="col-md-3 col-xs-6">
        <div class="box box-info text-center" style="padding:15px">
            <h3 class="text-primary" id="stat-points">0</h3>
            <p class="text-muted">GPS Points</p>
        </div>
    </div>
    <div class="col-md-3 col-xs-6">
        <div class="box box-success text-center" style="padding:15px">
            <h3 class="text-success" id="stat-start">—</h3>
            <p class="text-muted">First Ping</p>
        </div>
    </div>
    <div class="col-md-3 col-xs-6">
        <div class="box box-warning text-center" style="padding:15px">
            <h3 class="text-warning" id="stat-end">—</h3>
            <p class="text-muted">Last Ping</p>
        </div>
    </div>
    <div class="col-md-3 col-xs-6">
        <div class="box box-danger text-center" style="padding:15px">
            <h3 class="text-danger" id="stat-dist">—</h3>
            <p class="text-muted">Est. Distance</p>
        </div>
    </div>
</div>

<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
var TRAIL_USER_ID = <?= (int)$user['id'] ?>;
var trailMap, trailLine, startMarker, endMarker;

// Custom icons
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

// Init map
trailMap = L.map('trail-map').setView([20.5937, 78.9629], 5);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; <a href="https://openstreetmap.org">OpenStreetMap</a>',
    maxZoom: 19
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

    $.getJSON(BASE_URL + 'tracking/trail_data', { user_id: TRAIL_USER_ID, date: date }, function(res) {
        var pts = res.data || [];

        // Clear previous
        if (trailLine)    { trailMap.removeLayer(trailLine); trailLine = null; }
        if (startMarker)  { trailMap.removeLayer(startMarker); startMarker = null; }
        if (endMarker)    { trailMap.removeLayer(endMarker); endMarker = null; }

        if (!pts.length) {
            $('#trail-info').text('No data for this date').removeClass('label-success').addClass('label-warning');
            $('#trail-stats').hide();
            CRM.btn_reset($btn);
            return;
        }

        // Build latlng array
        var latlngs = pts.map(function(p) { return [parseFloat(p.latitude), parseFloat(p.longitude)]; });

        // Draw polyline
        trailLine = L.polyline(latlngs, { color: '#3c8dbc', weight: 4, opacity: 0.85 }).addTo(trailMap);
        trailMap.fitBounds(trailLine.getBounds(), { padding: [40, 40] });

        // Start marker (green)
        startMarker = L.marker(latlngs[0], { icon: greenIcon })
            .bindPopup('<b>Start</b><br>' + pts[0].recorded_at).addTo(trailMap);

        // End marker (red)
        var last = latlngs[latlngs.length - 1];
        endMarker = L.marker(last, { icon: redIcon })
            .bindPopup('<b>End</b><br>' + pts[pts.length - 1].recorded_at).addTo(trailMap);

        // Click on polyline shows time
        trailLine.on('click', function(e) {
            var idx = Math.round((pts.length - 1) * (e.latlng.lat - latlngs[0][0]) / (last[0] - latlngs[0][0]));
            idx = Math.max(0, Math.min(pts.length - 1, idx));
            L.popup().setLatLng(e.latlng).setContent(pts[idx].recorded_at).openOn(trailMap);
        });

        // Stats
        var dist = 0;
        for (var i = 1; i < latlngs.length; i++) {
            dist += haversineKm(latlngs[i-1][0], latlngs[i-1][1], latlngs[i][0], latlngs[i][1]);
        }
        $('#stat-points').text(pts.length);
        $('#stat-start').text(pts[0].recorded_at.substr(11, 5));
        $('#stat-end').text(pts[pts.length-1].recorded_at.substr(11, 5));
        $('#stat-dist').text(dist.toFixed(2) + ' km');
        $('#trail-stats').show();

        $('#trail-info').text(pts.length + ' points').removeClass('label-warning').addClass('label-success');
        CRM.btn_reset($btn);
    })
    .fail(function() {
        $('#trail-info').text('Error loading trail').addClass('label-danger');
        CRM.btn_reset($btn);
    });
}

$('#btn-load-trail').on('click', loadTrail);
// Auto-load today on page open
loadTrail();
</script>
