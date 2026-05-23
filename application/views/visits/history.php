<!-- Page Header -->
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
    <div class="flex items-center gap-3">
        <div class="w-10 h-10 bg-indigo-100 rounded-xl flex items-center justify-center flex-shrink-0">
            <i class="fa fa-history text-indigo-600 text-lg"></i>
        </div>
        <div>
            <h1 class="text-xl font-bold text-gray-800">Visit History</h1>
            <nav class="text-xs text-gray-400 flex items-center gap-1 mt-0.5">
                <a href="<?= base_url('dashboard') ?>" class="hover:text-blue-600 transition-colors">Home</a>
                <i class="fa fa-angle-right text-[10px]"></i>
                <a href="<?= base_url('visits') ?>" class="hover:text-blue-600 transition-colors">Visits</a>
                <i class="fa fa-angle-right text-[10px]"></i>
                <span class="text-gray-600">History</span>
            </nav>
        </div>
    </div>
    <a href="<?= base_url('visits') ?>"
       class="inline-flex items-center gap-1.5 px-4 py-2 text-sm bg-white border border-gray-200 text-gray-700 font-medium rounded-xl hover:bg-gray-50 transition-colors self-start sm:self-auto">
        <i class="fa fa-calendar text-gray-500"></i> Visit Plans
    </a>
</div>

<!-- Table -->
<div class="bg-white rounded-2xl shadow-sm border border-gray-100">
    <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
        <div>
            <h3 class="text-sm font-bold text-gray-800">Check-in / Check-out Log</h3>
            <p class="text-xs text-gray-400 mt-0.5">Complete visit history with timestamps</p>
        </div>
        <span class="px-2.5 py-1 bg-indigo-50 text-indigo-600 text-xs font-semibold rounded-lg">
            <i class="fa fa-history mr-1"></i> Log
        </span>
    </div>
    <div class="p-4 overflow-x-auto">
        <table id="history-table" class="w-full">
            <thead>
                <tr>
                    <th>#</th><th>Check In</th><th>Check Out</th><th>Customer</th>
                    <th>Staff</th><th>Distance</th><th>Auto</th><th>Status</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>

<script>
$('#history-table').DataTable({
    processing: true,
    serverSide: true,
    ajax: { url: BASE_URL + 'visits/datatable', data: function(d) { d.status_filter = ''; } },
    columns: [
        {data: 0}, {data: 1}, {data: 2}, {data: 3},
        {data: 4}, {data: 5}, {data: 6}, {data: 7}
    ],
    order: [[0, 'desc']]
});
</script>
