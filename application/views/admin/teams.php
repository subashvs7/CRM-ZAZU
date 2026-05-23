<section class="content-header">
    <h1>Teams <small>Manage field teams</small></h1>
    <ol class="breadcrumb"><li><a href="<?= base_url('dashboard') ?>">Home</a></li><li class="active">Teams</li></ol>
</section>
<section class="content">
<?php $this->load->view('partials/_status_tabs', get_defined_vars()); ?>
<div class="box box-primary">
    <div class="box-header with-border">
        <h3 class="box-title"><i class="fa fa-group"></i> Teams</h3>
        <div class="box-tools"><button class="btn btn-success btn-sm" id="btn-add-team"><i class="fa fa-plus"></i> Add Team</button></div>
    </div>
    <div class="box-body">
        <table id="teams-table" class="table table-bordered table-striped">
            <thead><tr><th>#</th><th>Name</th><th>Manager</th><th>Territory</th><th>Members</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody></tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="team-modal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header"><button type="button" class="close" data-dismiss="modal">&times;</button><h4 class="modal-title">Team</h4></div>
            <div class="modal-body">
                <form id="team-form">
                    <input type="hidden" name="<?= $csrf_name ?>" value="<?= $csrf_hash ?>">
                    <input type="hidden" name="id" id="team-id" value="0">
                    <div class="form-group"><label>Name *</label><input type="text" name="name" class="form-control" required></div>
                    <div class="form-group"><label>Manager</label>
                        <select name="manager_id" class="form-control select2">
                            <option value="">-- No Manager --</option>
                            <?php foreach($managers as $m): ?><option value="<?= $m['id'] ?>"><?= esc_html($m['name']) ?></option><?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group"><label>Territory</label><input type="text" name="territory" class="form-control"></div>
                    <div class="form-group"><label>Description</label><textarea name="description" class="form-control" rows="3"></textarea></div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-default" data-dismiss="modal">Cancel</button>
                <button class="btn btn-primary" id="btn-save-team"><i class="fa fa-save"></i> Save</button>
            </div>
        </div>
    </div>
</div>
</section>

<script>
var teamTable = $('#teams-table').DataTable({
    processing:true, serverSide:true,
    ajax:{url:BASE_URL+'admin/teams_dt', data:function(d){d.status_filter=window.currentStatusFilter||'';}},
    columns:[{data:0},{data:1},{data:2},{data:3},{data:4},{data:5},{data:6,orderable:false}],
    order:[[0,'desc']]
});
$('#status-tabs a').on('click',function(e){e.preventDefault();$('#status-tabs li').removeClass('active');$(this).parent().addClass('active');window.currentStatusFilter=$(this).data('status');$('#deleted-banner').toggle(window.currentStatusFilter==='deleted');teamTable.ajax.reload();});
$('#btn-add-team').click(function(){$('#team-form')[0].reset();$('#team-id').val(0);CRM.clear_errors($('#team-form'));$('#team-modal').modal('show');CRM.init_plugins($('#team-modal'));});
$('#btn-save-team').click(function(){
    var $btn=$(this);CRM.btn_loading($btn);
    $.ajax({url:BASE_URL+'admin/save_team',method:'POST',data:new FormData($('#team-form')[0]),processData:false,contentType:false,
        success:function(res){if(res.status==='success'){CRM.toast('success',res.message);$('#team-modal').modal('hide');teamTable.ajax.reload(null,false);}else{CRM.show_errors($('#team-form'),res.errors||{});CRM.toast('error',res.message);}},
        complete:function(){CRM.btn_reset($btn);}
    });
});
$(document).on('click','.btn-team-status',function(){
    var id=$(this).data('id'),action=$(this).data('action');
    if(action==='delete'){if(!confirm('Delete this team?'))return;}
    $.post(BASE_URL+'admin/team_status',{id:id,action:action,[CI3_CSRF_NAME]:CI3_CSRF_HASH},function(res){if(res.status==='success'){CRM.toast('success',res.message);teamTable.ajax.reload(null,false);}});
});
</script>
