<?php
/**
 * Fallback general + listado de entradas (blog, búsqueda, fallback).
 * front-page.php cubre la portada; archive.php cubre archivos; este es el resto.
 */
if (!defined('ABSPATH')) { exit; }
get_header();

// Título contextual
if (is_search()) {
    $d3d_head = 'Resultados para: ' . get_search_query();
    $d3d_eyebrow = 'Búsqueda';
} elseif (is_home()) {
    $d3d_head = single_post_title('', false) ?: 'Blog';
    $d3d_eyebrow = 'Publicaciones';
} else {
    $d3d_head = get_the_archive_title();
    $d3d_eyebrow = 'Archivo';
}
?>
  <div class="container">
    <header class="page-head">
      <div class="page-eyebrow"><?php echo esc_html(wp_strip_all_tags($d3d_eyebrow)); ?></div>
      <h1 class="page-title"><?php echo esc_html(wp_strip_all_tags($d3d_head)); ?></h1>
    </header>

    <?php if (have_posts()) : ?>
      <div class="post-list">
        <?php while (have_posts()) : the_post();
          get_template_part('template-parts/post-row');
        endwhile; ?>
      </div>

      <div style="max-width:820px;margin:48px auto 0;">
        <?php the_posts_pagination([
          'mid_size'  => 1,
          'prev_text' => '← Anteriores',
          'next_text' => 'Siguientes →',
        ]); ?>
      </div>
    <?php else : ?>
      <div class="prose">
        <p>No encontramos publicaciones. Vuelve al <a href="<?php echo esc_url(home_url('/')); ?>">inicio</a> o escríbenos por WhatsApp.</p>
      </div>
    <?php endif; ?>

    <div style="max-width:820px;margin:0 auto;">
      <a class="back-link" href="<?php echo esc_url(home_url('/')); ?>">← Volver al inicio</a>
    </div>
  </div>
<?php
get_footer();
