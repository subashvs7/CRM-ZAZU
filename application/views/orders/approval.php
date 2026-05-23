<section class="content-header">
    <h1>Pending Order Approval <small>Review and approve orders</small></h1>
    <ol class="breadcrumb"><li><a href="<?= base_url('dashboard') ?>">Home</a></li><li><a href="<?= base_url('orders') ?>">Orders</a></li><li class="active">Approval</li></ol>
</section>
<section class="content">
<div class="box box-warning">
    <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-clock-o"></i> Orders Pending Approval</h3></div>
    <div class="box-body">
        <table id="approval-table" class="table table-bordered table-striped">
            <thead><tr><th>#</th><th>Order #</th><th>Customer</th><th>Amount</th><th>Created By</th><th>Date</th><th>Actions</th></tr></thead>
            <tbody></tbody>
        </table>
    </div>
</div>
</section>
<script>
var approvalTable = $('#approval-table').DataTable({
    processing:true, serverSide:true,
    ajax:{url:BASE_URL+'orders/approval_dt'},
    columns:[{data:0},{data:1},{data:2},{data:3},{data:4},{data:5},{data:6,orderable:false}],
    order:[[0,'asc']]
});
$(document).on('click','.btn-approve-order',function(){
    var id=$(this).data('id');
    if(!confirm('Approve this order?')) return;
    $.post(BASE_URL+'orders/approve',{id:id,[CI3_CSRF_NAME]:CI3_CSRF_HASH},function(res){
        if(res.status==='success'){CRM.toast('success',res.message);approvalTable.ajax.reload(null,false);}
    });
});
$(document).on('click','.btn-reject-order',function(){
    var id=$(this).data('id');
    var reason=prompt('Rejection reason:');
    if(!reason) return;
    $.post(BASE_URL+'orders/reject',{id:id,reason:reason,[CI3_CSRF_NAME]:CI3_CSRF_HASH},function(res){
        if(res.status==='success'){CRM.toast('success',res.message);approvalTable.ajax.reload(null,false);}
    });
});
</script>
