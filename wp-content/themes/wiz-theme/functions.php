<?php

// ===================== INCLUDES =====================
require_once get_template_directory() . '/includes/class-wiz-data.php';
require_once get_template_directory() . '/includes/class-wiz-portfolio.php';
require_once get_template_directory() . '/includes/class-wiz-ajax.php';

// Register all AJAX endpoints
add_action('init', ['WizAjax', 'register']);

function wiz_theme_setup() {
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('html5', array('search-form', 'gallery', 'caption', 'style', 'script'));
    add_theme_support('custom-logo', array('height' => 80, 'width' => 160, 'flex-width' => true));
    add_theme_support('automatic-feed-links');
    register_nav_menus(array(
        'primary' => __('Primary Menu', 'wiz-theme'),
    ));
}
add_action('after_setup_theme', 'wiz_theme_setup');

// ===================== ADMIN BAR =====================
// Hide WordPress admin bar for all non-admin users
function wiz_hide_admin_bar() {
    if (!current_user_can('administrator')) {
        show_admin_bar(false);
    }
}
add_action('after_setup_theme', 'wiz_hide_admin_bar');


function wiz_theme_scripts() {
    wp_enqueue_style('wiz-style', get_stylesheet_uri(), array(), filemtime(get_stylesheet_directory() . '/style.css'));
    wp_enqueue_script('chartjs', 'https://cdn.jsdelivr.net/npm/chart.js', array(), '4.3.0', true);
    wp_enqueue_script('wiz-analytics', get_template_directory_uri() . '/assets/js/analytics.js', array('chartjs'), '0.2', true);
}
add_action('wp_enqueue_scripts', 'wiz_theme_scripts');

function wiz_register_widget_areas() {
    register_sidebar(array(
        'name' => __('Sidebar', 'wiz-theme'),
        'id'   => 'sidebar-1',
        'before_widget' => '<section id="%1$s" class="widget %2$s">',
        'after_widget'  => '</section>',
        'before_title'  => '<h2 class="widget-title">',
        'after_title'   => '</h2>',
    ));
}
add_action('widgets_init', 'wiz_register_widget_areas');

function wiz_theme_menu_fallback() {
    $exclude_slugs = array('login','register','forgot-password','reset-password','dashboard','edit-profile','change-password','privacy-policy','terms-of-service');
    $exclude_ids = array_filter(array_map('wiz_get_page_id_by_slug', $exclude_slugs));
    wp_page_menu(array(
        'menu_class' => 'menu',
        'show_home'  => true,
        'exclude'    => implode(',', $exclude_ids),
    ));
}

function wiz_exclude_auth_pages_from_primary_menu($items, $args) {
    if ('primary' !== $args->theme_location) return $items;
    $exclude_keys = array('login','register','forgot-password','reset-password','dashboard','edit-profile','change-password','privacy-policy','terms-of-service');
    return array_filter($items, function($item) use ($exclude_keys) {
        $url = strtolower(trim($item->url));
        foreach ($exclude_keys as $key) {
            if (strpos($url, '/' . $key) !== false) return false;
            if (strtolower(sanitize_title($item->title)) === $key) return false;
        }
        return true;
    });
}
add_filter('wp_nav_menu_objects', 'wiz_exclude_auth_pages_from_primary_menu', 10, 2);

function wiz_register_contact_submission_cpt() {
    register_post_type('contact_submission', array(
        'labels' => array(
            'name'          => __('Contact Submissions', 'wiz-theme'),
            'singular_name' => __('Contact Submission', 'wiz-theme'),
        ),
        'public'      => false,
        'show_ui'     => true,
        'has_archive' => false,
        'supports'    => array('title', 'editor'),
    ));
}
add_action('init', 'wiz_register_contact_submission_cpt');

