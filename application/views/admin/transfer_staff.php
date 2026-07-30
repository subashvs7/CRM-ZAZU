<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
    <div class="flex items-center gap-3">
        <div class="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center flex-shrink-0">
            <i class="fa fa-exchange text-blue-600 text-lg"></i>
        </div>
        <div>
            <h1 class="text-xl font-bold text-gray-800">Transfer Staff</h1>
            <nav class="text-xs text-gray-400 flex items-center gap-1 mt-0.5">
                <a href="<?= base_url('dashboard') ?>" class="hover:text-blue-600 transition-colors">Home</a>
                <i class="fa fa-angle-right text-[10px]"></i>
                <span class="text-gray-600">Transfer Staff</span>
            </nav>
        </div>
    </div>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-gray-100 mb-6">
    <div class="px-6 py-4 border-b border-gray-100 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h3 class="text-sm font-bold text-gray-800">Bulk Customer Transfer</h3>
            <p class="text-xs text-gray-400 mt-0.5">Select customers from the table below and assign them to a new field staff</p>
        </div>
        
        <form id="transfer-staff-form" method="POST" class="flex flex-col sm:flex-row items-end sm:items-center gap-3">
            <input type="hidden" name="<?= $csrf_name ?>" value="<?= $csrf_hash ?>">
            <div class="w-full sm:w-64">
                <select name="staff_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 select2" required>
                    <option value="">-- Select New Field Staff --</option>
                    <?php foreach($staff as $s): ?>
                        <option value="<?= $s['id'] ?>"><?= esc_html($s['name']) ?> <?= !empty($s['team_name']) ? '('.esc_html($s['team_name']).')' : '' ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="button" id="btn-transfer" class="px-5 py-2 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition-colors shadow-sm flex items-center justify-center gap-2 text-sm whitespace-nowrap disabled:opacity-50" disabled>
                <i class="fa fa-exchange"></i> Transfer Selected (<span id="selected-count">0</span>)
            </button>
        </form>
    </div>
    
    <div class="p-4 overflow-x-auto">
        <table id="customers-table" class="w-full text-left text-sm">
            <thead class="text-xs text-gray-500 bg-gray-50 uppercase border-b border-gray-200">
                <tr>
                    <th class="px-4 py-3 w-10">
                        <input type="checkbox" id="check-all" class="w-4 h-4 text-blue-600 bg-white border-gray-300 rounded focus:ring-blue-500">
                    </th>
                    <th class="px-4 py-3">#</th>
                    <th class="px-4 py-3">Customer</th>
                    <th class="px-4 py-3">Phone</th>
                    <th class="px-4 py-3">City</th>
                    <th class="px-4 py-3">Current Staff</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>

<script>
$(function() {
    if ($.fn.select2) {
        $('.select2').select2({ width: '100%' });
    }

    var table = $('#customers-table').DataTable({
        processing: true, 
        serverSide: true,
        ajax: { 
            url: BASE_URL + 'admin/transfer_staff_dt'
        },
        columns: [
            {data: 0, orderable: false, className: 'px-4 py-3'},
            {data: 1, className: 'px-4 py-3 text-gray-500'},
            {data: 2, className: 'px-4 py-3 font-medium text-gray-900'},
            {data: 3, className: 'px-4 py-3'},
            {data: 4, className: 'px-4 py-3'},
            {data: 5, className: 'px-4 py-3 text-gray-500'}
        ],
        order: [[1, 'desc']],
        drawCallback: function() {
            updateSelectedCount();
            $('#check-all').prop('checked', false);
        }
    });

    $('#check-all').on('click', function() {
        var isChecked = $(this).is(':checked');
        $('.customer-checkbox').prop('checked', isChecked);
        updateSelectedCount();
    });

    $('#customers-table').on('change', '.customer-checkbox', function() {
        if (!$(this).is(':checked')) {
            $('#check-all').prop('checked', false);
        } else {
            var allChecked = $('.customer-checkbox').length === $('.customer-checkbox:checked').length;
            $('#check-all').prop('checked', allChecked);
        }
        updateSelectedCount();
    });

    function updateSelectedCount() {
        var count = $('.customer-checkbox:checked').length;
        $('#selected-count').text(count);
        if (count > 0) {
            $('#btn-transfer').prop('disabled', false);
        } else {
            $('#btn-transfer').prop('disabled', true);
        }
    }

    $('#btn-transfer').click(function(e) {
        e.preventDefault();
        
        var selectedCustomers = [];
        $('.customer-checkbox:checked').each(function() {
            selectedCustomers.push($(this).val());
        });
        
        var staffId = $('[name="staff_id"]').val();
        
        if (selectedCustomers.length === 0) {
            CRM.toast('error', 'Please select at least one customer.');
            return;
        }
        
        if (!staffId) {
            CRM.toast('error', 'Please select a new field staff.');
            return;
        }

        if (!confirm('Are you sure you want to transfer ' + selectedCustomers.length + ' customer(s) to the selected staff?')) {
            return;
        }

        var $btn = $(this);
        CRM.btn_loading($btn);
        
        var data = {
            customer_ids: selectedCustomers,
            staff_id: staffId,
            [CI3_CSRF_NAME]: CI3_CSRF_HASH
        };
        
        $.ajax({
            url: BASE_URL + 'admin/process_transfer_staff',
            method: 'POST',
            data: data,
            success: function(res) {
                if (res.status === 'success') {
                    CRM.toast('success', res.message);
                    
                    if ($.fn.select2) {
                        $('[name="staff_id"]').val('').trigger('change');
                    } else {
                        $('[name="staff_id"]').val('');
                    }
                    
                    $('#check-all').prop('checked', false);
                    updateSelectedCount();
                    
                    table.ajax.reload(null, false);
                } else {
                    CRM.toast('error', res.message);
                }
            },
            error: function() {
                CRM.toast('error', 'An error occurred during transfer.');
            },
            complete: function() {
                CRM.btn_reset($btn);
                $('#btn-transfer').html('<i class="fa fa-exchange"></i> Transfer Selected (<span id="selected-count">0</span>)');
            }
        });
    });
});
</script>
