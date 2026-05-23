<section class="content-header">
    <h1>Lead Conversion</h1>
    <ol class="breadcrumb"><li>Reports</li><li class="active">Lead Conversion</li></ol>
</section>
<section class="content">
<?php include(APPPATH.'views/reports/_filter_bar.php'); ?>
<div class="box box-success">
    <div class="box-header with-border"><h3 class="box-title">Conversion Funnel</h3></div>
    <div class="box-body">
        <div id="funnel-chart" style="height:350px"></div>
    </div>
</div>
</section>
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
function loadReport() {
    var from=$('#from-date').val(), to=$('#to-date').val(), uid=$('#staff-filter').val()||'';
    $.getJSON(BASE_URL+'reports/lead_data',{from:from,to:to,user_id:uid}, function(res) {
        var stages=['new','contacted','qualified','proposal','negotiation','won','lost'];
        var labels=[], counts=[];
        var map={};
        $.each(res.data||[],function(i,r){map[r.lead_status]=parseInt(r.cnt);});
        $.each(stages,function(i,s){labels.push(s);counts.push(map[s]||0);});
        if(window.chart) window.chart.destroy();
        window.chart=new ApexCharts(document.getElementById('funnel-chart'),{chart:{type:'bar',height:350},series:[{name:'Leads',data:counts}],xaxis:{categories:labels},colors:['#3c8dbc','#00a65a','#f39c12','#e67e22','#e74c3c','#27ae60','#95a5a6']});
        window.chart.render();
    });
}
$('#btn-filter').click(loadReport);
</script>
