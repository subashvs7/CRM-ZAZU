<section class="content-header"><h1>Staff Sales Performance</h1><ol class="breadcrumb"><li>Reports</li><li class="active">Staff Sales</li></ol></section>
<section class="content">
<?php include(APPPATH.'views/reports/_filter_bar.php'); ?>
<div class="box box-primary">
    <div class="box-header with-border"><h3 class="box-title">Sales Leaderboard</h3></div>
    <div class="box-body">
        <table class="table table-bordered"><thead><tr><th>#</th><th>Staff</th><th>Visits</th><th>Leads</th><th>Orders</th><th>Revenue</th></tr></thead>
        <tbody id="staff-sales-body"><tr><td colspan="6" class="text-center text-muted">Apply filter</td></tr></tbody></table>
    </div>
</div>
</section>
<script>
function loadReport(){var from=$('#from-date').val(),to=$('#to-date').val();$.getJSON(BASE_URL+'reports/staff_data',{from:from,to:to},function(res){var html='';$.each(res.data||[],function(i,r){html+='<tr><td>'+(i+1)+'</td><td>'+CRM.esc(r.name)+'</td><td>'+r.visits+'</td><td>'+r.leads+'</td><td>'+r.orders+'</td><td>'+CRM.esc('₹'+parseFloat((r.revenue||0)/100).toLocaleString('en-IN'))+'</td></tr>';});$('#staff-sales-body').html(html||'<tr><td colspan="6" class="text-center">No data</td></tr>');});}
$('#btn-filter').click(loadReport);
</script>
