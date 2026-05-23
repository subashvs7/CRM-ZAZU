<!-- Page Header -->
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
    <div class="flex items-center gap-3">
        <div class="w-10 h-10 bg-emerald-100 rounded-xl flex items-center justify-center flex-shrink-0">
            <i class="fa fa-filter text-emerald-600 text-lg"></i>
        </div>
        <div>
            <h1 class="text-xl font-bold text-gray-800">Lead Conversion</h1>
            <nav class="text-xs text-gray-400 flex items-center gap-1 mt-0.5">
                <a href="<?= base_url('dashboard') ?>" class="hover:text-blue-600 transition-colors">Home</a>
                <i class="fa fa-angle-right text-[10px]"></i>
                <span class="text-gray-600">Reports — Lead Conversion</span>
            </nav>
        </div>
    </div>
</div>

<?php include(APPPATH.'views/reports/_filter_bar.php'); ?>

<div class="bg-white rounded-2xl shadow-sm border border-gray-100">
    <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
        <div>
            <h3 class="text-sm font-bold text-gray-800">Conversion Funnel</h3>
            <p class="text-xs text-gray-400 mt-0.5">Lead count by pipeline stage</p>
        </div>
        <span class="px-2.5 py-1 bg-emerald-50 text-emerald-600 text-xs font-semibold rounded-lg">
            <i class="fa fa-filter mr-1"></i> Pipeline
        </span>
    </div>
    <div class="p-4">
        <div id="funnel-chart" style="height:360px"></div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
var stageColors = {
    new: '#94a3b8', contacted: '#06b6d4', qualified: '#3b82f6',
    proposal: '#f59e0b', negotiation: '#f97316', won: '#22c55e', lost: '#ef4444'
};
function loadReport() {
    var from = $('#from-date').val(), to = $('#to-date').val(), uid = $('#staff-filter').val() || '';
    $.getJSON(BASE_URL + 'reports/lead_data', {from: from, to: to, user_id: uid}, function(res) {
        var stages = ['new', 'contacted', 'qualified', 'proposal', 'negotiation', 'won', 'lost'];
        var labels = [], counts = [], colors = [], map = {};
        $.each(res.data || [], function(i, r) { map[r.lead_status] = parseInt(r.cnt); });
        $.each(stages, function(i, s) {
            labels.push(s.charAt(0).toUpperCase() + s.slice(1));
            counts.push(map[s] || 0);
            colors.push(stageColors[s]);
        });
        if (window.chart) window.chart.destroy();
        window.chart = new ApexCharts(document.getElementById('funnel-chart'), {
            chart: {type: 'bar', height: 360, toolbar: {show: false}, fontFamily: 'inherit'},
            series: [{name: 'Leads', data: counts}],
            xaxis: {categories: labels, labels: {style: {fontSize: '11px', colors: '#94a3b8'}}},
            yaxis: {labels: {style: {fontSize: '11px', colors: '#94a3b8'}}},
            colors: colors,
            plotOptions: {bar: {borderRadius: 5, columnWidth: '55%', distributed: true}},
            dataLabels: {enabled: true, style: {fontSize: '11px', fontWeight: 'bold'}},
            legend: {show: false},
            grid: {borderColor: '#f1f5f9', strokeDashArray: 3},
            tooltip: {y: {formatter: function(v) { return v + ' leads'; }}}
        });
        window.chart.render();
    });
}
$('#btn-filter').click(loadReport);
</script>
