<!-- Page Header -->
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
    <div class="flex items-center gap-3">
        <div class="w-10 h-10 bg-amber-100 rounded-xl flex items-center justify-center flex-shrink-0">
            <i class="fa fa-shopping-cart text-amber-600 text-lg"></i>
        </div>
        <div>
            <h1 class="text-xl font-bold text-gray-800">Orders</h1>
            <nav class="text-xs text-gray-400 flex items-center gap-1 mt-0.5">
                <a href="<?= base_url('dashboard') ?>" class="hover:text-blue-600 transition-colors">Home</a>
                <i class="fa fa-angle-right text-[10px]"></i>
                <span class="text-gray-600">Orders</span>
            </nav>
        </div>
    </div>
    <div class="flex items-center gap-2 self-start sm:self-auto">
        <?php if($is_manager): ?>
        <a href="<?= base_url('orders/approval') ?>"
           class="inline-flex items-center gap-1.5 px-3 py-2 text-sm bg-amber-500 text-white font-medium rounded-xl hover:bg-amber-600 transition-colors">
            <i class="fa fa-clock-o"></i> Pending Approval
        </a>
        <?php endif; ?>
        <button id="btn-add-order"
                class="inline-flex items-center gap-1.5 px-4 py-2 text-sm bg-blue-600 text-white font-semibold rounded-xl hover:bg-blue-700 transition-colors shadow-sm">
            <i class="fa fa-plus"></i> New Order
        </button>
    </div>
</div>

<!-- Status Tabs -->
<?php $this->load->view('partials/_status_tabs', get_defined_vars()); ?>

<!-- Orders Table -->
<div class="bg-white rounded-2xl shadow-sm border border-gray-100">
    <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
        <div>
            <h3 class="text-sm font-bold text-gray-800">Order List</h3>
            <p class="text-xs text-gray-400 mt-0.5">All sales orders</p>
        </div>
        <span class="px-2.5 py-1 bg-amber-50 text-amber-600 text-xs font-semibold rounded-lg">
            <i class="fa fa-shopping-cart mr-1"></i> Orders
        </span>
    </div>
    <div class="p-4 overflow-x-auto">
        <table id="orders-table" class="w-full">
            <thead>
                <tr>
                    <th>#</th><th>Order #</th><th>Customer</th><th>Status</th>
                    <th>Amount</th><th>Created By</th><th>Date</th><th>Record</th><th>Actions</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>

<!-- Order Modal -->
<div class="modal fade" id="order-modal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">New Order</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <form id="order-form" method="POST" action="<?= base_url('orders/save') ?>">
                    <input type="hidden" name="<?= $csrf_name ?>" value="<?= $csrf_hash ?>">
                    <input type="hidden" name="id" id="order-id" value="0">
                    <input type="hidden" name="items" id="order-items-json" value="[]">

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-5">
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">Customer *</label>
                            <select name="customer_id" class="w-full px-3 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 select2" required>
                                <option value="">— Select Customer —</option>
                                <?php foreach($customers as $c): ?>
                                <option value="<?= $c['id'] ?>"><?= esc_html($c['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">Delivery Date</label>
                            <input type="text" name="delivery_date" class="w-full px-3 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 datepicker">
                        </div>
                    </div>

                    <div class="mb-2 flex items-center justify-between">
                        <p class="text-xs font-bold text-gray-700 uppercase tracking-wide">Order Items</p>
                        <button type="button" id="btn-add-item"
                                class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs bg-cyan-600 text-white rounded-lg hover:bg-cyan-700 font-semibold">
                            <i class="fa fa-plus"></i> Add Item
                        </button>
                    </div>
                    <div class="overflow-x-auto border border-gray-100 rounded-xl mb-4">
                        <table class="w-full text-sm" id="order-items-table">
                            <thead class="bg-gray-50">
                                <tr class="text-[11px] font-bold text-gray-500 uppercase">
                                    <th class="px-3 py-2.5 text-left">Product</th>
                                    <th class="px-3 py-2.5 text-left">Qty</th>
                                    <th class="px-3 py-2.5 text-left">Unit Price (₹)</th>
                                    <th class="px-3 py-2.5 text-left">Disc %</th>
                                    <th class="px-3 py-2.5 text-left">Line Total</th>
                                    <th class="px-3 py-2.5"></th>
                                </tr>
                            </thead>
                            <tbody id="order-items-body"></tbody>
                        </table>
                    </div>

                    <div class="flex justify-end mb-4">
                        <div class="w-72 bg-gray-50 rounded-xl p-4 space-y-2 text-sm">
                            <div class="flex justify-between text-gray-600">
                                <span>Subtotal</span>
                                <span id="order-subtotal" class="font-medium">₹0.00</span>
                            </div>
                            <div class="flex justify-between items-center text-gray-600 border-t border-gray-100 pt-2">
                                <span>Discount (₹)</span>
                                <input type="number" step="0.01" name="discount_amount" id="order-discount"
                                       class="w-24 px-2 py-1 border border-gray-200 rounded-lg text-xs focus:outline-none focus:ring-1 focus:ring-blue-500 text-right bg-white"
                                       value="0">
                            </div>
                            <div class="flex justify-between border-t border-gray-200 pt-2">
                                <span class="font-bold text-gray-800">Total</span>
                                <strong id="order-total" class="text-blue-600">₹0.00</strong>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">Notes</label>
                        <textarea name="notes" rows="2" class="w-full px-3 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="inline-flex items-center gap-1.5 px-4 py-2 text-sm bg-white border border-gray-200 text-gray-700 rounded-xl hover:bg-gray-50 font-medium" data-dismiss="modal">
                    Cancel
                </button>
                <button class="inline-flex items-center gap-1.5 px-4 py-2 text-sm bg-amber-500 text-white rounded-xl hover:bg-amber-600 font-semibold" id="btn-save-draft">
                    <i class="fa fa-save"></i> Save Draft
                </button>
                <button class="inline-flex items-center gap-1.5 px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700 font-semibold" id="btn-submit-approval">
                    <i class="fa fa-paper-plane"></i> Submit for Approval
                </button>
            </div>
        </div>
    </div>
</div>

<script>
window.PRODUCT_LIST = <?= json_encode(array_map(function($p){
    return ['id'=>(int)$p['id'],'name'=>$p['name'],'sku'=>$p['sku'],'unit'=>$p['unit'],'price'=>(int)$p['price'],'min_price'=>(int)$p['min_price']];
}, $products ?? [])) ?>;
</script>
