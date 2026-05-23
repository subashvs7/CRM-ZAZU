<section class="content-header">
    <h1>Apply for Leave</h1>
    <ol class="breadcrumb"><li><a href="<?= base_url('leave') ?>">Leave</a></li><li class="active">Apply</li></ol>
</section>
<section class="content">
<div class="row"><div class="col-md-6 col-md-offset-3">
<div class="box box-primary">
    <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-plane"></i> Leave Application</h3></div>
    <div class="box-body">
        <form id="leave-form" method="POST" action="<?= base_url('leave/save') ?>">
            <input type="hidden" name="<?= $csrf_name ?>" value="<?= $csrf_hash ?>">
            <div class="form-group"><label>Leave Type *</label>
                <select name="leave_type_id" class="form-control" required>
                    <option value="">-- Select Type --</option>
                    <?php foreach($types as $t): ?><option value="<?= $t['id'] ?>"><?= esc_html($t['name']) ?> (<?= $t['days_allowed_per_year'] ?> days/yr)</option><?php endforeach; ?>
                </select>
            </div>
            <div class="form-group"><label>From Date *</label><input type="text" name="from_date" class="form-control datepicker" required></div>
            <div class="form-group"><label>To Date *</label><input type="text" name="to_date" class="form-control datepicker" required></div>
            <div class="form-group"><label>Reason *</label><textarea name="reason" class="form-control" rows="4" required></textarea></div>
            <button type="submit" class="btn btn-primary btn-block"><i class="fa fa-paper-plane"></i> Submit Application</button>
        </form>
    </div>
</div>
</div></div>
</section>
<script>
CRM.init_plugins();
$('#leave-form').on('submit',function(e){
    e.preventDefault();
    CRM.submit_form($(this), function(){ window.location=BASE_URL+'leave'; });
});
</script>
