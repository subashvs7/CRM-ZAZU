<?php
$colors  = ['#2563eb', '#7c3aed', '#059669', '#dc2626', '#d97706', '#0891b2'];
$bgColor = $colors[abs(crc32($user['name'])) % count($colors)];
$initials = implode('', array_map(fn($w) => strtoupper($w[0]), array_slice(explode(' ', $user['name']), 0, 2)));
?>

<!-- ═══════ PAGE HEADER ═══════ -->
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-5">
    <div class="flex items-center gap-3">
        <div class="w-12 h-12 rounded-2xl flex items-center justify-center flex-shrink-0 text-white text-lg font-extrabold shadow-lg"
            style="background:<?= $bgColor ?>; box-shadow:0 4px 14px <?= $bgColor ?>55">
            <?= $initials ?>
        </div>
        <div>
            <h1 class="text-2xl font-extrabold text-gray-900">GPS Trail — <?= esc_html($user['name']) ?></h1>
            <nav class="text-xs text-gray-400 flex items-center gap-1.5 mt-0.5">
                <a href="<?= base_url('tracking/live') ?>" class="hover:text-blue-600 transition-colors font-medium">
                    <i class="fa fa-map-marker"></i> Live Tracking
                </a>
                <i class="fa fa-angle-right text-[10px]"></i>
                <span class="text-gray-500 font-medium">GPS Trail</span>
            </nav>
        </div>
    </div>
    <div class="flex items-center gap-2 flex-wrap">
        <div class="flex items-center gap-2 bg-white border-2 border-gray-200 rounded-xl px-3 py-2 shadow-sm">
            <i class="fa fa-calendar text-blue-500"></i>
            <input type="text" id="trail-date"
                class="text-sm font-bold text-gray-700 bg-transparent border-none outline-none w-[112px] datepicker"
                value="<?= date('Y-m-d') ?>" readonly>
        </div>
        <button id="btn-load-trail"
            class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-extrabold rounded-xl text-white shadow-md transition-all hover:opacity-90 active:scale-95"
            style="background:linear-gradient(135deg,#2563eb,#7c3aed); box-shadow:0 4px 14px rgba(37,99,235,.35)">
            <i class="fa fa-search"></i> Load Trail
        </button>
        <span id="trail-info" class="text-xs font-semibold text-gray-500 px-3 py-2 bg-white rounded-lg border-2 border-gray-200 shadow-sm"></span>
    </div>
</div>

<!-- ═══════ STAT CARDS ═══════ -->
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-5" id="trail-stats" style="display:none">
    <div class="bg-white rounded-2xl border-2 border-blue-100 px-5 py-4 flex items-center gap-4 shadow-sm" style="border-left:5px solid #2563eb">
        <div class="w-12 h-12 rounded-2xl flex items-center justify-center flex-shrink-0" style="background:#dbeafe">
            <i class="fa fa-map-pin text-blue-600 text-lg"></i>
        </div>
        <div>
            <div class="text-3xl font-extrabold text-blue-600 leading-tight" id="stat-points">0</div>
            <div class="text-xs text-gray-400 font-bold uppercase tracking-widest mt-0.5">GPS Points</div>
        </div>
    </div>
    <div class="bg-white rounded-2xl border-2 border-green-100 px-5 py-4 flex items-center gap-4 shadow-sm" style="border-left:5px solid #16a34a">
        <div class="w-12 h-12 rounded-2xl flex items-center justify-center flex-shrink-0" style="background:#dcfce7">
            <i class="fa fa-play-circle text-green-600 text-lg"></i>
        </div>
        <div>
            <div class="text-3xl font-extrabold text-green-600 leading-tight" id="stat-start">—</div>
            <div class="text-xs text-gray-400 font-bold uppercase tracking-widest mt-0.5">First Ping</div>
        </div>
    </div>
    <div class="bg-white rounded-2xl border-2 border-yellow-100 px-5 py-4 flex items-center gap-4 shadow-sm" style="border-left:5px solid #d97706">
        <div class="w-12 h-12 rounded-2xl flex items-center justify-center flex-shrink-0" style="background:#fef9c3">
            <i class="fa fa-stop-circle text-yellow-600 text-lg"></i>
        </div>
        <div>
            <div class="text-3xl font-extrabold text-yellow-600 leading-tight" id="stat-end">—</div>
            <div class="text-xs text-gray-400 font-bold uppercase tracking-widest mt-0.5">Last Ping</div>
        </div>
    </div>
    <div class="bg-white rounded-2xl border-2 border-pink-100 px-5 py-4 flex items-center gap-4 shadow-sm" style="border-left:5px solid #db2777">
        <div class="w-12 h-12 rounded-2xl flex items-center justify-center flex-shrink-0" style="background:#fce7f3">
            <i class="fa fa-road text-pink-600 text-lg"></i>
        </div>
        <div>
            <div class="text-2xl font-extrabold text-pink-600 leading-tight" id="stat-dist">—</div>
            <div class="text-xs text-gray-400 font-bold uppercase tracking-widest mt-0.5">Est. Distance</div>
        </div>
    </div>
</div>

