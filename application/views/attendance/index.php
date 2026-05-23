<section class="content-header">
    <h1>Attendance <small>Daily attendance records</small></h1>
    <ol class="breadcrumb"><li><a href="<?= base_url('dashboard') ?>">Home</a></li><li class="active">Attendance</li></ol>
</section>
<section class="content">
<div class="row" style="margin-bottom:15px">
    <div class="col-xs-12">
        <a href="<?= base_url('attendance/punch') ?>" class="btn btn-success"><i class="fa fa-sign-in"></i> Punch In/Out</a>
        <a href="<?= base_url('attendance/monthly') ?>" class="btn btn-info"><i class="fa fa-calendar"></i> Monthly View</a>
        <?php if($is_manager): ?><a href="<?= base_url('attendance/corrections') ?>" class="btn btn-warning"><i class="fa fa-edit"></i> Corrections</a><?php endif; ?>
    </div>
</div>
<?php $this->load->view('partials/_status_tabs', get_defined_vars()); ?>
<div class="box box-primary">
    <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-clock-o"></i> Attendance Records</h3></div>
    <div class="box-body">
        <table id="att-table" class="table table-bordered table-striped">
            <thead><tr><th>#</th><th>Date</th><th>Staff</th><th>In</th><th>Out</th><th>Status</th><th>Hours</th><th>Face</th><th>Regularized</th><th>Actions</th></tr></thead>
            <tbody></tbody>
        </table>
    </div>
</div>
</section>
<!-- DataTable init is handled by crm.attendance.js (loaded via page_js in footer)
     to avoid double-init. Global status-tab handler in crm.core.js uses window.mainTable. -->
