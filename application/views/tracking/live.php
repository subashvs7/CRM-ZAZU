<?php
$total_staff = count($staff);
?>
<!-- ═══════════════════════ PAGE HEADER ═══════════════════════ -->
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-5">
    <div class="flex items-center gap-3">
        <div class="w-12 h-12 rounded-2xl flex items-center justify-center flex-shrink-0 shadow-lg"
             style="background:linear-gradient(135deg,#1d4ed8,#7c3aed); box-shadow:0 4px 14px rgba(37,99,235,.35)">
            <i class="fa fa-map-marker text-white text-xl"></i>
        </div>
        <div>
            <h1 class="text-2xl font-extrabold text-gray-900">Live Tracking</h1>
            <nav class="text-xs text-gray-400 flex items-center gap-1.5 mt-0.5">
                <a href="<?= base_url('dashboard') ?>" class="hover:text-blue-600 transition-colors font-medium">Home</a>
                <i class="fa fa-angle-right text-[10px]"></i>
                <span class="text-gray-500 font-medium">Live Tracking</span>
            </nav>
        </div>
    </div>
    <div class="flex items-center gap-2 flex-wrap">
        <span id="live-pulse-badge" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-full text-sm font-bold" style="background:#dcfce7;color:#16a34a;border:2px solid #bbf7d0">
            <span class="live-dot"></span> LIVE
        </span>
        <span class="inline-flex items-center gap-1.5 px-4 py-2 rounded-full text-sm font-bold" style="background:#dbeafe;color:#1d4ed8;border:2px solid #bfdbfe">
            <i class="fa fa-users"></i> <span id="hdr-online">0</span> / <?= $total_staff ?> Online
        </span>
        <a href="<?= base_url('tracking/ping_status') ?>" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-bold border-2 border-gray-200 rounded-xl hover:bg-gray-50 text-gray-600 transition-colors bg-white shadow-sm">
            <i class="fa fa-list-alt"></i> Ping Log
        </a>
    </div>
</div>

<!-- ═══════════════════════ STATS BAR ═══════════════════════ -->
<div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-5" id="stats-bar">
    <div class="bg-white rounded-2xl border-2 border-blue-100 px-5 py-4 flex items-center gap-4 shadow-sm" style="border-left:5px solid #2563eb">
        <div class="w-12 h-12 rounded-2xl flex items-center justify-center flex-shrink-0" style="background:#dbeafe">
            <i class="fa fa-users text-blue-600 text-lg"></i>
        </div>
        <div>
            <div class="text-3xl font-extrabold text-blue-600 leading-tight" id="stat-total"><?= $total_staff ?></div>
            <div class="text-xs text-gray-400 font-bold uppercase tracking-widest mt-0.5">Total Staff</div>
        </div>
    </div>
    <div class="bg-white rounded-2xl border-2 border-green-100 px-5 py-4 flex items-center gap-4 shadow-sm" style="border-left:5px solid #16a34a">
        <div class="w-12 h-12 rounded-2xl flex items-center justify-center flex-shrink-0" style="background:#dcfce7">
            <span class="w-4 h-4 rounded-full bg-green-500 live-dot-sm inline-block"></span>
        </div>
        <div>
            <div class="text-3xl font-extrabold text-green-600 leading-tight" id="stat-online">0</div>
            <div class="text-xs text-gray-400 font-bold uppercase tracking-widest mt-0.5">Online Now</div>
        </div>
    </div>
    <div class="bg-white rounded-2xl border-2 border-yellow-100 px-5 py-4 flex items-center gap-4 shadow-sm" style="border-left:5px solid #d97706">
        <div class="w-12 h-12 rounded-2xl flex items-center justify-center flex-shrink-0" style="background:#fef9c3">
            <i class="fa fa-map-signs text-yellow-600 text-lg"></i>
        </div>
        <div>
            <div class="text-3xl font-extrabold text-yellow-600 leading-tight" id="stat-visits">0</div>
            <div class="text-xs text-gray-400 font-bold uppercase tracking-widest mt-0.5">Visits Today</div>
        </div>
    </div>
    <div class="bg-white rounded-2xl border-2 border-pink-100 px-5 py-4 flex items-center gap-4 shadow-sm" style="border-left:5px solid #db2777">
        <div class="w-12 h-12 rounded-2xl flex items-center justify-center flex-shrink-0" style="background:#fce7f3">
            <i class="fa fa-clock-o text-pink-600 text-lg"></i>
        </div>
        <div>
            <div class="text-base font-extrabold text-gray-700 leading-tight" id="stat-updated">—</div>
            <div class="text-xs text-gray-400 font-bold uppercase tracking-widest mt-0.5">Last Refresh</div>
        </div>
    </div>
