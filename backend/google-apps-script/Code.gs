/**
 * =========================================================
 * Technoknowledge: website forms → Google Sheet + email
 * =========================================================
 * Set up and run this ONLY while signed in as technoknowledge19@gmail.com.
 * Full steps: backend/google-apps-script/SETUP-GUIDE.md
 *
 * What it does for every form sent from thetechnoknowledge.com:
 *   1. Saves a new row in the matching tab of the Google Sheet
 *      (Camp Registrations, Proposal Requests, AITEP Starter Bookings, Contact Messages)
 *   2. Emails the details to technoknowledge19@gmail.com, with a one-tap
 *      "Reply on WhatsApp" button to the person's own number
 *   3. Answers the website with { ok: true } so the visitor sees "Thank you"
 *
 * Spam protection: hidden honeypot field, "too fast" check, max 5 sends per
 * number per 10 minutes, length limits, and no spreadsheet formulas from visitors.
 *
 * No passwords or keys are stored here. The web app runs as the technoknowledge19@gmail.com account.
 */

// ---------------------------------------------------------
// SETTINGS (safe to edit)
// ---------------------------------------------------------
var SETTINGS = {
  NOTIFY_EMAIL: 'technoknowledge19@gmail.com',   // every form is emailed here
  COMPANY_WHATSAPP: '923458205013',              // +92 345 8205013 (shown in emails)
  SENDER_NAME: 'Technoknowledge Website',
  TIMEZONE: 'Asia/Karachi',
  SHEET_NAME: 'Technoknowledge Website Forms',   // used only if setup() has to create a new Sheet
  AUTO_REPLY: false,       // true = also send a short "we got your request" email when the visitor gave an email
  MAX_PER_10_MIN: 5,       // per WhatsApp number
  MIN_FILL_MS: 2500,       // forms sent faster than this are treated as bots
  MAX_FIELD_LENGTH: 3000
};

var STATUS_OPTIONS = ['New', 'Contacted', 'Demo / meeting booked', 'Proposal sent', 'Enrolled / signed', 'Not interested'];

// ---------------------------------------------------------
// FORMS: tab name → columns. Keys match the field names in the website forms.
// Tab names must match FORMS in frontend/js/api.js
// ---------------------------------------------------------
var FORMS = {
  'Camp Registrations': {
    label: 'Techno Camps registration',
    required: ['parentName', 'whatsapp', 'childName', 'childAge'],
    nameKey: 'parentName',
    columns: [
      ['Parent name', 'parentName'],
      ['WhatsApp', 'whatsapp'],
      ['Child first name', 'childName'],
      ['Child age', 'childAge'],
      ['Courses', 'courses'],
      ['Class type', 'classType'],
      ['City', 'city'],
      ['Heard about us from', 'source'],
      ['Notes', 'notes']
    ]
  },
  'Proposal Requests': {
    label: 'School proposal request',
    required: ['name', 'whatsapp', 'institution'],
    nameKey: 'name',
    columns: [
      ['Program', 'program'],
      ['Interested in', 'interests'],      // request-proposal (checkboxes)
      ['Option chosen', 'interest'],        // ITEC, LMS, Robotics, M&E pages
      ['Name', 'name'],
      ['Role', 'role'],
      ['School / institution', 'institution'],
      ['City', 'city'],
      ['WhatsApp', 'whatsapp'],
      ['Email', 'email'],
      ['Teachers', 'teachers'],
      ['Students', 'students'],
      ['Message', 'message']
    ]
  },
  'AITEP Starter Bookings': {
    label: 'AITEP booking',
    required: ['name', 'whatsapp', 'institution'],
    nameKey: 'name',
    columns: [
      ['Option chosen', 'interest'],
      ['Name', 'name'],
      ['Role', 'role'],
      ['School / institution', 'institution'],
      ['City', 'city'],
      ['WhatsApp', 'whatsapp'],
      ['Teachers', 'teachers'],
      ['Message', 'message']
    ]
  },
  'Contact Messages': {
    label: 'Contact message',
    required: ['name', 'whatsapp', 'message'],
    nameKey: 'name',
    columns: [
      ['Name', 'name'],
      ['I am a', 'iam'],
      ['WhatsApp', 'whatsapp'],
      ['Email', 'email'],
      ['Subject', 'subject'],
      ['Message', 'message']
    ]
  }
};

