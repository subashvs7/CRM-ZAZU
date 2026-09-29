<!-- Summernote Lite CSS -->
<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-lite.min.css" rel="stylesheet">

<div class="space-y-6">
    <!-- Top Header Bar -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-white p-5 rounded-2xl border border-gray-100 shadow-xs">
        <div class="flex items-center gap-3.5">
            <div class="w-12 h-12 bg-gradient-to-tr from-blue-600 to-indigo-600 text-white rounded-2xl flex items-center justify-center shadow-md shadow-blue-500/20 flex-shrink-0">
                <i class="fa fa-envelope-open-o text-xl"></i>
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-xl font-bold text-gray-900">Bulk Mail & Outreach Hub</h1>
                    <span class="px-2 py-0.5 text-[11px] font-bold bg-blue-50 text-blue-700 border border-blue-200 rounded-full">Pro Marketer</span>
                </div>
                <p class="text-xs text-gray-500 mt-0.5">Dynamic product-specific email templates, client onboarding, portal credentials & follow-up tracking.</p>
            </div>
        </div>

        <!-- Quick Top Links to Separate Pages -->
        <div class="flex items-center gap-2 flex-wrap">
            <a href="<?= base_url('communications/mail_templates') ?>" class="px-3.5 py-2 bg-white hover:bg-gray-50 border border-gray-200 text-gray-700 text-xs font-semibold rounded-xl transition-all shadow-xs flex items-center gap-1.5">
                <i class="fa fa-folder-open-o text-purple-600"></i> Mail Templates (<?= count($templates) ?>)
            </a>
            <a href="<?= base_url('communications/mail_history') ?>" class="px-3.5 py-2 bg-white hover:bg-gray-50 border border-gray-200 text-gray-700 text-xs font-semibold rounded-xl transition-all shadow-xs flex items-center gap-1.5">
                <i class="fa fa-history text-indigo-600"></i> Mail History
            </a>
            <a href="<?= base_url('communications/smtp_settings') ?>" class="px-3.5 py-2 bg-white hover:bg-gray-50 border border-gray-200 text-gray-700 text-xs font-semibold rounded-xl transition-all shadow-xs flex items-center gap-1.5">
                <i class="fa fa-sliders text-emerald-600"></i> SMTP Settings
            </a>
        </div>
    </div>

    <!-- Quick Metrics Strip -->
    <div class="grid grid-cols-2 sm:grid-cols-5 gap-3.5 text-xs">
        <div class="p-3.5 bg-white border border-gray-100 rounded-2xl shadow-xs flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center font-bold flex-shrink-0">
                <i class="fa fa-paper-plane"></i>
            </div>
            <div>
                <span class="block text-lg font-black text-gray-900 font-mono" id="stat-total-sent"><?= number_format($stats['sent'] ?? 0) ?></span>
                <span class="text-[11px] font-semibold text-gray-500 uppercase tracking-tight">Delivered Emails</span>
            </div>
        </div>
        <div class="p-3.5 bg-white border border-gray-100 rounded-2xl shadow-xs flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center font-bold flex-shrink-0">
                <i class="fa fa-hourglass-half"></i>
            </div>
            <div>
                <span class="block text-lg font-black text-amber-700 font-mono" id="stat-queued"><?= number_format($stats['queued'] ?? 0) ?></span>
                <span class="text-[11px] font-semibold text-gray-500 uppercase tracking-tight">In Queue</span>
            </div>
        </div>
        <div class="p-3.5 bg-white border border-gray-100 rounded-2xl shadow-xs flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center font-bold flex-shrink-0">
                <i class="fa fa-folder-text-o fa-bookmark"></i>
            </div>
            <div>
                <span class="block text-lg font-black text-purple-700 font-mono" id="stat-templates"><?= count($templates) ?></span>
                <span class="text-[11px] font-semibold text-gray-500 uppercase tracking-tight">Active Templates</span>
            </div>
        </div>
        <div class="p-3.5 bg-white border border-gray-100 rounded-2xl shadow-xs flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold flex-shrink-0">
                <i class="fa fa-users"></i>
            </div>
            <div>
                <span class="block text-lg font-black text-emerald-700 font-mono"><?= number_format($total_leads_count) ?></span>
                <span class="text-[11px] font-semibold text-gray-500 uppercase tracking-tight">Leads with Email</span>
            </div>
        </div>
        <div class="p-3.5 bg-white border border-gray-100 rounded-2xl shadow-xs flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold flex-shrink-0">
                <i class="fa fa-building"></i>
            </div>
            <div>
                <span class="block text-lg font-black text-indigo-700 font-mono"><?= number_format($total_custs_count) ?></span>
                <span class="text-[11px] font-semibold text-gray-500 uppercase tracking-tight">Customers with Email</span>
            </div>
        </div>
    </div>

    <!-- Company Common Hostinger Multi-SMTP Live Quota & Auto-Switch Strip -->
    <div class="bg-gradient-to-r from-slate-900 via-blue-950 to-indigo-950 text-white rounded-2xl p-4 shadow-sm border border-slate-800">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-blue-500/20 border border-blue-400/30 text-blue-300 flex items-center justify-center flex-shrink-0">
                    <i class="fa fa-server text-base"></i>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-bold uppercase tracking-wider text-blue-300">Company Common SMTP Pool</span>
                        <span class="px-2 py-0.5 text-[10px] font-bold rounded-md bg-emerald-500/20 text-emerald-300 border border-emerald-400/30 flex items-center gap-1">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                            Auto-Switching Active (100/account limit)
                        </span>
                    </div>
                    <div class="text-xs text-gray-300 mt-0.5">
                        Company Mailboxes: <strong class="text-white font-mono" id="bm-pool-remaining"><?= number_format($smtp_pool['total_remaining'] ?? 0) ?></strong> of <span class="font-mono text-gray-400" id="bm-pool-capacity"><?= number_format($smtp_pool['total_capacity'] ?? 0) ?></span> emails remaining today across <strong class="text-white font-mono"><?= count($smtp_pool['accounts'] ?? []) ?></strong> shared accounts.
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <div class="hidden sm:block text-right">
                    <span class="text-[11px] text-gray-400 block">Sent Today: <strong class="text-emerald-400 font-mono" id="bm-pool-sent"><?= number_format($smtp_pool['total_sent'] ?? 0) ?></strong></span>
                    <div class="w-28 bg-white/10 rounded-full h-1.5 mt-1 overflow-hidden">
                        <div id="bm-pool-progress" class="bg-blue-400 h-1.5 rounded-full" style="width: <?= $smtp_pool['percent_remaining'] ?? 100 ?>%"></div>
                    </div>
                </div>
                <a href="<?= base_url('communications/smtp_settings') ?>" class="px-3 py-1.5 bg-white/10 hover:bg-white/20 text-white text-xs font-bold rounded-xl border border-white/20 transition-all flex items-center gap-1.5 flex-shrink-0">
                    <i class="fa fa-sliders"></i> SMTP Settings
                </a>
            </div>
        </div>

        <!-- Live alerts strip if accounts reached limit -->
        <div id="bm-smtp-alerts" class="mt-3 space-y-1.5 <?= empty($smtp_pool['alerts']) ? 'hidden' : '' ?>">
            <?php foreach (($smtp_pool['alerts'] ?? []) as $alt): ?>
                <div class="px-3 py-1.5 bg-amber-500/20 border border-amber-400/40 rounded-lg text-[11px] text-amber-200 flex items-center gap-2">
                    <i class="fa fa-exclamation-circle text-amber-300"></i>
                    <span><?= esc_html($alt) ?></span>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- TAB 1: COMPOSE & OUTREACH HUB (DEFAULT VIEW)                              -->
    <!-- ========================================================================= -->
    <div id="tab-compose" class="tab-pane active space-y-6">
        <form id="form-bulk-mail" action="<?= base_url('communications/process_bulk_mail') ?>" method="POST">
            <input type="hidden" name="<?= $csrf_name ?>" value="<?= $csrf_hash ?>">

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
                <!-- Left Panel: Composer & Audience Configuration (7 cols) -->
                <div class="lg:col-span-7 space-y-5">
                    
                    <!-- 1. Multi-Selection Audience Selector Card -->
                    <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-xs space-y-4">
                        <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                            <div>
                                <h3 class="text-xs font-bold text-gray-900 uppercase tracking-wide flex items-center gap-2">
                                    <span class="w-5 h-5 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center text-[10px]">1</span>
                                    Target Audience &amp; Multi-Selection Group
                                </h3>
                                <p class="text-[11px] text-gray-400 mt-0.5">Select multiple audience groups simultaneously (automatic deduplication)</p>
                            </div>
                            <span id="badge-audience-count" class="px-2.5 py-1 text-xs font-bold rounded-lg bg-emerald-50 text-emerald-800 border border-emerald-200 font-mono">
                                Calculating...
                            </span>
                        </div>

                        <!-- Quick Selection Shortcut Pills -->
                        <div class="flex items-center justify-between">
                            <span class="text-[11px] font-semibold text-gray-500 uppercase">Select Target Audiences:</span>
                            <div class="flex items-center gap-1.5">
                                <button type="button" id="btn-aud-select-all" class="px-2 py-0.5 text-[11px] font-semibold rounded-md bg-blue-50 hover:bg-blue-100 text-blue-700 transition-colors">
                                    Select All
                                </button>
                                <button type="button" id="btn-aud-leads-only" class="px-2 py-0.5 text-[11px] font-semibold rounded-md bg-gray-100 hover:bg-gray-200 text-gray-700 transition-colors">
                                    Leads Only
                                </button>
                                <button type="button" id="btn-aud-custs-only" class="px-2 py-0.5 text-[11px] font-semibold rounded-md bg-gray-100 hover:bg-gray-200 text-gray-700 transition-colors">
                                    Customers Only
                                </button>
                                <button type="button" id="btn-aud-clear" class="px-2 py-0.5 text-[11px] font-semibold rounded-md bg-rose-50 hover:bg-rose-100 text-rose-700 transition-colors">
                                    Clear
                                </button>
                            </div>
                        </div>

                        <!-- 3 Multi-Selection Interactive Cards -->
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <!-- Card 1: Leads -->
                            <label class="cursor-pointer border-2 border-blue-500 bg-blue-50/40 rounded-xl p-3 flex flex-col justify-between transition-all hover:shadow-xs audience-card" data-for="aud-chk-leads">
                                <div class="flex items-start justify-between">
                                    <div class="flex items-center gap-2">
                                        <div class="w-7 h-7 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center text-xs">
                                            <i class="fa fa-users"></i>
                                        </div>
                                        <span class="text-xs font-bold text-gray-900">All Leads</span>
                                    </div>
                                    <input type="checkbox" name="recipient_types[]" value="leads" id="aud-chk-leads" checked class="audience-chk rounded border-gray-300 text-blue-600 focus:ring-blue-500 mt-1">
                                </div>
                                <div class="mt-2 flex items-center justify-between text-[11px]">
                                    <span class="text-gray-500 font-mono"><?= number_format($total_leads_count) ?> with email</span>
                                    <span class="font-bold text-emerald-600">Selected</span>
                                </div>
                            </label>

                            <!-- Card 2: Customers -->
                            <label class="cursor-pointer border border-gray-200 bg-white rounded-xl p-3 flex flex-col justify-between transition-all hover:shadow-xs audience-card" data-for="aud-chk-custs">
                                <div class="flex items-start justify-between">
                                    <div class="flex items-center gap-2">
                                        <div class="w-7 h-7 rounded-lg bg-indigo-100 text-indigo-700 flex items-center justify-center text-xs">
                                            <i class="fa fa-building"></i>
                                        </div>
                                        <span class="text-xs font-bold text-gray-900">Customers</span>
                                    </div>
                                    <input type="checkbox" name="recipient_types[]" value="customers" id="aud-chk-custs" class="audience-chk rounded border-gray-300 text-blue-600 focus:ring-blue-500 mt-1">
                                </div>
                                <div class="mt-2 flex items-center justify-between text-[11px]">
                                    <span class="text-gray-500 font-mono"><?= number_format($total_custs_count) ?> with email</span>
                                    <span class="font-semibold text-gray-400 status-lbl">Optional</span>
                                </div>
                            </label>

                            <!-- Card 3: Contact Book -->
                            <label class="cursor-pointer border border-gray-200 bg-white rounded-xl p-3 flex flex-col justify-between transition-all hover:shadow-xs audience-card" data-for="aud-chk-contacts">
                                <div class="flex items-start justify-between">
                                    <div class="flex items-center gap-2">
                                        <div class="w-7 h-7 rounded-lg bg-amber-100 text-amber-700 flex items-center justify-center text-xs">
                                            <i class="fa fa-address-book"></i>
                                        </div>
                                        <span class="text-xs font-bold text-gray-900">Contact Book</span>
                                    </div>
                                    <input type="checkbox" name="recipient_types[]" value="contact_book" id="aud-chk-contacts" class="audience-chk rounded border-gray-300 text-blue-600 focus:ring-blue-500 mt-1">
                                </div>
                                <div class="mt-2 flex items-center justify-between text-[11px]">
                                    <span class="text-gray-500 font-mono"><?= number_format($total_contk_count) ?> with email</span>
                                    <span class="font-semibold text-gray-400 status-lbl">Optional</span>
                                </div>
                            </label>
                        </div>

                        <!-- Deduplication Badge -->
                        <div class="px-3.5 py-2 bg-emerald-50/70 border border-emerald-200 rounded-xl text-xs text-emerald-800 flex items-center justify-between">
                            <span class="flex items-center gap-2">
                                <i class="fa fa-check-circle text-emerald-600"></i>
                                <span><strong>Deduplication Engine Active:</strong> Duplicate emails across groups are automatically merged.</span>
                            </span>
                            <span class="text-[11px] text-gray-500 font-mono hidden sm:inline">1 email per contact</span>
                        </div>

                        <!-- ============================================================= -->
                        <!-- 1. LEADS SELECTION PANEL (Shown ONLY if All Leads is Checked) -->
                        <!-- ============================================================= -->
                        <div id="section-leads-config" class="p-4 bg-emerald-50/50 border border-emerald-200/80 rounded-2xl space-y-3 shadow-2xs transition-all">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-2.5 border-b border-emerald-100">
                                <div class="flex items-center gap-2">
                                    <div class="w-6 h-6 rounded-lg bg-emerald-600 text-white flex items-center justify-center text-xs shadow-xs">
                                        <i class="fa fa-users"></i>
                                    </div>
                                    <span class="text-xs font-bold text-gray-900 uppercase tracking-wide">Leads Multi-Select</span>
                                    <span id="badge-leads-count" class="px-2 py-0.5 rounded-full bg-emerald-600 text-white text-[10px] font-mono font-bold">
                                        All Leads Selected
                                    </span>
                                </div>
                                <div class="flex items-center gap-1.5 flex-wrap">
                                    <!-- Quick Batch Pickers -->
                                    <span class="text-[10px] font-bold text-emerald-800 uppercase mr-0.5">Quick Pick:</span>
                                    <button type="button" class="btn-leads-select-batch px-2 py-1 bg-white hover:bg-emerald-100 border border-emerald-300 text-emerald-800 rounded-lg text-xs font-bold transition shadow-2xs cursor-pointer" data-count="25" title="Select First 25 Leads">
                                        25
                                    </button>
                                    <button type="button" class="btn-leads-select-batch px-2 py-1 bg-white hover:bg-emerald-100 border border-emerald-300 text-emerald-800 rounded-lg text-xs font-bold transition shadow-2xs cursor-pointer" data-count="50" title="Select First 50 Leads">
                                        50
                                    </button>
                                    <button type="button" class="btn-leads-select-batch px-2 py-1 bg-white hover:bg-emerald-100 border border-emerald-300 text-emerald-800 rounded-lg text-xs font-bold transition shadow-2xs cursor-pointer" data-count="100" title="Select First 100 Leads">
                                        100
                                    </button>
                                    <span class="text-emerald-300 mx-0.5">|</span>
                                    <button type="button" id="btn-leads-select-all" class="px-2.5 py-1 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-bold transition-colors shadow-2xs cursor-pointer">
                                        <i class="fa fa-check-square"></i> All
                                    </button>
                                    <button type="button" id="btn-leads-deselect-all" class="px-2.5 py-1 bg-white hover:bg-rose-50 border border-rose-200 text-rose-700 rounded-lg text-xs font-bold transition-colors cursor-pointer">
                                        <i class="fa fa-square-o"></i> Clear
                                    </button>
                                </div>
                            </div>

                            <!-- Smart Filter Tabs Bar for Leads -->
                            <div class="flex items-center gap-1.5 flex-wrap pt-1">
                                <button type="button" class="leads-tab-filter active px-2.5 py-1 rounded-lg text-xs font-bold bg-emerald-600 text-white shadow-2xs transition cursor-pointer" data-filter="all">
                                    🌟 All Leads
                                </button>
                                <button type="button" class="leads-tab-filter px-2.5 py-1 rounded-lg text-xs font-bold bg-white text-gray-700 hover:bg-emerald-50 border border-gray-200 transition cursor-pointer" data-filter="never_sent">
                                    📬 Never Sent (Fresh)
                                </button>
                                <button type="button" class="leads-tab-filter px-2.5 py-1 rounded-lg text-xs font-bold bg-white text-gray-700 hover:bg-emerald-50 border border-gray-200 transition cursor-pointer" data-filter="already_sent">
                                    🔄 Already Sent (Follow-Up)
                                </button>
                                <button type="button" class="leads-tab-filter px-2.5 py-1 rounded-lg text-xs font-bold bg-white text-gray-700 hover:bg-emerald-50 border border-gray-200 transition cursor-pointer" data-filter="failed">
                                    ⚠️ Failed / Bounced (Retry)
                                </button>
                                <button type="button" class="leads-tab-filter px-2.5 py-1 rounded-lg text-xs font-bold bg-white text-gray-700 hover:bg-emerald-50 border border-gray-200 transition cursor-pointer" data-filter="inbound">
                                    📥 Inbound
                                </button>
                                <button type="button" class="leads-tab-filter px-2.5 py-1 rounded-lg text-xs font-bold bg-white text-gray-700 hover:bg-emerald-50 border border-gray-200 transition cursor-pointer" data-filter="outbound">
                                    📤 Outbound
                                </button>
                            </div>

                            <!-- Stage Filter & Search Row -->
                            <div class="grid grid-cols-1 sm:grid-cols-12 gap-2.5">
                                <div class="sm:col-span-5">
                                    <label class="block text-[11px] font-bold text-gray-600 mb-1">Lead Stage Filter</label>
                                    <select name="lead_status" id="select-lead-status" class="w-full px-2.5 py-1.5 bg-white border border-emerald-200 rounded-lg text-xs font-semibold text-gray-800 focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                                        <option value="all" selected>All Stages (Any Status)</option>
                                        <option value="new">New Inquiries</option>
                                        <option value="contacted">Contacted</option>
                                        <option value="qualified">Qualified</option>
                                        <option value="proposal">Proposal / Quotation Sent</option>
                                        <option value="negotiation">Negotiation</option>
                                        <option value="won">Closed / Won</option>
                                    </select>
                                </div>
                                <div class="sm:col-span-7">
                                    <label class="block text-[11px] font-bold text-gray-600 mb-1">Search Leads</label>
                                    <div class="relative">
                                        <i class="fa fa-search absolute left-2.5 top-2 text-gray-400 text-xs"></i>
                                        <input type="text" id="leads-search-input" placeholder="Search by lead name, company, email..." class="w-full pl-7 pr-3 py-1.5 bg-white border border-emerald-200 rounded-lg text-xs focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                                    </div>
                                </div>
                            </div>

                            <!-- Scrollable Lead Items List -->
                            <div class="max-h-60 overflow-y-auto border border-emerald-200 bg-white rounded-xl divide-y divide-gray-100" id="leads-checklist-container">
                                <div class="p-4 text-center text-gray-400 text-xs">
                                    <i class="fa fa-spinner fa-spin text-emerald-600 text-base mb-1 block"></i> Loading leads...
                                </div>
                            </div>
                        </div>

                        <!-- ================================================================= -->
                        <!-- 2. CUSTOMERS SELECTION PANEL (Shown ONLY if Customers is Checked) -->
                        <!-- ================================================================= -->
                        <div id="section-customers-config" class="hidden p-4 bg-indigo-50/50 border border-indigo-200/80 rounded-2xl space-y-3 shadow-2xs transition-all">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-2.5 border-b border-indigo-100">
                                <div class="flex items-center gap-2">
                                    <div class="w-6 h-6 rounded-lg bg-indigo-600 text-white flex items-center justify-center text-xs shadow-xs">
                                        <i class="fa fa-building"></i>
                                    </div>
                                    <span class="text-xs font-bold text-gray-900 uppercase tracking-wide">Customers Multi-Select</span>
                                    <span id="badge-custs-count" class="px-2 py-0.5 rounded-full bg-indigo-600 text-white text-[10px] font-mono font-bold">
                                        All Customers Selected
                                    </span>
                                </div>
                                <div class="flex items-center gap-1.5 flex-wrap">
                                    <!-- Quick Batch Pickers -->
                                    <span class="text-[10px] font-bold text-indigo-800 uppercase mr-0.5">Quick Pick:</span>
                                    <button type="button" class="btn-custs-select-batch px-2 py-1 bg-white hover:bg-indigo-100 border border-indigo-300 text-indigo-800 rounded-lg text-xs font-bold transition shadow-2xs cursor-pointer" data-count="25" title="Select First 25 Customers">
                                        25
                                    </button>
                                    <button type="button" class="btn-custs-select-batch px-2 py-1 bg-white hover:bg-indigo-100 border border-indigo-300 text-indigo-800 rounded-lg text-xs font-bold transition shadow-2xs cursor-pointer" data-count="50" title="Select First 50 Customers">
                                        50
                                    </button>
                                    <button type="button" class="btn-custs-select-batch px-2 py-1 bg-white hover:bg-indigo-100 border border-indigo-300 text-indigo-800 rounded-lg text-xs font-bold transition shadow-2xs cursor-pointer" data-count="100" title="Select First 100 Customers">
                                        100
                                    </button>
                                    <span class="text-indigo-300 mx-0.5">|</span>
                                    <button type="button" id="btn-custs-select-all" class="px-2.5 py-1 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-xs font-bold transition-colors shadow-2xs cursor-pointer">
                                        <i class="fa fa-check-square"></i> All
                                    </button>
                                    <button type="button" id="btn-custs-deselect-all" class="px-2.5 py-1 bg-white hover:bg-rose-50 border border-rose-200 text-rose-700 rounded-lg text-xs font-bold transition-colors cursor-pointer">
                                        <i class="fa fa-square-o"></i> Clear
                                    </button>
                                </div>
                            </div>

                            <!-- Smart Filter Tabs Bar for Customers -->
                            <div class="flex items-center gap-1.5 flex-wrap pt-1">
                                <button type="button" class="custs-tab-filter active px-2.5 py-1 rounded-lg text-xs font-bold bg-indigo-600 text-white shadow-2xs transition cursor-pointer" data-filter="all">
                                    🌟 All Customers
                                </button>
                                <button type="button" class="custs-tab-filter px-2.5 py-1 rounded-lg text-xs font-bold bg-white text-gray-700 hover:bg-indigo-50 border border-gray-200 transition cursor-pointer" data-filter="never_sent">
                                    📬 Uncontacted
                                </button>
                                <button type="button" class="custs-tab-filter px-2.5 py-1 rounded-lg text-xs font-bold bg-white text-gray-700 hover:bg-indigo-50 border border-gray-200 transition cursor-pointer" data-filter="already_sent">
                                    🔄 Already Contacted
                                </button>
                                <button type="button" class="custs-tab-filter px-2.5 py-1 rounded-lg text-xs font-bold bg-white text-gray-700 hover:bg-indigo-50 border border-gray-200 transition cursor-pointer" data-filter="failed">
                                    ⚠️ Failed (Retry)
                                </button>
                            </div>

                            <!-- Customer Search Bar -->
                            <div>
                                <label class="block text-[11px] font-bold text-gray-600 mb-1">Search Customers</label>
                                <div class="relative">
                                    <i class="fa fa-search absolute left-2.5 top-2 text-gray-400 text-xs"></i>
                                    <input type="text" id="custs-search-input" placeholder="Search by customer name, company, email..." class="w-full pl-7 pr-3 py-1.5 bg-white border border-indigo-200 rounded-lg text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                                </div>
                            </div>

                            <!-- Scrollable Customer Items List -->
                            <div class="max-h-60 overflow-y-auto border border-indigo-200 bg-white rounded-xl divide-y divide-gray-100" id="custs-checklist-container">
                                <div class="p-4 text-center text-gray-400 text-xs">
                                    <i class="fa fa-spinner fa-spin text-indigo-600 text-base mb-1 block"></i> Loading customers...
                                </div>
                            </div>
                        </div>

                        <!-- ==================================================================== -->
                        <!-- 3. CONTACT BOOK SELECTION PANEL (Shown ONLY if Contact Book Checked) -->
                        <!-- ==================================================================== -->
                        <div id="section-contacts-config" class="hidden p-4 bg-amber-50/50 border border-amber-200/80 rounded-2xl space-y-3 shadow-2xs transition-all">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-2.5 border-b border-amber-100">
                                <div class="flex items-center gap-2">
                                    <div class="w-6 h-6 rounded-lg bg-amber-600 text-white flex items-center justify-center text-xs shadow-xs">
                                        <i class="fa fa-address-book"></i>
                                    </div>
                                    <span class="text-xs font-bold text-gray-900 uppercase tracking-wide">Contact Book Multi-Select</span>
                                    <span id="badge-contacts-count" class="px-2 py-0.5 rounded-full bg-amber-600 text-white text-[10px] font-mono font-bold">
                                        All Contacts Selected
                                    </span>
                                </div>
                                <div class="flex items-center gap-1.5 flex-wrap">
                                    <button type="button" id="btn-contacts-select-all" class="px-2.5 py-1 bg-amber-600 hover:bg-amber-700 text-white rounded-lg text-xs font-bold transition-colors shadow-2xs">
                                        <i class="fa fa-check-square"></i> Select All
                                    </button>
                                    <button type="button" id="btn-contacts-deselect-all" class="px-2.5 py-1 bg-white hover:bg-amber-100 border border-amber-300 text-amber-800 rounded-lg text-xs font-bold transition-colors">
                                        <i class="fa fa-square-o"></i> Clear
                                    </button>
                                </div>
                            </div>

                            <!-- Contact Search Bar -->
                            <div>
                                <label class="block text-[11px] font-bold text-gray-600 mb-1">Search Contacts</label>
                                <div class="relative">
                                    <i class="fa fa-search absolute left-2.5 top-2 text-gray-400 text-xs"></i>
                                    <input type="text" id="contacts-search-input" placeholder="Search by name, company, email..." class="w-full pl-7 pr-3 py-1.5 bg-white border border-amber-200 rounded-lg text-xs focus:ring-2 focus:ring-amber-500 focus:outline-none">
                                </div>
                            </div>

                            <!-- Scrollable Contacts Items List -->
                            <div class="max-h-60 overflow-y-auto border border-amber-200 bg-white rounded-xl divide-y divide-gray-100" id="contacts-checklist-container">
                                <div class="p-4 text-center text-gray-400 text-xs">
                                    <i class="fa fa-spinner fa-spin text-amber-600 text-base mb-1 block"></i> Loading contacts...
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- 2. Instant Sender Mailbox Switcher Card -->
                    <div class="bg-white rounded-2xl border border-blue-200/80 bg-gradient-to-r from-blue-50/40 via-indigo-50/20 to-white p-5 shadow-xs space-y-3">
                        <div class="flex items-center justify-between pb-2 border-b border-gray-100">
                            <h3 class="text-xs font-bold text-gray-900 uppercase tracking-wide flex items-center gap-2">
                                <span class="w-5 h-5 rounded-full bg-blue-600 text-white flex items-center justify-center text-[10px]">
                                    <i class="fa fa-envelope"></i>
                                </span>
                                Instant Sender Mailbox Switcher
                            </h3>
                            <span class="px-2 py-0.5 text-[10px] font-bold rounded-md bg-blue-100 text-blue-800 font-mono">
                                <?= count($smtp_pool['accounts'] ?? []) ?> Mailbox<?= count($smtp_pool['accounts'] ?? []) === 1 ? '' : 'es' ?> Connected
                            </span>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1.5 flex items-center justify-between">
                                <span>Send Outbound Campaign From:</span>
                                <span class="text-[11px] text-blue-600 font-normal">Switch sender instantly anytime</span>
                            </label>
                            <select name="sender_smtp_id" id="select-sender-smtp" class="w-full px-3.5 py-2.5 bg-white border border-blue-300 rounded-xl text-xs font-semibold text-gray-800 focus:ring-2 focus:ring-blue-500 focus:outline-none shadow-xs">
                                <option value="auto" data-sender="Auto-Rotated Pool" data-email="Smart Fair-Share Mailbox Pool">
                                    🔄 Auto-Rotate Pool (Smart Fair-Share Load Balancing across all 100-limit mailboxes)
                                </option>
                                <?php foreach (($smtp_pool['accounts'] ?? []) as $acc): 
                                    $remQuota = max(0, (int)$acc['daily_limit'] - (int)$acc['sent_today']);
                                    $isExhausted = ($acc['status'] === 'limit_reached' || $remQuota === 0 || $acc['status'] === 'disabled');
                                ?>
                                <option value="<?= $acc['id'] ?>" 
                                        data-sender="<?= esc_html($acc['sender_name'] ?: $acc['name']) ?>" 
                                        data-email="<?= esc_html($acc['sender_email']) ?>"
                                        data-remaining="<?= $remQuota ?>"
                                        <?= $isExhausted ? 'disabled' : '' ?>>
                                    ✉️ <?= esc_html($acc['sender_name'] ?: $acc['name']) ?> &lt;<?= esc_html($acc['sender_email']) ?>&gt; &bull; <?= $remQuota ?> left today (<?= $acc['status'] ?>)
                                </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="flex items-center justify-between mt-1 text-[11px] text-gray-500">
                                <span><i class="fa fa-info-circle text-blue-500 mr-1"></i> Pick a specific mailbox or leave on Auto-Rotate.</span>
                                <a href="<?= base_url('communications/smtp_settings') ?>" class="text-blue-600 hover:underline font-semibold flex items-center gap-1">
                                    <i class="fa fa-sliders"></i> Open SMTP Settings
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- 3. Dynamic Product & Template Selector Card -->
                    <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-xs space-y-4">
                        <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                            <h3 class="text-xs font-bold text-gray-900 uppercase tracking-wide flex items-center gap-2">
                                <span class="w-5 h-5 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center text-[10px]">3</span>
                                Product & Dynamic Template
                            </h3>
                            <button type="button" id="btn-quick-new-template" class="text-xs font-bold text-emerald-600 hover:text-emerald-800 flex items-center gap-1">
                                <i class="fa fa-plus-circle"></i> Create New Template
                            </button>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1.5 flex items-center justify-between">
                                    <span>Select Product</span>
                                    <span class="text-[10px] text-purple-600 font-bold uppercase tracking-tight">Auto-Adapts Templates</span>
                                </label>
                                <select name="product_id" id="select-product" class="w-full px-3 py-2.5 bg-gray-50/70 border border-gray-200 rounded-xl text-xs font-medium text-gray-800 focus:bg-white focus:ring-2 focus:ring-purple-500 focus:outline-none">
                                    <option value="" data-name="CRM-ZAZU Solutions" data-price="₹ 1,29,999" data-url="www.zazutech.in">All Products / General Outreach</option>
                                    <?php foreach ($products as $p): ?>
                                    <option value="<?= $p['id'] ?>" 
                                            data-name="<?= esc_html($p['name']) ?>" 
                                            data-sku="<?= esc_html($p['sku']) ?>" 
                                            data-price="<?= $p['price'] ? esc_html(format_inr($p['price'])) : '' ?>" 
                                            data-url="<?= esc_html($p['website_url'] ?? '') ?>">
                                        <?= esc_html($p['name']) ?> (<?= esc_html($p['sku']) ?>)
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1.5">Template Category</label>
                                <select id="filter-category" class="w-full px-3 py-2.5 bg-gray-50/70 border border-gray-200 rounded-xl text-xs font-medium text-gray-800 focus:bg-white focus:ring-2 focus:ring-blue-500 focus:outline-none">
                                    <option value="all">All Categories</option>
                                    <option value="onboarding">Client Onboarding</option>
                                    <option value="thank_you">Thank You & Appreciation</option>
                                    <option value="login">Login Credentials & Access</option>
                                    <option value="demo">Product Demo & Walkthrough</option>
                                    <option value="proposal">Quotation & Proposal</option>
                                    <option value="followup">Follow-up & Pitch</option>
                                    <option value="general">General Notification</option>
                                </select>
                            </div>
                        </div>

                        <!-- Template Pick Dropdown -->
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1.5">Load Template Into Editor</label>
                            <div class="flex gap-2">
                                <select name="template_id" id="select-template" class="w-full px-3 py-2.5 bg-gray-50/70 border border-gray-200 rounded-xl text-xs font-medium text-gray-800 focus:bg-white focus:ring-2 focus:ring-blue-500 focus:outline-none">
                                    <option value="">-- Choose an Email Template (Or Write from Scratch) --</option>
                                    <?php foreach ($templates as $t): ?>
                                    <option value="<?= $t['id'] ?>" data-product="<?= $t['product_id'] ?>" data-cat="<?= $t['category'] ?>">
                                        [<?= strtoupper($t['category']) ?>] <?= esc_html($t['name']) ?> <?= $t['product_name'] ? '('.esc_html($t['product_name']).')' : '' ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="button" id="btn-apply-template" class="px-3.5 py-2.5 bg-blue-50 hover:bg-blue-100 text-blue-700 border border-blue-200 rounded-xl text-xs font-bold whitespace-nowrap transition-colors">
                                    Load
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- 4. Subject & Dynamic Merge Tags Toolbar -->
                    <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-xs space-y-4">
                        <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                            <h3 class="text-xs font-bold text-gray-900 uppercase tracking-wide flex items-center gap-2">
                                <span class="w-5 h-5 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center text-[10px]">4</span>
                                Email Subject Line
                            </h3>
                            <span class="text-[11px] text-gray-400 font-mono" id="subject-char-count">0 chars</span>
                        </div>

                        <div>
                            <input type="text" name="subject" id="mail-subject" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm font-semibold text-gray-900 focus:ring-2 focus:ring-blue-500 focus:outline-none" placeholder="e.g. Welcome to {{company_name}} - Your {{product_name}} Onboarding Kit" required>
                        </div>

                        <!-- Merge Tag Insertion Toolbar -->
                        <div class="space-y-1.5 pt-1">
                            <div class="flex items-center justify-between">
                                <span class="text-[11px] font-bold text-gray-500 uppercase tracking-tight">Click Tag to Insert at Cursor:</span>
                                <span class="text-[10px] text-gray-400">Inserts into Subject or Message body</span>
                            </div>
                            <div class="flex flex-wrap gap-1.5">
                                <button type="button" class="merge-tag-chip px-2.5 py-1 bg-gray-100 hover:bg-blue-100 text-gray-700 hover:text-blue-800 rounded-lg text-xs font-mono font-medium border border-gray-200" data-tag="{{customer_name}}">+ {{customer_name}}</button>
                                <button type="button" class="merge-tag-chip px-2.5 py-1 bg-gray-100 hover:bg-blue-100 text-gray-700 hover:text-blue-800 rounded-lg text-xs font-mono font-medium border border-gray-200" data-tag="{{company_name}}">+ {{company_name}}</button>
                                <button type="button" class="merge-tag-chip px-2.5 py-1 bg-purple-50 hover:bg-purple-100 text-purple-700 rounded-lg text-xs font-mono font-bold border border-purple-200" data-tag="{{product_name}}">+ {{product_name}}</button>
                                <button type="button" class="merge-tag-chip px-2.5 py-1 bg-purple-50 hover:bg-purple-100 text-purple-700 rounded-lg text-xs font-mono font-bold border border-purple-200" data-tag="{{product_price}}">+ {{product_price}}</button>
                                <button type="button" class="merge-tag-chip px-2.5 py-1 bg-emerald-50 hover:bg-emerald-100 text-emerald-800 rounded-lg text-xs font-mono font-medium border border-emerald-200" data-tag="{{login_url}}">+ {{login_url}}</button>
                                <button type="button" class="merge-tag-chip px-2.5 py-1 bg-emerald-50 hover:bg-emerald-100 text-emerald-800 rounded-lg text-xs font-mono font-medium border border-emerald-200" data-tag="{{login_email}}">+ {{login_email}}</button>
                                <button type="button" class="merge-tag-chip px-2.5 py-1 bg-amber-50 hover:bg-amber-100 text-amber-800 rounded-lg text-xs font-mono font-medium border border-amber-200" data-tag="{{temporary_password}}">+ {{temporary_password}}</button>
                                <button type="button" class="merge-tag-chip px-2.5 py-1 bg-gray-100 hover:bg-blue-100 text-gray-700 hover:text-blue-800 rounded-lg text-xs font-mono font-medium border border-gray-200" data-tag="{{sender_name}}">+ {{sender_name}}</button>
                                <button type="button" class="merge-tag-chip px-2.5 py-1 bg-gray-100 hover:bg-blue-100 text-gray-700 hover:text-blue-800 rounded-lg text-xs font-mono font-medium border border-gray-200" data-tag="{{sender_phone}}">+ {{sender_phone}}</button>
                                <button type="button" class="merge-tag-chip px-2.5 py-1 bg-gray-100 hover:bg-blue-100 text-gray-700 hover:text-blue-800 rounded-lg text-xs font-mono font-medium border border-gray-200" data-tag="{{current_date}}">+ {{current_date}}</button>
                            </div>
                        </div>
                    </div>

                    <!-- 5. Summernote WYSIWYG Editor with Image Drag-and-Drop Card -->
                    <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-xs space-y-4">
                        <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                            <h3 class="text-xs font-bold text-gray-900 uppercase tracking-wide flex items-center gap-2">
                                <span class="w-5 h-5 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center text-[10px]">5</span>
                                Email Content (Summernote WYSIWYG & Image Drag & Drop)
                            </h3>
                            <span class="text-[11px] text-emerald-600 font-semibold flex items-center gap-1">
                                <i class="fa fa-picture-o"></i> Drag images directly into editor
                            </span>
                        </div>

                        <!-- Summernote Textarea -->
                        <div>
                            <textarea id="summernote-editor" name="message" class="hidden" required></textarea>
                        </div>
                    </div>

                    <!-- 6. Dispatch Action & Test Send Controls Card -->
                    <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-xs space-y-4">
                        <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                            <h3 class="text-xs font-bold text-gray-900 uppercase tracking-wide flex items-center gap-2">
                                <span class="w-5 h-5 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center text-[10px]">6</span>
                                Review & Campaign Dispatch
                            </h3>
                            <div class="flex items-center gap-2">
                                <label class="text-xs font-semibold text-gray-600 cursor-pointer flex items-center gap-1.5">
                                    <input type="radio" name="dispatch_mode" value="instant" checked class="text-blue-600 focus:ring-blue-500">
                                    <span>Instant Dispatch & Activity Log</span>
                                </label>
                                <label class="text-xs font-semibold text-gray-600 cursor-pointer flex items-center gap-1.5 ml-2">
                                    <input type="radio" name="dispatch_mode" value="queue" class="text-blue-600 focus:ring-blue-500">
                                    <span>Background Queue</span>
                                </label>
                            </div>
                        </div>

                        <!-- Campaign Outreach Purpose & Follow-Up Planner -->
                        <div class="p-3.5 bg-indigo-50/50 border border-indigo-200/80 rounded-xl space-y-3">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <div class="flex items-center justify-between mb-1">
                                        <label class="block text-xs font-bold text-gray-800 flex items-center gap-1.5">
                                            <i class="fa fa-tag text-indigo-600"></i> Outreach Purpose / Stage
                                        </label>
                                        <div class="flex items-center gap-1">
                                            <button type="button" id="btn-manage-stages" class="px-2 py-0.5 text-[11px] font-semibold text-indigo-700 bg-indigo-100 hover:bg-indigo-200 rounded border border-indigo-200 transition-colors flex items-center gap-1" title="Customize stages and days">
                                                <i class="fa fa-sliders text-[10px]"></i> Edit Options
                                            </button>
                                            <button type="button" id="btn-view-stage-logs" class="px-2 py-0.5 text-[11px] font-semibold text-gray-700 bg-white hover:bg-gray-100 rounded border border-gray-200 transition-colors flex items-center gap-1" title="Check Stage Logs">
                                                <i class="fa fa-list-alt text-[10px] text-gray-500"></i> Logs
                                            </button>
                                        </div>
                                    </div>
                                    <select name="campaign_type" id="select-campaign-type" class="w-full px-3 py-2 bg-white border border-indigo-200 rounded-lg text-xs font-semibold text-gray-800 focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                                        <?php if(!empty($outreach_stages)): foreach ($outreach_stages as $stg): ?>
                                            <option value="<?= esc_html($stg['id']) ?>" data-days="<?= (int)($stg['days'] ?? 3) ?>" <?= $stg['id'] === 'outreach' ? 'selected' : '' ?>>
                                                <?= esc_html($stg['name']) ?>
                                            </option>
                                        <?php endforeach; else: ?>
                                            <option value="outreach" selected>🚀 Initial Outreach (First Pitch)</option>
                                            <option value="followup_1">🔁 Follow-Up #1 (Gentle Reminder)</option>
                                            <option value="followup_2">⚡ Follow-Up #2 (Last Call & Offer)</option>
                                            <option value="retry">🛠️ Retry / Resend Failed Dispatches</option>
                                            <option value="announcement">📢 Announcement / Product Update</option>
                                        <?php endif; ?>
                                    </select>
                                </div>
                                <div>
                                    <div class="flex items-center justify-between mb-1">
                                        <label class="block text-xs font-bold text-gray-800 flex items-center gap-1.5">
                                            <i class="fa fa-calendar text-indigo-600"></i> Schedule Next Follow-Up
                                        </label>
                                        <span id="followup-preview-badge" class="text-[10px] font-mono font-semibold text-indigo-700 bg-indigo-100/90 px-2 py-0.5 rounded border border-indigo-200">
                                            Due: <?= date('d M Y', strtotime('+3 days')) ?>
                                        </span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <select name="followup_schedule" id="select-followup-schedule" class="w-full px-3 py-2 bg-white border border-indigo-200 rounded-lg text-xs font-semibold text-gray-800 focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                                            <option value="1">After 1 Day (Tomorrow)</option>
                                            <option value="2">After 2 Days</option>
                                            <option value="3" selected>After 3 Days (Standard)</option>
                                            <option value="4">After 4 Days</option>
                                            <option value="5">After 5 Days</option>
                                            <option value="7">After 1 Week (7 Days)</option>
                                            <option value="10">After 10 Days</option>
                                            <option value="14">After 2 Weeks (14 Days)</option>
                                            <option value="custom_days">Custom Number of Days...</option>
                                            <option value="custom">Custom Date...</option>
                                        </select>
                                        <div id="wrap-custom-followup-days" class="hidden flex-shrink-0 w-28">
                                            <input type="number" min="1" max="365" name="custom_followup_days" id="input-custom-followup-days" placeholder="Days e.g. 6" class="w-full px-2.5 py-1.5 bg-white border border-indigo-200 rounded-lg text-xs font-semibold text-gray-800 focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                                        </div>
                                        <div id="wrap-custom-followup-date" class="hidden flex-shrink-0 w-36">
                                            <input type="date" name="custom_followup_date" id="input-custom-followup-date" min="<?= date('Y-m-d') ?>" value="<?= date('Y-m-d', strtotime('+3 days')) ?>" class="w-full px-2.5 py-1.5 bg-white border border-indigo-200 rounded-lg text-xs font-semibold text-gray-800 focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Partition / Batch Count Dispatch Control -->
                            <div class="pt-2 border-t border-indigo-100 flex flex-wrap items-center justify-between gap-2">
                                <div class="flex items-center gap-3">
                                    <span class="text-[11px] font-bold text-gray-700 flex items-center gap-1">
                                        <i class="fa fa-pie-chart text-indigo-600"></i> Dispatch Partition:
                                    </span>
                                    <label class="text-xs font-medium text-gray-700 cursor-pointer flex items-center gap-1.5">
                                        <input type="radio" name="partition_mode" value="all" checked class="text-indigo-600 focus:ring-indigo-500">
                                        <span>Dispatch All (<strong id="part-total-badge" class="font-mono">0</strong>)</span>
                                    </label>
                                    <label class="text-xs font-medium text-gray-700 cursor-pointer flex items-center gap-1.5">
                                        <input type="radio" name="partition_mode" value="custom" class="text-indigo-600 focus:ring-indigo-500">
                                        <span>Custom Batch:</span>
                                    </label>
                                    <div id="wrap-partition-input" class="hidden items-center gap-1.5">
                                        <input type="number" min="1" name="partition_limit" id="input-partition-limit" placeholder="e.g. 50" class="w-20 px-2 py-1 bg-white border border-indigo-300 rounded text-xs font-mono font-bold text-gray-800 focus:ring-1 focus:ring-indigo-500">
                                        <span class="text-[10px] text-gray-500">now, rest stay in queue</span>
                                    </div>
                                </div>
                                <span id="part-feedback-badge" class="text-[10px] text-indigo-600 italic">Full batch will be dispatched</span>
                            </div>
                        </div>

                        <!-- Quick Test Send Drawer -->
                        <div class="p-3.5 bg-gray-50 border border-gray-200/80 rounded-xl space-y-2">
                            <span class="block text-xs font-bold text-gray-700">Send 1-Click Test Preview to Your Inbox:</span>
                            <div class="flex gap-2">
                                <input type="email" id="input-test-email" class="w-full px-3 py-2 bg-white border border-gray-200 rounded-lg text-xs focus:ring-2 focus:ring-blue-500 focus:outline-none" placeholder="Enter your email to receive sample rendered preview" value="<?= esc_html($current_user['email'] ?? '') ?>">
                                <button type="button" id="btn-send-test-email" class="px-4 py-2 bg-white hover:bg-gray-100 text-gray-700 border border-gray-300 rounded-lg text-xs font-bold whitespace-nowrap transition-colors shadow-2xs">
                                    <i class="fa fa-paper-plane-o"></i> Send Test
                                </button>
                            </div>
                        </div>

                        <!-- Main Submit Button -->
                        <div class="pt-2">
                            <button type="submit" id="btn-launch-campaign" class="w-full py-3.5 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-bold text-sm rounded-xl transition-all shadow-md flex items-center justify-center gap-2">
                                <i class="fa fa-paper-plane"></i> <span id="label-launch-btn">Launch Bulk Outreach Campaign Now</span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Right Panel: Live Email Client Preview (Mockup UI) (5 cols) -->
                <div class="lg:col-span-5 sticky top-6 space-y-4">
                    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 space-y-3">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-1.5">
                                <span class="w-3 h-3 rounded-full bg-rose-400 inline-block"></span>
                                <span class="w-3 h-3 rounded-full bg-amber-400 inline-block"></span>
                                <span class="w-3 h-3 rounded-full bg-emerald-400 inline-block"></span>
                                <span class="text-xs font-bold text-gray-800 ml-2">Live Email Client Preview</span>
                            </div>
                            
                            <!-- Device View Toggle -->
                            <div class="inline-flex p-0.5 bg-gray-100 rounded-lg text-xs">
                                <button type="button" class="preview-mode-btn active px-2.5 py-1 rounded-md text-[11px] font-bold text-gray-700" data-mode="desktop">
                                    <i class="fa fa-desktop mr-1"></i> Desktop
                                </button>
                                <button type="button" class="preview-mode-btn px-2.5 py-1 rounded-md text-[11px] font-bold text-gray-500" data-mode="mobile">
                                    <i class="fa fa-mobile mr-1"></i> Mobile
                                </button>
                            </div>
                        </div>

                        <!-- Email Header Mockup Strip -->
                        <div class="bg-gray-50 border border-gray-200/70 rounded-xl p-3 text-xs space-y-1.5 font-sans">
                            <div class="flex items-center text-gray-600">
                                <span class="w-16 font-semibold text-gray-400">From:</span>
                                <span class="font-medium text-gray-800 truncate" id="preview-header-from">Auto-Rotated Pool &lt;Smart Fair-Share Mailbox Pool&gt;</span>
                            </div>
                            <div class="flex items-center text-gray-600">
                                <span class="w-16 font-semibold text-gray-400">To:</span>
                                <span class="font-medium text-blue-600 truncate" id="preview-header-to">Selected Recipient &lt;client@example.com&gt;</span>
                            </div>
                            <div class="flex items-center text-gray-600">
                                <span class="w-16 font-semibold text-gray-400">Subject:</span>
                                <span class="font-bold text-gray-900 truncate" id="preview-header-subject">(No Subject)</span>
                            </div>
                        </div>

                        <!-- Live Rendered Body Container -->
                        <div id="email-preview-container" class="email-client-mockup border border-gray-200 rounded-xl p-4 bg-gray-100/50 min-h-[460px] max-h-[640px] overflow-y-auto">
                            <div id="email-preview-rendered" class="bg-white rounded-xl shadow-xs overflow-hidden">
                                <!-- Populated dynamically by JS -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL: MANAGE OUTREACH STAGES                                             -->
