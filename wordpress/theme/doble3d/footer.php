<?php
/**
 * Footer para páginas internas. front-page.php tiene su propio footer inline.
 */
if (!defined('ABSPATH')) { exit; }
$d3d_assets = get_stylesheet_directory_uri() . '/assets';
?>
</main>

<footer>
  <div class="container foot">
    <a class="logo" href="<?php echo esc_url(home_url('/')); ?>" aria-label="Doble 3D — inicio">
      <img src="<?php echo esc_url($d3d_assets); ?>/logo-nuevo-420.webp"
           srcset="<?php echo esc_url($d3d_assets); ?>/logo-nuevo-420.webp 1x, <?php echo esc_url($d3d_assets); ?>/logo-nuevo-630.webp 1.5x"
           width="420" height="126" alt="Doble 3D" loading="lazy" decoding="async">
    </a>
    <div class="foot-links">
      <a href="<?php echo esc_url(home_url('/blog/')); ?>">Blog</a>
      <a href="https://www.linkedin.com/company/doble-3d/" target="_blank" rel="noopener">LinkedIn</a>
      <a href="https://www.youtube.com/@doble3d" target="_blank" rel="noopener">YouTube</a>
      <a href="<?php echo esc_url(home_url('/politicas-de-privacidad/')); ?>">Privacidad</a>
    </div>
    <div class="foot-cp">© <?php echo esc_html(date('Y')); ?> · SpA · Santiago</div>
  </div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
