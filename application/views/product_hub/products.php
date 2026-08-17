<!-- Page Header & Tablist Navigation -->
<div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
    <div class="flex items-center gap-3">
        <div class="w-11 h-11 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-2xl flex items-center justify-center text-white shadow-sm flex-shrink-0">
            <i class="fa fa-cubes text-lg"></i>
        </div>
        <div>
            <h1 class="text-xl font-black text-gray-900 tracking-tight">Products & Package Pricing Hub</h1>
            <nav class="text-xs text-gray-400 flex items-center gap-1.5 mt-0.5">
                <a href="<?= base_url('dashboard') ?>" class="hover:text-blue-600 transition-colors">Home</a>
                <i class="fa fa-angle-right text-[10px]"></i>
                <span class="text-gray-600">Product Hub</span>
                <i class="fa fa-angle-right text-[10px]"></i>
                <span class="text-blue-600 font-semibold">Products & Tiers</span>
            </nav>
        </div>
    </div>

    <!-- TABLIST BUTTONS BAR -->
    <div class="inline-flex p-1.5 bg-gray-100/90 rounded-2xl border border-gray-200 shadow-inner flex-wrap items-center gap-1">
        <button type="button" class="tab-btn active px-4 py-2 text-xs font-bold rounded-xl transition-all duration-200 flex items-center gap-2 text-white bg-blue-600 shadow-sm" data-tab="tab-pane-list">
            <i class="fa fa-th-list"></i>
            <span>Product List</span>
        </button>
        <button type="button" class="tab-btn px-4 py-2 text-xs font-bold rounded-xl transition-all duration-200 flex items-center gap-2 text-gray-600 hover:text-gray-900 hover:bg-white/60" data-tab="tab-pane-splits">
            <i class="fa fa-tags"></i>
            <span>Product Split</span>
        </button>
        <button type="button" class="tab-btn px-4 py-2 text-xs font-bold rounded-xl transition-all duration-200 flex items-center gap-2 text-gray-600 hover:text-gray-900 hover:bg-white/60" data-tab="tab-pane-tiers">
            <i class="fa fa-diamond"></i>
            <span>Package Tiers</span>
        </button>
        <button type="button" class="tab-btn px-4 py-2 text-xs font-bold rounded-xl transition-all duration-200 flex items-center gap-2 text-gray-600 hover:text-gray-900 hover:bg-white/60" data-tab="tab-pane-add">
            <i class="fa fa-plus-circle"></i>
            <span id="tab-add-label">Add Product</span>
        </button>
    </div>
</div>

<!-- ========================================================================= -->
<!-- TAB 1: PRODUCT LIST                                                      -->
<!-- ========================================================================= -->
<div id="tab-pane-list" class="tab-content block animate-in fade-in duration-200">
    <div class="w-full bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden flex flex-col">
        
        <!-- Table Header Toolbar -->
        <div class="p-6 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-lg font-bold text-gray-800">Products Catalogue</h2>
                <p class="text-xs text-gray-500 mt-0.5">Manage catalogue products, configure tier splits, and view interactive brochures.</p>
            </div>
            <div class="flex items-center gap-2">
                <button type="button" onclick="switchTab('tab-pane-add')" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-xl text-xs shadow-sm transition-colors flex items-center gap-1.5">
                    <i class="fa fa-plus"></i> Add New Product
                </button>
            </div>
        </div>

        <!-- Filters & Search -->
        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50 flex flex-wrap items-center gap-3 justify-between">
            <div class="relative w-full sm:w-auto flex-1 min-w-[240px] max-w-sm">
                <i class="fa fa-search absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                <input type="text" id="custom-search" class="w-full pl-9 pr-4 py-2 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 text-xs bg-white" placeholder="Search by name, SKU, or category...">
            </div>
        </div>

        <!-- DataTable Container -->
        <div class="p-0 overflow-x-auto flex-1">
            <table id="products-table" class="w-full text-left text-sm whitespace-nowrap">
                <thead class="text-[11px] text-gray-500 bg-gray-50/80 border-b border-gray-200 uppercase tracking-wider">
                    <tr>
                        <th class="px-6 py-4 font-bold">#</th>
                        <th class="px-6 py-4 font-bold">SKU</th>
                        <th class="px-6 py-4 font-bold">Product Image & Details</th>
                        <th class="px-6 py-4 font-bold">Category</th>
                        <th class="px-6 py-4 font-bold">Base Price (₹)</th>
                        <th class="px-6 py-4 font-bold">Status</th>
                        <th class="px-6 py-4 font-bold text-center">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100"></tbody>
            </table>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- TAB 2: PRODUCT SPLIT (PACKAGE TIERS MANAGEMENT)                          -->
<!-- ========================================================================= -->
<div id="tab-pane-splits" class="tab-content hidden animate-in fade-in duration-200">
    <div class="w-full bg-white rounded-3xl shadow-sm border border-gray-100 p-6 sm:p-8">
        
        <!-- Product Selector & Actions Header -->
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-5 pb-6 border-b border-gray-100">
            <div class="flex flex-col sm:flex-row sm:items-center gap-4 flex-1">
                <div class="w-full sm:w-80">
                    <label class="block text-[11px] font-extrabold uppercase tracking-wider text-gray-500 mb-1.5">Select Product to Configure Splits</label>
                    <select id="split-product-select" class="w-full px-3.5 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm font-semibold text-gray-800 focus:ring-2 focus:ring-blue-500 focus:bg-white transition-all select2">
                        <?php if(!empty($all_products)): foreach($all_products as $p): 
                            $p_img = !empty($p['logo']) ? base_url($p['logo']) : (!empty($p['image']) ? base_url($p['image']) : '');
                        ?>
                            <option value="<?= $p['id'] ?>" data-name="<?= esc_html($p['name']) ?>" data-sku="<?= esc_html($p['sku']) ?>" data-subtitle="<?= esc_html($p['subtitle'] ?? '') ?>" data-logo="<?= $p_img ?>">
                                <?= esc_html($p['name']) ?> (<?= esc_html($p['sku']) ?>)
                            </option>
                        <?php endforeach; endif; ?>
                    </select>
                </div>

                <div class="flex items-center gap-3 pt-2 sm:pt-4">
                    <div id="split-selected-logo-wrap" class="w-14 h-14 rounded-2xl bg-white border border-gray-200 shadow-sm flex items-center justify-center p-1.5 flex-shrink-0 overflow-hidden">
                        <i class="fa fa-cube text-blue-600 text-xl" id="split-logo-icon"></i>
                        <img id="split-logo-img" src="" class="w-full h-full object-contain hidden" alt="Product Image">
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="text-base font-extrabold text-gray-900" id="split-selected-title">Classwall ERP</h3>
                            <span class="px-2 py-0.5 text-[10px] font-bold rounded-lg bg-indigo-50 text-indigo-700 font-mono uppercase" id="split-selected-sku">CLS-ERP</span>
                        </div>
                        <p class="text-xs text-gray-400 mt-0.5 truncate max-w-md" id="split-selected-subtitle">Complete School Management. Simplified.</p>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center gap-2.5 flex-wrap">
                <button type="button" id="btn-add-new-split" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl shadow-sm transition-all flex items-center gap-2">
                    <i class="fa fa-plus"></i> Add Tier Split
                </button>
            </div>
        </div>

        <!-- Dynamic Tier Cards Container -->
        <div id="package-tier-cards-container" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6 pt-6">
            <!-- Loaded via AJAX -->
        </div>

    </div>
