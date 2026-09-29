<!-- Page Header -->
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
    <div class="flex items-center gap-3">
        <div class="w-10 h-10 bg-emerald-100 rounded-xl flex items-center justify-center flex-shrink-0">
            <i class="fa fa-filter text-emerald-600 text-lg"></i>
        </div>
        <div>
            <h1 class="text-xl font-bold text-gray-800">Leads</h1>
            <nav class="text-xs text-gray-400 flex items-center gap-1 mt-0.5">
                <a href="<?= base_url('dashboard') ?>" class="hover:text-blue-600 transition-colors">Home</a>
                <i class="fa fa-angle-right text-[10px]"></i>
                <span class="text-gray-600">Leads</span>
            </nav>
        </div>
    </div>
    <div class="flex flex-wrap items-center gap-2 self-start sm:self-auto">

        <!-- Export Dropdown (Custom Tailwind interactive menu) -->
        <div class="relative inline-block text-left" id="export-dropdown-wrapper">
            <button type="button" id="btn-export-dropdown"
                    class="inline-flex items-center gap-1.5 px-3 py-2 text-sm bg-white border border-gray-200 text-gray-700 font-medium rounded-xl hover:bg-gray-50 transition-colors shadow-sm focus:outline-none">
                <i class="fa fa-download text-emerald-600"></i> Export <i class="fa fa-angle-down text-xs ml-0.5 text-gray-500"></i>
            </button>
            <div id="export-dropdown-menu"
                 class="hidden absolute right-0 mt-2 w-56 bg-white rounded-2xl shadow-xl border border-gray-100 py-1.5 z-50 transition-all duration-150">
                <a class="px-4 py-2.5 hover:bg-emerald-50/60 text-gray-700 hover:text-emerald-800 flex items-center gap-2.5 text-xs font-semibold transition-colors"
                   href="<?= base_url('leads/export?format=xlsx') ?>">
                    <i class="fa fa-file-excel-o text-emerald-600 text-sm"></i> Export to Excel (.xlsx)
                </a>
                <a class="px-4 py-2.5 hover:bg-blue-50/60 text-gray-700 hover:text-blue-800 flex items-center gap-2.5 text-xs font-semibold transition-colors"
                   href="<?= base_url('leads/export?format=csv') ?>">
                    <i class="fa fa-file-text-o text-blue-600 text-sm"></i> Export to CSV (.csv)
                </a>
            </div>
        </div>

        <!-- Import Button triggers Right-Side Drawer -->
        <button type="button" id="btn-open-import"
           class="inline-flex items-center gap-1.5 px-3.5 py-2 text-sm bg-white border border-emerald-300 text-emerald-700 font-semibold rounded-xl hover:bg-emerald-50 transition-colors shadow-sm">
            <i class="fa fa-upload text-emerald-600"></i> Import
        </button>
        <button id="btn-add-lead"
                class="inline-flex items-center gap-1.5 px-4 py-2 text-sm bg-emerald-600 text-white font-semibold rounded-xl hover:bg-emerald-700 transition-colors shadow-sm">
            <i class="fa fa-plus"></i> Add Lead
        </button>
    </div>
</div>

<!-- ========================================================================= -->
<!-- ========================================================================= -->
<!-- 1. PRODUCT LISTS & AUDIENCES HUB (MAIN VIEW)                              -->
<!-- ========================================================================= -->
<div id="view-products-hub" class="space-y-4">
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <!-- Header Toolbar -->
        <div class="px-6 py-4 border-b border-gray-100 flex flex-col md:flex-row md:items-center justify-between gap-3 bg-gradient-to-r from-gray-50/60 via-white to-amber-50/30">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center font-bold text-lg flex-shrink-0 shadow-2xs border border-amber-200">
                    <i class="fa fa-cubes"></i>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h3 class="text-sm font-bold text-gray-800">Ads Leads by Product</h3>
                        <span id="product-lists-total-badge" class="px-2 py-0.5 text-[11px] font-bold bg-amber-100 text-amber-800 rounded-full font-mono"><?= !empty($lists_summary) ? count($lists_summary) : 0 ?> Products</span>
                    </div>
                    <p class="text-xs text-gray-400 mt-0.5">Click any Product Name to open customers & leads inside that product</p>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-2.5">
                <!-- Search Lists Input -->
                <div class="relative w-48 sm:w-60">
                    <i class="fa fa-search absolute left-3 top-2.5 text-gray-400 text-xs"></i>
                    <input type="text" id="product-list-search-input" placeholder="Search products..." class="w-full pl-8 pr-3 py-1.5 text-xs bg-gray-50 hover:bg-white focus:bg-white border border-gray-200 rounded-xl text-gray-700 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-emerald-500 transition-all">
                </div>

                <!-- Filter Toggle -->
                <button type="button" id="btn-toggle-list-filters" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-gray-700 bg-white hover:bg-gray-50 border border-gray-200 rounded-xl shadow-2xs transition-colors cursor-pointer">
                    <i class="fa fa-filter text-gray-500"></i> Filters
                </button>
            </div>
        </div>

        <!-- Quick filter bar (collapsible) -->
        <div id="list-quick-filter-bar" class="hidden px-6 py-2.5 bg-gray-50/70 border-b border-gray-100 flex flex-wrap items-center gap-2 text-xs">
            <span class="text-gray-400 font-semibold text-[11px] uppercase mr-1">Filter by:</span>
            <button type="button" class="btn-list-filter-pill active px-3 py-1 rounded-lg font-semibold text-xs bg-amber-500 text-white shadow-2xs cursor-pointer" data-filter="all">All Products</button>
            <button type="button" class="btn-list-filter-pill px-3 py-1 rounded-lg font-semibold text-xs bg-white text-gray-600 border border-gray-200 hover:bg-gray-50 cursor-pointer" data-filter="has_customers">With Customers</button>
            <button type="button" class="btn-list-filter-pill px-3 py-1 rounded-lg font-semibold text-xs bg-white text-gray-600 border border-gray-200 hover:bg-gray-50 cursor-pointer" data-filter="has_leads">With Leads</button>
        </div>

        <!-- Lists Table -->
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs whitespace-nowrap" id="product-lists-summary-table">
                <thead class="text-[11px] uppercase tracking-wider text-gray-500 bg-gray-50/80 border-b border-gray-100">
                    <tr>
                        <th class="py-3 px-4 font-bold text-gray-600">PRODUCT NAME</th>
                        <th class="py-3 px-4 font-bold text-gray-600"># OF RECORDS</th>
                        <th class="py-3 px-4 font-bold text-gray-600">CUSTOMER COUNT (INSIDE)</th>
                        <th class="py-3 px-4 font-bold text-gray-600">LEADS COUNT (INSIDE)</th>
                        <th class="py-3 px-4 font-bold text-gray-600">CREATED BY</th>
                        <th class="py-3 px-4 font-bold text-gray-600">LAST MODIFIED</th>
                        <th class="py-3 px-4 font-bold text-gray-600 text-right">ACTIONS</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100" id="product-lists-summary-tbody">
                    <?php if (!empty($lists_summary) && is_array($lists_summary)): ?>
                        <?php foreach ($lists_summary as $l): ?>
                            <?php if (!is_array($l)) continue; ?>
                            <tr class="hover:bg-amber-50/40 transition-colors product-list-card-row cursor-pointer"
                                data-id="<?= $l['id'] ?>"
                                data-name="<?= htmlspecialchars($l['name'] ?? '') ?>"
                                data-leads="<?= $l['leads_count'] ?? 0 ?>"
                                data-customers="<?= $l['customers_count'] ?? 0 ?>">
                                <td class="py-3.5 px-4">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-700 flex items-center justify-center font-bold text-xs border border-emerald-200 flex-shrink-0">
                                            <i class="fa fa-cube"></i>
                                        </div>
                                        <div>
                                            <button type="button" class="btn-filter-leads-by-product text-left font-bold text-gray-900 hover:text-emerald-700 text-sm tracking-tight cursor-pointer uppercase" data-id="<?= $l['id'] ?>" data-name="<?= htmlspecialchars($l['name'] ?? '') ?>" data-leads="<?= $l['leads_count'] ?? 0 ?>" data-customers="<?= $l['customers_count'] ?? 0 ?>">
                                                <?= htmlspecialchars($l['name'] ?? '') ?>
                                            </button>
                                            <?php if (!empty($l['sku']) || !empty($l['category_name'])): ?>
                                                <span class="block text-[11px] text-gray-400 font-normal">
                                                    <?= !empty($l['sku']) ? htmlspecialchars($l['sku']) : '' ?>
                                                    <?= (!empty($l['sku']) && !empty($l['category_name'])) ? ' • ' : '' ?>
                                                    <?= htmlspecialchars($l['category_name'] ?? '') ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3.5 px-4">
                                    <span class="font-mono font-bold text-gray-900 text-sm"><?= (int)($l['total_records'] ?? 0) ?></span>
                                    <span class="text-[11px] text-gray-400 font-medium ml-1">Records</span>
                                </td>
                                <td class="py-3.5 px-4">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                        <i class="fa fa-users text-blue-600"></i>
                                        <span><?= (int)($l['customers_count'] ?? 0) ?></span> Customers
                                    </span>
                                </td>
                                <td class="py-3.5 px-4">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        <i class="fa fa-filter text-emerald-600"></i>
                                        <span><?= (int)($l['leads_count'] ?? 0) ?></span> Leads
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-gray-600 text-xs">
                                    <i class="fa fa-user-circle-o text-gray-400 mr-1"></i> <?= htmlspecialchars($l['created_by'] ?? 'System') ?>
                                </td>
                                <td class="py-3.5 px-4 text-gray-500 text-xs">
                                    <i class="fa fa-clock-o text-gray-400 mr-1"></i> <?= htmlspecialchars($l['last_modified'] ?? 'Recently') ?>
                                </td>
                                <td class="py-3.5 px-4 text-right">
                                    <div class="inline-flex items-center gap-1.5">
                                        <button type="button" class="btn-filter-leads-by-product px-3 py-1.5 text-xs font-bold bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl transition-colors shadow-2xs flex items-center gap-1 cursor-pointer" data-id="<?= $l['id'] ?>" data-name="<?= htmlspecialchars($l['name'] ?? '') ?>" data-leads="<?= $l['leads_count'] ?? 0 ?>" data-customers="<?= $l['customers_count'] ?? 0 ?>">
                                            <i class="fa fa-folder-open-o"></i> Open Leads
                                        </button>
                                        <button type="button" class="btn-upload-to-product-list p-1.5 text-gray-400 hover:text-emerald-700 rounded-lg hover:bg-gray-100 transition-colors cursor-pointer" data-id="<?= $l['id'] ?>" title="Upload Excel to this Product">
                                            <i class="fa fa-upload"></i>
                                        </button>
                                        <a href="<?= base_url('leads/export?format=xlsx&product_id='.$l['id']) ?>" class="p-1.5 text-gray-400 hover:text-blue-700 rounded-lg hover:bg-gray-100 transition-colors" title="Export this product list">
                                            <i class="fa fa-download"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="py-6 text-center text-gray-400">
                                No products found in CRM database.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- 2. PRODUCT DRILL-DOWN: LEADS & CUSTOMERS INSIDE (OPENS ON CLICK)          -->
