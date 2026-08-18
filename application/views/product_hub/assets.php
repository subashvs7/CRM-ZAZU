<!-- Page Header & Tablist Navigation -->
<div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
    <div class="flex items-center gap-3">
        <div class="w-11 h-11 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-2xl flex items-center justify-center text-white shadow-sm flex-shrink-0">
            <i class="fa fa-folder-open text-lg"></i>
        </div>
        <div>
            <h1 class="text-xl font-black text-gray-900 tracking-tight">Assets & Product Collaterals</h1>
            <nav class="text-xs text-gray-400 flex items-center gap-1.5 mt-0.5">
                <a href="<?= base_url('dashboard') ?>" class="hover:text-blue-600 transition-colors">Home</a>
                <i class="fa fa-angle-right text-[10px]"></i>
                <span class="text-gray-600">Product Hub</span>
                <i class="fa fa-angle-right text-[10px]"></i>
                <span class="text-blue-600 font-semibold">Assets & Demos</span>
            </nav>
        </div>
    </div>

    <!-- TABLIST BUTTONS BAR -->
    <div class="inline-flex p-1.5 bg-gray-100/90 rounded-2xl border border-gray-200 shadow-inner flex-wrap items-center gap-1">
        <button type="button" class="tab-btn active px-4 py-2 text-xs font-bold rounded-xl transition-all duration-200 flex items-center gap-2 text-white bg-blue-600 shadow-sm" data-tab="tab-pane-list">
            <i class="fa fa-th-list"></i>
            <span>Assets List</span>
        </button>
        <button type="button" class="tab-btn px-4 py-2 text-xs font-bold rounded-xl transition-all duration-200 flex items-center gap-2 text-gray-600 hover:text-gray-900 hover:bg-white/60" data-tab="tab-pane-add">
            <i class="fa fa-cloud-upload"></i>
            <span id="tab-add-label">Add Asset</span>
        </button>
    </div>
</div>

