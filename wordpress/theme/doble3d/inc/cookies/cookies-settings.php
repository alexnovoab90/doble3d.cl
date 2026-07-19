<?php
/**
 * Doble 3D — Cookie banner: registro de option + sanitización (Settings API).
 *
 * Estructura de la option `doble3d_cookies_options`:
 *   enabled        bool
 *   title          string
 *   message        html limitado (wp_kses_post)
 *   accept_text    string
 *   reject_text    string
 *   show_reject    bool
 *   privacy_url    url
 *   privacy_text   string
 *   position       enum: bottom | top | bottom-left | bottom-right
 *   bg_color       hex
 *   text_color     hex
 *   btn_bg         hex
 *   btn_fg         hex
 *   expiry_days    int
 */

if (!defined('ABSPATH')) { exit; }

/**
 * Defaults — colores alineados al theme actual (--bg-1, --text, --accent).
 */
function d3d_cookies_defaults() {
    return [
        'enabled'      => 1,
        'title'        => 'Usamos cookies',
        'message'      => 'Este sitio utiliza cookies para mejorar tu experiencia, analizar el tráfico y personalizar contenido. Al continuar navegando aceptás nuestro uso de cookies.',
        'accept_text'  => 'Aceptar todas',
        'reject_text'  => 'Solo necesarias',
        'show_reject'  => 1,
        'privacy_url'  => 'https://doble3d.cl/politicas-de-privacidad/',
        'privacy_text' => 'Leer política de privacidad',
        'position'     => 'bottom-right',
        'bg_color'     => '#0f0f0f',
        'text_color'   => '#d4d4d4',
        'btn_bg'       => '#7c3aed',
        'btn_fg'       => '#ffffff',
        'expiry_days'  => 180,
    ];
}

/**
 * Sanitiza el array completo antes de guardar.
 */
function d3d_cookies_sanitize($input) {
    $defaults = d3d_cookies_defaults();
    $clean    = [];

    $clean['enabled']      = !empty($input['enabled']) ? 1 : 0;
    $clean['title']        = isset($input['title']) ? sanitize_text_field($input['title']) : $defaults['title'];
    $clean['message']      = isset($input['message']) ? wp_kses_post($input['message']) : $defaults['message'];
    $clean['accept_text']  = isset($input['accept_text']) ? sanitize_text_field($input['accept_text']) : $defaults['accept_text'];
    $clean['reject_text']  = isset($input['reject_text']) ? sanitize_text_field($input['reject_text']) : $defaults['reject_text'];
    $clean['show_reject']  = !empty($input['show_reject']) ? 1 : 0;
    $clean['privacy_url']  = isset($input['privacy_url']) ? esc_url_raw($input['privacy_url']) : $defaults['privacy_url'];
    $clean['privacy_text'] = isset($input['privacy_text']) ? sanitize_text_field($input['privacy_text']) : $defaults['privacy_text'];

    $valid_positions = ['bottom', 'top', 'bottom-left', 'bottom-right'];
    $clean['position'] = (isset($input['position']) && in_array($input['position'], $valid_positions, true))
        ? $input['position']
        : $defaults['position'];

    foreach (['bg_color', 'text_color', 'btn_bg', 'btn_fg'] as $k) {
        $hex = isset($input[$k]) ? sanitize_hex_color($input[$k]) : null;
        $clean[$k] = $hex ?: $defaults[$k];
    }

    $clean['expiry_days'] = isset($input['expiry_days']) ? max(1, absint($input['expiry_days'])) : $defaults['expiry_days'];

    return $clean;
}

add_action('admin_init', function () {
    register_setting('d3d_cookies_group', 'doble3d_cookies_options', [
        'type'              => 'array',
        'sanitize_callback' => 'd3d_cookies_sanitize',
        'default'           => d3d_cookies_defaults(),
    ]);

    // Sembramos defaults la primera vez que se carga el admin.
    if (false === get_option('doble3d_cookies_options')) {
        add_option('doble3d_cookies_options', d3d_cookies_defaults());
    }
});
