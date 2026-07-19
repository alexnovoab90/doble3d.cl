/* Doble 3D — Cookie banner: vanilla JS, sin dependencias.
 *
 * Comportamiento:
 *   - Si ya hay cookie 'doble3d_cookie_consent' → reaplica la decisión a Google
 *     (vía wp-consent-api → Site Kit Consent Mode) y quita el banner del DOM.
 *   - Si no: lo muestra con animación tras 600ms, atiende click en Aceptar/Rechazar,
 *     setea cookie con duración configurable (default 180 días), comunica el
 *     consentimiento a Google y dispara CustomEvent 'doble3d:consent'.
 *
 * Integración con Google: usa wp_set_consent() de wp-consent-api, que Site Kit
 * traduce a gtag('consent','update'). 'statistics' → analytics_storage,
 * 'marketing' → ad_storage/ad_user_data/ad_personalization.
 */
function d3dCookiesInit() {
    'use strict';

    var COOKIE = 'doble3d_cookie_consent';
    var banner = document.querySelector('.doble3d-cookie-banner');

    var cfg  = window.doble3dCookies || {};
    var days = cfg.expiryDays && cfg.expiryDays > 0 ? cfg.expiryDays : 180;

    function getCookie(name) {
        var m = document.cookie.match(new RegExp('(?:^|; )' + name.replace(/[-.+*]/g, '\\$&') + '=([^;]+)'));
        return m ? decodeURIComponent(m[1]) : null;
    }

    function setCookie(name, value, days) {
        var d = new Date();
        d.setTime(d.getTime() + days * 86400000);
        document.cookie = name + '=' + encodeURIComponent(value) +
            '; expires=' + d.toUTCString() +
            '; path=/; SameSite=Lax' +
            (location.protocol === 'https:' ? '; Secure' : '');
    }

    // Comunica la decisión a Google a través de wp-consent-api.
    // wp_set_consent puede no existir aún si wp-consent-api carga después: reintenta.
    function applyConsent(type) {
        var v = (type === 'all') ? 'allow' : 'deny';
        var tries = 0;
        (function set() {
            if (typeof window.wp_set_consent === 'function') {
                window.wp_set_consent('statistics', v);
                window.wp_set_consent('marketing', v);
                window.wp_set_consent('preferences', v);
            } else if (tries++ < 20) {
                setTimeout(set, 100); // espera a wp-consent-api, máx ~2s
            }
        })();
    }

    function hideBanner() {
        banner.classList.remove('is-visible');
        setTimeout(function () { if (banner.parentNode) banner.parentNode.removeChild(banner); }, 500);
    }

    function dispatchConsent(type) {
        try {
            document.dispatchEvent(new CustomEvent('doble3d:consent', { detail: { type: type } }));
        } catch (e) {
            // IE11 fallback (no esperado pero defensivo)
            var ev = document.createEvent('CustomEvent');
            ev.initCustomEvent('doble3d:consent', true, true, { type: type });
            document.dispatchEvent(ev);
        }
    }

    // Si ya hay decisión, reaplicarla a Google (mantiene Consent Mode sincronizado
    // en visitas recurrentes) y no mostrar el banner.
    var prev = getCookie(COOKIE);
    if (prev) {
        applyConsent(prev === 'all' ? 'all' : 'necessary');
        if (banner && banner.parentNode) banner.parentNode.removeChild(banner);
        return;
    }

    if (!banner) { return; }

    // Pintar tras un beat para no competir con LCP del hero.
    banner.hidden = false;
    setTimeout(function () { banner.classList.add('is-visible'); }, 600);

    var acceptBtn = banner.querySelector('[data-action="accept"]');
    var rejectBtn = banner.querySelector('[data-action="reject"]');

    if (acceptBtn) {
        acceptBtn.addEventListener('click', function () {
            setCookie(COOKIE, 'all', days);
            applyConsent('all');
            hideBanner();
            dispatchConsent('all');
        });
    }

    if (rejectBtn) {
        rejectBtn.addEventListener('click', function () {
            setCookie(COOKIE, 'necessary', days);
            applyConsent('necessary');
            hideBanner();
            dispatchConsent('necessary');
        });
    }
}

// El script puede cargarse antes de que el banner exista en el DOM (el HTML del
// banner se emite en wp_footer priority 999, después del enqueue de scripts).
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', d3dCookiesInit);
} else {
    d3dCookiesInit();
}
