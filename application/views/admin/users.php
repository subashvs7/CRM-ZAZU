<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
    <div class="flex items-center gap-3">
        <div class="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center flex-shrink-0">
            <i class="fa fa-users text-blue-600 text-lg"></i>
        </div>
        <div>
            <h1 class="text-xl font-bold text-gray-800">Users</h1>
            <nav class="text-xs text-gray-400 flex items-center gap-1 mt-0.5">
                <a href="<?= base_url('dashboard') ?>" class="hover:text-blue-600 transition-colors">Home</a>
                <i class="fa fa-angle-right text-[10px]"></i>
                <span class="text-gray-600">Users</span>
            </nav>
        </div>
    </div>
</div>

<?php $this->load->view('partials/_status_tabs', get_defined_vars()); ?>

<div class="bg-white rounded-2xl shadow-sm border border-gray-100">
    <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
        <div>
            <h3 class="text-sm font-bold text-gray-800">User List</h3>
            <p class="text-xs text-gray-400 mt-0.5">All system users and roles</p>
        </div>
        <div class="flex items-center gap-2">
            <button id="btn-view-credentials" class="inline-flex items-center gap-1.5 px-3 py-2 text-sm bg-white border border-gray-200 text-gray-700 font-medium rounded-xl hover:bg-gray-50 transition-colors">
                <i class="fa fa-key text-cyan-600"></i> Credentials
            </button>
            <button id="btn-add-user" class="inline-flex items-center gap-1.5 px-4 py-2 text-sm bg-blue-600 text-white font-semibold rounded-xl hover:bg-blue-700 transition-colors shadow-sm">
                <i class="fa fa-plus"></i> Add User
            </button>
        </div>
    </div>
    <div class="p-4 overflow-x-auto">
        <table id="users-table" class="w-full">
            <thead><tr><th>#</th><th>Name</th><th>Email</th><th>Role</th><th>Team</th><th>Status</th><th>Last Login</th><th>Actions</th></tr></thead>
            <tbody></tbody>
        </table>
    </div>
</div>

<!-- User Modal -->
<div class="modal fade" id="user-modal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">User</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <form id="user-form" method="POST">
                    <input type="hidden" name="<?= $csrf_name ?>" value="<?= $csrf_hash ?>">
                    <input type="hidden" name="id" id="user-id" value="0">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Name *</label>
                                <input type="text" name="name" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                            </div>
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Email *</label>
                                <input type="email" name="email" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                            </div>
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Password <span class="text-gray-400 font-normal">(leave blank to keep)</span></label>
                                <input type="password" name="password" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                                <input type="text" name="phone" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                        </div>
                        <div>
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Role *</label>
                                <select name="role" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                                    <option value="admin">Admin</option>
                                    <option value="manager">Manager</option>
                                    <option value="field_staff" selected>Field Staff</option>
                                </select>
                            </div>
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Team</label>
                                <select name="team_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 select2">
                                    <option value="">-- No Team --</option>
                                    <?php foreach($teams as $t): ?><option value="<?= $t['id'] ?>"><?= esc_html($t['name']) ?></option><?php endforeach; ?>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Permissions</label>
                                <div class="space-y-2">
                                    <label class="flex items-center gap-2 text-sm text-gray-700 cursor-pointer"><input type="checkbox" name="permissions[]" value="leads.manage" class="rounded"> Manage Leads</label>
                                    <label class="flex items-center gap-2 text-sm text-gray-700 cursor-pointer"><input type="checkbox" name="permissions[]" value="orders.approve" class="rounded"> Approve Orders</label>
                                    <label class="flex items-center gap-2 text-sm text-gray-700 cursor-pointer"><input type="checkbox" name="permissions[]" value="attendance.correct" class="rounded"> Correct Attendance</label>
                                    <label class="flex items-center gap-2 text-sm text-gray-700 cursor-pointer"><input type="checkbox" name="permissions[]" value="reports.view" class="rounded"> View Reports</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="px-4 py-2 text-sm text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-50" data-dismiss="modal">Cancel</button>
                <button class="px-4 py-2 text-sm bg-blue-600 text-white rounded-lg hover:bg-blue-700" id="btn-save-user"><i class="fa fa-save mr-1"></i> Save</button>
            </div>
        </div>
    </div>
</div>

<!-- Credentials Modal -->
<div class="modal fade" id="credentials-modal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title"><i class="fa fa-key mr-1"></i> User Credentials</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="flex items-start gap-2 p-3 mb-4 bg-amber-50 border border-amber-200 rounded-lg text-sm text-amber-800">
                    <i class="fa fa-lock mt-0.5"></i>
                    <span>Passwords are hashed and cannot be viewed. Use <strong>Reset Password</strong> to set a new one.</span>
                </div>
                <div id="credentials-body"><div class="text-center py-4 text-gray-400"><i class="fa fa-spinner fa-spin"></i></div></div>
            </div>
            <div class="modal-footer">
                <button class="px-4 py-2 text-sm text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-50" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
