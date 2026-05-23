<section class="content-header">
    <h1>Alert Rules</h1>
    <ol class="breadcrumb"><li><a href="<?= base_url('geofence') ?>">Geofence</a></li><li class="active">Alert Rules</li></ol>
</section>
<section class="content">
<div class="box box-warning">
    <div class="box-header with-border">
        <h3 class="box-title"><i class="fa fa-bell"></i> Alert Rules</h3>
        <button class="btn btn-success btn-sm pull-right" id="btn-add-rule"><i class="fa fa-plus"></i> Add Rule</button>
    </div>
    <div class="box-body">
        <table id="rules-table" class="table table-bordered table-striped">
            <thead><tr><th>#</th><th>Zone</th><th>Event</th><th>Notify Roles</th><th>Cooldown</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody></tbody>
        </table>
    </div>
</div>
<div class="modal fade" id="rule-modal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header"><button type="button" class="close" data-dismiss="modal">&times;</button><h4 class="modal-title">Alert Rule</h4></div>
            <div class="modal-body">
                <form id="rule-form">
                    <input type="hidden" name="<?= $csrf_name ?>" value="<?= $csrf_hash ?>">
                    <div class="form-group"><label>Zone (leave blank for all)</label>
                        <select name="geofence_zone_id" class="form-control select2">
                            <option value="">-- All Zones --</option>
                            <?php foreach($zones as $z): ?><option value="<?= $z['id'] ?>"><?= esc_html($z['name']) ?></option><?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group"><label>Event Type</label>
                        <select name="event_type" class="form-control"><option>enter</option><option>exit</option><option>offline</option><option>speeding</option></select>
                    </div>
                    <div class="form-group"><label>Notify Roles</label>
                        <div class="checkbox"><label><input type="checkbox" name="notify_roles[]" value="admin"> Admin</label></div>
                        <div class="checkbox"><label><input type="checkbox" name="notify_roles[]" value="manager"> Manager</label></div>
                    </div>
                    <div class="form-group"><label>Cooldown (minutes)</label><input type="number" name="cooldown_minutes" class="form-control" value="30"></div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-default" data-dismiss="modal">Cancel</button>
                <button class="btn btn-primary" id="btn-save-rule">Save</button>
            </div>
        </div>
    </div>
</div>
</section>
<script>
var rulesTable=$('#rules-table').DataTable({processing:true,serverSide:true,ajax:{url:BASE_URL+'geofence/rules_dt'},columns:[{data:0},{data:1},{data:2},{data:3},{data:4},{data:5},{data:6,orderable:false}],order:[[0,'desc']]});
$('#btn-add-rule').click(function(){$('#rule-form')[0].reset();$('#rule-modal').modal('show');});
$('#btn-save-rule').click(function(){
    $.ajax({url:BASE_URL+'geofence/save_rule',method:'POST',data:new FormData($('#rule-form')[0]),processData:false,contentType:false,
        success:function(res){if(res.status==='success'){CRM.toast('success',res.message);$('#rule-modal').modal('hide');rulesTable.ajax.reload(null,false);}else CRM.toast('error',res.message);}
    });
});
$(document).on('click','.btn-rule-status',function(){
    $.post(BASE_URL+'geofence/rule_status',{id:$(this).data('id'),action:$(this).data('action'),[CI3_CSRF_NAME]:CI3_CSRF_HASH},function(res){if(res.status==='success'){CRM.toast('success',res.message);rulesTable.ajax.reload(null,false);}});
});
</script>
