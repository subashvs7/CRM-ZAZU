<!-- Page Header -->
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
    <div class="flex items-center gap-3">
        <div class="w-10 h-10 bg-amber-100 rounded-xl flex items-center justify-center flex-shrink-0">
            <i class="fa fa-edit text-amber-600 text-lg"></i>
        </div>
        <div>
            <h1 class="text-xl font-bold text-gray-800">Attendance Corrections</h1>
            <nav class="text-xs text-gray-400 flex items-center gap-1 mt-0.5">
                <a href="<?= base_url('dashboard') ?>" class="hover:text-blue-600 transition-colors">Home</a>
                <i class="fa fa-angle-right text-[10px]"></i>
                <a href="<?= base_url('attendance') ?>" class="hover:text-blue-600 transition-colors">Attendance</a>
                <i class="fa fa-angle-right text-[10px]"></i>
                <span class="text-gray-600">Corrections</span>
            </nav>
        </div>
    </div>
</div>

<!-- Info Banner -->
<div class="flex items-start gap-3 p-4 bg-amber-50 border border-amber-200 rounded-2xl mb-5">
    <div class="w-8 h-8 bg-amber-100 rounded-lg flex items-center justify-center flex-shrink-0 mt-0.5">
        <i class="fa fa-info-circle text-amber-600 text-sm"></i>
    </div>
    <div>
        <p class="text-sm font-semibold text-amber-800">Pending Correction Requests</p>
        <p class="text-xs text-amber-600 mt-0.5">Review and approve or reject attendance correction requests from staff.</p>
    </div>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-gray-100">
    <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
        <div>
            <h3 class="text-sm font-bold text-gray-800">Correction Requests</h3>
            <p class="text-xs text-gray-400 mt-0.5">Attendance edits awaiting approval</p>
        </div>
        <span class="px-2.5 py-1 bg-amber-50 text-amber-600 text-xs font-semibold rounded-lg">
            <i class="fa fa-edit mr-1"></i> Pending
        </span>
    </div>
    <div class="p-4 overflow-x-auto">
        <table id="corr-table" class="w-full">
            <thead>
                <tr>
                    <th>#</th><th>Staff</th><th>Date</th><th>In</th><th>Out</th><th>Status</th><th>Actions</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>

<script>
var corrTable = $('#corr-table').DataTable({
    processing: true,
    serverSide: true,
    ajax: { url: BASE_URL + 'attendance/corrections_dt' },
    columns: [
        {data: 0}, {data: 1}, {data: 2}, {data: 3},
        {data: 4}, {data: 5}, {data: 6, orderable: false}
    ],
    order: [[2, 'desc']]
});

$(document).on('click', '.btn-approve-corr', function() {
    var id = $(this).data('id');
    $.post(BASE_URL + 'attendance/approve_correction', {id: id, [CI3_CSRF_NAME]: CI3_CSRF_HASH}, function(res) {
        if (res.status === 'success') {
            CRM.toast('success', res.message);
            corrTable.ajax.reload(null, false);
        }
    });
});
</script>
