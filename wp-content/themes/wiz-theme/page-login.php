<?php
/*
Template Name: Login
*/

get_header();

$login_message = '';
$message_type = '';

// Redirect if already logged in
if (is_user_logged_in()) {
    wp_redirect(wiz_get_page_url_by_slug('dashboard'));
    exit;
}

// Handle login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['wiz_login_submit'])) {
    check_admin_referer('wiz_login_nonce', 'wiz_nonce');

    $email = sanitize_email($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $login_message = 'Email and password are required.';
        $message_type = 'error';
    } else {
        // Authenticate user
        $user = wp_authenticate($email, $password);

        if (is_wp_error($user)) {
            $login_message = 'Invalid email or password.';
            $message_type = 'error';
        } else {
            // Check if email is verified
            $email_verified = get_user_meta($user->ID, 'email_verified', true);

            if (!$email_verified) {
                $login_message = 'Please verify your email before logging in. Check your inbox for the verification link.';
                $message_type = 'error';
            } else {
                // Log user in
                wp_set_current_user($user->ID);
                wp_set_auth_cookie($user->ID);
                do_action('wp_login', $user->user_login, $user);

                wp_redirect(wiz_get_page_url_by_slug('dashboard'));
                exit;
            }
        }
    }
}
?>

<div class="container auth-container">
    <div class="auth-form-wrapper">
        <h1>Login to Your Account</h1>

        <?php if (!empty($login_message)): ?>
            <div class="auth-message <?php echo esc_attr($message_type); ?>">
                <?php echo esc_html($login_message); ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="auth-form" id="login-form">
            <?php wp_nonce_field('wiz_login_nonce', 'wiz_nonce'); ?>

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

            <div class="form-group">
                <label for="password">Password</label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    required
                    placeholder="Your password"
                    class="form-control"
                />
            </div>

            <button type="submit" name="wiz_login_submit" class="btn btn-primary btn-block">
                Login
            </button>
        </form>

        <div class="auth-footer">
            Don't have an account? <a href="<?php echo esc_url(wiz_get_page_url_by_slug('register')); ?>">Register here</a><br>
            <a href="<?php echo esc_url(wiz_get_page_url_by_slug('forgot-password')); ?>">Forgot your password?</a>
        </div>
    </div>
</div>

<?php get_footer(); ?>
