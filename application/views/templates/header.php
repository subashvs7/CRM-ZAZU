<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= esc_html($page_title ?? 'Field CRM') ?> — Field CRM</title>

<!-- Tailwind CSS (Play CDN) -->
<script src="https://cdn.tailwindcss.com"></script>
<script>
tailwind.config = {
    theme: { extend: {} }
}
</script>

<!-- Font Awesome -->
<link rel="stylesheet" href="<?= base_url('assets/vendor/bower_components/font-awesome/css/font-awesome.min.css') ?>">
<!-- Select2 -->
<link rel="stylesheet" href="<?= base_url('assets/vendor/bower_components/select2/dist/css/select2.min.css') ?>">
<!-- Toastr -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
<!-- Bootstrap Datepicker CSS -->
<link rel="stylesheet" href="<?= base_url('assets/vendor/bower_components/bootstrap-datepicker/dist/css/bootstrap-datepicker.min.css') ?>">
<!-- Custom CRM Styles -->
<link rel="stylesheet" href="<?= base_url('assets/css/crm.custom.css') ?>">

<!-- jQuery + Bootstrap JS (modals only) -->
<script src="<?= base_url('assets/vendor/bower_components/jquery/dist/jquery.min.js') ?>"></script>
<script src="<?= base_url('assets/vendor/bower_components/bootstrap/dist/js/bootstrap.min.js') ?>"></script>
<script src="<?= base_url('assets/vendor/bower_components/datatables.net/js/jquery.dataTables.min.js') ?>"></script>
<script src="<?= base_url('assets/vendor/bower_components/select2/dist/js/select2.full.min.js') ?>"></script>
<script src="<?= base_url('assets/vendor/bower_components/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js') ?>"></script>
<script src="<?= base_url('assets/vendor/bower_components/moment/moment.js') ?>"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

<!-- Global JS Variables -->
<script>
var BASE_URL        = '<?= base_url() ?>';
var CI3_CSRF_NAME   = '<?= $csrf_name ?>';
var CI3_CSRF_HASH   = '<?= $csrf_hash ?>';
var CURRENT_USER_ID = <?= (int) $current_user_id ?>;
var CURRENT_ROLE    = '<?= esc_html($current_role) ?>';
</script>
<script src="<?= base_url('assets/js/crm.core.js') ?>"></script>
</head>
<body class="bg-slate-100">

<!-- Mobile sidebar overlay -->
<div id="sidebar-overlay" onclick="closeSidebar()"
     class="fixed inset-0 bg-black/60 backdrop-blur-sm hidden lg:hidden"
     style="z-index:9998"></div>

