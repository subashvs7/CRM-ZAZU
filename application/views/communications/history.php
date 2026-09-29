<?php
if (!function_exists('render_campaign_type_badge')) {
    function render_campaign_type_badge($type) {
        switch ($type) {
            case 'followup_1':
                return '<span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-blue-50 text-blue-700 border border-blue-200 whitespace-nowrap">Follow-Up #1</span>';
            case 'followup_2':
                return '<span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-purple-50 text-purple-700 border border-purple-200 whitespace-nowrap">Follow-Up #2</span>';
            case 'retry':
                return '<span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-rose-50 text-rose-700 border border-rose-200 whitespace-nowrap">Retry Resend</span>';
            case 'announcement':
                return '<span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-amber-50 text-amber-700 border border-amber-200 whitespace-nowrap">Announcement</span>';
            case 'outreach':
            default:
                return '<span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-indigo-50 text-indigo-700 border border-indigo-200 whitespace-nowrap">Outreach</span>';
        }
    }
}
?>
<div class="space-y-6">
    <!-- Top Header Bar -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-white p-5 rounded-2xl border border-gray-100 shadow-xs">
        <div class="flex items-center gap-3.5">
            <div class="w-12 h-12 bg-gradient-to-tr from-indigo-600 to-blue-600 text-white rounded-2xl flex items-center justify-center shadow-md shadow-indigo-500/20 flex-shrink-0">
                <i class="fa fa-history text-xl"></i>
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-xl font-bold text-gray-900">Mail Dispatch History & Delivery Logs</h1>
                    <span class="px-2 py-0.5 text-[11px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-200 rounded-full font-mono"><?= number_format($stats['campaigns'] ?? 0) ?> Campaigns</span>
                </div>
                <p class="text-xs text-gray-500 mt-0.5">Audit outgoing email dispatches, check recipient deliverability, sender mailbox rotation, and review follow-up logs.</p>
            </div>
        </div>

        <div class="flex items-center gap-2 flex-wrap">
            <a href="<?= base_url('communications/bulk_mail') ?>" class="px-3.5 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl transition-all shadow-xs flex items-center gap-1.5">
                <i class="fa fa-paper-plane"></i> Send Bulk Mail
            </a>
            <a href="<?= base_url('communications/smtp_settings') ?>" class="px-3.5 py-2 bg-purple-50 hover:bg-purple-100 text-purple-700 border border-purple-200 text-xs font-semibold rounded-xl transition-all shadow-xs flex items-center gap-1.5">
                <i class="fa fa-server"></i> SMTP Pool
            </a>
            <button type="button" id="btn-refresh-history" class="px-3.5 py-2 bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 text-xs font-semibold rounded-xl transition-all shadow-xs flex items-center gap-1.5 cursor-pointer">
                <i class="fa fa-refresh text-indigo-600"></i> Refresh
            </button>
        </div>
    </div>

    <!-- Live Background Queue Heartbeat Banner -->
    <div id="queue-heartbeat-banner" class="bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 text-white p-4 rounded-2xl border border-indigo-800/40 shadow-md flex flex-col md:flex-row md:items-center justify-between gap-3">
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-indigo-600/40 border border-indigo-400/30 flex items-center justify-center text-indigo-300">
                <i class="fa fa-clock-o text-base animate-pulse"></i>
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <span class="text-xs font-bold text-white uppercase tracking-wider">Anti-Ban Background Queue</span>
                    <span class="px-2 py-0.5 text-[10px] font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 rounded-full flex items-center gap-1">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-ping"></span> 2-Min Paced Interval
                    </span>
                </div>
                <p class="text-[11px] text-gray-300 mt-0.5">
                    Next safe email dispatch in: <span id="queue-countdown" class="font-mono font-bold text-amber-300">02:00</span>
                    <span class="text-gray-400 mx-1.5">•</span>
                    <span id="queue-pending-count" class="font-mono text-indigo-200 font-bold"><?= (int)($stats['queued'] ?? 0) ?></span> email(s) currently in queue.
                </p>
            </div>
        </div>
        <div class="flex items-center gap-2 self-end md:self-center">
            <button type="button" id="btn-trigger-queue-now" class="px-3 py-1.5 bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-semibold rounded-xl transition shadow-xs flex items-center gap-1.5 cursor-pointer">
                <i class="fa fa-bolt"></i> Send Next Now
            </button>
        </div>
    </div>

    <!-- Quick Metrics Strip -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-xs">
        <div class="p-4 bg-white border border-gray-100 rounded-2xl shadow-xs flex items-center gap-3.5">
            <div class="w-11 h-11 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold text-lg flex-shrink-0 border border-emerald-100">
                <i class="fa fa-check-circle"></i>
            </div>
            <div>
                <span class="block text-xl font-black text-gray-900 font-mono" id="stat-total-sent"><?= number_format($stats['sent'] ?? 0) ?></span>
                <span class="text-[11px] font-semibold text-gray-500 uppercase tracking-tight">Delivered Emails</span>
            </div>
        </div>
        <div class="p-4 bg-white border border-gray-100 rounded-2xl shadow-xs flex items-center gap-3.5">
            <div class="w-11 h-11 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center font-bold text-lg flex-shrink-0 border border-amber-100">
                <i class="fa fa-clock-o"></i>
            </div>
            <div>
                <span class="block text-xl font-black text-gray-900 font-mono" id="stat-total-queued"><?= number_format($stats['queued'] ?? 0) ?></span>
                <span class="text-[11px] font-semibold text-gray-500 uppercase tracking-tight">Queued / Sending</span>
            </div>
        </div>
        <div class="p-4 bg-white border border-gray-100 rounded-2xl shadow-xs flex items-center gap-3.5">
            <div class="w-11 h-11 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center font-bold text-lg flex-shrink-0 border border-rose-100">
                <i class="fa fa-exclamation-circle"></i>
            </div>
            <div>
                <span class="block text-xl font-black text-gray-900 font-mono" id="stat-total-failed"><?= number_format($stats['failed'] ?? 0) ?></span>
                <span class="text-[11px] font-semibold text-gray-500 uppercase tracking-tight">Failed Dispatches</span>
            </div>
        </div>
        <div class="p-4 bg-white border border-gray-100 rounded-2xl shadow-xs flex items-center gap-3.5">
            <div class="w-11 h-11 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold text-lg flex-shrink-0 border border-indigo-100">
                <i class="fa fa-bullhorn"></i>
            </div>
            <div>
                <span class="block text-xl font-black text-gray-900 font-mono" id="stat-total-campaigns"><?= number_format($stats['campaigns'] ?? 0) ?></span>
                <span class="text-[11px] font-semibold text-gray-500 uppercase tracking-tight">Total Campaigns</span>
            </div>
        </div>
    </div>

    <!-- Navigation Tabs & Action Bar -->
    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-200 pb-2">
        <div class="flex items-center gap-2">
            <button type="button" id="tab-btn-delivery-logs" class="history-tab-btn active px-4 py-2 text-xs font-bold border-b-2 border-indigo-600 text-indigo-600 flex items-center gap-2 cursor-pointer transition">
                <i class="fa fa-envelope-open-o"></i> Individual Delivery Logs
                <span class="px-2 py-0.5 text-[10px] font-mono bg-indigo-50 text-indigo-700 rounded-full border border-indigo-200" id="badge-total-logs"><?= number_format($total_logs ?? 0) ?></span>
            </button>
            <button type="button" id="tab-btn-campaigns" class="history-tab-btn px-4 py-2 text-xs font-bold border-b-2 border-transparent text-gray-500 hover:text-gray-700 flex items-center gap-2 cursor-pointer transition">
                <i class="fa fa-list-alt"></i> Campaign Batches
                <span class="px-2 py-0.5 text-[10px] font-mono bg-gray-100 text-gray-600 rounded-full"><?= count($recent_campaigns ?? []) ?></span>
            </button>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" id="btn-export-logs" class="px-4 py-2 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white text-xs font-bold rounded-xl transition-all shadow-xs flex items-center gap-2 cursor-pointer">
                <i class="fa fa-file-excel-o"></i> Export CSV / Excel
            </button>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- TAB 1: INDIVIDUAL DELIVERY LOGS & RICH FILTERS                            -->
    <!-- ========================================================================= -->
    <div id="tab-content-delivery-logs" class="space-y-4">
        <!-- Filter Toolbar -->
        <div class="bg-white rounded-2xl border border-gray-100 p-4 shadow-xs space-y-3">
            <!-- Quick Date Shortcut Pills -->
            <div class="flex flex-wrap items-center justify-between gap-2 pb-2.5 border-b border-gray-100 text-xs">
                <div class="flex items-center gap-1.5 flex-wrap">
                    <span class="text-[11px] font-bold text-gray-500 uppercase mr-1">Quick Date:</span>
                    <button type="button" class="btn-quick-date active px-2.5 py-1 rounded-lg text-[11px] font-semibold bg-indigo-50 text-indigo-700 hover:bg-indigo-100 transition cursor-pointer" data-range="all">All Time</button>
                    <button type="button" class="btn-quick-date px-2.5 py-1 rounded-lg text-[11px] font-semibold bg-gray-100 text-gray-700 hover:bg-gray-200 transition cursor-pointer" data-range="today">Today</button>
                    <button type="button" class="btn-quick-date px-2.5 py-1 rounded-lg text-[11px] font-semibold bg-gray-100 text-gray-700 hover:bg-gray-200 transition cursor-pointer" data-range="yesterday">Yesterday</button>
                    <button type="button" class="btn-quick-date px-2.5 py-1 rounded-lg text-[11px] font-semibold bg-gray-100 text-gray-700 hover:bg-gray-200 transition cursor-pointer" data-range="7days">Last 7 Days</button>
                    <button type="button" class="btn-quick-date px-2.5 py-1 rounded-lg text-[11px] font-semibold bg-gray-100 text-gray-700 hover:bg-gray-200 transition cursor-pointer" data-range="month">This Month</button>
                </div>
                <span class="text-[11px] text-gray-400 font-mono">Syncs automatically with Export</span>
            </div>

            <form id="filter-logs-form" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-7 gap-2.5 items-end">
                <!-- From Date -->
                <div>
                    <label class="block text-[11px] font-semibold text-gray-600 mb-1">From Date</label>
                    <input type="date" id="filter-from-date" class="w-full px-2.5 py-1.5 bg-gray-50 border border-gray-200 rounded-lg text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                </div>

                <!-- To Date -->
                <div>
                    <label class="block text-[11px] font-semibold text-gray-600 mb-1">To Date</label>
                    <input type="date" id="filter-to-date" class="w-full px-2.5 py-1.5 bg-gray-50 border border-gray-200 rounded-lg text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                </div>

                <!-- Campaign Purpose / Stage Filter -->
                <div>
                    <label class="block text-[11px] font-semibold text-gray-600 mb-1">Purpose / Stage</label>
                    <select id="filter-campaign-type" class="w-full px-2.5 py-1.5 bg-gray-50 border border-gray-200 rounded-lg text-xs font-semibold text-gray-800 focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                        <option value="all">All Purposes / Stages</option>
                        <?php if(!empty($outreach_stages)): foreach($outreach_stages as $stg): ?>
                            <option value="<?= esc_html($stg['id']) ?>"><?= esc_html($stg['name']) ?></option>
                        <?php endforeach; else: ?>
                            <option value="outreach">🚀 Initial Outreach</option>
                            <option value="followup_1">🔁 Follow-Up #1</option>
                            <option value="followup_2">⚡ Follow-Up #2</option>
                            <option value="retry">🛠️ Retry Resend</option>
                            <option value="announcement">📢 Announcement</option>
                        <?php endif; ?>
                    </select>
                </div>

                <!-- Follow-Up Due Filter -->
                <div>
                    <label class="block text-[11px] font-semibold text-gray-600 mb-1">Follow-Up Due</label>
                    <select id="filter-followup-due" class="w-full px-2.5 py-1.5 bg-gray-50 border border-gray-200 rounded-lg text-xs font-semibold text-gray-800 focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                        <option value="all">All Follow-Up Dates</option>
                        <option value="today">📅 Due Today</option>
                        <option value="overdue">⚠️ Overdue Follow-Ups</option>
                        <option value="upcoming">⏳ Upcoming Follow-Ups</option>
                        <option value="has_followup">Any Scheduled Follow-Up</option>
                    </select>
                </div>

                <!-- Sender Mailbox Filter -->
                <div>
                    <label class="block text-[11px] font-semibold text-gray-600 mb-1">Sender Mailbox</label>
                    <select id="filter-sender-email" class="w-full px-2.5 py-1.5 bg-gray-50 border border-gray-200 rounded-lg text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                        <option value="all">All Senders (Rotated)</option>
                        <?php if(!empty($distinct_senders)): foreach($distinct_senders as $s): ?>
                        <option value="<?= esc_html($s) ?>"><?= esc_html($s) ?></option>
                        <?php endforeach; endif; ?>
                    </select>
                </div>

                <!-- Status Filter -->
                <div>
                    <label class="block text-[11px] font-semibold text-gray-600 mb-1">Delivery Status</label>
                    <select id="filter-status" class="w-full px-2.5 py-1.5 bg-gray-50 border border-gray-200 rounded-lg text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                        <option value="all">All Statuses</option>
                        <option value="sent">Delivered (Sent)</option>
                        <option value="queued">Queued / Pending</option>
                        <option value="failed">Failed</option>
                    </select>
                </div>

                <!-- Search Input & Reset Buttons -->
                <div>
                    <label class="block text-[11px] font-semibold text-gray-600 mb-1">Search Lead / Ref</label>
                    <div class="flex items-center gap-1.5">
                        <input type="text" id="filter-search" placeholder="Email, name or ref..." class="w-full px-2.5 py-1.5 bg-gray-50 border border-gray-200 rounded-lg text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                        <button type="submit" class="px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-xs font-bold transition flex-shrink-0 cursor-pointer" title="Apply Filter">
                            <i class="fa fa-filter"></i>
                        </button>
                        <button type="button" id="btn-reset-filters" class="px-2.5 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-lg text-xs font-bold transition flex-shrink-0 cursor-pointer" title="Reset Filters">
                            <i class="fa fa-undo"></i>
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Delivery Logs Table -->
        <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-xs">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="bg-gray-50/80 text-gray-500 uppercase tracking-wider font-semibold border-b border-gray-200">
                            <th class="py-3 px-3">RECIPIENT</th>
                            <th class="py-3 px-3">PURPOSE</th>
                            <th class="py-3 px-3">CAMPAIGN / SUBJECT</th>
                            <th class="py-3 px-3">SENDER MAILBOX</th>
                            <th class="py-3 px-3 text-center">ANTI-SPAM REF</th>
                            <th class="py-3 px-3">DISPATCHED AT</th>
                            <th class="py-3 px-3">FOLLOW-UP DATE</th>
                            <th class="py-3 px-3 text-right">STATUS</th>
                        </tr>
                    </thead>
                    <tbody id="delivery-logs-body" class="divide-y divide-gray-100">
                        <?php if(!empty($delivery_logs)): foreach($delivery_logs as $log): 
                            $stBadge = 'bg-emerald-50 text-emerald-700 border-emerald-200';
                            if ($log['status'] === 'queued') $stBadge = 'bg-amber-50 text-amber-700 border-amber-200';
                            elseif ($log['status'] === 'failed') $stBadge = 'bg-rose-50 text-rose-700 border-rose-200';
                        ?>
                        <tr class="hover:bg-gray-50/60 transition-colors">
                            <td class="py-3 px-3">
                                <span class="font-bold text-gray-900 block"><?= esc_html($log['recipient_name'] ?: 'Customer') ?></span>
                                <span class="text-[11px] text-gray-500 font-mono"><?= esc_html($log['recipient_email']) ?></span>
                            </td>
                            <td class="py-3 px-3">
                                <?= render_campaign_type_badge($log['campaign_type'] ?? 'outreach') ?>
                            </td>
                            <td class="py-3 px-3 max-w-xs truncate">
                                <span class="font-medium text-gray-800 block truncate" title="<?= esc_html($log['campaign_subject'] ?? '') ?>"><?= esc_html($log['campaign_subject'] ?? 'Direct Outreach') ?></span>
                                <?php if(!empty($log['product_name'])): ?>
                                <span class="text-[10px] font-bold text-purple-700 bg-purple-50 px-1.5 py-0.5 rounded border border-purple-200"><?= esc_html($log['product_name']) ?></span>
                                <?php endif; ?>
                            </td>
                            <td class="py-3 px-3">
                                <?php if(!empty($log['sender_email'])): ?>
                                <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-gray-800">
                                    <i class="fa fa-envelope-o text-indigo-500"></i> <?= esc_html($log['sender_email']) ?>
                                </span>
                                <?php if(!empty($log['sender_mailbox_name'])): ?>
                                <span class="text-[10px] text-gray-400 block"><?= esc_html($log['sender_mailbox_name']) ?></span>
                                <?php endif; ?>
                                <?php else: ?>
                                <span class="text-gray-400 italic text-[11px]">Auto-assign on send</span>
                                <?php endif; ?>
                            </td>
                            <td class="py-3 px-3 text-center">
                                <?php if(!empty($log['anti_spam_hash'])): ?>
                                <span class="px-2 py-0.5 font-mono text-[10px] font-bold bg-slate-100 text-slate-800 border border-slate-300 rounded-md tracking-wider">
                                    #<?= esc_html($log['anti_spam_hash']) ?>
                                </span>
                                <?php else: ?>
                                <span class="text-gray-300 text-[10px]">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="py-3 px-3 text-gray-600 text-[11px] whitespace-nowrap">
                                <?= !empty($log['sent_at']) ? date('d M Y, h:i A', strtotime($log['sent_at'])) : '<span class="text-amber-500 italic">In Queue</span>' ?>
                            </td>
                            <td class="py-3 px-3 whitespace-nowrap">
                                <?php if(!empty($log['next_followup_date'])): ?>
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 text-[11px] font-semibold bg-blue-50 text-blue-700 border border-blue-200 rounded-lg">
                                    <i class="fa fa-calendar-check-o text-[10px]"></i> <?= date('d M Y', strtotime($log['next_followup_date'])) ?>
                                </span>
                                <?php else: ?>
                                <span class="text-gray-300 text-[11px]">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="py-3 px-3 text-right">
                                <span class="px-2 py-0.5 text-[10px] font-bold rounded-full border <?= $stBadge ?> uppercase">
                                    <?= esc_html($log['status']) ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; else: ?>
                        <tr id="empty-logs-row">
                            <td colspan="8" class="py-8 text-center text-gray-400 text-xs">
                                <i class="fa fa-inbox text-3xl block mb-2 opacity-50"></i>
                                No delivery logs found matching the selected filters.
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- TAB 2: CAMPAIGN BATCHES TABLE                                             -->
    <!-- ========================================================================= -->
    <div id="tab-content-campaigns" class="hidden bg-white rounded-2xl border border-gray-100 p-5 shadow-xs space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-gray-100">
            <div>
                <h3 class="text-sm font-bold text-gray-900">Campaign Dispatch Logs</h3>
                <p class="text-xs text-gray-500">Click "Details" on any campaign to audit delivered recipients and view the exact sent message.</p>
            </div>
            <div class="relative w-full sm:w-64">
                <i class="fa fa-search absolute left-3 top-2.5 text-gray-400 text-xs"></i>
                <input type="text" id="input-search-history" placeholder="Search campaign logs..." class="w-full pl-8 pr-3 py-1.5 bg-gray-50 border border-gray-200 rounded-xl text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-none">
            </div>
        </div>

        <!-- Campaign Table -->
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse" id="history-datatable">
                <thead>
                    <tr class="bg-gray-50/80 text-gray-500 uppercase tracking-wider font-semibold border-b border-gray-200">
                        <th class="py-3 px-3">CAMPAIGN ID</th>
                        <th class="py-3 px-3">PURPOSE</th>
                        <th class="py-3 px-3">SUBJECT & TEMPLATE</th>
                        <th class="py-3 px-3">AUDIENCE / PRODUCT</th>
                        <th class="py-3 px-3 text-center">DELIVERED</th>
                        <th class="py-3 px-3 text-center">QUEUED</th>
                        <th class="py-3 px-3 text-center">FAILED</th>
                        <th class="py-3 px-3">STATUS</th>
                        <th class="py-3 px-3">SENT DATE</th>
                        <th class="py-3 px-3 text-right">ACTIONS</th>
                    </tr>
                </thead>
                <tbody id="history-table-body" class="divide-y divide-gray-100">
                    <?php if(!empty($recent_campaigns)): foreach($recent_campaigns as $camp): 
                        $badgeStatus = 'bg-emerald-50 text-emerald-700 border-emerald-200';
                        if ($camp['status'] === 'pending') $badgeStatus = 'bg-amber-50 text-amber-700 border-amber-200';
                        elseif ($camp['status'] === 'processing') $badgeStatus = 'bg-blue-50 text-blue-700 border-blue-200';
                    ?>
                    <tr class="hover:bg-gray-50/60 transition-colors history-table-row">
                        <td class="py-3 px-3 font-mono font-bold text-gray-800">#<?= $camp['id'] ?></td>
                        <td class="py-3 px-3">
                            <?= render_campaign_type_badge($camp['campaign_type'] ?? 'outreach') ?>
                            <?php if(!empty($camp['next_followup_days'])): ?>
                            <span class="block text-[10px] text-gray-400 mt-0.5 font-sans">+<?= (int)$camp['next_followup_days'] ?>d cadence</span>
                            <?php endif; ?>
                        </td>
                        <td class="py-3 px-3">
                            <span class="font-bold text-gray-900 block"><?= esc_html($camp['subject']) ?></span>
                            <span class="text-[11px] text-gray-400"><?= esc_html($camp['template_name'] ?: 'Custom Compose') ?></span>
                        </td>
                        <td class="py-3 px-3">
                            <span class="font-medium text-gray-800 block"><?= esc_html($camp['recipient_type']) ?></span>
                            <?php if(!empty($camp['product_name'])): ?>
                            <span class="text-[10px] font-bold text-purple-700 bg-purple-50 px-1.5 py-0.5 rounded border border-purple-200"><?= esc_html($camp['product_name']) ?></span>
                            <?php endif; ?>
                        </td>
                        <td class="py-3 px-3 text-center font-bold text-emerald-600 font-mono"><?= (int)$camp['count_sent'] ?></td>
                        <td class="py-3 px-3 text-center font-bold text-amber-600 font-mono"><?= (int)$camp['count_queued'] ?></td>
                        <td class="py-3 px-3 text-center font-bold text-rose-600 font-mono"><?= (int)$camp['count_failed'] ?></td>
                        <td class="py-3 px-3">
                            <span class="px-2 py-0.5 text-[10px] font-bold rounded-full border <?= $badgeStatus ?> uppercase">
                                <?= esc_html($camp['status']) ?>
                            </span>
                        </td>
                        <td class="py-3 px-3 text-gray-500 text-[11px] whitespace-nowrap"><?= date('d M Y, h:i A', strtotime($camp['created_at'])) ?></td>
                        <td class="py-3 px-3 text-right">
                            <button type="button" class="btn-view-campaign-details px-2.5 py-1 text-xs font-semibold bg-gray-100 hover:bg-blue-50 text-gray-700 hover:text-blue-700 rounded-lg transition-colors border border-gray-200 cursor-pointer" data-id="<?= $camp['id'] ?>">
                                <i class="fa fa-eye mr-1"></i> Details
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; else: ?>
                    <tr>
                        <td colspan="10" class="py-8 text-center text-gray-400 text-xs">
                            <i class="fa fa-paper-plane-o text-2xl block mb-2 opacity-50"></i>
                            No campaign dispatches found yet.
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL: CAMPAIGN DETAILS & RECIPIENT LOG                                   -->
<!-- ========================================================================= -->
<div id="modal-campaign-detail" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-xs p-4 overflow-y-auto">
    <div class="bg-white rounded-2xl shadow-2xl border border-gray-100 w-full max-w-4xl max-h-[90vh] flex flex-col overflow-hidden animate-in fade-in zoom-in-95 duration-200">
        <div class="px-6 py-4 bg-slate-900 text-white flex items-center justify-between flex-shrink-0">
            <div class="flex items-center gap-2">
                <i class="fa fa-info-circle text-blue-400 text-lg"></i>
                <h3 class="text-sm font-bold" id="detail-modal-title">Campaign Details</h3>
            </div>
            <button type="button" id="btn-close-campaign-modal" class="text-white/80 hover:text-white text-lg cursor-pointer">
                <i class="fa fa-times"></i>
            </button>
        </div>

        <div class="p-6 overflow-y-auto space-y-5 flex-grow">
            <!-- Summary Header Box -->
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 text-xs bg-gray-50 p-4 rounded-xl border border-gray-200">
                <div>
                    <span class="text-gray-400 block text-[10px] font-bold uppercase">Audience Group</span>
                    <span id="detail-recipient-type" class="font-bold text-gray-800">-</span>
                </div>
                <div>
                    <span class="text-gray-400 block text-[10px] font-bold uppercase">Target Product</span>
                    <span id="detail-product-name" class="font-bold text-purple-700">-</span>
                </div>
                <div>
                    <span class="text-gray-400 block text-[10px] font-bold uppercase">Campaign Purpose</span>
                    <span id="detail-campaign-type" class="font-bold text-indigo-700">-</span>
                </div>
                <div>
                    <span class="text-gray-400 block text-[10px] font-bold uppercase">Follow-up Cadence</span>
                    <span id="detail-followup-schedule" class="font-bold text-blue-700">-</span>
                </div>
                <div>
                    <span class="text-gray-400 block text-[10px] font-bold uppercase">Total Recipients</span>
                    <span id="detail-total-recipients" class="font-black text-gray-900 font-mono">-</span>
                </div>
                <div>
                    <span class="text-gray-400 block text-[10px] font-bold uppercase">Sent Timestamp</span>
                    <span id="detail-created-at" class="font-medium text-gray-700">-</span>
                </div>
            </div>

            <!-- Subject & Sent Message Viewer -->
            <div class="space-y-1.5">
                <span class="text-xs font-bold text-gray-700">Subject: <span id="detail-subject" class="font-normal text-gray-900"></span></span>
                <div class="border border-gray-200 rounded-xl p-4 bg-white max-h-56 overflow-y-auto text-xs" id="detail-message-body">
                    <!-- HTML content -->
                </div>
            </div>

            <!-- Recipients Delivery Log -->
            <div class="space-y-2">
                <span class="text-xs font-bold text-gray-700">Recipient Dispatch Status:</span>
                <div class="border border-gray-200 rounded-xl overflow-hidden max-h-56 overflow-y-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-gray-100 text-gray-600 font-bold">
                            <tr>
                                <th class="p-2.5">Recipient</th>
                                <th class="p-2.5">Email</th>
                                <th class="p-2.5 text-center">Status</th>
                                <th class="p-2.5 text-right">Sent Time</th>
                            </tr>
                        </thead>
                        <tbody id="detail-queue-table" class="divide-y divide-gray-100 font-mono">
                            <!-- Populated via AJAX -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="px-6 py-3 bg-gray-50 border-t border-gray-100 flex justify-end flex-shrink-0">
            <button type="button" id="btn-close-campaign-modal-bottom" class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 rounded-xl text-xs font-bold transition-colors cursor-pointer">
                Close
            </button>
        </div>
    </div>
