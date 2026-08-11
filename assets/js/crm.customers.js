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
                {data:5, orderable:false},
                {data:6, orderable:false},
                {data:7},
                {data:8},
                {data:9, orderable:false}
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

    function openCustomerModal(data) {
        var $f = $('#customer-form');
        $f[0].reset();
        CRM.clear_errors($f);
        $('#customer-id').val(data ? data.id : 0);
        CRM.init_plugins($('#customer-modal'));
        if (data) {
            $.each(['name','phone','email','gst_number','address','city','state','pincode','latitude','longitude','notes'], function(i, f) {
                $f.find('[name="'+f+'"]').val(data[f] || '');
            });
            if (data.assigned_to) $f.find('[name="assigned_to"]').val(data.assigned_to).trigger('change');
            
            if (data.product_ids) {
                var pids = data.product_ids.split(',');
                $('#customer-products').val(pids).trigger('change');
            } else {
                $('#customer-products').val([]).trigger('change');
            }
            
            $('#customer-modal .modal-title').text('Edit Customer');
        } else {
            $('#customer-products').val([]).trigger('change');
            $('#customer-modal .modal-title').text('Add Customer');
        }
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

    $(document).on('click', '.btn-view-customer', function() {
        var id = $(this).data('id');
        $.getJSON(BASE_URL + 'customers/get_details/' + id, function(res) {
            if (res.status === 'success') {
                var d = res.data;
                var c = d.customer;
                
                // Set Header details
                $('#view-customer-initial').text(c.name ? c.name.charAt(0).toUpperCase() : 'C');
                $('#view-customer-name').text(c.name || 'Customer Details');
                
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
                if (c.products && c.products.length) {
                    $.each(c.products, function(i, name) {
                        prodHtml += '<span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-blue-50 text-blue-700 border border-blue-100">' + CRM.esc(name) + '</span>';
                    });
                } else {
                    prodHtml = '<span class="text-gray-400 text-xs">—</span>';
                }
                $('#view-customer-products-container').html(prodHtml);
                
                // Contacts
                var contactsHtml = '';
                if (d.contacts && d.contacts.length) {
                    $.each(d.contacts, function(i, con) {
                        var isPri = con.is_primary == 1 ? '<span class="inline-flex items-center px-1.5 py-0.2 rounded text-[9px] font-bold bg-green-100 text-green-700 uppercase tracking-wide ml-1.5">Primary</span>' : '';
                        contactsHtml += '<div class="py-3 flex items-start gap-2.5">' +
                            '<div class="w-7 h-7 rounded-full bg-purple-100 text-purple-700 text-xs font-bold flex items-center justify-center flex-shrink-0 mt-0.5">' + (con.name ? con.name.charAt(0).toUpperCase() : 'C') + '</div>' +
                            '<div class="min-w-0 flex-1">' +
                                '<div class="font-semibold text-gray-800 text-xs">' + CRM.esc(con.name) + isPri + '</div>' +
                                (con.designation ? '<div class="text-[10px] text-gray-400">' + CRM.esc(con.designation) + '</div>' : '') +
                                (con.phone ? '<div class="text-[10px] text-gray-600 mt-0.5"><i class="fa fa-phone text-gray-300 mr-1"></i>' + CRM.esc(con.phone) + '</div>' : '') +
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
});
