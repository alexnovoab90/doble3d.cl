<?php
/**
 * Template Name: Blog Doble 3D
 * Plantilla para la página "Blog". Ignora el contenido de la página (queda basura
 * de Visual Composer/Salient) y lista las entradas reales con paginación.
 * Asignar a la página /blog/ desde Editor de página → Atributos → Plantilla.
 */
if (!defined('ABSPATH')) { exit; }
get_header();

$d3d_paged = max(1, get_query_var('paged'), get_query_var('page'));
$d3d_q = new WP_Query([
  'post_type'      => 'post',
  'post_status'    => 'publish',
  'posts_per_page' => 10,
  'paged'          => $d3d_paged,
]);
?>
  <div class="container">
    <header class="page-head">
      <div class="page-eyebrow">Tecno&nbsp;Blog</div>
      <h1 class="page-title"><?php echo esc_html(wp_strip_all_tags(get_the_title())); ?></h1>
      <div class="page-meta"><span>Realidad virtual, animación 3D e IA aplicada a la industria</span></div>
    </header>

    <?php if ($d3d_q->have_posts()) : ?>
      <div class="post-list">
        <?php while ($d3d_q->have_posts()) : $d3d_q->the_post();
          get_template_part('template-parts/post-row');
        endwhile; ?>
      </div>

      <div style="max-width:820px;margin:48px auto 0;">
        <?php echo paginate_links([
          'total'     => $d3d_q->max_num_pages,
          'current'   => $d3d_paged,
          'mid_size'  => 1,
          'prev_text' => '← Anteriores',
          'next_text' => 'Siguientes →',
        ]); ?>
      </div>
    <?php else : ?>
      <div class="prose">
        <p>Aún no hay publicaciones. Vuelve pronto o escríbenos por WhatsApp.</p>
      </div>
    <?php endif; wp_reset_postdata(); ?>

    <div style="max-width:820px;margin:0 auto;">
      <a class="back-link" href="<?php echo esc_url(home_url('/')); ?>">← Volver al inicio</a>
    </div>
  </div>
<?php
get_footer();