</div>

<script>
$(function() {
    // Helper function for rendering campaign type badges in JS
    function renderTypeBadge(type) {
        if (type === 'followup_1') return '<span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-blue-50 text-blue-700 border border-blue-200 whitespace-nowrap">Follow-Up #1</span>';
        if (type === 'followup_2') return '<span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-purple-50 text-purple-700 border border-purple-200 whitespace-nowrap">Follow-Up #2</span>';
        if (type === 'retry') return '<span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-rose-50 text-rose-700 border border-rose-200 whitespace-nowrap">Retry Resend</span>';
        if (type === 'announcement') return '<span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-amber-50 text-amber-700 border border-amber-200 whitespace-nowrap">Announcement</span>';
        return '<span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-indigo-50 text-indigo-700 border border-indigo-200 whitespace-nowrap">Outreach</span>';
    }

    // Tab Switching
    $('#tab-btn-delivery-logs').on('click', function() {
        $('.history-tab-btn').removeClass('active border-indigo-600 text-indigo-600').addClass('border-transparent text-gray-500');
        $(this).addClass('active border-indigo-600 text-indigo-600').removeClass('border-transparent text-gray-500');
        $('#tab-content-delivery-logs').removeClass('hidden');
        $('#tab-content-campaigns').addClass('hidden');
    });

    $('#tab-btn-campaigns').on('click', function() {
        $('.history-tab-btn').removeClass('active border-indigo-600 text-indigo-600').addClass('border-transparent text-gray-500');
        $(this).addClass('active border-indigo-600 text-indigo-600').removeClass('border-transparent text-gray-500');
        $('#tab-content-campaigns').removeClass('hidden');
        $('#tab-content-delivery-logs').addClass('hidden');
    });

    // Delivery Logs AJAX Filtering
    function fetchDeliveryLogs() {
        var params = {
            from_date: $('#filter-from-date').val(),
            to_date: $('#filter-to-date').val(),
            campaign_type: $('#filter-campaign-type').val(),
            followup_due: $('#filter-followup-due').val(),
            sender_email: $('#filter-sender-email').val(),
            status: $('#filter-status').val(),
            search: $('#filter-search').val()
        };

        $('#delivery-logs-body').html('<tr><td colspan="8" class="py-6 text-center text-gray-400 text-xs"><i class="fa fa-spinner fa-spin mr-1"></i> Filtering delivery logs...</td></tr>');

        $.getJSON(BASE_URL + 'communications/delivery_logs_ajax', params, function(resp) {
            if (resp.status === 'success' && resp.data) {
                var rows = resp.data.rows;
                $('#badge-total-logs').text(resp.data.total);

                if (!rows || rows.length === 0) {
                    $('#delivery-logs-body').html('<tr><td colspan="8" class="py-8 text-center text-gray-400 text-xs"><i class="fa fa-inbox text-3xl block mb-2 opacity-50"></i> No delivery logs found matching the selected filters.</td></tr>');
                    return;
                }

                var html = '';
                rows.forEach(function(log) {
                    var stBadge = 'bg-emerald-50 text-emerald-700 border-emerald-200';
                    if (log.status === 'queued') stBadge = 'bg-amber-50 text-amber-700 border-amber-200';
                    else if (log.status === 'failed') stBadge = 'bg-rose-50 text-rose-700 border-rose-200';

                    var pBadge = log.product_name ? '<span class="text-[10px] font-bold text-purple-700 bg-purple-50 px-1.5 py-0.5 rounded border border-purple-200 ml-1">' + $('<div>').text(log.product_name).html() + '</span>' : '';
                    var senderHtml = log.sender_email ? '<span class="inline-flex items-center gap-1.5 text-xs font-semibold text-gray-800"><i class="fa fa-envelope-o text-indigo-500"></i> ' + $('<div>').text(log.sender_email).html() + '</span>' : '<span class="text-gray-400 italic text-[11px]">Auto-assign on send</span>';
                    var hashHtml = log.anti_spam_hash ? '<span class="px-2 py-0.5 font-mono text-[10px] font-bold bg-slate-100 text-slate-800 border border-slate-300 rounded-md tracking-wider">#' + $('<div>').text(log.anti_spam_hash).html() + '</span>' : '<span class="text-gray-300 text-[10px]">-</span>';
                    var followupHtml = log.next_followup_date ? '<span class="inline-flex items-center gap-1 px-2 py-0.5 text-[11px] font-semibold bg-blue-50 text-blue-700 border border-blue-200 rounded-lg"><i class="fa fa-calendar-check-o text-[10px]"></i> ' + log.next_followup_date + '</span>' : '<span class="text-gray-300 text-[11px]">-</span>';
                    var sentAtHtml = log.sent_at ? log.sent_at : '<span class="text-amber-500 italic">In Queue</span>';

                    html += '<tr class="hover:bg-gray-50/60 transition-colors">';
                    html += '<td class="py-3 px-3"><span class="font-bold text-gray-900 block">' + $('<div>').text(log.recipient_name || 'Customer').html() + '</span><span class="text-[11px] text-gray-500 font-mono">' + $('<div>').text(log.recipient_email).html() + '</span></td>';
                    html += '<td class="py-3 px-3">' + renderTypeBadge(log.campaign_type || 'outreach') + '</td>';
                    html += '<td class="py-3 px-3 max-w-xs truncate"><span class="font-medium text-gray-800 block truncate">' + $('<div>').text(log.campaign_subject || 'Direct Outreach').html() + '</span>' + pBadge + '</td>';
                    html += '<td class="py-3 px-3">' + senderHtml + '</td>';
                    html += '<td class="py-3 px-3 text-center">' + hashHtml + '</td>';
                    html += '<td class="py-3 px-3 text-gray-600 text-[11px] whitespace-nowrap">' + sentAtHtml + '</td>';
                    html += '<td class="py-3 px-3 whitespace-nowrap">' + followupHtml + '</td>';
                    html += '<td class="py-3 px-3 text-right"><span class="px-2 py-0.5 text-[10px] font-bold rounded-full border ' + stBadge + ' uppercase">' + log.status + '</span></td>';
                    html += '</tr>';
                });
                $('#delivery-logs-body').html(html);
            }
        });
    }

    // Quick Date Shortcut Buttons
    $('.btn-quick-date').on('click', function () {
        $('.btn-quick-date').removeClass('active bg-indigo-50 text-indigo-700').addClass('bg-gray-100 text-gray-700');
        $(this).addClass('active bg-indigo-50 text-indigo-700').removeClass('bg-gray-100 text-gray-700');

        var range = $(this).data('range');
        var now = new Date();
        var formatDate = function (d) {
            var month = '' + (d.getMonth() + 1),
                day = '' + d.getDate(),
                year = d.getFullYear();
            if (month.length < 2) month = '0' + month;
            if (day.length < 2) day = '0' + day;
            return [year, month, day].join('-');
        };

        if (range === 'today') {
            var t = formatDate(now);
            $('#filter-from-date').val(t);
            $('#filter-to-date').val(t);
        } else if (range === 'yesterday') {
            var y = new Date();
            y.setDate(now.getDate() - 1);
            var yStr = formatDate(y);
            $('#filter-from-date').val(yStr);
            $('#filter-to-date').val(yStr);
        } else if (range === '7days') {
            var past = new Date();
            past.setDate(now.getDate() - 7);
            $('#filter-from-date').val(formatDate(past));
            $('#filter-to-date').val(formatDate(now));
        } else if (range === 'month') {
            var firstDay = new Date(now.getFullYear(), now.getMonth(), 1);
            $('#filter-from-date').val(formatDate(firstDay));
            $('#filter-to-date').val(formatDate(now));
        } else {
            $('#filter-from-date').val('');
            $('#filter-to-date').val('');
        }
        fetchDeliveryLogs();
    });

    // Auto-fetch on dropdown filter changes
    $('#filter-campaign-type, #filter-followup-due, #filter-sender-email, #filter-status, #filter-from-date, #filter-to-date').on('change', function () {
        fetchDeliveryLogs();
    });

    // Debounced search on typing
    var searchTimer;
    $('#filter-search').on('input', function () {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(fetchDeliveryLogs, 350);
    });

    $('#filter-logs-form').on('submit', function(e) {
        e.preventDefault();
        fetchDeliveryLogs();
    });

    $('#btn-reset-filters').on('click', function() {
        $('#filter-from-date').val('');
        $('#filter-to-date').val('');
        $('#filter-campaign-type').val('all');
        $('#filter-followup-due').val('all');
        $('#filter-sender-email').val('all');
        $('#filter-status').val('all');
        $('#filter-search').val('');
        $('.btn-quick-date').removeClass('active bg-indigo-50 text-indigo-700').addClass('bg-gray-100 text-gray-700');
        $('.btn-quick-date[data-range="all"]').addClass('active bg-indigo-50 text-indigo-700').removeClass('bg-gray-100 text-gray-700');
        fetchDeliveryLogs();
    });

    // ── EXPORT CSV / EXCEL HANDLER ──────────────────────────────────────────
    $('#btn-export-logs').on('click', function(e) {
        e.preventDefault();
        var params = {
            from_date: $('#filter-from-date').val(),
            to_date: $('#filter-to-date').val(),
            campaign_type: $('#filter-campaign-type').val(),
            followup_due: $('#filter-followup-due').val(),
            sender_email: $('#filter-sender-email').val(),
            status: $('#filter-status').val(),
            search: $('#filter-search').val()
        };
        var url = BASE_URL + 'communications/export_delivery_logs_csv?' + $.param(params);
        window.location.href = url;
    });

    // Search campaign batch table
    $('#input-search-history').on('keyup', function() {
        var q = $(this).val().toLowerCase().trim();
        $('.history-table-row').each(function() {
            var text = $(this).text().toLowerCase();
            if (!q || text.indexOf(q) !== -1) {
                $(this).removeClass('hidden');
            } else {
                $(this).addClass('hidden');
            }
        });
    });

    // Refresh history
    $('#btn-refresh-history').on('click', function() {
        var $btn = $(this);
        $btn.find('i').addClass('fa-spin');
        fetchDeliveryLogs();
        $.getJSON(BASE_URL + 'communications/history_ajax', function(resp) {
            $btn.find('i').removeClass('fa-spin');
            if (resp.status === 'success' && resp.data) {
                if (resp.data.stats) {
                    $('#stat-total-sent').text(resp.data.stats.sent || 0);
                    $('#stat-total-queued').text(resp.data.stats.queued || 0);
                    $('#stat-total-failed').text(resp.data.stats.failed || 0);
                    $('#stat-total-campaigns').text(resp.data.stats.campaigns || 0);
                    $('#queue-pending-count').text(resp.data.stats.queued || 0);
                }
                CRM.toast('info', 'Dispatch history log refreshed.');
            }
        });
    });

    // View Campaign Details
    $(document).on('click', '.btn-view-campaign-details', function() {
        var id = $(this).data('id');
        $.getJSON(BASE_URL + 'communications/campaign_detail_ajax/' + id, function(resp) {
            if (resp.status === 'success' && resp.data) {
                var c = resp.data;
                var q = resp.data.items || [];

                $('#detail-modal-title').text('Campaign #' + c.id + ' — ' + c.subject);
                $('#detail-recipient-type').text(c.recipient_type);
                $('#detail-product-name').text(c.product_name || 'General (All)');
                
                var typeLabel = 'Initial Outreach';
                if (c.campaign_type === 'followup_1') typeLabel = 'Follow-Up #1';
                else if (c.campaign_type === 'followup_2') typeLabel = 'Follow-Up #2';
                else if (c.campaign_type === 'retry') typeLabel = 'Retry Resend';
                else if (c.campaign_type === 'announcement') typeLabel = 'Announcement';
                $('#detail-campaign-type').text(typeLabel);

                var cadence = c.next_followup_days ? '+' + c.next_followup_days + ' Days' : 'Immediate';
                $('#detail-followup-schedule').text(cadence);

                $('#detail-total-recipients').text(c.total_recipients);
                $('#detail-created-at').text(c.created_at);
                $('#detail-subject').text(c.subject);
                $('#detail-message-body').html(c.message);

                var qHtml = '';
                q.forEach(function(row) {
                    var stClass = 'text-emerald-600';
                    if (row.status === 'failed') stClass = 'text-rose-600 font-bold';
                    else if (row.status === 'queued') stClass = 'text-amber-600';

                    qHtml += '<tr>';
                    qHtml += '<td class="p-2.5 font-sans font-medium text-gray-800">' + $('<div>').text(row.recipient_name || '-').html() + '</td>';
                    qHtml += '<td class="p-2.5 text-gray-600">' + $('<div>').text(row.recipient_email).html() + '</td>';
                    qHtml += '<td class="p-2.5 text-center ' + stClass + ' uppercase text-[10px]">' + row.status + '</td>';
                    qHtml += '<td class="p-2.5 text-right text-gray-400 text-[11px]">' + (row.sent_at || '-') + '</td>';
                    qHtml += '</tr>';
                });
                $('#detail-queue-table').html(qHtml);
                $('#modal-campaign-detail').removeClass('hidden');
            }
        });
    });

    $('#btn-close-campaign-modal, #btn-close-campaign-modal-bottom').on('click', function() {
        $('#modal-campaign-detail').addClass('hidden');
    });

    // ---------------------------------------------------------
    // 2-MINUTE PACED BACKGROUND QUEUE TIMER & AJAX HEARTBEAT
    // ---------------------------------------------------------
    var countdownSeconds = 120; // 2 minutes

    function updateCountdownDisplay() {
        var mins = Math.floor(countdownSeconds / 60);
        var secs = countdownSeconds % 60;
        var display = (mins < 10 ? '0' : '') + mins + ':' + (secs < 10 ? '0' : '') + secs;
        $('#queue-countdown').text(display);
    }

    function triggerQueueBatch() {
        var pending = parseInt($('#queue-pending-count').text()) || 0;
        if (pending <= 0) {
            countdownSeconds = 120;
            updateCountdownDisplay();
            return;
        }

        $('#queue-countdown').text('Sending...');
        $.getJSON(BASE_URL + 'communications/process_queue_batch_ajax', function(resp) {
            countdownSeconds = 120;
            updateCountdownDisplay();
            if (resp.status === 'success' && resp.data) {
                $('#queue-pending-count').text(resp.data.remaining);
                if (resp.data.processed > 0) {
                    var sentVal = parseInt($('#stat-total-sent').text().replace(/,/g, '')) || 0;
                    $('#stat-total-sent').text((sentVal + resp.data.processed).toLocaleString());
                    fetchDeliveryLogs();
                }
            }
        }).fail(function() {
            countdownSeconds = 120;
            updateCountdownDisplay();
        });
    }

    setInterval(function() {
        var pending = parseInt($('#queue-pending-count').text()) || 0;
        if (pending > 0) {
            countdownSeconds--;
            if (countdownSeconds <= 0) {
                triggerQueueBatch();
            } else {
                updateCountdownDisplay();
            }
        } else {
            $('#queue-countdown').text('Idle');
        }
    }, 1000);

    $('#btn-trigger-queue-now').on('click', function() {
        triggerQueueBatch();
    });
});
</script>