</div>

<!-- ========================================================================= -->
<!-- TAB 3: MASTER PACKAGE TIERS (BRONZE, SILVER, GOLD, PLATINUM, ENTERPRISE) -->
<!-- ========================================================================= -->
<div id="tab-pane-tiers" class="tab-content hidden animate-in fade-in duration-200">
    <div class="grid grid-cols-1 xl:grid-cols-12 gap-6">
        
        <!-- Left: Add / Edit Master Tier Form -->
        <div class="xl:col-span-4">
            <div class="w-full bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden flex flex-col h-full">
                <div class="p-6 border-b border-gray-100 flex items-center justify-between bg-gradient-to-r from-gray-50/80 to-white">
                    <div>
                        <h2 id="form-tier-title" class="text-base font-extrabold text-gray-900">Add Package Tier</h2>
                        <p class="text-xs text-gray-500 mt-0.5">Configure global tiers (Bronze, Gold, Platinum).</p>
                    </div>
                    <button type="button" class="btn-cancel-tier px-3 py-1.5 text-xs font-bold text-gray-600 bg-white hover:bg-gray-100 border border-gray-200 rounded-xl transition-colors flex items-center gap-1 shadow-sm">
                        <i class="fa fa-refresh"></i> Reset
                    </button>
                </div>
                
                <form id="tier-form" class="p-6 flex-1 flex flex-col space-y-4">
                    <input type="hidden" name="<?= $csrf_name ?>" value="<?= $csrf_hash ?>" id="tier-csrf-token">
                    <input type="hidden" name="id" id="tier-id" value="0">
                    
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1.5">Tier Name <span class="text-red-500">*</span></label>
                        <input type="text" name="name" id="tier-name" class="w-full px-3.5 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 font-medium" placeholder="e.g. Platinum, Gold, Silver" required>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1.5">Slug (Auto-generated if empty)</label>
                        <input type="text" name="slug" id="tier-slug" class="w-full px-3.5 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 font-mono" placeholder="e.g. platinum">
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1.5">Badge Color</label>
                            <select name="badge_color" id="tier-badge-color" class="w-full px-3.5 py-2.5 border border-gray-200 rounded-xl text-xs focus:ring-2 focus:ring-indigo-500 font-semibold">
                                <option value="amber">Amber / Bronze</option>
                                <option value="slate">Slate / Silver</option>
                                <option value="yellow">Yellow / Gold</option>
                                <option value="purple">Purple / Platinum</option>
                                <option value="blue">Blue / Enterprise</option>
                                <option value="emerald">Emerald / Green</option>
                                <option value="rose">Rose / Pink</option>
                                <option value="gray">Neutral Gray</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1.5">FontAwesome Icon</label>
                            <select name="icon" id="tier-icon" class="w-full px-3.5 py-2.5 border border-gray-200 rounded-xl text-xs focus:ring-2 focus:ring-indigo-500 font-semibold">
                                <option value="fa-shield">fa-shield (Bronze)</option>
                                <option value="fa-star-half-o">fa-star-half-o (Silver)</option>
                                <option value="fa-star">fa-star (Gold)</option>
                                <option value="fa-diamond">fa-diamond (Platinum)</option>
                                <option value="fa-building">fa-building (Enterprise)</option>
                                <option value="fa-crown">fa-crown</option>
                                <option value="fa-cube">fa-cube</option>
                                <option value="fa-rocket">fa-rocket</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1.5">Sort Order</label>
                            <input type="number" name="sort_order" id="tier-sort-order" class="w-full px-3.5 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 font-bold" value="1">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1.5">Status</label>
                            <select name="status" id="tier-status" class="w-full px-3.5 py-2.5 border border-gray-200 rounded-xl text-xs focus:ring-2 focus:ring-indigo-500 font-semibold">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1.5">Description</label>
                        <textarea name="description" id="tier-desc" rows="3" class="w-full px-3.5 py-2.5 border border-gray-200 rounded-xl text-xs focus:ring-2 focus:ring-indigo-500 resize-none" placeholder="Target market or scale for this tier..."></textarea>
                    </div>

                    <div class="flex justify-end gap-2.5 pt-4 border-t border-gray-100 mt-auto">
                        <button type="button" class="btn-cancel-tier px-4 py-2 text-xs font-bold text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-xl transition-colors">Cancel</button>
                        <button type="submit" id="btn-save-tier" class="px-5 py-2 text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-700 rounded-xl shadow-md transition-all flex items-center gap-1.5">
                            <i class="fa fa-save"></i> Save Tier
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Right: Master Package Tiers DataTable -->
        <div class="xl:col-span-8">
            <div class="w-full bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden flex flex-col h-full">
                <div class="p-6 border-b border-gray-100 flex items-center justify-between">
                    <div>
                        <h2 class="text-base font-extrabold text-gray-900">Master Package Tiers List</h2>
                        <p class="text-xs text-gray-500 mt-0.5">Global tier definitions used across products for pricing split configuration.</p>
                    </div>
                </div>

                <div class="p-0 overflow-x-auto flex-1">
                    <table id="tiers-table" class="w-full text-left text-sm whitespace-nowrap">
                        <thead class="text-[11px] text-gray-500 bg-gray-50/80 border-b border-gray-200 uppercase tracking-wider">
                            <tr>
                                <th class="px-6 py-4 font-bold">Order</th>
                                <th class="px-6 py-4 font-bold">Tier Badge</th>
                                <th class="px-6 py-4 font-bold">Slug</th>
                                <th class="px-6 py-4 font-bold">Description</th>
                                <th class="px-6 py-4 font-bold">Status</th>
                                <th class="px-6 py-4 font-bold text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100"></tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- ========================================================================= -->
