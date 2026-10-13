/* =========================================================
   Technoknowledge: reveal.js
   Fades in elements with class="reveal" as they scroll into
   view. Respects "reduce motion". Content is always visible
   if JavaScript is off.
   ========================================================= */
(function () {
  'use strict';
  window.TK = window.TK || {};

  TK.initReveal = function () {
    var els = document.querySelectorAll('.reveal');
    if (!els.length) return;

    var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    if (reduce || !('IntersectionObserver' in window)) {
      els.forEach(function (el) { el.classList.add('is-visible'); });
      return;
    }

    var io = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (!entry.isIntersecting) return;
        var el = entry.target;
        var delay = parseInt(el.getAttribute('data-delay') || '0', 10);
        setTimeout(function () { el.classList.add('is-visible'); }, delay);
        io.unobserve(el);
      });
    }, { rootMargin: '0px 0px -8% 0px', threshold: 0.12 });

    els.forEach(function (el) { io.observe(el); });
  };
})();
