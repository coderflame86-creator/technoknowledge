/* =========================================================
   Technoknowledge: counters.js
   Counts numbers up when they scroll into view.
   Markup: <span data-count-to="50000" data-suffix="+">50,000+</span>
   The real number is in the HTML, so it is correct for Google,
   screen readers and visitors without JavaScript.
   ========================================================= */
(function () {
  'use strict';
  window.TK = window.TK || {};

  TK.initCounters = function () {
    var els = document.querySelectorAll('[data-count-to]');
    if (!els.length) return;
    var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    if (reduce || !('IntersectionObserver' in window)) return; // keep the final numbers

    function format(n, el) {
      return n.toLocaleString('en-US') + (el.getAttribute('data-suffix') || '');
    }

    function run(el) {
      var target = parseInt(el.getAttribute('data-count-to'), 10);
      var duration = 1600, t0 = null;
      function frame(t) {
        if (!t0) t0 = t;
        var p = Math.min((t - t0) / duration, 1);
        var eased = 1 - Math.pow(1 - p, 3);
        el.textContent = format(Math.round(target * eased), el);
        if (p < 1) requestAnimationFrame(frame);
      }
      requestAnimationFrame(frame);
    }

    var io = new IntersectionObserver(function (entries) {
      entries.forEach(function (e) {
        if (e.isIntersecting) { run(e.target); io.unobserve(e.target); }
      });
    }, { threshold: 0.6 });

    els.forEach(function (el) {
      el.textContent = format(0, el);
      io.observe(el);
    });
  };
})();
