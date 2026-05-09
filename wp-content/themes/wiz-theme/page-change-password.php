<?php
/*
Template Name: Change Password
*/
get_header();

if (!is_user_logged_in()) {
    wp_redirect(wiz_get_page_url_by_slug('login'));
    exit;
}

$user   = wp_get_current_user();
$notice = '';

if (isset($_POST['wiz_change_password_submit'])) {
    if (!isset($_POST['wiz_change_password_nonce']) || !wp_verify_nonce($_POST['wiz_change_password_nonce'], 'wiz_change_password_action')) {
        $notice = '<div class="form-notice error">Security check failed. Please refresh and try again.</div>';
    } else {
        $current  = $_POST['current_password'] ?? '';
        $new      = $_POST['new_password'] ?? '';
        $confirm  = $_POST['confirm_password'] ?? '';

        if (empty($current) || empty($new) || empty($confirm)) {
            $notice = '<div class="form-notice error">All fields are required.</div>';
        } elseif (!wp_check_password($current, $user->user_pass, $user->ID)) {
            $notice = '<div class="form-notice error">Current password is incorrect.</div>';
        } elseif (strlen($new) < 8) {
            $notice = '<div class="form-notice error">New password must be at least 8 characters.</div>';
        } elseif ($new !== $confirm) {
            $notice = '<div class="form-notice error">New passwords do not match.</div>';
        } else {
            wp_set_user_password($user->ID, $new);
            $notice = '<div class="form-notice success">Password changed successfully. Please log in again.</div>';
            wp_logout();
            wp_redirect(wiz_get_page_url_by_slug('login') . '?msg=password_changed');
            exit;
        }
    }
}
?>

<div class="auth-container">
    <div class="auth-card">
        <div class="auth-header">
            <h1 class="auth-title">Change Password</h1>
            <p class="auth-subtitle">Choose a strong new password</p>
        </div>
        <?php if ($notice) echo $notice; ?>
        <form class="auth-form" method="POST" action="<?php echo esc_url(get_permalink()); ?>">
            <?php wp_nonce_field('wiz_change_password_action', 'wiz_change_password_nonce'); ?>
            <div class="form-group">
                <label class="form-label" for="current_password">Current Password</label>
                <input class="form-input" type="password" id="current_password" name="current_password" required autocomplete="current-password">
            </div>
            <div class="form-group">
                <label class="form-label" for="new_password">New Password</label>
                <input class="form-input" type="password" id="new_password" name="new_password" required autocomplete="new-password" minlength="8">
            </div>
            <div class="form-group">
                <label class="form-label" for="confirm_password">Confirm New Password</label>
                <input class="form-input" type="password" id="confirm_password" name="confirm_password" required autocomplete="new-password" minlength="8">
            </div>
            <button type="submit" name="wiz_change_password_submit" class="btn btn-primary" style="width:100%; margin-top: 0.5rem;">Update Password</button>
        </form>
        <div style="text-align:center; margin-top: 1.5rem;">
            <a href="<?php echo esc_url(wiz_get_page_url_by_slug('dashboard')); ?>" style="color: var(--text-secondary); font-size: 0.9rem;">&larr; Back to Dashboard</a>
        </div>
    </div>
</div>

<?php get_footer(); ?>
