/* =========================================================
   Technoknowledge: spotlight.js
   Highlights the cards in a group one after another, and gives
   each card a gentle 3D tilt that follows the mouse.
   Markup: <div class="grid" data-spotlight data-interval="3200">
             <article class="card">…</article> …
           </div>
   - Starts when the group is on screen; pauses while the visitor
     hovers or focuses a card (that card is highlighted instead)
   - "Reduce motion": no auto-play and no tilt
   ========================================================= */
(function () {
  'use strict';
  window.TK = window.TK || {};

  TK.initSpotlights = function () {
    var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    var finePointer = window.matchMedia && window.matchMedia('(hover: hover) and (pointer: fine)').matches;

    document.querySelectorAll('[data-spotlight]').forEach(function (root) {
      var cards = Array.prototype.slice.call(root.children);
      if (cards.length < 2) return;
      var delay = parseInt(root.getAttribute('data-interval'), 10) || 3200;
      var i = -1, timer = null, hover = false, inView = false;
      root.classList.add('is-spotlit');

      function show(n) {
        i = n;
        cards.forEach(function (c, k) { c.classList.toggle('is-spot', k === n); });
      }
      function play() {
        clearInterval(timer); timer = null;
        if (reduce || hover || !inView) return;
        if (i < 0) show(0);
        timer = setInterval(function () { show((i + 1) % cards.length); }, delay);
      }

      cards.forEach(function (c, n) {
        c.addEventListener('mouseenter', function () { hover = true; show(n); play(); });
        c.addEventListener('focusin', function () { hover = true; show(n); play(); });
        c.addEventListener('mouseleave', function () { c.style.removeProperty('--rx'); c.style.removeProperty('--ry'); });
        if (!reduce && finePointer) {
          c.addEventListener('pointermove', function (e) {
            var r = c.getBoundingClientRect();
            var x = (e.clientX - r.left) / r.width - 0.5, y = (e.clientY - r.top) / r.height - 0.5;
            c.style.setProperty('--ry', (x * 6).toFixed(2) + 'deg');
            c.style.setProperty('--rx', (-y * 6).toFixed(2) + 'deg');
            c.style.setProperty('--mx', ((x + 0.5) * 100).toFixed(1) + '%');
            c.style.setProperty('--my', ((y + 0.5) * 100).toFixed(1) + '%');
          });
        }
      });
      root.addEventListener('mouseleave', function () { hover = false; play(); });
      root.addEventListener('focusout', function (e) { if (!root.contains(e.relatedTarget)) { hover = false; play(); } });

      if ('IntersectionObserver' in window) {
        new IntersectionObserver(function (en) { inView = en[0].isIntersecting; play(); }, { threshold: 0.35 }).observe(root);
      } else { inView = true; play(); }
    });
  };
})();
