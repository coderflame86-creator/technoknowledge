/* =========================================================
   Technoknowledge: flipbook.js
   Opens a book preview as a page-turning flipbook.
   Markup (any element can open a book):
     <button data-flipbook="../assets/books/grade-1/" data-pages="37"
             data-title="Bit-by-Bit · Grade 1">Preview</button>
   Page images: p001.webp, p002.webp … plus thumbs.webp (10 per row, 92×119 each)
   - Two-page spread on wide screens, one page on phones
   - Arrows, keyboard (← → Home End Esc), swipe, thumbnails, zoom
   - Without JavaScript the button does nothing harmful; the
     book cover and chapter list stay on the page
   ========================================================= */
(function () {
  'use strict';
  window.TK = window.TK || {};

  var reduceMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  function pad(n) { return ('00' + n).slice(-3); }
  function el(tag, cls, attrs) {
    var e = document.createElement(tag);
    if (cls) e.className = cls;
    if (attrs) Object.keys(attrs).forEach(function (k) { e.setAttribute(k, attrs[k]); });
    return e;
  }

  TK.initFlipbook = function () {
    var openers = document.querySelectorAll('[data-flipbook]');
    if (!openers.length || !window.HTMLDialogElement) return;

    /* ---------- build the viewer once ---------- */
    var dlg = el('dialog', 'flipbook', { 'aria-label': 'Book preview' });
    var bar = el('div', 'flipbook__bar');
    var title = el('p', 'flipbook__title');
    var count = el('p', 'flipbook__count', { 'aria-live': 'polite' });
    var tools = el('div', 'flipbook__tools');
    var zoomBtn = el('button', 'flipbook__tool', { type: 'button', 'aria-pressed': 'false', 'aria-label': 'Zoom' });
    zoomBtn.innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5M11 8v6M8 11h6"/></svg>';
    var cta = el('a', 'btn btn--primary flipbook__cta', { href: 'request-proposal.html?interest=books' });
    cta.textContent = 'Request these books';
    var closeBtn = el('button', 'flipbook__tool flipbook__close', { type: 'button', 'aria-label': 'Close preview' });
    closeBtn.innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18"/></svg>';
    tools.append(zoomBtn, cta, closeBtn);
    bar.append(title, count, tools);

    var stageWrap = el('div', 'flipbook__stage-wrap');
    var prev = el('button', 'flipbook__nav flipbook__prev', { type: 'button', 'aria-label': 'Previous page' });
    prev.innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m15 18-6-6 6-6"/></svg>';
    var next = el('button', 'flipbook__nav flipbook__next', { type: 'button', 'aria-label': 'Next page' });
    next.innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m9 18 6-6-6-6"/></svg>';
    var stage = el('div', 'flipbook__stage');
    var book = el('div', 'flipbook__book');
    var left = el('div', 'flipbook__page flipbook__page--left');
    var right = el('div', 'flipbook__page flipbook__page--right');
    var leftImg = el('img', '', { alt: '', draggable: 'false' });
    var rightImg = el('img', '', { alt: '', draggable: 'false' });
    left.append(leftImg); right.append(rightImg);
    book.append(left, right);
    stage.append(book);
    stageWrap.append(prev, stage, next);

    var thumbs = el('div', 'flipbook__thumbs', { role: 'list', 'aria-label': 'Pages' });

    dlg.append(bar, stageWrap, thumbs);
    document.body.appendChild(dlg);

    /* ---------- state ---------- */
    var base = '', total = 0, page = 1, busy = false, zoomed = false;
    function spread() { return window.matchMedia('(min-width: 900px)').matches; }
    function src(n) { return base + 'p' + pad(n) + '.webp'; }
    function preload(n) { if (n >= 1 && n <= total) { var i = new Image(); i.src = src(n); } }

    /* in spread mode page 1 (cover) sits alone on the right, then 2-3, 4-5 … */
    function pagesFor(p) {
      if (!spread()) return [null, p];
      if (p === 1) return [null, 1];
      var l = p % 2 === 0 ? p : p - 1;
      return [l, l + 1 <= total ? l + 1 : null];
    }
    function setImg(img, n) {
      if (n) { img.src = src(n); img.alt = 'Page ' + n; img.parentNode.classList.remove('is-empty'); }
      else { img.removeAttribute('src'); img.alt = ''; img.parentNode.classList.add('is-empty'); }
    }
    /* size the book to fit the stage (letter-size pages: height = 1.294 × width) */
    function fit() {
      if (zoomed) { book.style.width = ''; book.style.height = ''; return; }
      var r = stage.getBoundingClientRect();
      var ratio = spread() ? 2 / 1.294 : 1 / 1.294;
      var h = Math.min(r.height - 8, (r.width - 8) / ratio);
      book.style.height = Math.max(h, 120) + 'px';
      book.style.width = Math.max(h, 120) * ratio + 'px';
    }
    function render() {
      fit();
      var pr = pagesFor(page);
      book.classList.toggle('is-single', !spread());
      book.classList.toggle('is-cover', spread() && page === 1);
      setImg(leftImg, pr[0]);
      setImg(rightImg, pr[1]);
      var shown = pr.filter(Boolean);
      count.textContent = (shown.length > 1 ? 'Pages ' + shown[0] + '–' + shown[1] : 'Page ' + shown[0]) + ' of ' + total;
      prev.disabled = page <= 1;
      next.disabled = (pr[1] || pr[0]) >= total;
      thumbs.querySelectorAll('button').forEach(function (b) {
        var n = +b.getAttribute('data-page');
        var on = shown.indexOf(n) !== -1;
        b.classList.toggle('is-current', on);
        if (on) b.setAttribute('aria-current', 'page'); else b.removeAttribute('aria-current');
      });
      var cur = thumbs.querySelector('.is-current');
      // scroll only the thumbnail strip sideways (never the page itself)
      if (cur && thumbs.scrollWidth > thumbs.clientWidth) {
        var left = cur.offsetLeft - (thumbs.clientWidth - cur.offsetWidth) / 2;
        thumbs.scrollTo({ left: Math.max(0, left), behavior: reduceMotion ? 'auto' : 'smooth' });
      }
      var last = pr[1] || pr[0];
      preload(last + 1); preload(last + 2); preload(shown[0] - 1); preload(shown[0] - 2);
    }

    /* the page-turn: a leaf rotates around the spine (or the left edge on phones) */
    function turn(dir) {
      if (busy) return;
      var pr = pagesFor(page);
      var target;
      if (spread()) target = dir > 0 ? (pr[1] || pr[0]) + 1 : (page === 1 ? 1 : (pr[0] || 1) - 1);
      else target = page + dir;
      if (target < 1 || target > total || target === page) return;
      if (spread() && pagesFor(target)[0] === pr[0] && pagesFor(target)[1] === pr[1]) return;
      if (reduceMotion || zoomed) { page = target; render(); return; }

      busy = true;
      var tp = pagesFor(target);
      var leaf = el('div', 'flipbook__leaf ' + (dir > 0 ? 'is-forward' : 'is-back') + (spread() ? '' : ' is-single'));
      var front = el('div', 'flipbook__leaf-face flipbook__leaf-front');
      var back = el('div', 'flipbook__leaf-face flipbook__leaf-back');
      var fi = el('img', '', { alt: '' }), bi = el('img', '', { alt: '' });
      front.append(fi); back.append(bi); leaf.append(front, back);

      if (spread()) {
        book.classList.toggle('is-cover', tp[0] === null);
        if (dir > 0) {           // right page lifts and lands on the left
          fi.src = src(pr[1] || pr[0]); bi.src = src(tp[0]);
          setImg(rightImg, tp[1]);
        } else {                 // left page lifts and lands on the right
          fi.src = src(pr[0]); bi.src = src(tp[1]);
          setImg(leftImg, tp[0]);
        }
      } else {
        fi.src = src(dir > 0 ? page : target);
        setImg(rightImg, dir > 0 ? target : page);
        if (dir < 0) { bi.removeAttribute('src'); }
      }
      book.appendChild(leaf);
      requestAnimationFrame(function () { requestAnimationFrame(function () { leaf.classList.add('is-turning'); }); });
      var done = function () {
        leaf.remove(); page = target; busy = false; render();
      };
      leaf.addEventListener('transitionend', done, { once: true });
      setTimeout(function () { if (busy) done(); }, 900);
    }

    function buildThumbs() {
      thumbs.innerHTML = '';
      for (var n = 1; n <= total; n++) {
        var b = el('button', 'flipbook__thumb', { type: 'button', 'data-page': n, 'aria-label': 'Go to page ' + n, role: 'listitem' });
        var im = el('i', 'flipbook__thumb-img', { 'aria-hidden': 'true' });
        im.style.backgroundImage = 'url(' + base + 'thumbs.webp)';
        im.style.backgroundPosition = (-((n - 1) % 10) * 46) + 'px ' + (-Math.floor((n - 1) / 10) * 59.5) + 'px';
        im.style.backgroundSize = '460px auto';
        var s = el('span'); s.textContent = n;
        b.append(im, s);
        thumbs.appendChild(b);
      }
    }

    function setZoom(on) {
      zoomed = on;
      dlg.classList.toggle('is-zoomed', on);
      zoomBtn.setAttribute('aria-pressed', on ? 'true' : 'false');
      zoomBtn.setAttribute('aria-label', on ? 'Zoom out' : 'Zoom in');
      if (!on) stage.scrollTo(0, 0);
      fit();
    }

    function open(btn) {
      base = btn.getAttribute('data-flipbook');
      total = parseInt(btn.getAttribute('data-pages'), 10) || 1;
      title.textContent = btn.getAttribute('data-title') || 'Book preview';
      page = 1; setZoom(false);
      buildThumbs();
      dlg.showModal();
      render();
      document.documentElement.classList.add('flipbook-open');
      next.focus();
    }

    /* ---------- events ---------- */
    openers.forEach(function (b) {
      b.addEventListener('click', function (e) { e.preventDefault(); open(b); });
    });
    prev.addEventListener('click', function () { turn(-1); });
    next.addEventListener('click', function () { turn(1); });
    closeBtn.addEventListener('click', function () { dlg.close(); });
    zoomBtn.addEventListener('click', function () { setZoom(!zoomed); });
    dlg.addEventListener('close', function () { document.documentElement.classList.remove('flipbook-open'); });
    thumbs.addEventListener('click', function (e) {
      var b = e.target.closest('button[data-page]');
      if (b) { page = +b.getAttribute('data-page'); render(); }
    });
    // click on the right half → next, left half → previous (when not zoomed)
    var ignoreClick = false;
    book.addEventListener('click', function (e) {
      if (zoomed || ignoreClick) { ignoreClick = false; return; }
      var r = book.getBoundingClientRect();
      turn(e.clientX > r.left + r.width / 2 ? 1 : -1);
    });
    dlg.addEventListener('keydown', function (e) {
      if (e.key === 'ArrowRight') { e.preventDefault(); turn(1); }
      if (e.key === 'ArrowLeft') { e.preventDefault(); turn(-1); }
      if (e.key === 'Home') { page = 1; render(); }
      if (e.key === 'End') { page = total; render(); }
    });
    // swipe
    var sx = null;
    stage.addEventListener('pointerdown', function (e) { sx = e.clientX; });
    stage.addEventListener('pointerup', function (e) {
      if (sx === null || zoomed) return;
      var dx = e.clientX - sx; sx = null;
      if (Math.abs(dx) > 50) { ignoreClick = true; turn(dx < 0 ? 1 : -1); }
    });
    window.addEventListener('resize', function () { if (dlg.open) render(); });
  };
})();