</div>

<!-- ═══════════════════════ MAIN LAYOUT ═══════════════════════ -->
<div class="flex gap-4" style="height:calc(100vh - 310px); min-height:520px;">

    <!-- ── LEFT STAFF LIST ── -->
    <div class="bg-white rounded-2xl border-2 border-gray-200 flex flex-col flex-shrink-0 shadow-sm" style="width:275px;">
        <div class="px-4 py-3 border-b-2 border-gray-100" style="background:linear-gradient(135deg,#f8faff,#f0f4ff)">
            <div class="flex items-center justify-between mb-2.5">
                <h3 class="text-base font-extrabold text-gray-800">Field Staff</h3>
                <span class="text-xs font-bold text-gray-400">Click to focus</span>
            </div>
            <div class="relative">
                <input type="text" id="staff-search" placeholder="Search staff…"
                       class="w-full pl-9 pr-3 py-2 text-sm font-medium border-2 border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-blue-400 bg-white">
                <i class="fa fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
            </div>
        </div>
        <!-- Filter tabs -->
        <div class="flex border-b-2 border-gray-100 text-xs font-extrabold">
            <button class="staff-filter-btn active flex-1 text-center py-2.5 border-b-2 text-blue-600 border-blue-600 transition-colors" data-filter="all">All</button>
            <button class="staff-filter-btn flex-1 text-center py-2.5 border-b-2 text-gray-400 border-transparent hover:text-green-600 transition-colors" data-filter="online">Online</button>
            <button class="staff-filter-btn flex-1 text-center py-2.5 border-b-2 text-gray-400 border-transparent hover:text-red-500 transition-colors" data-filter="offline">Offline</button>
        </div>
        <div class="flex-1 overflow-y-auto" id="staff-panel">
            <?php foreach ($staff as $s):
                $colors  = ['#2563eb','#7c3aed','#059669','#dc2626','#d97706','#0891b2'];
                $bgColor = $colors[abs(crc32($s['name'])) % count($colors)];
                $initials = implode('', array_map(function($w) { return isset($w[0]) ? strtoupper($w[0]) : ''; }, array_slice(explode(' ', $s['name']), 0, 2)));
            ?>
            <div class="staff-card offline px-3 py-3 border-b-2 border-gray-50 cursor-pointer hover:bg-blue-50 transition-all"
                 id="card-<?= $s['id'] ?>" data-uid="<?= $s['id'] ?>" data-name="<?= esc_html($s['name']) ?>" data-status="offline"
                 onclick="focusStaff(<?= $s['id'] ?>)">
                <div class="flex items-center gap-3">
                    <div class="relative flex-shrink-0">
                        <div class="w-10 h-10 rounded-full flex items-center justify-center text-white text-sm font-extrabold shadow-md"
                             style="background:<?= $bgColor ?>"><?= $initials ?></div>
                        <span class="absolute -bottom-0.5 -right-0.5 w-3.5 h-3.5 rounded-full border-2 border-white bg-gray-300 status-dot" id="dot-<?= $s['id'] ?>"></span>
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="text-sm font-bold text-gray-800 truncate"><?= esc_html($s['name']) ?></div>
                        <div class="text-xs text-gray-400 mt-0.5 truncate font-medium" id="loc-<?= $s['id'] ?>">No GPS signal</div>
                    </div>
                    <div class="flex flex-col items-end gap-1 flex-shrink-0">
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-gray-100 text-gray-400" id="vbadge-<?= $s['id'] ?>">0 visits</span>
                        <span class="text-[10px] font-semibold text-gray-300" id="bat-<?= $s['id'] ?>"></span>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- ── MAP ── -->
    <div class="flex-1 bg-white rounded-2xl border-2 border-gray-200 flex flex-col overflow-hidden shadow-sm" style="position:relative">
        <!-- Map toolbar overlay -->
        <div class="absolute top-3 left-3 right-3 flex items-center justify-between pointer-events-none" style="z-index:600">
            <div class="flex items-center gap-2 pointer-events-auto">
                <button id="btn-fit" title="Fit all markers" onclick="fitAll()" class="lmap-btn">
                    <i class="fa fa-expand"></i> Fit All
                </button>
                <button id="btn-sat" title="Satellite view" onclick="toggleSat()" class="lmap-btn">
                    <i class="fa fa-globe"></i> Satellite
                </button>
            </div>
            <div class="flex items-center gap-2 pointer-events-auto">
                <div class="lmap-badge">
                    <span class="live-dot-sm"></span>
                    <span class="text-xs text-gray-500 font-mono font-medium" id="map-coord-display">Lat — Lng —</span>
                </div>
                <div class="lmap-badge">
                    <i class="fa fa-refresh text-blue-500 text-xs" id="refresh-spin"></i>
                    <span class="text-xs text-gray-500 font-semibold">Updated: <strong id="last-update">—</strong></span>
                </div>
            </div>
        </div>
        <div id="live-map" class="flex-1 w-full" style="min-height:300px"></div>
    </div>

    <!-- ── RIGHT DETAIL PANEL ── -->
    <div class="bg-white rounded-2xl border-2 border-gray-200 flex flex-col flex-shrink-0 shadow-sm" style="width:268px;" id="detail-panel">
        <div class="px-4 py-3 border-b-2 border-gray-100 flex items-center justify-between" style="background:linear-gradient(135deg,#f8faff,#f0f4ff)">
            <h3 class="text-base font-extrabold text-gray-800">Staff Detail</h3>
            <span class="text-xs font-bold text-gray-400">Select a marker</span>
        </div>
        <!-- Empty state -->
        <div id="detail-empty" class="flex-1 flex flex-col items-center justify-center text-center px-5 py-10">
            <div class="w-16 h-16 rounded-full bg-gray-100 flex items-center justify-center mb-4">
                <i class="fa fa-map-marker text-gray-300 text-2xl"></i>
            </div>
            <p class="text-base font-bold text-gray-400">No staff selected</p>
            <p class="text-xs text-gray-300 mt-1.5 font-medium">Click a marker or staff card<br>to view details</p>
        </div>
        <!-- Populated detail (content injected by JS showDetail) -->
        <div id="detail-content" style="display:none;flex:1;flex-direction:column;overflow-y:auto">

            <!-- Avatar + name + status -->
            <div style="padding:22px 20px 16px;border-bottom:2px solid #f1f5f9;display:flex;flex-direction:column;align-items:center;text-align:center;background:linear-gradient(180deg,#f8faff,#fff)">
                <div id="det-avatar"
                     style="width:68px;height:68px;border-radius:50%;display:flex;align-items:center;justify-content:center;color:#fff;font-size:22px;font-weight:900;margin-bottom:10px;flex-shrink:0"></div>
                <div id="det-name" style="font-size:16px;font-weight:800;color:#1e293b;line-height:1.2"></div>
                <div id="det-status-badge"
                     style="margin-top:8px;font-size:11px;font-weight:700;padding:4px 12px;border-radius:99px;display:inline-block"></div>
            </div>

            <!-- Dynamic info rows (built by JS dpRow helper) -->
            <div id="det-info-body" style="padding:8px 16px 4px;flex:1"></div>

            <!-- Action buttons -->
            <div style="padding:14px 16px;border-top:2px solid #f1f5f9;display:flex;flex-direction:column;gap:10px">
                <a id="det-trail-link" href="#"
                   style="display:flex;align-items:center;justify-content:center;gap:8px;padding:12px;border-radius:12px;font-size:13px;font-weight:800;color:#fff;text-decoration:none;transition:opacity .15s,transform .1s;box-shadow:0 4px 14px rgba(0,0,0,.15)"
                   onmouseover="this.style.opacity='.88';this.style.transform='translateY(-1px)'"
                   onmouseout="this.style.opacity='1';this.style.transform='none'">
                    <i class="fa fa-road"></i> View GPS Trail
                </a>
                <button id="det-focus-btn" onclick="refocusSelected()"
                        style="display:flex;align-items:center;justify-content:center;gap:8px;padding:11px;border-radius:12px;font-size:13px;font-weight:700;border:2px solid #e2e8f0;background:#fff;color:#475569;cursor:pointer;transition:all .15s"
                        onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='#fff'">
                    <i class="fa fa-crosshairs"></i> Focus on Map
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ═══════════════════════ STYLES ═══════════════════════ -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
<style>
/* Animations */
.live-dot {
    display:inline-block; width:10px; height:10px; border-radius:50%;
    background:#16a34a; animation:pulse-g 1.4s ease-in-out infinite;
}
.live-dot-sm {
    display:inline-block; width:7px; height:7px; border-radius:50%;
    background:#3b82f6; animation:pulse-b 2s ease-in-out infinite; flex-shrink:0;
}
@keyframes pulse-g { 0%,100%{box-shadow:0 0 0 0 rgba(22,163,74,.5)} 50%{box-shadow:0 0 0 7px rgba(22,163,74,0)} }
@keyframes pulse-b { 0%,100%{box-shadow:0 0 0 0 rgba(59,130,246,.4)} 50%{box-shadow:0 0 0 6px rgba(59,130,246,0)} }

