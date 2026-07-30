<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
    <div class="flex items-center gap-3">
        <a href="<?= base_url('product_hub/assets') ?>" class="w-10 h-10 bg-gray-100 hover:bg-gray-200 text-gray-500 rounded-xl flex items-center justify-center transition-colors">
            <i class="fa fa-arrow-left"></i>
        </a>
        <div>
            <h1 class="text-xl font-bold text-gray-800">Asset Details</h1>
            <nav class="text-xs text-gray-400 flex items-center gap-1 mt-0.5">
                <a href="<?= base_url('product_hub/assets') ?>" class="hover:text-blue-600 transition-colors">Product Hub</a>
                <i class="fa fa-angle-right text-[10px]"></i>
                <a href="<?= base_url('product_hub/assets') ?>" class="hover:text-blue-600 transition-colors">Assets & Demos</a>
                <i class="fa fa-angle-right text-[10px]"></i>
                <span class="text-gray-600">Asset Details</span>
            </nav>
        </div>
    </div>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 max-w-4xl">
    <div class="flex flex-col md:flex-row gap-8">
        <div class="w-full md:w-1/3 flex flex-col items-center justify-center bg-gray-50 rounded-xl p-8 border border-gray-100">
            <?php if ($asset['asset_type'] === 'Brochure' || $asset['asset_type'] === 'Document'): ?>
                <i class="fa fa-file-pdf-o text-6xl text-red-500 mb-4"></i>
            <?php elseif ($asset['asset_type'] === 'Video'): ?>
                <i class="fa fa-file-video-o text-6xl text-blue-500 mb-4"></i>
            <?php elseif ($asset['asset_type'] === 'PPT'): ?>
                <i class="fa fa-file-powerpoint-o text-6xl text-orange-500 mb-4"></i>
            <?php else: ?>
                <i class="fa fa-link text-6xl text-gray-500 mb-4"></i>
            <?php endif; ?>
            
            <h2 class="text-lg font-bold text-gray-800 text-center mb-1"><?= esc_html($asset['title']) ?></h2>
            <span class="px-3 py-1 bg-gray-200 text-gray-600 text-xs font-semibold rounded-full"><?= esc_html($asset['asset_type']) ?></span>
        </div>
        
        <div class="w-full md:w-2/3">
            <h3 class="text-xl font-bold text-gray-800 mb-4 border-b border-gray-100 pb-2">Information</h3>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-y-4 gap-x-6">
                <div>
                    <span class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Product</span>
                    <span class="text-sm text-gray-800 font-medium"><?= esc_html($asset['product_name'] ?? 'General/None') ?></span>
                </div>
                <div>
                    <span class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Added On</span>
                    <span class="text-sm text-gray-800 font-medium"><?= date('d M Y h:i A', strtotime($asset['created_at'])) ?></span>
                </div>
                <div class="sm:col-span-2 mt-2">
                    <span class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Description</span>
                    <p class="text-sm text-gray-600 bg-gray-50 p-3 rounded-lg border border-gray-100 min-h-[60px]">
                        <?= esc_html($asset['description'] ?: 'No description provided.') ?>
                    </p>
                </div>
                <div class="sm:col-span-2 mt-4">
                    <span class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">File / Link Access</span>
                    <?php if (filter_var($asset['file_link'], FILTER_VALIDATE_URL)): ?>
                        <a href="<?= esc_html($asset['file_link']) ?>" target="_blank" class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-800 font-medium rounded-lg transition-colors text-sm border border-gray-200">
                            <i class="fa fa-external-link text-blue-600"></i> Open External Link
                        </a>
                        <p class="text-xs text-blue-500 mt-2 truncate max-w-full"><a href="<?= esc_html($asset['file_link']) ?>" target="_blank" class="hover:underline"><?= esc_html($asset['file_link']) ?></a></p>
                    <?php else: ?>
                        <a href="<?= base_url($asset['file_link']) ?>" target="_blank" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-50 hover:bg-blue-100 text-blue-700 font-medium rounded-lg transition-colors text-sm border border-blue-200">
                            <i class="fa fa-download text-blue-600"></i> Download File
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
