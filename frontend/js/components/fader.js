/* =========================================================
   Technoknowledge: fader.js
   Photo slideshow that cross-fades automatically.
   Markup:
     <div class="fader" data-fader data-interval="4500" aria-roledescription="carousel" aria-label="…">
       <figure class="fader__slide is-active"><img …><figcaption>…</figcaption></figure>
       <figure class="fader__slide">…</figure>
     </div>
   - Dots are added by this script (click to jump)
   - Optional data-fader-card="#id": a card whose [data-fader-num] and
     [data-fader-text] are swapped to each slide's data-num / data-text
   - Pauses on hover/focus, when off-screen or the tab is hidden
   - "Reduce motion": no auto-play, dots still work
   ========================================================= */
(function () {
  'use strict';
  window.TK = window.TK || {};

  TK.initFaders = function () {
    var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    document.querySelectorAll('[data-fader]').forEach(function (root) {
      var slides = Array.prototype.slice.call(root.querySelectorAll('.fader__slide'));
      if (slides.length < 2) return;
      var delay = parseInt(root.getAttribute('data-interval'), 10) || 4500;
      var i = Math.max(0, slides.findIndex(function (s) { return s.classList.contains('is-active'); }));
      var timer = null, hover = false, inView = false;

      var dots = document.createElement('div');
      dots.className = 'fader__dots';
      slides.forEach(function (s, n) {
        var b = document.createElement('button');
        b.type = 'button';
        b.className = 'fader__dot';
        b.setAttribute('aria-label', 'Show photo ' + (n + 1) + ' of ' + slides.length);
        b.addEventListener('click', function () { show(n); play(); });
        dots.appendChild(b);
      });
      root.appendChild(dots);
      var dotEls = dots.querySelectorAll('.fader__dot');
      var card = root.getAttribute('data-fader-card') ? document.querySelector(root.getAttribute('data-fader-card')) : null;
      var cardNum = card && card.querySelector('[data-fader-num]');
      var cardText = card && card.querySelector('[data-fader-text]');
      var swapTimer = null;
      function updateCard(slide, animate) {
        if (!card || !slide.hasAttribute('data-num')) return;
        var num = slide.getAttribute('data-num'), txt = slide.getAttribute('data-text') || '';
        if (cardNum.textContent === num && cardText.textContent === txt) return;
        clearTimeout(swapTimer);
        if (!animate || reduce) { cardNum.textContent = num; cardText.textContent = txt; return; }
        card.classList.add('is-swapping');
        swapTimer = setTimeout(function () {
          cardNum.textContent = num; cardText.textContent = txt;
          card.classList.remove('is-swapping');
        }, 260);
      }
      var first = true;
      root.style.setProperty('--fader-interval', delay + 'ms');

      function show(n) {
        i = (n + slides.length) % slides.length;
        slides.forEach(function (s, k) {
          var on = k === i;
          s.classList.toggle('is-active', on);
          s.setAttribute('aria-hidden', on ? 'false' : 'true');
          var img = s.querySelector('img');
          if (img && img.loading === 'lazy' && Math.abs(k - i) <= 1) img.loading = 'eager';
        });
        dotEls.forEach(function (d, k) {
          d.classList.toggle('is-active', k === i);
          if (k === i) d.setAttribute('aria-current', 'true'); else d.removeAttribute('aria-current');
        });
        updateCard(slides[i], !first); first = false;
        root.classList.remove('is-ticking'); void root.offsetWidth;
        if (timer) root.classList.add('is-ticking');
      }
      function play() {
        clearInterval(timer); timer = null;
        if (!reduce && inView && !hover && !document.hidden) {
          timer = setInterval(function () { show(i + 1); }, delay);
        }
        show(i);
      }

      root.addEventListener('mouseenter', function () { hover = true; play(); });
      root.addEventListener('mouseleave', function () { hover = false; play(); });
      root.addEventListener('focusin', function () { hover = true; play(); });
      root.addEventListener('focusout', function (e) { if (!root.contains(e.relatedTarget)) { hover = false; play(); } });
      document.addEventListener('visibilitychange', play);
      if ('IntersectionObserver' in window) {
        new IntersectionObserver(function (en) { inView = en[0].isIntersecting; play(); }, { threshold: 0.25 }).observe(root);
      } else { inView = true; }
      show(i);
      play();
    });
  };
})();