<!-- TAB 4: ADD / EDIT PRODUCT                                                -->
<!-- ========================================================================= -->
<div id="tab-pane-add" class="tab-content hidden animate-in fade-in duration-200">
    <div class="w-full bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-6 sm:p-8 border-b border-gray-100 flex items-center justify-between bg-gradient-to-r from-gray-50/80 to-white">
            <div>
                <h2 id="form-title" class="text-xl font-extrabold text-gray-900">Add New Product</h2>
                <p class="text-xs text-gray-500 mt-1">Configure software identity, product image/logo, pricing parameters, and brochure metadata.</p>
            </div>
            <button type="button" class="btn-cancel px-4 py-2 text-xs font-bold text-gray-600 bg-white hover:bg-gray-100 border border-gray-200 rounded-xl transition-colors flex items-center gap-1.5 shadow-sm">
                <i class="fa fa-refresh"></i> Reset Form
            </button>
        </div>

        <form id="product-form" class="p-6 sm:p-8 space-y-6" enctype="multipart/form-data">
            <input type="hidden" name="<?= $csrf_name ?>" value="<?= $csrf_hash ?>" id="csrf-token">
            <input type="hidden" name="id" id="product-id" value="0">

            <!-- Section 1: Basic Info -->
            <div>
                <h3 class="text-xs font-bold uppercase tracking-wider text-blue-600 mb-4 flex items-center gap-1.5">
                    <i class="fa fa-info-circle"></i> 1. Product Identity
                </h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1.5">Product Name <span class="text-red-500">*</span></label>
                        <input type="text" name="name" class="w-full px-3.5 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all font-medium" placeholder="e.g. Classwall ERP" required>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1.5">SKU Code <span class="text-red-500">*</span></label>
                        <input type="text" name="sku" class="w-full px-3.5 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 uppercase font-mono transition-all font-medium" placeholder="e.g. CLS-ERP" required>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1.5">Tagline / Subtitle</label>
                        <input type="text" name="subtitle" class="w-full px-3.5 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 transition-all" placeholder="e.g. Complete School Management. Simplified.">
                    </div>
                </div>
            </div>

            <!-- Section 2: Logo & Branding Theme -->
            <div class="pt-5 border-t border-gray-100">
                <h3 class="text-xs font-bold uppercase tracking-wider text-purple-600 mb-4 flex items-center gap-1.5">
                    <i class="fa fa-paint-brush"></i> 2. Product Image & Brochure Branding
                </h3>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-5 items-start">
                    <!-- Product Image Upload -->
                    <div class="lg:col-span-2 bg-gray-50/70 border border-gray-200 rounded-2xl p-4">
                        <label class="block text-xs font-bold text-gray-800 mb-2 flex items-center gap-1.5">
                            <i class="fa fa-image text-purple-600"></i> Product Image / Logo Upload
                        </label>
                        <div class="flex flex-col sm:flex-row items-center gap-4">
                            <div id="logo-preview-container" class="w-20 h-20 rounded-2xl bg-white border-2 border-dashed border-gray-300 flex items-center justify-center overflow-hidden flex-shrink-0 shadow-sm p-1">
                                <i class="fa fa-image text-gray-300 text-2xl" id="logo-placeholder-icon"></i>
                                <img id="logo-preview-img" src="" class="w-full h-full object-contain hidden" alt="Product Image Preview">
                            </div>
                            <div class="flex-1 w-full">
                                <input type="file" name="logo" id="product-logo-input" accept="image/*" class="w-full text-xs text-gray-500 file:mr-3 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-purple-600 file:text-white hover:file:bg-purple-700 file:cursor-pointer file:shadow-sm">
                                <p class="text-[11px] text-gray-500 mt-1.5"><i class="fa fa-check-circle text-emerald-500"></i> Upload PNG, SVG, JPG or WEBP logo. Displayed on Catalogue and Splits.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Theme & Website -->
                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1.5">Brochure Theme Palette</label>
                            <select name="theme_color" class="w-full px-3.5 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 font-medium">
                                <option value="blue">Blue / Cyan (Education & Campus)</option>
                                <option value="amber">Amber / Orange (Construction & Contracting)</option>
                                <option value="emerald">Emerald / Green (Finance & Health)</option>
                                <option value="purple">Purple / Indigo (Tech & Enterprise SaaS)</option>
                                <option value="slate">Slate / Dark Navy (Industrial & Logistics)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1.5">Brochure Footer Website URL</label>
                            <input type="text" name="website_url" class="w-full px-3.5 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-500" placeholder="e.g. www.zazutech.in/classwall">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section 3: Pricing & Classification -->
            <div class="pt-5 border-t border-gray-100">
                <h3 class="text-xs font-bold uppercase tracking-wider text-emerald-600 mb-4 flex items-center gap-1.5">
                    <i class="fa fa-money"></i> 3. Pricing & Classification
                </h3>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1.5">Base Price (₹) <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <input type="number" step="0.01" name="price" class="w-full px-3.5 py-2.5 pr-8 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 font-bold text-gray-900" placeholder="e.g. 129999" required>
                            <i class="fa fa-rupee absolute right-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1.5">Min Price (₹)</label>
                        <div class="relative">
                            <input type="number" step="0.01" name="min_price" class="w-full px-3.5 py-2.5 pr-8 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 font-semibold text-gray-700" placeholder="e.g. 99999">
                            <i class="fa fa-rupee absolute right-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1.5">Category <span class="text-red-500">*</span></label>
                        <select name="category_id" class="w-full px-3.5 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 select2">
                            <option value="">-- Select Category --</option>
                            <?php foreach($categories as $c): ?><option value="<?= $c['id'] ?>"><?= esc_html($c['name']) ?></option><?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="mt-4">
                    <label class="block text-xs font-bold text-gray-700 mb-1.5">Product Overview Description</label>
                    <textarea name="description" rows="3" class="w-full px-3.5 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 resize-none" placeholder="Enter product overview and high-level specifications..."></textarea>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-6 border-t border-gray-100">
                <button type="button" onclick="switchTab('tab-pane-list')" class="px-6 py-2.5 text-xs font-bold text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-xl transition-colors">Cancel</button>
                <button type="button" id="btn-save-product" class="px-7 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl shadow-md transition-all text-xs flex items-center gap-2">
                    <i class="fa fa-save"></i> Save Product
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL: ADD / EDIT PACKAGE SPLIT                                          -->
<!-- ========================================================================= -->
<div id="split-modal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm hidden">
    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-lg mx-4 overflow-hidden animate-in fade-in zoom-in-95 duration-200">
        <div class="p-6 border-b border-gray-100 flex items-center justify-between bg-gray-50/80">
            <div class="flex items-center gap-2.5">
                <div class="w-9 h-9 rounded-xl bg-indigo-100 text-indigo-600 flex items-center justify-center">
                    <i class="fa fa-tags text-sm"></i>
                </div>
                <h4 id="split-modal-title" class="font-extrabold text-gray-900 text-base">Add Package Tier Split</h4>
            </div>
            <button type="button" class="btn-close-modal text-gray-400 hover:text-gray-600 p-2 rounded-xl hover:bg-gray-200 transition-colors">
                <i class="fa fa-times"></i>
            </button>
        </div>
        
        <form id="split-form" class="p-6 sm:p-8 space-y-4 max-h-[80vh] overflow-y-auto">
            <input type="hidden" name="<?= $csrf_name ?>" value="<?= $csrf_hash ?>" id="split-csrf">
            <input type="hidden" name="id" id="split-id" value="0">
            <input type="hidden" name="product_id" id="split-product-id" value="0">

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Package Tier <span class="text-red-500">*</span></label>
                    <select name="package_tier_id" id="split-tier-id" class="w-full px-3.5 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 font-semibold" required>
                        <option value="">-- Select Tier --</option>
                        <?php if(!empty($package_tiers)): foreach($package_tiers as $pt): ?>
                            <option value="<?= $pt['id'] ?>"><?= esc_html($pt['name']) ?></option>
                        <?php endforeach; endif; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Tier Subtitle</label>
                    <input type="text" name="tier_subtitle" id="split-tier-subtitle" class="w-full px-3.5 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500" placeholder="e.g. For Small Schools">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Monthly Price (₹) <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <input type="number" step="0.01" name="monthly_price" id="split-monthly-price" class="w-full px-3.5 py-2.5 pr-8 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 font-bold" placeholder="e.g. 7500" required>
                        <i class="fa fa-rupee absolute right-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Yearly Price (₹) <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <input type="number" step="0.01" name="yearly_price" id="split-yearly-price" class="w-full px-3.5 py-2.5 pr-8 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 font-bold" placeholder="e.g. 75000" required>
                        <i class="fa fa-rupee absolute right-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-3 gap-3">
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Discount Tag</label>
                    <input type="text" name="discount_label" id="split-discount-label" class="w-full px-3.5 py-2.5 border border-gray-200 rounded-xl text-xs font-semibold text-emerald-600" value="Save 17%">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Setup Fee (₹)</label>
                    <input type="number" step="0.01" name="implementation_fee" id="split-impl-fee" class="w-full px-3.5 py-2.5 border border-gray-200 rounded-xl text-xs" placeholder="e.g. 15000">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">User Limit</label>
                    <input type="text" name="user_limit" id="split-users" class="w-full px-3.5 py-2.5 border border-gray-200 rounded-xl text-xs" placeholder="e.g. Up to 100 Users">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Support Tier</label>
                    <select name="support_type" id="split-support-type" class="w-full px-3.5 py-2.5 border border-gray-200 rounded-xl text-xs font-medium">
                        <option value="Standard Support">Standard Support</option>
                        <option value="Priority Support">Priority Support</option>
                        <option value="Premium Support">Premium Support</option>
                        <option value="Dedicated Support">Dedicated Support</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Reports Scope</label>
                    <select name="reports_type" id="split-reports-type" class="w-full px-3.5 py-2.5 border border-gray-200 rounded-xl text-xs font-medium">
                        <option value="Basic Reports">Basic Reports</option>
                        <option value="Advanced Reports">Advanced Reports</option>
                        <option value="Advanced Reports & Analytics">Advanced Reports & Analytics</option>
                        <option value="Custom Reports & Analytics">Custom Reports & Analytics</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-700 mb-1">Included Features (1 per line)</label>
                <textarea name="features_included" id="split-features" rows="4" class="w-full px-3.5 py-2.5 border border-gray-200 rounded-xl text-xs font-sans focus:ring-2 focus:ring-indigo-500 resize-none" placeholder="Finance, Payroll, HRMS&#10;Asset Management, Budget&#10;Vehicle Management&#10;Parents Meeting, Grievance"></textarea>
            </div>

            <div class="flex items-center gap-2 pt-1">
                <input type="checkbox" name="is_popular" id="split-popular" value="1" class="w-4 h-4 text-indigo-600 rounded border-gray-300 focus:ring-indigo-500">
                <label for="split-popular" class="text-xs font-bold text-gray-700 cursor-pointer">Mark as Recommended / Most Popular Tier</label>
            </div>

            <div class="flex justify-end gap-2 pt-4 border-t border-gray-100">
                <button type="button" class="btn-close-modal px-4 py-2.5 text-xs font-bold text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-xl transition-colors">Cancel</button>
                <button type="submit" id="btn-save-split" class="px-6 py-2.5 text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-700 rounded-xl shadow-md transition-all flex items-center gap-1.5">
                    <i class="fa fa-save"></i> Save Tier Split
                </button>
            </div>
        </form>
    </div>
