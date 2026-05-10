<?php
/*
Template Name: Forgot Password
*/
get_header();

$message      = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['wiz_forgot_submit'])) {
    check_admin_referer('wiz_forgot_nonce', 'wiz_nonce');
    $email  = sanitize_email($_POST['email'] ?? '');
    $result = wiz_request_password_reset($email);
    $message      = $result['message'];
    $message_type = $result['success'] ? 'success' : 'error';
}
?>

<div class="auth-container">
  <div class="auth-card">
    <div class="auth-header">
      <h1 class="auth-title">Forgot Password?</h1>
      <p class="auth-subtitle">Enter your email and we'll send you a reset link</p>
    </div>

    <?php if (!empty($message)) : ?>
      <div class="form-notice <?php echo esc_attr($message_type); ?>"><?php echo esc_html($message); ?></div>
    <?php endif; ?>

    <form method="POST" class="auth-form">
      <?php wp_nonce_field('wiz_forgot_nonce', 'wiz_nonce'); ?>
      <div class="form-group">
        <label class="form-label" for="email">Email Address</label>
        <input class="form-input" type="email" id="email" name="email" required placeholder="your@email.com" autofocus>
      </div>
      <button type="submit" name="wiz_forgot_submit" class="btn btn-primary" style="width:100%;">Send Reset Link</button>
    </form>

    <div style="text-align:center; margin-top: var(--space-xl);">
      <a href="<?php echo esc_url(wiz_get_page_url_by_slug('login')); ?>" style="color: var(--text-secondary); font-size: 0.85rem;">&larr; Back to Login</a>
    </div>
  </div>
</div>

<?php get_footer(); ?>
