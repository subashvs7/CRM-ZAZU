<!-- Page Header -->
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
    <div class="flex items-center gap-3">
        <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-blue-700 rounded-2xl flex items-center justify-center flex-shrink-0 shadow-sm">
            <span class="text-white font-extrabold text-lg"><?= strtoupper(substr($customer['name'], 0, 1)) ?></span>
        </div>
        <div>
            <h1 class="text-xl font-bold text-gray-800"><?= esc_html($customer['name']) ?></h1>
            <nav class="text-xs text-gray-400 flex items-center gap-1 mt-0.5">
                <a href="<?= base_url('dashboard') ?>" class="hover:text-blue-600 transition-colors">Home</a>
                <i class="fa fa-angle-right text-[10px]"></i>
                <a href="<?= base_url('customers') ?>" class="hover:text-blue-600 transition-colors">Customers</a>
                <i class="fa fa-angle-right text-[10px]"></i>
                <span class="text-gray-600"><?= esc_html($customer['name']) ?></span>
            </nav>
        </div>
    </div>
    <div class="flex items-center gap-2 self-start sm:self-auto">
        <?= status_badge($customer['status']) ?>
    </div>
</div>

<div class="grid grid-cols-1 xl:grid-cols-3 gap-4">
    <!-- Left Column -->
    <div class="xl:col-span-1 space-y-4">

        <!-- Info Card -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100">
            <div class="px-6 py-4 border-b border-gray-100">
                <h3 class="text-sm font-bold text-gray-800 flex items-center gap-2">
                    <i class="fa fa-info-circle text-blue-500"></i> Customer Info
                </h3>
            </div>
            <div class="p-5 space-y-3">
                <div class="flex items-center justify-between py-1 border-b border-gray-50">
                    <span class="text-xs text-gray-400 uppercase font-semibold tracking-wide">Phone</span>
                    <a href="tel:<?= esc_html($customer['phone']) ?>" class="text-sm font-semibold text-blue-600 hover:underline">
                        <i class="fa fa-phone mr-1 text-xs"></i><?= esc_html($customer['phone']) ?>
                    </a>
                </div>
                <div class="flex items-center justify-between py-1 border-b border-gray-50">
                    <span class="text-xs text-gray-400 uppercase font-semibold tracking-wide">Email</span>
                    <span class="text-sm font-medium text-gray-700"><?= esc_html($customer['email'] ?? '—') ?></span>
                </div>
                <div class="flex items-center justify-between py-1 border-b border-gray-50">
                    <span class="text-xs text-gray-400 uppercase font-semibold tracking-wide">City</span>
                    <span class="text-sm text-gray-700"><?= esc_html($customer['city'] ?? '—') ?></span>
                </div>
                <div class="flex items-center justify-between py-1 border-b border-gray-50">
                    <span class="text-xs text-gray-400 uppercase font-semibold tracking-wide">State</span>
                    <span class="text-sm text-gray-700"><?= esc_html($customer['state'] ?? '—') ?></span>
                </div>
                <?php if(!empty($customer['gst_number'])): ?>
                <div class="flex items-center justify-between py-1 border-b border-gray-50">
                    <span class="text-xs text-gray-400 uppercase font-semibold tracking-wide">GST</span>
                    <span class="text-sm font-mono text-gray-700"><?= esc_html($customer['gst_number']) ?></span>
                </div>
                <?php endif; ?>
                <div class="flex items-center justify-between py-1">
                    <span class="text-xs text-gray-400 uppercase font-semibold tracking-wide">Assigned To</span>
                    <span class="text-sm font-medium text-gray-700">
                        <?php if(!empty($customer['assigned_name'])): ?>
                        <span class="inline-flex items-center gap-1.5">
                            <span class="w-5 h-5 rounded-full bg-blue-100 text-blue-700 text-[10px] font-bold flex items-center justify-center">
                                <?= strtoupper(substr($customer['assigned_name'], 0, 1)) ?>
                            </span>
                            <?= esc_html($customer['assigned_name']) ?>
                        </span>
                        <?php else: ?>
                        <span class="text-gray-400">Unassigned</span>
                        <?php endif; ?>
                    </span>
                </div>
            </div>
            <?php if(!empty($customer['notes'])): ?>
            <div class="px-5 pb-5">
                <div class="bg-amber-50 border border-amber-100 rounded-xl p-3">
                    <p class="text-xs text-amber-600 font-semibold uppercase tracking-wide mb-1">
                        <i class="fa fa-sticky-note mr-1"></i> Notes
                    </p>
                    <p class="text-xs text-gray-600 leading-relaxed"><?= nl2br(esc_html($customer['notes'])) ?></p>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Contact Persons -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                <h3 class="text-sm font-bold text-gray-800 flex items-center gap-2">
                    <i class="fa fa-users text-purple-500"></i> Contact Persons
                </h3>
                <button id="btn-add-contact" data-customer="<?= $customer['id'] ?>"
                        class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors font-semibold">
                    <i class="fa fa-plus"></i> Add
                </button>
            </div>
            <div class="divide-y divide-gray-50" id="contacts-list">
                <?php if($contacts): ?>
                    <?php foreach($contacts as $c): ?>
                    <div class="px-5 py-3.5 flex items-start gap-3">
                        <div class="w-8 h-8 rounded-full bg-purple-100 text-purple-700 text-xs font-bold flex items-center justify-center flex-shrink-0 mt-0.5">
                            <?= strtoupper(substr($c['name'], 0, 1)) ?>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="text-sm font-semibold text-gray-800"><?= esc_html($c['name']) ?></span>
                                <?php if($c['is_primary']): ?>
                                <span class="inline-flex items-center px-1.5 py-0.5 text-[10px] font-bold rounded-md bg-green-100 text-green-700 uppercase tracking-wide">Primary</span>
                                <?php endif; ?>
                            </div>
                            <?php if(!empty($c['designation'])): ?>
                            <p class="text-xs text-gray-400 mt-0.5"><?= esc_html($c['designation']) ?></p>
                            <?php endif; ?>
                            <?php if(!empty($c['phone'])): ?>
                            <p class="text-xs text-gray-600 mt-1">
                                <i class="fa fa-phone text-gray-300 mr-1"></i><?= esc_html($c['phone']) ?>
                            </p>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                <div class="p-8 text-center">
                    <div class="w-10 h-10 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-2">
                        <i class="fa fa-user text-gray-300"></i>
                    </div>
                    <p class="text-sm text-gray-400">No contacts added</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Right Column -->
    <div class="xl:col-span-2 space-y-4">

        <!-- Recent Orders -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 bg-amber-100 rounded-lg flex items-center justify-center">
                        <i class="fa fa-shopping-cart text-amber-600 text-sm"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-gray-800">Recent Orders</h3>
                        <p class="text-xs text-gray-400">Latest 5 orders</p>
                    </div>
                </div>
                <a href="<?= base_url('orders') ?>"
                   class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition-colors">
                    All Orders <i class="fa fa-arrow-right ml-0.5"></i>
                </a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50">
                            <th class="px-5 py-3 text-left text-[11px] font-bold text-gray-500 uppercase tracking-wide">Order #</th>
                            <th class="px-5 py-3 text-left text-[11px] font-bold text-gray-500 uppercase tracking-wide">Amount</th>
                            <th class="px-5 py-3 text-left text-[11px] font-bold text-gray-500 uppercase tracking-wide">Status</th>
                            <th class="px-5 py-3 text-left text-[11px] font-bold text-gray-500 uppercase tracking-wide">Date</th>
                        </tr>
                    </thead>
                    <tbody id="customer-orders-body">
                        <tr><td colspan="4" class="py-6 text-center text-gray-300"><i class="fa fa-spinner fa-spin"></i></td></tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Recent Leads -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 bg-emerald-100 rounded-lg flex items-center justify-center">
                        <i class="fa fa-filter text-emerald-600 text-sm"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-gray-800">Leads</h3>
                        <p class="text-xs text-gray-400">Latest 5 leads</p>
                    </div>
                </div>
                <a href="<?= base_url('leads') ?>"
                   class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition-colors">
                    All Leads <i class="fa fa-arrow-right ml-0.5"></i>
                </a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50">
                            <th class="px-5 py-3 text-left text-[11px] font-bold text-gray-500 uppercase tracking-wide">Title</th>
                            <th class="px-5 py-3 text-left text-[11px] font-bold text-gray-500 uppercase tracking-wide">Stage</th>
                            <th class="px-5 py-3 text-left text-[11px] font-bold text-gray-500 uppercase tracking-wide">Value</th>
                            <th class="px-5 py-3 text-left text-[11px] font-bold text-gray-500 uppercase tracking-wide">Date</th>
                        </tr>
                    </thead>
                    <tbody id="customer-leads-body">
                        <tr><td colspan="4" class="py-6 text-center text-gray-300"><i class="fa fa-spinner fa-spin"></i></td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