// Columns added to every tab
var FIRST_COLUMNS = ['Received (PKT)', 'Status', 'Chat on WhatsApp'];
var LAST_COLUMNS = ['Consent to contact', 'Sent from page'];

// =========================================================
// 1. RUN ONCE: creates/updates the tabs and links the Sheet
// =========================================================
function setup() {
  var ss = getSheet_(true);
  Object.keys(FORMS).forEach(function (tab) {
    var sh = ss.getSheetByName(tab) || ss.insertSheet(tab);
    var headers = headersFor_(tab);
    sh.getRange(1, 1, 1, headers.length).setValues([headers])
      .setFontWeight('bold').setBackground('#13294B').setFontColor('#FFFFFF').setWrap(true);
    sh.setFrozenRows(1);
    sh.setColumnWidth(1, 150);
    sh.setColumnWidth(2, 150);
    sh.setColumnWidth(3, 130);
    var status = SpreadsheetApp.newDataValidation().requireValueInList(STATUS_OPTIONS, true).setAllowInvalid(true).build();
    sh.getRange(2, 2, sh.getMaxRows() - 1, 1).setDataValidation(status);
  });
  // remove the empty default tab if it is still there
  var def = ss.getSheetByName('Sheet1');
  if (def && ss.getSheets().length > 1 && def.getLastRow() === 0) ss.deleteSheet(def);

  Logger.log('Ready. Sheet: ' + ss.getUrl());
  Logger.log('Emails go to: ' + SETTINGS.NOTIFY_EMAIL);
}

// =========================================================
// 2. OPTIONAL: run to check the email + sheet work end to end
// =========================================================
function sendTestSubmission() {
  var fake = {
    postData: { contents: JSON.stringify({
      form: 'Contact Messages', name: 'Test (please delete)', iam: 'School', whatsapp: '0345 8205013',
      email: SETTINGS.NOTIFY_EMAIL, subject: 'Test', message: 'Testing the website form connection.',
      consent: true, page: '/pages/contact.html', elapsed: 10000
    }) }
  };
  Logger.log(doPost(fake).getContent());
}

// =========================================================
// WEB APP
// =========================================================
function doGet() {
  return json_({ ok: true, service: 'Technoknowledge website forms' });
}

function doPost(e) {
  var data;
  try {
    data = JSON.parse((e && e.postData && e.postData.contents) || '{}');
  } catch (err) {
    return json_({ ok: false, message: 'Bad request.' });
  }

  var tab = String(data.form || '');
  var form = FORMS[tab];
  if (!form) return json_({ ok: false, message: 'Unknown form.' });

  // --- spam checks: answer "ok" so bots learn nothing, but save nothing ---
  if (data.website) return json_({ ok: true });
  if (typeof data.elapsed === 'number' && data.elapsed < SETTINGS.MIN_FILL_MS) return json_({ ok: true });

  // --- clean values ---
  var clean = {};
  Object.keys(data).forEach(function (k) { clean[k] = clean_(data[k]); });

  // --- required fields ---
  var missing = form.required.filter(function (k) { return !clean[k]; });
  if (missing.length) return json_({ ok: false, message: 'Please fill in all required fields.' });

  var waDigits = waNumber_(clean.whatsapp);
  if (!waDigits) return json_({ ok: false, message: 'Please enter a full mobile number.' });

  // --- rate limit per number ---
  var cache = CacheService.getScriptCache();
  var rlKey = 'rl_' + waDigits;
  var count = Number(cache.get(rlKey) || 0);
  if (count >= SETTINGS.MAX_PER_10_MIN) {
    return json_({ ok: false, message: 'We already have your request. Please WhatsApp us on +92 345 8205013.' });
  }
  cache.put(rlKey, String(count + 1), 600);

  var received = Utilities.formatDate(new Date(), SETTINGS.TIMEZONE, 'yyyy-MM-dd HH:mm');

  // --- 1. save to the Sheet ---
  var ss, rowNumber;
  var lock = LockService.getScriptLock();
  try {
    lock.waitLock(15000);
    ss = getSheet_(false);
    var sh = ss.getSheetByName(tab);
    if (!sh) { setup(); sh = ss.getSheetByName(tab); }
    var waLink = 'https://wa.me/' + waDigits;
    var row = [received, 'New', '=HYPERLINK("' + waLink + '","Chat")'];
    form.columns.forEach(function (c) { row.push(cell_(clean[c[1]])); });
    row.push(clean.consent ? 'Yes' : 'No');
    row.push(cell_(clean.page));
    sh.appendRow(row);
    rowNumber = sh.getLastRow();
  } catch (err) {
    console.error('Sheet save failed: ' + err);
  } finally {
    try { lock.releaseLock(); } catch (x) {}
  }

  // --- 2. email the team (SETTINGS.NOTIFY_EMAIL) ---
  var emailed = false;
  try {
    sendNotification_(tab, form, clean, waDigits, received, ss, rowNumber);
    emailed = true;
  } catch (err) {
    console.error('Email failed: ' + err);
  }

  // --- 3. optional auto-reply to the visitor ---
  if (SETTINGS.AUTO_REPLY && isEmail_(clean.email)) {
    try { sendAutoReply_(form, clean); } catch (err) { console.error('Auto-reply failed: ' + err); }
  }

  if (!rowNumber && !emailed) return json_({ ok: false, message: 'Please try again or WhatsApp us on +92 345 8205013.' });
  return json_({ ok: true });
}

