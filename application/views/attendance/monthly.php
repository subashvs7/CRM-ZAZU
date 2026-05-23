<section class="content-header">
    <h1>Monthly Attendance</h1>
    <ol class="breadcrumb"><li><a href="<?= base_url('attendance') ?>">Attendance</a></li><li class="active">Monthly</li></ol>
</section>
<section class="content">
<div class="box box-primary">
    <div class="box-header with-border">
        <h3 class="box-title">Monthly View</h3>
        <div class="box-tools">
            <select id="att-year" class="form-control input-sm" style="width:80px;display:inline-block">
                <?php for($y=date('Y');$y>=date('Y')-2;$y--): ?><option <?= $y==date('Y')?'selected':'' ?>><?= $y ?></option><?php endfor; ?>
            </select>
            <select id="att-month" class="form-control input-sm" style="width:100px;display:inline-block">
                <?php for($m=1;$m<=12;$m++): ?><option value="<?= $m ?>" <?= $m==date('m')?'selected':'' ?>><?= date('M',mktime(0,0,0,$m,1)) ?></option><?php endfor; ?>
            </select>
            <button class="btn btn-primary btn-sm" id="btn-load-monthly">Load</button>
        </div>
    </div>
    <div class="box-body" id="monthly-att-body">
        <p class="text-center text-muted">Select year and month, then click Load</p>
    </div>
</div>
</section>
<script>
$('#btn-load-monthly').click(function(){
    var year=$('#att-year').val(), month=$('#att-month').val();
    $.getJSON(BASE_URL+'attendance/monthly_data',{year:year,month:month}, function(res) {
        var html='<table class="table table-bordered table-condensed"><thead><tr><th>Date</th><th>In</th><th>Out</th><th>Status</th><th>Hours</th></tr></thead><tbody>';
        $.each(res.data||[], function(i,r) {
            html+='<tr><td>'+r.date+'</td><td>'+(r.punch_in_at?r.punch_in_at.substr(11,5):'-')+'</td><td>'+(r.punch_out_at?r.punch_out_at.substr(11,5):'-')+'</td><td>'+CRM.att_badge(r.attendance_status)+'</td><td>'+r.working_hours+'h</td></tr>';
        });
        if(!res.data||!res.data.length) html+='<tr><td colspan="5" class="text-center text-muted">No records</td></tr>';
        html+='</tbody></table>';
        $('#monthly-att-body').html(html);
    });
});
</script>
