<?php get_header(); ?>

<section class="page-content">
  <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
    <h1><?php the_title(); ?></h1>
    <div class="page-body"><?php the_content(); ?></div>
  <?php endwhile; endif; ?>
</section>

<?php get_footer(); ?>
