<!-- Page Header -->
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
    <div class="flex items-center gap-3">
        <div class="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center flex-shrink-0">
            <i class="fa fa-building-o text-blue-600 text-lg"></i>
        </div>
        <div>
            <h1 class="text-xl font-bold text-gray-800">Customers</h1>
            <nav class="text-xs text-gray-400 flex items-center gap-1 mt-0.5">
                <a href="<?= base_url('dashboard') ?>" class="hover:text-blue-600 transition-colors">Home</a>
                <i class="fa fa-angle-right text-[10px]"></i>
                <span class="text-gray-600">Customers</span>
            </nav>
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
    <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
        <div>
            <h3 class="text-sm font-bold text-gray-800">Customer List</h3>
            <p class="text-xs text-gray-400 mt-0.5">All registered customer accounts</p>
        </div>
        <span class="px-2.5 py-1 bg-blue-50 text-blue-600 text-xs font-semibold rounded-lg">
            <i class="fa fa-building-o mr-1"></i> Customers
        </span>
    </div>
    <div class="p-4 overflow-x-auto">
        <table id="customers-table" class="w-full">
            <thead>
                <tr>
                    <th>#</th><th>Name</th><th>Phone</th><th>Email</th>
                    <th>City</th><th>State</th><th>Assigned To</th>
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
