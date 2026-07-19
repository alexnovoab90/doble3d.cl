<?php
/**
 * Página de entradas del blog (cuando se asigna en Ajustes → Lectura → Página de entradas).
 * Si en su lugar usas una página con la plantilla "Blog Doble 3D", se usa template-blog.php.
 */
if (!defined('ABSPATH')) { exit; }
get_header();

$d3d_blog_title = single_post_title('', false) ?: 'Blog';
?>
  <div class="container">
    <header class="page-head">
      <div class="page-eyebrow">Tecno&nbsp;Blog</div>
      <h1 class="page-title"><?php echo esc_html(wp_strip_all_tags($d3d_blog_title)); ?></h1>
      <div class="page-meta"><span>Realidad virtual, animación 3D e IA aplicada a la industria</span></div>
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
        <p>Aún no hay publicaciones. Vuelve pronto o escríbenos por WhatsApp.</p>
      </div>
    <?php endif; ?>

    <div style="max-width:820px;margin:0 auto;">
      <a class="back-link" href="<?php echo esc_url(home_url('/')); ?>">← Volver al inicio</a>
    </div>
  </div>
<?php
get_footer();
