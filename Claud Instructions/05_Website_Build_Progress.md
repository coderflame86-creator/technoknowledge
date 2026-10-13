# Technoknowledge website: build progress

Claude is building the whole site (front end + form backend). Project folder on Romana's PC:
`D:\Technoknowledge(SMC-PVT)\Office Data\TK profile\technoknowledge-website\`
Single-file page previews: `TK profile\03 Previews\` (one per built page, plus Home, Style Guide, Blog post, 404; they link to each other). Linked multi-page preview: private artifact "Technoknowledge Website".

**Sync status (6 Oct 2026, full check):** every file in the PC folder was compared with the build (same files, same sizes); `sitemap.xml` regenerated (lastmod 6 Oct); all 17 previews rebuilt; navigation test: 38 pages, 0 problems. The artifact is at version 114 and matches the build. Go-live kit ready (`frontend/.htaccess`, `docs/redirect-map.txt`, `docs/GO-LIVE-GUIDE.md`, `scripts/make-sitemap.mjs`, `scripts/check-live.mjs`) — see `07_Go_Live_Guide.md`. Form emails now go to technoknowledge19@gmail.com. Only on the PC (not part of the build, left untouched): `instructions for developer\*.docx` (copies sent to the owner in chat on 6 Oct) and empty `.gitkeep` files.

## Decisions
- **Blog: every question ends with "?" (owner request, 6 Oct 2026).** 6 post titles reworded as proper questions (e.g. "A Guide for Principals: How Can Schools Start Teaching Coding, AI and Robotics?", "What Do Children Build in a Techno Camp?", "Why Is Coding a Good Investment for Your Child?", "How Can Teachers Use AI to Plan Lessons, Make Tests and Save Time?", "How to Teach Coding to Kids?", "How to Do Coding on a Laptop for Kids?") and 21 question headings inside posts. Page addresses (slugs) unchanged, so no redirects needed. Changed in `blog/posts/*.md`, then rebuilt (build.py → sync-components → seo2.py → make-sitemap). Rule for new posts: question titles/headings end with "?" — put any label first ("A Guide for Principals: How…?", "Understanding the Basics: What Is Coding?").
- **Techno Camps "From sign-up to first project" steps float (owner request, 6 Oct 2026).** `ol.steps--float`: cards float gently out of step; each card has a floating icon (book, clipboard, WhatsApp, rocket; `.step__icon`); numbers light up 1 → 4 in turn; on desktop a moving dashed path with a yellow dot links the steps; hover gives a white card with a yellow edge. CSS at the end of `css/pages/techno-camps.css`; reduced motion turns it off.
- **Techno Camps price cards animated (owner request, 6 Oct 2026).** Hover/focus: card lifts with a coloured top edge (1-to-1 yellow, group navy, school green). 1-to-1 card glows softly and its yellow button has a light sweep. Ticks pop in one by one and the price slides up when cards scroll into view. School card: dashed border pulses green, "For schools" tag has a pulsing dot. CSS only, end of `css/pages/techno-camps.css`; reduced motion turns the animations off.
- **Techno Camps: "Techno Camp at your school" quote option (owner request, 6 Oct 2026).** Third card in Step 2 (formats), H2 now "1-to-1, group or a camp at your school". Tag "For schools"; points: during term, summer or winter breaks · in person in Karachi, or online · courses and dates planned with you; "Custom quote per school"; button "Request a quote" → `request-proposal.html?interest=camp` (ticks the new option and opens at the form). Cards are 3 across on desktop; on tablets the school card spans the full width.
- **Techno Camps "why parents choose" strip animated (owner request, 6 Oct 2026).** The four cards (Live online classes, 1-to-1 or group, No experience needed, Outlines on request) slide in one after another; then a highlight moves from card to card every 2.4 s (light-yellow card, yellow icon that pops, yellow progress line). Hover/focus highlights a card and pauses. CSS `.camps-features__list li.is-hi` in `css/pages/techno-camps.css`; rotation script at the end of `js/pages/techno-camps.js`. Reduced motion: no rotation.
- **Techno Camps: "Register now" and "Find a course by age" visible on page load (owner request, 6 Oct 2026).** The two hero buttons now sit directly under the H1 (before the intro paragraph and prices), and the button gap is 24px. Measured: buttons end at ~578px on 1280–1920px wide screens and ~520–580px on phones, so they show without scrolling down to 640px-tall screens. Footer "Register Now" now also links to `techno-camps.html#register` (`components/footer.html`, synced to all 38 pages).
- **Menu link to the page you are already on (owner report "Contact us navigation is not working", 6 Oct 2026).** On the Contact page, About › Contact Us reloaded the same page, so nothing seemed to happen. Now (`js/components/header.js`): a header/drawer link with `aria-current="page"` and no `#` does not reload — it closes the menu and glides to the section marked `data-menu-target` (Contact: the message form `#message-form`) or else to the top. The current page is highlighted in simple dropdowns with a yellow-soft background and a "You're here" tag (`css/layout.css`; mobile: yellow-soft background). Links to other pages are unchanged. `fontwork/navtest.py` now expects this same-page behaviour.
- **Menu sub-items open their own section (owner report "navigation is not working properly", 6 Oct 2026).** In `frontend/components/header.html` (synced to all 38 pages): About › Our Story → `about.html#story`, Our Team → `#team`, Regional Partners → `#partners`; Contact Us → `contact.html`. Also fixed the same way: For Students › Register Now (menu card + mobile) → `techno-camps.html#register`, See courses → `techno-camps.html#courses`; AITEP promo "Book a session" → `aitep.html#booking`. Tested on desktop (section lands ~96px from top under the header) and mobile (~88px); previews link `About_Preview.html#story` etc.
- **New photo for Zuha Shan (owner, 6 Oct 2026).** Source `TK profile\tk pictures\zuha shan.jpeg` (1500×1500 colour, light-grey background) → cropped to 4:5, black-and-white like the other team photos, `assets/images/team/zuha-shan.webp` (480×600, ~25 KB). Used only on About › Team.
- **All website form data goes to technoknowledge19@gmail.com (owner, 6 Oct 2026).** `backend/google-apps-script/Code.gs` `NOTIFY_EMAIL: 'technoknowledge19@gmail.com'`; the Google Sheet and Apps Script are created and run while signed in as technoknowledge19@gmail.com (a normal Gmail account, so the old "create a Google account for info@" step is gone from `SETUP-GUIDE.md`). Also updated: `backend/README.md`, `backend/.env.example` (`MAIL_TO`), `docs/api-contract.md`, `docs/deployment.md`, the comment in `frontend/js/api.js`, and the Privacy Policy sentence about where form answers are sent (source `fontwork/pp_body.md`). **The public contact email shown on the website stays info@thetechnoknowledge.com** (header, footer, Contact, Request a Proposal, Privacy contact lines, schema) — only form delivery changed. To also copy info@: set `NOTIFY_EMAIL` to `'technoknowledge19@gmail.com, info@thetechnoknowledge.com'` and redeploy.
- **Go-live kit for Hostinger (owner request, 6 Oct 2026).** `frontend/.htaccess`: forces https + no-www; 301 redirects for 51 old WordPress addresses (all 33 old page slugs incl. /techno-camps/, /teacher-training-2/, /early-age-curriculum/, /school-registration/, /projects-and-collaborations/, plus 18 old blog posts from `docs/blog-redirects.txt`); old WP/Yoast sitemaps → `/sitemap.xml`; `/?p=` links → home; WordPress system/shop/archive URLs (`/wp-admin/`, `/feed/`, `/cart/`, `/tag/…`, `/category/…`) → **410 Gone**; `ErrorDocument 404 /404.html`; gzip compression, browser caching, security headers; HSTS line commented out until https is proven. `/index.html` is NOT redirected (canonical tag handles it). Old→new list lives in `docs/redirect-map.txt`; `.htaccess` is generated by `fontwork/mk_htaccess.py`. Rules checked by simulation (no Apache in the sandbox) — none catches a real site file. `scripts/check-live.mjs` (Node 18+) verifies the live site after launch; `scripts/make-sitemap.mjs` rebuilds `sitemap.xml`. **Key risk explained to owner:** Google does not blacklist redesigns; the real risks are lost rankings from missing redirects and the old hacked WordPress spam link — move ALL WordPress files out of `public_html` at launch and request a Search Console review if a security issue is listed.
- **Three older blog posts refreshed to 2026 (owner request, 5 Oct 2026).** Scratch games, computer science for kids and Python games posts: every "2025" → 2026; original publish dates kept; `updated: 2026-10-05` front-matter shows "Updated 5 October 2026" and sets `dateModified` (`blog/build.py`, `@@DATESHOWN@@` in `blog/templates/post.html`). After any blog rebuild run `node scripts/sync-components.mjs`, then `python3 fontwork/seo2.py`, then `node scripts/make-sitemap.mjs`. The Python post includes a few non-Python logic games — could be swapped in a future content refresh.
- **Techno Camps: kids trained across the globe (owner request, 5 Oct 2026).** H1 keyword line: "Online coding & AI bootcamps for kids in Pakistan and across the world". Hero lead: "About 10,000 kids from Pakistan and across the globe have trained with us online." FAQ "Can children outside Pakistan join?" with schema. Prices stay in PKR.
- **SEO pass 2 (owner request, 5 Oct 2026).** Shorter keyword-first titles, blog posts end "| Technoknowledge", shorter descriptions, BreadcrumbList schema on 11 more pages, Course schema (ITEC, Robotics, AITEP), Service schema (M&E, LMS), WebSite schema, width/height on 90 images, privacy page social tags. Script `fontwork/seo2.py`. Details in `06_SEO_Ranking_Plan.md`.
- **LMS hero: animated log-in → courses (owner request, 5 Oct 2026).** 11 s loop: log-in card, "student.grade6" typed, password dots, Log in → spinner, address bar `/login` → `/my/courses`, the real Grade 1–8 courses screenshot fades in. CSS `.lms-*` in `lms.css`. The username is a made-up example.
- **One STEM.org pill style everywhere (owner, 5 Oct 2026).** "STEM.org Accredited Curriculum". Home `.hero__trust`; ITEC, Robotics & IoT, AITEP and Books `<p class="stem-pill">` above the hero picture (rule in `components.css`). `.prog-hero__badge` remains only for the About award badge.
- **Home hero: animated robotics collage + STEM.org badge as the top highlight (owner request, 5 Oct 2026).** 5-tile collage that pops in, drifts and swaps projects; images in `assets/images/home/collage/`. Badge above the collage on wide screens, first in the hero on phones. Script `fontwork/homecollage.py`.
- **About hero: animated pathway slide first (owner request, 5 Oct 2026).** Block coding → Robotics & IoT → AI → Product development → "Creating Technology Producers"; slide 2 SEF photo. Script `fontwork/aboutscene.py`.
- **Impact sub-menu opens the right section (owner request, 5 Oct 2026).** `impact.html#achievements`, `#projects`, `#partners`; `main.js` remembers the section (`tk-section`) for viewers that drop the `#`.
- **STEM.org accreditation covers ITEC, Robotics & IoT and AITEP (owner-confirmed, 5 Oct 2026).** Not M&E (a service).
- **Books hero: the Grade 8 book opens and flips its pages (owner request, 5 Oct 2026).** `.bk3d` in `books.css`.
- **M&E hero: animated 4-chart sample report (owner request, 5 Oct 2026).** Labelled **"Sample data"**. Script `fontwork/mnescene.py`. **Replace with real (anonymised) figures when available.**
- **AITEP hero: animated "prompt → AI output" (owner request, 5 Oct 2026).** Script `fontwork/aiscene.py`.
- **Robotics & IoT hero: animated "code a robot" (owner request, 5 Oct 2026).** Script `fontwork/robotscene.py`.
- **Robotics & IoT: animated Grade 6/7/8 project cards (owner request, 5 Oct 2026).**
- **ITEC hero: animated block-coding slide (owner request, 5 Oct 2026).** `slider.js` supports `data-ms` per slide. Script `fontwork/codescene.py`.
- **Robotics & IoT › Grade 7: "Automatic Parking & Barricade System" (owner request, 5 Oct 2026).** 4th of 5 Grade 7 projects; added to `data/programs.json`.
- **Blog hero code card types itself (owner request, 5 Oct 2026).** `js/components/typer.js`.
- **One logo on every page (owner, 5 Oct 2026).** `assets/images/logos/tk-logo.svg`.
- **"Request a Demo" opens at the form (5 Oct 2026).**
- **Navigation in the Claude artifact viewer (5 Oct 2026).** `main.js` re-applies top/section scroll until the visitor interacts.
- **Navigation check.** `fontwork/navtest.py` on all 38 pages: 0 problems. Previews built with `fontwork/mkpreviews_all.py`.
- **SEO update (5 Oct 2026).** See `06_SEO_Ranking_Plan.md`. "First … company in Pakistan" claim NOT used. Scripts `fontwork/seo_apply.py`, `fontwork/seo_faq.py`, `fontwork/seo2.py`. The HTML pages are the source of truth.
- Static HTML/CSS/vanilla JS, no build step. Shared header/footer via `node scripts/sync-components.mjs`.
- Navy header, yellow CTA; buttons 10px radius. Hero headlines: no trailing full stops; section H2s end with "." and FAQ heading "What principals ask us?".
- **Office address: "Phase 6, DHA, Karachi, Pakistan"** everywhere.
- **Logo colours:** TECHNO #279FDA, KNOWLEDGE #317ABD, braces #FFD339, outer circles #EF5A3C, middle circle #279FDA. Site colour variables unchanged.
- **Regions = 5: Federal, Punjab, Sindh, Khyber Pakhtunkhwa, Gilgit-Baltistan.** About › "four regions" sentence left unchanged — **owner to confirm.**
- **Menu links open pages at the top**, except these section links: Request a Demo; Impact › Achievements / Projects / Partners; About › Our Story / Our Team / Regional Partners; For Students › Register Now / See courses; AITEP › Book a session. A link to the page you are on goes to that page's `data-menu-target` section (Contact → message form) or the top, without reloading.
- Forms: `backend/google-apps-script/Code.gs` + `SETUP-GUIDE.md`, run under **technoknowledge19@gmail.com**. **Owner action:** deploy and paste the `/exec` URL into `frontend/js/api.js` ENDPOINT.
- Public contact: info@thetechnoknowledge.com · WhatsApp +92 345 8205013 (`wa.me/923458205013`) · office hours Monday to Saturday, 9:00 am – 5:00 pm. Social: Facebook @TechnoKnowledge4, Instagram @technoknowledg4, X @technoknowledg4, YouTube @TechnoKnowledge4, LinkedIn /company/thetechnoknowledge.
- Photos: parental/school permission confirmed. No children's names visible. Team photos are black-and-white 480×600 WebP in `assets/images/team/`.
- Request a Proposal: 9 interest checkboxes (9th: "Techno Camp at our school", key `camp`); `?interest=…` pre-ticks; `demo` and `camp` open the page at the form.
- Team (owner-confirmed 4 Oct 2026): Romana Rafi (Founding member & CTO), Tanvir Maqsood (Founding member & COO · South Lead), Mohsin Amir (Creative Director & Master Trainer · North Lead), Dr. Rafi us Shan (Curriculum Lead & Advisor), Hira Gul (Curriculum Specialist · North Lead), Syed Ali Waqar (Financial Advisor), Zuha Shan (Scratch & Canva Trainer).
- Impact: 6 project cards and a wall of 22 client logos (Government 4 incl. Sindh Education Foundation).
- Blog: list page + 22 posts. Markdown in `blog/posts/`, builder `blog/build.py`.
- Privacy policy: **owner to confirm** 2-year retention, partner sharing only with consent, one-working-day reply.
- 404 page: `/404.html` (set in `.htaccess`).

## Steps
1. ✅ Design base, logo, header, footer, style guide
2. ✅ Home page
3. ✅ Techno Camps
4. ✅ School program pages: ITEC, AI & AITEP, Robotics & IoT, Monitoring & Evaluation
5. ✅ Request a Proposal + Contact
6. ✅ Books · LMS · About · Impact · Blog · Privacy policy · 404
7. ✅ Form backend — built; owner deploys per `SETUP-GUIDE.md` (as technoknowledge19@gmail.com)
8. Quality pass — SEO passes 1 and 2 and navigation tests done
9. 🟡 Go-live — kit ready. Remaining at launch: upload, move WordPress out, SSL, forms, Search Console/Bing, GA4 + privacy-policy update.

## Files the owner should delete manually
- `TK profile\tk-partners.zip` — optional.
- `technoknowledge-website\frontend\assets\images\logos\tk-logo-yellow-home.webp` and `tk-logo-home.svg` (no longer used) — optional.

## Open items
- **Before launch:** export the list of old URLs Google knows (Search Console → Pages → Indexed, and Performance → Pages) and send it to Claude so any address not yet in `docs/redirect-map.txt` gets a redirect.
- Owner to confirm the About, Impact and For Students sub-menus and Request a Demo.
- Optional: name the countries camp students joined from (e.g. UAE, UK) for Techno Camps.
- Review the new blog post "Pakistan's AI & Robotics Curriculum (Grades 1–12): A Guide for Schools".
- Off-site SEO (owner/team): Google Business Profile, partner backlinks, listicle outreach, press pitch — see 06_SEO_Ranking_Plan.
- Send the logo with the "Empowering young minds" tagline, if wanted.
- Deploy the forms script (as technoknowledge19@gmail.com) and paste the `/exec` URL into api.js.
- Confirm About › "four regions" → five.
- Principal testimonial.
- Partner logos for IdeoMetriX and Max-Academia.
- Optional: higher-resolution SEF logo.
- Confirm none of the 22 client organisations objects to their logo being shown.
- Whether to add deck-only clients like APSACS.
- Case study data (M&E results) — would also replace the "Sample data" numbers in the M&E hero charts.
- Short profile points for Syed Ali Waqar and Zuha Shan.
- Review the 3 earlier new blog posts.
- Confirm the 3 privacy-policy choices (ideally a lawyer review).
- Camp dates.
- Official STEM.org "accredited" seal.
- AI curriculum module list.
- VEX (Grades 1–5) specifics.
- Whether robotics project sheet images are AI renders.
- A real (anonymised) sample M&E report page.
- Confirm the Zindagi Trust photo may be shown publicly.
- Higher-resolution original of the t2 street-light photo.
- What the LMS school-admin view can show.
- More group photos for the home and Impact slideshows if wanted.

## Frontend QA audit (7 Oct 2026)
Checked every page at desktop 1440px, mobile 390px and small-phone 360px: console and page errors, failed requests, broken images, horizontal scroll and content spilling out of its box.
- **No console errors, failed requests or broken images** on any page.
- **Fixed: horizontal scroll at 360px** on impact.html ("See all robotics projects by grade") and itec-curriculum.html ("Request a subscription quote"). Long button labels could not wrap. `css/components.css` now lets buttons wrap on screens under 480px.
- **Fixed: "50,000+" counter touching the card edge** on impact.html on phones. `css/pages/impact.css` now uses smaller counter text and padding under 480px.
- Checked and fine (intended): hidden spam-trap field on forms, hidden blog heading for screen readers, and the clipped shine/pill/stat-circle effects.
- Result: every page is exactly 360px wide at 360px. No new sections or features were added and no colours changed. Delivered to the PC, the 03 Previews folder and artifact v115.