<!-- ═══════════════════════ SIDEBAR ═══════════════════════ -->
<aside id="sidebar"
       class="fixed top-0 left-0 h-full flex flex-col bg-slate-900 select-none"
       style="width:260px; z-index:9999; transition:transform .28s cubic-bezier(.4,0,.2,1), width .28s cubic-bezier(.4,0,.2,1);">

    <!-- Logo -->
    <div class="flex items-center gap-2.5 px-5 py-4 border-b border-slate-700/60 flex-shrink-0 min-h-[60px]">
        <div class="w-8 h-8 rounded-lg bg-blue-600 flex items-center justify-center flex-shrink-0">
            <i class="fa fa-map-marker text-white text-sm"></i>
        </div>
        <a href="<?= base_url('dashboard') ?>" class="sidebar-text font-bold text-white text-lg leading-none tracking-tight">
            Field<span class="text-blue-400">CRM</span>
        </a>
    </div>

    <!-- User Panel -->
    <?php
    $photo = !empty($current_user['profile_photo'])
        ? base_url('uploads/' . $current_user['profile_photo'])
        : base_url('assets/vendor/adminlte/img/avatar.png');
    $CI   =& get_instance();
    $seg1 = $CI->uri->segment(1);
    $seg2 = $CI->uri->segment(2);
    ?>
    <div class="flex items-center gap-3 px-4 py-3 mx-3 mt-3 mb-1 rounded-xl bg-slate-800/60 flex-shrink-0">
        <img src="<?= $photo ?>" alt=""
             class="w-9 h-9 rounded-full border-2 border-slate-600 object-cover flex-shrink-0">
        <div class="sidebar-text min-w-0">
            <p class="text-white text-[13px] font-semibold truncate leading-tight">
                <?= esc_html($current_user['name'] ?? '') ?>
            </p>
            <p class="text-slate-400 text-[11px] flex items-center gap-1.5 mt-0.5">
                <span class="w-1.5 h-1.5 rounded-full bg-green-400 flex-shrink-0 animate-pulse"></span>
                <?= esc_html(ucfirst(str_replace('_', ' ', $current_role))) ?>
            </p>
        </div>
    </div>

    <!-- Navigation -->
    <nav class="flex-1 px-3 py-2 overflow-y-auto overflow-x-hidden space-y-0.5 sidebar-nav">
        <?php
        function tw_active($seg) {
            $CI =& get_instance(); return $CI->uri->segment(1) === $seg;
        }
        $lnk = 'flex items-center gap-3 px-3 py-2.5 rounded-lg text-slate-400 hover:bg-slate-700/70 hover:text-white transition-all duration-150 text-[13px] font-medium group cursor-pointer w-full text-left';
        $lnk_on = 'flex items-center gap-3 px-3 py-2.5 rounded-lg bg-blue-600 text-white text-[13px] font-semibold w-full text-left';
        $ic = 'w-5 text-center flex-shrink-0 text-[14px]';
        ?>

        <?php if (has_module_access('dashboard')): ?>
        <p class="px-3 pt-2 pb-1 text-[10px] font-bold text-slate-500 uppercase tracking-widest sidebar-text">Main</p>
        <a href="<?= base_url('dashboard') ?>" class="<?= tw_active('dashboard') ? $lnk_on : $lnk ?>">
            <i class="fa fa-home <?= $ic ?>"></i>
            <span class="sidebar-text">Dashboard</span>
        </a>
        <?php endif; ?>

        <?php if (has_module_access('customers') || has_module_access('leads') || has_module_access('orders')): ?>
        <p class="px-3 pt-3 pb-1 text-[10px] font-bold text-slate-500 uppercase tracking-widest sidebar-text">CRM</p>
        <?php if (has_module_access('customers')): ?>
        <a href="<?= base_url('customers') ?>" class="<?= tw_active('customers') ? $lnk_on : $lnk ?>">
            <i class="fa fa-building-o <?= $ic ?>"></i>
            <span class="sidebar-text">Customers</span>
        </a>
        <?php endif; ?>
        <?php if (has_module_access('leads')): ?>
        <a href="<?= base_url('leads') ?>" class="<?= tw_active('leads') ? $lnk_on : $lnk ?>">
            <i class="fa fa-filter <?= $ic ?>"></i>
            <span class="sidebar-text">Leads</span>
        </a>
        <?php endif; ?>
        <?php if (has_module_access('orders')): ?>
        <a href="<?= base_url('orders') ?>" class="<?= tw_active('orders') ? $lnk_on : $lnk ?>">
            <i class="fa fa-shopping-cart <?= $ic ?>"></i>
            <span class="sidebar-text flex-1">Orders</span>
            <?php if ($is_manager): ?>
            <span id="pending-orders-badge" class="hidden text-[10px] bg-amber-500 text-white px-1.5 py-0.5 rounded-full font-bold sidebar-text">!</span>
            <?php endif; ?>
        </a>
        <?php endif; ?>
        <?php endif; ?>

        <?php if (has_module_access('visits') || has_module_access('tracking/live') || has_module_access('geofence')): ?>
        <p class="px-3 pt-3 pb-1 text-[10px] font-bold text-slate-500 uppercase tracking-widest sidebar-text">Field Ops</p>
        <?php if (has_module_access('visits')): ?>
        <a href="<?= base_url('visits') ?>" class="<?= tw_active('visits') ? $lnk_on : $lnk ?>">
            <i class="fa fa-map-signs <?= $ic ?>"></i>
            <span class="sidebar-text">Visits</span>
        </a>
        <?php endif; ?>
        <?php if (has_module_access('tracking/live')): ?>
        <a href="<?= base_url('tracking/live') ?>" class="<?= tw_active('tracking') ? $lnk_on : $lnk ?>">
            <i class="fa fa-map-marker <?= $ic ?>"></i>
            <span class="sidebar-text">Live Tracking</span>
        </a>
        <?php endif; ?>
        <?php if (has_module_access('geofence')): ?>
        <a href="<?= base_url('geofence') ?>" class="<?= tw_active('geofence') ? $lnk_on : $lnk ?>">
            <i class="fa fa-circle-o <?= $ic ?>"></i>
            <span class="sidebar-text">Geofence</span>
        </a>
        <?php endif; ?>
        <?php endif; ?>

        <?php if (has_module_access('attendance') || has_module_access('shifts') || has_module_access('leave') || has_module_access('selfie/log')): ?>
        <p class="px-3 pt-3 pb-1 text-[10px] font-bold text-slate-500 uppercase tracking-widest sidebar-text">HR</p>
        <?php if (has_module_access('attendance')): ?>
        <a href="<?= base_url('attendance') ?>" class="<?= tw_active('attendance') ? $lnk_on : $lnk ?>">
            <i class="fa fa-clock-o <?= $ic ?>"></i>
            <span class="sidebar-text">Attendance</span>
        </a>
        <?php endif; ?>
        <?php if (has_module_access('shifts')): ?>
        <a href="<?= base_url('shifts') ?>" class="<?= tw_active('shifts') ? $lnk_on : $lnk ?>">
            <i class="fa fa-calendar <?= $ic ?>"></i>
            <span class="sidebar-text">Shifts</span>
        </a>
        <?php endif; ?>
        <?php if (has_module_access('leave')): ?>
        <a href="<?= base_url('leave') ?>" class="<?= tw_active('leave') ? $lnk_on : $lnk ?>">
            <i class="fa fa-plane <?= $ic ?>"></i>
            <span class="sidebar-text">Leave</span>
        </a>
        <?php endif; ?>
        <?php if (has_module_access('selfie/log')): ?>
        <a href="<?= base_url('selfie/log') ?>" class="<?= tw_active('selfie') ? $lnk_on : $lnk ?>">
            <i class="fa fa-camera <?= $ic ?>"></i>
            <span class="sidebar-text">Selfie Verify</span>
        </a>
        <?php endif; ?>
        <?php endif; ?>

        <?php if (has_module_access('reports')): ?>
        <!-- Reports submenu -->
        <p class="px-3 pt-3 pb-1 text-[10px] font-bold text-slate-500 uppercase tracking-widest sidebar-text">Analytics</p>
        <button type="button" onclick="toggleSubmenu('reports-sub',this)"
                class="<?= $lnk ?> sidebar-text">
            <i class="fa fa-bar-chart <?= $ic ?>"></i>
            <span class="flex-1 sidebar-text text-left">Reports</span>
            <i class="fa fa-angle-right text-xs sub-arrow sidebar-text transition-transform duration-200 <?= $seg1==='reports'?'rotate-90':'' ?>"></i>
        </button>
        <div id="reports-sub" class="crm-submenu <?= $seg1==='reports'?'open':'' ?>">
            <a href="<?= base_url('reports/visits') ?>"          class="sub-link sidebar-text<?= $seg2==='visits'?' active':'' ?>"><i class="fa fa-map w-4 text-center"></i> Visit Reports</a>
            <a href="<?= base_url('reports/lead_conversion') ?>" class="sub-link sidebar-text<?= $seg2==='lead_conversion'?' active':'' ?>"><i class="fa fa-funnel w-4 text-center"></i> Lead Conversion</a>
            <a href="<?= base_url('reports/orders') ?>"          class="sub-link sidebar-text<?= $seg2==='orders'?' active':'' ?>"><i class="fa fa-shopping-bag w-4 text-center"></i> Orders</a>
            <a href="<?= base_url('reports/staff_sales') ?>"     class="sub-link sidebar-text<?= $seg2==='staff_sales'?' active':'' ?>"><i class="fa fa-trophy w-4 text-center"></i> Staff Sales</a>
            <a href="<?= base_url('reports/attendance') ?>"      class="sub-link sidebar-text<?= $seg2==='attendance'?' active':'' ?>"><i class="fa fa-clock-o w-4 text-center"></i> Attendance</a>
            <a href="<?= base_url('reports/punctuality') ?>"     class="sub-link sidebar-text<?= $seg2==='punctuality'?' active':'' ?>"><i class="fa fa-check-circle w-4 text-center"></i> Punctuality</a>
            <a href="<?= base_url('reports/leave_util') ?>"      class="sub-link sidebar-text<?= $seg2==='leave_util'?' active':'' ?>"><i class="fa fa-plane w-4 text-center"></i> Leave Util.</a>
            <a href="<?= base_url('reports/coverage') ?>"        class="sub-link sidebar-text<?= $seg2==='coverage'?' active':'' ?>"><i class="fa fa-map-o w-4 text-center"></i> Coverage Map</a>
        </div>
        <?php endif; ?>

        <?php if (has_module_access('admin')): ?>
        <!-- Admin submenu -->
        <p class="px-3 pt-3 pb-1 text-[10px] font-bold text-slate-500 uppercase tracking-widest sidebar-text">System</p>
        <button type="button" onclick="toggleSubmenu('admin-sub',this)"
                class="<?= $lnk ?> sidebar-text">
            <i class="fa fa-cogs <?= $ic ?>"></i>
            <span class="flex-1 sidebar-text text-left">Admin</span>
            <i class="fa fa-angle-right text-xs sub-arrow sidebar-text transition-transform duration-200 <?= $seg1==='admin'?'rotate-90':'' ?>"></i>
        </button>
        <div id="admin-sub" class="crm-submenu <?= $seg1==='admin'?'open':'' ?>">
            <a href="<?= base_url('admin/users') ?>"           class="sub-link sidebar-text<?= $seg2==='users'?' active':'' ?>"><i class="fa fa-users w-4 text-center"></i> Users</a>
            <a href="<?= base_url('admin/teams') ?>"           class="sub-link sidebar-text<?= $seg2==='teams'?' active':'' ?>"><i class="fa fa-group w-4 text-center"></i> Teams</a>
            <a href="<?= base_url('admin/products') ?>"        class="sub-link sidebar-text<?= $seg2==='products'?' active':'' ?>"><i class="fa fa-cubes w-4 text-center"></i> Products</a>
            <a href="<?= base_url('admin/notif_templates') ?>" class="sub-link sidebar-text<?= $seg2==='notif_templates'?' active':'' ?>"><i class="fa fa-envelope w-4 text-center"></i> Notif Templates</a>
            <a href="<?= base_url('admin/settings') ?>"        class="sub-link sidebar-text<?= $seg2==='settings'?' active':'' ?>"><i class="fa fa-sliders w-4 text-center"></i> Settings</a>
            <a href="<?= base_url('admin/role_permissions') ?>" class="sub-link sidebar-text<?= $seg2==='role_permissions'?' active':'' ?>"><i class="fa fa-key w-4 text-center"></i> Role Permissions</a>
        </div>
        <?php endif; ?>
    </nav>

    <!-- Sidebar footer -->
    <div class="px-3 py-3 border-t border-slate-700/60 flex-shrink-0">
        <a href="<?= base_url('auth/logout') ?>"
           class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-slate-400 hover:bg-red-600/20 hover:text-red-400 transition-all duration-150 text-[13px] font-medium">
            <i class="fa fa-sign-out w-5 text-center text-[14px]"></i>
            <span class="sidebar-text">Sign Out</span>
        </a>
    </div>
