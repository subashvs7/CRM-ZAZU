<!-- Summernote Lite CSS -->
<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-lite.min.css" rel="stylesheet">

<div class="space-y-6">
    <!-- Top Header Bar -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-white p-5 rounded-2xl border border-gray-100 shadow-xs">
        <div class="flex items-center gap-3.5">
            <div class="w-12 h-12 bg-gradient-to-tr from-purple-600 to-indigo-600 text-white rounded-2xl flex items-center justify-center shadow-md shadow-purple-500/20 flex-shrink-0">
                <i class="fa fa-folder-open-o text-xl"></i>
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-xl font-bold text-gray-900">Email Templates Library</h1>
                    <span class="px-2 py-0.5 text-[11px] font-bold bg-purple-50 text-purple-700 border border-purple-200 rounded-full font-mono"><?= count($templates) ?> Templates</span>
                </div>
                <p class="text-xs text-gray-500 mt-0.5">Design, customize, and manage product-specific outreach, welcome, and follow-up email templates.</p>
            </div>
        </div>

        <div class="flex items-center gap-2 flex-wrap">
            <a href="<?= base_url('communications/bulk_mail') ?>" class="px-3.5 py-2 bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 text-xs font-semibold rounded-xl transition-all shadow-xs flex items-center gap-1.5">
                <i class="fa fa-paper-plane text-blue-600"></i> Send Bulk Mail
            </a>
            <button type="button" id="btn-open-create-template" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl transition-all shadow-xs flex items-center gap-1.5 cursor-pointer">
                <i class="fa fa-plus-circle"></i> Create New Template
            </button>
        </div>
    </div>

    <!-- Filter & Search Toolbar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-white p-4 rounded-2xl border border-gray-100 shadow-xs">
        <div class="flex flex-wrap items-center gap-2.5">
            <span class="text-xs font-bold text-gray-600 uppercase tracking-tight">Filter:</span>
            <select id="grid-filter-product" class="px-3 py-1.5 bg-gray-50 border border-gray-200 rounded-xl text-xs font-medium focus:ring-2 focus:ring-purple-500 focus:outline-none">
                <option value="">All Products</option>
                <?php foreach ($products as $p): ?>
                <option value="<?= $p['id'] ?>"><?= esc_html($p['name']) ?></option>
                <?php endforeach; ?>
            </select>
            <select id="grid-filter-category" class="px-3 py-1.5 bg-gray-50 border border-gray-200 rounded-xl text-xs font-medium focus:ring-2 focus:ring-purple-500 focus:outline-none">
                <option value="all">All Categories</option>
                <option value="onboarding">Client Onboarding</option>
                <option value="thank_you">Thank You & Appreciation</option>
                <option value="login">Login Credentials & Access</option>
                <option value="demo">Product Demo</option>
                <option value="proposal">Proposal / Quotation</option>
                <option value="followup">Follow-up & Pitch</option>
                <option value="general">General</option>
            </select>
        </div>
        <div class="relative w-full sm:w-64">
            <i class="fa fa-search absolute left-3 top-2.5 text-gray-400 text-xs"></i>
            <input type="text" id="input-search-template" placeholder="Search templates..." class="w-full pl-8 pr-3 py-1.5 bg-gray-50 border border-gray-200 rounded-xl text-xs focus:ring-2 focus:ring-purple-500 focus:outline-none">
        </div>
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
        <div class="template-card bg-white border border-gray-200/80 hover:border-purple-400 rounded-2xl p-5 shadow-xs hover:shadow-md transition-all flex flex-col justify-between"
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
                    <h4 class="text-sm font-bold text-gray-900 group-hover:text-purple-600 transition-colors"><?= esc_html($t['name']) ?></h4>
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
                <a href="<?= base_url('communications/bulk_mail?template_id='.$t['id']) ?>" class="px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold transition-colors shadow-2xs inline-flex items-center gap-1.5">
                    <i class="fa fa-paper-plane"></i> Use in Send Mail
                </a>
                <div class="flex items-center gap-1.5">
                    <button type="button" class="btn-edit-template p-1.5 text-gray-400 hover:text-blue-600 rounded-lg hover:bg-gray-100 transition-colors cursor-pointer" title="Edit Template" data-id="<?= $t['id'] ?>">
                        <i class="fa fa-pencil"></i>
                    </button>
                    <button type="button" class="btn-delete-template p-1.5 text-gray-400 hover:text-rose-600 rounded-lg hover:bg-gray-100 transition-colors cursor-pointer" title="Delete Template" data-id="<?= $t['id'] ?>">
                        <i class="fa fa-trash-o"></i>
                    </button>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL: ADD / EDIT EMAIL TEMPLATE                                          -->
