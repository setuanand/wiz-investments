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
$member_since  = date('F j, Y', strtotime($user->user_registered));
?>

<div class="dashboard-page">
  <div class="container">

    <!-- Header -->
    <div class="dashboard-page-header">
      <div>
        <span class="section-label">Member Area</span>
        <h1 class="dashboard-page-title">Welcome back!</h1>
        <p class="dashboard-page-sub"><?php echo esc_html($user->user_email); ?></p>
      </div>
      <a href="<?php echo esc_url(wp_logout_url(home_url())); ?>" class="btn btn-secondary">Logout</a>
    </div>

    <!-- Top cards -->
    <div class="dashboard-top-grid">

      <div class="dashboard-card dashboard-card--info">
        <div class="dashboard-card-label">Account Type</div>
        <div class="dashboard-card-value"><?php echo esc_html(ucfirst($investor_type)); ?></div>
        <div class="dashboard-card-meta">Member since <?php echo esc_html($member_since); ?></div>
      </div>

      <div class="dashboard-card dashboard-card--info">
        <div class="dashboard-card-label">Email</div>
        <div class="dashboard-card-value" style="font-size: 1rem; word-break: break-all;"><?php echo esc_html($user->user_email); ?></div>
        <div class="dashboard-card-meta">Verified account</div>
      </div>

      <div class="dashboard-card dashboard-card--info">
        <div class="dashboard-card-label">Status</div>
        <div class="dashboard-card-value" style="color: var(--gain);">Active</div>
        <div class="dashboard-card-meta">All services available</div>
      </div>

    </div>

    <!-- Quick actions -->
    <h2 class="dashboard-section-title">Quick Access</h2>
    <div class="dashboard-resources-grid">

      <div class="dashboard-resource-card">
        <div class="dashboard-resource-icon">📊</div>
        <h3>Analytics Dashboard</h3>
        <p>Run trading simulations, view equity curves, and analyse performance metrics.</p>
        <a href="<?php echo esc_url(wiz_get_page_url_by_slug('analytics-dashboard')); ?>" class="btn btn-primary">Go to Analytics</a>
      </div>

      <div class="dashboard-resource-card">
        <div class="dashboard-resource-icon">👤</div>
        <h3>Edit Profile</h3>
        <p>Update your investor type and account preferences.</p>
        <a href="<?php echo esc_url(wiz_get_page_url_by_slug('edit-profile')); ?>" class="btn btn-secondary">Edit Profile</a>
      </div>

      <div class="dashboard-resource-card">
        <div class="dashboard-resource-icon">🔑</div>
        <h3>Change Password</h3>
        <p>Update your account password to keep your account secure.</p>
        <a href="<?php echo esc_url(wiz_get_page_url_by_slug('change-password')); ?>" class="btn btn-secondary">Change Password</a>
      </div>

      <div class="dashboard-resource-card">
        <div class="dashboard-resource-icon">✉️</div>
        <h3>Contact Support</h3>
        <p>Have a question or need help? Our team responds within 24 hours.</p>
        <a href="<?php echo esc_url(wiz_get_page_url_by_slug('contact')); ?>" class="btn btn-secondary">Contact Us</a>
      </div>

    </div>

  </div>
</div>

<?php get_footer(); ?>