function wiz_process_contact_submission() {
    if (!isset($_POST['wiz_contact_submit'])) return '';
    if (!isset($_POST['wiz_contact_nonce']) || !wp_verify_nonce($_POST['wiz_contact_nonce'], 'wiz_contact_action')) {
        return '<div class="form-notice error">Security check failed. Please refresh and try again.</div>';
    }
    $name    = sanitize_text_field($_POST['name'] ?? '');
    $email   = sanitize_email($_POST['email'] ?? '');
    $message = sanitize_textarea_field($_POST['message'] ?? '');
    if (empty($name) || empty($email) || empty($message)) {
        return '<div class="form-notice error">Please complete all fields before submitting.</div>';
    }
    $post_id = wp_insert_post(array(
        'post_title'   => 'Contact from ' . $name,
        'post_type'    => 'contact_submission',
        'post_content' => $message,
        'post_status'  => 'private',
    ));
    if (is_wp_error($post_id)) {
        return '<div class="form-notice error">Unable to save your message right now. Please try again later.</div>';
    }
    update_post_meta($post_id, 'contact_email', $email);
    update_post_meta($post_id, 'contact_name', $name);
    wp_mail(get_option('admin_email'), 'New Contact Message from ' . $name, $message . "\n\nEmail: " . $email);
    return '<div class="form-notice success">Thanks, ' . esc_html($name) . '! Your message has been received. We will reply soon.</div>';
}

function wiz_get_page_id_by_slug($slug) {
    $page = get_page_by_path($slug);
    return $page ? $page->ID : 0;
}

function wiz_get_page_url_by_slug($slug) {
    $page_id = wiz_get_page_id_by_slug($slug);
    if ($page_id) return get_permalink($page_id);
    return home_url('/' . trim($slug, '/') . '/');
}

// Single source of truth for all pages and their templates
function wiz_get_all_pages() {
    return array(
        array('title' => 'About',                'slug' => 'about',               'template' => 'page-about.php'),
        array('title' => 'Services',             'slug' => 'services',            'template' => 'page-services.php'),
        array('title' => 'Contact',              'slug' => 'contact',             'template' => 'page-contact.php'),
        array('title' => 'Analytics Dashboard',  'slug' => 'analytics-dashboard', 'template' => 'page-analytics.php'),
        array('title' => 'Privacy Policy',       'slug' => 'privacy-policy',      'template' => 'page-privacy.php'),
        array('title' => 'Terms of Service',     'slug' => 'terms-of-service',    'template' => 'page-terms.php'),
        array('title' => 'Register',             'slug' => 'register',            'template' => 'page-register.php'),
        array('title' => 'Login',                'slug' => 'login',               'template' => 'page-login.php'),
        array('title' => 'Dashboard',            'slug' => 'dashboard',           'template' => 'page-dashboard.php'),
        array('title' => 'Edit Profile',         'slug' => 'edit-profile',        'template' => 'page-edit-profile.php'),
        array('title' => 'Change Password',      'slug' => 'change-password',     'template' => 'page-change-password.php'),
        array('title' => 'Forgot Password',      'slug' => 'forgot-password',     'template' => 'page-forgot-password.php'),
        array('title' => 'Reset Password',       'slug' => 'reset-password',      'template' => 'page-reset-password.php'),
    );
}

function wiz_create_default_pages() {
    foreach (wiz_get_all_pages() as $p) {
        if (!wiz_get_page_id_by_slug($p['slug'])) {
            $id = wp_insert_post(array(
                'post_title'   => $p['title'],
                'post_name'    => $p['slug'],
                'post_status'  => 'publish',
                'post_type'    => 'page',
                'post_content' => '',
            ));
            if (!is_wp_error($id)) {
                update_post_meta($id, '_wp_page_template', $p['template']);
            }
        }
    }
}
add_action('init', 'wiz_create_default_pages');

// Force-correct template assignments — fixes pages that exist but have wrong/missing template meta
function wiz_fix_page_templates() {
    foreach (wiz_get_all_pages() as $p) {
        $id = wiz_get_page_id_by_slug($p['slug']);
        if ($id && get_post_meta($id, '_wp_page_template', true) !== $p['template']) {
            update_post_meta($id, '_wp_page_template', $p['template']);
        }
    }
}
add_action('init', 'wiz_fix_page_templates');

// ===================== AUTHENTICATION FUNCTIONS =====================

