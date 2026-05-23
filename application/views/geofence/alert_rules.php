<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
    <div class="flex items-center gap-3">
        <div class="w-10 h-10 bg-amber-100 rounded-xl flex items-center justify-center flex-shrink-0">
            <i class="fa fa-bell text-amber-600 text-lg"></i>
        </div>
        <div>
            <h1 class="text-xl font-bold text-gray-800">Alert Rules</h1>
            <nav class="text-xs text-gray-400 flex items-center gap-1 mt-0.5">
                <a href="<?= base_url('geofence') ?>" class="hover:text-blue-600 transition-colors">Geofence</a>
                <i class="fa fa-angle-right text-[10px]"></i>
                <span class="text-gray-600">Alert Rules</span>
            </nav>
        </div>
    </div>
    <button id="btn-add-rule" class="inline-flex items-center gap-1.5 px-4 py-2 text-sm bg-amber-500 text-white font-semibold rounded-xl hover:bg-amber-600 transition-colors shadow-sm self-start sm:self-auto">
        <i class="fa fa-plus"></i> Add Rule
    </button>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-gray-100">
    <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-2">
        <div class="w-8 h-8 bg-amber-100 rounded-lg flex items-center justify-center">
            <i class="fa fa-bell text-amber-600 text-sm"></i>
        </div>
        <div>
            <h3 class="text-sm font-bold text-gray-800">Alert Rules</h3>
            <p class="text-xs text-gray-400 mt-0.5">Trigger notifications on geofence events</p>
        </div>
    </div>
    <div class="p-4 overflow-x-auto">
        <table id="rules-table" class="w-full">
            <thead><tr><th>#</th><th>Zone</th><th>Event</th><th>Notify Roles</th><th>Cooldown</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody></tbody>
        </table>
    </div>
</div>

<!-- Rule Modal -->
<div class="modal fade" id="rule-modal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Alert Rule</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <form id="rule-form">
                    <input type="hidden" name="<?= $csrf_name ?>" value="<?= $csrf_hash ?>">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">Zone <span class="normal-case font-normal text-gray-400">(leave blank for all)</span></label>
                            <select name="geofence_zone_id" class="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 select2">
                                <option value="">-- All Zones --</option>
                                <?php foreach($zones as $z): ?><option value="<?= $z['id'] ?>"><?= esc_html($z['name']) ?></option><?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">Event Type</label>
                            <select name="event_type" class="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-amber-500">
                                <option>enter</option><option>exit</option><option>offline</option><option>speeding</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-2">Notify Roles</label>
                            <div class="space-y-2">
                                <label class="flex items-center gap-2 text-sm text-gray-700 cursor-pointer">
                                    <input type="checkbox" name="notify_roles[]" value="admin" class="rounded"> Admin
                                </label>
                                <label class="flex items-center gap-2 text-sm text-gray-700 cursor-pointer">
                                    <input type="checkbox" name="notify_roles[]" value="manager" class="rounded"> Manager
                                </label>
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">Cooldown <span class="normal-case font-normal text-gray-400">(minutes)</span></label>
                            <input type="number" name="cooldown_minutes" class="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-amber-500" value="30">
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="px-4 py-2 text-sm text-gray-700 border border-gray-300 rounded-xl hover:bg-gray-50" data-dismiss="modal">Cancel</button>
                <button class="px-4 py-2 text-sm bg-amber-500 text-white rounded-xl hover:bg-amber-600" id="btn-save-rule"><i class="fa fa-save mr-1"></i> Save</button>
            </div>
        </div>
    </div>
</div>

<script>
var rulesTable = $('#rules-table').DataTable({
    processing: true, serverSide: true,
    ajax: { url: BASE_URL+'geofence/rules_dt' },
    columns: [{data:0},{data:1},{data:2},{data:3},{data:4},{data:5},{data:6,orderable:false}],
    order: [[0,'desc']]
});

$('#btn-add-rule').click(function(){
    $('#rule-form')[0].reset();
    $('#rule-modal').modal('show');
});

$('#btn-save-rule').click(function(){
    $.ajax({
        url: BASE_URL+'geofence/save_rule', method: 'POST',
        data: new FormData($('#rule-form')[0]), processData: false, contentType: false,
        success: function(res){
            if(res.status==='success'){ CRM.toast('success',res.message); $('#rule-modal').modal('hide'); rulesTable.ajax.reload(null,false); }
            else CRM.toast('error', res.message);
        }
    });
});

$(document).on('click', '.btn-rule-status', function(){
    $.post(BASE_URL+'geofence/rule_status', {id:$(this).data('id'), action:$(this).data('action'), [CI3_CSRF_NAME]:CI3_CSRF_HASH}, function(res){
        if(res.status==='success'){ CRM.toast('success',res.message); rulesTable.ajax.reload(null,false); }
    });
});
</script>