/* Map buttons */
.lmap-btn {
    background:white; border:2px solid #e2e8f0; border-radius:10px;
    padding:6px 14px; display:inline-flex; align-items:center; gap:6px;
    cursor:pointer; color:#374151; font-size:13px; font-weight:700;
    box-shadow:0 2px 8px rgba(0,0,0,.1); transition:all .15s;
}
.lmap-btn:hover { background:#f1f5f9; transform:translateY(-1px); }
.lmap-btn.active { background:#1d4ed8; color:white; border-color:#1d4ed8; }

.lmap-badge {
    display:inline-flex; align-items:center; gap:6px;
    background:rgba(255,255,255,.95); backdrop-filter:blur(8px);
    border:2px solid #e2e8f0; border-radius:10px;
    padding:5px 12px; box-shadow:0 2px 8px rgba(0,0,0,.08);
}

/* Staff cards */
.staff-card { border-left:4px solid transparent; transition:border-color .2s, background .15s; }
.staff-card.online  { border-left-color:#16a34a; }
.staff-card.offline { border-left-color:#d1d5db; }
.staff-card.selected { background:#eff6ff !important; border-left-color:#2563eb !important; }
.staff-filter-btn.active { color:#2563eb; border-bottom-color:#2563eb; }

/* Detail panel rows */
.det-row   { display:flex; align-items:flex-start; gap:12px; }
.det-icon  { width:15px; text-align:center; margin-top:2px; font-size:14px; flex-shrink:0; }
.det-label { font-size:10px; font-weight:700; color:#94a3b8; text-transform:uppercase; letter-spacing:.06em; }
.det-value { font-size:13px; font-weight:600; color:#1e293b; margin-top:2px; }

/* Battery bar */
.batt-bar-wrap { height:6px; background:#e2e8f0; border-radius:99px; overflow:hidden; margin-top:5px; }
.batt-bar      { height:100%; border-radius:99px; transition:width .4s; }

/* Custom staff marker */
.smark-wrap { display:flex; flex-direction:column; align-items:center; }
.smark-circle {
    border-radius:50%; border:3px solid white;
    display:flex; align-items:center; justify-content:center;
    color:white; font-weight:900; cursor:pointer;
    transition:transform .2s; box-shadow:0 3px 14px rgba(0,0,0,.25);
}
.smark-circle:hover { transform:scale(1.12); }
.smark-pulse {
    position:absolute; top:-5px; left:-5px;
    border-radius:50%; opacity:.35;
    animation:mpulse 2s ease-in-out infinite;
}
@keyframes mpulse { 0%,100%{transform:scale(1);opacity:.35} 50%{transform:scale(1.5);opacity:0} }

/* Leaflet popup */
.leaflet-popup-content-wrapper {
    border-radius:16px !important; padding:0 !important;
    box-shadow:0 10px 40px rgba(0,0,0,.18) !important; overflow:hidden;
}
.leaflet-popup-content { margin:0 !important; }
.lp { font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif; min-width:210px; }
.lp-hdr { padding:12px 16px; display:flex; align-items:center; gap:10px; }
.lp-body { padding:10px 16px 12px; }
.lp-row { display:flex; align-items:center; gap:8px; font-size:13px; color:#475569; margin-bottom:7px; }
.lp-row i { color:#94a3b8; width:14px; text-align:center; }
.lp-row strong { color:#1e293b; }
.lp-foot { padding:8px 16px; border-top:2px solid #f1f5f9; }
.lp-foot a {
    display:flex; align-items:center; justify-content:center; gap:6px;
    padding:9px; border-radius:10px; font-size:12px; font-weight:800;
    text-decoration:none; color:white;
    background:linear-gradient(135deg,#1d4ed8,#7c3aed);
    transition:opacity .15s;
}
.lp-foot a:hover { opacity:.85; }
</style>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
var STAFF_DATA = <?= json_encode(array_map(function($s) { return [
    'id'      => (int)$s['id'],
    'name'    => $s['name'],
    'initials'=> implode('', array_map(function($w) { return isset($w[0]) ? strtoupper($w[0]) : ''; }, array_slice(explode(' ',$s['name']),0,2))),
    'color'   => ['#2563eb','#7c3aed','#059669','#dc2626','#d97706','#0891b2'][abs(crc32($s['name'])) % 6],
]; }, $staff)) ?>;
</script>
