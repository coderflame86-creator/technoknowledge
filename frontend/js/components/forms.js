/* =========================================================
   Technoknowledge: forms.js
   Shared form handling for every form on the site.
   Markup:
     <form data-form="campRegistration" novalidate>
       <div class="field"> label + input + <p class="field__error"></p> </div>
       <input class="hp-field" name="website">   (spam trap, keep empty)
       <button type="submit">…</button>
       <p class="form-status" role="status"></p>
     </form>
   - Checks required fields, phone and email before sending
   - A .field with data-required-group needs at least one ticked checkbox
   - Shows errors next to each field and moves focus to the first
   - Sends through TK.api.submit() (js/api.js)
   - Uses textContent only (never innerHTML) for anything typed by users
   ========================================================= */
(function () {
  'use strict';
  window.TK = window.TK || {};

  var MESSAGES = {
    required: 'Please fill this in.',
    choice: 'Please choose one.',
    group: 'Please choose at least one.',
    phone: 'Please enter a full mobile number, e.g. 0300 1234567.',
    email: 'Please enter a valid email address.',
    consent: 'Please tick this box so we can contact you.'
  };

  function isPhone(v) {
    var digits = v.replace(/[^\d]/g, '');
    return digits.length >= 10 && digits.length <= 13;
  }
  function isEmail(v) { return /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(v); }

  function setError(field, msg) {
    if (!field) return;
    var box = field.querySelector('.field__error');
    field.classList.toggle('has-error', !!msg);
    if (box) box.textContent = msg || '';
    field.querySelectorAll('input, select, textarea').forEach(function (el) {
      if (msg) el.setAttribute('aria-invalid', 'true'); else el.removeAttribute('aria-invalid');
    });
  }

  /** Returns {valid, data}. data holds trimmed values; checkboxes with the same name become arrays. */
  function validate(form) {
    var data = {}, firstBad = null;

    form.querySelectorAll('input, select, textarea').forEach(function (el) {
      if (!el.name || el.disabled || el.classList.contains('hp-field')) return;
      if (el.type === 'radio') { if (el.checked) data[el.name] = el.value; return; }
      if (el.type === 'checkbox') {
        if (el.hasAttribute('data-single')) { data[el.name] = el.checked; return; }
        if (!Array.isArray(data[el.name])) data[el.name] = [];
        if (el.checked) data[el.name].push(el.value);
        return;
      }
      if (el.type === 'hidden' && el.hasAttribute('data-multi')) {
        if (!Array.isArray(data[el.name])) data[el.name] = [];
        data[el.name].push(el.value);
        return;
      }
      data[el.name] = (el.value || '').trim();
    });

    form.querySelectorAll('.field').forEach(function (field) {
      var msg = '';
      var input = field.querySelector('input:not([type=radio]):not([type=checkbox]), select, textarea');
      var radios = field.querySelectorAll('input[type=radio]');
      var consent = field.querySelector('input[type=checkbox][data-single][required]');

      if (radios.length && radios[0].required && !field.querySelector('input[type=radio]:checked')) msg = MESSAGES.choice;
      else if (field.hasAttribute('data-required-group') && !field.querySelector('input[type=checkbox]:checked')) msg = MESSAGES.group;
      else if (consent && !consent.checked) msg = MESSAGES.consent;
      else if (input) {
        var v = (input.value || '').trim();
        if (input.required && !v) msg = MESSAGES.required;
        else if (v && input.type === 'tel' && !isPhone(v)) msg = MESSAGES.phone;
        else if (v && input.type === 'email' && !isEmail(v)) msg = MESSAGES.email;
      }
      setError(field, msg);
      if (msg && !firstBad) firstBad = field;
    });

    if (firstBad) {
      var focusEl = firstBad.querySelector('input, select, textarea');
      firstBad.scrollIntoView({ behavior: 'smooth', block: 'center' });
      if (focusEl) setTimeout(function () { focusEl.focus({ preventScroll: true }); }, 300);
    }
    return { valid: !firstBad, data: data };
  }

  function showStatus(form, type, text) {
    var s = form.querySelector('.form-status');
    if (!s) return;
    s.className = 'form-status is-' + type;
    s.textContent = text;
  }

  TK.forms = { validate: validate, setError: setError, showStatus: showStatus };

  TK.initForms = function () {
    document.querySelectorAll('form[data-form]').forEach(function (form) {
      // clear a field's error as soon as the visitor fixes it
      form.addEventListener('input', function (e) {
        var f = e.target.closest('.field');
        if (f && f.classList.contains('has-error')) setError(f, '');
      });
      form.addEventListener('change', function (e) {
        var f = e.target.closest('.field');
        if (f && f.classList.contains('has-error')) setError(f, '');
      });

      form.addEventListener('submit', function (e) {
        e.preventDefault();
        var hp = form.querySelector('.hp-field');
        if (hp && hp.value) return; // bot filled the hidden field

        var result = validate(form);
        if (!result.valid) { showStatus(form, 'error', 'Please check the highlighted fields.'); return; }

        var btn = form.querySelector('[type=submit]');
        if (btn) { btn.disabled = true; btn.classList.add('is-loading'); btn.setAttribute('data-label', btn.textContent); btn.textContent = 'Sending…'; }
        showStatus(form, 'info', '');

        TK.api.submit(form.getAttribute('data-form'), result.data)
          .then(function (res) {
            form.dispatchEvent(new CustomEvent('tk:sent', { detail: { preview: !!res.preview, data: result.data } }));
            var ok = form.getAttribute('data-success') || 'Thank you! We will contact you soon.';
            showStatus(form, 'success', res.preview ? ok + ' (Preview: this form is not connected yet.)' : ok);
          })
          .catch(function () {
            showStatus(form, 'error', 'Sorry, your form could not be sent. Please try again, or WhatsApp us on +92 345 8205013.');
          })
          .then(function () {
            if (btn) { btn.disabled = false; btn.classList.remove('is-loading'); btn.textContent = btn.getAttribute('data-label'); }
          });
      });
    });
  };
})();
