<!-- Page Header -->
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
    <div class="flex items-center gap-3">
        <div class="w-10 h-10 bg-green-100 rounded-xl flex items-center justify-center flex-shrink-0">
            <i class="fa fa-bar-chart text-green-600 text-lg"></i>
        </div>
        <div>
            <h1 class="text-xl font-bold text-gray-800">Leave Balances</h1>
            <nav class="text-xs text-gray-400 flex items-center gap-1 mt-0.5">
                <a href="<?= base_url('dashboard') ?>" class="hover:text-blue-600 transition-colors">Home</a>
                <i class="fa fa-angle-right text-[10px]"></i>
                <a href="<?= base_url('leave') ?>" class="hover:text-blue-600 transition-colors">Leave</a>
                <i class="fa fa-angle-right text-[10px]"></i>
                <span class="text-gray-600">Balances</span>
            </nav>
        </div>
    </div>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-gray-100">
    <div class="px-6 py-4 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h3 class="text-sm font-bold text-gray-800">Leave Balance Report</h3>
            <p class="text-xs text-gray-400 mt-0.5">View used, pending, and available leave days</p>
        </div>
        <div class="flex items-center gap-2">
            <?php if($is_manager): ?>
            <select id="bal-user" class="px-3 py-2 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                <?php foreach($users as $u): ?>
                <option value="<?= $u['id'] ?>"><?= esc_html($u['name']) ?></option>
                <?php endforeach; ?>
            </select>
            <?php endif; ?>
            <select id="bal-year" class="px-3 py-2 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                <?php for($y = date('Y'); $y >= date('Y') - 2; $y--): ?>
                <option <?= $y == date('Y') ? 'selected' : '' ?>><?= $y ?></option>
                <?php endfor; ?>
            </select>
            <button id="btn-load-balances"
                    class="inline-flex items-center gap-1.5 px-4 py-2 text-sm bg-blue-600 text-white font-semibold rounded-xl hover:bg-blue-700 transition-colors">
                <i class="fa fa-search"></i> Load
            </button>
        </div>
    </div>
    <div class="p-5" id="balances-body">
        <div class="flex flex-col items-center justify-center py-14 text-center">
            <div class="w-12 h-12 bg-gray-100 rounded-full flex items-center justify-center mb-3">
                <i class="fa fa-bar-chart text-gray-300 text-xl"></i>
            </div>
            <p class="text-sm text-gray-400">Select user and year, then click Load</p>
        </div>
    </div>
</div>

<script>
$('#btn-load-balances').click(function() {
    var uid = $('#bal-user').val() || '', year = $('#bal-year').val();
    $('#balances-body').html('<div class="text-center py-10 text-gray-300"><i class="fa fa-spinner fa-spin fa-2x"></i></div>');
    $.getJSON(BASE_URL + 'leave/balances_data', {user_id: uid, year: year}, function(res) {
        if (!res.data || !res.data.length) {
            $('#balances-body').html('<div class="py-12 text-center"><p class="text-sm text-gray-400">No balance data found</p></div>');
            return;
        }
        var html = '<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">';
        $.each(res.data, function(i, r) {
            var avail = r.total_days - r.used_days - r.pending_days;
            var pct = r.total_days > 0 ? Math.round(r.used_days / r.total_days * 100) : 0;
            html += '<div class="bg-gray-50 rounded-2xl p-5 border border-gray-100">' +
                '<p class="text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-3">' + CRM.esc(r.leave_type_name) + '</p>' +
                '<div class="flex items-end gap-1 mb-1">' +
                '<p class="text-3xl font-extrabold text-blue-600 leading-none">' + Math.max(0, avail) + '</p>' +
                '<p class="text-xs text-gray-400 mb-0.5">/ ' + r.total_days + ' days available</p>' +
                '</div>' +
                '<div class="bg-gray-200 rounded-full h-2 mt-3 mb-3">' +
                '<div class="h-2 rounded-full bg-blue-500 transition-all" style="width:' + pct + '%"></div>' +
                '</div>' +
                '<div class="grid grid-cols-3 gap-2 text-center">' +
                '<div class="bg-white rounded-xl p-2 border border-gray-100">' +
                '<p class="text-lg font-bold text-gray-700">' + r.total_days + '</p>' +
                '<p class="text-[10px] text-gray-400 uppercase font-semibold">Total</p>' +
                '</div>' +
                '<div class="bg-amber-50 rounded-xl p-2 border border-amber-100">' +
                '<p class="text-lg font-bold text-amber-600">' + r.used_days + '</p>' +
                '<p class="text-[10px] text-amber-500 uppercase font-semibold">Used</p>' +
                '</div>' +
                '<div class="bg-cyan-50 rounded-xl p-2 border border-cyan-100">' +
                '<p class="text-lg font-bold text-cyan-600">' + r.pending_days + '</p>' +
                '<p class="text-[10px] text-cyan-500 uppercase font-semibold">Pending</p>' +
                '</div>' +
                '</div></div>';
        });
        html += '</div>';
        $('#balances-body').html(html);
    });
});
</script>
