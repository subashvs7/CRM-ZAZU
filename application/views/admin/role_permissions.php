<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
    <div class="flex items-center gap-3">
        <div class="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center flex-shrink-0">
            <i class="fa fa-key text-blue-600 text-lg"></i>
        </div>
        <div>
            <h1 class="text-xl font-bold text-gray-800">Role Permissions</h1>
            <nav class="text-xs text-gray-400 flex items-center gap-1 mt-0.5">
                <a href="<?= base_url('dashboard') ?>" class="hover:text-blue-600 transition-colors">Home</a>
                <i class="fa fa-angle-right text-[10px]"></i>
                <span class="text-gray-600">Role Permissions</span>
            </nav>
        </div>
    </div>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-gray-100">
    <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
        <div>
            <h3 class="text-sm font-bold text-gray-800">Role Directory</h3>
            <p class="text-xs text-gray-400 mt-0.5">Manage menu and module access permissions for roles</p>
        </div>
        <span class="px-2.5 py-1 bg-blue-50 text-blue-600 text-xs font-semibold rounded-lg">
            <i class="fa fa-shield mr-1"></i> Role-Based Access Control (RBAC)
        </span>
    </div>
    <div class="p-4 overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="border-b border-gray-100 text-xs font-semibold text-gray-500 uppercase">
                    <th class="py-3.5 px-4 w-16">#</th>
                    <th class="py-3.5 px-4 w-48">Role</th>
                    <th class="py-3.5 px-4">Allowed Modules</th>
                    <th class="py-3.5 px-4 w-36 text-right">Actions</th>
                </tr>
            </thead>
            <tbody id="roles-list-body" class="divide-y divide-gray-50 text-sm">
                <tr>
                    <td colspan="4" class="py-8 text-center text-gray-400">
                        <i class="fa fa-spinner fa-spin mr-1.5"></i> Loading permissions...
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<!-- Edit Permissions Modal -->
<div class="modal fade" id="permissions-modal" tabindex="-1">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <div class="modal-content rounded-2xl overflow-hidden border-0 shadow-2xl">
            <div class="modal-header bg-slate-900 text-white border-b-0 px-6 py-4 flex items-center justify-between">
                <h4 class="modal-title font-bold text-lg flex items-center gap-2">
                    <i class="fa fa-key text-blue-400"></i> Edit Permissions: <span id="modal-role-name">Role</span>
                </h4>
                <button type="button" class="text-white/80 hover:text-white text-xl font-bold focus:outline-none" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body p-6 bg-slate-50 max-h-[65vh] overflow-y-auto">
                <form id="permissions-form" class="space-y-4">
                    <input type="hidden" name="<?= $csrf_name ?>" value="<?= $csrf_hash ?>">
                    <input type="hidden" name="role" id="modal-role-key" value="">
                    
                    <!-- Module Permission Cards -->
                    <div id="modules-list" class="space-y-3">
                        <!-- Filled by JavaScript -->
                    </div>
                </form>
            </div>
            <div class="modal-footer bg-white border-t border-gray-100 px-6 py-4 flex justify-end gap-3">
                <button class="px-4 py-2 border border-gray-200 text-gray-700 font-medium rounded-xl hover:bg-gray-50 transition-colors text-sm" data-dismiss="modal">
                    Cancel
                </button>
                <button class="px-5 py-2 bg-blue-600 text-white font-semibold rounded-xl hover:bg-blue-700 transition-colors shadow-sm text-sm" id="btn-save-permissions">
                    <i class="fa fa-save mr-1.5"></i> Save Changes
                </button>
            </div>
        </div>
    </div>
</div>

