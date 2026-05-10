<?php
/*
Template Name: Reset Password
*/
get_header();

$message      = '';
$message_type = '';
$token_valid  = false;
$token        = '';

if (!isset($_GET['token'])) {
    $message      = 'No reset token provided.';
    $message_type = 'error';
} else {
    $token  = sanitize_text_field($_GET['token']);
    $verify = wiz_verify_password_reset_token($token);
    if ($verify['success']) {
        $token_valid = true;
    } else {
        $message      = $verify['message'];
        $message_type = 'error';
    }
}
?>

<div class="auth-container">
  <div class="auth-card">
    <div class="auth-header">
      <h1 class="auth-title">Reset Password</h1>
      <p class="auth-subtitle">Choose a strong new password for your account</p>
    </div>

    <div id="reset-notice">
      <?php if (!empty($message)) : ?>
        <div class="form-notice <?php echo esc_attr($message_type); ?>"><?php echo esc_html($message); ?></div>
      <?php endif; ?>
    </div>

    <?php if ($token_valid) : ?>
    <form class="auth-form" id="reset-form">
      <?php wp_nonce_field('wiz_reset_nonce', 'wiz_nonce'); ?>
      <input type="hidden" name="token" value="<?php echo esc_attr($token); ?>">
      <div class="form-group">
        <label class="form-label" for="new_password">New Password</label>
        <input class="form-input" type="password" id="new_password" name="new_password" required placeholder="Minimum 8 characters" minlength="8" autofocus>
        <small style="color: var(--text-muted); font-size: 0.8rem; margin-top: 4px; display:block;">Must be at least 8 characters</small>
      </div>
      <div class="form-group">
        <label class="form-label" for="confirm_password">Confirm New Password</label>
        <input class="form-input" type="password" id="confirm_password" name="confirm_password" required placeholder="Repeat your new password">
      </div>
      <button type="submit" class="btn btn-primary" style="width:100%;">Reset Password</button>
    </form>

    <script>
    document.getElementById('reset-form').addEventListener('submit', function(e) {
        e.preventDefault();
        var form = this;
        var btn = form.querySelector('button[type="submit"]');
        var notice = document.getElementById('reset-notice');
        btn.disabled = true;
        btn.textContent = 'Resetting...';
        var data = new FormData(form);
        data.append('action', 'wiz_reset_password');
        fetch('<?php echo esc_url(admin_url('admin-ajax.php')); ?>', {
            method: 'POST',
            body: data
        })
        .then(function(r) { return r.json(); })
        .then(function(res) {
            if (res.success) {
                notice.innerHTML = '<div class="form-notice success">' + res.data + '</div>';
                form.style.display = 'none';
                document.getElementById('reset-login-link').style.display = 'block';
            } else {
                notice.innerHTML = '<div class="form-notice error">' + res.data + '</div>';
                btn.disabled = false;
                btn.textContent = 'Reset Password';
            }
        })
        .catch(function() {
            notice.innerHTML = '<div class="form-notice error">Something went wrong. Please try again.</div>';
            btn.disabled = false;
            btn.textContent = 'Reset Password';
        });
    });
    </script>

    <?php else : ?>
      <div style="text-align:center; margin-top: var(--space-lg);">
        <a href="<?php echo esc_url(wiz_get_page_url_by_slug('forgot-password')); ?>" style="color: var(--primary); font-size: 0.9rem;">Request a new reset link</a>
      </div>
    <?php endif; ?>

    <div id="reset-login-link" style="display:none; text-align:center; margin-top: var(--space-lg);">
      <a href="<?php echo esc_url(wiz_get_page_url_by_slug('login')); ?>" class="btn btn-primary" style="display:inline-block;">Go to Login</a>
    </div>

    <div style="text-align:center; margin-top: var(--space-xl);">
      <a href="<?php echo esc_url(wiz_get_page_url_by_slug('login')); ?>" style="color: var(--text-secondary); font-size: 0.85rem;">&larr; Back to Login</a>
    </div>
  </div>
</div>

<?php get_footer(); ?>
