/* =========================================================
   Technoknowledge: main.js
   Starts shared interactivity on every page.
   Load order in every page (all with "defer"):
     js/components/*.js  →  js/main.js
   Scripts are plain (not ES modules) so pages also work when
   opened straight from a folder on your computer.
   ========================================================= */
(function () {
  'use strict';
  var TK = window.TK || {};

  /* Every page opens at the top.
     - The browser does not restore an old scroll position.
     - When the site is shown inside a frame (e.g. a preview), the outer page is scrolled to the top too.
     - Links to a section (page.html#section) still jump to that section. */
  function pageName(path) { return (path.split('/').pop() || 'index.html').replace(/\.html?$/, '').replace(/^Technoknowledge_|_Preview$/g, '').toLowerCase(); }
  function toTop(smooth) {
    var root = document.documentElement;
    var target = null;
    if (location.hash && location.hash.length > 1) {
      try { target = document.getElementById(decodeURIComponent(location.hash.slice(1))); } catch (e) {}
    }
    // some viewers drop the #section from a link: use the one remembered on click
    if (!target) {
      try {
        var saved = JSON.parse(sessionStorage.getItem('tk-section') || 'null');
        if (saved && saved.page === pageName(location.pathname)) target = document.getElementById(saved.id);
      } catch (e) {}
    }
    // a page can ask to open at a section (e.g. "Request a Demo" opens at the form)
    if (!target) target = document.querySelector('[data-land-here]');
    if (target) {
      // link to a section: bring that section to the top (below the header)
      target.scrollIntoView({ block: 'start', behavior: smooth ? 'auto' : 'instant' });
      return;
    }
    var pad = root.style.scrollPaddingTop;
    root.style.scrollPaddingTop = '0px';
    try { root.scrollIntoView({ block: 'start', behavior: 'instant' }); } catch (e) {}
    window.scrollTo(0, 0);
    root.style.scrollPaddingTop = pad;
  }
  if ('scrollRestoration' in history) history.scrollRestoration = 'manual';
  toTop();
  // Some viewers (e.g. the Claude artifact frame) restore the old scroll position
  // after the page has loaded. Repeat the jump a few times until the visitor
  // scrolls, clicks or types themselves.
  var userMoved = false;
  ['wheel', 'touchstart', 'keydown', 'mousedown'].forEach(function (ev) {
    window.addEventListener(ev, function () { userMoved = true; }, { passive: true, once: true });
  });
  function toTopIfIdle() { if (!userMoved) toTop(); }
  document.addEventListener('DOMContentLoaded', toTopIfIdle);
  window.addEventListener('load', function () {
    toTopIfIdle();
    [60, 250, 600, 1200].forEach(function (ms) { setTimeout(toTopIfIdle, ms); });
  });
  window.addEventListener('pageshow', function (e) { if (e.persisted) toTop(); });

  // Remember "?interest=…" from links, in case a viewer drops the query string.
  document.addEventListener('click', function (e) {
    var a = e.target.closest && e.target.closest('a[href*="interest="]');
    if (!a) return;
    var m = a.getAttribute('href').match(/[?&]interest=([^&#]+)/);
    try { if (m) sessionStorage.setItem('tk-interest', decodeURIComponent(m[1])); } catch (err) {}
  }, true);
  window.addEventListener('hashchange', function () { toTop(true); });

  // Remember "page.html#section" from links, in case a viewer drops the #section.
  document.addEventListener('click', function (e) {
    var a = e.target.closest && e.target.closest('a[href*="#"]');
    if (!a) return;
    var href = a.getAttribute('href'), i = href.indexOf('#');
    if (i <= 0 || href.length <= i + 1) return;   // only links to another page's section
    try { sessionStorage.setItem('tk-section', JSON.stringify({ page: pageName(href.slice(0, i).split('?')[0]), id: href.slice(i + 1) })); } catch (err) {}
  }, true);
  // forget it once the page has settled, so later visits open at the top
  window.addEventListener('load', function () { setTimeout(function () { try { sessionStorage.removeItem('tk-section'); } catch (e) {} }, 1500); });

  function start() {
    if (TK.initHeader) TK.initHeader();
    if (TK.initReveal) TK.initReveal();
    // widgets (each file is only loaded on pages that use it)
    if (TK.initSliders) TK.initSliders();
    if (TK.initTabs) TK.initTabs();
    if (TK.initCounters) TK.initCounters();
    if (TK.initCarousels) TK.initCarousels();
    if (TK.initFilters) TK.initFilters();
    if (TK.initJourney) TK.initJourney();
    if (TK.initFaders) TK.initFaders();
    if (TK.initSpotlights) TK.initSpotlights();
    if (TK.initTypers) TK.initTypers();
    if (TK.initForms) TK.initForms();
    if (TK.initLightbox) TK.initLightbox();
    if (TK.initFlipbook) TK.initFlipbook();
    if (TK.initPage) TK.initPage(); // one-page script from js/pages/

    // current year in the footer
    document.querySelectorAll('[data-year]').forEach(function (el) {
      el.textContent = new Date().getFullYear();
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', start);
  } else {
    start();
  }
})();
