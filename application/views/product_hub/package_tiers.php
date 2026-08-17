<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
    <div class="flex items-center gap-3">
        <div class="w-10 h-10 bg-indigo-100 rounded-xl flex items-center justify-center flex-shrink-0">
            <i class="fa fa-tags text-indigo-600 text-lg"></i>
        </div>
        <div>
            <h1 class="text-xl font-bold text-gray-800">Master Package Tiers</h1>
            <nav class="text-xs text-gray-400 flex items-center gap-1 mt-0.5">
                <a href="<?= base_url('dashboard') ?>" class="hover:text-blue-600 transition-colors">Home</a>
                <i class="fa fa-angle-right text-[10px]"></i>
                <span class="text-gray-600">Product Hub</span>
                <i class="fa fa-angle-right text-[10px]"></i>
                <span class="text-gray-600">Package Tiers</span>
            </nav>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 xl:grid-cols-12 gap-6 mb-6">
    <!-- Left Column: Add / Edit Tier Form -->
    <div class="xl:col-span-4">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 h-full flex flex-col">
            <div class="p-6 border-b border-gray-100 flex items-center justify-between">
                <div>
                    <h2 id="form-tier-title" class="text-lg font-bold text-gray-800">Add Package Tier</h2>
                    <p class="text-sm text-gray-500 mt-1">Configure global package tiers (e.g. Bronze, Gold).</p>
                </div>
                <button type="button" class="btn-cancel-tier px-3 py-1.5 text-xs font-semibold text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors flex items-center gap-1">
                    <i class="fa fa-refresh"></i> Reset
                </button>
            </div>
            
            <form id="tier-form" class="p-6 flex-1 flex flex-col space-y-4">
                <input type="hidden" name="<?= $csrf_name ?>" value="<?= $csrf_hash ?>" id="tier-csrf-token">
                <input type="hidden" name="id" id="tier-id" value="0">
                
                <div>
                    <label class="block text-xs font-semibold text-gray-800 mb-1.5">Tier Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" id="tier-name" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" placeholder="e.g. Platinum, Gold, Silver" required>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-800 mb-1.5">Slug (Auto-generated if empty)</label>
                    <input type="text" name="slug" id="tier-slug" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" placeholder="e.g. platinum">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-800 mb-1.5">Badge Color</label>
                        <select name="badge_color" id="tier-badge-color" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500">
                            <option value="amber">Amber / Bronze</option>
                            <option value="slate">Slate / Silver</option>
                            <option value="yellow">Yellow / Gold</option>
                            <option value="purple">Purple / Platinum</option>
                            <option value="blue">Blue / Enterprise</option>
                            <option value="emerald">Emerald / Green</option>
                            <option value="rose">Rose / Pink</option>
                            <option value="gray">Neutral Gray</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-800 mb-1.5">Icon (FontAwesome)</label>
                        <select name="icon" id="tier-icon" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500">
                            <option value="fa-shield">fa-shield (Bronze)</option>
                            <option value="fa-star-half-o">fa-star-half-o (Silver)</option>
                            <option value="fa-star">fa-star (Gold)</option>
                            <option value="fa-diamond">fa-diamond (Platinum)</option>
                            <option value="fa-building">fa-building (Enterprise)</option>
                            <option value="fa-crown">fa-crown</option>
                            <option value="fa-cube">fa-cube</option>
                            <option value="fa-rocket">fa-rocket</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-800 mb-1.5">Sort Order</label>
                        <input type="number" name="sort_order" id="tier-sort-order" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500" value="1">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-800 mb-1.5">Status</label>
                        <select name="status" id="tier-status" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-800 mb-1.5">Description</label>
                    <textarea name="description" id="tier-desc" rows="3" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 resize-none" placeholder="Brief details about who this tier is designed for..."></textarea>
                </div>

                <div class="flex justify-end gap-3 pt-6 border-t border-gray-100 mt-auto">
                    <button type="button" class="btn-cancel-tier px-5 py-2 text-gray-600 font-semibold bg-gray-50 hover:bg-gray-100 border border-gray-200 rounded-lg transition-colors text-sm">Cancel</button>
                    <button type="submit" id="btn-save-tier" class="px-5 py-2 bg-indigo-600 text-white font-semibold rounded-lg hover:bg-indigo-700 transition-colors shadow-sm text-sm flex items-center gap-2">
                        <i class="fa fa-save"></i> Save Tier
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Right Column: Package Tiers Master List -->
    <div class="xl:col-span-8">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 h-full flex flex-col">
            <div class="p-6 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h2 class="text-lg font-bold text-gray-800">Package Tiers Master List</h2>
                    <p class="text-sm text-gray-500 mt-1">Global tiers used across all products for price splitting.</p>
                </div>
            </div>

            <div class="p-0 overflow-x-auto flex-1">
                <table id="tiers-table" class="w-full text-left text-sm whitespace-nowrap">
                    <thead class="text-[11px] text-gray-500 bg-gray-50 border-b border-gray-200 uppercase">
                        <tr>
                            <th class="px-6 py-4 font-bold">Order</th>
                            <th class="px-6 py-4 font-bold">Tier Badge</th>
                            <th class="px-6 py-4 font-bold">Slug</th>
                            <th class="px-6 py-4 font-bold">Description</th>
                            <th class="px-6 py-4 font-bold">Status</th>
                            <th class="px-6 py-4 font-bold text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(function() {
    var table = $('#tiers-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: BASE_URL + 'product_hub/package_tiers_dt'
        },
        dom: '<"hidden"f>rt<"p-6 flex items-center justify-between border-t border-gray-100"i<"flex items-center gap-2"p>>',
        columns: [
            {data: 0, className: 'px-6 py-4 font-bold text-gray-500 align-middle text-sm'},
            {data: 1, className: 'px-6 py-4 align-middle'},
            {data: 2, className: 'px-6 py-4 text-gray-500 align-middle text-sm font-mono'},
            {data: 3, className: 'px-6 py-4 text-gray-600 align-middle text-sm'},
            {data: 4, className: 'px-6 py-4 align-middle'},
            {data: 5, orderable: false, className: 'px-6 py-4 align-middle text-center'}
        ],
        order: [[0, 'asc']]
    });

    function resetTierForm() {
        $('#tier-form')[0].reset();
        $('#tier-id').val(0);
        $('#form-tier-title').text('Add Package Tier');
        $('#btn-save-tier').html('<i class="fa fa-save"></i> Save Tier');
    }

    $('.btn-cancel-tier').click(function(e) {
        e.preventDefault();
        resetTierForm();
    });

    $('#tier-form').on('submit', function(e) {
        e.preventDefault();
        var $btn = $('#btn-save-tier');
        var ogText = $btn.html();
        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Saving...');

        $.ajax({
            url: BASE_URL + 'product_hub/save_package_tier',
            method: 'POST',
            data: $(this).serialize(),
            success: function(res) {
                if (res.status === 'success') {
                    Swal.fire('Success!', res.message, 'success');
                    resetTierForm();
                    table.ajax.reload(null, false);
                } else {
                    Swal.fire('Error!', res.message || 'Failed to save tier.', 'error');
                }
            },
            error: function(xhr) {
                Swal.fire('Error!', 'An error occurred.', 'error');
            },
            complete: function() {
                $btn.prop('disabled', false).html(ogText);
            }
        });
    });

    $(document).on('click', '.btn-edit-tier', function() {
        var id = $(this).data('id');
        $.get(BASE_URL + 'product_hub/get_package_tier/' + id, function(res) {
            if (res.status !== 'success') {
                Swal.fire('Error!', res.message || 'Failed to load tier.', 'error');
                return;
            }
            var d = res.data;
            $('#tier-id').val(d.id);
            $('#tier-name').val(d.name);
            $('#tier-slug').val(d.slug);
            $('#tier-badge-color').val(d.badge_color);
            $('#tier-icon').val(d.icon);
            $('#tier-sort-order').val(d.sort_order);
            $('#tier-status').val(d.status);
            $('#tier-desc').val(d.description || '');

            $('#form-tier-title').text('Edit Package Tier');
            $('#btn-save-tier').html('<i class="fa fa-save"></i> Update Tier');
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    });

    $(document).on('click', '.btn-tier-status', function() {
        var id = $(this).data('id');
        var action = $(this).data('action');
        let csrf_name = '<?= $csrf_name ?>';
        let csrf_hash = $('#tier-csrf-token').val();

        let confirmText = action === 'delete' ? 'Delete this Package Tier?' : 'Change status of this Tier?';

        Swal.fire({
            title: 'Are you sure?',
            text: confirmText,
            icon: action === 'delete' ? 'warning' : 'question',
            showCancelButton: true,
            confirmButtonColor: action === 'delete' ? '#ef4444' : '#4f46e5',
            confirmButtonText: 'Yes, proceed'
        }).then((result) => {
            if (result.isConfirmed) {
                let data = { id: id, action: action };
                data[csrf_name] = csrf_hash;
                $.post(BASE_URL + 'product_hub/package_tier_status', data, function(res) {
                    if (res.status === 'success') {
                        Swal.fire('Success!', res.message, 'success');
                        table.ajax.reload(null, false);
                    } else {
                        Swal.fire('Error!', res.message, 'error');
                    }
                });
            }
        });
    });
});
</script>
