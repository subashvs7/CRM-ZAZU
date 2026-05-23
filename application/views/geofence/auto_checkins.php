<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
    <div class="flex items-center gap-3">
        <div class="w-10 h-10 bg-cyan-100 rounded-xl flex items-center justify-center flex-shrink-0">
            <i class="fa fa-check-circle text-cyan-600 text-lg"></i>
        </div>
        <div>
            <h1 class="text-xl font-bold text-gray-800">Auto Check-ins</h1>
            <nav class="text-xs text-gray-400 flex items-center gap-1 mt-0.5">
                <a href="<?= base_url('geofence') ?>" class="hover:text-blue-600 transition-colors">Geofence</a>
                <i class="fa fa-angle-right text-[10px]"></i>
                <span class="text-gray-600">Auto Check-ins</span>
            </nav>
        </div>
    </div>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-gray-100">
    <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-2">
        <div class="w-8 h-8 bg-cyan-100 rounded-lg flex items-center justify-center">
            <i class="fa fa-check-circle text-cyan-600 text-sm"></i>
        </div>
        <div>
            <h3 class="text-sm font-bold text-gray-800">Auto Check-in Log</h3>
            <p class="text-xs text-gray-400 mt-0.5">Check-ins triggered automatically by geofence entry</p>
        </div>
    </div>
    <div class="p-4 overflow-x-auto">
        <table id="checkins-table" class="w-full">
            <thead><tr><th>#</th><th>Staff</th><th>Customer</th><th>Zone</th><th>Check-in Time</th></tr></thead>
            <tbody></tbody>
        </table>
    </div>
</div>

<script>
$('#checkins-table').DataTable({
    processing: true, serverSide: true,
    ajax: { url: BASE_URL+'geofence/checkins_dt' },
    columns: [{data:0},{data:1},{data:2},{data:3},{data:4}],
    order: [[0,'desc']]
});
</script>
