<section class="content-header">
    <h1>Planned vs Actual <small>Visit compliance</small></h1>
    <ol class="breadcrumb"><li><a href="<?= base_url('visits') ?>">Visits</a></li><li class="active">Planned vs Actual</li></ol>
</section>
<section class="content">
<div class="box box-primary">
    <div class="box-header with-border">
        <h3 class="box-title">Compliance Report</h3>
        <div class="box-tools">
            <input type="date" id="from-date" class="form-control input-sm" style="width:120px;display:inline-block" value="<?= date('Y-m-01') ?>">
            <input type="date" id="to-date"   class="form-control input-sm" style="width:120px;display:inline-block" value="<?= date('Y-m-t') ?>">
            <button class="btn btn-primary btn-sm" id="btn-filter">Filter</button>
        </div>
    </div>
    <div class="box-body" id="pva-chart-box">
        <div id="pva-chart" style="height:350px"></div>
    </div>
</div>
</section>
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
function loadPVA() {
    var from=$('#from-date').val(), to=$('#to-date').val();
    $.getJSON(BASE_URL+'visits/planned_actual',{from:from,to:to}, function(res) {
        var d = res.data;
        var dates=[], planned=[], actual=[];
        var planMap={}, actualMap={};
        $.each(d.plans||[], function(i,r) { planMap[r.planned_date]=(planMap[r.planned_date]||0)+parseInt(r.planned); });
        $.each(d.actual||[], function(i,r) { actualMap[r.visit_date]=(actualMap[r.visit_date]||0)+parseInt(r.actual); });
        var allDates=Object.keys(Object.assign({},planMap,actualMap)).sort();
        $.each(allDates, function(i,d) { dates.push(d); planned.push(planMap[d]||0); actual.push(actualMap[d]||0); });
        var chart=new ApexCharts(document.getElementById('pva-chart'), {
            chart:{type:'bar',height:350},series:[{name:'Planned',data:planned},{name:'Actual',data:actual}],
            xaxis:{categories:dates},colors:['#3c8dbc','#00a65a'],legend:{position:'top'}
        });
        chart.render();
    });
}
$('#btn-filter').click(loadPVA);
loadPVA();
</script>
