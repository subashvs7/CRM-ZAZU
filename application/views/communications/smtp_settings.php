<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
    <div class="flex items-center gap-3">
        <div class="w-10 h-10 bg-blue-50 text-blue-600 rounded-xl flex items-center justify-center flex-shrink-0">
            <i class="fa fa-envelope text-lg"></i>
        </div>
        <div>
            <h1 class="text-xl font-bold text-gray-800">Hostinger SMTP Mail Pool</h1>
            <nav class="text-xs text-gray-400 flex items-center gap-1 mt-0.5">
                <a href="<?= base_url('dashboard') ?>" class="hover:text-blue-600 transition-colors">Home</a>
                <i class="fa fa-angle-right text-[10px]"></i>
                <a href="<?= base_url('communications/bulk_mail') ?>" class="hover:text-blue-600 transition-colors">Communications</a>
                <i class="fa fa-angle-right text-[10px]"></i>
                <span class="text-gray-600 font-semibold">SMTP Settings</span>
            </nav>
        </div>
    </div>

    <div class="flex items-center gap-2.5">
        <button type="button" id="btn-refresh-pool" class="px-3 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-bold rounded-xl transition-colors flex items-center gap-1.5 shadow-2xs">
            <i class="fa fa-refresh"></i> Refresh Quota
        </button>
        <button type="button" id="btn-add-smtp" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl transition-colors shadow-sm flex items-center gap-1.5">
            <i class="fa fa-plus-circle"></i> Add Hostinger Mailbox
        </button>
    </div>
</div>

