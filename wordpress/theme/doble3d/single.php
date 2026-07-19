<?php
/**
 * Template para entradas individuales del blog.
 */
if (!defined('ABSPATH')) { exit; }
get_header();

while (have_posts()) : the_post();
    $cats = get_the_category();
    $cat  = !empty($cats) ? $cats[0] : null;
    $cat_name = $cat ? $cat->name : 'Blog';
    $cat_link = $cat ? get_category_link($cat) : home_url('/blog/');

    $d3d_words = str_word_count(wp_strip_all_tags(get_the_content()));
    $d3d_rt    = max(1, (int) round($d3d_words / 200));
?>
  <div class="container">
    <header class="page-head">
      <nav class="crumbs" aria-label="Migas de pan">
        <a href="<?php echo esc_url(home_url('/')); ?>">Inicio</a>
        <span class="crumbs-sep" aria-hidden="true">›</span>
        <a href="<?php echo esc_url(home_url('/blog/')); ?>">Blog</a>
        <span class="crumbs-sep" aria-hidden="true">›</span>
        <a href="<?php echo esc_url($cat_link); ?>"><?php echo esc_html($cat_name); ?></a>
      </nav>
      <div class="page-eyebrow"><?php echo esc_html($cat_name); ?></div>
      <h1 class="page-title"><?php the_title(); ?></h1>
      <div class="page-meta">
        <span><?php echo esc_html(get_the_date('d.m.Y')); ?></span>
        <?php if (get_the_author()) : ?>
          <span class="sep"></span>
          <span><?php the_author(); ?></span>
        <?php endif; ?>
        <span class="sep"></span>
        <span><?php echo esc_html($d3d_rt); ?> min de lectura</span>
      </div>
    </header>

    <?php if (has_post_thumbnail()) : ?>
      <div class="prose" style="margin-bottom:40px;">
        <?php the_post_thumbnail('large', ['style' => 'width:100%;border-radius:6px;border:1px solid var(--border-s);']); ?>
      </div>
    <?php endif; ?>

    <article class="prose">
      <?php the_content(); ?>
    </article>

    <aside class="post-services" aria-label="Servicios de Doble 3D">
      <div class="post-services-eyebrow">Cómo lo aplicamos en Doble 3D</div>
      <div class="post-services-grid">
        <a class="post-service" href="<?php echo esc_url(home_url('/servicios/realidad-virtual/')); ?>">
          <span class="post-service-title">Realidad Virtual industrial</span>
          <span class="post-service-desc">Simuladores VR de armado y desarme sobre tus equipos críticos.</span>
        </a>
        <a class="post-service" href="<?php echo esc_url(home_url('/servicios/animacion-3d/')); ?>">
          <span class="post-service-title">Animación 3D técnica</span>
          <span class="post-service-desc">Videos que explican procesos complejos en menos de tres minutos.</span>
        </a>
        <a class="post-service" href="<?php echo esc_url(home_url('/servicios/gamificacion-scorm/')); ?>">
          <span class="post-service-title">Apps gamificadas SCORM</span>
          <span class="post-service-desc">Entrenamiento con tracking SCORM 2004 / xAPI para tu LMS.</span>
        </a>
        <a class="post-service" href="<?php echo esc_url(home_url('/servicios/core/')); ?>">
          <span class="post-service-title">Plataforma CORE</span>
          <span class="post-service-desc">Gestión centralizada de entrenamiento VR y analítica en vivo.</span>
        </a>
      </div>
      <a class="post-services-cta" href="<?php echo esc_url(home_url('/#contacto')); ?>">Hablemos de tu operación →</a>
    </aside>

    <?php
      $d3d_related_args = [
          'post_type'           => 'post',
          'posts_per_page'      => 3,
          'post__not_in'        => [get_the_ID()],
          'ignore_sticky_posts' => 1,
          'no_found_rows'       => true,
      ];
      if ($cat) { $d3d_related_args['cat'] = $cat->term_id; }
      $d3d_related = new WP_Query($d3d_related_args);
      if ($d3d_related->have_posts()) : ?>
      <section class="related-posts" aria-label="Artículos relacionados">
        <div class="related-posts-head">Sigue leyendo</div>
        <div class="post-list">
          <?php while ($d3d_related->have_posts()) : $d3d_related->the_post();
              get_template_part('template-parts/post-row');
          endwhile; ?>
        </div>
      </section>
      <?php wp_reset_postdata(); endif; ?>

    <?php
      $d3d_prev = get_previous_post();
      $d3d_next = get_next_post();
      if ($d3d_prev || $d3d_next) : ?>
      <nav class="post-nav" aria-label="Más entradas">
        <?php if ($d3d_prev) : ?>
          <a class="post-nav-item prev" href="<?php echo esc_url(get_permalink($d3d_prev)); ?>">
            <span class="post-nav-dir">← Anterior</span>
            <span class="post-nav-title"><?php echo esc_html(wp_strip_all_tags(get_the_title($d3d_prev))); ?></span>
          </a>
        <?php else : ?><span></span><?php endif; ?>
        <?php if ($d3d_next) : ?>
          <a class="post-nav-item next" href="<?php echo esc_url(get_permalink($d3d_next)); ?>">
            <span class="post-nav-dir">Siguiente →</span>
            <span class="post-nav-title"><?php echo esc_html(wp_strip_all_tags(get_the_title($d3d_next))); ?></span>
          </a>
        <?php else : ?><span></span><?php endif; ?>
      </nav>
    <?php endif; ?>

    <div style="max-width:760px;margin:0 auto;">
      <a class="back-link" href="<?php echo esc_url(home_url('/blog/')); ?>">← Volver al blog</a>
    </div>
  </div>
<?php endwhile;

get_footer();
