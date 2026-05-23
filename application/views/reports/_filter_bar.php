<div class="bg-white rounded-2xl shadow-sm border border-gray-100 mb-5">
    <div class="px-6 py-4">
        <div class="flex flex-wrap items-end gap-3">
            <div>
                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">From</label>
                <input type="text" id="from-date"
                       class="px-3 py-2 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white datepicker"
                       value="<?= date('Y-m-01') ?>" readonly>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">To</label>
                <input type="text" id="to-date"
                       class="px-3 py-2 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white datepicker"
                       value="<?= date('Y-m-t') ?>" readonly>
            </div>
            <?php if(isset($staff)): ?>
            <div class="min-w-48">
                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">Staff</label>
                <select id="staff-filter" class="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 select2">
                    <option value="">— All Staff —</option>
                    <?php foreach($staff as $s): ?>
                    <option value="<?= $s['id'] ?>"><?= esc_html($s['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php endif; ?>
            <button id="btn-filter"
                    class="inline-flex items-center gap-1.5 px-4 py-2 text-sm bg-blue-600 text-white font-semibold rounded-xl hover:bg-blue-700 transition-colors shadow-sm">
                <i class="fa fa-search"></i> Apply Filter
            </button>
        </div>
    </div>
</div>
