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
                    <span id="queue-status-pill" class="px-2 py-0.5 text-[10px] font-bold <?= !empty($is_queue_paused) ? 'bg-amber-500/20 text-amber-300 border border-amber-500/30' : 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/30' ?> rounded-full flex items-center gap-1">
                        <?php if(!empty($is_queue_paused)): ?>
                            <i class="fa fa-pause"></i> Queue Paused
                        <?php else: ?>
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-ping"></span> 1-Min Paced Interval
                        <?php endif; ?>
                    </span>
                </div>
                <p class="text-[11px] text-gray-300 mt-0.5">
                    Next safe email dispatch in: <span id="queue-countdown" class="font-mono font-bold text-amber-300"><?= !empty($is_queue_paused) ? 'Paused ⏸️' : '01:00' ?></span>
                    <span class="text-gray-400 mx-1.5">•</span>
                    <span id="queue-pending-count" class="font-mono text-indigo-200 font-bold"><?= (int)($stats['queued'] ?? 0) ?></span> email(s) currently in queue.
                </p>
            </div>
        </div>
        <div class="flex items-center gap-2 self-end md:self-center">
            <button type="button" id="btn-toggle-global-queue" class="px-3 py-1.5 <?= !empty($is_queue_paused) ? 'bg-emerald-600 hover:bg-emerald-500' : 'bg-amber-600 hover:bg-amber-500' ?> text-white text-xs font-semibold rounded-xl transition shadow-xs flex items-center gap-1.5 cursor-pointer" title="Toggle automatic queue dispatching">
                <i class="fa <?= !empty($is_queue_paused) ? 'fa-play' : 'fa-pause' ?>"></i>
                <span><?= !empty($is_queue_paused) ? 'Resume Queue' : 'Pause Queue' ?></span>
            </button>
            <button type="button" id="btn-trigger-queue-now" class="px-3 py-1.5 bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-semibold rounded-xl transition shadow-xs flex items-center gap-1.5 cursor-pointer">
                <i class="fa fa-bolt"></i> Send Next Now
            </button>
        </div>
    </div>

    <!-- Quick Metrics Strip & Date Filter Header -->
    <div class="space-y-2.5">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="flex items-center gap-2">
                <span class="text-xs font-bold text-gray-800 uppercase tracking-wider flex items-center gap-1.5">
                    <i class="fa fa-bar-chart text-indigo-600"></i> Dispatch Metrics
                </span>
                <span id="label-active-stats-filter" class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-indigo-50 text-indigo-700 border border-indigo-200">All Time</span>
            </div>

            <!-- Date Filter Pills -->
            <div class="flex items-center gap-1 bg-white p-1 rounded-xl border border-gray-200 shadow-2xs text-xs flex-wrap">
                <button type="button" class="btn-stats-filter active px-2.5 py-1 font-semibold rounded-lg text-indigo-700 bg-indigo-50 border border-indigo-200 transition cursor-pointer" data-filter="all">
                    All Time
                </button>
                <button type="button" class="btn-stats-filter px-2.5 py-1 font-semibold rounded-lg text-gray-600 hover:text-gray-900 hover:bg-gray-100 transition cursor-pointer" data-filter="today">
                    Today
                </button>
                <button type="button" class="btn-stats-filter px-2.5 py-1 font-semibold rounded-lg text-gray-600 hover:text-gray-900 hover:bg-gray-100 transition cursor-pointer" data-filter="yesterday">
                    Yesterday
                </button>
                <button type="button" class="btn-stats-filter px-2.5 py-1 font-semibold rounded-lg text-gray-600 hover:text-gray-900 hover:bg-gray-100 transition cursor-pointer" data-filter="this_week">
                    This Week
                </button>
                <button type="button" class="btn-stats-filter px-2.5 py-1 font-semibold rounded-lg text-gray-600 hover:text-gray-900 hover:bg-gray-100 transition cursor-pointer" data-filter="this_month">
                    This Month
                </button>
                <button type="button" id="btn-toggle-stats-custom-date" class="px-2.5 py-1 font-semibold rounded-lg text-gray-600 hover:text-gray-900 hover:bg-gray-100 transition cursor-pointer inline-flex items-center gap-1">
                    <i class="fa fa-calendar text-[11px]"></i> Custom
                </button>
            </div>
        </div>

        <!-- Custom Date Range Picker (collapsible) -->
        <div id="stats-custom-date-wrap" class="hidden flex items-center justify-end gap-2 bg-indigo-50/60 p-2.5 rounded-xl border border-indigo-100 text-xs">
            <span class="text-gray-500 font-medium text-[11px]">From:</span>
            <input type="date" id="stats-from-date" class="px-2 py-1 bg-white border border-gray-300 rounded-lg text-xs text-gray-800 focus:outline-none focus:ring-1 focus:ring-indigo-500">
            <span class="text-gray-500 font-medium text-[11px]">To:</span>
            <input type="date" id="stats-to-date" class="px-2 py-1 bg-white border border-gray-300 rounded-lg text-xs text-gray-800 focus:outline-none focus:ring-1 focus:ring-indigo-500">
            <button type="button" id="btn-apply-stats-custom-date" class="px-3 py-1 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-bold text-xs cursor-pointer shadow-xs transition">
                Apply Filter
            </button>
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
                    <div class="flex items-center gap-1.5 flex-wrap">
                        <span class="block text-xl font-black text-gray-900 font-mono" id="stat-total-queued"><?= number_format($stats['queued'] ?? 0) ?></span>
                        <span id="stat-total-paused-badge" class="<?= empty($stats['paused']) ? 'hidden' : '' ?> text-[10px] text-amber-800 font-bold bg-amber-100 px-1.5 py-0.5 rounded-full"><span id="stat-total-paused"><?= (int)($stats['paused'] ?? 0) ?></span> paused</span>
                    </div>
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
                        <option value="queued">Queued / Sending</option>
                        <option value="paused">Paused</option>
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
                            elseif ($log['status'] === 'paused') $stBadge = 'bg-amber-100 text-amber-800 border-amber-300';
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
                                <div>
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 text-[11px] font-semibold bg-blue-50 text-blue-700 border border-blue-200 rounded-lg">
                                        <i class="fa fa-calendar-check-o text-[10px]"></i> <?= date('d M Y', strtotime($log['next_followup_date'])) ?>
                                    </span>
                                    <?php if(!empty($log['followup_template_name'])): ?>
                                        <span class="block text-[10px] text-indigo-600 font-semibold truncate max-w-[130px]" title="<?= esc_html($log['followup_template_name']) ?>">
                                            <i class="fa fa-file-text-o text-[9px]"></i> <?= esc_html($log['followup_template_name']) ?>
                                        </span>
                                    <?php elseif(!empty($log['campaign_id'])): ?>
                                        <button type="button" class="btn-quick-assign-tpl block text-[10px] text-amber-600 hover:text-amber-800 underline font-semibold mt-0.5" data-campaign-id="<?= (int)$log['campaign_id'] ?>" data-subject="<?= esc_html($log['campaign_subject'] ?? '') ?>">
                                            + Set Template
                                        </button>
                                    <?php endif; ?>
                                </div>
                                <?php else: ?>
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 text-[11px] font-semibold bg-amber-50 text-amber-700 border border-amber-200 rounded-lg">
                                    <i class="fa fa-bolt text-[10px]"></i> Instant
                                </span>
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
                        <th class="py-3 px-3">SENT &amp; FOLLOW-UP DATES</th>
                        <th class="py-3 px-3 text-center">QUEUE CONTROL</th>
                        <th class="py-3 px-3 text-right">ACTIONS</th>
                    </tr>
                </thead>
                <tbody id="history-table-body" class="divide-y divide-gray-100">
                    <?php if(!empty($recent_campaigns)): foreach($recent_campaigns as $camp): 
                        $badgeStatus = 'bg-emerald-50 text-emerald-700 border-emerald-200';
                        if ($camp['status'] === 'pending') $badgeStatus = 'bg-amber-50 text-amber-700 border-amber-200';
                        elseif ($camp['status'] === 'processing') $badgeStatus = 'bg-blue-50 text-blue-700 border-blue-200';
                        elseif ($camp['status'] === 'paused') $badgeStatus = 'bg-amber-100 text-amber-900 border-amber-300';
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
                            <?php if(!empty($camp['next_followup_days']) || !empty($camp['followup_template_id'])): ?>
                                <div class="mt-1 flex items-center gap-1.5 flex-wrap">
                                    <?php if(!empty($camp['followup_template_name'])): ?>
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-semibold bg-indigo-50 text-indigo-700 border border-indigo-200" title="Follow-up Email Template">
                                            <i class="fa fa-envelope-o text-[9px]"></i> Follow-Up: <strong><?= esc_html($camp['followup_template_name']) ?></strong>
                                        </span>
                                    <?php else: ?>
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-semibold bg-amber-50 text-amber-700 border border-amber-300">
                                            <i class="fa fa-clock-o text-[9px]"></i> Follow-Up: Not Assigned
                                        </span>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td class="py-3 px-3">
                            <span class="font-medium text-gray-800 block"><?= esc_html($camp['recipient_type']) ?></span>
                            <?php if(!empty($camp['product_name'])): ?>
                            <span class="text-[10px] font-bold text-purple-700 bg-purple-50 px-1.5 py-0.5 rounded border border-purple-200"><?= esc_html($camp['product_name']) ?></span>
                            <?php endif; ?>
                        </td>
                        <td class="py-3 px-3 text-center font-bold text-emerald-600 font-mono"><?= (int)$camp['count_sent'] ?></td>
                        <td class="py-3 px-3 text-center font-mono">
                            <span class="font-bold text-amber-600"><?= (int)$camp['count_queued'] ?></span>
                            <?php if(!empty($camp['count_paused'])): ?>
                                <span class="block text-[10px] text-amber-800 font-semibold bg-amber-100 px-1.5 py-0.5 rounded-full mt-0.5" title="Paused emails in queue"><?= (int)$camp['count_paused'] ?> paused</span>
                            <?php endif; ?>
                        </td>
                        <td class="py-3 px-3 text-center font-bold text-rose-600 font-mono"><?= (int)$camp['count_failed'] ?></td>
                        <td class="py-3 px-3">
                            <span class="px-2 py-0.5 text-[10px] font-bold rounded-full border <?= $badgeStatus ?> uppercase">
                                <?= esc_html($camp['status']) ?>
                            </span>
                        </td>
                        <td class="py-3 px-3 text-xs whitespace-nowrap">
                            <div class="space-y-1">
                                <div class="text-[11px] text-gray-700 font-medium flex items-center gap-1.5" title="Sent / Created Date">
                                    <i class="fa fa-paper-plane-o text-gray-400 text-[10px]"></i>
                                    <span><?= date('d M Y, h:i A', strtotime($camp['created_at'])) ?></span>
                                </div>
                                <?php 
                                    $fDate = !empty($camp['next_followup_date']) ? $camp['next_followup_date'] : null;
                                    if (!$fDate && !empty($camp['next_followup_days']) && !empty($camp['created_at'])) {
                                        $fDate = date('Y-m-d', strtotime("+{$camp['next_followup_days']} days", strtotime($camp['created_at'])));
                                    }
                                ?>
                                <?php if(!empty($fDate)): 
                                    $today = date('Y-m-d');
                                    $fDateFmt = date('d M Y', strtotime($fDate));
                                    $isToday = ($fDate === $today);
                                    $isOverdue = ($fDate < $today);
                                ?>
                                    <div class="flex items-center gap-1">
                                        <?php if($isToday): ?>
                                            <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-bold bg-amber-50 text-amber-800 border border-amber-300 animate-pulse" title="Follow-up due Today">
                                                <i class="fa fa-clock-o text-[9px]"></i> Next: Today (<?= $fDateFmt ?>)
                                            </span>
                                        <?php elseif($isOverdue): ?>
                                            <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-300" title="Follow-up date passed">
                                                <i class="fa fa-exclamation-circle text-[9px]"></i> Next: <?= $fDateFmt ?> (Past)
                                            </span>
                                        <?php else: ?>
                                            <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-semibold bg-blue-50 text-blue-700 border border-blue-200" title="Scheduled Follow-up Date">
                                                <i class="fa fa-calendar-check-o text-[9px]"></i> Next: <?= $fDateFmt ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                <?php else: ?>
                                    <span class="text-[10px] text-gray-400 block font-normal">— No Follow-Up</span>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td class="py-3 px-3 text-center whitespace-nowrap">
                            <?php 
                                $qC = (int)($camp['count_queued'] ?? 0);
                                $pC = (int)($camp['count_paused'] ?? 0);
                            ?>
                            <?php if($qC > 0 && $pC == 0): ?>
                                <button type="button" class="btn-open-camp-queue-modal px-2.5 py-1 text-xs font-semibold bg-amber-50 hover:bg-amber-100 text-amber-700 border border-amber-300 rounded-lg transition-colors cursor-pointer inline-flex items-center gap-1 shadow-2xs" 
                                    data-campaign-id="<?= (int)$camp['id'] ?>" 
                                    data-subject="<?= esc_html($camp['subject']) ?>" 
                                    data-queued="<?= $qC ?>" 
                                    data-paused="<?= $pC ?>" 
                                    data-action="pause" 
                                    title="Pause queue for Campaign #<?= $camp['id'] ?>">
                                    <i class="fa fa-pause text-[10px]"></i> <span>Pause (<?= $qC ?>)</span>
                                </button>
                            <?php elseif($pC > 0 && $qC == 0): ?>
                                <button type="button" class="btn-open-camp-queue-modal px-2.5 py-1 text-xs font-semibold bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-300 rounded-lg transition-colors cursor-pointer inline-flex items-center gap-1 shadow-2xs" 
                                    data-campaign-id="<?= (int)$camp['id'] ?>" 
                                    data-subject="<?= esc_html($camp['subject']) ?>" 
                                    data-queued="<?= $qC ?>" 
                                    data-paused="<?= $pC ?>" 
                                    data-action="resume" 
                                    title="Resume paused queue for Campaign #<?= $camp['id'] ?>">
                                    <i class="fa fa-play text-[10px]"></i> <span>Resume (<?= $pC ?>)</span>
                                </button>
                            <?php elseif($qC > 0 && $pC > 0): ?>
                                <button type="button" class="btn-open-camp-queue-modal px-2.5 py-1 text-xs font-semibold bg-indigo-50 hover:bg-indigo-100 text-indigo-700 border border-indigo-200 rounded-lg transition-colors cursor-pointer inline-flex items-center gap-1 shadow-2xs" 
                                    data-campaign-id="<?= (int)$camp['id'] ?>" 
                                    data-subject="<?= esc_html($camp['subject']) ?>" 
                                    data-queued="<?= $qC ?>" 
                                    data-paused="<?= $pC ?>" 
                                    data-action="pause" 
                                    title="Manage queue partition for Campaign #<?= $camp['id'] ?>">
                                    <i class="fa fa-sliders text-[10px]"></i> <span><?= $qC ?>Q / <?= $pC ?>P</span>
                                </button>
                            <?php else: ?>
                                <span class="text-gray-300 text-[11px] italic">— Completed</span>
                            <?php endif; ?>
                        </td>
                        <td class="py-3 px-3 text-right whitespace-nowrap">
                            <div class="inline-flex items-center gap-1.5 justify-end">
                                <button type="button" 
                                    class="btn-quick-assign-tpl px-2.5 py-1 text-xs font-semibold <?= !empty($camp['followup_template_name']) ? 'bg-indigo-50 hover:bg-indigo-100 text-indigo-700 border-indigo-200' : 'bg-amber-50 hover:bg-amber-100 text-amber-700 border-amber-300' ?> rounded-lg transition-colors border cursor-pointer inline-flex items-center gap-1 shadow-2xs" 
                                    data-campaign-id="<?= (int)$camp['id'] ?>" 
                                    data-subject="<?= esc_html($camp['subject']) ?>" 
                                    data-current-template="<?= (int)($camp['followup_template_id'] ?? 0) ?>" 
                                    title="<?= !empty($camp['followup_template_name']) ? 'Change Follow-Up Template: '.esc_html($camp['followup_template_name']) : 'Assign Follow-Up Template' ?>">
                                    <i class="fa fa-pencil text-[11px]"></i>
                                    <span><?= !empty($camp['followup_template_name']) ? 'Edit Template' : 'Set Template' ?></span>
                                </button>
                                <button type="button" class="btn-view-campaign-details px-2.5 py-1 text-xs font-semibold bg-gray-100 hover:bg-blue-50 text-gray-700 hover:text-blue-700 rounded-lg transition-colors border border-gray-200 cursor-pointer inline-flex items-center gap-1" data-id="<?= $camp['id'] ?>">
                                    <i class="fa fa-eye text-[11px]"></i> <span>Details</span>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; else: ?>
                    <tr>
                        <td colspan="11" class="py-8 text-center text-gray-400 text-xs">
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
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-7 gap-3 text-xs bg-gray-50 p-4 rounded-xl border border-gray-200">
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
                    <span class="text-gray-400 block text-[10px] font-bold uppercase">Follow-Up Template</span>
                    <span id="detail-followup-template" class="font-bold text-indigo-800 block truncate">-</span>
                    <button type="button" id="btn-detail-change-tpl" class="text-[10px] text-indigo-600 hover:text-indigo-800 font-bold underline cursor-pointer mt-0.5 inline-block">
                        <i class="fa fa-pencil"></i> Edit Template
                    </button>
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

