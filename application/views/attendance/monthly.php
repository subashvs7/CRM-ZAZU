<!-- Page Header -->
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
    <div class="flex items-center gap-3">
        <div class="w-10 h-10 bg-indigo-100 rounded-xl flex items-center justify-center flex-shrink-0">
            <i class="fa fa-calendar text-indigo-600 text-lg"></i>
        </div>
        <div>
            <h1 class="text-xl font-bold text-gray-800">Monthly Attendance</h1>
            <nav class="text-xs text-gray-400 flex items-center gap-1 mt-0.5">
                <a href="<?= base_url('dashboard') ?>" class="hover:text-blue-600 transition-colors">Home</a>
                <i class="fa fa-angle-right text-[10px]"></i>
                <a href="<?= base_url('attendance') ?>" class="hover:text-blue-600 transition-colors">Attendance</a>
                <i class="fa fa-angle-right text-[10px]"></i>
                <span class="text-gray-600">Monthly</span>
            </nav>
        </div>
    </div>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-gray-100">
    <div class="px-6 py-4 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h3 class="text-sm font-bold text-gray-800">Monthly View</h3>
            <p class="text-xs text-gray-400 mt-0.5">Select month to view attendance summary</p>
        </div>
        <div class="flex items-center gap-2">
            <select id="att-year" class="px-3 py-2 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                <?php for($y = date('Y'); $y >= date('Y') - 2; $y--): ?>
                <option <?= $y == date('Y') ? 'selected' : '' ?>><?= $y ?></option>
                <?php endfor; ?>
            </select>
            <select id="att-month" class="px-3 py-2 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                <?php for($m = 1; $m <= 12; $m++): ?>
                <option value="<?= $m ?>" <?= $m == date('m') ? 'selected' : '' ?>>
                    <?= date('F', mktime(0, 0, 0, $m, 1)) ?>
                </option>
                <?php endfor; ?>
            </select>
            <button id="btn-load-monthly"
                    class="inline-flex items-center gap-1.5 px-4 py-2 text-sm bg-blue-600 text-white font-semibold rounded-xl hover:bg-blue-700 transition-colors">
                <i class="fa fa-search"></i> Load
            </button>
        </div>
    </div>
    <div class="p-5" id="monthly-att-body">
        <div class="flex flex-col items-center justify-center py-14 text-center">
            <div class="w-12 h-12 bg-gray-100 rounded-full flex items-center justify-center mb-3">
                <i class="fa fa-calendar-o text-gray-300 text-xl"></i>
            </div>
            <p class="text-sm text-gray-400">Select year and month, then click Load</p>
        </div>
    </div>
</div>

<script>
$('#btn-load-monthly').click(function() {
    var year = $('#att-year').val(), month = $('#att-month').val();
    $('#monthly-att-body').html('<div class="text-center py-10 text-gray-300"><i class="fa fa-spinner fa-spin fa-2x"></i></div>');
    $.getJSON(BASE_URL + 'attendance/monthly_data', {year: year, month: month}, function(res) {
        if (!res.data || !res.data.length) {
            $('#monthly-att-body').html('<div class="py-12 text-center"><p class="text-sm text-gray-400">No records found for this period</p></div>');
            return;
        }
        var html = '<div class="overflow-x-auto"><table class="w-full text-sm">' +
            '<thead><tr class="bg-gray-50">' +
            '<th class="px-5 py-3 text-left text-[11px] font-bold text-gray-500 uppercase tracking-wide">Date</th>' +
            '<th class="px-5 py-3 text-left text-[11px] font-bold text-gray-500 uppercase tracking-wide">Punch In</th>' +
            '<th class="px-5 py-3 text-left text-[11px] font-bold text-gray-500 uppercase tracking-wide">Punch Out</th>' +
            '<th class="px-5 py-3 text-left text-[11px] font-bold text-gray-500 uppercase tracking-wide">Status</th>' +
            '<th class="px-5 py-3 text-left text-[11px] font-bold text-gray-500 uppercase tracking-wide">Hours</th>' +
            '</tr></thead><tbody class="divide-y divide-gray-50">';
        $.each(res.data, function(i, r) {
            html += '<tr class="hover:bg-gray-50">' +
                '<td class="px-5 py-3 font-medium text-gray-800">' + r.date + '</td>' +
                '<td class="px-5 py-3 text-gray-600">' + (r.punch_in_at ? r.punch_in_at.substr(11, 5) : '<span class="text-gray-300">—</span>') + '</td>' +
                '<td class="px-5 py-3 text-gray-600">' + (r.punch_out_at ? r.punch_out_at.substr(11, 5) : '<span class="text-gray-300">—</span>') + '</td>' +
                '<td class="px-5 py-3">' + CRM.att_badge(r.attendance_status) + '</td>' +
                '<td class="px-5 py-3 font-semibold text-gray-700">' + (r.working_hours ? r.working_hours + 'h' : '—') + '</td>' +
                '</tr>';
        });
        html += '</tbody></table></div>';
        $('#monthly-att-body').html(html);
    });
});
</script>
