/* =========================================================
   Technoknowledge: filter.js
   Filter buttons for a gallery or card grid. Markup:
   [data-filter]  (optional data-filter-noun="projects")
     .filter > button.filter__btn[data-filter-value="all|robotics|..."]
     [data-filter-item][data-category="robotics iot"]
   Animation:
   - a navy pill slides to the chosen button
   - cards that no longer match shrink and fade out, then the
     matching cards pop back in one after another
   - "reduce motion": instant, no animation
   ========================================================= */
(function () {
  'use strict';
  window.TK = window.TK || {};

  var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  TK.initFilters = function () {
    document.querySelectorAll('[data-filter]').forEach(function (root) {
      var buttons = Array.prototype.slice.call(root.querySelectorAll('.filter__btn'));
      var items = Array.prototype.slice.call(root.querySelectorAll('[data-filter-item]'));
      var status = root.querySelector('[data-filter-status]');
      var noun = root.getAttribute('data-filter-noun') || 'projects';
      var group = buttons.length ? buttons[0].parentNode : null;
      var run = 0;

      /* sliding pill behind the active button */
      var pill = null;
      if (group) {
        pill = document.createElement('span');
        pill.className = 'filter__pill';
        pill.setAttribute('aria-hidden', 'true');
        group.insertBefore(pill, group.firstChild);
        group.classList.add('has-pill');
      }
      function movePill(btn, instant) {
        if (!pill || !btn) return;
        if (instant) pill.style.transition = 'none';
        pill.style.width = btn.offsetWidth + 'px';
        pill.style.height = btn.offsetHeight + 'px';
        pill.style.transform = 'translate(' + btn.offsetLeft + 'px,' + btn.offsetTop + 'px)';
        if (instant) { void pill.offsetWidth; pill.style.transition = ''; }
      }
      function active() { return buttons.filter(function (b) { return b.getAttribute('aria-pressed') === 'true'; })[0] || buttons[0]; }

      function matches(it, value) {
        var cats = (it.getAttribute('data-category') || '').split(/\s+/);
        return value === 'all' || cats.indexOf(value) !== -1;
      }

      function apply(value) {
        var my = ++run;
        var show = items.filter(function (it) { return matches(it, value); });
        var leaving = items.filter(function (it) { return !it.hidden && show.indexOf(it) === -1; });
        if (status) status.textContent = show.length + ' ' + noun + ' shown';

        function reveal() {
          if (my !== run) return;
          items.forEach(function (it) {
            it.classList.remove('is-leaving', 'is-entering');
            it.style.removeProperty('--i');
            it.hidden = show.indexOf(it) === -1;
          });
          if (reduce) return;
          show.forEach(function (it, n) {
            it.style.setProperty('--i', Math.min(n, 12));
            void it.offsetWidth;
            it.classList.add('is-entering');
          });
          setTimeout(function () {
            if (my !== run) return;
            show.forEach(function (it) { it.classList.remove('is-entering'); it.style.removeProperty('--i'); });
          }, 1200);
        }

        if (reduce || !leaving.length) { reveal(); return; }
        leaving.forEach(function (it) { it.classList.add('is-leaving'); });
        setTimeout(reveal, 220);
      }

      buttons.forEach(function (btn) {
        btn.addEventListener('click', function () {
          if (btn.getAttribute('aria-pressed') === 'true') return;
          buttons.forEach(function (b) { b.setAttribute('aria-pressed', b === btn ? 'true' : 'false'); });
          movePill(btn);
          if (!reduce) { btn.classList.remove('is-pop'); void btn.offsetWidth; btn.classList.add('is-pop'); }
          apply(btn.getAttribute('data-filter-value'));
        });
      });

      movePill(active(), true);
      window.addEventListener('resize', function () { movePill(active(), true); });
      window.addEventListener('load', function () { movePill(active(), true); });
      if (document.fonts && document.fonts.ready) document.fonts.ready.then(function () { movePill(active(), true); });
    });
  };
})();
