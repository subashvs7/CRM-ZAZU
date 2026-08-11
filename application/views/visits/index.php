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
        <!-- Buttons removed as per user request -->
    </div>
</div>

<!-- Status Tabs -->
<div class="mb-4">
    <div class="border-b border-gray-200">
        <nav class="flex gap-0" id="status-tabs">
            <a href="#" data-status="" class="px-4 py-2.5 text-sm font-medium border-b-2 transition-colors border-blue-600 text-blue-600">All</a>
            <a href="#" data-status="primary" class="px-4 py-2.5 text-sm font-medium border-b-2 transition-colors border-transparent text-gray-500 hover:text-gray-700">Primary</a>
            <a href="#" data-status="followup" class="px-4 py-2.5 text-sm font-medium border-b-2 transition-colors border-transparent text-gray-500 hover:text-gray-700">Follow-up</a>
            <a href="#" data-status="inactive" class="px-4 py-2.5 text-sm font-medium border-b-2 transition-colors border-transparent text-gray-500 hover:text-gray-700">Inactive</a>
            <?php if($is_admin): ?>
            <a href="#" data-status="deleted" class="px-4 py-2.5 text-sm font-medium border-b-2 transition-colors border-transparent text-red-400 hover:text-red-600">
                <i class="fa fa-trash mr-1"></i>Deleted
            </a>
            <?php endif; ?>
        </nav>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-stretch">
    <!-- Left Column: List -->
    <div class="lg:col-span-2 flex flex-col">
        <!-- Table -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 flex-1 flex flex-col">
            <div class="px-6 py-4 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h3 class="text-sm font-bold text-gray-800">Scheduled Visits</h3>
                    <p class="text-xs text-gray-400 mt-0.5">Planned field visit schedule</p>
                </div>
                <div class="flex flex-col sm:flex-row gap-3 items-center">
                    <div class="w-full sm:w-auto">
                        <select id="filter-customer-type" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">All Types</option>
                            <option value="primary">Primary</option>
                            <option value="followup">Follow-up</option>
                        </select>
                    </div>
                    <div class="w-full sm:w-auto">
                        <input type="date" id="filter-date" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <?php if(isset($is_admin) && $is_admin || (isset($is_manager) && $is_manager)): ?>
                    <div class="w-full sm:w-auto">
                        <select id="filter-staff" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">All Staff</option>
                            <?php foreach($staff as $s): ?>
                                <option value="<?= $s['id'] ?>"><?= esc_html($s['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="p-4 overflow-x-auto">
                <table id="visits-table" class="w-full text-sm">
                    <thead>
                        <tr>
                            <th class="text-left font-semibold text-gray-500 pb-2">#</th>
                            <th class="text-left font-semibold text-gray-500 pb-2">Customer</th>
                            <th class="text-left font-semibold text-gray-500 pb-2">Assigned To</th>
                            <th class="text-left font-semibold text-gray-500 pb-2">Date & Time</th>
                            <th class="text-left font-semibold text-gray-500 pb-2">Purpose</th>
                            <th class="text-left font-semibold text-gray-500 pb-2">Status</th>
                            <th class="text-center font-semibold text-gray-500 pb-2">Action</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Right Column: Plan Visit Form -->
    <div class="flex flex-col">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 flex-1">
            <h3 class="text-sm font-bold text-gray-800">Plan New Visit</h3>
            <p class="text-xs text-gray-400 mt-0.5 mb-5">Schedule a new visit</p>
            
            <form id="right-visit-form" onsubmit="return false;">
                <input type="hidden" name="<?= $csrf_name ?>" value="<?= $csrf_hash ?>">
                <div class="grid grid-cols-1 gap-4">
                    <div class="grid grid-cols-2 gap-3">
                        <div class="col-span-2">
                            <label class="block text-[11px] font-bold text-gray-500 mb-1">Customer Type</label>
                            <div class="flex items-center gap-2 mb-2 p-1 bg-gray-100 rounded-lg w-full">
                                <button type="button" class="flex-1 py-1.5 text-xs font-semibold rounded-md bg-white text-gray-800 shadow-sm transition-all customer-type-tab" data-type="primary">Primary</button>
                                <button type="button" class="flex-1 py-1.5 text-xs font-semibold rounded-md text-gray-500 hover:text-gray-700 transition-all customer-type-tab" data-type="followup">Follow-up</button>
                            </div>
                            <input type="hidden" name="customer_type_selection" id="customer-type-selection" value="primary">
                        </div>
                        <div class="col-span-2 sm:col-span-1">
                            <label class="block text-[11px] font-bold text-gray-500 mb-1">Customer <span class="text-red-500">*</span></label>
                            <select name="customer_id" id="plan-customer-select" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-xs focus:outline-none focus:border-blue-500 select2" required style="width: 100%;">
                                <option value="">-- Select Customer --</option>
                            </select>
                        </div>
                        <div class="col-span-2 sm:col-span-1">
                            <label class="block text-[11px] font-bold text-gray-500 mb-1">Assign To <span class="text-red-500">*</span></label>
                            <select name="user_id" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-xs focus:outline-none focus:border-blue-500">
                                <option value="<?= $this->session->userdata('user_id') ?>">Self</option>
                                <?php if($is_manager): foreach($staff as $s): ?>
                                <option value="<?= $s['id'] ?>"><?= esc_html($s['name']) ?></option>
                                <?php endforeach; endif; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[11px] font-bold text-gray-500 mb-1">Date <span class="text-red-500">*</span></label>
                            <input type="date" name="planned_date" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-xs focus:outline-none focus:border-blue-500" required>
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-gray-500 mb-1">Time <span class="text-red-500">*</span></label>
                            <input type="time" name="planned_time" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-xs focus:outline-none focus:border-blue-500" required>
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-[11px] font-bold text-gray-500 mb-1">Purpose <span class="text-red-500">*</span></label>
                        <select name="purpose" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-xs focus:outline-none focus:border-blue-500" required>
                            <option value="">-- Select Purpose --</option>
                            <option value="Product Demo">Product Demo</option>
                            <option value="Follow Up">Follow Up</option>
                            <option value="Requirement Discussion">Requirement Discussion</option>
                            <option value="Product Presentation">Product Presentation</option>
                            <option value="Site Visit">Site Visit</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    
                    <div class="flex justify-end gap-2 mt-2">
                        <button type="reset" class="px-4 py-2 text-xs font-semibold text-gray-600 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors">Reset</button>
                        <button type="button" id="btn-save-right-visit" class="px-4 py-2 text-xs font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors shadow-sm">Plan Visit</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- History Card -->
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 mt-6">
    <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
        <div>
            <h3 class="text-sm font-bold text-gray-800">Visit History</h3>
            <p class="text-xs text-gray-400 mt-0.5">Completed visits log</p>
        </div>
        <span class="px-2.5 py-1 bg-indigo-50 text-indigo-600 text-xs font-semibold rounded-lg">
            <i class="fa fa-history mr-1"></i> Log
        </span>
    </div>
    <div class="p-4 overflow-x-auto">
        <table id="history-table" class="w-full text-sm">
            <thead>
                <tr>
                    <th class="text-left font-semibold text-gray-500 pb-2">#</th>
                    <th class="text-left font-semibold text-gray-500 pb-2">Check In</th>
                    <th class="text-left font-semibold text-gray-500 pb-2">Check Out</th>
                    <th class="text-left font-semibold text-gray-500 pb-2">Customer</th>
                    <th class="text-left font-semibold text-gray-500 pb-2">Staff</th>
                    <th class="text-left font-semibold text-gray-500 pb-2">Distance</th>
                    <th class="text-left font-semibold text-gray-500 pb-2">Auto</th>
                    <th class="text-left font-semibold text-gray-500 pb-2">Status</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>

<!-- Check In Modal -->
<div class="modal fade" id="checkin-modal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 rounded-2xl shadow-xl overflow-hidden">
            <div class="modal-header bg-green-600 text-white border-0 px-5 py-4">
                <h4 class="modal-title text-sm font-bold flex items-center gap-2"><i class="fa fa-map-marker"></i> Check In Visit</h4>
                <button type="button" class="close text-white opacity-80 hover:opacity-100 text-lg leading-none" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body p-6 bg-white">
                <form id="checkin-form" onsubmit="return false;">
                    <input type="hidden" name="<?= $csrf_name ?>" value="<?= $csrf_hash ?>">
                    <input type="hidden" name="visit_plan_id" id="chk-in-plan-id" value="">
                    <input type="hidden" name="customer_id" id="chk-in-customer-id" value="">
                    
                    <div class="space-y-4">
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-[11px] font-bold text-gray-500 mb-1.5">Check In Time</label>
                                <input type="text" id="chk-in-time-display" name="check_in_at" class="w-full px-3 py-2 border border-gray-200 <?= $is_admin ? '' : 'bg-gray-50' ?> rounded-lg text-xs" <?= $is_admin ? '' : 'readonly' ?>>
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-gray-500 mb-1.5">Location</label>
                                <input type="text" id="chk-in-location-display" name="check_in_location" class="w-full px-3 py-2 border border-gray-200 <?= $is_admin ? '' : 'bg-gray-50' ?> rounded-lg text-xs" <?= $is_admin ? '' : 'readonly' ?> placeholder="Fetching...">
                            </div>
                        </div>
                        
                        <iframe id="chk-in-map" class="w-full h-32 rounded-lg border border-gray-200 hidden" frameborder="0" scrolling="no" marginheight="0" marginwidth="0"></iframe>
                        
                        <div>
                            <label class="block text-[11px] font-bold text-gray-500 mb-1.5">Check In Notes</label>
                            <textarea name="notes" rows="2" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-xs focus:outline-none focus:border-green-500 resize-none" placeholder="Enter check in notes (optional)..."></textarea>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer bg-gray-50 border-t border-gray-100 px-6 py-4 flex justify-end gap-3">
                <button type="button" class="px-4 py-2 text-xs font-semibold text-gray-600 hover:text-gray-900 transition-colors" data-dismiss="modal">Cancel</button>
                <button type="button" class="px-5 py-2 text-xs font-semibold text-white bg-green-600 rounded-xl hover:bg-green-700 transition-colors shadow-sm" id="btn-save-checkin">Check In</button>
            </div>
        </div>
    </div>
</div>

<!-- Check Out Modal -->
<div class="modal fade" id="checkout-modal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 rounded-2xl shadow-xl overflow-hidden">
            <div class="modal-header bg-red-600 text-white border-0 px-5 py-4">
                <h4 class="modal-title text-sm font-bold flex items-center gap-2"><i class="fa fa-map-marker"></i> Check Out Visit</h4>
                <button type="button" class="close text-white opacity-80 hover:opacity-100 text-lg leading-none" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body p-6 bg-white">
                <form id="checkout-form" onsubmit="return false;">
                    <input type="hidden" name="<?= $csrf_name ?>" value="<?= $csrf_hash ?>">
                    
                    <div class="space-y-4">
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-[11px] font-bold text-gray-500 mb-1.5">Check Out Time</label>
                                <input type="text" id="chk-out-time-display" name="check_out_at" class="w-full px-3 py-2 border border-gray-200 <?= $is_admin ? '' : 'bg-gray-50' ?> rounded-lg text-xs" <?= $is_admin ? '' : 'readonly' ?>>
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-gray-500 mb-1.5">Location</label>
                                <input type="text" id="chk-out-location-display" name="check_out_location" class="w-full px-3 py-2 border border-gray-200 <?= $is_admin ? '' : 'bg-gray-50' ?> rounded-lg text-xs" <?= $is_admin ? '' : 'readonly' ?> placeholder="Fetching...">
                            </div>
                        </div>

                        <iframe id="chk-out-map" class="w-full h-32 rounded-lg border border-gray-200 hidden" frameborder="0" scrolling="no" marginheight="0" marginwidth="0"></iframe>
                        
                        <div>
                            <label class="block text-[11px] font-bold text-gray-500 mb-2">Status <span class="text-red-500">*</span></label>
                            <div class="grid grid-cols-3 gap-2">
                                <label class="flex items-center gap-1.5 text-xs text-gray-700 cursor-pointer"><input type="radio" name="visit_outcome" value="Met Successfully" class="w-3.5 h-3.5 text-red-600"> Met Successfully</label>
                                <label class="flex items-center gap-1.5 text-xs text-gray-700 cursor-pointer"><input type="radio" name="visit_outcome" value="Not Available" class="w-3.5 h-3.5 text-red-600"> Not Available</label>
                                <label class="flex items-center gap-1.5 text-xs text-gray-700 cursor-pointer"><input type="radio" name="visit_outcome" value="Reschedule" class="w-3.5 h-3.5 text-red-600"> Reschedule</label>
                                <label class="flex items-center gap-1.5 text-xs text-gray-700 cursor-pointer"><input type="radio" name="visit_outcome" value="Refused" class="w-3.5 h-3.5 text-red-600"> Refused</label>
                                <label class="flex items-center gap-1.5 text-xs text-gray-700 cursor-pointer"><input type="radio" name="visit_outcome" value="Canceled" class="w-3.5 h-3.5 text-red-600"> Canceled</label>
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-[11px] font-bold text-gray-500 mb-2">Related Follow Ups</label>
                            <div class="grid grid-cols-4 gap-2">
                                <label class="flex items-center gap-1.5 text-xs text-gray-700 cursor-pointer"><input type="checkbox" name="related_follow_ups[]" value="Quotation" class="w-3.5 h-3.5 rounded text-red-600 focus:ring-red-500"> Quotation</label>
                                <label class="flex items-center gap-1.5 text-xs text-gray-700 cursor-pointer"><input type="checkbox" name="related_follow_ups[]" value="Demo" class="w-3.5 h-3.5 rounded text-red-600 focus:ring-red-500"> Demo</label>
                                <label class="flex items-center gap-1.5 text-xs text-gray-700 cursor-pointer"><input type="checkbox" name="related_follow_ups[]" value="Proposal" class="w-3.5 h-3.5 rounded text-red-600 focus:ring-red-500"> Proposal</label>
                                <label class="flex items-center gap-1.5 text-xs text-gray-700 cursor-pointer"><input type="checkbox" name="related_follow_ups[]" value="Payment" class="w-3.5 h-3.5 rounded text-red-600 focus:ring-red-500"> Payment</label>
                                <label class="flex items-center gap-1.5 text-xs text-gray-700 cursor-pointer"><input type="checkbox" name="related_follow_ups[]" value="Support" class="w-3.5 h-3.5 rounded text-red-600 focus:ring-red-500"> Support</label>
                                <label class="flex items-center gap-1.5 text-xs text-gray-700 cursor-pointer"><input type="checkbox" name="related_follow_ups[]" value="Information" class="w-3.5 h-3.5 rounded text-red-600 focus:ring-red-500"> Information</label>
                                <label class="flex items-center gap-1.5 text-xs text-gray-700 cursor-pointer"><input type="checkbox" name="related_follow_ups[]" value="Other" class="w-3.5 h-3.5 rounded text-red-600 focus:ring-red-500"> Other</label>
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-[11px] font-bold text-gray-500 mb-1.5">Check Out Notes</label>
                            <textarea name="notes" rows="2" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-xs focus:outline-none focus:border-red-500 resize-none" placeholder="Enter visit summary..."></textarea>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer bg-gray-50 border-t border-gray-100 px-6 py-4 flex justify-end gap-3">
                <button type="button" class="px-4 py-2 text-xs font-semibold text-gray-600 hover:text-gray-900 transition-colors" data-dismiss="modal">Cancel</button>
                <button type="button" class="px-5 py-2 text-xs font-semibold text-white bg-red-600 rounded-xl hover:bg-red-700 transition-colors shadow-sm" id="btn-save-checkout">Save & Move</button>
            </div>
        </div>
    </div>
</div>

<!-- Re-plan Modal -->
<div class="modal fade" id="replan-modal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 rounded-2xl shadow-xl overflow-hidden">
            <div class="modal-header bg-yellow-500 text-white border-0 px-5 py-4">
                <h4 class="modal-title text-sm font-bold flex items-center gap-2"><i class="fa fa-calendar"></i> Re-plan Visit</h4>
                <button type="button" class="close text-white opacity-80 hover:opacity-100 text-lg leading-none" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body p-6 bg-white">
                <form id="replan-form" onsubmit="return false;">
                    <input type="hidden" name="<?= $csrf_name ?>" value="<?= $csrf_hash ?>">
                    <input type="hidden" name="id" id="replan-plan-id" value="">
                    <input type="hidden" name="action" value="replan">
                    
                    <div class="space-y-4">
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-[11px] font-bold text-gray-500 mb-1.5">New Date <span class="text-red-500">*</span></label>
                                <input type="date" id="replan-date" name="planned_date" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-xs focus:outline-none focus:border-yellow-500" required>
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-gray-500 mb-1.5">New Time <span class="text-red-500">*</span></label>
                                <input type="time" id="replan-time" name="planned_time" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-xs focus:outline-none focus:border-yellow-500" required>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer bg-gray-50 border-t border-gray-100 px-6 py-4 flex justify-end gap-3">
                <button type="button" class="px-4 py-2 text-xs font-semibold text-gray-600 hover:text-gray-900 transition-colors" data-dismiss="modal">Cancel</button>
                <button type="button" class="px-5 py-2 text-xs font-semibold text-white bg-yellow-500 rounded-xl hover:bg-yellow-600 transition-colors shadow-sm" id="btn-save-replan">Save Changes</button>
            </div>
        </div>
    </div>
</div>

<script>
    var all_customers = <?= json_encode($customers) ?>;
</script>