<!-- ═══════ MAIN LAYOUT ═══════ -->
<div class="flex gap-4" style="height:calc(100vh - 310px); min-height:560px;">

    <!-- ── MAP CARD ── -->
    <div class="flex-1 bg-white rounded-2xl border-2 border-gray-200 flex flex-col overflow-hidden relative shadow-sm">
        <!-- Map toolbar overlay -->
        <div class="absolute top-3 left-3 right-3 flex items-center justify-between pointer-events-none" style="z-index:600">
            <div class="flex items-center gap-2 pointer-events-auto">
                <button id="btn-fit-trail" title="Fit route" class="tmap-btn"><i class="fa fa-expand"></i> Fit Route</button>
                <button id="btn-sat-trail" title="Satellite" class="tmap-btn"><i class="fa fa-globe"></i> Satellite</button>
            </div>
            <div class="flex items-center gap-2 pointer-events-auto">
                <div id="trail-loading-badge" class="tmap-badge" style="display:none">
                    <i class="fa fa-spinner fa-spin text-blue-500 text-xs"></i>
                    <span class="text-xs text-gray-600 font-semibold">Calculating road route…</span>
                </div>
                <div class="tmap-badge" id="coord-badge">
                    <i class="fa fa-crosshairs text-blue-400 text-xs"></i>
                    <span class="text-xs text-gray-500 font-mono" id="map-coord-display">Lat — Lng —</span>
                </div>
            </div>
        </div>
        <div id="trail-map" class="flex-1 w-full" style="min-height:300px"></div>
        <!-- Legend bar -->
        <div class="flex items-center gap-5 px-5 py-2.5 border-t-2 border-gray-100 bg-gray-50 flex-wrap">
            <span class="flex items-center gap-2 text-xs font-semibold text-gray-600">
                <span class="w-4 h-4 rounded-full bg-green-500 border-2 border-white shadow inline-block"></span> Start
            </span>
            <span class="flex items-center gap-2 text-xs font-semibold text-gray-600">
                <span class="w-4 h-4 rounded-full bg-red-500 border-2 border-white shadow inline-block"></span> End
            </span>
            <span class="flex items-center gap-2 text-xs font-semibold text-gray-600">
                <span class="w-4 h-4 rounded-full border-2 border-white shadow inline-block" style="background:#f59e0b"></span> Customer Stop
            </span>
            <span class="flex items-center gap-2 text-xs font-semibold text-gray-600">
                <span class="inline-block w-10 h-1.5 rounded-full" style="background:#1e40af"></span> Route Path
            </span>
            <span class="ml-auto text-xs font-semibold text-gray-400" id="trail-route-type"></span>
        </div>
    </div>

    <!-- ── RIGHT SIDEBAR ── -->
    <div class="flex flex-col gap-3 flex-shrink-0" style="width:320px; height:100%; max-height:100%; overflow:hidden;">

        <!-- Customer Details Card Removed -->

        <!-- 3. Field Routes Panel -->
        <div class="bg-white rounded-2xl border-2 border-gray-200 shadow-sm overflow-hidden flex flex-col flex-1" style="min-height: 150px;">
            <div class="px-4 py-3 border-b-2 border-gray-100 flex items-center justify-between" style="background:linear-gradient(135deg,#f8faff,#f0f4ff)">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-purple-600 flex items-center justify-center flex-shrink-0 text-white"><i class="fa fa-map-signs text-sm"></i></div>
                    <div>
                        <h3 class="text-xs font-extrabold text-gray-800">Field Routes</h3>
                    </div>
                </div>
                <span class="text-[10px] font-bold text-purple-700 bg-purple-50 px-2 py-0.5 rounded-full border border-purple-200" id="info-timeline-count">0</span>
            </div>

            <!-- Empty state -->
            <div id="timeline-empty" class="flex-1 flex flex-col items-center justify-center text-center p-5 bg-white">
                <div class="w-10 h-10 rounded-full bg-gray-50 flex items-center justify-center mb-2 text-gray-300"><i class="fa fa-calendar-o text-base"></i></div>
                <p class="text-xs font-bold text-gray-400">No data loaded</p>
            </div>

            <!-- No visits state -->
            <div id="no-visits-panel" class="flex-1 flex flex-col items-center justify-center text-center p-5 bg-white" style="display:none">
                <div class="w-10 h-10 rounded-full bg-pink-50 flex items-center justify-center mb-2 text-pink-400"><i class="fa fa-map-signs text-base"></i></div>
                <p class="text-xs font-bold text-gray-400">No customer visits</p>
            </div>

            <!-- Scrollable visits list -->
            <div class="flex-1 overflow-y-auto bg-white" id="timeline-list" style="display:none;">
                <!-- Visited stops list -->
            </div>
        </div>

    </div>
</div>

<!-- ── CUSTOMERS OF THE DAY TABLE ── -->
<div class="bg-white rounded-2xl border-2 border-gray-200 shadow-sm mt-5 overflow-hidden" id="cust-table-card">
    <div class="px-5 py-4 border-b-2 border-gray-100 flex items-center justify-between" style="background:linear-gradient(135deg,#f8faff,#f0f4ff)">
        <div>
            <h3 class="text-base font-extrabold text-gray-800">Customers Status — <span id="tbl-date-str">...</span></h3>
            <p class="text-xs text-gray-400 mt-0.5 font-bold">List of customers created, planned, or visited on this date and their status</p>
        </div>
        <div class="text-xs font-bold text-blue-700 bg-blue-50 px-3 py-1.5 rounded-full border border-blue-100">
            Completed: <span id="tbl-completed-count" class="font-extrabold">0</span> / <span id="tbl-total-count" class="font-extrabold">0</span>
        </div>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm font-medium text-gray-600">
            <thead class="bg-gray-50 text-xs text-gray-400 font-bold uppercase tracking-wider border-b-2 border-gray-100">
                <tr>
                    <th class="px-6 py-3.5">#</th>
                    <th class="px-6 py-3.5">Customer Name</th>
                    <th class="px-6 py-3.5">Phone</th>
                    <th class="px-6 py-3.5">City</th>
                    <th class="px-6 py-3.5">Coordinates</th>
                    <th class="px-6 py-3.5">Assigned Staff</th>
                    <th class="px-6 py-3.5">Visited By</th>
                    <th class="px-6 py-3.5">Check-in Time</th>
                    <th class="px-6 py-3.5">Duration</th>
                    <th class="px-6 py-3.5">Status</th>
                </tr>
            </thead>
            <tbody id="tbl-cust-rows" class="divide-y divide-gray-100 bg-white">
                <!-- Rows injected dynamically by JS -->
            </tbody>
        </table>
    </div>
</div>

