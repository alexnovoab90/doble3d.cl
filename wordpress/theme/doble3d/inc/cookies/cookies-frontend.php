<?php
/**
 * Doble 3D — Cookie banner: render frontend + enqueue de CSS/JS.
 *
 * Sólo enquota assets y pinta el banner si:
 *   - El banner está activado en admin
 *   - El usuario no tiene cookie de consentimiento todavía
 *     (lo verifica el JS en el cliente; el render server-side siempre lo emite
 *      cuando enabled, y el JS lo oculta/elimina si ya hay cookie.)
 */

if (!defined('ABSPATH')) { exit; }

/**
 * Helper: obtiene la option mezclada con defaults — funciona aunque admin
 * nunca haya guardado nada.
 */
function d3d_cookies_get_opts() {
    $defaults = function_exists('d3d_cookies_defaults') ? d3d_cookies_defaults() : [];
    $stored   = get_option('doble3d_cookies_options', []);
    if (!is_array($stored)) { $stored = []; }
    return wp_parse_args($stored, $defaults);
}

/**
 * Declara este banner como gestor de consentimiento opt-in ante wp-consent-api.
 * Con esto, Site Kit (Consent Mode v2) trata el consentimiento como DENEGADO por
 * defecto hasta que el visitante acepte, y el wp_set_consent() del banner pasa a
 * 'granted' por el canal oficial.
 */
add_filter('wp_get_consent_type', function ($type) {
    $opts = d3d_cookies_get_opts();
    return empty($opts['enabled']) ? $type : 'optin';
});

add_action('wp_enqueue_scripts', function () {
    $opts = d3d_cookies_get_opts();
    if (empty($opts['enabled'])) { return; }

    $base = get_stylesheet_directory_uri();
    $path = get_stylesheet_directory();

    $css_file = '/assets/css/cookies-banner.css';
    $js_file  = '/assets/js/cookies-banner.js';

    $css_ver = file_exists($path . $css_file) ? filemtime($path . $css_file) : '1.0.0';
    $js_ver  = file_exists($path . $js_file)  ? filemtime($path . $js_file)  : '1.0.0';

    wp_enqueue_style('d3d-cookies', $base . $css_file, [], $css_ver);
    wp_enqueue_script('d3d-cookies', $base . $js_file, [], $js_ver, true);

    wp_localize_script('d3d-cookies', 'doble3dCookies', [
        'expiryDays' => absint($opts['expiry_days'] ?? 180),
    ]);
});

add_action('wp_footer', function () {
    $opts = d3d_cookies_get_opts();
    if (empty($opts['enabled'])) { return; }

    $title    = $opts['title'];
    $message  = wp_kses_post($opts['message']);
    $accept   = $opts['accept_text'];
    $reject   = $opts['reject_text'];
    $show_rej = !empty($opts['show_reject']);
    $link_url = $opts['privacy_url'];
    $link_txt = $opts['privacy_text'];
    $position = $opts['position'];

    $css_vars = sprintf(
        '--cb-bg:%s;--cb-fg:%s;--cb-btn-bg:%s;--cb-btn-fg:%s;',
        esc_attr($opts['bg_color']),
        esc_attr($opts['text_color']),
        esc_attr($opts['btn_bg']),
        esc_attr($opts['btn_fg'])
    );
    ?>
    <div class="doble3d-cookie-banner"
         role="dialog"
         aria-labelledby="doble3d-cookie-title"
         aria-describedby="doble3d-cookie-message"
         data-position="<?php echo esc_attr($position); ?>"
         style="<?php echo $css_vars; ?>"
         hidden>
        <h3 id="doble3d-cookie-title" class="doble3d-cookie-banner__title"><?php echo esc_html($title); ?></h3>
        <p id="doble3d-cookie-message" class="doble3d-cookie-banner__message"><?php echo $message; ?></p>
        <div class="doble3d-cookie-banner__actions">
            <button type="button" class="doble3d-cookie-banner__btn doble3d-cookie-banner__btn--accept" data-action="accept"><?php echo esc_html($accept); ?></button>
            <?php if ($show_rej): ?>
                <button type="button" class="doble3d-cookie-banner__btn doble3d-cookie-banner__btn--reject" data-action="reject"><?php echo esc_html($reject); ?></button>
            <?php endif; ?>
        </div>
        <?php if ($link_url): ?>
            <a class="doble3d-cookie-banner__link" href="<?php echo esc_url($link_url); ?>"><?php echo esc_html($link_txt); ?></a>
        <?php endif; ?>
    </div>
    <?php
}, 999);
