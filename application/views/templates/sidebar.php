<!-- Mobile sidebar overlay -->
<div id="sidebar-overlay" onclick="closeSidebar()"
     class="fixed inset-0 bg-black/60 backdrop-blur-sm hidden lg:hidden"
     style="z-index:9998"></div>

<!-- ═══════════════════════ SIDEBAR ═══════════════════════ -->
<aside id="sidebar"
       class="fixed top-0 left-0 h-full flex flex-col bg-black select-none"
       style="width:260px; z-index:9999; transition:transform .28s cubic-bezier(.4,0,.2,1), width .28s cubic-bezier(.4,0,.2,1);">

    <!-- Logo -->
    <div class="flex items-center justify-center pt-5 pb-3 flex-shrink-0 px-4 w-full logo-container transition-all">
        <a href="<?= base_url('dashboard') ?>" class="flex items-center justify-center bg-white rounded-xl w-full py-2 min-h-[44px] transition-all overflow-hidden">
            <img src="<?= base_url('assets/images/crm-logo.png') ?>" alt="CRM Logo" class="sidebar-text w-full max-w-[150px] h-auto object-contain px-2">
            <img src="<?= base_url('assets/images/fav-icon.png') ?>" alt="CRM Icon" class="hidden collapsed-logo-img w-7 h-7 object-contain">
        </a>
    </div>

    <!-- Navigation -->
    <nav class="flex-1 px-3 py-2 overflow-y-auto overflow-x-hidden space-y-0.5 sidebar-nav">
        <?php
        $CI   =& get_instance();
        $seg1 = $CI->uri->segment(1);
        $seg2 = $CI->uri->segment(2);
        
        if (!function_exists('tw_active')) {
            function tw_active($seg) {
                $CI =& get_instance(); return $CI->uri->segment(1) === $seg;
            }
        }
        $lnk = 'flex items-center gap-3 px-3 py-2.5 mx-3 rounded-lg text-slate-400 hover:bg-white/10 hover:text-white transition-all duration-150 text-[13px] font-medium group cursor-pointer text-left';
        $lnk_on = 'flex items-center gap-3 px-3 py-2.5 mx-3 rounded-lg bg-blue-600 text-white text-[13px] font-medium text-left shadow-md';
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
        <button type="button" onclick="toggleSubmenu('customers-sub',this)"
                class="<?= $lnk ?> sidebar-text w-[calc(100%-1.5rem)]">
            <i class="fa fa-building-o <?= $ic ?>"></i>
            <span class="flex-1 sidebar-text text-left">Customers</span>
            <i class="fa fa-angle-right text-xs sub-arrow sidebar-text transition-transform duration-200 <?= $seg1==='customers'?'rotate-90':'' ?>"></i>
        </button>
        <div id="customers-sub" class="crm-submenu <?= $seg1==='customers'?'open':'' ?>">
            <a href="<?= base_url('customers/index/primary') ?>" class="sub-link sidebar-text<?= ($seg1==='customers' && $this->uri->segment(3) !== 'followup') ?' active':'' ?>"><i class="fa fa-star w-4 text-center"></i> Primary Customers</a>
            <a href="<?= base_url('customers/index/followup') ?>" class="sub-link sidebar-text<?= ($seg1==='customers' && $this->uri->segment(3) === 'followup') ?' active':'' ?>"><i class="fa fa-refresh w-4 text-center"></i> Follow-ups</a>
        </div>
        <?php endif; ?>
        <?php if (has_module_access('leads')): ?>
        <a href="<?= base_url('leads') ?>" class="<?= tw_active('leads') ? $lnk_on : $lnk ?>">
            <i class="fa fa-filter <?= $ic ?>"></i>
            <span class="sidebar-text">Ads Leads</span>
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
        <!-- <a href="<?= base_url('geofence') ?>" class="<?= tw_active('geofence') ? $lnk_on : $lnk ?>">
            <i class="fa fa-circle-o <?= $ic ?>"></i>
            <span class="sidebar-text">Geofence</span>
        </a> -->
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
        <!--<?php if (has_module_access('shifts')): ?>-->
        <!--<a href="<?= base_url('shifts') ?>" class="<?= tw_active('shifts') ? $lnk_on : $lnk ?>">-->
        <!--    <i class="fa fa-calendar <?= $ic ?>"></i>-->
        <!--    <span class="sidebar-text">Shifts</span>-->
        <!--</a>-->
        <!--<?php endif; ?>-->
        <!--<?php if (has_module_access('leave')): ?>-->
        <!--<a href="<?= base_url('leave') ?>" class="<?= tw_active('leave') ? $lnk_on : $lnk ?>">-->
        <!--    <i class="fa fa-plane <?= $ic ?>"></i>-->
        <!--    <span class="sidebar-text">Leave</span>-->
        <!--</a>-->
        <!--<?php endif; ?>-->
        <!--<?php if (has_module_access('selfie/log')): ?>-->
        <!--<a href="<?= base_url('selfie/log') ?>" class="<?= tw_active('selfie') ? $lnk_on : $lnk ?>">-->
        <!--    <i class="fa fa-camera <?= $ic ?>"></i>-->
        <!--    <span class="sidebar-text">Selfie Verify</span>-->
        <!--</a>-->
        <!--<?php endif; ?>-->
        <?php endif; ?>

        <!--<?php if (has_module_access('reports')): ?>-->
        <!-- Reports submenu -->
        <!--<p class="px-3 pt-3 pb-1 text-[10px] font-bold text-slate-500 uppercase tracking-widest sidebar-text">Analytics</p>-->
        <!--<button type="button" onclick="toggleSubmenu('reports-sub',this)"-->
        <!--        class="<?= $lnk ?> sidebar-text w-[calc(100%-1.5rem)]">-->
        <!--    <i class="fa fa-bar-chart <?= $ic ?>"></i>-->
        <!--    <span class="flex-1 sidebar-text text-left">Reports</span>-->
        <!--    <i class="fa fa-angle-right text-xs sub-arrow sidebar-text transition-transform duration-200 <?= $seg1==='reports'?'rotate-90':'' ?>"></i>-->
        <!--</button>-->
        <!--<div id="reports-sub" class="crm-submenu <?= $seg1==='reports'?'open':'' ?>">-->
        <!--    <a href="<?= base_url('reports/visits') ?>"          class="sub-link sidebar-text<?= $seg2==='visits'?' active':'' ?>"><i class="fa fa-map w-4 text-center"></i> Visit Reports</a>-->
        <!--    <a href="<?= base_url('reports/lead_conversion') ?>" class="sub-link sidebar-text<?= $seg2==='lead_conversion'?' active':'' ?>"><i class="fa fa-funnel w-4 text-center"></i> Lead Conversion</a>-->
        <!--    <a href="<?= base_url('reports/orders') ?>"          class="sub-link sidebar-text<?= $seg2==='orders'?' active':'' ?>"><i class="fa fa-shopping-bag w-4 text-center"></i> Orders</a>-->
        <!--    <a href="<?= base_url('reports/staff_sales') ?>"     class="sub-link sidebar-text<?= $seg2==='staff_sales'?' active':'' ?>"><i class="fa fa-trophy w-4 text-center"></i> Staff Sales</a>-->
        <!--    <a href="<?= base_url('reports/attendance') ?>"      class="sub-link sidebar-text<?= $seg2==='attendance'?' active':'' ?>"><i class="fa fa-clock-o w-4 text-center"></i> Attendance</a>-->
        <!--    <a href="<?= base_url('reports/punctuality') ?>"     class="sub-link sidebar-text<?= $seg2==='punctuality'?' active':'' ?>"><i class="fa fa-check-circle w-4 text-center"></i> Punctuality</a>-->
        <!--    <a href="<?= base_url('reports/leave_util') ?>"      class="sub-link sidebar-text<?= $seg2==='leave_util'?' active':'' ?>"><i class="fa fa-plane w-4 text-center"></i> Leave Util.</a>-->
        <!--    <a href="<?= base_url('reports/coverage') ?>"        class="sub-link sidebar-text<?= $seg2==='coverage'?' active':'' ?>"><i class="fa fa-map-o w-4 text-center"></i> Coverage Map</a>-->
        <!--</div>-->
        <!--<?php endif; ?>-->

        <!-- Product Hub -->
        <p class="px-3 pt-3 pb-1 text-[10px] font-bold text-slate-500 uppercase tracking-widest sidebar-text">Product Hub</p>
        <a href="<?= base_url('product_hub/assets') ?>" class="<?= tw_active('product_hub') && $seg2==='assets' ? $lnk_on : $lnk ?>">
            <i class="fa fa-folder-open <?= $ic ?>"></i>
            <span class="sidebar-text">Assets & Demos</span>
        </a>
        <a href="<?= base_url('product_hub/products') ?>" class="<?= tw_active('product_hub') && ($seg2==='products' || $seg2==='package_tiers') ? $lnk_on : $lnk ?>">
            <i class="fa fa-cubes <?= $ic ?>"></i>
            <span class="sidebar-text">Products</span>
        </a>

        <!-- Workspace -->
        <p class="px-3 pt-3 pb-1 text-[10px] font-bold text-slate-500 uppercase tracking-widest sidebar-text">Workspace</p>
        <a href="<?= base_url('workspace/contact_book') ?>" class="<?= tw_active('workspace') && $seg2==='contact_book' ? $lnk_on : $lnk ?>">
            <i class="fa fa-address-book <?= $ic ?>"></i>
            <span class="sidebar-text">Contact Book</span>
        </a>

        <!-- Communications -->
        <p class="px-3 pt-3 pb-1 text-[10px] font-bold text-slate-500 uppercase tracking-widest sidebar-text">Communications</p>
        <a href="<?= base_url('communications/bulk_mail') ?>" class="<?= tw_active('communications') ? $lnk_on : $lnk ?>">
            <i class="fa fa-envelope-open-o <?= $ic ?>"></i>
            <span class="sidebar-text">Bulk Mail Hub</span>
        </a>

        <?php if (has_module_access('admin')): ?>
        <!-- Admin submenu -->
        <p class="px-3 pt-3 pb-1 text-[10px] font-bold text-slate-500 uppercase tracking-widest sidebar-text">System</p>
        <button type="button" onclick="toggleSubmenu('admin-sub',this)"
                class="<?= $lnk ?> sidebar-text w-[calc(100%-1.5rem)]">
            <i class="fa fa-cogs <?= $ic ?>"></i>
            <span class="flex-1 sidebar-text text-left">Admin</span>
            <i class="fa fa-angle-right text-xs sub-arrow sidebar-text transition-transform duration-200 <?= $seg1==='admin'?'rotate-90':'' ?>"></i>
        </button>
        <div id="admin-sub" class="crm-submenu <?= $seg1==='admin'?'open':'' ?>">
            <a href="<?= base_url('admin/users') ?>"           class="sub-link sidebar-text<?= $seg2==='users'?' active':'' ?>"><i class="fa fa-users w-4 text-center"></i> Users</a>
            <!--<a href="<?= base_url('admin/teams') ?>"           class="sub-link sidebar-text<?= $seg2==='teams'?' active':'' ?>"><i class="fa fa-group w-4 text-center"></i> Teams</a>-->
            <!--<a href="<?= base_url('admin/notif_templates') ?>" class="sub-link sidebar-text<?= $seg2==='notif_templates'?' active':'' ?>"><i class="fa fa-envelope w-4 text-center"></i> Notif Templates</a>-->
            <a href="<?= base_url('admin/settings') ?>"        class="sub-link sidebar-text<?= $seg2==='settings'?' active':'' ?>"><i class="fa fa-sliders w-4 text-center"></i> Settings</a>
            <a href="<?= base_url('admin/role_permissions') ?>" class="sub-link sidebar-text<?= $seg2==='role_permissions'?' active':'' ?>"><i class="fa fa-key w-4 text-center"></i> Role Permissions</a>
            <a href="<?= base_url('admin/transfer_staff') ?>" class="sub-link sidebar-text<?= $seg2==='transfer_staff'?' active':'' ?>"><i class="fa fa-exchange w-4 text-center"></i> Transfer Staff</a>
        </div>
        <?php endif; ?>
    </nav>

</aside>