<script>
$(function() {
    var modules = [
        { key: 'dashboard', label: 'Dashboard', desc: 'View main system metrics, active tasks, and performance summaries.' },
        { key: 'customers', label: 'Customers', desc: 'Manage customer accounts, contact persons, and map views.' },
        { key: 'leads',     label: 'Leads',     desc: 'Track sales pipeline, activities, and opportunities.' },
        { key: 'orders',    label: 'Orders',    desc: 'Create, approve, and track client orders.' },
        { key: 'visits',    label: 'Visits',    desc: 'Manage field visits, check-ins, check-outs, and logs.' },
        { key: 'tracking/live', label: 'Live Tracking', desc: 'Monitor live GPS locations of field staff.' },
        { key: 'geofence',  label: 'Geofence',  desc: 'Manage office, customer, and restricted geofences.' },
        { key: 'attendance', label: 'Attendance', desc: 'Track daily punch-ins, selfies, and working hours.' },
        { key: 'shifts',    label: 'Shifts',    desc: 'Configure shifts and assign them to employees.' },
        { key: 'leave',     label: 'Leave',     desc: 'Manage leave requests, balances, and holidays.' },
        { key: 'selfie/log', label: 'Selfie Verify', desc: 'Audit selfies uploaded for check-ins or attendance.' },
        { key: 'reports',   label: 'Reports',   desc: 'View deep-dive reports and analytics.' },
        { key: 'admin',     label: 'Admin Settings', desc: 'Full system settings, users, teams, and role permissions.' }
    ];

    var roleLabels = {
        'admin': { name: 'Admin', char: 'A', color: 'bg-red-500 text-white' },
        'manager': { name: 'Manager', char: 'M', color: 'bg-amber-500 text-white' },
        'field_staff': { name: 'Field Staff', char: 'S', color: 'bg-blue-500 text-white' }
    };

    var currentPermissions = {};

    function loadRolePermissions() {
        $.getJSON(BASE_URL + 'admin/role_permissions/get', function(res) {
            if (res.status === 'success') {
                currentPermissions = res.data;
                renderRolesTable();
            } else {
                CRM.toast('error', 'Failed to load permissions.');
            }
        });
    }

    function renderRolesTable() {
        var html = '';
        var idx = 1;
        $.each(roleLabels, function(roleKey, roleMeta) {
            var allowedList = currentPermissions[roleKey] || [];
            
            // Generate tags for allowed modules
            var tagsHtml = '';
            if (allowedList.length === 0) {
                tagsHtml = '<span class="text-xs text-gray-400 italic">No access configured</span>';
            } else {
                tagsHtml = '<div class="flex flex-wrap gap-1.5">';
                $.each(modules, function(i, mod) {
                    if (allowedList.indexOf(mod.key) !== -1) {
                        tagsHtml += '<span class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-semibold rounded-lg bg-green-50 text-green-700 border border-green-100"><i class="fa fa-check text-[10px]"></i> ' + mod.label + '</span>';
                    }
                });
                tagsHtml += '</div>';
            }

            html += '<tr class="hover:bg-slate-50/50 transition-colors">' +
                '<td class="py-4 px-4 text-gray-400 font-semibold">' + idx++ + '</td>' +
                '<td class="py-4 px-4">' +
                '   <div class="flex items-center gap-3">' +
                '       <div class="w-8 h-8 rounded-full ' + roleMeta.color + ' flex items-center justify-center font-bold text-xs shadow-sm">' + roleMeta.char + '</div>' +
                '       <span class="font-bold text-gray-800">' + roleMeta.name + '</span>' +
                '   </div>' +
                '</td>' +
                '<td class="py-4 px-4">' + tagsHtml + '</td>' +
                '<td class="py-4 px-4 text-right">' +
                '   <button class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs bg-blue-600 text-white font-semibold rounded-xl hover:bg-blue-700 transition-colors shadow-sm btn-edit-permissions" data-role="' + roleKey + '">' +
                '       <i class="fa fa-key"></i> Permissions' +
                '   </button>' +
                '</td>' +
                '</tr>';
        });
        $('#roles-list-body').html(html);
    }

    $(document).on('click', '.btn-edit-permissions', function() {
        var role = $(this).data('role');
        var meta = roleLabels[role];
        if (!meta) return;

        $('#modal-role-key').val(role);
        $('#modal-role-name').text(meta.name);

        var allowed = currentPermissions[role] || [];
        var html = '';

        $.each(modules, function(i, mod) {
            var isChecked = allowed.indexOf(mod.key) !== -1;
            html += '<div class="flex items-center justify-between p-4 bg-white rounded-xl border border-gray-100 shadow-sm gap-4 transition-all hover:border-gray-200">' +
                '   <div class="min-w-0 flex-1">' +
                '       <h5 class="text-sm font-bold text-gray-800">' + mod.label + '</h5>' +
                '       <p class="text-xs text-gray-400 mt-0.5 leading-normal">' + mod.desc + '</p>' +
                '   </div>' +
                '   <label class="relative inline-flex items-center cursor-pointer flex-shrink-0">' +
                '       <input type="checkbox" name="modules[]" value="' + mod.key + '" class="sr-only peer" ' + (isChecked ? 'checked' : '') + '>' +
                '       <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[\'\'] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>' +
                '   </label>' +
                '</div>';
        });

        $('#modules-list').html(html);
        $('#permissions-modal').modal('show');
    });

    $('#btn-save-permissions').click(function() {
        var $btn = $(this);
        CRM.btn_loading($btn);
        
        var formData = $('#permissions-form').serialize();
        $.ajax({
            url: BASE_URL + 'admin/role_permissions/save',
            method: 'POST',
            data: formData,
            success: function(res) {
                if (res.status === 'success') {
                    CRM.toast('success', res.message);
                    $('#permissions-modal').modal('hide');
                    loadRolePermissions();
                } else {
                    CRM.toast('error', res.message);
                }
            },
            error: function() {
                CRM.toast('error', 'An error occurred while saving.');
            },
            complete: function() {
                CRM.btn_reset($btn);
            }
        });
    });

    loadRolePermissions();
});
</script>
