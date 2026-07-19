<?php
/**
 * Template Name: Servicio
 *
 * Página de servicio con Service schema (JSON-LD) + enlaces internos.
 * Asignar desde el editor de páginas: Atributos de página → Plantilla → "Servicio".
 * El nombre/descripción del Service se derivan del título y extracto de la página,
 * así que no requiere configuración por-página.
 */
if (!defined('ABSPATH')) { exit; }
get_header();

while (have_posts()) : the_post();

    $svc_name = wp_strip_all_tags(get_the_title());
    $svc_desc = has_excerpt()
        ? wp_strip_all_tags(get_the_excerpt())
        : wp_trim_words(wp_strip_all_tags(get_the_content()), 40, '…');

    // Service schema. provider enlaza a la Organization que emite Yoast (#organization).
    $svc_schema = [
        '@context'    => 'https://schema.org',
        '@type'       => 'Service',
        'name'        => $svc_name,
        'serviceType' => $svc_name,
        'url'         => get_permalink(),
        'description' => $svc_desc,
        'provider'    => ['@id' => home_url('/') . '#organization'],
        'areaServed'  => [
            ['@type' => 'Country', 'name' => 'Chile'],
            ['@type' => 'Country', 'name' => 'Perú'],
            ['@type' => 'Country', 'name' => 'Argentina'],
            ['@type' => 'Country', 'name' => 'Colombia'],
            ['@type' => 'Country', 'name' => 'México'],
        ],
    ];
    echo '<script type="application/ld+json">' . wp_json_encode($svc_schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . '</script>' . "\n";

    // Otros servicios para el bloque de enlaces internos (excluye el actual por slug).
    $svc_links = [
        'realidad-virtual'   => ['Realidad Virtual industrial', 'Simuladores VR de armado y desarme'],
        'animacion-3d'       => ['Animación 3D técnica', 'Videos que explican procesos complejos'],
        'gamificacion-scorm' => ['Apps gamificadas SCORM', 'Entrenamiento con tracking para tu LMS'],
        'core'               => ['Plataforma CORE', 'Gestión centralizada de entrenamiento VR'],
    ];
    $current_slug = get_post_field('post_name', get_the_ID());
?>
  <div class="container">
    <header class="page-head">
      <nav class="crumbs" aria-label="Migas de pan">
        <a href="<?php echo esc_url(home_url('/')); ?>">Inicio</a>
        <span class="crumbs-sep" aria-hidden="true">›</span>
        <a href="<?php echo esc_url(home_url('/servicios/')); ?>">Servicios</a>
      </nav>
      <div class="page-eyebrow">Servicios</div>
      <h1 class="page-title"><?php the_title(); ?></h1>
    </header>

    <?php if (has_post_thumbnail()) : ?>
      <div class="prose" style="margin-bottom:40px;">
        <?php the_post_thumbnail('large', ['style' => 'width:100%;border-radius:6px;border:1px solid var(--border-s);']); ?>
      </div>
    <?php endif; ?>

    <article class="prose">
      <?php the_content(); ?>
    </article>

    <aside class="post-services" aria-label="Otros servicios de Doble 3D">
      <div class="post-services-eyebrow">Otros servicios</div>
      <div class="post-services-grid">
        <?php foreach ($svc_links as $slug => $info) :
            if ($slug === $current_slug) { continue; } ?>
          <a class="post-service" href="<?php echo esc_url(home_url('/servicios/' . $slug . '/')); ?>">
            <span class="post-service-title"><?php echo esc_html($info[0]); ?></span>
            <span class="post-service-desc"><?php echo esc_html($info[1]); ?></span>
          </a>
        <?php endforeach; ?>
      </div>
      <a class="post-services-cta" href="<?php echo esc_url(home_url('/#contacto')); ?>">Hablemos de tu operación →</a>
    </aside>

    <div style="max-width:760px;margin:0 auto;">
      <a class="back-link" href="<?php echo esc_url(home_url('/')); ?>">← Volver al inicio</a>
    </div>
  </div>
<?php endwhile;

get_footer();
