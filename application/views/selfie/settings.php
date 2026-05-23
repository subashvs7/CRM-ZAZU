<section class="content-header">
    <h1>Selfie Settings</h1>
    <ol class="breadcrumb"><li><a href="<?= base_url('selfie/log') ?>">Selfie</a></li><li class="active">Settings</li></ol>
</section>
<section class="content">
<div class="box box-primary">
    <div class="box-body">
        <form id="selfie-settings-form" method="POST" action="<?= base_url('selfie/save_settings') ?>">
            <input type="hidden" name="<?= $csrf_name ?>" value="<?= $csrf_hash ?>">
            <div class="form-group"><label>Face Match Threshold (0.0 - 1.0)</label>
                <input type="number" step="0.01" min="0" max="1" name="face_match_threshold" class="form-control" value="<?= esc_html($settings['face_match_threshold']??'0.75') ?>" style="width:200px">
                <p class="help-block">Values above this threshold are considered a match. Recommended: 0.75</p>
            </div>
            <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Save</button>
        </form>
    </div>
</div>
</section>
<script>
$('#selfie-settings-form').on('submit',function(e){
    e.preventDefault();
    CRM.submit_form($(this));
});
</script>
