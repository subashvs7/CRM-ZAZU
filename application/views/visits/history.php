<section class="content-header">
    <h1>Visit History <small>Check-in/out log</small></h1>
    <ol class="breadcrumb"><li><a href="<?= base_url('visits') ?>">Visits</a></li><li class="active">History</li></ol>
</section>
<section class="content">
<div class="box box-primary">
    <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-history"></i> Visit Log</h3></div>
    <div class="box-body">
        <table id="history-table" class="table table-bordered table-striped">
            <thead><tr><th>#</th><th>Check In</th><th>Check Out</th><th>Customer</th><th>Staff</th><th>Distance</th><th>Auto</th><th>Status</th></tr></thead>
            <tbody></tbody>
        </table>
    </div>
</div>
</section>
<script>
$('#history-table').DataTable({
    processing:true, serverSide:true,
    ajax:{url:BASE_URL+'visits/datatable', data:function(d){d.status_filter='';}},
    columns:[{data:0},{data:1},{data:2},{data:3},{data:4},{data:5},{data:6},{data:7}],
    order:[[0,'desc']]
});
</script>
