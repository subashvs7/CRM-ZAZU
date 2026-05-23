<!-- Page Header -->
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
    <div class="flex items-center gap-3">
        <div class="w-12 h-12 bg-gradient-to-br from-amber-500 to-orange-600 rounded-2xl flex items-center justify-center flex-shrink-0 shadow-sm">
            <i class="fa fa-shopping-cart text-white text-lg"></i>
        </div>
        <div>
            <h1 class="text-xl font-bold text-gray-800">Order <?= esc_html($order['order_number']) ?></h1>
            <nav class="text-xs text-gray-400 flex items-center gap-1 mt-0.5">
                <a href="<?= base_url('dashboard') ?>" class="hover:text-blue-600 transition-colors">Home</a>
                <i class="fa fa-angle-right text-[10px]"></i>
                <a href="<?= base_url('orders') ?>" class="hover:text-blue-600 transition-colors">Orders</a>
                <i class="fa fa-angle-right text-[10px]"></i>
                <span class="text-gray-600"><?= esc_html($order['order_number']) ?></span>
            </nav>
        </div>
    </div>
    <div class="flex items-center gap-2 self-start sm:self-auto">
        <?= order_status_badge($order['order_status']) ?>
        <a href="<?= base_url('orders/pdf/'.$order['id']) ?>"
           class="inline-flex items-center gap-1.5 px-3 py-2 text-sm bg-white border border-gray-200 text-gray-700 font-medium rounded-xl hover:bg-gray-50 transition-colors">
            <i class="fa fa-download text-gray-500"></i> PDF
        </a>
    </div>
</div>

