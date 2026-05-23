<section class="content-header">
    <h1>GPS Ping Status</h1>
    <ol class="breadcrumb"><li><a href="<?= base_url('tracking/live') ?>">Tracking</a></li><li class="active">Ping Status</li></ol>
</section>
<section class="content">
<div class="box box-primary">
    <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-satellite"></i> Recent GPS Pings</h3></div>
    <div class="box-body">
        <table id="ping-table" class="table table-bordered table-striped">
            <thead><tr><th>#</th><th>Staff</th><th>Lat, Lng</th><th>Accuracy</th><th>Speed</th><th>Battery</th><th>Time</th></tr></thead>
            <tbody></tbody>
        </table>
    </div>
</div>
</section>
<script>
$('#ping-table').DataTable({processing:true,serverSide:true,ajax:{url:BASE_URL+'tracking/ping_dt'},columns:[{data:0},{data:1},{data:2},{data:3},{data:4},{data:5},{data:6}],order:[[0,'desc']]});
</script>
