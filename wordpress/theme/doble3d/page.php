<?php
/**
 * Template para Páginas (privacidad, términos, etc.)
 */
if (!defined('ABSPATH')) { exit; }
get_header();

while (have_posts()) : the_post(); ?>
  <div class="container">
    <header class="page-head">
      <div class="page-eyebrow">Doble 3D</div>
      <h1 class="page-title"><?php the_title(); ?></h1>
      <?php if (get_the_modified_date()) : ?>
        <div class="page-meta">
          <span>Última actualización: <?php echo esc_html(get_the_modified_date('d.m.Y')); ?></span>
        </div>
      <?php endif; ?>
    </header>

    <article class="prose">
      <?php the_content(); ?>
    </article>

    <div style="max-width:760px;margin:0 auto;">
      <a class="back-link" href="<?php echo esc_url(home_url('/')); ?>">← Volver al inicio</a>
    </div>
  </div>
<?php endwhile;

get_footer();
