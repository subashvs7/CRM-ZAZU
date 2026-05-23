<section class="content-header">
    <h1>Attendance Corrections</h1>
    <ol class="breadcrumb"><li><a href="<?= base_url('attendance') ?>">Attendance</a></li><li class="active">Corrections</li></ol>
</section>
<section class="content">
<div class="box box-warning">
    <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-edit"></i> Pending Corrections</h3></div>
    <div class="box-body">
        <table id="corr-table" class="table table-bordered table-striped">
            <thead><tr><th>#</th><th>Staff</th><th>Date</th><th>In</th><th>Out</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody></tbody>
        </table>
    </div>
</div>
</section>
<script>
var corrTable=$('#corr-table').DataTable({processing:true,serverSide:true,ajax:{url:BASE_URL+'attendance/corrections_dt'},columns:[{data:0},{data:1},{data:2},{data:3},{data:4},{data:5},{data:6,orderable:false}],order:[[2,'desc']]});
$(document).on('click','.btn-approve-corr',function(){
    var id=$(this).data('id');
    $.post(BASE_URL+'attendance/approve_correction',{id:id,[CI3_CSRF_NAME]:CI3_CSRF_HASH},function(res){if(res.status==='success'){CRM.toast('success',res.message);corrTable.ajax.reload(null,false);}});
});
</script>