function wiz_generate_token($length = 32) {
    return bin2hex(random_bytes($length / 2));
}

function wiz_send_verification_email($user_id, $email, $token) {
    $verify_link = add_query_arg(array('action' => 'verify_email', 'token' => $token), wiz_get_page_url_by_slug('register'));
    $subject = 'Verify your Wiz Investments email address';
    $message = sprintf(
        "Welcome to Wiz Investments!\n\nPlease verify your email by clicking the link below:\n\n%s\n\nThis link will expire in 24 hours.\n\nIf you did not create this account, please ignore this email.",
        esc_url($verify_link)
    );
    return wp_mail($email, $subject, $message);
}

function wiz_send_password_reset_email($email, $token) {
    $reset_link = add_query_arg(array('token' => $token), wiz_get_page_url_by_slug('reset-password'));
    $subject = 'Reset your Wiz Investments password';
    $message = sprintf(
        "Click the link below to reset your password:\n\n%s\n\nThis link will expire in 1 hour.\n\nIf you did not request a password reset, please ignore this email.",
        esc_url($reset_link)
    );
    return wp_mail($email, $subject, $message);
}

function wiz_register_user($email, $password, $password_confirm) {
    if (empty($email) || empty($password))
        return array('success' => false, 'message' => 'Email and password are required.');
    if (!is_email($email))
        return array('success' => false, 'message' => 'Invalid email address.');
    if (strlen($password) < 8)
        return array('success' => false, 'message' => 'Password must be at least 8 characters.');
    if ($password !== $password_confirm)
        return array('success' => false, 'message' => 'Passwords do not match.');
    if (email_exists($email))
        return array('success' => false, 'message' => 'This email is already registered.');
    $user_id = wp_create_user($email, $password, $email);
    if (is_wp_error($user_id))
        return array('success' => false, 'message' => 'Error creating account: ' . $user_id->get_error_message());
    $token = wiz_generate_token();
    update_user_meta($user_id, 'verification_token', $token);
    update_user_meta($user_id, 'email_verified', false);
    update_user_meta($user_id, 'investor_type', 'subscriber');
    if (!wiz_send_verification_email($user_id, $email, $token))
        return array('success' => false, 'message' => 'Account created, but verification email could not be sent. Please contact support.');
    return array('success' => true, 'message' => 'Registration successful! Please check your email to verify your account.', 'user_id' => $user_id);
}

function wiz_verify_email_token($token) {
    $users = get_users(array('meta_key' => 'verification_token', 'meta_value' => $token));
    if (empty($users)) return array('success' => false, 'message' => 'Invalid or expired verification link.');
    $user = $users[0];
    if (get_user_meta($user->ID, 'email_verified', true))
        return array('success' => false, 'message' => 'Email already verified. Please log in.');
    update_user_meta($user->ID, 'email_verified', true);
    delete_user_meta($user->ID, 'verification_token');
    return array('success' => true, 'message' => 'Email verified successfully! You can now log in.', 'user_id' => $user->ID);
}

function wiz_request_password_reset($email) {
    if (!is_email($email)) return array('success' => false, 'message' => 'Invalid email address.');
    $user = get_user_by('email', $email);
    if (!$user) return array('success' => true, 'message' => 'If that email exists, you will receive a password reset link.');
    $token  = wiz_generate_token();
    $expiry = time() + HOUR_IN_SECONDS;
    update_user_meta($user->ID, 'password_reset_token', $token);
    update_user_meta($user->ID, 'password_reset_expiry', $expiry);
    if (!wiz_send_password_reset_email($email, $token))
        return array('success' => false, 'message' => 'Could not send reset email. Please try again.');
    return array('success' => true, 'message' => 'If that email exists, you will receive a password reset link.');
}

function wiz_verify_password_reset_token($token) {
    $users = get_users(array('meta_key' => 'password_reset_token', 'meta_value' => $token));
    if (empty($users)) return array('success' => false, 'message' => 'Invalid or expired reset link.');
    $user   = $users[0];
    $expiry = get_user_meta($user->ID, 'password_reset_expiry', true);
    if (time() > $expiry) return array('success' => false, 'message' => 'This reset link has expired.');
    return array('success' => true, 'user_id' => $user->ID);
}

