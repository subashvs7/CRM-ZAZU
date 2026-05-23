<!-- Page Header -->
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
    <div class="flex items-center gap-3">
        <div class="w-12 h-12 bg-gradient-to-br from-emerald-500 to-green-700 rounded-2xl flex items-center justify-center flex-shrink-0 shadow-sm">
            <i class="fa fa-filter text-white text-lg"></i>
        </div>
        <div>
            <h1 class="text-xl font-bold text-gray-800"><?= esc_html($lead['title']) ?></h1>
            <nav class="text-xs text-gray-400 flex items-center gap-1 mt-0.5">
                <a href="<?= base_url('dashboard') ?>" class="hover:text-blue-600 transition-colors">Home</a>
                <i class="fa fa-angle-right text-[10px]"></i>
                <a href="<?= base_url('leads') ?>" class="hover:text-blue-600 transition-colors">Leads</a>
                <i class="fa fa-angle-right text-[10px]"></i>
                <span class="text-gray-600 truncate max-w-[140px]"><?= esc_html($lead['title']) ?></span>
            </nav>
        </div>
    </div>
    <div class="self-start sm:self-auto">
        <?= lead_status_badge($lead['lead_status']) ?>
    </div>
</div>

<div class="grid grid-cols-1 xl:grid-cols-3 gap-4">
    <!-- Left Column -->
    <div class="xl:col-span-1 space-y-4">

        <!-- Lead Info -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100">
            <div class="px-6 py-4 border-b border-gray-100">
                <h3 class="text-sm font-bold text-gray-800 flex items-center gap-2">
                    <i class="fa fa-info-circle text-emerald-500"></i> Lead Info
                </h3>
            </div>
            <div class="p-5 space-y-3">
                <div class="flex items-center justify-between py-1 border-b border-gray-50">
                    <span class="text-xs text-gray-400 uppercase font-semibold tracking-wide">Customer</span>
                    <a href="<?= base_url('customers/detail/'.$lead['customer_id']) ?>"
                       class="text-sm font-semibold text-blue-600 hover:underline">
                        <?= esc_html($lead['customer_name']) ?>
                    </a>
                </div>
                <?php if(!empty($lead['customer_phone'])): ?>
                <div class="flex items-center justify-between py-1 border-b border-gray-50">
                    <span class="text-xs text-gray-400 uppercase font-semibold tracking-wide">Phone</span>
                    <a href="tel:<?= esc_html($lead['customer_phone']) ?>" class="text-sm text-blue-600 hover:underline">
                        <?= esc_html($lead['customer_phone']) ?>
                    </a>
                </div>
                <?php endif; ?>
                <div class="flex items-center justify-between py-1 border-b border-gray-50">
                    <span class="text-xs text-gray-400 uppercase font-semibold tracking-wide">Source</span>
                    <span class="text-sm text-gray-700"><?= esc_html(ucfirst(str_replace('_', ' ', $lead['source']))) ?></span>
                </div>
                <div class="flex items-center justify-between py-1 border-b border-gray-50">
                    <span class="text-xs text-gray-400 uppercase font-semibold tracking-wide">Assigned</span>
                    <span class="text-sm font-medium text-gray-700">
                        <?php if(!empty($lead['assigned_name'])): ?>
                        <span class="inline-flex items-center gap-1.5">
                            <span class="w-5 h-5 rounded-full bg-purple-100 text-purple-700 text-[10px] font-bold flex items-center justify-center">
                                <?= strtoupper(substr($lead['assigned_name'], 0, 1)) ?>
                            </span>
                            <?= esc_html($lead['assigned_name']) ?>
                        </span>
                        <?php else: ?>
                        <span class="text-gray-400">Unassigned</span>
                        <?php endif; ?>
                    </span>
                </div>
                <?php if($lead['expected_value']): ?>
                <div class="flex items-center justify-between py-1 border-b border-gray-50">
                    <span class="text-xs text-gray-400 uppercase font-semibold tracking-wide">Value</span>
                    <span class="text-sm font-bold text-emerald-600">
                        <i class="fa fa-inr text-xs"></i> <?= format_inr($lead['expected_value']) ?>
                    </span>
                </div>
                <?php endif; ?>
                <?php if($lead['expected_close_date']): ?>
                <div class="flex items-center justify-between py-1 border-b border-gray-50">
                    <span class="text-xs text-gray-400 uppercase font-semibold tracking-wide">Close Date</span>
                    <span class="text-sm text-gray-700">
                        <i class="fa fa-calendar-o mr-1 text-gray-400"></i><?= esc_html($lead['expected_close_date']) ?>
                    </span>
                </div>
                <?php endif; ?>
                <div class="flex items-center justify-between py-1">
                    <span class="text-xs text-gray-400 uppercase font-semibold tracking-wide">Status</span>
                    <?= status_badge($lead['status']) ?>
                </div>
            </div>
            <?php if($lead['description']): ?>
            <div class="px-5 pb-5">
                <div class="bg-blue-50 border border-blue-100 rounded-xl p-3">
                    <p class="text-xs text-blue-600 font-semibold uppercase tracking-wide mb-1">
                        <i class="fa fa-align-left mr-1"></i> Description
                    </p>
                    <p class="text-xs text-gray-600 leading-relaxed"><?= nl2br(esc_html($lead['description'])) ?></p>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Update Stage / Log Activity -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100">
            <div class="px-6 py-4 border-b border-gray-100">
                <h3 class="text-sm font-bold text-gray-800 flex items-center gap-2">
                    <i class="fa fa-pencil text-blue-500"></i> Log Activity
                </h3>
            </div>
            <div class="p-5">
                <form id="activity-form" method="POST" action="<?= base_url('leads/add_activity') ?>">
                    <input type="hidden" name="<?= $csrf_name ?>" value="<?= $csrf_hash ?>">
                    <input type="hidden" name="lead_id" value="<?= $lead['id'] ?>">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">Activity Type</label>
                            <select name="activity_type" class="w-full px-3 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="call">Call</option>
                                <option value="email">Email</option>
                                <option value="visit">Visit</option>
                                <option value="note">Note</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">Move Stage To</label>
                            <select name="stage" class="w-full px-3 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">— No Change —</option>
                                <option value="new">New</option>
                                <option value="contacted">Contacted</option>
                                <option value="qualified">Qualified</option>
                                <option value="proposal">Proposal</option>
                                <option value="negotiation">Negotiation</option>
                                <option value="won">Won</option>
                                <option value="lost">Lost</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">Notes</label>
                            <textarea name="notes" rows="3" class="w-full px-3 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none"></textarea>
                        </div>
                        <button type="submit" class="w-full flex items-center justify-center gap-2 px-4 py-2.5 bg-blue-600 text-white text-sm font-semibold rounded-xl hover:bg-blue-700 transition-colors">
                            <i class="fa fa-plus"></i> Log Activity
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Right Column - Activity Timeline -->
    <div class="xl:col-span-2">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 h-full">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-2">
                <div class="w-8 h-8 bg-cyan-100 rounded-lg flex items-center justify-center">
                    <i class="fa fa-history text-cyan-600 text-sm"></i>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-gray-800">Activity Timeline</h3>
                    <p class="text-xs text-gray-400">All logged interactions</p>
                </div>
            </div>
            <div class="p-5" id="activity-timeline">
                <?php if($activities): ?>
                <div class="space-y-4">
                    <?php
                    $type_colors = [
                        'call'  => ['bg' => 'bg-blue-100',   'text' => 'text-blue-700',   'icon' => 'fa-phone'],
                        'email' => ['bg' => 'bg-purple-100', 'text' => 'text-purple-700', 'icon' => 'fa-envelope'],
                        'visit' => ['bg' => 'bg-green-100',  'text' => 'text-green-700',  'icon' => 'fa-map-marker'],
                        'note'  => ['bg' => 'bg-amber-100',  'text' => 'text-amber-700',  'icon' => 'fa-sticky-note'],
                    ];
                    $last_act_i = count($activities) - 1;
                    foreach ($activities as $act_i => $a):
                        $tc = $type_colors[$a['activity_type']] ?? ['bg' => 'bg-gray-100', 'text' => 'text-gray-700', 'icon' => 'fa-circle'];
                    ?>
                    <div class="flex gap-4">
                        <div class="flex flex-col items-center">
                            <div class="w-8 h-8 rounded-xl <?= $tc['bg'] ?> <?= $tc['text'] ?> flex items-center justify-center flex-shrink-0">
                                <i class="fa <?= $tc['icon'] ?> text-xs"></i>
                            </div>
                            <?php if($act_i < $last_act_i): ?>
                            <div class="w-px flex-1 bg-gray-100 mt-2"></div>
                            <?php endif; ?>
                        </div>
                        <div class="flex-1 pb-4">
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="inline-flex items-center px-2 py-0.5 text-[11px] font-bold rounded-lg <?= $tc['bg'] ?> <?= $tc['text'] ?> uppercase tracking-wide">
                                    <?= esc_html(ucfirst($a['activity_type'])) ?>
                                </span>
                                <span class="text-sm font-semibold text-gray-800"><?= esc_html($a['user_name']) ?></span>
                                <span class="text-xs text-gray-400 ml-auto"><?= time_ago($a['occurred_at']) ?></span>
                            </div>
                            <?php if($a['notes']): ?>
                            <p class="mt-2 text-sm text-gray-600 bg-gray-50 rounded-xl px-3 py-2 leading-relaxed">
                                <?= nl2br(esc_html($a['notes'])) ?>
                            </p>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <div class="flex flex-col items-center justify-center py-16 text-center">
                    <div class="w-14 h-14 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                        <i class="fa fa-history text-gray-300 text-2xl"></i>
                    </div>
                    <p class="text-sm font-medium text-gray-500">No activities yet</p>
                    <p class="text-xs text-gray-400 mt-1">Log your first activity using the form</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
$('#activity-form').on('submit', function(e) {
    e.preventDefault();
    CRM.submit_form($(this), function() { setTimeout(function() { location.reload(); }, 800); });
});
</script>
