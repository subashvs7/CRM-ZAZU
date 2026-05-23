<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
    <div class="flex items-center gap-3">
        <div class="w-10 h-10 bg-indigo-100 rounded-xl flex items-center justify-center flex-shrink-0">
            <i class="fa fa-user-plus text-indigo-600 text-lg"></i>
        </div>
        <div>
            <h1 class="text-xl font-bold text-gray-800">Shift Assignment</h1>
            <nav class="text-xs text-gray-400 flex items-center gap-1 mt-0.5">
                <a href="<?= base_url('shifts') ?>" class="hover:text-blue-600 transition-colors">Shifts</a>
                <i class="fa fa-angle-right text-[10px]"></i>
                <span class="text-gray-600">Assignment</span>
            </nav>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-5 gap-5">
    <!-- Assign Form -->
    <div class="lg:col-span-2">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-2">
                <div class="w-8 h-8 bg-indigo-100 rounded-lg flex items-center justify-center">
                    <i class="fa fa-user-plus text-indigo-600 text-sm"></i>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-gray-800">Assign Shift</h3>
                    <p class="text-xs text-gray-400 mt-0.5">Link staff to a shift pattern</p>
                </div>
            </div>
            <div class="p-5">
                <form id="assign-form" method="POST" action="<?= base_url('shifts/save_assignment') ?>">
                    <input type="hidden" name="<?= $csrf_name ?>" value="<?= $csrf_hash ?>">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">Staff *</label>
                            <select name="user_id" class="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 select2" required>
                                <option value="">-- Select Staff --</option>
                                <?php foreach($staff as $s): ?><option value="<?= $s['id'] ?>"><?= esc_html($s['name']) ?></option><?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">Shift *</label>
                            <select name="shift_id" class="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 select2" required>
                                <option value="">-- Select Shift --</option>
                                <?php foreach($shifts as $sh): ?><option value="<?= $sh['id'] ?>"><?= esc_html($sh['name']) ?> (<?= $sh['start_time'] ?>–<?= $sh['end_time'] ?>)</option><?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">Effective From *</label>
                            <input type="text" name="effective_from" class="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 datepicker" required>
                        </div>
                        <button type="submit" class="w-full flex items-center justify-center gap-2 px-4 py-2.5 bg-indigo-600 text-white text-sm font-semibold rounded-xl hover:bg-indigo-700 transition-colors shadow-sm">
                            <i class="fa fa-check"></i> Assign Shift
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Current Assignments -->
    <div class="lg:col-span-3">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-2">
                <div class="w-8 h-8 bg-cyan-100 rounded-lg flex items-center justify-center">
                    <i class="fa fa-list text-cyan-600 text-sm"></i>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-gray-800">Current Assignments</h3>
                    <p class="text-xs text-gray-400 mt-0.5">Active shift schedule per staff</p>
                </div>
            </div>
            <div class="p-4" id="assign-table-box">
                <p class="text-center text-gray-400 py-6"><i class="fa fa-spinner fa-spin mr-1"></i> Loading...</p>
            </div>
        </div>
    </div>
</div>

<script>
CRM.init_plugins();

function loadAssignments() {
    $.getJSON(BASE_URL+'shifts/calendar_data', function(res) {
        var html = '<div class="overflow-x-auto"><table class="w-full text-sm">' +
            '<thead class="bg-gray-50"><tr>' +
            '<th class="px-4 py-3 text-left text-[11px] font-bold text-gray-500 uppercase tracking-wide">Staff / Shift</th>' +
            '<th class="px-4 py-3 text-left text-[11px] font-bold text-gray-500 uppercase tracking-wide">From</th>' +
            '<th class="px-4 py-3 text-left text-[11px] font-bold text-gray-500 uppercase tracking-wide">To</th>' +
            '</tr></thead><tbody class="divide-y divide-gray-50">';
        $.each(res||[], function(i,r){
            html += '<tr class="hover:bg-gray-50">' +
                '<td class="px-4 py-3 font-semibold text-gray-700">'+CRM.esc(r.title||'')+'</td>' +
                '<td class="px-4 py-3 text-gray-500">'+CRM.esc(r.start||'')+'</td>' +
                '<td class="px-4 py-3 text-gray-500">'+(r.end ? CRM.esc(r.end) : '<em class="text-gray-400">Ongoing</em>')+'</td></tr>';
        });
        html += '</tbody></table></div>';
        $('#assign-table-box').html(html);
    });
}

loadAssignments();

$('#assign-form').on('submit', function(e) {
    e.preventDefault();
    CRM.submit_form($(this), function(){
        loadAssignments();
        $('#assign-form')[0].reset();
        CRM.init_plugins();
    });
});
</script>
