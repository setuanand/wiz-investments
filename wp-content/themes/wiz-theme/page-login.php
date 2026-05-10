<?php
/*
Template Name: Login
*/
get_header();

$login_message = '';
$message_type  = '';

if (is_user_logged_in()) {
    wp_redirect(wiz_get_page_url_by_slug('dashboard'));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['wiz_login_submit'])) {
    check_admin_referer('wiz_login_nonce', 'wiz_nonce');
    $email    = sanitize_email($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    if (empty($email) || empty($password)) {
        $login_message = 'Email and password are required.';
        $message_type  = 'error';
    } else {
        $user = wp_authenticate($email, $password);
        if (is_wp_error($user)) {
            $login_message = 'Invalid email or password.';
            $message_type  = 'error';
        } else {
            $email_verified = get_user_meta($user->ID, 'email_verified', true);
            if (!$email_verified) {
                $login_message = 'Please verify your email before logging in. Check your inbox for the verification link.';
                $message_type  = 'error';
            } else {
                wp_set_current_user($user->ID);
                wp_set_auth_cookie($user->ID);
                do_action('wp_login', $user->user_login, $user);
                wp_redirect(wiz_get_page_url_by_slug('dashboard'));
                exit;
            }
        }
    }
}

// Optional message from URL
if (isset($_GET['msg'])) {
    if ($_GET['msg'] === 'password_changed') {
        $login_message = 'Password updated successfully. Please log in with your new password.';
        $message_type  = 'success';
    }
}
?>

<div class="auth-container">
  <div class="auth-card">
    <div class="auth-header">
      <h1 class="auth-title">Welcome back</h1>
      <p class="auth-subtitle">Log in to your WizInvestments account</p>
    </div>

    <?php if (!empty($login_message)) : ?>
      <div class="form-notice <?php echo esc_attr($message_type); ?>"><?php echo esc_html($login_message); ?></div>
    <?php endif; ?>

    <form method="POST" class="auth-form" id="login-form">
      <?php wp_nonce_field('wiz_login_nonce', 'wiz_nonce'); ?>
      <div class="form-group">
        <label class="form-label" for="email">Email Address</label>
        <input class="form-input" type="email" id="email" name="email" required placeholder="your@email.com" value="<?php echo isset($_POST['email']) ? esc_attr(sanitize_email($_POST['email'])) : ''; ?>" autofocus>
      </div>
      <div class="form-group">
        <label class="form-label" for="password">Password</label>
        <input class="form-input" type="password" id="password" name="password" required placeholder="Your password">
      </div>
      <div style="text-align: right; margin-top: -0.5rem; margin-bottom: var(--space-lg);">
        <a href="<?php echo esc_url(wiz_get_page_url_by_slug('forgot-password')); ?>" style="font-size: 0.85rem; color: var(--text-secondary);">Forgot password?</a>
      </div>
      <button type="submit" name="wiz_login_submit" class="btn btn-primary" style="width:100%;">Log In</button>
    </form>

    <div class="auth-footer" style="text-align:center; margin-top: var(--space-xl); color: var(--text-secondary); font-size: 0.9rem;">
      Don't have an account? <a href="<?php echo esc_url(wiz_get_page_url_by_slug('register')); ?>" style="color: var(--primary);">Create one free</a>
    </div>
  </div>
</div>

<?php get_footer(); ?>
