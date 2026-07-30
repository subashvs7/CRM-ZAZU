<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
    <div class="flex items-center gap-3">
        <div class="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center flex-shrink-0">
            <i class="fa fa-paper-plane text-blue-600 text-lg"></i>
        </div>
        <div>
            <h1 class="text-xl font-bold text-gray-800">Bulk Mail</h1>
            <nav class="text-xs text-gray-400 flex items-center gap-1 mt-0.5">
                <a href="<?= base_url('dashboard') ?>" class="hover:text-blue-600 transition-colors">Home</a>
                <i class="fa fa-angle-right text-[10px]"></i>
                <span class="text-gray-600">Communications</span>
                <i class="fa fa-angle-right text-[10px]"></i>
                <span class="text-gray-600">Bulk Mail</span>
            </nav>
        </div>
    </div>
    <div class="flex items-center gap-3">
        <button type="button" class="px-4 py-2 text-blue-600 font-medium bg-white border border-blue-200 hover:bg-blue-50 rounded-lg transition-colors text-sm shadow-sm flex items-center gap-1.5">
            <i class="fa fa-file-text-o"></i> Preview Email
        </button>
        <button type="button" class="px-4 py-2 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition-colors shadow-sm text-sm flex items-center gap-1.5">
            <i class="fa fa-paper-plane"></i> Send Email
        </button>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Left Column: Form -->
    <div class="lg:col-span-2 space-y-6">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100">
                <h3 class="text-sm font-bold text-gray-800">Compose and send bulk emails to your contacts, leads or customers.</h3>
            </div>
            
            <form action="<?= base_url('communications/process_bulk_mail') ?>" method="POST" class="p-6">
                <input type="hidden" name="<?= $csrf_name ?>" value="<?= $csrf_hash ?>">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-2">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Recipients <span class="text-red-500">*</span></label>
                        <div class="flex gap-2">
                            <div class="flex-grow">
                                <select name="recipient_type" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm select2" required>
                                    <option value="Customers">Customers</option>
                                    <option value="Active Customers">Active Customers</option>
                                    <option value="All Leads">All Leads</option>
                                    <option value="Contact Book">Contact Book</option>
                                </select>
                            </div>
                            <button type="button" class="px-4 py-2.5 bg-gray-50 text-gray-700 font-medium rounded-lg hover:bg-gray-100 transition-colors whitespace-nowrap text-sm border border-gray-200 flex items-center shadow-sm">
                                <i class="fa fa-users mr-2"></i> Select Recipients
                            </button>
                        </div>
                        <p class="text-xs text-gray-500 mt-2">1,245 recipients selected</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Email Template</label>
                        <select name="template_id" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm select2">
                            <option value="">-- Select Template (Optional) --</option>
                            <?php foreach($templates as $t): ?>
                                <option value="<?= $t['id'] ?>"><?= esc_html($t['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="mb-6 mt-4">
                    <label class="block text-sm font-bold text-gray-700 mb-2">Subject <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <input type="text" name="subject" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm pr-[140px]" placeholder="Important Update From CRM-Zazu" value="Important Update From CRM-Zazu" required>
                        <div class="absolute inset-y-0 right-0 flex items-center pr-2">
                            <button type="button" class="px-3 py-1.5 bg-gray-50 text-gray-700 font-medium rounded hover:bg-gray-100 transition-colors text-xs border border-gray-200 flex items-center gap-1.5 shadow-sm">
                                <i class="fa fa-tags"></i> Insert Merge Tag <i class="fa fa-angle-down ml-1"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="mb-6">
                    <label class="block text-sm font-bold text-gray-700 mb-2">Message <span class="text-red-500">*</span></label>
                    <div class="border border-gray-300 rounded-lg overflow-hidden focus-within:ring-2 focus-within:ring-blue-500 focus-within:border-blue-500 transition-shadow">
                        <div class="bg-white border-b border-gray-200 px-4 py-2.5 flex justify-between items-center text-gray-600">
                            <div class="flex gap-1.5 items-center">
                                <button type="button" class="p-1.5 hover:bg-gray-100 rounded text-gray-700 font-serif font-bold w-8 h-8 flex items-center justify-center transition-colors">B</button>
                                <button type="button" class="p-1.5 hover:bg-gray-100 rounded text-gray-700 font-serif italic w-8 h-8 flex items-center justify-center transition-colors">I</button>
                                <button type="button" class="p-1.5 hover:bg-gray-100 rounded text-gray-700 font-serif underline w-8 h-8 flex items-center justify-center transition-colors">U</button>
                                <button type="button" class="p-1.5 hover:bg-gray-100 rounded text-gray-700 flex items-center justify-center transition-colors px-2"><span class="font-serif mr-1 text-sm">A</span><i class="fa fa-caret-down text-[10px]"></i></button>
                                <div class="w-px h-5 bg-gray-200 my-auto mx-2"></div>
                                <button type="button" class="p-1.5 hover:bg-gray-100 rounded text-gray-700 w-8 h-8 flex items-center justify-center transition-colors"><i class="fa fa-link"></i></button>
                                <button type="button" class="p-1.5 hover:bg-gray-100 rounded text-gray-700 w-8 h-8 flex items-center justify-center transition-colors"><i class="fa fa-picture-o"></i></button>
                                <div class="w-px h-5 bg-gray-200 my-auto mx-2"></div>
                                <button type="button" class="p-1.5 hover:bg-gray-100 rounded text-gray-700 w-8 h-8 flex items-center justify-center transition-colors"><i class="fa fa-list-ul"></i></button>
                                <button type="button" class="p-1.5 hover:bg-gray-100 rounded text-gray-700 w-8 h-8 flex items-center justify-center transition-colors"><i class="fa fa-list-ol"></i></button>
                                <div class="w-px h-5 bg-gray-200 my-auto mx-2"></div>
                                <button type="button" class="p-1.5 hover:bg-gray-100 rounded text-gray-700 w-8 h-8 flex items-center justify-center transition-colors"><i class="fa fa-align-left"></i></button>
                                <button type="button" class="p-1.5 hover:bg-gray-100 rounded text-gray-700 w-8 h-8 flex items-center justify-center transition-colors"><i class="fa fa-align-center"></i></button>
                                <button type="button" class="p-1.5 hover:bg-gray-100 rounded text-gray-700 w-8 h-8 flex items-center justify-center transition-colors"><i class="fa fa-align-justify"></i></button>
                            </div>
                            <div class="flex gap-1 items-center">
                                <button type="button" class="p-1.5 hover:bg-gray-100 rounded text-gray-500 w-8 h-8 flex items-center justify-center transition-colors"><i class="fa fa-undo text-xs"></i></button>
                                <button type="button" class="p-1.5 hover:bg-gray-100 rounded text-gray-500 w-8 h-8 flex items-center justify-center transition-colors"><i class="fa fa-repeat text-xs"></i></button>
                            </div>
                        </div>
                        <textarea name="message" rows="12" class="w-full p-5 focus:outline-none focus:ring-0 text-sm text-gray-700 leading-relaxed resize-y" placeholder="Type your message here..." required>Dear {{customer_name}},

We are excited to share some important updates with you. Please take a moment to review the information below.

If you have any questions, feel free to reach out to our support team.

Best regards,
CRM-Zazu Team</textarea>
                    </div>
                </div>

                <div class="mb-8">
                    <label class="block text-sm font-bold text-gray-700 mb-2">Attachments</label>
                    <div class="border border-dashed border-gray-300 rounded-xl p-5 bg-gray-50/50 flex items-center gap-4">
                        <button type="button" class="px-5 py-2.5 bg-white text-gray-700 font-medium rounded-lg hover:bg-gray-50 transition-colors text-sm border border-gray-200 shadow-sm flex items-center gap-2">
                            <i class="fa fa-paperclip"></i> Upload Files
                        </button>
                        <span class="text-xs text-gray-400 font-medium">(Max file size: 10MB)</span>
                    </div>
                </div>

                <div class="flex items-center justify-between pt-6 border-t border-gray-100">
                    <button type="button" class="px-5 py-2.5 text-gray-700 font-medium bg-white border border-gray-200 hover:bg-gray-50 rounded-lg transition-colors text-sm shadow-sm flex items-center gap-2">
                        <i class="fa fa-file-text-o"></i> Save as Draft
                    </button>
                    <div class="flex gap-3">
                        <button type="button" class="px-5 py-2.5 text-blue-600 font-medium bg-white border border-blue-200 hover:bg-blue-50 rounded-lg transition-colors text-sm shadow-sm flex items-center gap-2">
                            <i class="fa fa-file-text-o"></i> Preview Email
                        </button>
                        <button type="submit" class="px-6 py-2.5 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition-colors shadow-sm text-sm flex items-center gap-2">
                            <i class="fa fa-paper-plane"></i> Send Email
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Right Column: Logs -->
    <div class="lg:col-span-1">
        <!-- Logs Card -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden h-full flex flex-col">
            <div class="p-5 border-b border-gray-100 flex items-center justify-between">
                <h3 class="text-base font-bold text-gray-800">Bulk Mail Logs</h3>
                <span class="flex items-center text-xs font-medium text-green-600 bg-green-50 px-2.5 py-1 rounded-full border border-green-100">
                    <span class="w-1.5 h-1.5 rounded-full bg-green-500 mr-1.5 animate-pulse"></span> Live
                </span>
            </div>
            
            <div class="p-5 flex-grow flex flex-col">
                <div class="flex gap-2 mb-6">
                    <div class="relative flex-grow">
                        <i class="fa fa-search absolute left-3.5 top-1/2 transform -translate-y-1/2 text-gray-400 text-sm"></i>
                        <input type="text" placeholder="Search logs..." class="w-full pl-9 pr-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                    <select class="border border-gray-200 rounded-lg text-sm px-3 py-2 bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent w-28">
                        <option>All Status</option>
                    </select>
                    <button type="button" class="w-9 h-9 border border-gray-200 rounded-lg flex items-center justify-center hover:bg-gray-50 text-gray-600 transition-colors shrink-0" onclick="location.reload()">
                        <i class="fa fa-refresh text-sm"></i>
                    </button>
                </div>

                <div class="grid grid-cols-4 gap-3 mb-6">
                    <div class="border border-gray-100 rounded-xl py-3 px-1 text-center flex flex-col items-center justify-center bg-white shadow-sm">
                        <div class="w-7 h-7 rounded-full bg-blue-50 text-blue-500 flex items-center justify-center mb-1.5">
                            <i class="fa fa-paper-plane text-[10px]"></i>
                        </div>
                        <div class="font-bold text-gray-800 text-sm"><?= isset($stats['total']) ? number_format($stats['total']) : 0 ?></div>
                        <div class="text-[10px] text-gray-500 font-medium">Total</div>
                    </div>
                    <div class="border border-gray-100 rounded-xl py-3 px-1 text-center flex flex-col items-center justify-center bg-white shadow-sm">
                        <div class="w-7 h-7 rounded-full bg-green-50 text-green-500 flex items-center justify-center mb-1.5">
                            <i class="fa fa-check text-[10px]"></i>
                        </div>
                        <div class="font-bold text-gray-800 text-sm"><?= isset($stats['sent']) ? number_format($stats['sent']) : 0 ?></div>
                        <div class="text-[10px] text-gray-500 font-medium">Sent</div>
                    </div>
                    <div class="border border-gray-100 rounded-xl py-3 px-1 text-center flex flex-col items-center justify-center bg-white shadow-sm">
                        <div class="w-7 h-7 rounded-full bg-orange-50 text-orange-500 flex items-center justify-center mb-1.5">
                            <i class="fa fa-clock-o text-[10px]"></i>
                        </div>
                        <div class="font-bold text-gray-800 text-sm"><?= isset($stats['queued']) ? number_format($stats['queued']) : 0 ?></div>
                        <div class="text-[10px] text-gray-500 font-medium">Queued</div>
                    </div>
                    <div class="border border-gray-100 rounded-xl py-3 px-1 text-center flex flex-col items-center justify-center bg-white shadow-sm">
                        <div class="w-7 h-7 rounded-full bg-red-50 text-red-500 flex items-center justify-center mb-1.5">
                            <i class="fa fa-times text-[10px]"></i>
                        </div>
                        <div class="font-bold text-gray-800 text-sm"><?= isset($stats['failed']) ? number_format($stats['failed']) : 0 ?></div>
                        <div class="text-[10px] text-gray-500 font-medium">Failed</div>
                    </div>
                </div>

                <div class="flex items-center justify-between mb-4">
                    <h4 class="text-[13px] font-bold text-gray-800">Last Logs</h4>
                    <a href="#" class="text-[11px] font-semibold text-blue-600 hover:text-blue-700">View All</a>
                </div>

                <div class="space-y-3 flex-grow overflow-y-auto">
                    <?php if(!empty($recent_campaigns)): foreach($recent_campaigns as $camp): 
                        $status_class = '';
                        $status_text = ucfirst($camp['status']);
                        if ($camp['status'] == 'completed') $status_class = 'bg-green-50 text-green-600 border-green-100';
                        elseif ($camp['status'] == 'processing') $status_class = 'bg-blue-50 text-blue-600 border-blue-100';
                        else $status_class = 'bg-orange-50 text-orange-600 border-orange-100';
                    ?>
                    <div class="border border-gray-100 rounded-xl p-3 flex items-center justify-between hover:border-gray-200 transition-colors cursor-pointer group">
                        <div class="flex items-center gap-3">
                            <div class="px-2.5 py-1 text-[10px] font-bold rounded-full border <?= $status_class ?> w-16 text-center"><?= $status_text ?></div>
                            <div class="max-w-[120px] sm:max-w-[150px]">
                                <h5 class="text-xs font-bold text-gray-800 mb-0.5 group-hover:text-blue-600 transition-colors truncate" title="<?= esc_html($camp['subject']) ?>"><?= esc_html($camp['subject']) ?></h5>
                                <p class="text-[10px] text-gray-500"><?= $camp['total_recipients'] ?> recipients</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-[10px] text-gray-400 whitespace-nowrap"><?= time_ago($camp['created_at']) ?></span>
                            <i class="fa fa-angle-right text-gray-300 text-sm"></i>
                        </div>
                    </div>
                    <?php endforeach; else: ?>
                    <div class="flex flex-col items-center justify-center h-full py-8 opacity-75">
                        <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mb-3 border border-gray-100">
                            <i class="fa fa-paper-plane-o text-gray-400 text-2xl"></i>
                        </div>
                        <h5 class="text-sm font-bold text-gray-600 mb-1">No Recent Campaigns</h5>
                        <p class="text-xs text-gray-400 text-center max-w-[200px]">You haven't sent any bulk emails recently.</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(function() {
    if ($.fn.select2) {
        $('.select2').select2({ width: '100%' });
    }
});
</script>
