<!-- Page Header -->
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
    <div class="flex items-center gap-3">
        <div class="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center flex-shrink-0">
            <i class="fa fa-map text-blue-600 text-lg"></i>
        </div>
        <div>
            <h1 class="text-xl font-bold text-gray-800">Coverage Map</h1>
            <nav class="text-xs text-gray-400 flex items-center gap-1 mt-0.5">
                <a href="<?= base_url('dashboard') ?>" class="hover:text-blue-600 transition-colors">Home</a>
                <i class="fa fa-angle-right text-[10px]"></i>
                <span class="text-gray-600">Reports — Coverage</span>
            </nav>
        </div>
    </div>
</div>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
<style>
#coverage-map { height: 520px; border-radius: 0 0 1rem 1rem; z-index: 0; }
.leaflet-legend { background: #fff; padding: 10px 14px; border-radius: 12px; font-size: 12px; line-height: 24px; box-shadow: 0 4px 16px rgba(0,0,0,.12); }
.leaflet-legend span { display: inline-block; width: 10px; height: 10px; border-radius: 50%; margin-right: 6px; vertical-align: middle; }
</style>

<?php 
$show_reset = true;
include(APPPATH.'views/reports/_filter_bar.php'); 
?>

<div class="bg-white rounded-2xl shadow-sm border border-gray-100">
    <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
        <div class="flex items-center gap-2">
            <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                <i class="fa fa-map text-blue-600 text-sm"></i>
            </div>
            <div>
                <h3 class="text-sm font-bold text-gray-800">Customer Visit Coverage</h3>
                <p class="text-xs text-gray-400">Geographic distribution of visits</p>
            </div>
        </div>
        <div id="coverage-stats" class="hidden flex items-center gap-3">
            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-semibold rounded-lg bg-green-100 text-green-700">
                <i class="fa fa-circle text-[7px]"></i> Visited: <strong id="stat-visited">0</strong>
            </span>
            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-semibold rounded-lg bg-red-100 text-red-700">
                <i class="fa fa-circle text-[7px]"></i> Not visited: <strong id="stat-not-visited">0</strong>
            </span>
        </div>
    </div>
    <div id="coverage-map"></div>
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
var coverageMap = L.map('coverage-map').setView([20.5937, 78.9629], 5);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; <a href="https://openstreetmap.org">OpenStreetMap</a>', maxZoom: 19
}).addTo(coverageMap);

var legend = L.control({position: 'bottomright'});
legend.onAdd = function() {
    var div = L.DomUtil.create('div', 'leaflet-legend');
    div.innerHTML =
        '<strong style="display:block;margin-bottom:6px;font-size:11px;color:#374151;text-transform:uppercase;letter-spacing:.05em">Visit Coverage</strong>' +
        '<span style="background:#22c55e"></span> Visited this period<br>' +
        '<span style="background:#ef4444"></span> Not visited';
    return div;
};
legend.addTo(coverageMap);

var coverageLayers = [];

function loadCoverage() {
    var from = $('#from-date').val(), to = $('#to-date').val();
    var userId = $('#staff-filter').val() || '';
    coverageLayers.forEach(function(l) { coverageMap.removeLayer(l); });
    coverageLayers = [];

    $.getJSON(BASE_URL + 'reports/coverage_data', {from: from, to: to, user_id: userId}, function(res) {
        var visited = 0, notVisited = 0, bounds = [];
        $.each(res.data || [], function(i, r) {
            if (!r.latitude || !r.longitude) return;
            var lat = parseFloat(r.latitude), lng = parseFloat(r.longitude);
            var cnt = parseInt(r.visit_count) || 0;
            var color = cnt > 0 ? '#22c55e' : '#ef4444';

            var popupHtml = '<div style="min-width:220px;font-family:inherit;color:#334155;padding:2px">';
            popupHtml += '<div style="font-size:13px;font-weight:700;color:#1e293b;margin-bottom:8px;border-bottom:1px solid #f1f5f9;padding-bottom:6px;">' + CRM.esc(r.name) + '</div>';
            if (cnt > 0 && r.visits && r.visits.length > 0) {
                popupHtml += '<div style="display:flex;flex-direction:column;gap:8px;max-height:160px;overflow-y:auto;padding-right:4px;">';
                $.each(r.visits, function(j, v) {
                    popupHtml += '<div style="display:flex;align-items:center;gap:8px;padding:6px;background:#f8fafc;border-radius:8px;border:1px solid #f1f5f9;">' +
                        '<img src="' + v.profile_photo + '" style="width:28px;height:28px;border-radius:50%;object-fit:cover;border:1px solid #cbd5e1;flex-shrink:0;">' +
                        '<div style="min-width:0;flex:1;">' +
                            '<div style="font-size:11px;font-weight:600;color:#334155;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;line-height:1.2;">' + CRM.esc(v.staff_name) + '</div>' +
                            '<div style="font-size:9px;color:#64748b;margin-top:2px;display:flex;align-items:center;gap:3px;line-height:1;">' +
                                '<i class="fa fa-calendar" style="font-size:9px;"></i> ' + v.check_in_at +
                            '</div>' +
                        '</div>' +
                    '</div>';
                });
                popupHtml += '</div>';
            } else {
                popupHtml += '<div style="display:flex;align-items:center;gap:6px;color:#ef4444;font-size:11px;font-weight:500;padding:4px 0;">' +
                    '<i class="fa fa-times-circle" style="font-size:13px;"></i> No visits recorded' +
                '</div>';
            }
            popupHtml += '</div>';

            var circle = L.circle([lat, lng], {
                color: color,
                fillColor: color,
                fillOpacity: cnt > 0 ? 0.45 : 0.25,
                weight: cnt > 0 ? 2 : 1,
                radius: cnt > 0 ? (300 + cnt * 60) : 200
            }).bindPopup(popupHtml);
            circle.addTo(coverageMap);
            coverageLayers.push(circle);
            bounds.push([lat, lng]);
            if (cnt > 0) visited++; else notVisited++;
        });
        if (bounds.length) coverageMap.fitBounds(bounds, {padding: [40, 40]});
        $('#stat-visited').text(visited);
        $('#stat-not-visited').text(notVisited);
        if (bounds.length) $('#coverage-stats').removeClass('hidden').addClass('flex');
    });
}

$('#btn-filter').on('click', loadCoverage);
$(document).on('click', '#btn-reset', function() {
    var $from = $('#from-date');
    var $to = $('#to-date');
    var $staff = $('#staff-filter');
    if ($from.length)  $from.datepicker('update', $from.attr('data-default'));
    if ($to.length)    $to.datepicker('update', $to.attr('data-default'));
    if ($staff.length) $staff.val($staff.attr('data-default')).trigger('change');
    loadCoverage();
});
loadCoverage();
</script>
