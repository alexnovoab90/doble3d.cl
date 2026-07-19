<?php
/**
 * Doble 3D — Cookie banner: menú admin + formulario de configuración.
 *
 * Crea un menú top-level "Doble 3D" con icono dashicons-admin-customizer.
 * Submenú "Banner de Cookies".
 */

if (!defined('ABSPATH')) { exit; }

add_action('admin_menu', function () {
    // Menú top-level
    add_menu_page(
        'Doble 3D',
        'Doble 3D',
        'manage_options',
        'doble3d',
        'd3d_admin_landing_page',
        'dashicons-admin-customizer',
        58
    );

    // Submenú: redefine la primera entrada del menú padre para no duplicar.
    add_submenu_page(
        'doble3d',
        'Banner de Cookies — Doble 3D',
        'Banner de Cookies',
        'manage_options',
        'doble3d-cookies',
        'd3d_cookies_admin_page'
    );

    // Quita la entrada auto-generada que repite el slug del padre.
    remove_submenu_page('doble3d', 'doble3d');
});

/**
 * Landing del menú padre (redirige al submenú).
 */
function d3d_admin_landing_page() {
    wp_safe_redirect(admin_url('admin.php?page=doble3d-cookies'));
    exit;
}

/**
 * Enqueue color-picker solo en la página del banner.
 */
add_action('admin_enqueue_scripts', function ($hook) {
    if (strpos($hook, 'doble3d-cookies') === false) { return; }
    wp_enqueue_style('wp-color-picker');
    wp_enqueue_script('wp-color-picker');
    wp_add_inline_script('wp-color-picker',
        'jQuery(function($){ $(".doble3d-color-picker").wpColorPicker(); });'
    );
});

/**
 * Render del formulario de configuración.
 */
function d3d_cookies_admin_page() {
    if (!current_user_can('manage_options')) { return; }

    $opts = wp_parse_args(get_option('doble3d_cookies_options', []), d3d_cookies_defaults());
    ?>
    <div class="wrap">
        <h1>Banner de Cookies</h1>
        <p>Configura el banner de consentimiento de cookies del sitio. Los cambios aplican al frontend al guardar.</p>

        <form method="post" action="options.php">
            <?php settings_fields('d3d_cookies_group'); ?>

            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row"><label>Activar banner</label></th>
                    <td>
                        <label>
                            <input type="checkbox" name="doble3d_cookies_options[enabled]" value="1" <?php checked($opts['enabled'], 1); ?>>
                            Mostrar el banner en el frontend
                        </label>
                    </td>
                </tr>

                <tr>
                    <th scope="row"><label for="d3d-title">Título</label></th>
                    <td>
                        <input type="text" id="d3d-title" name="doble3d_cookies_options[title]" class="regular-text" value="<?php echo esc_attr($opts['title']); ?>">
                    </td>
                </tr>

                <tr>
                    <th scope="row"><label for="d3d-message">Mensaje</label></th>
                    <td>
                        <textarea id="d3d-message" name="doble3d_cookies_options[message]" rows="4" class="large-text"><?php echo esc_textarea($opts['message']); ?></textarea>
                        <p class="description">Acepta HTML básico: &lt;a&gt;, &lt;strong&gt;, &lt;em&gt;.</p>
                    </td>
                </tr>

                <tr>
                    <th scope="row"><label for="d3d-accept">Texto botón aceptar</label></th>
                    <td>
                        <input type="text" id="d3d-accept" name="doble3d_cookies_options[accept_text]" class="regular-text" value="<?php echo esc_attr($opts['accept_text']); ?>">
                    </td>
                </tr>

                <tr>
                    <th scope="row"><label>Mostrar botón rechazar</label></th>
                    <td>
                        <label>
                            <input type="checkbox" name="doble3d_cookies_options[show_reject]" value="1" <?php checked($opts['show_reject'], 1); ?>>
                            Mostrar opción "Solo necesarias"
                        </label>
                    </td>
                </tr>

                <tr>
                    <th scope="row"><label for="d3d-reject">Texto botón rechazar</label></th>
                    <td>
                        <input type="text" id="d3d-reject" name="doble3d_cookies_options[reject_text]" class="regular-text" value="<?php echo esc_attr($opts['reject_text']); ?>">
                    </td>
                </tr>

                <tr>
                    <th scope="row"><label for="d3d-privacy-url">Enlace a política</label></th>
                    <td>
                        <input type="url" id="d3d-privacy-url" name="doble3d_cookies_options[privacy_url]" class="regular-text code" value="<?php echo esc_attr($opts['privacy_url']); ?>">
                    </td>
                </tr>

                <tr>
                    <th scope="row"><label for="d3d-privacy-text">Texto del enlace</label></th>
                    <td>
                        <input type="text" id="d3d-privacy-text" name="doble3d_cookies_options[privacy_text]" class="regular-text" value="<?php echo esc_attr($opts['privacy_text']); ?>">
                    </td>
                </tr>

                <tr>
                    <th scope="row"><label for="d3d-position">Posición</label></th>
                    <td>
                        <select id="d3d-position" name="doble3d_cookies_options[position]">
                            <?php
                            $positions = [
                                'bottom-right' => 'Abajo derecha',
                                'bottom-left'  => 'Abajo izquierda',
                                'bottom'       => 'Abajo centrado',
                                'top'          => 'Arriba centrado',
                            ];
                            foreach ($positions as $val => $label) {
                                printf('<option value="%s" %s>%s</option>',
                                    esc_attr($val),
                                    selected($opts['position'], $val, false),
                                    esc_html($label)
                                );
                            }
                            ?>
                        </select>
                    </td>
                </tr>

                <tr>
                    <th scope="row">Colores</th>
                    <td>
                        <p>
                            <label style="display:inline-block;width:170px;">Fondo:</label>
                            <input type="text" class="doble3d-color-picker" name="doble3d_cookies_options[bg_color]" value="<?php echo esc_attr($opts['bg_color']); ?>" data-default-color="#0f0f0f">
                        </p>
                        <p>
                            <label style="display:inline-block;width:170px;">Texto:</label>
                            <input type="text" class="doble3d-color-picker" name="doble3d_cookies_options[text_color]" value="<?php echo esc_attr($opts['text_color']); ?>" data-default-color="#d4d4d4">
                        </p>
                        <p>
                            <label style="display:inline-block;width:170px;">Botón aceptar (fondo):</label>
                            <input type="text" class="doble3d-color-picker" name="doble3d_cookies_options[btn_bg]" value="<?php echo esc_attr($opts['btn_bg']); ?>" data-default-color="#7c3aed">
                        </p>
                        <p>
                            <label style="display:inline-block;width:170px;">Botón aceptar (texto):</label>
                            <input type="text" class="doble3d-color-picker" name="doble3d_cookies_options[btn_fg]" value="<?php echo esc_attr($opts['btn_fg']); ?>" data-default-color="#ffffff">
                        </p>
                    </td>
                </tr>

                <tr>
                    <th scope="row"><label for="d3d-expiry">Días de expiración</label></th>
                    <td>
                        <input type="number" id="d3d-expiry" name="doble3d_cookies_options[expiry_days]" min="1" max="3650" value="<?php echo esc_attr($opts['expiry_days']); ?>" class="small-text">
                        <p class="description">Cuántos días dura la decisión del usuario antes de que vuelva a aparecer el banner.</p>
                    </td>
                </tr>
            </table>

            <?php submit_button('Guardar cambios'); ?>
        </form>
    </div>
    <?php
}
