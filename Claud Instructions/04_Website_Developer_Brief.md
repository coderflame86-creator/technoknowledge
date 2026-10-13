# 1. About this document

This document gives the web developer everything needed to rebuild **thetechnoknowledge.com**: what to fix, the new site structure, page-by-page specifications, the design system, SEO settings and a launch checklist.

**Platform:** stay on **WordPress** (Elementor is fine). Rebuild the pages cleanly rather than patching the current ones. Yoast SEO is already installed and should be kept.

**Design prototypes.** These HTML files are in the shared "TK profile" folder. Open them in Chrome to see the intended look and behaviour. They are references to rebuild from, not files to upload as they are.

| File | What it shows |
|---|---|
| Technoknowledge_Homepage.html | **Final homepage:** menu with dropdowns, hero product slider, program tabs, gallery, achievements, camps, testimonials, footer |
| AITEP_Landing_Page.html | AITEP program page |
| ITEC_Landing_Page.html | ITEC curriculum & teacher training page |
| Robotics_IoT_Landing_Page.html | Robotics & IoT page |
| Monitoring_Evaluation_Landing_Page.html | Monitoring & Evaluation page |
| Homepage_Header_OptionA.html | Approved header (now part of the homepage file) |
| TK_Website_Review_and_SEO_Plan.docx | Full review and the reasons behind these changes |

**Images in the prototypes** were cut from the company pamphlet and flyers, so they are low resolution. Request the **original photos** from Technoknowledge (see Section 11). Do not use the prototype images on the live site.

---

# 2. Fix first (before any redesign work)

| # | Task | Details |
|---|---|---|
| 1 | **Remove the hidden spam link and secure the site** | The homepage contains a hidden gambling link ("roobet bonus" → amincissement.ch). Remove it, then: update WordPress core, the theme and all plugins · delete unused or unknown plugins and themes · check *Users* for unknown admin accounts · change all admin, hosting, FTP and database passwords · run Wordfence or Sucuri and clean anything it finds · in Google Search Console, check **Security issues** and **Manual actions**. Search the theme files and database for the link text to find where it was injected |
| 2 | **Correct wrong facts site-wide** | Founded **January 2021** (not 2019) · **50,000+ students** · **5,000+ teachers** (not 20,000+) · **50+ schools**. Remove "hundreds of schools already using Technoknowledge LMS" |
| 3 | **Update camp prices** | **PKR 12,999 (group) / PKR 21,999 (1-to-1) per 24-hour course**. Camps run online in summer and winter breaks. Remove the old PKR 4,500 / 5,500 / 9,000 per-level prices and the PKR 11,999 form price |
| 4 | **Remove placeholder text** | "Lorem ipsum" and "European languages" on the current M&E page (/teacher-training-2/) |
| 5 | **Fix the broken link** | The footer "Monitoring and Evaluation" link returns 404 |
| 6 | **Remove shop leftovers** | Deactivate **WooCommerce** if nothing is sold online ("Your Cart", "Popular Products") |
| 7 | **Small fixes** | "Trainer the Trainer" typo · duplicate menu items (Books, Techno Camps) · footer year → 2026 |

---

# 3. New site structure

## 3.1 Main menu

**Home · For Schools ▾ · For Students ▾ · Impact ▾ · About ▾ · Blog**, plus a WhatsApp icon and a **Request a proposal** button (yellow, right side).

| Menu | Dropdown items |
|---|---|
| **For Schools** (wide two-column dropdown with icons and one-line descriptions) | ITEC Curriculum & Training · AI Curriculum & AITEP · Robotics & IoT · Monitoring & Evaluation · Teacher Training · Books & LMS. Bottom row of quick links: *ITEC Training · AITEP · Robotics & IoT Training* |
| **For Students** | Techno Camps · Scratch · Programming · Generative AI |
| **Impact** | Achievements · Student Projects · Partners & Projects |
| **About** | Our Story · Our Team · Regional Partners · Contact Us |
| **Blog** | (no dropdown) |

**On mobile** (below 1100px), the menu collapses to a menu button that opens a full-screen list. Each main item expands to show its sub-items, and "Request a proposal" sits at the bottom.

## 3.2 Pages to remove

- **School Programs** (/school-programs/): 301 redirect to /for-schools/
- **School Registration** (/school-registration/): 301 redirect to /request-a-proposal/. Also remove the inactive "Madrasa Reform" and "Non-Profit Schools" options

## 3.3 New URLs and 301 redirects

