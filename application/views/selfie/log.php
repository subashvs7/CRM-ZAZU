<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
    <div class="flex items-center gap-3">
        <div class="w-10 h-10 bg-purple-100 rounded-xl flex items-center justify-center flex-shrink-0">
            <i class="fa fa-camera text-purple-600 text-lg"></i>
        </div>
        <div>
            <h1 class="text-xl font-bold text-gray-800">Selfie Log</h1>
            <nav class="text-xs text-gray-400 flex items-center gap-1 mt-0.5">
                <a href="<?= base_url('dashboard') ?>" class="hover:text-blue-600 transition-colors">Home</a>
                <i class="fa fa-angle-right text-[10px]"></i>
                <span class="text-gray-600">Selfie Log</span>
            </nav>
        </div>
    </div>
    <div class="flex items-center gap-2 self-start sm:self-auto">
        <a href="<?= base_url('selfie/mismatches') ?>" class="inline-flex items-center gap-1.5 px-3 py-2 text-sm bg-white border border-gray-200 text-gray-700 font-medium rounded-xl hover:bg-gray-50 transition-colors">
            <i class="fa fa-exclamation-triangle text-red-500"></i> Mismatches
        </a>
        <?php if($is_admin): ?>
        <a href="<?= base_url('selfie/settings') ?>" class="inline-flex items-center gap-1.5 px-3 py-2 text-sm bg-white border border-gray-200 text-gray-700 font-medium rounded-xl hover:bg-gray-50 transition-colors">
            <i class="fa fa-cog text-gray-500"></i> Settings
        </a>
        <?php endif; ?>
    </div>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-gray-100">
    <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-2">
        <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center">
            <i class="fa fa-camera text-purple-600 text-sm"></i>
        </div>
        <div>
            <h3 class="text-sm font-bold text-gray-800">Selfie Verification Log</h3>
            <p class="text-xs text-gray-400 mt-0.5">Face match records for attendance check-ins</p>
        </div>
    </div>
    <div class="p-4 overflow-x-auto">
        <table id="selfie-table" class="w-full">
            <thead><tr><th>#</th><th>Staff</th><th>Date</th><th>Punch Time</th><th>Photo</th><th>Verified</th><th>Confidence</th></tr></thead>
            <tbody></tbody>
        </table>
    </div>
</div>

<script>
$('#selfie-table').DataTable({
    processing: true, serverSide: true,
    ajax: { url: BASE_URL+'selfie/log_dt' },
    columns: [{data:0},{data:1},{data:2},{data:3},{data:4},{data:5},{data:6}],
    order: [[2,'desc']]
});
</script>
