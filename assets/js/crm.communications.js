/**
 * CRM Communications — Bulk Mail Hub JS Logic
 * Single-Page Workspace: Dynamic Product Templates, Summernote with Image Drag & Drop,
 * Real-time Email Mockup Preview, Audience Filtering, and Follow-up History.
 */

$(function () {
    'use strict';

    var currentProductMeta = {
        name: 'CRM-ZAZU Solutions',
        sku: 'CRM-CORE',
        price: '₹ 1,29,999',
        url: 'www.zazutech.in'
    };

    var lastFocusedInput = null;

    // ─────────────────────────────────────────────────────────────────────────
    // 1. Summernote Initialization with Image Drag & Drop
    // ─────────────────────────────────────────────────────────────────────────
    function uploadSummernoteImage(file, $editor) {
        var data = new FormData();
        data.append('file', file);
        if (typeof CI3_CSRF_NAME !== 'undefined' && typeof CI3_CSRF_HASH !== 'undefined') {
            data.append(CI3_CSRF_NAME, CI3_CSRF_HASH);
        }

        $.ajax({
            url: BASE_URL + 'communications/upload_image',
            method: 'POST',
            data: data,
            processData: false,
            contentType: false,
            success: function (resp) {
                if (resp.status === 'success' && resp.data && resp.data.url) {
                    $editor.summernote('insertImage', resp.data.url);
                } else {
                    fallbackBase64Image(file, $editor);
                }
            },
            error: function () {
                fallbackBase64Image(file, $editor);
            }
        });
    }

    function fallbackBase64Image(file, $editor) {
        var reader = new FileReader();
        reader.onloadend = function () {
            $editor.summernote('insertImage', reader.result);
        };
        reader.readAsDataURL(file);
    }

    // Main Composer Summernote
    $('#summernote-editor').summernote({
        placeholder: 'Compose your email message here or load an email template above...',
        tabsize: 2,
        height: 320,
        toolbar: [
            ['style', ['style', 'bold', 'italic', 'underline', 'clear']],
            ['font', ['strikethrough', 'superscript', 'subscript']],
            ['fontsize', ['fontsize']],
            ['color', ['color']],
            ['para', ['ul', 'ol', 'paragraph']],
            ['table', ['table']],
            ['insert', ['link', 'picture', 'hr']],
            ['view', ['fullscreen', 'codeview', 'help']]
        ],
        callbacks: {
            onInit: function () {
                // If template already selected, load it; else default greeting
                if (!$('#summernote-editor').summernote('code') || $('#summernote-editor').summernote('isEmpty')) {
                    var urlParams = new URLSearchParams(window.location.search);
                    var paramTplId = urlParams.get('template_id');
                    if (paramTplId && $('#select-template option[value="' + paramTplId + '"]').length) {
                        $('#select-template').val(paramTplId).trigger('change');
                    } else {
                        var initialTplId = $('#select-template').val();
                        if (initialTplId) {
                            loadTemplateDetail(initialTplId);
                        } else if ($('#select-template option').length > 1) {
                            $('#select-template').prop('selectedIndex', 1).trigger('change');
                        }
                    }
                }
                updateLivePreview();
            },
            onChange: function () {
                updateLivePreview();
            },
            onImageUpload: function (files) {
                for (var i = 0; i < files.length; i++) {
                    uploadSummernoteImage(files[i], $('#summernote-editor'));
                }
            }
        }
    });

    // Modal Template Summernote
    $('#modal-summernote').summernote({
        placeholder: 'Design your email template with HTML & images...',
        tabsize: 2,
        height: 250,
        toolbar: [
            ['style', ['style', 'bold', 'italic', 'underline', 'clear']],
            ['color', ['color']],
            ['para', ['ul', 'ol', 'paragraph']],
            ['table', ['table']],
            ['insert', ['link', 'picture']],
            ['view', ['codeview']]
        ],
        callbacks: {
            onImageUpload: function (files) {
                for (var i = 0; i < files.length; i++) {
                    uploadSummernoteImage(files[i], $('#modal-summernote'));
                }
            }
        }
    });


    // ─────────────────────────────────────────────────────────────────────────
    // 2. Navigation Tabs (Composer vs Templates vs History)
    // ─────────────────────────────────────────────────────────────────────────
    $('.tab-nav-btn').on('click', function () {
        var targetTab = $(this).data('tab');
        $('.tab-nav-btn').removeClass('active bg-white text-blue-600 shadow-xs').addClass('text-gray-600');
        $(this).addClass('active bg-white text-blue-600 shadow-xs').removeClass('text-gray-600');

        $('.tab-pane').addClass('hidden');
        $('#' + targetTab).removeClass('hidden');

        if (targetTab === 'tab-history') {
            loadCampaignHistory();
        }

        if (location.hash !== '#' + targetTab) {
            history.replaceState(null, null, '#' + targetTab);
        }
    });

    // Check hash on load (e.g. #tab-history or #tab-templates)
    if (location.hash) {
        var hashTab = location.hash.replace('#', '');
        var $targetBtn = $('.tab-nav-btn[data-tab="' + hashTab + '"]');
        if ($targetBtn.length) {
            $targetBtn.trigger('click');
        }
    }


    // ─────────────────────────────────────────────────────────────────────────
    // 3. Dynamic Product & Template Filtering
    // ─────────────────────────────────────────────────────────────────────────
    $('#select-product').on('change', function () {
        var $opt = $(this).find('option:selected');
        var pid = $(this).val();

        currentProductMeta.name  = $opt.data('name') || 'CRM-ZAZU Solutions';
        currentProductMeta.sku   = $opt.data('sku') || 'CRM-CORE';
        currentProductMeta.price = $opt.data('price') || '₹ 1,29,999';
        currentProductMeta.url   = $opt.data('url') || 'www.zazutech.in';

        // Reload templates for this product
        reloadTemplateDropdown(pid, $('#filter-category').val());
        // Recalculate audience count
        updateAudienceCount();
        // Update preview
        updateLivePreview();
    });

    $('#filter-category').on('change', function () {
        reloadTemplateDropdown($('#select-product').val(), $(this).val());
    });

    function reloadTemplateDropdown(productId, category) {
        $.getJSON(BASE_URL + 'communications/get_templates_ajax', {
            product_id: productId || '',
            category: category || 'all'
        }, function (res) {
            if (res.status === 'success' && res.data) {
                var items = res.data;
                var html = '<option value="">-- Choose an Email Template (Or Write from Scratch) --</option>';
                $.each(items, function (i, t) {
                    var prodLabel = t.product_name ? ' (' + t.product_name + ')' : '';
                    html += '<option value="' + t.id + '" data-product="' + (t.product_id || '') + '" data-cat="' + t.category + '">';
                    html += '[' + (t.category || 'general').toUpperCase() + '] ' + CRM.esc(t.name) + prodLabel;
                    html += '</option>';
                });
                $('#select-template').html(html);

                // Auto-select first matching template if available
                if (items.length > 0) {
                    $('#select-template').val(items[0].id).trigger('change');
                }
            }
        });
    }

    $('#select-template, #btn-apply-template').on('change click', function (e) {
        var templateId = $('#select-template').val();
        if (templateId) {
            loadTemplateDetail(templateId);
        }
    });

    function loadTemplateDetail(templateId) {
        $.getJSON(BASE_URL + 'communications/get_template_ajax/' + templateId, function (res) {
            if (res.status === 'success' && res.data) {
                var tpl = res.data;
                $('#mail-subject').val(tpl.subject);
                $('#summernote-editor').summernote('code', tpl.body);
                updateLivePreview();
                $('#subject-char-count').text(tpl.subject.length + ' chars');
            }
        });
    }


    // ─────────────────────────────────────────────────────────────────────────
    // 4. Merge Tags Insertion
    // ─────────────────────────────────────────────────────────────────────────
    $('#mail-subject').on('focus', function () {
        lastFocusedInput = 'subject';
    });

    $('.note-editable').on('focus click', function () {
        lastFocusedInput = 'summernote';
    });

    $('.merge-tag-chip').on('click', function () {
        var tag = $(this).data('tag');
        if (lastFocusedInput === 'subject') {
            var input = document.getElementById('mail-subject');
            var start = input.selectionStart || input.value.length;
            var end = input.selectionEnd || input.value.length;
            input.value = input.value.substring(0, start) + tag + input.value.substring(end);
            input.focus();
            input.setSelectionRange(start + tag.length, start + tag.length);
            $('#mail-subject').trigger('input');
        } else {
            $('#summernote-editor').summernote('insertText', tag);
        }
        updateLivePreview();
    });

    $('#mail-subject').on('input keyup', function () {
        $('#subject-char-count').text($(this).val().length + ' chars');
        updateLivePreview();
    });


    // ─────────────────────────────────────────────────────────────────────────
    // 5. Live Email Client Preview & Audience Recipient Resolution
    // ─────────────────────────────────────────────────────────────────────────
    var leadsData = [];
    var customersData = [];
    var contactsData = [];

    var selectedLeads = new Set();
    var selectedCustomers = new Set();
    var selectedContacts = new Set();

    var leadsLoaded = false;
    var custsLoaded = false;
    var contsLoaded = false;

    var leadsReachFilter = 'all';
    var custsReachFilter = 'all';

    function getSelectedPreviewRecipient() {
        // 1. If user checked specific lead(s), pick the first selected lead
        if (selectedLeads && selectedLeads.size > 0 && leadsData && leadsData.length > 0) {
            for (var i = 0; i < leadsData.length; i++) {
                if (selectedLeads.has(leadsData[i].key)) {
                    return leadsData[i];
                }
            }
        }
        // 2. If user checked specific customer(s)
        if (selectedCustomers && selectedCustomers.size > 0 && customersData && customersData.length > 0) {
            for (var j = 0; j < customersData.length; j++) {
                if (selectedCustomers.has(customersData[j].key)) {
                    return customersData[j];
                }
            }
        }
        // 3. If user checked specific contact(s)
        if (selectedContacts && selectedContacts.size > 0 && contactsData && contactsData.length > 0) {
            for (var k = 0; k < contactsData.length; k++) {
                if (selectedContacts.has(contactsData[k].key)) {
                    return contactsData[k];
                }
            }
        }

        // 4. Fallback to first available lead if 'All Leads' audience group is checked
        if ($('#aud-chk-leads').is(':checked') && leadsData && leadsData.length > 0) {
            return leadsData[0];
        }
        // 5. Fallback to first available customer if 'Customers' audience group is checked
        if ($('#aud-chk-custs').is(':checked') && customersData && customersData.length > 0) {
            return customersData[0];
        }
        // 6. Fallback to first available contact
        if ($('#aud-chk-contacts').is(':checked') && contactsData && contactsData.length > 0) {
            return contactsData[0];
        }

        return null;
    }

    function updateLivePreview() {
        var rawSubject = $('#mail-subject').val() || '(No Subject Line)';
        var rawBody    = $('#summernote-editor').summernote('code') || '<p class="text-gray-400 italic">No content composed yet.</p>';

        var rec = getSelectedPreviewRecipient();

        var sampleName    = rec ? (rec.name || 'Valued Recipient') : 'Valued Recipient';
        var sampleFirst   = rec ? (rec.first_name ? rec.first_name.trim().split(' ')[0] : (sampleName.trim().split(' ')[0] || 'Valued')) : 'Valued';
        var sampleLast    = rec ? (rec.last_name || '') : '';
        var sampleCompany = rec ? (rec.company || 'Enterprise Client') : 'Enterprise Client';
        var sampleEmail   = rec ? (rec.email || 'client@example.com') : 'client@example.com';
        var samplePhone   = rec ? (rec.phone || '') : '+91 9876543210';

        var senderName    = 'Antigravity Team';
        var senderPhone   = '+91 44 28765432';

        var replacements = {
            '{{customer_name}}': sampleName,
            '{{lead_name}}': sampleName,
            '{{name}}': sampleName,
            '{{first_name}}': sampleFirst,
            '{{last_name}}': sampleLast,
            '{{company_name}}': sampleCompany,
            '{{email}}': sampleEmail,
            '{{phone}}': samplePhone,
            '{{product_name}}': currentProductMeta.name || (rec && rec.product_name ? rec.product_name : 'CRM-ZAZU Solutions'),
            '{{product_price}}': currentProductMeta.price || (rec && rec.product_price ? rec.product_price : '₹ 1,29,999'),
            '{{login_url}}': BASE_URL + 'auth/login',
            '{{login_email}}': sampleEmail,
            '{{temporary_password}}': 'Zazu@' + new Date().getFullYear(),
            '{{sender_name}}': senderName,
            '{{sender_phone}}': senderPhone,
            '{{current_date}}': new Date().toLocaleDateString('en-IN', { day: '2-digit', month: 'short', year: 'numeric' })
        };

        var renderedSubject = rawSubject;
        var renderedBody    = rawBody;

        $.each(replacements, function (tag, val) {
            renderedSubject = renderedSubject.split(tag).join(val);
            renderedBody    = renderedBody.split(tag).join(val);
        });

        if (rec && rec.email) {
            $('#preview-header-to').text(sampleName + ' <' + sampleEmail + '>');
        } else {
            $('#preview-header-to').text('Selected Recipient <client@example.com>');
        }

        $('#preview-header-subject').text(renderedSubject);
        $('#email-preview-rendered').html(renderedBody);
    }

    // Toggle Desktop / Mobile View
    $('.preview-mode-btn').on('click', function () {
        var mode = $(this).data('mode');
        $('.preview-mode-btn').removeClass('active text-gray-700 bg-white shadow-2xs').addClass('text-gray-500');
        $(this).addClass('active text-gray-700 bg-white shadow-2xs').removeClass('text-gray-500');

        if (mode === 'mobile') {
            $('#email-preview-container').addClass('mode-mobile');
        } else {
            $('#email-preview-container').removeClass('mode-mobile');
        }
    });

    // ─────────────────────────────────────────────────────────────────────────
    // 6. Multi-Selection Audience Configuration & Live Counter
    // ─────────────────────────────────────────────────────────────────────────
    function getSelectedAudiences() {
        var types = [];
        $('.audience-chk:checked').each(function () {
            types.push($(this).val());
        });
        return types;
    }

    function syncAudienceCardsUI() {
        $('.audience-card').each(function () {
            var forId = $(this).data('for');
            var $chk = $('#' + forId);
            if ($chk.is(':checked')) {
                $(this).removeClass('border-gray-200 bg-white').addClass('border-2 border-blue-500 bg-blue-50/40 shadow-xs');
                $(this).find('.status-lbl').removeClass('text-gray-400 font-semibold').addClass('text-emerald-600 font-bold').text('Selected');
            } else {
                $(this).removeClass('border-2 border-blue-500 bg-blue-50/40 shadow-xs').addClass('border border-gray-200 bg-white');
                $(this).find('.status-lbl').removeClass('text-emerald-600 font-bold').addClass('text-gray-400 font-semibold').text('Optional');
            }
        });

        // 1. Leads Section Visibility
        var hasLeads = $('#aud-chk-leads').is(':checked');
        if (hasLeads) {
            $('#section-leads-config').removeClass('hidden');
            $('#section-leads-config input, #section-leads-config select').prop('disabled', false);
            if (!leadsLoaded) {
                loadLeads();
            }
        } else {
            $('#section-leads-config').addClass('hidden');
            $('#section-leads-config input, #section-leads-config select').prop('disabled', true);
        }

        // 2. Customers Section Visibility
        var hasCusts = $('#aud-chk-custs').is(':checked');
        if (hasCusts) {
            $('#section-customers-config').removeClass('hidden');
            $('#section-customers-config input').prop('disabled', false);
            if (!custsLoaded) {
                loadCustomers();
            }
        } else {
            $('#section-customers-config').addClass('hidden');
            $('#section-customers-config input').prop('disabled', true);
        }

        // 3. Contact Book Section Visibility
        var hasContacts = $('#aud-chk-contacts').is(':checked');
        if (hasContacts) {
            $('#section-contacts-config').removeClass('hidden');
            $('#section-contacts-config input').prop('disabled', false);
            if (!contsLoaded) {
                loadContacts();
            }
        } else {
            $('#section-contacts-config').addClass('hidden');
            $('#section-contacts-config input').prop('disabled', true);
        }

        updateOverallAudienceSummary();
    }

    // ── LEADS LOADER & RENDERER ─────────────────────────────────────────────
    function loadLeads() {
        var pId   = $('#select-product').val();
        var lStat = $('#select-lead-status').val();

        $('#leads-checklist-container').html('<div class="p-4 text-center text-gray-400 text-xs"><i class="fa fa-spinner fa-spin text-emerald-600 text-base mb-1 block"></i> Loading leads...</div>');

        $.getJSON(BASE_URL + 'communications/get_audience_recipients_ajax', {
            recipient_types: ['leads'],
            product_id: pId,
            lead_status: lStat,
            reach_filter: leadsReachFilter
        }, function (res) {
            if (res.status === 'success' && res.data) {
                leadsData = res.data || [];
                leadsLoaded = true;
                selectedLeads.clear();
                $.each(leadsData, function (i, r) {
                    selectedLeads.add(r.key);
                });
                renderLeadsList();
                updateLivePreview();
            } else {
                $('#leads-checklist-container').html('<div class="p-4 text-center text-rose-500 text-xs">Failed to load leads list.</div>');
            }
        });
    }

    function renderLeadsList() {
        var q = ($('#leads-search-input').val() || '').toLowerCase().trim();
        var filtered = $.grep(leadsData, function (r) {
            if (!q) return true;
            return ((r.name || '') + ' ' + (r.company || '') + ' ' + (r.email || '')).toLowerCase().indexOf(q) !== -1;
        });

        if (filtered.length === 0) {
            $('#leads-checklist-container').html('<div class="p-4 text-center text-gray-400 text-xs">No leads found matching query.</div>');
            updateLeadsBadge();
            return;
        }

        var html = '';
        $.each(filtered, function (i, r) {
            var isChecked = selectedLeads.has(r.key);

            var reachBadge = '<span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-blue-50 text-blue-700 border border-blue-200">📬 Never Sent</span>';
            if (r.reach_status === 'already_sent') {
                var sentInfo = r.last_sent_at ? 'Sent: ' + r.last_sent_at : 'Contacted';
                reachBadge = '<span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-purple-50 text-purple-700 border border-purple-200" title="' + sentInfo + '">🔄 Contacted' + (r.outreach_count > 0 ? ' (' + r.outreach_count + ')' : '') + '</span>';
            } else if (r.reach_status === 'failed') {
                reachBadge = '<span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-rose-50 text-rose-700 border border-rose-200">⚠️ Failed (Retry)</span>';
            }

            var dirBadge = r.direction === 'inbound'
                ? '<span class="px-1.5 py-0.5 text-[9px] font-bold rounded bg-emerald-50 text-emerald-700 border border-emerald-200">📥 INBOUND</span>'
                : '<span class="px-1.5 py-0.5 text-[9px] font-bold rounded bg-slate-100 text-slate-600 border border-slate-200">📤 OUTBOUND</span>';

            html += '<label class="flex items-center justify-between p-2.5 hover:bg-emerald-50/60 cursor-pointer transition-colors border-b border-gray-100 last:border-b-0 select-none">';
            html += '<div class="flex items-center gap-2.5">';
            html += '<input type="checkbox" name="selected_recipient_keys[]" class="lead-item-chk rounded border-gray-300 text-emerald-600 focus:ring-emerald-500 w-4 h-4 cursor-pointer" value="' + CRM.esc(r.key) + '" ' + (isChecked ? 'checked' : '') + '>';
            html += '<div>';
            html += '<div class="font-bold text-gray-900 text-xs flex items-center gap-1.5">';
            html += '<span>' + CRM.esc(r.name) + '</span>';
            if (r.company) html += '<span class="text-gray-400 font-normal">(' + CRM.esc(r.company) + ')</span>';
            html += '</div>';
            html += '<div class="text-[11px] text-gray-500 font-mono">' + CRM.esc(r.email) + (r.phone ? ' &bull; ' + CRM.esc(r.phone) : '') + '</div>';
            html += '</div>';
            html += '</div>';
            html += '<div class="flex items-center gap-1.5 flex-wrap justify-end">';
            html += dirBadge;
            html += reachBadge;
            html += '<span class="px-2 py-0.5 text-[10px] rounded font-bold bg-gray-100 text-gray-700 uppercase">' + CRM.esc(r.status || 'lead') + '</span>';
            html += '</div>';
            html += '</label>';
        });

        $('#leads-checklist-container').html(html);
        updateLeadsBadge();
    }

    function updateLeadsBadge() {
        var count = selectedLeads.size;
        var total = leadsData.length;
        if (count === total && total > 0) {
            $('#badge-leads-count').text('All (' + total + ') Selected');
        } else {
            $('#badge-leads-count').text(count + ' of ' + total + ' Selected');
        }
        updateOverallAudienceSummary();
    }

    $(document).on('change', '.lead-item-chk', function () {
        var key = $(this).val();
        if ($(this).is(':checked')) {
            selectedLeads.add(key);
        } else {
            selectedLeads.delete(key);
        }
        updateLeadsBadge();
    });

    // Quick Batch Selection for Leads
    $(document).on('click', '.btn-leads-select-batch', function () {
        var count = parseInt($(this).data('count')) || 50;
        selectedLeads.clear();
        $('.lead-item-chk').prop('checked', false);

        var added = 0;
        $('.lead-item-chk').each(function () {
            if (added < count) {
                $(this).prop('checked', true);
                selectedLeads.add($(this).val());
                added++;
            }
        });
        updateLeadsBadge();
        CRM.toast('info', 'Selected first ' + added + ' leads.');
    });

    // Smart Filter Tabs for Leads
    $(document).on('click', '.leads-tab-filter', function () {
        $('.leads-tab-filter').removeClass('active bg-emerald-600 text-white shadow-2xs').addClass('bg-white text-gray-700 hover:bg-emerald-50 border border-gray-200');
        $(this).addClass('active bg-emerald-600 text-white shadow-2xs').removeClass('bg-white text-gray-700 hover:bg-emerald-50 border border-gray-200');
        leadsReachFilter = $(this).data('filter') || 'all';
        loadLeads();
    });

    $('#btn-leads-select-all').on('click', function () {
        $('.lead-item-chk').prop('checked', true);
        $.each(leadsData, function(i, r) { selectedLeads.add(r.key); });
        updateLeadsBadge();
    });

    $('#btn-leads-deselect-all').on('click', function () {
        $('.lead-item-chk').prop('checked', false);
        selectedLeads.clear();
        updateLeadsBadge();
    });

    $('#leads-search-input').on('input', function () {
        renderLeadsList();
    });

    $('#select-lead-status').on('change', function () {
        loadLeads();
    });


    // ── CUSTOMERS LOADER & RENDERER ─────────────────────────────────────────
    function loadCustomers() {
        $('#custs-checklist-container').html('<div class="p-4 text-center text-gray-400 text-xs"><i class="fa fa-spinner fa-spin text-indigo-600 text-base mb-1 block"></i> Loading customers...</div>');

        $.getJSON(BASE_URL + 'communications/get_audience_recipients_ajax', {
            recipient_types: ['customers'],
            reach_filter: custsReachFilter
        }, function (res) {
            if (res.status === 'success' && res.data) {
                customersData = res.data || [];
                custsLoaded = true;
                selectedCustomers.clear();
                $.each(customersData, function (i, r) {
                    selectedCustomers.add(r.key);
                });
                renderCustomersList();
                updateLivePreview();
            } else {
                $('#custs-checklist-container').html('<div class="p-4 text-center text-rose-500 text-xs">Failed to load customers list.</div>');
            }
        });
    }

    function renderCustomersList() {
        var q = ($('#custs-search-input').val() || '').toLowerCase().trim();
        var filtered = $.grep(customersData, function (r) {
            if (!q) return true;
            return ((r.name || '') + ' ' + (r.company || '') + ' ' + (r.email || '')).toLowerCase().indexOf(q) !== -1;
        });

        if (filtered.length === 0) {
            $('#custs-checklist-container').html('<div class="p-4 text-center text-gray-400 text-xs">No customers found matching query.</div>');
            updateCustsBadge();
            return;
        }

        var html = '';
        $.each(filtered, function (i, r) {
            var isChecked = selectedCustomers.has(r.key);

            var reachBadge = '<span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-blue-50 text-blue-700 border border-blue-200">📬 Uncontacted</span>';
            if (r.reach_status === 'already_sent') {
                var sentInfo = r.last_sent_at ? 'Sent: ' + r.last_sent_at : 'Contacted';
                reachBadge = '<span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-purple-50 text-purple-700 border border-purple-200" title="' + sentInfo + '">🔄 Contacted</span>';
            } else if (r.reach_status === 'failed') {
                reachBadge = '<span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-rose-50 text-rose-700 border border-rose-200">⚠️ Failed</span>';
            }

            html += '<label class="flex items-center justify-between p-2.5 hover:bg-indigo-50/60 cursor-pointer transition-colors border-b border-gray-100 last:border-b-0 select-none">';
            html += '<div class="flex items-center gap-2.5">';
            html += '<input type="checkbox" name="selected_recipient_keys[]" class="cust-item-chk rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 w-4 h-4 cursor-pointer" value="' + CRM.esc(r.key) + '" ' + (isChecked ? 'checked' : '') + '>';
            html += '<div>';
            html += '<div class="font-bold text-gray-900 text-xs flex items-center gap-1.5">';
            html += '<span>' + CRM.esc(r.name) + '</span>';
            if (r.company) html += '<span class="text-gray-400 font-normal">(' + CRM.esc(r.company) + ')</span>';
            html += '</div>';
            html += '<div class="text-[11px] text-gray-500 font-mono">' + CRM.esc(r.email) + (r.phone ? ' &bull; ' + CRM.esc(r.phone) : '') + '</div>';
            html += '</div>';
            html += '</div>';
            html += '<div class="flex items-center gap-1.5 flex-wrap justify-end">';
            html += reachBadge;
            html += '<span class="px-2 py-0.5 text-[10px] rounded font-bold bg-indigo-100 text-indigo-800 uppercase">' + CRM.esc(r.status || 'Customer') + '</span>';
            html += '</div>';
            html += '</label>';
        });

        $('#custs-checklist-container').html(html);
        updateCustsBadge();
    }

    function updateCustsBadge() {
        var count = selectedCustomers.size;
        var total = customersData.length;
        if (count === total && total > 0) {
            $('#badge-custs-count').text('All (' + total + ') Selected');
        } else {
            $('#badge-custs-count').text(count + ' of ' + total + ' Selected');
        }
        updateOverallAudienceSummary();
    }

    $(document).on('change', '.cust-item-chk', function () {
        var key = $(this).val();
        if ($(this).is(':checked')) {
            selectedCustomers.add(key);
        } else {
            selectedCustomers.delete(key);
        }
        updateCustsBadge();
    });

    // Quick Batch Selection for Customers
    $(document).on('click', '.btn-custs-select-batch', function () {
        var count = parseInt($(this).data('count')) || 50;
        selectedCustomers.clear();
        $('.cust-item-chk').prop('checked', false);

        var added = 0;
        $('.cust-item-chk').each(function () {
            if (added < count) {
                $(this).prop('checked', true);
                selectedCustomers.add($(this).val());
                added++;
            }
        });
        updateCustsBadge();
        CRM.toast('info', 'Selected first ' + added + ' customers.');
    });

    // Smart Filter Tabs for Customers
    $(document).on('click', '.custs-tab-filter', function () {
        $('.custs-tab-filter').removeClass('active bg-indigo-600 text-white shadow-2xs').addClass('bg-white text-gray-700 hover:bg-indigo-50 border border-gray-200');
        $(this).addClass('active bg-indigo-600 text-white shadow-2xs').removeClass('bg-white text-gray-700 hover:bg-indigo-50 border border-gray-200');
        custsReachFilter = $(this).data('filter') || 'all';
        loadCustomers();
    });

    $('#btn-custs-select-all').on('click', function () {
        $('.cust-item-chk').prop('checked', true);
        $.each(customersData, function(i, r) { selectedCustomers.add(r.key); });
        updateCustsBadge();
    });

    $('#btn-custs-deselect-all').on('click', function () {
        $('.cust-item-chk').prop('checked', false);
        selectedCustomers.clear();
        updateCustsBadge();
    });

    $('#custs-search-input').on('input', function () {
        renderCustomersList();
    });

    // ── FOLLOW-UP SCHEDULE & LIVE DATE PREVIEW ──────────────────────────────
    function formatFollowupDate(d) {
        var months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
        var day = ('0' + d.getDate()).slice(-2);
        var m = months[d.getMonth()];
        var y = d.getFullYear();
        return day + ' ' + m + ' ' + y;
    }

    function updateFollowupPreview() {
        var mode = $('#select-followup-schedule').val();
        var now = new Date();
        var targetDate = new Date();

        if (mode === 'none' || !mode) {
            $('#wrap-custom-followup-date').addClass('hidden');
            $('#wrap-custom-followup-days').addClass('hidden');
            $('#followup-preview-badge')
                .removeClass('text-indigo-700 bg-indigo-100/90 border-indigo-200')
                .addClass('text-gray-500 bg-gray-100 border-gray-200')
                .html('<i class="fa fa-ban text-[10px]"></i> No Follow-Up');
            return;
        }

        $('#followup-preview-badge')
            .removeClass('text-gray-500 bg-gray-100 border-gray-200')
            .addClass('text-indigo-700 bg-indigo-100/90 border-indigo-200');

        if (mode === 'custom') {
            $('#wrap-custom-followup-date').removeClass('hidden');
            $('#wrap-custom-followup-days').addClass('hidden');
            var customVal = $('#input-custom-followup-date').val();
            if (customVal) {
                var parts = customVal.split('-');
                if (parts.length === 3) {
                    targetDate = new Date(parseInt(parts[0], 10), parseInt(parts[1], 10) - 1, parseInt(parts[2], 10));
                    $('#followup-preview-badge').html('<i class="fa fa-calendar-check-o text-[10px]"></i> Due: ' + formatFollowupDate(targetDate));
                    return;
                }
            }
            $('#followup-preview-badge').text('Pick custom date');
            return;
        } else if (mode === 'custom_days') {
            $('#wrap-custom-followup-days').removeClass('hidden');
            $('#wrap-custom-followup-date').addClass('hidden');
            var days = parseInt($('#input-custom-followup-days').val(), 10) || 1;
            targetDate.setDate(now.getDate() + days);
            $('#followup-preview-badge').html('<i class="fa fa-calendar-check-o text-[10px]"></i> Due: ' + formatFollowupDate(targetDate));
            return;
        } else {
            $('#wrap-custom-followup-date').addClass('hidden');
            $('#wrap-custom-followup-days').addClass('hidden');
            var dCount = parseInt(mode, 10) || 3;
            targetDate.setDate(now.getDate() + dCount);
            $('#followup-preview-badge').html('<i class="fa fa-calendar-check-o text-[10px]"></i> Due: ' + formatFollowupDate(targetDate));
        }
    }

    $('#select-followup-schedule').on('change', function () {
        updateFollowupPreview();
    });

    $('#input-custom-followup-date').on('change input', function () {
        updateFollowupPreview();
    });

    $('#input-custom-followup-days').on('input change', function () {
        updateFollowupPreview();
    });

    $('#select-campaign-type').on('change', function () {
        var currentSchedule = $('#select-followup-schedule').val();
        // Keep 'none' untouched if user selected 'none'
        if (currentSchedule === 'none') {
            return;
        }
        var defDays = $(this).find(':selected').data('days');
        if (defDays && currentSchedule !== 'custom' && currentSchedule !== 'custom_days') {
            var exists = $('#select-followup-schedule option[value="' + defDays + '"]').length > 0;
            if (exists) {
                $('#select-followup-schedule').val(String(defDays)).trigger('change');
            }
        }
    });

    updateFollowupPreview();


    // ── CONTACT BOOK LOADER & RENDERER ──────────────────────────────────────
    function loadContacts() {
        $('#contacts-checklist-container').html('<div class="p-4 text-center text-gray-400 text-xs"><i class="fa fa-spinner fa-spin text-amber-600 text-base mb-1 block"></i> Loading contacts...</div>');

        $.getJSON(BASE_URL + 'communications/get_audience_recipients_ajax', {
            recipient_types: ['contact_book']
        }, function (res) {
            if (res.status === 'success' && res.data) {
                contactsData = res.data || [];
                contsLoaded = true;
                selectedContacts.clear();
                $.each(contactsData, function (i, r) {
                    selectedContacts.add(r.key);
                });
                renderContactsList();
                updateLivePreview();
            } else {
                $('#contacts-checklist-container').html('<div class="p-4 text-center text-rose-500 text-xs">Failed to load contacts list.</div>');
            }
        });
    }

    function renderContactsList() {
        var q = ($('#contacts-search-input').val() || '').toLowerCase().trim();
        var filtered = $.grep(contactsData, function (r) {
            if (!q) return true;
            return ((r.name || '') + ' ' + (r.company || '') + ' ' + (r.email || '')).toLowerCase().indexOf(q) !== -1;
        });

        if (filtered.length === 0) {
            $('#contacts-checklist-container').html('<div class="p-4 text-center text-gray-400 text-xs">No contacts found matching query.</div>');
            updateContactsBadge();
            return;
        }

        var html = '';
        $.each(filtered, function (i, r) {
            var isChecked = selectedContacts.has(r.key);
            html += '<label class="flex items-center justify-between p-2.5 hover:bg-amber-50/60 cursor-pointer transition-colors border-b border-gray-100 last:border-b-0 select-none">';
            html += '<div class="flex items-center gap-2.5">';
            html += '<input type="checkbox" name="selected_recipient_keys[]" class="contact-item-chk rounded border-gray-300 text-amber-600 focus:ring-amber-500 w-4 h-4 cursor-pointer" value="' + CRM.esc(r.key) + '" ' + (isChecked ? 'checked' : '') + '>';
            html += '<div>';
            html += '<div class="font-bold text-gray-900 text-xs flex items-center gap-1.5">';
            html += '<span>' + CRM.esc(r.name) + '</span>';
            if (r.company) html += '<span class="text-gray-400 font-normal">(' + CRM.esc(r.company) + ')</span>';
            html += '</div>';
            html += '<div class="text-[11px] text-gray-500 font-mono">' + CRM.esc(r.email) + (r.phone ? ' &bull; ' + CRM.esc(r.phone) : '') + '</div>';
            html += '</div>';
            html += '</div>';
            html += '<span class="px-2 py-0.5 text-[10px] rounded font-bold bg-amber-100 text-amber-800 uppercase">Contact</span>';
            html += '</label>';
        });

        $('#contacts-checklist-container').html(html);
        updateContactsBadge();
    }

    function updateContactsBadge() {
        var count = selectedContacts.size;
        var total = contactsData.length;
        if (count === total && total > 0) {
            $('#badge-contacts-count').text('All (' + total + ') Selected');
        } else {
            $('#badge-contacts-count').text(count + ' of ' + total + ' Selected');
        }
        updateOverallAudienceSummary();
    }

    $(document).on('change', '.contact-item-chk', function () {
        var key = $(this).val();
        if ($(this).is(':checked')) {
            selectedContacts.add(key);
        } else {
            selectedContacts.delete(key);
        }
        updateContactsBadge();
    });

    $('#btn-contacts-select-all').on('click', function () {
        $('.contact-item-chk').prop('checked', true);
        $.each(contactsData, function(i, r) { selectedContacts.add(r.key); });
        updateContactsBadge();
    });

    $('#btn-contacts-deselect-all').on('click', function () {
        $('.contact-item-chk').prop('checked', false);
        selectedContacts.clear();
        updateContactsBadge();
    });

    $('#contacts-search-input').on('input', function () {
        renderContactsList();
    });


    // ── OVERALL AUDIENCE SUMMARY & PREVIEW ──────────────────────────────────
    function updateOverallAudienceSummary() {
        var hasLeads = $('#aud-chk-leads').is(':checked');
        var hasCusts = $('#aud-chk-custs').is(':checked');
        var hasConts = $('#aud-chk-contacts').is(':checked');

        var totalCount = 0;
        var sampleList = [];

        if (hasLeads) {
            totalCount += selectedLeads.size;
            $.each(leadsData, function(i, r) {
                if (selectedLeads.has(r.key) && sampleList.length < 5) sampleList.push(r);
            });
        }
        if (hasCusts) {
            totalCount += selectedCustomers.size;
            $.each(customersData, function(i, r) {
                if (selectedCustomers.has(r.key) && sampleList.length < 10) sampleList.push(r);
            });
        }
        if (hasConts) {
            totalCount += selectedContacts.size;
            $.each(contactsData, function(i, r) {
                if (selectedContacts.has(r.key) && sampleList.length < 15) sampleList.push(r);
            });
        }

        if (!hasLeads && !hasCusts && !hasConts) {
            $('#badge-audience-count').text('0 Selected (Check at least 1 audience group)');
            $('#audience-preview-total').text('0 recipients');
            $('#part-total-badge').text('0');
            $('#audience-preview-list').html('<div class="py-4 text-center text-rose-500 text-xs font-semibold">Please select at least one audience group (Leads, Customers, or Contact Book).</div>');
            return;
        }

        $('#badge-audience-count').text(totalCount.toLocaleString() + ' Unique Contacts Selected');
        $('#audience-preview-total').text(totalCount.toLocaleString() + ' recipients eligible');
        $('#part-total-badge').text(totalCount.toLocaleString());
        $('#input-partition-limit').attr('max', totalCount);
        if (typeof updatePartitionFeedback === 'function') updatePartitionFeedback();

        var listHtml = '';
        if (sampleList.length > 0) {
            $.each(sampleList, function (i, item) {
                var badgeColor = 'bg-gray-100 text-gray-700';
                var typeLabel = item.source_type || item.type || 'Contact';
                if (typeLabel.toLowerCase().indexOf('lead') !== -1) {
                    badgeColor = 'bg-emerald-100 text-emerald-800';
                } else if (typeLabel.toLowerCase().indexOf('customer') !== -1) {
                    badgeColor = 'bg-indigo-100 text-indigo-800';
                } else if (typeLabel.toLowerCase().indexOf('contact') !== -1) {
                    badgeColor = 'bg-amber-100 text-amber-800';
                }

                listHtml += '<div class="py-2 flex items-center justify-between border-b border-gray-100 last:border-b-0">';
                listHtml += '<div><strong class="text-gray-800 font-semibold">' + CRM.esc(item.name || item.first_name || 'Recipient') + '</strong> ';
                if (item.company) listHtml += '<span class="text-gray-400 font-normal">(' + CRM.esc(item.company) + ')</span>';
                listHtml += '<span class="block text-gray-500 font-mono text-[11px]">' + CRM.esc(item.email) + '</span></div>';
                listHtml += '<span class="px-2 py-0.5 text-[10px] rounded font-bold ' + badgeColor + ' uppercase">' + CRM.esc(typeLabel) + '</span>';
                listHtml += '</div>';
            });
            if (totalCount > sampleList.length) {
                listHtml += '<div class="py-2 text-center text-xs text-gray-400 font-medium">+ ' + (totalCount - sampleList.length) + ' more recipients</div>';
            }
        } else {
            listHtml = '<div class="py-4 text-center text-gray-400 text-xs">No recipients selected matching criteria.</div>';
        }
        $('#audience-preview-list').html(listHtml);
        updateLivePreview();
    }

    // Audience Checkbox Event Listeners
    $(document).on('change', '.audience-chk', function () {
        syncAudienceCardsUI();
    });

    // Quick Audience Selection Shortcut Buttons
    $('#btn-aud-select-all').on('click', function () {
        $('.audience-chk').prop('checked', true);
        syncAudienceCardsUI();
    });

    $('#btn-aud-leads-only').on('click', function () {
        $('#aud-chk-leads').prop('checked', true);
        $('#aud-chk-custs, #aud-chk-contacts').prop('checked', false);
        syncAudienceCardsUI();
    });

    $('#btn-aud-custs-only').on('click', function () {
        $('#aud-chk-custs').prop('checked', true);
        $('#aud-chk-leads, #aud-chk-contacts').prop('checked', false);
        syncAudienceCardsUI();
    });

    $('#btn-aud-clear').on('click', function () {
        $('.audience-chk').prop('checked', false);
        syncAudienceCardsUI();
    });

    // ─────────────────────────────────────────────────────────────────────────
    // Instant Sender Mailbox Switcher Handler
    // ─────────────────────────────────────────────────────────────────────────
    $('#select-sender-smtp').on('change', function () {
        var $opt = $(this).find('option:selected');
        var sName = $opt.data('sender') || 'Auto-Rotated Pool';
        var sEmail = $opt.data('email') || 'Smart Fair-Share Mailbox Pool';
        $('#preview-header-from').html(CRM.esc(sName) + ' &lt;' + CRM.esc(sEmail) + '&gt;');
    });

    // Initial sync and count calculation
    syncAudienceCardsUI();


    // ─────────────────────────────────────────────────────────────────────────
    // Live Hostinger SMTP Pool Status Polling & Updates
    // ─────────────────────────────────────────────────────────────────────────
    function refreshBulkMailSmtpPool() {
        $.getJSON(BASE_URL + 'communications/smtp_pool_status', function (res) {
            if (res.status === 'success' && res.data) {
                var p = res.data;
                $('#bm-pool-remaining').text(Number(p.total_remaining).toLocaleString());
                $('#bm-pool-capacity').text(Number(p.total_capacity).toLocaleString());
                $('#bm-pool-sent').text(Number(p.total_sent).toLocaleString());
                $('#bm-pool-progress').css('width', (p.percent_remaining || 0) + '%');

                if (p.alerts && p.alerts.length > 0) {
                    var alertsHtml = '';
                    $.each(p.alerts, function (i, alt) {
                        alertsHtml += '<div class="px-3 py-1.5 bg-amber-500/20 border border-amber-400/40 rounded-lg text-[11px] text-amber-200 flex items-center gap-2">';
                        alertsHtml += '<i class="fa fa-exclamation-circle text-amber-300"></i><span>' + CRM.esc(alt) + '</span></div>';
                    });
                    $('#bm-smtp-alerts').html(alertsHtml).removeClass('hidden');
                } else {
                    $('#bm-smtp-alerts').html('').addClass('hidden');
                }
            }
        });
    }


    // ─────────────────────────────────────────────────────────────────────────
    // 7. 1-Click Send Test Email
    // ─────────────────────────────────────────────────────────────────────────
    $('#btn-send-test-email').on('click', function () {
        var testEmail = $('#input-test-email').val().trim();
        var subject   = $('#mail-subject').val().trim();
        var body      = $('#summernote-editor').summernote('code');
        var productId = $('#select-product').val();

        if (!testEmail) {
            CRM.toast('error', 'Please enter a recipient test email address.');
            $('#input-test-email').focus();
            return;
        }

        var $btn = $(this);
        var origText = $btn.html();
        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Sending...');

        $.ajax({
            url: BASE_URL + 'communications/send_test_email',
            method: 'POST',
            data: {
                test_email: testEmail,
                subject: subject,
                body: body,
                product_id: productId,
                sender_smtp_id: $('#select-sender-smtp').val(),
                [CI3_CSRF_NAME]: CI3_CSRF_HASH
            },
            success: function (res) {
                if (res.status === 'success') {
                    CRM.toast('success', res.message || 'Test email dispatched!');
                } else {
                    CRM.toast('error', res.message || 'Failed to send test email.');
                }
            },
            error: function () {
                CRM.toast('error', 'Test email request failed. Please check network.');
            },
            complete: function () {
                $btn.prop('disabled', false).html(origText);
            }
        });
    });


    // ─────────────────────────────────────────────────────────────────────────
    // 8. Launch Bulk Mail Campaign (Form Submit)
    // ─────────────────────────────────────────────────────────────────────────
    $('#form-bulk-mail').on('submit', function (e) {
        e.preventDefault();

        var subject = $('#mail-subject').val().trim();
        var body    = $('#summernote-editor').summernote('code');

        if (!subject) {
            CRM.toast('error', 'Subject line is required.');
            $('#mail-subject').focus();
            return;
        }

        if ($('#summernote-editor').summernote('isEmpty')) {
            CRM.toast('error', 'Please enter your email content.');
            return;
        }

        var $btn = $('#btn-launch-campaign');
        var origHtml = $btn.html();
        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Dispatching Bulk Emails & Logging Activities...');

        var formData = $(this).serialize();

        $.ajax({
            url: $(this).attr('action'),
            method: 'POST',
            data: formData,
            success: function (resp) {
                if (resp.status === 'success') {
                    CRM.toast('success', resp.message);
                    refreshBulkMailSmtpPool();

                    // Switch to history tab
                    $('[data-tab="tab-history"]').trigger('click');
                    loadCampaignHistory();
                } else {
                    CRM.toast('error', resp.message || 'Bulk mail dispatch failed.');
                }
            },
            error: function (xhr) {
                var json = xhr.responseJSON;
                CRM.toast('error', (json && json.message) || 'Error processing campaign.');
            },
            complete: function () {
                $btn.prop('disabled', false).html(origHtml);
            }
        });
    });


    // ─────────────────────────────────────────────────────────────────────────
    // 9. Template Creator & Editor Modal
    // ─────────────────────────────────────────────────────────────────────────
    $('#btn-open-create-template, #btn-quick-new-template, .btn-trigger-add-template').on('click', function () {
        $('#modal-template-title').text('Create New Email Template');
        $('#tpl-edit-id').val('');
        $('#tpl-input-name').val('');
        $('#tpl-input-category').val('general');
        $('#tpl-input-product').val($('#select-product').val() || '');
        $('#tpl-input-subject').val('');
        $('#modal-summernote').summernote('code', '');
        $('#modal-template-editor').removeClass('hidden');
    });

    $('#btn-close-template-modal, #btn-cancel-template-modal').on('click', function () {
        $('#modal-template-editor').addClass('hidden');
    });

    // Save Template via AJAX
    $('#form-save-template').on('submit', function (e) {
        e.preventDefault();

        var name    = $('#tpl-input-name').val().trim();
        var subject = $('#tpl-input-subject').val().trim();
        var body    = $('#modal-summernote').summernote('code');

        if (!name || !subject || $('#modal-summernote').summernote('isEmpty')) {
            CRM.toast('error', 'Please fill in Template Name, Subject, and Content.');
            return;
        }

        $('#modal-summernote').val(body);
        var postData = $(this).serialize();
        if (typeof CI3_CSRF_NAME !== 'undefined' && typeof CI3_CSRF_HASH !== 'undefined') {
            if (postData.indexOf(encodeURIComponent(CI3_CSRF_NAME) + '=') === -1 && postData.indexOf(CI3_CSRF_NAME + '=') === -1) {
                postData += (postData ? '&' : '') + encodeURIComponent(CI3_CSRF_NAME) + '=' + encodeURIComponent(CI3_CSRF_HASH);
            }
        }

        $.ajax({
            url: BASE_URL + 'communications/save_template_ajax',
            method: 'POST',
            data: postData,
            success: function (res) {
                if (res.status === 'success') {
                    CRM.toast('success', res.message || 'Template saved successfully!');
                    $('#modal-template-editor').addClass('hidden');

                    // Reload dropdown and grid
                    reloadTemplateDropdown($('#select-product').val(), $('#filter-category').val());
                    setTimeout(function () {
                        location.reload();
                    }, 800);
                } else {
                    CRM.toast('error', res.message || 'Failed to save template.');
                }
            }
        });
    });

    // Use Template from Grid Card
    $(document).on('click', '.btn-use-template', function () {
        var tid = $(this).data('id');
        $('[data-tab="tab-compose"]').trigger('click');
        $('#select-template').val(tid).trigger('change');
        loadTemplateDetail(tid);
    });

    // Edit Template from Grid Card
    $(document).on('click', '.btn-edit-template', function () {
        var tid = $(this).data('id');
        $.getJSON(BASE_URL + 'communications/get_template_ajax/' + tid, function (res) {
            if (res.status === 'success' && res.data) {
                var t = res.data;
                $('#modal-template-title').text('Edit Email Template');
                $('#tpl-edit-id').val(t.id);
                $('#tpl-input-name').val(t.name);
                $('#tpl-input-category').val(t.category);
                $('#tpl-input-product').val(t.product_id || '');
                $('#tpl-input-subject').val(t.subject);
                $('#modal-summernote').summernote('code', t.body);
                $('#modal-template-editor').removeClass('hidden');
            }
        });
    });

    // Delete Template from Grid Card
    $(document).on('click', '.btn-delete-template', function () {
        var tid = $(this).data('id');
        if (confirm('Are you sure you want to delete this email template?')) {
            $.post(BASE_URL + 'communications/delete_template_ajax', {
                id: tid,
                [CI3_CSRF_NAME]: CI3_CSRF_HASH
            }, function (res) {
                if (res.status === 'success') {
                    CRM.toast('success', 'Template removed.');
                    $('.template-card[data-id="' + tid + '"]').fadeOut(250, function () {
                        $(this).remove();
                    });
                }
            });
        }
    });

    // Filter Template Grid Cards
    $('#grid-filter-product, #grid-filter-category').on('change', function () {
        var fProd = $('#grid-filter-product').val();
        var fCat  = $('#grid-filter-category').val();

        $('.template-card').each(function () {
            var cardProd = $(this).data('product');
            var cardCat  = $(this).data('cat');
            var matchProd = !fProd || cardProd == fProd || !cardProd;
            var matchCat  = fCat === 'all' || cardCat === fCat;

            if (matchProd && matchCat) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    });


    // ─────────────────────────────────────────────────────────────────────────
    // 10. Campaign History & Follow-up Details Modal
    // ─────────────────────────────────────────────────────────────────────────
    function loadCampaignHistory() {
        $.getJSON(BASE_URL + 'communications/history_ajax', function (res) {
            if (res.status === 'success' && res.data) {
                var stats = res.data.stats || {};
                var camps = res.data.campaigns || [];

                $('#stat-total-sent').text((stats.sent || 0).toLocaleString());
                $('#stat-queued').text((stats.queued || 0).toLocaleString());

                var tbody = '';
                if (camps.length > 0) {
                    $.each(camps, function (i, c) {
                        var badge = 'bg-emerald-50 text-emerald-700 border-emerald-200';
                        if (c.status === 'pending') badge = 'bg-amber-50 text-amber-700 border-amber-200';
                        else if (c.status === 'processing') badge = 'bg-blue-50 text-blue-700 border-blue-200';

                        tbody += '<tr class="hover:bg-gray-50/60 transition-colors">';
                        tbody += '<td class="py-3 px-3 font-mono font-bold text-gray-800">#' + c.id + '</td>';
                        tbody += '<td class="py-3 px-3"><span class="font-bold text-gray-900 block">' + CRM.esc(c.subject) + '</span>';
                        tbody += '<span class="text-[11px] text-gray-400">' + CRM.esc(c.template_name || 'Custom Compose') + '</span></td>';
                        tbody += '<td class="py-3 px-3"><span class="font-medium text-gray-800 block">' + CRM.esc(c.recipient_type) + '</span>';
                        if (c.product_name) tbody += '<span class="text-[10px] font-bold text-purple-700 bg-purple-50 px-1.5 py-0.5 rounded border border-purple-200">' + CRM.esc(c.product_name) + '</span>';
                        tbody += '</td>';
                        tbody += '<td class="py-3 px-3 text-center font-bold text-emerald-600 font-mono">' + (c.count_sent || 0) + '</td>';
                        tbody += '<td class="py-3 px-3 text-center font-bold text-amber-600 font-mono">' + (c.count_queued || 0) + '</td>';
                        tbody += '<td class="py-3 px-3 text-center font-bold text-rose-600 font-mono">' + (c.count_failed || 0) + '</td>';
                        tbody += '<td class="py-3 px-3"><span class="px-2 py-0.5 text-[10px] font-bold rounded-full border ' + badge + ' uppercase">' + c.status + '</span></td>';
                        tbody += '<td class="py-3 px-3 text-gray-500 text-[11px] whitespace-nowrap">' + CRM.time_ago(c.created_at) + '</td>';
                        tbody += '<td class="py-3 px-3 text-right"><button type="button" class="btn-view-campaign-details px-2.5 py-1 text-xs font-semibold bg-gray-100 hover:bg-blue-50 text-gray-700 hover:text-blue-700 rounded-lg transition-colors border border-gray-200" data-id="' + c.id + '"><i class="fa fa-eye mr-1"></i> Details</button></td>';
                        tbody += '</tr>';
                    });
                } else {
                    tbody = '<tr><td colspan="9" class="py-8 text-center text-gray-400 text-xs"><i class="fa fa-paper-plane-o text-2xl block mb-2 opacity-50"></i>No campaigns sent yet.</td></tr>';
                }
                $('#history-table-body').html(tbody);
            }
        });
    }

    $('#btn-refresh-history').on('click', function () {
        loadCampaignHistory();
        CRM.toast('info', 'Campaign logs refreshed.');
    });

    // View Campaign Details Modal
    $(document).on('click', '.btn-view-campaign-details', function () {
        var cid = $(this).data('id');
        $.getJSON(BASE_URL + 'communications/campaign_detail_ajax/' + cid, function (res) {
            if (res.status === 'success' && res.data) {
                var c = res.data;
                $('#detail-modal-title').text('Campaign #' + c.id + ' — Details & Follow-up Log');
                $('#detail-recipient-type').text(c.recipient_type);
                $('#detail-product-name').text(c.product_name || 'All Products');
                $('#detail-total-recipients').text(c.total_recipients || 0);
                $('#detail-created-at').text(c.created_at);
                $('#detail-subject').text(c.subject);
                $('#detail-message-body').html(c.message);

                var qHtml = '';
                var items = c.items || [];
                if (items.length > 0) {
                    $.each(items, function (i, itm) {
                        var itmBadge = (itm.status === 'sent') ? 'text-emerald-600 bg-emerald-50' : ((itm.status === 'failed') ? 'text-rose-600 bg-rose-50' : 'text-amber-600 bg-amber-50');
                        qHtml += '<tr>';
                        qHtml += '<td class="p-2.5 font-bold text-gray-800">' + CRM.esc(itm.recipient_name || itm.lead_title || itm.customer_name || 'Contact') + '</td>';
                        qHtml += '<td class="p-2.5 text-gray-600">' + CRM.esc(itm.recipient_email) + '</td>';
                        qHtml += '<td class="p-2.5 text-center"><span class="px-2 py-0.5 rounded text-[10px] font-bold ' + itmBadge + '">' + itm.status.toUpperCase() + '</span></td>';
                        qHtml += '<td class="p-2.5 text-right text-gray-400 text-[11px]">' + (itm.sent_at || '-') + '</td>';
                        qHtml += '</tr>';
                    });
                } else {
                    qHtml = '<tr><td colspan="4" class="p-4 text-center text-gray-400">No recipient items found in queue.</td></tr>';
                }
                $('#detail-queue-table').html(qHtml);
                $('#modal-campaign-detail').removeClass('hidden');
            }
        });
    });

    $('#btn-close-campaign-modal, #btn-close-campaign-modal-bottom').on('click', function () {
        $('#modal-campaign-detail').addClass('hidden');
    });

    // ── PARTITION / BATCH COUNT CONTROL ─────────────────────────────────────
    $('input[name="partition_mode"]').on('change', function () {
        if ($(this).val() === 'custom') {
            $('#wrap-partition-input').removeClass('hidden').addClass('flex');
            $('#input-partition-limit').focus();
            updatePartitionFeedback();
        } else {
            $('#wrap-partition-input').addClass('hidden').removeClass('flex');
            $('#part-feedback-badge').text('Full batch will be dispatched');
        }
    });

    $('#input-partition-limit').on('input change', function () {
        updatePartitionFeedback();
    });

    function updatePartitionFeedback() {
        var partMode = $('input[name="partition_mode"]:checked').val();
        if (partMode !== 'custom') {
            $('#part-feedback-badge').text('Full batch will be dispatched');
            return;
        }
        var total = parseInt($('#part-total-badge').text().replace(/,/g, ''), 10) || 0;
        var limit = parseInt($('#input-partition-limit').val(), 10) || 0;
        if (limit <= 0) {
            $('#part-feedback-badge').text('Enter partition count to send now');
        } else if (limit >= total && total > 0) {
            $('#part-feedback-badge').text('All ' + total + ' will be dispatched now (no remainder)');
        } else {
            var remain = total - limit;
            $('#part-feedback-badge').html('<strong>' + limit + '</strong> sent now, <strong>' + remain + '</strong> held in queue');
        }
    }

    // ── MANAGE OUTREACH STAGES MODAL ────────────────────────────────────────
    var currentStagesCache = [];

    function syncStagesFromSelect() {
        currentStagesCache = [];
        $('#select-campaign-type option').each(function () {
            var id = $(this).val();
            var name = $(this).text().trim();
            var days = parseInt($(this).data('days'), 10) || 3;
            var locked = (id === 'outreach');
            currentStagesCache.push({ id: id, name: name, days: days, locked: locked });
        });
    }

    syncStagesFromSelect();

    $('#btn-manage-stages').on('click', function () {
        syncStagesFromSelect();
        renderStagesEditor();
        $('#modal-manage-stages').removeClass('hidden');
    });

    $('.btn-close-stage-modal').on('click', function () {
        $('#modal-manage-stages').addClass('hidden');
    });

    function renderStagesEditor() {
        var html = '';
        $.each(currentStagesCache, function (idx, stg) {
            var isLocked = (stg.id === 'outreach');
            html += '<div class="stage-editor-row p-3 rounded-xl border ' + (isLocked ? 'bg-gray-50 border-gray-200' : 'bg-white border-indigo-100 hover:border-indigo-300') + ' transition-all flex flex-col sm:flex-row sm:items-center justify-between gap-2.5" data-id="' + CRM.esc(stg.id) + '" data-locked="' + (isLocked ? '1' : '0') + '">';
            html += '  <div class="flex items-center gap-2 flex-1">';
            html += '    <span class="w-6 h-6 rounded-lg ' + (isLocked ? 'bg-amber-100 text-amber-700' : 'bg-indigo-100 text-indigo-700') + ' flex items-center justify-center text-xs flex-shrink-0">';
            html += '      <i class="fa ' + (isLocked ? 'fa-lock' : 'fa-tag') + '"></i>';
            html += '    </span>';
            if (isLocked) {
                html += '    <div class="flex-1">';
                html += '      <span class="text-xs font-bold text-gray-800 block">' + CRM.esc(stg.name) + '</span>';
                html += '      <span class="text-[10px] text-gray-400 font-semibold uppercase">Intro Pitch (System Base - Locked)</span>';
                html += '    </div>';
            } else {
                html += '    <input type="text" class="stage-name-input flex-1 px-2.5 py-1.5 bg-gray-50 border border-gray-200 rounded-lg text-xs font-semibold text-gray-800 focus:bg-white focus:ring-1 focus:ring-indigo-500 focus:outline-none" value="' + CRM.esc(stg.name) + '" placeholder="Stage Name e.g. 🔁 Follow-Up #1">';
            }
            html += '  </div>';

            html += '  <div class="flex items-center justify-end gap-2 flex-shrink-0">';
            html += '    <div class="flex items-center gap-1">';
            html += '      <span class="text-[11px] text-gray-500 font-medium">Gap:</span>';
            html += '      <input type="number" min="1" max="90" class="stage-days-input w-14 px-2 py-1 bg-gray-50 border border-gray-200 rounded text-xs font-mono font-bold text-gray-800 focus:bg-white focus:outline-none text-center" value="' + (parseInt(stg.days, 10) || 3) + '">';
            html += '      <span class="text-[11px] text-gray-500">Days</span>';
            html += '    </div>';

            if (!isLocked) {
                html += '    <button type="button" class="btn-remove-stage-row p-1.5 text-gray-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-colors" title="Delete stage">';
                html += '      <i class="fa fa-trash-o text-sm"></i>';
                html += '    </button>';
            } else {
                html += '    <span class="p-1.5 text-gray-300" title="Fixed stage"><i class="fa fa-lock text-sm"></i></span>';
            }
            html += '  </div>';
            html += '</div>';
        });
        $('#stages-editor-list').html(html);
    }

    $('#btn-add-stage-row').on('click', function () {
        var count = currentStagesCache.length;
        var nextId = 'followup_' + count;
        currentStagesCache.push({
            id: nextId,
            name: '⚡ Follow-Up #' + count + ' (Custom)',
            days: 2,
            locked: false
        });
        renderStagesEditor();
    });

    $(document).on('click', '.btn-remove-stage-row', function () {
        var row = $(this).closest('.stage-editor-row');
        var id = row.data('id');
        currentStagesCache = $.grep(currentStagesCache, function (s) {
            return s.id !== id || s.locked;
        });
        renderStagesEditor();
    });

    $('#btn-save-stages-submit').on('click', function () {
        var updated = [];
        $('#stages-editor-list .stage-editor-row').each(function () {
            var row = $(this);
            var id = row.data('id');
            var isLocked = row.data('locked') == '1';
            var name = isLocked ? (row.find('.text-gray-800').text().trim() || '🚀 Initial Outreach (First Pitch)') : row.find('.stage-name-input').val().trim();
            var days = parseInt(row.find('.stage-days-input').val(), 10) || 3;

            if (name) {
                updated.push({ id: id, name: name, days: days, locked: isLocked });
            }
        });

        if (updated.length === 0) {
            CRM.toast('error', 'Please define at least one stage.');
            return;
        }

        var btn = $(this);
        btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Saving...');

        var postPayload = {
            stages: updated
        };
        if (typeof CI3_CSRF_NAME !== 'undefined' && typeof CI3_CSRF_HASH !== 'undefined') {
            postPayload[CI3_CSRF_NAME] = CI3_CSRF_HASH;
        }

        $.ajax({
            url: BASE_URL + 'communications/save_outreach_stages_ajax',
            method: 'POST',
            data: postPayload,
            success: function (res) {
                btn.prop('disabled', false).html('<i class="fa fa-check"></i> Save & Apply Changes');
                if (res.status === 'success') {
                    currentStagesCache = (res.data && res.data.stages) ? res.data.stages : updated;
                    var selectHtml = '';
                    var currentVal = $('#select-campaign-type').val();
                    $.each(currentStagesCache, function (i, st) {
                        var sel = (st.id === currentVal) ? 'selected' : '';
                        selectHtml += '<option value="' + CRM.esc(st.id) + '" data-days="' + st.days + '" ' + sel + '>' + CRM.esc(st.name) + '</option>';
                    });
                    $('#select-campaign-type').html(selectHtml);
                    $('#modal-manage-stages').addClass('hidden');
                    CRM.toast('success', res.message || 'Outreach stages updated successfully!');
                } else {
                    CRM.toast('error', res.message || 'Failed to save stages.');
                }
            },
            error: function () {
                btn.prop('disabled', false).html('<i class="fa fa-check"></i> Save & Apply Changes');
                CRM.toast('error', 'Server error while saving stages.');
            }
        });
    });

    // ── STAGE LOGS & SCHEDULE TRACKER MODAL ─────────────────────────────────
    $('#btn-view-stage-logs, #btn-refresh-stage-logs').on('click', function () {
        loadStageLogs($('#filter-logs-stage').val() || 'all');
        $('#modal-stage-logs').removeClass('hidden');
    });

    $('.btn-close-logs-modal').on('click', function () {
        $('#modal-stage-logs').addClass('hidden');
    });

    $('#filter-logs-stage').on('change', function () {
        loadStageLogs($(this).val());
    });

    function loadStageLogs(stageFilter) {
        $('#stage-logs-cards-grid').html('<div class="col-span-full py-4 text-center text-gray-400 text-xs"><i class="fa fa-spinner fa-spin mr-1"></i> Loading breakdown...</div>');
        $('#stage-logs-table-body').html('<tr><td colspan="5" class="py-6 text-center text-gray-400 text-xs"><i class="fa fa-spinner fa-spin mr-1"></i> Loading logs...</td></tr>');

        $.getJSON(BASE_URL + 'communications/get_stage_logs_ajax', { stage: stageFilter }, function (res) {
            if (res.status === 'success' && res.data) {
                var stages = res.data.stages || [];
                var logs = res.data.logs || [];

                // Render filter options if not populated
                if ($('#filter-logs-stage option').length <= 1) {
                    var optHtml = '<option value="all">All Stages</option>';
                    $.each(stages, function (i, st) {
                        optHtml += '<option value="' + CRM.esc(st.id) + '">' + CRM.esc(st.name) + '</option>';
                    });
                    $('#filter-logs-stage').html(optHtml);
                    if (stageFilter) $('#filter-logs-stage').val(stageFilter);
                }

                // Render Summary Cards
                var cardHtml = '';
                $.each(stages, function (i, st) {
                    cardHtml += '<div class="p-3 bg-indigo-50/50 border border-indigo-100 rounded-xl space-y-1">';
                    cardHtml += '  <span class="text-[11px] font-bold text-gray-700 block truncate" title="' + CRM.esc(st.name) + '">' + CRM.esc(st.name) + '</span>';
                    cardHtml += '  <div class="flex items-baseline gap-2">';
                    cardHtml += '    <span class="text-base font-extrabold text-indigo-700 font-mono">' + (st.sent || 0) + '</span>';
                    cardHtml += '    <span class="text-[10px] text-gray-400">sent</span>';
                    if (st.queued > 0) {
                        cardHtml += '    <span class="text-[10px] font-bold text-amber-600 font-mono">(' + st.queued + ' queued)</span>';
                    }
                    cardHtml += '  </div>';
                    if (st.next_due) {
                        cardHtml += '  <span class="text-[10px] text-blue-600 block"><i class="fa fa-clock-o"></i> Next due: ' + st.next_due + '</span>';
                    } else {
                        cardHtml += '  <span class="text-[10px] text-gray-400 block">-</span>';
                    }
                    cardHtml += '</div>';
                });
                $('#stage-logs-cards-grid').html(cardHtml || '<div class="col-span-full text-center text-gray-400 text-xs">No stages active.</div>');

                // Render Table Logs
                var tblHtml = '';
                if (logs.length > 0) {
                    $.each(logs, function (i, l) {
                        var stBadge = (l.status === 'sent') ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : ((l.status === 'failed') ? 'bg-rose-50 text-rose-700 border-rose-200' : 'bg-amber-50 text-amber-700 border-amber-200');
                        tblHtml += '<tr class="hover:bg-gray-50/80 transition-colors">';
                        tblHtml += '  <td class="py-2.5 px-3">';
                        tblHtml += '    <strong class="text-gray-900 block">' + CRM.esc(l.recipient_name || 'Lead') + '</strong>';
                        tblHtml += '    <span class="text-[11px] text-gray-400 font-mono">' + CRM.esc(l.recipient_email) + '</span>';
                        tblHtml += '  </td>';
                        tblHtml += '  <td class="py-2.5 px-3">';
                        tblHtml += '    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">' + CRM.esc(l.campaign_type || 'outreach') + '</span>';
                        tblHtml += '  </td>';
                        tblHtml += '  <td class="py-2.5 px-3 text-[11px] text-gray-600 whitespace-nowrap">' + (l.sent_at || '<span class="italic text-amber-500">In Queue</span>') + '</td>';
                        tblHtml += '  <td class="py-2.5 px-3 text-[11px] whitespace-nowrap">';
                        if (l.next_followup_date) {
                            tblHtml += '    <span class="inline-flex items-center gap-1 px-2 py-0.5 font-semibold bg-blue-50 text-blue-700 border border-blue-200 rounded-lg">';
                            tblHtml += '      <i class="fa fa-calendar-check-o text-[10px]"></i> ' + l.next_followup_date;
                            tblHtml += '    </span>';
                        } else {
                            tblHtml += '    <span class="inline-flex items-center gap-1 px-2 py-0.5 font-semibold bg-amber-50 text-amber-700 border border-amber-200 rounded-lg"><i class="fa fa-bolt text-[10px]"></i> Instant</span>';
                        }
                        tblHtml += '  </td>';
                        tblHtml += '  <td class="py-2.5 px-3 text-right whitespace-nowrap">';
                        tblHtml += '    <span class="px-2 py-0.5 text-[10px] font-bold rounded-full border uppercase ' + stBadge + '">' + CRM.esc(l.status) + '</span>';
                        tblHtml += '  </td>';
                        tblHtml += '</tr>';
                    });
                } else {
                    tblHtml = '<tr><td colspan="5" class="py-8 text-center text-gray-400 text-xs">No activity logs recorded for this stage yet.</td></tr>';
                }
                $('#stage-logs-table-body').html(tblHtml);
            } else {
                $('#stage-logs-cards-grid').html('<div class="col-span-full py-4 text-center text-rose-500 text-xs">Failed to load stage stats.</div>');
                $('#stage-logs-table-body').html('<tr><td colspan="5" class="py-6 text-center text-rose-500 text-xs">Error loading stage logs.</td></tr>');
            }
        });
    }
});