Use the free **Redirection** plugin, or Yoast Premium. Every old URL must redirect to its new one so existing Google rankings are kept.

| Page | New URL | Redirect from |
|---|---|---|
| Home | / | — |
| For Schools (overview) | /for-schools/ | /school-programs/, /services/ |
| ITEC Curriculum & Training | /itec-curriculum/ | /early-age-curriculum/ |
| AI Curriculum & AITEP | /aitep-ai-training-for-teachers/ | — (new) |
| Robotics & IoT | /robotics-iot-for-schools/ | — (new) |
| Teacher Training (hub) | /teacher-training/ | — (keep URL) |
| Monitoring & Evaluation | /monitoring-and-evaluation/ | /teacher-training-2/ |
| Books & LMS | /books/ | — (keep URL) |
| Techno Camps | /coding-camps-for-kids/ | /techno-camps/, /registration/ |
| Impact | /impact/ | /projects-and-collaborations/ |
| About | /about/ | — |
| Request a proposal | /request-a-proposal/ | /school-registration/ |
| Contact | /contact/ | — |
| Blog | /blog/ | — |

---

# 4. Design system

| Element | Specification |
|---|---|
| **Colours** | Navy **#13294B** (main background, headings) · Yellow **#FFC21A** (primary buttons, highlights; hover #FFD866) · Green **#2E8B57** (secondary buttons, camps) · Blue **#1E5FA8** (links) · Red **#D93A2B** (accents) · Light grey background **#F6F7FA** · Body text **#1B1F2A** · Muted text **#4A5263** · Borders **#E3E7EF** |
| **Logo colours** | Red #E5483A · Yellow #FFC21A · Green #3BAA6B (the three dots in the brackets) |
| **Fonts (Google Fonts)** | Headings: **Sora** 600–800 · Body: **Nunito Sans** 400–800 |
| **Type sizes (desktop / mobile)** | H1 60–62px / 36–38px · H2 38–40px / 28–30px · Body 16–18px · Small labels 13px, uppercase, letter-spacing 0.8px |
| **Buttons** | Height ≥48px, radius 10px, weight 800. Primary: yellow with navy text. Secondary: navy or green with white text. Outline: 2px white or navy border. Hover: lift 2px |
| **Cards** | White, radius 14–20px, 1px #E3E7EF border. Hover: lift 4–6px with a soft shadow |
| **Layout** | Max content width 1240px, 28px side padding (16–20px on mobile), section padding 96px (64px on mobile) |
| **Icons** | Simple line icons (2px stroke). No emoji |
| **Accessibility** | Touch targets ≥44px · text contrast ≥4.5:1 · real buttons and links (keyboard-usable) · alt text on every image · respect "reduced motion" (stop auto-sliding) |

---

# 5. Homepage specification

Build from **Technoknowledge_Homepage.html**. Sections in order:

