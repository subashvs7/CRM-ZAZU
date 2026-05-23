<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
    <div class="flex items-center gap-3">
        <div class="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center flex-shrink-0">
            <i class="fa fa-satellite text-blue-600 text-lg"></i>
        </div>
        <div>
            <h1 class="text-xl font-bold text-gray-800">GPS Ping Status</h1>
            <nav class="text-xs text-gray-400 flex items-center gap-1 mt-0.5">
                <a href="<?= base_url('tracking/live') ?>" class="hover:text-blue-600 transition-colors">Tracking</a>
                <i class="fa fa-angle-right text-[10px]"></i>
                <span class="text-gray-600">Ping Status</span>
            </nav>
        </div>
    </div>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-gray-100">
    <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-2">
        <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
            <i class="fa fa-satellite text-blue-600 text-sm"></i>
        </div>
        <div>
            <h3 class="text-sm font-bold text-gray-800">Recent GPS Pings</h3>
            <p class="text-xs text-gray-400 mt-0.5">Latest location signals from field staff</p>
        </div>
    </div>
    <div class="p-4 overflow-x-auto">
        <table id="ping-table" class="w-full">
            <thead><tr><th>#</th><th>Staff</th><th>Lat, Lng</th><th>Accuracy</th><th>Speed</th><th>Battery</th><th>Time</th></tr></thead>
            <tbody></tbody>
        </table>
    </div>
</div>

<script>
$('#ping-table').DataTable({
    processing: true, serverSide: true,
    ajax: { url: BASE_URL+'tracking/ping_dt' },
    columns: [{data:0},{data:1},{data:2},{data:3},{data:4},{data:5},{data:6}],
    order: [[0,'desc']]
});
</script>