<!-- ========================================================================= -->
<!-- TAB 1: ASSETS LIST                                                       -->
<!-- ========================================================================= -->
<div id="tab-pane-list" class="tab-content block animate-in fade-in duration-200">
    <div class="w-full bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden flex flex-col">
        
        <!-- Header Toolbar -->
        <div class="p-6 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-gradient-to-r from-gray-50/80 to-white">
            <div>
                <h2 class="text-lg font-extrabold text-gray-900">Assets & Collaterals Catalogue</h2>
                <p class="text-xs text-gray-500 mt-0.5">Browse product brochures, marketing videos, specification sheets, and presentations.</p>
            </div>
            <div class="flex items-center gap-2">
                <button type="button" onclick="switchTab('tab-pane-add')" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl text-xs shadow-sm transition-colors flex items-center gap-1.5">
                    <i class="fa fa-plus"></i> Upload New Asset
                </button>
            </div>
        </div>

        <!-- Filter Chips Bar -->
        <div class="px-6 border-b border-gray-100 flex items-center gap-2 overflow-x-auto py-3 bg-white">
            <button type="button" data-type="All" id="tab-all" class="type-tab px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all bg-blue-50 text-blue-700 border border-blue-200 shadow-xs whitespace-nowrap">
                All (0)
            </button>
            <button type="button" data-type="Documents" id="tab-documents" class="type-tab px-3.5 py-1.5 rounded-xl text-xs font-semibold text-gray-600 hover:text-gray-900 hover:bg-gray-100 transition-all border border-transparent whitespace-nowrap">
                Documents (0)
            </button>
            <button type="button" data-type="Videos" id="tab-videos" class="type-tab px-3.5 py-1.5 rounded-xl text-xs font-semibold text-gray-600 hover:text-gray-900 hover:bg-gray-100 transition-all border border-transparent whitespace-nowrap">
                Videos (0)
            </button>
            <button type="button" data-type="Images" id="tab-images" class="type-tab px-3.5 py-1.5 rounded-xl text-xs font-semibold text-gray-600 hover:text-gray-900 hover:bg-gray-100 transition-all border border-transparent whitespace-nowrap">
                Images (0)
            </button>
            <button type="button" data-type="Presentations" id="tab-presentations" class="type-tab px-3.5 py-1.5 rounded-xl text-xs font-semibold text-gray-600 hover:text-gray-900 hover:bg-gray-100 transition-all border border-transparent whitespace-nowrap">
                Presentations (0)
            </button>
        </div>

        <!-- Search & Dropdown Filter Bar -->
        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50 flex flex-wrap items-center gap-3 justify-between">
            <div class="relative w-full sm:w-80">
                <i class="fa fa-search absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                <input type="text" id="custom-search" class="w-full pl-9 pr-4 py-2 bg-white border border-gray-200 rounded-xl text-xs focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all font-medium placeholder-gray-400" placeholder="Search by asset title, product, type...">
            </div>

            <div class="flex items-center gap-2.5 w-full sm:w-auto">
                <select id="filter-type" class="px-3.5 py-2 bg-white border border-gray-200 text-gray-700 rounded-xl text-xs font-semibold focus:ring-2 focus:ring-blue-500">
                    <option value="All Types">All Asset Types</option>
                    <option value="Documents">Documents & PDF</option>
                    <option value="Videos">Videos & Demos</option>
                    <option value="Images">Images & Graphics</option>
                    <option value="Presentations">Presentations & PPT</option>
                </select>
                <button type="button" id="btn-refresh-table" class="p-2 bg-white border border-gray-200 text-gray-600 hover:text-blue-600 rounded-xl text-xs font-bold transition-colors shadow-xs" title="Refresh Table">
                    <i class="fa fa-refresh"></i>
                </button>
            </div>
        </div>

        <!-- Assets Table -->
        <div class="p-0 overflow-x-auto flex-1">
            <table id="assets-table" class="w-full text-left text-xs whitespace-nowrap">
                <thead class="text-[11px] uppercase tracking-wider text-gray-500 bg-gray-50/70 border-b border-gray-200 font-bold">
                    <tr>
                        <th class="px-6 py-3.5">#</th>
                        <th class="px-6 py-3.5">Asset Title</th>
                        <th class="px-6 py-3.5">Product & SKU</th>
                        <th class="px-6 py-3.5">Type</th>
                        <th class="px-6 py-3.5">Uploaded By</th>
                        <th class="px-6 py-3.5">Date</th>
                        <th class="px-6 py-3.5 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100"></tbody>
            </table>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- TAB 2: ADD / EDIT ASSET                                                  -->
