<section class="content-header">
    <h1>Notifications</h1>
    <ol class="breadcrumb"><li><a href="<?= base_url('dashboard') ?>">Home</a></li><li class="active">Notifications</li></ol>
</section>
<section class="content">
<div class="box box-primary">
    <div class="box-header with-border">
        <h3 class="box-title"><i class="fa fa-bell"></i> Notifications</h3>
        <button class="btn btn-default btn-sm pull-right" id="btn-mark-all"><i class="fa fa-check-double"></i> Mark All Read</button>
    </div>
    <div class="box-body">
        <?php if ($notifications): ?>
        <ul class="timeline">
        <?php foreach ($notifications as $n): ?>
        <li<?= !$n['is_read'] ? ' style="background:#fffde7;border-left:4px solid #f39c12"' : '' ?>>
            <i class="fa fa-bell<?= !$n['is_read'] ? ' bg-yellow' : ' bg-gray' ?>" style="margin-left:-4px"></i>
            <div class="timeline-item">
                <span class="time"><i class="fa fa-clock-o"></i> <?= time_ago($n['created_at']) ?></span>
                <h3 class="timeline-header"><?= esc_html($n['title']) ?></h3>
                <div class="timeline-body"><?= esc_html($n['body']) ?></div>
            </div>
        </li>
        <?php endforeach; ?>
        </ul>
        <?php else: ?>
        <p class="text-center text-muted"><i class="fa fa-bell-slash fa-2x"></i><br>No notifications</p>
        <?php endif; ?>
    </div>
</div>
</section>
<script>
$('#btn-mark-all').click(function(){
    $.post(BASE_URL+'notifications/mark_all',{[CI3_CSRF_NAME]:CI3_CSRF_HASH},function(res){
        if(res.status==='success'){CRM.toast('success',res.message);location.reload();}
    });
});
</script>
