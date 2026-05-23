<section class="content-header">
    <h1>Shift Assignment</h1>
    <ol class="breadcrumb"><li><a href="<?= base_url('shifts') ?>">Shifts</a></li><li class="active">Assignment</li></ol>
</section>
<section class="content">
<div class="row">
    <div class="col-md-5">
        <div class="box box-primary">
            <div class="box-header with-border"><h3 class="box-title">Assign Shift</h3></div>
            <div class="box-body">
                <form id="assign-form" method="POST" action="<?= base_url('shifts/save_assignment') ?>">
                    <input type="hidden" name="<?= $csrf_name ?>" value="<?= $csrf_hash ?>">
                    <div class="form-group"><label>Staff *</label>
                        <select name="user_id" class="form-control select2" required>
                            <option value="">-- Select Staff --</option>
                            <?php foreach($staff as $s): ?><option value="<?= $s['id'] ?>"><?= esc_html($s['name']) ?></option><?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group"><label>Shift *</label>
                        <select name="shift_id" class="form-control select2" required>
                            <option value="">-- Select Shift --</option>
                            <?php foreach($shifts as $sh): ?><option value="<?= $sh['id'] ?>"><?= esc_html($sh['name']) ?> (<?= $sh['start_time'] ?>–<?= $sh['end_time'] ?>)</option><?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group"><label>Effective From *</label><input type="text" name="effective_from" class="form-control datepicker" required></div>
                    <button type="submit" class="btn btn-primary btn-block">Assign Shift</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-7">
        <div class="box box-info">
            <div class="box-header with-border"><h3 class="box-title">Current Assignments</h3></div>
            <div class="box-body" id="assign-table-box">
                <p class="text-center text-muted"><i class="fa fa-spinner fa-spin"></i> Loading...</p>
            </div>
        </div>
    </div>
</div>
</section>
<script>
CRM.init_plugins();
function loadAssignments() {
    $.getJSON(BASE_URL+'shifts/calendar_data', function(res) {
        var html='<table class="table table-condensed table-bordered"><thead><tr><th>Staff / Shift</th><th>From</th><th>To</th></tr></thead><tbody>';
        $.each(res||[], function(i,r){html+='<tr><td>'+CRM.esc(r.title||'')+'</td><td>'+CRM.esc(r.start||'')+'</td><td>'+(r.end||'<em>Ongoing</em>')+'</td></tr>';});
        html+='</tbody></table>';
        $('#assign-table-box').html(html);
    });
}
loadAssignments();
$('#assign-form').on('submit', function(e) {
    e.preventDefault();
    CRM.submit_form($(this), function(){ loadAssignments(); $('#assign-form')[0].reset(); CRM.init_plugins(); });
});
</script>
