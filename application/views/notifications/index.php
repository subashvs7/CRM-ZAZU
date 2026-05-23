<!-- Page Header -->
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
    <div class="flex items-center gap-3">
        <div class="w-10 h-10 bg-amber-100 rounded-xl flex items-center justify-center flex-shrink-0">
            <i class="fa fa-bell text-amber-600 text-lg"></i>
        </div>
        <div>
            <h1 class="text-xl font-bold text-gray-800">Notifications</h1>
            <nav class="text-xs text-gray-400 flex items-center gap-1 mt-0.5">
                <a href="<?= base_url('dashboard') ?>" class="hover:text-blue-600 transition-colors">Home</a>
                <i class="fa fa-angle-right text-[10px]"></i>
                <span class="text-gray-600">Notifications</span>
            </nav>
        </div>
    </div>
    <button id="btn-mark-all"
            class="inline-flex items-center gap-1.5 px-4 py-2 text-sm bg-white border border-gray-200 text-gray-700 font-medium rounded-xl hover:bg-gray-50 transition-colors self-start sm:self-auto">
        <i class="fa fa-check text-green-600"></i> Mark All Read
    </button>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-gray-100">
    <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
        <div>
            <h3 class="text-sm font-bold text-gray-800">All Notifications</h3>
            <p class="text-xs text-gray-400 mt-0.5">Your recent activity alerts</p>
        </div>
        <span class="px-2.5 py-1 bg-amber-50 text-amber-600 text-xs font-semibold rounded-lg">
            <i class="fa fa-bell mr-1"></i>
            <?= count(array_filter($notifications ?? [], fn($n) => !$n['is_read'])) ?> unread
        </span>
    </div>
    <div class="divide-y divide-gray-50">
        <?php if($notifications): ?>
        <?php foreach($notifications as $n): ?>
        <div class="flex items-start gap-4 px-6 py-4 hover:bg-gray-50 transition-colors <?= !$n['is_read'] ? 'bg-amber-50 hover:bg-amber-50/80' : '' ?>">
            <div class="flex-shrink-0 mt-0.5">
                <div class="w-9 h-9 rounded-xl flex items-center justify-center <?= !$n['is_read'] ? 'bg-amber-100 text-amber-600' : 'bg-gray-100 text-gray-400' ?>">
                    <i class="fa fa-bell text-sm"></i>
                </div>
            </div>
            <div class="flex-1 min-w-0">
                <div class="flex items-start justify-between gap-3">
                    <p class="text-sm font-semibold text-gray-800 leading-snug"><?= esc_html($n['title']) ?></p>
                    <span class="text-xs text-gray-400 flex-shrink-0 mt-0.5">
                        <i class="fa fa-clock-o mr-0.5"></i><?= time_ago($n['created_at']) ?>
                    </span>
                </div>
                <?php if(!empty($n['body'])): ?>
                <p class="text-sm text-gray-500 mt-1 leading-relaxed"><?= esc_html($n['body']) ?></p>
                <?php endif; ?>
            </div>
            <?php if(!$n['is_read']): ?>
            <div class="flex-shrink-0 w-2 h-2 rounded-full bg-amber-400 mt-2.5"></div>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
        <?php else: ?>
        <div class="py-20 text-center">
            <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fa fa-bell-slash text-gray-300 text-2xl"></i>
            </div>
            <p class="text-sm font-medium text-gray-500">No notifications yet</p>
            <p class="text-xs text-gray-400 mt-1">You're all caught up!</p>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
$('#btn-mark-all').click(function() {
    $.post(BASE_URL + 'notifications/mark_all', {[CI3_CSRF_NAME]: CI3_CSRF_HASH}, function(res) {
        if (res.status === 'success') { CRM.toast('success', res.message); location.reload(); }
    });
});
</script>
