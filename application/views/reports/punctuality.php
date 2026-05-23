<!-- Page Header -->
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
    <div class="flex items-center gap-3">
        <div class="w-10 h-10 bg-green-100 rounded-xl flex items-center justify-center flex-shrink-0">
            <i class="fa fa-check-circle text-green-600 text-lg"></i>
        </div>
        <div>
            <h1 class="text-xl font-bold text-gray-800">Punctuality Report</h1>
            <nav class="text-xs text-gray-400 flex items-center gap-1 mt-0.5">
                <a href="<?= base_url('dashboard') ?>" class="hover:text-blue-600 transition-colors">Home</a>
                <i class="fa fa-angle-right text-[10px]"></i>
                <span class="text-gray-600">Reports — Punctuality</span>
            </nav>
        </div>
    </div>
</div>

<?php include(APPPATH.'views/reports/_filter_bar.php'); ?>

<div class="bg-white rounded-2xl shadow-sm border border-gray-100">
    <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
        <div>
            <h3 class="text-sm font-bold text-gray-800">Punctuality Summary</h3>
            <p class="text-xs text-gray-400 mt-0.5">Present days, avg hours, and punctuality percentage</p>
        </div>
        <span class="px-2.5 py-1 bg-green-50 text-green-600 text-xs font-semibold rounded-lg">
            <i class="fa fa-check-circle mr-1"></i> Compliance
        </span>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50">
                    <th class="px-5 py-3 text-left text-[11px] font-bold text-gray-500 uppercase tracking-wide">Staff</th>
                    <th class="px-5 py-3 text-right text-[11px] font-bold text-gray-500 uppercase tracking-wide">Total Days</th>
                    <th class="px-5 py-3 text-right text-[11px] font-bold text-gray-500 uppercase tracking-wide">Present</th>
                    <th class="px-5 py-3 text-right text-[11px] font-bold text-gray-500 uppercase tracking-wide">Avg Hours</th>
                    <th class="px-5 py-3 text-left pl-5 text-[11px] font-bold text-gray-500 uppercase tracking-wide">Punctuality</th>
                </tr>
            </thead>
            <tbody id="punct-body" class="divide-y divide-gray-50">
                <tr>
                    <td colspan="5" class="py-10 text-center text-gray-400 text-sm">Apply filter to load data</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<script>
function loadReport() {
    $.getJSON(BASE_URL + 'reports/punctuality_data', {from: $('#from-date').val(), to: $('#to-date').val()}, function(res) {
        var html = '';
        $.each(res.data || [], function(i, r) {
            var pct = r.total_days > 0 ? (r.present_days / r.total_days * 100).toFixed(0) : 0;
            var barColor = pct >= 80 ? '#22c55e' : pct >= 60 ? '#f59e0b' : '#ef4444';
            html += '<tr class="hover:bg-gray-50">' +
                '<td class="px-5 py-3">' +
                '<div class="flex items-center gap-2">' +
                '<div class="w-7 h-7 rounded-full bg-blue-100 text-blue-700 text-[11px] font-bold flex items-center justify-center flex-shrink-0">' +
                CRM.esc((r.staff_name || '?')[0].toUpperCase()) +
                '</div>' +
                '<span class="text-sm font-semibold text-gray-800">' + CRM.esc(r.staff_name) + '</span>' +
                '</div>' +
                '</td>' +
                '<td class="px-5 py-3 text-right text-gray-600">' + r.total_days + '</td>' +
                '<td class="px-5 py-3 text-right font-bold text-blue-600">' + r.present_days + '</td>' +
                '<td class="px-5 py-3 text-right text-gray-600">' + parseFloat(r.avg_hours || 0).toFixed(2) + 'h</td>' +
                '<td class="px-5 py-3 pl-5">' +
                '<div class="flex items-center gap-3">' +
                '<div class="flex-1 bg-gray-100 rounded-full h-2">' +
                '<div class="h-2 rounded-full transition-all" style="width:' + pct + '%;background:' + barColor + '"></div>' +
                '</div>' +
                '<span class="text-xs font-bold text-gray-700 w-10 text-right flex-shrink-0">' + pct + '%</span>' +
                '</div>' +
                '</td>' +
                '</tr>';
        });
        $('#punct-body').html(html || '<tr><td colspan="5" class="py-8 text-center text-gray-400">No data for this period</td></tr>');
    });
}
$('#btn-filter').click(loadReport);
</script>