<!-- ========================================================================= -->
<!-- MODAL: ASSIGN / CHANGE FOLLOW-UP TEMPLATE                                 -->
<!-- ========================================================================= -->
<div id="modal-assign-followup-template" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-xs p-4 overflow-y-auto">
    <div class="bg-white rounded-2xl shadow-2xl border border-gray-100 w-full max-w-lg overflow-hidden animate-in fade-in zoom-in-95 duration-200 my-8">
        <div class="px-5 py-4 bg-gradient-to-r from-indigo-600 to-blue-600 text-white flex items-center justify-between">
            <div class="flex items-center gap-2">
                <i class="fa fa-envelope-o text-lg"></i>
                <div>
                    <h3 class="text-sm font-bold">Assign Follow-Up Email Template</h3>
                    <p class="text-[11px] text-indigo-100" id="assign-modal-camp-label">Campaign #</p>
                </div>
            </div>
            <button type="button" class="btn-close-assign-modal text-white/80 hover:text-white text-lg cursor-pointer">
                <i class="fa fa-times"></i>
            </button>
        </div>

        <form id="form-assign-followup-template" class="p-5 space-y-4">
            <input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>" value="<?= $this->security->get_csrf_hash() ?>">
            <input type="hidden" name="campaign_id" id="assign-input-campaign-id" value="">

            <div class="p-3 bg-gray-50 border border-gray-200 rounded-xl text-xs space-y-1">
                <span class="text-gray-400 block text-[10px] font-bold uppercase">Campaign Subject:</span>
                <span id="assign-label-subject" class="font-bold text-gray-900 block truncate"></span>
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1.5 flex items-center justify-between">
                    <span>Select Follow-Up Email Template: <span class="text-rose-500">*</span></span>
                    <a href="<?= base_url('communications/mail_templates') ?>" target="_blank" class="text-indigo-600 hover:underline text-[11px] font-normal">Manage Templates</a>
                </label>
                <select name="followup_template_id" id="assign-select-template" class="w-full px-3 py-2.5 bg-white border border-indigo-200 rounded-xl text-xs font-medium text-gray-800 focus:ring-2 focus:ring-indigo-500 focus:outline-none" required>
                    <option value="">— Select Follow-Up Template —</option>
                    <?php if(!empty($templates)): foreach($templates as $tpl): ?>
                        <option value="<?= $tpl['id'] ?>" data-cat="<?= $tpl['category'] ?>">
                            [<?= strtoupper($tpl['category']) ?>] <?= esc_html($tpl['name']) ?> <?= !empty($tpl['product_name']) ? '('.esc_html($tpl['product_name']).')' : '' ?>
                        </option>
                    <?php endforeach; endif; ?>
                </select>
            </div>

            <!-- Preview Snippet -->
            <div id="assign-preview-snippet" class="hidden p-3 bg-indigo-50/50 border border-indigo-100 rounded-xl text-xs space-y-1">
                <span class="text-[10px] uppercase font-bold text-indigo-700 block">Template Subject:</span>
                <span id="assign-preview-subject" class="text-gray-900 font-semibold block truncate"></span>
                <div id="assign-preview-body" class="text-gray-600 text-[11px] line-clamp-3 mt-1 max-h-20 overflow-hidden"></div>
            </div>

            <!-- Queue Option for Background Queue -->
            <div class="p-3.5 bg-blue-50/80 border border-blue-200 rounded-xl space-y-2">
                <label class="flex items-start gap-2.5 cursor-pointer text-xs font-semibold text-blue-950">
                    <input type="checkbox" name="queue_followup_now" id="assign-checkbox-queue-now" value="1" checked class="mt-0.5 rounded text-indigo-600 focus:ring-indigo-500">
                    <div>
                        <span>Add follow-up emails to background queue now</span>
                        <p class="text-[11px] font-normal text-blue-700 mt-0.5 leading-snug">
                            Queues follow-up emails for delivered outreach recipients. The 1-minute paced anti-ban queue will smoothly send them in the background.
                        </p>
                    </div>
                </label>

                <!-- Follow-up Partition Control -->
                <div id="assign-partition-section" class="pt-3 border-t border-blue-200/80 space-y-2.5">
                    <div class="flex items-center justify-between">
                        <span class="font-bold text-blue-950 uppercase text-[10px] tracking-wider flex items-center gap-1.5">
                            <i class="fa fa-pie-chart text-indigo-600"></i> Follow-Up Batch Partition:
                        </span>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-white text-indigo-700 border border-indigo-200 shadow-2xs">
                            Eligible: <span id="assign-eligible-count">0</span>
                        </span>
                    </div>

                    <div class="flex items-center gap-4 flex-wrap text-xs">
                        <label class="inline-flex items-center gap-1.5 cursor-pointer font-medium text-slate-800">
                            <input type="radio" name="followup_partition_mode" value="all" checked class="text-indigo-600 focus:ring-indigo-500">
                            <span>Queue All Eligible (<span id="assign-partition-all-count">0</span>)</span>
                        </label>
                        <label class="inline-flex items-center gap-1.5 cursor-pointer font-medium text-slate-800">
                            <input type="radio" name="followup_partition_mode" value="custom" class="text-indigo-600 focus:ring-indigo-500">
                            <span>Custom Batch</span>
                        </label>
                    </div>

                    <div id="assign-custom-batch-input-wrap" class="hidden flex items-center gap-2 pt-1.5 border-t border-blue-100">
                        <span class="text-slate-600 text-[11px] font-medium">Send:</span>
                        <input type="number" name="followup_partition_limit" id="assign-followup-partition-limit" min="1" max="5000" placeholder="25" class="w-20 px-2 py-1 bg-white border border-indigo-300 rounded-lg text-xs font-bold text-slate-900 focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                        <span class="text-[11px] text-slate-500 font-normal">recipients now, rest will stay pending</span>
                    </div>

                    <div id="assign-partition-hint" class="text-[11px] text-indigo-800 font-medium italic pt-0.5"></div>
                </div>
            </div>

            <div class="flex items-center justify-between pt-3 border-t border-gray-100">
                <button type="button" class="btn-close-assign-modal px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl text-xs font-semibold cursor-pointer">
                    Cancel
                </button>
                <button type="submit" id="btn-submit-assign-template" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold transition flex items-center gap-1.5 cursor-pointer shadow-xs">
                    <i class="fa fa-check"></i> Save &amp; Link Template
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL: CAMPAIGN QUEUE PARTITION & CONTROL (PLAY/PAUSE)                    -->
<!-- ========================================================================= -->
<div id="modal-campaign-queue-control" class="hidden fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl space-y-4 border border-gray-100 animate-in fade-in zoom-in-95 duration-150">
        <div class="flex items-start justify-between border-b border-gray-100 pb-3">
            <div>
                <h3 class="text-sm font-bold text-gray-900 flex items-center gap-2">
                    <span class="w-6 h-6 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center text-xs">
                        <i class="fa fa-sliders"></i>
                    </span>
                    <span>Campaign Queue Partition &amp; Control</span>
                </h3>
                <p id="cq-modal-subtitle" class="text-xs text-gray-500 mt-0.5 font-medium truncate max-w-[320px]">Campaign #0</p>
            </div>
            <button type="button" class="btn-close-camp-queue-modal text-gray-400 hover:text-gray-600 text-lg cursor-pointer">
                <i class="fa fa-times"></i>
            </button>
        </div>

        <form id="form-camp-queue-control" class="space-y-4">
            <input type="hidden" name="campaign_id" id="cq-campaign-id">

            <!-- Live Status Breakdown Cards -->
            <div class="grid grid-cols-2 gap-2.5 p-3 rounded-xl bg-slate-50 border border-slate-100 text-center text-xs">
                <div class="p-2.5 bg-white rounded-xl border border-amber-200 shadow-2xs">
                    <span class="block text-lg font-black text-amber-600 font-mono" id="cq-count-queued">0</span>
                    <span class="text-[10px] uppercase font-bold text-slate-500 tracking-wider">Active Queue</span>
                </div>
                <div class="p-2.5 bg-white rounded-xl border border-amber-300 bg-amber-50/50 shadow-2xs">
                    <span class="block text-lg font-black text-amber-900 font-mono" id="cq-count-paused">0</span>
                    <span class="text-[10px] uppercase font-bold text-amber-800 tracking-wider">Paused in Queue</span>
                </div>
            </div>

            <!-- Action Select: Pause vs Resume -->
            <div>
                <label class="block text-xs font-bold text-gray-700 mb-1.5">Action to Perform</label>
                <div class="grid grid-cols-2 gap-2">
                    <label class="cq-action-tab flex items-center justify-center gap-2 p-2.5 rounded-xl border border-gray-200 cursor-pointer font-semibold text-xs transition" id="cq-action-pause-label">
                        <input type="radio" name="cq_action" value="pause" class="sr-only cq-action-radio">
                        <i class="fa fa-pause text-amber-600"></i>
                        <span>Pause Queue</span>
                    </label>
                    <label class="cq-action-tab flex items-center justify-center gap-2 p-2.5 rounded-xl border border-gray-200 cursor-pointer font-semibold text-xs transition" id="cq-action-resume-label">
                        <input type="radio" name="cq_action" value="resume" class="sr-only cq-action-radio">
                        <i class="fa fa-play text-emerald-600"></i>
                        <span>Resume Queue</span>
                    </label>
                </div>
            </div>

            <!-- Partition Mode Selection -->
            <div class="space-y-3 p-3.5 rounded-xl bg-indigo-50/60 border border-indigo-100 text-xs">
                <div class="flex items-center justify-between">
                    <span class="font-bold text-slate-800 flex items-center gap-1.5">
                        <i class="fa fa-pie-chart text-indigo-600"></i> Partition Size:
                    </span>
                    <span class="text-[10px] font-bold text-indigo-700 bg-white px-2 py-0.5 rounded-full border border-indigo-200 shadow-2xs" id="cq-max-eligible-label">Available: 0</span>
                </div>

                <div class="flex items-center gap-4 flex-wrap text-xs">
                    <label class="inline-flex items-center gap-1.5 cursor-pointer font-medium text-slate-800">
                        <input type="radio" name="cq_partition_mode" value="all" checked class="text-indigo-600 focus:ring-indigo-500 cq-part-mode">
                        <span id="cq-label-all-mode">All Emails (0)</span>
                    </label>
                    <label class="inline-flex items-center gap-1.5 cursor-pointer font-medium text-slate-800">
                        <input type="radio" name="cq_partition_mode" value="custom" class="text-indigo-600 focus:ring-indigo-500 cq-part-mode">
                        <span>Custom Batch</span>
                    </label>
                </div>

                <div id="cq-custom-batch-wrap" class="hidden flex items-center gap-2 pt-2 border-t border-indigo-100">
                    <span class="text-slate-600 text-[11px] font-medium" id="cq-custom-input-label">Quantity:</span>
                    <input type="number" name="cq_partition_limit" id="cq-partition-limit" min="1" placeholder="10" class="w-24 px-2.5 py-1 bg-white border border-indigo-300 rounded-lg text-xs font-bold text-slate-900 focus:ring-2 focus:ring-indigo-500 focus:outline-none font-mono">
                    <span class="text-[11px] text-slate-500" id="cq-custom-input-hint">emails</span>
                </div>

                <!-- Dynamic Realtime Hint -->
                <div id="cq-partition-hint" class="text-[11px] text-indigo-900 font-medium italic pt-1 border-t border-indigo-100/60 leading-relaxed">
                    Select an option above to preview queue partition changes.
                </div>
            </div>

            <div class="flex items-center justify-between pt-3 border-t border-gray-100">
                <button type="button" class="btn-close-camp-queue-modal px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl text-xs font-semibold cursor-pointer">
                    Cancel
                </button>
                <button type="submit" id="btn-submit-camp-queue-control" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold transition flex items-center gap-1.5 cursor-pointer shadow-xs">
                    <i class="fa fa-check"></i> <span id="cq-submit-text">Apply Changes</span>
                </button>
            </div>
        </form>
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

    // ---------------------------------------------------------
    // DYNAMIC STATS & CAMPAIGN BATCHES RENDERING & DATE FILTERING
    // ---------------------------------------------------------
    var currentStatsFilter = 'all';
    var statsFromDate = '';
    var statsToDate = '';

    function renderCampaignBatches(campaigns) {
        if (!campaigns || !campaigns.length) {
            $('#history-table-body').html('<tr><td colspan="11" class="py-8 text-center text-gray-400 text-xs"><i class="fa fa-paper-plane-o text-2xl block mb-2 opacity-50"></i>No campaign dispatches found for this date period.</td></tr>');
            return;
        }

        var today = new Date().toISOString().slice(0, 10);
        var html = '';
        campaigns.forEach(function(camp) {
            var badgeStatus = 'bg-emerald-50 text-emerald-700 border-emerald-200';
            if (camp.status === 'pending') badgeStatus = 'bg-amber-50 text-amber-700 border-amber-200';
            else if (camp.status === 'processing') badgeStatus = 'bg-blue-50 text-blue-700 border-blue-200';
            else if (camp.status === 'paused') badgeStatus = 'bg-amber-100 text-amber-900 border-amber-300';

            // Purpose
            var purposeHtml = renderTypeBadge(camp.campaign_type || 'outreach');
            if (camp.next_followup_days) {
                purposeHtml += '<span class="block text-[10px] text-gray-400 mt-0.5 font-sans">+' + parseInt(camp.next_followup_days) + 'd cadence</span>';
            }

            // Template subtitle
            var tplHtml = '';
            if (camp.next_followup_days || camp.followup_template_id) {
                if (camp.followup_template_name) {
                    tplHtml = '<div class="mt-1 flex items-center gap-1.5 flex-wrap"><span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-semibold bg-indigo-50 text-indigo-700 border border-indigo-200" title="Follow-up Email Template"><i class="fa fa-envelope-o text-[9px]"></i> Follow-Up: <strong>' + $('<div>').text(camp.followup_template_name).html() + '</strong></span></div>';
                } else {
                    tplHtml = '<div class="mt-1 flex items-center gap-1.5 flex-wrap"><span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-semibold bg-amber-50 text-amber-700 border border-amber-300"><i class="fa fa-clock-o text-[9px]"></i> Follow-Up: Not Assigned</span></div>';
                }
            }

            // Dates (Created Date + Follow-Up Date)
            var createdDateStr = camp.created_at ? new Date(camp.created_at.replace(/-/g, '/')).toLocaleString('en-US', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit', hour12: true }) : '-';
            
            var fDate = camp.next_followup_date || '';
            if (!fDate && camp.next_followup_days && camp.created_at) {
                var cDateObj = new Date(camp.created_at.replace(/-/g, '/'));
                cDateObj.setDate(cDateObj.getDate() + parseInt(camp.next_followup_days));
                fDate = cDateObj.toISOString().slice(0, 10);
            }

            var fBadgeHtml = '<span class="text-[10px] text-gray-400 block font-normal">— No Follow-Up</span>';
            if (fDate) {
                var fDateObj = new Date(fDate + 'T00:00:00');
                var fDateFmt = fDateObj.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
                if (fDate === today) {
                    fBadgeHtml = '<span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-bold bg-amber-50 text-amber-800 border border-amber-300 animate-pulse" title="Follow-up due Today"><i class="fa fa-clock-o text-[9px]"></i> Next: Today (' + fDateFmt + ')</span>';
                } else if (fDate < today) {
                    fBadgeHtml = '<span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-300" title="Follow-up date passed"><i class="fa fa-exclamation-circle text-[9px]"></i> Next: ' + fDateFmt + ' (Past)</span>';
                } else {
                    fBadgeHtml = '<span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-semibold bg-blue-50 text-blue-700 border border-blue-200"><i class="fa fa-calendar-check-o text-[9px]"></i> Next: ' + fDateFmt + '</span>';
                }
            }

            var editBtnClass = camp.followup_template_name ? 'bg-indigo-50 hover:bg-indigo-100 text-indigo-700 border-indigo-200' : 'bg-amber-50 hover:bg-amber-100 text-amber-700 border-amber-300';
            var editBtnText = camp.followup_template_name ? 'Edit Template' : 'Set Template';

            var qC = parseInt(camp.count_queued || 0);
            var pC = parseInt(camp.count_paused || 0);

            var pausedBadge = pC > 0 ? ('<span class="block text-[10px] text-amber-800 font-semibold bg-amber-100 px-1.5 py-0.5 rounded-full mt-0.5" title="Paused in queue">' + pC + ' paused</span>') : '';

            var qCtrlBtn = '<span class="text-gray-300 text-[11px] italic">— Completed</span>';
            if (qC > 0 && pC === 0) {
                qCtrlBtn = '<button type="button" class="btn-open-camp-queue-modal px-2.5 py-1 text-xs font-semibold bg-amber-50 hover:bg-amber-100 text-amber-700 border border-amber-300 rounded-lg transition-colors cursor-pointer inline-flex items-center gap-1 shadow-2xs" data-campaign-id="' + camp.id + '" data-subject="' + $('<div>').text(camp.subject).html() + '" data-queued="' + qC + '" data-paused="' + pC + '" data-action="pause" title="Pause queue for Campaign #' + camp.id + '"><i class="fa fa-pause text-[10px]"></i><span>Pause (' + qC + ')</span></button>';
            } else if (pC > 0 && qC === 0) {
                qCtrlBtn = '<button type="button" class="btn-open-camp-queue-modal px-2.5 py-1 text-xs font-semibold bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-300 rounded-lg transition-colors cursor-pointer inline-flex items-center gap-1 shadow-2xs" data-campaign-id="' + camp.id + '" data-subject="' + $('<div>').text(camp.subject).html() + '" data-queued="' + qC + '" data-paused="' + pC + '" data-action="resume" title="Resume queue for Campaign #' + camp.id + '"><i class="fa fa-play text-[10px]"></i><span>Resume (' + pC + ')</span></button>';
            } else if (qC > 0 && pC > 0) {
                qCtrlBtn = '<button type="button" class="btn-open-camp-queue-modal px-2.5 py-1 text-xs font-semibold bg-indigo-50 hover:bg-indigo-100 text-indigo-700 border border-indigo-200 rounded-lg transition-colors cursor-pointer inline-flex items-center gap-1 shadow-2xs" data-campaign-id="' + camp.id + '" data-subject="' + $('<div>').text(camp.subject).html() + '" data-queued="' + qC + '" data-paused="' + pC + '" data-action="pause" title="Manage partition"><i class="fa fa-sliders text-[10px]"></i><span>' + qC + 'Q / ' + pC + 'P</span></button>';
            }

            html += '<tr class="hover:bg-gray-50/60 transition-colors history-table-row">';
            html += '<td class="py-3 px-3 font-mono font-bold text-gray-800">#' + camp.id + '</td>';
            html += '<td class="py-3 px-3">' + purposeHtml + '</td>';
            html += '<td class="py-3 px-3"><span class="font-bold text-gray-900 block">' + $('<div>').text(camp.subject).html() + '</span><span class="text-[11px] text-gray-400">' + $('<div>').text(camp.template_name || 'Custom Compose').html() + '</span>' + tplHtml + '</td>';
            html += '<td class="py-3 px-3"><span class="font-medium text-gray-800 block">' + $('<div>').text(camp.recipient_type || '-').html() + '</span>' + (camp.product_name ? ('<span class="text-[10px] font-bold text-purple-700 bg-purple-50 px-1.5 py-0.5 rounded border border-purple-200">' + $('<div>').text(camp.product_name).html() + '</span>') : '') + '</td>';
            html += '<td class="py-3 px-3 text-center font-bold text-emerald-600 font-mono">' + (camp.count_sent || 0) + '</td>';
            html += '<td class="py-3 px-3 text-center font-mono"><span class="font-bold text-amber-600">' + qC + '</span>' + pausedBadge + '</td>';
            html += '<td class="py-3 px-3 text-center font-bold text-rose-600 font-mono">' + (camp.count_failed || 0) + '</td>';
            html += '<td class="py-3 px-3"><span class="px-2 py-0.5 text-[10px] font-bold rounded-full border ' + badgeStatus + ' uppercase">' + camp.status + '</span></td>';
            html += '<td class="py-3 px-3 text-xs whitespace-nowrap"><div class="space-y-1"><div class="text-[11px] text-gray-700 font-medium flex items-center gap-1.5"><i class="fa fa-paper-plane-o text-gray-400 text-[10px]"></i><span>' + createdDateStr + '</span></div><div class="flex items-center gap-1">' + fBadgeHtml + '</div></div></td>';
            html += '<td class="py-3 px-3 text-center whitespace-nowrap">' + qCtrlBtn + '</td>';
            html += '<td class="py-3 px-3 text-right whitespace-nowrap"><div class="inline-flex items-center gap-1.5 justify-end">';
            html += '<button type="button" class="btn-quick-assign-tpl px-2.5 py-1 text-xs font-semibold ' + editBtnClass + ' rounded-lg transition-colors border cursor-pointer inline-flex items-center gap-1 shadow-2xs" data-campaign-id="' + camp.id + '" data-subject="' + $('<div>').text(camp.subject).html() + '" data-current-template="' + (camp.followup_template_id || 0) + '"><i class="fa fa-pencil text-[11px]"></i><span>' + editBtnText + '</span></button>';
            html += '<button type="button" class="btn-view-campaign-details px-2.5 py-1 text-xs font-semibold bg-gray-100 hover:bg-blue-50 text-gray-700 hover:text-blue-700 rounded-lg transition-colors border border-gray-200 cursor-pointer inline-flex items-center gap-1" data-id="' + camp.id + '"><i class="fa fa-eye text-[11px]"></i><span>Details</span></button>';
            html += '</div></td>';
            html += '</tr>';
        });

        $('#history-table-body').html(html);
    }

    function loadStats(filter, fromDate, toDate) {
        currentStatsFilter = filter || 'all';
        statsFromDate = fromDate || '';
        statsToDate = toDate || '';

        var filterLabels = {
            'all': 'All Time',
            'today': 'Today',
            'yesterday': 'Yesterday',
            'this_week': 'This Week',
            'this_month': 'This Month',
            'custom': (statsFromDate || statsToDate) ? (statsFromDate + ' to ' + statsToDate) : 'Custom Range'
        };
        $('#label-active-stats-filter').text(filterLabels[currentStatsFilter] || 'Filtered');

        var url = BASE_URL + 'communications/history_ajax?date_filter=' + encodeURIComponent(currentStatsFilter);
        if (statsFromDate) url += '&from_date=' + encodeURIComponent(statsFromDate);
        if (statsToDate) url += '&to_date=' + encodeURIComponent(statsToDate);

        $.getJSON(url, function(resp) {
            if (resp.status === 'success' && resp.data) {
                if (resp.data.stats) {
                    var s = resp.data.stats;
                    $('#stat-total-sent').text(parseInt(s.sent || 0).toLocaleString());
                    $('#stat-total-queued').text(parseInt(s.queued || 0).toLocaleString());
                    if (parseInt(s.paused || 0) > 0) {
                        $('#stat-total-paused').text(parseInt(s.paused).toLocaleString());
                        $('#stat-total-paused-badge').removeClass('hidden');
                    } else {
                        $('#stat-total-paused-badge').addClass('hidden');
                    }
                    $('#stat-total-failed').text(parseInt(s.failed || 0).toLocaleString());
                    $('#stat-total-campaigns').text(parseInt(s.campaigns || 0).toLocaleString());
                    $('#queue-pending-count').text(s.queued || 0);
                }
                if (typeof resp.data.is_queue_paused !== 'undefined') {
                    setGlobalQueuePauseState(resp.data.is_queue_paused);
                }
                if (resp.data.campaigns) {
                    renderCampaignBatches(resp.data.campaigns);
                }
            }
        });
    }

    $(document).on('click', '.btn-stats-filter', function() {
        $('.btn-stats-filter').removeClass('active text-indigo-700 bg-indigo-50 border border-indigo-200').addClass('text-gray-600 hover:text-gray-900 hover:bg-gray-100');
        $(this).addClass('active text-indigo-700 bg-indigo-50 border border-indigo-200').removeClass('text-gray-600 hover:text-gray-900 hover:bg-gray-100');
        
        var filter = $(this).data('filter');
        $('#stats-custom-date-wrap').addClass('hidden');
        loadStats(filter);
    });

    $('#btn-toggle-stats-custom-date').on('click', function() {
        $('#stats-custom-date-wrap').toggleClass('hidden');
    });

    $('#btn-apply-stats-custom-date').on('click', function() {
        var from = $('#stats-from-date').val();
        var to = $('#stats-to-date').val();
        if (!from && !to) {
            alert('Please select at least one date.');
            return;
        }
        $('.btn-stats-filter').removeClass('active text-indigo-700 bg-indigo-50 border border-indigo-200').addClass('text-gray-600 hover:text-gray-900 hover:bg-gray-100');
        loadStats('custom', from, to);
    });

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
                    else if (log.status === 'paused') stBadge = 'bg-amber-100 text-amber-800 border-amber-300';
                    else if (log.status === 'failed') stBadge = 'bg-rose-50 text-rose-700 border-rose-200';

                    var pBadge = log.product_name ? '<span class="text-[10px] font-bold text-purple-700 bg-purple-50 px-1.5 py-0.5 rounded border border-purple-200 ml-1">' + $('<div>').text(log.product_name).html() + '</span>' : '';
                    var senderHtml = log.sender_email ? '<span class="inline-flex items-center gap-1.5 text-xs font-semibold text-gray-800"><i class="fa fa-envelope-o text-indigo-500"></i> ' + $('<div>').text(log.sender_email).html() + '</span>' : '<span class="text-gray-400 italic text-[11px]">Auto-assign on send</span>';
                    var hashHtml = log.anti_spam_hash ? '<span class="px-2 py-0.5 font-mono text-[10px] font-bold bg-slate-100 text-slate-800 border border-slate-300 rounded-md tracking-wider">#' + $('<div>').text(log.anti_spam_hash).html() + '</span>' : '<span class="text-gray-300 text-[10px]">-</span>';
                    var tplSubtitle = '';
                    if (log.followup_template_name) {
                        tplSubtitle = '<span class="block text-[10px] text-indigo-600 font-semibold truncate max-w-[130px]" title="' + $('<div>').text(log.followup_template_name).html() + '"><i class="fa fa-file-text-o text-[9px]"></i> ' + $('<div>').text(log.followup_template_name).html() + '</span>';
                    } else if (log.campaign_id && log.next_followup_date) {
                        tplSubtitle = '<button type="button" class="btn-quick-assign-tpl block text-[10px] text-amber-600 hover:text-amber-800 underline font-semibold mt-0.5" data-campaign-id="' + log.campaign_id + '" data-subject="' + $('<div>').text(log.campaign_subject || '').html() + '">+ Set Template</button>';
                    }
                    var followupHtml = log.next_followup_date ? ('<div><span class="inline-flex items-center gap-1 px-2 py-0.5 text-[11px] font-semibold bg-blue-50 text-blue-700 border border-blue-200 rounded-lg"><i class="fa fa-calendar-check-o text-[10px]"></i> ' + log.next_followup_date + '</span>' + tplSubtitle + '</div>') : '<span class="inline-flex items-center gap-1 px-2 py-0.5 text-[11px] font-semibold bg-amber-50 text-amber-700 border border-amber-200 rounded-lg"><i class="fa fa-bolt text-[10px]"></i> Instant</span>';
                    var sentAtHtml = log.sent_at ? log.sent_at : (log.status === 'paused' ? '<span class="text-amber-700 italic">Paused in Queue</span>' : '<span class="text-amber-500 italic">In Queue</span>');

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
        loadStats(currentStatsFilter, statsFromDate, statsToDate);
        setTimeout(function() {
            $btn.find('i').removeClass('fa-spin');
            CRM.toast('info', 'Dispatch history log refreshed.');
        }, 500);
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

                if (c.followup_template_name) {
                    $('#detail-followup-template').text(c.followup_template_name);
                } else if (c.next_followup_days) {
                    $('#detail-followup-template').text('None Assigned');
                } else {
                    $('#detail-followup-template').text('No Follow-Up');
                }
                $('#btn-detail-change-tpl').data('campaign-id', c.id).data('subject', c.subject).data('current-template', c.followup_template_id || 0);
                if (c.next_followup_days) {
                    $('#btn-detail-change-tpl').removeClass('hidden');
                } else {
                    $('#btn-detail-change-tpl').addClass('hidden');
                }

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
    // ASSIGN / CHANGE FOLLOW-UP TEMPLATE MODAL HANDLERS
    // ---------------------------------------------------------
    function updateFollowupPartitionHint(eligible) {
        if (typeof eligible === 'undefined') {
            eligible = parseInt($('#assign-eligible-count').text()) || 0;
        }
        var mode = $('input[name="followup_partition_mode"]:checked').val();
        if (mode === 'custom') {
            var limit = parseInt($('#assign-followup-partition-limit').val()) || 0;
            if (limit > eligible) {
                limit = eligible;
                $('#assign-followup-partition-limit').val(limit);
            }
            var held = Math.max(0, eligible - limit);
            $('#assign-partition-hint').removeClass('hidden').html('<i class="fa fa-info-circle"></i> <strong>' + limit + '</strong> follow-up emails will be queued now, <strong>' + held + '</strong> held pending for future batches.');
        } else {
            $('#assign-partition-hint').removeClass('hidden').html('<i class="fa fa-info-circle"></i> All <strong>' + eligible + '</strong> delivered recipients will be queued for follow-up.');
        }
    }

    $(document).on('click', '.btn-quick-assign-tpl, #btn-detail-change-tpl', function(e) {
        e.preventDefault();
        var campId = $(this).data('campaign-id');
        var subject = $(this).data('subject') || '';
        var curTpl = $(this).data('current-template') || '';

        $('#assign-input-campaign-id').val(campId);
        $('#assign-modal-camp-label').text('Campaign #' + campId);
        $('#assign-label-subject').text(subject || '(No Subject)');
        $('#assign-select-template').val(curTpl ? String(curTpl) : '').trigger('change');

        // Reset partition controls
        $('input[name="followup_partition_mode"][value="all"]').prop('checked', true);
        $('#assign-custom-batch-input-wrap').addClass('hidden');
        $('#assign-checkbox-queue-now').prop('checked', true);
        $('#assign-partition-section').removeClass('hidden');

        // Fetch eligible delivered count for this campaign
        $.getJSON(BASE_URL + 'communications/campaign_detail_ajax/' + campId, function(res) {
            if (res.status === 'success' && res.data) {
                var c = res.data;
                var qItems = c.items || [];
                var deliveredOutreach = 0;
                var alreadyFollowup = 0;
                qItems.forEach(function(item) {
                    if (item.campaign_type === 'outreach' && item.status === 'sent') {
                        deliveredOutreach++;
                    }
                    if (item.campaign_type && item.campaign_type.indexOf('followup') !== -1) {
                        alreadyFollowup++;
                    }
                });
                var eligible = Math.max(0, deliveredOutreach - alreadyFollowup);
                if (eligible === 0 && deliveredOutreach > 0 && alreadyFollowup === 0) {
                    eligible = deliveredOutreach;
                }
                if (eligible === 0 && parseInt(c.total_recipients || 0) > 0 && alreadyFollowup === 0) {
                    eligible = parseInt(c.total_recipients || 0);
                }

                $('#assign-eligible-count, #assign-partition-all-count').text(eligible);
                $('#assign-followup-partition-limit').attr('max', eligible);
                var defaultBatch = (eligible > 25) ? 25 : (eligible > 0 ? eligible : 25);
                $('#assign-followup-partition-limit').val(defaultBatch);
                updateFollowupPartitionHint(eligible);
            }
        });

        $('#modal-assign-followup-template').removeClass('hidden');
    });

    $(document).on('click', '.btn-close-assign-modal', function() {
        $('#modal-assign-followup-template').addClass('hidden');
    });

    $('input[name="followup_partition_mode"]').on('change', function() {
        if ($(this).val() === 'custom') {
            $('#assign-custom-batch-input-wrap').removeClass('hidden');
        } else {
            $('#assign-custom-batch-input-wrap').addClass('hidden');
        }
        updateFollowupPartitionHint();
    });

    $('#assign-followup-partition-limit').on('input keyup', function() {
        updateFollowupPartitionHint();
    });

    $('#assign-checkbox-queue-now').on('change', function() {
        if ($(this).is(':checked')) {
            $('#assign-partition-section').slideDown(150);
        } else {
            $('#assign-partition-section').slideUp(150);
        }
    });

    $('#assign-select-template').on('change', function() {
        var tid = $(this).val();
        if (!tid) {
            $('#assign-preview-snippet').addClass('hidden');
            return;
        }
        $.getJSON(BASE_URL + 'communications/get_template_ajax/' + tid, function(res) {
            if (res.status === 'success' && res.data) {
                $('#assign-preview-subject').text(res.data.subject || '(No Subject)');
                $('#assign-preview-body').html(res.data.body || '');
                $('#assign-preview-snippet').removeClass('hidden');
            }
        });
    });

    $('#form-assign-followup-template').on('submit', function(e) {
        e.preventDefault();
        var $btn = $('#btn-submit-assign-template');
        var origHtml = $btn.html();
        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Saving...');

        $.ajax({
            url: BASE_URL + 'communications/update_campaign_followup_template_ajax',
            method: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(resp) {
                $btn.prop('disabled', false).html(origHtml);
                if (resp.status === 'success') {
                    CRM.toast('success', resp.message || 'Follow-up template linked!');
                    $('#modal-assign-followup-template').addClass('hidden');
                    fetchDeliveryLogs();
                    loadStats(currentStatsFilter, statsFromDate, statsToDate);
                    setTimeout(function() { location.reload(); }, 900);
                } else {
                    alert(resp.message || 'Failed to assign template.');
                }
            },
            error: function() {
                $btn.prop('disabled', false).html(origHtml);
                alert('Network error while assigning template.');
            }
        });
    });


    // ---------------------------------------------------------
    // CAMPAIGN QUEUE PARTITION & CONTROL MODAL HANDLERS
    // ---------------------------------------------------------
    var activeCampQueue = {
        id: 0,
        subject: '',
        queued: 0,
        paused: 0,
        action: 'pause'
    };

    function updateCampQueueModalView() {
        $('#cq-campaign-id').val(activeCampQueue.id);
        $('#cq-modal-subtitle').text('Campaign #' + activeCampQueue.id + ' — ' + activeCampQueue.subject);
        $('#cq-count-queued').text(activeCampQueue.queued);
        $('#cq-count-paused').text(activeCampQueue.paused);

        $('.cq-action-tab').removeClass('bg-amber-50 border-amber-300 text-amber-900 ring-2 ring-amber-400 bg-emerald-50 border-emerald-300 text-emerald-900 ring-2 ring-emerald-400').addClass('bg-white border-gray-200 text-gray-700');

        if (activeCampQueue.action === 'pause') {
            $('#cq-action-pause-label').addClass('bg-amber-50 border-amber-300 text-amber-900 ring-2 ring-amber-400');
            $('input[name="cq_action"][value="pause"]').prop('checked', true);
            $('#cq-max-eligible-label').text('Available to Pause: ' + activeCampQueue.queued);
            $('#cq-label-all-mode').text('Pause All Active (' + activeCampQueue.queued + ')');
            $('#cq-custom-input-label').text('Pause count:');
            $('#cq-custom-input-hint').text('emails will be paused');
            $('#cq-submit-text').text('Pause ' + ($('input[name="cq_partition_mode"]:checked').val() === 'custom' ? 'Selected' : 'All') + ' Queue');
            $('#btn-submit-camp-queue-control').removeClass('bg-emerald-600 hover:bg-emerald-700').addClass('bg-amber-600 hover:bg-amber-700');
        } else {
            $('#cq-action-resume-label').addClass('bg-emerald-50 border-emerald-300 text-emerald-900 ring-2 ring-emerald-400');
            $('input[name="cq_action"][value="resume"]').prop('checked', true);
            $('#cq-max-eligible-label').text('Available to Resume: ' + activeCampQueue.paused);
            $('#cq-label-all-mode').text('Resume All Paused (' + activeCampQueue.paused + ')');
            $('#cq-custom-input-label').text('Resume count:');
            $('#cq-custom-input-hint').text('emails back to queue');
            $('#cq-submit-text').text('Resume ' + ($('input[name="cq_partition_mode"]:checked').val() === 'custom' ? 'Selected' : 'All') + ' Queue');
            $('#btn-submit-camp-queue-control').removeClass('bg-amber-600 hover:bg-amber-700').addClass('bg-emerald-600 hover:bg-emerald-700');
        }

        updateCampQueueHint();
    }

    function updateCampQueueHint() {
        var mode = $('input[name="cq_partition_mode"]:checked').val();
        var isPause = (activeCampQueue.action === 'pause');
        var maxCount = isPause ? activeCampQueue.queued : activeCampQueue.paused;

        if (mode === 'custom') {
            $('#cq-custom-batch-wrap').removeClass('hidden');
            var limit = parseInt($('#cq-partition-limit').val()) || 0;
            if (limit > maxCount) {
                limit = maxCount;
                $('#cq-partition-limit').val(limit);
            }
            if (isPause) {
                var bal = Math.max(0, activeCampQueue.queued - limit);
                $('#cq-partition-hint').html('<i class="fa fa-info-circle text-amber-600"></i> Will <strong>pause ' + limit + ' email(s)</strong>. Balance <strong>' + bal + ' email(s)</strong> will continue running in processing queue.');
            } else {
                var remPaused = Math.max(0, activeCampQueue.paused - limit);
                $('#cq-partition-hint').html('<i class="fa fa-info-circle text-emerald-600"></i> Will <strong>resume ' + limit + ' email(s)</strong> back into active queue. <strong>' + remPaused + ' email(s)</strong> remain paused.');
            }
        } else {
            $('#cq-custom-batch-wrap').addClass('hidden');
            if (isPause) {
                $('#cq-partition-hint').html('<i class="fa fa-info-circle text-amber-600"></i> Will pause <strong>all ' + activeCampQueue.queued + ' active emails</strong>. Campaign queue dispatching will be paused.');
            } else {
                $('#cq-partition-hint').html('<i class="fa fa-info-circle text-emerald-600"></i> Will resume <strong>all ' + activeCampQueue.paused + ' paused emails</strong> back into active queue.');
            }
        }
    }

    $(document).on('click', '.btn-open-camp-queue-modal', function(e) {
        e.preventDefault();
        var campId = parseInt($(this).data('campaign-id'));
        var subject = $(this).data('subject') || '';
        var queued = parseInt($(this).data('queued')) || 0;
        var paused = parseInt($(this).data('paused')) || 0;
        var preferredAction = $(this).data('action') || (queued > 0 ? 'pause' : 'resume');

        activeCampQueue = {
            id: campId,
            subject: subject,
            queued: queued,
            paused: paused,
            action: preferredAction
        };

        if (queued === 0 && paused > 0) activeCampQueue.action = 'resume';
        if (paused === 0 && queued > 0) activeCampQueue.action = 'pause';

        $('input[name="cq_partition_mode"][value="all"]').prop('checked', true);
        var defLimit = queued > 0 ? Math.min(10, queued) : Math.min(10, paused);
        $('#cq-partition-limit').val(defLimit > 0 ? defLimit : 1);

        updateCampQueueModalView();
        $('#modal-campaign-queue-control').removeClass('hidden');
    });

    $(document).on('change', 'input[name="cq_action"]', function() {
        activeCampQueue.action = $(this).val();
        updateCampQueueModalView();
    });

    $(document).on('change', 'input[name="cq_partition_mode"]', function() {
        updateCampQueueHint();
        var isCustom = $(this).val() === 'custom';
        $('#cq-submit-text').text((activeCampQueue.action === 'pause' ? 'Pause ' : 'Resume ') + (isCustom ? 'Selected' : 'All') + ' Queue');
    });

    $('#cq-partition-limit').on('input', function() {
        updateCampQueueHint();
    });

    $('.btn-close-camp-queue-modal').on('click', function() {
        $('#modal-campaign-queue-control').addClass('hidden');
    });

    $('#form-camp-queue-control').on('submit', function(e) {
        e.preventDefault();
        var $btn = $('#btn-submit-camp-queue-control');
        var origHtml = $btn.html();
        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Applying...');

        var postData = {
            campaign_id: activeCampQueue.id,
            action: activeCampQueue.action,
            partition_mode: $('input[name="cq_partition_mode"]:checked').val(),
            partition_limit: $('#cq-partition-limit').val()
        };

        $.post(BASE_URL + 'communications/toggle_campaign_queue_pause_ajax', postData, function(resp) {
            $btn.prop('disabled', false).html(origHtml);
            if (resp.status === 'success') {
                $('#modal-campaign-queue-control').addClass('hidden');
                CRM.toast('success', resp.message);
                loadStats(currentStatsFilter, statsFromDate, statsToDate);
                fetchDeliveryLogs();
            } else {
                CRM.toast('error', resp.message || 'Failed to update campaign queue.');
            }
        }, 'json').fail(function() {
            $btn.prop('disabled', false).html(origHtml);
            CRM.toast('error', 'Server error while applying queue partition.');
        });
    });

    // ---------------------------------------------------------
    // 1-MINUTE PACED BACKGROUND QUEUE TIMER & GLOBAL PAUSE LOGIC
    // ---------------------------------------------------------
    var TIMER_KEY      = 'crm_queue_timer_start_1m';
    var TIMER_DURATION = 60; // seconds
    var countdownSeconds;
    var isGlobalQueuePaused = <?= !empty($is_queue_paused) ? 'true' : 'false' ?>;

    function setGlobalQueuePauseState(paused) {
        isGlobalQueuePaused = !!paused;
        var $btn = $('#btn-toggle-global-queue');
        var $pill = $('#queue-status-pill');

        if (isGlobalQueuePaused) {
            $btn.removeClass('bg-amber-600 hover:bg-amber-500').addClass('bg-emerald-600 hover:bg-emerald-500');
            $btn.html('<i class="fa fa-play"></i> <span>Resume Queue</span>');
            $pill.removeClass('bg-emerald-500/20 text-emerald-300 border-emerald-500/30').addClass('bg-amber-500/20 text-amber-300 border-amber-500/30');
            $pill.html('<i class="fa fa-pause"></i> Queue Paused');
            $('#queue-countdown').text('Paused ⏸️');
        } else {
            $btn.removeClass('bg-emerald-600 hover:bg-emerald-500').addClass('bg-amber-600 hover:bg-amber-500');
            $btn.html('<i class="fa fa-pause"></i> <span>Pause Queue</span>');
            $pill.removeClass('bg-amber-500/20 text-amber-300 border-amber-500/30').addClass('bg-emerald-500/20 text-emerald-300 border-emerald-500/30');
            $pill.html('<span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-ping"></span> 1-Min Paced Interval');
            updateCountdownDisplay();
        }
    }

    $('#btn-toggle-global-queue').on('click', function() {
        var $btn = $(this);
        var targetAction = isGlobalQueuePaused ? 'resume' : 'pause';
        $btn.prop('disabled', true);

        $.post(BASE_URL + 'communications/toggle_global_queue_ajax', { action: targetAction }, function(resp) {
            $btn.prop('disabled', false);
            if (resp.status === 'success' && resp.data) {
                setGlobalQueuePauseState(resp.data.is_paused);
                CRM.toast('success', resp.message);
                loadStats(currentStatsFilter, statsFromDate, statsToDate);
            } else {
                CRM.toast('error', resp.message || 'Failed to update queue state.');
            }
        }, 'json').fail(function() {
            $btn.prop('disabled', false);
            CRM.toast('error', 'Server error toggling background queue.');
        });
    });

    function initTimer() {
        var stored = localStorage.getItem(TIMER_KEY);
        if (stored) {
            var elapsed = Math.floor((Date.now() - parseInt(stored)) / 1000);
            var remaining = TIMER_DURATION - elapsed;
            countdownSeconds = (remaining > 0 && remaining <= TIMER_DURATION) ? remaining : TIMER_DURATION;
        } else {
            countdownSeconds = TIMER_DURATION;
            localStorage.setItem(TIMER_KEY, Date.now());
        }
    }

    function resetTimer() {
        countdownSeconds = TIMER_DURATION;
        localStorage.setItem(TIMER_KEY, Date.now());
    }

    function updateCountdownDisplay() {
        if (isGlobalQueuePaused) {
            $('#queue-countdown').text('Paused ⏸️');
            return;
        }
        var mins = Math.floor(countdownSeconds / 60);
        var secs = countdownSeconds % 60;
        var display = (mins < 10 ? '0' : '') + mins + ':' + (secs < 10 ? '0' : '') + secs;
        $('#queue-countdown').text(display);
    }

    function triggerQueueBatch() {
        if (isGlobalQueuePaused) {
            CRM.toast('warning', 'Background queue is paused. Resume queue to dispatch emails.');
            return;
        }
        var pending = parseInt($('#queue-pending-count').text()) || 0;
        if (pending <= 0) {
            resetTimer();
            updateCountdownDisplay();
            return;
        }

        $('#queue-countdown').text('Sending...');
        $.getJSON(BASE_URL + 'communications/process_queue_batch_ajax', function(resp) {
            resetTimer();
            updateCountdownDisplay();
            if (resp.status === 'success' && resp.data) {
                if (resp.data.is_paused) {
                    setGlobalQueuePauseState(true);
                    CRM.toast('warning', resp.data.message);
                    return;
                }
                $('#queue-pending-count').text(resp.data.remaining);
                if (resp.data.processed > 0) {
                    var sentVal = parseInt($('#stat-total-sent').text().replace(/,/g, '')) || 0;
                    $('#stat-total-sent').text((sentVal + resp.data.processed).toLocaleString());
                    fetchDeliveryLogs();
                }
            }
        }).fail(function() {
            resetTimer();
            updateCountdownDisplay();
        });
    }

    // Initialize timer from localStorage on page load
    initTimer();
    updateCountdownDisplay();

    setInterval(function() {
        if (isGlobalQueuePaused) {
            $('#queue-countdown').text('Paused ⏸️');
            return;
        }
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

    // ── AUTO LOAD STATS + LOGS ON PAGE OPEN ─────────────────────────────────
    (function autoLoadOnOpen() {
        loadStats('all');
        fetchDeliveryLogs();
    })();
});
</script>
