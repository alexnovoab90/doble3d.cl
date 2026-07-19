<?php
/**
 * Doble 3D — Front Page
 * Servido desde el theme propio `doble3d` (sin Salient).
 */
if (!defined('ABSPATH')) { exit; }

$year       = date('Y');
$page_url   = home_url('/');
$assets_url = get_stylesheet_directory_uri() . '/assets';
$og_image   = $assets_url . '/og-doble3d.jpg';

// WhatsApp CTAs — mensajes segmentados por sección (pre-rellenados, URL-encoded)
$wa_n    = '56958015971';
$wa_base = 'https://wa.me/' . $wa_n . '?text=';

$wa_hero  = $wa_base . rawurlencode("Hola Doble 3D,\n\nVengo desde la web y me gustaría ver una demo VR. ¿Tienen disponibilidad esta semana?\n\n— Enviado desde: Home");

$wa_serv  = $wa_base . rawurlencode("Hola Doble 3D,\n\nEstoy evaluando un proyecto de [VR / Animación 3D / App gamificada].\n\nContexto breve:\n- Empresa:\n- Industria:\n- Cantidad de usuarios aprox:\n- Fecha estimada:\n\n¿Podemos coordinar una cotización?\n\n— Enviado desde: Servicios");

$wa_core  = $wa_base . rawurlencode("Hola Doble 3D,\n\nMe interesa la plataforma CORE. ¿Podrían enviarme el pitch deck y precios por aquí?\n\nPienso evaluarlo con mi equipo antes de agendar una reunión.\n\n— Enviado desde: CORE");

$wa_tiers = $wa_base . rawurlencode("Hola Doble 3D,\n\nVi los planes de CORE y quiero entender cuál se ajusta a mi operación.\n\n¿Podemos agendar 15 minutos esta semana?\n\n— Enviado desde: Precios");

$wa_faq   = $wa_base . rawurlencode("Hola Doble 3D,\n\nMe gustaría contarles sobre mi operación y ver qué solución podría encajar.\n\nTe aviso por acá cuando tenga un rato.\n\n— Enviado desde: FAQ");

$wa_reel  = $wa_base . rawurlencode("Hola Doble 3D,\n\nVi el reel en la web. Me gustaría recibir un caso real documentado (resultados, métricas, proceso) de un cliente similar al mío.\n\nMi industria: [Minería / Manufactura / Otra]\n\n— Enviado desde: Reel");

$wa_float = $wa_base . rawurlencode("Hola Doble 3D, vengo desde el sitio. Quiero hacer una consulta.");

// ============ BLOG POSTS — fetch desde WordPress, cacheado 1h ============
// Solo corre si estamos dentro de WP; fuera de WP se hide la sección silenciosamente.
$blog_posts = [];
if (function_exists('get_transient') && class_exists('WP_Query')) {
    $cache_key = 'd3d_landing_blog_posts_v2';
    $blog_posts = get_transient($cache_key);
    if ($blog_posts === false) {
        $q = new WP_Query([
            'posts_per_page'      => 3,
            'post_status'         => 'publish',
            'orderby'             => 'date',
            'order'               => 'DESC',
            'ignore_sticky_posts' => true,
            'no_found_rows'       => true,
        ]);
        $blog_posts = [];
        while ($q->have_posts()) {
            $q->the_post();
            $pid   = get_the_ID();
            $cats  = get_the_category($pid);
            $cat   = !empty($cats) ? $cats[0]->name : 'Blog';
            $thumb = function_exists('get_the_post_thumbnail_url') ? get_the_post_thumbnail_url($pid, 'medium_large') : '';
            $exc   = function_exists('wp_trim_words') ? wp_trim_words(get_the_excerpt() ?: wp_strip_all_tags(get_the_content()), 22, '…') : '';
            // Limpia HTML crudo que a veces viene en títulos/excerpts de WP
            $clean_title = function_exists('wp_strip_all_tags') ? wp_strip_all_tags(get_the_title(), true) : strip_tags(get_the_title());
            $clean_title = trim(preg_replace('/\s+/', ' ', $clean_title));
            $clean_exc   = function_exists('wp_strip_all_tags') ? wp_strip_all_tags($exc, true) : strip_tags($exc);
            $clean_exc   = trim(preg_replace('/\s+/', ' ', $clean_exc));
            $blog_posts[] = [
                'title'   => $clean_title,
                'url'     => get_permalink(),
                'excerpt' => $clean_exc,
                'thumb'   => $thumb,
                'date'    => get_the_date('d.m.Y'),
                'cat'     => $cat,
            ];
        }
        wp_reset_postdata();
        set_transient($cache_key, $blog_posts, defined('HOUR_IN_SECONDS') ? HOUR_IN_SECONDS : 3600);
    }
}
?>
<!doctype html>
<html lang="es-CL">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<?php wp_site_icon(); ?>
<?php
// SEO meta tags: Yoast Premium maneja title, description, og: y twitter:. Si Yoast NO está activo, emitimos fallback.
if (!defined('WPSEO_VERSION')):
?>
<title>Doble 3D — Aprender haciendo</title>
<meta name="description" content="Entrenamiento industrial en VR, animación 3D y apps gamificadas con SCORM. No vendemos tecnología: vendemos cero incidencias.">
<meta property="og:type" content="website">
<meta property="og:url" content="<?php echo htmlspecialchars($page_url, ENT_QUOTES); ?>">
<meta property="og:site_name" content="Doble 3D">
<meta property="og:title" content="Doble 3D — Aprender haciendo">
<meta property="og:description" content="Entrenamiento industrial en VR, animación 3D y apps gamificadas con SCORM. No vendemos tecnología: vendemos cero incidencias.">
<meta property="og:image" content="<?php echo htmlspecialchars($og_image, ENT_QUOTES); ?>">
<meta property="og:image:alt" content="Doble 3D — Aprender haciendo">
<meta property="og:locale" content="es_CL">
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="Doble 3D — Aprender haciendo">
<meta name="twitter:description" content="Entrenamiento industrial en VR, animación 3D y apps gamificadas con SCORM.">
<meta name="twitter:image" content="<?php echo htmlspecialchars($og_image, ENT_QUOTES); ?>">
<?php endif; ?>
<link rel="preload" as="font" type="font/woff2" crossorigin href="<?php echo htmlspecialchars($assets_url, ENT_QUOTES); ?>/Geist-var.woff2">
<link rel="preload" as="font" type="font/woff2" crossorigin href="<?php echo htmlspecialchars($assets_url, ENT_QUOTES); ?>/IBMPlexMono-400.woff2">
<link rel="preload" as="font" type="font/woff2" crossorigin href="<?php echo htmlspecialchars($assets_url, ENT_QUOTES); ?>/IBMPlexMono-500.woff2">
<link rel="preload" as="image" type="image/avif" fetchpriority="high"
      imagesrcset="<?php echo htmlspecialchars($assets_url, ENT_QUOTES); ?>/heroe-1280.avif 1280w, <?php echo htmlspecialchars($assets_url, ENT_QUOTES); ?>/heroe-1920.avif 1920w, <?php echo htmlspecialchars($assets_url, ENT_QUOTES); ?>/heroe-2560.avif 2560w"
      imagesizes="100vw">