// =========================================================
// EMAILS
// =========================================================
function sendNotification_(tab, form, d, waDigits, received, ss, rowNumber) {
  var who = d[form.nameKey] || 'Someone';
  var subject;
  if (tab === 'Camp Registrations') {
    subject = 'New camp registration: ' + (d.childName || 'child') + ', age ' + (d.childAge || '?') + ' (parent: ' + who + ')';
  } else if (tab === 'Proposal Requests') {
    var isDemo = d.program === 'Demo request' || /Book a demo/.test(d.interests || '');
    subject = (isDemo ? 'New demo request' : 'New proposal request' + (d.program ? ' (' + d.program + ')' : '')) + ': ' + (d.institution || who) + (d.city ? ', ' + d.city : '');
  } else if (tab === 'AITEP Starter Bookings') {
    subject = 'New AITEP booking: ' + (d.institution || who) + (d.interest ? ' (' + d.interest + ')' : '');
  } else {
    subject = 'New website message from ' + who + (d.subject ? ': ' + d.subject : '');
  }

  var greeting = 'Assalam o Alaikum ' + who + ', this is Technoknowledge. Thank you for your ' + form.label.toLowerCase() + '.';
  var waReply = 'https://wa.me/' + waDigits + '?text=' + encodeURIComponent(greeting);

  var rows = form.columns.map(function (c) {
    var v = d[c[1]];
    if (!v) return '';
    return '<tr><td style="padding:8px 12px;border-bottom:1px solid #E6EAF0;color:#5B6B7F;width:38%;vertical-align:top">' +
      esc_(c[0]) + '</td><td style="padding:8px 12px;border-bottom:1px solid #E6EAF0;color:#13294B;vertical-align:top">' +
      esc_(v).replace(/\n/g, '<br>') + '</td></tr>';
  }).join('');

  var sheetLink = ss ? ss.getUrl() + (rowNumber ? '#gid=' + ss.getSheetByName(tab).getSheetId() + '&range=A' + rowNumber : '') : '';

  var html =
    '<div style="font-family:Arial,Helvetica,sans-serif;max-width:620px;margin:0 auto;color:#13294B">' +
      '<div style="background:#13294B;color:#fff;padding:18px 22px;border-radius:10px 10px 0 0">' +
        '<div style="font-size:12px;letter-spacing:.06em;text-transform:uppercase;opacity:.8">' + esc_(form.label) + '</div>' +
        '<div style="font-size:20px;font-weight:bold;margin-top:4px">' + esc_(who) + '</div>' +
        '<div style="font-size:13px;opacity:.8;margin-top:4px">Received ' + esc_(received) + ' (Pakistan time)</div>' +
      '</div>' +
      '<div style="border:1px solid #E6EAF0;border-top:0;border-radius:0 0 10px 10px;padding:18px 10px">' +
        '<table style="border-collapse:collapse;width:100%;font-size:14px">' + rows + '</table>' +
        '<p style="margin:20px 12px 6px">' +
          '<a href="' + waReply + '" style="display:inline-block;background:#1FA855;color:#fff;text-decoration:none;font-weight:bold;padding:11px 18px;border-radius:8px;margin:0 8px 8px 0">Reply on WhatsApp</a>' +
          (sheetLink ? '<a href="' + sheetLink + '" style="display:inline-block;background:#FFD400;color:#13294B;text-decoration:none;font-weight:bold;padding:11px 18px;border-radius:8px;margin:0 8px 8px 0">Open in Google Sheet</a>' : '') +
        '</p>' +
        '<p style="margin:6px 12px;font-size:12px;color:#5B6B7F">Consent to contact: ' + (d.consent ? 'Yes' : 'No') +
          ' · Sent from ' + esc_(d.page || 'website') + '. Please update the Status column after you reply.</p>' +
      '</div>' +
    '</div>';

  var text = form.label + ' from ' + who + ' (' + received + ')\n\n' +
    form.columns.map(function (c) { return d[c[1]] ? c[0] + ': ' + d[c[1]] : ''; }).filter(String).join('\n') +
    '\n\nReply on WhatsApp: ' + waReply + (sheetLink ? '\nGoogle Sheet: ' + sheetLink : '');

  var opts = { name: SETTINGS.SENDER_NAME, htmlBody: html };
  if (isEmail_(d.email)) opts.replyTo = d.email;   // "Reply" in Gmail goes to the visitor
  MailApp.sendEmail(SETTINGS.NOTIFY_EMAIL, subject, text, opts);
}

