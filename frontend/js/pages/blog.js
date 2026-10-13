/* =========================================================
   Technoknowledge: pages/blog.js
   Blog list page:
   - Topic chips and a search box filter the post cards
   - Shows "No posts match" when nothing is left
   Blog post pages:
   - Reading progress bar at the top of the window
   - Highlights the current section in the "On this page" list
   - "Copy link" share button
   Uses textContent only (never innerHTML with user input).
   ========================================================= */
(function () {
  'use strict';
  window.TK = window.TK || {};

  function initList() {
    var root = document.querySelector('[data-blog]');
    if (!root) return;
    var chips = root.querySelectorAll('[data-topic]');
    var search = root.querySelector('[data-blog-search]');
    var cards = root.querySelectorAll('[data-post]');
    var empty = root.querySelector('[data-blog-empty]');
    var status = root.querySelector('[data-blog-status]');
    var topic = 'all';

    function apply() {
      var q = (search && search.value || '').trim().toLowerCase();
      var shown = 0;
      cards.forEach(function (card) {
        var okTopic = topic === 'all' || card.getAttribute('data-cat') === topic;
        var okText = !q || (card.getAttribute('data-text') || '').indexOf(q) !== -1;
        var on = okTopic && okText;
        card.hidden = !on;
        if (on) shown++;
      });
      if (empty) empty.hidden = shown !== 0;
      if (status) status.textContent = shown + (shown === 1 ? ' post' : ' posts');
    }

    chips.forEach(function (chip) {
      chip.addEventListener('click', function () {
        topic = chip.getAttribute('data-topic');
        chips.forEach(function (c) { c.setAttribute('aria-pressed', c === chip ? 'true' : 'false'); });
        apply();
      });
    });
    if (search) {
      search.addEventListener('input', apply);
      search.form && search.form.addEventListener('submit', function (e) { e.preventDefault(); apply(); });
    }
    // allow ?topic=for-schools links
    var t = new URLSearchParams(location.search).get('topic');
    if (t) {
      var c = root.querySelector('[data-topic="' + t.replace(/[^a-z-]/g, '') + '"]');
      if (c) c.click();
    }
  }

  function initPost() {
    var article = document.querySelector('[data-article]');
    if (!article) return;

    // reading progress
    var bar = document.querySelector('[data-progress]');
    function progress() {
      if (!bar) return;
      var r = article.getBoundingClientRect();
      var total = r.height - window.innerHeight;
      var p = total > 0 ? Math.min(1, Math.max(0, -r.top / total)) : 1;
      bar.style.transform = 'scaleX(' + p + ')';
    }
    window.addEventListener('scroll', progress, { passive: true });
    window.addEventListener('resize', progress);
    progress();

    // current section in the table of contents
    var links = document.querySelectorAll('[data-toc] a');
    if (links.length && 'IntersectionObserver' in window) {
      var map = {};
      links.forEach(function (a) { map[a.getAttribute('href').slice(1)] = a; });
      var io = new IntersectionObserver(function (entries) {
        entries.forEach(function (en) {
          if (en.isIntersecting && map[en.target.id]) {
            links.forEach(function (a) { a.classList.remove('is-current'); });
            map[en.target.id].classList.add('is-current');
          }
        });
      }, { rootMargin: '-20% 0px -70% 0px' });
      Object.keys(map).forEach(function (id) {
        var h = document.getElementById(id);
        if (h) io.observe(h);
      });
    }

    // copy link
    var copy = document.querySelector('[data-copy-link]');
    if (copy && navigator.clipboard) {
      copy.addEventListener('click', function () {
        navigator.clipboard.writeText(location.href).then(function () {
          var label = copy.querySelector('span');
          var old = label.textContent;
          label.textContent = 'Link copied';
          setTimeout(function () { label.textContent = old; }, 2000);
        });
      });
    } else if (copy) {
      copy.hidden = true;
    }
  }

  TK.initPage = function () {
    initList();
    initPost();
  };
})();
