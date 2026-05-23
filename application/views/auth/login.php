<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= esc_html($title) ?></title>
<link rel="stylesheet" href="<?= base_url('assets/vendor/bower_components/bootstrap/dist/css/bootstrap.min.css') ?>">
<link rel="stylesheet" href="<?= base_url('assets/vendor/bower_components/font-awesome/css/font-awesome.min.css') ?>">
<link rel="stylesheet" href="<?= base_url('assets/vendor/adminlte/css/AdminLTE.min.css') ?>">
<link rel="stylesheet" href="<?= base_url('assets/vendor/adminlte/css/skins/skin-blue.min.css') ?>">
<link rel="stylesheet" href="<?= base_url('assets/css/crm.custom.css') ?>">
<style>
body { background: #1a252f; }
.login-box { width: 380px; }
.login-logo a { color: #fff; font-size: 28px; font-weight: 700; }
.login-logo small { font-size: 14px; color: #bdc3c7; display: block; }
.login-box-body { border-radius: 6px; }
</style>
</head>
<body class="login-page">
<div class="login-box">
    <div class="login-logo">
        <a href="#"><i class="fa fa-map-marker"></i> Field<strong>CRM</strong></a>
        <small>Enterprise Field Staff Management</small>
    </div>
    <div class="login-box-body">
        <p class="login-box-msg">Sign in to continue</p>
        <div id="login-alert" class="alert alert-danger" style="display:none"></div>
        <form id="login-form">
            <input type="hidden" name="<?= $csrf_name ?>" value="<?= $csrf_hash ?>">
            <div class="form-group has-feedback">
                <input type="email" name="email" class="form-control" placeholder="Email" required autocomplete="email">
                <span class="glyphicon glyphicon-envelope form-control-feedback"></span>
            </div>
            <div class="form-group has-feedback">
                <input type="password" name="password" class="form-control" placeholder="Password" required autocomplete="current-password">
                <span class="glyphicon glyphicon-lock form-control-feedback"></span>
            </div>
            <div class="row">
                <div class="col-xs-12">
                    <button type="submit" id="btn-login" class="btn btn-primary btn-block btn-flat">
                        <i class="fa fa-sign-in"></i> Sign In
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script src="<?= base_url('assets/vendor/bower_components/jquery/dist/jquery.min.js') ?>"></script>
<script src="<?= base_url('assets/vendor/bower_components/bootstrap/dist/js/bootstrap.min.js') ?>"></script>
<script>
var BASE_URL = '<?= base_url() ?>';
var CI3_CSRF_NAME = '<?= $csrf_name ?>';
var CI3_CSRF_HASH = '<?= $csrf_hash ?>';

$('#login-form').on('submit', function(e) {
    e.preventDefault();
    var $btn = $('#btn-login');
    $btn.html('<i class="fa fa-spinner fa-spin"></i> Signing in...').prop('disabled', true);
    $('#login-alert').hide();

    $.ajax({
        url: BASE_URL + 'auth/do_login',
        method: 'POST',
        data: $(this).serialize(),
        success: function(res) {
            if (res.status === 'success') {
                window.location = res.data.redirect;
            } else {
                $('#login-alert').text(res.message).show();
                $btn.html('<i class="fa fa-sign-in"></i> Sign In').prop('disabled', false);
            }
        },
        error: function(xhr) {
            var res = xhr.responseJSON || {};
            $('#login-alert').text(res.message || 'Login failed. Please try again.').show();
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
