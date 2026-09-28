<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
    <div class="flex items-center gap-3">
        <div class="w-10 h-10 bg-slate-100 rounded-xl flex items-center justify-center flex-shrink-0">
            <i class="fa fa-sliders text-slate-600 text-lg"></i>
        </div>
        <div>
            <h1 class="text-xl font-bold text-gray-800">Settings &amp; SMTP Mail Pool</h1>
            <nav class="text-xs text-gray-400 flex items-center gap-1 mt-0.5">
                <a href="<?= base_url('dashboard') ?>" class="hover:text-blue-600 transition-colors">Home</a>
                <i class="fa fa-angle-right text-[10px]"></i>
                <span class="text-gray-600">Settings</span>
            </nav>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="flex items-center bg-gray-100 p-1 rounded-xl">
        <button type="button" class="tab-btn px-4 py-2 text-xs font-bold rounded-lg transition-all text-blue-600 bg-white shadow-xs" data-target="#tab-general">
            <i class="fa fa-sliders mr-1.5"></i> General Settings
        </button>
        <button type="button" class="tab-btn px-4 py-2 text-xs font-bold rounded-lg transition-all text-gray-600 hover:text-gray-900" data-target="#tab-smtp">
            <i class="fa fa-envelope mr-1.5 text-blue-500"></i> Company Common SMTP Pool
            <span class="ml-1.5 px-2 py-0.5 text-[10px] rounded-full bg-blue-100 text-blue-700 font-mono" id="tab-smtp-badge"><?= count($smtp_pool['accounts'] ?? []) ?>/10</span>
        </button>
    </div>
</div>

