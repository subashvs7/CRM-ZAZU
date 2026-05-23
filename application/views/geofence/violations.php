<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
    <div class="flex items-center gap-3">
        <div class="w-10 h-10 bg-red-100 rounded-xl flex items-center justify-center flex-shrink-0">
            <i class="fa fa-exclamation-triangle text-red-600 text-lg"></i>
        </div>
        <div>
            <h1 class="text-xl font-bold text-gray-800">Geo Violations &amp; Alerts</h1>
            <nav class="text-xs text-gray-400 flex items-center gap-1 mt-0.5">
                <a href="<?= base_url('geofence') ?>" class="hover:text-blue-600 transition-colors">Geofence</a>
                <i class="fa fa-angle-right text-[10px]"></i>
                <span class="text-gray-600">Violations</span>
            </nav>
        </div>
    </div>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-gray-100">
    <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-2">
        <div class="w-8 h-8 bg-red-100 rounded-lg flex items-center justify-center">
            <i class="fa fa-exclamation-triangle text-red-600 text-sm"></i>
        </div>
        <div>
            <h3 class="text-sm font-bold text-gray-800">Geo Alerts</h3>
            <p class="text-xs text-gray-400 mt-0.5">Zone entry, exit, and boundary violations</p>
        </div>
    </div>
    <div class="p-4 overflow-x-auto">
        <table id="violations-table" class="w-full">
            <thead><tr><th>#</th><th>Zone</th><th>Staff</th><th>Type</th><th>Triggered</th><th>Resolved</th><th>Actions</th></tr></thead>
            <tbody></tbody>
        </table>
    </div>
</div>

<script>
var violTable = $('#violations-table').DataTable({
    processing: true, serverSide: true,
    ajax: { url: BASE_URL+'geofence/violations_dt' },
    columns: [{data:0},{data:1},{data:2},{data:3},{data:4},{data:5},{data:6,orderable:false}],
    order: [[0,'desc']]
});

$(document).on('click', '.btn-resolve-alert', function(){
    $.post(BASE_URL+'geofence/resolve_alert', {id:$(this).data('id'), [CI3_CSRF_NAME]:CI3_CSRF_HASH}, function(res){
        if(res.status==='success'){ CRM.toast('success',res.message); violTable.ajax.reload(null,false); }
    });
});
</script>