<!-- ========================================================================= -->
<!-- HOSTINGER MULTI-SMTP MAIL POOL CONTENT                                    -->
<!-- ========================================================================= -->
<div class="space-y-6">

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
                <span class="text-xs text-gray-400 font-semibold">emails / day</span>
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
                <span class="text-2xl font-black text-gray-900 font-mono" id="pool-metric-sent"><?= number_format($smtp_pool['total_sent'] ?? 0) ?></span>
                <span class="text-xs text-gray-400 font-semibold">dispatched</span>
            </div>
            <p class="text-[11px] text-gray-400 mt-1">Resets automatically every midnight</p>
        </div>

        <div class="bg-white rounded-2xl border border-gray-100 p-4 shadow-xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Remaining Quota</span>
                <span class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center text-sm font-bold">
                    <i class="fa fa-battery-three-quarters"></i>
                </span>
            </div>
            <div class="mt-2 flex items-baseline gap-2">
                <span class="text-2xl font-black text-gray-900 font-mono" id="pool-metric-remaining"><?= number_format($smtp_pool['total_remaining'] ?? 0) ?></span>
                <span class="text-xs text-gray-400 font-semibold">available</span>
            </div>
            <div class="w-full bg-gray-100 rounded-full h-1.5 mt-2 overflow-hidden">
                <div id="pool-metric-progress" class="bg-emerald-500 h-1.5 rounded-full" style="width: <?= $smtp_pool['percent_remaining'] ?? 100 ?>%"></div>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-gray-100 p-4 shadow-xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Auto-Switch Engine</span>
                <span class="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center text-sm font-bold">
                    <i class="fa fa-bolt"></i>
                </span>
            </div>
            <div class="mt-2">
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    ACTIVE ROTATION
                </span>
            </div>
            <p class="text-[11px] text-gray-400 mt-2">Auto-switches to next SMTP when 100 limit is reached</p>
        </div>
    </div>

    <!-- Active Alerts Strip -->
    <div id="smtp-alerts-container" class="space-y-2 <?= empty($smtp_pool['alerts']) ? 'hidden' : '' ?>">
        <?php foreach (($smtp_pool['alerts'] ?? []) as $alert): ?>
        <div class="p-3.5 bg-amber-50 border border-amber-200 rounded-xl flex items-start gap-3 text-xs text-amber-900 shadow-xs">
            <i class="fa fa-exclamation-triangle text-amber-600 text-sm mt-0.5 flex-shrink-0"></i>
            <div class="flex-1 font-medium"><?= esc_html($alert) ?></div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Best Practices Banner -->
    <div class="bg-gradient-to-r from-slate-900 to-indigo-950 rounded-2xl p-5 text-white shadow-md">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
            <div class="space-y-1 max-w-2xl">
                <div class="inline-flex items-center gap-2 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-blue-500/20 text-blue-300 border border-blue-400/30 uppercase tracking-wider">
                    Hostinger SMTP Best Practices &bull; 100 Emails/Day Limit Solution
                </div>
                <h3 class="text-base font-bold text-white">Multi-Mailbox Pool with Automated Rotation</h3>
                <p class="text-xs text-slate-300 leading-relaxed">
                    Hostinger limits standard mailboxes to <strong>100 emails/day</strong>. By connecting up to 10 Hostinger accounts (e.g. outreach@, sales@, info@, support@), CRM-ZAZU creates a combined pool of <strong>1,000 emails/day</strong>. When an account sends 100 emails, the system automatically alerts you and switches to the next available account with zero interruption.
                </p>
            </div>
            <div class="bg-white/10 backdrop-blur-xs border border-white/10 rounded-xl p-3.5 text-xs space-y-1.5 flex-shrink-0 font-mono text-slate-200">
                <div class="flex justify-between gap-4"><span class="text-slate-400">Hostinger Host:</span><span class="font-bold text-white">smtp.hostinger.com</span></div>
                <div class="flex justify-between gap-4"><span class="text-slate-400">SSL Port / Crypto:</span><span class="font-bold text-emerald-400">465 (SSL)</span></div>
                <div class="flex justify-between gap-4"><span class="text-slate-400">TLS Port / Crypto:</span><span class="font-bold text-sky-400">587 (TLS)</span></div>
                <div class="flex justify-between gap-4"><span class="text-slate-400">Username:</span><span class="font-bold text-white">Your full email</span></div>
            </div>
        </div>
    </div>

    <!-- Main Table Card -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-xs overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h3 class="text-sm font-bold text-gray-900">Company Common Hostinger SMTP Accounts (<?= count($smtp_pool['accounts'] ?? []) ?> of 10 Slots)</h3>
                <p class="text-xs text-gray-400 mt-0.5">Shared company mailboxes used for all outbound emails with automated rotation and instant switching</p>
            </div>
            <span class="text-xs font-mono font-bold text-blue-600 bg-blue-50 px-2.5 py-1 rounded-lg border border-blue-100">
                <?= count($smtp_pool['accounts'] ?? []) ?> / 10 Connected
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="bg-gray-50/80 text-gray-500 uppercase tracking-wider font-semibold border-b border-gray-100">
                        <th class="py-3 px-4 w-12 text-center">Slot</th>
                        <th class="py-3 px-4">Account &amp; Sender</th>
                        <th class="py-3 px-4">Server Details</th>
                        <th class="py-3 px-4 text-center">Daily Limit</th>
                        <th class="py-3 px-4">Usage Today</th>
                        <th class="py-3 px-4 text-center">Status</th>
                        <th class="py-3 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody id="smtp-accounts-tbody" class="divide-y divide-gray-100">
                    <?php if (empty($smtp_pool['accounts'])): ?>
                        <tr>
                            <td colspan="7" class="py-12 text-center text-gray-400">
                                <i class="fa fa-envelope-open-o text-4xl mb-3 text-gray-300 block"></i>
                                <span class="font-bold text-gray-600 block text-sm">No SMTP accounts configured yet</span>
                                Click "+ Add Hostinger Mailbox" above to connect your first mailbox slot.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($smtp_pool['accounts'] as $idx => $acc): 
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
                                    #<?= $idx + 1 ?>
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
        <div class="px-6 py-4 bg-gradient-to-r from-blue-600 to-indigo-600 text-white flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-white/10 flex items-center justify-center">
                    <i class="fa fa-envelope text-white"></i>
                </div>
                <div>
                    <h3 class="text-sm font-bold" id="modal-smtp-title">Add Hostinger SMTP Account</h3>
                    <p class="text-[11px] text-blue-100">Shared company Hostinger email account for all CRM mailings</p>
                </div>
            </div>
            <button type="button" class="btn-close-smtp-modal text-white/80 hover:text-white transition-colors">
                <i class="fa fa-times text-base"></i>
            </button>
        </div>

        <form id="form-smtp-account" class="p-6 space-y-4">
            <input type="hidden" name="id" id="smtp-id" value="">
            <input type="hidden" name="<?= $csrf_name ?>" value="<?= $csrf_hash ?>">

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1.5">Account Label / Slot Name <span class="text-rose-500">*</span></label>
                    <input type="text" name="name" id="smtp-name" required placeholder="e.g. Sales Outreach Mail" class="w-full px-3 py-2 border border-gray-200 rounded-xl text-xs focus:ring-2 focus:ring-blue-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1.5">Daily Quota Limit <span class="text-rose-500">*</span></label>
                    <input type="number" name="daily_limit" id="smtp-daily-limit" required value="100" min="10" max="500" class="w-full px-3 py-2 border border-gray-200 rounded-xl text-xs font-mono focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    <span class="text-[10px] text-gray-400">Hostinger standard: 100 emails/day</span>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1.5">Company Sender Display Name <span class="text-rose-500">*</span></label>
                    <input type="text" name="sender_name" id="smtp-sender-name" required placeholder="e.g. ZAZU CRM Team" class="w-full px-3 py-2 border border-gray-200 rounded-xl text-xs focus:ring-2 focus:ring-blue-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1.5">Company Sender Email Address <span class="text-rose-500">*</span></label>
                    <input type="email" name="sender_email" id="smtp-sender-email" required placeholder="e.g. outreach@zazutech.in" class="w-full px-3 py-2 border border-gray-200 rounded-xl text-xs focus:ring-2 focus:ring-blue-500 focus:outline-none">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
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

    // Port / Crypto Selector Sync
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

    // Open Add Account Modal
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

    // Open Edit Account Modal
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

    // Test Connection Socket
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

    // Send Real Test Email Verification to user inbox
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

    // Reset Today's Count
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

    // Delete SMTP Account
    $(document).on('click', '.btn-delete-smtp', function(){
        var id = $(this).data('id');
        if(!confirm('Are you sure you want to remove this Hostinger SMTP account from the active rotation pool?')) return;

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

    // Save SMTP Form Submit
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
                    rowsHtml = '<tr><td colspan="7" class="py-8 text-center text-gray-400"><i class="fa fa-envelope-open-o text-3xl mb-2 text-gray-300 block"></i>No SMTP accounts configured. Click "+ Add Hostinger Mailbox" to set up your first mail sender.</td></tr>';
                }
                $('#smtp-accounts-tbody').html(rowsHtml);
            }
        });
    }
});
</script>
