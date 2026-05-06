<!doctype html>
<html <?php language_attributes(); ?>>
<head>
  <meta charset="<?php bloginfo( 'charset' ); ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
  <header class="site-header">
    <div class="header-container">
      <div class="site-branding">
        <?php if (function_exists('the_custom_logo') && has_custom_logo()) : ?>
          <div class="site-logo"><?php the_custom_logo(); ?></div>
        <?php endif; ?>
        <a class="site-title" href="<?php echo esc_url(home_url('/')); ?>"><?php bloginfo('name'); ?></a>
      </div>
      <nav class="site-nav">
        <?php
        wp_nav_menu(array(
          'theme_location' => 'primary',
          'menu_class'     => 'menu',
          'container'      => false,
          'fallback_cb'    => 'wiz_theme_menu_fallback',
        ));
        ?>
      </nav>
      <div class="auth-nav">
        <?php if (is_user_logged_in()) : ?>
          <div class="user-menu">
            <span class="user-name"><?php echo esc_html(wp_get_current_user()->user_email); ?></span>
            <a href="<?php echo esc_url(wiz_get_page_url_by_slug('dashboard')); ?>" class="btn btn-sm btn-primary">Dashboard</a>
            <a href="<?php echo esc_url(wp_logout_url(home_url())); ?>" class="btn btn-sm btn-secondary">Logout</a>
          </div>
        <?php else : ?>
          <div class="guest-menu">
            <a href="<?php echo esc_url(wiz_get_page_url_by_slug('login')); ?>" class="btn btn-sm btn-secondary">Login</a>
            <a href="<?php echo esc_url(wiz_get_page_url_by_slug('register')); ?>" class="btn btn-sm btn-primary">Register</a>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </header>
  <main class="site">
