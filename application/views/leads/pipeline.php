<!-- Page Header -->
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
    <div class="flex items-center gap-3">
        <div class="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center flex-shrink-0">
            <i class="fa fa-columns text-blue-600 text-lg"></i>
        </div>
        <div>
            <h1 class="text-xl font-bold text-gray-800">Lead Pipeline</h1>
            <nav class="text-xs text-gray-400 flex items-center gap-1 mt-0.5">
                <a href="<?= base_url('dashboard') ?>" class="hover:text-blue-600 transition-colors">Home</a>
                <i class="fa fa-angle-right text-[10px]"></i>
                <a href="<?= base_url('leads') ?>" class="hover:text-blue-600 transition-colors">Leads</a>
                <i class="fa fa-angle-right text-[10px]"></i>
                <span class="text-gray-600">Pipeline</span>
            </nav>
        </div>
    </div>
    <a href="<?= base_url('leads') ?>"
       class="inline-flex items-center gap-1.5 px-4 py-2 text-sm bg-white border border-gray-200 text-gray-700 font-medium rounded-xl hover:bg-gray-50 transition-colors self-start sm:self-auto">
        <i class="fa fa-list text-gray-500"></i> List View
    </a>
</div>

<!-- Kanban Board -->
<div class="flex gap-4 overflow-x-auto pb-6">
<?php
$stages = [
    'new'         => ['label' => 'New',         'color' => 'from-slate-500 to-slate-600',   'dot' => 'bg-slate-400'],
    'contacted'   => ['label' => 'Contacted',   'color' => 'from-cyan-500 to-cyan-600',     'dot' => 'bg-cyan-400'],
    'qualified'   => ['label' => 'Qualified',   'color' => 'from-blue-500 to-blue-700',     'dot' => 'bg-blue-400'],
    'proposal'    => ['label' => 'Proposal',    'color' => 'from-amber-500 to-amber-600',   'dot' => 'bg-amber-400'],
    'negotiation' => ['label' => 'Negotiation', 'color' => 'from-orange-500 to-orange-600', 'dot' => 'bg-orange-400'],
    'won'         => ['label' => 'Won',         'color' => 'from-emerald-500 to-green-600', 'dot' => 'bg-emerald-400'],
    'lost'        => ['label' => 'Lost',        'color' => 'from-red-500 to-rose-600',      'dot' => 'bg-red-400'],
];
foreach ($stages as $key => $stage):
    $leads = $pipeline[$key] ?? [];
    $total_val = array_sum(array_column($leads, 'expected_value'));
?>
<div class="pipeline-column flex-shrink-0 w-64">
    <!-- Column Header -->
    <div class="bg-gradient-to-r <?= $stage['color'] ?> text-white px-4 py-3 rounded-t-2xl flex items-center justify-between">
        <div class="flex items-center gap-2">
            <div class="w-2 h-2 rounded-full <?= $stage['dot'] ?> bg-white/60"></div>
            <span class="text-sm font-bold"><?= $stage['label'] ?></span>
        </div>
        <span class="bg-white/25 text-white text-xs px-2 py-0.5 rounded-full font-bold min-w-[22px] text-center">
            <?= count($leads) ?>
        </span>
    </div>
    <?php if($total_val > 0): ?>
    <div class="bg-white/80 border-x border-gray-200 px-4 py-1.5 text-center">
        <span class="text-xs font-semibold text-gray-500">₹<?= number_format($total_val / 100, 0, '.', ',') ?></span>
    </div>
    <?php endif; ?>

    <!-- Cards -->
    <div class="bg-gray-50/80 border border-gray-200 border-t-0 rounded-b-2xl min-h-[24rem] p-2 space-y-2" data-stage="<?= $key ?>">
        <?php foreach ($leads as $lead): ?>
        <div class="pipeline-card bg-white border border-gray-100 rounded-xl p-3.5 cursor-pointer shadow-sm"
             onclick="window.location='<?= base_url('leads/detail/'.$lead['id']) ?>'">
            <p class="text-sm font-bold text-gray-800 leading-tight line-clamp-2"><?= esc_html($lead['title']) ?></p>
            <div class="flex items-center gap-1.5 mt-2">
                <div class="w-4 h-4 rounded-full bg-blue-100 text-blue-700 text-[9px] font-bold flex items-center justify-center flex-shrink-0">
                    <?= strtoupper(substr($lead['customer_name'] ?? '?', 0, 1)) ?>
                </div>
                <p class="text-xs text-gray-500 truncate"><?= esc_html($lead['customer_name']) ?></p>
            </div>
            <?php if($lead['expected_value']): ?>
            <p class="text-xs font-semibold text-emerald-600 mt-2">
                <i class="fa fa-inr text-[10px]"></i> <?= format_inr($lead['expected_value']) ?>
            </p>
            <?php endif; ?>
            <?php if($lead['expected_close_date']): ?>
            <p class="text-[11px] text-gray-400 mt-1.5 flex items-center gap-1">
                <i class="fa fa-calendar-o"></i><?= esc_html($lead['expected_close_date']) ?>
            </p>
            <?php endif; ?>
            <?php if(!empty($lead['assigned_name'])): ?>
            <div class="flex items-center gap-1.5 mt-2 pt-2 border-t border-gray-50">
                <div class="w-4 h-4 rounded-full bg-purple-100 text-purple-700 text-[9px] font-bold flex items-center justify-center flex-shrink-0">
                    <?= strtoupper(substr($lead['assigned_name'], 0, 1)) ?>
                </div>
                <span class="text-[11px] text-gray-400 truncate"><?= esc_html($lead['assigned_name']) ?></span>
            </div>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
        <?php if(empty($leads)): ?>
        <div class="flex flex-col items-center justify-center h-28 text-gray-300">
            <i class="fa fa-inbox fa-lg mb-1"></i>
            <span class="text-xs">No leads</span>
        </div>
        <?php endif; ?>
    </div>
</div>
<?php endforeach; ?>
</div>
