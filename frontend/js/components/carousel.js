/* =========================================================
   Technoknowledge: carousel.js
   One-at-a-time carousel (used for testimonials).
   Markup: [data-carousel]
             .carousel__track > .carousel__item (many)
             .carousel__prev / .carousel__next buttons
             .carousel__dots (filled by this script)
   ========================================================= */
(function () {
  'use strict';
  window.TK = window.TK || {};

  TK.initCarousels = function () {
    document.querySelectorAll('[data-carousel]').forEach(function (root) {
      var items = root.querySelectorAll('.carousel__item');
      if (items.length < 2) return;
      var dotsWrap = root.querySelector('.carousel__dots');
      var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
      var current = 0, timer = null, dots = [];

      if (dotsWrap) {
        items.forEach(function (_, i) {
          var b = document.createElement('button');
          b.type = 'button';
          b.className = 'carousel__dot';
          b.setAttribute('aria-label', 'Show quote ' + (i + 1) + ' of ' + items.length);
          b.addEventListener('click', function () { show(i); restart(); });
          dotsWrap.appendChild(b);
          dots.push(b);
        });
      }

      function show(i) {
        current = (i + items.length) % items.length;
        items.forEach(function (it, k) {
          it.classList.toggle('is-active', k === current);
          it.setAttribute('aria-hidden', k === current ? 'false' : 'true');
        });
        dots.forEach(function (d, k) { d.setAttribute('aria-current', k === current ? 'true' : 'false'); });
      }
      function start() { if (!reduce) { stop(); timer = setInterval(function () { show(current + 1); }, 7000); } }
      function stop() { clearInterval(timer); }
      function restart() { stop(); start(); }

      var prev = root.querySelector('.carousel__prev');
      var next = root.querySelector('.carousel__next');
      if (prev) prev.addEventListener('click', function () { show(current - 1); restart(); });
      if (next) next.addEventListener('click', function () { show(current + 1); restart(); });
      root.addEventListener('mouseenter', stop);
      root.addEventListener('mouseleave', start);
      root.addEventListener('focusin', stop);

      root.classList.add('is-ready');
      show(0);
      start();
    });
  };
})();