<!-- ========================================================================= -->
<div id="tab-pane-add" class="tab-content hidden animate-in fade-in duration-200">
    <div class="w-full bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-6 sm:p-8 border-b border-gray-100 flex items-center justify-between bg-gradient-to-r from-gray-50/80 to-white">
            <div>
                <h2 id="form-title" class="text-xl font-extrabold text-gray-900">Upload New Asset</h2>
                <p class="text-xs text-gray-500 mt-1">Upload software brochures, product demo videos, screenshots, or specification documents.</p>
            </div>
            <button type="button" class="btn-cancel px-4 py-2 text-xs font-bold text-gray-600 bg-white hover:bg-gray-100 border border-gray-200 rounded-xl transition-colors flex items-center gap-1.5 shadow-sm">
                <i class="fa fa-refresh"></i> Reset Form
            </button>
        </div>

        <form id="upload-form" action="<?= base_url('product_hub/process_upload') ?>" method="POST" enctype="multipart/form-data" class="p-6 sm:p-8 space-y-6">
            <input type="hidden" name="<?= $csrf_name ?>" value="<?= $csrf_hash ?>" id="csrf-token">
            <input type="hidden" name="id" id="asset_id" value="0">

            <!-- Section 1: Classification & Identity -->
            <div>
                <h3 class="text-xs font-bold uppercase tracking-wider text-blue-600 mb-4 flex items-center gap-1.5">
                    <i class="fa fa-cube"></i> 1. Product & Asset Classification
                </h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1.5">Associated Product <span class="text-gray-400 font-normal">(Optional)</span></label>
                        <select name="product_id" id="asset-product-id" class="w-full px-3.5 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 select2">
                            <option value="">-- General / No Specific Product --</option>
                            <?php foreach($products as $p): ?>
                                <option value="<?= $p['id'] ?>" data-logo="<?= !empty($p['logo']) ? base_url(ltrim($p['logo'],'/')) : '' ?>"><?= esc_html($p['name']) ?> (<?= esc_html($p['sku'] ?? 'PROD') ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1.5">Asset Type <span class="text-red-500">*</span></label>
                        <select name="asset_type" id="asset-type-select" class="w-full px-3.5 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 font-semibold text-gray-800" required>
                            <option value="">-- Select Type --</option>
                            <option value="Document">Document (PDF / Docs)</option>
                            <option value="Brochure">Brochure</option>
                            <option value="Video">Video (Demo / Walkthrough)</option>
                            <option value="Image">Image (UI Screenshot / Graphic)</option>
                            <option value="Presentation">Presentation (PPT / Slides)</option>
                            <option value="Link">External Link / Web URL</option>
                        </select>
                    </div>

                    <div class="sm:col-span-2 lg:col-span-1">
                        <label class="block text-xs font-bold text-gray-700 mb-1.5">Asset Title <span class="text-red-500">*</span></label>
                        <input type="text" name="title" id="asset-title-input" class="w-full px-3.5 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 font-medium" placeholder="e.g. Classwall ERP Complete Feature Brochure" required>
                    </div>
                </div>
            </div>

            <!-- Section 2: Description -->
            <div class="pt-5 border-t border-gray-100">
                <label class="block text-xs font-bold text-gray-700 mb-1.5">Asset Description / Scope Summary <span class="text-gray-400 font-normal">(Optional)</span></label>
                <textarea name="description" id="asset-description-input" rows="3" class="w-full px-3.5 py-2.5 border border-gray-200 rounded-xl text-xs focus:ring-2 focus:ring-blue-500 resize-none font-normal" placeholder="Brief summary of what this collateral covers, key highlights, or target audience..."></textarea>
            </div>

            <!-- Section 3: File Upload or External URL -->
            <div class="pt-5 border-t border-gray-100">
                <h3 class="text-xs font-bold uppercase tracking-wider text-purple-600 mb-4 flex items-center gap-1.5">
                    <i class="fa fa-cloud-upload"></i> 2. File Upload or External Resource URL
                </h3>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-5 items-start">
                    <!-- File Drag & Drop Box -->
                    <div class="bg-gray-50/70 border border-gray-200 rounded-2xl p-5">
                        <label class="block text-xs font-bold text-gray-800 mb-2 flex items-center justify-between">
                            <span class="flex items-center gap-1.5"><i class="fa fa-file-text text-purple-600"></i> Upload Asset File</span>
                            <span class="text-[10px] text-gray-400 font-normal">PDF, DOC, PPT, MP4, JPG, PNG, WEBP (Max 50MB)</span>
                        </label>

                        <div class="relative border-2 border-dashed border-gray-300 hover:border-purple-500 rounded-2xl p-6 text-center hover:bg-white transition-all cursor-pointer group bg-white/60">
                            <input type="file" name="asset_file" id="asset-file-input" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10">
                            <div class="w-12 h-12 rounded-2xl bg-purple-50 text-purple-600 flex items-center justify-center mx-auto mb-2 group-hover:scale-110 transition-transform">
                                <i class="fa fa-cloud-upload text-xl"></i>
                            </div>
                            <p class="text-xs font-bold text-gray-700">Drag & drop file here or <span class="text-purple-600 font-extrabold underline">browse</span></p>
                            <p id="file-name-display" class="text-xs font-semibold text-purple-600 mt-2 truncate max-w-xs mx-auto"></p>
                        </div>
                    </div>

                    <!-- External Link / URL Box -->
                    <div class="bg-gray-50/70 border border-gray-200 rounded-2xl p-5 space-y-4">
                        <label class="block text-xs font-bold text-gray-800 mb-2 flex items-center justify-between">
                            <span class="flex items-center gap-1.5"><i class="fa fa-globe text-blue-600"></i> OR External Resource URL</span>
                            <span class="text-[10px] text-gray-400 font-normal">YouTube, Vimeo, Google Drive, Loom</span>
                        </label>
                        <div>
                            <input type="url" name="link_url" id="asset-link-url" class="w-full px-3.5 py-2.5 border border-gray-200 rounded-xl text-xs focus:ring-2 focus:ring-blue-500 font-medium bg-white" placeholder="https://youtube.com/watch?v=... or https://drive.google.com/...">
                            <p class="text-[11px] text-gray-400 mt-1.5"><i class="fa fa-info-circle text-blue-500"></i> Use for online demo video streams or interactive cloud presentations.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center justify-end gap-3 pt-6 border-t border-gray-100">
                <button type="button" onclick="switchTab('tab-pane-list')" class="px-6 py-2.5 text-xs font-bold text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-xl transition-colors">
                    Cancel
                </button>
                <button type="submit" id="btn-save-asset" class="px-7 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl shadow-md transition-all text-xs flex items-center gap-2">
                    <i class="fa fa-save"></i> <span id="btn-submit-label">Upload Asset</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Hidden image element container for Viewer.js -->
<div id="viewer-container" class="hidden">
    <img id="viewer-target-image" src="" alt="Viewer Target">
</div>

<!-- Viewer.js CDN -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/viewerjs/1.11.6/viewer.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/viewerjs/1.11.6/viewer.min.js"></script>

<script>
// ========================================================
// GLOBAL TAB SWITCHER
// ========================================================
var assetsTable;

function switchTab(tabId, bypassReset) {
    $('.tab-content').addClass('hidden').removeClass('block');
    $('#' + tabId).removeClass('hidden').addClass('block');

    $('.tab-btn').removeClass('active text-white bg-blue-600 shadow-sm').addClass('text-gray-600 hover:text-gray-900 hover:bg-white/60');
    $(`.tab-btn[data-tab="${tabId}"]`).addClass('active text-white bg-blue-600 shadow-sm').removeClass('text-gray-600 hover:text-gray-900 hover:bg-white/60');

    if (tabId === 'tab-pane-list') {
        if (typeof assetsTable !== 'undefined' && assetsTable) {
            assetsTable.ajax.reload(null, false);
        }
        loadCounts();
    } else if (tabId === 'tab-pane-add') {
        if (!bypassReset) {
            resetAssetForm();
        }
    }

    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function resetAssetForm() {
    $('#upload-form')[0].reset();
    $('#asset_id').val(0);
    $('#file-name-display').text('');
    if ($.fn.select2) {
        $('#asset-product-id').val('').trigger('change');
    }
    $('#form-title').text('Upload New Asset');
    $('#tab-add-label').text('Add Asset');
    $('#btn-submit-label').text('Upload Asset');
}

$(function() {
    let currentType = 'All';

    if ($.fn.select2) {
        $('.select2').select2({ width: '100%' });
    }

    // Tab buttons click handler
    $('.tab-btn').on('click', function() {
        let tab = $(this).data('tab');
        switchTab(tab);
    });

    // File input change
    $('#asset-file-input').on('change', function() {
        var fileName = $(this).val().split('\\').pop();
        if (fileName) {
            $('#file-name-display').html('<i class="fa fa-check-circle text-emerald-500"></i> ' + fileName);
        } else {
            $('#file-name-display').text('');
        }
    });

    // Reset button
    $('.btn-cancel').on('click', function(e) {
        e.preventDefault();
        resetAssetForm();
    });

    // ========================================================
    // DATATABLE INITIALIZATION
    // ========================================================
    assetsTable = $('#assets-table').DataTable({
        processing: true, 
        serverSide: true,
        ajax: { 
            url: BASE_URL + 'product_hub/datatable',
            data: function(d) {
                d.type = currentType;
            }
        },
        dom: '<"hidden"f>rt<"p-6 flex items-center justify-between border-t border-gray-100"i<"flex items-center gap-2"p>>',
        columns: [
            {data: 0, className: 'px-6 py-4 text-gray-500 font-mono align-middle text-xs font-semibold'},
            {
                data: 1, 
                className: 'px-6 py-4 align-middle',
                render: function(data, type, row) {
                    let assetType = row[9] || '';
                    let desc = row[10] || '';
                    let iconClass = 'fa-file-text text-blue-500';
                    let iconBg = 'bg-blue-50 border-blue-100 text-blue-600';

                    if (assetType === 'Document' || assetType === 'Brochure') {
                        iconClass = 'fa-file-pdf-o text-red-500';
                        iconBg = 'bg-red-50 border-red-100 text-red-600';
                    } else if (assetType === 'Video') {
                        iconClass = 'fa-play-circle text-emerald-500';
                        iconBg = 'bg-emerald-50 border-emerald-100 text-emerald-600';
                    } else if (assetType === 'Image') {
                        iconClass = 'fa-image text-amber-500';
                        iconBg = 'bg-amber-50 border-amber-100 text-amber-600';
                    } else if (assetType === 'Presentation' || assetType === 'PPT') {
                        iconClass = 'fa-file-powerpoint-o text-purple-500';
                        iconBg = 'bg-purple-50 border-purple-100 text-purple-600';
                    } else if (assetType === 'Link') {
                        iconClass = 'fa-globe text-cyan-500';
                        iconBg = 'bg-cyan-50 border-cyan-100 text-cyan-600';
                    }

                    return `
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl flex items-center justify-center border shadow-2xs flex-shrink-0 ${iconBg}">
                            <i class="fa ${iconClass} text-sm"></i>
                        </div>
                        <div class="min-w-0 max-w-xs">
                            <div class="font-bold text-gray-900 text-xs truncate">${data}</div>
                            ${desc ? `<div class="text-[11px] text-gray-400 truncate mt-0.5">${desc}</div>` : ''}
                        </div>
                    </div>`;
                }
            },
            {data: 2, className: 'px-6 py-4 align-middle'},
            {
                data: 3, 
                className: 'px-6 py-4 align-middle',
                render: function(data) {
                    let typeClass = 'bg-blue-50 text-blue-700 border-blue-200';
                    if (data === 'Document' || data === 'Brochure') typeClass = 'bg-red-50 text-red-700 border-red-200';
                    else if (data === 'Video') typeClass = 'bg-emerald-50 text-emerald-700 border-emerald-200';
                    else if (data === 'Image') typeClass = 'bg-amber-50 text-amber-700 border-amber-200';
                    else if (data === 'Presentation' || data === 'PPT') typeClass = 'bg-purple-50 text-purple-700 border-purple-200';
                    else if (data === 'Link') typeClass = 'bg-cyan-50 text-cyan-700 border-cyan-200';

                    return `<span class="px-2.5 py-1 text-[10px] font-bold rounded-lg border uppercase tracking-wider ${typeClass}">${data}</span>`;
                }
            },
            {
                data: 5, 
                className: 'px-6 py-4 align-middle font-semibold text-gray-700',
                render: function(data) {
                    return `<span class="inline-flex items-center gap-1 text-xs"><i class="fa fa-user-circle-o text-gray-400"></i> ${data}</span>`;
                }
            },
            {data: 6, className: 'px-6 py-4 text-gray-500 align-middle text-xs'},
            {
                data: 0, 
                orderable: false, 
                className: 'px-6 py-4 text-center align-middle',
                render: function(data, type, row) {
                    let assetType = row[9] || '';
                    let rawUrl = row[8] || row[4];
                    let isImage = (assetType === 'Image') || /\.(jpg|jpeg|png|webp|gif|svg)$/i.test(rawUrl);

                    let viewBtn = '';
                    if (isImage) {
                        viewBtn = `<button type="button" class="w-7 h-7 rounded-lg bg-emerald-50 text-emerald-600 hover:bg-emerald-100 flex items-center justify-center transition-colors btn-view-image" data-url="${rawUrl}" data-title="${row[1]}" title="Preview Image with Viewer.js"><i class="fa fa-eye"></i></button>`;
                    } else {
                        viewBtn = `<a href="${rawUrl}" target="_blank" class="w-7 h-7 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-100 flex items-center justify-center transition-colors" title="View / Download Resource"><i class="fa fa-eye"></i></a>`;
                    }

                    return `
                    <div class="flex items-center justify-center gap-1.5">
                        ${viewBtn}
                        <button type="button" class="w-7 h-7 rounded-lg bg-indigo-50 text-indigo-600 hover:bg-indigo-100 flex items-center justify-center transition-colors btn-edit" data-id="${data}" title="Edit Asset">
                            <i class="fa fa-pencil"></i>
                        </button>
                        <button type="button" class="w-7 h-7 rounded-lg bg-red-50 text-red-600 hover:bg-red-100 flex items-center justify-center transition-colors btn-delete" data-id="${data}" title="Delete Asset">
                            <i class="fa fa-trash"></i>
                        </button>
                    </div>`;
                }
            }
        ],
        order: [[0, 'desc']],
        drawCallback: function() {
            $('#assets-table_paginate > .pagination').addClass('flex items-center gap-1');
            $('#assets-table_paginate .paginate_button').addClass('w-8 h-8 flex items-center justify-center bg-white border border-gray-200 text-gray-600 rounded-xl hover:bg-gray-50 text-xs font-bold cursor-pointer');
            $('#assets-table_paginate .paginate_button.current').addClass('bg-blue-600 border-blue-600 text-white hover:bg-blue-700').removeClass('bg-white text-gray-600');
        }
    });

    // Custom Search
    $('#custom-search').on('keyup', function() {
        assetsTable.search(this.value).draw();
    });

    $('#btn-refresh-table').on('click', function() {
        assetsTable.ajax.reload(null, false);
        loadCounts();
    });

    // ========================================================
    // VIEWER.JS INTEGRATION FOR IMAGE PREVIEW
    // ========================================================
    var activeViewer = null;

    $(document).on('click', '.btn-view-image', function(e) {
        e.preventDefault();
        let url = $(this).data('url');
        let title = $(this).data('title') || 'Image Preview';

        if (!url) return;

        if (activeViewer) {
            activeViewer.destroy();
        }

        let $img = $('#viewer-target-image');
        $img.attr('src', url).attr('alt', title);

        let imgEl = document.getElementById('viewer-target-image');
        activeViewer = new Viewer(imgEl, {
            inline: false,
            button: true,
            navbar: false,
            title: true,
            toolbar: {
                zoomIn: 1,
                zoomOut: 1,
                oneToOne: 1,
                reset: 1,
                prev: 0,
                play: 0,
                next: 0,
                rotateLeft: 1,
                rotateRight: 1,
                flipHorizontal: 1,
                flipVertical: 1,
            },
            hidden: function () {
                activeViewer.destroy();
                activeViewer = null;
            }
        });

        activeViewer.show();
    });

    // ========================================================
    // TAB FILTER CLICKS
    // ========================================================
    $('.type-tab').on('click', function(e) {
        e.preventDefault();
        $('.type-tab').removeClass('bg-blue-50 text-blue-700 border-blue-200 shadow-xs').addClass('text-gray-600 hover:text-gray-900 hover:bg-gray-100 border-transparent');
        $(this).addClass('bg-blue-50 text-blue-700 border-blue-200 shadow-xs').removeClass('text-gray-600 hover:text-gray-900 hover:bg-gray-100 border-transparent');
        
        currentType = $(this).data('type');
        
        if ($(`#filter-type option[value="${currentType}"]`).length) {
            $('#filter-type').val(currentType);
        } else if (currentType === 'All') {
            $('#filter-type').val('All Types');
        }
        
        assetsTable.draw();
    });

    $('#filter-type').on('change', function() {
        let val = $(this).val();
        currentType = val === 'All Types' ? 'All' : val;
        
        $('.type-tab').removeClass('bg-blue-50 text-blue-700 border-blue-200 shadow-xs').addClass('text-gray-600 hover:text-gray-900 hover:bg-gray-100 border-transparent');
        $(`.type-tab[data-type="${currentType}"]`).addClass('bg-blue-50 text-blue-700 border-blue-200 shadow-xs').removeClass('text-gray-600 hover:text-gray-900 hover:bg-gray-100 border-transparent');
        
        assetsTable.draw();
    });

    // ========================================================
    // FORM SUBMISSION (AJAX)
    // ========================================================
    $('#upload-form').on('submit', function(e) {
        e.preventDefault();
        let formData = new FormData(this);
        let btn = $('#btn-save-asset');
        let ogHtml = btn.html();
        btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Saving...');

        $.ajax({
            url: $(this).attr('action'),
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(res) {
                if (res.status === 'success') {
                    CRM.toast('success', res.message || 'Asset saved successfully.');
                    resetAssetForm();
                    switchTab('tab-pane-list');
                } else {
                    CRM.toast('error', res.message || 'Validation error.');
                }
            },
            error: function(xhr) {
                let res = xhr.responseJSON;
                CRM.toast('error', res && res.message ? res.message : 'An error occurred while saving asset.');
            },
            complete: function() {
                btn.prop('disabled', false).html(ogHtml);
            }
        });
    });

    // ========================================================
    // EDIT CLICK
    // ========================================================
    $(document).on('click', '.btn-edit', function() {
        let id = $(this).data('id');
        let $btn = $(this);
        let origHtml = $btn.html();
        $btn.html('<i class="fa fa-spinner fa-spin"></i>');

        $.get(BASE_URL + 'product_hub/get_asset/' + id, function(res) {
            $btn.html(origHtml);
            if (res.status === 'success') {
                let data = res.data;
                $('#asset_id').val(data.id);
                $('#asset-product-id').val(data.product_id || '').trigger('change');
                $('#asset-type-select').val(data.asset_type);
                $('#asset-title-input').val(data.title);
                $('#asset-description-input').val(data.description || '');
                
                if (data.file_link && data.file_link.startsWith('http')) {
                    $('#asset-link-url').val(data.file_link);
                    $('#file-name-display').text('');
                } else {
                    $('#asset-link-url').val('');
                    $('#file-name-display').html(data.file_link ? '<i class="fa fa-file text-blue-500"></i> Current File: ' + data.file_link.split('/').pop() : '');
                }

                $('#form-title').text('Edit Asset: ' + data.title);
                $('#tab-add-label').text('Edit Asset');
                $('#btn-submit-label').text('Update Asset');

                switchTab('tab-pane-add', true);
            } else {
                CRM.toast('error', 'Failed to fetch asset data.');
            }
        }).fail(function() {
            $btn.html(origHtml);
            CRM.toast('error', 'Connection error while loading asset details.');
        });
    });

    // ========================================================
    // DELETE CLICK
    // ========================================================
    $(document).on('click', '.btn-delete', function() {
        let id = $(this).data('id');
        let csrf_name = '<?= $csrf_name ?>';
        let csrf_hash = $('#csrf-token').val() || (typeof CI3_CSRF_HASH !== 'undefined' ? CI3_CSRF_HASH : '');
        
        Swal.fire({
            title: 'Delete this asset?',
            text: 'This collateral will be permanently removed from the product hub catalogue.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Yes, delete it'
        }).then((result) => {
            if (result.isConfirmed) {
                let data = { id: id };
                data[csrf_name] = csrf_hash;
                
                $.post(BASE_URL + 'product_hub/delete_asset', data, function(res) {
                    if (res.status === 'success') {
                        CRM.toast('success', 'Asset deleted successfully.');
                        assetsTable.ajax.reload(null, false);
                        loadCounts();
                    } else {
                        CRM.toast('error', res.message || 'Could not delete asset.');
                    }
                }).fail(function() {
                    CRM.toast('error', 'Server error while deleting asset.');
                });
            }
        });
    });

    // Dynamic Live Counts
    function loadCounts() {
        $.get(BASE_URL + 'product_hub/get_asset_counts', function(res) {
            if (res.status === 'success' && res.data) {
                $('#tab-all').text(`All (${res.data.All || 0})`);
                $('#tab-documents').text(`Documents (${res.data.Documents || 0})`);
                $('#tab-videos').text(`Videos (${res.data.Videos || 0})`);
                $('#tab-images').text(`Images (${res.data.Images || 0})`);
                $('#tab-presentations').text(`Presentations (${res.data.Presentations || 0})`);
            }
        });
    }

    loadCounts();
});
</script>
