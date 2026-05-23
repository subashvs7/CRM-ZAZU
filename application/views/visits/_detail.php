<!-- Page Header -->
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
    <div class="flex items-center gap-3">
        <div class="w-10 h-10 bg-cyan-100 rounded-xl flex items-center justify-center flex-shrink-0">
            <i class="fa fa-map-marker text-cyan-600 text-lg"></i>
        </div>
        <div>
            <h1 class="text-xl font-bold text-gray-800">Visit Detail</h1>
            <nav class="text-xs text-gray-400 flex items-center gap-1 mt-0.5">
                <a href="<?= base_url('dashboard') ?>" class="hover:text-blue-600 transition-colors">Home</a>
                <i class="fa fa-angle-right text-[10px]"></i>
                <a href="<?= base_url('visits') ?>" class="hover:text-blue-600 transition-colors">Visits</a>
                <i class="fa fa-angle-right text-[10px]"></i>
                <span class="text-gray-600">Detail</span>
            </nav>
        </div>
    </div>
    <div class="self-start sm:self-auto">
        <?= visit_status_badge($plan['visit_status']) ?>
    </div>
</div>

<div class="max-w-2xl">
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-2">
            <div class="w-8 h-8 bg-cyan-100 rounded-lg flex items-center justify-center">
                <i class="fa fa-map-marker text-cyan-600 text-sm"></i>
            </div>
            <div>
                <h3 class="text-sm font-bold text-gray-800">Visit Information</h3>
                <p class="text-xs text-gray-400">Planned visit details</p>
            </div>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="bg-gray-50 rounded-xl p-4">
                    <p class="text-[11px] font-bold text-gray-400 uppercase tracking-wide mb-1">Customer</p>
                    <p class="text-sm font-semibold text-gray-800"><?= esc_html($plan['customer_name']) ?></p>
                </div>
                <div class="bg-gray-50 rounded-xl p-4">
                    <p class="text-[11px] font-bold text-gray-400 uppercase tracking-wide mb-1">Staff</p>
                    <div class="flex items-center gap-2">
                        <div class="w-6 h-6 rounded-full bg-blue-100 text-blue-700 text-[10px] font-bold flex items-center justify-center flex-shrink-0">
                            <?= strtoupper(substr($plan['user_name'] ?? '?', 0, 1)) ?>
                        </div>
                        <p class="text-sm font-semibold text-gray-800"><?= esc_html($plan['user_name']) ?></p>
                    </div>
                </div>
                <div class="bg-gray-50 rounded-xl p-4">
                    <p class="text-[11px] font-bold text-gray-400 uppercase tracking-wide mb-1">Planned Date</p>
                    <p class="text-sm font-semibold text-gray-800">
                        <i class="fa fa-calendar-o mr-1 text-gray-400"></i><?= esc_html($plan['planned_date']) ?>
                    </p>
                </div>
                <div class="bg-gray-50 rounded-xl p-4">
                    <p class="text-[11px] font-bold text-gray-400 uppercase tracking-wide mb-1">Planned Time</p>
                    <p class="text-sm font-semibold text-gray-800">
                        <i class="fa fa-clock-o mr-1 text-gray-400"></i><?= esc_html($plan['planned_time'] ?? '—') ?>
                    </p>
                </div>
                <div class="bg-gray-50 rounded-xl p-4">
                    <p class="text-[11px] font-bold text-gray-400 uppercase tracking-wide mb-1">Created By</p>
                    <p class="text-sm font-semibold text-gray-800"><?= esc_html($plan['created_by_name'] ?? '—') ?></p>
                </div>
                <div class="bg-gray-50 rounded-xl p-4">
                    <p class="text-[11px] font-bold text-gray-400 uppercase tracking-wide mb-1">Status</p>
                    <?= visit_status_badge($plan['visit_status']) ?>
                </div>
                <?php if(!empty($plan['purpose'])): ?>
                <div class="sm:col-span-2 bg-blue-50 border border-blue-100 rounded-xl p-4">
                    <p class="text-[11px] font-bold text-blue-500 uppercase tracking-wide mb-2">
                        <i class="fa fa-align-left mr-1"></i> Purpose
                    </p>
                    <p class="text-sm text-gray-700 leading-relaxed"><?= nl2br(esc_html($plan['purpose'])) ?></p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
