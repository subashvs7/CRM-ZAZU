<!-- Page Header -->
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
    <div class="flex items-center gap-3">
        <div class="w-10 h-10 bg-amber-100 rounded-xl flex items-center justify-center flex-shrink-0">
            <i class="fa fa-shopping-cart text-amber-600 text-lg"></i>
        </div>
        <div>
            <h1 class="text-xl font-bold text-gray-800">Order Reports</h1>
            <nav class="text-xs text-gray-400 flex items-center gap-1 mt-0.5">
                <a href="<?= base_url('dashboard') ?>" class="hover:text-blue-600 transition-colors">Home</a>
                <i class="fa fa-angle-right text-[10px]"></i>
                <span class="text-gray-600">Reports — Orders</span>
            </nav>
        </div>
    </div>
</div>

<?php include(APPPATH.'views/reports/_filter_bar.php'); ?>

<div class="bg-white rounded-2xl shadow-sm border border-gray-100">
    <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
        <div>
            <h3 class="text-sm font-bold text-gray-800">Order Revenue by Staff</h3>
            <p class="text-xs text-gray-400 mt-0.5">Orders and revenue broken down by staff member</p>
        </div>
        <span class="px-2.5 py-1 bg-amber-50 text-amber-600 text-xs font-semibold rounded-lg">
            <i class="fa fa-shopping-cart mr-1"></i> Revenue
        </span>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50">
                    <th class="px-5 py-3 text-left text-[11px] font-bold text-gray-500 uppercase tracking-wide">Staff</th>
                    <th class="px-5 py-3 text-right text-[11px] font-bold text-gray-500 uppercase tracking-wide">Orders</th>
                    <th class="px-5 py-3 text-right text-[11px] font-bold text-gray-500 uppercase tracking-wide">Revenue</th>
                    <th class="px-5 py-3 text-left text-[11px] font-bold text-gray-500 uppercase tracking-wide">Status</th>
                </tr>
            </thead>
            <tbody id="orders-report-body" class="divide-y divide-gray-50">
                <tr>
                    <td colspan="4" class="py-10 text-center text-gray-400 text-sm">Apply filter to load data</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<script>
function loadReport() {
    var from = $('#from-date').val(), to = $('#to-date').val(), uid = $('#staff-filter').val() || '';
    $.getJSON(BASE_URL + 'reports/orders_data', {from: from, to: to, user_id: uid}, function(res) {
        var html = '';
        $.each(res.data || [], function(i, r) {
            html += '<tr class="hover:bg-gray-50">' +
                '<td class="px-5 py-3 font-semibold text-gray-800">' + CRM.esc(r.staff_name) + '</td>' +
                '<td class="px-5 py-3 text-right font-bold text-blue-600">' + r.total_orders + '</td>' +
                '<td class="px-5 py-3 text-right font-bold text-emerald-600">₹' + parseFloat((r.total_revenue || 0) / 100).toLocaleString('en-IN') + '</td>' +
                '<td class="px-5 py-3 text-gray-500 capitalize">' + CRM.esc(r.order_status) + '</td>' +
                '</tr>';
        });
        $('#orders-report-body').html(html || '<tr><td colspan="4" class="py-8 text-center text-gray-400">No data for this period</td></tr>');
    });
}
$('#btn-filter').click(loadReport);
</script>