<!-- ═══════ STYLES ═══════ -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
<style>
    #trail-map {
        z-index: 0;
    }

    /* Map toolbar buttons */
    .tmap-btn {
        background: white;
        border: 2px solid #e2e8f0;
        border-radius: 10px;
        padding: 6px 14px;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        cursor: pointer;
        color: #374151;
        font-size: 13px;
        font-weight: 700;
        box-shadow: 0 2px 8px rgba(0, 0, 0, .1);
        transition: all .15s;
    }

    .tmap-btn:hover {
        background: #f1f5f9;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, .12);
    }

    .tmap-btn.active {
        background: #1d4ed8;
        color: white;
        border-color: #1d4ed8;
    }

    .tmap-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: rgba(255, 255, 255, .95);
        backdrop-filter: blur(8px);
        border: 2px solid #e2e8f0;
        border-radius: 10px;
        padding: 5px 12px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, .08);
    }

    /* ── ROUTE STOP MARKERS (image-3 style) ── */
    .tmark-start,
    .tmark-end,
    .tmark-cust {
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        border: 3px solid white;
        font-weight: 900;
        color: white;
        box-shadow: 0 3px 14px rgba(0, 0, 0, .3);
        cursor: pointer;
        transition: transform .2s;
    }

    .tmark-start {
        width: 42px;
        height: 42px;
        font-size: 16px;
        background: #16a34a;
        box-shadow: 0 3px 14px rgba(22, 163, 74, .4);
    }

    .tmark-end {
        width: 42px;
        height: 42px;
        font-size: 16px;
        background: #dc2626;
        box-shadow: 0 3px 14px rgba(220, 38, 38, .4);
    }

    .tmark-cust {
        width: 46px;
        height: 46px;
        font-size: 16px;
        font-weight: 900;
        background: #f59e0b;
        border: 4px solid white;
        box-shadow: 0 4px 18px rgba(245, 158, 11, .5);
        position: relative;
    }

    /* Pulse ring on customer stops */
    .tmark-cust::after {
        content: '';
        position: absolute;
        top: -8px;
        left: -8px;
        width: 58px;
        height: 58px;
        border-radius: 50%;
        border: 3px solid #f59e0b;
        opacity: .4;
        animation: cust-pulse 2s ease-in-out infinite;
    }

    @keyframes cust-pulse {

        0%,
        100% {
            transform: scale(1);
            opacity: .4;
        }

        50% {
            transform: scale(1.3);
            opacity: 0;
        }
    }

    /* Connector arrow (triangle below each stop) */
    .tmark-arrow {
        width: 0;
        height: 0;
        border-left: 7px solid transparent;
        border-right: 7px solid transparent;
        margin-top: -2px;
    }

    .tmark-start-wrap,
    .tmark-end-wrap,
    .tmark-cust-wrap {
        display: flex;
        flex-direction: column;
        align-items: center;
    }

    /* Timeline item cards */
    .tl-item {
        padding: 14px 16px;
        border-bottom: 2px solid #f3f4f6;
        cursor: pointer;
        transition: background .15s;
        position: relative;
    }

    .tl-item:hover {
        background: #f8fafc;
    }

    .tl-item:last-child {
        border-bottom: none;
    }

    .tl-item.active {
        background: #eff6ff;
        border-left: 4px solid #2563eb;
    }

    .tl-num {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background: #f59e0b;
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 13px;
        font-weight: 900;
        flex-shrink: 0;
        box-shadow: 0 2px 8px rgba(245, 158, 11, .4);
    }

    .tl-cust-name {
        font-size: 14px;
        font-weight: 800;
        color: #1e293b;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 190px;
    }

    .tl-time {
        font-size: 11px;
        color: #64748b;
        font-weight: 600;
    }

    .tl-dist {
        font-size: 12px;
        font-weight: 800;
        color: #7c3aed;
    }

    .tl-dur {
        font-size: 11px;
        font-weight: 700;
        color: #2563eb;
        background: #dbeafe;
        padding: 2px 8px;
        border-radius: 99px;
        display: inline-block;
    }

    .tl-notes {
        font-size: 11px;
        font-style: italic;
        color: #64748b;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 5px 10px;
        margin-top: 6px;
    }

    /* Leaflet popup override */
    .leaflet-popup-content-wrapper {
        border-radius: 20px !important;
        padding: 0 !important;
        overflow: hidden;
        box-shadow: 0 20px 60px rgba(0, 0, 0, .18) !important;
        border: none !important;
    }

    .leaflet-popup-content {
        margin: 0 !important;
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
    }

    .leaflet-popup-tip-container {
        display: none;
    }

    .leaflet-popup-close-button {
        top: 10px !important;
        right: 12px !important;
        font-size: 18px !important;
        color: #64748b !important;
        width: 28px !important;
        height: 28px !important;
        display: flex !important;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        transition: background .15s;
    }

    .leaflet-popup-close-button:hover {
        background: #f1f5f9 !important;
    }

    .tp {
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
    }

    .tp-lbl {
        font-size: 10px;
        font-weight: 700;
        color: #94a3b8;
        text-transform: uppercase;
        letter-spacing: .05em;
        line-height: 1;
    }

    .tp-val {
        font-size: 13px;
        font-weight: 700;
        color: #1e293b;
        margin-top: 2px;
        line-height: 1.2;
    }
</style>

