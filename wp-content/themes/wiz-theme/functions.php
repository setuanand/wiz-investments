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
    wp_page_menu(array(
        'menu_class' => 'menu',
        'show_home' => true,
    ));
}

function wiz_register_contact_submission_cpt() {
    register_post_type('contact_submission', array(
        'labels' => array(
            'name' => __('Contact Submissions', 'wiz-theme'),
            'singular_name' => __('Contact Submission', 'wiz-theme'),
        ),
        'public' => false,
        'show_ui' => true,
        'has_archive' => false,
        'supports' => array('title', 'editor'),
    ));
}
add_action('init', 'wiz_register_contact_submission_cpt');

function wiz_process_contact_submission() {
    if (!isset($_POST['wiz_contact_submit'])) {
        return '';
    }

    if (!isset($_POST['wiz_contact_nonce']) || !wp_verify_nonce($_POST['wiz_contact_nonce'], 'wiz_contact_action')) {
        return '<div class="form-notice error">Security check failed. Please refresh and try again.</div>';
    }

    $name = sanitize_text_field($_POST['name'] ?? '');
    $email = sanitize_email($_POST['email'] ?? '');
    $message = sanitize_textarea_field($_POST['message'] ?? '');

    if (empty($name) || empty($email) || empty($message)) {
        return '<div class="form-notice error">Please complete all fields before submitting.</div>';
    }

    $post_id = wp_insert_post(array(
        'post_title' => 'Contact from ' . $name,
        'post_type' => 'contact_submission',
        'post_content' => $message,
        'post_status' => 'private',
    ));

    if (is_wp_error($post_id)) {
        return '<div class="form-notice error">Unable to save your message right now. Please try again later.</div>';
    }

    update_post_meta($post_id, 'contact_email', $email);
    update_post_meta($post_id, 'contact_name', $name);

    $admin_email = get_option('admin_email');
    wp_mail($admin_email, 'New Contact Message from ' . $name, $message . "\n\nEmail: " . $email);

    return '<div class="form-notice success">Thanks, ' . esc_html($name) . '! Your message has been received. We will reply soon.</div>';
}

function wiz_get_page_id_by_slug($slug) {
    $page = get_page_by_path($slug);
    return $page ? $page->ID : 0;
}

function wiz_create_default_pages() {
    if (current_user_can('manage_options')) {
        $pages = array(
            array('title' => 'About', 'slug' => 'about', 'template' => 'page-about.php'),
            array('title' => 'Services', 'slug' => 'services', 'template' => 'page-services.php'),
            array('title' => 'Contact', 'slug' => 'contact', 'template' => 'page-contact.php'),
            array('title' => 'Analytics Dashboard', 'slug' => 'analytics-dashboard', 'template' => 'page-analytics.php'),
        );

        foreach ($pages as $page_data) {
            if (!wiz_get_page_id_by_slug($page_data['slug'])) {
                $page_id = wp_insert_post(array(
                    'post_title' => $page_data['title'],
                    'post_name' => $page_data['slug'],
                    'post_status' => 'publish',
                    'post_type' => 'page',
                    'post_content' => '',
                ));

                if (!is_wp_error($page_id) && $page_data['template']) {
                    update_post_meta($page_id, '_wp_page_template', $page_data['template']);
                }
            }
        }
    }
}
add_action('init', 'wiz_create_default_pages');
