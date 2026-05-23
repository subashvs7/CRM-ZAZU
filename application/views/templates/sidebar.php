<?php
// All vars injected by MY_Controller::load_view():
// $current_user, $current_role, $is_admin, $is_manager
$CI   =& get_instance();
$seg1 = $CI->uri->segment(1);

function nav_active($seg) {
    $CI =& get_instance();
    return $CI->uri->segment(1) === $seg ? 'active' : '';
}
?>
<aside class="main-sidebar">
    <section class="sidebar">

        <!-- User panel -->
        <div class="user-panel">
            <div class="pull-left image">
                <?php
                $photo = !empty($current_user['profile_photo'])
                    ? base_url('uploads/' . $current_user['profile_photo'])
                    : base_url('assets/vendor/adminlte/img/avatar.png');
                ?>
                <img src="<?= $photo ?>" class="img-circle"
                     alt="<?= esc_html($current_user['name'] ?? 'User') ?>">
            </div>
            <div class="pull-left info">
                <p><?= esc_html($current_user['name'] ?? '') ?></p>
                <a href="#"><i class="fa fa-circle text-success"></i>
                    <?= esc_html(ucfirst(str_replace('_', ' ', $current_role))) ?></a>
            </div>
        </div>

        <ul class="sidebar-menu" data-widget="tree">

            <!-- ── NAVIGATION ───────────────────────────────── -->
            <li class="header">NAVIGATION</li>
            <li class="<?= nav_active('dashboard') ?>">
                <a href="<?= base_url('dashboard') ?>">
                    <i class="fa fa-dashboard"></i><span>Dashboard</span>
                </a>
            </li>

            <!-- ── CRM (all roles) ──────────────────────────── -->
            <li class="header">CRM</li>

            <li class="<?= nav_active('customers') ?>">
                <a href="<?= base_url('customers') ?>">
                    <i class="fa fa-building-o"></i><span>Customers</span>
                </a>
            </li>

            <li class="<?= nav_active('leads') ?>">
                <a href="<?= base_url('leads') ?>">
                    <i class="fa fa-filter"></i><span>Leads</span>
                </a>
            </li>

            <li class="<?= nav_active('orders') ?>">
                <a href="<?= base_url('orders') ?>">
                    <i class="fa fa-shopping-cart"></i><span>Orders</span>
                    <?php if ($is_manager): ?>
                    <span class="pull-right-container">
                        <span class="label label-warning pull-right" id="pending-orders-badge" style="display:none">!</span>
                    </span>
                    <?php endif; ?>
                </a>
            </li>

            <!-- ── FIELD OPS (all roles) ────────────────────── -->
            <li class="header">FIELD OPS</li>

            <li class="<?= nav_active('visits') ?>">
                <a href="<?= base_url('visits') ?>">
                    <i class="fa fa-map-signs"></i><span>Visits</span>
                </a>
            </li>

            <?php if ($is_manager): ?>
            <!-- Live Tracking & Geofence — Admin / Manager only -->
            <li class="<?= nav_active('tracking') ?>">
                <a href="<?= base_url('tracking/live') ?>">
                    <i class="fa fa-map-marker"></i><span>Live Tracking</span>
                </a>
            </li>

            <li class="<?= nav_active('geofence') ?>">
                <a href="<?= base_url('geofence') ?>">
                    <i class="fa fa-circle-o"></i><span>Geofence</span>
                </a>
            </li>
            <?php endif; ?>

            <!-- ── HR (all roles see their own data) ────────── -->
            <li class="header">HR</li>

            <li class="<?= nav_active('attendance') ?>">
                <a href="<?= base_url('attendance') ?>">
                    <i class="fa fa-clock-o"></i><span>Attendance</span>
                </a>
            </li>

            <?php if ($is_manager): ?>
            <!-- Shifts management — Admin / Manager only -->
            <li class="<?= nav_active('shifts') ?>">
                <a href="<?= base_url('shifts') ?>">
                    <i class="fa fa-calendar"></i><span>Shifts</span>
                </a>
            </li>
            <?php endif; ?>

            <li class="<?= nav_active('leave') ?>">
                <a href="<?= base_url('leave') ?>">
                    <i class="fa fa-plane"></i><span>Leave</span>
                </a>
            </li>

            <?php if ($is_manager): ?>
            <!-- Selfie verification log — Admin / Manager only -->
            <li class="<?= nav_active('selfie') ?>">
                <a href="<?= base_url('selfie/log') ?>">
                    <i class="fa fa-camera"></i><span>Selfie Verify</span>
                </a>
            </li>
            <?php endif; ?>

            <!-- ── REPORTS (Admin / Manager only) ───────────── -->
            <?php if ($is_manager): ?>
            <li class="header">REPORTS</li>
            <li class="treeview <?= $seg1 === 'reports' ? 'active menu-open' : '' ?>">
                <a href="#">
                    <i class="fa fa-bar-chart"></i><span>Reports</span>
                    <span class="pull-right-container">
                        <i class="fa fa-angle-left pull-right"></i>
                    </span>
                </a>
                <ul class="treeview-menu">
                    <li><a href="<?= base_url('reports/visits') ?>">
                        <i class="fa fa-circle-o"></i> Visit Reports</a></li>
                    <li><a href="<?= base_url('reports/lead_conversion') ?>">
                        <i class="fa fa-circle-o"></i> Lead Conversion</a></li>
                    <li><a href="<?= base_url('reports/orders') ?>">
                        <i class="fa fa-circle-o"></i> Orders</a></li>
                    <li><a href="<?= base_url('reports/staff_sales') ?>">
                        <i class="fa fa-circle-o"></i> Staff Sales</a></li>
                    <li><a href="<?= base_url('reports/attendance') ?>">
                        <i class="fa fa-circle-o"></i> Attendance</a></li>
                    <li><a href="<?= base_url('reports/punctuality') ?>">
                        <i class="fa fa-circle-o"></i> Punctuality</a></li>
                    <li><a href="<?= base_url('reports/leave_util') ?>">
                        <i class="fa fa-circle-o"></i> Leave Utilisation</a></li>
                    <li><a href="<?= base_url('reports/coverage') ?>">
                        <i class="fa fa-circle-o"></i> Coverage Map</a></li>
                </ul>
            </li>
            <?php endif; ?>

            <!-- ── ADMIN PANEL (Admin only) ──────────────────── -->
            <?php if ($is_admin): ?>
            <li class="header">ADMIN</li>
            <li class="treeview <?= $seg1 === 'admin' ? 'active menu-open' : '' ?>">
                <a href="#">
                    <i class="fa fa-cogs"></i><span>Admin</span>
                    <span class="pull-right-container">
                        <i class="fa fa-angle-left pull-right"></i>
                    </span>
                </a>
                <ul class="treeview-menu">
                    <li><a href="<?= base_url('admin/users') ?>">
                        <i class="fa fa-users"></i> Users</a></li>
                    <li><a href="<?= base_url('admin/teams') ?>">
                        <i class="fa fa-group"></i> Teams</a></li>
                    <li><a href="<?= base_url('admin/products') ?>">
                        <i class="fa fa-cubes"></i> Products</a></li>
                    <li><a href="<?= base_url('admin/notif_templates') ?>">
                        <i class="fa fa-envelope"></i> Notif Templates</a></li>
                    <li><a href="<?= base_url('admin/settings') ?>">
                        <i class="fa fa-sliders"></i> Settings</a></li>
                </ul>
            </li>
            <?php endif; ?>

        </ul>
    </section>
</aside>

<!-- Content Wrapper -->
<div class="content-wrapper">
