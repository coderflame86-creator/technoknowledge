# Deployment guide

## Option A (chosen): static website + Google Apps Script for forms
1. Upload `frontend/` to the web host (it is the website root).
2. Forms: follow `backend/google-apps-script/SETUP-GUIDE.md` (Google Sheet + email to technoknowledge19@gmail.com). No server needed.
3. Enable HTTPS (free with the host's SSL).

## Option B: WordPress
Rebuild each page in Elementor using `frontend/` as the visual reference, and send forms to
the same endpoints (or use a form plugin that emails technoknowledge19@gmail.com).

## Before going live (checklist)
- [ ] Real photos added, compressed (WebP)
- [ ] Forms connected: `backend/google-apps-script/SETUP-GUIDE.md` done, `ENDPOINT` set in `frontend/js/api.js`
- [ ] Every form tested on the live site: row in the Google Sheet + email in technoknowledge19@gmail.com
- [ ] GA4 installed; form submits and WhatsApp clicks tracked as conversions
- [ ] Each page has one H1, a unique title and meta description
- [x] `sitemap.xml` (36 pages) and `robots.txt` created in `frontend/` (5 Oct 2026). At launch: verify the domain in Google Search Console and Bing Webmaster Tools, submit `https://thetechnoknowledge.com/sitemap.xml`, and request indexing for Home, Techno Camps, ITEC, Robotics & IoT, AITEP and the AI & Robotics curriculum guide. Rebuild it after page changes with `node scripts/make-sitemap.mjs`.
- [x] 301 redirects from old URLs prepared in `frontend/.htaccess` (51 old addresses, list in `docs/redirect-map.txt`); WordPress leftovers return 410. **Follow `docs/GO-LIVE-GUIDE.md` on launch day** and run `node scripts/check-live.mjs`.
- [ ] Old spam link removed and old site secured before switching
- [ ] Mobile check on a real phone
