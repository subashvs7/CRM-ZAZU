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

        <!-- Quick Top Actions & Navigation Tabs -->
        <div class="flex items-center gap-2 flex-wrap">
            <div class="inline-flex p-1 bg-gray-100 rounded-xl text-xs font-semibold text-gray-600">
                <button type="button" class="tab-nav-btn active px-3.5 py-1.5 rounded-lg transition-all" data-tab="tab-compose">
                    <i class="fa fa-pencil-square-o mr-1"></i> Composer & Preview
                </button>
                <button type="button" class="tab-nav-btn px-3.5 py-1.5 rounded-lg transition-all" data-tab="tab-templates">
                    <i class="fa fa-folder-open-o mr-1"></i> Templates Library (<span id="badge-template-count"><?= count($templates) ?></span>)
                </button>
                <button type="button" class="tab-nav-btn px-3.5 py-1.5 rounded-lg transition-all" data-tab="tab-history">
                    <i class="fa fa-history mr-1"></i> History & Logs
                </button>
            </div>
            <button type="button" id="btn-open-create-template" class="px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl transition-all shadow-sm flex items-center gap-1.5">
                <i class="fa fa-plus-circle"></i> Add Template
            </button>
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
                <a href="<?= base_url('admin/settings#tab-smtp') ?>" class="px-3 py-1.5 bg-white/10 hover:bg-white/20 text-white text-xs font-bold rounded-xl border border-white/20 transition-all flex items-center gap-1.5 flex-shrink-0">
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

                        <!-- Granular Filters for Leads & Customers -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-1">
                            <div id="wrapper-lead-status">
                                <label class="block text-xs font-semibold text-gray-700 mb-1.5">Lead Stage Filter</label>
                                <select name="lead_status" id="select-lead-status" class="w-full px-3 py-2 bg-gray-50/70 border border-gray-200 rounded-xl text-xs font-medium text-gray-800 focus:bg-white focus:ring-2 focus:ring-blue-500 focus:outline-none">
                                    <option value="all" selected>All Stages (Any Status)</option>
                                    <option value="new">New Inquiries</option>
                                    <option value="contacted">Contacted</option>
                                    <option value="qualified">Qualified</option>
                                    <option value="proposal">Proposal / Quotation Sent</option>
                                    <option value="negotiation">Negotiation</option>
                                    <option value="won">Closed / Won</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1.5">Recipient Deduplication Engine</label>
                                <div class="px-3 py-2 bg-emerald-50/60 border border-emerald-200 rounded-xl text-xs text-emerald-800 flex items-center gap-2">
                                    <i class="fa fa-check-circle text-emerald-600"></i>
                                    <span>Emails deduplicated across selected audiences</span>
                                </div>
                            </div>
                        </div>

                        <!-- Audience Preview Trigger -->
                        <div class="pt-1 flex items-center justify-between text-xs text-gray-500 border-t border-gray-100">
                            <span class="text-[11px] text-gray-400"><i class="fa fa-shield text-blue-500 mr-1"></i> Invalid or duplicate emails are safely filtered out.</span>
                            <button type="button" id="btn-toggle-audience-drawer" class="text-xs font-bold text-blue-600 hover:text-blue-800 flex items-center gap-1">
                                <i class="fa fa-list"></i> Inspect Recipients List
                            </button>
                        </div>

                        <!-- Dropdown Audience Preview Table Drawer -->
                        <div id="audience-preview-drawer" class="hidden mt-2 p-3 bg-gray-50 border border-gray-200 rounded-xl text-xs space-y-2 max-h-56 overflow-y-auto">
                            <div class="font-bold text-gray-700 text-[11px] uppercase tracking-wide flex justify-between">
                                <span>Previewing First 15 Verified Recipients:</span>
                                <span id="audience-preview-total" class="text-blue-600">0 found</span>
                            </div>
                            <div id="audience-preview-list" class="divide-y divide-gray-200/70">
                                <!-- Loaded dynamically via AJAX -->
                            </div>
                        </div>
                    </div>

                    <!-- 2. Dynamic Product & Template Selector Card -->
                    <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-xs space-y-4">
                        <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                            <h3 class="text-xs font-bold text-gray-900 uppercase tracking-wide flex items-center gap-2">
                                <span class="w-5 h-5 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center text-[10px]">2</span>
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

                    <!-- 3. Subject & Dynamic Merge Tags Toolbar -->
                    <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-xs space-y-4">
                        <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                            <h3 class="text-xs font-bold text-gray-900 uppercase tracking-wide flex items-center gap-2">
                                <span class="w-5 h-5 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center text-[10px]">3</span>
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

                    <!-- 4. Summernote WYSIWYG Editor with Image Drag-and-Drop Card -->
                    <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-xs space-y-4">
                        <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                            <h3 class="text-xs font-bold text-gray-900 uppercase tracking-wide flex items-center gap-2">
                                <span class="w-5 h-5 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center text-[10px]">4</span>
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

                    <!-- 5. Dispatch Action & Test Send Controls Card -->
                    <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-xs space-y-4">
                        <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                            <h3 class="text-xs font-bold text-gray-900 uppercase tracking-wide flex items-center gap-2">
                                <span class="w-5 h-5 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center text-[10px]">5</span>
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
                                <span class="font-medium text-gray-800 truncate">CRM-ZAZU Outreach &lt;outreach@crm-zazu.local&gt;</span>
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

    <!-- ========================================================================= -->
    <!-- TAB 2: TEMPLATES LIBRARY (GRID VIEW)                                      -->
    <!-- ========================================================================= -->
    <div id="tab-templates" class="tab-pane hidden space-y-5">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-white p-4 rounded-2xl border border-gray-100 shadow-xs">
            <div class="flex items-center gap-3">
                <span class="text-xs font-bold text-gray-700">Filter Templates:</span>
                <select id="grid-filter-product" class="px-3 py-1.5 bg-gray-50 border border-gray-200 rounded-lg text-xs font-medium focus:outline-none">
                    <option value="">All Products</option>
                    <?php foreach ($products as $p): ?>
                    <option value="<?= $p['id'] ?>"><?= esc_html($p['name']) ?></option>
                    <?php endforeach; ?>
                </select>
                <select id="grid-filter-category" class="px-3 py-1.5 bg-gray-50 border border-gray-200 rounded-lg text-xs font-medium focus:outline-none">
                    <option value="all">All Categories</option>
                    <option value="onboarding">Client Onboarding</option>
                    <option value="thank_you">Thank You & Appreciation</option>
                    <option value="login">Login Credentials & Access</option>
                    <option value="demo">Product Demo</option>
                    <option value="proposal">Proposal / Quotation</option>
                    <option value="followup">Follow-up & Pitch</option>
                </select>
            </div>
            <button type="button" class="btn-trigger-add-template px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl transition-all shadow-xs flex items-center gap-1.5">
                <i class="fa fa-plus-circle"></i> Create New Template
            </button>
        </div>

        <!-- Template Cards Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5" id="template-cards-grid">
            <?php foreach ($templates as $t): 
                $catColor = 'bg-blue-50 text-blue-700 border-blue-200';
                if ($t['category'] === 'onboarding') $catColor = 'bg-sky-50 text-sky-700 border-sky-200';
                elseif ($t['category'] === 'thank_you') $catColor = 'bg-emerald-50 text-emerald-700 border-emerald-200';
                elseif ($t['category'] === 'login') $catColor = 'bg-slate-100 text-slate-800 border-slate-300';
                elseif ($t['category'] === 'demo') $catColor = 'bg-purple-50 text-purple-700 border-purple-200';
                elseif ($t['category'] === 'proposal') $catColor = 'bg-amber-50 text-amber-700 border-amber-200';
            ?>
            <div class="template-card bg-white border border-gray-200/80 hover:border-blue-400 rounded-2xl p-5 shadow-xs hover:shadow-md transition-all flex flex-col justify-between"
                 data-product="<?= $t['product_id'] ?>" data-cat="<?= $t['category'] ?>" data-id="<?= $t['id'] ?>">
                <div class="space-y-3">
                    <div class="flex items-start justify-between gap-2">
                        <span class="px-2.5 py-0.5 text-[10px] font-bold rounded-full border <?= $catColor ?> uppercase tracking-wider">
                            <?= esc_html(str_replace('_', ' ', $t['category'])) ?>
                        </span>
                        <?php if ($t['product_name']): ?>
                        <span class="px-2 py-0.5 text-[10px] font-bold rounded-md bg-purple-50 text-purple-700 border border-purple-200 font-mono">
                            <?= esc_html($t['product_name']) ?>
                        </span>
                        <?php endif; ?>
                    </div>

                    <div>
                        <h4 class="text-sm font-bold text-gray-900 group-hover:text-blue-600 transition-colors"><?= esc_html($t['name']) ?></h4>
                        <p class="text-xs text-gray-500 font-mono mt-1 truncate" title="<?= esc_html($t['subject']) ?>">
                            <i class="fa fa-envelope-o text-gray-400 mr-1"></i> <?= esc_html($t['subject']) ?>
                        </p>
                    </div>

                    <div class="bg-gray-50 rounded-xl p-3 text-[11px] text-gray-600 max-h-24 overflow-hidden relative">
                        <?= strip_tags($t['body']) ?>
                        <div class="absolute inset-x-0 bottom-0 h-6 bg-gradient-to-t from-gray-50 to-transparent"></div>
                    </div>
                </div>

                <div class="pt-4 mt-4 border-t border-gray-100 flex items-center justify-between">
                    <button type="button" class="btn-use-template px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-xs font-bold transition-colors shadow-2xs" data-id="<?= $t['id'] ?>">
                        <i class="fa fa-check mr-1"></i> Use Template
                    </button>
                    <div class="flex items-center gap-1.5">
                        <button type="button" class="btn-edit-template p-1.5 text-gray-400 hover:text-blue-600 rounded-lg hover:bg-gray-100 transition-colors" title="Edit Template" data-id="<?= $t['id'] ?>">
                            <i class="fa fa-pencil"></i>
                        </button>
                        <button type="button" class="btn-delete-template p-1.5 text-gray-400 hover:text-rose-600 rounded-lg hover:bg-gray-100 transition-colors" title="Delete Template" data-id="<?= $t['id'] ?>">
                            <i class="fa fa-trash-o"></i>
                        </button>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- TAB 3: DISPATCH HISTORY & CAMPAIGN LOGS                                   -->
    <!-- ========================================================================= -->
    <div id="tab-history" class="tab-pane hidden space-y-5">
        <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-xs space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-gray-100">
                <div>
                    <h3 class="text-sm font-bold text-gray-900">Campaign Dispatch History & Follow-up Logs</h3>
                    <p class="text-xs text-gray-500">Track delivery status, view exact emails sent, and audit lead follow-up logs.</p>
                </div>
                <button type="button" id="btn-refresh-history" class="px-3 py-1.5 bg-gray-50 hover:bg-gray-100 text-gray-700 border border-gray-200 rounded-lg text-xs font-semibold flex items-center gap-1.5">
                    <i class="fa fa-refresh"></i> Refresh Log
                </button>
            </div>

            <!-- Campaign Table -->
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse" id="history-datatable">
                    <thead>
                        <tr class="bg-gray-50/80 text-gray-500 uppercase tracking-wider font-semibold border-b border-gray-200">
                            <th class="py-3 px-3">CAMPAIGN ID</th>
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
                        <tr class="hover:bg-gray-50/60 transition-colors">
                            <td class="py-3 px-3 font-mono font-bold text-gray-800">#<?= $camp['id'] ?></td>
                            <td class="py-3 px-3">
                                <span class="font-bold text-gray-900 block"><?= esc_html($camp['subject']) ?></span>
                                <span class="text-[11px] text-gray-400"><?= esc_html($camp['template_name'] ?: 'Custom Compose') ?></span>
                            </td>
                            <td class="py-3 px-3">
                                <span class="font-medium text-gray-800 block"><?= esc_html($camp['recipient_type']) ?></span>
                                <?php if($camp['product_name']): ?>
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
                                <button type="button" class="btn-view-campaign-details px-2.5 py-1 text-xs font-semibold bg-gray-100 hover:bg-blue-50 text-gray-700 hover:text-blue-700 rounded-lg transition-colors border border-gray-200" data-id="<?= $camp['id'] ?>">
                                    <i class="fa fa-eye mr-1"></i> Details
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; else: ?>
                        <tr>
                            <td colspan="9" class="py-8 text-center text-gray-400 text-xs">
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
</div>