<!-- ========================================================================= -->
<div id="view-leads-database" class="hidden space-y-4">
    <!-- Active Product Drill-down Header Card -->
    <div class="p-4 bg-white rounded-2xl shadow-sm border border-gray-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div class="flex items-center gap-3">
            <button type="button" id="btn-back-to-products" class="px-3.5 py-2 bg-emerald-50 hover:bg-emerald-100 text-emerald-800 text-xs font-bold rounded-xl border border-emerald-300 transition-all shadow-2xs flex items-center gap-2 cursor-pointer">
                <i class="fa fa-arrow-left text-emerald-600"></i> Back to Product Lists
            </button>
            <div class="border-l border-gray-200 pl-3">
                <div class="flex items-center gap-2">
                    <span class="text-xs text-gray-400 font-semibold uppercase">Product:</span>
                    <h2 id="active-product-title" class="text-base font-black text-gray-900 uppercase">PROMAN</h2>
                    <span id="active-product-badge" class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-50 text-blue-700 border border-blue-200">16 Leads • 1 Customer</span>
                </div>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <button type="button" class="btn-open-format-notes inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold text-emerald-700 bg-emerald-50 hover:bg-emerald-100 rounded-xl transition-colors border border-emerald-200 shadow-2xs cursor-pointer">
                <i class="fa fa-dollar text-emerald-600"></i> Format Notes
            </button>
            <a href="<?= base_url('leads/sample_template?format=xlsx') ?>" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold text-emerald-700 bg-white hover:bg-emerald-50 rounded-xl transition-colors border border-emerald-300 shadow-2xs">
                <i class="fa fa-file-excel-o text-emerald-600"></i> Sample Excel
            </a>
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-white text-gray-700 text-xs font-bold rounded-xl border border-gray-200">
                <i class="fa fa-columns text-gray-500"></i> 33 Columns
            </span>
        </div>
    </div>

    <!-- Status Tabs -->
    <?php $this->load->view('partials/_status_tabs', get_defined_vars()); ?>

    <!-- Leads Table Card -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <!-- Floating/Sliding Multi-Select Bulk Actions Bar -->
        <div id="leads-bulk-bar" class="hidden px-5 py-3.5 bg-gradient-to-r from-rose-50 via-amber-50 to-rose-50 border-b border-rose-200 flex flex-wrap items-center justify-between gap-3 animate-in fade-in duration-200">
            <div class="flex items-center gap-3">
                <span class="w-8 h-8 rounded-xl bg-rose-600 text-white flex items-center justify-center font-bold text-xs shadow-xs">
                    <i class="fa fa-check-square-o text-sm"></i>
                </span>
                <div>
                    <span class="text-xs font-bold text-rose-900"><span class="selected-count-pill font-mono font-black text-sm">0</span> Lead(s) Selected</span>
                    <p class="text-[11px] text-rose-700">Choose a bulk action to apply to all selected records:</p>
                </div>
            </div>
            <div class="flex items-center gap-2 flex-wrap">
                <!-- Delete Selected (Default for All, Active, Inactive tabs) -->
                <button type="button" id="btn-banner-bulk-delete" class="px-3.5 py-2 bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold rounded-xl shadow-xs transition-all flex items-center gap-1.5 cursor-pointer">
                    <i class="fa fa-trash"></i> Delete Selected (<span class="selected-count-pill font-mono">0</span>)
                </button>

                <!-- Deleted Tab Actions (Visible only when in Deleted Tab) -->
                <button type="button" id="btn-banner-bulk-restore" class="hidden px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-xs transition-all flex items-center gap-1.5 cursor-pointer">
                    <i class="fa fa-undo"></i> Restore Selected (<span class="selected-count-pill font-mono">0</span>)
                </button>
                <button type="button" id="btn-banner-bulk-permanent-delete" class="hidden px-3.5 py-2 bg-red-800 hover:bg-red-900 text-white text-xs font-bold rounded-xl shadow-xs transition-all flex items-center gap-1.5 cursor-pointer">
                    <i class="fa fa-trash-o"></i> Permanent Delete
                </button>

                <button type="button" id="btn-banner-bulk-clear" class="px-3 py-2 bg-white border border-gray-200 hover:bg-gray-100 text-gray-700 text-xs font-semibold rounded-xl shadow-2xs transition-all flex items-center gap-1 cursor-pointer">
                    <i class="fa fa-times text-gray-400"></i> Clear
                </button>
            </div>
        </div>

        <!-- Card Top Bar: Quick Selectors & Delete Selected in red box area -->
        <div class="px-5 pt-3.5 pb-2.5 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-gray-50/60">
            <div class="flex items-center gap-2.5">
                <span class="text-xs font-bold text-gray-700 uppercase tracking-wider">Leads Records</span>
                <span id="badge-selection-indicator" class="hidden px-2.5 py-0.5 text-[11px] font-bold bg-rose-100 text-rose-800 border border-rose-200 rounded-full font-mono">
                    <span class="selected-count-pill font-bold">0</span> Selected
                </span>
            </div>

            <!-- Red Box Positioned Action Controls -->
            <div class="flex items-center gap-2 flex-wrap">
                <!-- Batch Quick Pickers -->
                <div class="inline-flex rounded-xl shadow-2xs border border-gray-200 bg-white p-0.5 text-xs font-semibold text-gray-700">
                    <button type="button" id="btn-select-page" class="px-2.5 py-1 rounded-lg hover:bg-gray-100 transition cursor-pointer" title="Select all on this page">Select Page</button>
                    <button type="button" id="btn-select-25" class="px-2.5 py-1 rounded-lg hover:bg-gray-100 transition cursor-pointer" title="Select first 25">25</button>
                    <button type="button" id="btn-select-50" class="px-2.5 py-1 rounded-lg hover:bg-gray-100 transition cursor-pointer" title="Select first 50">50</button>
                    <button type="button" id="btn-select-none" class="px-2.5 py-1 rounded-lg hover:bg-gray-100 text-gray-500 hover:text-gray-800 transition cursor-pointer" title="Deselect all">Clear</button>
                </div>

                <!-- Primary Top Delete Button (Turns vibrant red when >=1 selected) -->
                <button type="button" id="btn-top-bulk-delete" disabled class="px-3.5 py-1.5 bg-gray-100 text-gray-400 border border-gray-200 text-xs font-bold rounded-xl transition-all shadow-xs flex items-center gap-1.5 cursor-not-allowed">
                    <i class="fa fa-trash"></i> <span>Delete Selected</span> <span class="selected-count-container hidden font-mono">(<span class="selected-count-pill">0</span>)</span>
                </button>

                <!-- Deleted Tab Actions for Top Bar -->
                <button type="button" id="btn-top-bulk-restore" class="hidden px-3.5 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl transition-all shadow-xs flex items-center gap-1.5 cursor-pointer">
                    <i class="fa fa-undo"></i> Restore (<span class="selected-count-pill font-mono">0</span>)
                </button>
                <button type="button" id="btn-top-bulk-permanent-delete" class="hidden px-3.5 py-1.5 bg-red-800 hover:bg-red-900 text-white text-xs font-bold rounded-xl transition-all shadow-xs flex items-center gap-1.5 cursor-pointer">
                    <i class="fa fa-trash-o"></i> Permanent Delete
                </button>
            </div>
        </div>

        <div class="p-4 overflow-x-auto">
            <table id="leads-table" class="w-full text-left min-w-[1260px]" style="width:100%">
                <thead>
                    <tr class="text-xs text-gray-500 uppercase bg-gray-50 border-b border-gray-100">
                        <th class="py-3 px-3 w-8 text-center"><input type="checkbox" id="check-all-leads" class="w-4 h-4 rounded border-gray-300 text-emerald-600 focus:ring-emerald-500 cursor-pointer"></th>
                        <th class="py-3 px-3 w-10">#</th>
                        <th class="py-3 px-3">LEAD / CONTACT</th>
                        <th class="py-3 px-3">COMPANY</th>
                        <th class="py-3 px-3">EMAIL & STATUS</th>
                        <th class="py-3 px-3">PHONE</th>
                        <th class="py-3 px-3">ACCOUNT OWNER</th>
                        <th class="py-3 px-3">LOCATION</th>
                        <th class="py-3 px-3">OUTREACH</th>
                        <th class="py-3 px-3">DEMO / QUOTE</th>
                        <th class="py-3 px-3">STAGE / STATUS</th>
                        <th class="py-3 px-3">CREATED</th>
                        <th class="py-3 px-3 text-right">ACTIONS</th>
                    </tr>
                </thead>
                <tbody class="text-sm divide-y divide-gray-100"></tbody>
            </table>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- RIGHT SIDE DRAWER FOR IMPORT                                              -->
<!-- ========================================================================= -->
<div id="import-drawer-backdrop" class="fixed inset-0 bg-slate-900/50 backdrop-blur-xs z-[9998] transition-opacity duration-300 opacity-0 pointer-events-none"></div>

