<!-- Page Header -->
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
    <div class="flex items-center gap-3">
        <div class="w-10 h-10 bg-amber-100 rounded-xl flex items-center justify-center flex-shrink-0">
            <i class="fa fa-trophy text-amber-600 text-lg"></i>
        </div>
        <div>
            <h1 class="text-xl font-bold text-gray-800">Staff Sales Performance</h1>
            <nav class="text-xs text-gray-400 flex items-center gap-1 mt-0.5">
                <a href="<?= base_url('dashboard') ?>" class="hover:text-blue-600 transition-colors">Home</a>
                <i class="fa fa-angle-right text-[10px]"></i>
                <span class="text-gray-600">Reports — Staff Sales</span>
            </nav>
        </div>
    </div>
</div>

<?php include(APPPATH.'views/reports/_filter_bar.php'); ?>

<div class="bg-white rounded-2xl shadow-sm border border-gray-100">
    <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
        <div>
            <h3 class="text-sm font-bold text-gray-800">Sales Leaderboard</h3>
            <p class="text-xs text-gray-400 mt-0.5">Ranked by revenue for the selected period</p>
        </div>
        <span class="px-2.5 py-1 bg-amber-50 text-amber-600 text-xs font-semibold rounded-lg">
            <i class="fa fa-trophy mr-1"></i> Rankings
        </span>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50">
                    <th class="px-5 py-3 text-left text-[11px] font-bold text-gray-500 uppercase tracking-wide w-10">#</th>
                    <th class="px-5 py-3 text-left text-[11px] font-bold text-gray-500 uppercase tracking-wide">Staff</th>
                    <th class="px-5 py-3 text-right text-[11px] font-bold text-gray-500 uppercase tracking-wide">Visits</th>
                    <th class="px-5 py-3 text-right text-[11px] font-bold text-gray-500 uppercase tracking-wide">Leads</th>
                    <th class="px-5 py-3 text-right text-[11px] font-bold text-gray-500 uppercase tracking-wide">Orders</th>
                    <th class="px-5 py-3 text-right text-[11px] font-bold text-gray-500 uppercase tracking-wide">Revenue</th>
                </tr>
            </thead>
            <tbody id="staff-sales-body" class="divide-y divide-gray-50">
                <tr>
                    <td colspan="6" class="py-10 text-center text-gray-400 text-sm">Apply filter to load data</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<script>
function loadReport() {
    var from = $('#from-date').val(), to = $('#to-date').val();
    var uid = $('#staff-filter').val() || '';
    $.getJSON(BASE_URL + 'reports/staff_data', {from: from, to: to, user_id: uid}, function(res) {
        var html = '';
        var medals = ['🥇', '🥈', '🥉'];
        $.each(res.data || [], function(i, r) {
            html += '<tr class="hover:bg-gray-50">' +
                '<td class="px-5 py-3">' +
                (i < 3 ? '<span class="text-base">' + medals[i] + '</span>' : '<span class="text-xs font-bold text-gray-400">' + (i + 1) + '</span>') +
                '</td>' +
                '<td class="px-5 py-3">' +
                '<div class="flex items-center gap-2">' +
                '<div class="w-7 h-7 rounded-full bg-blue-100 text-blue-700 text-[11px] font-bold flex items-center justify-center flex-shrink-0">' +
                CRM.esc((r.name || '?')[0].toUpperCase()) +
                '</div>' +
                '<span class="text-sm font-semibold text-gray-800">' + CRM.esc(r.name) + '</span>' +
                '</div>' +
                '</td>' +
                '<td class="px-5 py-3 text-right text-gray-600">' + r.visits + '</td>' +
                '<td class="px-5 py-3 text-right text-gray-600">' + r.leads + '</td>' +
                '<td class="px-5 py-3 text-right text-gray-600">' + r.orders + '</td>' +
                '<td class="px-5 py-3 text-right font-bold text-emerald-600">₹' + parseFloat((r.revenue || 0) / 100).toLocaleString('en-IN') + '</td>' +
                '</tr>';
        });
        $('#staff-sales-body').html(html || '<tr><td colspan="6" class="py-8 text-center text-gray-400">No data for this period</td></tr>');
    });
}
$('#btn-filter').click(loadReport);
</script>
