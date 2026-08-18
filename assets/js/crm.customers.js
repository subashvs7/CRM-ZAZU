/**
 * Customers module JS
 */
$(function() {
    if ($('#customers-table').length && !$.fn.DataTable.isDataTable('#customers-table')) {
        window.mainTable = $('#customers-table').DataTable({
            processing: true, serverSide: true,
            ajax: { 
                url: BASE_URL + 'customers/datatable', 
                data: function(d) { 
                    d.status_filter = window.currentStatusFilter || ''; 
                    d.customer_type = $('#filter-customer-type').val() || '';
                    d.staff_filter = $('#filter-staff').val() || '';
                    d.date_filter = $('#filter-date').val() || '';
                } 
            },
            columns: [
                {data:0},
                {data:1},
                {data:2},
                {data:3},
                {data:4},
                {data:5},
                {data:6, orderable:false},
                {data:7, orderable:false},
                {data:8},
                {data:9},
                {data:10, orderable:false}
            ],
            order: [[0, 'desc']],
            drawCallback: function() {
                // Initialize tooltips with click trigger
                $('[data-toggle="tooltip"]').tooltip({
                    trigger: 'click'
                });
            }
        });

        // Trigger AJAX reload when filters change
        $('#filter-staff, #filter-date').on('change', function() {
            window.mainTable.ajax.reload();
        });
    }

    // Dismiss tooltips when clicking outside of them
    $(document).on('click', function(e) {
        $('[data-toggle="tooltip"]').each(function() {
            if (!$(this).is(e.target) && $(this).has(e.target).length === 0 && $('.tooltip').has(e.target).length === 0) {
                $(this).tooltip('hide');
            }
        });
    });

    function loadCustomerPackageSplits(productId, preselectedSplitId) {
        if (!productId || productId === '' || productId === '0') {
            $('#customer-splits-container').slideUp(150);
            if ($.fn.select2 && $('#customer-package-splits').data('select2')) {
                $('#customer-package-splits').val('').trigger('change.select2');
            }
            $('#customer-package-splits').html('<option value="">-- Select Package Tier --</option>');
            return;
        }

        $('#splits-status-msg').text('Loading package tiers...');
        $('#customer-splits-container').slideDown(150);

        var pidsParam = Array.isArray(productId) ? productId.join(',') : productId;

        $.get(BASE_URL + 'customers/get_splits_by_products_ajax', { product_ids: pidsParam }, function(res) {
            $('#splits-status-msg').text('');
            if (res.status === 'success' && res.data && res.data.length > 0) {
                let html = '<option value="">-- Select Package Tier --</option>';
                res.data.forEach(function(s) {
                    html += `<option value="${s.id}">${s.display_name || (s.product_name + ' - ' + s.tier_name)}</option>`;
                });

                $('#customer-package-splits').html(html);

                if ($.fn.select2) {
                    if ($('#customer-package-splits').data('select2')) {
                        $('#customer-package-splits').select2('destroy');
                    }
                    $('#customer-package-splits').select2({
                        width: '100%',
                        placeholder: '-- Select Package Tier --',
                        allowClear: true
                    });
                }

                if (preselectedSplitId) {
                    var singleSid = Array.isArray(preselectedSplitId) ? preselectedSplitId[0] : (preselectedSplitId + '').split(',')[0];
                    $('#customer-package-splits').val(singleSid).trigger('change');
                } else {
                    $('#customer-package-splits').val('').trigger('change');
                }
            } else {
                $('#customer-package-splits').html('<option value="">No package tiers configured for selected product</option>');
                if ($.fn.select2) {
                    if ($('#customer-package-splits').data('select2')) {
                        $('#customer-package-splits').select2('destroy');
                    }
                    $('#customer-package-splits').select2({ width: '100%' });
                }
            }
        }).fail(function() {
            $('#splits-status-msg').text('');
        });
    }

    $('#customer-products').on('change', function() {
        if (!window.isCustomerModalPopulating) {
            let pid = $(this).val();
            loadCustomerPackageSplits(pid, '');
        }
    });

    function openCustomerModal(data) {
        var $f = $('#customer-form');
        $f[0].reset();
        CRM.clear_errors($f);
        $('#customer-id').val(data ? data.id : 0);
        CRM.init_plugins($('#customer-modal'));
        
        window.isCustomerModalPopulating = true;

        if (data) {
            $.each(['customer_name','customer_org_name','phone','email','gst_number','address','city','state','pincode','latitude','longitude','notes'], function(i, f) {
                $f.find('[name="'+f+'"]').val(data[f] || '');
            });
            if (data.assigned_to) $f.find('[name="assigned_to"]').val(data.assigned_to).trigger('change');
            
            var sid = data.package_split_ids ? (data.package_split_ids + '').split(',')[0] : '';
            if (data.product_ids) {
                var pid = (data.product_ids + '').split(',')[0];
                $('#customer-products').val(pid).trigger('change');
                loadCustomerPackageSplits(pid, sid);
            } else {
                $('#customer-products').val('').trigger('change');
                loadCustomerPackageSplits('', '');
            }
            
            $('#customer-modal .modal-title').text('Edit Customer');
        } else {
            $('#customer-products').val('').trigger('change');
            loadCustomerPackageSplits('', '');
            $('#customer-modal .modal-title').text('Add Customer');
        }

        window.isCustomerModalPopulating = false;
        $('#customer-modal').modal('show');
    }

    $('#btn-add-customer').click(function() { openCustomerModal(null); });

    $(document).on('click', '.btn-edit-customer', function() {
        $.getJSON(BASE_URL + 'customers/get/' + $(this).data('id'), function(res) {
            if (res.status === 'success') openCustomerModal(res.data);
            else CRM.toast('error', res.message || 'Failed to load.');
        });
    });

    $('#btn-save-customer').click(function() {
        var $btn = $(this); CRM.btn_loading($btn);
        $.ajax({
            url: BASE_URL + 'customers/save', method: 'POST',
            data: new FormData($('#customer-form')[0]), processData: false, contentType: false,
            success: function(res) {
                if (res.status === 'success') {
                    CRM.toast('success', res.message);
                    $('#customer-modal').modal('hide');
                    if (window.mainTable) window.mainTable.ajax.reload(null, false);
                } else {
                    CRM.show_errors($('#customer-form'), res.errors || {});
                    CRM.toast('error', res.message);
                }
            },
            complete: function() { CRM.btn_reset($btn); }
        });
    });

    $(document).on('click', '.btn-customer-status', function() {
        var action = $(this).data('action'), id = $(this).data('id');
        CRM.handle_status(action, id, BASE_URL + 'customers/status', window.mainTable,
            action === 'delete' ? 'Delete this customer?' : 'Are you sure?');
    });

    // Plan Visit from Customers Page
    $(document).on('click', '.btn-plan-visit', function() {
        var id = $(this).data('id');
        var name = $(this).data('name');
        
        $('#plan-visit-form')[0].reset();
        $('#plan-visit-customer-id').val(id);
        $('#plan-visit-customer-name').text(name);
        
        $('#plan-visit-modal').modal('show');
    });

    $('#btn-save-plan-visit').click(function() {
        var $btn = $(this);
        CRM.btn_loading($btn);
        
        $.ajax({
            url: BASE_URL + 'visits/save',
            method: 'POST',
            data: new FormData($('#plan-visit-form')[0]),
            processData: false,
            contentType: false,
            success: function(res) {
                if (res.status === 'success') {
                    CRM.toast('success', 'Visit planned successfully.');
                    $('#plan-visit-modal').modal('hide');
                } else {
                    CRM.toast('error', res.message);
                }
            },
            complete: function() { 
                CRM.btn_reset($btn); 
            }
        });
    });


    // ---- Contact persons (customer detail page) ----
    function openContactModal(customerId, data) {
        var $f = $('#contact-form');
        $f[0].reset();
        CRM.clear_errors($f);
        $('#contact-customer-id').val(customerId);
        $('#contact-id').val(data ? data.id : 0);
        if (data) {
            $f.find('[name="name"]').val(data.name || '');
            $f.find('[name="designation"]').val(data.designation || '');
            $f.find('[name="phone"]').val(data.phone || '');
            $f.find('[name="email"]').val(data.email || '');
            $f.find('[name="is_primary"]').prop('checked', data.is_primary == 1);
            $('#contact-modal .modal-title').text('Edit Contact');
        } else {
            $('#contact-modal .modal-title').text('Add Contact');
        }
        $('#contact-modal').modal('show');
    }

    $(document).on('click', '#btn-add-contact', function() {
        openContactModal($(this).data('customer'), null);
    });

    $(document).on('click', '.btn-edit-contact', function() {
        var data = $(this).data();
        openContactModal(data.customer, data);
    });

    $('#btn-save-contact').click(function() {
        var name = $.trim($('#contact-name').val());
        if (!name) { CRM.toast('error', 'Name is required.'); return; }
        var $btn = $(this); CRM.btn_loading($btn);
        $.ajax({
            url: BASE_URL + 'customers/contacts/save', method: 'POST',
            data: new FormData($('#contact-form')[0]), processData: false, contentType: false,
            success: function(res) {
                if (res.status === 'success') {
                    CRM.toast('success', res.message);
                    $('#contact-modal').modal('hide');
                    location.reload();
                } else {
                    CRM.show_errors($('#contact-form'), res.errors || {});
                    CRM.toast('error', res.message);
                }
            },
            complete: function() { CRM.btn_reset($btn); }
        });
    });

    $(document).on('click', '.btn-delete-contact', function() {
        if (!confirm('Remove this contact person?')) return;
        $.post(BASE_URL + 'customers/contacts/status',
            {id: $(this).data('id'), action: 'delete', [CI3_CSRF_NAME]: CI3_CSRF_HASH},
            function(res) {
                if (res.status === 'success') { CRM.toast('success', 'Contact removed.'); location.reload(); }
                else CRM.toast('error', res.message || 'Failed.');
            }
        );
    });

    $(document).on('click', '.btn-view-notes', function() {
        var notes = $(this).data('notes');
        Swal.fire({
            html: 
                '<div class="bg-blue-600 text-white p-4 rounded-t-xl text-left">' +
                    '<h3 class="text-base font-bold flex items-center gap-2"><i class="fa fa-sticky-note-o"></i> Customer Notes</h3>' +
                '</div>' +
                '<div class="text-left text-[13px] text-gray-700 p-5 leading-relaxed whitespace-pre-wrap bg-white">' + 
                    CRM.esc(notes) + 
                '</div>',
            showConfirmButton: true,
            confirmButtonText: 'Close',
            confirmButtonColor: '#2563eb',
            padding: '0',
            customClass: {
                popup: 'rounded-xl overflow-hidden border border-gray-200 shadow-xl',
                htmlContainer: 'm-0 p-0',
                confirmButton: 'mb-4 px-6 py-2 rounded-lg text-sm font-semibold shadow-sm hover:bg-blue-700'
            }
        });
    });

    $(document).on('click', '.btn-view-customer', function() {
        var id = $(this).data('id');
        $.getJSON(BASE_URL + 'customers/get_details/' + id, function(res) {
            if (res.status === 'success') {
                var d = res.data;
                var c = d.customer;
                
                // Set Header details
                var initialName = c.customer_name || c.customer_org_name || 'C';
                $('#view-customer-initial').text(initialName.charAt(0).toUpperCase());
                $('#view-customer-name').text(c.customer_org_name || 'Customer Details');
                
                // Set General Info fields
                $('#view-customer-contact-name').text(c.customer_name || '—');
                $('#view-customer-company-name').text(c.customer_org_name || '—');
                
                // Status badge
                var stClass = 'bg-gray-100 text-gray-800';
                if (c.status === 'active') stClass = 'bg-green-100 text-green-800';
                else if (c.status === 'inactive') stClass = 'bg-amber-100 text-amber-800';
                $('#view-customer-status-badge').attr('class', 'inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium uppercase tracking-wider ' + stClass).text(c.status);
                
                // General info
                $('#view-customer-phone').html(c.phone ? '<a href="tel:'+CRM.esc(c.phone)+'" class="text-blue-600 hover:underline"><i class="fa fa-phone mr-1"></i>'+CRM.esc(c.phone)+'</a>' : '—');
                $('#view-customer-email').text(c.email || '—');
                $('#view-customer-gst').text(c.gst_number || '—');
                $('#view-customer-assigned').text(c.assigned_name || 'Unassigned');
                $('#view-customer-address').text([c.address, c.city, c.state, c.pincode].filter(Boolean).join(', ') || '—');
                
                // Products
                var prodHtml = '';
                if (d.customer.products_data && d.customer.products_data.length) {
                    $.each(d.customer.products_data, function(i, p) {
                        prodHtml += '<button type="button" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-200 hover:bg-blue-600 hover:text-white transition-all shadow-xs cursor-pointer btn-view-product-popup" data-product-id="' + p.id + '" data-customer-id="' + id + '"><i class="fa fa-cube text-[10px]"></i> ' + CRM.esc(p.name) + '</button>';
                    });
                } else if (c.products && c.products.length) {
                    $.each(c.products, function(i, name) {
                        prodHtml += '<span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-blue-50 text-blue-700 border border-blue-100">' + CRM.esc(name) + '</span>';
                    });
                } else {
                    prodHtml = '<span class="text-gray-400 text-xs">—</span>';
                }
                $('#view-customer-products-container').html(prodHtml);

                // Package Splits
                var splitsHtml = '';
                if (d.customer.splits_data && d.customer.splits_data.length) {
                    $.each(d.customer.splits_data, function(i, sp) {
                        var badgeC = sp.badge_color || 'indigo';
                        splitsHtml += '<button type="button" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-bold bg-' + badgeC + '-50 text-' + badgeC + '-700 border border-' + badgeC + '-200 hover:bg-' + badgeC + '-600 hover:text-white transition-all shadow-xs cursor-pointer btn-view-product-popup" data-product-id="' + sp.product_id + '" data-customer-id="' + id + '"><i class="fa ' + (sp.icon || 'fa-tag') + ' text-[10px]"></i> ' + CRM.esc(sp.tier_name) + '</button>';
                    });
                    $('#view-customer-splits-wrap').show();
                } else if (c.package_splits && c.package_splits.length) {
                    $.each(c.package_splits, function(i, splitName) {
                        splitsHtml += '<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-200"><i class="fa fa-tag text-[10px]"></i> ' + CRM.esc(splitName) + '</span>';
                    });
                    $('#view-customer-splits-wrap').show();
                } else {
                    splitsHtml = '<span class="text-gray-400 text-xs">—</span>';
                    $('#view-customer-splits-wrap').hide();
                }
                $('#view-customer-splits-container').html(splitsHtml);
                
                // Contacts (Contact Persons + Contact Book)
                var contactsHtml = '';
                var allContacts = [];
                
                // Add contact persons (from Contact_person_model)
                if (d.contacts && d.contacts.length) {
                    $.each(d.contacts, function(i, con) {
                        allContacts.push({ name: con.name, phone: con.phone, title: con.designation, is_primary: con.is_primary, source: 'contact_person' });
                    });
                }
                // Add Contact Book entries linked to this customer
                if (d.cb_contacts && d.cb_contacts.length) {
                    $.each(d.cb_contacts, function(i, con) {
                        allContacts.push({ name: con.name, phone: con.phone, title: con.job_title, is_primary: 0, source: 'contact_book' });
                    });
                }

                if (allContacts.length) {
                    $.each(allContacts, function(i, con) {
                        var isPri = con.is_primary == 1 ? '<span class="inline-flex items-center px-1.5 py-0.5 rounded text-[9px] font-bold bg-green-100 text-green-700 uppercase tracking-wide ml-1.5">Primary</span>' : '';
                        var srcBadge = con.source === 'contact_book' ? '<span class="inline-flex items-center px-1.5 py-0.5 rounded text-[9px] font-medium bg-indigo-50 text-indigo-500 ml-1">Book</span>' : '';
                        contactsHtml += '<div class="py-3 flex items-start gap-2.5 border-b border-gray-50 last:border-0">' +
                            '<div class="w-7 h-7 rounded-full bg-purple-100 text-purple-700 text-xs font-bold flex items-center justify-center flex-shrink-0 mt-0.5">' + (con.name ? con.name.charAt(0).toUpperCase() : 'C') + '</div>' +
                            '<div class="min-w-0 flex-1">' +
                                '<div class="font-semibold text-gray-800 text-xs flex items-center flex-wrap gap-1">' + CRM.esc(con.name) + isPri + srcBadge + '</div>' +
                                (con.title ? '<div class="text-[10px] text-gray-400 mt-0.5">' + CRM.esc(con.title) + '</div>' : '') +
                                (con.phone ? '<div class="text-[11px] text-blue-600 mt-1 font-medium"><a href="tel:' + CRM.esc(con.phone) + '" class="flex items-center gap-1 hover:underline"><i class="fa fa-phone text-[9px]"></i>' + CRM.esc(con.phone) + '</a></div>' : '<div class="text-[10px] text-gray-300 mt-0.5">No phone</div>') +
                            '</div>' +
                        '</div>';
                    });
                } else {
                    contactsHtml = '<div class="py-6 text-center text-gray-400 text-xs">No contacts added</div>';
                }
                $('#view-customer-contacts-list').html(contactsHtml);
                
                // Visit Plans
                var visitsHtml = '';
                if (d.visit_plans && d.visit_plans.length) {
                    $.each(d.visit_plans, function(i, vp) {
                        var statusClass = 'bg-gray-100 text-gray-800';
                        if (vp.visit_status === 'completed') statusClass = 'bg-green-100 text-green-800';
                        else if (vp.visit_status === 'pending') statusClass = 'bg-blue-100 text-blue-800';
                        
                        visitsHtml += '<tr class="hover:bg-gray-50">' +
                            '<td class="px-4 py-2.5 font-medium text-gray-800 border-r border-b border-gray-200">' + CRM.esc(vp.planned_date) + (vp.planned_time ? ' ' + CRM.esc(vp.planned_time) : '') + '</td>' +
                            '<td class="px-4 py-2.5 text-gray-600 border-r border-b border-gray-200">' + CRM.esc(vp.purpose || '—') + '</td>' +
                            '<td class="px-4 py-2.5 text-gray-600 border-r border-b border-gray-200">' + CRM.esc(vp.user_name || '—') + '</td>' +
                            '<td class="px-4 py-2.5 border-b border-gray-200"><span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium uppercase ' + statusClass + '">' + CRM.esc(vp.visit_status) + '</span></td>' +
                        '</tr>';
                    });
                } else {
                    visitsHtml = '<tr><td colspan="4" class="py-6 text-center text-gray-400 border-b border-gray-200">No visit plans found</td></tr>';
                }
                $('#view-customer-visits-table').html(visitsHtml);
                
                // Visit Logs
                var logsHtml = '';
                if (d.visit_logs && d.visit_logs.length) {
                    $.each(d.visit_logs, function(i, vl) {
                        var inTime = vl.check_in_at ? new Date(vl.check_in_at).toLocaleString() : '—';
                        var outTime = vl.check_out_at ? new Date(vl.check_out_at).toLocaleTimeString() : 'Not checked out';
                        
                        logsHtml += '<tr class="hover:bg-gray-50">' +
                            '<td class="px-4 py-2.5 font-medium text-gray-800 border-r border-b border-gray-200">' + CRM.esc(inTime) + '<div class="text-[10px] text-gray-400 mt-0.5">Out: ' + CRM.esc(outTime) + '</div></td>' +
                            '<td class="px-4 py-2.5 text-gray-600 border-r border-b border-gray-200">' + CRM.esc(vl.user_name || '—') + '</td>' +
                            '<td class="px-4 py-2.5 text-gray-700 font-semibold border-r border-b border-gray-200">' + CRM.esc(vl.visit_outcome || '—') + '</td>' +
                            '<td class="px-4 py-2.5 text-gray-500 max-w-xs truncate border-b border-gray-200" title="' + CRM.esc(vl.notes || '') + '">' + CRM.esc(vl.notes || '—') + '</td>' +
                        '</tr>';
                    });
                } else {
                    logsHtml = '<tr><td colspan="4" class="py-6 text-center text-gray-400 border-b border-gray-200">No check-in history found</td></tr>';
                }
                $('#view-customer-logs-table').html(logsHtml);
                
                $('#customer-view-modal').modal('show');
            } else {
                CRM.toast('error', res.message || 'Failed to load details.');
            }
        });
    });

    // ---- View Product & Package Splits SweetAlert Modal (Single Card Only) ----
    $(document).on('click', '.btn-view-product-popup', function(e) {
        e.preventDefault();
        e.stopPropagation();

        var productId = $(this).data('product-id');
        var splitId = $(this).data('split-id') || 0;
        var customerId = $(this).data('customer-id') || 0;

        if (!productId) {
            CRM.toast('warning', 'Product information not available.');
            return;
        }

        Swal.fire({
            title: 'Loading Details...',
            html: '<div class="flex items-center justify-center p-6"><i class="fa fa-circle-o-notch fa-spin text-blue-600 text-3xl"></i></div>',
            showConfirmButton: false,
            allowOutsideClick: false,
            didOpen: function() {
                Swal.showLoading();
            }
        });

        $.getJSON(BASE_URL + 'customers/get_product_splits_modal_data', { product_id: productId, split_id: splitId, customer_id: customerId }, function(res) {
            if (res.status !== 'success' || !res.data) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: res.message || 'Failed to fetch product information.'
                });
                return;
            }

            var p = res.data.product;
            var splits = res.data.splits || [];
            var custInfo = res.data.customer_info;

            var tierColorMap = {
                'amber':   { bg: 'bg-amber-500', lightBg: 'bg-amber-50', border: 'border-amber-200', text: 'text-amber-700', badge: 'bg-amber-100 text-amber-800 border-amber-200', grad: 'from-amber-500 to-amber-600' },
                'slate':   { bg: 'bg-slate-600', lightBg: 'bg-slate-50', border: 'border-slate-200', text: 'text-slate-700', badge: 'bg-slate-100 text-slate-800 border-slate-200', grad: 'from-slate-600 to-slate-700' },
                'yellow':  { bg: 'bg-yellow-500', lightBg: 'bg-yellow-50', border: 'border-yellow-200', text: 'text-yellow-800', badge: 'bg-yellow-100 text-yellow-800 border-yellow-200', grad: 'from-amber-400 to-yellow-500' },
                'purple':  { bg: 'bg-purple-600', lightBg: 'bg-purple-50', border: 'border-purple-200', text: 'text-purple-700', badge: 'bg-purple-100 text-purple-800 border-purple-200', grad: 'from-purple-600 to-indigo-600' },
                'blue':    { bg: 'bg-blue-600', lightBg: 'bg-blue-50', border: 'border-blue-200', text: 'text-blue-700', badge: 'bg-blue-100 text-blue-800 border-blue-200', grad: 'from-blue-600 to-cyan-600' },
                'emerald': { bg: 'bg-emerald-600', lightBg: 'bg-emerald-50', border: 'border-emerald-200', text: 'text-emerald-700', badge: 'bg-emerald-100 text-emerald-800 border-emerald-200', grad: 'from-emerald-500 to-teal-600' },
                'rose':    { bg: 'bg-rose-600', lightBg: 'bg-rose-50', border: 'border-rose-200', text: 'text-rose-700', badge: 'bg-rose-100 text-rose-800 border-rose-200', grad: 'from-rose-500 to-pink-600' },
                'indigo':  { bg: 'bg-indigo-600', lightBg: 'bg-indigo-50', border: 'border-indigo-200', text: 'text-indigo-700', badge: 'bg-indigo-100 text-indigo-800 border-indigo-200', grad: 'from-indigo-600 to-blue-600' },
                'gray':    { bg: 'bg-gray-600', lightBg: 'bg-gray-50', border: 'border-gray-200', text: 'text-gray-700', badge: 'bg-gray-100 text-gray-800 border-gray-200', grad: 'from-gray-600 to-gray-700' }
            };

            // Logo markup
            var logoHtml = '';
            if (p.logo_url) {
                logoHtml = '<div class="w-16 h-16 bg-white rounded-2xl p-2 shadow-md border border-white/20 flex items-center justify-center flex-shrink-0 overflow-hidden">' +
                    '<img src="' + CRM.esc(p.logo_url) + '" class="w-full h-full object-contain" alt="' + CRM.esc(p.name) + '" onerror="this.outerHTML=\'<i class=\\\'fa fa-cube text-blue-600 text-3xl\\\'></i>\';">' +
                '</div>';
            } else {
                logoHtml = '<div class="w-16 h-16 bg-white rounded-2xl p-2 shadow-md border border-white/20 flex items-center justify-center flex-shrink-0">' +
                    '<i class="fa fa-cube text-blue-600 text-3xl"></i>' +
                '</div>';
            }

            // Customer banner
            var custBadge = '';
            if (custInfo && custInfo.name) {
                custBadge = '<span class="inline-flex items-center gap-1.5 px-3 py-1 bg-white/15 backdrop-blur-md rounded-full text-[11px] font-semibold text-white border border-white/20">' +
                    '<i class="fa fa-user text-blue-200"></i> ' + CRM.esc(custInfo.name) +
                '</span>';
            }

            // Target split card: get ONLY that card
            var singleSplit = null;
            if (splits && splits.length > 0) {
                if (splitId > 0) {
                    singleSplit = splits.find(function(s) { return s.id == splitId; }) || splits[0];
                } else {
                    singleSplit = splits.find(function(s) { return s.is_customer_selected; }) || splits[0];
                }
            }

            var modalHtml = '<div class="w-full text-left font-sans select-text">' +
                // Top Hero Header with Product Logo & Name
                '<div class="relative bg-gradient-to-r from-slate-900 via-indigo-950 to-blue-900 text-white p-5 sm:p-6 rounded-t-3xl overflow-hidden">' +
                    '<div class="flex items-center justify-between gap-3 relative z-10">' +
                        '<div class="flex items-center gap-3.5 min-w-0">' +
                            logoHtml +
                            '<div class="min-w-0">' +
                                '<div class="flex items-center gap-2 flex-wrap">' +
                                    '<h2 class="text-xl font-black tracking-tight text-white">' + CRM.esc(p.name) + '</h2>' +
                                    '<span class="px-2 py-0.5 text-xs font-mono font-bold bg-white/15 text-blue-200 rounded-lg border border-white/10">' + CRM.esc(p.sku) + '</span>' +
                                '</div>' +
                                (p.subtitle ? '<p class="text-xs text-blue-100/90 mt-0.5 font-medium truncate max-w-sm">' + CRM.esc(p.subtitle) + '</p>' : '') +
                                (p.website_url ? '<div class="mt-1"><a href="' + (p.website_url.indexOf('http') === 0 ? CRM.esc(p.website_url) : 'https://' + CRM.esc(p.website_url)) + '" target="_blank" class="inline-flex items-center gap-1 text-[11px] text-blue-200 hover:text-white underline"><i class="fa fa-globe"></i> ' + CRM.esc(p.website_url) + '</a></div>' : '') +
                            '</div>' +
                        '</div>' +
                        (custBadge ? '<div class="flex-shrink-0">' + custBadge + '</div>' : '') +
                    '</div>' +
                '</div>' +

                // Body: ONLY THAT CARD
                '<div class="p-5 sm:p-6 bg-slate-50 space-y-4 max-h-[75vh] overflow-y-auto custom-scrollbar">';

            if (singleSplit) {
                var colorKey = singleSplit.badge_color || 'indigo';
                var cTheme = tierColorMap[colorKey] || tierColorMap['indigo'];
                var isSelected = singleSplit.is_customer_selected;
                var isPop = singleSplit.is_popular == 1;

                modalHtml += '<div class="bg-white rounded-2xl border border-gray-200 shadow-md overflow-hidden flex flex-col">' +
                    // Card Top Header
                    '<div class="' + cTheme.lightBg + ' px-5 py-4 border-b ' + cTheme.border + ' flex items-center justify-between gap-3">' +
                        '<div class="flex items-center gap-2.5 min-w-0">' +
                            '<span class="w-9 h-9 rounded-xl bg-white ' + cTheme.text + ' border ' + cTheme.border + ' flex items-center justify-center text-base shadow-xs flex-shrink-0"><i class="fa ' + (singleSplit.icon || 'fa-cube') + '"></i></span>' +
                            '<div class="min-w-0">' +
                                '<h4 class="font-extrabold text-base text-gray-900">' + CRM.esc(singleSplit.tier_name) + '</h4>' +
                                (singleSplit.tier_subtitle ? '<p class="text-xs text-gray-500 font-medium mt-0.5">' + CRM.esc(singleSplit.tier_subtitle) + '</p>' : '') +
                            '</div>' +
                        '</div>' +
                        '<div class="flex items-center gap-1.5 flex-shrink-0">' +
                            (isPop ? '<span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-black bg-gradient-to-r from-amber-500 to-orange-500 text-white shadow-xs"><i class="fa fa-star text-[9px]"></i> Popular</span>' : '') +
                            (isSelected ? '<span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-black bg-emerald-600 text-white shadow-xs"><i class="fa fa-check text-[9px]"></i> Assigned</span>' : '') +
                        '</div>' +
                    '</div>' +

                    // Pricing & Details Body
                    '<div class="p-5 space-y-4">' +
                        // Price Block
                        '<div class="bg-slate-50/80 rounded-xl p-4 border border-slate-100 flex items-center justify-between flex-wrap gap-2">' +
                            '<div>' +
                                '<div class="text-[10px] font-bold uppercase tracking-wider text-gray-400">Monthly Pricing</div>' +
                                '<div class="flex items-baseline gap-1 mt-0.5">' +
                                    '<span class="text-3xl font-black text-gray-900 tracking-tight">' + CRM.esc(singleSplit.monthly_price_formatted) + '</span>' +
                                    '<span class="text-xs font-semibold text-gray-500">/ month</span>' +
                                '</div>' +
                            '</div>' +
                            '<div class="text-right">' +
                                '<div class="text-[10px] font-bold uppercase tracking-wider text-gray-400">Annual Plan</div>' +
                                '<div class="text-sm font-bold text-gray-800 mt-0.5">' + CRM.esc(singleSplit.yearly_price_formatted) + '</div>' +
                                (singleSplit.discount_label ? '<span class="inline-block mt-0.5 px-2 py-0.5 bg-emerald-50 text-emerald-700 border border-emerald-200 rounded text-[10px] font-black">' + CRM.esc(singleSplit.discount_label) + '</span>' : '') +
                            '</div>' +
                        '</div>' +

                        (singleSplit.implementation_fee_formatted && singleSplit.implementation_fee_formatted !== '₹0' ? 
                            '<div class="text-xs text-indigo-700 bg-indigo-50/80 px-3 py-2 rounded-xl border border-indigo-100 flex items-center gap-2"><i class="fa fa-wrench"></i> <span>Setup / Onboarding Fee: <strong>' + CRM.esc(singleSplit.implementation_fee_formatted) + '</strong></span></div>' : '') +

                        // Limits Specs Grid
                        '<div class="grid grid-cols-2 gap-2.5 text-xs">' +
                            '<div class="bg-gray-50 rounded-xl p-2.5 border border-gray-100"><span class="text-gray-400 text-[10px] uppercase font-bold block">User Limit</span><span class="font-bold text-gray-800 mt-0.5 block">' + CRM.esc(singleSplit.user_limit || 'Unlimited') + '</span></div>' +
                            '<div class="bg-gray-50 rounded-xl p-2.5 border border-gray-100"><span class="text-gray-400 text-[10px] uppercase font-bold block">Storage</span><span class="font-bold text-gray-800 mt-0.5 block">' + CRM.esc(singleSplit.storage_limit || 'Standard') + '</span></div>' +
                            '<div class="bg-gray-50 rounded-xl p-2.5 border border-gray-100"><span class="text-gray-400 text-[10px] uppercase font-bold block">Support</span><span class="font-bold text-gray-800 mt-0.5 block">' + CRM.esc(singleSplit.support_type || 'Standard') + '</span></div>' +
                            '<div class="bg-gray-50 rounded-xl p-2.5 border border-gray-100"><span class="text-gray-400 text-[10px] uppercase font-bold block">Reports</span><span class="font-bold text-gray-800 mt-0.5 block">' + CRM.esc(singleSplit.reports_type || 'Standard') + '</span></div>' +
                        '</div>' +

                        // Features List
                        (singleSplit.features_array && singleSplit.features_array.length > 0 ? 
                            '<div class="pt-3 border-t border-gray-100">' +
                                '<div class="text-xs font-bold uppercase tracking-wider text-gray-700 mb-2 flex items-center gap-1.5"><i class="fa fa-check-square-o text-indigo-600"></i> Included Features</div>' +
                                '<ul class="space-y-2 text-xs text-gray-600">' +
                                    singleSplit.features_array.map(function(feat) {
                                        return '<li class="flex items-start gap-2.5 leading-relaxed"><i class="fa fa-check-circle text-emerald-500 text-sm mt-0.5 flex-shrink-0"></i> <span>' + CRM.esc(feat) + '</span></li>';
                                    }).join('') +
                                '</ul>' +
                            '</div>' : '') +
                    '</div>' +
                '</div>';
            } else {
                // Fallback single product card if no split configured
                modalHtml += '<div class="bg-white rounded-2xl border border-gray-200 p-5 shadow-xs space-y-3">' +
                    '<div class="flex items-center justify-between">' +
                        '<span class="text-xs font-bold text-gray-500 uppercase tracking-wider">Product Pricing</span>' +
                        '<span class="text-xl font-black text-gray-900">' + (p.price_formatted || '—') + '</span>' +
                    '</div>' +
                    (p.description ? '<p class="text-xs text-gray-600 leading-relaxed pt-2 border-t border-gray-100">' + CRM.esc(p.description) + '</p>' : '') +
                '</div>';
            }

            modalHtml += '</div>' + // End body container
                '</div>'; // End main wrapper

            Swal.fire({
                html: modalHtml,
                width: '560px',
                padding: '0',
                showConfirmButton: true,
                confirmButtonText: 'Close',
                confirmButtonColor: '#2563eb',
                showCloseButton: true,
                customClass: {
                    popup: 'rounded-3xl overflow-hidden p-0 border border-gray-200 shadow-2xl bg-white',
                    htmlContainer: 'm-0 p-0 text-left',
                    confirmButton: 'my-4 px-6 py-2 rounded-xl text-xs font-bold shadow-sm hover:bg-blue-700'
                }
            });
        }).fail(function() {
            Swal.fire({
                icon: 'error',
                title: 'Network Error',
                text: 'Failed to connect to the server. Please try again.'
            });
        });
    });
});