function wiz_reset_password_with_token($token, $new_password, $confirm_password) {
    if (empty($new_password) || empty($confirm_password))
        return array('success' => false, 'message' => 'Password fields cannot be empty.');
    if (strlen($new_password) < 8)
        return array('success' => false, 'message' => 'Password must be at least 8 characters.');
    if ($new_password !== $confirm_password)
        return array('success' => false, 'message' => 'Passwords do not match.');
    $verify = wiz_verify_password_reset_token($token);
    if (!$verify['success']) return $verify;
    $user_id = $verify['user_id'];
    // Preserve email_verified before wp_set_password clears auth cookies
    $email_verified = get_user_meta($user_id, 'email_verified', true);
    wp_set_password($new_password, $user_id);
    // Restore email_verified (wp_set_password does not clear it but we ensure it)
    update_user_meta($user_id, 'email_verified', $email_verified ?: true);
    delete_user_meta($user_id, 'password_reset_token');
    delete_user_meta($user_id, 'password_reset_expiry');
    return array('success' => true, 'message' => 'Password reset successfully! You can now log in.', 'user_id' => $user_id);
}

function wiz_enqueue_auth_scripts() {
    $auth_templates = array('page-login.php','page-register.php','page-dashboard.php','page-forgot-password.php','page-reset-password.php','page-edit-profile.php','page-change-password.php');
    foreach ($auth_templates as $t) {
        if (is_page_template($t)) {
            wp_enqueue_style('wiz-auth', get_template_directory_uri() . '/assets/css/auth.css', array(), filemtime(get_stylesheet_directory() . '/assets/css/auth.css'));
            wp_enqueue_script('wiz-auth', get_template_directory_uri() . '/assets/js/auth.js', array(), '1.0', true);
            break;
        }
    }
}
add_action('wp_enqueue_scripts', 'wiz_enqueue_auth_scripts');

// ===================== CONTACT FORM AJAX =====================

