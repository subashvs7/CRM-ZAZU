<!-- Page Header -->
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
    <div class="flex items-center gap-3">
        <div class="w-10 h-10 bg-violet-100 rounded-xl flex items-center justify-center flex-shrink-0">
            <i class="fa fa-plane text-violet-600 text-lg"></i>
        </div>
        <div>
            <h1 class="text-xl font-bold text-gray-800">Leave Utilisation</h1>
            <nav class="text-xs text-gray-400 flex items-center gap-1 mt-0.5">
                <a href="<?= base_url('dashboard') ?>" class="hover:text-blue-600 transition-colors">Home</a>
                <i class="fa fa-angle-right text-[10px]"></i>
                <span class="text-gray-600">Reports — Leave</span>
            </nav>
        </div>
    </div>
</div>

<!-- Year Filter -->
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 mb-5">
    <div class="px-6 py-4">
        <div class="flex items-end gap-3">
            <div>
                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">Year</label>
                <select id="year-filter" class="px-3 py-2 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                    <?php for($y = date('Y'); $y >= date('Y') - 2; $y--): ?>
                    <option <?= $y == date('Y') ? 'selected' : '' ?>><?= $y ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <button id="btn-filter"
                    class="inline-flex items-center gap-1.5 px-4 py-2 text-sm bg-blue-600 text-white font-semibold rounded-xl hover:bg-blue-700 transition-colors shadow-sm">
                <i class="fa fa-search"></i> Apply Filter
            </button>
        </div>
    </div>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-gray-100">
    <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
        <div>
            <h3 class="text-sm font-bold text-gray-800">Leave Balances</h3>
            <p class="text-xs text-gray-400 mt-0.5">Used, pending, and available leave by staff and type</p>
        </div>
        <span class="px-2.5 py-1 bg-violet-50 text-violet-600 text-xs font-semibold rounded-lg">
            <i class="fa fa-plane mr-1"></i> Utilisation
        </span>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50">
                    <th class="px-5 py-3 text-left text-[11px] font-bold text-gray-500 uppercase tracking-wide">Staff</th>
                    <th class="px-5 py-3 text-left text-[11px] font-bold text-gray-500 uppercase tracking-wide">Type</th>
                    <th class="px-5 py-3 text-right text-[11px] font-bold text-gray-500 uppercase tracking-wide">Total</th>
                    <th class="px-5 py-3 text-right text-[11px] font-bold text-gray-500 uppercase tracking-wide">Used</th>
                    <th class="px-5 py-3 text-right text-[11px] font-bold text-gray-500 uppercase tracking-wide">Pending</th>
                    <th class="px-5 py-3 text-right text-[11px] font-bold text-gray-500 uppercase tracking-wide">Available</th>
                </tr>
            </thead>
            <tbody id="leave-util-body" class="divide-y divide-gray-50">
                <tr>
                    <td colspan="6" class="py-10 text-center text-gray-400 text-sm">Apply filter to load data</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<script>
function loadReport() {
    $.getJSON(BASE_URL + 'reports/leave_data', {year: $('#year-filter').val()}, function(res) {
        var html = '';
        $.each(res.data || [], function(i, r) {
            var avail = r.total_days - r.used_days - r.pending_days;
            html += '<tr class="hover:bg-gray-50">' +
                '<td class="px-5 py-3 font-semibold text-gray-800">' + CRM.esc(r.staff_name) + '</td>' +
                '<td class="px-5 py-3 text-gray-600">' + CRM.esc(r.leave_type) + '</td>' +
                '<td class="px-5 py-3 text-right text-gray-500">' + r.total_days + '</td>' +
                '<td class="px-5 py-3 text-right font-semibold text-amber-600">' + r.used_days + '</td>' +
                '<td class="px-5 py-3 text-right font-semibold text-cyan-600">' + r.pending_days + '</td>' +
                '<td class="px-5 py-3 text-right font-bold text-emerald-600">' + Math.max(0, avail) + '</td>' +
                '</tr>';
        });
        $('#leave-util-body').html(html || '<tr><td colspan="6" class="py-8 text-center text-gray-400">No data for this year</td></tr>');
    });
}
$('#btn-filter').click(loadReport);
</script>
