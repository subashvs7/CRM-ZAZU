<section class="content-header">
    <h1>Leave Balances</h1>
    <ol class="breadcrumb"><li><a href="<?= base_url('leave') ?>">Leave</a></li><li class="active">Balances</li></ol>
</section>
<section class="content">
<div class="box box-info">
    <div class="box-header with-border">
        <h3 class="box-title">Leave Balances</h3>
        <div class="box-tools">
            <?php if($is_manager): ?>
            <select id="bal-user" class="form-control input-sm" style="width:180px;display:inline-block">
                <?php foreach($users as $u): ?><option value="<?= $u['id'] ?>"><?= esc_html($u['name']) ?></option><?php endforeach; ?>
            </select>
            <?php endif; ?>
            <select id="bal-year" class="form-control input-sm" style="width:80px;display:inline-block">
                <?php for($y=date('Y');$y>=date('Y')-2;$y--): ?><option <?= $y==date('Y')?'selected':'' ?>><?= $y ?></option><?php endfor; ?>
            </select>
            <button class="btn btn-primary btn-sm" id="btn-load-balances">Load</button>
        </div>
    </div>
    <div class="box-body" id="balances-body"><p class="text-muted text-center">Click Load to view balances</p></div>
</div>
</section>
<script>
$('#btn-load-balances').click(function(){
    var uid=$('#bal-user').val()||'', year=$('#bal-year').val();
    $.getJSON(BASE_URL+'leave/balances_data',{user_id:uid,year:year},function(res){
        var html='<table class="table table-bordered"><thead><tr><th>Type</th><th>Total</th><th>Used</th><th>Pending</th><th>Available</th></tr></thead><tbody>';
        $.each(res.data||[],function(i,r){
            var avail=r.total_days-r.used_days-r.pending_days;
            html+='<tr><td>'+CRM.esc(r.leave_type_name)+'</td><td>'+r.total_days+'</td><td>'+r.used_days+'</td><td>'+r.pending_days+'</td><td><strong>'+avail+'</strong></td></tr>';
        });
        html+='</tbody></table>';
        $('#balances-body').html(html);
    });
});
</script>
