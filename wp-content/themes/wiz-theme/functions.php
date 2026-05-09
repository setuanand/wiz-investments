<?php
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
    wp_set_user_password($verify['user_id'], $new_password);
    delete_user_meta($verify['user_id'], 'password_reset_token');
    delete_user_meta($verify['user_id'], 'password_reset_expiry');
    return array('success' => true, 'message' => 'Password reset successfully! You can now log in.');
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
