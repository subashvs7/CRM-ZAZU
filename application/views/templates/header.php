<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= esc_html($page_title ?? 'Field CRM') ?> — Field CRM</title>
<link rel="icon" type="image/png" href="<?= base_url('assets/images/fav-icon.png') ?>">

<!-- Google Fonts: Outfit -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

<!-- Tailwind CSS (Play CDN) -->
<script src="https://cdn.tailwindcss.com"></script>
<script>
tailwind.config = {
    theme: {
        extend: {
            fontFamily: {
                sans: ['Outfit', 'sans-serif'],
            }
        }
    }
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
<?php
$css_path = FCPATH . 'assets/css/crm.custom.css';
$css_version = file_exists($css_path) ? filemtime($css_path) : time();
?>
<link rel="stylesheet" href="<?= base_url('assets/css/crm.custom.css?v=' . $css_version) ?>">

<!-- jQuery + Bootstrap JS (modals only) -->
<script src="<?= base_url('assets/vendor/bower_components/jquery/dist/jquery.min.js') ?>"></script>
<script src="<?= base_url('assets/vendor/bower_components/bootstrap/dist/js/bootstrap.min.js') ?>"></script>
<script src="<?= base_url('assets/vendor/bower_components/datatables.net/js/jquery.dataTables.min.js') ?>"></script>
<script src="<?= base_url('assets/vendor/bower_components/select2/dist/js/select2.full.min.js') ?>"></script>
<script src="<?= base_url('assets/vendor/bower_components/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js') ?>"></script>
<script src="<?= base_url('assets/vendor/bower_components/moment/moment.js') ?>"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

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


<!-- ═══════════════════════ MAIN AREA ═══════════════════════ -->
<div id="main" class="flex flex-col min-h-screen" style="margin-left:260px; transition:margin-left .28s cubic-bezier(.4,0,.2,1);">

<!-- Top Navbar -->
<header id="topbar"
        class="bg-white h-[70px] flex items-center justify-between px-4 sm:px-6 sticky top-0 gap-4 shadow-sm"
        style="z-index:500;">

    <!-- Sidebar toggle & Title -->
    <div class="flex items-center gap-4">
        <button onclick="toggleSidebar()" title="Toggle menu"
                class="p-2 rounded-lg text-slate-500 hover:bg-slate-100 transition-colors flex-shrink-0">
            <i class="fa fa-bars text-lg"></i>
        </button>
        <div class="hidden sm:block min-w-0">
            <h1 class="text-lg font-bold text-slate-800 truncate leading-none"><?= esc_html($page_title ?? 'Dashboard') ?></h1>
        </div>
    </div>



    <!-- Right Icons & User -->
    <div class="flex items-center gap-3 sm:gap-4">
        <?php if (($current_role ?? '') === 'field_staff'): ?>
        <style>
        @keyframes ping-small {
          75%, 100% {
            transform: scale(1.4, 1.6);
            opacity: 0;
          }
        }
        .animate-ping-small {
          animation: ping-small 2s cubic-bezier(0, 0, 0.2, 1) infinite;
        }
        </style>
        <div class="relative flex" id="gps-wrapper">
            <!-- Pulsing outer ring (Smaller scale) -->
            <div id="gps-pulse-ring" class="hidden absolute inset-0 rounded-full bg-green-400 animate-ping-small opacity-75"></div>
            
            <div id="gps-nav-item" class="relative z-10 flex items-center gap-1.5 text-xs px-3 py-1.5 rounded-full border transition-colors text-white bg-red-500 border-red-600 shadow-sm" title="GPS Auto-Tracking">
                <!-- ACTIVE: Map Marker -->
                <div id="gps-icon-active" class="hidden relative flex items-center justify-center ml-0.5">
                    <i class="fa fa-map-marker text-white text-[13px]"></i>
                </div>
                
                <!-- OFFLINE: Location Marker -->
                <div id="gps-icon-inactive" class="relative flex items-center justify-center ml-0.5">
                    <i class="fa fa-map-marker text-white text-[13px]"></i>
                </div>

                <span id="gps-status-label" class="hidden sm:inline font-medium tracking-wide">
                    GPS <span id="gps-active-check" class="hidden ml-0.5">✓</span>
                </span>
            </div>
        </div>
        
        <script>
        document.addEventListener("DOMContentLoaded", function() {
            if ('permissions' in navigator) {
                navigator.permissions.query({name:'geolocation'}).then(function(result) {
                    function updateGPS() {
                        var item = document.getElementById('gps-nav-item');
                        var active = document.getElementById('gps-icon-active');
                        var inactive = document.getElementById('gps-icon-inactive');
                        var check = document.getElementById('gps-active-check');
                        var ring = document.getElementById('gps-pulse-ring');
                        if (!item || !active || !inactive) return;
                        
                        if (result.state === 'granted') {
                            item.className = 'relative z-10 flex items-center gap-1.5 text-xs px-3 py-1.5 rounded-full border transition-colors text-white bg-green-500 border-green-600 shadow-sm';
                            active.classList.remove('hidden');
                            inactive.classList.add('hidden');
                            if(check) check.classList.remove('hidden');
                            if(ring) ring.classList.remove('hidden');
                        } else {
                            item.className = 'relative z-10 flex items-center gap-1.5 text-xs px-3 py-1.5 rounded-full border transition-colors text-white bg-red-500 border-red-600 shadow-sm';
                            active.classList.add('hidden');
                            inactive.classList.remove('hidden');
                            if(check) check.classList.add('hidden');
                            if(ring) ring.classList.add('hidden');
                        }
                    }
                    updateGPS();
                    result.onchange = updateGPS;
                }).catch(function(e) { console.log('Geolocation permission check failed:', e); });
            }
        });
        </script>
        <?php endif; ?>

        <div class="flex items-center gap-1 sm:gap-2">
            <!-- Notifications bell -->
            <div class="relative" id="notif-wrapper">
                <button onclick="toggleNotifDropdown()" title="Notifications"
                        class="relative p-2 rounded-full text-slate-500 hover:bg-slate-100 transition-colors">
                    <i class="fa fa-bell-o text-[18px]"></i>
                    <span id="notif-count"
                          class="hidden absolute top-1 right-1 min-w-[16px] h-[16px] bg-red-500 text-white text-[10px] font-bold rounded-full flex items-center justify-center px-0.5 leading-none shadow-sm">0</span>
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

            <!-- Dummy Message Icon (from design) -->
            <!-- <button class="relative p-2 rounded-full text-slate-500 hover:bg-slate-100 transition-colors hidden sm:block">
                <i class="fa fa-commenting-o text-[18px]"></i>
                <span class="absolute top-1 right-0 min-w-[16px] h-[16px] bg-red-500 text-white text-[10px] font-bold rounded-full flex items-center justify-center px-0.5 leading-none shadow-sm">1</span>
            </button> -->
            
            <!-- Dummy Calendar Icon (from design) -->
            <!-- <button class="relative p-2 rounded-full text-slate-500 hover:bg-slate-100 transition-colors hidden sm:block">
                <i class="fa fa-calendar-o text-[18px]"></i>
            </button> -->
        </div>

        <div class="w-px h-8 bg-slate-200 hidden sm:block mx-1"></div>

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
                     class="w-8 h-8 rounded-full object-cover">
                <div class="hidden md:flex flex-col text-left">
                    <span class="text-sm font-bold text-gray-800 max-w-[120px] truncate leading-tight">
                        <?= esc_html($current_user['name'] ?? 'Admin') ?>
                    </span>
                    <span class="text-[10px] text-gray-500 truncate leading-tight">
                        <?= esc_html(ucfirst(str_replace('_',' ',$current_role ?? ''))) ?>
                    </span>
                </div>
                <i class="fa fa-angle-down text-xs text-gray-400 hidden md:block ml-1"></i>
            </button>
            <!-- Dropdown -->
            <div id="user-dropdown"
                 class="hidden absolute right-0 top-full mt-2 w-52 bg-white border border-gray-200 rounded-xl shadow-xl overflow-hidden"
                 style="z-index:600;">
                <div class="px-4 py-3 bg-gradient-to-r from-slate-800 to-slate-700">
                    <p class="text-white text-sm font-bold"><?= esc_html($current_user['name'] ?? 'Admin') ?></p>
                    <p class="text-slate-400 text-xs"><?= esc_html(ucfirst(str_replace('_',' ',$current_role ?? ''))) ?></p>
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
    </div>
</header>

<!-- Page content -->
<div class="flex-1 p-5 sm:p-6">