<div id="import-drawer" class="fixed top-0 right-0 h-full w-full sm:w-[500px] md:w-[560px] bg-white z-[9999] shadow-2xl transform translate-x-full transition-transform duration-300 ease-in-out flex flex-col">
    <!-- Drawer Header -->
    <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between bg-white flex-shrink-0">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold text-lg border border-emerald-100">
                <i class="fa fa-cloud-upload"></i>
            </div>
            <div>
                <h3 class="text-base font-bold text-gray-800 leading-tight">Import Leads</h3>
                <p class="text-xs text-gray-400 mt-0.5">Upload 33-column Excel or CSV sheet</p>
            </div>
        </div>
        <button type="button" id="btn-close-drawer" class="w-8 h-8 rounded-lg hover:bg-gray-100 text-gray-400 hover:text-gray-600 flex items-center justify-center transition-colors">
            <i class="fa fa-times text-base"></i>
        </button>
    </div>

    <!-- Drawer Body -->
    <div class="flex-1 overflow-y-auto p-6 space-y-5">
        <!-- Top Action Card: Download Templates & View Format Notes -->
        <div class="bg-gradient-to-br from-emerald-50 via-teal-50 to-cyan-50 border border-emerald-200/80 rounded-2xl p-4 space-y-3">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-emerald-900 uppercase tracking-wide flex items-center gap-1.5">
                    <i class="fa fa-file-excel-o text-emerald-600"></i> Template & Notes
                </span>
                <button type="button" id="btn-open-format-notes" class="btn-open-format-notes text-xs font-bold text-emerald-700 hover:text-emerald-900 bg-white px-2.5 py-1 rounded-lg border border-emerald-300 shadow-2xs flex items-center gap-1 hover:bg-emerald-50 transition-colors">
                    <i class="fa fa-info-circle text-emerald-600"></i> Format Notes
                </button>
            </div>
            <p class="text-xs text-emerald-800 leading-relaxed">
                Download our sample template with all 33 headers pre-filled, or click <strong>Format Notes</strong> to see validation rules.
            </p>
            <div class="flex items-center gap-2 pt-1">
                <a href="<?= base_url('leads/sample_template?format=xlsx') ?>" class="flex-1 inline-flex items-center justify-center gap-1.5 px-3 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-2xs transition-colors">
                    <i class="fa fa-download"></i> Sample Excel (.xlsx)
                </a>
                <a href="<?= base_url('leads/sample_template?format=csv') ?>" class="flex-1 inline-flex items-center justify-center gap-1.5 px-3 py-2 bg-white hover:bg-emerald-100/50 border border-emerald-300 text-emerald-800 text-xs font-bold rounded-xl shadow-2xs transition-colors">
                    <i class="fa fa-download"></i> Sample CSV
                </a>
            </div>
        </div>

        <!-- Upload Form inside Drawer -->
        <form id="drawer-import-form" method="POST" action="<?= base_url('leads/import_validate') ?>" enctype="multipart/form-data">
            <input type="hidden" name="<?= $csrf_name ?>" value="<?= $csrf_hash ?>">

            <!-- Target Product / List Assignment -->
            <div class="space-y-1.5 p-3.5 bg-gradient-to-r from-purple-50/70 to-indigo-50/70 border border-purple-200/80 rounded-2xl mb-4">
                <label class="block text-xs font-bold text-purple-950 uppercase tracking-wide flex items-center justify-between">
                    <span class="flex items-center gap-1.5"><i class="fa fa-cube text-purple-600"></i> Target Product / Campaign List</span>
                    <span class="text-[10px] text-purple-600 font-semibold">Auto or Manual</span>
                </label>
                <select name="target_product_id" id="drawer-target-product" class="w-full text-xs font-semibold bg-white border border-purple-200 rounded-xl px-3 py-2.5 text-gray-800 focus:ring-2 focus:ring-purple-500 focus:border-purple-500 shadow-2xs">
                    <option value="auto">🔄 Auto-Detect from Excel / Ads Keywords</option>
                    <?php if (!empty($products)): ?>
                        <?php foreach ($products as $p): ?>
                            <option value="<?= $p['id'] ?>">📦 <?= htmlspecialchars($p['name']) ?><?= !empty($p['sku']) ? ' ('.$p['sku'].')' : '' ?></option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
                <p class="text-[11px] text-purple-700 leading-tight">Excel leads will be automatically categorized under the selected preloaded CRM product.</p>
            </div>

            <!-- Dropzone / File Picker -->
            <div class="space-y-2">
                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wide">
                    Select Spreadsheet File <span class="text-rose-500">*</span>
                </label>
                <div id="drawer-dropzone" class="border-2 border-dashed border-gray-300 hover:border-emerald-500 rounded-2xl p-6 text-center transition-all bg-gray-50/50 cursor-pointer group">
                    <div class="w-12 h-12 rounded-2xl bg-white text-emerald-600 flex items-center justify-center mx-auto mb-3 shadow-2xs border border-gray-100 group-hover:scale-105 transition-transform">
                        <i class="fa fa-cloud-upload text-xl"></i>
                    </div>
                    <p class="text-sm font-semibold text-gray-700 mb-1" id="drawer-file-label">Select .xlsx, .xls or .csv file</p>
                    <p class="text-xs text-gray-400 mb-3">Drag and drop file here or click to browse</p>
                    <input type="file" name="file" id="drawer-file-input" accept=".xlsx, .xls, .csv" required class="hidden">
                    <button type="button" class="px-3.5 py-1.5 bg-white border border-gray-200 rounded-xl text-xs font-semibold text-gray-700 hover:bg-gray-100 shadow-2xs" onclick="$('#drawer-file-input').click();">
                        Browse Files
                    </button>
                </div>

                <!-- Selected File Tag -->
                <div id="drawer-file-selected" class="hidden items-center justify-between p-3 bg-emerald-50 border border-emerald-200 rounded-xl text-xs">
                    <div class="flex items-center gap-2 truncate">
                        <i class="fa fa-file-excel-o text-emerald-600 text-sm"></i>
                        <span id="drawer-selected-filename" class="font-semibold text-emerald-900 truncate">file.xlsx</span>
                        <span id="drawer-selected-filesize" class="text-emerald-700 text-[10px]"></span>
                    </div>
                    <button type="button" id="btn-remove-file" class="text-gray-400 hover:text-rose-600 ml-2">
                        <i class="fa fa-times"></i>
                    </button>
                </div>
            </div>

            <!-- Customer Auto-link Checkbox -->
            <div class="mt-4 p-3.5 bg-gray-50 border border-gray-200/80 rounded-xl">
                <label class="flex items-start gap-2.5 text-xs text-gray-700 cursor-pointer">
                    <input type="checkbox" name="auto_create_customers" id="drawer-auto-customer" value="1" checked class="mt-0.5 w-4 h-4 text-emerald-600 rounded border-gray-300 focus:ring-emerald-500">
                    <span class="leading-relaxed">
                        <strong class="font-semibold text-gray-800">Auto-create / Link Customers</strong><br>
                        Automatically create customer profile if Company Name is new.
                    </span>
                </label>
            </div>

            <!-- Progress Indicator -->
            <div id="drawer-import-progress" class="mt-4 hidden space-y-1.5">
                <div class="flex items-center justify-between text-xs font-semibold text-gray-700">
                    <span class="flex items-center gap-1.5 text-emerald-700">
                        <i class="fa fa-spinner fa-spin"></i> Checking previous records & validating sheet...
                    </span>
                    <span id="drawer-progress-pct">0%</span>
                </div>
                <div class="w-full bg-gray-100 rounded-full h-2 overflow-hidden">
                    <div id="drawer-progress-bar" class="bg-emerald-600 h-2 rounded-full transition-all duration-300" style="width:0%"></div>
                </div>
            </div>

            <!-- Upload & Validate Button -->
            <div class="mt-6">
                <button type="submit" id="btn-drawer-start-import" class="w-full py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-sm rounded-xl transition-all shadow-sm flex items-center justify-center gap-2">
                    <i class="fa fa-shield"></i> Validate & Check Previous Data
                </button>
            </div>
        </form>

        <!-- ========================================================================= -->
        <!-- PREVIEW & DUPLICATE RESOLUTION SECTION (BELOW THE BUTTON)                -->
        <!-- ========================================================================= -->
        <div id="drawer-validation-preview" class="mt-6 hidden space-y-4">
            <!-- Header bar with file info -->
            <div class="p-3.5 bg-slate-900 text-white rounded-xl flex items-center justify-between text-xs">
                <div class="flex items-center gap-2 truncate">
                    <i class="fa fa-check-circle text-emerald-400 text-base"></i>
                    <span class="font-semibold truncate" id="preview-filename">Validated File</span>
                </div>
                <span class="text-[11px] bg-slate-800 px-2 py-0.5 rounded text-slate-300 font-mono" id="preview-total-badge">0 Rows</span>
            </div>

            <!-- Stats Metric Cards -->
            <div class="grid grid-cols-3 gap-2 text-center text-xs">
                <div class="p-3 bg-emerald-50 border border-emerald-200 rounded-xl">
                    <span class="block text-lg font-black text-emerald-700 font-mono" id="preview-new-count">0</span>
                    <span class="text-[10px] font-bold text-emerald-800 uppercase tracking-tight">New Leads</span>
                </div>
                <div class="p-3 bg-amber-50 border border-amber-200 rounded-xl" id="box-dup-metric">
                    <span class="block text-lg font-black text-amber-700 font-mono" id="preview-dup-count">0</span>
                    <span class="text-[10px] font-bold text-amber-800 uppercase tracking-tight">Duplicates (DB)</span>
                </div>
                <div class="p-3 bg-rose-50 border border-rose-200 rounded-xl">
                    <span class="block text-lg font-black text-rose-700 font-mono" id="preview-invalid-count">0</span>
                    <span class="text-[10px] font-bold text-rose-800 uppercase tracking-tight">Invalid Rows</span>
                </div>
            </div>

            <!-- Dynamic Product Detection Banner -->
            <div id="drawer-product-detection-banner" class="p-3 bg-purple-50/80 border border-purple-200 rounded-xl flex items-center justify-between text-xs">
                <div class="flex items-center gap-2">
                    <div class="w-7 h-7 rounded-lg bg-purple-100 text-purple-700 flex items-center justify-center font-bold text-xs flex-shrink-0">
                        <i class="fa fa-cube"></i>
                    </div>
                    <div>
                        <span class="font-bold text-purple-950">Dynamic Product Detection</span>
                        <p class="text-[11px] text-purple-700">Matched via Demo column, Keywords & CRM Products • Deal amounts manually fixed</p>
                    </div>
                </div>
                <span id="preview-matched-products-count" class="font-bold text-xs text-purple-800 bg-white px-2.5 py-1 rounded-lg border border-purple-200 font-mono shadow-2xs">0 Matched</span>
            </div>

            <!-- DUPLICATE RESOLUTION PANEL (Shown if duplicates found) -->
            <div id="drawer-duplicates-panel" class="hidden space-y-3 bg-amber-50/70 border border-amber-200/90 rounded-2xl p-4">
                <div class="flex items-start justify-between gap-2">
                    <div>
                        <h4 class="text-xs font-bold text-amber-900 uppercase tracking-wide flex items-center gap-1.5">
                            <i class="fa fa-exclamation-triangle text-amber-600"></i> Previous Data Conflict Detected
                        </h4>
                        <p class="text-xs text-amber-800 mt-1 leading-relaxed">
                            <span id="dup-notice-count" class="font-bold">0</span> records match existing leads by <strong>Email Address</strong>. Choose your resolution rule:
                        </p>
                    </div>
                    <button type="button" id="btn-show-dup-popup" class="flex-shrink-0 px-2.5 py-1 text-[11px] font-bold bg-white text-amber-800 border border-amber-300 rounded-lg shadow-2xs hover:bg-amber-100 transition-colors">
                        <i class="fa fa-bell-o"></i> Popup
                    </button>
                </div>

                <!-- Duplicate Options Radio Cards -->
                <div class="space-y-2 pt-1 text-xs">
                    <!-- Option 1: Skip Duplicates -->
                    <label class="dup-choice-label flex items-start gap-2.5 p-3 bg-white border-2 border-emerald-500 rounded-xl cursor-pointer transition-all shadow-xs">
                        <input type="radio" name="global_dup_action" value="skip" checked class="mt-0.5 text-emerald-600 focus:ring-emerald-500">
                        <div class="flex-1">
                            <span class="font-bold text-emerald-950 flex items-center gap-1">
                                <i class="fa fa-ban text-emerald-600"></i> Skip Duplicates (Recommended)
                            </span>
                            <p class="text-[11px] text-gray-500 mt-0.5">
                                Keep previous database records untouched. Only insert the brand new unique leads.
                            </p>
                        </div>
                    </label>

                    <!-- Option 2: Overwrite / Update Previous -->
                    <label class="dup-choice-label flex items-start gap-2.5 p-3 bg-white border border-gray-200 hover:border-blue-400 rounded-xl cursor-pointer transition-all">
                        <input type="radio" name="global_dup_action" value="overwrite" class="mt-0.5 text-blue-600 focus:ring-blue-500">
                        <div class="flex-1">
                            <span class="font-bold text-blue-950 flex items-center gap-1">
                                <i class="fa fa-refresh text-blue-600"></i> Overwrite / Update Previous Records
                            </span>
                            <p class="text-[11px] text-gray-500 mt-0.5">
                                Update existing previous leads in CRM with new Excel details, and add new leads.
                            </p>
                        </div>
                    </label>

                    <!-- Option 3: Delete & Replace Previous -->
                    <label class="dup-choice-label flex items-start gap-2.5 p-3 bg-white border border-gray-200 hover:border-rose-400 rounded-xl cursor-pointer transition-all">
                        <input type="radio" name="global_dup_action" value="delete" class="mt-0.5 text-rose-600 focus:ring-rose-500">
                        <div class="flex-1">
                            <span class="font-bold text-rose-950 flex items-center gap-1">
                                <i class="fa fa-trash-o text-rose-600"></i> Delete Previous & Replace With New
                            </span>
                            <p class="text-[11px] text-gray-500 mt-0.5">
                                Soft delete the previous duplicate records and insert fresh lead entries.
                            </p>
                        </div>
                    </label>
                </div>

                <!-- Duplicate Details Table -->
                <div class="pt-2">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs font-bold text-gray-700">Matched Duplicate Leads:</span>
                        <span class="text-[11px] text-gray-400">Select action per row</span>
                    </div>
                    <div class="overflow-x-auto max-h-56 overflow-y-auto border border-amber-200/80 rounded-xl bg-white shadow-2xs">
                        <table class="w-full text-left text-xs whitespace-nowrap min-w-[560px]">
                            <thead class="bg-amber-100/70 text-amber-900 text-[10px] uppercase font-bold sticky top-0">
                                <tr>
                                    <th class="py-2 px-2.5">Row</th>
                                    <th class="py-2 px-2.5">Spreadsheet Lead</th>
                                    <th class="py-2 px-2.5">Matched Product</th>
                                    <th class="py-2 px-2.5">Previous Lead Matched</th>
                                    <th class="py-2 px-2.5">Match Reason</th>
                                    <th class="py-2 px-2.5 text-right">Row Action</th>
                                </tr>
                            </thead>
                            <tbody id="drawer-dup-table-body" class="divide-y divide-gray-100"></tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Clean / No Duplicates Notice -->
            <div id="drawer-clean-panel" class="hidden p-3.5 bg-emerald-50 border border-emerald-200 rounded-xl text-xs text-emerald-800 flex items-center gap-2.5">
                <i class="fa fa-check-circle text-emerald-600 text-base flex-shrink-0"></i>
                <div>
                    <strong class="font-bold text-emerald-900">Zero Duplicates Detected!</strong>
                    <p class="text-[11px] text-emerald-700 mt-0.5">All spreadsheet rows are fresh and unique against existing database records.</p>
                </div>
            </div>

            <!-- Confirmation Buttons Below Preview -->
            <div class="space-y-2 pt-2">
                <button type="button" id="btn-confirm-save-import" class="w-full py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-sm rounded-xl transition-all shadow-md flex items-center justify-center gap-2">
                    <i class="fa fa-check-circle"></i> <span id="btn-confirm-label">Confirm & Save Leads</span>
                </button>
                <button type="button" id="btn-cancel-import-preview" class="w-full py-2.5 bg-white hover:bg-gray-100 text-gray-700 font-semibold text-xs rounded-xl border border-gray-200 transition-colors">
                    <i class="fa fa-times mr-1"></i> Cancel & Choose Another File
                </button>
            </div>
        </div>

        <!-- Import Results Box (Inside Drawer) -->
        <div id="drawer-import-results" class="mt-4 hidden"></div>
    </div>

    <!-- In-Drawer Duplicate Popup Backdrop & Modal -->
    <div id="drawer-duplicate-backdrop" class="hidden absolute inset-0 bg-slate-900/40 backdrop-blur-2xs z-25 transition-opacity"></div>
    <div id="drawer-duplicate-popup" class="hidden absolute inset-x-4 top-24 bg-white border-2 border-amber-400 rounded-2xl shadow-2xl p-5 z-30 transition-all transform animate-in fade-in zoom-in-95 duration-200">
        <div class="flex items-start justify-between gap-3 mb-3">
            <div class="flex items-center gap-2.5">
                <div class="w-9 h-9 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center font-bold text-base flex-shrink-0">
                    <i class="fa fa-exclamation-triangle"></i>
                </div>
                <div>
                    <h4 class="text-sm font-bold text-gray-900 leading-tight">Duplicate Leads Found!</h4>
                    <p class="text-xs text-gray-500">Existing records match by Email Address</p>
                </div>
            </div>
            <button type="button" id="btn-close-dup-popup" class="text-gray-400 hover:text-gray-600 text-lg leading-none p-1">
                &times;
            </button>
        </div>

        <p class="text-xs text-gray-600 leading-relaxed mb-4">
            Found <strong id="popup-dup-num" class="text-amber-700 font-bold">0</strong> duplicate row(s) that match previous leads in your database. Do you want to skip them or overwrite previous data?
        </p>

        <div class="flex flex-col sm:flex-row items-center gap-2">
            <button type="button" id="btn-popup-skip-save" class="w-full sm:flex-1 py-2.5 px-3 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl transition-colors shadow-2xs flex items-center justify-center gap-1.5">
                <i class="fa fa-ban"></i> Skip Duplicates (Save New)
            </button>
            <button type="button" id="btn-popup-overwrite-save" class="w-full sm:flex-1 py-2.5 px-3 bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs rounded-xl transition-colors shadow-2xs flex items-center justify-center gap-1.5">
                <i class="fa fa-refresh"></i> Overwrite Previous
            </button>
        </div>
        <button type="button" id="btn-popup-review" class="w-full mt-2 py-2 text-center text-xs font-semibold text-gray-500 hover:text-gray-800">
            Or Review Row Details Below &darr;
        </button>
    </div>
