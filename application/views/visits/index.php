<section class="content-header">
    <h1>Visit Plans <small>Schedule field visits</small></h1>
    <ol class="breadcrumb"><li><a href="<?= base_url('dashboard') ?>">Home</a></li><li class="active">Visits</li></ol>
</section>
<section class="content">
<?php $this->load->view('partials/_status_tabs', get_defined_vars()); ?>
<div class="box box-primary">
    <div class="box-header with-border">
        <h3 class="box-title"><i class="fa fa-map-signs"></i> Visit Plans</h3>
        <div class="box-tools">
            <a href="<?= base_url('visits/history') ?>" class="btn btn-info btn-sm"><i class="fa fa-history"></i> History</a>
            <a href="<?= base_url('visits/checkin') ?>" class="btn btn-success btn-sm"><i class="fa fa-sign-in"></i> Check In</a>
            <button class="btn btn-primary btn-sm" id="btn-plan-visit"><i class="fa fa-plus"></i> Plan Visit</button>
        </div>
    </div>
    <div class="box-body">
        <table id="visits-table" class="table table-bordered table-striped">
            <thead><tr><th>#</th><th>Date</th><th>Time</th><th>Customer</th><th>Staff</th><th>Status</th><th>Purpose</th><th>Record</th><th>Actions</th></tr></thead>
            <tbody></tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="visit-modal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header"><button type="button" class="close" data-dismiss="modal">&times;</button><h4 class="modal-title">Plan Visit</h4></div>
            <div class="modal-body">
                <form id="visit-form" method="POST" action="<?= base_url('visits/save') ?>">
                    <input type="hidden" name="<?= $csrf_name ?>" value="<?= $csrf_hash ?>">
                    <input type="hidden" name="id" id="visit-id" value="0">
                    <div class="form-group"><label>Customer *</label>
                        <select name="customer_id" class="form-control select2" required>
                            <option value="">-- Select --</option>
                            <?php foreach($customers as $c): ?><option value="<?= $c['id'] ?>"><?= esc_html($c['name']) ?></option><?php endforeach; ?>
                        </select>
                    </div>
                    <?php if($is_manager): ?>
                    <div class="form-group"><label>Assign To</label>
                        <select name="user_id" class="form-control select2">
                            <option value="">-- Self --</option>
                            <?php foreach($staff as $s): ?><option value="<?= $s['id'] ?>"><?= esc_html($s['name']) ?></option><?php endforeach; ?>
                        </select>
                    </div>
                    <?php endif; ?>
                    <div class="form-group"><label>Date *</label><input type="text" name="planned_date" class="form-control datepicker" required></div>
                    <div class="form-group"><label>Time</label><input type="time" name="planned_time" class="form-control"></div>
                    <div class="form-group"><label>Purpose</label><textarea name="purpose" class="form-control" rows="3"></textarea></div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-default" data-dismiss="modal">Cancel</button>
                <button class="btn btn-primary" id="btn-save-visit"><i class="fa fa-save"></i> Save</button>
            </div>
        </div>
    </div>
</div>
</section>
