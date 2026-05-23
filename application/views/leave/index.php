<section class="content-header">
    <h1>Leave <small>My leave requests</small></h1>
    <ol class="breadcrumb"><li><a href="<?= base_url('dashboard') ?>">Home</a></li><li class="active">Leave</li></ol>
</section>
<section class="content">
<!-- Balance Cards -->
<div class="row">
<?php foreach ($balances as $b): ?>
<div class="col-md-3 col-xs-6">
    <div class="box box-info">
        <div class="box-body text-center">
            <h4><?= esc_html($b['leave_type_name']) ?></h4>
            <?php $avail = $b['total_days'] - $b['used_days'] - $b['pending_days']; ?>
            <h3 class="text-primary"><?= $avail ?></h3>
            <p class="text-muted">Available / <?= $b['total_days'] ?></p>
            <div class="progress xs">
                <div class="progress-bar" style="width:<?= $b['total_days'] > 0 ? round($b['used_days']/$b['total_days']*100) : 0 ?>%"></div>
            </div>
        </div>
    </div>
</div>
<?php endforeach; ?>
</div>

<?php $this->load->view('partials/_status_tabs', get_defined_vars()); ?>
<div class="box box-primary">
    <div class="box-header with-border">
        <h3 class="box-title"><i class="fa fa-plane"></i> My Leave Requests</h3>
        <div class="box-tools">
            <?php if($is_manager): ?><a href="<?= base_url('leave/approval') ?>" class="btn btn-warning btn-sm"><i class="fa fa-clock-o"></i> Approval Queue</a><?php endif; ?>
            <a href="<?= base_url('leave/apply') ?>" class="btn btn-success btn-sm"><i class="fa fa-plus"></i> Apply Leave</a>
        </div>
    </div>
    <div class="box-body">
        <table id="leave-table" class="table table-bordered table-striped">
            <thead><tr><th>#</th><th>Staff</th><th>Type</th><th>From</th><th>To</th><th>Days</th><th>Status</th><th>Applied</th><th>Actions</th></tr></thead>
            <tbody></tbody>
        </table>
    </div>
</div>
</section>
<script>
var leaveTable=$('#leave-table').DataTable({processing:true,serverSide:true,ajax:{url:BASE_URL+'leave/datatable',data:function(d){d.status_filter=window.currentStatusFilter||'';}},columns:[{data:0},{data:1},{data:2},{data:3},{data:4},{data:5},{data:6},{data:7},{data:8,orderable:false}],order:[[0,'desc']]});
$('#status-tabs a').on('click',function(e){e.preventDefault();$('#status-tabs li').removeClass('active');$(this).parent().addClass('active');window.currentStatusFilter=$(this).data('status');$('#deleted-banner').toggle(window.currentStatusFilter==='deleted');leaveTable.ajax.reload();});
$(document).on('click','.btn-cancel-leave',function(){
    if(!confirm('Cancel this leave request?')) return;
    $.post(BASE_URL+'leave/cancel',{id:$(this).data('id'),[CI3_CSRF_NAME]:CI3_CSRF_HASH},function(res){if(res.status==='success'){CRM.toast('success',res.message);leaveTable.ajax.reload(null,false);}});
});
</script>
