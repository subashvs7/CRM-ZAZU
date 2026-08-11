<!-- Page Header -->
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
    <div class="flex items-center gap-3">
        <div class="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center flex-shrink-0">
            <i class="fa fa-building-o text-blue-600 text-lg"></i>
        </div>
        <div>
            <h1 class="text-xl font-bold text-gray-800"><?= isset($page_title) ? esc_html($page_title) : 'Customers' ?></h1>
            <nav class="text-xs text-gray-400 flex items-center gap-1 mt-0.5">
                <a href="<?= base_url('dashboard') ?>" class="hover:text-blue-600 transition-colors">Home</a>
                <i class="fa fa-angle-right text-[10px]"></i>
                <span class="text-gray-600"><?= isset($page_title) ? esc_html($page_title) : 'Customers' ?></span>
            </nav>
            <input type="hidden" id="filter-customer-type" value="<?= isset($customer_type) ? esc_html($customer_type) : '' ?>">
        </div>
    </div>
    <button id="btn-add-customer"
            class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white text-sm font-semibold rounded-xl hover:bg-blue-700 transition-colors shadow-sm self-start sm:self-auto">
        <i class="fa fa-plus"></i> Add Customer
    </button>
</div>

<!-- Status Tabs -->
<?php $this->load->view('partials/_status_tabs', get_defined_vars()); ?>

<!-- Customers Table Card -->
<div class="bg-white rounded-2xl shadow-sm border border-gray-100">
    <div class="px-6 py-4 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h3 class="text-sm font-bold text-gray-800">Customer List</h3>
            <p class="text-xs text-gray-400 mt-0.5">All registered customer accounts</p>
        </div>
        <div class="flex flex-col sm:flex-row gap-3 items-center">
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
        <table id="customers-table" class="w-full">
            <thead>
                <tr>
                    <th>#</th><th>Name</th><th>Phone / Email</th>
                    <th>City / State</th><th>Assigned To</th><th>Products</th><th>Notes</th>
                    <th>Status</th><th>Created</th><th>Actions</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>