function sendAutoReply_(form, d) {
  var name = d[form.nameKey] || '';
  var body =
    'Assalam o Alaikum ' + name + ',\n\n' +
    'Thank you for your ' + form.label.toLowerCase() + '. Our team will contact you within one working day.\n\n' +
    'If it is urgent, WhatsApp us on +92 345 8205013: https://wa.me/' + SETTINGS.COMPANY_WHATSAPP + '\n\n' +
    'Technoknowledge (SMC-Pvt) Ltd.\nhttps://thetechnoknowledge.com';
  MailApp.sendEmail(d.email, 'We received your request: Technoknowledge', body,
    { name: 'Technoknowledge', replyTo: SETTINGS.NOTIFY_EMAIL });
}

// =========================================================
// HELPERS
// =========================================================
function getSheet_(createIfMissing) {
  var props = PropertiesService.getScriptProperties();
  var id = props.getProperty('SHEET_ID');
  if (id) return SpreadsheetApp.openById(id);
  var ss = SpreadsheetApp.getActiveSpreadsheet();   // script opened from the Sheet (recommended)
  if (!ss && createIfMissing) ss = SpreadsheetApp.create(SETTINGS.SHEET_NAME);
  if (!ss) throw new Error('No Sheet linked. Run setup() first.');
  props.setProperty('SHEET_ID', ss.getId());
  return ss;
}

function headersFor_(tab) {
  return FIRST_COLUMNS.concat(FORMS[tab].columns.map(function (c) { return c[0]; }), LAST_COLUMNS);
}

/** Text only, trimmed and length-limited. */
function clean_(v) {
  if (v === true || v === false) return v;
  if (Array.isArray(v)) v = v.map(String).filter(String).join(', ');
  if (v === null || v === undefined) return '';
  var s = String(v).replace(/[\u0000-\u0008\u000B\u000C\u000E-\u001F]/g, '').trim();
  if (s.length > SETTINGS.MAX_FIELD_LENGTH) s = s.slice(0, SETTINGS.MAX_FIELD_LENGTH) + '…';
  return s;
}

/** Stops a visitor's text running as a spreadsheet formula (e.g. "=...") and keeps the 0 in 0300… numbers. */
function cell_(v) {
  var s = String(v === undefined || v === null ? '' : v);
  return /^([=+\-@]|0\d)/.test(s) ? "'" + s : s;
}

/** Pakistani or international number → digits for wa.me (e.g. 0300 1234567 → 923001234567). */
function waNumber_(v) {
  var d = String(v || '').replace(/\D/g, '');
  if (d.indexOf('00') === 0) d = d.slice(2);
  if (d.indexOf('0') === 0) d = '92' + d.slice(1);
  if (d.length === 10 && d.charAt(0) === '3') d = '92' + d;
  return (d.length >= 10 && d.length <= 15) ? d : '';
}

function isEmail_(v) { return /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(String(v || '')); }

function esc_(s) {
  return String(s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}

function json_(obj) {
  return ContentService.createTextOutput(JSON.stringify(obj)).setMimeType(ContentService.MimeType.JSON);
}