</aside>

<!-- ═══════════════════════ MAIN AREA ═══════════════════════ -->
<div id="main" class="flex flex-col min-h-screen" style="margin-left:260px; transition:margin-left .28s cubic-bezier(.4,0,.2,1);">

<!-- Top Navbar -->
<header id="topbar"
        class="bg-white border-b border-gray-200 h-[60px] flex items-center px-4 sticky top-0 gap-3"
        style="z-index:500;">

    <!-- Sidebar toggle -->
    <button onclick="toggleSidebar()" title="Toggle menu"
            class="p-2 rounded-lg text-gray-500 hover:bg-gray-100 hover:text-gray-700 transition-colors flex-shrink-0">
        <i class="fa fa-bars text-base"></i>
    </button>

    <!-- Page breadcrumb -->
    <div class="flex-1 min-w-0 hidden sm:block">
        <p class="text-sm font-semibold text-gray-700 truncate"><?= esc_html($page_title ?? '') ?></p>
    </div>

    <!-- GPS indicator (field_staff only) -->
    <?php if ($current_role === 'field_staff'): ?>
    <div id="gps-nav-item" class="flex items-center gap-1.5 text-xs text-gray-400 px-2 py-1 rounded-lg border border-gray-200" title="GPS Auto-Tracking">
        <span id="gps-status-dot" class="w-2 h-2 rounded-full bg-gray-300 flex-shrink-0"></span>
        <span id="gps-status-label" class="hidden sm:inline font-medium">GPS</span>
    </div>
    <?php endif; ?>

    <!-- Notifications bell -->
    <div class="relative" id="notif-wrapper">
        <button onclick="toggleNotifDropdown()" title="Notifications"
                class="relative p-2 rounded-lg text-gray-500 hover:bg-gray-100 hover:text-gray-700 transition-colors">
            <i class="fa fa-bell-o text-base"></i>
            <span id="notif-count"
                  class="hidden absolute -top-0.5 -right-0.5 min-w-[18px] h-[18px] bg-red-500 text-white text-[10px] font-bold rounded-full flex items-center justify-center px-0.5 leading-none">0</span>
        </button>
        <!-- Dropdown -->
        <div id="notif-dropdown"
             class="hidden absolute right-0 top-full mt-2 w-80 bg-white border border-gray-200 rounded-xl shadow-xl overflow-hidden"
             style="z-index:600;">
            <div class="px-4 py-3 bg-gradient-to-r from-blue-600 to-blue-700 flex items-center justify-between">
                <span class="text-white text-xs font-bold uppercase tracking-wider">Notifications</span>
                <a href="<?= base_url('notifications') ?>" class="text-blue-200 text-xs hover:text-white">View all</a>
            </div>
            <ul id="notif-list" class="max-h-72 overflow-y-auto divide-y divide-gray-100">
                <li class="px-4 py-3 text-sm text-gray-400 text-center">
                    <i class="fa fa-spinner fa-spin mr-1"></i> Loading...
                </li>
            </ul>
        </div>
    </div>

    <!-- User menu -->
    <div class="relative" id="user-menu-wrapper">
        <?php
        $nav_photo = !empty($current_user['profile_photo'])
            ? base_url('uploads/' . $current_user['profile_photo'])
            : base_url('assets/vendor/adminlte/img/avatar.png');
        ?>
        <button onclick="toggleUserMenu()"
                class="flex items-center gap-2 px-2 py-1.5 rounded-lg hover:bg-gray-100 transition-colors">
            <img src="<?= $nav_photo ?>" alt=""
                 class="w-8 h-8 rounded-full object-cover border-2 border-gray-200">
            <span class="hidden md:block text-sm font-semibold text-gray-700 max-w-[120px] truncate">
                <?= esc_html($current_user['name'] ?? '') ?>
            </span>
            <i class="fa fa-angle-down text-xs text-gray-400 hidden md:block"></i>
        </button>
        <!-- Dropdown -->
        <div id="user-dropdown"
             class="hidden absolute right-0 top-full mt-2 w-52 bg-white border border-gray-200 rounded-xl shadow-xl overflow-hidden"
             style="z-index:600;">
            <div class="px-4 py-3 bg-gradient-to-r from-slate-800 to-slate-700">
                <p class="text-white text-sm font-bold"><?= esc_html($current_user['name'] ?? '') ?></p>
                <p class="text-slate-400 text-xs"><?= esc_html(ucfirst(str_replace('_',' ',$current_role))) ?></p>
            </div>
            <a href="<?= base_url('notifications') ?>"
               class="flex items-center gap-2 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                <i class="fa fa-bell text-gray-400 w-4"></i> Notifications
            </a>
            <div class="border-t border-gray-100"></div>
            <a href="<?= base_url('auth/logout') ?>"
               class="flex items-center gap-2 px-4 py-2.5 text-sm text-red-600 hover:bg-red-50 transition-colors">
                <i class="fa fa-sign-out w-4"></i> Sign Out
            </a>
        </div>
    </div>
</header>

<!-- Page content -->
<div class="flex-1 p-5 sm:p-6">
