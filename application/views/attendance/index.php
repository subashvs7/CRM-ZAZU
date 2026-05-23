<!-- Page Header -->
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
    <div class="flex items-center gap-3">
        <div class="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center flex-shrink-0">
            <i class="fa fa-clock-o text-blue-600 text-lg"></i>
        </div>
        <div>
            <h1 class="text-xl font-bold text-gray-800">Attendance</h1>
            <nav class="text-xs text-gray-400 flex items-center gap-1 mt-0.5">
                <a href="<?= base_url('dashboard') ?>" class="hover:text-blue-600 transition-colors">Home</a>
                <i class="fa fa-angle-right text-[10px]"></i>
                <span class="text-gray-600">Attendance</span>
            </nav>
        </div>
    </div>
    <div class="flex items-center gap-2 self-start sm:self-auto">
        <a href="<?= base_url('attendance/punch') ?>"
           class="inline-flex items-center gap-1.5 px-3 py-2 text-sm bg-green-600 text-white font-medium rounded-xl hover:bg-green-700 transition-colors">
            <i class="fa fa-sign-in"></i> Punch In/Out
        </a>
        <a href="<?= base_url('attendance/monthly') ?>"
           class="inline-flex items-center gap-1.5 px-3 py-2 text-sm bg-white border border-gray-200 text-gray-700 font-medium rounded-xl hover:bg-gray-50 transition-colors">
            <i class="fa fa-calendar text-cyan-600"></i> Monthly
        </a>
        <?php if($is_manager): ?>
        <a href="<?= base_url('attendance/corrections') ?>"
           class="inline-flex items-center gap-1.5 px-3 py-2 text-sm bg-amber-500 text-white font-medium rounded-xl hover:bg-amber-600 transition-colors">
            <i class="fa fa-edit"></i> Corrections
        </a>
        <?php endif; ?>
    </div>
</div>

<!-- Status Tabs -->
<?php $this->load->view('partials/_status_tabs', get_defined_vars()); ?>

<!-- Table -->
<div class="bg-white rounded-2xl shadow-sm border border-gray-100">
    <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
        <div>
            <h3 class="text-sm font-bold text-gray-800">Attendance Records</h3>
            <p class="text-xs text-gray-400 mt-0.5">Daily punch-in and punch-out log</p>
        </div>
        <span class="px-2.5 py-1 bg-blue-50 text-blue-600 text-xs font-semibold rounded-lg">
            <i class="fa fa-clock-o mr-1"></i> Log
        </span>
    </div>
    <div class="p-4 overflow-x-auto">
        <table id="att-table" class="w-full">
            <thead>
                <tr>
                    <th>#</th><th>Date</th><th>Staff</th><th>In</th><th>Out</th>
                    <th>Status</th><th>Hours</th><th>Face</th><th>Regularized</th><th>Actions</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>

<!-- Regularize Attendance Modal -->
<div class="modal fade" id="regularize-modal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Regularize Attendance</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <form id="regularize-form">
                    <input type="hidden" name="<?= $csrf_name ?>" value="<?= $csrf_hash ?>">
                    <input type="hidden" name="id" id="reg-att-id" value="0">

                    <!-- Current record info -->
                    <div class="grid grid-cols-3 gap-3 p-3 bg-gray-50 rounded-xl mb-4 text-center">
                        <div>
                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wide mb-0.5">Date</p>
                            <p class="text-sm font-semibold text-gray-800" id="reg-att-date">—</p>
                        </div>
                        <div>
                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wide mb-0.5">Current In</p>
                            <p class="text-sm font-medium text-gray-700" id="reg-in-display">—</p>
                        </div>
                        <div>
                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wide mb-0.5">Current Out</p>
                            <p class="text-sm font-medium text-gray-700" id="reg-out-display">—</p>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">
                                Corrected Date <span class="text-gray-400 font-normal normal-case">(leave blank to keep original)</span>
                            </label>
                            <input type="text" name="corrected_date" id="reg-corrected-date"
                                   class="w-full px-3 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 datepicker"
                                   placeholder="yyyy-mm-dd">
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">Corrected Punch In</label>
                                <input type="time" name="corrected_in" id="reg-corrected-in"
                                       class="w-full px-3 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">Corrected Punch Out</label>
                                <input type="time" name="corrected_out" id="reg-corrected-out"
                                       class="w-full px-3 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">Reason *</label>
                            <textarea name="reason" id="reg-reason" rows="3"
                                      class="w-full px-3 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none"
                                      required placeholder="Explain the reason for regularization..."></textarea>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="inline-flex items-center gap-1.5 px-4 py-2 text-sm bg-white border border-gray-200 text-gray-700 rounded-xl hover:bg-gray-50 font-medium" data-dismiss="modal">
                    Cancel
                </button>
                <button class="inline-flex items-center gap-1.5 px-4 py-2 text-sm bg-amber-500 text-white rounded-xl hover:bg-amber-600 font-semibold" id="btn-save-regularize">
                    <i class="fa fa-check"></i> Regularize
                </button>
            </div>
        </div>
    </div>
</div>
