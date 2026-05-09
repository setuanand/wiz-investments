<?php
/*
Template Name: Edit Profile
*/
get_header();

if (!is_user_logged_in()) {
    wp_redirect(wiz_get_page_url_by_slug('login'));
    exit;
}

$user    = wp_get_current_user();
$notice  = '';

if (isset($_POST['wiz_edit_profile_submit'])) {
    if (!isset($_POST['wiz_edit_profile_nonce']) || !wp_verify_nonce($_POST['wiz_edit_profile_nonce'], 'wiz_edit_profile_action')) {
        $notice = '<div class="form-notice error">Security check failed. Please refresh and try again.</div>';
    } else {
        $investor_type = sanitize_text_field($_POST['investor_type'] ?? '');
        $allowed_types = array('subscriber', 'retail', 'professional', 'institutional');
        if (!in_array($investor_type, $allowed_types)) $investor_type = 'subscriber';
        update_user_meta($user->ID, 'investor_type', $investor_type);
        $notice = '<div class="form-notice success">Profile updated successfully.</div>';
        $user = wp_get_current_user(); // refresh
    }
}

$current_type = get_user_meta($user->ID, 'investor_type', true) ?: 'subscriber';
?>

<div class="auth-container">
    <div class="auth-card">
        <div class="auth-header">
            <h1 class="auth-title">Edit Profile</h1>
            <p class="auth-subtitle">Update your account details</p>
        </div>
        <?php if ($notice) echo $notice; ?>
        <form class="auth-form" method="POST" action="<?php echo esc_url(get_permalink()); ?>">
            <?php wp_nonce_field('wiz_edit_profile_action', 'wiz_edit_profile_nonce'); ?>
            <div class="form-group">
                <label class="form-label">Email Address</label>
                <input class="form-input" type="email" value="<?php echo esc_attr($user->user_email); ?>" disabled>
                <small style="color: var(--text-muted); font-size: 0.8rem;">Email cannot be changed.</small>
            </div>
            <div class="form-group">
                <label class="form-label" for="investor_type">Investor Type</label>
                <select class="form-input" id="investor_type" name="investor_type">
                    <?php foreach (array('subscriber' => 'Subscriber', 'retail' => 'Retail Investor', 'professional' => 'Professional', 'institutional' => 'Institutional') as $val => $label): ?>
                        <option value="<?php echo $val; ?>" <?php selected($current_type, $val); ?>><?php echo $label; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Member Since</label>
                <input class="form-input" type="text" value="<?php echo esc_attr(date('F j, Y', strtotime($user->user_registered))); ?>" disabled>
            </div>
            <button type="submit" name="wiz_edit_profile_submit" class="btn btn-primary" style="width:100%; margin-top: 0.5rem;">Save Changes</button>
        </form>
        <div style="text-align:center; margin-top: 1.5rem;">
            <a href="<?php echo esc_url(wiz_get_page_url_by_slug('dashboard')); ?>" style="color: var(--text-secondary); font-size: 0.9rem;">&larr; Back to Dashboard</a>
        </div>
    </div>
</div>

<?php get_footer(); ?>
