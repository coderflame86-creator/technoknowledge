/* =========================================================
   Technoknowledge: typer.js
   Types out a code block letter by letter when the page loads,
   keeping its colours. Usage: <code data-typer> … </code>
   Optional: data-typer-speed="32" (ms per letter),
             data-typer-delay="500" (ms before typing starts).
   With "reduce motion" switched on, the code is shown at once.
   ========================================================= */
(function () {
  'use strict';
  window.TK = window.TK || {};

  TK.initTypers = function () {
    var els = document.querySelectorAll('[data-typer]');
    if (!els.length) return;
    var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    Array.prototype.forEach.call(els, function (el) {
      if (reduce) return;
      var speed = parseInt(el.getAttribute('data-typer-speed'), 10) || 32;
      var delay = parseInt(el.getAttribute('data-typer-delay'), 10) || 500;

      // keep the box its final size so nothing below jumps while typing
      var box = el.closest('pre') || el;
      box.style.minHeight = box.offsetHeight + 'px';
      box.style.minWidth = box.offsetWidth + 'px';

      // collect every text piece (with its colour wrapper) and empty it
      var parts = [];
      (function walk(node) {
        node.childNodes.forEach(function (n) {
          if (n.nodeType === 3) { parts.push({ node: n, text: n.nodeValue }); n.nodeValue = ''; }
          else walk(n);
        });
      })(el);

      var caret = document.createElement('span');
      caret.className = 'typer-caret';
      caret.setAttribute('aria-hidden', 'true');
      el.setAttribute('aria-label', parts.map(function (p) { return p.text; }).join(''));

      var i = 0, j = 0;
      function place() {
        var n = parts[i] && parts[i].node;
        if (n) n.parentNode.insertBefore(caret, n.nextSibling);
      }
      function step() {
        if (i >= parts.length) { el.appendChild(caret); caret.classList.add('is-done'); return; }
        var p = parts[i];
        if (j < p.text.length) {
          var ch = p.text.charAt(j++);
          p.node.nodeValue += ch;
          place();
          // a short pause at the end of each line, faster through indentation
          var wait = ch === '\n' ? speed * 6 : (ch === ' ' && p.node.nodeValue.slice(-2) === '  ' ? 4 : speed);
          setTimeout(step, wait);
        } else { i++; j = 0; step(); }
      }
      place();
      setTimeout(step, delay);
    });
  };
})();
