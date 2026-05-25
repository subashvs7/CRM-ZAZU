<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
    <div class="flex items-center gap-3">
        <div class="w-10 h-10 bg-emerald-100 rounded-xl flex items-center justify-center flex-shrink-0">
            <i class="fa fa-circle-o text-emerald-600 text-lg"></i>
        </div>
        <div>
            <h1 class="text-xl font-bold text-gray-800">Geofence Zones</h1>
            <nav class="text-xs text-gray-400 flex items-center gap-1 mt-0.5">
                <a href="<?= base_url('dashboard') ?>" class="hover:text-blue-600 transition-colors">Home</a>
                <i class="fa fa-angle-right text-[10px]"></i>
                <span class="text-gray-600">Geofence</span>
            </nav>
        </div>
    </div>
    <div class="flex items-center gap-2 self-start sm:self-auto">
        <a href="<?= base_url('geofence/violations') ?>" class="inline-flex items-center gap-1.5 px-3 py-2 text-sm bg-white border border-gray-200 text-gray-700 font-medium rounded-xl hover:bg-gray-50 transition-colors">
            <i class="fa fa-exclamation-triangle text-red-500"></i> Violations
        </a>
        <a href="<?= base_url('geofence/alert_rules') ?>" class="inline-flex items-center gap-1.5 px-3 py-2 text-sm bg-white border border-gray-200 text-gray-700 font-medium rounded-xl hover:bg-gray-50 transition-colors">
            <i class="fa fa-bell text-amber-500"></i> Alert Rules
        </a>
        <button id="btn-add-zone" class="inline-flex items-center gap-1.5 px-4 py-2 text-sm bg-emerald-600 text-white font-semibold rounded-xl hover:bg-emerald-700 transition-colors shadow-sm">
            <i class="fa fa-plus"></i> Add Zone
        </button>
    </div>
</div>

<?php $this->load->view('partials/_status_tabs', get_defined_vars()); ?>

<div class="bg-white rounded-2xl shadow-sm border border-gray-100">
    <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-2">
        <div class="w-8 h-8 bg-emerald-100 rounded-lg flex items-center justify-center">
            <i class="fa fa-circle-o text-emerald-600 text-sm"></i>
        </div>
        <div>
            <h3 class="text-sm font-bold text-gray-800">Geofence Zones</h3>
            <p class="text-xs text-gray-400 mt-0.5">Customer, office, and territory boundaries</p>
        </div>
    </div>
    <div class="p-4 overflow-x-auto">
        <table id="zones-table" class="w-full">
            <thead><tr><th>#</th><th>Name</th><th>Type</th><th>Radius</th><th>Auto Checkin</th><th>On Enter</th><th>On Exit</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody></tbody>
        </table>
    </div>
</div>

<!-- Zone Modal -->
<div class="modal fade" id="zone-modal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Geofence Zone</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <form id="zone-form">
                    <input type="hidden" name="<?= $csrf_name ?>" value="<?= $csrf_hash ?>">
                    <input type="hidden" name="id" id="zone-id" value="0">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">Name *</label>
                                <input type="text" name="name" class="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500" required>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">Zone Type</label>
                                <select name="zone_type" class="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                                    <option value="customer">Customer</option>
                                    <option value="office">Office</option>
                                    <option value="restricted">Restricted</option>
                                    <option value="territory">Territory</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">Customer</label>
                                <select name="customer_id" class="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 select2">
                                    <option value="">-- None --</option>
                                    <?php foreach($customers as $c): ?><option value="<?= $c['id'] ?>"><?= esc_html($c['name']) ?></option><?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="space-y-4">
                            <div class="location-picker-group">
                                <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">Zone Center <span class="normal-case font-normal text-gray-400">(GPS coordinates)</span></label>
                                <div class="grid grid-cols-2 gap-2 mb-2">
                                    <input type="text" name="center_lat" class="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500" placeholder="Latitude">
                                    <input type="text" name="center_lng" class="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500" placeholder="Longitude">
                                </div>
                                <button type="button" class="w-full flex items-center justify-center gap-1.5 px-3 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700 btn-detect-location"
                                        data-lat="[name='center_lat']" data-lng="[name='center_lng']"
                                        data-feedback="#geo-location-feedback" data-map="#geo-map-preview">
                                    <i class="fa fa-crosshairs"></i> Detect My Location
                                </button>
                                <div id="geo-location-feedback" class="text-xs text-gray-500 mt-1"></div>
                                <div id="geo-map-preview" style="display:none" class="mt-2 rounded-xl overflow-hidden"></div>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">Radius (meters)</label>
                                <input type="number" name="radius_meters" class="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500" placeholder="e.g. 200">
                            </div>
                            <div class="space-y-2 pt-1">
                                <label class="flex items-center gap-2 text-sm text-gray-700 cursor-pointer">
                                    <input type="checkbox" name="auto_checkin" value="1" class="rounded"> Auto Check-in
                                </label>
                                <label class="flex items-center gap-2 text-sm text-gray-700 cursor-pointer">
                                    <input type="checkbox" name="alert_on_enter" value="1" class="rounded"> Alert on Enter
                                </label>
                                <label class="flex items-center gap-2 text-sm text-gray-700 cursor-pointer">
                                    <input type="checkbox" name="alert_on_exit" value="1" class="rounded"> Alert on Exit
                                </label>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="px-4 py-2 text-sm text-gray-700 border border-gray-300 rounded-xl hover:bg-gray-50" data-dismiss="modal">Cancel</button>
                <button type="button" class="px-4 py-2 text-sm bg-emerald-600 text-white rounded-xl hover:bg-emerald-700" id="btn-save-zone"><i class="fa fa-save mr-1"></i> Save</button>
            </div>
        </div>
    </div>
</div>
