<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
    <div class="flex items-center gap-3">
        <div class="w-10 h-10 bg-indigo-100 rounded-xl flex items-center justify-center flex-shrink-0">
            <i class="fa fa-calendar text-indigo-600 text-lg"></i>
        </div>
        <div>
            <h1 class="text-xl font-bold text-gray-800">Shifts</h1>
            <nav class="text-xs text-gray-400 flex items-center gap-1 mt-0.5">
                <a href="<?= base_url('dashboard') ?>" class="hover:text-blue-600 transition-colors">Home</a>
                <i class="fa fa-angle-right text-[10px]"></i>
                <span class="text-gray-600">Shifts</span>
            </nav>
        </div>
    </div>
    <div class="flex items-center gap-2 self-start sm:self-auto">
        <a href="<?= base_url('shifts/assignment') ?>" class="inline-flex items-center gap-1.5 px-3 py-2 text-sm bg-white border border-gray-200 text-gray-700 font-medium rounded-xl hover:bg-gray-50 transition-colors">
            <i class="fa fa-users text-cyan-600"></i> Assign
        </a>
        <a href="<?= base_url('shifts/calendar') ?>" class="inline-flex items-center gap-1.5 px-3 py-2 text-sm bg-white border border-gray-200 text-gray-700 font-medium rounded-xl hover:bg-gray-50 transition-colors">
            <i class="fa fa-calendar text-amber-500"></i> Calendar
        </a>
        <button id="btn-add-shift" class="inline-flex items-center gap-1.5 px-4 py-2 text-sm bg-indigo-600 text-white font-semibold rounded-xl hover:bg-indigo-700 transition-colors shadow-sm">
            <i class="fa fa-plus"></i> Add Shift
        </button>
    </div>
</div>

<?php $this->load->view('partials/_status_tabs', get_defined_vars()); ?>

<div class="bg-white rounded-2xl shadow-sm border border-gray-100">
    <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-2">
        <div class="w-8 h-8 bg-indigo-100 rounded-lg flex items-center justify-center">
            <i class="fa fa-calendar text-indigo-600 text-sm"></i>
        </div>
        <div>
            <h3 class="text-sm font-bold text-gray-800">Work Shifts</h3>
            <p class="text-xs text-gray-400 mt-0.5">Shift definitions and timings</p>
        </div>
    </div>
    <div class="p-4 overflow-x-auto">
        <table id="shifts-table" class="w-full">
            <thead><tr><th>#</th><th>Name</th><th>Start</th><th>End</th><th>Grace</th><th>Full Day</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody></tbody>
        </table>
    </div>
</div>

<!-- Shift Modal -->
<div class="modal fade" id="shift-modal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Shift</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <form id="shift-form">
                    <input type="hidden" name="<?= $csrf_name ?>" value="<?= $csrf_hash ?>">
                    <input type="hidden" name="id" id="shift-id" value="0">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">Name *</label>
                            <input type="text" name="name" class="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" required>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">Start Time</label>
                                <input type="time" name="start_time" class="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">End Time</label>
                                <input type="time" name="end_time" class="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            </div>
                        </div>
                        <div class="grid grid-cols-3 gap-3">
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">Grace <span class="normal-case font-normal">(mins)</span></label>
                                <input type="number" name="grace_minutes" class="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" value="15">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">Half Day <span class="normal-case font-normal">(hrs)</span></label>
                                <input type="number" step="0.5" name="half_day_hours" class="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" value="4">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">Full Day <span class="normal-case font-normal">(hrs)</span></label>
                                <input type="number" step="0.5" name="full_day_hours" class="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" value="8">
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="px-4 py-2 text-sm text-gray-700 border border-gray-300 rounded-xl hover:bg-gray-50" data-dismiss="modal">Cancel</button>
                <button class="px-4 py-2 text-sm bg-indigo-600 text-white rounded-xl hover:bg-indigo-700" id="btn-save-shift"><i class="fa fa-save mr-1"></i> Save</button>
            </div>
        </div>
    </div>
</div>

<script>
var shiftsTable = $('#shifts-table').DataTable({
    processing: true, serverSide: true,
    ajax: { url: BASE_URL+'shifts/datatable', data: function(d){ d.status_filter = window.currentStatusFilter||''; } },
    columns: [{data:0},{data:1},{data:2},{data:3},{data:4},{data:5},{data:6},{data:7,orderable:false}],
    order: [[0,'desc']]
});
window.mainTable = shiftsTable;

$('#btn-add-shift').click(function(){
    $('#shift-form')[0].reset();
    $('#shift-id').val(0);
    $('#shift-modal').modal('show');
});

$(document).on('click', '.btn-edit-shift', function(){
    $.getJSON(BASE_URL+'shifts/get/'+$(this).data('id'), function(res){
        var s = res.data;
        $('[name="name"]').val(s.name);
        $('[name="start_time"]').val(s.start_time);
        $('[name="end_time"]').val(s.end_time);
        $('[name="grace_minutes"]').val(s.grace_minutes);
        $('[name="half_day_hours"]').val(s.half_day_hours);
        $('[name="full_day_hours"]').val(s.full_day_hours);
        $('#shift-id').val(s.id);
        $('#shift-modal').modal('show');
    });
});

$('#btn-save-shift').click(function(){
    $.ajax({
        url: BASE_URL+'shifts/save', method: 'POST',
        data: new FormData($('#shift-form')[0]), processData: false, contentType: false,
        success: function(res){
            if(res.status==='success'){ CRM.toast('success',res.message); $('#shift-modal').modal('hide'); shiftsTable.ajax.reload(null,false); }
            else CRM.toast('error', res.message);
        }
    });
});

$(document).on('click', '.btn-shift-status', function(){
    $.post(BASE_URL+'shifts/status', {id:$(this).data('id'), action:$(this).data('action'), [CI3_CSRF_NAME]:CI3_CSRF_HASH}, function(res){
        if(res.status==='success'){ CRM.toast('success',res.message); shiftsTable.ajax.reload(null,false); }
    });
});
</script>