| # | Section | Content and behaviour |
|---|---|---|
| 1 | **Sticky header** | Logo · menu with dropdowns (Section 3.1) · WhatsApp icon (wa.me/923458205013) · "Request a proposal" button. Stays fixed on scroll and gains a shadow |
| 2 | **Hero** | Label: "Coding · AI · Robotics · Grades 1–8" · **H1: "We create technology producers."** · Text: "Coding, AI and robotics education for schools and students across Pakistan: full-year curriculum, teacher training and hands-on camps." · Buttons: "For schools: Request a proposal" (yellow) and "For parents: Explore camps" (outline) |
| 3 | **Hero product slider** (right side) | 4 slides: **Robotics & IoT** ("Students build real robots") · **ITEC** ("From block coding to Python") · **AI & AITEP** ("AI skills for students and teachers") · **Teacher Training** ("We train your teachers to deliver it"). Each slide = real photo + white caption card + "Explore →" link. **Behaviour:** fade every 6s · slow zoom on the image · labelled progress bars below that can be clicked · pause on hover · swipe on mobile · no auto-slide if the user prefers reduced motion. **Headline and buttons must not slide.** Small yellow "STEM.org accredited" label on top |
| 4 | **Trust strip** | "Trusted by: Federal Directorate of Education · Sindh Education Foundation · Gilgit-Baltistan Education · Ministry of Federal Education · Zindagi Trust · DIL" (logos if permission is given) |
| 5 | **Program explorer** (tabs) | Tabs: ITEC Curriculum & Training · AI Curriculum & AITEP · Robotics & IoT · Monitoring & Evaluation. Each panel: large photo plus 1–2 small photos, title, one sentence, tool chips, "For students / For teachers" boxes and buttons. Clicking a "For Schools" menu item opens the matching tab |
| 6 | **Learning journey** | 4 steps: Digital Creator → Coder → AI Builder → Robotics & IoT Innovator (navy background, hover lift) |
| 7 | **Student project gallery** | Filter buttons: All / Robotics / IoT / In the classroom. Image grid with captions (project name and grade). Image zooms slightly on hover |
| 8 | **Teacher training cards** | ITEC Training (40 hrs: 24 in person + 16 online) · AITEP (20 hrs, free Starter session) · Robotics & IoT Training (40 hrs). Each card links to its page |
| 9 | **Achievements** | Counters that animate when scrolled into view: 50,000+ students · 5,000+ teachers · 50+ schools · 4 regions. Award cards: Best Women-led Startup (NIC Islamabad) · STEM.org accredited · National curriculum contributor (SNC CS, Grades 6–8) · 3rd place, Women Accelerator Cohort 1 *(confirm full name)*. Partner list or logos |
| 10 | **Techno Camps** | Green block: photo, "Techno Camps: online coding camps kids love", Scratch · Programming · Generative AI, PKR 12,999 group / PKR 21,999 1-to-1, "Register for the next camp" |
| 11 | **Testimonials** | Rotating quotes (7s), previous/next buttons and dots. Use real parent quotes plus at least one principal quote |
| 12 | **Final call to action** | Yellow block: "Ready to make your students technology producers?" with Request a proposal and WhatsApp buttons |
| 13 | **Footer** | Logo, one-line description, address, phone, WhatsApp, email, social icons · columns: For Schools, Teacher Training, For Students, Company (incl. **LMS Login**: lms.thetechnoknowledge.com) · © 2026 Technoknowledge (SMC-Pvt) Ltd. |

---

# 6. Program pages

All program pages share one layout: hero → content sections → pricing cards → FAQ → booking section (WhatsApp + phone + email + short form). **Build each from its prototype file.** Key content:

## 6.1 AITEP: AI-Driven Academic Transformation Program (/aitep-ai-training-for-teachers/)
- **H1:** "Less paperwork. More teaching. Smarter schools with AI."
- **Subline:** "AITEP trains your teachers to plan lessons, make tests and prepare materials with AI, in minutes, not hours."
- **Sections:** What your teachers will learn (6 cards) · Why schools choose AITEP · How it works (Free Starter → Full training → Ongoing support) · Pricing cards · FAQ · Booking ("See what AI can do for your teachers, free.")
- **Pricing cards:**
  - **AITEP Starter:** free, 3 hours online via Zoom
  - **Full AITEP:** **PKR 25,000–30,000 per teacher**, excl. provincial tax, 20 hours
- **Form option:** Free Starter session / Full AITEP proposal

## 6.2 ITEC: Information & Technology Enhanced Curriculum (/itec-curriculum/)
- **H1:** "Your students won't just use computers. They'll program them."
- **Sections:**
  - What students learn: computer fundamentals; logic & problem solving; coding (Scratch Jr, Scratch, HTML, Python); application development with MIT App Inventor
  - What the school gets: Bit-by-Bit books, LMS or both; ready-made lessons; video lectures in English; teacher, student and admin portals
  - Teacher training
  - Pricing
  - FAQ
- **Pricing cards:**
  - **ITEC Teacher Training:** **PKR 25,000 per teacher**, 40 hours (24 in person + 16 online on LMS)
  - **Curriculum:** custom quote
- **All content is in English.** Do not mention Urdu.

## 6.3 Robotics & IoT (/robotics-iot-for-schools/)
- **H1:** "Students don't just learn about robots. They build them."
- **Sections:**
  - Student projects by grade (Grade 6, 7 and 8 project lists; VEX virtual robotics for Grades 1–5)
  - What the school gets
  - Teacher training: 8-day plan, every teacher gets…, tools
  - Pricing
  - FAQ
- **Pricing cards:**
  - **Robotics & IoT Teacher Training:** **PKR 35,000 per teacher**, around 40 hours; hardware kits bought separately by the school
  - **Curriculum:** custom quote
- **Do not list the "Network Jammer" project.**

## 6.4 Monitoring & Evaluation (/monitoring-and-evaluation/)
- **H1:** "Measure it. Improve it. Prove it."
- **Sections:**
  - 6-step process: Pre-assessment → School visits → Interviews (teachers, students, admin) → Modular assessments → Post-assessment → Report & recommendations
  - What you receive: grade-wise progress, issues highlighted, recommended changes
  - Why principals choose M&E
  - Plans
  - FAQ