</div>

<!-- SweetAlert2 CDN -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
// ========================================================
// GLOBAL TAB SWITCHER FUNCTION WITH AJAX LOADING
// ========================================================
var table;
var tiersTable;

function switchTab(tabId, bypassReset) {
    $('.tab-content').addClass('hidden').removeClass('block');
    $('#' + tabId).removeClass('hidden').addClass('block');

    $('.tab-btn').removeClass('active text-white bg-blue-600 shadow-sm').addClass('text-gray-600 hover:text-gray-900 hover:bg-white/60');
    $(`.tab-btn[data-tab="${tabId}"]`).addClass('active text-white bg-blue-600 shadow-sm').removeClass('text-gray-600 hover:text-gray-900 hover:bg-white/60');

    // Dynamic AJAX loading per tab
    if (tabId === 'tab-pane-list') {
        if (typeof table !== 'undefined' && table) {
            table.ajax.reload(null, false);
        }
    } else if (tabId === 'tab-pane-splits') {
        refreshProductDropdownAndSplits();
    } else if (tabId === 'tab-pane-tiers') {
        if (typeof tiersTable !== 'undefined' && tiersTable) {
            tiersTable.ajax.reload(null, false);
        }
    } else if (tabId === 'tab-pane-add') {
        if (!bypassReset) {
            resetForm();
        }
    }

    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function refreshProductDropdownAndSplits() {
    $.get(BASE_URL + 'product_hub/get_active_products_ajax', function(res) {
        if (res.status === 'success' && res.data && res.data.length) {
            let currentVal = $('#split-product-select').val();
            let optionsHtml = '';
            let validSelected = false;

            res.data.forEach(function(p) {
                let isSel = (p.id == currentVal) ? 'selected' : '';
                if (isSel) validSelected = true;
                optionsHtml += `<option value="${p.id}" data-name="${p.name}" data-sku="${p.sku}" data-subtitle="${p.subtitle}" data-logo="${p.logo_url}" ${isSel}>${p.name} (${p.sku})</option>`;
            });

            $('#split-product-select').html(optionsHtml);
            if (!validSelected) {
                $('#split-product-select').val(res.data[0].id);
            }
            if ($.fn.select2) {
                $('#split-product-select').trigger('change.select2');
            }
            $('#split-product-select').trigger('change');
        } else {
            let selectedId = $('#split-product-select').val();
            if (selectedId) {
                loadProductPackageSplits(selectedId);
            }
        }
    });
}

$(function() {
    let currentSelectedProductId = $('#split-product-select').val() || 0;

    if ($.fn.select2) {
        $('.select2').select2({ width: '100%' });
    }
    
    // Initialise product split header image and details
    if ($('#split-product-select').length && $('#split-product-select').val()) {
        $('#split-product-select').trigger('change');
    }

    // Tab Button Click
    $('.tab-btn').click(function() {
        let tabId = $(this).data('tab');
        switchTab(tabId);
    });

    // Logo preview on file input change
    $('#product-logo-input').on('change', function() {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                $('#logo-preview-img').attr('src', e.target.result).removeClass('hidden');
                $('#logo-placeholder-icon').addClass('hidden');
            };
            reader.readAsDataURL(file);
        }
    });

    // ========================================================
    // TAB 1: PRODUCT LIST DATATABLE
    // ========================================================
    var table = $('#products-table').DataTable({
        processing: true, 
        serverSide: true,
        ajax: { 
            url: BASE_URL + 'product_hub/products_dt'
        },
        dom: '<"hidden"f>rt<"p-6 flex items-center justify-between border-t border-gray-100"i<"flex items-center gap-2"p>>',
        columns: [
            {data: 0, className: 'px-6 py-4 text-gray-500 align-middle text-sm'},
            {data: 1, className: 'px-6 py-4 text-gray-500 align-middle text-sm uppercase font-mono'},
            {data: 2, className: 'px-6 py-4 align-middle'},
            {data: 3, className: 'px-6 py-4 text-gray-500 align-middle text-sm font-medium'},
            {data: 4, className: 'px-6 py-4 text-gray-900 align-middle text-sm font-extrabold'},
            {data: 5, className: 'px-6 py-4 align-middle'},
            {data: 6, orderable: false, className: 'px-6 py-4 align-middle text-center'}
        ],
        order: [[0, 'desc']],
        drawCallback: function() {
            $('.dataTables_paginate > .pagination').addClass('flex items-center gap-1');
            $('.dataTables_paginate .paginate_button').addClass('w-8 h-8 flex items-center justify-center bg-white border border-gray-200 text-gray-600 rounded-xl hover:bg-gray-50 text-xs font-bold cursor-pointer');
            $('.dataTables_paginate .paginate_button.current').addClass('bg-blue-600 border-blue-600 text-white hover:bg-blue-700').removeClass('bg-white text-gray-600');
            $('.dataTables_paginate .paginate_button.disabled').addClass('opacity-50 cursor-not-allowed hover:bg-white');
            
            let infoText = $('.dataTables_info').text();
            $('.dataTables_info').html(`<span class="text-xs font-semibold text-gray-400">${infoText}</span>`);
        }
    });

    $('#custom-search').on('keyup', function() {
        table.search(this.value).draw();
    });

    function resetForm() {
        $('#product-form')[0].reset(); 
        $('#product-id').val(0);
        $('#logo-preview-img').attr('src', '').addClass('hidden');
        $('#logo-placeholder-icon').removeClass('hidden');
        if ($.fn.select2) {
            $('#product-form [name=category_id]').val('').trigger('change');
        }
        $('#form-title').text('Add New Product');
        $('#tab-add-label').text('Add Product');
        $('#btn-save-product').html('<i class="fa fa-save"></i> Save Product');
    }

    $('.btn-cancel').click(function(e){
        e.preventDefault();
        resetForm();
    });

    // Edit Product from Table -> Switches to Tab 4
    $(document).on('click', '.btn-edit-product', function(){
        var id = $(this).data('id');
        $.get(BASE_URL+'product_hub/get_product/'+id, function(res){
            if (res.status !== 'success') { 
                Swal.fire('Error!', res.message||'Failed to load data.', 'error'); 
                return; 
            }
            var d = res.data;
            $('#product-form')[0].reset();
            $('#product-id').val(d.id);
            $('#product-form [name=name]').val(d.name||'');
            $('#product-form [name=sku]').val(d.sku||'');
            $('#product-form [name=subtitle]').val(d.subtitle||'');
            $('#product-form [name=theme_color]').val(d.theme_color||'blue');
            $('#product-form [name=website_url]').val(d.website_url||'');
            $('#product-form [name=price]').val(d.price||'');
            $('#product-form [name=min_price]').val(d.min_price||'');
            $('#product-form [name=description]').val(d.description||'');

            if (d.logo_url) {
                $('#logo-preview-img').attr('src', d.logo_url).removeClass('hidden');
                $('#logo-placeholder-icon').addClass('hidden');
            } else {
                $('#logo-preview-img').attr('src', '').addClass('hidden');
                $('#logo-placeholder-icon').removeClass('hidden');
            }
            
            if ($.fn.select2) {
                $('#product-form [name=category_id]').val(d.category_id||'').trigger('change');
            } else {
                $('#product-form [name=category_id]').val(d.category_id||'');
            }

            $('#form-title').text('Edit Product: ' + d.name);
            $('#tab-add-label').text('Edit Product');
            $('#btn-save-product').html('<i class="fa fa-save"></i> Update Product');
            
            switchTab('tab-pane-add', true);
        });
    });

    // Delete Product
    $(document).on('click', '.btn-product-status', function(){
        var id = $(this).data('id');
        var action = $(this).data('action');
        let csrf_name = '<?= $csrf_name ?>';
        let csrf_hash = $('#csrf-token').val();

        if (action === 'delete') {
            Swal.fire({
                title: 'Delete Product?',
                text: "All associated package splits will also be removed.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                confirmButtonText: 'Yes, delete it'
            }).then((result) => {
                if (result.isConfirmed) {
                    let data = { id: id, action: action };
                    data[csrf_name] = csrf_hash;
                    
                    $.post(BASE_URL+'product_hub/product_status', data, function(res){
                        if(res.status === 'success') {
                            Swal.fire('Deleted!', res.message, 'success');
                            table.ajax.reload(null, false);
                        } else {
                            Swal.fire('Error!', res.message, 'error');
                        }
                    });
                }
            });
        }
    });

    // Save Product Submit
    $('#btn-save-product').click(function(){
        let formObj = document.getElementById('product-form');
        if (!formObj.checkValidity()) {
            formObj.reportValidity();
            return;
        }

        var $btn=$(this); 
        let ogText = $btn.html();
        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Saving...');
        
        let formData = new FormData(formObj);

        $.ajax({
            url: BASE_URL+'product_hub/save_product',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(res){
                if(res.status==='success') {
                    Swal.fire('Success!', res.message, 'success');
                    resetForm();
                    table.ajax.reload(null,false);
                    switchTab('tab-pane-list');
                } else {
                    let errorMsg = res.message;
                    if(res.errors) {
                        let errArr = [];
                        for(let key in res.errors) {
                            errArr.push(res.errors[key]);
                        }
                        if(errArr.length > 0) errorMsg = errArr.join('<br>');
                    }
                    Swal.fire('Validation Error', errorMsg, 'error');
                }
            },
            error: function(xhr) {
                let res = xhr.responseJSON;
                Swal.fire('Error!', res && res.message ? res.message : 'An error occurred.', 'error');
            },
            complete: function(){ 
                $btn.prop('disabled', false).html(ogText);
            }
        });
    });

    // ========================================================
    // TAB 2: PRODUCT PACKAGE SPLIT LOGICS & PRODUCT SWITCHER
    // ========================================================

    // Switch to Splits tab when clicking "Splits" on Product List Table
    $(document).on('click', '.btn-manage-splits', function() {
        let prodId = $(this).data('id');
        currentSelectedProductId = prodId;
        $('#split-product-select').val(prodId).trigger('change');
        switchTab('tab-pane-splits');
    });

    // When changing product in the Splits tab dropdown
    $('#split-product-select').on('change', function() {
        let opt = $(this).find(':selected');
        currentSelectedProductId = $(this).val();
        let name = opt.data('name') || opt.text();
        let sku = opt.data('sku') || '';
        let subtitle = opt.data('subtitle') || '';
        let logo = opt.data('logo') || '';

        $('#split-selected-title').text(name);
        $('#split-selected-sku').text(sku);
        $('#split-selected-subtitle').text(subtitle || 'Enterprise Software Package Splits');

        if (logo) {
            $('#split-logo-img').attr('src', logo).removeClass('hidden');
            $('#split-logo-icon').addClass('hidden');
        } else {
            $('#split-logo-img').attr('src', '').addClass('hidden');
            $('#split-logo-icon').removeClass('hidden');
        }

        loadProductPackageSplits(currentSelectedProductId);
    });

    // Load Tier Cards for Selected Product
    function loadProductPackageSplits(productId) {
        let container = $('#package-tier-cards-container');
        container.html('<div class="col-span-full text-center py-12 text-gray-400"><i class="fa fa-spinner fa-spin text-3xl"></i><p class="text-xs mt-3 font-semibold">Loading Package Splits...</p></div>');

        $.get(BASE_URL + 'product_hub/get_package_splits/' + productId, function(res) {
            if (res.status !== 'success' || !res.data || !res.data.length) {
                container.html(`
                    <div class="col-span-full border-2 border-dashed border-gray-200 rounded-3xl p-10 text-center bg-gray-50/50">
                        <div class="w-14 h-14 bg-indigo-50 text-indigo-600 rounded-2xl flex items-center justify-center mx-auto mb-3 text-2xl shadow-sm">
                            <i class="fa fa-tags"></i>
                        </div>
                        <h4 class="font-extrabold text-gray-900 text-base">No Package Tiers Configured Yet</h4>
                        <p class="text-xs text-gray-500 mt-1 max-w-md mx-auto">Split this product into Basic, Diamond, Platinum, or Enterprise package tiers with monthly/yearly pricing and custom feature bundles.</p>
                        <button type="button" class="btn-open-add-split mt-5 px-5 py-2.5 bg-blue-600 text-white rounded-xl text-xs font-bold hover:bg-blue-700 shadow-md transition-all">
                            <i class="fa fa-plus"></i> Configure First Tier Split
                        </button>
                    </div>
                `);
                return;
            }

            let html = '';
            res.data.forEach(function(s) {
                let badgeColor = s.badge_color || 'indigo';
                let isPop = s.is_popular == 1 ? '<span class="absolute -top-3 right-5 bg-gradient-to-r from-amber-500 to-yellow-500 text-white text-[10px] font-black px-3 py-1 rounded-full shadow-md uppercase tracking-wider">POPULAR</span>' : '';
                
                let featuresList = '';
                if (s.features_array && s.features_array.length) {
                    s.features_array.forEach(function(f) {
                        featuresList += `<li class="flex items-start gap-2 text-xs text-gray-600"><i class="fa fa-check-circle text-emerald-500 text-xs mt-0.5 flex-shrink-0"></i> <span class="leading-tight">${f}</span></li>`;
                    });
                } else {
                    featuresList = '<li class="text-xs text-gray-400 italic">Standard core module access</li>';
                }

                html += `
                <div class="relative bg-white border ${s.is_popular == 1 ? 'border-indigo-500 ring-2 ring-indigo-100 shadow-lg' : 'border-gray-200'} rounded-3xl p-6 flex flex-col justify-between hover:shadow-xl transition-all">
                    ${isPop}
                    <div>
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center gap-2.5">
                                <div class="w-10 h-10 rounded-xl bg-${badgeColor}-50 text-${badgeColor}-600 flex items-center justify-center border border-${badgeColor}-100">
                                    <i class="fa ${s.icon || 'fa-cube'} text-base"></i>
                                </div>
                                <div>
                                    <h4 class="font-extrabold text-gray-900 text-base">${s.tier_name}</h4>
                                    <span class="text-[11px] text-gray-400 font-bold uppercase tracking-wider">${s.tier_subtitle || s.billing_cycle}</span>
                                </div>
                            </div>
                        </div>

                        <div class="my-4 pb-3 border-b border-gray-100">
                            <div class="text-2xl sm:text-3xl font-black text-gray-900 tracking-tight">${s.monthly_price_formatted}<span class="text-xs text-gray-400 font-semibold">/month</span></div>
                            <div class="text-xs font-bold text-gray-700 mt-1">${s.yearly_price_formatted}/year <span class="text-emerald-700 font-extrabold bg-emerald-50 px-2 py-0.5 rounded-md text-[10px] ml-1">${s.discount_label}</span></div>
                        </div>

                        <div class="py-2.5 px-3 bg-gray-50/80 rounded-2xl my-3 grid grid-cols-2 gap-2 text-center">
                            <div>
                                <div class="text-[10px] text-gray-400 uppercase font-extrabold tracking-wider">Users</div>
                                <div class="text-xs font-bold text-gray-800 truncate mt-0.5">${s.user_limit}</div>
                            </div>
                            <div>
                                <div class="text-[10px] text-gray-400 uppercase font-extrabold tracking-wider">Setup Fee</div>
                                <div class="text-xs font-bold text-gray-800 mt-0.5">${s.implementation_fee_formatted}</div>
                            </div>
                        </div>

                        <ul class="space-y-2 mt-4 mb-6">
                            ${featuresList}
                        </ul>
                    </div>

                    <div class="flex items-center gap-2 pt-4 border-t border-gray-100">
                        <button type="button" class="btn-edit-split flex-1 py-2 text-xs font-bold text-indigo-600 bg-indigo-50 hover:bg-indigo-100 rounded-xl transition-colors flex items-center justify-center gap-1.5" data-id="${s.id}">
                            <i class="fa fa-pencil"></i> Edit Tier
                        </button>
                        <button type="button" class="btn-delete-split px-3.5 py-2 text-xs font-bold text-red-600 bg-red-50 hover:bg-red-100 rounded-xl transition-colors" data-id="${s.id}" title="Delete Tier">
                            <i class="fa fa-trash"></i>
                        </button>
                    </div>
                </div>
                `;
            });

            container.html(html);
        });
    }

    // Modal Add Trigger
    $(document).on('click', '#btn-add-new-split, .btn-open-add-split', function() {
        $('#split-form')[0].reset();
        $('#split-id').val(0);
        $('#split-product-id').val(currentSelectedProductId);
        $('#split-modal-title').text('Add Package Tier Split');
        $('#split-modal').removeClass('hidden');
    });

    // Modal Edit Trigger
    $(document).on('click', '.btn-edit-split', function() {
        let splitId = $(this).data('id');
        $.get(BASE_URL + 'product_hub/get_single_package_split/' + splitId, function(res) {
            if (res.status !== 'success') return;
            let d = res.data;
            $('#split-id').val(d.id);
            $('#split-product-id').val(d.product_id);
            $('#split-tier-id').val(d.package_tier_id);
            $('#split-tier-subtitle').val(d.tier_subtitle);
            $('#split-monthly-price').val(d.monthly_price);
            $('#split-yearly-price').val(d.yearly_price);
            $('#split-discount-label').val(d.discount_label);
            $('#split-impl-fee').val(d.implementation_fee);
            $('#split-users').val(d.user_limit);
            $('#split-support-type').val(d.support_type);
            $('#split-reports-type').val(d.reports_type);
            $('#split-features').val(d.features_included);
            $('#split-popular').prop('checked', d.is_popular == 1);

            $('#split-modal-title').text('Edit ' + d.tier_name + ' Tier Split');
            $('#split-modal').removeClass('hidden');
        });
    });

    // Submit Split Form
    $('#split-form').on('submit', function(e) {
        e.preventDefault();
        let btn = $('#btn-save-split');
        let ogHtml = btn.html();
        btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Saving...');

        $.ajax({
            url: BASE_URL + 'product_hub/save_package_split',
            type: 'POST',
            data: $(this).serialize(),
            success: function(res) {
                if (res.status === 'success') {
                    Swal.fire('Saved!', res.message, 'success');
                    $('#split-modal').addClass('hidden');
                    loadProductPackageSplits(currentSelectedProductId);
                } else {
                    Swal.fire('Error', res.message || 'Validation failed', 'error');
                }
            },
            error: function() {
                Swal.fire('Error', 'Server connection failed', 'error');
            },
            complete: function() {
                btn.prop('disabled', false).html(ogHtml);
            }
        });
    });

    // Delete Split
    $(document).on('click', '.btn-delete-split', function() {
        let splitId = $(this).data('id');
        let csrfName = '<?= $csrf_name ?>';
        let csrfHash = $('#split-csrf').val();

        Swal.fire({
            title: 'Delete this Tier Split?',
            text: 'This package pricing tier will be permanently removed from this product.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            confirmButtonText: 'Yes, delete'
        }).then((result) => {
            if (result.isConfirmed) {
                let postData = { id: splitId };
                postData[csrfName] = csrfHash;
                $.post(BASE_URL + 'product_hub/delete_package_split', postData, function(res) {
                    if (res.status === 'success') {
                        Swal.fire('Deleted!', res.message, 'success');
                        loadProductPackageSplits(currentSelectedProductId);
                    } else {
                        Swal.fire('Error', res.message, 'error');
                    }
                });
            }
        });
    });

    // ========================================================
    // TAB 3: MASTER PACKAGE TIERS DATATABLE & FORM
    // ========================================================
    tiersTable = $('#tiers-table').DataTable({
        processing: true, 
        serverSide: true,
        ajax: { 
            url: BASE_URL + 'product_hub/package_tiers_dt'
        },
        dom: '<"hidden"f>rt<"p-6 flex items-center justify-between border-t border-gray-100"i<"flex items-center gap-2"p>>',
        columns: [
            {data: 0, className: 'px-6 py-4 font-bold text-gray-800 align-middle text-sm'},
            {data: 1, className: 'px-6 py-4 align-middle'},
            {data: 2, className: 'px-6 py-4 text-gray-500 font-mono text-xs align-middle'},
            {data: 3, className: 'px-6 py-4 text-gray-600 align-middle text-xs'},
            {data: 4, className: 'px-6 py-4 align-middle'},
            {data: 5, orderable: false, className: 'px-6 py-4 align-middle text-center'}
        ],
        order: [[0, 'asc']],
        drawCallback: function() {
            $('#tiers-table_paginate > .pagination').addClass('flex items-center gap-1');
            $('#tiers-table_paginate .paginate_button').addClass('w-8 h-8 flex items-center justify-center bg-white border border-gray-200 text-gray-600 rounded-xl hover:bg-gray-50 text-xs font-bold cursor-pointer');
            $('#tiers-table_paginate .paginate_button.current').addClass('bg-indigo-600 border-indigo-600 text-white hover:bg-indigo-700').removeClass('bg-white text-gray-600');
        }
    });

    function resetTierForm() {
        $('#tier-form')[0].reset();
        $('#tier-id').val(0);
        $('#form-tier-title').text('Add Package Tier');
        $('#btn-save-tier').html('<i class="fa fa-save"></i> Save Tier');
    }

    $('.btn-cancel-tier').click(function(e) {
        e.preventDefault();
        resetTierForm();
    });

    $('#tier-form').on('submit', function(e) {
        e.preventDefault();
        let btn = $('#btn-save-tier');
        let ogHtml = btn.html();
        btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Saving...');

        $.ajax({
            url: BASE_URL + 'product_hub/save_package_tier',
            type: 'POST',
            data: $(this).serialize(),
            success: function(res) {
                if (res.status === 'success') {
                    Swal.fire('Saved!', res.message, 'success');
                    resetTierForm();
                    tiersTable.ajax.reload(null, false);
                    reloadTierDropdowns();
                } else {
                    Swal.fire('Error', res.message || 'Validation failed', 'error');
                }
            },
            error: function() {
                Swal.fire('Error', 'Server connection failed', 'error');
            },
            complete: function() {
                btn.prop('disabled', false).html(ogHtml);
            }
        });
    });

    $(document).on('click', '.btn-edit-tier', function() {
        let id = $(this).data('id');
        $.get(BASE_URL + 'product_hub/get_package_tier/' + id, function(res) {
            if (res.status !== 'success') return;
            let d = res.data;
            $('#tier-id').val(d.id);
            $('#tier-name').val(d.name);
            $('#tier-slug').val(d.slug);
            $('#tier-badge-color').val(d.badge_color);
            $('#tier-icon').val(d.icon);
            $('#tier-sort-order').val(d.sort_order);
            $('#tier-status').val(d.status);
            $('#tier-desc').val(d.description);

            $('#form-tier-title').text('Edit ' + d.name + ' Tier');
            $('#btn-save-tier').html('<i class="fa fa-save"></i> Update Tier');
        });
    });

    $(document).on('click', '.btn-tier-status', function() {
        let id = $(this).data('id');
        let action = $(this).data('action');
        let csrfName = '<?= $csrf_name ?>';
        let csrfHash = $('#tier-csrf-token').val();

        let postData = { id: id, action: action };
        postData[csrfName] = csrfHash;

        if (action === 'delete') {
            Swal.fire({
                title: 'Delete Package Tier?',
                text: 'This will remove this master tier template.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                confirmButtonText: 'Yes, delete'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.post(BASE_URL + 'product_hub/package_tier_status', postData, function(res) {
                        if (res.status === 'success') {
                            Swal.fire('Deleted!', res.message, 'success');
                            tiersTable.ajax.reload(null, false);
                            reloadTierDropdowns();
                        } else {
                            Swal.fire('Error', res.message, 'error');
                        }
                    });
                }
            });
        } else {
            $.post(BASE_URL + 'product_hub/package_tier_status', postData, function(res) {
                if (res.status === 'success') {
                    tiersTable.ajax.reload(null, false);
                    reloadTierDropdowns();
                } else {
                    Swal.fire('Error', res.message, 'error');
                }
            });
        }
    });

    function reloadTierDropdowns() {
        $.get(BASE_URL + 'product_hub/get_active_tiers', function(res) {
            if (res.status === 'success' && res.data) {
                let options = '<option value="">-- Select Tier --</option>';
                res.data.forEach(function(t) {
                    options += `<option value="${t.id}">${t.name}</option>`;
                });
                $('#split-tier-id').html(options);
            }
        });
    }

    // Modal Close handler
    $('.btn-close-modal').click(function() {
        $('#split-modal').addClass('hidden');
    });
});
</script>
