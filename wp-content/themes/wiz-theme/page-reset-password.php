<?php
/*
Template Name: Reset Password
*/
get_header();

$message      = '';
$message_type = '';
$token_valid  = false;

if (!isset($_GET['token'])) {
    $message      = 'No reset token provided.';
    $message_type = 'error';
} else {
    $token  = sanitize_text_field($_GET['token']);
    $verify = wiz_verify_password_reset_token($token);
    if ($verify['success']) {
        $token_valid = true;
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['wiz_reset_submit'])) {
            check_admin_referer('wiz_reset_nonce', 'wiz_nonce');
            $result = wiz_reset_password_with_token($token, $_POST['new_password'] ?? '', $_POST['confirm_password'] ?? '');
            $message      = $result['message'];
            $message_type = $result['success'] ? 'success' : 'error';
            if ($result['success']) $token_valid = false;
        }
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

    <?php if (!empty($message)) : ?>
      <div class="form-notice <?php echo esc_attr($message_type); ?>"><?php echo esc_html($message); ?></div>
    <?php endif; ?>

    <?php if ($token_valid) : ?>
    <form method="POST" class="auth-form" id="reset-form">
      <?php wp_nonce_field('wiz_reset_nonce', 'wiz_nonce'); ?>
      <div class="form-group">
        <label class="form-label" for="new_password">New Password</label>
        <input class="form-input" type="password" id="new_password" name="new_password" required placeholder="Minimum 8 characters" minlength="8" autofocus>
        <small style="color: var(--text-muted); font-size: 0.8rem; margin-top: 4px; display:block;">Must be at least 8 characters</small>
      </div>
      <div class="form-group">
        <label class="form-label" for="confirm_password">Confirm New Password</label>
        <input class="form-input" type="password" id="confirm_password" name="confirm_password" required placeholder="Repeat your new password">
      </div>
      <button type="submit" name="wiz_reset_submit" class="btn btn-primary" style="width:100%;">Reset Password</button>
    </form>

    <?php elseif ($message_type === 'success') : ?>
      <a href="<?php echo esc_url(wiz_get_page_url_by_slug('login')); ?>" class="btn btn-primary" style="width:100%; text-align:center; display:block;">Go to Login</a>

    <?php else : ?>
      <div style="text-align:center; margin-top: var(--space-lg);">
        <a href="<?php echo esc_url(wiz_get_page_url_by_slug('forgot-password')); ?>" style="color: var(--primary); font-size: 0.9rem;">Request a new reset link</a>
      </div>
    <?php endif; ?>

    <div style="text-align:center; margin-top: var(--space-xl);">
      <a href="<?php echo esc_url(wiz_get_page_url_by_slug('login')); ?>" style="color: var(--text-secondary); font-size: 0.85rem;">&larr; Back to Login</a>
    </div>
  </div>
</div>

<?php get_footer(); ?>
