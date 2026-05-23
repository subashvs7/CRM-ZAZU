<section class="content-header">
    <h1>Leave Approval</h1>
    <ol class="breadcrumb"><li><a href="<?= base_url('leave') ?>">Leave</a></li><li class="active">Approval</li></ol>
</section>
<section class="content">
<div class="box box-warning">
    <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-clock-o"></i> Pending Leave Requests</h3></div>
    <div class="box-body">
        <table id="approval-table" class="table table-bordered table-striped">
            <thead><tr><th>#</th><th>Staff</th><th>Type</th><th>From</th><th>To</th><th>Days</th><th>Reason</th><th>Applied</th><th>Actions</th></tr></thead>
            <tbody></tbody>
        </table>
    </div>
</div>
</section>
<script>
var approvalTable=$('#approval-table').DataTable({processing:true,serverSide:true,ajax:{url:BASE_URL+'leave/approval_dt'},columns:[{data:0},{data:1},{data:2},{data:3},{data:4},{data:5},{data:6},{data:7},{data:8,orderable:false}],order:[[0,'asc']]});
$(document).on('click','.btn-approve-leave',function(){
    if(!confirm('Approve?')) return;
    $.post(BASE_URL+'leave/approve',{id:$(this).data('id'),[CI3_CSRF_NAME]:CI3_CSRF_HASH},function(res){if(res.status==='success'){CRM.toast('success',res.message);approvalTable.ajax.reload(null,false);}});
});
$(document).on('click','.btn-reject-leave',function(){
    var notes=prompt('Rejection reason:');
    if(!notes) return;
    $.post(BASE_URL+'leave/reject',{id:$(this).data('id'),notes:notes,[CI3_CSRF_NAME]:CI3_CSRF_HASH},function(res){if(res.status==='success'){CRM.toast('success',res.message);approvalTable.ajax.reload(null,false);}});
});
</script>
