# backend/

The website forms are handled by a **Google Apps Script web app**, owned by
**technoknowledge19@gmail.com**. There is no server to rent or maintain.

| File | What it is |
|---|---|
| `google-apps-script/Code.gs` | The script. Paste it into Apps Script from the Google Sheet. |
| `google-apps-script/SETUP-GUIDE.md` | Step-by-step setup (about 15 minutes, no coding). |

## What happens when someone sends a form
1. `frontend/js/forms.js` checks the fields and stops bots (hidden honeypot field).
2. `frontend/js/api.js` sends the form to the web app URL (`ENDPOINT`).
3. `Code.gs`:
   - rejects spam (honeypot, sent too fast, more than 5 sends per number in 10 minutes)
   - saves a row in the right tab of the Google Sheet (Camp Registrations, Proposal Requests, AITEP Starter Bookings, Contact Messages)
   - emails technoknowledge19@gmail.com, with a **Reply on WhatsApp** button to the visitor's number
   - answers `{ "ok": true }`
4. The visitor sees "Thank you". If sending fails, they are asked to WhatsApp +92 345 8205013.

The field names are listed in `../docs/api-contract.md`.

## Secrets
None. The web app runs as the technoknowledge19@gmail.com account, so no passwords, keys or tokens are stored in the
script or the website. The web app URL is not a secret.

## Later (optional)
If the team outgrows Google Sheets (for example, a CRM), keep the same field names and the
`{ ok: true }` answer, and only change `ENDPOINT` in `frontend/js/api.js`.
The `src/` folders and `.env.example` are kept for that future server option and are not used now.