<!-- ========================================================================= -->
<div id="modal-manage-stages" class="fixed inset-0 z-50 hidden bg-black/50 backdrop-blur-xs flex items-center justify-center p-4 overflow-y-auto">
    <div class="bg-white rounded-2xl shadow-2xl max-w-xl w-full border border-gray-200 overflow-hidden animate-fade-in my-8">
        <div class="px-5 py-4 bg-gradient-to-r from-indigo-700 to-blue-700 text-white flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-white/10 flex items-center justify-center text-sm">
                    <i class="fa fa-sliders"></i>
                </div>
                <div>
                    <h3 class="text-sm font-bold">Manage Outreach Stages</h3>
                    <p class="text-[11px] text-indigo-100">Customize follow-up stages, labels, and default follow-up intervals</p>
                </div>
            </div>
            <button type="button" class="btn-close-stage-modal text-white/80 hover:text-white text-lg">
                <i class="fa fa-times"></i>
            </button>
        </div>

        <div class="p-5 space-y-4 max-h-[60vh] overflow-y-auto">
            <div class="p-3 bg-amber-50 border border-amber-200 rounded-xl text-xs text-amber-800 flex items-start gap-2">
                <i class="fa fa-lock text-amber-600 mt-0.5"></i>
                <div>
                    <strong>Intro is Locked:</strong> Initial Outreach is the permanent starting pitch. All follow-ups (Follow-Up #1, #2, etc.) can be renamed, re-scheduled, or added.
                </div>
            </div>

            <div id="stages-editor-list" class="space-y-2.5">
                <!-- Dynamically generated rows -->
            </div>

            <button type="button" id="btn-add-stage-row" class="w-full py-2.5 bg-indigo-50 hover:bg-indigo-100 border border-dashed border-indigo-300 text-indigo-700 text-xs font-bold rounded-xl transition-all flex items-center justify-center gap-1.5">
                <i class="fa fa-plus-circle"></i> Add New Follow-Up Stage
            </button>
        </div>

        <div class="px-5 py-3.5 bg-gray-50 border-t border-gray-100 flex items-center justify-between">
            <button type="button" class="btn-close-stage-modal px-4 py-2 bg-white hover:bg-gray-100 text-gray-700 border border-gray-200 rounded-xl text-xs font-bold transition-all">
                Cancel
            </button>
            <button type="button" id="btn-save-stages-submit" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold transition-all shadow-sm flex items-center gap-1.5">
                <i class="fa fa-check"></i> Save & Apply Changes
            </button>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL: STAGE LOGS & SCHEDULE TRACKER                                      -->
<!-- ========================================================================= -->
<div id="modal-stage-logs" class="fixed inset-0 z-50 hidden bg-black/50 backdrop-blur-xs flex items-center justify-center p-4 overflow-y-auto">
    <div class="bg-white rounded-2xl shadow-2xl max-w-4xl w-full border border-gray-200 max-h-[90vh] flex flex-col overflow-hidden animate-fade-in my-6">
        <div class="px-5 py-4 bg-gradient-to-r from-slate-900 to-indigo-950 text-white flex items-center justify-between flex-shrink-0">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-indigo-500/20 text-indigo-400 flex items-center justify-center text-sm">
                    <i class="fa fa-calendar-check-o"></i>
                </div>
                <div>
                    <h3 class="text-sm font-bold">Outreach Stage &amp; Scheduled Follow-Up Logs</h3>
                    <p class="text-[11px] text-gray-400">Live breakdown of leads by stage, sent timestamps, and upcoming due dates</p>
                </div>
            </div>
            <button type="button" class="btn-close-logs-modal text-white/70 hover:text-white text-lg">
                <i class="fa fa-times"></i>
            </button>
        </div>

        <div class="p-5 overflow-y-auto space-y-4 flex-1">
            <!-- Stage Summary Cards Grid -->
            <div id="stage-logs-cards-grid" class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                <div class="p-3 bg-gray-50 border border-gray-200 rounded-xl text-center text-xs text-gray-400">
                    Loading stats...
                </div>
            </div>

            <!-- Filter Strip -->
            <div class="flex items-center justify-between pt-2 border-t border-gray-100">
                <div class="flex items-center gap-2">
                    <span class="text-xs font-semibold text-gray-700">Filter by Stage:</span>
                    <select id="filter-logs-stage" class="px-2.5 py-1.5 bg-white border border-gray-200 rounded-lg text-xs font-semibold text-gray-800 focus:outline-none">
                        <option value="all">All Stages</option>
                    </select>
                </div>
                <button type="button" id="btn-refresh-stage-logs" class="px-3 py-1 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg text-xs font-semibold flex items-center gap-1">
                    <i class="fa fa-refresh"></i> Refresh
                </button>
            </div>

            <!-- Logs Table -->
            <div class="border border-gray-200 rounded-xl overflow-hidden shadow-2xs">
                <div class="overflow-x-auto max-h-[350px]">
                    <table class="w-full text-left text-xs text-gray-600">
                        <thead class="bg-gray-50 text-[11px] font-bold text-gray-700 uppercase border-b border-gray-200 sticky top-0">
                            <tr>
                                <th class="py-2.5 px-3">Recipient</th>
                                <th class="py-2.5 px-3">Stage</th>
                                <th class="py-2.5 px-3">Sent At</th>
                                <th class="py-2.5 px-3">Next Due Date</th>
                                <th class="py-2.5 px-3 text-right">Status</th>
                            </tr>
                        </thead>
                        <tbody id="stage-logs-table-body" class="divide-y divide-gray-100">
                            <tr>
                                <td colspan="5" class="py-6 text-center text-gray-400 text-xs">Loading logs...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="px-5 py-3 bg-gray-50 border-t border-gray-100 flex items-center justify-end flex-shrink-0">
            <button type="button" class="btn-close-logs-modal px-4 py-1.5 bg-white hover:bg-gray-100 text-gray-700 border border-gray-300 rounded-xl text-xs font-bold">
                Close
            </button>
        </div>
    </div>
</div>


<!-- Summernote Lite JS -->
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-lite.min.js"></script>
<!-- Bulk Mail Hub Logic -->
<script src="<?= base_url('assets/js/crm.communications.js?v=' . time()) ?>"></script>
