<!-- Page Header -->
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
    <div class="flex items-center gap-3">
        <div class="w-10 h-10 bg-red-100 rounded-xl flex items-center justify-center flex-shrink-0">
            <i class="fa fa-clock-o text-red-600 text-lg"></i>
        </div>
        <div>
            <h1 class="text-xl font-bold text-gray-800">Pending Approvals</h1>
            <nav class="text-xs text-gray-400 flex items-center gap-1 mt-0.5">
                <a href="<?= base_url('dashboard') ?>" class="hover:text-blue-600 transition-colors">Home</a>
                <i class="fa fa-angle-right text-[10px]"></i>
                <a href="<?= base_url('orders') ?>" class="hover:text-blue-600 transition-colors">Orders</a>
                <i class="fa fa-angle-right text-[10px]"></i>
                <span class="text-gray-600">Approval</span>
            </nav>
        </div>
    </div>
    <a href="<?= base_url('orders') ?>"
       class="inline-flex items-center gap-1.5 px-4 py-2 text-sm bg-white border border-gray-200 text-gray-700 font-medium rounded-xl hover:bg-gray-50 transition-colors self-start sm:self-auto">
        <i class="fa fa-arrow-left text-gray-500"></i> All Orders
    </a>
</div>

<!-- Info Banner -->
<div class="flex items-start gap-3 p-4 bg-amber-50 border border-amber-200 rounded-2xl mb-5">
    <div class="w-8 h-8 bg-amber-100 rounded-lg flex items-center justify-center flex-shrink-0 mt-0.5">
        <i class="fa fa-exclamation-triangle text-amber-600 text-sm"></i>
    </div>
    <div>
        <p class="text-sm font-semibold text-amber-800">Orders Awaiting Your Action</p>
        <p class="text-xs text-amber-600 mt-0.5">Review each order and approve or reject with a reason.</p>
    </div>
</div>

<!-- Approval Table -->
<div class="bg-white rounded-2xl shadow-sm border border-gray-100">
    <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
        <div>
            <h3 class="text-sm font-bold text-gray-800">Pending Orders</h3>
            <p class="text-xs text-gray-400 mt-0.5">Orders submitted for approval</p>
        </div>
        <span class="px-2.5 py-1 bg-red-50 text-red-600 text-xs font-semibold rounded-lg">
            <i class="fa fa-clock-o mr-1"></i> Needs Action
        </span>
    </div>
    <div class="p-4 overflow-x-auto">
        <table id="approval-table" class="w-full">
            <thead>
                <tr>
                    <th>#</th><th>Order #</th><th>Customer</th>
                    <th>Amount</th><th>Created By</th><th>Date</th><th>Actions</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>

<script>
var approvalTable = $('#approval-table').DataTable({
    processing: true,
    serverSide: true,
    ajax: { url: BASE_URL + 'orders/approval_dt' },
    columns: [
        {data: 0}, {data: 1}, {data: 2},
        {data: 3}, {data: 4}, {data: 5},
        {data: 6, orderable: false}
    ],
    order: [[0, 'asc']]
});

$(document).on('click', '.btn-approve-order', function() {
    var id = $(this).data('id');
    if (!confirm('Approve this order?')) return;
    $.post(BASE_URL + 'orders/approve', {id: id, [CI3_CSRF_NAME]: CI3_CSRF_HASH}, function(res) {
        if (res.status === 'success') {
            CRM.toast('success', res.message);
            approvalTable.ajax.reload(null, false);
        }
    });
});

$(document).on('click', '.btn-reject-order', function() {
    var id = $(this).data('id');
    var reason = prompt('Rejection reason:');
    if (!reason) return;
    $.post(BASE_URL + 'orders/reject', {id: id, reason: reason, [CI3_CSRF_NAME]: CI3_CSRF_HASH}, function(res) {
        if (res.status === 'success') {
            CRM.toast('success', res.message);
            approvalTable.ajax.reload(null, false);
        }
    });
});
</script>