var cid = <?= (int)$customer['id'] ?>;

// Orders columns: 0=id, 1=order_number, 2=customer_name, 3=order_status badge(HTML),
//                 4=final_amount(HTML), 5=created_by, 6=date, 7=status badge, 8=actions
$.getJSON(BASE_URL+'orders/datatable?customer_id='+cid+'&length=5&start=0&draw=1', function(res) {
    var html = '';
    $.each(res.data||[], function(i,r) {
        html += '<tr class="border-b border-gray-50 hover:bg-gray-50">' +
            '<td class="px-5 py-3 font-medium text-gray-800">'+CRM.esc(r[1])+'</td>' +
            '<td class="px-5 py-3 font-semibold text-gray-700">'+(r[4]||'—')+'</td>' +
            '<td class="px-5 py-3">'+(r[3]||'—')+'</td>' +
            '<td class="px-5 py-3 text-xs text-gray-400">'+CRM.esc(r[6]||'—')+'</td>' +
            '</tr>';
    });
    $('#customer-orders-body').html(html||'<tr><td colspan="4" class="py-8 text-center text-gray-400 text-sm">No orders found</td></tr>');
});

// Leads columns: 0=id, 1=title, 2=customer_name, 3=lead_status badge(HTML), 4=source,
//                5=assigned_name, 6=expected_value(HTML), 7=close_date, 8=status badge, 9=date, 10=actions
$.getJSON(BASE_URL+'leads/datatable?customer_id='+cid+'&length=5&start=0&draw=1', function(res) {
    var html = '';
    $.each(res.data||[], function(i,r) {
        html += '<tr class="border-b border-gray-50 hover:bg-gray-50">' +
            '<td class="px-5 py-3 font-medium text-gray-800">'+CRM.esc(r[1])+'</td>' +
            '<td class="px-5 py-3">'+(r[3]||'—')+'</td>' +
            '<td class="px-5 py-3 text-gray-700">'+(r[6]||'—')+'</td>' +
            '<td class="px-5 py-3 text-xs text-gray-400">'+CRM.esc(r[9]||'—')+'</td>' +
            '</tr>';
    });
    $('#customer-leads-body').html(html||'<tr><td colspan="4" class="py-8 text-center text-gray-400 text-sm">No leads found</td></tr>');
});
</script>
