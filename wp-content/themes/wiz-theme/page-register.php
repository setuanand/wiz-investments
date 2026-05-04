<?php
/*
Template Name: Register
*/

get_header();

// Handle registration
$registration_message = '';
$is_success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['wiz_register_submit'])) {
    check_admin_referer('wiz_register_nonce', 'wiz_nonce');

    $email = sanitize_email($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';

    $result = wiz_register_user($email, $password, $password_confirm);

    if ($result['success']) {
        $is_success = true;
        $registration_message = $result['message'];
    } else {
        $registration_message = $result['message'];
    }
}

// Handle email verification
if (isset($_GET['action']) && $_GET['action'] === 'verify_email' && isset($_GET['token'])) {
    $token = sanitize_text_field($_GET['token']);
    $verify_result = wiz_verify_email_token($token);

    if ($verify_result['success']) {
        $is_success = true;
        $registration_message = $verify_result['message'] . ' <a href="' . esc_url(get_home_url(null, 'login')) . '">Login here</a>.';
    } else {
        $registration_message = $verify_result['message'];
    }
}
?>

<div class="container auth-container">
    <div class="auth-form-wrapper">
        <h1>Create Your Account</h1>

        <?php if (!empty($registration_message)): ?>
            <div class="auth-message <?php echo $is_success ? 'success' : 'error'; ?>">
                <?php echo wp_kses_post($registration_message); ?>
            </div>
        <?php endif; ?>

        <?php if (!$is_success || !isset($_GET['action']) || $_GET['action'] !== 'verify_email'): ?>
        <form method="POST" class="auth-form" id="register-form">
            <?php wp_nonce_field('wiz_register_nonce', 'wiz_nonce'); ?>

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
                />
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    required
                    placeholder="Minimum 8 characters"
                    class="form-control"
                    minlength="8"
                />
                <small class="form-text">Must be at least 8 characters</small>
            </div>

            <div class="form-group">
                <label for="password_confirm">Confirm Password</label>
                <input
                    type="password"
                    id="password_confirm"
                    name="password_confirm"
                    required
                    placeholder="Confirm your password"
                    class="form-control"
                />
            </div>

            <button type="submit" name="wiz_register_submit" class="btn btn-primary btn-block">
                Create Account
            </button>
        </form>

        <div class="auth-footer">
            Already have an account? <a href="<?php echo esc_url(get_home_url(null, 'login')); ?>">Login here</a>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php get_footer(); ?>