</div>


<!-- ========================================================================= -->
<!-- EXCEL FORMAT NOTES & VALIDATION GUIDELINES MODAL                          -->
<!-- ========================================================================= -->
<div class="modal fade" id="format-notes-modal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
        <div class="modal-content rounded-2xl border-0 shadow-2xl overflow-hidden">
            <div class="modal-header bg-gradient-to-r from-slate-800 to-slate-900 px-6 py-4 text-white flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-emerald-500/20 text-emerald-400 border border-emerald-400/30 flex items-center justify-center font-bold text-base">
                        <i class="fa fa-file-text-o"></i>
                    </div>
                    <div>
                        <h4 class="modal-title text-base font-bold text-white">Excel Format Notes & Validation Requirements</h4>
                        <p class="text-xs text-slate-300">All 33 columns specification, data validation rules and sample values</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <a href="<?= base_url('leads/sample_template?format=xlsx') ?>" class="inline-flex items-center gap-1 px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-semibold shadow-2xs transition-colors">
                        <i class="fa fa-download"></i> Download Sample Excel
                    </a>
                    <button type="button" class="text-white hover:text-gray-300 text-2xl font-light leading-none focus:outline-none ml-2" data-dismiss="modal">&times;</button>
                </div>
            </div>

            <div class="modal-body p-6 max-h-[75vh] overflow-y-auto space-y-4">
                <div class="p-3.5 bg-blue-50 border border-blue-200 rounded-xl text-xs text-blue-900 flex items-start gap-2.5">
                    <i class="fa fa-lightbulb-o text-blue-600 text-base mt-0.5 flex-shrink-0"></i>
                    <div>
                        <strong class="font-bold">Validation Instructions:</strong>
                        <ul class="list-disc list-inside mt-1 space-y-0.5 text-blue-800">
                            <li>Each row must have at least one identifier: <code>First Name</code>, <code>Last Name</code>, <code>Title</code>, <code>Company Name</code>, or <code>Email</code>.</li>
                            <li>If provided, <code>Email</code> and <code>Secondary Email</code> must be valid email addresses (e.g. <code>user@example.com</code>).</li>
                            <li>Column headers are case-insensitive and resilient to spaces or formatting (e.g. <code># Employees</code>, <code>Employees</code>, or <code>No of Employees</code> are all recognized).</li>
                        </ul>
                    </div>
                </div>

                <div class="border border-gray-200 rounded-2xl overflow-hidden shadow-2xs">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-gray-50 border-b border-gray-200 text-gray-700 uppercase font-bold text-[11px]">
                            <tr>
                                <th class="py-2.5 px-3 w-12 text-center">#</th>
                                <th class="py-2.5 px-3">Column Name</th>
                                <th class="py-2.5 px-3">Data Type / Format</th>
                                <th class="py-2.5 px-3">Sample Value</th>
                                <th class="py-2.5 px-3">Validation & Notes</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-gray-700">
                            <tr class="bg-white hover:bg-gray-50"><td class="py-2 px-3 text-center font-bold text-gray-400">1</td><td class="py-2 px-3 font-semibold text-gray-900">First Name</td><td class="py-2 px-3">Text (max 100)</td><td class="py-2 px-3 font-mono text-gray-600">Rajesh</td><td class="py-2 px-3 text-gray-500">Contact's given name</td></tr>
                            <tr class="bg-white hover:bg-gray-50"><td class="py-2 px-3 text-center font-bold text-gray-400">2</td><td class="py-2 px-3 font-semibold text-gray-900">Last Name</td><td class="py-2 px-3">Text (max 100)</td><td class="py-2 px-3 font-mono text-gray-600">Kumar</td><td class="py-2 px-3 text-gray-500">Contact's family name / surname</td></tr>
                            <tr class="bg-white hover:bg-gray-50"><td class="py-2 px-3 text-center font-bold text-gray-400">3</td><td class="py-2 px-3 font-semibold text-emerald-800">Title</td><td class="py-2 px-3">Text (max 200)</td><td class="py-2 px-3 font-mono text-gray-600">CTO / Director of IT</td><td class="py-2 px-3 text-gray-500">Job Title or Designation. If blank, auto-filled from Name/Company</td></tr>
                            <tr class="bg-white hover:bg-gray-50"><td class="py-2 px-3 text-center font-bold text-gray-400">4</td><td class="py-2 px-3 font-semibold text-gray-900">Company Name</td><td class="py-2 px-3">Text (max 200)</td><td class="py-2 px-3 font-mono text-gray-600">TechNova Solutions</td><td class="py-2 px-3 text-gray-500">Company / Organization name. Auto-links customer</td></tr>
                            <tr class="bg-white hover:bg-gray-50"><td class="py-2 px-3 text-center font-bold text-gray-400">5</td><td class="py-2 px-3 font-semibold text-blue-700">Email</td><td class="py-2 px-3">Email format</td><td class="py-2 px-3 font-mono text-gray-600">rajesh@technova.com</td><td class="py-2 px-3 text-gray-500">Must be a valid email syntax if entered</td></tr>
                            <tr class="bg-white hover:bg-gray-50"><td class="py-2 px-3 text-center font-bold text-gray-400">6</td><td class="py-2 px-3 font-semibold text-gray-900">Email Status</td><td class="py-2 px-3">Text (max 50)</td><td class="py-2 px-3 font-mono text-gray-600">Valid / Verified</td><td class="py-2 px-3 text-gray-500">Valid, Verified, Unverified, Bounced, Catch-all</td></tr>
                            <tr class="bg-white hover:bg-gray-50"><td class="py-2 px-3 text-center font-bold text-gray-400">7</td><td class="py-2 px-3 font-semibold text-gray-900">Secondary Email</td><td class="py-2 px-3">Email format</td><td class="py-2 px-3 font-mono text-gray-600">rajesh.p@gmail.com</td><td class="py-2 px-3 text-gray-500">Optional secondary/personal email</td></tr>
                            <tr class="bg-white hover:bg-gray-50"><td class="py-2 px-3 text-center font-bold text-gray-400">8</td><td class="py-2 px-3 font-semibold text-gray-900">Corporate Phone</td><td class="py-2 px-3">Phone number</td><td class="py-2 px-3 font-mono text-gray-600">+91 9876543210</td><td class="py-2 px-3 text-gray-500">Mobile or direct corporate phone number</td></tr>
                            <tr class="bg-white hover:bg-gray-50"><td class="py-2 px-3 text-center font-bold text-gray-400">9</td><td class="py-2 px-3 font-semibold text-gray-900">Account Owner</td><td class="py-2 px-3">Text / User Name</td><td class="py-2 px-3 font-mono text-gray-600">admin</td><td class="py-2 px-3 text-gray-500">Sales representative username or name</td></tr>
                            <tr class="bg-white hover:bg-gray-50"><td class="py-2 px-3 text-center font-bold text-gray-400">10</td><td class="py-2 px-3 font-semibold text-gray-900"># Employees</td><td class="py-2 px-3">Range / Count</td><td class="py-2 px-3 font-mono text-gray-600">50-100</td><td class="py-2 px-3 text-gray-500">Employee range e.g. 1-10, 50-100, 500+</td></tr>
                            <tr class="bg-white hover:bg-gray-50"><td class="py-2 px-3 text-center font-bold text-gray-400">11</td><td class="py-2 px-3 font-semibold text-gray-900">Industry</td><td class="py-2 px-3">Text (max 150)</td><td class="py-2 px-3 font-mono text-gray-600">Information Technology</td><td class="py-2 px-3 text-gray-500">Business industry/sector</td></tr>
                            <tr class="bg-white hover:bg-gray-50"><td class="py-2 px-3 text-center font-bold text-gray-400">12</td><td class="py-2 px-3 font-semibold text-gray-900">Keywords</td><td class="py-2 px-3">Text / Tags</td><td class="py-2 px-3 font-mono text-gray-600">Cloud, SaaS, AI, CRM</td><td class="py-2 px-3 text-gray-500">Comma-separated tags or interest keywords</td></tr>
                            <tr class="bg-white hover:bg-gray-50"><td class="py-2 px-3 text-center font-bold text-gray-400">13</td><td class="py-2 px-3 font-semibold text-gray-900">Person Linkedin Url</td><td class="py-2 px-3">URL</td><td class="py-2 px-3 font-mono text-gray-600">https://linkedin.com/in/...</td><td class="py-2 px-3 text-gray-500">Personal LinkedIn profile link</td></tr>
                            <tr class="bg-white hover:bg-gray-50"><td class="py-2 px-3 text-center font-bold text-gray-400">14</td><td class="py-2 px-3 font-semibold text-gray-900">Website</td><td class="py-2 px-3">URL / Domain</td><td class="py-2 px-3 font-mono text-gray-600">https://technova.com</td><td class="py-2 px-3 text-gray-500">Company website URL</td></tr>
                            <tr class="bg-white hover:bg-gray-50"><td class="py-2 px-3 text-center font-bold text-gray-400">15</td><td class="py-2 px-3 font-semibold text-gray-900">Company Linkedin Url</td><td class="py-2 px-3">URL</td><td class="py-2 px-3 font-mono text-gray-600">https://linkedin.com/company/...</td><td class="py-2 px-3 text-gray-500">Company page on LinkedIn</td></tr>
                            <tr class="bg-white hover:bg-gray-50"><td class="py-2 px-3 text-center font-bold text-gray-400">16</td><td class="py-2 px-3 font-semibold text-gray-900">Facebook Url</td><td class="py-2 px-3">URL</td><td class="py-2 px-3 font-mono text-gray-600">https://facebook.com/...</td><td class="py-2 px-3 text-gray-500">Facebook profile or page URL</td></tr>
                            <tr class="bg-white hover:bg-gray-50"><td class="py-2 px-3 text-center font-bold text-gray-400">17</td><td class="py-2 px-3 font-semibold text-gray-900">Twitter Url</td><td class="py-2 px-3">URL</td><td class="py-2 px-3 font-mono text-gray-600">https://twitter.com/...</td><td class="py-2 px-3 text-gray-500">Twitter/X profile URL</td></tr>
                            <tr class="bg-white hover:bg-gray-50"><td class="py-2 px-3 text-center font-bold text-gray-400">18</td><td class="py-2 px-3 font-semibold text-gray-900">Address</td><td class="py-2 px-3">Text</td><td class="py-2 px-3 font-mono text-gray-600">123 Anna Salai</td><td class="py-2 px-3 text-gray-500">Contact's personal/office street address</td></tr>
                            <tr class="bg-white hover:bg-gray-50"><td class="py-2 px-3 text-center font-bold text-gray-400">19</td><td class="py-2 px-3 font-semibold text-gray-900">City</td><td class="py-2 px-3">Text (max 100)</td><td class="py-2 px-3 font-mono text-gray-600">Chennai</td><td class="py-2 px-3 text-gray-500">Contact city name</td></tr>
                            <tr class="bg-white hover:bg-gray-50"><td class="py-2 px-3 text-center font-bold text-gray-400">20</td><td class="py-2 px-3 font-semibold text-gray-900">State</td><td class="py-2 px-3">Text (max 100)</td><td class="py-2 px-3 font-mono text-gray-600">Tamil Nadu</td><td class="py-2 px-3 text-gray-500">Contact state or province</td></tr>
                            <tr class="bg-white hover:bg-gray-50"><td class="py-2 px-3 text-center font-bold text-gray-400">21</td><td class="py-2 px-3 font-semibold text-gray-900">Country</td><td class="py-2 px-3">Text (max 100)</td><td class="py-2 px-3 font-mono text-gray-600">India</td><td class="py-2 px-3 text-gray-500">Contact country name</td></tr>
                            <tr class="bg-white hover:bg-gray-50"><td class="py-2 px-3 text-center font-bold text-gray-400">22</td><td class="py-2 px-3 font-semibold text-gray-900">Company Address</td><td class="py-2 px-3">Text</td><td class="py-2 px-3 font-mono text-gray-600">456 OMR IT Corridor</td><td class="py-2 px-3 text-gray-500">Corporate HQ street address</td></tr>
                            <tr class="bg-white hover:bg-gray-50"><td class="py-2 px-3 text-center font-bold text-gray-400">23</td><td class="py-2 px-3 font-semibold text-gray-900">Company City</td><td class="py-2 px-3">Text (max 100)</td><td class="py-2 px-3 font-mono text-gray-600">Chennai</td><td class="py-2 px-3 text-gray-500">Company HQ city</td></tr>
                            <tr class="bg-white hover:bg-gray-50"><td class="py-2 px-3 text-center font-bold text-gray-400">24</td><td class="py-2 px-3 font-semibold text-gray-900">Company State</td><td class="py-2 px-3">Text (max 100)</td><td class="py-2 px-3 font-mono text-gray-600">Tamil Nadu</td><td class="py-2 px-3 text-gray-500">Company HQ state</td></tr>
                            <tr class="bg-white hover:bg-gray-50"><td class="py-2 px-3 text-center font-bold text-gray-400">25</td><td class="py-2 px-3 font-semibold text-gray-900">Company Country</td><td class="py-2 px-3">Text (max 100)</td><td class="py-2 px-3 font-mono text-gray-600">India</td><td class="py-2 px-3 text-gray-500">Company HQ country</td></tr>
                            <tr class="bg-white hover:bg-gray-50"><td class="py-2 px-3 text-center font-bold text-gray-400">26</td><td class="py-2 px-3 font-semibold text-gray-900">Company Phone</td><td class="py-2 px-3">Phone number</td><td class="py-2 px-3 font-mono text-gray-600">+91 44 28765432</td><td class="py-2 px-3 text-gray-500">Company switchboard / board phone</td></tr>
                            <tr class="bg-white hover:bg-gray-50"><td class="py-2 px-3 text-center font-bold text-gray-400">27</td><td class="py-2 px-3 font-semibold text-gray-900">Technologies</td><td class="py-2 px-3">Text</td><td class="py-2 px-3 font-mono text-gray-600">PHP, React, AWS, MySQL</td><td class="py-2 px-3 text-gray-500">Comma-separated tech stack</td></tr>
                            <tr class="bg-white hover:bg-gray-50"><td class="py-2 px-3 text-center font-bold text-gray-400">28</td><td class="py-2 px-3 font-semibold text-gray-900">Annual Revenue</td><td class="py-2 px-3">Text (max 100)</td><td class="py-2 px-3 font-mono text-gray-600">$2M - $5M</td><td class="py-2 px-3 text-gray-500">Annual turnover / revenue bracket</td></tr>
                            <tr class="bg-white hover:bg-gray-50"><td class="py-2 px-3 text-center font-bold text-gray-400">29</td><td class="py-2 px-3 font-semibold text-gray-900">Email Sent</td><td class="py-2 px-3">Text / Yes/No</td><td class="py-2 px-3 font-mono text-gray-600">Yes / No</td><td class="py-2 px-3 text-gray-500">Outreach email dispatch status</td></tr>
                            <tr class="bg-white hover:bg-gray-50"><td class="py-2 px-3 text-center font-bold text-gray-400">30</td><td class="py-2 px-3 font-semibold text-gray-900">Email Open</td><td class="py-2 px-3">Text / Yes/No</td><td class="py-2 px-3 font-mono text-gray-600">Yes / No</td><td class="py-2 px-3 text-gray-500">Email opened by recipient</td></tr>
                            <tr class="bg-white hover:bg-gray-50"><td class="py-2 px-3 text-center font-bold text-gray-400">31</td><td class="py-2 px-3 font-semibold text-gray-900">Email Bounced</td><td class="py-2 px-3">Text / Yes/No</td><td class="py-2 px-3 font-mono text-gray-600">No / Yes</td><td class="py-2 px-3 text-gray-500">Email bounce status</td></tr>
                            <tr class="bg-white hover:bg-gray-50"><td class="py-2 px-3 text-center font-bold text-gray-400">32</td><td class="py-2 px-3 font-semibold text-gray-900">Demo</td><td class="py-2 px-3">Text</td><td class="py-2 px-3 font-mono text-gray-600">Scheduled / Completed</td><td class="py-2 px-3 text-gray-500">Product demo status (Scheduled, Done, Yes, No)</td></tr>
                            <tr class="bg-white hover:bg-gray-50"><td class="py-2 px-3 text-center font-bold text-gray-400">33</td><td class="py-2 px-3 font-semibold text-gray-900">Quotation</td><td class="py-2 px-3">Text</td><td class="py-2 px-3 font-mono text-gray-600">Sent / Draft / Approved</td><td class="py-2 px-3 text-gray-500">Price quotation or proposal status</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer bg-gray-50 px-6 py-3 border-t border-gray-100 flex items-center justify-between">
                <span class="text-xs text-gray-500">
                    <i class="fa fa-check-circle text-emerald-600 mr-1"></i> All headers match user specification exactly
                </span>
                <button type="button" class="px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white rounded-xl text-xs font-semibold" data-dismiss="modal">
                    Got it, Close
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- RIGHT-SIDE DRAWER FOR ADD / EDIT LEAD (ALL 33 FIELDS ACROSS TABS)          -->
<!-- ========================================================================= -->
<div id="lead-drawer-backdrop" class="fixed inset-0 bg-slate-900/50 backdrop-blur-xs z-[9998] transition-opacity duration-300 opacity-0 pointer-events-none"></div>

<div id="lead-drawer" class="fixed top-0 right-0 h-full w-full sm:w-[680px] md:w-[780px] lg:w-[840px] bg-white z-[9999] shadow-2xl transform translate-x-full transition-transform duration-300 ease-in-out flex flex-col">
    <!-- Drawer Header -->
    <div class="px-6 py-4 bg-gradient-to-r from-emerald-600 to-teal-700 text-white flex items-center justify-between flex-shrink-0 shadow-sm">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-white/15 backdrop-blur-xs text-white border border-white/20 flex items-center justify-center font-bold text-lg">
                <i class="fa fa-user-plus" id="lead-drawer-icon"></i>
            </div>
            <div>
                <h3 class="text-base font-bold text-white leading-tight" id="lead-drawer-title">Add Lead</h3>
                <p class="text-xs text-emerald-100">Manage all 33 lead attributes, company information & pipeline</p>
            </div>
        </div>
        <button type="button" id="btn-close-lead-drawer" class="w-8 h-8 rounded-lg hover:bg-white/15 text-white/80 hover:text-white flex items-center justify-center transition-colors">
            <i class="fa fa-times text-lg"></i>
        </button>
    </div>

    <!-- Tab Navigation (Sticky below header) -->
    <div class="bg-gray-50 border-b border-gray-200 px-6 pt-3 flex gap-2 flex-shrink-0 overflow-x-auto">
        <button type="button" class="lead-tab-btn active px-4 py-2.5 text-xs font-bold rounded-t-xl transition-all border-b-2 border-emerald-600 text-emerald-700 bg-white whitespace-nowrap" data-target="#tab-contact">
            <i class="fa fa-user mr-1.5"></i> Contact & Personal
        </button>
        <button type="button" class="lead-tab-btn px-4 py-2.5 text-xs font-semibold rounded-t-xl transition-all border-b-2 border-transparent text-gray-500 hover:text-gray-700 whitespace-nowrap" data-target="#tab-company">
            <i class="fa fa-building mr-1.5"></i> Company & Firmographics
        </button>
        <button type="button" class="lead-tab-btn px-4 py-2.5 text-xs font-semibold rounded-t-xl transition-all border-b-2 border-transparent text-gray-500 hover:text-gray-700 whitespace-nowrap" data-target="#tab-sales">
            <i class="fa fa-line-chart mr-1.5"></i> Outreach & Sales Pipeline
        </button>
    </div>

    <!-- Scrollable Drawer Body with All 33 Form Fields -->
    <div class="flex-1 overflow-y-auto p-6">
        <form id="lead-form" method="POST" action="<?= base_url('leads/save') ?>">
            <input type="hidden" name="<?= $csrf_name ?>" value="<?= $csrf_hash ?>">
            <input type="hidden" name="id" id="lead-id" value="0">

            <!-- TAB 1: CONTACT & PERSONAL -->
            <div id="tab-contact" class="lead-tab-pane space-y-5">
                <div class="bg-emerald-50/60 border border-emerald-100 rounded-xl p-3.5 flex items-center justify-between text-xs text-emerald-800">
                    <span class="font-semibold"><i class="fa fa-info-circle mr-1 text-emerald-600"></i> Contact Profile & Social Presence</span>
                    <span class="text-gray-500">* Required identification</span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 uppercase mb-1">First Name</label>
                        <input type="text" name="first_name" class="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none" placeholder="e.g. Rajesh">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 uppercase mb-1">Last Name</label>
                        <input type="text" name="last_name" class="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none" placeholder="e.g. Kumar">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 uppercase mb-1">Title / Designation *</label>
                        <input type="text" name="title" class="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none" placeholder="e.g. CTO / Product Head" required>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 uppercase mb-1">Email</label>
                        <input type="email" name="email" class="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none" placeholder="name@company.com">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 uppercase mb-1">Email Status</label>
                        <select name="email_status" class="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                            <option value="">— Select / Unknown —</option>
                            <option value="Valid">Valid</option>
                            <option value="Verified">Verified</option>
                            <option value="Unverified">Unverified</option>
                            <option value="Catch-all">Catch-all</option>
                            <option value="Bounced">Bounced</option>
                            <option value="Invalid">Invalid</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 uppercase mb-1">Secondary Email</label>
                        <input type="email" name="secondary_email" class="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none" placeholder="personal@gmail.com">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 uppercase mb-1">Corporate Phone</label>
                        <input type="text" name="corporate_phone" class="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none" placeholder="+91 9876543210">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 uppercase mb-1">Website</label>
                        <input type="text" name="website" class="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none" placeholder="https://company.com">
                    </div>
                </div>

                <div class="border-t border-gray-100 pt-4">
                    <h5 class="text-xs font-bold text-gray-700 uppercase tracking-wide mb-3 flex items-center gap-1.5">
                        <i class="fa fa-share-alt text-blue-500"></i> Social Profiles
                    </h5>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1"><i class="fa fa-linkedin text-blue-600"></i> Person LinkedIn URL</label>
                            <input type="text" name="person_linkedin_url" class="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none" placeholder="https://linkedin.com/in/username">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1"><i class="fa fa-facebook text-blue-800"></i> Facebook URL</label>
                            <input type="text" name="facebook_url" class="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none" placeholder="https://facebook.com/username">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1"><i class="fa fa-twitter text-cyan-500"></i> Twitter URL</label>
                            <input type="text" name="twitter_url" class="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none" placeholder="https://twitter.com/handle">
                        </div>
                    </div>
                </div>

                <div class="border-t border-gray-100 pt-4">
                    <h5 class="text-xs font-bold text-gray-700 uppercase tracking-wide mb-3 flex items-center gap-1.5">
                        <i class="fa fa-map-marker text-rose-500"></i> Personal / Contact Address
                    </h5>
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div class="md:col-span-4">
                            <label class="block text-xs font-medium text-gray-600 mb-1">Street Address</label>
                            <input type="text" name="address" class="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none" placeholder="Door No, Street Name, Landmark">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">City</label>
                            <input type="text" name="city" class="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none" placeholder="Chennai">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">State</label>
                            <input type="text" name="state" class="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none" placeholder="Tamil Nadu">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-xs font-medium text-gray-600 mb-1">Country</label>
                            <input type="text" name="country" class="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none" placeholder="India">
                        </div>
                    </div>
                </div>
            </div>

            <!-- TAB 2: COMPANY & FIRMOGRAPHICS -->
            <div id="tab-company" class="lead-tab-pane space-y-5 hidden">
                <div class="bg-blue-50/60 border border-blue-100 rounded-xl p-3.5 flex items-center justify-between text-xs text-blue-800">
                    <span class="font-semibold"><i class="fa fa-building-o mr-1 text-blue-600"></i> Company Firmographics & Corporate Headquarters</span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 uppercase mb-1">Company Name</label>
                        <input type="text" name="company_name" class="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none" placeholder="e.g. TechNova Solutions Pvt Ltd">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 uppercase mb-1">Link to Existing Customer (Optional)</label>
                        <select name="customer_id" class="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-emerald-500 select2">
                            <option value="">— Select / Unlinked —</option>
                            <?php foreach($customers as $c): ?>
                            <option value="<?= $c['id'] ?>"><?= esc_html($c['customer_name'] ? $c['customer_name'] . ' (' . $c['customer_org_name'] . ')' : $c['customer_org_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 uppercase mb-1"># Employees</label>
                        <input type="text" name="employees_count" class="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none" placeholder="e.g. 10-50, 100-250">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 uppercase mb-1">Industry</label>
                        <input type="text" name="industry" class="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none" placeholder="e.g. Information Technology">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 uppercase mb-1">Annual Revenue</label>
                        <input type="text" name="annual_revenue" class="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none" placeholder="e.g. $1M - $5M">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 uppercase mb-1">Keywords</label>
                        <input type="text" name="keywords" class="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none" placeholder="e.g. SaaS, CRM, Cloud Infrastructure">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 uppercase mb-1">Technologies</label>
                        <input type="text" name="technologies" class="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none" placeholder="e.g. PHP, MySQL, React, AWS">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 uppercase mb-1">Company Phone</label>
                        <input type="text" name="company_phone" class="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none" placeholder="+91 44 28765432">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 uppercase mb-1">Company LinkedIn URL</label>
                        <input type="text" name="company_linkedin_url" class="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none" placeholder="https://linkedin.com/company/technova">
                    </div>
                </div>

                <div class="border-t border-gray-100 pt-4">
                    <h5 class="text-xs font-bold text-gray-700 uppercase tracking-wide mb-3 flex items-center gap-1.5">
                        <i class="fa fa-map-pin text-indigo-500"></i> Company Headquarters Address
                    </h5>
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div class="md:col-span-4">
                            <label class="block text-xs font-medium text-gray-600 mb-1">Company Address</label>
                            <input type="text" name="company_address" class="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none" placeholder="Tech Park, Block B, 4th Floor">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Company City</label>
                            <input type="text" name="company_city" class="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none" placeholder="Chennai">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Company State</label>
                            <input type="text" name="company_state" class="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none" placeholder="Tamil Nadu">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-xs font-medium text-gray-600 mb-1">Company Country</label>
                            <input type="text" name="company_country" class="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none" placeholder="India">
                        </div>
                    </div>
                </div>
            </div>

            <!-- TAB 3: OUTREACH & PIPELINE -->
            <div id="tab-sales" class="lead-tab-pane space-y-5 hidden">
                <div class="bg-amber-50/60 border border-amber-100 rounded-xl p-3.5 flex items-center justify-between text-xs text-amber-800">
                    <span class="font-semibold"><i class="fa fa-envelope-open mr-1 text-amber-600"></i> Outreach Performance, Deal Value & Pipeline Stage</span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 uppercase mb-1">Account Owner (Name)</label>
                        <input type="text" name="account_owner" class="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none" placeholder="e.g. Rajesh / admin">
                    </div>
                    <?php if($is_manager): ?>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 uppercase mb-1">Assigned Staff User</label>
                        <select name="assigned_to" class="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-emerald-500 select2">
                            <option value="">— Self / Unassigned —</option>
                            <?php foreach($staff as $s): ?>
                            <option value="<?= $s['id'] ?>"><?= esc_html($s['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <?php endif; ?>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 uppercase mb-1">Lead Stage</label>
                        <select name="lead_status" class="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                            <option value="new">New</option>
                            <option value="contacted">Contacted</option>
                            <option value="qualified">Qualified</option>
                            <option value="proposal">Proposal</option>
                            <option value="negotiation">Negotiation</option>
                            <option value="won">Won</option>
                            <option value="lost">Lost</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 uppercase mb-1">Source</label>
                        <select name="source" class="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                            <option value="online">Online / Web</option>
                            <option value="field">Field</option>
                            <option value="call">Call</option>
                            <option value="referral">Referral</option>
                            <option value="walk_in">Walk In</option>
                        </select>
                    </div>
                </div>

                <div class="border-t border-gray-100 pt-4">
                    <h5 class="text-xs font-bold text-gray-700 uppercase tracking-wide mb-3 flex items-center gap-1.5">
                        <i class="fa fa-envelope text-blue-500"></i> Email Outreach Tracking
                    </h5>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Email Sent</label>
                            <select name="email_sent" class="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                                <option value="">— Not Specified —</option>
                                <option value="Yes">Yes</option>
                                <option value="No">No</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Email Open</label>
                            <select name="email_open" class="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                                <option value="">— Not Specified —</option>
                                <option value="Yes">Yes</option>
                                <option value="No">No</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Email Bounced</label>
                            <select name="email_bounced" class="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                                <option value="">— Not Specified —</option>
                                <option value="No">No</option>
                                <option value="Yes">Yes</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="border-t border-gray-100 pt-4">
                    <h5 class="text-xs font-bold text-gray-700 uppercase tracking-wide mb-3 flex items-center gap-1.5">
                        <i class="fa fa-desktop text-purple-600"></i> Demo & Quotation Status
                    </h5>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Product Demo</label>
                            <input type="text" name="product_demo" class="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none" placeholder="e.g. Class Wall / Proman / Scheduled / Yes">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Quotation</label>
                            <input type="text" name="quotation" class="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none" placeholder="e.g. Sent / Draft / Approved / Rejected">
                        </div>
                    </div>
                </div>

                <div class="border-t border-gray-100 pt-4">
                    <h5 class="text-xs font-bold text-gray-700 uppercase tracking-wide mb-3 flex items-center justify-between">
                        <span class="flex items-center gap-1.5"><i class="fa fa-cube text-emerald-600"></i> Product & Deal Forecast</span>
                        <span class="text-[10px] text-gray-400 font-normal">Auto-detected from sheet keywords • Manual amount</span>
                    </h5>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 uppercase mb-1">Interested / Linked Product</label>
                            <select name="product_id" id="lead-product-id" class="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                                <option value="">— Select Product (or None) —</option>
                                <?php if (!empty($products)): foreach ($products as $p): ?>
                                    <option value="<?= $p['id'] ?>" data-price="<?= number_format($p['price'] / 100, 2, '.', '') ?>">
                                        <?= htmlspecialchars($p['name']) ?> (<?= htmlspecialchars($p['sku']) ?>)
                                    </option>
                                <?php endforeach; endif; ?>
                            </select>
                            <p class="text-[11px] text-gray-400 mt-1">Auto-detected from spreadsheet keywords on upload.</p>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 uppercase mb-1">
                                Expected Value (₹) <span class="text-[10px] font-normal text-emerald-600 lowercase">(manual fixed)</span>
                            </label>
                            <input type="number" step="0.01" name="expected_value" id="lead-expected-value" class="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none" placeholder="0.00">
                            <p class="text-[11px] text-gray-400 mt-1">Amount is manually fixed and not auto-overwritten.</p>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 uppercase mb-1">Expected Close Date</label>
                            <input type="text" name="expected_close_date" class="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-emerald-500 datepicker" placeholder="YYYY-MM-DD">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 uppercase mb-1">Catalogue Reference Price</label>
                            <div id="product-ref-price-badge" class="px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl text-sm text-gray-500 font-mono flex items-center justify-between">
                                <span>Catalogue: <strong id="product-ref-price-text" class="text-gray-700">₹0.00</strong></span>
                                <span class="text-[10px] text-gray-400 font-sans">Reference only</span>
                            </div>
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-xs font-semibold text-gray-600 uppercase mb-1">Description / Internal Notes</label>
                            <textarea name="description" rows="3" class="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-emerald-500 resize-none" placeholder="Key talking points, customer requirements, follow-up history..."></textarea>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- Drawer Sticky Footer with Save button -->
    <div class="bg-gray-50 border-t border-gray-200 px-6 py-3.5 flex items-center justify-between flex-shrink-0">
        <span class="text-xs text-gray-500 hidden sm:flex items-center gap-1">
            <i class="fa fa-shield text-emerald-600"></i> All 33 Excel format fields supported
        </span>
        <div class="flex items-center gap-2 ml-auto">
            <button type="button" id="btn-cancel-lead-drawer" class="px-4 py-2 text-sm bg-white border border-gray-200 text-gray-700 rounded-xl hover:bg-gray-100 font-medium transition-colors">
                Cancel
            </button>
            <button type="button" class="px-5 py-2 text-sm bg-emerald-600 text-white rounded-xl hover:bg-emerald-700 font-semibold shadow-sm transition-colors flex items-center gap-1.5" id="btn-save-lead">
                <i class="fa fa-save"></i> Save Lead
            </button>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- RIGHT-SIDE DRAWER FOR PREVIEW / QUICK VIEW (ALL 33 FIELDS)                -->
<!-- ========================================================================= -->
<div id="view-drawer-backdrop" class="fixed inset-0 bg-slate-900/50 backdrop-blur-xs z-[9998] transition-opacity duration-300 opacity-0 pointer-events-none"></div>

<div id="view-drawer" class="fixed top-0 right-0 h-full w-full sm:w-[600px] md:w-[720px] lg:w-[780px] bg-slate-50 z-[9999] shadow-2xl transform translate-x-full transition-transform duration-300 ease-in-out flex flex-col">
    <!-- Header / Banner -->
    <div class="bg-gradient-to-r from-slate-800 to-slate-900 px-6 py-5 text-white flex items-center justify-between flex-shrink-0 shadow-sm">
        <div class="flex items-center gap-3.5">
            <div class="w-12 h-12 rounded-2xl bg-emerald-500/20 text-emerald-400 border border-emerald-400/30 flex items-center justify-center font-bold text-lg flex-shrink-0" id="view-avatar">
                L
            </div>
            <div>
                <div class="flex items-center gap-2 flex-wrap">
                    <h3 class="text-base font-bold text-white leading-tight" id="view-name">Lead Details</h3>
                    <span id="view-stage-badge"></span>
                    <span id="view-status-badge"></span>
                </div>
                <p class="text-xs text-slate-300 mt-1 flex items-center gap-2 flex-wrap">
                    <span id="view-title" class="font-medium"></span>
                    <span class="text-slate-500">•</span>
                    <span id="view-company" class="text-emerald-400 font-semibold"></span>
                </p>
            </div>
        </div>
        <button type="button" id="btn-close-view-drawer" class="w-8 h-8 rounded-lg hover:bg-white/10 text-white/80 hover:text-white flex items-center justify-center transition-colors">
            <i class="fa fa-times text-lg"></i>
        </button>
    </div>

    <!-- Scrollable Drawer Body with All 33 Fields -->
    <div class="flex-1 overflow-y-auto p-6 space-y-5">
        <!-- Contact & Communication Card -->
        <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm space-y-4">
            <h5 class="text-xs font-bold text-gray-800 uppercase tracking-wide flex items-center gap-2 border-b border-gray-100 pb-2.5">
                <i class="fa fa-user-circle text-emerald-600 text-sm"></i> Contact & Communication
            </h5>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
                <div>
                    <span class="text-gray-400 block font-medium mb-0.5">Primary Email:</span>
                    <div class="flex items-center gap-2">
                        <span id="view-email" class="text-sm font-semibold text-gray-800 font-mono">-</span>
                        <button type="button" id="btn-copy-email" class="text-gray-400 hover:text-emerald-600 transition-colors" title="Copy Email">
                            <i class="fa fa-copy"></i>
                        </button>
                    </div>
                </div>
                <div>
                    <span class="text-gray-400 block font-medium mb-0.5">Email Status:</span>
                    <div id="view-email-status" class="mt-0.5">-</div>
                </div>
                <div>
                    <span class="text-gray-400 block font-medium mb-0.5">Secondary Email:</span>
                    <span id="view-secondary-email" class="text-sm text-gray-700 font-mono">-</span>
                </div>
                <div>
                    <span class="text-gray-400 block font-medium mb-0.5">Corporate Phone:</span>
                    <span id="view-corporate-phone" class="text-sm font-semibold text-gray-800 font-mono">-</span>
                </div>
                <div class="md:col-span-2">
                    <span class="text-gray-400 block font-medium mb-0.5">Personal Address:</span>
                    <span id="view-address" class="text-xs text-gray-700">-</span>
                </div>
            </div>
            <!-- Social & Web Badges -->
            <div class="pt-2 border-t border-gray-50 flex flex-wrap gap-2 text-xs" id="view-social-links">
                <!-- Dynamic social badges -->
            </div>
        </div>

        <!-- Company Firmographics Card -->
        <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm space-y-4">
            <h5 class="text-xs font-bold text-gray-800 uppercase tracking-wide flex items-center gap-2 border-b border-gray-100 pb-2.5">
                <i class="fa fa-building text-blue-600 text-sm"></i> Company & Firmographics
            </h5>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-xs">
                <div>
                    <span class="text-gray-400 block font-medium mb-0.5">Company Name:</span>
                    <span id="view-company-name" class="text-sm font-bold text-gray-800">-</span>
                </div>
                <div>
                    <span class="text-gray-400 block font-medium mb-0.5"># Employees:</span>
                    <span id="view-employees" class="text-sm font-semibold text-gray-700">-</span>
                </div>
                <div>
                    <span class="text-gray-400 block font-medium mb-0.5">Industry:</span>
                    <span id="view-industry" class="text-sm font-semibold text-gray-700">-</span>
                </div>
                <div>
                    <span class="text-gray-400 block font-medium mb-0.5">Annual Revenue:</span>
                    <span id="view-revenue" class="text-sm font-bold text-emerald-600">-</span>
                </div>
                <div>
                    <span class="text-gray-400 block font-medium mb-0.5">Company Phone:</span>
                    <span id="view-company-phone" class="text-sm font-mono text-gray-700">-</span>
                </div>
                <div>
                    <span class="text-gray-400 block font-medium mb-0.5">Company LinkedIn:</span>
                    <span id="view-company-linkedin" class="text-xs">-</span>
                </div>
                <div class="md:col-span-3">
                    <span class="text-gray-400 block font-medium mb-0.5">Company Address:</span>
                    <span id="view-company-address" class="text-xs text-gray-700">-</span>
                </div>
                <div class="md:col-span-3">
                    <span class="text-gray-400 block font-medium mb-0.5">Technologies:</span>
                    <div id="view-technologies" class="flex flex-wrap gap-1 mt-1">-</div>
                </div>
                <div class="md:col-span-3">
                    <span class="text-gray-400 block font-medium mb-0.5">Keywords:</span>
                    <div id="view-keywords" class="flex flex-wrap gap-1 mt-1">-</div>
                </div>
            </div>
        </div>

        <!-- Sales Activity & Pipeline Card -->
        <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm space-y-4">
            <h5 class="text-xs font-bold text-gray-800 uppercase tracking-wide flex items-center gap-2 border-b border-gray-100 pb-2.5">
                <i class="fa fa-line-chart text-purple-600 text-sm"></i> Sales Activity & Pipeline
            </h5>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-xs">
                <div>
                    <span class="text-gray-400 block font-medium mb-0.5">Account Owner:</span>
                    <span id="view-account-owner" class="text-sm font-semibold text-gray-800">-</span>
                </div>
                <div>
                    <span class="text-gray-400 block font-medium mb-0.5">Assigned Staff:</span>
                    <span id="view-assigned-name" class="text-sm font-semibold text-gray-700">-</span>
                </div>
                <div>
                    <span class="text-gray-400 block font-medium mb-0.5">Lead Source:</span>
                    <span id="view-source" class="text-sm font-semibold text-gray-700 uppercase">-</span>
                </div>
                <div>
                    <span class="text-gray-400 block font-medium mb-0.5">Email Sent:</span>
                    <span id="view-email-sent" class="text-xs">-</span>
                </div>
                <div>
                    <span class="text-gray-400 block font-medium mb-0.5">Email Open:</span>
                    <span id="view-email-open" class="text-xs">-</span>
                </div>
                <div>
                    <span class="text-gray-400 block font-medium mb-0.5">Email Bounced:</span>
                    <span id="view-email-bounced" class="text-xs">-</span>
                </div>
                <div>
                    <span class="text-gray-400 block font-medium mb-0.5">Demo Status:</span>
                    <span id="view-demo" class="text-xs font-semibold">-</span>
                </div>
                <div>
                    <span class="text-gray-400 block font-medium mb-0.5">Quotation:</span>
                    <span id="view-quotation" class="text-xs font-semibold">-</span>
                </div>
                <div>
                    <span class="text-gray-400 block font-medium mb-0.5">Expected Deal Value:</span>
                    <span id="view-expected-value" class="text-sm font-bold text-emerald-600">-</span>
                </div>
                <div>
                    <span class="text-gray-400 block font-medium mb-0.5">Linked Product:</span>
                    <span id="view-product-badge" class="text-xs font-semibold text-emerald-800 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200 inline-flex items-center gap-1">
                        <i class="fa fa-cube text-emerald-600"></i> <span id="view-product-name">-</span>
                    </span>
                </div>
                <div class="md:col-span-3" id="view-desc-container">
                    <span class="text-gray-400 block font-medium mb-1">Description / Notes:</span>
                    <div id="view-description" class="p-3 bg-gray-50 border border-gray-100 rounded-xl text-xs text-gray-700 whitespace-pre-wrap leading-relaxed">-</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Drawer Sticky Footer -->
    <div class="bg-white border-t border-gray-200 px-6 py-3.5 flex items-center justify-between flex-shrink-0">
        <a href="#" id="view-timeline-link" class="text-xs text-blue-600 font-semibold hover:underline flex items-center gap-1">
            <i class="fa fa-history"></i> Open Full Activity Timeline
        </a>
        <div class="flex items-center gap-2">
            <button type="button" id="btn-close-view-drawer-footer" class="px-4 py-2 text-sm bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl font-medium transition-colors">
                Close
            </button>
            <button type="button" id="btn-view-to-edit" class="px-4 py-2 text-sm bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-semibold transition-colors flex items-center gap-1.5 shadow-sm">
                <i class="fa fa-pencil"></i> Edit Lead
            </button>
        </div>
    </div>
</div>


