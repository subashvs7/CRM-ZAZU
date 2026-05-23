<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
    <div class="flex items-center gap-3">
        <div class="w-10 h-10 bg-purple-100 rounded-xl flex items-center justify-center flex-shrink-0">
            <i class="fa fa-cog text-purple-600 text-lg"></i>
        </div>
        <div>
            <h1 class="text-xl font-bold text-gray-800">Selfie Settings</h1>
            <nav class="text-xs text-gray-400 flex items-center gap-1 mt-0.5">
                <a href="<?= base_url('selfie/log') ?>" class="hover:text-blue-600 transition-colors">Selfie</a>
                <i class="fa fa-angle-right text-[10px]"></i>
                <span class="text-gray-600">Settings</span>
            </nav>
        </div>
    </div>
</div>

<div class="max-w-md">
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-2">
            <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center">
                <i class="fa fa-cog text-purple-600 text-sm"></i>
            </div>
            <div>
                <h3 class="text-sm font-bold text-gray-800">Selfie Verification Settings</h3>
                <p class="text-xs text-gray-400 mt-0.5">Configure face recognition parameters</p>
            </div>
        </div>
        <div class="p-6">
            <form id="selfie-settings-form" method="POST" action="<?= base_url('selfie/save_settings') ?>">
                <input type="hidden" name="<?= $csrf_name ?>" value="<?= $csrf_hash ?>">
                <div class="space-y-5">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">Face Match Threshold <span class="normal-case font-normal text-gray-400">(0.0 – 1.0)</span></label>
                        <input type="number" step="0.01" min="0" max="1" name="face_match_threshold"
                               class="w-40 px-3 py-2 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-500"
                               value="<?= esc_html($settings['face_match_threshold'] ?? '0.75') ?>">
                        <p class="text-xs text-gray-400 mt-1.5">Values above this threshold are considered a match. Recommended: 0.75</p>
                    </div>
                    <div class="pt-2 border-t border-gray-100">
                        <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 bg-purple-600 text-white text-sm font-semibold rounded-xl hover:bg-purple-700 transition-colors shadow-sm">
                            <i class="fa fa-save"></i> Save Settings
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$('#selfie-settings-form').on('submit', function(e){
    e.preventDefault();
    CRM.submit_form($(this));
});
</script>
