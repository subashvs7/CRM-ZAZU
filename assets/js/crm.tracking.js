/**
 * Live Tracking — Leaflet + OpenStreetMap
 * Loaded once via footer page_js AFTER Leaflet JS has been loaded inline in tracking/live.php
 */
(function() {  // IIFE prevents double-init if script somehow loads twice

var liveMap    = null;
var staffMarkers = {};
var REFRESH_MS   = 30000;
var initialized  = false;

function makeDotIcon(color) {
    return L.divIcon({
        className: '',
        html: '<div style="width:14px;height:14px;border-radius:50%;background:' + color
            + ';border:2px solid #fff;box-shadow:0 1px 5px rgba(0,0,0,.5)"></div>',
        iconSize:   [14, 14],
        iconAnchor: [7,  7],
        popupAnchor:[0, -10]
    });
}

$(function() {
    var el = document.getElementById('live-map');
    if (!el) return;                    // not on tracking page
    if (initialized) return;            // guard against double-init
    initialized = true;

    // Initialise Leaflet map
    liveMap = L.map('live-map', {
        center: [20.5937, 78.9629],
        zoom:   5,
        zoomControl: true
    });

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://openstreetmap.org">OpenStreetMap</a> contributors',
        maxZoom: 19
    }).addTo(liveMap);

    // Force a size recalculation in case the container was hidden during init
    setTimeout(function() { liveMap.invalidateSize(); }, 300);

    // First load then repeat
    loadLivePositions();
    setInterval(loadLivePositions, REFRESH_MS);
});

function loadLivePositions() {
    var $spin = $('#refresh-spin');
    $spin.addClass('fa-spin');

    $.getJSON(BASE_URL + 'tracking/live_data', function(res) {
        var online = 0;
        var now    = Math.floor(Date.now() / 1000);

        $.each(res.data || [], function(i, s) {
            var uid = s.user_id;

            if (s.online && s.lat && s.lng) {
                var lat  = parseFloat(s.lat);
                var lng  = parseFloat(s.lng);
                var age  = now - (s.ts || 0);
                var color = age < 180 ? '#00a65a' : '#f39c12';  // green < 3min, orange otherwise
                var icon  = makeDotIcon(color);

                var popupHtml =
                    '<div style="min-width:160px">' +
                    '<strong style="font-size:13px">' + CRM.esc(s.name) + '</strong><br>' +
                    '<small style="color:#666">' + lat.toFixed(5) + ', ' + lng.toFixed(5) + '</small><br>' +
                    (s.battery != null ? '<span><i class="fa fa-battery-half"></i> ' + s.battery + '%</span><br>' : '') +
                    '<small class="text-muted">Last ping: ' + CRM.time_ago(new Date(s.ts * 1000).toISOString()) + '</small>' +
                    '<br><a href="' + BASE_URL + 'tracking/trail/' + uid + '" class="btn btn-xs btn-info" style="margin-top:4px">' +
                    '<i class="fa fa-road"></i> View Trail</a>' +
                    '</div>';

                if (staffMarkers[uid]) {
                    staffMarkers[uid].setLatLng([lat, lng]).setIcon(icon);
                    staffMarkers[uid].setPopupContent(popupHtml);
                } else {
                    staffMarkers[uid] = L.marker([lat, lng], { icon: icon, title: s.name })
                        .bindPopup(popupHtml)
                        .addTo(liveMap);
                }

                // Sidebar card
                $('#card-' + uid).removeClass('offline').addClass('online');
                $('#dot-'  + uid).css('background', color);
                $('#loc-'  + uid).html(
                    '<span style="color:' + color + '"><i class="fa fa-circle"></i> Online</span>' +
                    (s.battery != null ? ' <span class="text-muted">' + s.battery + '%</span>' : '') +
                    '<br><small style="color:#aaa">' + CRM.time_ago(new Date(s.ts * 1000).toISOString()) + '</small>'
                );
                online++;

            } else {
                // Offline
                if (staffMarkers[uid]) {
                    staffMarkers[uid].setIcon(makeDotIcon('#aaa'));
                }
                $('#card-' + uid).removeClass('online').addClass('offline');
                $('#dot-'  + uid).css('background', '#aaa');
                $('#loc-'  + uid).html('<span class="text-muted"><i class="fa fa-times-circle"></i> No GPS signal</span>');
            }
        });

        $('#online-count').text(online);
        $('#last-update').text(new Date().toLocaleTimeString('en-IN', {
            hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: false
        }));
        $spin.removeClass('fa-spin');
    })
    .fail(function() { $spin.removeClass('fa-spin'); });
}

// Public: staff sidebar card click → pan map
window.focusStaff = function(uid) {
    if (staffMarkers[uid]) {
        liveMap.setView(staffMarkers[uid].getLatLng(), 15, { animate: true, duration: 0.5 });
        staffMarkers[uid].openPopup();
    } else {
        CRM.toast('info', 'No GPS signal from this staff member yet.');
    }
};

})(); // end IIFE
