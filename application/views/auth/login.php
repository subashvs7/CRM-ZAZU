<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= esc_html($title ?? 'Login') ?> — Field CRM</title>
<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="<?= base_url('assets/vendor/bower_components/font-awesome/css/font-awesome.min.css') ?>">
</head>
<body class="min-h-screen bg-gradient-to-br from-slate-900 via-blue-950 to-slate-900 flex items-center justify-center p-4">

<div class="w-full max-w-sm">

    <!-- Logo -->
    <div class="text-center mb-8">
        <div class="inline-flex items-center justify-center w-16 h-16 bg-blue-600 rounded-2xl shadow-lg mb-4 ring-4 ring-blue-600/20">
            <i class="fa fa-map-marker text-white text-3xl"></i>
        </div>
        <h1 class="text-3xl font-bold text-white tracking-tight">Field<span class="text-blue-400">CRM</span></h1>
        <p class="text-slate-400 text-sm mt-1">Enterprise Field Staff Management</p>
    </div>

    <!-- Card -->
    <div class="bg-white rounded-2xl shadow-2xl p-8">
        <h2 class="text-xl font-bold text-gray-800 mb-1">Sign in to continue</h2>
        <p class="text-sm text-gray-400 mb-6">Enter your credentials to access the dashboard</p>

        <div id="login-alert" class="hidden mb-5 flex items-start gap-2 p-3 bg-red-50 border border-red-200 text-red-700 rounded-xl text-sm">
            <i class="fa fa-exclamation-circle mt-0.5 flex-shrink-0"></i>
            <span id="login-alert-msg"></span>
        </div>

        <form id="login-form">
            <input type="hidden" name="<?= $csrf_name ?>" value="<?= $csrf_hash ?>">

            <div class="mb-4">
                <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">Email address</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fa fa-envelope text-gray-400 text-sm"></i>
                    </div>
                    <input type="email" name="email"
                           class="w-full pl-9 pr-3 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                           placeholder="you@company.com" required autocomplete="email">
                </div>
            </div>

            <div class="mb-6">
                <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">Password</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fa fa-lock text-gray-400 text-sm"></i>
                    </div>
                    <input type="password" name="password"
                           class="w-full pl-9 pr-3 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                           placeholder="••••••••" required autocomplete="current-password">
                </div>
            </div>

            <button type="submit" id="btn-login"
                    class="w-full flex items-center justify-center gap-2 px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-xl transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-60 disabled:cursor-not-allowed shadow-sm">
                <i class="fa fa-sign-in"></i>
                Sign In
            </button>
        </form>
    </div>

    <p class="text-center text-slate-500 text-xs mt-6">&copy; <?= date('Y') ?> Field CRM</p>
</div>

<script src="<?= base_url('assets/vendor/bower_components/jquery/dist/jquery.min.js') ?>"></script>
<script>
var BASE_URL = '<?= base_url() ?>';
var CI3_CSRF_NAME = '<?= $csrf_name ?>';
var CI3_CSRF_HASH = '<?= $csrf_hash ?>';

function showAlert(msg) {
    $('#login-alert-msg').text(msg);
    $('#login-alert').removeClass('hidden');
}

$('#login-form').on('submit', function(e) {
    e.preventDefault();
    var $btn = $('#btn-login');
    $btn.html('<i class="fa fa-spinner fa-spin"></i> Signing in...').prop('disabled', true);
    $('#login-alert').addClass('hidden');

    $.ajax({
        url: BASE_URL + 'auth/do_login',
        method: 'POST',
        data: $(this).serialize(),
        success: function(res) {
            if (res.status === 'success') {
                window.location = res.data.redirect;
            } else {
                showAlert(res.message || 'Login failed.');
                $btn.html('<i class="fa fa-sign-in"></i> Sign In').prop('disabled', false);
            }
        },
        error: function(xhr) {
            var res = xhr.responseJSON || {};
            showAlert(res.message || 'Login failed. Please try again.');
            $btn.html('<i class="fa fa-sign-in"></i> Sign In').prop('disabled', false);
        }
    });
    return false;
});

$(document).ajaxComplete(function(e, xhr) {
    var t = xhr.getResponseHeader('X-CSRF-Token');
    if (t) { CI3_CSRF_HASH = t; $('[name="'+CI3_CSRF_NAME+'"]').val(t); }
});
</script>
</body>
</html>
