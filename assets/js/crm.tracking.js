/**
 * crm.tracking.js — Live Tracking Map  (Premium Redesign)
 */
(function () {
    'use strict';

    /* ─── State ──────────────────────────────────────────────── */
    var liveMap      = null;
    var baseLayer    = null;
    var satLayer     = null;
    var isSat        = false;
    var staffMarkers = {};
    var staffPayload = {};
    var selectedUid  = null;
    var firstLoad    = true;
    var REFRESH_MS   = 15000;
    var ONLINE_SEC   = 300;

    /* ─── Staff metadata from PHP ────────────────────────────── */
    var staffMeta = {};
    if (window.STAFF_DATA && Array.isArray(STAFF_DATA)) {
        STAFF_DATA.forEach(function (s) { staffMeta[s.id] = s; });
    }
    function getColor(uid)    { return (staffMeta[uid] || {}).color    || '#6b7280'; }
    function getInitials(uid) { return (staffMeta[uid] || {}).initials || '??'; }
    function getName(uid)     { return (staffMeta[uid] || {}).name     || 'Staff'; }
    function calcOnline(s) {
        if (!s) return false;
        return s.online === true || s.online === 'true';
    }

    /* ─── Global CSS (injected once) ────────────────────────── */
    $('<style>').text([
        /* Pulse animation */
        '@keyframes trk_pulse{0%,100%{transform:scale(1);opacity:.22}50%{transform:scale(1.5);opacity:0}}',

        /* Leaflet popup reset */
        '.leaflet-popup-content-wrapper{border-radius:20px!important;padding:0!important;overflow:hidden;box-shadow:0 20px 60px rgba(0,0,0,.18)!important;border:none!important}',
        '.leaflet-popup-content{margin:0!important;font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,sans-serif}',
        '.leaflet-popup-tip-container{display:none}',       /* hide default tip — we add our own */
        '.leaflet-popup-close-button{top:10px!important;right:12px!important;font-size:18px!important;color:#64748b!important;width:28px!important;height:28px!important;display:flex;align-items:center;justify-content:center;border-radius:8px;transition:background .15s}',
        '.leaflet-popup-close-button:hover{background:#f1f5f9!important}',

        /* Popup card styles */
        '.tk-popup{}',
        '.tk-hdr{padding:18px 18px 14px;display:flex;align-items:center;gap:12px}',
        '.tk-av{width:52px;height:52px;border-radius:50%;display:flex;align-items:center;justify-content:center;color:#fff;font-size:18px;font-weight:900;flex-shrink:0;border:3px solid rgba(255,255,255,.5)}',
        '.tk-nm{font-size:16px;font-weight:800;color:#fff;line-height:1.2;text-shadow:0 1px 3px rgba(0,0,0,.2)}',
        '.tk-badge{display:inline-flex;align-items:center;gap:5px;margin-top:5px;font-size:11px;font-weight:700;padding:3px 10px;border-radius:99px;backdrop-filter:blur(8px)}',
        '.tk-badge.online{background:rgba(255,255,255,.25);color:#fff}',
        '.tk-badge.offline{background:rgba(0,0,0,.18);color:rgba(255,255,255,.8)}',
        '.tk-body{padding:12px 18px 14px;background:#fff}',
        '.tk-row{display:flex;align-items:center;gap:10px;padding:7px 0;border-bottom:1px solid #f1f5f9;font-size:13px;color:#334155}',
        '.tk-row:last-child{border-bottom:none}',
        '.tk-icon{width:30px;height:30px;border-radius:8px;display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:12px}',
        '.tk-lbl{font-size:10px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.05em;line-height:1}',
        '.tk-val{font-size:13px;font-weight:700;color:#1e293b;margin-top:2px;line-height:1.2}',
        '.tk-batt-bar{height:5px;background:#e2e8f0;border-radius:99px;overflow:hidden;margin-top:5px;width:100%}',
        '.tk-batt-fill{height:100%;border-radius:99px;transition:width .4s}',
        '.tk-foot{padding:12px 18px;background:#f8fafc;border-top:2px solid #f1f5f9}',
        '.tk-btn{display:flex;align-items:center;justify-content:center;gap:8px;padding:11px 16px;border-radius:12px;font-size:13px;font-weight:800;text-decoration:none;color:#fff;transition:opacity .15s,transform .1s;cursor:pointer}',
        '.tk-btn:hover{opacity:.88;transform:translateY(-1px)}',

        /* Staff card styles */
        '.staff-card{border-left:4px solid transparent;transition:all .2s}',
        '.staff-card.online{border-left-color:#16a34a}',
        '.staff-card.offline{border-left-color:#e2e8f0}',
        '.staff-card.selected{background:#eff6ff!important;border-left-color:#2563eb!important}',
        '.staff-card:hover{background:#f8fafc}',
        '.staff-filter-btn{transition:all .2s;font-size:12px;font-weight:700;cursor:pointer}',
        '.staff-filter-btn.active{color:#2563eb;border-bottom-color:#2563eb}',

        /* Detail panel rows */
        '.dp-row{display:flex;align-items:center;gap:12px;padding:8px 0;border-bottom:1px solid #f1f5f9}',
        '.dp-row:last-child{border-bottom:none}',
        '.dp-ic{width:34px;height:34px;border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:13px}',
        '.dp-lbl{font-size:10px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.06em}',
        '.dp-val{font-size:13px;font-weight:700;color:#1e293b;margin-top:2px}'
    ].join('')).appendTo('head');

    /* ─── Marker icon ────────────────────────────────────────── */
    function makeMarkerIcon(uid, online) {
        var color    = online ? getColor(uid) : '#94a3b8';
        var initials = getInitials(uid);
        var pulse    = online
            ? '<div style="position:absolute;top:-10px;left:-10px;width:68px;height:68px;border-radius:50%;background:' + color + ';opacity:.22;animation:trk_pulse 2s ease-in-out infinite"></div>'
            : '';
        return L.divIcon({
            className: '',
            html: '<div style="display:flex;flex-direction:column;align-items:center">'
                + '<div style="position:relative">'
                + pulse
                + '<div style="width:48px;height:48px;border-radius:50%;background:' + color + ';border:4px solid #fff;'
                + 'box-shadow:0 6px 20px ' + color + '66;display:flex;align-items:center;justify-content:center;'
                + 'color:#fff;font-size:15px;font-weight:900;cursor:pointer;font-family:-apple-system,sans-serif">'
                + initials + '</div></div>'
                + '<div style="width:0;height:0;border-left:8px solid transparent;border-right:8px solid transparent;'
                + 'border-top:12px solid ' + color + ';margin-top:-3px"></div>'
                + '</div>',
            iconSize:    [48, 64],
            iconAnchor:  [24, 64],
            popupAnchor: [0, -68]
        });
    }

    /* ─── Premium popup card ─────────────────────────────────── */
    function makePopup(uid, s, online) {
        var color    = online ? getColor(uid) : '#94a3b8';
        var darken   = online ? getColor(uid) : '#6b7280';
        var initials = getInitials(uid);
        var name     = s.name || getName(uid);
        var ts       = s.ts ? parseInt(s.ts, 10) : 0;
        var ageStr   = ts ? CRM.time_ago(new Date(ts * 1000).toISOString()) : '—';
        var bat      = (s.battery != null && s.battery !== '') ? parseInt(s.battery, 10) : null;
        var batClr   = bat != null ? (bat >= 60 ? '#16a34a' : bat >= 25 ? '#f59e0b' : '#ef4444') : '#94a3b8';
        var lat      = s.lat ? parseFloat(s.lat).toFixed(5) : '—';
        var lng      = s.lng ? parseFloat(s.lng).toFixed(5) : '—';
        var vc       = parseInt(s.visits_today || 0, 10);

        var hdrGrad  = 'linear-gradient(135deg, ' + color + ', ' + darken + 'cc)';
        var statusCls = online ? 'online' : 'offline';
        var statusTxt = online ? '&#9679; Online'  : '&#9675; Offline';

        /* battery row */
        var batHtml = bat != null
            ? '<div class="tk-row">'
            + '<div class="tk-icon" style="background:#f0fdf4"><i class="fa fa-battery-' + (bat>=75?'full':bat>=40?'half':'quarter') + '" style="color:' + batClr + '"></i></div>'
            + '<div style="flex:1"><div class="tk-lbl">Battery</div>'
            + '<div class="tk-val" style="color:' + batClr + '">' + bat + '%'
            + '<div class="tk-batt-bar"><div class="tk-batt-fill" style="width:' + bat + '%;background:' + batClr + '"></div></div></div></div>'
            + '</div>'
            : '';

        /* last customer row */
        var custHtml = s.last_customer
            ? '<div class="tk-row">'
            + '<div class="tk-icon" style="background:#fdf4ff"><i class="fa fa-building" style="color:#a855f7"></i></div>'
            + '<div><div class="tk-lbl">Last Visit</div><div class="tk-val">' + CRM.esc(s.last_customer) + '</div></div>'
            + '</div>'
            : '';

        return '<div class="tk-popup" style="min-width:240px">'

            /* ── Header ── */
            + '<div class="tk-hdr" style="background:' + hdrGrad + '">'
            + '<div class="tk-av" style="background:rgba(255,255,255,.2)">' + initials + '</div>'
            + '<div style="flex:1;min-width:0">'
            + '<div class="tk-nm">' + CRM.esc(name) + '</div>'
            + '<span class="tk-badge ' + statusCls + '">' + statusTxt + '</span>'
            + '</div></div>'

            /* ── Body ── */
            + '<div class="tk-body">'

            + '<div class="tk-row">'
            + '<div class="tk-icon" style="background:#eff6ff"><i class="fa fa-crosshairs" style="color:#3b82f6"></i></div>'
            + '<div><div class="tk-lbl">Coordinates</div>'
            + '<div class="tk-val" style="font-family:\'SF Mono\',Menlo,monospace;font-size:12px">' + lat + ', ' + lng + '</div></div>'
            + '</div>'

            + '<div class="tk-row">'
            + '<div class="tk-icon" style="background:#fdf8ff"><i class="fa fa-clock-o" style="color:#8b5cf6"></i></div>'
            + '<div><div class="tk-lbl">Last Ping</div>'
            + '<div class="tk-val">' + ageStr + '</div></div>'
            + '</div>'

            + batHtml

            + '<div class="tk-row">'
            + '<div class="tk-icon" style="background:#fffbeb"><i class="fa fa-map-signs" style="color:#f59e0b"></i></div>'
            + '<div><div class="tk-lbl">Visits Today</div>'
            + '<div class="tk-val">' + vc + ' visit' + (vc !== 1 ? 's' : '') + '</div></div>'
            + '</div>'

            + custHtml
            + '</div>'

            /* ── Footer button ── */
            + '<div class="tk-foot">'
            + '<a href="' + BASE_URL + 'tracking/trail/' + uid + '" class="tk-btn" style="background:linear-gradient(135deg,' + color + ',' + darken + 'cc)">'
            + '<i class="fa fa-road"></i> View GPS Trail'
            + '</a></div></div>';
    }

    /* ─── Premium detail panel ───────────────────────────────── */
    function showDetail(uid, s, online) {
        selectedUid  = uid;
        var color    = online ? getColor(uid) : '#94a3b8';
        var initials = getInitials(uid);
        var name     = s.name || getName(uid);
        var ts       = s.ts ? parseInt(s.ts, 10) : 0;
        var ageStr   = ts ? CRM.time_ago(new Date(ts * 1000).toISOString()) : 'No data';
        var bat      = (s.battery != null && s.battery !== '') ? parseInt(s.battery, 10) : null;
        var batClr   = bat != null ? (bat >= 60 ? '#16a34a' : bat >= 25 ? '#f59e0b' : '#ef4444') : '#94a3b8';
        var lat      = s.lat ? parseFloat(s.lat).toFixed(5) : '—';
        var lng      = s.lng ? parseFloat(s.lng).toFixed(5) : '—';
        var vc       = parseInt(s.visits_today || 0, 10);

        /* ── Avatar ── */
        var $av = $('#det-avatar');
        $av.css({
            background:  'linear-gradient(135deg, ' + color + ', ' + color + 'cc)',
            'box-shadow': '0 8px 24px ' + color + '55'
        }).text(initials);

        /* ── Name + badge ── */
        $('#det-name').text(name);
        if (online) {
            $('#det-status-badge')
                .html('&#9679; Online')
                .css({ background: '#dcfce7', color: '#15803d', border: '2px solid #86efac' });
        } else {
            $('#det-status-badge')
                .html('&#9675; Offline')
                .css({ background: '#f1f5f9', color: '#64748b', border: '2px solid #e2e8f0' });
        }

        /* ── Info rows (rebuild with dp-row classes) ── */
        var $info = $('#det-info-body').empty();

        function dpRow(iconBg, iconColor, iconCls, label, valueHtml) {
            return $('<div class="dp-row">'
                + '<div class="dp-ic" style="background:' + iconBg + '"><i class="fa ' + iconCls + '" style="color:' + iconColor + '"></i></div>'
                + '<div style="flex:1"><div class="dp-lbl">' + label + '</div><div class="dp-val">' + valueHtml + '</div></div>'
                + '</div>');
        }

        $info.append(dpRow('#eff6ff','#3b82f6','fa-crosshairs','Location',
            '<span style="font-family:monospace;font-size:12px">' + (s.lat ? lat+', '+lng : 'No GPS data') + '</span>'
        ));
        $info.append(dpRow('#fdf8ff','#8b5cf6','fa-clock-o','Last Ping', ageStr));

        if (bat != null) {
            var batBarHtml = '<strong style="color:' + batClr + '">' + bat + '%</strong>'
                + '<div style="height:6px;background:#e2e8f0;border-radius:99px;overflow:hidden;margin-top:4px">'
                + '<div style="height:100%;width:' + bat + '%;background:' + batClr + ';border-radius:99px;transition:width .4s"></div></div>';
            $info.append(dpRow('#f0fdf4', batClr, 'fa-battery-' + (bat>=75?'full':bat>=40?'half':'quarter'), 'Battery', batBarHtml));
        }

        $info.append(dpRow('#fffbeb','#f59e0b','fa-map-signs','Visits Today',
            '<strong>' + vc + '</strong> visit' + (vc !== 1 ? 's' : '') + ' today'
        ));
        $info.append(dpRow('#fdf4ff','#a855f7','fa-building','Last Customer',
            s.last_customer ? CRM.esc(s.last_customer) : '<span style="color:#94a3b8">—</span>'
        ));

        /* Trail link */
        $('#det-trail-link').attr('href', BASE_URL + 'tracking/trail/' + uid)
            .css('background', 'linear-gradient(135deg,' + color + ',' + color + 'cc)');

        var $focusBtn = $('#det-focus-btn');
        if (online && staffMarkers[uid]) {
            $focusBtn.show();
        } else {
            $focusBtn.hide();
        }

        /* ── Show panel ── */
        document.getElementById('detail-empty').style.display   = 'none';
        document.getElementById('detail-content').style.display = 'flex';
        document.getElementById('detail-content').style.flexDirection = 'column';
        document.getElementById('detail-content').style.flex    = '1';
        document.getElementById('detail-content').style.overflowY = 'auto';

        $('.staff-card').removeClass('selected');
        $('#card-' + uid).addClass('selected');
    }

    /* ─── Filter + Search ────────────────────────────────────── */
    function applyFilter() {
        var filter = $('.staff-filter-btn.active').attr('data-filter') || 'all';
        var query  = ($('#staff-search').val() || '').toLowerCase().trim();
        $('.staff-card').each(function () {
            var $c    = $(this);
            var name  = ($c.attr('data-name') || '').toLowerCase();
            var stat  = $c.attr('data-status') || 'offline';
            $c.toggle((filter === 'all' || filter === stat) && (!query || name.indexOf(query) !== -1));
        });
    }

    $(document).on('click', '.staff-filter-btn', function () {
        var $t = $(this);
        $('.staff-filter-btn')
            .removeClass('active text-blue-600 border-blue-600 text-green-600 border-green-600 text-red-500 border-red-500')
            .addClass('text-gray-400 border-transparent');
        
        $t.addClass('active').removeClass('text-gray-400 border-transparent');
        var filter = $t.attr('data-filter');
        if (filter === 'all') {
            $t.addClass('text-blue-600 border-blue-600');
        } else if (filter === 'online') {
            $t.addClass('text-green-600 border-green-600');
        } else if (filter === 'offline') {
            $t.addClass('text-red-500 border-red-500');
        }
        applyFilter();
    });
    $(document).on('input', '#staff-search', applyFilter);

    /* ─── Globals ────────────────────────────────────────────── */
    window.fitAll = function () {
        var pts = Object.keys(staffMarkers).map(function (uid) { return staffMarkers[uid].getLatLng(); });
        if (!pts.length) { CRM.toast('info', 'No GPS positions available yet.'); return; }
        if (pts.length === 1) { liveMap.setView([pts[0].lat, pts[0].lng], 16, { animate: true }); return; }
        liveMap.fitBounds(pts.map(function (p) { return [p.lat, p.lng]; }), { padding: [60, 60], animate: true });
    };
    window.toggleSat = function () {
        var $b = $('#btn-sat');
        if (!isSat) {
            if (!satLayer) satLayer = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', { attribution: 'Esri', maxZoom: 19 });
            satLayer.addTo(liveMap); baseLayer.remove(); $b.addClass('active'); isSat = true;
        } else { if (satLayer) satLayer.remove(); baseLayer.addTo(liveMap); $b.removeClass('active'); isSat = false; }
    };
    window.refocusSelected = function () {
        if (selectedUid && staffMarkers[selectedUid]) {
            liveMap.setView(staffMarkers[selectedUid].getLatLng(), 16, { animate: true });
            staffMarkers[selectedUid].openPopup();
        }
    };
    window.focusStaff = function (uid) {
        uid = parseInt(uid, 10);
        var s      = staffPayload[uid];
        var online = s ? calcOnline(s) : false;
        if (s) showDetail(uid, s, online);
        if (staffMarkers[uid]) { liveMap.setView(staffMarkers[uid].getLatLng(), 16, { animate: true, duration: 0.5 }); staffMarkers[uid].openPopup(); }
        else if (!s) CRM.toast('info', 'No GPS data available yet.');
    };

    /* ─── Main polling ───────────────────────────────────────── */
    function loadLivePositions() {
        var $spin = $('#refresh-spin');
        $spin.addClass('fa-spin');
        $.getJSON(BASE_URL + 'tracking/live_data', function (res) {
            var list = res.data || [], onlineCnt = 0, totalVisits = 0, firstPts = [];
            list.forEach(function (s) {
                var uid    = parseInt(s.user_id, 10);
                var online = calcOnline(s);
                staffPayload[uid] = s;
                totalVisits += parseInt(s.visits_today || 0, 10);

                if (s.lat && s.lng && online) {
                    var lat = parseFloat(s.lat), lng = parseFloat(s.lng);
                    var icon = makeMarkerIcon(uid, online);
                    var pop  = makePopup(uid, s, online);
                    var isNew = !staffMarkers[uid];
                    if (!isNew) {
                        staffMarkers[uid].setLatLng([lat, lng]).setIcon(icon).setPopupContent(pop);
                    } else {
                        staffMarkers[uid] = L.marker([lat, lng], { icon: icon, title: s.name || '' })
                            .bindPopup(pop, { maxWidth: 300, minWidth: 240, className: '', autoPanPaddingTop: 80 })
                            .addTo(liveMap);
                        (function (cu) {
                            staffMarkers[cu].on('click', function () {
                                var d = staffPayload[cu];
                                if (d) showDetail(cu, d, calcOnline(d));
                            });
                        })(uid);
                        if (firstLoad) firstPts.push([lat, lng]);
                    }
                    onlineCnt++;
                } else {
                    if (staffMarkers[uid]) {
                        liveMap.removeLayer(staffMarkers[uid]);
                        delete staffMarkers[uid];
                    }
                }

                /* ── Card UI ── */
                var $card  = $('#card-' + uid);
                var ts     = s.ts ? parseInt(s.ts, 10) : 0;
                var ageStr = ts ? CRM.time_ago(new Date(ts * 1000).toISOString()) : '';
                $card.attr('data-status', online ? 'online' : 'offline');
                if (online) {
                    $card.removeClass('offline').addClass('online');
                    $('#dot-' + uid).css('background', getColor(uid));
                    $('#loc-' + uid).html('<span style="color:' + getColor(uid) + ';font-size:12px;font-weight:700">&#9679; Online &middot; ' + ageStr + '</span>');
                } else {
                    $card.removeClass('online').addClass('offline');
                    $('#dot-' + uid).css('background', '#d1d5db');
                    if (s.lat && ts) {
                        $('#loc-' + uid).html('<span style="color:#94a3b8;font-size:12px;font-weight:600">&#9675; Last seen: ' + ageStr + '</span>');
                    } else {
                        $('#loc-' + uid).html('<span style="color:#94a3b8;font-size:12px;font-weight:600">&#9675; No GPS signal</span>');
                    }
                }

                /* visits badge */
                var vc = parseInt(s.visits_today || 0, 10);
                $('#vbadge-' + uid).text(vc + ' visit' + (vc !== 1 ? 's' : '')).css(vc > 0 ? { background: '#dbeafe', color: '#1d4ed8' } : { background: '#f1f5f9', color: '#94a3b8' });

                /* battery badge */
                var bat = (s.battery != null && s.battery !== '') ? parseInt(s.battery, 10) : null;
                if (bat != null) {
                    var bc = bat >= 60 ? '#16a34a' : bat >= 25 ? '#f59e0b' : '#ef4444';
                    $('#bat-' + uid).html('<i class="fa fa-battery-half" style="color:' + bc + '"></i>&nbsp;' + bat + '%');
                } else { $('#bat-' + uid).text(''); }

                if (selectedUid === uid) showDetail(uid, s, online);
            });

            $('#stat-online').text(onlineCnt); $('#hdr-online').text(onlineCnt);
            $('#stat-visits').text(totalVisits);
            var t = new Date().toLocaleTimeString('en-IN', { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: false });
            $('#last-update').text(t); $('#stat-updated').text(t);

            if (firstLoad && firstPts.length) {
                firstPts.length === 1 ? liveMap.setView(firstPts[0], 15, { animate: true }) : liveMap.fitBounds(firstPts, { padding: [60, 60], animate: true });
            }
            firstLoad = false;
            applyFilter();
            $spin.removeClass('fa-spin');
        }).fail(function () { $('#refresh-spin').removeClass('fa-spin'); });
    }

    /* ─── Map Init ───────────────────────────────────────────── */
    $(function () {
        if (liveMap || !document.getElementById('live-map')) return;
        liveMap = L.map('live-map', { center: [20.5937, 78.9629], zoom: 5, zoomControl: false });
        L.control.zoom({ position: 'bottomright' }).addTo(liveMap);
        baseLayer = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://openstreetmap.org">OpenStreetMap</a>', maxZoom: 19
        }).addTo(liveMap);
        liveMap.on('mousemove', function (e) {
            $('#map-coord-display').text('Lat ' + e.latlng.lat.toFixed(4) + '  Lng ' + e.latlng.lng.toFixed(4));
        });
        setTimeout(function () { liveMap.invalidateSize(); }, 400);
        loadLivePositions();
        setInterval(loadLivePositions, REFRESH_MS);
    });

})();
