<section class="content-header">
    <h1>Order Reports</h1>
    <ol class="breadcrumb"><li>Reports</li><li class="active">Orders</li></ol>
</section>
<section class="content">
<?php include(APPPATH.'views/reports/_filter_bar.php'); ?>
<div class="box box-warning">
    <div class="box-header with-border"><h3 class="box-title">Order Revenue by Staff</h3></div>
    <div class="box-body">
        <table class="table table-bordered" id="orders-report-table">
            <thead><tr><th>Staff</th><th>Orders</th><th>Revenue</th><th>Status</th></tr></thead>
            <tbody id="orders-report-body"><tr><td colspan="4" class="text-center text-muted">Apply filter</td></tr></tbody>
        </table>
    </div>
</div>
</section>
<script>
function loadReport(){
    var from=$('#from-date').val(),to=$('#to-date').val(),uid=$('#staff-filter').val()||'';
    $.getJSON(BASE_URL+'reports/orders_data',{from:from,to:to,user_id:uid},function(res){
        var html='';
        $.each(res.data||[],function(i,r){html+='<tr><td>'+CRM.esc(r.staff_name)+'</td><td>'+r.total_orders+'</td><td>'+CRM.esc(CRM.format_inr ? CRM.format_inr(r.total_revenue||0) : r.total_revenue)+'</td><td>'+CRM.esc(r.order_status)+'</td></tr>';});
        $('#orders-report-body').html(html||'<tr><td colspan="4" class="text-center text-muted">No data</td></tr>');
    });
}
$('#btn-filter').click(loadReport);
</script>
