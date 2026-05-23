<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
    <div class="flex items-center gap-3">
        <div class="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center flex-shrink-0">
            <i class="fa fa-map-marker text-blue-600 text-lg"></i>
        </div>
        <div>
            <h1 class="text-xl font-bold text-gray-800">Live Tracking</h1>
            <nav class="text-xs text-gray-400 flex items-center gap-1 mt-0.5">
                <a href="<?= base_url('dashboard') ?>" class="hover:text-blue-600 transition-colors">Home</a>
                <i class="fa fa-angle-right text-[10px]"></i>
                <span class="text-gray-600">Live Tracking</span>
            </nav>
        </div>
    </div>
</div>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
<style>
#live-map { height: 540px; border-radius: 0 0 1rem 1rem; background: #e8e8e8; }
</style>

<div class="grid grid-cols-1 lg:grid-cols-4 gap-5">
    <!-- Map Panel -->
    <div class="lg:col-span-3">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                        <i class="fa fa-map-marker text-blue-600 text-sm"></i>
                    </div>
                    <h3 class="text-sm font-bold text-gray-800">Live Map</h3>
                </div>
                <div class="flex items-center gap-3">
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-semibold rounded-lg bg-green-100 text-green-700" id="online-badge">
                        <i class="fa fa-circle text-green-500" style="font-size:7px"></i>
                        <span id="online-count">0</span> Online
                    </span>
                    <span class="text-xs text-gray-400">
                        <i class="fa fa-refresh" id="refresh-spin"></i>
                        Updated: <span id="last-update">—</span>
                    </span>
                    <a href="<?= base_url('tracking/ping_status') ?>" class="inline-flex items-center gap-1 px-2.5 py-1.5 text-xs border border-gray-200 rounded-xl hover:bg-gray-50 text-gray-600 font-medium">
                        <i class="fa fa-satellite"></i> Ping Log
                    </a>
                </div>
            </div>
            <div id="live-map"></div>
        </div>
    </div>

    <!-- Staff Panel -->
    <div class="lg:col-span-1">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 h-full">
            <div class="px-5 py-4 border-b border-gray-100">
                <h3 class="text-sm font-bold text-gray-800">Staff Status</h3>
                <p class="text-xs text-gray-400 mt-0.5">Click to focus on map</p>
            </div>
            <div class="overflow-y-auto" style="max-height:540px" id="staff-panel">
                <?php foreach ($staff as $s): ?>
                <div class="staff-card offline px-4 py-3 border-b border-gray-50 cursor-pointer hover:bg-gray-50 transition-colors border-l-4 border-l-gray-200"
                     id="card-<?= $s['id'] ?>" onclick="focusStaff(<?= $s['id'] ?>)">
                    <div class="flex items-center gap-2">
                        <span class="inline-block w-2 h-2 rounded-full bg-gray-300 flex-shrink-0" id="dot-<?= $s['id'] ?>"></span>
                        <span class="text-sm font-semibold text-gray-700"><?= esc_html($s['name']) ?></span>
                    </div>
                    <div class="text-xs text-gray-400 mt-0.5 ml-4" id="loc-<?= $s['id'] ?>">No GPS signal</div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
