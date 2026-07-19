<?php
/**
 * Fila de un post en el listado del blog. Debe llamarse dentro del loop (the_post()).
 * Usado por home.php, template-blog.php, index.php, archive.php.
 */
if (!defined('ABSPATH')) { exit; }
$d3d_cats = get_the_category();
$d3d_cat  = !empty($d3d_cats) ? $d3d_cats[0]->name : 'Blog';
$d3d_exc  = wp_trim_words(get_the_excerpt() ?: wp_strip_all_tags(get_the_content()), 28, '…');
?>
<a class="post-row" href="<?php the_permalink(); ?>">
  <div class="post-row-meta">
    <span><?php echo esc_html($d3d_cat); ?></span>
    <span class="sep" aria-hidden="true"></span>
    <span><?php echo esc_html(get_the_date('d.m.Y')); ?></span>
  </div>
  <div class="post-row-title"><?php echo esc_html(wp_strip_all_tags(get_the_title())); ?></div>
  <?php if ($d3d_exc) : ?><p class="post-row-excerpt"><?php echo esc_html($d3d_exc); ?></p><?php endif; ?>
</a>
