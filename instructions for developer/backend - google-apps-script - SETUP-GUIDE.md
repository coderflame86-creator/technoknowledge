# Connect the website forms to Google Sheets and technoknowledge19@gmail.com

Time needed: about 15 minutes. You do this **once**. No coding, no paid service.

When it is done, every form on the website will:

1. save a new row in one Google Sheet, owned by **technoknowledge19@gmail.com**, with one tab per form:
   - **Camp Registrations** (Techno Camps form)
   - **Proposal Requests** (Request a proposal, ITEC, LMS, Robotics & IoT, M&E forms)
   - **AITEP Starter Bookings** (AITEP form)
   - **Contact Messages** (Contact form)
2. email the details to **technoknowledge19@gmail.com**, with two buttons:
   - **Reply on WhatsApp**, which opens a chat with the person's own number and a greeting already typed
   - **Open in Google Sheet**, which jumps to that row
3. show the visitor "Thank you". If anything fails, the visitor is asked to WhatsApp **+92 345 8205013**.

Every WhatsApp button on the website already opens **+92 345 8205013** (`wa.me/923458205013`).

---

## Before you start

Sign in to Google as **technoknowledge19@gmail.com** (it is a normal Gmail account, so nothing else is needed).

> Do every step below while signed in as **technoknowledge19@gmail.com**. If you are signed in to other Google accounts in the same browser, use a private/incognito window so the Sheet and script are not created under a personal account.

---

## Step 1: Create the Google Sheet

1. Go to **sheets.google.com** and click **Blank spreadsheet**.
2. Name it **Technoknowledge Website Forms** (click "Untitled spreadsheet" at the top left).

## Step 2: Add the script

1. In the Sheet, click **Extensions → Apps Script**. A new tab opens.
2. Click "Untitled project" at the top and name it **Website Forms**.
3. Delete everything in the `Code.gs` box.
4. Open the file `backend/google-apps-script/Code.gs` from the website folder, copy **all** of it, and paste it into the box.
5. Click the **Save** icon (or press Ctrl + S).

## Step 3: Run setup once

1. In the toolbar, choose **setup** from the function list (next to "Debug"), then click **Run**.
2. Google asks for permission. Click **Review permissions**, choose **technoknowledge19@gmail.com**, then:
   - If you see "Google hasn't verified this app", click **Advanced → Go to Website Forms (unsafe)**. This warning appears for every private script. It is your own script, running in your own account.
   - Click **Allow**. The script asks to manage your spreadsheets and send email as you. It needs both: one to save rows, one to send the email.
3. Go back to the Sheet. You should now see four tabs, each with a navy header row and a **Status** column with a drop-down (New, Contacted, Demo / meeting booked, Proposal sent, Enrolled / signed, Not interested).

## Step 4: Send a test email

1. Back in Apps Script, choose **sendTestSubmission** from the function list and click **Run**.
2. Check:
   - the **Contact Messages** tab has a row named "Test (please delete)"
   - the technoknowledge19@gmail.com inbox has an email "New website message from Test (please delete)"
3. If the email is in **Spam**, open it and click **Not spam**. (If emails keep going to spam in the Hostinger mailbox, ask your Hostinger/DNS admin to add `include:_spf.google.com` to the domain's SPF record.)
4. Delete the test row.

## Step 5: Publish it as a web app

1. Click **Deploy → New deployment**.
2. Click the gear icon next to "Select type" and choose **Web app**.
3. Fill in:
   - Description: `Website forms v1`
   - Execute as: **Me (technoknowledge19@gmail.com)**
   - Who has access: **Anyone**

   "Anyone" only means the website can send forms to it. Nobody can see or open the Sheet. It stays private to technoknowledge19@gmail.com.
4. Click **Deploy**, then copy the **Web app URL**. It looks like
   `https://script.google.com/macros/s/AKfy…long code…/exec`

## Step 6: Put the URL into the website

1. Open `frontend/js/api.js` in the website folder (Notepad is fine).
2. Find this line:
   ```js
   var ENDPOINT = '';
   ```
3. Paste the URL between the quotes:
   ```js
   var ENDPOINT = 'https://script.google.com/macros/s/AKfy…/exec';
   ```
4. Save and upload `api.js` to the website again.

This URL is not a password. It is safe for it to be in the website code.

## Step 7: Test every form on the live site

Send one test from each page, then check that a row appears in the right tab and an email arrives in technoknowledge19@gmail.com:

| Page | Tab |
|---|---|
| Techno Camps | Camp Registrations |
| Request a proposal | Proposal Requests |
| ITEC curriculum, LMS, Robotics & IoT, Monitoring & Evaluation | Proposal Requests (the **Program** column says which page) |
| AITEP | AITEP Starter Bookings |
| Contact | Contact Messages |

Wait a few seconds before you press Send on each test. Forms sent within 2.5 seconds of the page opening are treated as robots and silently ignored.

---

## Daily use

- Every new request arrives in technoknowledge19@gmail.com with **Reply on WhatsApp** and **Open in Google Sheet** buttons.
- Pressing **Reply** in the email replies to the visitor's email address, when they gave one.
- In the Sheet, change **Status** after each reply, so the team can see what is still waiting.
- To let other staff see the Sheet: **Share** → add their email as **Editor** or **Viewer**. Do not choose "Anyone with the link".
- Email limit: Google allows about 100 emails a day from a free account (1,500 with Google Workspace). This is far above the expected number of website forms.

## Changing things later

| You want to… | Do this |
|---|---|
| Send a short "we got your request" email to visitors who give an email | In `Code.gs` set `AUTO_REPLY: true`, then do **Deploy → Manage deployments → Edit (pencil) → Version: New version → Deploy** |
| Send notifications to another address as well | Change `NOTIFY_EMAIL` to, e.g. `'technoknowledge19@gmail.com, info@thetechnoknowledge.com'`, then deploy a new version as above |
| Change the Status options | Edit `STATUS_OPTIONS`, run **setup** again |

After any change to `Code.gs`, always use **Manage deployments → Edit → New version**. Do not create a "New deployment", because that makes a new URL and the website would still use the old one.

## If something goes wrong

| Problem | Fix |
|---|---|
| Website says "Preview: this form is not connected yet" | `ENDPOINT` in `api.js` is still empty, or the old `api.js` is cached. Upload it again and refresh the page with Ctrl + F5. |
| Website says "Sorry, your form could not be sent" | Open the web app URL in a browser. It should show `{"ok":true,"service":"Technoknowledge website forms"}`. If it asks you to sign in, "Who has access" is not set to **Anyone**. Fix it in Manage deployments. |
| Row saved but no email | In Apps Script, open **Executions** (left menu) to see the error. Usually permission was not given: run **setup** again and click Allow. |
| Email arrives but no row | Someone renamed or deleted a tab. Run **setup** again to recreate it. |
| "We already have your request" | The same WhatsApp number sent 5 forms in 10 minutes. Wait 10 minutes. |
