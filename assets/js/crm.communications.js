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
                    var initialTplId = $('#select-template').val();
                    if (initialTplId) {
                        loadTemplateDetail(initialTplId);
                    } else if ($('#select-template option').length > 1) {
                        // Pick first template
                        $('#select-template').prop('selectedIndex', 1).trigger('change');
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
    });


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
    // 5. Live Email Client Preview (Mockup UI)
    // ─────────────────────────────────────────────────────────────────────────
    function updateLivePreview() {
        var rawSubject = $('#mail-subject').val() || '(No Subject Line)';
        var rawBody    = $('#summernote-editor').summernote('code') || '<p class="text-gray-400 italic">No content composed yet.</p>';

        var replacements = {
            '{{customer_name}}': 'Rajesh Kumar',
            '{{first_name}}': 'Rajesh',
            '{{last_name}}': 'Kumar',
            '{{company_name}}': 'TechNova Solutions Pvt Ltd',
            '{{email}}': 'rajesh@technova.com',
            '{{phone}}': '+91 9876543210',
            '{{product_name}}': currentProductMeta.name,
            '{{product_price}}': currentProductMeta.price,
            '{{login_url}}': BASE_URL + 'auth/login',
            '{{login_email}}': 'rajesh@technova.com',
            '{{temporary_password}}': 'Zazu@' + new Date().getFullYear(),
            '{{sender_name}}': 'Antigravity Team',
            '{{sender_phone}}': '+91 44 28765432',
            '{{current_date}}': new Date().toLocaleDateString('en-IN', { day: '2-digit', month: 'short', year: 'numeric' })
        };

        var renderedSubject = rawSubject;
        var renderedBody    = rawBody;

        $.each(replacements, function (tag, val) {
            renderedSubject = renderedSubject.split(tag).join(val);
            renderedBody    = renderedBody.split(tag).join(val);
        });

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
    // 6. Audience Recipient Counts & Drawer Inspection
    // ─────────────────────────────────────────────────────────────────────────
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

        // If leads selected, enable lead stage filter; else mute it
        var hasLeads = $('#aud-chk-leads').is(':checked');
        if (hasLeads) {
            $('#wrapper-lead-status').removeClass('opacity-40 pointer-events-none');
        } else {
            $('#wrapper-lead-status').addClass('opacity-40 pointer-events-none');
        }
    }

    function updateAudienceCount() {
        syncAudienceCardsUI();
        var types = getSelectedAudiences();
        var pId   = $('#select-product').val();
        var lStat = $('#select-lead-status').val();

        if (types.length === 0) {
            $('#badge-audience-count').text('0 Selected (Check at least 1 audience)');
            $('#audience-preview-total').text('0 recipients');
            $('#audience-preview-list').html('<div class="py-4 text-center text-rose-500 text-xs font-semibold">Please select at least one audience group (Leads, Customers, or Contact Book).</div>');
            return;
        }

        $('#badge-audience-count').html('<i class="fa fa-spinner fa-spin"></i> Calculating unique recipients...');

        $.getJSON(BASE_URL + 'communications/audience_count_ajax', {
            recipient_types: types,
            product_id: pId,
            lead_status: lStat
        }, function (res) {
            if (res.status === 'success' && res.data) {
                var total = res.data.total || 0;
                var samples = res.data.samples || [];

                $('#badge-audience-count').text(total.toLocaleString() + ' Unique Contacts Selected');
                $('#audience-preview-total').text(total.toLocaleString() + ' recipients eligible');

                var listHtml = '';
                if (samples.length > 0) {
                    $.each(samples, function (i, item) {
                        var badgeColor = 'bg-gray-100 text-gray-700';
                        var typeLabel = item.source_type || item.type || 'Contact';
                        if (typeLabel.toLowerCase().indexOf('lead') !== -1) {
                            badgeColor = 'bg-emerald-100 text-emerald-800';
                        } else if (typeLabel.toLowerCase().indexOf('customer') !== -1) {
                            badgeColor = 'bg-indigo-100 text-indigo-800';
                        } else if (typeLabel.toLowerCase().indexOf('contact') !== -1) {
                            badgeColor = 'bg-amber-100 text-amber-800';
                        }

                        listHtml += '<div class="py-2 flex items-center justify-between">';
                        listHtml += '<div><strong class="text-gray-800 font-semibold">' + CRM.esc(item.name || item.first_name || 'Recipient') + '</strong> ';
                        if (item.company) listHtml += '<span class="text-gray-400">(' + CRM.esc(item.company) + ')</span>';
                        listHtml += '<span class="block text-gray-500 font-mono text-[11px]">' + CRM.esc(item.email) + '</span></div>';
                        listHtml += '<span class="px-2 py-0.5 text-[10px] rounded font-bold ' + badgeColor + ' uppercase">' + CRM.esc(typeLabel) + '</span>';
                        listHtml += '</div>';
                    });
                } else {
                    listHtml = '<div class="py-4 text-center text-gray-400 text-xs">No verified recipients found matching the chosen audience filters.</div>';
                }
                $('#audience-preview-list').html(listHtml);
            }
        });
    }

    // Audience Checkbox Event Listeners
    $(document).on('change', '.audience-chk, #select-lead-status', function () {
        updateAudienceCount();
    });

    // Quick Audience Selection Shortcut Buttons
    $('#btn-aud-select-all').on('click', function () {
        $('.audience-chk').prop('checked', true);
        updateAudienceCount();
    });

    $('#btn-aud-leads-only').on('click', function () {
        $('#aud-chk-leads').prop('checked', true);
        $('#aud-chk-custs, #aud-chk-contacts').prop('checked', false);
        updateAudienceCount();
    });

    $('#btn-aud-custs-only').on('click', function () {
        $('#aud-chk-custs').prop('checked', true);
        $('#aud-chk-leads, #aud-chk-contacts').prop('checked', false);
        updateAudienceCount();
    });

    $('#btn-aud-clear').on('click', function () {
        $('.audience-chk').prop('checked', false);
        updateAudienceCount();
    });

    // Toggle Preview Drawer
    $('#btn-toggle-audience-drawer').on('click', function () {
        $('#audience-preview-drawer').toggleClass('hidden');
    });

    // Initial sync and count calculation
    updateAudienceCount();

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

        var postData = $(this).serialize();

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
});
