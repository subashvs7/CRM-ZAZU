/**
 * Leads Module JS - 33 Excel Fields, Drawer Import, Format Notes, and Full CRUD
 */
$(function() {
    'use strict';

    // 1. Initialize DataTable with 13 columns (Col 0: Checkbox, Col 1: ID, Col 12: Actions)
    if ($('#leads-table').length && !$.fn.DataTable.isDataTable('#leads-table')) {
        window.mainTable = $('#leads-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: BASE_URL + 'leads/datatable',
                data: function(d) {
                    d.status_filter = window.currentStatusFilter || '';
                    d.product_id    = window.currentProductFilter || '';
                }
            },
            columns: [
                { data: 0, orderable: false, searchable: false },
                { data: 1 },
                { data: 2 },
                { data: 3 },
                { data: 4 },
                { data: 5 },
                { data: 6 },
                { data: 7 },
                { data: 8 },
                { data: 9 },
                { data: 10 },
                { data: 11 },
                { data: 12, orderable: false, searchable: false }
            ],
            order: [[1, 'desc']],
            pageLength: 10,
            language: {
                search: "",
                searchPlaceholder: "Search by Name, Company, Email, Phone, Owner...",
                processing: '<div class="p-3 bg-white/95 shadow-lg border border-gray-100 rounded-2xl font-semibold text-emerald-600 flex items-center justify-center gap-2"><i class="fa fa-spinner fa-spin text-base"></i> Loading Leads...</div>'
            }
        });
    }

    // 2. MULTI-SELECT & BULK ACTIONS CONTROLLER FOR LEADS
    function getSelectedLeadIds() {
        var ids = [];
        $('.lead-row-checkbox:checked').each(function() {
            var val = parseInt($(this).val());
            if (val && ids.indexOf(val) === -1) {
                ids.push(val);
            }
        });
        return ids;
    }

    function syncLeadSelectionUI() {
        var ids = getSelectedLeadIds();
        var count = ids.length;
        var totalOnPage = $('.lead-row-checkbox').length;
        var isDeletedTab = (window.currentStatusFilter === 'deleted');

        $('.selected-count-pill').text(count);

        if (count > 0) {
            // Show badge indicator & floating bulk bar
            $('#badge-selection-indicator').removeClass('hidden');
            $('#leads-bulk-bar').removeClass('hidden');
            $('.selected-count-container').removeClass('hidden');

            if (isDeletedTab) {
                // In deleted tab: hide normal delete, show restore and permanent delete
                $('#btn-top-bulk-delete, #btn-banner-bulk-delete').addClass('hidden');
                $('#btn-top-bulk-restore, #btn-banner-bulk-restore').removeClass('hidden');
                $('#btn-top-bulk-permanent-delete, #btn-banner-bulk-permanent-delete').removeClass('hidden');
            } else {
                // In normal tabs: enable delete button and make it active red
                $('#btn-top-bulk-restore, #btn-banner-bulk-restore').addClass('hidden');
                $('#btn-top-bulk-permanent-delete, #btn-banner-bulk-permanent-delete').addClass('hidden');
                $('#btn-top-bulk-delete, #btn-banner-bulk-delete')
                    .removeClass('hidden bg-gray-100 text-gray-400 border-gray-200 cursor-not-allowed')
                    .addClass('bg-rose-600 hover:bg-rose-700 text-white cursor-pointer shadow-xs')
                    .prop('disabled', false);
            }
        } else {
            // Hide badge indicator & floating bulk bar
            $('#badge-selection-indicator').addClass('hidden');
            $('#leads-bulk-bar').addClass('hidden');
            $('.selected-count-container').addClass('hidden');

            // Reset top delete button to disabled state
            $('#btn-top-bulk-restore, #btn-banner-bulk-restore').addClass('hidden');
            $('#btn-top-bulk-permanent-delete, #btn-banner-bulk-permanent-delete').addClass('hidden');
            $('#btn-top-bulk-delete, #btn-banner-bulk-delete')
                .removeClass('hidden bg-rose-600 hover:bg-rose-700 text-white cursor-pointer shadow-xs')
                .addClass('bg-gray-100 text-gray-400 border-gray-200 cursor-not-allowed')
                .prop('disabled', true);
        }

        // Header checkbox state
        $('#check-all-leads').prop('checked', totalOnPage > 0 && count === totalOnPage);
    }

    function clearLeadSelection() {
        $('.lead-row-checkbox, #check-all-leads').prop('checked', false);
        syncLeadSelectionUI();
    }

    // Reset selection whenever DataTable redraws or page changes
    if (window.mainTable) {
        window.mainTable.on('draw', function() {
            syncLeadSelectionUI();
        });
    }

    // Checkbox events
    $(document).on('change', '#check-all-leads', function() {
        var checked = $(this).is(':checked');
        $('.lead-row-checkbox').prop('checked', checked);
        syncLeadSelectionUI();
    });

    $(document).on('change', '.lead-row-checkbox', function() {
        syncLeadSelectionUI();
    });

    // Quick selection helpers
    $(document).on('click', '#btn-select-page', function() {
        $('.lead-row-checkbox').prop('checked', true);
        syncLeadSelectionUI();
    });

    $(document).on('click', '#btn-select-25', function() {
        $('.lead-row-checkbox').prop('checked', false);
        $('.lead-row-checkbox').slice(0, 25).prop('checked', true);
        syncLeadSelectionUI();
    });

    $(document).on('click', '#btn-select-50', function() {
        $('.lead-row-checkbox').prop('checked', false);
        $('.lead-row-checkbox').slice(0, 50).prop('checked', true);
        syncLeadSelectionUI();
    });

    $(document).on('click', '#btn-select-none, #btn-banner-bulk-clear', function() {
        clearLeadSelection();
    });

    // Tab switch handler to refresh UI for Deleted tab vs Active tab
    $(document).on('click', '#status-tabs a[data-status]', function() {
        setTimeout(function() {
            clearLeadSelection();
        }, 150);
    });

    // Execute Bulk Action helper
    function executeBulkAction(action, confirmMsg) {
        var ids = getSelectedLeadIds();
        if (!ids.length) {
            CRM.toast('warning', 'Please select at least one lead.');
            return;
        }

        if (confirmMsg && !confirm(confirmMsg)) {
            return;
        }

        var $btn = $(this);
        var origHtml = $btn.html();
        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Processing...');

        var postData = {
            ids: ids,
            action: action
        };
        if (typeof CI3_CSRF_NAME !== 'undefined') {
            postData[CI3_CSRF_NAME] = CI3_CSRF_HASH;
        }

        $.ajax({
            url: BASE_URL + 'leads/bulk_status',
            type: 'POST',
            dataType: 'json',
            data: postData,
            success: function(resp) {
                $btn.prop('disabled', false).html(origHtml);
                if (resp.status === 'success') {
                    CRM.toast('success', resp.message || 'Bulk operation completed.');
                    clearLeadSelection();
                    if (window.mainTable && window.mainTable.ajax) {
                        window.mainTable.ajax.reload(null, false);
                    }
                } else {
                    CRM.toast('error', resp.message || 'Failed to complete bulk action.');
                }
            },
            error: function(xhr) {
                $btn.prop('disabled', false).html(origHtml);
                var err = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Server error occurred during bulk operation.';
                CRM.toast('error', err);
            }
        });
    }

    // Trigger Bulk Delete
    $(document).on('click', '#btn-top-bulk-delete, #btn-banner-bulk-delete', function() {
        var ids = getSelectedLeadIds();
        if (!ids.length) return;
        executeBulkAction.call(this, 'delete', 'Are you sure you want to delete ' + ids.length + ' selected lead(s)?');
    });

    // Trigger Bulk Restore
    $(document).on('click', '#btn-top-bulk-restore, #btn-banner-bulk-restore', function() {
        var ids = getSelectedLeadIds();
        if (!ids.length) return;
        executeBulkAction.call(this, 'restore', 'Restore ' + ids.length + ' selected lead(s) to Active?');
    });

    // Trigger Bulk Permanent Delete
    $(document).on('click', '#btn-top-bulk-permanent-delete, #btn-banner-bulk-permanent-delete', function() {
        var ids = getSelectedLeadIds();
        if (!ids.length) return;
        executeBulkAction.call(this, 'permanent_delete', 'WARNING: Are you sure you want to PERMANENTLY DELETE ' + ids.length + ' lead(s)? This will delete all associated activities and CANNOT be undone!');
    });

    // 3. Export Dropdown Menu Handler
    $(document).on('click', '#btn-export-dropdown', function(e) {
        e.preventDefault();
        e.stopPropagation();
        $('#export-dropdown-menu').toggleClass('hidden');
    });

    // Close export dropdown on outside click
    $(document).on('click', function(e) {
        if (!$(e.target).closest('#export-dropdown-wrapper').length) {
            $('#export-dropdown-menu').addClass('hidden');
        }
    });

    // Close dropdown on clicking any item (append product_id filter if active)
    $(document).on('click', '#export-dropdown-menu a', function(e) {
        if (window.currentProductFilter) {
            e.preventDefault();
            var href = $(this).attr('href');
            var url = new URL(href, window.location.origin);
            url.searchParams.set('product_id', window.currentProductFilter);
            window.location.href = url.toString();
        }
        $('#export-dropdown-menu').addClass('hidden');
    });

    // Close on Escape key
    $(document).on('keydown', function(e) {
        if (e.key === 'Escape') {
            $('#export-dropdown-menu').addClass('hidden');
            closeImportDrawer();
            closeLeadDrawer();
            closeViewDrawer();
        }
    });

    // 4. Tab Navigation in Add/Edit Lead Drawer
    $(document).on('click', '.lead-tab-btn', function() {
        var target = $(this).data('target');
        $('.lead-tab-btn')
            .removeClass('active border-emerald-600 text-emerald-700 bg-white')
            .addClass('border-transparent text-gray-500');
        $(this)
            .addClass('active border-emerald-600 text-emerald-700 bg-white')
            .removeClass('border-transparent text-gray-500');

        $('.lead-tab-pane').addClass('hidden');
        $(target).removeClass('hidden');
    });

    // 5. RIGHT-SIDE DRAWER FOR ADD / EDIT LEAD
    function openLeadDrawer(data) {
        var $f = $('#lead-form');
        $f[0].reset();
        CRM.clear_errors($f);
        $('#lead-id').val(data ? data.id : 0);

        // Switch back to first tab
        $('.lead-tab-btn:first').trigger('click');

        if (data) {
            $('#lead-drawer-title').text('Edit Lead — #' + data.id);
            $('#lead-drawer-icon').removeClass('fa-user-plus').addClass('fa-pencil-square-o');
            $('#btn-save-lead').html('<i class="fa fa-save"></i> Update Lead');

            // Contact tab
            $f.find('[name="first_name"]').val(data.first_name || '');
            $f.find('[name="last_name"]').val(data.last_name || '');
            $f.find('[name="title"]').val(data.title || '');
            $f.find('[name="email"]').val(data.email || '');
            $f.find('[name="email_status"]').val(data.email_status || '');
            $f.find('[name="secondary_email"]').val(data.secondary_email || '');
            $f.find('[name="corporate_phone"]').val(data.corporate_phone || '');
            $f.find('[name="website"]').val(data.website || '');
            $f.find('[name="person_linkedin_url"]').val(data.person_linkedin_url || '');
            $f.find('[name="facebook_url"]').val(data.facebook_url || '');
            $f.find('[name="twitter_url"]').val(data.twitter_url || '');
            $f.find('[name="address"]').val(data.address || '');
            $f.find('[name="city"]').val(data.city || '');
            $f.find('[name="state"]').val(data.state || '');
            $f.find('[name="country"]').val(data.country || '');

            // Company tab
            $f.find('[name="company_name"]').val(data.company_name || '');
            $f.find('[name="customer_id"]').val(data.customer_id || '').trigger('change');
            $f.find('[name="employees_count"]').val(data.employees_count || '');
            $f.find('[name="industry"]').val(data.industry || '');
            $f.find('[name="annual_revenue"]').val(data.annual_revenue || '');
            $f.find('[name="keywords"]').val(data.keywords || '');
            $f.find('[name="technologies"]').val(data.technologies || '');
            $f.find('[name="company_phone"]').val(data.company_phone || '');
            $f.find('[name="company_linkedin_url"]').val(data.company_linkedin_url || '');
            $f.find('[name="company_address"]').val(data.company_address || '');
            $f.find('[name="company_city"]').val(data.company_city || '');
            $f.find('[name="company_state"]').val(data.company_state || '');
            $f.find('[name="company_country"]').val(data.company_country || '');

            // Sales tab
            $f.find('[name="account_owner"]').val(data.account_owner || '');
            if ($f.find('[name="assigned_to"]').length) {
                $f.find('[name="assigned_to"]').val(data.assigned_to || '').trigger('change');
            }
            $f.find('[name="lead_status"]').val(data.lead_status || 'new');
            $f.find('[name="source"]').val(data.source || 'online');
            $f.find('[name="email_sent"]').val(data.email_sent || '');
            $f.find('[name="email_open"]').val(data.email_open || '');
            $f.find('[name="email_bounced"]').val(data.email_bounced || '');
            $f.find('[name="product_demo"], [name="demo"]').val(data.product_demo || data.demo || '');
            $f.find('[name="quotation"]').val(data.quotation || '');
            $f.find('[name="product_id"]').val(data.product_id || '').trigger('change');
            $f.find('[name="expected_value"]').val(data.expected_value ? (data.expected_value / 100).toFixed(2) : '');
            $f.find('[name="expected_close_date"]').val(data.expected_close_date || '');
            $f.find('[name="description"]').val(data.description || '');

            // Update catalogue reference price text
            var $selectedProduct = $f.find('#lead-product-id option:selected');
            var refPrice = $selectedProduct.data('price');
            if (refPrice && parseFloat(refPrice) > 0) {
                $('#product-ref-price-text').text('₹' + parseFloat(refPrice).toLocaleString('en-IN', {minimumFractionDigits: 2}));
            } else {
                $('#product-ref-price-text').text('₹0.00');
            }
        } else {
            $('#lead-drawer-title').text('Add New Lead');
            $('#lead-drawer-icon').removeClass('fa-pencil-square-o').addClass('fa-user-plus');
            $('#btn-save-lead').html('<i class="fa fa-save"></i> Save Lead');
            $f.find('[name="lead_status"]').val('new');
            $f.find('[name="source"]').val('online');
            $f.find('[name="customer_id"]').val('').trigger('change');
            if (window.currentProductFilter && window.currentProductFilter !== 'all' && window.currentProductFilter !== 'unassigned') {
                $f.find('[name="product_id"]').val(window.currentProductFilter).trigger('change');
            } else {
                $f.find('[name="product_id"]').val('').trigger('change');
            }
            $('#product-ref-price-text').text('₹0.00');
            if ($f.find('[name="assigned_to"]').length) {
                $f.find('[name="assigned_to"]').val('').trigger('change');
            }
        }

        CRM.init_plugins($('#lead-drawer'));

        // Slide-in drawer
        $('#lead-drawer-backdrop')
            .removeClass('pointer-events-none opacity-0')
            .addClass('opacity-100');
        $('#lead-drawer')
            .removeClass('translate-x-full')
            .addClass('translate-x-0');
        $('body').addClass('overflow-hidden');
    }

    function closeLeadDrawer() {
        $('#lead-drawer')
            .removeClass('translate-x-0')
            .addClass('translate-x-full');
        $('#lead-drawer-backdrop')
            .removeClass('opacity-100')
            .addClass('opacity-0 pointer-events-none');
        if (!$('#view-drawer').hasClass('translate-x-0') && !$('#import-drawer').hasClass('translate-x-0')) {
            $('body').removeClass('overflow-hidden');
        }
    }

    $('#btn-close-lead-drawer, #btn-cancel-lead-drawer, #lead-drawer-backdrop').click(function() {
        closeLeadDrawer();
    });

    // Trigger Add Lead
    $('#btn-add-lead').click(function() {
        openLeadDrawer(null);
    });

    // Trigger Edit Lead from DataTable button
    $(document).on('click', '.btn-edit-lead', function() {
        var id = $(this).data('id');
        $.getJSON(BASE_URL + 'leads/get/' + id, function(res) {
            if (res.status === 'success') {
                openLeadDrawer(res.data);
            } else {
                CRM.toast('error', res.message || 'Failed to load lead details.');
            }
        }).fail(function() {
            CRM.toast('error', 'Network error loading lead.');
        });
    });

    // Save Lead Form Submit
    $('#btn-save-lead').click(function() {
        var $btn = $(this);
        CRM.btn_loading($btn);
        CRM.clear_errors($('#lead-form'));

        $.ajax({
            url: BASE_URL + 'leads/save',
            method: 'POST',
            data: new FormData($('#lead-form')[0]),
            processData: false,
            contentType: false,
            success: function(res) {
                if (res.status === 'success') {
                    CRM.toast('success', res.message);
                    closeLeadDrawer();
                    if (window.mainTable) window.mainTable.ajax.reload(null, false);
                } else {
                    CRM.show_errors($('#lead-form'), res.errors || {});
                    CRM.toast('error', res.message || 'Validation failed. Please check form.');
                }
            },
            error: function(xhr) {
                var json = xhr.responseJSON;
                if (json && json.errors) {
                    CRM.show_errors($('#lead-form'), json.errors);
                }
                CRM.toast('error', (json && json.message) || 'Error saving lead.');
            },
            complete: function() {
                CRM.btn_reset($btn);
            }
        });
    });

    // 6. RIGHT-SIDE DRAWER FOR PREVIEW / QUICK VIEW
    function openViewDrawer() {
        $('#view-drawer-backdrop')
            .removeClass('pointer-events-none opacity-0')
            .addClass('opacity-100');
        $('#view-drawer')
            .removeClass('translate-x-full')
            .addClass('translate-x-0');
        $('body').addClass('overflow-hidden');
    }

    function closeViewDrawer() {
        $('#view-drawer')
            .removeClass('translate-x-0')
            .addClass('translate-x-full');
        $('#view-drawer-backdrop')
            .removeClass('opacity-100')
            .addClass('opacity-0 pointer-events-none');
        if (!$('#lead-drawer').hasClass('translate-x-0') && !$('#import-drawer').hasClass('translate-x-0')) {
            $('body').removeClass('overflow-hidden');
        }
    }

    $('#btn-close-view-drawer, #btn-close-view-drawer-footer, #view-drawer-backdrop').click(function() {
        closeViewDrawer();
    });

    $(document).on('click', '.btn-view-lead', function() {
        var id = $(this).data('id');
        $.getJSON(BASE_URL + 'leads/get/' + id, function(res) {
            if (res.status !== 'success') {
                CRM.toast('error', res.message || 'Lead not found.');
                return;
            }
            var d = res.data;
            var fullName = $.trim((d.first_name || '') + ' ' + (d.last_name || ''));
            var dispName = fullName || d.title || ('Lead #' + d.id);
            var initial  = dispName.charAt(0).toUpperCase();

            $('#view-avatar').text(initial);
            $('#view-name').text(dispName);
            $('#view-title').text(d.title || 'Lead');
            $('#view-company').text(d.company_name || d.customer_name || 'No Company');

            // Badges
            var stageColors = {
                'new': 'bg-slate-100 text-slate-700',
                'contacted': 'bg-cyan-100 text-cyan-700',
                'qualified': 'bg-blue-100 text-blue-700',
                'proposal': 'bg-amber-100 text-amber-700',
                'negotiation': 'bg-orange-100 text-orange-700',
                'won': 'bg-green-100 text-green-700',
                'lost': 'bg-red-100 text-red-700'
            };
            var stageClass = stageColors[d.lead_status] || 'bg-gray-100 text-gray-700';
            $('#view-stage-badge').html('<span class="px-2 py-0.5 text-xs font-bold rounded-lg ' + stageClass + ' uppercase tracking-wide">' + (d.lead_status || 'new') + '</span>');

            var statusClass = d.status === 'active' ? 'bg-emerald-500/20 text-emerald-300' : 'bg-rose-500/20 text-rose-300';
            $('#view-status-badge').html('<span class="px-2 py-0.5 text-xs font-semibold rounded-lg ' + statusClass + '">' + (d.status || 'active') + '</span>');

            // Contact
            if (d.email) {
                $('#view-email').html('<a href="mailto:' + $('<div>').text(d.email).html() + '" class="text-blue-600 hover:underline">' + $('<div>').text(d.email).html() + '</a>');
            } else {
                $('#view-email').text('-');
            }

            var emailStatusBadge = '-';
            if (d.email_status) {
                var es = d.email_status.toLowerCase();
                var esClass = 'bg-gray-100 text-gray-700';
                if (es === 'valid' || es === 'verified') esClass = 'bg-emerald-50 text-emerald-700 border border-emerald-200';
                else if (es === 'bounced' || es === 'invalid') esClass = 'bg-rose-50 text-rose-700 border border-rose-200';
                else if (es === 'unverified' || es === 'catch-all') esClass = 'bg-amber-50 text-amber-700 border border-amber-200';
                emailStatusBadge = '<span class="px-2 py-0.5 text-xs font-bold rounded-md ' + esClass + '">' + $('<div>').text(d.email_status).html() + '</span>';
            }
            $('#view-email-status').html(emailStatusBadge);

            $('#view-secondary-email').text(d.secondary_email || '-');
            if (d.corporate_phone) {
                $('#view-corporate-phone').html('<a href="tel:' + $('<div>').text(d.corporate_phone).html() + '" class="text-emerald-600 hover:underline flex items-center gap-1"><i class="fa fa-phone text-xs"></i> ' + $('<div>').text(d.corporate_phone).html() + '</a>');
            } else {
                $('#view-corporate-phone').text('-');
            }

            var addrParts = [];
            if (d.address) addrParts.push(d.address);
            if (d.city) addrParts.push(d.city);
            if (d.state) addrParts.push(d.state);
            if (d.country) addrParts.push(d.country);
            $('#view-address').text(addrParts.length ? addrParts.join(', ') : '-');

            // Social & Web Links
            var socialHtml = [];
            if (d.website) {
                socialHtml.push('<a href="' + $('<div>').text(d.website).html() + '" target="_blank" class="inline-flex items-center gap-1.5 px-3 py-1 bg-gray-50 hover:bg-gray-100 text-gray-700 rounded-lg border border-gray-200 font-semibold"><i class="fa fa-globe text-emerald-600"></i> Website</a>');
            }
            if (d.person_linkedin_url) {
                socialHtml.push('<a href="' + $('<div>').text(d.person_linkedin_url).html() + '" target="_blank" class="inline-flex items-center gap-1.5 px-3 py-1 bg-blue-50 hover:bg-blue-100 text-blue-700 rounded-lg border border-blue-200 font-semibold"><i class="fa fa-linkedin text-blue-600"></i> Person LinkedIn</a>');
            }
            if (d.facebook_url) {
                socialHtml.push('<a href="' + $('<div>').text(d.facebook_url).html() + '" target="_blank" class="inline-flex items-center gap-1.5 px-3 py-1 bg-blue-50 hover:bg-blue-100 text-blue-800 rounded-lg border border-blue-200 font-semibold"><i class="fa fa-facebook text-blue-800"></i> Facebook</a>');
            }
            if (d.twitter_url) {
                socialHtml.push('<a href="' + $('<div>').text(d.twitter_url).html() + '" target="_blank" class="inline-flex items-center gap-1.5 px-3 py-1 bg-cyan-50 hover:bg-cyan-100 text-cyan-800 rounded-lg border border-cyan-200 font-semibold"><i class="fa fa-twitter text-cyan-600"></i> Twitter</a>');
            }
            $('#view-social-links').html(socialHtml.length ? socialHtml.join('') : '<span class="text-gray-400 italic">No social profiles provided</span>');

            // Company
            $('#view-company-name').text(d.company_name || d.customer_name || '-');
            $('#view-employees').text(d.employees_count || '-');
            $('#view-industry').text(d.industry || '-');
            $('#view-revenue').text(d.annual_revenue || '-');
            $('#view-company-phone').text(d.company_phone || '-');
            if (d.company_linkedin_url) {
                $('#view-company-linkedin').html('<a href="' + $('<div>').text(d.company_linkedin_url).html() + '" target="_blank" class="text-blue-600 hover:underline flex items-center gap-1"><i class="fa fa-linkedin"></i> Company Page</a>');
            } else {
                $('#view-company-linkedin').text('-');
            }

            var compAddr = [];
            if (d.company_address) compAddr.push(d.company_address);
            if (d.company_city) compAddr.push(d.company_city);
            if (d.company_state) compAddr.push(d.company_state);
            if (d.company_country) compAddr.push(d.company_country);
            $('#view-company-address').text(compAddr.length ? compAddr.join(', ') : '-');

            // Tech Stack & Keywords tags
            if (d.technologies) {
                var techTags = d.technologies.split(',').map(function(t) {
                    return '<span class="px-2 py-0.5 bg-blue-50 text-blue-700 rounded-md border border-blue-100 font-semibold">' + $('<div>').text($.trim(t)).html() + '</span>';
                });
                $('#view-technologies').html(techTags.join(' '));
            } else {
                $('#view-technologies').text('-');
            }

            if (d.keywords) {
                var kwTags = d.keywords.split(',').map(function(k) {
                    return '<span class="px-2 py-0.5 bg-purple-50 text-purple-700 rounded-md border border-purple-100 font-semibold">' + $('<div>').text($.trim(k)).html() + '</span>';
                });
                $('#view-keywords').html(kwTags.join(' '));
            } else {
                $('#view-keywords').text('-');
            }

            // Sales & Pipeline
            $('#view-account-owner').text(d.account_owner || d.assigned_name || '-');
            $('#view-assigned-name').text(d.assigned_name || '-');
            $('#view-source').text(d.source || '-');

            $('#view-email-sent').html(d.email_sent ? '<span class="px-2 py-0.5 rounded font-bold ' + (d.email_sent.toLowerCase() === 'yes' ? 'bg-blue-50 text-blue-700 border border-blue-200' : 'bg-gray-100 text-gray-700') + '">' + $('<div>').text(d.email_sent).html() + '</span>' : '-');
            $('#view-email-open').html(d.email_open ? '<span class="px-2 py-0.5 rounded font-bold ' + (d.email_open.toLowerCase() === 'yes' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-gray-100 text-gray-700') + '">' + $('<div>').text(d.email_open).html() + '</span>' : '-');
            $('#view-email-bounced').html(d.email_bounced ? '<span class="px-2 py-0.5 rounded font-bold ' + (d.email_bounced.toLowerCase() === 'yes' ? 'bg-rose-50 text-rose-700 border border-rose-200' : 'bg-emerald-50 text-emerald-700 border border-emerald-200') + '">' + $('<div>').text(d.email_bounced).html() + '</span>' : '-');

            $('#view-demo').text(d.product_demo || d.demo || '-');
            $('#view-quotation').text(d.quotation || '-');
            $('#view-expected-value').text(d.expected_value ? ('₹' + (d.expected_value / 100).toLocaleString('en-IN', {minimumFractionDigits: 2})) : '-');
            if (d.product_name) {
                $('#view-product-name').text(d.product_name + (d.product_sku ? ' (' + d.product_sku + ')' : ''));
                $('#view-product-badge').removeClass('hidden').addClass('inline-flex');
            } else {
                $('#view-product-name').text('None linked');
                $('#view-product-badge').removeClass('hidden').addClass('inline-flex text-gray-500 bg-gray-50 border-gray-200');
            }
            $('#view-description').text(d.description || 'No notes available.');

            $('#view-timeline-link').attr('href', BASE_URL + 'leads/detail/' + d.id);
            $('#btn-view-to-edit').data('id', d.id);

            openViewDrawer();
        });
    });

    // Lead drawer product select change (Updates reference price without overwriting manual expected value)
    $(document).on('change', '#lead-product-id', function() {
        var $opt = $(this).find('option:selected');
        var price = $opt.data('price');
        if (price && parseFloat(price) > 0) {
            $('#product-ref-price-text').text('₹' + parseFloat(price).toLocaleString('en-IN', {minimumFractionDigits: 2}));
        } else {
            $('#product-ref-price-text').text('₹0.00');
        }
        // Notice: Amount is manually fixed! Do not alter expected_value input.
    });

    // Copy Email Click
    $('#btn-copy-email').click(function() {
        var email = $('#view-email a').text() || $('#view-email').text();
        if (email && email !== '-') {
            navigator.clipboard.writeText(email).then(function() {
                CRM.toast('success', 'Email copied to clipboard!');
            });
        }
    });

    // Switch from View Drawer to Edit Drawer
    $('#btn-view-to-edit').click(function() {
        var id = $(this).data('id');
        closeViewDrawer();
        setTimeout(function() {
            $('.btn-edit-lead[data-id="' + id + '"]').first().trigger('click');
        }, 200);
    });

    // 7. RIGHT SIDE DRAWER FOR IMPORT & DUPLICATE VALIDATION
    var activeImportToken = null;
    var activeNewCount = 0;
    var activeDupCount = 0;

    function openImportDrawer() {
        $('#import-drawer-backdrop')
            .removeClass('pointer-events-none opacity-0')
            .addClass('opacity-100');
        $('#import-drawer')
            .removeClass('translate-x-full')
            .addClass('translate-x-0');
        $('body').addClass('overflow-hidden');

        // Reset import state
        resetImportState();
    }

    function resetImportState() {
        activeImportToken = null;
        activeNewCount = 0;
        activeDupCount = 0;
        $('#drawer-import-form')[0].reset();
        $('#drawer-file-selected').addClass('hidden').removeClass('flex');
        $('#drawer-file-label').text('Select .xlsx, .xls or .csv file');
        $('#drawer-import-progress').addClass('hidden');
        $('#drawer-validation-preview').addClass('hidden');
        $('#drawer-duplicate-backdrop, #drawer-duplicate-popup').addClass('hidden');
        $('#drawer-import-results').addClass('hidden').html('');
        $('#drawer-dup-table-body').empty();
        $('#btn-drawer-start-import').prop('disabled', false).html('<i class="fa fa-shield"></i> Validate & Check Previous Data');
        $('#preview-matched-products-count').text('0 Matched');
        $('input[name="global_dup_action"][value="skip"]').prop('checked', true);
        updateDupChoiceStyles('skip');
        if (window.currentProductFilter && window.currentProductFilter !== 'all' && window.currentProductFilter !== 'unassigned') {
            $('#drawer-target-product').val(window.currentProductFilter);
        } else {
            $('#drawer-target-product').val('auto');
        }
    }

    function closeImportDrawer() {
        $('#import-drawer')
            .removeClass('translate-x-0')
            .addClass('translate-x-full');
        $('#import-drawer-backdrop')
            .removeClass('opacity-100')
            .addClass('opacity-0 pointer-events-none');
        $('#drawer-duplicate-backdrop, #drawer-duplicate-popup').addClass('hidden');
        if (!$('#lead-drawer').hasClass('translate-x-0') && !$('#view-drawer').hasClass('translate-x-0')) {
            $('body').removeClass('overflow-hidden');
        }
    }

    $('#btn-open-import').click(function(e) {
        e.preventDefault();
        openImportDrawer();
    });

    $('#btn-close-drawer, #import-drawer-backdrop').click(function() {
        closeImportDrawer();
    });

    // Auto-open drawer if requested in URL (e.g. ?open_import=1)
    if (window.location.search.indexOf('open_import=1') !== -1) {
        openImportDrawer();
    }

    // Format Notes Modal
    $(document).on('click', '.btn-open-format-notes', function(e) {
        e.preventDefault();
        $('#format-notes-modal').modal('show');
    });

    // Drawer File Input & Drag-Drop Handling
    var $dropzone = $('#drawer-dropzone');
    var $fileInput = $('#drawer-file-input');

    $dropzone.on('dragover dragenter', function(e) {
        e.preventDefault();
        e.stopPropagation();
        $dropzone.addClass('border-emerald-500 bg-emerald-50/40');
    });

    $dropzone.on('dragleave dragend drop', function(e) {
        e.preventDefault();
        e.stopPropagation();
        $dropzone.removeClass('border-emerald-500 bg-emerald-50/40');
    });

    $dropzone.on('drop', function(e) {
        var files = e.originalEvent.dataTransfer.files;
        if (files && files.length) {
            $fileInput[0].files = files;
            updateSelectedFileDisplay(files[0]);
        }
    });

    $fileInput.on('change', function() {
        if (this.files && this.files.length) {
            updateSelectedFileDisplay(this.files[0]);
        }
    });

    function updateSelectedFileDisplay(file) {
        $('#drawer-selected-filename').text(file.name);
        var sizeKb = (file.size / 1024).toFixed(1) + ' KB';
        if (file.size > 1048576) {
            sizeKb = (file.size / 1048576).toFixed(2) + ' MB';
        }
        $('#drawer-selected-filesize').text('(' + sizeKb + ')');
        $('#drawer-file-selected').removeClass('hidden').addClass('flex');
        $('#drawer-file-label').text(file.name);
        $('#drawer-validation-preview').addClass('hidden');
        $('#drawer-duplicate-popup').addClass('hidden');
        $('#drawer-import-results').addClass('hidden').html('');
    }

    $('#btn-remove-file').click(function(e) {
        e.stopPropagation();
        $fileInput.val('');
        $('#drawer-file-selected').addClass('hidden').removeClass('flex');
        $('#drawer-file-label').text('Select .xlsx, .xls or .csv file');
        $('#drawer-validation-preview').addClass('hidden');
        $('#drawer-duplicate-popup').addClass('hidden');
    });

    // STEP 1: Drawer Import Form Submit -> Validate against previous database records
    $('#drawer-import-form').on('submit', function(e) {
        e.preventDefault();

        if (!$fileInput[0].files.length) {
            CRM.toast('error', 'Please choose an Excel or CSV file to validate.');
            return;
        }

        var $btn = $('#btn-drawer-start-import');
        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin mr-1.5"></i> Validating Against Previous Records...');

        var $prog = $('#drawer-import-progress');
        var $bar  = $('#drawer-progress-bar');
        var $pct  = $('#drawer-progress-pct');
        var $prev = $('#drawer-validation-preview');
        var $res  = $('#drawer-import-results');

        $prog.removeClass('hidden');
        $bar.css('width', '35%');
        $pct.text('35%');
        $prev.addClass('hidden');
        $res.addClass('hidden').html('');

        var formData = new FormData(this);

        $.ajax({
            url: BASE_URL + 'leads/import_validate',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            xhr: function() {
                var xhr = new window.XMLHttpRequest();
                xhr.upload.addEventListener('progress', function(evt) {
                    if (evt.lengthComputable) {
                        var percentComplete = Math.round((evt.loaded / evt.total) * 70);
                        $bar.css('width', percentComplete + '%');
                        $pct.text(percentComplete + '%');
                    }
                }, false);
                return xhr;
            },
            success: function(resp) {
                $bar.css('width', '100%');
                $pct.text('100%');

                if (resp.status === 'success') {
                    var d = resp.data || {};
                    activeImportToken = d.token;
                    activeNewCount    = d.new_count || 0;
                    activeDupCount    = d.duplicate_count || 0;

                    // Populate summary numbers
                    $('#preview-filename').text(d.filename || 'Uploaded File');
                    $('#preview-total-badge').text((d.total_rows || 0) + ' Rows');
                    $('#preview-new-count').text(d.new_count || 0);
                    $('#preview-dup-count').text(d.duplicate_count || 0);
                    $('#preview-invalid-count').text(d.invalid_count || 0);
                    $('#preview-matched-products-count').text((d.matched_products_count || 0) + ' Matched');

                    // Check if duplicates exist
                    if (d.duplicate_count > 0) {
                        $('#dup-notice-count').text(d.duplicate_count);
                        $('#popup-dup-num').text(d.duplicate_count);
                        $('#drawer-duplicates-panel').removeClass('hidden');
                        $('#drawer-clean-panel').addClass('hidden');

                        // Render duplicate rows into table
                        var tbodyHtml = '';
                        (d.duplicates || []).forEach(function(dup) {
                            tbodyHtml += '<tr class="hover:bg-amber-50/50 transition-colors">';
                            tbodyHtml += '<td class="py-2.5 px-2.5 font-bold text-gray-500 font-mono">#' + dup.row + '</td>';
                            tbodyHtml += '<td class="py-2.5 px-2.5">';
                            tbodyHtml += '<div class="font-bold text-gray-900 leading-tight">' + $('<div>').text(dup.name).html() + '</div>';
                            if (dup.company) tbodyHtml += '<div class="text-[11px] text-gray-500">' + $('<div>').text(dup.company).html() + '</div>';
                            tbodyHtml += '<div class="text-[11px] text-gray-500 font-mono">' + $('<div>').text(dup.email || dup.phone || '').html() + '</div>';
                            tbodyHtml += '</td>';
                            tbodyHtml += '<td class="py-2.5 px-2.5">';
                            if (dup.matched_product_name) {
                                tbodyHtml += '<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-bold bg-purple-100 text-purple-800 border border-purple-200"><i class="fa fa-cube text-purple-600"></i> ' + $('<div>').text(dup.matched_product_name).html() + '</span>';
                            } else {
                                tbodyHtml += '<span class="text-gray-400 text-xs italic">None</span>';
                            }
                            tbodyHtml += '</td>';
                            tbodyHtml += '<td class="py-2.5 px-2.5">';
                            tbodyHtml += '<div class="font-semibold text-amber-900 leading-tight">Lead #' + dup.matched_id + ': ' + $('<div>').text(dup.matched_name).html() + '</div>';
                            if (dup.matched_company) tbodyHtml += '<div class="text-[11px] text-gray-500">' + $('<div>').text(dup.matched_company).html() + '</div>';
                            tbodyHtml += '</td>';
                            tbodyHtml += '<td class="py-2.5 px-2.5">';
                            tbodyHtml += '<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-300">';
                            tbodyHtml += (dup.match_field === 'Email' || dup.match_field === 'Secondary Email' ? '<i class="fa fa-envelope"></i> ' : '<i class="fa fa-phone"></i> ') + $('<div>').text(dup.match_field + ': ' + dup.match_value).html();
                            tbodyHtml += '</span>';
                            tbodyHtml += '</td>';
                            tbodyHtml += '<td class="py-2.5 px-2.5 text-right">';
                            tbodyHtml += '<select class="row-dup-action text-[11px] font-bold px-2 py-1 rounded-lg border border-gray-300 bg-white text-gray-700 focus:ring-1 focus:ring-emerald-500" data-row="' + dup.row + '">';
                            tbodyHtml += '<option value="skip" selected>Skip (Keep DB)</option>';
                            tbodyHtml += '<option value="overwrite">Overwrite (Update)</option>';
                            tbodyHtml += '<option value="delete">Delete & Replace</option>';
                            tbodyHtml += '</select>';
                            tbodyHtml += '</td>';
                            tbodyHtml += '</tr>';
                        });
                        $('#drawer-dup-table-body').html(tbodyHtml);

                        // Reset radio selection to 'skip'
                        $('input[name="global_dup_action"][value="skip"]').prop('checked', true);
                        updateDupChoiceStyles('skip');
                        updateConfirmButtonLabel('skip');

                        // Show In-Drawer Popup Alert with Backdrop
                        $('#drawer-duplicate-backdrop, #drawer-duplicate-popup').removeClass('hidden');
                    } else {
                        $('#drawer-duplicates-panel').addClass('hidden');
                        $('#drawer-clean-panel').removeClass('hidden');
                        $('#drawer-duplicate-backdrop, #drawer-duplicate-popup').addClass('hidden');
                        $('#btn-confirm-label').text('Confirm & Save All ' + (d.new_count || 0) + ' Leads');
                    }

                    // Show preview section right below button
                    $prev.removeClass('hidden');

                    // Scroll down to preview section smoothly
                    var drawerContent = $('#import-drawer .overflow-y-auto');
                    if (drawerContent.length) {
                        drawerContent.animate({ scrollTop: $prev.position().top + drawerContent.scrollTop() - 20 }, 300);
                    }

                    CRM.toast('success', 'Spreadsheet validated! Check summary and duplicate options below.');
                } else {
                    CRM.toast('error', resp.message || 'Validation failed.');
                    $res.html('<div class="p-3.5 bg-rose-50 border border-rose-200 rounded-xl text-xs text-rose-800 font-medium"><i class="fa fa-exclamation-circle text-rose-600 mr-1.5"></i> ' + $('<div>').text(resp.message).html() + '</div>').removeClass('hidden');
                }
            },
            error: function(xhr) {
                var json = xhr.responseJSON;
                var msg = (json && json.message) || 'Validation failed due to network or server error.';
                CRM.toast('error', msg);
                $res.html('<div class="p-3.5 bg-rose-50 border border-rose-200 rounded-xl text-xs text-rose-800 font-medium"><i class="fa fa-exclamation-circle text-rose-600 mr-1.5"></i> ' + $('<div>').text(msg).html() + '</div>').removeClass('hidden');
            },
            complete: function() {
                $btn.prop('disabled', false).html('<i class="fa fa-shield"></i> Re-Validate Spreadsheet');
            }
        });
    });

    // Helper: Style duplicate choice cards
    function updateDupChoiceStyles(action) {
        $('.dup-choice-label').removeClass('border-2 border-emerald-500 border-blue-500 border-rose-500 shadow-xs').addClass('border border-gray-200');
        var $active = $('input[name="global_dup_action"][value="' + action + '"]').closest('.dup-choice-label');
        if (action === 'skip') {
            $active.removeClass('border-gray-200').addClass('border-2 border-emerald-500 shadow-xs');
        } else if (action === 'overwrite') {
            $active.removeClass('border-gray-200').addClass('border-2 border-blue-500 shadow-xs');
        } else if (action === 'delete') {
            $active.removeClass('border-gray-200').addClass('border-2 border-rose-500 shadow-xs');
        }
    }

    function updateConfirmButtonLabel(action) {
        if (action === 'skip') {
            $('#btn-confirm-label').text('Confirm & Save (' + activeNewCount + ' New Leads — Skip ' + activeDupCount + ' Duplicates)');
        } else if (action === 'overwrite') {
            $('#btn-confirm-label').text('Confirm & Save (Update ' + activeDupCount + ' Existing & Add ' + activeNewCount + ' New)');
        } else if (action === 'delete') {
            $('#btn-confirm-label').text('Confirm & Save (Replace ' + activeDupCount + ' Old & Add ' + activeNewCount + ' New)');
        }
    }

    // Global radio option change
    $(document).on('change', 'input[name="global_dup_action"]', function() {
        var action = $(this).val();
        updateDupChoiceStyles(action);
        updateConfirmButtonLabel(action);

        // Sync table row action selects
        $('.row-dup-action').val(action);
    });

    // In-Drawer Popup Buttons
    $('#btn-close-dup-popup, #btn-popup-review, #drawer-duplicate-backdrop').click(function() {
        $('#drawer-duplicate-backdrop, #drawer-duplicate-popup').addClass('hidden');
    });

    $('#btn-show-dup-popup').click(function() {
        $('#drawer-duplicate-backdrop, #drawer-duplicate-popup').removeClass('hidden');
    });

    $('#btn-popup-skip-save').click(function() {
        $('input[name="global_dup_action"][value="skip"]').prop('checked', true);
        updateDupChoiceStyles('skip');
        updateConfirmButtonLabel('skip');
        $('.row-dup-action').val('skip');
        $('#drawer-duplicate-backdrop, #drawer-duplicate-popup').addClass('hidden');
        $('#btn-confirm-save-import').trigger('click');
    });

    $('#btn-popup-overwrite-save').click(function() {
        $('input[name="global_dup_action"][value="overwrite"]').prop('checked', true);
        updateDupChoiceStyles('overwrite');
        updateConfirmButtonLabel('overwrite');
        $('.row-dup-action').val('overwrite');
        $('#drawer-duplicate-backdrop, #drawer-duplicate-popup').addClass('hidden');
        $('#btn-confirm-save-import').trigger('click');
    });

    // Cancel Preview & Choose Another File
    $('#btn-cancel-import-preview').click(function() {
        resetImportState();
    });

    // STEP 2: Confirm & Save Leads
    $('#btn-confirm-save-import').click(function() {
        var formEl = document.getElementById('drawer-import-form');
        var fileInput = document.getElementById('drawer-file-input');
        if (!fileInput || !fileInput.files || !fileInput.files.length) {
            CRM.toast('error', 'Please choose a spreadsheet file first.');
            return;
        }

        var $btn = $(this);
        var originalText = $btn.html();
        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Processing & Saving to Database...');

        var formData = new FormData(formEl);
        var globalAction = $('input[name="global_dup_action"]:checked').val() || 'skip';
        var rowActions = {};
        $('.row-dup-action').each(function() {
            var r = $(this).data('row');
            rowActions[r] = $(this).val();
        });

        formData.append('duplicate_action', globalAction);
        formData.append('row_actions', JSON.stringify(rowActions));

        // Attach CSRF token
        if (typeof CI3_CSRF_NAME !== 'undefined' && typeof CI3_CSRF_HASH !== 'undefined') {
            formData.append(CI3_CSRF_NAME, CI3_CSRF_HASH);
        } else {
            var $csrf = $('input[name="csrf_token"]');
            if ($csrf.length) formData.append($csrf.attr('name'), $csrf.val());
        }

        $.ajax({
            url: BASE_URL + 'leads/import_confirm',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(resp) {
                if (resp.status === 'success') {
                    CRM.toast('success', resp.message);

                    var d = resp.data || {};
                    var imported = d.imported || 0;
                    var updated  = d.updated || 0;
                    var replaced = d.deleted_and_replaced || d.replaced || 0;
                    var skipped  = d.skipped || 0;

                    var html = '<div class="p-4 bg-emerald-50 border border-emerald-200 rounded-2xl space-y-3 mt-4">';
                    html += '<div class="flex items-center gap-2 text-emerald-900 font-bold text-sm">';
                    html += '<i class="fa fa-check-circle text-emerald-600 text-lg"></i> Import Process Successfully Completed!';
                    html += '</div>';

                    html += '<div class="grid grid-cols-2 sm:grid-cols-4 gap-2 text-center text-xs">';
                    html += '<div class="p-2.5 bg-white border border-emerald-200 rounded-xl font-bold text-emerald-800"><span class="block text-base font-black text-emerald-600">' + imported + '</span>New Added</div>';
                    if (updated > 0) {
                        html += '<div class="p-2.5 bg-white border border-blue-200 rounded-xl font-bold text-blue-800"><span class="block text-base font-black text-blue-600">' + updated + '</span>Overwritten</div>';
                    }
                    if (replaced > 0) {
                        html += '<div class="p-2.5 bg-white border border-rose-200 rounded-xl font-bold text-rose-800"><span class="block text-base font-black text-rose-600">' + replaced + '</span>Replaced</div>';
                    }
                    if (skipped > 0) {
                        html += '<div class="p-2.5 bg-white border border-amber-200 rounded-xl font-bold text-amber-800"><span class="block text-base font-black text-amber-600">' + skipped + '</span>Skipped</div>';
                    }
                    html += '</div>';

                    html += '<button type="button" id="btn-import-done" class="w-full py-2.5 bg-emerald-600 text-white font-bold rounded-xl text-xs hover:bg-emerald-700 transition-colors shadow-sm">Done & Close Drawer</button>';
                    html += '</div>';

                    $('#drawer-validation-preview').addClass('hidden');
                    $('#drawer-duplicate-backdrop, #drawer-duplicate-popup').addClass('hidden');
                    $('#drawer-import-results').html(html).removeClass('hidden');

                    if (window.mainTable) {
                        window.mainTable.ajax.reload(null, false);
                    }
                    refreshProductLists();
                } else {
                    CRM.toast('error', resp.message || 'Import failed.');
                }
            },
            error: function(xhr) {
                var json = xhr.responseJSON;
                var errMsg = '';
                if (json && json.message) {
                    errMsg = json.message;
                } else if (xhr.status === 403) {
                    errMsg = 'CSRF Token or Session expired. Please re-validate the file.';
                } else if (xhr.status === 401) {
                    errMsg = 'Your session has expired. Please refresh and log in.';
                } else if (xhr.responseText) {
                    var match = xhr.responseText.match(/<p>(.*?)<\/p>/i) || xhr.responseText.match(/<h4>(.*?)<\/h4>/i);
                    if (match && match[1]) {
                        errMsg = match[1].replace(/<[^>]+>/g, '').trim();
                    }
                }
                if (!errMsg) {
                    errMsg = (xhr.statusText && xhr.statusText !== 'error') ? xhr.statusText : 'Import confirmation failed.';
                }
                CRM.toast('error', errMsg);
            },
            complete: function() {
                $btn.prop('disabled', false).html(originalText);
            }
        });
    });

    $(document).on('click', '#btn-import-done', function() {
        closeImportDrawer();
    });


    // 8. Lead Status Change (Activate, Deactivate, Delete, Restore)
    $(document).on('click', '.btn-lead-status', function() {
        var action = $(this).data('action'), id = $(this).data('id');
        var msg = action === 'delete' ? 'Delete this lead record?' : 'Change status for this lead?';
        CRM.handle_status(action, id, BASE_URL + 'leads/status', window.mainTable, msg);
    });

    // =========================================================================
    // PRODUCT LISTS & SEGMENTS HUB (LIST CONCEPT)
    // =========================================================================

    // 1. Open Product Drill-down: Click Product List row or "Open List" button
    $(document).on('click', '.btn-filter-leads-by-product, .product-list-card-row', function(e) {
        // If clicking action buttons inside row, don't trigger row click
        if ($(e.target).closest('a, .btn-upload-to-product-list, .btn-delete-product-list, input').length) {
            return;
        }

        var id        = $(this).data('id');
        var name      = $(this).data('name') || 'Product List';
        var leads     = $(this).data('leads') || 0;
        var customers = $(this).data('customers') || 0;

        window.currentProductFilter = id;

        $('#active-product-title').text(name);
        $('#active-product-badge').text(leads + ' Leads • ' + customers + ' Customers');

        // Hide Product Lists Hub, Show Leads & Customers for this Product
        $('#view-products-hub').addClass('hidden');
        $('#view-leads-database').removeClass('hidden');

        if (window.mainTable) {
            window.mainTable.ajax.reload();
        }

        $('html, body').animate({ scrollTop: 0 }, 200);
    });

    // 2. Back to Product Lists Overview
    $(document).on('click', '#btn-back-to-products', function() {
        window.currentProductFilter = '';
        $('#view-leads-database').addClass('hidden');
        $('#view-products-hub').removeClass('hidden');
        refreshProductLists();
        $('html, body').animate({ scrollTop: 0 }, 200);
    });

    // 4. Live Search among Product Lists
    $(document).on('input', '#product-list-search-input', function() {
        var q = $(this).val().toLowerCase().trim();
        $('.product-list-card-row').each(function() {
            var name = $(this).data('name') || '';
            var rowText = $(this).text().toLowerCase();
            if (!q || name.indexOf(q) !== -1 || rowText.indexOf(q) !== -1) {
                $(this).removeClass('hidden');
            } else {
                $(this).addClass('hidden');
            }
        });
    });

    // 5. Toggle Quick Filter Bar
    $(document).on('click', '#btn-toggle-list-filters', function() {
        $('#list-quick-filter-bar').toggleClass('hidden');
    });

    // 6. Quick Filter Pills
    $(document).on('click', '.btn-list-filter-pill', function() {
        $('.btn-list-filter-pill').removeClass('active bg-amber-500 text-white shadow-2xs').addClass('bg-white text-gray-600 border border-gray-200');
        $(this).addClass('active bg-amber-500 text-white shadow-2xs').removeClass('bg-white text-gray-600 border border-gray-200');
        var f = $(this).data('filter');
        $('.product-list-card-row').each(function() {
            var leads = parseInt($(this).data('leads')) || 0;
            var custs = parseInt($(this).data('customers')) || 0;
            if (f === 'all') {
                $(this).removeClass('hidden');
            } else if (f === 'has_customers') {
                $(this).toggle(custs > 0);
            } else if (f === 'has_leads') {
                $(this).toggle(leads > 0);
            }
        });
    });

    // 7. Upload leads to a specific product
    $(document).on('click', '.btn-upload-to-product-list', function(e) {
        e.stopPropagation();
        var id = $(this).data('id');
        $('#drawer-target-product').val(id).trigger('change');
        $('#btn-open-import').trigger('click');
    });

    // 8. Refresh Product Lists via AJAX
    function refreshProductLists() {
        $.ajax({
            url: BASE_URL + 'leads/lists_ajax',
            method: 'GET',
            dataType: 'json',
            success: function(resp) {
                if (resp && resp.status === 'success' && resp.data) {
                    var lists = resp.data;
                    $('#product-lists-total-badge').text(lists.length + ' Lists');
                    var html = '';
                    if (lists.length === 0) {
                        html = '<tr><td colspan="7" class="py-6 text-center text-gray-400">No products found in CRM database.</td></tr>';
                    } else {
                        lists.forEach(function(l) {
                            var escName = $('<div>').text(l.name).html();
                            var escSku  = l.sku ? $('<div>').text(l.sku).html() : '';
                            var escCat  = l.category_name ? $('<div>').text(l.category_name).html() : '';
                            var subtitle = '';
                            if (escSku || escCat) {
                                subtitle = '<span class="block text-[11px] text-gray-400 font-normal">' + (escSku || '') + ((escSku && escCat) ? ' • ' : '') + (escCat || '') + '</span>';
                            }
                            var creator = $('<div>').text(l.created_by).html();
                            var modified = $('<div>').text(l.last_modified).html();

                            html += '<tr class="hover:bg-amber-50/40 transition-colors product-list-card-row cursor-pointer" data-id="' + l.id + '" data-name="' + escName.toLowerCase() + '" data-leads="' + l.leads_count + '" data-customers="' + l.customers_count + '">';
                            html += '<td class="py-3.5 px-4"><div class="flex items-center gap-2.5">';
                            html += '<div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-700 flex items-center justify-center font-bold text-xs border border-emerald-200 flex-shrink-0"><i class="fa fa-cube"></i></div>';
                            html += '<div><button type="button" class="btn-filter-leads-by-product text-left font-bold text-gray-900 hover:text-emerald-700 text-sm tracking-tight cursor-pointer uppercase" data-id="' + l.id + '" data-name="' + escName + '" data-leads="' + l.leads_count + '" data-customers="' + l.customers_count + '">' + escName + '</button>' + subtitle + '</div>';
                            html += '</div></td>';
                            html += '<td class="py-3.5 px-4"><span class="font-mono font-bold text-gray-900 text-sm">' + l.total_records + '</span><span class="text-[11px] text-gray-400 font-medium ml-1">Records</span></td>';
                            html += '<td class="py-3.5 px-4"><span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-bold bg-blue-50 text-blue-700 border border-blue-200"><i class="fa fa-users text-blue-600"></i><span>' + l.customers_count + '</span> Customers</span></td>';
                            html += '<td class="py-3.5 px-4"><span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200"><i class="fa fa-filter text-emerald-600"></i><span>' + l.leads_count + '</span> Leads</span></td>';
                            html += '<td class="py-3.5 px-4 text-gray-600 text-xs"><i class="fa fa-user-circle-o text-gray-400 mr-1"></i> ' + creator + '</td>';
                            html += '<td class="py-3.5 px-4 text-gray-500 text-xs"><i class="fa fa-clock-o text-gray-400 mr-1"></i> ' + modified + '</td>';
                            html += '<td class="py-3.5 px-4 text-right"><div class="inline-flex items-center gap-1.5">';
                            html += '<button type="button" class="btn-filter-leads-by-product px-3 py-1.5 text-xs font-bold bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl transition-colors shadow-2xs flex items-center gap-1 cursor-pointer" data-id="' + l.id + '" data-name="' + escName + '" data-leads="' + l.leads_count + '" data-customers="' + l.customers_count + '"><i class="fa fa-folder-open-o"></i> Open Leads</button>';
                            html += '<button type="button" class="btn-upload-to-product-list p-1.5 text-gray-400 hover:text-emerald-700 rounded-lg hover:bg-gray-100 transition-colors cursor-pointer" data-id="' + l.id + '" title="Upload Excel to this Product"><i class="fa fa-upload"></i></button>';
                            html += '<a href="' + BASE_URL + 'leads/export?format=xlsx&product_id=' + l.id + '" class="p-1.5 text-gray-400 hover:text-blue-700 rounded-lg hover:bg-gray-100 transition-colors" title="Export this product list"><i class="fa fa-download"></i></a>';
                            html += '</div></td></tr>';
                        });
                    }
                    $('#product-lists-summary-tbody').html(html);

                    // Also synchronize dropdowns
                    var currentTargetVal = $('#drawer-target-product').val();
                    var currentDtVal     = $('#dt-product-filter').val();
                    var optsHtml = '<option value="auto">🔄 Auto-Detect from Excel / Ads Keywords</option>';
                    var dtOptsHtml = '<option value="">All Products / Lists</option>';
                    lists.forEach(function(l) {
                        if (!l.is_unassigned) {
                            var escN = $('<div>').text(l.name).html();
                            optsHtml += '<option value="' + l.id + '">📦 ' + escN + '</option>';
                            dtOptsHtml += '<option value="' + l.id + '">📦 ' + escN + '</option>';
                        }
                    });
                    dtOptsHtml += '<option value="unassigned">Unassigned Leads</option>';

                    $('#drawer-target-product').html(optsHtml).val(currentTargetVal || 'auto');
                    $('#dt-product-filter').html(dtOptsHtml).val(currentDtVal || '');
                }
            }
        });
    }
    window.refreshProductLists = refreshProductLists;
});
