/* =========================================================
   Technoknowledge: pages/techno-camps.js
   - "Add to registration" buttons on course cards
   - Chosen courses appear as removable chips in the form
     (as hidden inputs name="courses")
   - "Choose 1-to-1 / group" buttons pre-select the class type
   - Sticky bar shows how many courses are chosen
   - Hides the form after a successful send
   ========================================================= */
(function () {
  'use strict';
  window.TK = window.TK || {};

  TK.initPage = function () {
    var form = document.querySelector('form[data-form="campRegistration"]');
    if (!form) return;

    var list = form.querySelector('[data-picked-list]');
    var empty = form.querySelector('[data-picked-empty]');
    var bar = document.querySelector('[data-pick-bar]');
    var countEl = document.querySelector('[data-pick-count]');
    var wordEl = document.querySelector('[data-pick-word]');
    var register = document.getElementById('register');
    var picked = [];

    function buttonsFor(value) {
      return Array.prototype.filter.call(document.querySelectorAll('.course__add'), function (b) {
        return b.getAttribute('data-course') === value;
      });
    }

    function render() {
      list.textContent = '';
      picked.forEach(function (value) {
        var li = document.createElement('li');
        li.className = 'picked__chip';
        var label = document.createElement('span');
        label.textContent = value;
        var hidden = document.createElement('input');
        hidden.type = 'hidden'; hidden.name = 'courses'; hidden.value = value; hidden.setAttribute('data-multi', '');
        var remove = document.createElement('button');
        remove.type = 'button';
        remove.className = 'picked__remove';
        remove.setAttribute('aria-label', 'Remove ' + value);
        remove.textContent = '×';
        remove.addEventListener('click', function () { toggle(value, false); });
        li.appendChild(label); li.appendChild(hidden); li.appendChild(remove);
        list.appendChild(li);
      });
      empty.hidden = picked.length > 0;
      if (countEl) countEl.textContent = picked.length;
      if (wordEl) wordEl.textContent = picked.length === 1 ? 'course' : 'courses';
      updateBar();
    }

    function toggle(value, on) {
      var i = picked.indexOf(value);
      if (on === undefined) on = i === -1;
      if (on && i === -1) picked.push(value);
      if (!on && i !== -1) picked.splice(i, 1);
      buttonsFor(value).forEach(function (b) { b.setAttribute('aria-pressed', on ? 'true' : 'false'); });
      render();
    }

    document.querySelectorAll('.course__add').forEach(function (btn) {
      btn.addEventListener('click', function () { toggle(btn.getAttribute('data-course')); });
    });

    // format buttons → pre-select class type and go to the form
    document.querySelectorAll('[data-choose-format]').forEach(function (btn) {
      btn.addEventListener('click', function () {
        var radio = form.querySelector('input[name="classType"][value="' + btn.getAttribute('data-choose-format') + '"]');
        if (radio) { radio.checked = true; radio.dispatchEvent(new Event('change', { bubbles: true })); }
        register.scrollIntoView({ behavior: 'smooth', block: 'start' });
      });
    });

    // sticky bar: only when courses are chosen and the form is not on screen
    var formVisible = false;
    function updateBar() {
      if (!bar) return;
      bar.hidden = !(picked.length > 0 && !formVisible);
    }
    if ('IntersectionObserver' in window) {
      new IntersectionObserver(function (entries) {
        formVisible = entries[0].isIntersecting;
        updateBar();
      }, { threshold: 0.05 }).observe(register);
    }

    // after a successful send, swap the form for a thank-you panel
    form.addEventListener('tk:sent', function () {
      picked.slice().forEach(function (v) { toggle(v, false); });
      form.reset();
      render();
    });

    render();
  };
})();

/* ---- "Why parents choose" strip: highlight moves from card to card ----
   Adds .is-hi to one card at a time (every 2.4 s). Hover or keyboard focus
   highlights that card and pauses; the rotation resumes when the pointer
   leaves. Reduced motion: only hover/focus highlights, no rotation. */
(function () {
  'use strict';
  function start() {
    var list = document.querySelector('.camps-features__list');
    if (!list) return;
    var items = Array.prototype.slice.call(list.children);
    if (!items.length) return;
    var MS = 2400, i = -1, timer = null;
    var still = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    list.style.setProperty('--cf-ms', MS + 'ms');

    function show(n) {
      items.forEach(function (li, k) { li.classList.toggle('is-hi', k === n); });
      i = n;
    }
    function next() { show((i + 1) % items.length); }
    function play() {
      if (still || timer) return;
      list.classList.remove('is-paused');
      timer = setInterval(next, MS);
    }
    function pause() { clearInterval(timer); timer = null; list.classList.add('is-paused'); }

    items.forEach(function (li, k) {
      li.addEventListener('mouseenter', function () { pause(); show(k); });
      li.addEventListener('focusin', function () { pause(); show(k); });
    });
    list.addEventListener('mouseleave', function () { if (still) show(-1); else { next(); play(); } });
    list.addEventListener('focusout', function (e) { if (!list.contains(e.relatedTarget)) { if (still) show(-1); else play(); } });
    // pause while the tab is hidden
    document.addEventListener('visibilitychange', function () { if (document.hidden) { clearInterval(timer); timer = null; } else if (!list.classList.contains('is-paused')) play(); });

    if (!still) setTimeout(function () { next(); play(); }, 900);   // after the slide-in
  }
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', start);
  else start();
})();
