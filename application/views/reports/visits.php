<section class="content-header">
    <h1>Visit Reports</h1>
    <ol class="breadcrumb"><li><a href="<?= base_url('reports/visits') ?>">Reports</a></li><li class="active">Visits</li></ol>
</section>
<section class="content">
<?php include(APPPATH.'views/reports/_filter_bar.php'); ?>
<div class="box box-primary">
    <div class="box-header with-border"><h3 class="box-title">Visit Summary by Staff</h3></div>
    <div class="box-body">
        <div id="report-chart" style="height:300px;margin-bottom:20px"></div>
        <table class="table table-bordered" id="report-table">
            <thead><tr><th>Staff</th><th>Total Visits</th><th>Unique Customers</th><th>Avg Duration (min)</th></tr></thead>
            <tbody id="report-body"><tr><td colspan="4" class="text-center text-muted">Apply filter to load data</td></tr></tbody>
        </table>
    </div>
</div>
</section>
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
function loadReport() {
    var from=$('#from-date').val(), to=$('#to-date').val(), uid=$('#staff-filter').val()||'';
    $.getJSON(BASE_URL+'reports/visits_data',{from:from,to:to,user_id:uid}, function(res) {
        var html='', names=[], visits=[];
        $.each(res.data||[], function(i,r) {
            html+='<tr><td>'+CRM.esc(r.staff_name)+'</td><td>'+r.total_visits+'</td><td>'+r.unique_customers+'</td><td>'+(r.avg_duration?parseFloat(r.avg_duration).toFixed(0):'-')+'</td></tr>';
            names.push(r.staff_name); visits.push(parseInt(r.total_visits));
        });
        $('#report-body').html(html||'<tr><td colspan="4" class="text-center text-muted">No data</td></tr>');
        if(window.chart) window.chart.destroy();
        window.chart = new ApexCharts(document.getElementById('report-chart'), {
            chart:{type:'bar',height:300},series:[{name:'Visits',data:visits}],
            xaxis:{categories:names},colors:['#3c8dbc']
        });
        window.chart.render();
    });
}
$('#btn-filter').click(loadReport);
</script>
