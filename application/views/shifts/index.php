<section class="content-header">
    <h1>Shifts <small>Work shift management</small></h1>
    <ol class="breadcrumb"><li><a href="<?= base_url('dashboard') ?>">Home</a></li><li class="active">Shifts</li></ol>
</section>
<section class="content">
<?php $this->load->view('partials/_status_tabs', get_defined_vars()); ?>
<div class="box box-primary">
    <div class="box-header with-border">
        <h3 class="box-title"><i class="fa fa-calendar"></i> Shifts</h3>
        <div class="box-tools">
            <a href="<?= base_url('shifts/assignment') ?>" class="btn btn-info btn-sm"><i class="fa fa-users"></i> Assign</a>
            <a href="<?= base_url('shifts/calendar') ?>" class="btn btn-warning btn-sm"><i class="fa fa-calendar"></i> Calendar</a>
            <button class="btn btn-success btn-sm" id="btn-add-shift"><i class="fa fa-plus"></i> Add Shift</button>
        </div>
    </div>
    <div class="box-body">
        <table id="shifts-table" class="table table-bordered table-striped">
            <thead><tr><th>#</th><th>Name</th><th>Start</th><th>End</th><th>Grace</th><th>Full Day</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody></tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="shift-modal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header"><button type="button" class="close" data-dismiss="modal">&times;</button><h4 class="modal-title">Shift</h4></div>
            <div class="modal-body">
                <form id="shift-form">
                    <input type="hidden" name="<?= $csrf_name ?>" value="<?= $csrf_hash ?>">
                    <input type="hidden" name="id" id="shift-id" value="0">
                    <div class="form-group"><label>Name *</label><input type="text" name="name" class="form-control" required></div>
                    <div class="row">
                        <div class="col-md-6"><div class="form-group"><label>Start Time</label><input type="time" name="start_time" class="form-control"></div></div>
                        <div class="col-md-6"><div class="form-group"><label>End Time</label><input type="time" name="end_time" class="form-control"></div></div>
                    </div>
                    <div class="row">
                        <div class="col-md-4"><div class="form-group"><label>Grace (mins)</label><input type="number" name="grace_minutes" class="form-control" value="15"></div></div>
                        <div class="col-md-4"><div class="form-group"><label>Half Day (hrs)</label><input type="number" step="0.5" name="half_day_hours" class="form-control" value="4"></div></div>
                        <div class="col-md-4"><div class="form-group"><label>Full Day (hrs)</label><input type="number" step="0.5" name="full_day_hours" class="form-control" value="8"></div></div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-default" data-dismiss="modal">Cancel</button>
                <button class="btn btn-primary" id="btn-save-shift">Save</button>
            </div>
        </div>
    </div>
</div>
</section>
<script>
var shiftsTable=$('#shifts-table').DataTable({processing:true,serverSide:true,ajax:{url:BASE_URL+'shifts/datatable',data:function(d){d.status_filter=window.currentStatusFilter||'';}},columns:[{data:0},{data:1},{data:2},{data:3},{data:4},{data:5},{data:6},{data:7,orderable:false}],order:[[0,'desc']]});
$('#status-tabs a').on('click',function(e){e.preventDefault();$('#status-tabs li').removeClass('active');$(this).parent().addClass('active');window.currentStatusFilter=$(this).data('status');$('#deleted-banner').toggle(window.currentStatusFilter==='deleted');shiftsTable.ajax.reload();});
$('#btn-add-shift').click(function(){$('#shift-form')[0].reset();$('#shift-id').val(0);$('#shift-modal').modal('show');});
$(document).on('click','.btn-edit-shift',function(){
    $.getJSON(BASE_URL+'shifts/get/'+$(this).data('id'),function(res){
        var s=res.data;
        $('[name="name"]').val(s.name);$('[name="start_time"]').val(s.start_time);$('[name="end_time"]').val(s.end_time);
        $('[name="grace_minutes"]').val(s.grace_minutes);$('[name="half_day_hours"]').val(s.half_day_hours);$('[name="full_day_hours"]').val(s.full_day_hours);
        $('#shift-id').val(s.id);$('#shift-modal').modal('show');
    });
});
$('#btn-save-shift').click(function(){
    $.ajax({url:BASE_URL+'shifts/save',method:'POST',data:new FormData($('#shift-form')[0]),processData:false,contentType:false,
        success:function(res){if(res.status==='success'){CRM.toast('success',res.message);$('#shift-modal').modal('hide');shiftsTable.ajax.reload(null,false);}else CRM.toast('error',res.message);}
    });
});
$(document).on('click','.btn-shift-status',function(){
    $.post(BASE_URL+'shifts/status',{id:$(this).data('id'),action:$(this).data('action'),[CI3_CSRF_NAME]:CI3_CSRF_HASH},function(res){if(res.status==='success'){CRM.toast('success',res.message);shiftsTable.ajax.reload(null,false);}});
});
</script>
