<section class="content-header">
    <h1>Auto Check-ins</h1>
    <ol class="breadcrumb"><li><a href="<?= base_url('geofence') ?>">Geofence</a></li><li class="active">Auto Check-ins</li></ol>
</section>
<section class="content">
<div class="box box-info">
    <div class="box-header with-border"><h3 class="box-title">Auto Check-in Log</h3></div>
    <div class="box-body">
        <table id="checkins-table" class="table table-bordered table-striped">
            <thead><tr><th>#</th><th>Staff</th><th>Customer</th><th>Zone</th><th>Check-in Time</th></tr></thead>
            <tbody></tbody>
        </table>
    </div>
</div>
</section>
<script>
$('#checkins-table').DataTable({processing:true,serverSide:true,ajax:{url:BASE_URL+'geofence/checkins_dt'},columns:[{data:0},{data:1},{data:2},{data:3},{data:4}],order:[[0,'desc']]});
</script>
