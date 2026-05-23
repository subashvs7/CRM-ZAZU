<section class="content-header">
    <h1>Coverage Map <small>Customer visit coverage</small></h1>
    <ol class="breadcrumb">
        <li><a href="<?= base_url('reports/visits') ?>">Reports</a></li>
        <li class="active">Coverage</li>
    </ol>
</section>
<section class="content">

<!-- Leaflet CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
<style>
#coverage-map { height: 520px; border-radius: 0 0 6px 6px; z-index: 0; }
.leaflet-legend { background: #fff; padding: 8px 12px; border-radius: 4px; font-size: 12px; line-height: 22px; box-shadow: 0 1px 5px rgba(0,0,0,.2); }
.leaflet-legend span { display:inline-block; width:12px; height:12px; border-radius:50%; margin-right:5px; vertical-align:middle; }
</style>

<?php include(APPPATH.'views/reports/_filter_bar.php'); ?>

<div class="box box-primary">
    <div class="box-header with-border">
        <h3 class="box-title"><i class="fa fa-map"></i> Customer Visit Coverage</h3>
        <div class="box-tools pull-right" id="coverage-stats" style="display:none">
            <span class="label label-success">
                <i class="fa fa-circle"></i> Visited: <strong id="stat-visited">0</strong>
            </span>
            <span class="label label-danger" style="margin-left:6px">
                <i class="fa fa-circle"></i> Not visited: <strong id="stat-not-visited">0</strong>
            </span>
        </div>
    </div>
    <div class="box-body p-0">
        <div id="coverage-map"></div>
    </div>
</div>

<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
var coverageMap = L.map('coverage-map').setView([20.5937, 78.9629], 5);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; <a href="https://openstreetmap.org">OpenStreetMap</a>',
    maxZoom: 19
}).addTo(coverageMap);

// Legend control
var legend = L.control({ position: 'bottomright' });
legend.onAdd = function() {
    var div = L.DomUtil.create('div', 'leaflet-legend');
    div.innerHTML =
        '<strong style="display:block;margin-bottom:4px">Visit Coverage</strong>' +
        '<span style="background:#27ae60"></span> Visited this period<br>' +
        '<span style="background:#e74c3c"></span> Not visited';
    return div;
};
legend.addTo(coverageMap);

var coverageLayers = [];

function loadCoverage() {
    var from = $('#from-date').val();
    var to   = $('#to-date').val();

    // Remove previous circles
    coverageLayers.forEach(function(l) { coverageMap.removeLayer(l); });
    coverageLayers = [];

    $.getJSON(BASE_URL + 'reports/coverage_data', { from: from, to: to }, function(res) {
        var visited = 0, notVisited = 0, bounds = [];

        $.each(res.data || [], function(i, r) {
            if (!r.latitude || !r.longitude) return;

            var lat = parseFloat(r.latitude);
            var lng = parseFloat(r.longitude);
            var cnt = parseInt(r.visit_count) || 0;

            var color  = cnt > 0 ? '#27ae60' : '#e74c3c';
            var radius = cnt > 0 ? (300 + cnt * 60) : 200;
            var opacity = cnt > 0 ? 0.45 : 0.25;

            var circle = L.circle([lat, lng], {
                color:       color,
                fillColor:   color,
                fillOpacity: opacity,
                weight:      cnt > 0 ? 2 : 1,
                radius:      radius
            }).bindPopup(
                '<div style="min-width:140px">' +
                '<strong>' + CRM.esc(r.name) + '</strong><br>' +
                (cnt > 0
                    ? '<span style="color:#27ae60"><i class="fa fa-check-circle"></i> ' + cnt + ' visit' + (cnt > 1 ? 's' : '') + '</span>'
                    : '<span style="color:#e74c3c"><i class="fa fa-times-circle"></i> No visits</span>') +
                '</div>'
            );

            circle.addTo(coverageMap);
            coverageLayers.push(circle);
            bounds.push([lat, lng]);

            if (cnt > 0) visited++; else notVisited++;
        });

        if (bounds.length) {
            coverageMap.fitBounds(bounds, { padding: [40, 40] });
        }

        $('#stat-visited').text(visited);
        $('#stat-not-visited').text(notVisited);
        if (bounds.length) { $('#coverage-stats').show(); }
    });
}

$('#btn-filter').on('click', loadCoverage);
loadCoverage(); // Auto-load on page open
</script>
