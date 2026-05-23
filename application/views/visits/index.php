<!-- Page Header -->
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
    <div class="flex items-center gap-3">
        <div class="w-10 h-10 bg-cyan-100 rounded-xl flex items-center justify-center flex-shrink-0">
            <i class="fa fa-map-signs text-cyan-600 text-lg"></i>
        </div>
        <div>
            <h1 class="text-xl font-bold text-gray-800">Visit Plans</h1>
            <nav class="text-xs text-gray-400 flex items-center gap-1 mt-0.5">
                <a href="<?= base_url('dashboard') ?>" class="hover:text-blue-600 transition-colors">Home</a>
                <i class="fa fa-angle-right text-[10px]"></i>
                <span class="text-gray-600">Visits</span>
            </nav>
        </div>
    </div>
    <div class="flex items-center gap-2 self-start sm:self-auto">
        <a href="<?= base_url('visits/history') ?>"
           class="inline-flex items-center gap-1.5 px-3 py-2 text-sm bg-white border border-gray-200 text-gray-700 font-medium rounded-xl hover:bg-gray-50 transition-colors">
            <i class="fa fa-history text-cyan-600"></i> History
        </a>
        <a href="<?= base_url('visits/checkin') ?>"
           class="inline-flex items-center gap-1.5 px-3 py-2 text-sm bg-green-600 text-white font-medium rounded-xl hover:bg-green-700 transition-colors">
            <i class="fa fa-sign-in"></i> Check In
        </a>
        <button id="btn-plan-visit"
                class="inline-flex items-center gap-1.5 px-4 py-2 text-sm bg-blue-600 text-white font-semibold rounded-xl hover:bg-blue-700 transition-colors shadow-sm">
            <i class="fa fa-plus"></i> Plan Visit
        </button>
    </div>
</div>

<!-- Status Tabs -->
<?php $this->load->view('partials/_status_tabs', get_defined_vars()); ?>

<!-- Table -->
<div class="bg-white rounded-2xl shadow-sm border border-gray-100">
    <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
        <div>
            <h3 class="text-sm font-bold text-gray-800">Scheduled Visits</h3>
            <p class="text-xs text-gray-400 mt-0.5">Planned field visit schedule</p>
        </div>
        <span class="px-2.5 py-1 bg-cyan-50 text-cyan-600 text-xs font-semibold rounded-lg">
            <i class="fa fa-map-signs mr-1"></i> Plans
        </span>
    </div>
    <div class="p-4 overflow-x-auto">
        <table id="visits-table" class="w-full">
            <thead>
                <tr>
                    <th>#</th><th>Date</th><th>Time</th><th>Customer</th>
                    <th>Staff</th><th>Status</th><th>Purpose</th><th>Record</th><th>Actions</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>

<!-- Visit Modal -->
<div class="modal fade" id="visit-modal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Plan Visit</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <form id="visit-form" onsubmit="return false;">
                    <input type="hidden" name="<?= $csrf_name ?>" value="<?= $csrf_hash ?>">
                    <input type="hidden" name="id" id="visit-id" value="0">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">Customer *</label>
                            <select name="customer_id" class="w-full px-3 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 select2" required>
                                <option value="">— Select Customer —</option>
                                <?php foreach($customers as $c): ?>
                                <option value="<?= $c['id'] ?>"><?= esc_html($c['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <?php if($is_manager): ?>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">Assign To</label>
                            <select name="user_id" class="w-full px-3 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 select2">
                                <option value="">— Self —</option>
                                <?php foreach($staff as $s): ?>
                                <option value="<?= $s['id'] ?>"><?= esc_html($s['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <?php endif; ?>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">Date *</label>
                                <input type="text" name="planned_date" class="w-full px-3 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 datepicker" required>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">Time</label>
                                <input type="time" name="planned_time" class="w-full px-3 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">Purpose</label>
                            <textarea name="purpose" rows="3" class="w-full px-3 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none"></textarea>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="inline-flex items-center gap-1.5 px-4 py-2 text-sm bg-white border border-gray-200 text-gray-700 rounded-xl hover:bg-gray-50 font-medium" data-dismiss="modal">
                    Cancel
                </button>
                <button type="button" class="inline-flex items-center gap-1.5 px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700 font-semibold" id="btn-save-visit">
                    <i class="fa fa-save"></i> Save Visit
                </button>
            </div>
        </div>
    </div>
</div>
