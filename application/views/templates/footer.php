    </div><!-- /.content-wrapper -->
</div><!-- /.wrapper -->

<!--
  All core plugins (jQuery, Bootstrap, DataTables, Select2, AdminLTE, etc.)
  and global variables (BASE_URL, CRM.*) are loaded in header.php.
  Footer only loads: notification polling + page-specific module JS.
-->

<!-- CSRF auto-refresh + notifications polling -->
<script>
$(document).ajaxComplete(function(e, xhr) {
    var t = xhr.getResponseHeader('X-CSRF-Token');
    if (t) {
        CI3_CSRF_HASH = t;
        $('[name="' + CI3_CSRF_NAME + '"]').val(t);
    }
});

toastr.options = { positionClass: 'toast-top-right', timeOut: 4000, progressBar: true, closeButton: true };

// Notification bell polling
function loadNotifications() {
    $.getJSON(BASE_URL + 'notifications/count', function(res) {
        var d = res.data || {};
        if (d.count > 0) { $('#notif-count').text(d.count).show(); } else { $('#notif-count').hide(); }
        if (d.items && d.items.length) {
            var html = '';
            $.each(d.items, function(i, n) {
                html += '<li><a href="#" class="mark-notif-read" data-id="' + n.id + '">' +
                    '<i class="fa fa-circle text-' + (n.is_read ? 'muted' : 'warning') + '"></i> ' +
                    CRM.esc(n.title) + '<br><small class="text-muted">' + CRM.time_ago(n.created_at) + '</small></a></li>';
            });
            $('#notif-list').html(html);
        }
    });
}
loadNotifications();
setInterval(loadNotifications, 60000);

$(document).on('click', '.mark-notif-read', function(e) {
    e.preventDefault();
    $.post(BASE_URL + 'notifications/mark_read', {id: $(this).data('id'), [CI3_CSRF_NAME]: CI3_CSRF_HASH},
        function() { loadNotifications(); });
});

// Ensure modal containers exist
$(function() {
    if (!$('#ajax-modal').length) {
        $('body').append('<div class="modal fade" id="ajax-modal" tabindex="-1"><div class="modal-dialog"><div class="modal-content"></div></div></div>');
    }
    if (!$('#ajax-modal-lg').length) {
        $('body').append('<div class="modal fade" id="ajax-modal-lg" tabindex="-1"><div class="modal-dialog modal-lg"><div class="modal-content"></div></div></div>');
    }
    CRM.init_plugins();
});
</script>

<?php if (isset($page_js)): ?>
<script src="<?= base_url('assets/js/crm.' . $page_js . '.js') ?>"></script>
<?php endif; ?>

<?php if ($current_role === 'field_staff'): ?>
<!--
  ╔══════════════════════════════════════════════════════╗
  ║  GPS AUTO-TRACKER — Field Staff Web Browser          ║
  ║  Runs silently on every page for logged-in staff.    ║
  ║  Sends location to server every N minutes.           ║
  ╚══════════════════════════════════════════════════════╝
-->
<script>
(function() {
    'use strict';

    // ── Config ─────────────────────────────────────────────────────
    var PING_INTERVAL_MS = <?= (int)get_setting('gps_ping_interval', 600) ?> * 1000;
    // fallback: if setting returns 0, use 10 minutes
    if (PING_INTERVAL_MS < 10000) PING_INTERVAL_MS = 600000;

    var GPS_OPTIONS = {
        enableHighAccuracy: true,
        timeout:            20000,   // 20 seconds to get a fix
        maximumAge:         60000    // accept cached fix up to 1 minute old
    };

    var lastPingAt   = null;
    var pingTimer    = null;
    var permDenied   = false;

    // ── UI helpers ─────────────────────────────────────────────────
    function setDot(color, label, title) {
        $('#gps-status-dot').css('background', color);
        $('#gps-status-label').text(label || 'GPS');
        $('#gps-nav-item').attr('title', title || '');
    }

    function dotAcquiring() { setDot('#f39c12', 'GPS', 'Acquiring GPS location…'); }
    function dotOk(msg)      { setDot('#00a65a', 'GPS ✓', msg); }
    function dotError(msg)   { setDot('#e74c3c', 'GPS ✗', msg); }
    function dotOff()        { setDot('#aaa',    'GPS', 'Location permission denied'); }

    // ── Send coordinates to server ──────────────────────────────────
    function sendPing(lat, lng, accuracy, battery) {
        $.post(BASE_URL + 'gps/ping', {
            lat:      lat,
            lng:      lng,
            accuracy: accuracy || '',
            battery:  (battery !== undefined && battery !== null) ? battery : '',
            [CI3_CSRF_NAME]: CI3_CSRF_HASH
        })
        .done(function(res) {
            if (res.status === 'success') {
                lastPingAt = new Date();
                var timeStr = lastPingAt.toLocaleTimeString('en-IN',
                    { hour: '2-digit', minute: '2-digit', hour12: false });
                var mins    = Math.round(PING_INTERVAL_MS / 60000);
                dotOk('Last: ' + timeStr);
                setDot('#00a65a', 'GPS ✓',
                    'Location sent at ' + timeStr +
                    ' — next in ' + mins + ' min\n' +
                    lat.toFixed(5) + ', ' + lng.toFixed(5));
            }
        })
        .fail(function() {
            // Silent fail — don't disturb the user, just change dot colour
            dotError('Ping failed');
        });
    }

    // ── Get current position & ping ─────────────────────────────────
    function pingLocation() {
        if (permDenied) return;
        dotAcquiring();

        navigator.geolocation.getCurrentPosition(
            // Success
            function(pos) {
                var lat      = pos.coords.latitude;
                var lng      = pos.coords.longitude;
                var accuracy = pos.coords.accuracy;
                // Battery API (not widely supported but try)
                if (navigator.getBattery) {
                    navigator.getBattery().then(function(batt) {
                        sendPing(lat, lng, accuracy, Math.round(batt.level * 100));
                    }).catch(function() {
                        sendPing(lat, lng, accuracy, null);
                    });
                } else {
                    sendPing(lat, lng, accuracy, null);
                }
            },
            // Error
            function(err) {
                if (err.code === 1) {          // PERMISSION_DENIED
                    permDenied = true;
                    dotOff();
                    clearInterval(pingTimer);  // stop trying
                    console.info('[GPS Tracker] Permission denied — auto-tracker stopped.');
                } else if (err.code === 2) {
                    dotError('No signal');
                } else {
                    dotError('Timeout');
                }
            },
            GPS_OPTIONS
        );
    }

    // ── Init ────────────────────────────────────────────────────────
    $(function() {
        if (!navigator.geolocation) {
            dotOff();
            console.info('[GPS Tracker] Geolocation not supported by browser.');
            return;
        }

        dotAcquiring();

        // Ping immediately, then on interval
        pingLocation();
        pingTimer = setInterval(pingLocation, PING_INTERVAL_MS);

        // Also ping when the user returns to the tab (visibility change)
        document.addEventListener('visibilitychange', function() {
            if (!document.hidden && lastPingAt) {
                var elapsed = (Date.now() - lastPingAt.getTime()) / 1000;
                // If more than half the interval has passed, ping now
                if (elapsed > PING_INTERVAL_MS / 2000) {
                    clearInterval(pingTimer);
                    pingLocation();
                    pingTimer = setInterval(pingLocation, PING_INTERVAL_MS);
                }
            }
        });
    });

})();
</script>
<?php endif; ?>

<?php if (isset($inline_js)): ?>
<script><?= $inline_js ?></script>
<?php endif; ?>

</body>
</html>