- **Plans:** Quarterly / Bi-annual / Annual, each "Custom quote"

## 6.5 Pages without a prototype (build in the same style)

| Page | Content |
|---|---|
| **Teacher Training hub** (/teacher-training/) | Short intro + three cards (ITEC Training, AITEP, Robotics & IoT Training) with hours, price per teacher and a link to each program page · what every teacher gets (LMS access, training pack, assessments, certificate) · booking section |
| **Techno Camps** (/coding-camps-for-kids/) | Hero for parents · best-selling camps (Scratch, Programming, Generative AI) and the other courses offered · ages · online, 24 hours per course · **PKR 12,999 group / PKR 21,999 1-to-1** · summer and winter dates · parent testimonials · registration form (child's name, age, parent name, WhatsApp, course, group or 1-to-1) |
| **Request a proposal** (/request-a-proposal/) | One short form used by every school page: name · role · school/institution · city · WhatsApp · interest (ITEC / AITEP / Robotics & IoT / Teacher training / M&E) · message (optional). Thank-you message + WhatsApp link |
| **Impact** (/impact/) | Counters · government projects (FDE, Sindh Education Foundation, GB Ministry of Education, SNC curriculum) · partner schools and NGOs · student project gallery · case studies (when available) |
| **About** (/about/) | Story since January 2021 · mission "Creating Technology Producers" · team (updated titles) · regional partners IdeoMetriX (Gilgit-Baltistan) and Max-Academia (Punjab) · awards |
| **Books & LMS** (/books/) | Bit-by-Bit series (Grades 1–8) with cover images · LMS features · link to LMS login |

---

# 7. Forms and tracking

- **One form plugin** for all forms (WPForms, Fluent Forms or Elementor Forms). Send to info@thetechnoknowledge.com, ideally with a WhatsApp or email notification to the sales person.
- **Spam protection:** add reCAPTCHA v3 or Cloudflare Turnstile to every form.
- **Thank-you message:** promise a reply within one working day.
- **Google Analytics 4:** track these as conversions: form submission (each form separately) · WhatsApp button click · phone click · email click.
- **Google Search Console:** verify the domain, submit the sitemap, and check Security issues and Manual actions.
- *(Optional, free)* **Microsoft Clarity** for heatmaps and session recordings.

---

# 8. SEO settings (Yoast)

## 8.1 Every page
- **Exactly one H1** per page, containing the page's main search term. The current homepage, Services, Digitized Curriculum and Projects pages have **no H1**.
- Set the **SEO title** and **meta description** in Yoast using the table below. Do not leave them blank, because blank fields produce broken auto-text such as a YouTube link.
- **Alt text on every image**, describing what it shows, e.g. "Grade 7 students testing a line-following robot".
- **Internal links:** program pages link to each other, blog posts link to program pages.
- **Images:** name image files descriptively (robotics-line-following-robot.webp, not IMG_1234.jpg).

## 8.2 Titles and meta descriptions

| Page | SEO title | Meta description |
|---|---|---|
| Home | Coding, AI & Robotics Education for Schools in Pakistan \| Technoknowledge | Full-year coding, AI and robotics curriculum, teacher training and coding camps for Grades 1–8. Serving schools across Pakistan since 2021. |
| ITEC | Coding Curriculum for Schools, Grades 1–8 \| ITEC \| Technoknowledge | A full-year computer and coding curriculum with books, LMS and trained teachers. Scratch, App Inventor, HTML and Python. Request a proposal. |
| Robotics & IoT | Robotics & IoT Program for Schools in Pakistan \| Technoknowledge | Arduino robotics curriculum, real student projects and hands-on teacher training for Grades 1–8. Get a proposal for your school. |
| AITEP | AI Training for Teachers in Pakistan \| AITEP \| Technoknowledge | Train your teachers to plan lessons, make tests and prepare materials with AI, in minutes, not hours. Book a free Starter session. |
| Teacher Training | Teacher Training: Coding, AI & Robotics \| Technoknowledge | Hands-on Train the Trainer programs for schools: ITEC coding, AITEP AI training, and Robotics & IoT. On campus and online. |
| M&E | School Monitoring & Evaluation Services \| Technoknowledge | Quarterly, bi-annual or annual M&E: assessments, school visits, interviews and grade-wise progress reports with recommendations. |
| Techno Camps | Coding Camps for Kids in Pakistan: Scratch, Python & AI \| Technoknowledge | Online summer and winter coding camps: Scratch, programming and Generative AI. Group or 1-to-1 classes. |
| Contact | Contact Technoknowledge \| Karachi, Pakistan | Call or WhatsApp +92 345 8205013, email info@thetechnoknowledge.com, or visit us in DHA Phase 6, Karachi. |

## 8.3 Structured data and sitemap
- **Yoast → Site representation:** Organization "Technoknowledge (SMC-Pvt) Ltd.", logo, and social profiles (sameAs).
- **FAQ schema:** add FAQ blocks to the program pages (Yoast FAQ block, or Rank Math if preferred).
- **Course schema:** add to the Techno Camps page (course name, provider, description).
- **Sitemap:** in Yoast, exclude Elementor floating buttons and ElementsKit content types from the sitemap. Keep only pages, posts and categories.

---

# 9. Performance

- **Images:** convert to **WebP**, keep hero images under about 200 KB and others under 120 KB, and lazy-load everything below the first screen.
- **Hero slider:** load only the first slide image immediately and lazy-load the other three.
- **Caching:** add a caching plugin (LiteSpeed Cache if the host uses LiteSpeed, otherwise WP Super Cache or W3 Total Cache).
- **Fonts:** load only the Google Font weights that are used.
- **Plugins:** remove unused ones (WooCommerce if unused, duplicate page builders, old slider plugins).
- **Targets:** test on **PageSpeed Insights**. Aim for a mobile performance score of 70+, Largest Contentful Paint under 2.5s and CLS under 0.1.

---

# 10. Content consistency

| Item | Correct value |
|---|---|
| Company name | Technoknowledge (SMC-Pvt) Ltd. · tagline **"Creating Technology Producers"** |
| Founded | January 2021 |
| Figures | 50,000+ students · 5,000+ teachers · 50+ schools |
| Product names | **ITEC** (Information & Technology Enhanced Curriculum) · **AITEP** (AI-Driven Academic Transformation Program) · **Robotics & IoT** · **Techno Camps** · **Bit-by-Bit** books |
| Training | ITEC Training: 40 hrs (24 in person + 16 online), PKR 25,000/teacher · AITEP: 20 hrs, PKR 25,000–30,000/teacher · Robotics & IoT: about 40 hrs, PKR 35,000/teacher + hardware · all excl. provincial tax |
| Camps | Online, summer & winter, 24 hrs/course, PKR 12,999 group / PKR 21,999 1-to-1 |
| Contact | +92 322 2055185 · WhatsApp +92 345 8205013 · info@thetechnoknowledge.com · 142 Khayaban-e-Shahbaz, Phase 6, DHA, Karachi |
| Social links | Facebook /TechnoKnowledge4 · YouTube @TechnoKnowledge4 · Instagram, X and LinkedIn: **confirm the correct handles with Technoknowledge** (the website currently uses @technoknowledg4, printed material uses @technoknowledge4). Add Instagram and LinkedIn to the footer |

---

# 11. Content Technoknowledge must provide

- [ ] **Logo files:** full-colour and white versions, SVG or PNG
- [ ] **Original high-resolution photos:**
  - students with robots and projects
  - classrooms and computer labs
  - teacher training sessions, including the group photo from the robotics training
  - AITEP sessions
  - camps
- [ ] **Short videos** of student projects (for YouTube and the gallery)
- [ ] **Principal testimonials:** 2–3 quotes with name, role and school (with permission)
- [ ] **Partner and client logos** you have permission to show
- [ ] Full name of the **Women Accelerator** award
- [ ] **Team photos and updated titles**
- [ ] **Next camp dates**
- [ ] **Case study data:** results from M&E or school outcomes

---

# 12. Launch checklist

- [ ] Spam link removed, site secured, Search Console shows no security issues
- [ ] All pages built, with exactly one H1, SEO title, meta description and alt text
- [ ] All 301 redirects working (test every old URL)
- [ ] Menu and dropdowns work on desktop, tablet and phone, and with the keyboard
- [ ] Slider, tabs, gallery filter, counters and testimonials work, and are paused under reduced motion
- [ ] Every form sends correctly, shows a thank-you message and is recorded as a GA4 conversion
- [ ] WhatsApp, phone and email links work on mobile
- [ ] No old facts left (search the site for "2019", "20,000", "11,999", "4,500", "Lorem")
- [ ] PageSpeed mobile score checked, images WebP and lazy-loaded
- [ ] Sitemap submitted in Search Console, and Google Business Profile links to the site
- [ ] Final review and approval by Technoknowledge
