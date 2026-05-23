<!-- Page Header -->
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
    <div class="flex items-center gap-3">
        <div class="w-10 h-10 bg-cyan-100 rounded-xl flex items-center justify-center flex-shrink-0">
            <i class="fa fa-map-signs text-cyan-600 text-lg"></i>
        </div>
        <div>
            <h1 class="text-xl font-bold text-gray-800">Visit Reports</h1>
            <nav class="text-xs text-gray-400 flex items-center gap-1 mt-0.5">
                <a href="<?= base_url('dashboard') ?>" class="hover:text-blue-600 transition-colors">Home</a>
                <i class="fa fa-angle-right text-[10px]"></i>
                <span class="text-gray-600">Reports — Visits</span>
            </nav>
        </div>
    </div>
</div>

<?php include(APPPATH.'views/reports/_filter_bar.php'); ?>

<div class="bg-white rounded-2xl shadow-sm border border-gray-100">
    <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
        <div>
            <h3 class="text-sm font-bold text-gray-800">Visit Summary by Staff</h3>
            <p class="text-xs text-gray-400 mt-0.5">Total visits, unique customers, and average duration</p>
        </div>
        <span class="px-2.5 py-1 bg-cyan-50 text-cyan-600 text-xs font-semibold rounded-lg">
            <i class="fa fa-bar-chart mr-1"></i> Staff
        </span>
    </div>
    <div class="p-4">
        <div id="report-chart" class="mb-6" style="height:300px"></div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50">
                        <th class="px-5 py-3 text-left text-[11px] font-bold text-gray-500 uppercase tracking-wide">Staff</th>
                        <th class="px-5 py-3 text-right text-[11px] font-bold text-gray-500 uppercase tracking-wide">Total Visits</th>
                        <th class="px-5 py-3 text-right text-[11px] font-bold text-gray-500 uppercase tracking-wide">Unique Customers</th>
                        <th class="px-5 py-3 text-right text-[11px] font-bold text-gray-500 uppercase tracking-wide">Avg Duration (min)</th>
                    </tr>
                </thead>
                <tbody id="report-body" class="divide-y divide-gray-50">
                    <tr>
                        <td colspan="4" class="py-10 text-center text-gray-400 text-sm">
                            Apply filter to load data
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
function loadReport() {
    var from = $('#from-date').val(), to = $('#to-date').val(), uid = $('#staff-filter').val() || '';
    $.getJSON(BASE_URL + 'reports/visits_data', {from: from, to: to, user_id: uid}, function(res) {
        var html = '', names = [], visits = [];
        $.each(res.data || [], function(i, r) {
            html += '<tr class="hover:bg-gray-50">' +
                '<td class="px-5 py-3 font-semibold text-gray-800">' + CRM.esc(r.staff_name) + '</td>' +
                '<td class="px-5 py-3 text-right font-bold text-blue-600">' + r.total_visits + '</td>' +
                '<td class="px-5 py-3 text-right text-gray-600">' + r.unique_customers + '</td>' +
                '<td class="px-5 py-3 text-right text-gray-600">' + (r.avg_duration ? parseFloat(r.avg_duration).toFixed(0) : '—') + '</td>' +
                '</tr>';
            names.push(r.staff_name);
            visits.push(parseInt(r.total_visits));
        });
        $('#report-body').html(html || '<tr><td colspan="4" class="py-8 text-center text-gray-400">No data for this period</td></tr>');
        if (window.chart) window.chart.destroy();
        window.chart = new ApexCharts(document.getElementById('report-chart'), {
            chart: {type: 'bar', height: 300, toolbar: {show: false}, fontFamily: 'inherit'},
            series: [{name: 'Visits', data: visits}],
            xaxis: {categories: names, labels: {style: {fontSize: '11px', colors: '#94a3b8'}}},
            yaxis: {labels: {style: {fontSize: '11px', colors: '#94a3b8'}}},
            colors: ['#0891b2'],
            plotOptions: {bar: {borderRadius: 5, columnWidth: '50%'}},
            dataLabels: {enabled: false},
            grid: {borderColor: '#f1f5f9', strokeDashArray: 3},
            tooltip: {y: {formatter: function(v) { return v + ' visits'; }}}
        });
        window.chart.render();
    });
}
$('#btn-filter').click(loadReport);
</script>
