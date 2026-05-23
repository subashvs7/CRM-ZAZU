<section class="content-header">
    <h1>Lead Pipeline <small>Kanban view</small></h1>
    <ol class="breadcrumb"><li><a href="<?= base_url('dashboard') ?>">Home</a></li><li><a href="<?= base_url('leads') ?>">Leads</a></li><li class="active">Pipeline</li></ol>
</section>
<section class="content">
<div class="pipeline-board" style="display:flex;gap:12px;overflow-x:auto;padding-bottom:20px">
<?php
$stages = ['new'=>['label'=>'New','color'=>'#95a5a6'],'contacted'=>['label'=>'Contacted','color'=>'#3498db'],'qualified'=>['label'=>'Qualified','color'=>'#2980b9'],'proposal'=>['label'=>'Proposal','color'=>'#f39c12'],'negotiation'=>['label'=>'Negotiation','color'=>'#e67e22'],'won'=>['label'=>'Won','color'=>'#27ae60'],'lost'=>['label'=>'Lost','color'=>'#e74c3c']];
foreach ($stages as $key => $stage):
    $leads = $pipeline[$key] ?? [];
?>
<div class="pipeline-column" style="min-width:240px;flex:0 0 240px">
    <div style="background:<?= $stage['color'] ?>;color:#fff;padding:8px 12px;border-radius:6px 6px 0 0;font-weight:600">
        <?= $stage['label'] ?> <span class="badge"><?= count($leads) ?></span>
    </div>
    <div class="pipeline-col-body" style="background:#f9f9f9;border:1px solid #ddd;border-top:0;border-radius:0 0 6px 6px;min-height:400px;padding:8px" data-stage="<?= $key ?>">
        <?php foreach ($leads as $lead): ?>
        <div class="pipeline-card" style="background:#fff;border:1px solid #e3e3e3;border-radius:4px;padding:10px;margin-bottom:8px;cursor:pointer" onclick="window.location='<?= base_url('leads/detail/'.$lead['id']) ?>'">
            <div style="font-weight:600;font-size:13px"><?= esc_html($lead['title']) ?></div>
            <div class="text-muted" style="font-size:12px"><?= esc_html($lead['customer_name']) ?></div>
            <?php if($lead['expected_value']): ?>
            <div class="text-success" style="font-size:12px;margin-top:4px"><i class="fa fa-inr"></i> <?= format_inr($lead['expected_value']) ?></div>
            <?php endif; ?>
            <?php if($lead['expected_close_date']): ?>
            <div class="text-muted" style="font-size:11px"><i class="fa fa-calendar"></i> <?= $lead['expected_close_date'] ?></div>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
        <?php if (empty($leads)): ?>
        <div class="text-center text-muted" style="padding-top:40px;font-size:12px">No leads</div>
        <?php endif; ?>
    </div>
</div>
<?php endforeach; ?>
</div>
</section>
