<section class="content-header">
    <h1>Live Tracking <small>Real-time field staff locations</small></h1>
    <ol class="breadcrumb">
        <li><a href="<?= base_url('dashboard') ?>">Home</a></li>
        <li class="active">Live Tracking</li>
    </ol>
</section>
<section class="content">

<!-- Leaflet CSS (must be in <body> here since it's view-specific) -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
<style>
/* IMPORTANT: do NOT set z-index on #live-map — Leaflet manages its own z-index internally */
#live-map {
    height: 540px;
    border-radius: 0 0 6px 6px;
    background: #e8e8e8;   /* grey placeholder while tiles load */
}
.staff-card         { padding: 8px 12px; border-bottom: 1px solid #eee; cursor: pointer; transition: background .15s; }
.staff-card:hover   { background: #f5f9ff; }
.staff-card.online  { border-left: 4px solid #00a65a; background: #f0fff5; }
.staff-card.offline { border-left: 4px solid #ddd; }
.staff-name         { font-weight: 600; font-size: 13px; }
.staff-loc          { font-size: 11px; color: #888; margin-top: 2px; }
</style>

<div class="row">
    <!-- Map panel -->
    <div class="col-md-9">
        <div class="box box-primary" style="margin-bottom:0">
            <div class="box-header with-border" style="padding:8px 15px">
                <h3 class="box-title"><i class="fa fa-map-marker"></i> Live Map</h3>
                <div class="box-tools pull-right">
                    <span class="label label-success" id="online-badge">
                        <i class="fa fa-circle"></i> <span id="online-count">0</span> Online
                    </span>
                    &nbsp;
                    <span class="label label-default">
                        <i class="fa fa-refresh" id="refresh-spin"></i>
                        Updated: <span id="last-update">—</span>
                    </span>
                    &nbsp;
                    <a href="<?= base_url('tracking/ping_status') ?>" class="btn btn-xs btn-default">
                        <i class="fa fa-satellite"></i> Ping Log
                    </a>
                </div>
            </div>
            <div class="box-body p-0">
                <div id="live-map"></div>
            </div>
        </div>
    </div>

    <!-- Staff list panel -->
    <div class="col-md-3">
        <div class="box box-info" style="margin-bottom:0">
            <div class="box-header with-border" style="padding:8px 15px">
                <h3 class="box-title">Staff Status</h3>
            </div>
            <div class="box-body p-0" style="max-height:540px;overflow-y:auto" id="staff-panel">
                <?php foreach ($staff as $s): ?>
                <div class="staff-card offline" id="card-<?= $s['id'] ?>"
                     onclick="focusStaff(<?= $s['id'] ?>)" title="Click to focus on map">
                    <div class="staff-name">
                        <span style="display:inline-block;width:9px;height:9px;border-radius:50%;
                              background:#aaa;margin-right:5px;vertical-align:middle"
                              id="dot-<?= $s['id'] ?>"></span>
                        <?= esc_html($s['name']) ?>
                    </div>
                    <div class="staff-loc" id="loc-<?= $s['id'] ?>">
                        <span class="text-muted">No GPS signal</span>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<!-- Leaflet JS — loaded here so it's available before crm.tracking.js (loaded by footer page_js) -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<!-- NOTE: do NOT add crm.tracking.js here — footer page_js loads it once after this -->
