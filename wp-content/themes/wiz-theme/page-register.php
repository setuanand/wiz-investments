<?php
/*
Template Name: Register
*/
get_header();

$registration_message = '';
$is_success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['wiz_register_submit'])) {
    check_admin_referer('wiz_register_nonce', 'wiz_nonce');
    $email            = sanitize_email($_POST['email'] ?? '');
    $password         = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';
    $result = wiz_register_user($email, $password, $password_confirm);
    $is_success           = $result['success'];
    $registration_message = $result['message'];
}

if (isset($_GET['action']) && $_GET['action'] === 'verify_email' && isset($_GET['token'])) {
    $verify_result        = wiz_verify_email_token(sanitize_text_field($_GET['token']));
    $is_success           = $verify_result['success'];
    $registration_message = $verify_result['success']
        ? $verify_result['message'] . ' <a href="' . esc_url(wiz_get_page_url_by_slug('login')) . '" style="color:var(--primary);">Log in here</a>.'
        : $verify_result['message'];
}
?>

<div class="auth-container">
  <div class="auth-card">
    <div class="auth-header">
      <h1 class="auth-title">Create your account</h1>
      <p class="auth-subtitle">Free forever on the Subscriber plan</p>
    </div>

    <?php if (!empty($registration_message)) : ?>
      <div class="form-notice <?php echo $is_success ? 'success' : 'error'; ?>"><?php echo wp_kses_post($registration_message); ?></div>
    <?php endif; ?>

    <?php if (!$is_success || !isset($_GET['action'])) : ?>
    <form method="POST" class="auth-form" id="register-form">
      <?php wp_nonce_field('wiz_register_nonce', 'wiz_nonce'); ?>
      <div class="form-group">
        <label class="form-label" for="email">Email Address</label>
        <input class="form-input" type="email" id="email" name="email" required placeholder="your@email.com" value="<?php echo isset($_POST['email']) ? esc_attr(sanitize_email($_POST['email'])) : ''; ?>">
      </div>
      <div class="form-group">
        <label class="form-label" for="password">Password</label>
        <input class="form-input" type="password" id="password" name="password" required placeholder="Minimum 8 characters" minlength="8">
        <small style="color: var(--text-muted); font-size: 0.8rem; margin-top: 4px; display:block;">Must be at least 8 characters</small>
      </div>
      <div class="form-group">
        <label class="form-label" for="password_confirm">Confirm Password</label>
        <input class="form-input" type="password" id="password_confirm" name="password_confirm" required placeholder="Repeat your password">
      </div>
      <button type="submit" name="wiz_register_submit" class="btn btn-primary" style="width:100%;">Create Account</button>
    </form>

    <div style="text-align:center; margin-top: var(--space-xl); color: var(--text-secondary); font-size: 0.9rem;">
      Already have an account? <a href="<?php echo esc_url(wiz_get_page_url_by_slug('login')); ?>" style="color: var(--primary);">Log in</a>
    </div>

    <div style="text-align:center; margin-top: var(--space-md); font-size: 0.8rem; color: var(--text-muted); line-height: 1.6;">
      By creating an account you agree to our
      <a href="<?php echo esc_url(wiz_get_page_url_by_slug('terms-of-service')); ?>" style="color: var(--text-muted); text-decoration: underline;">Terms of Service</a>
      and
      <a href="<?php echo esc_url(wiz_get_page_url_by_slug('privacy-policy')); ?>" style="color: var(--text-muted); text-decoration: underline;">Privacy Policy</a>.
    </div>
    <?php endif; ?>
  </div>
</div>

<?php get_footer(); ?>