<!-- Customer Modal -->
<div class="modal fade" id="customer-modal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="customer-modal-title">Customer</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <form id="customer-form" method="POST" action="<?= base_url('customers/save') ?>">
                    <input type="hidden" name="<?= $csrf_name ?>" value="<?= $csrf_hash ?>">
                    <input type="hidden" name="id" id="customer-id" value="0">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">Name *</label>
                                <input type="text" name="name" class="w-full px-3 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-shadow" required>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">Phone *</label>
                                <input type="text" name="phone" class="w-full px-3 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-shadow" required>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">Email</label>
                                <input type="email" name="email" class="w-full px-3 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-shadow">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">GST Number</label>
                                <input type="text" name="gst_number" class="w-full px-3 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-shadow">
                            </div>
                            <?php if($is_manager): ?>
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">Assigned To</label>
                                <select name="assigned_to" class="w-full px-3 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 select2">
                                    <option value="">— Unassigned —</option>
                                    <?php foreach($staff as $s): ?>
                                    <option value="<?= $s['id'] ?>"><?= esc_html($s['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <?php endif; ?>
                        </div>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">Address</label>
                                <textarea name="address" rows="3" class="w-full px-3 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-shadow resize-none"></textarea>
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">City</label>
                                    <input type="text" name="city" class="w-full px-3 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-shadow">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">State</label>
                                    <input type="text" name="state" class="w-full px-3 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-shadow">
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">Pincode</label>
                                <input type="text" name="pincode" class="w-full px-3 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-shadow">
                            </div>
                            <div class="location-picker-group">
                                <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">
                                    Location <span class="text-gray-400 font-normal text-xs normal-case">(GPS coordinates)</span>
                                </label>
                                <div class="grid grid-cols-2 gap-2 mb-2">
                                    <input type="text" name="latitude" id="cust-lat" class="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Latitude">
                                    <input type="text" name="longitude" id="cust-lng" class="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Longitude">
                                </div>
                                <button type="button" class="w-full flex items-center justify-center gap-2 px-3 py-2 text-xs bg-slate-700 text-white rounded-xl hover:bg-slate-800 btn-detect-location"
                                        data-lat="[name='latitude']" data-lng="[name='longitude']"
                                        data-feedback="#cust-location-feedback" data-map="#cust-map-preview">
                                    <i class="fa fa-crosshairs"></i> Detect My Location
                                </button>
                                <div id="cust-location-feedback" class="mt-1 text-xs text-gray-500"></div>
                                <div id="cust-map-preview" class="hidden mt-2 rounded-xl overflow-hidden"></div>
                            </div>
                        </div>
                    </div>
                    <div class="mt-4">
                        <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">Products</label>
                        <select name="product_ids[]" id="customer-products" class="w-full px-3 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 select2" multiple="multiple" data-placeholder="Select Products">
                            <?php foreach($products as $p): ?>
                                <option value="<?= $p['id'] ?>"><?= esc_html($p['name']) ?> (<?= esc_html($p['sku']) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mt-4">
                        <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">Notes</label>
                        <textarea name="notes" rows="2" class="w-full px-3 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-shadow resize-none"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="inline-flex items-center gap-1.5 px-4 py-2 text-sm bg-white border border-gray-200 text-gray-700 rounded-xl hover:bg-gray-50 transition-colors font-medium" data-dismiss="modal">
                    Cancel
                </button>
                <button class="inline-flex items-center gap-1.5 px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition-colors font-semibold" id="btn-save-customer">
                    <i class="fa fa-save"></i> Save Customer
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Plan Visit Modal -->
<div class="modal fade" id="plan-visit-modal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 rounded-2xl shadow-xl overflow-hidden">
            <div class="modal-header bg-purple-600 text-white border-0 px-5 py-4">
                <h4 class="modal-title text-sm font-bold flex items-center gap-2"><i class="fa fa-calendar-plus-o"></i> Plan Visit for <span id="plan-visit-customer-name"></span></h4>
                <button type="button" class="close text-white opacity-80 hover:opacity-100 text-lg leading-none" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body p-6 bg-white">
                <form id="plan-visit-form" onsubmit="return false;">
                    <input type="hidden" name="<?= $csrf_name ?>" value="<?= $csrf_hash ?>">
                    <input type="hidden" name="customer_id" id="plan-visit-customer-id" value="">
                    
                    <div class="space-y-4">
                        <?php if($is_admin || $is_manager): ?>
                        <div>
                            <label class="block text-[11px] font-bold text-gray-500 mb-1.5">Assign To <span class="text-red-500">*</span></label>
                            <select name="user_id" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-xs focus:outline-none focus:border-purple-500" required>
                                <option value="">-- Select Staff --</option>
                                <?php foreach($staff as $s): ?>
                                    <option value="<?= $s['id'] ?>"><?= esc_html($s['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <?php else: ?>
                        <input type="hidden" name="user_id" value="<?= $this->session->userdata('user_id') ?>">
                        <?php endif; ?>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-[11px] font-bold text-gray-500 mb-1.5">Date <span class="text-red-500">*</span></label>
                                <input type="date" name="planned_date" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-xs focus:outline-none focus:border-purple-500" required>
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-gray-500 mb-1.5">Time</label>
                                <input type="time" name="planned_time" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-xs focus:outline-none focus:border-purple-500">
                            </div>
                        </div>

                        <div>
                            <label class="block text-[11px] font-bold text-gray-500 mb-1.5">Purpose <span class="text-red-500">*</span></label>
                            <select name="purpose" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-xs focus:outline-none focus:border-purple-500" required>
                                <option value="">-- Select Purpose --</option>
                                <option value="Sales Pitch">Sales Pitch</option>
                                <option value="Follow Up">Follow Up</option>
                                <option value="Payment Collection">Payment Collection</option>
                                <option value="Product Demo">Product Demo</option>
                                <option value="Routine Check">Routine Check</option>
                                <option value="Issue Resolution">Issue Resolution</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer bg-gray-50 border-t border-gray-100 px-6 py-4 flex justify-end gap-3">
                <button type="button" class="px-4 py-2 text-xs font-semibold text-gray-600 hover:text-gray-900 transition-colors" data-dismiss="modal">Cancel</button>
                <button type="button" class="px-5 py-2 text-xs font-semibold text-white bg-purple-600 rounded-xl hover:bg-purple-700 transition-colors shadow-sm" id="btn-save-plan-visit">Schedule Visit</button>
            </div>
        </div>
    </div>
</div>

<!-- Customer View Modal -->
<div class="modal fade" id="customer-view-modal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content border-0 rounded-2xl shadow-xl overflow-hidden">
            <div class="modal-header bg-blue-600 text-white border-0 px-6 py-4 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 bg-blue-500 rounded-lg flex items-center justify-center text-white font-extrabold" id="view-customer-initial"></div>
                    <div>
                        <h4 class="modal-title text-sm font-bold" id="view-customer-name">Customer Details</h4>
                        <span class="text-[10px] text-blue-100 font-medium" id="view-customer-status-badge"></span>
                    </div>
                </div>
                <button type="button" class="close text-white opacity-80 hover:opacity-100 text-lg leading-none" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body p-6 bg-slate-50">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Column 1: Info & Contacts -->
                    <div class="space-y-5">
                        <!-- General Info Box -->
                        <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm space-y-4">
                            <h3 class="text-xs font-bold text-gray-800 flex items-center gap-2 border-b pb-2.5 mb-3">
                                <i class="fa fa-info-circle text-blue-500 text-sm"></i> General Info
                            </h3>
                            <div>
                                <span class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider">Phone</span>
                                <span class="text-xs font-semibold text-gray-700" id="view-customer-phone"></span>
                            </div>
                            <div>
                                <span class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider">Email</span>
                                <span class="text-xs font-semibold text-gray-700" id="view-customer-email"></span>
                            </div>
                            <div>
                                <span class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider">GST Number</span>
                                <span class="text-xs font-mono text-gray-700" id="view-customer-gst"></span>
                            </div>
                        </div>

                        <!-- Assignment & Products Box -->
                        <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm space-y-4">
                            <h3 class="text-xs font-bold text-gray-800 flex items-center gap-2 border-b pb-2.5 mb-3">
                                <i class="fa fa-tag text-indigo-500 text-sm"></i> Assignment & Products
                            </h3>
                            <div>
                                <span class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider">Assigned To</span>
                                <span class="text-xs font-semibold text-gray-700" id="view-customer-assigned"></span>
                            </div>
                            <div>
                                <span class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider">Products</span>
                                <div class="flex flex-wrap gap-1 mt-1" id="view-customer-products-container"></div>
                            </div>
                            <div>
                                <span class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider">Address</span>
                                <span class="text-xs text-gray-600 block mt-0.5 leading-relaxed" id="view-customer-address"></span>
                            </div>
                        </div>

                        <!-- Contacts Box -->
                        <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm">
                            <h3 class="text-xs font-bold text-gray-800 flex items-center gap-2 border-b pb-2.5 mb-3">
                                <i class="fa fa-users text-purple-500 text-sm"></i> Contact Persons
                            </h3>
                            <div class="divide-y divide-gray-50" id="view-customer-contacts-list"></div>
                        </div>
                    </div>

                    <!-- Column 2 & 3: Visits & Followups -->
                    <div class="lg:col-span-2 space-y-5">
                        <!-- Planned Visits Box -->
                        <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm">
                            <h3 class="text-xs font-bold text-gray-800 flex items-center gap-2 border-b pb-2.5 mb-3">
                                <i class="fa fa-calendar text-orange-500 text-sm"></i> Visit Plans & Follow-ups
                            </h3>
                            <div class="responsive-table-container border border-gray-200 rounded-xl overflow-hidden">
                                <table class="w-full text-xs text-left border-collapse">
                                    <thead>
                                        <tr class="bg-gray-50">
                                            <th class="px-4 py-2.5 font-bold text-gray-500 uppercase tracking-wide border-r border-b border-gray-200">Planned Date</th>
                                            <th class="px-4 py-2.5 font-bold text-gray-500 uppercase tracking-wide border-r border-b border-gray-200">Purpose</th>
                                            <th class="px-4 py-2.5 font-bold text-gray-500 uppercase tracking-wide border-r border-b border-gray-200">Assign To</th>
                                            <th class="px-4 py-2.5 font-bold text-gray-500 uppercase tracking-wide border-b border-gray-200">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody id="view-customer-visits-table"></tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Actual Visit Logs Box -->
                        <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm">
                            <h3 class="text-xs font-bold text-gray-800 flex items-center gap-2 border-b pb-2.5 mb-3">
                                <i class="fa fa-check-circle text-emerald-500 text-sm"></i> Check-in/Check-out History
                            </h3>
                            <div class="responsive-table-container border border-gray-200 rounded-xl overflow-hidden">
                                <table class="w-full text-xs text-left border-collapse">
                                    <thead>
                                        <tr class="bg-gray-50">
                                            <th class="px-4 py-2.5 font-bold text-gray-500 uppercase tracking-wide border-r border-b border-gray-200">Date & Time</th>
                                            <th class="px-4 py-2.5 font-bold text-gray-500 uppercase tracking-wide border-r border-b border-gray-200">Staff</th>
                                            <th class="px-4 py-2.5 font-bold text-gray-500 uppercase tracking-wide border-r border-b border-gray-200">Outcome</th>
                                            <th class="px-4 py-2.5 font-bold text-gray-500 uppercase tracking-wide border-b border-gray-200">Notes</th>
                                        </tr>
                                    </thead>
                                    <tbody id="view-customer-logs-table"></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-gray-50 border-t border-gray-100 px-6 py-4 flex justify-end">
                <button type="button" class="px-4 py-2 text-xs font-semibold bg-white border border-gray-200 text-gray-600 hover:text-gray-900 rounded-xl transition-colors" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
