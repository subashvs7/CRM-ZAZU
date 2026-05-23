<section class="content-header">
    <h1>Users <small>Manage system users</small></h1>
    <ol class="breadcrumb"><li><a href="<?= base_url('dashboard') ?>"><i class="fa fa-dashboard"></i> Home</a></li><li class="active">Users</li></ol>
</section>
<section class="content">
<?php $this->load->view('partials/_status_tabs', get_defined_vars()); ?>
<div class="box box-primary">
    <div class="box-header with-border">
        <h3 class="box-title"><i class="fa fa-users"></i> Users</h3>
        <div class="box-tools">
            <button class="btn btn-info btn-sm" id="btn-view-credentials"><i class="fa fa-key"></i> View Credentials</button>
            <button class="btn btn-success btn-sm" id="btn-add-user"><i class="fa fa-plus"></i> Add User</button>
        </div>
    </div>
    <div class="box-body">
        <table id="users-table" class="table table-bordered table-striped">
            <thead><tr><th>#</th><th>Name</th><th>Email</th><th>Role</th><th>Team</th><th>Status</th><th>Last Login</th><th>Actions</th></tr></thead>
            <tbody></tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="user-modal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header"><button type="button" class="close" data-dismiss="modal">&times;</button><h4 class="modal-title">User</h4></div>
            <div class="modal-body">
                <form id="user-form" method="POST">
                    <input type="hidden" name="<?= $csrf_name ?>" value="<?= $csrf_hash ?>">
                    <input type="hidden" name="id" id="user-id" value="0">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group"><label>Name *</label><input type="text" name="name" class="form-control" required></div>
                            <div class="form-group"><label>Email *</label><input type="email" name="email" class="form-control" required></div>
                            <div class="form-group"><label>Password <small class="text-muted">(leave blank to keep)</small></label><input type="password" name="password" class="form-control"></div>
                            <div class="form-group"><label>Phone</label><input type="text" name="phone" class="form-control"></div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group"><label>Role *</label>
                                <select name="role" class="form-control" required>
                                    <option value="admin">Admin</option>
                                    <option value="manager">Manager</option>
                                    <option value="field_staff" selected>Field Staff</option>
                                </select>
                            </div>
                            <div class="form-group"><label>Team</label>
                                <select name="team_id" class="form-control select2">
                                    <option value="">-- No Team --</option>
                                    <?php foreach($teams as $t): ?><option value="<?= $t['id'] ?>"><?= esc_html($t['name']) ?></option><?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group"><label>Permissions</label>
                                <div class="checkbox"><label><input type="checkbox" name="permissions[]" value="leads.manage"> Manage Leads</label></div>
                                <div class="checkbox"><label><input type="checkbox" name="permissions[]" value="orders.approve"> Approve Orders</label></div>
                                <div class="checkbox"><label><input type="checkbox" name="permissions[]" value="attendance.correct"> Correct Attendance</label></div>
                                <div class="checkbox"><label><input type="checkbox" name="permissions[]" value="reports.view"> View Reports</label></div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-default" data-dismiss="modal">Cancel</button>
                <button class="btn btn-primary" id="btn-save-user"><i class="fa fa-save"></i> Save</button>
            </div>
        </div>
    </div>
</div>
</section>

<script>
var userTable = $('#users-table').DataTable({
    processing: true, serverSide: true,
    ajax: { url: BASE_URL+'admin/users_dt', data: function(d) { d.status_filter = window.currentStatusFilter||''; } },
    columns: [{data:0},{data:1},{data:2},{data:3},{data:4},{data:5},{data:6},{data:7,orderable:false}],
    order: [[0,'desc']]
});

$('#status-tabs a').on('click', function(e) {
    e.preventDefault();
    $('#status-tabs li').removeClass('active'); $(this).parent().addClass('active');
    window.currentStatusFilter = $(this).data('status');
    $('#deleted-banner').toggle(window.currentStatusFilter === 'deleted');
    userTable.ajax.reload();
});

$('#btn-add-user').click(function() {
    $('#user-form')[0].reset(); $('#user-id').val(0);
    $('#user-modal .modal-title').text('Add User');
    $('[name="permissions[]"]').prop('checked', false);
    CRM.clear_errors($('#user-form'));
    $('#user-modal').modal('show');
    CRM.init_plugins($('#user-modal'));
});