<style>
  /* Self-hosted fonts — single preload, zero render-blocking CSS chain */
  @font-face{
    font-family:'Geist';
    font-style:normal;
    font-weight:100 900;
    font-display:swap;
    src:url('<?php echo htmlspecialchars($assets_url, ENT_QUOTES); ?>/Geist-var.woff2') format('woff2-variations'),
        url('<?php echo htmlspecialchars($assets_url, ENT_QUOTES); ?>/Geist-var.woff2') format('woff2');
    font-stretch:100%;
  }
  @font-face{
    font-family:'IBM Plex Mono';
    font-style:normal;
    font-weight:400;
    font-display:swap;
    src:url('<?php echo htmlspecialchars($assets_url, ENT_QUOTES); ?>/IBMPlexMono-400.woff2') format('woff2');
    unicode-range:U+0000-00FF,U+0131,U+0152-0153,U+02BB-02BC,U+02C6,U+02DA,U+02DC,U+2000-206F,U+2074,U+20AC,U+2122,U+2191,U+2193,U+2212,U+2215,U+FEFF,U+FFFD;
  }
  @font-face{
    font-family:'IBM Plex Mono';
    font-style:normal;
    font-weight:400;
    font-display:swap;
    src:url('<?php echo htmlspecialchars($assets_url, ENT_QUOTES); ?>/IBMPlexMono-400-ext.woff2') format('woff2');
    unicode-range:U+0100-024F,U+1E00-1EFF,U+20A0-20AB,U+20AD-20CF,U+2C60-2C7F,U+A720-A7FF;
  }
  @font-face{
    font-family:'IBM Plex Mono';
    font-style:normal;
    font-weight:500;
    font-display:swap;
    src:url('<?php echo htmlspecialchars($assets_url, ENT_QUOTES); ?>/IBMPlexMono-500.woff2') format('woff2');
  }

  :root{
    --bg-0:#080808;
    --bg-1:#0f0f0f;
    --bg-2:#141414;
    --border-s:rgba(255,255,255,0.08);
    --border-m:rgba(255,255,255,0.14);
    --text:#d4d4d4;
    --white:#ffffff;
    --muted:#a3a3a3;
    --subtle:#808080;
    --accent:#7c3aed;
    --accent-light:#a78bfa;
    --sans:'Geist', ui-sans-serif, system-ui, sans-serif;
    --mono:'IBM Plex Mono', ui-monospace, 'SF Mono', Menlo, monospace;
  }
  *{box-sizing:border-box;margin:0;padding:0;}
  html,body{margin:0 !important;padding:0 !important;background:var(--bg-0);color:var(--text);font-family:var(--sans);font-weight:400;-webkit-font-smoothing:antialiased;text-rendering:optimizeLegibility;overflow-x:hidden;width:100%;}
  a{color:inherit;text-decoration:none;}
  button{font-family:inherit;background:none;border:0;color:inherit;cursor:pointer;}
  img{display:block;max-width:100%;}
  ::selection{background:var(--accent);color:#fff;}

  .mono{font-family:var(--mono);letter-spacing:0.08em;text-transform:uppercase;font-size:11px;font-weight:500;color:var(--muted);}
  .container{max-width:1280px;margin:0 auto;padding:0 40px;}

  /* ============ NAV: minimal, floats over hero ============ */
  /* padding-inline crece un poco en pantallas anchas, sin llegar a alinearse con el container */
  .nav{position:fixed;top:0;left:0;right:0;z-index:60;
       display:grid;grid-template-columns:1fr auto 1fr;align-items:center;
       padding:56px clamp(40px, 2.2vw, 64px) 28px;
       transition:background .3s ease, border-color .3s ease, padding .3s ease;border-bottom:1px solid transparent;}
  .nav.scrolled{background:rgba(8,8,8,0.85);backdrop-filter:blur(20px);-webkit-backdrop-filter:blur(20px);
                border-bottom-color:var(--border-s);
                padding:18px clamp(40px, 2.2vw, 64px);}
  .nav > .logo{justify-self:start;}
  .nav > .nav-links{justify-self:center;}
  .nav > .nav-right{justify-self:end;}
  .logo{display:inline-flex;align-items:center;color:var(--white);}
  .logo img{display:block;height:44px;width:auto;transition:height .3s ease, opacity .2s;}
  .nav.scrolled .logo img{height:34px;}
  .logo:hover img{opacity:0.85;}
  .nav-links{display:flex;gap:2px;}
  .nav-links a{font-family:var(--mono);font-size:11px;letter-spacing:0.14em;text-transform:uppercase;color:var(--text);
               padding:10px 16px;transition:color .2s;}
  .nav-links a:hover{color:var(--white);}
  .nav-right{display:flex;align-items:center;gap:22px;margin-right:clamp(8px, 1.6vw, 32px);}
  .nav-cta{font-family:var(--mono);font-size:11px;letter-spacing:0.14em;text-transform:uppercase;color:var(--white);
           display:inline-flex;align-items:center;gap:8px;transition:color .2s;}
  .nav-cta::before{content:"";width:6px;height:6px;border-radius:50%;background:var(--accent);
                   box-shadow:0 0 12px rgba(124,58,237,0.7);}
  .nav-cta:hover{color:var(--accent-light);}

  /* social icons en el nav */
  .nav-social{display:flex;align-items:center;gap:14px;}
  .nav-social a{color:var(--muted);display:inline-flex;transition:color .2s, transform .2s;}
  .nav-social a:hover{color:var(--white);transform:translateY(-1px);}
  .nav-social svg{width:17px;height:17px;display:block;}

  /* botón hamburguesa (oculto en desktop) */
  .nav-toggle{display:none;position:relative;z-index:70;flex-direction:column;justify-content:center;gap:5px;
              width:42px;height:42px;border:1px solid var(--border-m);border-radius:6px;cursor:pointer;}
  .nav-toggle span{display:block;width:18px;height:1.5px;background:var(--text);margin:0 auto;
                   transition:transform .25s ease, opacity .2s ease;}
  .nav.menu-open .nav-toggle span:nth-child(1){transform:translateY(6.5px) rotate(45deg);}
  .nav.menu-open .nav-toggle span:nth-child(2){opacity:0;}
  .nav.menu-open .nav-toggle span:nth-child(3){transform:translateY(-6.5px) rotate(-45deg);}

  /* panel de menú mobile (oculto salvo en mobile + abierto) */
  .nav-mobile{display:none;}

  /* ============ HERO: Option A — cinematic overlay ============ */
  .hero{position:relative;height:100svh;min-height:640px;max-height:900px;overflow:hidden;background:#050505;}
  .hero-img{position:absolute;inset:0;width:100%;height:100%;z-index:1;}
  .hero-img img{width:100%;height:100%;object-fit:cover;object-position:60% 30%;}
  .hero-veil{position:absolute;inset:0;z-index:2;pointer-events:none;
             background:
               linear-gradient(90deg, rgba(8,8,8,0.92) 0%, rgba(8,8,8,0.55) 28%, rgba(8,8,8,0) 50%),
               linear-gradient(180deg, rgba(8,8,8,0.5) 0%, rgba(8,8,8,0) 18%, rgba(8,8,8,0) 70%, rgba(8,8,8,0.85) 100%);}
  .hero-crop{position:absolute;inset:40px;z-index:3;pointer-events:none;border:1px solid rgba(255,255,255,0.035);}
  .hero-crop::before,.hero-crop::after{content:"";position:absolute;width:14px;height:14px;border:1px solid rgba(255,255,255,0.15);}
  .hero-crop::before{top:-1px;left:-1px;border-right:0;border-bottom:0;}
  .hero-crop::after{bottom:-1px;right:-1px;border-left:0;border-top:0;}
  .hero-crop .c2,.hero-crop .c3{position:absolute;width:14px;height:14px;border:1px solid rgba(255,255,255,0.15);}
  .hero-crop .c2{top:-1px;right:-1px;border-left:0;border-bottom:0;}
  .hero-crop .c3{bottom:-1px;left:-1px;border-top:0;border-right:0;}

  .hero-layout{position:absolute;inset:0;z-index:4;display:grid;grid-template-rows:auto 1fr auto;padding:140px 72px 72px;}
  .hero-top{display:flex;justify-content:space-between;align-items:flex-start;gap:40px;}
  .hero-tag{display:inline-flex;align-items:center;gap:10px;font-family:var(--mono);font-size:11px;letter-spacing:0.22em;
            text-transform:uppercase;color:var(--muted);}
  .hero-tag::before{content:"";width:18px;height:1px;background:var(--accent);}
  .hero-meta{font-family:var(--mono);font-size:10px;letter-spacing:0.22em;text-transform:uppercase;color:var(--subtle);
             text-align:right;line-height:1.9;}
  .hero-meta span{color:var(--muted);}

  .hero-mid{display:flex;align-items:center;}
  .hero-head{max-width:640px;}
  .hero-head h1{font-family:var(--sans);font-size:clamp(56px,7.5vw,120px);line-height:0.9;letter-spacing:-0.045em;
                color:var(--white);font-weight:800;}
  .hero-head h1 .l1{display:block;font-weight:800;}
  .hero-head h1 .l2{display:block;font-weight:300;color:var(--muted);font-style:italic;font-size:0.78em;margin-top:4px;letter-spacing:-0.04em;}
  .hero-head h1 .l3{display:block;font-weight:800;color:var(--white);}

  .hero-bot{display:flex;align-items:flex-end;justify-content:space-between;gap:40px;}
  .hero-cta{display:inline-flex;align-items:center;gap:18px;padding:18px 0;color:var(--white);
            border-bottom:1px solid var(--border-m);font-family:var(--mono);font-size:12px;letter-spacing:0.2em;
            text-transform:uppercase;transition:border-color .3s, color .3s, gap .3s;}
  .hero-cta:hover{border-bottom-color:var(--accent);color:var(--accent-light);gap:24px;}
  .hero-cta svg{width:36px;height:10px;}
  .hero-scroll{font-family:var(--mono);font-size:10px;letter-spacing:0.22em;text-transform:uppercase;color:var(--subtle);
               writing-mode:vertical-rl;transform:rotate(180deg);}
  .hero-frame-id{font-family:var(--mono);font-size:10px;letter-spacing:0.2em;text-transform:uppercase;color:var(--subtle);}

  /* ============ TICKER ============ */
  .ticker{border-top:1px solid var(--border-s);border-bottom:1px solid var(--border-s);padding:32px 0;overflow:hidden;background:var(--bg-0);}
  .ticker-inner{display:flex;align-items:center;gap:56px;}
  .ticker-label{flex:0 0 auto;padding-left:40px;font-family:var(--mono);font-size:10px;letter-spacing:0.22em;
                text-transform:uppercase;color:var(--subtle);white-space:nowrap;}
  .ticker-track{flex:1;overflow:hidden;mask-image:linear-gradient(90deg,transparent,#000 6%,#000 94%,transparent);
                -webkit-mask-image:linear-gradient(90deg,transparent,#000 6%,#000 94%,transparent);}
  .ticker-row{display:inline-flex;gap:72px;animation:ticker 50s linear infinite;padding-right:72px;align-items:center;}
  @keyframes ticker{from{transform:translateX(0)}to{transform:translateX(-50%)}}
  .ticker-logo{flex:0 0 auto;white-space:nowrap;font-family:var(--sans);font-weight:700;font-size:18px;
               color:#7a7a7a;letter-spacing:-0.02em;opacity:0.55;transition:opacity .3s, color .3s;}
  .ticker-logo:hover{opacity:1;color:var(--white);}
  .ticker-logo i{font-style:normal;font-weight:300;color:var(--subtle);}

  /* ============ MANIFIESTO: single pullquote ============ */
  .manifiesto{padding:200px 0 200px;position:relative;}
  .manifiesto .container{position:relative;}
  .mf-mark{font-family:var(--mono);font-size:10px;letter-spacing:0.28em;text-transform:uppercase;color:var(--subtle);
           margin-bottom:80px;display:flex;align-items:center;gap:16px;}
  .mf-mark::before{content:"";width:40px;height:1px;background:var(--subtle);}
  .mf-quote{font-family:var(--sans);font-size:clamp(36px,5.6vw,88px);line-height:1.02;letter-spacing:-0.04em;
            font-weight:300;color:var(--muted);max-width:1100px;}
  .mf-quote b{font-weight:700;color:var(--white);}
  .mf-quote em{font-style:italic;font-weight:300;color:var(--text);}
  .mf-sig{margin-top:80px;display:flex;justify-content:space-between;align-items:flex-end;border-top:1px solid var(--border-s);padding-top:24px;}
  .mf-sig .who{font-family:var(--mono);font-size:10px;letter-spacing:0.22em;text-transform:uppercase;color:var(--subtle);line-height:1.8;}
  .mf-sig .who span{color:var(--muted);}

  /* ============ SERVICIOS: 3 cards ============ */
  .servicios{padding:0 0 180px;}
  .sec-label{font-family:var(--mono);font-size:10px;letter-spacing:0.28em;text-transform:uppercase;color:var(--subtle);
             margin-bottom:32px;display:flex;align-items:center;gap:16px;}
  .sec-label::before{content:"";width:40px;height:1px;background:var(--subtle);}
  .sec-h{font-size:clamp(44px,5.2vw,80px);line-height:1;letter-spacing:-0.04em;font-weight:300;color:var(--muted);
         max-width:1000px;margin-bottom:96px;}
  .sec-h b{font-weight:700;color:var(--white);}

  .serv-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:1px;background:var(--border-s);border-top:1px solid var(--border-s);border-bottom:1px solid var(--border-s);}
  .serv-card{background:var(--bg-0);padding:64px 48px 48px;transition:background .35s ease;position:relative;min-height:420px;display:flex;flex-direction:column;}
  .serv-card:hover{background:var(--bg-1);}
  .serv-num{font-family:var(--mono);font-size:11px;letter-spacing:0.2em;color:var(--subtle);position:absolute;top:32px;left:48px;}
  .serv-num-right{font-family:var(--mono);font-size:11px;letter-spacing:0.2em;color:var(--subtle);position:absolute;top:32px;right:48px;}
  .serv-card h3{font-family:var(--sans);font-size:32px;font-weight:500;color:var(--white);letter-spacing:-0.025em;
                line-height:1.1;margin-top:auto;margin-bottom:20px;}
  .serv-card p{color:var(--muted);font-size:15px;line-height:1.55;max-width:38ch;}
  .serv-arrow{margin-top:40px;font-family:var(--mono);font-size:10px;letter-spacing:0.22em;text-transform:uppercase;
              color:var(--subtle);display:flex;align-items:center;gap:10px;transition:color .25s;}
  .serv-card:hover .serv-arrow{color:var(--white);}
  .serv-arrow svg{width:24px;height:8px;}
  .serv-warn{display:none;margin-top:14px;font-family:var(--mono);font-size:10px;letter-spacing:0.12em;
             text-transform:uppercase;color:var(--accent-light);line-height:1.5;opacity:0.85;}

  /* ============ CORE ============ */
  .core{background:var(--bg-1);border-top:1px solid var(--border-s);border-bottom:1px solid var(--border-s);padding:180px 0;}
  .core-head{margin-bottom:96px;max-width:1000px;}
  .core-head h2{font-size:clamp(48px,5.6vw,88px);font-weight:300;color:var(--muted);letter-spacing:-0.04em;line-height:1;}
  .core-head h2 b{font-weight:700;color:var(--white);display:block;}
  .core-head h2 .accent{color:var(--accent-light);font-weight:700;font-style:italic;}

  .core-layout{display:grid;grid-template-columns:minmax(0,1fr) minmax(0,1.25fr);gap:80px;align-items:center;}
  .core-features{display:flex;flex-direction:column;gap:2px;background:var(--border-s);border-top:1px solid var(--border-s);border-bottom:1px solid var(--border-s);}
  .feature{background:var(--bg-1);padding:26px 0;display:grid;grid-template-columns:48px 1fr auto;gap:24px;align-items:baseline;transition:background .2s;}
  .feature:hover{background:var(--bg-2);padding-left:16px;padding-right:16px;}
  .feature .fn{font-family:var(--mono);font-size:10px;letter-spacing:0.22em;color:var(--subtle);}
  .feature .ft{color:var(--white);font-size:17px;font-weight:500;letter-spacing:-0.01em;}
  .feature .fd{color:var(--muted);font-size:13px;text-align:right;font-family:var(--mono);letter-spacing:0.06em;}

  .core-panel{border:1px solid var(--border-s);border-radius:6px;background:var(--bg-0);padding:20px;
              box-shadow:0 40px 80px rgba(0,0,0,0.6);}
  .panel-head{display:flex;align-items:center;justify-content:space-between;padding:4px 4px 16px;border-bottom:1px solid var(--border-s);margin-bottom:16px;}
  .panel-head .name{font-family:var(--mono);font-size:10px;letter-spacing:0.18em;text-transform:uppercase;color:var(--muted);}
  .panel-head .live{font-family:var(--mono);font-size:10px;letter-spacing:0.18em;text-transform:uppercase;color:var(--accent-light);display:inline-flex;align-items:center;gap:8px;}
  .panel-head .live::before{content:"";width:6px;height:6px;border-radius:50%;background:var(--accent);box-shadow:0 0 8px rgba(124,58,237,0.8);}

  .widgets{display:grid;grid-template-columns:1fr 1fr;gap:12px;}
  .widget{padding:18px;border:1px solid var(--border-s);border-radius:4px;background:var(--bg-0);}
  .widget.wide{grid-column:1/-1;}
  .wl{font-family:var(--mono);font-size:10px;letter-spacing:0.18em;text-transform:uppercase;color:var(--subtle);margin-bottom:14px;display:flex;justify-content:space-between;}
  .wl b{color:var(--text);font-weight:500;}

  .big-num{font-family:var(--sans);font-size:42px;font-weight:700;color:var(--white);letter-spacing:-0.04em;line-height:1;}
  .big-num small{font-family:var(--mono);font-size:10px;color:var(--muted);margin-left:8px;letter-spacing:0.14em;font-weight:500;}

  .scatter{height:140px;position:relative;border-bottom:1px dashed var(--border-s);border-left:1px dashed var(--border-s);margin-top:6px;}
  .scatter .pt{position:absolute;width:5px;height:5px;border-radius:50%;background:var(--text);}
  .scatter .pt.on{background:var(--accent-light);width:6px;height:6px;box-shadow:0 0 8px rgba(167,139,250,0.6);}

  .bars{display:grid;grid-template-columns:repeat(12,1fr);gap:4px;align-items:end;height:80px;margin-top:10px;}
  .bars div{background:var(--subtle);border-radius:1px;transition:background .2s;}
  .bars div.hi{background:var(--accent);}

  .minilog{font-family:var(--mono);font-size:10px;line-height:1.9;color:var(--muted);letter-spacing:0.04em;}
  .minilog span{color:var(--accent-light);}
  .minilog b{color:var(--white);font-weight:500;}

  /* ============ TIERS ============ */
  .tiers{padding:180px 0;}
  .tiers-card{border:1px solid var(--border-s);border-radius:6px;overflow:hidden;background:var(--bg-0);display:grid;grid-template-columns:repeat(5,1fr);}
  .tier{padding:40px 28px;border-right:1px solid var(--border-s);display:flex;flex-direction:column;gap:24px;position:relative;}
  .tier:last-child{border-right:0;}
  .tier.hero-tier{background:rgba(124,58,237,0.06);box-shadow:inset 0 0 0 1px rgba(124,58,237,0.3);z-index:1;}
  .tier .tname{font-family:var(--mono);font-size:10px;letter-spacing:0.22em;text-transform:uppercase;color:var(--muted);}
  .tier.hero-tier .tname{color:var(--accent-light);}
  .tier .price{font-family:var(--sans);font-size:42px;font-weight:700;color:var(--white);letter-spacing:-0.04em;line-height:1;}
  .tier .price small{font-family:var(--mono);font-size:10px;color:var(--muted);display:block;margin-top:10px;letter-spacing:0.14em;text-transform:uppercase;font-weight:500;}
  .tier .tdesc{color:var(--muted);font-size:13px;line-height:1.55;min-height:48px;}
  .tier ul{list-style:none;display:flex;flex-direction:column;gap:12px;margin-top:4px;flex:1;}
  .tier li{font-family:var(--mono);font-size:11px;letter-spacing:0.06em;color:var(--text);line-height:1.5;padding-left:16px;position:relative;}
  .tier li::before{content:"";position:absolute;left:0;top:7px;width:6px;height:1px;background:var(--muted);}
  .tier.hero-tier li::before{background:var(--accent-light);}
  .tier .tbtn{margin-top:auto;padding:12px 14px;border:1px solid var(--border-m);text-align:center;font-size:11px;
              color:var(--white);transition:all .2s;font-family:var(--mono);letter-spacing:0.18em;text-transform:uppercase;}
  .tier .tbtn:hover{border-color:var(--white);background:var(--white);color:var(--bg-0);}
  .tier.hero-tier .tbtn{background:var(--accent);border-color:var(--accent);}
  .tier.hero-tier .tbtn:hover{background:var(--white);color:var(--accent);border-color:var(--white);}
  .tier .badge{position:absolute;top:18px;right:18px;font-family:var(--mono);font-size:9px;letter-spacing:0.22em;text-transform:uppercase;color:var(--accent-light);}

  /* ============ FAQ ============ */
  .faq{padding:0 0 180px;}
  .faq-wrap{max-width:920px;margin:0 auto;}
  .faq-item{border-bottom:1px solid var(--border-s);transition:background .2s;}
  .faq-item:first-child{border-top:1px solid var(--border-s);}
  .faq-q{width:100%;display:flex;align-items:center;justify-content:space-between;gap:20px;padding:28px 0;text-align:left;color:var(--white);font-size:20px;font-weight:400;letter-spacing:-0.015em;transition:padding .25s;}
  .faq-q:hover{padding-left:8px;padding-right:8px;}
  .faq-icon{width:14px;height:14px;flex:0 0 auto;position:relative;}
  .faq-icon::before,.faq-icon::after{content:"";position:absolute;background:var(--muted);transition:background .25s, transform .25s;}
  .faq-icon::before{left:0;right:0;top:50%;height:1px;margin-top:-0.5px;}
  .faq-icon::after{top:0;bottom:0;left:50%;width:1px;margin-left:-0.5px;transition:transform .25s;}
  .faq-item.open .faq-icon::before{background:var(--accent-light);}
  .faq-item.open .faq-icon::after{background:var(--accent-light);transform:rotate(90deg);}
  .faq-a{max-height:0;overflow:hidden;transition:max-height .4s ease;}
  .faq-item.open .faq-a{max-height:400px;}
  .faq-a-inner{padding:0 0 32px 0;color:var(--muted);font-size:16px;line-height:1.7;max-width:70ch;}

  /* ============ BLOG / PUBLICACIONES — editorial-industrial, cards respiradas, imagen feather ============ */
  .blog{padding:180px 0;border-top:1px solid var(--border-s);position:relative;}
  .blog .sec-h{margin-bottom:80px;}
  .blog-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:64px 44px;}

  .blog-card{display:flex;flex-direction:column;text-decoration:none;color:inherit;position:relative;
             padding-top:28px;min-height:0;}
  /* línea tope que crece de izquierda a ancho completo al hover — cue editorial */
  .blog-card::before{content:"";position:absolute;top:0;left:0;width:28px;height:1px;
                     background:var(--subtle);transition:width .55s cubic-bezier(.2,.8,.2,1), background-color .25s;}
  .blog-card:hover::before{width:100%;background:var(--accent-light);}
  /* index stamp tipo magazine — 01 / 02 / 03 */
  .blog-idx{position:absolute;top:14px;right:0;font-family:var(--mono);font-size:10px;
            letter-spacing:0.22em;color:var(--subtle);transition:color .25s;}
  .blog-card:hover .blog-idx{color:var(--accent-light);}

  /* thumbnail con fade mask al fondo → la imagen se disuelve en el bg, sin corte duro */
  .blog-thumb{position:relative;width:100%;aspect-ratio:4/3;overflow:hidden;background:var(--bg-1);
              margin-bottom:28px;border:1px solid var(--accent);
              -webkit-mask-image:linear-gradient(180deg,#000 0%,#000 72%,transparent 100%);
                      mask-image:linear-gradient(180deg,#000 0%,#000 72%,transparent 100%);}
  .blog-thumb img{width:100%;height:100%;object-fit:cover;
                  transition:transform .8s cubic-bezier(.2,.7,.2,1), opacity .4s;opacity:0.88;}
  .blog-card:hover .blog-thumb img{transform:scale(1.035);opacity:1;}
  /* fallback cuando no hay featured image */
  .blog-thumb-empty{position:absolute;inset:0;display:flex;flex-direction:column;gap:8px;align-items:center;justify-content:center;
                    font-family:var(--mono);font-size:10px;letter-spacing:0.3em;text-transform:uppercase;
                    color:var(--subtle);background:
                      radial-gradient(ellipse at 30% 25%, rgba(124,58,237,0.1), transparent 55%),
                      linear-gradient(135deg, var(--bg-1) 0%, var(--bg-2) 100%);}
  .blog-thumb-empty .bte-mark{font-size:9px;letter-spacing:0.4em;color:var(--accent-light);opacity:0.6;}

  .blog-meta{font-family:var(--mono);font-size:10px;letter-spacing:0.2em;text-transform:uppercase;
             color:var(--subtle);display:flex;align-items:center;gap:14px;margin-bottom:18px;}
  .blog-meta .sep{width:1px;height:10px;background:var(--border-m);flex:0 0 1px;}

  .blog-title{font-family:var(--sans);font-size:24px;font-weight:500;color:var(--white);line-height:1.25;
              letter-spacing:-0.025em;margin-bottom:16px;transition:color .25s;
              display:-webkit-box;-webkit-line-clamp:3;-webkit-box-orient:vertical;overflow:hidden;}
  .blog-card:hover .blog-title{color:var(--accent-light);}

  .blog-excerpt{color:var(--muted);font-size:14px;line-height:1.7;margin-bottom:32px;
                display:-webkit-box;-webkit-line-clamp:3;-webkit-box-orient:vertical;overflow:hidden;}

  .blog-arr{margin-top:auto;font-family:var(--mono);font-size:10px;letter-spacing:0.24em;text-transform:uppercase;
            color:var(--subtle);display:inline-flex;align-items:center;gap:10px;transition:color .25s, gap .35s;}
  .blog-card:hover .blog-arr{color:var(--white);gap:16px;}
  .blog-arr svg{width:28px;height:8px;}

  /* ============ CONTACTO ============ */
  .contact{padding:120px 0 180px;position:relative;background-color:var(--bg-0);
    background-image:
      linear-gradient(rgba(8,8,8,0.85), rgba(8,8,8,0.85)),
      url('<?php echo htmlspecialchars($assets_url, ENT_QUOTES); ?>/contacto-bg-1920.webp');
    background-repeat:no-repeat, no-repeat;
    background-size:100% 100%, auto 100%;
    background-position:center, -180px center;}
  .contact .container{position:relative;z-index:1;}
  .contact-grid{display:grid;grid-template-columns:minmax(0,1fr) minmax(0,1fr);gap:80px;}
  .c-left h2{font-size:clamp(40px,4.6vw,68px);font-weight:300;color:var(--muted);letter-spacing:-0.035em;line-height:1;margin-bottom:40px;}
  .c-left h2 b{font-weight:700;color:var(--white);}
  .c-list{display:flex;flex-direction:column;}
  .c-item{display:grid;grid-template-columns:120px 1fr auto;gap:24px;align-items:center;padding:22px 0;border-bottom:1px solid var(--border-s);color:var(--text);transition:color .25s, padding-left .25s;}
  .c-item:first-child{border-top:1px solid var(--border-s);}
  .c-item:hover{color:var(--white);padding-left:8px;}
  .c-item .ilab{font-family:var(--mono);font-size:10px;letter-spacing:0.22em;text-transform:uppercase;color:var(--subtle);}
  .c-item .ival{font-size:16px;letter-spacing:-0.01em;}
  .c-item .iarr{font-family:var(--mono);font-size:10px;color:var(--subtle);letter-spacing:0.22em;}
  .c-item:hover .iarr{color:var(--accent-light);}

  .form{border:1px solid var(--border-s);border-radius:4px;padding:40px;background:var(--bg-0);}
  .form-row{display:grid;grid-template-columns:1fr 1fr;gap:24px;margin-bottom:28px;}
  .field{position:relative;display:flex;flex-direction:column;gap:10px;border-bottom:1px solid var(--border-s);padding-bottom:16px;transition:border-color .25s;}
  .field::after{
    content:"";position:absolute;bottom:-1px;left:50%;width:0;height:1px;
    background:var(--accent-light);box-shadow:0 0 6px rgba(167,139,250,0.4);
    transition:width .4s cubic-bezier(.4,0,.2,1), left .4s cubic-bezier(.4,0,.2,1);
  }
  .field:focus-within::after{left:0;width:100%;}
  .field label{font-family:var(--mono);font-size:10px;letter-spacing:0.22em;text-transform:uppercase;color:var(--subtle);transition:color .25s;}
  .field:focus-within label{color:var(--accent-light);}
  .field.has-error{border-bottom-color:#ef4444;}
  .field.has-error label{color:#f87171;}
  .field-err{
    color:#fca5a5;font-family:var(--mono);font-size:10px;letter-spacing:0.14em;
    text-transform:uppercase;margin-top:8px;display:block;
    animation:fieldErrIn .25s ease-out;
  }
  @keyframes fieldErrIn{from{opacity:0;transform:translateY(-4px);}to{opacity:1;transform:translateY(0);}}
  .field input,.field select,.field textarea{width:100%;background:transparent;border:0;padding:0;color:var(--white);
    font-family:var(--sans);font-size:15px;outline:none;}
  .field textarea{resize:vertical;min-height:100px;font-family:var(--sans);line-height:1.5;}
  .field select{appearance:none;cursor:pointer;background-image:url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%236b6b6b' stroke-width='2'><polyline points='6 9 12 15 18 9'/></svg>");background-repeat:no-repeat;background-position:right 0 center;padding-right:24px;}
  .field select option{background:var(--bg-0);color:var(--white);}
  .form-submit{margin-top:20px;padding:20px 0;border-top:1px solid var(--border-s);width:100%;color:var(--white);font-family:var(--mono);font-size:11px;letter-spacing:0.24em;text-transform:uppercase;display:flex;align-items:center;justify-content:space-between;transition:color .25s;}
  .form-submit:hover{color:var(--accent-light);}
  .form-submit svg{width:36px;height:10px;transition:transform .3s;}
  .form-submit:hover svg{transform:translateX(4px);}
  .form-submit.is-sending{opacity:0.55;pointer-events:none;}
  .form-submit.is-sending .form-submit-lbl::after{content:" · enviando";color:var(--muted);}
  .form-error{
    margin-top:20px;padding:14px 18px;
    border:1px solid rgba(239,68,68,0.25);background:rgba(239,68,68,0.06);
    border-radius:3px;color:#fca5a5;font-size:13px;line-height:1.5;
  }
  .form-error b{color:#fecaca;}

  /* Éxito del formulario */
  .form-success{
    border:1px solid var(--border-s);border-radius:4px;
    padding:48px 40px 32px;background:var(--bg-0);position:relative;
    animation:formSuccessIn .5s cubic-bezier(.25,.46,.45,.94);
  }
  @keyframes formSuccessIn{
    from{opacity:0;transform:translateY(10px);}
    to{opacity:1;transform:translateY(0);}
  }
  .form-success[hidden]{display:none;}
  .form-success-mark{
    position:relative;width:56px;height:56px;border-radius:50%;
    border:1px solid var(--accent);background:rgba(124,58,237,0.06);
    display:flex;align-items:center;justify-content:center;
    color:var(--accent-light);margin-bottom:28px;
  }
  .form-success-mark svg{width:26px;height:26px;
    stroke-dasharray:40;stroke-dashoffset:40;
    animation:checkDraw .7s ease-out .2s forwards;
  }
  @keyframes checkDraw{to{stroke-dashoffset:0;}}
  .form-success-mark::after{
    content:"";position:absolute;inset:-4px;border:1px solid var(--accent);
    border-radius:50%;opacity:0.4;
    animation:waRing 2.2s ease-out infinite;
  }
  .form-success-eyebrow{
    font-family:var(--mono);font-size:10px;letter-spacing:0.24em;
    text-transform:uppercase;color:var(--accent-light);margin-bottom:14px;
    display:inline-flex;align-items:center;gap:10px;
  }
  .form-success-eyebrow::before{
    content:"";width:28px;height:1px;background:var(--accent);
  }
  .form-success-title{
    font-family:var(--sans);font-size:clamp(30px,3.4vw,44px);
    font-weight:300;color:var(--white);letter-spacing:-0.03em;
    line-height:1.05;margin-bottom:18px;
  }
  .form-success-copy{
    color:var(--muted);font-size:15px;line-height:1.7;
    max-width:46ch;margin-bottom:32px;
  }
  .form-success-copy b{color:var(--white);font-weight:600;}
  .form-success-actions{
    display:flex;align-items:center;gap:28px;flex-wrap:wrap;margin-bottom:36px;
  }
  .form-success-reset{
    background:transparent;border:0;padding:12px 0;cursor:pointer;
    font-family:var(--mono);font-size:10px;letter-spacing:0.22em;
    text-transform:uppercase;color:var(--muted);
    display:inline-flex;align-items:center;gap:10px;
    transition:color .25s, gap .25s;
  }
  .form-success-reset svg{width:14px;height:14px;}
  .form-success-reset:hover{color:var(--white);gap:14px;}
  .form-success-meta{
    display:flex;align-items:center;gap:14px;flex-wrap:wrap;
    padding-top:22px;border-top:1px dashed var(--border-s);
    font-family:var(--mono);font-size:9px;letter-spacing:0.24em;
    text-transform:uppercase;color:var(--subtle);
  }
  .form-success-dot{
    width:4px;height:4px;border-radius:50%;background:var(--subtle);
  }

  /* ============ WHATSAPP CTAs ============ */
  @keyframes waPulse{0%,100%{opacity:1;transform:scale(1);}50%{opacity:0.45;transform:scale(0.7);}}
  @keyframes waRing{0%{transform:scale(1);opacity:0.6;}100%{transform:scale(1.8);opacity:0;}}

  /* Hero — segunda CTA (WA) junto a la del reel */
  .hero-ctas{display:flex;align-items:center;gap:36px;flex-wrap:wrap;}
  .hero-cta--wa{position:relative;}
  .hero-cta--wa::before{
    content:"";display:inline-block;width:8px;height:8px;border-radius:50%;
    background:#4ade80;box-shadow:0 0 8px rgba(74,222,128,0.7);
    animation:waPulse 2s ease infinite;margin-right:2px;
  }
  .hero-reel-meta{font-family:var(--mono);font-size:10px;letter-spacing:0.22em;text-transform:uppercase;color:var(--subtle);margin-top:10px;display:flex;align-items:center;gap:8px;}
  .hero-reel-meta a{color:var(--muted);border-bottom:1px solid rgba(255,255,255,0.1);padding-bottom:2px;transition:color .25s, border-color .25s;}
  .hero-reel-meta a:hover{color:var(--accent-light);border-bottom-color:var(--accent);}

  /* CTA inline monoespaciado (CORE, FAQ) */
  .wa-link{
    display:inline-flex;align-items:center;gap:12px;
    font-family:var(--mono);font-size:11px;letter-spacing:0.22em;text-transform:uppercase;
    color:var(--white);padding:12px 0;
    border-bottom:1px solid var(--border-m);
    transition:color .25s, border-color .25s, gap .25s;cursor:pointer;
  }
  .wa-link:hover{color:var(--accent-light);border-bottom-color:var(--accent);gap:18px;}
  .wa-link .wa-svg{width:15px;height:15px;color:#25D366;flex-shrink:0;}
  .wa-link .wa-arr{font-size:13px;margin-left:4px;}

  /* CTA boxed (Servicios banda, Tiers cta) */
  .wa-btn{
    display:inline-flex;align-items:center;gap:14px;
    padding:18px 28px;border:1px solid var(--border-m);border-radius:3px;
    background:transparent;color:var(--white);
    font-family:var(--mono);font-size:11px;letter-spacing:0.22em;text-transform:uppercase;
    position:relative;overflow:hidden;
    transition:border-color .3s, color .3s, gap .3s;cursor:pointer;
  }
  .wa-btn::before{
    content:"";position:absolute;inset:0;z-index:0;
    background:linear-gradient(90deg, transparent, rgba(124,58,237,0.18), transparent);
    transform:translateX(-100%);transition:transform .6s ease;
  }
  .wa-btn:hover::before{transform:translateX(100%);}
  .wa-btn:hover{border-color:var(--accent);color:var(--accent-light);gap:18px;}
  .wa-btn > *{position:relative;z-index:1;}
  .wa-btn .wa-svg{width:16px;height:16px;color:#25D366;flex-shrink:0;}
  .wa-btn .wa-arr{font-size:14px;}

  /* Banda CTA (Servicios) */
  .wa-band{
    display:flex;align-items:center;justify-content:space-between;gap:48px;
    padding:56px 0 48px;margin-top:96px;
    border-top:1px solid var(--border-s);position:relative;
  }
  .wa-band::before{
    content:"";position:absolute;top:-1px;left:0;width:48px;height:1px;background:var(--accent);
  }
  .wa-band-meta{
    font-family:var(--mono);font-size:10px;letter-spacing:0.24em;text-transform:uppercase;
    color:var(--subtle);margin-bottom:18px;display:block;
  }
  .wa-band-title{
    font-family:var(--sans);font-size:clamp(24px,3vw,42px);line-height:1.05;
    letter-spacing:-0.025em;color:var(--muted);font-weight:300;max-width:24ch;
  }
  .wa-band-title b{color:var(--white);font-weight:700;font-style:italic;}

  /* CTA inline dentro de CORE */
  .core-cta{
    margin-top:32px;padding-top:28px;border-top:1px dashed var(--border-s);
    display:flex;flex-direction:column;gap:12px;max-width:44ch;
  }
  .core-cta-meta{font-family:var(--mono);font-size:10px;letter-spacing:0.24em;text-transform:uppercase;color:var(--subtle);}

  /* Tiers CTA closing block */
  .tiers-cta{
    display:flex;align-items:center;justify-content:space-between;gap:40px;
    margin-top:56px;padding:40px 0 0;border-top:1px dashed var(--border-s);
  }
  .tiers-cta-txt{max-width:56ch;}
  .tiers-cta-meta{font-family:var(--mono);font-size:10px;letter-spacing:0.24em;text-transform:uppercase;color:var(--subtle);margin-bottom:10px;}
  .tiers-cta-title{font-family:var(--sans);font-size:clamp(20px,2.4vw,30px);color:var(--white);font-weight:400;line-height:1.2;letter-spacing:-0.02em;}
  .tiers-cta-title b{color:var(--accent-light);font-weight:600;}

  /* FAQ CTA closing */
  .faq-cta{
    display:flex;align-items:center;justify-content:space-between;gap:32px;
    margin-top:56px;padding-top:36px;border-top:1px dashed var(--border-s);
  }
  .faq-cta-lbl{font-family:var(--mono);font-size:10px;letter-spacing:0.24em;text-transform:uppercase;color:var(--muted);}

  /* Botón flotante WhatsApp */
  .wa-float{
    position:fixed;bottom:28px;right:28px;z-index:80;
    display:inline-flex;align-items:center;gap:12px;
    padding:10px 22px 10px 10px;
    background:rgba(12,12,12,0.92);
    backdrop-filter:blur(14px);-webkit-backdrop-filter:blur(14px);
    border:1px solid rgba(255,255,255,0.1);border-radius:999px;
    font-family:var(--mono);font-size:11px;letter-spacing:0.22em;text-transform:uppercase;
    color:var(--white);box-shadow:0 14px 36px rgba(0,0,0,0.5);
    opacity:0;transform:translateY(14px) scale(0.96);
    transition:opacity .45s ease, transform .45s ease, border-color .25s, background .25s;
    pointer-events:none;
  }
  .wa-float.on{opacity:1;transform:translateY(0) scale(1);pointer-events:auto;}
  .wa-float:hover{border-color:var(--accent);background:rgba(16,16,16,0.96);}
  .wa-float-icon{
    position:relative;width:36px;height:36px;border-radius:50%;background:#25D366;
    display:inline-flex;align-items:center;justify-content:center;flex-shrink:0;
  }
  .wa-float-icon svg{width:18px;height:18px;color:#fff;}
  .wa-float-icon::after{
    content:"";position:absolute;inset:-3px;border:1px solid #25D366;border-radius:50%;
    opacity:0.5;animation:waRing 2s ease-out infinite;
  }
  .wa-float-text{line-height:1;}

  /* ============ BACK TO TOP ============ */
  .to-top{
    position:fixed;bottom:28px;left:28px;z-index:79;
    width:44px;height:44px;border-radius:50%;
    display:inline-flex;align-items:center;justify-content:center;
    background:rgba(12,12,12,0.92);
    backdrop-filter:blur(14px);-webkit-backdrop-filter:blur(14px);
    border:1px solid rgba(255,255,255,0.1);
    color:var(--white);
    box-shadow:0 14px 36px rgba(0,0,0,0.5);
    cursor:pointer;
    opacity:0;transform:translateY(14px) scale(0.96);
    transition:opacity .45s ease, transform .45s ease, border-color .25s, background .25s, color .25s;
    pointer-events:none;
  }
  .to-top.on{opacity:1;transform:translateY(0) scale(1);pointer-events:auto;}
  .to-top:hover{border-color:var(--accent);background:rgba(16,16,16,0.96);color:var(--accent-light);}
  .to-top svg{width:16px;height:16px;}
  @media (max-width:960px){
    .to-top{bottom:16px;left:16px;width:44px;height:44px;}
  }

  /* ============ FOOTER ============ */
  footer{border-top:1px solid var(--border-s);padding:64px 0 48px;}
  .foot{display:grid;grid-template-columns:1fr auto 1fr;align-items:center;gap:32px;}
  .foot-cp{color:var(--subtle);font-family:var(--mono);font-size:10px;letter-spacing:0.22em;text-transform:uppercase;text-align:right;}
  .foot-links{display:flex;gap:32px;justify-content:center;}
  .foot-links a{font-family:var(--mono);font-size:10px;letter-spacing:0.22em;text-transform:uppercase;color:var(--muted);transition:color .2s;}
  .foot-links a:hover{color:var(--white);}

  /* ============ LIGHTBOX (video on-demand) ============ */
  .lightbox{position:fixed;inset:0;z-index:100;background:rgba(5,5,5,0.94);backdrop-filter:blur(10px);-webkit-backdrop-filter:blur(10px);
            display:none;align-items:center;justify-content:center;padding:40px;}
  .lightbox.open{display:flex;}
  .lightbox-inner{width:100%;max-width:1280px;aspect-ratio:16/9;position:relative;box-shadow:0 40px 120px rgba(0,0,0,0.8);}
  .lightbox-frame{position:absolute;inset:0;background:#000;border:1px solid var(--border-s);}
  .lightbox-frame iframe{width:100%;height:100%;border:0;display:block;}
  .lightbox-close{position:fixed;top:24px;right:24px;width:44px;height:44px;border:1px solid var(--border-m);
                  border-radius:50%;background:rgba(8,8,8,0.6);color:var(--white);font-size:22px;line-height:1;
                  display:flex;align-items:center;justify-content:center;transition:all .25s;cursor:pointer;z-index:101;}
  .lightbox-close:hover{background:var(--white);color:var(--bg-0);border-color:var(--white);transform:rotate(90deg);}
  body.no-scroll{overflow:hidden;}

  /* ============ RESPONSIVE ============ */
  @media (max-width:960px){
    .container{padding:0 24px;}
    .nav{padding:20px 24px;display:flex;justify-content:space-between;align-items:center;}
    .nav.scrolled{padding:14px 24px;}
    .nav-links{display:none;}
    .nav-right .nav-social,
    .nav-right .nav-cta{display:none;}
    .nav-toggle{display:flex;}
    .nav-mobile{position:fixed;inset:0;z-index:55;display:none;flex-direction:column;
                align-items:center;justify-content:center;gap:6px;padding:96px 24px 48px;overflow-y:auto;
                background:rgba(8,8,8,0.98);backdrop-filter:blur(22px);-webkit-backdrop-filter:blur(22px);}
    .nav.menu-open + .nav-mobile{display:flex;}
    .nav-mobile a{font-family:var(--mono);font-size:14px;letter-spacing:0.16em;text-transform:uppercase;
                  color:var(--text);padding:14px 16px;transition:color .2s;}
    .nav-mobile a:hover{color:var(--white);}
    .nav-mobile .nav-social{margin-top:24px;gap:28px;}
    .nav-mobile .nav-social svg{width:23px;height:23px;}
    .nav-mobile .nav-cta{margin-top:20px;font-size:13px;}
    .hero{min-height:560px;max-height:none;}
    .hero-layout{padding:100px 24px 40px;}
    .hero-img img{object-position:65% 30%;}
    .hero-veil{background:
               linear-gradient(180deg, rgba(8,8,8,0.9) 0%, rgba(8,8,8,0.3) 35%, rgba(8,8,8,0) 55%, rgba(8,8,8,0.95) 100%);}
    .hero-head h1{font-size:clamp(40px,9.5vw,68px);}
    .logo img{height:36px;}
    .nav.scrolled .logo img{height:30px;}
    .nav-right{margin-right:0;}
    .hero-crop{inset:16px;}
    .hero-scroll{display:none;}
    .hero-meta{font-size:9px;}
    .serv-grid{grid-template-columns:1fr;}
    .blog{padding:100px 0;}
    .blog-grid{grid-template-columns:1fr;gap:64px;}
    .blog-card{padding-top:24px;}
    .blog-thumb{margin-bottom:24px;}
    .core-layout,.contact-grid{grid-template-columns:1fr;gap:48px;}
    .core-head{margin-bottom:48px;}
    .feature{grid-template-columns:36px 1fr;gap:4px 14px;padding:22px 0;}
    .feature .fn{grid-row:1;grid-column:1;align-self:start;}
    .feature .ft{grid-row:1;grid-column:2;font-size:16px;}
    .feature .fd{grid-row:2;grid-column:2;text-align:left;font-size:11px;letter-spacing:0.04em;line-height:1.4;}
    .core-panel{padding:14px;max-width:100%;}
    .panel-head{flex-wrap:wrap;gap:6px 10px;padding:4px 4px 12px;}
    .panel-head .name{font-size:9px;letter-spacing:0.12em;}
    .widgets{grid-template-columns:1fr;gap:10px;}
    .widget{padding:16px;}
    .big-num{font-size:30px;}
    .big-num small{display:block;margin-left:0;margin-top:6px;}
    .scatter{height:110px;}
    .bars{height:60px;}
    .tiers-card{grid-template-columns:1fr;border-radius:6px;}
    .tier{border-right:0;border-bottom:1px solid var(--border-s);padding:32px 24px;}
    .tier:last-child{border-bottom:0;}
    .tier.hero-tier{box-shadow:inset 0 0 0 1px rgba(124,58,237,0.3);}
    .form{padding:28px 20px;}
    .contact{padding:80px 0 100px;background-image:none;background-color:var(--bg-0);}
    .form-row{grid-template-columns:1fr;gap:20px;margin-bottom:20px;}
    .foot{grid-template-columns:1fr;text-align:center;}
    .foot-cp{text-align:center;}
    .foot-links{flex-wrap:wrap;gap:20px 24px;}
    .manifiesto,.servicios,.core,.tiers,.faq,.contact{padding-top:100px;padding-bottom:100px;}
    .c-item{grid-template-columns:100px 1fr;gap:16px;}
    .c-item .iarr{display:none;}
    .lightbox{padding:16px;}
    .lightbox-close{top:14px;right:14px;width:38px;height:38px;font-size:20px;}
    .serv-warn{display:block;}

    /* WA CTAs — mobile */
    .hero-ctas{gap:20px;flex-direction:column;align-items:flex-start;}
    .hero-cta{font-size:10px;padding:14px 0;gap:12px;}
    .wa-band{flex-direction:column;align-items:flex-start;gap:28px;padding:40px 0;margin-top:64px;}
    .wa-band-title{max-width:100%;}
    .tiers-cta,.faq-cta{flex-direction:column;align-items:flex-start;gap:20px;}
    .wa-btn{padding:16px 22px;font-size:10px;}
    .wa-float{bottom:16px;right:16px;padding:8px 8px;gap:0;}
    .wa-float-text{display:none;}
    .wa-float-icon{width:48px;height:48px;}
    .wa-float-icon svg{width:22px;height:22px;}
  }
</style>
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>

<!-- Sentinelas invisibles para IntersectionObserver (nav.scrolled y waFloat.on) — evitan leer window.scrollY -->
<div id="navSentinel" aria-hidden="true" style="position:absolute;top:40px;left:0;width:1px;height:1px;pointer-events:none;"></div>
<div id="waSentinel"  aria-hidden="true" style="position:absolute;top:600px;left:0;width:1px;height:1px;pointer-events:none;"></div>

<?php
// Iconos de redes sociales reutilizables (nav desktop + panel mobile).
$d3d_social = '<div class="nav-social">'
  . '<a href="https://www.instagram.com/doble3d/" target="_blank" rel="noopener" aria-label="Instagram"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.3" cy="6.7" r="1" fill="currentColor" stroke="none"/></svg></a>'
  . '<a href="https://www.facebook.com/people/Doble-3D/" target="_blank" rel="noopener" aria-label="Facebook"><svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M13.5 21v-8h2.7l.4-3.1h-3.1V7.9c0-.9.25-1.5 1.53-1.5H17V3.6c-.28-.04-1.25-.12-2.37-.12-2.35 0-3.96 1.43-3.96 4.07v2.32H8v3.1h2.67V21z"/></svg></a>'
  . '<a href="https://www.linkedin.com/company/doble-3d/" target="_blank" rel="noopener" aria-label="LinkedIn"><svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M6.94 5a1.94 1.94 0 11-3.88 0 1.94 1.94 0 013.88 0zM3.3 8.4h3.3V21H3.3zM9.4 8.4h3.16v1.72h.05c.44-.83 1.5-1.72 3.1-1.72 3.3 0 3.9 2.17 3.9 5V21h-3.3v-6.74c0-1.6-.03-3.67-2.24-3.67-2.24 0-2.58 1.75-2.58 3.55V21H9.4z"/></svg></a>'
  . '<a href="https://www.youtube.com/@doble3d" target="_blank" rel="noopener" aria-label="YouTube"><svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M23 7.5a3 3 0 00-2.1-2.1C19 4.9 12 4.9 12 4.9s-7 0-8.9.5A3 3 0 001 7.5 31 31 0 00.5 12 31 31 0 001 16.5a3 3 0 002.1 2.1c1.9.5 8.9.5 8.9.5s7 0 8.9-.5a3 3 0 002.1-2.1A31 31 0 0023 12a31 31 0 00-.5-4.5zM9.75 15.5v-7l6 3.5z"/></svg></a>'
  . '</div>';
?>
<!-- ============ NAV ============ -->
<nav class="nav" id="nav">
  <a href="#" class="logo" aria-label="Doble 3D — inicio"><img src="<?php echo htmlspecialchars($assets_url, ENT_QUOTES); ?>/logo-nuevo-420.webp" srcset="<?php echo htmlspecialchars($assets_url, ENT_QUOTES); ?>/logo-nuevo-420.webp 1x, <?php echo htmlspecialchars($assets_url, ENT_QUOTES); ?>/logo-nuevo-630.webp 1.5x" width="420" height="126" alt="Doble 3D" fetchpriority="high" decoding="async"></a>
  <div class="nav-links">
    <a href="#servicios">Servicios</a>
    <a href="#core">CORE</a>
    <a href="#tiers">Planes</a>
    <a href="#blog">Blog</a>
    <a href="#faq">FAQ</a>
  </div>
  <div class="nav-right">
    <?php echo $d3d_social; ?>
    <a href="#contacto" class="nav-cta">Hablemos</a>
    <button type="button" class="nav-toggle" id="navToggle" aria-label="Abrir menú" aria-expanded="false" aria-controls="navMobile">
      <span></span><span></span><span></span>
    </button>
  </div>
</nav>
<div class="nav-mobile" id="navMobile">
  <a href="#servicios">Servicios</a>
  <a href="#core">CORE</a>
  <a href="#tiers">Planes</a>
  <a href="#blog">Blog</a>
  <a href="#faq">FAQ</a>
  <?php echo $d3d_social; ?>
  <a href="#contacto" class="nav-cta">Hablemos</a>
</div>

<!-- ============ HERO — Option A cinematic overlay ============ -->
<header class="hero">
  <div class="hero-img">
    <picture>
      <source type="image/avif"
              srcset="<?php echo htmlspecialchars($assets_url, ENT_QUOTES); ?>/heroe-1280.avif 1280w, <?php echo htmlspecialchars($assets_url, ENT_QUOTES); ?>/heroe-1920.avif 1920w, <?php echo htmlspecialchars($assets_url, ENT_QUOTES); ?>/heroe-2560.avif 2560w"
              sizes="100vw">
      <source type="image/webp"
              srcset="<?php echo htmlspecialchars($assets_url, ENT_QUOTES); ?>/heroe-1280.webp 1280w, <?php echo htmlspecialchars($assets_url, ENT_QUOTES); ?>/heroe-1920.webp 1920w, <?php echo htmlspecialchars($assets_url, ENT_QUOTES); ?>/heroe-2560.webp 2560w"
              sizes="100vw">
      <img src="<?php echo htmlspecialchars($assets_url, ENT_QUOTES); ?>/heroe-1920.webp" width="1920" height="1072" alt="Operador con casco VR interactuando con componente industrial holográfico" fetchpriority="high" decoding="async">
    </picture>
  </div>
  <div class="hero-veil"></div>
  <div class="hero-crop"><span class="c2"></span><span class="c3"></span></div>

  <div class="hero-layout">
    <div class="hero-top">
      <span class="hero-tag">VR · AR · 3D / Entrenamiento industrial</span>
      <div class="hero-meta">
        No. 001 — <span>Santiago, CL</span><br>
        Ed. <?php echo $year; ?> — <span>Vol. X</span>
      </div>
    </div>

    <div class="hero-mid">
      <div class="hero-head">
        <h1>
          <span class="l1">Realidad virtual</span>
          <span class="l2"> animación 3D</span>
          <span class="l3">sin complicaciones.</span>
        </h1>
      </div>
    </div>

    <div class="hero-bot">
      <div>
        <div class="hero-ctas">
          <a href="https://www.youtube.com/watch?v=INTnf-lgKAQ" target="_blank" rel="noopener" class="hero-cta">
            Ver reel · 02:14
            <svg viewBox="0 0 36 10" fill="none" stroke="currentColor" stroke-width="1"><line x1="0" y1="5" x2="32" y2="5"/><polyline points="28 1 34 5 28 9"/></svg>
          </a>
          <a href="<?php echo htmlspecialchars($wa_hero, ENT_QUOTES); ?>" target="_blank" rel="noopener" class="hero-cta hero-cta--wa">
            Ver demo en 5 minutos
            <svg viewBox="0 0 36 10" fill="none" stroke="currentColor" stroke-width="1"><line x1="0" y1="5" x2="32" y2="5"/><polyline points="28 1 34 5 28 9"/></svg>
          </a>
        </div>
        <div class="hero-reel-meta">
          <span>¿Industria similar?</span>
          <a href="<?php echo htmlspecialchars($wa_reel, ENT_QUOTES); ?>" target="_blank" rel="noopener">Recibe un caso real documentado →</a>
        </div>
      </div>
      <span class="hero-frame-id">FR_001 · 00:00:01:00</span>
      <span class="hero-scroll">Scroll ↓</span>
    </div>
  </div>
</header>

<!-- ============ TICKER ============ -->
<section class="ticker" aria-label="Clientes">
  <div class="ticker-inner">
    <div class="ticker-label">[ Clientes · 2018–<?php echo $year; ?> ]</div>
    <div class="ticker-track">
      <div class="ticker-row" id="tickerRow">
        <?php
          $ticker = [
            ['Codelco','CL'],['Kinross','CA'],['Barrick','US'],['Centinela','CL'],
            ['BHP','AU'],['Collahuasi','CL'],['FLSmidth','DK'],['Minera Los Pelambres','CL'],
            ['Fly With Us','CL'],['AHA','CL'],['CMPC','CL'],
          ];
          // Duplicado para marquee infinito
          foreach(array_merge($ticker, $ticker) as $t){
            echo '<div class="ticker-logo">'.htmlspecialchars($t[0]).' <i>— '.htmlspecialchars($t[1]).'</i></div>';
          }
        ?>
      </div>
    </div>
  </div>
</section>

<!-- ============ MANIFIESTO ============ -->
<section class="manifiesto">
  <div class="container">
    <div class="mf-mark">Manifiesto — 001</div>
    <p class="mf-quote">
      No vendemos <em>tecnología</em>. Vendemos <b>Cero Incidencias</b>
    </p>
    <div class="mf-sig">
      <div class="who">Doble 3D SpA<br><span>Fundado 2015 · Providencia, Santiago</span></div>
      <div class="who" style="text-align:right;">Tres disciplinas<br><span>VR · Animación 3D · Gamificación</span></div>
    </div>
  </div>
</section>

<!-- ============ SERVICIOS ============ -->
<section class="servicios" id="servicios">
  <div class="container">
    <div class="sec-label">Servicios — 002</div>
    <h2 class="sec-h">Tres formas de entrenar <b>sin riesgo real.</b></h2>
  </div>

  <div class="serv-grid">
    <article class="serv-card">
      <span class="serv-num">01</span>
      <span class="serv-num-right">VR</span>
      <h3>Armado y desarme en realidad virtual.</h3>
      <p>Simulaciones de mantenimiento sobre equipos críticos. El operador practica procedimientos completos con feedback háptico, sin detener la faena.</p>
      <button class="serv-arrow" type="button" data-video="bQVflT-ywkE" aria-label="Ver video demo en popup">
        <svg viewBox="0 0 24 8" fill="none" stroke="currentColor" stroke-width="1"><line x1="0" y1="4" x2="20" y2="4"/><polyline points="17 1 22 4 17 7"/></svg>
        Ver video
      </button>
    </article>

    <article class="serv-card">
      <span class="serv-num">02</span>
      <span class="serv-num-right">3D</span>
      <h3>Videos industriales.</h3>
      <p>Animaciones técnicas que explican procesos complejos en menos de tres minutos. Reemplazan manuales de cuarenta páginas.</p>
      <button class="serv-arrow" type="button" data-video="pExbOqU9Wgc" aria-label="Ver video demo en popup">
        <svg viewBox="0 0 24 8" fill="none" stroke="currentColor" stroke-width="1"><line x1="0" y1="4" x2="20" y2="4"/><polyline points="17 1 22 4 17 7"/></svg>
        Ver video
      </button>
    </article>

    <article class="serv-card">
      <span class="serv-num">03</span>
      <span class="serv-num-right">APP</span>
      <h3>Aplicaciones gamificadas con SCORM.</h3>
      <p>Entrenamiento con mecánicas de juego y estándar LMS. Se integra a Moodle, SAP SuccessFactors o Cornerstone sin fricción.</p>
      <a class="serv-arrow" href="https://doble3d.cl/webgl/pointandclick/leykarin/" target="_blank" rel="noopener">
        <svg viewBox="0 0 24 8" fill="none" stroke="currentColor" stroke-width="1"><line x1="0" y1="4" x2="20" y2="4"/><polyline points="17 1 22 4 17 7"/></svg>
        Probar demo
      </a>
      <span class="serv-warn">— Para una mejor experiencia, ver el demo en escritorio.</span>
    </article>
  </div>

  <div class="container">
    <div class="wa-band">
      <div>
        <span class="wa-band-meta">Siguiente paso · 02.04</span>
        <div class="wa-band-title">Cotiza <b>tu proyecto</b> en un mensaje.</div>
      </div>
      <a href="<?php echo htmlspecialchars($wa_serv, ENT_QUOTES); ?>" target="_blank" rel="noopener" class="wa-btn">
        <svg class="wa-svg" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893A11.821 11.821 0 0020.885 3.488"/></svg>
        <span>Cotiza tu proyecto VR</span>
        <span class="wa-arr">→</span>
      </a>
    </div>
  </div>
</section>

<!-- ============ CORE ============ -->
<section class="core" id="core">
  <div class="container">
    <div class="core-head">
      <div class="sec-label">Plataforma — 003</div>
      <h2>CORE.<b>Gestión centralizada</b>de entrenamiento VR.</h2>
    </div>

    <div class="core-layout">
      <div>
        <p style="color:var(--text);font-size:17px;line-height:1.6;max-width:44ch;margin-bottom:28px;">
          Una consola web para desplegar simulaciones, asignar trayectos formativos, registrar resultados y exportar a cualquier LMS compatible con SCORM 2004.
        </p>

        <div class="core-cta">
          <span class="core-cta-meta">Material técnico · 03.01</span>
          <a href="<?php echo htmlspecialchars($wa_core, ENT_QUOTES); ?>" target="_blank" rel="noopener" class="wa-link">
            <svg class="wa-svg" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893A11.821 11.821 0 0020.885 3.488"/></svg>
            Recibe el pitch deck
            <span class="wa-arr">→</span>
          </a>
        </div>

        <div class="core-features" style="margin-top:48px;">
          <div class="feature"><span class="fn">01</span><span class="ft">Biblioteca modular</span><span class="fd">Versionado · acceso por rol</span></div>
          <div class="feature"><span class="fn">02</span><span class="ft">Analítica en vivo</span><span class="fd">Heatmaps de mirada</span></div>
          <div class="feature"><span class="fn">03</span><span class="ft">Gestión de cohortes</span><span class="fd">Onboarding masivo</span></div>
          <div class="feature"><span class="fn">04</span><span class="ft">SCORM 2004 nativo</span><span class="fd">Moodle · SAP · Cornerstone</span></div>
          <div class="feature"><span class="fn">05</span><span class="ft">Multi-headset</span><span class="fd">Quest 3 · Pico 4</span></div>
          <div class="feature"><span class="fn">06</span><span class="ft">Hosting privado</span><span class="fd">Cloud · On-premise</span></div>
        </div>
      </div>

      <aside class="core-panel" aria-label="Preview dashboard CORE">
        <div class="panel-head">
          <span class="name">core.doble3d.cl / dashboard</span>
          <span class="live">en vivo</span>
        </div>

        <div class="widgets">
          <div class="widget">
            <div class="wl">Cumplimiento <b>Q2</b></div>
            <div class="big-num">87<span style="color:var(--muted);">%</span><small>objetivo 90</small></div>
          </div>
          <div class="widget">
            <div class="wl">Sesiones <b>semana</b></div>
            <div class="big-num">1,204<small>+12% vs Q1</small></div>
          </div>

          <div class="widget wide">
            <div class="wl">Progreso por cohorte<b>12 semanas</b></div>
            <div class="scatter" id="scatter"><?php
              // Mismo PRNG que la versión JS (seed 7) → renderizado server-side, cero reflow
              $s = 7;
              $rnd = function() use (&$s){ $s = ($s*9301+49297) % 233280; return $s/233280; };
              for($i=0;$i<44;$i++){
                $x = 2 + $rnd()*96;
                $y = 6 + $rnd()*86;
                $on = $rnd() > 0.7;
                echo '<div class="pt'.($on?' on':'').'" style="left:'.number_format($x,2,'.','').'%;top:'.number_format($y,2,'.','').'%;"></div>';
              }
            ?></div>
          </div>

          <div class="widget wide">
            <div class="wl">Certificaciones <b>últimos 12 meses</b></div>
            <div class="bars" id="bars"><?php
              $s = 19;
              $rnd = function() use (&$s){ $s = ($s*9301+49297) % 233280; return $s/233280; };
              for($i=0;$i<12;$i++){
                $h = 18 + $rnd()*82;
                $hi = $i === 8 ? ' class="hi"' : '';
                echo '<div'.$hi.' style="height:'.number_format($h,2,'.','').'%;"></div>';
              }
            ?></div>
          </div>

          <div class="widget wide">
            <div class="minilog" id="minilog"><b>[log]</b> sesión 08A terminada · 00:18:42 · <span>✓ aprobada</span><br><b>[log]</b> cohorte M-04 asignada · 24 operadores · Centinela<br><b>[log]</b> paquete SCORM exportado · core-armado-v3.2.zip<br><b>[log]</b> nuevo headset aprovisionado · Quest 3 · SN FA7-9921</div>
          </div>
        </div>
      </aside>
    </div>
  </div>
</section>

<!-- ============ TIERS ============ -->
<section class="tiers" id="tiers">
  <div class="container">
    <div class="sec-label">Planes — 004</div>
    <h2 class="sec-h">Escalabilidad <b>de precisión,</b> no de emergencia.</h2>
    <p style="color:var(--muted);font-size:16px;max-width:60ch;margin:-48px 0 72px;line-height:1.6;">Cada tier es un hito estratégico, no un bracket de costos. La infraestructura que necesitas hoy, con la capacidad que exigirás mañana.</p>

    <div class="tiers-card">
      <div class="tier">
        <span class="tname">Tier 01 · Pilot Launch</span>
        <div class="price">100<small>usuarios · prueba de producto</small></div>
        <p class="tdesc">“Prove the product. Win the first believers.”</p>
        <ul><li>CDN global · zero latency</li><li>DB footprint mínimo</li><li>Cache de sesión + rate-limit</li><li>Email transaccional incluido</li></ul>
        <a class="tbtn" href="#contacto">Consultar</a>
      </div>

      <div class="tier">
        <span class="tname">Tier 02 · Production</span>
        <div class="price">500<small>usuarios · primeros SLAs</small></div>
        <p class="tdesc">“First SLAs. First real commitments.”</p>
        <ul><li>DB producción · 60 conexiones</li><li>Almacenamiento escalable</li><li>Cache 5× dentro del free tier</li><li>Uptime garantizado</li></ul>
        <a class="tbtn" href="#contacto">Consultar</a>
      </div>

      <div class="tier hero-tier">
        <span class="badge">Recomendado</span>
        <span class="tname">Tier 03 · Growth Stage</span>
        <div class="price">1.000<small>usuarios · hockey stick</small></div>
        <p class="tdesc">“The hockey stick begins.”</p>
        <ul><li>Frontend · maxDuration 60s</li><li>DB Small · 2 GB RAM + PgBouncer</li><li>Cache 4× demanda · pay-as-you-go</li><li>Cero deuda técnica</li></ul>
        <a class="tbtn" href="#contacto">Consultar</a>
      </div>

      <div class="tier">
        <span class="tname">Tier 04 · Scale</span>
        <div class="price">3.000<small>usuarios · enterprise ready</small></div>
        <p class="tdesc">“Enterprise conversations start.”</p>
        <ul><li>DB Medium · 4 GB RAM on-demand</li><li>Cache hasta 1.8M comandos/mes</li><li>Speed Insights integrado</li><li>Right-size por deployment</li></ul>
        <a class="tbtn" href="#contacto">Consultar</a>
      </div>

      <div class="tier">
        <span class="tname">Tier 05 · Enterprise</span>
        <div class="price">10k+<small>usuarios · categoría definida</small></div>
        <p class="tdesc">“The category is defined. The moat is built.”</p>
        <ul><li>Frontend · SLA 99.99%</li><li>DB Large · 8 GB RAM · CPU dedicado</li><li>Cache Pro plan fijo</li><li>Soporte dedicado 24/7</li></ul>
        <a class="tbtn" href="#contacto">Hablar</a>
      </div>
    </div>

    <div class="tiers-cta">
      <div class="tiers-cta-txt">
        <div class="tiers-cta-meta">Decisión · 04.06</div>
        <div class="tiers-cta-title">¿No estás seguro <b>qué tier se ajusta</b> a tu operación?</div>
      </div>
      <a href="<?php echo htmlspecialchars($wa_tiers, ENT_QUOTES); ?>" target="_blank" rel="noopener" class="wa-btn">
        <svg class="wa-svg" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893A11.821 11.821 0 0020.885 3.488"/></svg>
        <span>Agenda 15 minutos</span>
        <span class="wa-arr">→</span>
      </a>
    </div>
  </div>
</section>

<!-- ============ FAQ ============ -->
<section class="faq" id="faq">
  <div class="container">
    <div class="sec-label">Preguntas — 005</div>
    <h2 class="sec-h">Todo lo que <b>nos preguntan</b> antes.</h2>

    <div class="faq-wrap">
      <div class="faq-item open">
        <button class="faq-q">¿Necesitamos experiencia previa con VR para usar sus simulaciones?<span class="faq-icon"></span></button>
        <div class="faq-a"><div class="faq-a-inner">No. Diseñamos un tutorial inicial de 90 segundos dentro de cada simulación. La idea es que cualquier operador con casco puesto pueda partir sin instrucciones externas.</div></div>
      </div>
      <div class="faq-item">
        <button class="faq-q">¿Qué tan personalizables son los contenidos?<span class="faq-icon"></span></button>
        <div class="faq-a"><div class="faq-a-inner">Trabajamos 100% a medida. Modelamos tus equipos reales a partir de planos, fotogrametría o escaneos, y replicamos tus procedimientos paso a paso con tu equipo de prevención.</div></div>
      </div>
      <div class="faq-item">
        <button class="faq-q">¿Cómo se integra CORE con nuestro LMS corporativo?<span class="faq-icon"></span></button>
        <div class="faq-a"><div class="faq-a-inner">CORE exporta paquetes SCORM 2004 3rd Edition y xAPI. Probado en Moodle, SAP SuccessFactors, Cornerstone y Totara. Si usas otro sistema, lo validamos en la sesión de scoping.</div></div>
      </div>
      <div class="faq-item">
        <button class="faq-q">¿Funciona sin conexión en faena remota?<span class="faq-icon"></span></button>
        <div class="faq-a"><div class="faq-a-inner">Sí. Las simulaciones corren localmente en el casco y sincronizan datos cuando hay conectividad. El plan On-premise incluye un servidor local para faenas sin internet estable.</div></div>
      </div>
      <div class="faq-item">
        <button class="faq-q">¿Venden o arriendan los cascos?<span class="faq-icon"></span></button>
        <div class="faq-a"><div class="faq-a-inner">Vendemos los cascos a precio retail, sin márgenes adicionales. Incluye SLA de reemplazo en 72 horas dentro de Chile. Optamos por este modelo para que el cliente sea dueño de su equipamiento desde el día uno.</div></div>
      </div>
      <div class="faq-item">
        <button class="faq-q">¿Qué ocurre con las apps a medida al finalizar el contrato?<span class="faq-icon"></span></button>
        <div class="faq-a"><div class="faq-a-inner">Las apps personalizadas siguen funcionando al terminar el contrato. Las dejamos configuradas para enviar reportes automáticos a un correo designado por ti. Las funciones de la plataforma CORE (gestión centralizada, dashboards, etc.) sí requieren contrato activo.</div></div>
      </div>
      <div class="faq-item">
        <button class="faq-q">¿Qué pasa con los datos de desempeño de los operadores?<span class="faq-icon"></span></button>
        <div class="faq-a"><div class="faq-a-inner">Cumplimos con la Ley 21.719 de protección de datos personales. El cliente es dueño de sus datos; nosotros actuamos como encargados de tratamiento con cláusulas estándar revisables.</div></div>
      </div>
    </div>

    <div class="faq-cta">
      <span class="faq-cta-lbl">¿No resolvimos algo?</span>
      <a href="<?php echo htmlspecialchars($wa_faq, ENT_QUOTES); ?>" target="_blank" rel="noopener" class="wa-link">
        <svg class="wa-svg" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893A11.821 11.821 0 0020.885 3.488"/></svg>
        Cuéntame sobre tu operación
        <span class="wa-arr">→</span>
      </a>
    </div>
  </div>
</section>

<?php if (!empty($blog_posts)): ?>
<!-- ============ BLOG / PUBLICACIONES ============ -->
<section class="blog" id="blog">
  <div class="container">
    <div class="sec-label">Publicaciones — 006</div>
    <h2 class="sec-h">Ideas y casos, <b>en movimiento.</b></h2>

    <div class="blog-grid">
      <?php foreach ($blog_posts as $i => $post): ?>
        <a class="blog-card" href="<?php echo htmlspecialchars($post['url'], ENT_QUOTES); ?>">
          <span class="blog-idx" aria-hidden="true"><?php echo str_pad($i+1, 2, '0', STR_PAD_LEFT); ?></span>
          <div class="blog-thumb">
            <?php if (!empty($post['thumb'])): ?>
              <img src="<?php echo htmlspecialchars($post['thumb'], ENT_QUOTES); ?>" alt="" loading="lazy" decoding="async">
            <?php else: ?>
              <span class="blog-thumb-empty">
                <span class="bte-mark">◆ Doble 3D</span>
                <span><?php echo htmlspecialchars($post['cat']); ?></span>
              </span>
            <?php endif; ?>
          </div>
          <div class="blog-meta">
            <span><?php echo htmlspecialchars($post['cat']); ?></span>
            <span class="sep" aria-hidden="true"></span>
            <span><?php echo htmlspecialchars($post['date']); ?></span>
          </div>
          <h3 class="blog-title"><?php echo htmlspecialchars($post['title']); ?></h3>
          <?php if (!empty($post['excerpt'])): ?>
            <p class="blog-excerpt"><?php echo htmlspecialchars($post['excerpt']); ?></p>
          <?php endif; ?>
          <span class="blog-arr">
            Leer publicación
            <svg viewBox="0 0 36 10" fill="none" stroke="currentColor" stroke-width="1" aria-hidden="true"><line x1="0" y1="5" x2="32" y2="5"/><polyline points="28 1 34 5 28 9"/></svg>
          </span>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- ============ CONTACTO ============ -->
<section class="contact" id="contacto">
  <div class="container">
    <div class="sec-label">Contacto — 007</div>

    <div class="contact-grid">
      <div class="c-left">
        <h2>Hablemos de <b>tu próximo proyecto.</b></h2>
        <div class="c-list">
          <a class="c-item" href="mailto:dwolfft@doble3d.cl">
            <span class="ilab">Correo</span>
            <span class="ival">dwolfft@doble3d.cl</span>
            <span class="iarr">→</span>
          </a>
          <a class="c-item" href="https://wa.me/56958015971" target="_blank" rel="noopener">
            <span class="ilab">WhatsApp</span>
            <span class="ival">+56 9 5801 5971</span>
            <span class="iarr">→</span>
          </a>
          <a class="c-item" href="#">
            <span class="ilab">Horario</span>
            <span class="ival">Lun a Vie · 09:00–19:00</span>
            <span class="iarr">→</span>
          </a>
        </div>
      </div>

      <form class="form" id="contactForm" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="POST">
        <input type="hidden" name="action" value="d3d_contact">
        <?php wp_nonce_field('d3d_contact', 'd3d_nonce'); ?>
        <input type="hidden" name="form_ts" value="<?php echo time(); ?>">
        <input type="text" name="website" style="position:absolute;left:-9999px;opacity:0;height:0;width:0;" tabindex="-1" autocomplete="off" aria-hidden="true">

        <div class="form-row">
          <div class="field"><label for="f-nombre">Nombre</label><input id="f-nombre" type="text" name="nombre" placeholder="Tu nombre" required></div>
          <div class="field"><label for="f-empresa">Empresa</label><input id="f-empresa" type="text" name="empresa" placeholder="Razón social"></div>
        </div>
        <div class="form-row">
          <div class="field"><label for="f-correo">Correo</label><input id="f-correo" type="email" name="correo" placeholder="tucorreo@empresa.cl" required></div>
          <div class="field"><label for="f-telefono">Teléfono</label><input id="f-telefono" type="tel" name="telefono" placeholder="+56 9 ..."></div>
        </div>
        <div class="field" style="margin-bottom:28px;">
          <label for="f-solucion">Solución de interés</label>
          <select id="f-solucion" name="solucion">
            <option>Armado VR</option><option>Videos 3D</option>
            <option>Visualizador Onboarding</option><option>App Gamificada</option>
            <option>Plataforma CORE</option><option>Asesoría</option>
          </select>
        </div>
        <div class="field">
          <label for="f-mensaje">Mensaje</label>
          <textarea id="f-mensaje" name="mensaje" placeholder="Cuéntanos qué necesitas entrenar, para cuántas personas y en qué faena." required></textarea>
        </div>
        <button type="submit" class="form-submit">
          <span class="form-submit-lbl">Enviar mensaje</span>
          <svg viewBox="0 0 36 10" fill="none" stroke="currentColor" stroke-width="1"><line x1="0" y1="5" x2="32" y2="5"/><polyline points="28 1 34 5 28 9"/></svg>
        </button>
      </form>

      <div class="form-success" id="formSuccess" hidden>
        <div class="form-success-mark" aria-hidden="true">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M4.5 12.5l5 5L19.5 7.5"/></svg>
        </div>
        <div class="form-success-eyebrow">Transmisión recibida</div>
        <h3 class="form-success-title">Mensaje enviado.</h3>
        <p class="form-success-copy">Te respondemos dentro de <b>un día hábil</b>. Si prefieres adelantar, puedes escribirnos directo por WhatsApp — tenemos el contexto a mano.</p>
        <div class="form-success-actions">
          <a href="<?php echo htmlspecialchars($wa_faq, ENT_QUOTES); ?>" target="_blank" rel="noopener" class="wa-btn">
            <svg class="wa-svg" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893A11.821 11.821 0 0020.885 3.488"/></svg>
            <span>Continuar por WhatsApp</span>
            <span class="wa-arr">→</span>
          </a>
          <button type="button" class="form-success-reset" id="formReset">
            <span>Enviar otro</span>
            <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><polyline points="3 8 8 3 13 8"/><polyline points="3 13 8 8 13 13" style="opacity:0.4"/></svg>
          </button>
        </div>
        <div class="form-success-meta">
          <span>REQ confirmado · <?php echo $year; ?>/<span id="formRef">0001</span></span>
          <span class="form-success-dot"></span>
          <span>Autoresponse enviado a tu correo</span>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ============ FOOTER ============ -->
<footer>
  <div class="container foot">
    <a class="logo" href="#" aria-label="Doble 3D — inicio"><img src="<?php echo htmlspecialchars($assets_url, ENT_QUOTES); ?>/logo-nuevo-420.webp" srcset="<?php echo htmlspecialchars($assets_url, ENT_QUOTES); ?>/logo-nuevo-420.webp 1x, <?php echo htmlspecialchars($assets_url, ENT_QUOTES); ?>/logo-nuevo-630.webp 1.5x" width="420" height="126" alt="Doble 3D" loading="lazy" decoding="async"></a>
    <div class="foot-links">
      <a href="https://doble3d.cl/blog/" rel="noopener">Blog</a>
      <a href="https://www.linkedin.com/company/doble-3d/" target="_blank" rel="noopener">LinkedIn</a>
      <a href="https://www.youtube.com/@doble3d" target="_blank" rel="noopener">YouTube</a>
      <a href="https://doble3d.cl/politicas-de-privacidad/" target="_blank" rel="noopener">Privacidad</a>
    </div>
    <div class="foot-cp">© <?php echo $year; ?> · SpA · Santiago</div>
  </div>
</footer>

<!-- ============ FLOATING WHATSAPP ============ -->
<a href="<?php echo htmlspecialchars($wa_float, ENT_QUOTES); ?>" target="_blank" rel="noopener" class="wa-float" id="waFloat" aria-label="Consulta por WhatsApp">
  <span class="wa-float-icon" aria-hidden="true">
    <svg viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893A11.821 11.821 0 0020.885 3.488"/></svg>
  </span>
  <span class="wa-float-text">Consulta por WhatsApp</span>
</a>

<!-- ============ BACK TO TOP ============ -->
<button type="button" class="to-top" id="toTop" aria-label="Volver arriba">
  <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true">
    <polyline points="3 9 8 4 13 9"/>
    <line x1="8" y1="4" x2="8" y2="13"/>
  </svg>
</button>

<!-- ============ LIGHTBOX (video modal) ============ -->
<div class="lightbox" id="lightbox" role="dialog" aria-modal="true" aria-label="Reproductor de video">
  <button class="lightbox-close" id="lightboxClose" type="button" aria-label="Cerrar video">×</button>
  <div class="lightbox-inner">
    <div class="lightbox-frame" id="lightboxFrame"></div>
  </div>
</div>

<?php wp_footer(); ?>
</body>
</html>
