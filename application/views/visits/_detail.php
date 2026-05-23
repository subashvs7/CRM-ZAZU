<section class="content-header">
    <h1>Visit Detail</h1>
    <ol class="breadcrumb"><li><a href="<?= base_url('visits') ?>">Visits</a></li><li class="active">Detail</li></ol>
</section>
<section class="content">
<div class="box box-primary">
    <div class="box-body">
        <dl class="dl-horizontal">
            <dt>Customer</dt><dd><?= esc_html($plan['customer_name']) ?></dd>
            <dt>Staff</dt><dd><?= esc_html($plan['user_name']) ?></dd>
            <dt>Planned Date</dt><dd><?= $plan['planned_date'] ?></dd>
            <dt>Time</dt><dd><?= $plan['planned_time']??'-' ?></dd>
            <dt>Status</dt><dd><?= visit_status_badge($plan['visit_status']) ?></dd>
            <dt>Purpose</dt><dd><?= nl2br(esc_html($plan['purpose']??'-')) ?></dd>
            <dt>Created By</dt><dd><?= esc_html($plan['created_by_name']??'-') ?></dd>
        </dl>
    </div>
</div>
</section>
