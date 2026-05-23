<!-- Page Header -->
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
    <div class="flex items-center gap-3">
        <div class="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center flex-shrink-0">
            <i class="fa fa-bar-chart text-blue-600 text-lg"></i>
        </div>
        <div>
            <h1 class="text-xl font-bold text-gray-800">Planned vs Actual</h1>
            <nav class="text-xs text-gray-400 flex items-center gap-1 mt-0.5">
                <a href="<?= base_url('dashboard') ?>" class="hover:text-blue-600 transition-colors">Home</a>
                <i class="fa fa-angle-right text-[10px]"></i>
                <a href="<?= base_url('visits') ?>" class="hover:text-blue-600 transition-colors">Visits</a>
                <i class="fa fa-angle-right text-[10px]"></i>
                <span class="text-gray-600">Compliance</span>
            </nav>
        </div>
    </div>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-gray-100">
    <div class="px-6 py-4 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h3 class="text-sm font-bold text-gray-800">Visit Compliance Report</h3>
            <p class="text-xs text-gray-400 mt-0.5">Compare planned vs actual visits by date</p>
        </div>
        <div class="flex items-center gap-2">
            <input type="text" id="from-date"
                   class="px-3 py-2 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white datepicker"
                   value="<?= date('Y-m-01') ?>" readonly>
            <span class="text-gray-400 text-xs font-medium">to</span>
            <input type="text" id="to-date"
                   class="px-3 py-2 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white datepicker"
                   value="<?= date('Y-m-t') ?>" readonly>
            <button id="btn-filter"
                    class="inline-flex items-center gap-1.5 px-4 py-2 text-sm bg-blue-600 text-white font-semibold rounded-xl hover:bg-blue-700 transition-colors">
                <i class="fa fa-search"></i> Apply
            </button>
        </div>
    </div>

    <!-- Legend -->
    <div class="px-6 pt-4 flex items-center gap-4">
        <div class="flex items-center gap-1.5">
            <div class="w-3 h-3 rounded-sm bg-blue-500"></div>
            <span class="text-xs font-medium text-gray-500">Planned</span>
        </div>
        <div class="flex items-center gap-1.5">
            <div class="w-3 h-3 rounded-sm bg-emerald-500"></div>
            <span class="text-xs font-medium text-gray-500">Actual</span>
        </div>
    </div>

    <div class="p-4">
        <div id="pva-chart" style="height:360px"></div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
var pvaChart;
function loadPVA() {
    var from = $('#from-date').val(), to = $('#to-date').val();
    $.getJSON(BASE_URL + 'visits/planned_actual', {from: from, to: to}, function(res) {
        var d = res.data;
        var planMap = {}, actualMap = {};
        $.each(d.plans || [], function(i, r) { planMap[r.planned_date] = (planMap[r.planned_date] || 0) + parseInt(r.planned); });
        $.each(d.actual || [], function(i, r) { actualMap[r.visit_date] = (actualMap[r.visit_date] || 0) + parseInt(r.actual); });
        var allDates = Object.keys(Object.assign({}, planMap, actualMap)).sort();
        var dates = [], planned = [], actual = [];
        $.each(allDates, function(i, dt) { dates.push(dt); planned.push(planMap[dt] || 0); actual.push(actualMap[dt] || 0); });
        if (pvaChart) pvaChart.destroy();
        pvaChart = new ApexCharts(document.getElementById('pva-chart'), {
            chart: {type: 'bar', height: 360, toolbar: {show: false}, fontFamily: 'inherit'},
            series: [
                {name: 'Planned', data: planned},
                {name: 'Actual', data: actual}
            ],
            xaxis: {categories: dates, labels: {style: {fontSize: '11px', colors: '#94a3b8'}}},
            yaxis: {labels: {style: {fontSize: '11px', colors: '#94a3b8'}}},
            colors: ['#3b82f6', '#22c55e'],
            plotOptions: {bar: {borderRadius: 4, columnWidth: '60%'}},
            dataLabels: {enabled: false},
            grid: {borderColor: '#f1f5f9', strokeDashArray: 3},
            legend: {show: false},
            tooltip: {y: {formatter: function(v) { return v + ' visits'; }}}
        });
        pvaChart.render();
    });
}
$('#btn-filter').click(loadPVA);
loadPVA();
</script>
