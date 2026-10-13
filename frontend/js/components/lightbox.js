/* =========================================================
   Technoknowledge: lightbox.js
   Opens a larger image in a dialog.
   Markup: <a href="big.webp" data-lightbox="group" data-caption="Title">
             <img src="thumb.webp" alt="…"></a>
   - Previous / next within the same group, arrow keys, Escape
   - Without JavaScript the link simply opens the big image
   ========================================================= */
(function () {
  'use strict';
  window.TK = window.TK || {};

  TK.initLightbox = function () {
    var links = Array.prototype.slice.call(document.querySelectorAll('a[data-lightbox]'));
    if (!links.length || !window.HTMLDialogElement) return;

    var dlg = document.createElement('dialog');
    dlg.className = 'lightbox';
    dlg.setAttribute('aria-label', 'Image preview');
    dlg.innerHTML =
      '<div class="lightbox__inner">' +
      '<button class="lightbox__close" type="button" aria-label="Close">×</button>' +
      '<button class="lightbox__nav lightbox__prev" type="button" aria-label="Previous">‹</button>' +
      '<figure class="lightbox__fig"><img alt=""><figcaption></figcaption></figure>' +
      '<button class="lightbox__nav lightbox__next" type="button" aria-label="Next">›</button>' +
      '</div>';
    document.body.appendChild(dlg);

    var img = dlg.querySelector('img');
    var cap = dlg.querySelector('figcaption');
    var group = [], index = 0;

    function show(i) {
      index = (i + group.length) % group.length;
      var a = group[index];
      img.src = a.getAttribute('href');
      img.alt = (a.querySelector('img') || {}).alt || '';
      cap.textContent = a.getAttribute('data-caption') || '';
      dlg.classList.toggle('is-single', group.length < 2);
    }

    links.forEach(function (a) {
      a.addEventListener('click', function (e) {
        e.preventDefault();
        var name = a.getAttribute('data-lightbox');
        group = links.filter(function (l) { return l.getAttribute('data-lightbox') === name; });
        show(group.indexOf(a));
        dlg.showModal();
      });
    });

    dlg.querySelector('.lightbox__close').addEventListener('click', function () { dlg.close(); });
    dlg.querySelector('.lightbox__prev').addEventListener('click', function () { show(index - 1); });
    dlg.querySelector('.lightbox__next').addEventListener('click', function () { show(index + 1); });
    dlg.addEventListener('click', function (e) { if (e.target === dlg) dlg.close(); });
    dlg.addEventListener('keydown', function (e) {
      if (e.key === 'ArrowRight') show(index + 1);
      if (e.key === 'ArrowLeft') show(index - 1);
    });
  };
})();
