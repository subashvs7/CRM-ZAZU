<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
    <div class="flex items-center gap-3">
        <div class="w-10 h-10 bg-violet-100 rounded-xl flex items-center justify-center flex-shrink-0">
            <i class="fa fa-envelope text-violet-600 text-lg"></i>
        </div>
        <div>
            <h1 class="text-xl font-bold text-gray-800">Notification Templates</h1>
            <nav class="text-xs text-gray-400 flex items-center gap-1 mt-0.5">
                <a href="<?= base_url('dashboard') ?>" class="hover:text-blue-600 transition-colors">Home</a>
                <i class="fa fa-angle-right text-[10px]"></i>
                <span class="text-gray-600">Notif Templates</span>
            </nav>
        </div>
    </div>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-gray-100">
    <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
        <div class="flex items-center gap-2">
            <div class="w-8 h-8 bg-violet-100 rounded-lg flex items-center justify-center">
                <i class="fa fa-envelope text-violet-600 text-sm"></i>
            </div>
            <div>
                <h3 class="text-sm font-bold text-gray-800">Templates</h3>
                <p class="text-xs text-gray-400 mt-0.5">Push, email, and SMS notification templates</p>
            </div>
        </div>
        <button id="btn-add-tpl" class="inline-flex items-center gap-1.5 px-4 py-2 text-sm bg-violet-600 text-white font-semibold rounded-xl hover:bg-violet-700 transition-colors shadow-sm">
            <i class="fa fa-plus"></i> Add Template
        </button>
    </div>
    <div class="p-4 overflow-x-auto">
        <table id="tpl-table" class="w-full">
            <thead><tr><th>#</th><th>Name</th><th>Channel</th><th>Body Preview</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody></tbody>
        </table>
    </div>
</div>

<!-- Template Modal -->
<div class="modal fade" id="tpl-modal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Template</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <form id="tpl-form">
                    <input type="hidden" name="<?= $csrf_name ?>" value="<?= $csrf_hash ?>">
                    <input type="hidden" name="id" id="tpl-id" value="0">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">Name</label>
                            <input type="text" name="name" class="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-violet-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">Channel</label>
                            <select name="channel" class="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-violet-500">
                                <option>push</option><option>email</option><option>sms</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">Subject</label>
                            <input type="text" name="subject" class="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-violet-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">Body</label>
                            <textarea name="body" class="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-violet-500" rows="6"></textarea>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="px-4 py-2 text-sm text-gray-700 border border-gray-300 rounded-xl hover:bg-gray-50" data-dismiss="modal">Cancel</button>
                <button class="px-4 py-2 text-sm bg-violet-600 text-white rounded-xl hover:bg-violet-700" id="btn-save-tpl"><i class="fa fa-save mr-1"></i> Save</button>
            </div>
        </div>
    </div>
</div>

<script>
var tplTable = $('#tpl-table').DataTable({
    processing: true, serverSide: true,
    ajax: { url: BASE_URL+'admin/templates_dt' },
    columns: [{data:0},{data:1},{data:2},{data:3},{data:4},{data:5,orderable:false}],
    order: [[0,'desc']]
});

$('#btn-add-tpl').click(function(){
    $('#tpl-form')[0].reset(); $('#tpl-id').val(0);
    $('#tpl-modal .modal-title').text('Add Template');
    $('#tpl-modal').modal('show');
});

$(document).on('click', '.btn-edit-tpl', function(){
    var id = $(this).data('id');
    $.get(BASE_URL+'admin/get_template/'+id, function(res){
        if (res.status !== 'success') { CRM.toast('error', res.message||'Failed to load.'); return; }
        var d = res.data;
        $('#tpl-form')[0].reset();
        $('#tpl-id').val(d.id);
        $('#tpl-form [name=name]').val(d.name||'');
        $('#tpl-form [name=channel]').val(d.channel||'push');
        $('#tpl-form [name=subject]').val(d.subject||'');
        $('#tpl-form [name=body]').val(d.body||'');
        $('#tpl-modal .modal-title').text('Edit Template');
        $('#tpl-modal').modal('show');
    });
});

$('#btn-save-tpl').click(function(){
    $.ajax({
        url: BASE_URL+'admin/save_template', method:'POST',
        data: new FormData($('#tpl-form')[0]), processData:false, contentType:false,
        success: function(res){
            if(res.status==='success'){ CRM.toast('success',res.message); $('#tpl-modal').modal('hide'); tplTable.ajax.reload(null,false); }
            else CRM.toast('error', res.message);
        }
    });
});
</script>
