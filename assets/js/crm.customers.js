/**
 * Customers module JS
 */
$(function() {
    if ($('#customers-table').length && !$.fn.DataTable.isDataTable('#customers-table')) {
        window.mainTable = $('#customers-table').DataTable({
            processing: true, serverSide: true,
            ajax: { url: BASE_URL + 'customers/datatable', data: function(d) { d.status_filter = window.currentStatusFilter || ''; } },
            columns: [{data:0},{data:1},{data:2},{data:3},{data:4},{data:5},{data:6},{data:7},{data:8},{data:9,orderable:false}],
            order: [[0, 'desc']]
        });
    }

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
            $('#customer-modal .modal-title').text('Edit Customer');
        } else {
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
});