function wiz_ajax_contact() {
    if (!isset($_POST['wiz_contact_nonce']) || !wp_verify_nonce($_POST['wiz_contact_nonce'], 'wiz_contact_action')) {
        wp_send_json_error('Security check failed. Please refresh and try again.');
    }
    $name    = sanitize_text_field($_POST['name'] ?? '');
    $email   = sanitize_email($_POST['email'] ?? '');
    $subject = sanitize_text_field($_POST['subject'] ?? 'General Enquiry');
    $message = sanitize_textarea_field($_POST['message'] ?? '');

    if (empty($name) || empty($email) || empty($message)) {
        wp_send_json_error('Please complete all fields before submitting.');
    }
    if (!is_email($email)) {
        wp_send_json_error('Please enter a valid email address.');
    }

    // Save to WP admin
    $post_id = wp_insert_post(array(
        'post_title'   => '[' . $subject . '] from ' . $name,
        'post_type'    => 'contact_submission',
        'post_content' => $message,
        'post_status'  => 'private',
    ));
    if (is_wp_error($post_id)) {
        wp_send_json_error('Unable to save your message. Please try again.');
    }
    update_post_meta($post_id, 'contact_email', $email);
    update_post_meta($post_id, 'contact_name', $name);
    update_post_meta($post_id, 'contact_subject', $subject);

    // Email to admin (plain text)
    $admin_email   = get_option('admin_email');
    $admin_subject = 'New Contact: [' . $subject . '] from ' . $name;
    $admin_body    = "You have received a new message via WizInvestments.\n\n"
                   . "Name:    " . $name . "\n"
                   . "Email:   " . $email . "\n"
                   . "Subject: " . $subject . "\n"
                   . "Message:\n" . $message . "\n\n"
                   . "---\nReply directly to this email to respond.";
    wp_mail($admin_email, $admin_subject, $admin_body, array('Reply-To: ' . $name . ' <' . $email . '>'));

    // Confirmation email to sender (HTML)
    $headers = array(
        'Content-Type: text/html; charset=UTF-8',
        'From: WizInvestments <' . $admin_email . '>',
    );
    $confirm_subject = 'We received your message — WizInvestments';
    $confirm_body    = '<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<style>
  body { margin: 0; padding: 0; background-color: #0d1117; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; }
  .wrapper { max-width: 580px; margin: 0 auto; padding: 40px 20px; }
  .card { background: #161b22; border: 1px solid #30363d; border-radius: 12px; overflow: hidden; }
  .header { background: #161b22; border-bottom: 1px solid #30363d; padding: 32px 40px; text-align: center; }
  .logo { font-size: 24px; font-weight: 800; color: #e6edf3; letter-spacing: -0.02em; }
  .logo span { color: #2962ff; }
  .body { padding: 40px; }
  .greeting { font-size: 22px; font-weight: 700; color: #e6edf3; margin: 0 0 12px; }
  .text { font-size: 15px; color: #8b949e; line-height: 1.7; margin: 0 0 24px; }
  .summary { background: #0d1117; border: 1px solid #30363d; border-radius: 8px; padding: 20px 24px; margin: 24px 0; }
  .summary-row { display: flex; padding: 8px 0; border-bottom: 1px solid #21262d; }
  .summary-row:last-child { border-bottom: none; }
  .summary-label { font-size: 12px; font-weight: 600; color: #484f58; text-transform: uppercase; letter-spacing: 0.08em; width: 80px; flex-shrink: 0; padding-top: 2px; }
  .summary-value { font-size: 14px; color: #e6edf3; flex: 1; word-break: break-word; }
  .message-box { background: #0d1117; border: 1px solid #30363d; border-left: 3px solid #2962ff; border-radius: 8px; padding: 16px 20px; margin: 20px 0; font-size: 14px; color: #8b949e; line-height: 1.7; white-space: pre-wrap; }
  .cta { text-align: center; margin: 32px 0 8px; }
  .btn { display: inline-block; background: #2962ff; color: #ffffff; text-decoration: none; padding: 14px 32px; border-radius: 8px; font-size: 15px; font-weight: 600; letter-spacing: -0.01em; }
  .footer { padding: 24px 40px; text-align: center; border-top: 1px solid #30363d; }
  .footer-text { font-size: 12px; color: #484f58; line-height: 1.6; margin: 0; }
  .footer-text a { color: #2962ff; text-decoration: none; }
</style>
</head>
<body>
<div class="wrapper">
  <div class="card">
    <div class="header">
      <div class="logo">Wiz<span>Investments</span></div>
    </div>
    <div class="body">
      <p class="greeting">Thanks, ' . esc_html($name) . '!</p>
      <p class="text">We have received your message and will get back to you within 24 hours on business days. Here is a summary of what you sent us:</p>
      <div class="summary">
        <div class="summary-row">
          <span class="summary-label">Name</span>
          <span class="summary-value">' . esc_html($name) . '</span>
        </div>
        <div class="summary-row">
          <span class="summary-label">Email</span>
          <span class="summary-value">' . esc_html($email) . '</span>
        </div>
        <div class="summary-row">
          <span class="summary-label">Subject</span>
          <span class="summary-value">' . esc_html($subject) . '</span>
        </div>
      </div>
      <div class="message-box">' . esc_html($message) . '</div>
      <p class="text">While you wait, feel free to explore our analytics dashboard or learn more about our services.</p>
      <div class="cta">
        <a href="' . esc_url(home_url('/analytics-dashboard/')) . '" class="btn">Explore Analytics</a>
      </div>
    </div>
    <div class="footer">
      <p class="footer-text">
        You are receiving this because you submitted a message on <a href="' . esc_url(home_url()) . '">WizInvestments</a>.<br>
        &copy; ' . date('Y') . ' WizInvestments &nbsp;&bull;&nbsp;
        <a href="' . esc_url(home_url('/privacy-policy/')) . '">Privacy Policy</a> &nbsp;&bull;&nbsp;
        <a href="' . esc_url(home_url('/terms-of-service/')) . '">Terms of Service</a>
      </p>
    </div>
  </div>
</div>
</body>
</html>';

    wp_mail($email, $confirm_subject, $confirm_body, $headers);

    wp_send_json_success('Thanks, ' . esc_html($name) . '! Your message has been received. Check your inbox for a confirmation email.');
}
add_action('wp_ajax_wiz_contact', 'wiz_ajax_contact');
add_action('wp_ajax_nopriv_wiz_contact', 'wiz_ajax_contact');

// ===================== PASSWORD RESET AJAX =====================

function wiz_ajax_reset_password() {
    if (!isset($_POST['wiz_nonce']) || !wp_verify_nonce($_POST['wiz_nonce'], 'wiz_reset_nonce')) {
        wp_send_json_error('Security check failed. Please refresh and try again.');
    }
    $token            = sanitize_text_field($_POST['token'] ?? '');
    $new_password     = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($token)) {
        wp_send_json_error('Invalid reset token.');
    }

    $result = wiz_reset_password_with_token($token, $new_password, $confirm_password);

    if ($result['success']) {
        // Log the user back in automatically after reset
        wp_set_current_user($result['user_id']);
        wp_set_auth_cookie($result['user_id'], false);
        wp_send_json_success('Password reset successfully! Logging you in...');
    } else {
        wp_send_json_error($result['message']);
    }
}
add_action('wp_ajax_wiz_reset_password', 'wiz_ajax_reset_password');
add_action('wp_ajax_nopriv_wiz_reset_password', 'wiz_ajax_reset_password');

// ===================== FORGOT PASSWORD AJAX =====================

function wiz_ajax_forgot_password() {
    if (!isset($_POST['wiz_nonce']) || !wp_verify_nonce($_POST['wiz_nonce'], 'wiz_forgot_nonce')) {
        wp_send_json_error('Security check failed. Please refresh and try again.');
    }
    $email  = sanitize_email($_POST['email'] ?? '');
    $result = wiz_request_password_reset($email);
    if ($result['success']) {
        wp_send_json_success($result['message']);
    } else {
        wp_send_json_error($result['message']);
    }
}
add_action('wp_ajax_wiz_forgot_password', 'wiz_ajax_forgot_password');
add_action('wp_ajax_nopriv_wiz_forgot_password', 'wiz_ajax_forgot_password');

// ===================== FAVICON =====================

function wiz_add_favicon() {
    $base = get_template_directory_uri() . '/assets/img/';
    echo '<link rel="icon" type="image/png" sizes="32x32" href="' . esc_url($base . 'favicon-32.png') . '">' . "\n";
    echo '<link rel="icon" type="image/png" sizes="64x64" href="' . esc_url($base . 'favicon.png') . '">' . "\n";
    echo '<link rel="apple-touch-icon" sizes="180x180" href="' . esc_url($base . 'favicon-180.png') . '">' . "\n";
    echo '<link rel="icon" type="image/svg+xml" href="' . esc_url($base . 'favicon.svg') . '">' . "\n";
}
add_action('wp_head', 'wiz_add_favicon');
add_action('admin_head', 'wiz_add_favicon');

// Localize nonces for JS
function wiz_localize_nonces() {
    $nonce_data = array(
        'data'      => wp_create_nonce('wiz_data_nonce'),
        'portfolio' => wp_create_nonce('wiz_portfolio_nonce'),
        'ajaxUrl'   => admin_url('admin-ajax.php'),
    );
    wp_localize_script('wiz-analytics', 'wizNonces', $nonce_data);
}
add_action('wp_enqueue_scripts', 'wiz_localize_nonces', 20);

// Output wizNonces inline in wp_head so it is always available regardless of script enqueue
function wiz_inline_nonces() {
    $nonce_data = array(
        'data'      => wp_create_nonce('wiz_data_nonce'),
        'portfolio' => wp_create_nonce('wiz_portfolio_nonce'),
        'ajaxUrl'   => admin_url('admin-ajax.php'),
    );
    $json = wp_json_encode($nonce_data);
    echo "<script>window.wizNonces = {$json};</script>
";
}
add_action('wp_head', 'wiz_inline_nonces');
