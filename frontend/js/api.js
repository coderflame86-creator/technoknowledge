/* =========================================================
   Technoknowledge: api.js
   The ONLY file that sends form data anywhere.
   Forms go to one Google Apps Script web app (set up in Step 7),
   which saves each form to its own tab in the Google Sheet and
   emails technoknowledge19@gmail.com.
   ========================================================= */
(function () {
  'use strict';
  window.TK = window.TK || {};
  var LOADED_AT = Date.now();   // used for the "sent too fast = bot" check

  // Backend form endpoint: auto-detects local subfolder or production domain
  var ENDPOINT = (function () {
    if (window.TK_ENDPOINT) return window.TK_ENDPOINT;
    if (location.pathname.indexOf('/TK/') !== -1) {
      return '/TK/backend/api/submit.php';
    }
    return '/backend/api/submit.php';
  })();

  // Form names (must match the tab names in the Google Sheet)
  var FORMS = {
    campRegistration: 'Camp Registrations',
    proposalRequest: 'Proposal Requests',
    aitepStarter: 'AITEP Starter Bookings',
    contact: 'Contact Messages'
  };

  /**
   * Send one form.
   * @param {string} formKey  one of the FORMS keys
   * @param {object} data     plain field values (no files)
   * @returns {Promise<{ok:boolean, preview?:boolean, message?:string}>}
   */
  function submit(formKey, data) {
    if (!FORMS[formKey]) return Promise.reject(new Error('Unknown form: ' + formKey));

    var payload = Object.assign({}, data, {
      form: FORMS[formKey],
      page: location.pathname,
      sentAt: new Date().toISOString(),
      elapsed: Date.now() - LOADED_AT
    });

    if (!ENDPOINT) {
      return new Promise(function (resolve) {
        setTimeout(function () { resolve({ ok: true, preview: true }); }, 700);
      });
    }

    // text/plain avoids a CORS pre-flight request, which Apps Script does not answer
    return fetch(ENDPOINT, {
      method: 'POST',
      headers: { 'Content-Type': 'text/plain;charset=utf-8' },
      body: JSON.stringify(payload)
    })
      .then(function (res) { return res.json(); })
      .then(function (json) {
        if (!json || json.ok !== true) throw new Error((json && json.message) || 'Something went wrong.');
        return json;
      });
  }

  TK.api = { submit: submit, FORMS: FORMS };
})();
