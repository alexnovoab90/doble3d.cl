<?php
/**
 * Header para páginas internas (page.php, single.php, index.php, archive.php, search.php).
 * NOTA: front-page.php NO usa este header — es un documento autónomo optimizado para el critical path.
 */
if (!defined('ABSPATH')) { exit; }
$d3d_assets = get_stylesheet_directory_uri() . '/assets';
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo('charset'); ?>">
<meta name="viewport" content="width=device-width,initial-scale=1">
<link rel="preload" as="font" type="font/woff2" crossorigin href="<?php echo esc_url($d3d_assets); ?>/Geist-var.woff2">
<?php wp_head(); ?>
<style>
  @font-face{font-family:'Geist';font-style:normal;font-weight:100 900;font-display:swap;
    src:url('<?php echo esc_url($d3d_assets); ?>/Geist-var.woff2') format('woff2-variations'),
        url('<?php echo esc_url($d3d_assets); ?>/Geist-var.woff2') format('woff2');font-stretch:100%;}
  @font-face{font-family:'IBM Plex Mono';font-style:normal;font-weight:400;font-display:swap;
    src:url('<?php echo esc_url($d3d_assets); ?>/IBMPlexMono-400.woff2') format('woff2');}
  @font-face{font-family:'IBM Plex Mono';font-style:normal;font-weight:500;font-display:swap;
    src:url('<?php echo esc_url($d3d_assets); ?>/IBMPlexMono-500.woff2') format('woff2');}

  :root{
    --bg-0:#080808;--bg-1:#0f0f0f;--bg-2:#141414;
    --border-s:rgba(255,255,255,0.08);--border-m:rgba(255,255,255,0.14);
    --text:#d4d4d4;--white:#ffffff;--muted:#a3a3a3;--subtle:#808080;
    --accent:#7c3aed;--accent-light:#a78bfa;
    --sans:'Geist', ui-sans-serif, system-ui, sans-serif;
    --mono:'IBM Plex Mono', ui-monospace, 'SF Mono', Menlo, monospace;
  }
  *{box-sizing:border-box;margin:0;padding:0;}
  html,body{margin:0 !important;padding:0 !important;background:var(--bg-0);color:var(--text);
    font-family:var(--sans);font-weight:400;-webkit-font-smoothing:antialiased;text-rendering:optimizeLegibility;
    overflow-x:hidden;width:100%;}
  a{color:inherit;text-decoration:none;}
  button{font-family:inherit;background:none;border:0;color:inherit;cursor:pointer;}
  img{display:block;max-width:100%;height:auto;}
  ::selection{background:var(--accent);color:#fff;}
  .container{max-width:1100px;margin:0 auto;padding:0 40px;}

  /* ===== NAV (sólida desde el inicio en páginas internas) ===== */
  .nav{position:fixed;top:0;left:0;right:0;z-index:60;display:grid;grid-template-columns:1fr auto 1fr;align-items:center;
       padding:18px clamp(40px,2.2vw,64px);background:rgba(8,8,8,0.9);
       backdrop-filter:blur(20px);-webkit-backdrop-filter:blur(20px);border-bottom:1px solid var(--border-s);}
  .nav > .logo{justify-self:start;}
  .nav > .nav-links{justify-self:center;}
  .nav > .nav-right{justify-self:end;}
  .logo{display:inline-flex;align-items:center;color:var(--white);}
  .logo img{display:block;height:34px;width:auto;}
  .nav .nav-links{display:flex;gap:2px;}
  .nav .nav-links a{font-family:var(--mono);font-size:11px;letter-spacing:0.14em;text-transform:uppercase;color:var(--text);
               padding:10px 16px;transition:color .2s;}
  .nav .nav-links a:hover{color:var(--white);}
  .nav-right{display:flex;align-items:center;gap:22px;}
  .nav-cta{font-family:var(--mono);font-size:11px;letter-spacing:0.14em;text-transform:uppercase;color:var(--white);
           display:inline-flex;align-items:center;gap:8px;transition:color .2s;}
  .nav-cta::before{content:"";width:6px;height:6px;border-radius:50%;background:var(--accent);box-shadow:0 0 12px rgba(124,58,237,0.7);}
  .nav-cta:hover{color:var(--accent-light);}

  /* ===== CONTENIDO ===== */
  .inner-main{padding:150px 0 120px;min-height:62vh;}
  .page-head{max-width:820px;margin:0 auto 56px;}
  .page-eyebrow{font-family:var(--mono);font-size:10px;letter-spacing:0.28em;text-transform:uppercase;color:var(--subtle);
                margin-bottom:24px;display:flex;align-items:center;gap:16px;}
  .page-eyebrow::before{content:"";width:40px;height:1px;background:var(--subtle);}
  .page-title{font-family:var(--sans);font-size:clamp(34px,5vw,64px);line-height:1.02;letter-spacing:-0.04em;
              font-weight:700;color:var(--white);}
  .page-meta{font-family:var(--mono);font-size:11px;letter-spacing:0.16em;text-transform:uppercase;color:var(--subtle);
             margin-top:18px;display:flex;gap:14px;flex-wrap:wrap;align-items:center;}
  .page-meta .sep{width:1px;height:11px;background:var(--border-m);}

  /* breadcrumbs (single) */
  .crumbs{font-family:var(--mono);font-size:10px;letter-spacing:0.18em;text-transform:uppercase;color:var(--subtle);
          margin-bottom:24px;display:flex;flex-wrap:wrap;gap:10px;align-items:center;}
  .crumbs a{color:var(--subtle);transition:color .2s;}
  .crumbs a:hover{color:var(--white);}
  .crumbs-sep{color:var(--border-m);}

  /* bloque "servicios relacionados" (single) */
  .post-services{max-width:760px;margin:64px auto 0;padding-top:40px;border-top:1px solid var(--border-s);}
  .post-services-eyebrow{font-family:var(--mono);font-size:10px;letter-spacing:0.22em;text-transform:uppercase;
                         color:var(--accent-light);margin-bottom:24px;}
  .post-services-grid{display:grid;grid-template-columns:1fr 1fr;gap:12px;}
  .post-service{display:flex;flex-direction:column;gap:6px;padding:20px;border:1px solid var(--border-s);
                border-radius:6px;transition:border-color .2s,transform .2s;}
  .post-service:hover{border-color:var(--border-m);transform:translateY(-2px);}
  .post-service-title{font-family:var(--sans);font-size:16px;font-weight:600;color:var(--white);letter-spacing:-0.01em;}
  .post-service-desc{font-size:13px;line-height:1.5;color:var(--muted);}
  .post-services-cta{display:inline-flex;margin-top:24px;font-family:var(--mono);font-size:11px;letter-spacing:0.16em;
                     text-transform:uppercase;color:var(--accent-light);transition:color .2s,gap .3s;gap:8px;}
  .post-services-cta:hover{color:var(--white);}

  /* posts relacionados (single) */
  .related-posts{max-width:820px;margin:64px auto 0;}
  .related-posts-head{font-family:var(--mono);font-size:10px;letter-spacing:0.22em;text-transform:uppercase;
                      color:var(--subtle);margin-bottom:8px;}

  /* prose */
  .prose{max-width:760px;margin:0 auto;font-size:16px;line-height:1.75;color:var(--text);}
  .prose > * + *{margin-top:1.25em;}
  .prose h2{font-family:var(--sans);font-size:clamp(24px,3vw,34px);font-weight:600;color:var(--white);
            letter-spacing:-0.025em;line-height:1.15;margin-top:2em;margin-bottom:.2em;}
  .prose h3{font-family:var(--sans);font-size:clamp(19px,2.2vw,24px);font-weight:600;color:var(--white);
            letter-spacing:-0.02em;margin-top:1.6em;margin-bottom:.1em;}
  .prose h4{font-family:var(--mono);font-size:12px;letter-spacing:0.18em;text-transform:uppercase;color:var(--accent-light);
            margin-top:1.6em;}
  .prose p{color:var(--text);}
  .prose a{color:var(--accent-light);text-decoration:underline;text-underline-offset:3px;transition:color .2s;}
  .prose a:hover{color:var(--white);}
  .prose strong{color:var(--white);font-weight:600;}
  .prose ul,.prose ol{padding-left:1.4em;color:var(--text);}
  .prose li{margin-top:.5em;}
  .prose li::marker{color:var(--accent);}
  .prose blockquote{border-left:2px solid var(--accent);padding:4px 0 4px 24px;color:var(--muted);font-style:italic;}
  .prose img,.prose figure{border-radius:6px;border:1px solid var(--border-s);}
  .prose figure{padding:0;overflow:hidden;}
  .prose figcaption{font-family:var(--mono);font-size:11px;letter-spacing:0.06em;color:var(--subtle);margin-top:8px;text-align:center;}
  .prose hr{border:0;border-top:1px solid var(--border-s);margin:2.5em 0;}
  .prose code{font-family:var(--mono);font-size:0.88em;background:var(--bg-2);padding:2px 6px;border-radius:3px;color:var(--accent-light);}
  .prose pre{background:var(--bg-1);border:1px solid var(--border-s);border-radius:6px;padding:20px;overflow:auto;}
  .prose pre code{background:none;padding:0;color:var(--text);}
  .prose table{width:100%;border-collapse:collapse;font-size:14px;}
  .prose th,.prose td{border:1px solid var(--border-s);padding:10px 14px;text-align:left;vertical-align:top;}
  .prose th{background:var(--bg-1);color:var(--white);font-weight:600;}

  .back-link{display:inline-flex;align-items:center;gap:10px;margin-top:64px;font-family:var(--mono);font-size:10px;
             letter-spacing:0.22em;text-transform:uppercase;color:var(--subtle);transition:color .2s,gap .3s;}
  .back-link:hover{color:var(--white);gap:16px;}

  /* blog listing (index/archive) */
  .post-list{max-width:820px;margin:0 auto;display:flex;flex-direction:column;gap:2px;
             border-top:1px solid var(--border-s);}
  .post-row{display:block;padding:32px 0;border-bottom:1px solid var(--border-s);transition:padding-left .25s;}
  .post-row:hover{padding-left:10px;}
  .post-row-meta{font-family:var(--mono);font-size:10px;letter-spacing:0.2em;text-transform:uppercase;color:var(--subtle);
                 margin-bottom:12px;display:flex;gap:12px;align-items:center;}
  .post-row-title{font-family:var(--sans);font-size:clamp(20px,2.4vw,26px);font-weight:500;color:var(--white);
                  letter-spacing:-0.02em;line-height:1.25;transition:color .2s;}
  .post-row:hover .post-row-title{color:var(--accent-light);}
  .post-row-excerpt{color:var(--muted);font-size:14px;line-height:1.6;margin-top:10px;max-width:64ch;}

  /* navegación entre entradas (single) */
  .post-nav{max-width:760px;margin:64px auto 0;display:grid;grid-template-columns:1fr 1fr;gap:16px;
            border-top:1px solid var(--border-s);padding-top:32px;}
  .post-nav-item{display:flex;flex-direction:column;gap:8px;padding:18px 20px;border:1px solid var(--border-s);
                 border-radius:6px;transition:border-color .2s,transform .2s;}
  .post-nav-item:hover{border-color:var(--border-m);transform:translateY(-2px);}
  .post-nav-item.next{text-align:right;}
  .post-nav-dir{font-family:var(--mono);font-size:10px;letter-spacing:0.2em;text-transform:uppercase;color:var(--subtle);}
  .post-nav-title{font-size:15px;color:var(--white);line-height:1.3;}

  /* paginación (listados) */
  .pagination,.nav-links.pagination{display:flex;flex-wrap:wrap;gap:8px;justify-content:center;}
  .page-numbers{font-family:var(--mono);font-size:12px;letter-spacing:0.08em;color:var(--text);
                padding:10px 16px;border:1px solid var(--border-s);border-radius:4px;transition:border-color .2s,color .2s;}
  a.page-numbers:hover{border-color:var(--border-m);color:var(--white);}
  .page-numbers.current{background:var(--accent);border-color:var(--accent);color:#fff;}
  .page-numbers.dots{border-color:transparent;}

  /* footer */
  footer{border-top:1px solid var(--border-s);padding:64px 0 48px;margin-top:40px;}
  .foot{display:grid;grid-template-columns:1fr auto 1fr;align-items:center;gap:32px;}
  .foot-cp{color:var(--subtle);font-family:var(--mono);font-size:10px;letter-spacing:0.22em;text-transform:uppercase;text-align:right;}
  .foot-links{display:flex;gap:32px;justify-content:center;}
  .foot-links a{font-family:var(--mono);font-size:10px;letter-spacing:0.22em;text-transform:uppercase;color:var(--muted);transition:color .2s;}
  .foot-links a:hover{color:var(--white);}

  @media (max-width:960px){
    .container{padding:0 24px;}
    .nav .nav-links{display:none;}
    .inner-main{padding:110px 0 80px;}
    .post-nav{grid-template-columns:1fr;}
    .post-nav-item.next{text-align:left;}
    .post-services-grid{grid-template-columns:1fr;}
    .foot{grid-template-columns:1fr;text-align:center;}
    .foot-cp{text-align:center;}
    .foot-links{flex-wrap:wrap;gap:18px 24px;}
  }
</style>
</head>
<body <?php body_class(); ?>>
<nav class="nav" id="nav">
  <a href="<?php echo esc_url(home_url('/')); ?>" class="logo" aria-label="Doble 3D — inicio">
    <img src="<?php echo esc_url($d3d_assets); ?>/logo-nuevo-420.webp"
         srcset="<?php echo esc_url($d3d_assets); ?>/logo-nuevo-420.webp 1x, <?php echo esc_url($d3d_assets); ?>/logo-nuevo-630.webp 1.5x"
         width="420" height="126" alt="Doble 3D" decoding="async">
  </a>
  <div class="nav-links">
    <a href="<?php echo esc_url(home_url('/#servicios')); ?>">Servicios</a>
    <a href="<?php echo esc_url(home_url('/#core')); ?>">CORE</a>
    <a href="<?php echo esc_url(home_url('/#tiers')); ?>">Planes</a>
    <a href="<?php echo esc_url(home_url('/blog/')); ?>">Blog</a>
    <a href="<?php echo esc_url(home_url('/#faq')); ?>">FAQ</a>
  </div>
  <div class="nav-right">
    <a href="<?php echo esc_url(home_url('/#contacto')); ?>" class="nav-cta">Hablemos</a>
  </div>
</nav>
<main class="inner-main">