<!-- ========================================================================= -->
<!-- TAB 1: GENERAL APP SETTINGS                                               -->
<!-- ========================================================================= -->
<div id="tab-general" class="settings-tab-pane">
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-2">
            <div class="w-8 h-8 bg-slate-100 rounded-lg flex items-center justify-center">
                <i class="fa fa-sliders text-slate-600 text-sm"></i>
            </div>
            <div>
                <h3 class="text-sm font-bold text-gray-800">Application Configuration</h3>
                <p class="text-xs text-gray-400 mt-0.5">General system preferences and tracking rules</p>
            </div>
        </div>
        <div class="p-6">
            <form id="settings-form">
                <input type="hidden" name="<?= $csrf_name ?>" value="<?= $csrf_hash ?>">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-10">
                    <div>
                        <h4 class="text-xs font-bold text-gray-500 uppercase tracking-wide mb-4 pb-2 border-b border-gray-100">General</h4>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">Company Name</label>
                                <input type="text" name="company_name" class="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" value="<?= esc_html($settings['company_name']??'') ?>">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">Timezone</label>
                                <input type="text" name="timezone" class="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" value="<?= esc_html($settings['timezone']??'Asia/Kolkata') ?>">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">Currency</label>
                                <input type="text" name="currency" class="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" value="<?= esc_html($settings['currency']??'INR') ?>">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">Order Prefix</label>
                                <input type="text" name="order_prefix" class="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" value="<?= esc_html($settings['order_prefix']??'ORD') ?>">
                            </div>
                        </div>
                    </div>
                    <div>
                        <h4 class="text-xs font-bold text-gray-500 uppercase tracking-wide mb-4 pb-2 border-b border-gray-100">Tracking &amp; Attendance</h4>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">GPS Ping Interval <span class="normal-case font-normal text-gray-400">(seconds)</span></label>
                                <input type="number" name="gps_ping_interval" class="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" value="<?= esc_html($settings['gps_ping_interval']??'30') ?>">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">Face Match Threshold <span class="normal-case font-normal text-gray-400">(0–1)</span></label>
                                <input type="number" step="0.01" name="face_match_threshold" class="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" value="<?= esc_html($settings['face_match_threshold']??'0.75') ?>">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">Attendance Window <span class="normal-case font-normal text-gray-400">(hours)</span></label>
                                <input type="number" name="attendance_window_hours" class="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" value="<?= esc_html($settings['attendance_window_hours']??'2') ?>">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="mt-8 pt-5 border-t border-gray-100">
                    <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 bg-blue-600 text-white text-sm font-semibold rounded-xl hover:bg-blue-700 transition-colors shadow-sm">
                        <i class="fa fa-save"></i> Save Settings
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- TAB 2: HOSTINGER MULTI-SMTP MAIL POOL (UP TO 10 ACCOUNTS)                 -->
<!-- ========================================================================= -->
<div id="tab-smtp" class="settings-tab-pane hidden space-y-6">

    <!-- Hero Pool Overview Metrics -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl border border-gray-100 p-4 shadow-xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Total Pool Capacity</span>
                <span class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center text-sm font-bold">
                    <i class="fa fa-server"></i>
                </span>
            </div>
            <div class="mt-2 flex items-baseline gap-2">
                <span class="text-2xl font-black text-gray-900 font-mono" id="pool-metric-capacity"><?= number_format($smtp_pool['total_capacity'] ?? 0) ?></span>
                <span class="text-xs font-semibold text-gray-500">emails / day</span>
            </div>
            <p class="text-[11px] text-gray-400 mt-1">Sum of all active Hostinger account limits</p>
        </div>

        <div class="bg-white rounded-2xl border border-gray-100 p-4 shadow-xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Sent Today</span>
                <span class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center text-sm font-bold">
                    <i class="fa fa-paper-plane"></i>
                </span>
            </div>
            <div class="mt-2 flex items-baseline gap-2">
                <span class="text-2xl font-black text-emerald-600 font-mono" id="pool-metric-sent"><?= number_format($smtp_pool['total_sent'] ?? 0) ?></span>
                <span class="text-xs font-semibold text-gray-500">dispatched</span>
            </div>
            <p class="text-[11px] text-gray-400 mt-1">Resets automatically every midnight (00:00)</p>
        </div>

        <div class="bg-white rounded-2xl border border-gray-100 p-4 shadow-xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Remaining Quota</span>
                <span class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center text-sm font-bold">
                    <i class="fa fa-battery-half"></i>
                </span>
            </div>
            <div class="mt-2 flex items-baseline gap-2">
                <span class="text-2xl font-black text-indigo-600 font-mono" id="pool-metric-remaining"><?= number_format($smtp_pool['total_remaining'] ?? 0) ?></span>
                <span class="text-xs font-semibold text-gray-500">available</span>
            </div>
            <div class="w-full bg-gray-100 rounded-full h-1.5 mt-2 overflow-hidden">
                <div id="pool-metric-progress" class="bg-indigo-600 h-1.5 rounded-full transition-all duration-500" style="width: <?= $smtp_pool['percent_remaining'] ?? 100 ?>%"></div>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-gray-100 p-4 shadow-xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Auto-Switch Engine</span>
                <span class="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center text-sm font-bold">
                    <i class="fa fa-bolt"></i>
                </span>
            </div>
            <div class="mt-2 flex items-baseline gap-2">
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    ACTIVE ROTATION
                </span>
            </div>
            <p class="text-[11px] text-gray-500 mt-1.5">Auto-switches to next SMTP when 100 limit is reached</p>
        </div>
    </div>

    <!-- Alert Notifications Banner (Quota / Limit Alerts) -->
    <div id="smtp-alerts-container" class="space-y-2 <?= empty($smtp_pool['alerts']) ? 'hidden' : '' ?>">
        <?php foreach (($smtp_pool['alerts'] ?? []) as $alert): ?>
            <div class="p-3.5 bg-amber-50 border border-amber-200 rounded-xl flex items-start gap-3 text-xs text-amber-900 shadow-xs">
                <i class="fa fa-exclamation-triangle text-amber-600 text-sm mt-0.5 flex-shrink-0"></i>
                <div class="flex-1 font-medium"><?= esc_html($alert) ?></div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Hostinger Guide & Configuration Info Card -->
    <div class="bg-gradient-to-r from-blue-900 via-indigo-900 to-slate-900 text-white rounded-2xl p-5 shadow-sm">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 mb-1.5">
                    <span class="px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider bg-blue-500/30 text-blue-200 rounded-md border border-blue-400/30">Hostinger SMTP Best Practices</span>
                    <span class="text-xs text-gray-300 font-semibold">100 Emails/Day Limit Solution</span>
                </div>
                <h4 class="text-sm font-bold text-white">Multi-Mailbox Pool with Automated Rotation</h4>
                <p class="text-xs text-gray-300 mt-1 max-w-2xl leading-relaxed">
                    Hostinger limits standard mailboxes to <strong>100 emails/day</strong>. By connecting up to 10 Hostinger SMTP mail accounts (e.g., <em>outreach@, sales@, info@, support@</em>), CRM-ZAZU creates a combined pool of <strong>1,000 emails/day</strong>. When an account completes 100 sends, the system automatically alerts you and switches to the next available account with zero campaign interruptions!
                </p>
            </div>
            <div class="bg-white/10 backdrop-blur-sm border border-white/10 rounded-xl p-3 text-xs space-y-1.5 min-w-[260px]">
                <div class="flex items-center justify-between text-gray-300">
                    <span>Hostinger Host:</span>
                    <strong class="text-white font-mono">smtp.hostinger.com</strong>
                </div>
                <div class="flex items-center justify-between text-gray-300">
                    <span>SSL Port / Crypto:</span>
                    <strong class="text-emerald-300 font-mono">465 (SSL)</strong>
                </div>
                <div class="flex items-center justify-between text-gray-300">
                    <span>TLS Port / Crypto:</span>
                    <strong class="text-blue-300 font-mono">587 (TLS)</strong>
                </div>
                <div class="flex items-center justify-between text-gray-300">
                    <span>Username:</span>
                    <strong class="text-white">Your full email</strong>
                </div>
            </div>
        </div>
    </div>

    <!-- Accounts Pool Management List -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h3 class="text-sm font-bold text-gray-900">Company Common Hostinger SMTP Accounts (<?= count($smtp_pool['accounts'] ?? []) ?> of 10 Slots)</h3>
                <p class="text-xs text-gray-400 mt-0.5">Shared company mailboxes used for all outbound emails with automated 100-limit rotation</p>
            </div>
            <div class="flex items-center gap-2">
                <button type="button" id="btn-refresh-pool" class="px-3 py-2 bg-gray-100 text-gray-700 hover:bg-gray-200 text-xs font-semibold rounded-xl transition-colors flex items-center gap-1.5" title="Refresh Live Quota Counts">
                    <i class="fa fa-refresh"></i> Refresh
                </button>
                <button type="button" id="btn-add-smtp" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl transition-all shadow-sm flex items-center gap-1.5 <?= count($smtp_pool['accounts'] ?? []) >= 10 ? 'opacity-50 cursor-not-allowed' : '' ?>" <?= count($smtp_pool['accounts'] ?? []) >= 10 ? 'disabled' : '' ?>>
                    <i class="fa fa-plus"></i> Add Company Common Mailbox
                </button>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-gray-600">
                <thead class="bg-gray-50/75 border-b border-gray-100 text-gray-500 uppercase text-[11px] font-bold tracking-wider">
                    <tr>
                        <th class="py-3.5 px-4 w-12 text-center">Slot</th>
                        <th class="py-3.5 px-4">Account &amp; Sender</th>
                        <th class="py-3.5 px-4">Server Host &amp; Port</th>
                        <th class="py-3.5 px-4 text-center">Daily Quota</th>
                        <th class="py-3.5 px-4">Usage Today</th>
                        <th class="py-3.5 px-4 text-center">Status</th>
                        <th class="py-3.5 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody id="smtp-accounts-tbody" class="divide-y divide-gray-100">
                    <?php if (empty($smtp_pool['accounts'])): ?>
                        <tr>
                            <td colspan="7" class="py-8 text-center text-gray-400">
                                <i class="fa fa-envelope-open-o text-3xl mb-2 text-gray-300 block"></i>
                                No SMTP accounts configured. Click "+ Add Hostinger SMTP Account" to set up your first mail sender.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($smtp_pool['accounts'] as $index => $acc): ?>
                            <?php 
                                $cap  = (int)$acc['daily_limit'];
                                $sent = (int)$acc['sent_today'];
                                $rem  = max(0, $cap - $sent);
                                $pct  = $cap > 0 ? round(($sent / $cap) * 100) : 100;

                                $badgeClass = 'bg-emerald-50 text-emerald-700 border-emerald-200';
                                $badgeText  = 'ACTIVE';
                                if ($acc['status'] === 'disabled') {
                                    $badgeClass = 'bg-gray-100 text-gray-600 border-gray-200';
                                    $badgeText  = 'DISABLED';
                                } elseif ($rem === 0 || $acc['status'] === 'limit_reached') {
                                    $badgeClass = 'bg-rose-50 text-rose-700 border-rose-200';
                                    $badgeText  = 'LIMIT REACHED (100)';
                                } elseif ($rem <= 15) {
                                    $badgeClass = 'bg-amber-50 text-amber-700 border-amber-200';
                                    $badgeText  = 'LOW QUOTA (' . $rem . ' left)';
                                }
                            ?>
                            <tr class="hover:bg-gray-50/60 transition-colors" data-id="<?= $acc['id'] ?>" data-json='<?= htmlspecialchars(json_encode($acc), ENT_QUOTES, 'UTF-8') ?>'>
                                <td class="py-3.5 px-4 text-center font-mono font-bold text-gray-400">
                                    #<?= $index + 1 ?>
                                </td>
                                <td class="py-3.5 px-4">
                                    <div class="font-bold text-gray-900"><?= esc_html($acc['name']) ?></div>
                                    <div class="text-[11px] text-gray-500 font-mono flex items-center gap-1 mt-0.5">
                                        <i class="fa fa-user-circle text-gray-400"></i> <?= esc_html($acc['sender_name'] ?: 'CRM Mailer') ?>
                                        <span class="text-gray-300">|</span>
                                        <i class="fa fa-envelope text-gray-400"></i> <?= esc_html($acc['sender_email']) ?>
                                    </div>
                                </td>
                                <td class="py-3.5 px-4 font-mono text-[11px]">
                                    <div class="text-gray-800 font-semibold"><?= esc_html($acc['smtp_host']) ?>:<?= esc_html($acc['smtp_port']) ?></div>
                                    <span class="px-1.5 py-0.5 rounded text-[10px] uppercase font-bold bg-blue-50 text-blue-700 border border-blue-100">
                                        <?= strtoupper($acc['smtp_crypto'] ?: 'SSL') ?>
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-center font-mono font-bold text-gray-800">
                                    <?= $cap ?> / day
                                </td>
                                <td class="py-3.5 px-4 min-w-[160px]">
                                    <div class="flex items-center justify-between text-[11px] font-semibold mb-1">
                                        <span class="font-mono text-gray-700"><?= $sent ?> sent</span>
                                        <span class="font-mono text-<?= $rem <= 15 ? 'rose-600 font-bold' : 'emerald-600' ?>"><?= $rem ?> left</span>
                                    </div>
                                    <div class="w-full bg-gray-100 rounded-full h-1.5 overflow-hidden">
                                        <div class="h-1.5 rounded-full <?= $pct >= 100 ? 'bg-rose-500' : ($pct >= 80 ? 'bg-amber-500' : 'bg-emerald-500') ?>" style="width: <?= min(100, $pct) ?>%"></div>
                                    </div>
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <span class="px-2 py-0.5 text-[10px] font-bold rounded-lg border <?= $badgeClass ?>">
                                        <?= $badgeText ?>
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <button type="button" class="btn-test-smtp px-2.5 py-1.5 bg-blue-50 hover:bg-blue-100 text-blue-700 font-semibold text-[11px] rounded-lg transition-colors flex items-center gap-1" data-id="<?= $acc['id'] ?>" title="Send test email template to your inbox">
                                            <i class="fa fa-paper-plane text-blue-600"></i> Send Test
                                        </button>
                                        <button type="button" class="btn-edit-smtp px-2.5 py-1.5 bg-blue-50 hover:bg-blue-100 text-blue-700 font-semibold text-[11px] rounded-lg transition-colors flex items-center gap-1" data-id="<?= $acc['id'] ?>" title="Edit credentials">
                                            <i class="fa fa-pencil"></i> Edit
                                        </button>
                                        <button type="button" class="btn-reset-smtp px-2 py-1.5 bg-amber-50 hover:bg-amber-100 text-amber-700 font-semibold text-[11px] rounded-lg transition-colors" data-id="<?= $acc['id'] ?>" title="Reset sent today to 0">
                                            <i class="fa fa-undo"></i>
                                        </button>
                                        <button type="button" class="btn-delete-smtp px-2 py-1.5 bg-rose-50 hover:bg-rose-100 text-rose-700 font-semibold text-[11px] rounded-lg transition-colors" data-id="<?= $acc['id'] ?>" title="Delete account">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL: ADD / EDIT HOSTINGER SMTP ACCOUNT                                  -->
