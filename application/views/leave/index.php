<!-- Page Header -->
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
    <div class="flex items-center gap-3">
        <div class="w-10 h-10 bg-violet-100 rounded-xl flex items-center justify-center flex-shrink-0">
            <i class="fa fa-plane text-violet-600 text-lg"></i>
        </div>
        <div>
            <h1 class="text-xl font-bold text-gray-800">Leave</h1>
            <nav class="text-xs text-gray-400 flex items-center gap-1 mt-0.5">
                <a href="<?= base_url('dashboard') ?>" class="hover:text-blue-600 transition-colors">Home</a>
                <i class="fa fa-angle-right text-[10px]"></i>
                <span class="text-gray-600">Leave</span>
            </nav>
        </div>
    </div>
    <div class="flex items-center gap-2 self-start sm:self-auto">
        <?php if($is_manager): ?>
        <a href="<?= base_url('leave/approval') ?>"
           class="inline-flex items-center gap-1.5 px-3 py-2 text-sm bg-amber-500 text-white font-medium rounded-xl hover:bg-amber-600 transition-colors">
            <i class="fa fa-clock-o"></i> Approval Queue
        </a>
        <?php endif; ?>
        <a href="<?= base_url('leave/apply') ?>"
           class="inline-flex items-center gap-1.5 px-4 py-2 text-sm bg-violet-600 text-white font-semibold rounded-xl hover:bg-violet-700 transition-colors shadow-sm">
            <i class="fa fa-plus"></i> Apply Leave
        </a>
    </div>
</div>

<!-- Balance Cards -->
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
<?php foreach($balances as $b):
    $avail = $b['total_days'] - $b['used_days'] - $b['pending_days'];
    $pct   = $b['total_days'] > 0 ? round($b['used_days'] / $b['total_days'] * 100) : 0;
    $avail_pct = $b['total_days'] > 0 ? round($avail / $b['total_days'] * 100) : 0;
?>
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
    <p class="text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-3"><?= esc_html($b['leave_type_name']) ?></p>
    <div class="flex items-end gap-1 mb-1">
        <p class="text-3xl font-extrabold text-blue-600 leading-none"><?= max(0, $avail) ?></p>
        <p class="text-xs text-gray-400 mb-0.5">/ <?= $b['total_days'] ?> days</p>
    </div>
    <p class="text-xs text-gray-400 mb-2">Available</p>
    <div class="bg-gray-100 rounded-full h-1.5">
        <div class="h-1.5 rounded-full bg-blue-500 transition-all" style="width:<?= $avail_pct ?>%"></div>
    </div>
    <?php if($b['pending_days'] > 0): ?>
    <p class="text-[11px] text-amber-600 mt-1.5"><i class="fa fa-clock-o mr-1"></i><?= $b['pending_days'] ?> pending</p>
    <?php endif; ?>
</div>
<?php endforeach; ?>
</div>

<!-- Status Tabs -->
<?php $this->load->view('partials/_status_tabs', get_defined_vars()); ?>

<!-- Leave Table -->
<div class="bg-white rounded-2xl shadow-sm border border-gray-100">
    <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
        <div>
            <h3 class="text-sm font-bold text-gray-800">My Leave Requests</h3>
            <p class="text-xs text-gray-400 mt-0.5">All submitted leave applications</p>
        </div>
        <span class="px-2.5 py-1 bg-violet-50 text-violet-600 text-xs font-semibold rounded-lg">
            <i class="fa fa-plane mr-1"></i> Requests
        </span>
    </div>
    <div class="p-4 overflow-x-auto">
        <table id="leave-table" class="w-full">
            <thead>
                <tr>
                    <th>#</th><th>Staff</th><th>Type</th><th>From</th>
                    <th>To</th><th>Days</th><th>Status</th><th>Applied</th><th>Actions</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>

<script>
var leaveTable = $('#leave-table').DataTable({
    processing: true,
    serverSide: true,
    ajax: {
        url: BASE_URL + 'leave/datatable',
        data: function(d) { d.status_filter = window.currentStatusFilter || ''; }
    },
    columns: [
        {data: 0}, {data: 1}, {data: 2}, {data: 3},
        {data: 4}, {data: 5}, {data: 6}, {data: 7},
        {data: 8, orderable: false}
    ],
    order: [[0, 'desc']]
});
window.mainTable = leaveTable;

$(document).on('click', '.btn-cancel-leave', function() {
    if (!confirm('Cancel this leave request?')) return;
    $.post(BASE_URL + 'leave/cancel', {id: $(this).data('id'), [CI3_CSRF_NAME]: CI3_CSRF_HASH}, function(res) {
        if (res.status === 'success') {
            CRM.toast('success', res.message);
            leaveTable.ajax.reload(null, false);
        }
    });
});
</script>
