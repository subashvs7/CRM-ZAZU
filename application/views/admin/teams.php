<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
    <div class="flex items-center gap-3">
        <div class="w-10 h-10 bg-purple-100 rounded-xl flex items-center justify-center flex-shrink-0">
            <i class="fa fa-sitemap text-purple-600 text-lg"></i>
        </div>
        <div>
            <h1 class="text-xl font-bold text-gray-800">Teams</h1>
            <nav class="text-xs text-gray-400 flex items-center gap-1 mt-0.5">
                <a href="<?= base_url('dashboard') ?>" class="hover:text-blue-600 transition-colors">Home</a>
                <i class="fa fa-angle-right text-[10px]"></i>
                <span class="text-gray-600">Teams</span>
            </nav>
        </div>
    </div>
</div>

<?php $this->load->view('partials/_status_tabs', get_defined_vars()); ?>

<div class="bg-white rounded-xl shadow-sm border border-gray-100">
    <div class="px-5 py-3.5 border-b border-gray-100 flex items-center justify-between">
        <h3 class="text-sm font-semibold text-gray-700 flex items-center gap-2"><i class="fa fa-group text-blue-500"></i> Teams</h3>
        <button id="btn-add-team" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
            <i class="fa fa-plus"></i> Add Team
        </button>
    </div>
    <div class="p-4 overflow-x-auto">
        <table id="teams-table" class="w-full">
            <thead><tr><th>#</th><th>Name</th><th>Manager</th><th>Territory</th><th>Members</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody></tbody>
        </table>
    </div>
</div>

<!-- Team Modal -->
<div class="modal fade" id="team-modal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Team</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <form id="team-form">
                    <input type="hidden" name="<?= $csrf_name ?>" value="<?= $csrf_hash ?>">
                    <input type="hidden" name="id" id="team-id" value="0">
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Name *</label>
                        <input type="text" name="name" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Manager</label>
                        <select name="manager_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 select2">
                            <option value="">-- No Manager --</option>
                            <?php foreach($managers as $m): ?><option value="<?= $m['id'] ?>"><?= esc_html($m['name']) ?></option><?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Territory</label>
                        <input type="text" name="territory" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                        <textarea name="description" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" rows="3"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="px-4 py-2 text-sm text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-50" data-dismiss="modal">Cancel</button>
                <button class="px-4 py-2 text-sm bg-blue-600 text-white rounded-lg hover:bg-blue-700" id="btn-save-team"><i class="fa fa-save mr-1"></i> Save</button>
            </div>
        </div>
    </div>
</div>

<script>
var teamTable = $('#teams-table').DataTable({
    processing: true, serverSide: true,
    ajax: { url: BASE_URL+'admin/teams_dt', data: function(d){ d.status_filter = window.currentStatusFilter||''; } },
    columns: [{data:0},{data:1},{data:2},{data:3},{data:4},{data:5},{data:6,orderable:false}],
    order: [[0,'desc']]
});
window.mainTable = teamTable;

$('#btn-add-team').click(function(){
    $('#team-form')[0].reset();
    $('#team-id').val(0);
    CRM.clear_errors($('#team-form'));
    $('#team-modal .modal-title').text('Add Team');
    $('#team-modal').modal('show');
    CRM.init_plugins($('#team-modal'));
});

$('#btn-save-team').click(function(){
    var $btn = $(this); CRM.btn_loading($btn);
    $.ajax({
        url: BASE_URL+'admin/save_team', method:'POST',
        data: new FormData($('#team-form')[0]), processData:false, contentType:false,
        success: function(res){
            if(res.status==='success'){ CRM.toast('success',res.message); $('#team-modal').modal('hide'); teamTable.ajax.reload(null,false); }
            else { CRM.show_errors($('#team-form'),res.errors||{}); CRM.toast('error',res.message); }
        },
        complete: function(){ CRM.btn_reset($btn); }
    });
});

$(document).on('click', '.btn-edit-team', function(){
    var id = $(this).data('id');
    $.get(BASE_URL+'admin/get_team/'+id, function(res){
        if (res.status !== 'success') { CRM.toast('error', res.message||'Failed to load.'); return; }
        var d = res.data;
        $('#team-form')[0].reset();
        CRM.clear_errors($('#team-form'));
        $('#team-id').val(d.id);
        $('#team-form [name=name]').val(d.name||'');
        $('#team-form [name=territory]').val(d.territory||'');
        $('#team-form [name=description]').val(d.description||'');
        $('#team-form [name=manager_id]').val(d.manager_id||'').trigger('change');
        $('#team-modal .modal-title').text('Edit Team');
        $('#team-modal').modal('show');
        CRM.init_plugins($('#team-modal'));
    });
});

$(document).on('click', '.btn-team-status', function(){
    var id=$(this).data('id'), action=$(this).data('action');
    if(action==='delete'){ if(!confirm('Delete this team?')) return; }
    $.post(BASE_URL+'admin/team_status', {id:id, action:action, [CI3_CSRF_NAME]:CI3_CSRF_HASH}, function(res){
        if(res.status==='success'){ CRM.toast('success',res.message); teamTable.ajax.reload(null,false); }
    });
});
</script>
