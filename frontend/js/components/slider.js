/* =========================================================
   Technoknowledge: slider.js
   Fading image slider with clickable progress labels.
   Markup: [data-slider] > .slider__slide (many)
                         + .slider__tab buttons (same count)
   - changes every 6 s (data-interval to override,
     or data-ms on one slide to give it longer)
   - pauses on hover / keyboard focus / hidden tab
   - swipe on touch screens, arrow keys on the tabs
   - no auto-play when "reduce motion" is on
   ========================================================= */
(function () {
  'use strict';
  window.TK = window.TK || {};

  TK.initSliders = function () {
    document.querySelectorAll('[data-slider]').forEach(setup);
  };

  function setup(root) {
    var slides = root.querySelectorAll('.slider__slide');
    var tabs = root.querySelectorAll('.slider__tab');
    if (slides.length < 2) return;

    var interval = parseInt(root.getAttribute('data-interval') || '6000', 10);
    var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    var current = 0, timer = null, paused = false;
    root.style.setProperty('--slide-ms', interval + 'ms');

    function show(i, fromUser) {
      current = (i + slides.length) % slides.length;
      slides.forEach(function (s, k) {
        var on = k === current;
        s.classList.toggle('is-active', on);
        s.setAttribute('aria-hidden', on ? 'false' : 'true');
        s.querySelectorAll('a, button').forEach(function (el) { el.tabIndex = on ? 0 : -1; });
      });
      tabs.forEach(function (t, k) {
        var on = k === current;
        t.setAttribute('aria-selected', on ? 'true' : 'false');
        t.tabIndex = on ? 0 : -1;
        // restart the progress bar animation
        t.classList.remove('is-running');
        void t.offsetWidth;
        if (on && !reduce && !paused) { t.style.setProperty('--slide-ms', dur(k) + 'ms'); t.classList.add('is-running'); }
      });
      if (fromUser) restart();
    }

    // a slide can ask for its own time on screen with data-ms="10000"
    function dur(i) { return parseInt(slides[i].getAttribute('data-ms'), 10) || interval; }
    function next() { show(current + 1); }
    function start() {
      if (reduce) return;
      stop();
      timer = setTimeout(function () { next(); start(); }, dur(current));
      var t = tabs[current];
      if (t) { t.style.setProperty('--slide-ms', dur(current) + 'ms'); t.classList.remove('is-running'); void t.offsetWidth; t.classList.add('is-running'); }
    }
    function stop() { clearTimeout(timer); timer = null; }
    function restart() { if (!paused) start(); }
    function pause() { paused = true; stop(); root.classList.add('is-paused'); }
    function resume() { paused = false; root.classList.remove('is-paused'); start(); }

    tabs.forEach(function (t, k) {
      t.addEventListener('click', function () { show(k, true); });
      t.addEventListener('keydown', function (e) {
        if (e.key === 'ArrowRight' || e.key === 'ArrowLeft') {
          e.preventDefault();
          show(current + (e.key === 'ArrowRight' ? 1 : -1), true);
          tabs[current].focus();
        }
      });
    });

    root.addEventListener('mouseenter', pause);
    root.addEventListener('mouseleave', resume);
    root.addEventListener('focusin', pause);
    root.addEventListener('focusout', function (e) { if (!root.contains(e.relatedTarget)) resume(); });
    document.addEventListener('visibilitychange', function () { document.hidden ? stop() : restart(); });

    // swipe
    var x0 = null;
    root.addEventListener('touchstart', function (e) { x0 = e.touches[0].clientX; }, { passive: true });
    root.addEventListener('touchend', function (e) {
      if (x0 === null) return;
      var dx = e.changedTouches[0].clientX - x0;
      if (Math.abs(dx) > 40) show(current + (dx < 0 ? 1 : -1), true);
      x0 = null;
    });

    root.classList.add('is-ready');
    show(0);
    start();
  }
})();
