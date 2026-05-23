<section class="content-header">
    <h1>Geo Violations & Alerts</h1>
    <ol class="breadcrumb"><li><a href="<?= base_url('geofence') ?>">Geofence</a></li><li class="active">Violations</li></ol>
</section>
<section class="content">
<div class="box box-danger">
    <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-exclamation-triangle"></i> Geo Alerts</h3></div>
    <div class="box-body">
        <table id="violations-table" class="table table-bordered table-striped">
            <thead><tr><th>#</th><th>Zone</th><th>Staff</th><th>Type</th><th>Triggered</th><th>Resolved</th><th>Actions</th></tr></thead>
            <tbody></tbody>
        </table>
    </div>
</div>
</section>
<script>
var violTable=$('#violations-table').DataTable({processing:true,serverSide:true,ajax:{url:BASE_URL+'geofence/violations_dt'},columns:[{data:0},{data:1},{data:2},{data:3},{data:4},{data:5},{data:6,orderable:false}],order:[[0,'desc']]});
$(document).on('click','.btn-resolve-alert',function(){
    $.post(BASE_URL+'geofence/resolve_alert',{id:$(this).data('id'),[CI3_CSRF_NAME]:CI3_CSRF_HASH},function(res){if(res.status==='success'){CRM.toast('success',res.message);violTable.ajax.reload(null,false);}});
});
</script>
