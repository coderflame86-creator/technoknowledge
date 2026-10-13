# Go-live guide: moving thetechnoknowledge.com to the new website safely (Hostinger)

## Short answer: will Google "blacklist" pages, will users see 404s?
- **Google does not blacklist a website for being redesigned.** What can happen is: (1) old addresses that ranked on Google stop working and show 404, so those rankings are lost; (2) rankings wobble for 2–6 weeks while Google re-reads the new pages; (3) the only real "blacklist" risk is **security**: the old WordPress site had a hidden gambling spam link. If Google finds hacked/spam content it can show "This site may be hacked" or a red Safe Browsing warning. The new site is plain HTML with no WordPress, so that risk disappears **once the old WordPress files are removed**.
- **404s are managed with 301 redirects.** Every known old address (51 of them, including all old blog posts and the odd old posts with fashion titles) is already mapped to its new page in `frontend/.htaccess`. WordPress system addresses (`/wp-admin/`, `/feed/`, `/cart/`, `/tag/…`) return **410 Gone**, which tells Google to drop them quickly. Anything else unknown shows our friendly 404 page with a real 404 status.

## What is already prepared
| File | What it does |
|---|---|
| `frontend/.htaccess` | https + no-www, 301 redirects from old WordPress URLs, 410 for WordPress leftovers, custom 404 page, compression, browser caching, security headers |
| `docs/redirect-map.txt` | The list of old → new addresses (edit here, then rebuild `.htaccess` with `fontwork/mk_htaccess.py` or edit `.htaccess` by hand) |
| `frontend/sitemap.xml` + `frontend/robots.txt` | 36 pages for Google; rebuild with `node scripts/make-sitemap.mjs` after changes |
| `scripts/check-live.mjs` | Run after go-live: checks every page, every redirect, https/www and the 404 status |

## Step by step

### A. One week before
1. **Back up the old site** in Hostinger (Files + Database → Backups → download). Keep it for 3 months.
2. **Get the full list of old URLs that Google knows**: Google Search Console → Indexing → Pages → "Indexed" → Export; and Search Console → Performance → Pages (last 16 months) → Export. Compare with `docs/redirect-map.txt` and add any missing address (send the list to Claude).
3. In Search Console check **Security issues** and **Manual actions**. If either shows a problem, note it (fixing it is step C5).
4. Lower nothing on the domain/DNS: the domain, hosting and email stay the same.

### B. Go-live day (Hostinger hPanel → File Manager)
1. Create a folder `old-wordpress-backup` **outside** `public_html` (or download everything) and **move all old WordPress files out of `public_html`** — do not leave `wp-content`, `wp-config.php` etc. inside, or the hacked code stays reachable.
2. Upload the **contents** of `frontend/` into `public_html` (so `public_html/index.html`, `public_html/.htaccess`, `public_html/pages/…`). Make sure hidden files are shown so `.htaccess` is uploaded.
3. hPanel → Security → **SSL**: make sure the free SSL is active for `thetechnoknowledge.com` and `www.thetechnoknowledge.com`.
4. Forms: follow `backend/google-apps-script/SETUP-GUIDE.md` and paste the `/exec` URL into `frontend/js/api.js` (upload that file again).
5. Open in a private window: `http://thetechnoknowledge.com`, `https://www.thetechnoknowledge.com`, `/techno-camps/`, `/teacher-training-2/`, `/games-to-learn-python/`, `/abc-not-real` — they should land on the right https pages and the last one on the 404 page.
6. Run `node scripts/check-live.mjs` → it must end with "All good." Fix anything it lists.
7. Hostinger hPanel → Advanced → **Cache Manager**: purge cache after uploading.

### C. Same day: tell Google and Bing
1. **Google Search Console** (property `thetechnoknowledge.com`, Domain type): Sitemaps → remove old sitemaps (`sitemap_index.xml`, `wp-sitemap.xml`) → add `sitemap.xml`.
2. URL Inspection → **Request indexing** for: Home, Techno Camps, ITEC, Robotics & IoT, AITEP, the AI & Robotics curriculum guide.
3. **Bing Webmaster Tools**: import from Search Console (one click), submit `sitemap.xml`.
4. Google Business Profile: website link → `https://thetechnoknowledge.com/`.
5. If Search Console showed a Security issue or Manual action: after the new site is live, click **Request review** and write: "Website rebuilt as static HTML on [date]; the old WordPress installation that contained an injected spam link has been removed entirely; all passwords changed."
6. Check **Google Safe Browsing**: https://transparencyreport.google.com/safe-browsing/search?url=thetechnoknowledge.com — should say "No unsafe content found".

### D. First 6 weeks: watch and fix
| When | Where | What to do |
|---|---|---|
| Day 2, 7, 14, 30 | Search Console → Indexing → Pages → **Not found (404)** | For each old address listed, add a line to `docs/redirect-map.txt` (old → best new page) and to `.htaccess`, upload |
| Weekly | Search Console → Pages → "Soft 404", "Redirect error", "Blocked by robots.txt" | Should be 0 for the new pages |
| Weekly | Search Console → Performance | Clicks/impressions dip a little for 2–6 weeks, then recover. A big drop on one page = a missing redirect |
| Monthly | `node scripts/check-live.mjs` | Must end "All good." |
| Monthly | Search Console → Enhancements (Breadcrumbs, FAQ, Courses) | Fix any errors shown |

## Rules that keep rankings safe
- **Never change a published page address.** If you must, add a 301 redirect the same day.
- Keep `<link rel="canonical">`, titles and descriptions when editing pages.
- Don't put the site behind a "coming soon" page or password once live; don't add `noindex` (only 404 and the style guide have it).
- Don't delete a blog post — update it, or redirect it to the closest page.
- After any page change: `node scripts/sync-components.mjs` (if header/footer changed) and `node scripts/make-sitemap.mjs`, then upload.
- Turn on the HSTS line in `.htaccess` only after a few weeks of trouble-free https.