$(document).on('click', '.btn-edit-user', function() {
    var id = $(this).data('id');
    $.getJSON(BASE_URL+'admin/get_user/'+id, function(res) {
        var u = res.data;
        $('#user-id').val(u.id);
        $('[name="name"]').val(u.name);
        $('[name="email"]').val(u.email);
        $('[name="role"]').val(u.role);
        $('[name="phone"]').val(u.phone||'');
        $('[name="team_id"]').val(u.team_id||'').trigger('change');
        $('[name="permissions[]"]').prop('checked', false);
        $.each(u.permissions||[], function(i,p) { $('[name="permissions[]"][value="'+p+'"]').prop('checked',true); });
        $('#user-modal .modal-title').text('Edit User');
        CRM.clear_errors($('#user-form'));
        $('#user-modal').modal('show');
        CRM.init_plugins($('#user-modal'));
    });
});

$('#btn-save-user').click(function() {
    var $btn = $(this); CRM.btn_loading($btn);
    $.ajax({
        url: BASE_URL+'admin/save_user', method:'POST',
        data: new FormData($('#user-form')[0]), processData:false, contentType:false,
        success: function(res) {
            if (res.status==='success') { CRM.toast('success',res.message); $('#user-modal').modal('hide'); userTable.ajax.reload(null,false); }
            else { CRM.show_errors($('#user-form'),res.errors||{}); CRM.toast('error',res.message); }
        },
        complete: function() { CRM.btn_reset($btn); }
    });
});

$(document).on('click', '.btn-user-status', function() {
    var id=$(this).data('id'), action=$(this).data('action');
    if(action==='delete'||action==='deactivate') { if(!confirm('Are you sure?')) return; }
    $.post(BASE_URL+'admin/user_status', {id:id,action:action,[CI3_CSRF_NAME]:CI3_CSRF_HASH}, function(res) {
        if(res.status==='success') { CRM.toast('success',res.message); userTable.ajax.reload(null,false); }
    });
});

// View Credentials
$('#btn-view-credentials').click(function() {
    $.getJSON(BASE_URL+'admin/users_credentials', function(res) {
        var html = '<table class="table table-bordered table-condensed table-hover">' +
            '<thead><tr><th>#</th><th>Name</th><th>Email (Username)</th><th>Role</th><th>Status</th><th>Reset PW</th></tr></thead><tbody>';
        $.each(res.data||[], function(i, u) {
            html += '<tr>' +
                '<td>'+u.id+'</td>' +
                '<td>'+CRM.esc(u.name)+'</td>' +
                '<td><code>'+CRM.esc(u.email)+'</code></td>' +
                '<td><span class="label label-'+(u.role==='admin'?'danger':u.role==='manager'?'warning':'primary')+'">'+CRM.esc(u.role.replace('_',' '))+'</span></td>' +
                '<td>'+CRM.status_badge(u.status)+'</td>' +
                '<td><button class="btn btn-xs btn-warning btn-reset-pw" data-id="'+u.id+'" data-name="'+CRM.esc(u.name)+'"><i class="fa fa-lock"></i> Reset</button></td>' +
                '</tr>';
        });
        html += '</tbody></table>';
        $('#credentials-body').html(html);
        $('#credentials-modal').modal('show');
    });
});

// Reset Password inline
$(document).on('click', '.btn-reset-pw', function() {
    var id = $(this).data('id'), name = $(this).data('name');
    var pw = prompt('Set new password for ' + name + ':');
    if (!pw || pw.length < 6) { if(pw !== null) alert('Password must be at least 6 characters.'); return; }
    $.post(BASE_URL+'admin/reset_password', {id:id, password:pw, [CI3_CSRF_NAME]:CI3_CSRF_HASH}, function(res) {
        CRM.toast(res.status==='success'?'success':'error', res.message);
    });
});
</script>

<!-- Credentials Modal -->
<div class="modal fade" id="credentials-modal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header"><button type="button" class="close" data-dismiss="modal">&times;</button><h4 class="modal-title"><i class="fa fa-key"></i> User Credentials</h4></div>
            <div class="modal-body">
                <div class="alert alert-warning"><i class="fa fa-lock"></i> Passwords are hashed and cannot be viewed. Use <strong>Reset Password</strong> to set a new one.</div>
                <div id="credentials-body"><div class="text-center"><i class="fa fa-spinner fa-spin"></i></div></div>
            </div>
            <div class="modal-footer"><button class="btn btn-default" data-dismiss="modal">Close</button></div>
        </div>
    </div>
</div>