<!-- ========================================================================= -->
<div id="modal-template-editor" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-xs p-4 overflow-y-auto">
    <div class="bg-white rounded-2xl shadow-2xl border border-gray-100 w-full max-w-3xl overflow-hidden animate-in fade-in zoom-in-95 duration-200">
        <div class="px-6 py-4 bg-gradient-to-r from-purple-600 to-indigo-600 text-white flex items-center justify-between">
            <div class="flex items-center gap-2">
                <i class="fa fa-pencil-square-o text-lg"></i>
                <h3 class="text-sm font-bold" id="modal-template-title">Create New Email Template</h3>
            </div>
            <button type="button" id="btn-close-template-modal" class="text-white/80 hover:text-white text-lg cursor-pointer">
                <i class="fa fa-times"></i>
            </button>
        </div>

        <form id="form-save-template" class="p-6 space-y-4">
            <input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>" value="<?= $this->security->get_csrf_hash() ?>">
            <input type="hidden" name="id" id="tpl-edit-id" value="">

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="sm:col-span-2">
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Template Name <span class="text-rose-500">*</span></label>
                    <input type="text" name="name" id="tpl-input-name" class="w-full px-3 py-2 border border-gray-200 rounded-xl text-xs focus:ring-2 focus:ring-purple-500 focus:outline-none" placeholder="e.g. VIP Client Onboarding Welcome" required>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Category <span class="text-rose-500">*</span></label>
                    <select name="category" id="tpl-input-category" class="w-full px-3 py-2 border border-gray-200 rounded-xl text-xs focus:ring-2 focus:ring-purple-500 focus:outline-none" required>
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
                    <select name="product_id" id="tpl-input-product" class="w-full px-3 py-2 border border-gray-200 rounded-xl text-xs focus:ring-2 focus:ring-purple-500 focus:outline-none">
                        <option value="">General (All Products)</option>
                        <?php foreach ($products as $p): ?>
                        <option value="<?= $p['id'] ?>"><?= esc_html($p['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Default Subject Line <span class="text-rose-500">*</span></label>
                    <input type="text" name="subject" id="tpl-input-subject" class="w-full px-3 py-2 border border-gray-200 rounded-xl text-xs focus:ring-2 focus:ring-purple-500 focus:outline-none" placeholder="e.g. Welcome to {{company_name}} - {{product_name}}" required>
                </div>
            </div>

            <!-- Merge tags helper pills -->
            <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">Insert Dynamic Merge Tags:</label>
                <div class="flex flex-wrap gap-1.5 text-[11px]">
                    <button type="button" class="btn-insert-tag px-2 py-0.5 bg-gray-100 hover:bg-purple-100 text-gray-700 hover:text-purple-700 rounded border border-gray-200" data-tag="{{name}}">{{name}}</button>
                    <button type="button" class="btn-insert-tag px-2 py-0.5 bg-gray-100 hover:bg-purple-100 text-gray-700 hover:text-purple-700 rounded border border-gray-200" data-tag="{{company}}">{{company}}</button>
                    <button type="button" class="btn-insert-tag px-2 py-0.5 bg-gray-100 hover:bg-purple-100 text-gray-700 hover:text-purple-700 rounded border border-gray-200" data-tag="{{product_name}}">{{product_name}}</button>
                    <button type="button" class="btn-insert-tag px-2 py-0.5 bg-gray-100 hover:bg-purple-100 text-gray-700 hover:text-purple-700 rounded border border-gray-200" data-tag="{{product_price}}">{{product_price}}</button>
                    <button type="button" class="btn-insert-tag px-2 py-0.5 bg-gray-100 hover:bg-purple-100 text-gray-700 hover:text-purple-700 rounded border border-gray-200" data-tag="{{portal_username}}">{{portal_username}}</button>
                    <button type="button" class="btn-insert-tag px-2 py-0.5 bg-gray-100 hover:bg-purple-100 text-gray-700 hover:text-purple-700 rounded border border-gray-200" data-tag="{{portal_password}}">{{portal_password}}</button>
                    <button type="button" class="btn-insert-tag px-2 py-0.5 bg-gray-100 hover:bg-purple-100 text-gray-700 hover:text-purple-700 rounded border border-gray-200" data-tag="{{app_url}}">{{app_url}}</button>
                    <button type="button" class="btn-insert-tag px-2 py-0.5 bg-gray-100 hover:bg-purple-100 text-gray-700 hover:text-purple-700 rounded border border-gray-200" data-tag="{{company_name}}">{{company_name}}</button>
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">Template Content (Rich Text & Image Drag & Drop) <span class="text-rose-500">*</span></label>
                <textarea id="modal-summernote" name="body"></textarea>
            </div>

            <div class="flex items-center justify-between pt-4 border-t border-gray-100">
                <button type="button" id="btn-cancel-template-modal" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl text-xs font-semibold transition-colors cursor-pointer">
                    Cancel
                </button>
                <button type="submit" id="btn-submit-template-save" class="px-5 py-2.5 bg-purple-600 hover:bg-purple-700 text-white rounded-xl text-xs font-bold shadow-sm transition-all flex items-center gap-1.5 cursor-pointer">
                    <i class="fa fa-check"></i> Save Template
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Summernote Lite JS -->
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-lite.min.js"></script>

<script>
$(function() {
    // Summernote editor
    $('#modal-summernote').summernote({
        placeholder: 'Write your email template content here...',
        tabsize: 2,
        height: 280,
        toolbar: [
            ['style', ['style']],
            ['font', ['bold', 'italic', 'underline', 'clear']],
            ['fontname', ['fontname']],
            ['color', ['color']],
            ['para', ['ul', 'ol', 'paragraph']],
            ['table', ['table']],
            ['insert', ['link', 'picture', 'hr']],
            ['view', ['fullscreen', 'codeview']]
        ]
    });

    // Tag inserters
    $('.btn-insert-tag').on('click', function() {
        var tag = $(this).data('tag');
        $('#modal-summernote').summernote('insertText', tag);
    });

    // Open Create Template Modal
    $('#btn-open-create-template').on('click', function() {
        $('#form-save-template')[0].reset();
        $('#tpl-edit-id').val('');
        $('#modal-summernote').summernote('code', '');
        $('#modal-template-title').text('Create New Email Template');
        $('#modal-template-editor').removeClass('hidden');
    });

    // Close Modal
    $('#btn-close-template-modal, #btn-cancel-template-modal').on('click', function() {
        $('#modal-template-editor').addClass('hidden');
    });

    // Filter cards
    function filterCards() {
        var p = $('#grid-filter-product').val();
        var c = $('#grid-filter-category').val();
        var q = $('#input-search-template').val().toLowerCase().trim();

        $('.template-card').each(function() {
            var cp = $(this).data('product');
            var cc = $(this).data('cat');
            var text = $(this).text().toLowerCase();

            var matchP = !p || String(cp) === String(p);
            var matchC = c === 'all' || !c || String(cc) === String(c);
            var matchQ = !q || text.indexOf(q) !== -1;

            if (matchP && matchC && matchQ) {
                $(this).removeClass('hidden');
            } else {
                $(this).addClass('hidden');
            }
        });
    }

    $('#grid-filter-product, #grid-filter-category').on('change', filterCards);
    $('#input-search-template').on('keyup', filterCards);

    // Edit Template
    $(document).on('click', '.btn-edit-template', function() {
        var id = $(this).data('id');
        $.getJSON(BASE_URL + 'communications/get_template_ajax/' + id, function(resp) {
            if (resp.status === 'success' && resp.data) {
                var t = resp.data;
                $('#tpl-edit-id').val(t.id);
                $('#tpl-input-name').val(t.name);
                $('#tpl-input-category').val(t.category);
                $('#tpl-input-product').val(t.product_id || '');
                $('#tpl-input-subject').val(t.subject);
                $('#modal-summernote').summernote('code', t.body);
                $('#modal-template-title').text('Edit Email Template — ' + t.name);
                $('#modal-template-editor').removeClass('hidden');
            }
        });
    });

    // Save Template Form
    $('#form-save-template').on('submit', function(e) {
        e.preventDefault();
        $('#modal-summernote').val($('#modal-summernote').summernote('code'));
        var $btn = $('#btn-submit-template-save');
        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Saving...');

        $.ajax({
            url: BASE_URL + 'communications/save_template_ajax',
            method: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(resp) {
                $btn.prop('disabled', false).html('<i class="fa fa-check"></i> Save Template');
                if (resp.status === 'success') {
                    CRM.toast('success', resp.message || 'Template saved successfully!');
                    setTimeout(function() { location.reload(); }, 600);
                } else {
                    CRM.toast('error', resp.message || 'Failed to save template.');
                }
            },
            error: function() {
                $btn.prop('disabled', false).html('<i class="fa fa-check"></i> Save Template');
                CRM.toast('error', 'Network error saving template.');
            }
        });
    });

    // Delete Template
    $(document).on('click', '.btn-delete-template', function() {
        var id = $(this).data('id');
        if (!confirm('Are you sure you want to delete this email template?')) return;

        var postData = { id: id };
        if (typeof CI3_CSRF_NAME !== 'undefined' && typeof CI3_CSRF_HASH !== 'undefined') {
            postData[CI3_CSRF_NAME] = CI3_CSRF_HASH;
        }

        $.ajax({
            url: BASE_URL + 'communications/delete_template_ajax',
            method: 'POST',
            data: postData,
            dataType: 'json',
            success: function(resp) {
                if (resp.status === 'success') {
                    CRM.toast('success', 'Template deleted successfully.');
                    $('.template-card[data-id="' + id + '"]').fadeOut(300, function() { $(this).remove(); });
                } else {
                    CRM.toast('error', resp.message || 'Could not delete template.');
                }
            }
        });
    });
});
</script>
