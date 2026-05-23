<section class="content-header">
    <h1>Face Mismatches</h1>
    <ol class="breadcrumb"><li><a href="<?= base_url('selfie/log') ?>">Selfie</a></li><li class="active">Mismatches</li></ol>
</section>
<section class="content">
<div class="box box-danger">
    <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-exclamation-triangle"></i> Face Verification Failures</h3></div>
    <div class="box-body">
        <table id="mismatch-table" class="table table-bordered table-striped">
            <thead><tr><th>#</th><th>Staff</th><th>Date</th><th>Punch Time</th><th>Confidence</th><th>Actions</th></tr></thead>
            <tbody></tbody>
        </table>
    </div>
</div>
</section>
<script>
var mismatchTable=$('#mismatch-table').DataTable({processing:true,serverSide:true,ajax:{url:BASE_URL+'selfie/mismatches_dt'},columns:[{data:0},{data:1},{data:2},{data:3},{data:4},{data:5,orderable:false}],order:[[2,'desc']]});
$(document).on('click','.btn-override-selfie',function(){
    if(!confirm('Override face verification for this record?')) return;
    $.post(BASE_URL+'selfie/override',{id:$(this).data('id'),[CI3_CSRF_NAME]:CI3_CSRF_HASH},function(res){if(res.status==='success'){CRM.toast('success',res.message);mismatchTable.ajax.reload(null,false);}});
});
</script>
