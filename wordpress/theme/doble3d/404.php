<?php if (!defined('ABSPATH')) { exit; } ?><!doctype html>
<html lang="es-CL">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="robots" content="noindex,follow">
<title>404 — Doble 3D</title>
<?php wp_head(); ?>
<style>
  html,body{margin:0;padding:0;background:#080808;color:#d4d4d4;font-family:ui-sans-serif,system-ui,sans-serif;}
  .wrap{min-height:100vh;display:flex;flex-direction:column;align-items:center;justify-content:center;text-align:center;padding:40px 24px;gap:24px;}
  h1{font-size:clamp(36px,8vw,72px);font-weight:300;letter-spacing:-0.02em;color:#fff;margin:0;}
  p{color:#a3a3a3;font-size:16px;max-width:48ch;line-height:1.6;margin:0;}
  a{display:inline-block;padding:14px 28px;border:1px solid #a78bfa;color:#a78bfa;text-decoration:none;font-family:ui-monospace,monospace;font-size:11px;letter-spacing:0.22em;text-transform:uppercase;transition:all .25s;}
  a:hover{background:#a78bfa;color:#080808;}
</style>
</head>
<body <?php body_class(); ?>>
<div class="wrap">
  <h1>404</h1>
  <p>Esta página no existe o cambió de ubicación. Vuelve a la portada para encontrar lo que buscas.</p>
  <a href="<?php echo esc_url(home_url('/')); ?>">Volver al inicio</a>
</div>
<?php wp_footer(); ?>
</body>
</html>