<!-- ========================================================================= -->
<div id="modal-smtp-account" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs hidden">
    <div class="bg-white rounded-2xl shadow-2xl border border-gray-100 max-w-lg w-full overflow-hidden animate-scale-up">
        <div class="px-6 py-4 bg-gray-50 border-b border-gray-100 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span class="w-8 h-8 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center font-bold text-xs">
                    <i class="fa fa-envelope"></i>
                </span>
                <div>
                    <h3 class="text-sm font-bold text-gray-900" id="modal-smtp-title">Add Company Common SMTP Mailbox</h3>
                    <p class="text-[11px] text-gray-500">Shared company Hostinger email account for all CRM mailings</p>
                </div>
            </div>
            <button type="button" class="btn-close-smtp-modal w-8 h-8 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 flex items-center justify-center transition-colors">
                <i class="fa fa-times text-sm"></i>
            </button>
        </div>

        <form id="form-smtp-account" class="p-6 space-y-4">
            <input type="hidden" name="<?= $csrf_name ?>" value="<?= $csrf_hash ?>">
            <input type="hidden" name="id" id="smtp-id" value="">

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1.5">Account Label / Slot Name <span class="text-rose-500">*</span></label>
                    <input type="text" name="name" id="smtp-name" required placeholder="e.g. Company Mailbox 1 (Primary)" class="w-full px-3 py-2 border border-gray-200 rounded-xl text-xs focus:ring-2 focus:ring-blue-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1.5">Daily Quota Limit <span class="text-rose-500">*</span></label>
                    <input type="number" name="daily_limit" id="smtp-daily-limit" required value="100" min="1" max="1000" class="w-full px-3 py-2 border border-gray-200 rounded-xl text-xs font-mono focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    <span class="text-[10px] text-gray-400 mt-0.5 block">Hostinger standard: 100 emails/day</span>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1.5">Company Sender Display Name <span class="text-rose-500">*</span></label>
                    <input type="text" name="sender_name" id="smtp-sender-name" required value="<?= esc_html($settings['company_name'] ?? 'Company Team') ?>" placeholder="e.g. <?= esc_html($settings['company_name'] ?? 'Company Team') ?>" class="w-full px-3 py-2 border border-gray-200 rounded-xl text-xs focus:ring-2 focus:ring-blue-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1.5">Company Sender Email Address <span class="text-rose-500">*</span></label>
                    <input type="email" name="sender_email" id="smtp-sender-email" required placeholder="e.g. contact@yourcompany.com" class="w-full px-3 py-2 border border-gray-200 rounded-xl text-xs focus:ring-2 focus:ring-blue-500 focus:outline-none">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div class="sm:col-span-2">
                    <label class="block text-xs font-semibold text-gray-700 mb-1.5">SMTP Host <span class="text-rose-500">*</span></label>
                    <input type="text" name="smtp_host" id="smtp-host" required value="smtp.hostinger.com" class="w-full px-3 py-2 border border-gray-200 rounded-xl text-xs font-mono focus:ring-2 focus:ring-blue-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1.5">Port &amp; Crypto</label>
                    <select name="smtp_port_crypto" id="smtp-port-crypto" class="w-full px-3 py-2 border border-gray-200 rounded-xl text-xs font-mono focus:ring-2 focus:ring-blue-500 focus:outline-none">
                        <option value="465_ssl" selected>465 (SSL)</option>
                        <option value="587_tls">587 (TLS)</option>
                    </select>
                    <input type="hidden" name="smtp_port" id="smtp-port" value="465">
                    <input type="hidden" name="smtp_crypto" id="smtp-crypto" value="ssl">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1.5">SMTP Username <span class="text-rose-500">*</span></label>
                    <input type="text" name="smtp_user" id="smtp-user" required placeholder="full email address" class="w-full px-3 py-2 border border-gray-200 rounded-xl text-xs font-mono focus:ring-2 focus:ring-blue-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1.5">SMTP Password <span id="pwd-required" class="text-rose-500">*</span></label>
                    <input type="password" name="smtp_pass" id="smtp-pass" placeholder="Hostinger email password" class="w-full px-3 py-2 border border-gray-200 rounded-xl text-xs font-mono focus:ring-2 focus:ring-blue-500 focus:outline-none">
                </div>
            </div>

            <div class="flex items-center justify-between pt-1">
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Status</label>
                    <select name="status" id="smtp-status" class="px-3 py-1.5 border border-gray-200 rounded-lg text-xs font-semibold focus:ring-2 focus:ring-blue-500 focus:outline-none">
                        <option value="active">Active (In Pool)</option>
                        <option value="disabled">Disabled</option>
                    </select>
                </div>
                <button type="button" id="btn-modal-test-conn" class="px-3 py-2 bg-amber-50 hover:bg-amber-100 text-amber-800 text-xs font-bold rounded-xl border border-amber-200 transition-colors flex items-center gap-1.5 mt-4">
                    <i class="fa fa-bolt"></i> Test Connection Live
                </button>
            </div>

            <div id="modal-test-result" class="hidden p-3 rounded-xl text-xs font-mono"></div>

            <div class="pt-4 border-t border-gray-100 flex items-center justify-end gap-2">
                <button type="button" class="btn-close-smtp-modal px-4 py-2 bg-gray-100 text-gray-600 hover:bg-gray-200 text-xs font-semibold rounded-xl transition-colors">
                    Cancel
                </button>
                <button type="submit" id="btn-save-smtp-submit" class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl transition-colors shadow-sm flex items-center gap-1.5">
                    <i class="fa fa-check"></i> Save Account to Pool
                </button>
            </div>
        </form>
    </div>
