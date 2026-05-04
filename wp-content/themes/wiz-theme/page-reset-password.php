<?php
/*
Template Name: Reset Password
*/

get_header();

$message = '';
$message_type = '';
$token_valid = false;

if (!isset($_GET['token'])) {
    $message = 'No reset token provided.';
    $message_type = 'error';
} else {
    $token = sanitize_text_field($_GET['token']);
    $verify = wiz_verify_password_reset_token($token);

    if ($verify['success']) {
        $token_valid = true;

        // Handle password reset
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['wiz_reset_submit'])) {
            check_admin_referer('wiz_reset_nonce', 'wiz_nonce');

            $new_password = $_POST['new_password'] ?? '';
            $confirm_password = $_POST['confirm_password'] ?? '';

            $result = wiz_reset_password_with_token($token, $new_password, $confirm_password);

            if ($result['success']) {
                $message = $result['message'];
                $message_type = 'success';
                $token_valid = false;
            } else {
                $message = $result['message'];
                $message_type = 'error';
            }
        }
    } else {
        $message = $verify['message'];
        $message_type = 'error';
    }
}
?>

<div class="container auth-container">
    <div class="auth-form-wrapper">
        <h1>Reset Your Password</h1>

        <?php if (!empty($message)): ?>
            <div class="auth-message <?php echo esc_attr($message_type); ?>">
                <?php echo esc_html($message); ?>
            </div>
        <?php endif; ?>

        <?php if ($token_valid): ?>
        <form method="POST" class="auth-form" id="reset-form">
            <?php wp_nonce_field('wiz_reset_nonce', 'wiz_nonce'); ?>

            <div class="form-group">
                <label for="new_password">New Password</label>
                <input
                    type="password"
                    id="new_password"
                    name="new_password"
                    required
                    placeholder="Minimum 8 characters"
                    class="form-control"
                    minlength="8"
                />
                <small class="form-text">Must be at least 8 characters</small>
            </div>

            <div class="form-group">
                <label for="confirm_password">Confirm Password</label>
                <input
                    type="password"
                    id="confirm_password"
                    name="confirm_password"
                    required
                    placeholder="Confirm your password"
                    class="form-control"
                />
            </div>

            <button type="submit" name="wiz_reset_submit" class="btn btn-primary btn-block">
                Reset Password
            </button>
        </form>
        <?php elseif ($message_type === 'success'): ?>
            <div class="auth-footer">
                <a href="<?php echo esc_url(get_home_url(null, 'login')); ?>" class="btn btn-primary btn-block">
                    Go to Login
                </a>
            </div>
        <?php endif; ?>

        <?php if (!$token_valid && $message_type !== 'success'): ?>
        <div class="auth-footer">
            <a href="<?php echo esc_url(get_home_url(null, 'forgot-password')); ?>">Request another reset link</a>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php get_footer(); ?>
