<?php
/*
Template Name: Dashboard
*/
get_header();

if (!is_user_logged_in()) {
    wp_redirect(wiz_get_page_url_by_slug('login'));
    exit;
}

$user          = wp_get_current_user();
$investor_type = get_user_meta($user->ID, 'investor_type', true) ?: 'subscriber';
?>

<div class="container dashboard-container">
    <div class="dashboard-header">
        <h1>Welcome, <?php echo esc_html($user->user_email); ?>!</h1>
        <p class="dashboard-subtitle">Member Dashboard</p>
    </div>

    <div class="dashboard-grid">
        <div class="dashboard-card">
            <h3>Account Information</h3>
            <div class="dashboard-info">
                <p><strong>Email:</strong> <?php echo esc_html($user->user_email); ?></p>
                <p><strong>Account Type:</strong> <?php echo esc_html(ucfirst($investor_type)); ?></p>
                <p><strong>Member Since:</strong> <?php echo esc_html(date('F j, Y', strtotime($user->user_registered))); ?></p>
            </div>
        </div>

        <div class="dashboard-card">
            <h3>Account Actions</h3>
            <ul class="dashboard-actions">
                <li><a href="<?php echo esc_url(wiz_get_page_url_by_slug('edit-profile')); ?>" class="btn btn-secondary">Edit Profile</a></li>
                <li><a href="<?php echo esc_url(wiz_get_page_url_by_slug('change-password')); ?>" class="btn btn-secondary">Change Password</a></li>
            </ul>
        </div>
    </div>

    <div class="dashboard-section">
        <h2>Member Resources</h2>
        <div class="resources-grid">
            <div class="resource-card">
                <h4>Analytics Dashboard</h4>
                <p>View simulated trading scenarios and analytics.</p>
                <a href="<?php echo esc_url(wiz_get_page_url_by_slug('analytics-dashboard')); ?>" class="btn btn-primary">Go to Analytics</a>
            </div>

            <div class="resource-card">
                <h4>Contact Support</h4>
                <p>Have questions? Reach out to our support team.</p>
                <a href="<?php echo esc_url(wiz_get_page_url_by_slug('contact')); ?>" class="btn btn-primary">Contact Us</a>
            </div>
        </div>
    </div>

    <div class="dashboard-footer">
        <a href="<?php echo esc_url(wp_logout_url(home_url())); ?>" class="btn btn-danger" style="margin-top: 2rem; display: inline-block;">Logout</a>
    </div>
</div>

<?php get_footer(); ?>
