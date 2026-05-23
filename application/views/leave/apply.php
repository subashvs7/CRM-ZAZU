<!-- Page Header -->
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
    <div class="flex items-center gap-3">
        <div class="w-10 h-10 bg-violet-100 rounded-xl flex items-center justify-center flex-shrink-0">
            <i class="fa fa-plane text-violet-600 text-lg"></i>
        </div>
        <div>
            <h1 class="text-xl font-bold text-gray-800">Apply for Leave</h1>
            <nav class="text-xs text-gray-400 flex items-center gap-1 mt-0.5">
                <a href="<?= base_url('dashboard') ?>" class="hover:text-blue-600 transition-colors">Home</a>
                <i class="fa fa-angle-right text-[10px]"></i>
                <a href="<?= base_url('leave') ?>" class="hover:text-blue-600 transition-colors">Leave</a>
                <i class="fa fa-angle-right text-[10px]"></i>
                <span class="text-gray-600">Apply</span>
            </nav>
        </div>
    </div>
    <a href="<?= base_url('leave') ?>"
       class="inline-flex items-center gap-1.5 px-4 py-2 text-sm bg-white border border-gray-200 text-gray-700 font-medium rounded-xl hover:bg-gray-50 transition-colors self-start sm:self-auto">
        <i class="fa fa-arrow-left text-gray-500"></i> Back to Leave
    </a>
</div>

<div class="max-w-lg mx-auto">
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-2">
            <div class="w-8 h-8 bg-violet-100 rounded-lg flex items-center justify-center">
                <i class="fa fa-paper-plane text-violet-600 text-sm"></i>
            </div>
            <div>
                <h3 class="text-sm font-bold text-gray-800">Leave Application</h3>
                <p class="text-xs text-gray-400">Fill in the details to submit your request</p>
            </div>
        </div>
        <div class="p-6">
            <form id="leave-form" method="POST" action="<?= base_url('leave/save') ?>">
                <input type="hidden" name="<?= $csrf_name ?>" value="<?= $csrf_hash ?>">
                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">Leave Type *</label>
                        <select name="leave_type_id" class="w-full px-3 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                            <option value="">— Select Type —</option>
                            <?php foreach($types as $t): ?>
                            <option value="<?= $t['id'] ?>"><?= esc_html($t['name']) ?> (<?= $t['days_allowed_per_year'] ?> days/yr)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">From Date *</label>
                            <input type="text" name="from_date" class="w-full px-3 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 datepicker" required>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">To Date *</label>
                            <input type="text" name="to_date" class="w-full px-3 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 datepicker" required>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">Reason *</label>
                        <textarea name="reason" rows="4" class="w-full px-3 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none" required placeholder="Please provide the reason for your leave request..."></textarea>
                    </div>
                    <button type="submit"
                            class="w-full flex items-center justify-center gap-2 px-4 py-3 bg-violet-600 text-white text-sm font-bold rounded-xl hover:bg-violet-700 transition-colors shadow-sm">
                        <i class="fa fa-paper-plane"></i> Submit Application
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
CRM.init_plugins();
$('#leave-form').on('submit', function(e) {
    e.preventDefault();
    CRM.submit_form($(this), function() { window.location = BASE_URL + 'leave'; });
});
</script>
