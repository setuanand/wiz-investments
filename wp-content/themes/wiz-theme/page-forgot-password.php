<?php
/*
Template Name: Forgot Password
*/

get_header();

$message = '';
$message_type = '';

// Handle password reset request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['wiz_forgot_submit'])) {
    check_admin_referer('wiz_forgot_nonce', 'wiz_nonce');

    $email = sanitize_email($_POST['email'] ?? '');

    if (empty($email)) {
        $message = 'Please enter your email address.';
        $message_type = 'error';
    } else {
        $result = wiz_request_password_reset($email);
        $message = $result['message'];
        $message_type = $result['success'] ? 'success' : 'error';
    }
}
?>

<div class="container auth-container">
    <div class="auth-form-wrapper">
        <h1>Reset Your Password</h1>
        <p class="form-description">Enter your email address and we'll send you a link to reset your password.</p>

        <?php if (!empty($message)): ?>
            <div class="auth-message <?php echo esc_attr($message_type); ?>">
                <?php echo esc_html($message); ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="auth-form" id="forgot-form">
            <?php wp_nonce_field('wiz_forgot_nonce', 'wiz_nonce'); ?>

            <div class="form-group">
                <label for="email">Email Address</label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    required
                    placeholder="your@email.com"
                    class="form-control"
                    value="<?php echo isset($_POST['email']) ? esc_attr(sanitize_email($_POST['email'])) : ''; ?>"
                    autofocus
                />
            </div>

            <button type="submit" name="wiz_forgot_submit" class="btn btn-primary btn-block">
                Send Reset Link
            </button>
        </form>

        <div class="auth-footer">
            Remember your password? <a href="<?php echo esc_url(get_home_url(null, 'login')); ?>">Login here</a><br>
            Don't have an account? <a href="<?php echo esc_url(get_home_url(null, 'register')); ?>">Register here</a>
        </div>
    </div>
</div>

<?php get_footer(); ?>