<!-- ═══════ SCRIPTS ═══════ -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    (function() {
        'use strict';

        var TRAIL_USER_ID = <?= (int)$user['id'] ?>;
        var USER_COLOR = '<?= $bgColor ?>';
        var USER_INITIALS = '<?= $initials ?>';

        var trailMap, baseLayer, satLayer, isSat = false;
        var trailLine = null,
            startMarker = null,
            endMarker = null;
        var customerMarkers = [];
        var otherCustomerMarkers = [];
        var allPts = [],
            allVisits = [];
        var activeTimelineIdx = -1;
        var lastResponse = null;

        /* ─── Marker factories ─────────────────────────────────────────── */
        function wrapIcon(innerHtml, arrowColor) {
            return '<div style="display:flex;flex-direction:column;align-items:center">' +
                innerHtml +
                '<div style="width:0;height:0;border-left:7px solid transparent;border-right:7px solid transparent;border-top:10px solid ' + arrowColor + ';margin-top:-2px;filter:drop-shadow(0 2px 2px rgba(0,0,0,.2))"></div>' +
                '</div>';
        }

        var startIcon = L.divIcon({
            className: '',
            html: wrapIcon('<div class="tmark-start"><i class="fa fa-play" style="font-size:14px"></i></div>', '#16a34a'),
            iconSize: [42, 56],
            iconAnchor: [21, 56],
            popupAnchor: [0, -58]
        });
        var endIcon = L.divIcon({
            className: '',
            html: wrapIcon('<div class="tmark-end"><i class="fa fa-flag-checkered" style="font-size:14px"></i></div>', '#dc2626'),
            iconSize: [42, 56],
            iconAnchor: [21, 56],
            popupAnchor: [0, -58]
        });

        function makeCustIcon(num) {
            return L.divIcon({
                className: '',
                html: wrapIcon('<div class="tmark-cust">' + num + '</div>', '#f59e0b'),
                iconSize: [46, 62],
                iconAnchor: [23, 62],
                popupAnchor: [0, -64]
            });
        }

        function makeOtherCustIcon(name, color) {
            var initials = (name || 'C').split(' ').slice(0, 2).map(function(w) {
                return w ? w[0].toUpperCase() : '';
            }).join('');
            if (!initials) initials = 'C';
            return L.divIcon({
                className: '',
                html: wrapIcon('<div style="width:40px;height:40px;border-radius:50%;background:' + color + ';color:white;display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:800;border:3px solid white;box-shadow:0 3px 12px ' + color + '66">' + initials + '</div>', color),
                iconSize: [40, 56],
                iconAnchor: [20, 56],
                popupAnchor: [0, -58]
            });
        }

        function otherCustPopup(c, date, isVisited) {
            var lat = parseFloat(c.latitude);
            var lng = parseFloat(c.longitude);
            var assigned = c.assigned_staff || '—';
            var phone = c.phone || '—';
            var city = c.city || '—';
            var statusText = isVisited ? 'Visited by other staff' : 'Unvisited';
            var statusCls = isVisited ? 'background:rgba(255,255,255,0.25);color:#fff;' : 'background:rgba(0,0,0,0.15);color:rgba(255,255,255,0.9);';
            var headerBg = isVisited ? 'linear-gradient(135deg,#16a34a,#15803d)' : 'linear-gradient(135deg,#ef4444,#dc2626)';

            var visitsHtml = '';
            if (c.visits && c.visits.length) {
                c.visits.forEach(function(v) {
                    visitsHtml += '<div style="margin-top:6px;padding:6px 10px;background:#f0fdf4;border-radius:8px;border:1px solid #bbf7d0;font-size:11px">' +
                        '<strong>Visited by:</strong> ' + CRM.esc(v.visited_by_staff) + '<br>' +
                        '<strong>Time:</strong> ' + fmtT(v.check_in_at) + ' - ' + (v.check_out_at ? fmtT(v.check_out_at) : 'ongoing') +
                        '</div>';
                });
            } else {
                visitsHtml = '<div style="margin-top:6px;color:#94a3b8;font-size:11px;font-style:italic">No visits recorded on this date</div>';
            }

            return '<div class="tp" style="min-width:260px; font-family:-apple-system,BlinkMacSystemFont,Segoe UI,Roboto,sans-serif">' +
                '<div class="tp-hdr" style="background:' + headerBg + ';color:#fff;padding:12px 14px">' +
                '<div style="font-size:14px;font-weight:800;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">' + CRM.esc(c.name) + '</div>' +
                '<span style="' + statusCls + 'font-size:9px;font-weight:700;padding:2px 8px;border-radius:99px;margin-top:4px;display:inline-block">' + statusText + '</span>' +
                '</div>' +
                '<div class="tp-body" style="padding:12px 14px;background:#fff;font-size:12px;color:#475569">' +
                '<div style="display:grid;grid-template-cols:1fr 1fr;gap:8px;padding-bottom:8px;border-bottom:1px solid #f1f5f9;margin-bottom:8px">' +
                '<div><div style="font-size:9px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.05em">Phone</div><div style="font-weight:700;color:#1e293b">' + CRM.esc(phone) + '</div></div>' +
                '<div><div style="font-size:9px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.05em">City</div><div style="font-weight:700;color:#1e293b">' + CRM.esc(city) + '</div></div>' +
                '<div style="grid-column: span 2"><div style="font-size:9px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.05em">Assigned Staff</div><div style="font-weight:700;color:#1e293b">' + CRM.esc(assigned) + '</div></div>' +
                '</div>' +
                '<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px"><span>Coordinates:</span><span style="font-family:monospace;font-size:11px;color:#64748b">' + lat.toFixed(5) + ', ' + lng.toFixed(5) + '</span></div>' +
                '<div><div style="font-size:9px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.05em;margin-bottom:4px">Visits on ' + date + '</div>' + visitsHtml + '</div>' +
                '</div></div>';
        }

        /* ─── Helpers ──────────────────────────────────────────────────── */
        function haversineKm(lat1, lng1, lat2, lng2) {
            var R = 6371,
                dLat = (lat2 - lat1) * Math.PI / 180,
                dLng = (lng2 - lng1) * Math.PI / 180;
            var a = Math.sin(dLat / 2) * Math.sin(dLat / 2) + Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) * Math.sin(dLng / 2) * Math.sin(dLng / 2);
            return R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
        }

        function findClosestIdx(timeStr, pts) {
            var t = new Date(timeStr).getTime(),
                minD = Infinity,
                idx = 0;
            pts.forEach(function(p, i) {
                var d = Math.abs(new Date(p.recorded_at).getTime() - t);
                if (d < minD) {
                    minD = d;
                    idx = i;
                }
            });
            return idx;
        }

        function findClosestByLoc(lat, lng, pts) {
            var best = null,
                minD = Infinity;
            pts.forEach(function(p) {
                var d = haversineKm(lat, lng, +p.latitude, +p.longitude);
                if (d < minD) {
                    minD = d;
                    best = p;
                }
            });
            return best;
        }

        function durStr(a, b) {
            if (!b) return '<span style="color:#f59e0b;font-weight:700">Still checked in</span>';
            var m = Math.round((new Date(b) - new Date(a)) / 60000);
            if (m < 60) return m + ' min' + (m !== 1 ? 's' : '');
            var h = Math.floor(m / 60),
                r = m % 60;
            return h + 'h' + (r ? ' ' + r + 'm' : '');
        }

        function fmtT(s) {
            if (!s) return '—';
            return new Date(s).toLocaleTimeString('en-US', {
                hour: '2-digit',
                minute: '2-digit',
                hour12: true
            });
        }

        function straightSeg(fromIdx, toIdx, pts) {
            var km = 0;
            for (var j = fromIdx + 1; j <= toIdx; j++)
                km += haversineKm(+pts[j - 1].latitude, +pts[j - 1].longitude, +pts[j].latitude, +pts[j].longitude);
            return km;
        }

        /* ─── Customer popup HTML ──────────────────────────────────────── */
        function custPopup(v, c, num) {
            var lat = parseFloat(v.check_in_lat || v.customer_lat || c.latitude);
            var lng = parseFloat(v.check_in_lng || v.customer_lng || c.longitude);
            var dur = typeof v.check_out_at === 'string' ? durStr(v.check_in_at, v.check_out_at) : 'Still checked in';
            var isOngoing = !v.check_out_at;
            var durClr = isOngoing ? '#f59e0b' : '#2563eb';

            var phone = c.phone || '—';
            var city = c.city || '—';
            var assigned = c.assigned_staff || '—';
            var visitedBy = v.visited_by_staff || '<?= esc_html($user['name']) ?>';

            var notesHtml = v.notes ?
                '<div style="margin-top:8px;padding:6px 10px;background:#fdf4ff;border-radius:8px;border:1px solid #f3e8ff;font-size:11px;font-style:italic;color:#6b21a8">' +
                '<strong>Notes:</strong> ' + CRM.esc(v.notes) +
                '</div>' :
                '';

            return '<div class="tp" style="min-width:280px; font-family:-apple-system,BlinkMacSystemFont,Segoe UI,Roboto,sans-serif">' +
                '<div class="tp-hdr" style="background:linear-gradient(135deg,#f59e0b,#d97706);color:#fff;padding:12px 14px;display:flex;align-items:center;gap:10px">' +
                '<div style="width:36px;height:36px;border-radius:50%;background:rgba(255,255,255,.25);color:white;display:flex;align-items:center;justify-content:center;font-size:14px;font-weight:900;flex-shrink:0;border:2px solid rgba(255,255,255,.5)">' + num + '</div>' +
                '<div style="flex:1;min-width:0">' +
                '<div style="font-size:14px;font-weight:800;text-shadow:0 1px 2px rgba(0,0,0,.15);line-height:1.2;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">' + CRM.esc(c.name) + '</div>' +
                '<div style="font-size:10px;opacity:0.9;font-weight:700;margin-top:1px"><i class="fa fa-map-marker"></i> Customer Stop #' + num + '</div>' +
                '</div></div>' +
                '<div class="tp-body" style="padding:12px 14px;background:#fff;font-size:12px;color:#475569">'

                // Quick info grid (Phone, City, Assigned, Visited)
                +
                '<div style="display:grid;grid-template-cols:1fr 1fr;gap:8px;padding-bottom:8px;border-bottom:1px solid #f1f5f9;margin-bottom:8px">' +
                '<div><div style="font-size:9px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.05em">Phone</div><div style="font-weight:700;color:#1e293b">' + CRM.esc(phone) + '</div></div>' +
                '<div><div style="font-size:9px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.05em">City</div><div style="font-weight:700;color:#1e293b">' + CRM.esc(city) + '</div></div>' +
                '<div><div style="font-size:9px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.05em">Assigned To</div><div style="font-weight:700;color:#1e293b">' + CRM.esc(assigned) + '</div></div>' +
                '<div><div style="font-size:9px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.05em">Visited By</div><div style="font-weight:700;color:#1e293b">' + CRM.esc(visitedBy) + '</div></div>' +
                '</div>'

                // Check-in & Check-out timing rows
                +
                '<div style="display:flex;flex-direction:column;gap:6px">' +
                '<div style="display:flex;justify-content:space-between;align-items:center"><span>Check-in:</span><strong style="color:#1e293b">' + fmtT(v.check_in_at) + '</strong></div>' +
                '<div style="display:flex;justify-content:space-between;align-items:center"><span>Check-out:</span><strong style="color:#1e293b">' + (v.check_out_at ? fmtT(v.check_out_at) : '<span style="color:#f59e0b">Ongoing</span>') + '</strong></div>' +
                '<div style="display:flex;justify-content:space-between;align-items:center"><span>Duration:</span><strong style="color:' + durClr + '">' + dur + '</strong></div>' +
                '<div style="display:flex;justify-content:space-between;align-items:center"><span>Coordinates:</span><span style="font-family:monospace;font-size:11px;color:#64748b">' + lat.toFixed(5) + ', ' + lng.toFixed(5) + '</span></div>' +
                '</div>'

                +
                notesHtml +
                '</div></div>';
        }

        function startPopup(pt, ll) {
            var time = pt.recorded_at ? pt.recorded_at.substr(11, 8) : '—';
            return '<div class="tp" style="min-width:220px">' +
                '<div class="tp-hdr" style="background:linear-gradient(135deg,#16a34a,#15803d);color:#fff;padding:14px 16px;font-size:15px;font-weight:800;display:flex;align-items:center;gap:8px">' +
                '<i class="fa fa-play-circle" style="font-size:18px"></i> Journey Start' +
                '</div>' +
                '<div class="tp-body" style="padding:12px 16px 14px;background:#fff">' +
                '<div class="tp-row" style="display:flex;align-items:center;gap:10px;padding:6px 0;border-bottom:1px solid #f1f5f9">' +
                '<div class="tp-icon" style="width:28px;height:28px;border-radius:8px;display:flex;align-items:center;justify-content:center;background:#f0fdf4;flex-shrink:0"><i class="fa fa-clock-o" style="color:#16a34a;font-size:12px"></i></div>' +
                '<div style="flex:1"><div class="tp-lbl" style="font-size:10px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.05em;line-height:1">Time</div><div class="tp-val" style="font-size:13px;font-weight:700;color:#1e293b;margin-top:2px">' + time + '</div></div>' +
                '</div>' +
                '<div class="tp-row" style="display:flex;align-items:center;gap:10px;padding:6px 0">' +
                '<div class="tp-icon" style="width:28px;height:28px;border-radius:8px;display:flex;align-items:center;justify-content:center;background:#f8fafc;flex-shrink:0"><i class="fa fa-crosshairs" style="color:#64748b;font-size:12px"></i></div>' +
                '<div style="flex:1"><div class="tp-lbl" style="font-size:10px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.05em;line-height:1">Coordinates</div><div class="tp-val" style="font-size:12px;font-family:monospace;font-weight:700;color:#1e293b;margin-top:2px">' + ll[0].toFixed(5) + ', ' + ll[1].toFixed(5) + '</div></div>' +
                '</div>' +
                '</div></div>';
        }

        function endPopup(pt, ll) {
            var time = pt.recorded_at ? pt.recorded_at.substr(11, 8) : '—';
            return '<div class="tp" style="min-width:220px">' +
                '<div class="tp-hdr" style="background:linear-gradient(135deg,#dc2626,#b91c1c);color:#fff;padding:14px 16px;font-size:15px;font-weight:800;display:flex;align-items:center;gap:8px">' +
                '<i class="fa fa-flag-checkered" style="font-size:18px"></i> Journey End' +
                '</div>' +
                '<div class="tp-body" style="padding:12px 16px 14px;background:#fff">' +
                '<div class="tp-row" style="display:flex;align-items:center;gap:10px;padding:6px 0;border-bottom:1px solid #f1f5f9">' +
                '<div class="tp-icon" style="width:28px;height:28px;border-radius:8px;display:flex;align-items:center;justify-content:center;background:#fef2f2;flex-shrink:0"><i class="fa fa-clock-o" style="color:#dc2626;font-size:12px"></i></div>' +
                '<div style="flex:1"><div class="tp-lbl" style="font-size:10px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.05em;line-height:1">Time</div><div class="tp-val" style="font-size:13px;font-weight:700;color:#1e293b;margin-top:2px">' + time + '</div></div>' +
                '</div>' +
                '<div class="tp-row" style="display:flex;align-items:center;gap:10px;padding:6px 0">' +
                '<div class="tp-icon" style="width:28px;height:28px;border-radius:8px;display:flex;align-items:center;justify-content:center;background:#f8fafc;flex-shrink:0"><i class="fa fa-crosshairs" style="color:#64748b;font-size:12px"></i></div>' +
                '<div style="flex:1"><div class="tp-lbl" style="font-size:10px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.05em;line-height:1">Coordinates</div><div class="tp-val" style="font-size:12px;font-family:monospace;font-weight:700;color:#1e293b;margin-top:2px">' + ll[0].toFixed(5) + ', ' + ll[1].toFixed(5) + '</div></div>' +
                '</div>' +
                '</div></div>';
        }

        /* ─── Build timeline panel ──────────────────────────────────────── */
        function buildTimeline(visits, pts, segFn) {
            var $list = $('#timeline-list').empty();
            $('#timeline-empty').hide();

            if (!visits.length) {
                $('#no-visits-panel').show();
                $('#timeline-list').hide();
                $('#timeline-summary').text('0 customer visits');
                return;
            }
            $('#no-visits-panel').hide();
            $('#timeline-list').show();
            $('#timeline-summary').text(visits.length + ' visit' + (visits.length !== 1 ? 's' : '') + ' today');

            var prevIdx = 0;
            visits.forEach(function(v, i) {
                var custName = v.customer_name || 'Unknown Customer';
                var ciLat = parseFloat(v.check_in_lat || v.customer_lat);
                var ciLng = parseFloat(v.check_in_lng || v.customer_lng);
                var ptIdx = findClosestIdx(v.check_in_at, pts);
                var segKm = segFn(prevIdx, ptIdx, pts);
                prevIdx = ptIdx;

                var custId = parseInt(v.customer_id, 10);
                var cObj = null;
                if (lastResponse && lastResponse.data.day_customers) {
                    cObj = lastResponse.data.day_customers.find(function(x) {
                        return parseInt(x.id, 10) === custId;
                    });
                }
                if (!cObj) {
                    cObj = {
                        name: custName,
                        latitude: ciLat,
                        longitude: ciLng,
                        phone: '—',
                        city: '—',
                        assigned_staff: '—'
                    };
                }

                /* map marker */
                if (ciLat && ciLng) {
                    var m = L.marker([ciLat, ciLng], {
                            icon: makeCustIcon(i + 1)
                        })
                        .bindPopup(custPopup(v, cObj, i + 1), {
                            maxWidth: 280,
                            className: 'leaflet-custom-popup',
                            autoPanPaddingTop: 80
                        })
                        .addTo(trailMap);
                    m.on('click', function() {
                        setActiveItem(i);
                    });
                    customerMarkers.push(m);
                } else {
                    customerMarkers.push(null);
                }

                /* timeline row */
                var dur = durStr(v.check_in_at, v.check_out_at);
                var distHtml = segKm > 0 ? '<span class="tl-dist">+' + segKm.toFixed(2) + ' km</span>' : '';
                var notesHtml = v.notes ? '<div class="tl-notes"><i class="fa fa-comment-o" style="margin-right:4px;color:#94a3b8"></i>' + CRM.esc(v.notes) + '</div>' : '';

                var $row = $(
                    '<div class="tl-item" data-idx="' + i + '">' +
                    '<div style="display:flex;align-items:flex-start;gap:12px">' +
                    '<div class="tl-num">' + (i + 1) + '</div>' +
                    '<div style="flex:1;min-width:0">' +
                    '<div class="tl-cust-name" title="' + CRM.esc(custName) + '">' + CRM.esc(custName) + '</div>' +
                    '<div style="display:flex;gap:8px;margin-top:4px;flex-wrap:wrap;align-items:center">' +
                    '<span class="tl-time"><i class="fa fa-clock-o" style="color:#94a3b8;margin-right:2px"></i>' + fmtT(v.check_in_at) + ' – ' + (v.check_out_at ? fmtT(v.check_out_at) : '<span style="color:#f59e0b">ongoing</span>') + '</span>' +
                    '</div>' +
                    '<div style="display:flex;align-items:center;gap:8px;margin-top:6px">' +
                    '<span class="tl-dur">' + dur + '</span>' +
                    distHtml +
                    '</div>' +
                    notesHtml +
                    '</div>' +
                    '</div></div>'
                );
                $row.on('click', function() {
                    setActiveItem(i);
                    if (ciLat && ciLng) {
                        trailMap.setView([ciLat, ciLng], 16, {
                            animate: true
                        });
                        if (customerMarkers[i]) customerMarkers[i].openPopup();
                    }
                });
                $list.append($row);
            });

            if (lastResponse) {
                renderCustomersTable(lastResponse, $('#trail-date').val());
            }
        }

        function setActiveItem(idx) {
            $('.tl-item').removeClass('active');
            $('.tl-item[data-idx="' + idx + '"]').addClass('active');
            activeTimelineIdx = idx;
        }

        /* ─── Straight line draw ────────────────────────────────────────── */
        function drawStraightLine(pts, visits) {
            var latlngs = pts.map(function(p) {
                return [+p.latitude, +p.longitude];
            });
            trailLine = L.polyline(latlngs, {
                color: '#1e40af',
                weight: 7,
                opacity: 0.9,
                lineJoin: 'round',
                lineCap: 'round'
            }).addTo(trailMap);
            trailMap.fitBounds(trailLine.getBounds(), {
                padding: [60, 60]
            });
            var dist = 0;
            for (var i = 1; i < latlngs.length; i++)
                dist += haversineKm(latlngs[i - 1][0], latlngs[i - 1][1], latlngs[i][0], latlngs[i][1]);
            $('#stat-dist').text(dist.toFixed(2) + ' km');
            $('#trail-route-type').html('<i class="fa fa-minus" style="margin-right:4px"></i>Straight-line estimate');
            buildTimeline(visits, pts, straightSeg);
        }

        // Customer Details Card functions removed

        function renderCustomersTable(res, date) {
            var completedCnt = 0;
            var totalCnt = res.data.day_customers ? res.data.day_customers.length : 0;
            var $tbody = $('#tbl-cust-rows').empty();
            $('#tbl-date-str').text(date);

            if (res.data.day_customers) {
                res.data.day_customers.forEach(function(c, idx) {
                    var lat = c.latitude ? parseFloat(c.latitude) : 0;
                    var lng = c.longitude ? parseFloat(c.longitude) : 0;
                    var hasCoords = lat !== 0 && lng !== 0 && !isNaN(lat) && !isNaN(lng);
                    var assigned = c.assigned_staff || '<span class="text-gray-300">—</span>';

                    // Find visits for this customer on this date
                    var status = 'Pending';
                    var statusClr = 'bg-gray-100 text-gray-500 border-gray-200';
                    var visitedBy = '<span class="text-gray-300">—</span>';
                    var checkIn = '—';
                    var duration = '—';

                    if (c.visits && c.visits.length) {
                        var v = c.visits[0]; // Take the first visit on that day
                        visitedBy = CRM.esc(v.visited_by_staff);
                        checkIn = fmtT(v.check_in_at);
                        duration = durStr(v.check_in_at, v.check_out_at);

                        if (v.check_out_at) {
                            status = 'Completed';
                            statusClr = 'bg-green-50 text-green-700 border-green-200';
                            completedCnt++;
                        } else {
                            status = 'In Progress';
                            statusClr = 'bg-orange-50 text-orange-700 border-orange-200';
                        }
                    }

                    // Check if visited by selected staff (which is already plotted in customerMarkers by buildTimeline)
                    var isVisitedByCurrent = allVisits.some(function(v) {
                        return parseInt(v.customer_id, 10) === parseInt(c.id, 10);
                    });
                    var isVisitedByAny = c.visits && c.visits.length > 0;

                    if (hasCoords && !isVisitedByCurrent) {
                        var color = isVisitedByAny ? '#16a34a' : '#ef4444'; // Green for visited by other staff, Red for unvisited
                        var m = L.marker([lat, lng], {
                                icon: makeOtherCustIcon(c.name, color)
                            })
                            .bindPopup(otherCustPopup(c, date, isVisitedByAny), {
                                maxWidth: 260,
                                autoPanPaddingTop: 80
                            })
                            .addTo(trailMap);
                        m.on('click', function() {
                            showCustomerDetails(c);
                        });
                        otherCustomerMarkers.push(m);
                        c.mapMarker = m;
                    } else if (isVisitedByCurrent) {
                        // Find the marker in customerMarkers
                        var vIdx = allVisits.findIndex(function(v) {
                            return parseInt(v.customer_id, 10) === parseInt(c.id, 10);
                        });
                        if (vIdx !== -1 && customerMarkers[vIdx]) {
                            c.mapMarker = customerMarkers[vIdx];
                        }
                    }

                    var $row = $(
                        '<tr class="cursor-pointer hover:bg-blue-50 transition-colors">' +
                        '<td class="px-6 py-4 font-bold text-gray-400">' + (idx + 1) + '</td>' +
                        '<td class="px-6 py-4 font-extrabold text-gray-800">' + CRM.esc(c.name) + '</td>' +
                        '<td class="px-6 py-4 font-semibold text-gray-600">' + CRM.esc(c.phone || '—') + '</td>' +
                        '<td class="px-6 py-4 font-semibold text-gray-600">' + CRM.esc(c.city || '—') + '</td>' +
                        '<td class="px-6 py-4 font-mono text-xs text-gray-400">' + (hasCoords ? lat.toFixed(5) + ', ' + lng.toFixed(5) : '—') + '</td>' +
                        '<td class="px-6 py-4 font-semibold text-gray-600">' + assigned + '</td>' +
                        '<td class="px-6 py-4 font-semibold text-gray-700">' + visitedBy + '</td>' +
                        '<td class="px-6 py-4 text-xs font-semibold text-gray-500">' + checkIn + '</td>' +
                        '<td class="px-6 py-4"><span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-50 text-blue-700 border border-blue-100">' + duration + '</span></td>' +
                        '<td class="px-6 py-4"><span class="px-2.5 py-1 rounded-full text-xs font-bold border ' + statusClr + '">' + status + '</span></td>' +
                        '</tr>'
                    );

                    $row.on('click', function(e) {
                        if (hasCoords && c.mapMarker) {
                            trailMap.setView([lat, lng], 16, {
                                animate: true
                            });
                            c.mapMarker.openPopup();
                        }
                    });

                    $tbody.append($row);
                });
            }

            $('#tbl-completed-count').text(completedCnt);
            $('#tbl-total-count').text(totalCnt);
            setTimeout(function() {
                if (trailMap) {
                    trailMap.invalidateSize();
                }
            }, 100);
        }

        /* ─── Load trail ────────────────────────────────────────────────── */
        function loadTrail() {
            var date = $('#trail-date').val();
            var $btn = $('#btn-load-trail');
            CRM.btn_loading($btn);
            $('#trail-info').text('Loading…').css('color', '#64748b');
            $('#trail-loading-badge').hide();
            $('#trail-route-type').text('');

            $.getJSON(BASE_URL + 'tracking/trail_data', {
                user_id: TRAIL_USER_ID,
                date: date
            }, function(res) {
                allPts = res.data.trail || [];
                allVisits = res.data.visits || [];
                lastResponse = res;

                /* clear layers */
                if (trailLine) {
                    trailMap.removeLayer(trailLine);
                    trailLine = null;
                }
                if (startMarker) {
                    trailMap.removeLayer(startMarker);
                    startMarker = null;
                }
                if (endMarker) {
                    trailMap.removeLayer(endMarker);
                    endMarker = null;
                }
                customerMarkers.forEach(function(m) {
                    if (m) trailMap.removeLayer(m);
                });
                customerMarkers = [];
                otherCustomerMarkers.forEach(function(m) {
                    if (m) trailMap.removeLayer(m);
                });
                otherCustomerMarkers = [];
                trailMap.off('click');

                if (!allPts.length) {
                    $('#trail-info').text('No GPS data for this date').css('color', '#d97706');
                    $('#trail-stats').hide();
                    $('#timeline-empty').show();
                    $('#timeline-list').hide();
                    $('#no-visits-panel').hide();
                    $('#timeline-summary').text('Load a date to see visits');
                    $('#info-timeline-count').text('0');
                    renderCustomersTable(res, date);
                    CRM.btn_reset($btn);
                    return;
                }

                var lls = allPts.map(function(p) {
                    return [+p.latitude, +p.longitude];
                });

                /* start & end */
                startMarker = L.marker(lls[0], {
                        icon: startIcon
                    })
                    .bindPopup(startPopup(allPts[0], lls[0]), {
                        maxWidth: 240,
                        autoPanPaddingTop: 80
                    }).addTo(trailMap);
                endMarker = L.marker(lls[lls.length - 1], {
                        icon: endIcon
                    })
                    .bindPopup(endPopup(allPts[allPts.length - 1], lls[lls.length - 1]), {
                        maxWidth: 240,
                        autoPanPaddingTop: 80
                    }).addTo(trailMap);

                /* click for nearest timestamp */
                trailMap.on('click', function(e) {
                    var closest = findClosestByLoc(e.latlng.lat, e.latlng.lng, allPts);
                    if (closest) {
                        var d = haversineKm(e.latlng.lat, e.latlng.lng, +closest.latitude, +closest.longitude);
                        if (d < 0.2) {
                            var time = closest.recorded_at ? closest.recorded_at.substr(11, 8) : '—';
                            var speed = (closest.speed != null && closest.speed !== '') ? parseFloat(closest.speed).toFixed(1) + ' km/h' : '—';
                            var bat = (closest.battery_level != null && closest.battery_level !== '') ? closest.battery_level + '%' : '—';
                            L.popup({
                                    autoPanPaddingTop: 80
                                }).setLatLng([+closest.latitude, +closest.longitude])
                                .setContent(
                                    '<div class="tp" style="min-width:200px">' +
                                    '<div class="tp-hdr" style="background:linear-gradient(135deg,#3b82f6,#1d4ed8);color:#fff;padding:10px 14px;font-size:13px;font-weight:800;display:flex;align-items:center;gap:6px">' +
                                    '<i class="fa fa-map-pin"></i> GPS Ping Details' +
                                    '</div>' +
                                    '<div class="tp-body" style="padding:10px 14px;background:#fff">' +
                                    '<div class="tp-row" style="display:flex;align-items:center;gap:10px;padding:5px 0;border-bottom:1px solid #f1f5f9">' +
                                    '<div class="tp-icon" style="width:24px;height:24px;border-radius:6px;display:flex;align-items:center;justify-content:center;background:#eff6ff;flex-shrink:0"><i class="fa fa-clock-o" style="color:#3b82f6;font-size:11px"></i></div>' +
                                    '<div style="flex:1"><div class="tp-lbl" style="font-size:9px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.05em;line-height:1">Time</div><div class="tp-val" style="font-size:12px;font-weight:700;color:#1e293b;margin-top:1px">' + time + '</div></div>' +
                                    '</div>' +
                                    '<div class="tp-row" style="display:flex;align-items:center;gap:10px;padding:5px 0;border-bottom:1px solid #f1f5f9">' +
                                    '<div class="tp-icon" style="width:24px;height:24px;border-radius:6px;display:flex;align-items:center;justify-content:center;background:#f0fdf4;flex-shrink:0"><i class="fa fa-dashboard" style="color:#16a34a;font-size:11px"></i></div>' +
                                    '<div style="flex:1"><div class="tp-lbl" style="font-size:9px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.05em;line-height:1">Speed</div><div class="tp-val" style="font-size:12px;font-weight:700;color:#1e293b;margin-top:1px">' + speed + '</div></div>' +
                                    '</div>' +
                                    '<div class="tp-row" style="display:flex;align-items:center;gap:10px;padding:5px 0">' +
                                    '<div class="tp-icon" style="width:24px;height:24px;border-radius:6px;display:flex;align-items:center;justify-content:center;background:#fffbeb;flex-shrink:0"><i class="fa fa-battery-half" style="color:#f59e0b;font-size:11px"></i></div>' +
                                    '<div style="flex:1"><div class="tp-lbl" style="font-size:9px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.05em;line-height:1">Battery</div><div class="tp-val" style="font-size:12px;font-weight:700;color:#1e293b;margin-top:1px">' + bat + '</div></div>' +
                                    '</div>' +
                                    '</div></div>'
                                )
                                .openOn(trailMap);
                        }
                    }
                });

                /* stats */
                $('#stat-points').text(allPts.length);
                $('#stat-start').text(allPts[0].recorded_at.substr(11, 5));
                $('#stat-end').text(allPts[allPts.length - 1].recorded_at.substr(11, 5));
                $('#trail-stats').show();
                $('#trail-info').text(allPts.length + ' GPS pts · ' + allVisits.length + ' visits').css('color', '#16a34a');

                $('#info-timeline-count').text(allVisits.length);

                /* OSRM road routing (≤80 waypoints) */
                if (allPts.length >= 2) {
                    var minD = 0.03,
                        filtered = [];
                    while (minD < 2.0) {
                        filtered = [allPts[0]];
                        for (var i = 1; i < allPts.length - 1; i++) {
                            var last = filtered[filtered.length - 1];
                            if (haversineKm(+last.latitude, +last.longitude, +allPts[i].latitude, +allPts[i].longitude) >= minD)
                                filtered.push(allPts[i]);
                        }
                        filtered.push(allPts[allPts.length - 1]);
                        if (filtered.length <= 80) break;
                        minD += 0.05;
                    }
                    var coords = filtered.map(function(p) {
                        return (+p.longitude) + ',' + ((+p.latitude));
                    }).join(';');
                    var osrmUrl = 'https://router.project-osrm.org/route/v1/driving/' + coords + '?overview=full&geometries=geojson&steps=false';
                    $('#trail-loading-badge').show();
                    $.ajax({
                        url: osrmUrl,
                        dataType: 'json',
                        timeout: 10000,
                        success: function(r) {
                            $('#trail-loading-badge').hide();
                            if (r.code === 'Ok' && r.routes && r.routes.length) {
                                var route = r.routes[0];
                                trailLine = L.geoJSON(route.geometry, {
                                    style: {
                                        color: '#1e40af',
                                        weight: 8,
                                        opacity: 0.92,
                                        lineJoin: 'round',
                                        lineCap: 'round'
                                    }
                                }).addTo(trailMap);
                                trailMap.fitBounds(trailLine.getBounds(), {
                                    padding: [60, 60]
                                });
                                $('#stat-dist').text((route.distance / 1000).toFixed(2) + ' km');
                                $('#trail-route-type').html('<i class="fa fa-road" style="margin-right:4px;color:#16a34a"></i>Road route via OSRM');
                                var legs = route.legs;
                                buildTimeline(allVisits, filtered, function(fromIdx, toIdx, pts) {
                                    var meters = 0;
                                    for (var j = fromIdx; j < toIdx; j++) meters += legs[j] ? legs[j].distance : 0;
                                    return meters / 1000;
                                });
                            } else {
                                drawStraightLine(allPts, allVisits);
                            }
                        },
                        error: function() {
                            $('#trail-loading-badge').hide();
                            drawStraightLine(allPts, allVisits);
                        },
                        complete: function() {
                            CRM.btn_reset($btn);
                        }
                    });
                } else {
                    drawStraightLine(allPts, allVisits);
                    CRM.btn_reset($btn);
                }
            }).fail(function() {
                $('#trail-info').text('Error loading trail').css('color', '#dc2626');
                CRM.btn_reset($btn);
            });
        }

        /* ─── Fit & Satellite ───────────────────────────────────────────── */
        $('#btn-fit-trail').on('click', function() {
            if (trailLine) trailMap.fitBounds(trailLine.getBounds(), {
                padding: [60, 60],
                animate: true
            });
            else if (allPts.length) {
                var lls = allPts.map(function(p) {
                    return [+p.latitude, +p.longitude];
                });
                trailMap.fitBounds(L.latLngBounds(lls), {
                    padding: [60, 60],
                    animate: true
                });
            }
        });
        $('#btn-sat-trail').on('click', function() {
            var $b = $(this);
            if (!isSat) {
                if (!satLayer) satLayer = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
                    attribution: 'Esri',
                    maxZoom: 19
                });
                satLayer.addTo(trailMap);
                baseLayer.remove();
                $b.addClass('active');
                isSat = true;
            } else {
                if (satLayer) satLayer.remove();
                baseLayer.addTo(trailMap);
                $b.removeClass('active');
                isSat = false;
            }
        });

        /* ─── Map init ──────────────────────────────────────────────────── */
        $(function() {
            trailMap = L.map('trail-map', {
                center: [20.5937, 78.9629],
                zoom: 5,
                zoomControl: false
            });
            L.control.zoom({
                position: 'bottomright'
            }).addTo(trailMap);
            baseLayer = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://openstreetmap.org">OpenStreetMap</a>',
                maxZoom: 19
            }).addTo(trailMap);
            trailMap.on('mousemove', function(e) {
                $('#map-coord-display').text('Lat ' + e.latlng.lat.toFixed(4) + '  Lng ' + e.latlng.lng.toFixed(4));
            });
            setTimeout(function() {
                trailMap.invalidateSize();
            }, 300);
            $('#btn-load-trail').on('click', loadTrail);
            loadTrail();
        });

    })();
</script>