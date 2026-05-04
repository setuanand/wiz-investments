<!doctype html>
<html <?php language_attributes(); ?>>
<head>
  <meta charset="<?php bloginfo( 'charset' ); ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
  <header class="site-header site">
    <div class="site-branding">
      <?php if (function_exists('the_custom_logo') && has_custom_logo()) : ?>
        <div class="site-logo"><?php the_custom_logo(); ?></div>
      <?php endif; ?>
      <div>
        <a class="site-title" href="<?php echo esc_url(home_url('/')); ?>"><?php bloginfo('name'); ?></a>
        <p class="site-description"><?php bloginfo('description'); ?></p>
      </div>
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
  </header>
  <main class="site">