var userTable = $('#users-table').DataTable({
    processing: true, serverSide: true,
    ajax: { url: BASE_URL+'admin/users_dt', data: function(d){ d.status_filter = window.currentStatusFilter||''; } },
    columns: [{data:0},{data:1},{data:2},{data:3},{data:4},{data:5},{data:6},{data:7,orderable:false}],
    order: [[0,'desc']]
});
window.mainTable = userTable;

$('#btn-add-user').click(function(){
    $('#user-form')[0].reset();
    $('#user-id').val(0);
    $('#user-modal .modal-title').text('Add User');
    $('[name="permissions[]"]').prop('checked', false);
    CRM.clear_errors($('#user-form'));
    $('#user-modal').modal('show');
    CRM.init_plugins($('#user-modal'));
});

$(document).on('click', '.btn-edit-user', function(){
    var id = $(this).data('id');
    $.getJSON(BASE_URL+'admin/get_user/'+id, function(res){
        var u = res.data;
        $('#user-id').val(u.id);
        $('[name="name"]').val(u.name);
        $('[name="email"]').val(u.email);
        $('[name="role"]').val(u.role);
        $('[name="phone"]').val(u.phone||'');
        $('[name="team_id"]').val(u.team_id||'').trigger('change');
        $('[name="permissions[]"]').prop('checked', false);
        $.each(u.permissions||[], function(i,p){ $('[name="permissions[]"][value="'+p+'"]').prop('checked',true); });
        $('#user-modal .modal-title').text('Edit User');
        CRM.clear_errors($('#user-form'));
        $('#user-modal').modal('show');
        CRM.init_plugins($('#user-modal'));
    });
});

$('#btn-save-user').click(function(){
    var $btn = $(this); CRM.btn_loading($btn);
    $.ajax({
        url: BASE_URL+'admin/save_user', method:'POST',
        data: new FormData($('#user-form')[0]), processData:false, contentType:false,
        success: function(res){
            if(res.status==='success'){ CRM.toast('success',res.message); $('#user-modal').modal('hide'); userTable.ajax.reload(null,false); }
            else { CRM.show_errors($('#user-form'),res.errors||{}); CRM.toast('error',res.message); }
        },
        complete: function(){ CRM.btn_reset($btn); }
    });
});

$(document).on('click', '.btn-user-status', function(){
    var id=$(this).data('id'), action=$(this).data('action');
    if(action==='delete'||action==='deactivate'){ if(!confirm('Are you sure?')) return; }
    $.post(BASE_URL+'admin/user_status', {id:id, action:action, [CI3_CSRF_NAME]:CI3_CSRF_HASH}, function(res){
        if(res.status==='success'){ CRM.toast('success',res.message); userTable.ajax.reload(null,false); }
    });
});

$('#btn-view-credentials').click(function(){
    $.getJSON(BASE_URL+'admin/users_credentials', function(res){
        var html = '<div class="overflow-x-auto"><table class="w-full text-sm">' +
            '<thead class="text-xs font-semibold text-gray-500 uppercase border-b border-gray-200">' +
            '<tr><th class="pb-2 text-left">#</th><th class="pb-2 text-left">Name</th><th class="pb-2 text-left">Email</th><th class="pb-2 text-left">Role</th><th class="pb-2 text-left">Status</th><th class="pb-2 text-left">Reset PW</th></tr></thead>' +
            '<tbody class="divide-y divide-gray-50">';
        $.each(res.data||[], function(i,u){
            var roleColor = u.role==='admin' ? 'bg-red-100 text-red-700' : u.role==='manager' ? 'bg-amber-100 text-amber-700' : 'bg-blue-100 text-blue-700';
            html += '<tr>' +
                '<td class="py-2 text-gray-400">'+u.id+'</td>' +
                '<td class="py-2 text-gray-700">'+CRM.esc(u.name)+'</td>' +
                '<td class="py-2"><code class="text-xs bg-gray-100 px-1.5 py-0.5 rounded">'+CRM.esc(u.email)+'</code></td>' +
                '<td class="py-2"><span class="inline-flex items-center px-2 py-0.5 text-xs font-medium rounded-full '+roleColor+'">'+CRM.esc(u.role.replace('_',' '))+'</span></td>' +
                '<td class="py-2">'+CRM.status_badge(u.status)+'</td>' +
                '<td class="py-2"><button class="inline-flex items-center gap-1 px-2 py-1 text-xs bg-amber-100 text-amber-700 rounded hover:bg-amber-200 btn-reset-pw" data-id="'+u.id+'" data-name="'+CRM.esc(u.name)+'"><i class="fa fa-lock"></i> Reset</button></td>' +
                '</tr>';
        });
        html += '</tbody></table></div>';
        $('#credentials-body').html(html);
        $('#credentials-modal').modal('show');
    });
});

$(document).on('click', '.btn-reset-pw', function(){
    var id=$(this).data('id'), name=$(this).data('name');
    var pw = prompt('Set new password for '+name+':');
    if(!pw || pw.length < 6){ if(pw!==null) alert('Password must be at least 6 characters.'); return; }
    $.post(BASE_URL+'admin/reset_password', {id:id, password:pw, [CI3_CSRF_NAME]:CI3_CSRF_HASH}, function(res){
        CRM.toast(res.status==='success'?'success':'error', res.message);
    });
});
</script>
