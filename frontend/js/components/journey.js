/* =========================================================
   Technoknowledge: journey.js
   Learning journey (home page): the four steps light up one
   after another in their own colour, like a path being walked.
   - Starts when the section scrolls into view, loops every ~2.4 s
   - Hover, tap or keyboard focus on a step highlights it and pauses the loop
   - Respects "reduce motion": no auto-play, hover/focus still works
   Markup: <ol class="journey__steps" data-journey> <li class="journey__step"> …
   ========================================================= */
(function () {
  'use strict';
  window.TK = window.TK || {};

  TK.initJourney = function () {
    var lists = document.querySelectorAll('[data-journey]');
    var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    lists.forEach(function (list) {
      var steps = Array.prototype.slice.call(list.querySelectorAll('.journey__step'));
      if (!steps.length) return;
      var current = -1, timer = null, paused = false, inView = false;

      function show(i) {
        current = i;
        steps.forEach(function (s, n) {
          s.classList.toggle('is-active', n === i);
          s.classList.toggle('is-done', n < i);
        });
      }
      function next() { show((current + 1) % steps.length); }
      function play() {
        if (reduce || paused || !inView || timer) return;
        if (current < 0) next();
        timer = setInterval(next, 2400);
      }
      function stop() { clearInterval(timer); timer = null; }

      steps.forEach(function (s, n) {
        if (!s.hasAttribute('tabindex')) s.setAttribute('tabindex', '0');
        s.addEventListener('mouseenter', function () { paused = true; stop(); show(n); });
        s.addEventListener('focus', function () { paused = true; stop(); show(n); });
        s.addEventListener('click', function () { paused = true; stop(); show(n); });
      });
      list.addEventListener('mouseleave', function () { paused = false; play(); });
      list.addEventListener('focusout', function (e) {
        if (!list.contains(e.relatedTarget)) { paused = false; play(); }
      });

      list.classList.add('is-live');
      if ('IntersectionObserver' in window) {
        new IntersectionObserver(function (entries) {
          inView = entries[0].isIntersecting;
          if (inView) play(); else stop();
        }, { threshold: 0.35 }).observe(list);
      } else { inView = true; play(); }
    });
  };
})();
