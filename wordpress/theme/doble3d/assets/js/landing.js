/* Doble 3D — landing front-page JS
 * Sentinelas (nav.scrolled / waFloat.on), FAQ accordion, formulario AJAX a admin-post.php, lightbox de video.
 */
(function () {
  'use strict';

  // --- Nav + WhatsApp float: IntersectionObserver en lugar de scroll listener ---
  var nav = document.getElementById('nav');
  var waFloat = document.getElementById('waFloat');
  var navSentinel = document.getElementById('navSentinel');
  var waSentinel = document.getElementById('waSentinel');
  if (navSentinel && nav) {
    new IntersectionObserver(function (entries) {
      nav.classList.toggle('scrolled', !entries[0].isIntersecting);
    }).observe(navSentinel);
  }
  if (waSentinel && waFloat) {
    new IntersectionObserver(function (entries) {
      waFloat.classList.toggle('on', !entries[0].isIntersecting);
    }).observe(waSentinel);
  }

  // --- Back to top: reusa el mismo sentinel del WA float (~600px desde top) ---
  var toTop = document.getElementById('toTop');
  if (toTop && waSentinel) {
    new IntersectionObserver(function (entries) {
      toTop.classList.toggle('on', !entries[0].isIntersecting);
    }).observe(waSentinel);
    toTop.addEventListener('click', function () {
      var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
      window.scrollTo({ top: 0, behavior: reduce ? 'auto' : 'smooth' });
    });
  }

  // --- FAQ accordion ---
  document.querySelectorAll('.faq-item').forEach(function (item) {
    var q = item.querySelector('.faq-q');
    if (!q) return;
    q.addEventListener('click', function () {
      var wasOpen = item.classList.contains('open');
      document.querySelectorAll('.faq-item.open').forEach(function (i) { i.classList.remove('open'); });
      if (!wasOpen) item.classList.add('open');
    });
  });

  // --- Contact form: AJAX submit a admin-post.php (wp_mail) ---
  (function () {
    var form = document.getElementById('contactForm');
    var success = document.getElementById('formSuccess');
    var reset = document.getElementById('formReset');
    if (!form || !success) return;

    var cfg = window.d3dCfg || {};
    var endpoint = cfg.endpoint || form.action;

    var loadedAt = Date.now();
    var MIN_FILL_MS = 2000;

    function showError(msg) {
      var prev = form.querySelector('.form-error');
      if (prev) prev.remove();
      var err = document.createElement('div');
      err.className = 'form-error';
      err.innerHTML = msg;
      form.appendChild(err);
    }

    function clearFieldErrors() {
      form.querySelectorAll('.field.has-error').forEach(function (f) {
        f.classList.remove('has-error');
        var e = f.querySelector('.field-err');
        if (e) e.remove();
      });
    }

    function markFieldError(name, msg) {
      var input = form.querySelector('[name="' + name + '"]');
      if (!input) return;
      var wrap = input.closest('.field');
      if (!wrap) return;
      wrap.classList.add('has-error');
      var err = wrap.querySelector('.field-err');
      if (!err) {
        err = document.createElement('span');
        err.className = 'field-err';
        wrap.appendChild(err);
      }
      err.textContent = msg;
      input.focus();
    }

    form.addEventListener('input', function (e) {
      var wrap = e.target.closest('.field');
      if (wrap && wrap.classList.contains('has-error')) {
        wrap.classList.remove('has-error');
        var err = wrap.querySelector('.field-err');
        if (err) err.remove();
      }
    });

    function validatePhone(val) {
      if (!val || !val.trim()) return true;
      var digits = val.replace(/\D/g, '');
      return digits.length >= 8 && digits.length <= 12;
    }

    function showSuccess() {
      var ref = document.getElementById('formRef');
      if (ref) ref.textContent = String(Date.now()).slice(-4);
      form.hidden = true;
      success.hidden = false;
      success.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }

    form.addEventListener('submit', function (e) {
      e.preventDefault();
      clearFieldErrors();
      var prevErr = form.querySelector('.form-error');
      if (prevErr) prevErr.remove();

      // Honeypot temporal: submit <2s desde render = probable bot. Silencioso.
      if (Date.now() - loadedAt < MIN_FILL_MS) {
        showSuccess();
        return;
      }

      var phoneInput = form.querySelector('input[name="telefono"]');
      if (phoneInput && !validatePhone(phoneInput.value)) {
        markFieldError('telefono', 'Formato no válido · ej: +56 9 1234 5678');
        return;
      }

      var btn = form.querySelector('.form-submit');
      if (btn) btn.classList.add('is-sending');

      fetch(endpoint, {
        method: 'POST',
        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        body: new FormData(form),
        credentials: 'same-origin'
      })
        .then(function (res) {
          return res.json().catch(function () { return {}; }).then(function (data) {
            return { ok: res.ok, status: res.status, data: data };
          });
        })
        .then(function (out) {
          if (out.ok && out.data && out.data.success) {
            showSuccess();
          } else {
            // Errores de validación devueltos por el server (422)
            if (out.data && out.data.data && out.data.data.fields) {
              Object.keys(out.data.data.fields).forEach(function (k) {
                markFieldError(k, out.data.data.fields[k]);
              });
              if (btn) btn.classList.remove('is-sending');
              return;
            }
            var msg = (out.data && out.data.data && out.data.data.msg)
              ? out.data.data.msg
              : 'No pudimos enviar el mensaje. Revisa tu conexión o escríbenos por <b>WhatsApp al +56 9 5801 5971</b>.';
            if (btn) btn.classList.remove('is-sending');
            showError(msg);
          }
        })
        .catch(function () {
          if (btn) btn.classList.remove('is-sending');
          showError('No pudimos enviar el mensaje. Revisa tu conexión o escríbenos por <b>WhatsApp al +56 9 5801 5971</b>.');
        });
    });

    if (reset) {
      reset.addEventListener('click', function () {
        form.reset();
        var sb = form.querySelector('.form-submit');
        if (sb) sb.classList.remove('is-sending');
        var prevErr = form.querySelector('.form-error');
        if (prevErr) prevErr.remove();
        success.hidden = true;
        form.hidden = false;
        form.scrollIntoView({ behavior: 'smooth', block: 'start' });
      });
    }
  })();

  // --- Lightbox video (YouTube embebido on-demand) ---
  (function () {
    var lb = document.getElementById('lightbox');
    var frame = document.getElementById('lightboxFrame');
    var closeBtn = document.getElementById('lightboxClose');
    if (!lb || !frame || !closeBtn) return;

    function open(videoId) {
      frame.innerHTML = '<iframe src="https://www.youtube.com/embed/' + encodeURIComponent(videoId) +
        '?autoplay=1&rel=0&modestbranding=1" allow="autoplay; encrypted-media; picture-in-picture; fullscreen" allowfullscreen></iframe>';
      lb.classList.add('open');
      document.body.classList.add('no-scroll');
    }

    function close() {
      lb.classList.remove('open');
      document.body.classList.remove('no-scroll');
      frame.innerHTML = '';
    }

    document.querySelectorAll('[data-video]').forEach(function (btn) {
      btn.addEventListener('click', function (e) { e.preventDefault(); open(btn.dataset.video); });
    });
    closeBtn.addEventListener('click', close);
    lb.addEventListener('click', function (e) { if (e.target === lb) close(); });
    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape' && lb.classList.contains('open')) close();
    });
  })();

  // --- Menú mobile (hamburguesa) ---
  (function () {
    var toggle = document.getElementById('navToggle');
    var navEl = document.getElementById('nav');
    var panel = document.getElementById('navMobile');
    if (!toggle || !navEl || !panel) return;

    function setOpen(open) {
      navEl.classList.toggle('menu-open', open);
      toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
      toggle.setAttribute('aria-label', open ? 'Cerrar menú' : 'Abrir menú');
      document.body.classList.toggle('no-scroll', open);
    }

    toggle.addEventListener('click', function () {
      setOpen(!navEl.classList.contains('menu-open'));
    });
    panel.querySelectorAll('a').forEach(function (a) {
      a.addEventListener('click', function () { setOpen(false); });
    });
    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape' && navEl.classList.contains('menu-open')) setOpen(false);
    });
  })();
})();