<div class="grid grid-cols-1 xl:grid-cols-3 gap-4">
    <!-- Left: Order Info -->
    <div class="xl:col-span-1 space-y-4">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100">
            <div class="px-6 py-4 border-b border-gray-100">
                <h3 class="text-sm font-bold text-gray-800 flex items-center gap-2">
                    <i class="fa fa-info-circle text-amber-500"></i> Order Info
                </h3>
            </div>
            <div class="p-5 space-y-3">
                <div class="flex items-center justify-between py-1 border-b border-gray-50">
                    <span class="text-xs text-gray-400 uppercase font-semibold tracking-wide">Customer</span>
                    <span class="text-sm font-semibold text-gray-800"><?= esc_html($order['customer_name']) ?></span>
                </div>
                <?php if(!empty($order['customer_phone'])): ?>
                <div class="flex items-center justify-between py-1 border-b border-gray-50">
                    <span class="text-xs text-gray-400 uppercase font-semibold tracking-wide">Phone</span>
                    <a href="tel:<?= esc_html($order['customer_phone']) ?>" class="text-sm text-blue-600 hover:underline">
                        <?= esc_html($order['customer_phone']) ?>
                    </a>
                </div>
                <?php endif; ?>
                <div class="flex items-center justify-between py-1 border-b border-gray-50">
                    <span class="text-xs text-gray-400 uppercase font-semibold tracking-wide">Created By</span>
                    <span class="text-sm font-medium text-gray-700"><?= esc_html($order['created_by_name']) ?></span>
                </div>
                <?php if(!empty($order['delivery_date'])): ?>
                <div class="flex items-center justify-between py-1 border-b border-gray-50">
                    <span class="text-xs text-gray-400 uppercase font-semibold tracking-wide">Delivery Date</span>
                    <span class="text-sm text-gray-700">
                        <i class="fa fa-calendar-o mr-1 text-gray-400"></i><?= esc_html($order['delivery_date']) ?>
                    </span>
                </div>
                <?php endif; ?>
                <?php if(!empty($order['approved_by'])): ?>
                <div class="flex items-center justify-between py-1 border-b border-gray-50">
                    <span class="text-xs text-gray-400 uppercase font-semibold tracking-wide">Approved By</span>
                    <span class="text-sm text-gray-700"><?= esc_html($order['approved_by_name']) ?></span>
                </div>
                <div class="flex items-center justify-between py-1 border-b border-gray-50">
                    <span class="text-xs text-gray-400 uppercase font-semibold tracking-wide">Approved At</span>
                    <span class="text-xs text-gray-600"><?= esc_html($order['approved_at']) ?></span>
                </div>
                <?php endif; ?>
            </div>

            <!-- Amount Summary -->
            <div class="mx-5 mb-5 bg-gray-50 rounded-xl p-4 space-y-2">
                <div class="flex justify-between text-sm text-gray-500">
                    <span>Subtotal</span>
                    <span><?= format_inr($order['total_amount']) ?></span>
                </div>
                <div class="flex justify-between text-sm text-gray-500 border-t border-gray-100 pt-2">
                    <span>Discount</span>
                    <span class="text-red-500">−<?= format_inr($order['discount_amount']) ?></span>
                </div>
                <div class="flex justify-between border-t border-gray-200 pt-2">
                    <span class="text-sm font-bold text-gray-800">Final Amount</span>
                    <span class="text-base font-extrabold text-blue-600"><?= format_inr($order['final_amount']) ?></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Right: Order Items -->
    <div class="xl:col-span-2">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-2">
                <div class="w-8 h-8 bg-cyan-100 rounded-lg flex items-center justify-center">
                    <i class="fa fa-list text-cyan-600 text-sm"></i>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-gray-800">Order Items</h3>
                    <p class="text-xs text-gray-400"><?= count($items) ?> item<?= count($items) !== 1 ? 's' : '' ?></p>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50">
                            <th class="px-5 py-3 text-left text-[11px] font-bold text-gray-500 uppercase tracking-wide">Product</th>
                            <th class="px-5 py-3 text-left text-[11px] font-bold text-gray-500 uppercase tracking-wide">SKU</th>
                            <th class="px-5 py-3 text-left text-[11px] font-bold text-gray-500 uppercase tracking-wide">Unit</th>
                            <th class="px-5 py-3 text-right text-[11px] font-bold text-gray-500 uppercase tracking-wide">Qty</th>
                            <th class="px-5 py-3 text-right text-[11px] font-bold text-gray-500 uppercase tracking-wide">Unit Price</th>
                            <th class="px-5 py-3 text-right text-[11px] font-bold text-gray-500 uppercase tracking-wide">Disc %</th>
                            <th class="px-5 py-3 text-right text-[11px] font-bold text-gray-500 uppercase tracking-wide">Line Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        <?php foreach($items as $item): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-5 py-3 font-semibold text-gray-800"><?= esc_html($item['product_name']) ?></td>
                            <td class="px-5 py-3 text-xs text-gray-400 font-mono"><?= esc_html($item['sku']) ?></td>
                            <td class="px-5 py-3 text-gray-500"><?= esc_html($item['unit']) ?></td>
                            <td class="px-5 py-3 text-right font-bold text-gray-800"><?= $item['qty'] ?></td>
                            <td class="px-5 py-3 text-right text-gray-700"><?= format_inr($item['unit_price']) ?></td>
                            <td class="px-5 py-3 text-right">
                                <?php if($item['discount_pct'] > 0): ?>
                                <span class="inline-flex items-center px-1.5 py-0.5 text-xs bg-red-50 text-red-600 rounded-md font-semibold">
                                    <?= $item['discount_pct'] ?>%
                                </span>
                                <?php else: ?>
                                <span class="text-gray-400">—</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-5 py-3 text-right font-bold text-gray-800"><?= format_inr($item['line_total']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot class="bg-gray-50 border-t-2 border-gray-200">
                        <tr>
                            <td colspan="6" class="px-5 py-2 text-right text-sm text-gray-500">Subtotal</td>
                            <td class="px-5 py-2 text-right text-sm font-semibold text-gray-700"><?= format_inr($order['total_amount']) ?></td>
                        </tr>
                        <tr>
                            <td colspan="6" class="px-5 py-2 text-right text-sm text-gray-500">Discount</td>
                            <td class="px-5 py-2 text-right text-sm font-semibold text-red-500">−<?= format_inr($order['discount_amount']) ?></td>
                        </tr>
                        <tr class="border-t border-gray-200">
                            <td colspan="6" class="px-5 py-3 text-right text-sm font-bold text-gray-800">Final Amount</td>
                            <td class="px-5 py-3 text-right text-base font-extrabold text-blue-600"><?= format_inr($order['final_amount']) ?></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>
