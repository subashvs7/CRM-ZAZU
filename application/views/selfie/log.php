<section class="content-header">
    <h1>Selfie Log</h1>
    <ol class="breadcrumb"><li><a href="<?= base_url('dashboard') ?>">Home</a></li><li class="active">Selfie Log</li></ol>
</section>
<section class="content">
<div class="box box-primary">
    <div class="box-header with-border">
        <h3 class="box-title"><i class="fa fa-camera"></i> Selfie Verification Log</h3>
        <div class="box-tools">
            <a href="<?= base_url('selfie/mismatches') ?>" class="btn btn-danger btn-sm"><i class="fa fa-exclamation-triangle"></i> Mismatches</a>
            <?php if($is_admin): ?><a href="<?= base_url('selfie/settings') ?>" class="btn btn-default btn-sm"><i class="fa fa-cog"></i> Settings</a><?php endif; ?>
        </div>
    </div>
    <div class="box-body">
        <table id="selfie-table" class="table table-bordered table-striped">
            <thead><tr><th>#</th><th>Staff</th><th>Date</th><th>Punch Time</th><th>Photo</th><th>Verified</th><th>Confidence</th></tr></thead>
            <tbody></tbody>
        </table>
    </div>
</div>
</section>
<script>
$('#selfie-table').DataTable({processing:true,serverSide:true,ajax:{url:BASE_URL+'selfie/log_dt'},columns:[{data:0},{data:1},{data:2},{data:3},{data:4},{data:5},{data:6}],order:[[2,'desc']]});
</script>
