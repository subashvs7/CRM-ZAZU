<div class="border-b border-gray-200 mb-4">
    <nav class="flex gap-0" id="status-tabs">
        <a href="#" data-status=""
           class="px-4 py-2.5 text-sm font-medium border-b-2 transition-colors <?= !$sf ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700' ?>">
            All
        </a>
        <a href="#" data-status="active"
           class="px-4 py-2.5 text-sm font-medium border-b-2 transition-colors <?= $sf==='active' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700' ?>">
            Active
        </a>
        <a href="#" data-status="inactive"
           class="px-4 py-2.5 text-sm font-medium border-b-2 transition-colors <?= $sf==='inactive' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700' ?>">
            Inactive
        </a>
        <?php if($is_admin): ?>
        <a href="#" data-status="deleted"
           class="px-4 py-2.5 text-sm font-medium border-b-2 transition-colors <?= $sf==='deleted' ? 'border-red-500 text-red-600' : 'border-transparent text-red-400 hover:text-red-600' ?>">
            <i class="fa fa-trash mr-1"></i>Deleted
        </a>
        <?php endif; ?>
    </nav>
</div>
<div id="deleted-banner" class="<?= $sf==='deleted' ? '' : 'hidden' ?> mb-4 flex items-center gap-2 p-3 bg-red-50 border border-red-200 text-red-700 rounded-lg text-sm">
    <i class="fa fa-exclamation-triangle flex-shrink-0"></i>
    <span><strong>Deleted Records.</strong> Hidden from all normal users. Use <strong>Restore</strong> to recover.</span>
</div>
