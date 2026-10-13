/* =========================================================
   Technoknowledge: tabs.js
   Accessible tabs. Markup:
   [data-tabs]
     .tabs__list [role=tablist] > .tabs__tab[role=tab][aria-controls=id]
     .tabs__panel[role=tabpanel][id]
   Without JavaScript every panel is visible (nothing is lost).
   Options (on the [data-tabs] element):
     data-tabs-indicator   a pill slides smoothly to the chosen tab
     data-tabs-auto="6000" moves to the next tab every 6 s while the
                           section is on screen, with a progress line in
                           the pill. Pauses on hover/focus; stops for good
                           once the visitor picks a tab. Off for "reduce motion".
   ========================================================= */
(function () {
  'use strict';
  window.TK = window.TK || {};

  TK.initTabs = function () {
    document.querySelectorAll('[data-tabs]').forEach(function (root) {
      var tabs = Array.prototype.slice.call(root.querySelectorAll('[role="tab"]'));
      if (!tabs.length) return;

      function select(tab, focus) {
        tabs.forEach(function (t) {
          var on = t === tab;
          t.setAttribute('aria-selected', on ? 'true' : 'false');
          t.tabIndex = on ? 0 : -1;
          var panel = document.getElementById(t.getAttribute('aria-controls'));
          if (panel) panel.hidden = !on;
        });
        if (focus) tab.focus();
        moveIndicator(tab);
        restartProgress();
      }

      /* ---- sliding pill ---- */
      var list = root.querySelector('[role="tablist"]');
      var pill = null;
      if (root.hasAttribute('data-tabs-indicator') && list) {
        pill = document.createElement('span');
        pill.className = 'tabs__pill';
        pill.setAttribute('aria-hidden', 'true');
        pill.innerHTML = '<i class="tabs__progress"></i>';
        list.insertBefore(pill, list.firstChild);
        list.classList.add('has-pill');
        window.addEventListener('resize', function () { moveIndicator(current(), true); });
      }
      function current() { return tabs.filter(function (t) { return t.getAttribute('aria-selected') === 'true'; })[0] || tabs[0]; }
      function moveIndicator(tab, instant) {
        if (!pill || !tab) return;
        if (instant) pill.style.transition = 'none';
        pill.style.width = tab.offsetWidth + 'px';
        pill.style.height = tab.offsetHeight + 'px';
        pill.style.transform = 'translate(' + tab.offsetLeft + 'px,' + tab.offsetTop + 'px)';
        if (instant) { void pill.offsetWidth; pill.style.transition = ''; }
        // keep the chosen tab visible when the tab row scrolls sideways (phones)
        if (list.scrollWidth > list.clientWidth) {
          list.scrollTo({ left: tab.offsetLeft - (list.clientWidth - tab.offsetWidth) / 2, behavior: 'smooth' });
        }
      }

      /* ---- auto-play ---- */
      var delay = parseInt(root.getAttribute('data-tabs-auto'), 10) || 0;
      var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
      var timer = null, inView = false, hover = false, stopped = !delay || reduce;
      if (pill && delay && !stopped) root.style.setProperty('--tabs-auto', delay + 'ms');
      function restartProgress() {
        if (!pill) return;
        pill.classList.remove('is-running');
        if (stopped || !inView || hover) return;
        void pill.offsetWidth;
        pill.classList.add('is-running');
      }
      function tick() {
        var i = tabs.indexOf(current());
        select(tabs[(i + 1) % tabs.length]);
      }
      function play() {
        clearInterval(timer); timer = null;
        if (stopped || !inView || hover) { restartProgress(); return; }
        timer = setInterval(tick, delay);
        restartProgress();
      }
      function stopForGood() { stopped = true; clearInterval(timer); timer = null; restartProgress(); }

      tabs.forEach(function (tab, i) {
        tab.addEventListener('click', function () { stopForGood(); select(tab); });
        tab.addEventListener('keydown', function (e) {
          var k = null;
          if (e.key === 'ArrowRight') k = (i + 1) % tabs.length;
          if (e.key === 'ArrowLeft') k = (i - 1 + tabs.length) % tabs.length;
          if (e.key === 'Home') k = 0;
          if (e.key === 'End') k = tabs.length - 1;
          if (k !== null) { e.preventDefault(); stopForGood(); select(tabs[k], true); }
        });
      });

      var start = tabs.filter(function (t) { return t.getAttribute('aria-selected') === 'true'; })[0] || tabs[0];
      select(start);
      if (pill) { moveIndicator(start, true); window.addEventListener('load', function () { moveIndicator(current(), true); }); }

      if (!stopped) {
        root.addEventListener('mouseenter', function () { hover = true; play(); });
        root.addEventListener('mouseleave', function () { hover = false; play(); });
        root.addEventListener('focusin', function () { hover = true; play(); });
        root.addEventListener('focusout', function (e) { if (!root.contains(e.relatedTarget)) { hover = false; play(); } });
        if ('IntersectionObserver' in window) {
          new IntersectionObserver(function (en) { inView = en[0].isIntersecting; play(); }, { threshold: 0.3 }).observe(root);
        } else { inView = true; play(); }
      }
    });
  };
})();
