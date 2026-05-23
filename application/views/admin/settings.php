<section class="content-header">
    <h1>Settings <small>Application configuration</small></h1>
    <ol class="breadcrumb"><li><a href="<?= base_url('dashboard') ?>">Home</a></li><li class="active">Settings</li></ol>
</section>
<section class="content">
<div class="box box-primary">
    <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-sliders"></i> App Settings</h3></div>
    <div class="box-body">
        <form id="settings-form">
            <input type="hidden" name="<?= $csrf_name ?>" value="<?= $csrf_hash ?>">
            <div class="row">
                <div class="col-md-6">
                    <h4>General</h4>
                    <div class="form-group"><label>Company Name</label><input type="text" name="company_name" class="form-control" value="<?= esc_html($settings['company_name']??'') ?>"></div>
                    <div class="form-group"><label>Timezone</label><input type="text" name="timezone" class="form-control" value="<?= esc_html($settings['timezone']??'Asia/Kolkata') ?>"></div>
                    <div class="form-group"><label>Currency</label><input type="text" name="currency" class="form-control" value="<?= esc_html($settings['currency']??'INR') ?>"></div>
                    <div class="form-group"><label>Order Prefix</label><input type="text" name="order_prefix" class="form-control" value="<?= esc_html($settings['order_prefix']??'ORD') ?>"></div>
                </div>
                <div class="col-md-6">
                    <h4>Tracking &amp; Attendance</h4>
                    <div class="form-group"><label>GPS Ping Interval (seconds)</label><input type="number" name="gps_ping_interval" class="form-control" value="<?= esc_html($settings['gps_ping_interval']??'30') ?>"></div>
                    <div class="form-group"><label>Face Match Threshold (0–1)</label><input type="number" step="0.01" name="face_match_threshold" class="form-control" value="<?= esc_html($settings['face_match_threshold']??'0.75') ?>"></div>
                    <div class="form-group"><label>Attendance Window (hours)</label><input type="number" name="attendance_window_hours" class="form-control" value="<?= esc_html($settings['attendance_window_hours']??'2') ?>"></div>
                </div>
            </div>
            <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Save Settings</button>
        </form>
    </div>
</div>
</section>
<script>
$('#settings-form').on('submit', function(e) {
    e.preventDefault();
    var $btn = $(this).find('[type=submit]'); CRM.btn_loading($btn);
    $.ajax({url:BASE_URL+'admin/save_settings',method:'POST',data:new FormData(this),processData:false,contentType:false,
        success:function(res){CRM.toast(res.status==='success'?'success':'error',res.message);},
        complete:function(){CRM.btn_reset($btn);}
    });
});
</script>