</div>

<script>
$(function(){
    'use strict';

    // ── Tab Navigation ────────────────────────────────────────────────────────
    $('.tab-btn').on('click', function(){
        var target = $(this).data('target');
        $('.tab-btn').removeClass('text-blue-600 bg-white shadow-xs').addClass('text-gray-600 hover:text-gray-900');
        $(this).addClass('text-blue-600 bg-white shadow-xs').removeClass('text-gray-600 hover:text-gray-900');

        $('.settings-tab-pane').addClass('hidden');
        $(target).removeClass('hidden');

        if(location.hash !== target) {
            history.replaceState(null, null, target);
        }
    });

    // Check hash on load (e.g. #tab-smtp)
    if (location.hash) {
        var $hashTab = $('[data-target="' + location.hash + '"]');
        if ($hashTab.length) {
            $hashTab.trigger('click');
        }
    }

    // ── General Settings Form Submit ──────────────────────────────────────────
    $('#settings-form').on('submit', function(e){
        e.preventDefault();
        var $btn = $(this).find('[type=submit]'); 
        CRM.btn_loading($btn);
        $.ajax({
            url: BASE_URL + 'admin/save_settings', 
            method: 'POST',
            data: new FormData(this), 
            processData: false, 
            contentType: false,
            success: function(res){ 
                CRM.toast(res.status === 'success' ? 'success' : 'error', res.message); 
            },
            error: function(){
                CRM.toast('error', 'Failed to save general settings.');
            },
            complete: function(){ 
                CRM.btn_reset($btn); 
            }
        });
    });

    // ── Port / Crypto Selector Sync ───────────────────────────────────────────
    $('#smtp-port-crypto').on('change', function(){
        var val = $(this).val();
        if(val === '465_ssl') {
            $('#smtp-port').val('465');
            $('#smtp-crypto').val('ssl');
        } else {
            $('#smtp-port').val('587');
            $('#smtp-crypto').val('tls');
        }
    });

    // ── Open Add Account Modal ────────────────────────────────────────────────
    $('#btn-add-smtp').on('click', function(){
        $('#modal-smtp-title').text('Add Hostinger SMTP Account');
        $('#form-smtp-account')[0].reset();
        $('#smtp-id').val('');
        $('#smtp-host').val('smtp.hostinger.com');
        $('#smtp-port-crypto').val('465_ssl').trigger('change');
        $('#smtp-daily-limit').val('100');
        $('#smtp-pass').prop('required', true);
        $('#pwd-required').removeClass('hidden');
        $('#modal-test-result').addClass('hidden').html('');
        $('#modal-smtp-account').removeClass('hidden');
    });

    $('.btn-close-smtp-modal').on('click', function(){
        $('#modal-smtp-account').addClass('hidden');
    });

    // ── Open Edit Account Modal ───────────────────────────────────────────────
    $(document).on('click', '.btn-edit-smtp', function(){
        var $tr = $(this).closest('tr');
        var data = $tr.data('json');
        if(!data) return;

        $('#modal-smtp-title').text('Edit SMTP Account: ' + data.name);
        $('#smtp-id').val(data.id);
        $('#smtp-name').val(data.name);
        $('#smtp-sender-name').val(data.sender_name);
        $('#smtp-sender-email').val(data.sender_email);
        $('#smtp-host').val(data.smtp_host || 'smtp.hostinger.com');
        $('#smtp-user').val(data.smtp_user);
        $('#smtp-pass').val('').prop('required', false);
        $('#pwd-required').addClass('hidden');
        $('#smtp-daily-limit').val(data.daily_limit || 100);
        $('#smtp-status').val(data.status || 'active');

        var combo = (data.smtp_port == '587' || data.smtp_crypto == 'tls') ? '587_tls' : '465_ssl';
        $('#smtp-port-crypto').val(combo).trigger('change');

        $('#modal-test-result').addClass('hidden').html('');
        $('#modal-smtp-account').removeClass('hidden');
    });

    // ── Test Connection Live inside Modal ─────────────────────────────────────
    $('#btn-modal-test-conn').on('click', function(){
        var host = $('#smtp-host').val().trim();
        var port = $('#smtp-port').val().trim();
        var crypto = $('#smtp-crypto').val().trim();

        var $btn = $(this);
        var origHtml = $btn.html();
        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Testing Socket...');
        $('#modal-test-result').removeClass('hidden').attr('class', 'p-3 rounded-xl text-xs font-mono bg-blue-50 text-blue-800 border border-blue-200').html('Connecting to ' + host + ':' + port + ' (' + crypto.toUpperCase() + ')...');

        $.ajax({
            url: BASE_URL + 'admin/test_smtp_connection',
            method: 'POST',
            data: {
                smtp_host: host,
                smtp_port: port,
                smtp_crypto: crypto,
                [CI3_CSRF_NAME]: CI3_CSRF_HASH
            },
            success: function(res){
                if(res.status === 'success') {
                    $('#modal-test-result').attr('class', 'p-3 rounded-xl text-xs font-mono bg-emerald-50 text-emerald-800 border border-emerald-200').html('<i class="fa fa-check-circle mr-1"></i> ' + res.message);
                } else {
                    $('#modal-test-result').attr('class', 'p-3 rounded-xl text-xs font-mono bg-rose-50 text-rose-800 border border-rose-200').html('<i class="fa fa-times-circle mr-1"></i> ' + res.message);
                }
            },
            error: function(){
                $('#modal-test-result').attr('class', 'p-3 rounded-xl text-xs font-mono bg-rose-50 text-rose-800 border border-rose-200').html('<i class="fa fa-times-circle mr-1"></i> Socket test failed. Hostinger server or port unreachable.');
            },
            complete: function(){
                $btn.prop('disabled', false).html(origHtml);
            }
        });
    });

    // ── Test Connection / Send Test Email for existing account row ───────────
    $(document).on('click', '.btn-test-smtp', function(){
        var id = $(this).data('id');
        var defaultEmail = '<?= esc_html($current_user['email'] ?? '') ?>';
        var toEmail = prompt('Enter recipient email address to send a verification test template:', defaultEmail);
        if(!toEmail || !toEmail.trim()) return;

        var $btn = $(this);
        var origHtml = $btn.html();
        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin text-blue-500"></i> Sending...');

        $.ajax({
            url: BASE_URL + 'admin/send_test_email',
            method: 'POST',
            data: { 
                id: id, 
                to_email: toEmail.trim(), 
                [CI3_CSRF_NAME]: CI3_CSRF_HASH 
            },
            success: function(res){
                if(res.status === 'success') {
                    CRM.toast('success', res.message);
                } else {
                    CRM.toast('error', res.message);
                }
            },
            error: function(xhr){
                var json = xhr.responseJSON;
                CRM.toast('error', (json && json.message) || 'Failed to dispatch test email. Check SMTP credentials.');
            },
            complete: function(){
                $btn.prop('disabled', false).html(origHtml);
            }
        });
    });

    // ── Reset Today's Count ───────────────────────────────────────────────────
    $(document).on('click', '.btn-reset-smtp', function(){
        var id = $(this).data('id');
        if(!confirm('Reset today\'s sent counter to 0 for this account? This will reactivate the account for further sends today.')) return;

        $.ajax({
            url: BASE_URL + 'admin/reset_smtp_counter',
            method: 'POST',
            data: { id: id, [CI3_CSRF_NAME]: CI3_CSRF_HASH },
            success: function(res){
                CRM.toast('success', res.message);
                refreshPoolData();
            }
        });
    });

    // ── Delete Account ────────────────────────────────────────────────────────
    $(document).on('click', '.btn-delete-smtp', function(){
        var id = $(this).data('id');
        if(!confirm('Are you sure you want to remove this SMTP account from your pool?')) return;

        $.ajax({
            url: BASE_URL + 'admin/delete_smtp_account',
            method: 'POST',
            data: { id: id, [CI3_CSRF_NAME]: CI3_CSRF_HASH },
            success: function(res){
                CRM.toast('success', res.message);
                refreshPoolData();
            }
        });
    });

    // ── Save SMTP Form (Submit) ───────────────────────────────────────────────
    $('#form-smtp-account').on('submit', function(e){
        e.preventDefault();
        var $btn = $('#btn-save-smtp-submit');
        var origHtml = $btn.html();
        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Saving...');

        $.ajax({
            url: BASE_URL + 'admin/save_smtp_account',
            method: 'POST',
            data: $(this).serialize(),
            success: function(res){
                if(res.status === 'success') {
                    CRM.toast('success', res.message);
                    $('#modal-smtp-account').addClass('hidden');
                    refreshPoolData();
                } else {
                    CRM.toast('error', res.message);
                }
            },
            error: function(xhr){
                var json = xhr.responseJSON;
                CRM.toast('error', (json && json.message) || 'Error saving SMTP account.');
            },
            complete: function(){
                $btn.prop('disabled', false).html(origHtml);
            }
        });
    });

    // ── Refresh Pool Data AJAX ────────────────────────────────────────────────
    $('#btn-refresh-pool').on('click', function(){
        refreshPoolData();
    });

    function refreshPoolData() {
        $.getJSON(BASE_URL + 'admin/smtp_pool_status', function(res){
            if(res.status === 'success' && res.data) {
                var pool = res.data;
                $('#pool-metric-capacity').text(Number(pool.total_capacity).toLocaleString());
                $('#pool-metric-sent').text(Number(pool.total_sent).toLocaleString());
                $('#pool-metric-remaining').text(Number(pool.total_remaining).toLocaleString());
                $('#pool-metric-progress').css('width', (pool.percent_remaining || 0) + '%');
                $('#tab-smtp-badge').text(pool.total_accounts + '/10');

                if(pool.total_accounts >= 10) {
                    $('#btn-add-smtp').prop('disabled', true).addClass('opacity-50 cursor-not-allowed');
                } else {
                    $('#btn-add-smtp').prop('disabled', false).removeClass('opacity-50 cursor-not-allowed');
                }

                // Render alerts
                var alertsHtml = '';
                if(pool.alerts && pool.alerts.length > 0) {
                    $.each(pool.alerts, function(i, a){
                        alertsHtml += '<div class="p-3.5 bg-amber-50 border border-amber-200 rounded-xl flex items-start gap-3 text-xs text-amber-900 shadow-xs">';
                        alertsHtml += '<i class="fa fa-exclamation-triangle text-amber-600 text-sm mt-0.5 flex-shrink-0"></i>';
                        alertsHtml += '<div class="flex-1 font-medium">' + CRM.esc(a) + '</div></div>';
                    });
                    $('#smtp-alerts-container').html(alertsHtml).removeClass('hidden');
                } else {
                    $('#smtp-alerts-container').html('').addClass('hidden');
                }

                // Re-render table rows
                var rowsHtml = '';
                if(pool.accounts && pool.accounts.length > 0) {
                    $.each(pool.accounts, function(i, acc){
                        var cap = parseInt(acc.daily_limit, 10) || 100;
                        var sent = parseInt(acc.sent_today, 10) || 0;
                        var rem = Math.max(0, cap - sent);
                        var pct = cap > 0 ? Math.round((sent / cap) * 100) : 100;

                        var badgeClass = 'bg-emerald-50 text-emerald-700 border-emerald-200';
                        var badgeText = 'ACTIVE';
                        if(acc.status === 'disabled') {
                            badgeClass = 'bg-gray-100 text-gray-600 border-gray-200';
                            badgeText = 'DISABLED';
                        } else if(rem === 0 || acc.status === 'limit_reached') {
                            badgeClass = 'bg-rose-50 text-rose-700 border-rose-200';
                            badgeText = 'LIMIT REACHED (100)';
                        } else if(rem <= 15) {
                            badgeClass = 'bg-amber-50 text-amber-700 border-amber-200';
                            badgeText = 'LOW QUOTA (' + rem + ' left)';
                        }

                        var barColor = pct >= 100 ? 'bg-rose-500' : (pct >= 80 ? 'bg-amber-500' : 'bg-emerald-500');

                        rowsHtml += '<tr class="hover:bg-gray-50/60 transition-colors" data-id="' + acc.id + '" data-json=\'' + JSON.stringify(acc) + '\'>';
                        rowsHtml += '<td class="py-3.5 px-4 text-center font-mono font-bold text-gray-400">#' + (i+1) + '</td>';
                        rowsHtml += '<td class="py-3.5 px-4"><div class="font-bold text-gray-900">' + CRM.esc(acc.name) + '</div>';
                        rowsHtml += '<div class="text-[11px] text-gray-500 font-mono flex items-center gap-1 mt-0.5"><i class="fa fa-user-circle text-gray-400"></i> ' + CRM.esc(acc.sender_name || 'CRM Mailer') + ' <span class="text-gray-300">|</span> <i class="fa fa-envelope text-gray-400"></i> ' + CRM.esc(acc.sender_email) + '</div></td>';
                        rowsHtml += '<td class="py-3.5 px-4 font-mono text-[11px]"><div class="text-gray-800 font-semibold">' + CRM.esc(acc.smtp_host) + ':' + CRM.esc(acc.smtp_port) + '</div><span class="px-1.5 py-0.5 rounded text-[10px] uppercase font-bold bg-blue-50 text-blue-700 border border-blue-100">' + CRM.esc((acc.smtp_crypto || 'ssl').toUpperCase()) + '</span></td>';
                        rowsHtml += '<td class="py-3.5 px-4 text-center font-mono font-bold text-gray-800">' + cap + ' / day</td>';
                        rowsHtml += '<td class="py-3.5 px-4 min-w-[160px]"><div class="flex items-center justify-between text-[11px] font-semibold mb-1"><span class="font-mono text-gray-700">' + sent + ' sent</span><span class="font-mono ' + (rem <= 15 ? 'text-rose-600 font-bold' : 'text-emerald-600') + '">' + rem + ' left</span></div><div class="w-full bg-gray-100 rounded-full h-1.5 overflow-hidden"><div class="h-1.5 rounded-full ' + barColor + '" style="width:' + Math.min(100, pct) + '%"></div></div></td>';
                        rowsHtml += '<td class="py-3.5 px-4 text-center"><span class="px-2 py-0.5 text-[10px] font-bold rounded-lg border ' + badgeClass + '">' + badgeText + '</span></td>';
                        rowsHtml += '<td class="py-3.5 px-4 text-right"><div class="flex items-center justify-end gap-1.5">';
                        rowsHtml += '<button type="button" class="btn-test-smtp px-2.5 py-1.5 bg-blue-50 hover:bg-blue-100 text-blue-700 font-semibold text-[11px] rounded-lg transition-colors flex items-center gap-1" data-id="' + acc.id + '" title="Send test email template to your inbox"><i class="fa fa-paper-plane text-blue-600"></i> Send Test</button>';
                        rowsHtml += '<button type="button" class="btn-edit-smtp px-2.5 py-1.5 bg-blue-50 hover:bg-blue-100 text-blue-700 font-semibold text-[11px] rounded-lg transition-colors flex items-center gap-1" data-id="' + acc.id + '"><i class="fa fa-pencil"></i> Edit</button>';
                        rowsHtml += '<button type="button" class="btn-reset-smtp px-2 py-1.5 bg-amber-50 hover:bg-amber-100 text-amber-700 font-semibold text-[11px] rounded-lg transition-colors" data-id="' + acc.id + '"><i class="fa fa-undo"></i></button>';
                        rowsHtml += '<button type="button" class="btn-delete-smtp px-2 py-1.5 bg-rose-50 hover:bg-rose-100 text-rose-700 font-semibold text-[11px] rounded-lg transition-colors" data-id="' + acc.id + '"><i class="fa fa-trash"></i></button>';
                        rowsHtml += '</div></td></tr>';
                    });
                } else {
                    rowsHtml = '<tr><td colspan="7" class="py-8 text-center text-gray-400"><i class="fa fa-envelope-open-o text-3xl mb-2 text-gray-300 block"></i>No SMTP accounts configured. Click "+ Add Hostinger SMTP Account" to set up your first mail sender.</td></tr>';
                }
                $('#smtp-accounts-tbody').html(rowsHtml);
            }
        });
    }
});
</script>
