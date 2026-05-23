<section class="content-header">
    <h1>Notification Templates</h1>
    <ol class="breadcrumb"><li><a href="<?= base_url('dashboard') ?>">Home</a></li><li class="active">Notif Templates</li></ol>
</section>
<section class="content">
<div class="box box-primary">
    <div class="box-header with-border">
        <h3 class="box-title"><i class="fa fa-envelope"></i> Templates</h3>
        <div class="box-tools"><button class="btn btn-success btn-sm" id="btn-add-tpl"><i class="fa fa-plus"></i> Add Template</button></div>
    </div>
    <div class="box-body">
        <table id="tpl-table" class="table table-bordered table-striped">
            <thead><tr><th>#</th><th>Name</th><th>Channel</th><th>Body Preview</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody></tbody>
        </table>
    </div>
</div>
<div class="modal fade" id="tpl-modal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header"><button type="button" class="close" data-dismiss="modal">&times;</button><h4 class="modal-title">Template</h4></div>
            <div class="modal-body">
                <form id="tpl-form">
                    <input type="hidden" name="<?= $csrf_name ?>" value="<?= $csrf_hash ?>">
                    <input type="hidden" name="id" id="tpl-id" value="0">
                    <div class="form-group"><label>Name</label><input type="text" name="name" class="form-control"></div>
                    <div class="form-group"><label>Channel</label>
                        <select name="channel" class="form-control"><option>push</option><option>email</option><option>sms</option></select>
                    </div>
                    <div class="form-group"><label>Subject</label><input type="text" name="subject" class="form-control"></div>
                    <div class="form-group"><label>Body</label><textarea name="body" class="form-control" rows="6"></textarea></div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-default" data-dismiss="modal">Cancel</button>
                <button class="btn btn-primary" id="btn-save-tpl">Save</button>
            </div>
        </div>
    </div>
</div>
</section>
<script>
var tplTable=$('#tpl-table').DataTable({processing:true,serverSide:true,ajax:{url:BASE_URL+'admin/templates_dt'},columns:[{data:0},{data:1},{data:2},{data:3},{data:4},{data:5,orderable:false}],order:[[0,'desc']]});
$('#btn-add-tpl').click(function(){$('#tpl-form')[0].reset();$('#tpl-id').val(0);$('#tpl-modal').modal('show');});
$('#btn-save-tpl').click(function(){
    $.ajax({url:BASE_URL+'admin/save_template',method:'POST',data:new FormData($('#tpl-form')[0]),processData:false,contentType:false,
        success:function(res){if(res.status==='success'){CRM.toast('success',res.message);$('#tpl-modal').modal('hide');tplTable.ajax.reload(null,false);}else CRM.toast('error',res.message);}
    });
});
</script>
