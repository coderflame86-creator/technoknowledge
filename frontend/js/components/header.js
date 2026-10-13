/* =========================================================
   Technoknowledge: header.js
   Sticky header shadow, desktop dropdowns (hover + click +
   keyboard) and the mobile menu drawer.
   ========================================================= */
(function () {
  'use strict';
  window.TK = window.TK || {};

  TK.initHeader = function () {
    var header = document.getElementById('site-header');
    if (!header) return;

    /* ---- shadow once the page scrolls ---- */
    var onScroll = function () {
      header.classList.toggle('is-scrolled', window.scrollY > 8);
      if (document.body.classList.contains('menu-open')) placeDrawer();
    };
    onScroll();
    window.addEventListener('scroll', onScroll, { passive: true });

    /* ---- desktop dropdowns ---- */
    var items = Array.prototype.slice.call(header.querySelectorAll('.nav__item'));
    var canHover = window.matchMedia('(hover: hover) and (pointer: fine)').matches;
    var closeTimer;

    function setOpen(item, open) {
      var btn = item.querySelector('.nav__toggle');
      if (!btn) return;
      item.classList.toggle('is-open', open);
      btn.setAttribute('aria-expanded', open ? 'true' : 'false');
    }
    function closeAll(except) {
      items.forEach(function (it) { if (it !== except) setOpen(it, false); });
    }

    items.forEach(function (item) {
      var btn = item.querySelector('.nav__toggle');
      if (!btn) return;

      btn.addEventListener('click', function () {
        var open = !item.classList.contains('is-open');
        closeAll(item);
        setOpen(item, open);
      });

      if (canHover) {
        item.addEventListener('mouseenter', function () {
          clearTimeout(closeTimer);
          closeAll(item);
          setOpen(item, true);
        });
        item.addEventListener('mouseleave', function () {
          closeTimer = setTimeout(function () { setOpen(item, false); }, 180);
        });
      }

      // close when keyboard focus leaves the item
      item.addEventListener('focusout', function (e) {
        if (!item.contains(e.relatedTarget)) setOpen(item, false);
      });
    });

    document.addEventListener('click', function (e) {
      if (!e.target.closest('.nav__item')) closeAll();
    });

    /* ---- mobile drawer ---- */
    // place the drawer right under the header, wherever the header is now
    function placeDrawer() {
      var bottom = Math.max(0, header.getBoundingClientRect().bottom);
      document.documentElement.style.setProperty('--menu-top', bottom + 'px');
    }
    var menuBtn = header.querySelector('.menu-button');
    var drawer = document.getElementById('mobile-nav');

    function setMenu(open) {
      if (!menuBtn || !drawer) return;
      if (open) placeDrawer();
      drawer.classList.toggle('is-open', open);
      document.body.classList.toggle('menu-open', open);
      menuBtn.setAttribute('aria-expanded', open ? 'true' : 'false');
      menuBtn.setAttribute('aria-label', open ? 'Close menu' : 'Open menu');
    }

    if (menuBtn && drawer) {
      menuBtn.addEventListener('click', function () {
        setMenu(menuBtn.getAttribute('aria-expanded') !== 'true');
      });
      // close the drawer after choosing a link (also for #anchors on the same page)
      drawer.addEventListener('click', function (e) {
        if (e.target.closest('a')) setMenu(false);
      });
      window.addEventListener('resize', function () {
        if (window.innerWidth >= 1100) setMenu(false);
      });
    }

    /* ---- a menu link to the page you are already on ----
       Instead of reloading the same page (which looks like nothing happened),
       close the menu and glide to the page's main spot: a section marked
       data-menu-target (e.g. the message form on Contact), or the top. */
    document.addEventListener('click', function (e) {
      var a = e.target.closest && e.target.closest('a[aria-current="page"]');
      if (!a || !(header.contains(a) || (drawer && drawer.contains(a)))) return;
      if ((a.getAttribute('href') || '').indexOf('#') !== -1) return;
      e.preventDefault();
      closeAll();
      setMenu(false);
      var to = document.querySelector('[data-menu-target]');
      if (to) to.scrollIntoView({ behavior: 'smooth', block: 'start' });
      else window.scrollTo({ top: 0, behavior: 'smooth' });
    });

    /* ---- Escape closes everything ---- */
    document.addEventListener('keydown', function (e) {
      if (e.key !== 'Escape') return;
      var openItem = header.querySelector('.nav__item.is-open');
      if (openItem) {
        setOpen(openItem, false);
        openItem.querySelector('.nav__toggle').focus();
      }
      if (drawer && drawer.classList.contains('is-open')) {
        setMenu(false);
        menuBtn.focus();
      }
    });
  };
})();
