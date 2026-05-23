<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= esc_html($page_title ?? 'Field CRM') ?> — Field CRM</title>

<!-- ── CSS ─────────────────────────────────────────────────────── -->
<link rel="stylesheet" href="<?= base_url('assets/vendor/bower_components/bootstrap/dist/css/bootstrap.min.css') ?>">
<link rel="stylesheet" href="<?= base_url('assets/vendor/bower_components/font-awesome/css/font-awesome.min.css') ?>">
<link rel="stylesheet" href="<?= base_url('assets/vendor/bower_components/datatables.net-bs/css/dataTables.bootstrap.min.css') ?>">
<link rel="stylesheet" href="<?= base_url('assets/vendor/bower_components/select2/dist/css/select2.min.css') ?>">
<link rel="stylesheet" href="<?= base_url('assets/vendor/bower_components/bootstrap-datepicker/dist/css/bootstrap-datepicker.min.css') ?>">
<link rel="stylesheet" href="<?= base_url('assets/vendor/adminlte/css/AdminLTE.min.css') ?>">
<link rel="stylesheet" href="<?= base_url('assets/vendor/adminlte/css/skins/skin-blue.min.css') ?>">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
<link rel="stylesheet" href="<?= base_url('assets/css/crm.custom.css') ?>">

<!-- ── JS loaded in <head> ─────────────────────────────────────
     All plugins are loaded here so every inline <script> inside view
     files can safely call $(), DataTable(), CRM.*, toastr, etc.
     without "$ is not defined" or "DataTable is not a function" errors.
  ─────────────────────────────────────────────────────────────── -->
<script src="<?= base_url('assets/vendor/bower_components/jquery/dist/jquery.min.js') ?>"></script>
<script src="<?= base_url('assets/vendor/bower_components/bootstrap/dist/js/bootstrap.min.js') ?>"></script>
<script src="<?= base_url('assets/vendor/bower_components/jquery-slimscroll/jquery.slimscroll.min.js') ?>"></script>
<script src="<?= base_url('assets/vendor/adminlte/js/adminlte.min.js') ?>"></script>
<script src="<?= base_url('assets/vendor/bower_components/datatables.net/js/jquery.dataTables.min.js') ?>"></script>
<script src="<?= base_url('assets/vendor/bower_components/datatables.net-bs/js/dataTables.bootstrap.min.js') ?>"></script>
<script src="<?= base_url('assets/vendor/bower_components/select2/dist/js/select2.full.min.js') ?>"></script>
<script src="<?= base_url('assets/vendor/bower_components/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js') ?>"></script>
<script src="<?= base_url('assets/vendor/bower_components/moment/moment.js') ?>"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

<!-- ── Global JS Variables ──────────────────────────────────────
     Available to every inline script in every view.
  ─────────────────────────────────────────────────────────────── -->
<script>
var BASE_URL        = '<?= base_url() ?>';
var CI3_CSRF_NAME   = '<?= $csrf_name ?>';
var CI3_CSRF_HASH   = '<?= $csrf_hash ?>';
var CURRENT_USER_ID = <?= (int)($current_user_id) ?>;
var CURRENT_ROLE    = '<?= esc_html($current_role) ?>';
</script>
<script src="<?= base_url('assets/js/crm.core.js') ?>"></script>
</head>
<body class="hold-transition skin-blue sidebar-mini">
<div class="wrapper">

<!-- ── Navbar ──────────────────────────────────────────────────── -->
<header class="main-header">
    <a href="<?= base_url('dashboard') ?>" class="logo">
        <span class="logo-mini"><b>FC</b></span>
        <span class="logo-lg"><b>Field</b>CRM</span>
    </a>
    <nav class="navbar navbar-static-top">
        <a href="#" class="sidebar-toggle" data-toggle="push-menu" role="button">
            <span class="sr-only">Toggle navigation</span>
        </a>
        <div class="navbar-custom-menu">
            <ul class="nav navbar-nav">
                <?php if ($current_role === 'field_staff'): ?>
                <!-- GPS Tracking Status — field_staff only -->
                <li id="gps-nav-item" title="GPS Auto-Tracking">
                    <a href="#" style="cursor:default">
                        <span id="gps-status-dot"
                              style="display:inline-block;width:10px;height:10px;border-radius:50%;
                                     background:#aaa;margin-right:4px;vertical-align:middle"></span>
                        <span id="gps-status-label" class="hidden-xs"
                              style="font-size:11px;color:#ccc;vertical-align:middle">GPS</span>
                    </a>
                </li>
                <?php endif; ?>
                <!-- Notifications bell -->
                <li class="dropdown notifications-menu">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                        <i class="fa fa-bell-o"></i>
                        <span class="label label-warning" id="notif-count" style="display:none">0</span>
                    </a>
                    <ul class="dropdown-menu">
                        <li class="header">Notifications</li>
                        <li><ul class="menu" id="notif-list"><li><a href="#">Loading...</a></li></ul></li>
                        <li class="footer"><a href="<?= base_url('notifications') ?>">View All</a></li>
                    </ul>
                </li>
                <!-- User Account -->
                <li class="dropdown user user-menu">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                        <?php
                        $nav_photo = !empty($current_user['profile_photo'])
                            ? base_url('uploads/' . $current_user['profile_photo'])
                            : base_url('assets/vendor/adminlte/img/avatar.png');
                        ?>
                        <img src="<?= $nav_photo ?>" class="user-image" alt="<?= esc_html($current_user['name'] ?? 'User') ?>" style="border-radius:50%;height:25px;width:25px;margin-top:-2px">
                        <span class="hidden-xs"><?= esc_html($current_user['name'] ?? '') ?></span>
                    </a>
                    <ul class="dropdown-menu">
                        <li class="user-header" style="background:#3c8dbc">
                            <img src="<?= $nav_photo ?>" class="img-circle" alt="<?= esc_html($current_user['name'] ?? 'User') ?>" style="border:3px solid rgba(255,255,255,.3)">
                            <p><?= esc_html($current_user['name'] ?? '') ?>
                                <small><?= esc_html(ucfirst(str_replace('_',' ',$current_role))) ?></small>
                            </p>
                        </li>
                        <li class="user-footer">
                            <div class="pull-right">
                                <a href="<?= base_url('auth/logout') ?>" class="btn btn-default btn-flat">Sign out</a>
                            </div>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </nav>
</header>