<!-- ========================================================================= -->
<!-- MODAL: ADD / EDIT EMAIL TEMPLATE                                          -->
<!-- ========================================================================= -->
<div id="modal-template-editor" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-xs p-4 overflow-y-auto">
    <div class="bg-white rounded-2xl shadow-2xl border border-gray-100 w-full max-w-3xl overflow-hidden animate-in fade-in zoom-in-95 duration-200">
        <div class="px-6 py-4 bg-gradient-to-r from-blue-600 to-indigo-600 text-white flex items-center justify-between">
            <div class="flex items-center gap-2">
                <i class="fa fa-pencil-square-o text-lg"></i>
                <h3 class="text-sm font-bold" id="modal-template-title">Create New Email Template</h3>
            </div>
            <button type="button" id="btn-close-template-modal" class="text-white/80 hover:text-white text-lg">
                <i class="fa fa-times"></i>
            </button>
        </div>

        <form id="form-save-template" class="p-6 space-y-4">
            <input type="hidden" name="id" id="tpl-edit-id" value="">

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="sm:col-span-2">
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Template Name <span class="text-rose-500">*</span></label>
                    <input type="text" name="name" id="tpl-input-name" class="w-full px-3 py-2 border border-gray-200 rounded-xl text-xs focus:ring-2 focus:ring-blue-500 focus:outline-none" placeholder="e.g. VIP Client Onboarding Welcome" required>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Category <span class="text-rose-500">*</span></label>
                    <select name="category" id="tpl-input-category" class="w-full px-3 py-2 border border-gray-200 rounded-xl text-xs focus:ring-2 focus:ring-blue-500 focus:outline-none" required>
                        <option value="onboarding">Client Onboarding</option>
                        <option value="thank_you">Thank You & Appreciation</option>
                        <option value="login">Login Credentials & Access</option>
                        <option value="demo">Product Demo & Walkthrough</option>
                        <option value="proposal">Proposal & Quotation</option>
                        <option value="followup">Follow-up & Pitch</option>
                        <option value="general">General</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Related Product</label>
                    <select name="product_id" id="tpl-input-product" class="w-full px-3 py-2 border border-gray-200 rounded-xl text-xs focus:ring-2 focus:ring-blue-500 focus:outline-none">
                        <option value="">General (All Products)</option>
                        <?php foreach ($products as $p): ?>
                        <option value="<?= $p['id'] ?>"><?= esc_html($p['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Default Subject Line <span class="text-rose-500">*</span></label>
                    <input type="text" name="subject" id="tpl-input-subject" class="w-full px-3 py-2 border border-gray-200 rounded-xl text-xs focus:ring-2 focus:ring-blue-500 focus:outline-none" placeholder="e.g. Welcome to {{company_name}} - {{product_name}}" required>
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">Template Content (WYSIWYG & Image Drag & Drop) <span class="text-rose-500">*</span></label>
                <textarea id="modal-summernote" name="body"></textarea>
            </div>

            <div class="flex items-center justify-between pt-4 border-t border-gray-100">
                <button type="button" id="btn-cancel-template-modal" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl text-xs font-semibold transition-colors">
                    Cancel
                </button>
                <button type="submit" id="btn-submit-template-save" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold shadow-sm transition-all flex items-center gap-1.5">
                    <i class="fa fa-check"></i> Save Template
                </button>
            </div>
        </form>
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
            <button type="button" id="btn-close-campaign-modal" class="text-white/80 hover:text-white text-lg">
                <i class="fa fa-times"></i>
            </button>
        </div>

        <div class="p-6 overflow-y-auto space-y-5 flex-grow">
            <!-- Summary Header Box -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs bg-gray-50 p-4 rounded-xl border border-gray-200">
                <div>
                    <span class="text-gray-400 block text-[10px] font-bold uppercase">Audience Group</span>
                    <span id="detail-recipient-type" class="font-bold text-gray-800">-</span>
                </div>
                <div>
                    <span class="text-gray-400 block text-[10px] font-bold uppercase">Target Product</span>
                    <span id="detail-product-name" class="font-bold text-purple-700">-</span>
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
            <button type="button" id="btn-close-campaign-modal-bottom" class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 rounded-xl text-xs font-bold transition-colors">
                Close
            </button>
        </div>
    </div>
</div>

<!-- Summernote Lite JS -->
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-lite.min.js"></script>
<!-- Bulk Mail Hub Logic -->
<script src="<?= base_url('assets/js/crm.communications.js?v=' . time()) ?>"></script>
