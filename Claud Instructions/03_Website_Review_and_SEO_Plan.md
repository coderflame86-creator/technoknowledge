# Summary

Your plan is right: remove **School Programs** and **School Registration**, and add **AITEP, ITEC Training and Robotics & IoT** under Train the Trainer. On its own, though, it won't fix the biggest problems. This report adds four things:

1. **Fix the issues that are hurting trust and Google right now.** The hidden gambling spam link is still on your homepage, wrong facts are published, and there is placeholder text.
2. **Reorganise the menu around who is buying:** Schools, Students & Parents, and Teachers.
3. **Fix basic on-page SEO.** Missing headings, broken page descriptions and generic page titles mean Google doesn't know what each page is about.
4. **Build visibility outside your website:** a Google Business Profile, links from partners and press, and useful content.

**Where you stand today on Google.** A search for "Technoknowledge" finds you. Searches for what schools and parents actually type do not show Technoknowledge on the first page, for example:

- "coding robotics curriculum for schools Karachi"
- "AI training for teachers Pakistan"
- "coding classes for kids Karachi"

Those results go to competitors, foreign platforms and news sites.

*Website checked on 1 October 2026. Search results come from a single check and vary by location and device.*

---

# 1. Fix immediately (0–30 days)

| # | Issue found | Why it matters | What to do |
|---|---|---|---|
| 1 | **Hidden spam link "roobet bonus" (gambling) is still on the homepage** | Google can penalise or flag sites with injected spam links. It usually means the site was hacked | Remove the link. Update WordPress, the theme and all plugins. Delete unused plugins and unknown admin users. Change all passwords. Run a security scan (Wordfence or Sucuri, both have free versions). In Google Search Console, check "Security issues" and "Manual actions" |
| 2 | **Wrong facts:** "founded in 2019" (About, School Programs); "20,000+ teachers"; LMS page says "hundreds of schools" use the LMS | Principals and partners spot inconsistencies, and they damage credibility | Change to **January 2021, 50,000+ students, 5,000+ teachers, 50+ schools** |
| 3 | **Outdated camp prices:** PKR 4,500–9,000 per level and PKR 11,999 per course | Parents see two different prices, neither of them current | Show **PKR 12,999 (group) / PKR 21,999 (1-to-1) per 24-hour course**, and note that camps run in summer and winter |
| 4 | **"Lorem ipsum" placeholder text** and "European languages" template text on the Monitoring & Evaluation page | Looks unfinished | Replace with the new M&E page |
| 5 | **Broken footer link:** "Monitoring and Evaluation" goes to a 404 page | A broken experience for visitors and for Google | Fix the link (see the URL changes in Section 2) |
| 6 | **Leftover shop features:** "Your Cart", "Popular Products" and WooCommerce rules | Confusing, and adds unnecessary weight to the site | If you don't sell online, deactivate WooCommerce |
| 7 | **Typos and duplicates:** "Trainer the Trainer"; Books and Techno Camps appear twice in the menu | Looks careless | Fix during the menu rebuild |
| 8 | **Copyright shows 2025.** Newest blog post is from June 2025 | Makes the site look inactive | Update the footer, and restart the blog (Section 5) |

---

# 2. New site structure and menu

## Your planned changes, and what to add

| Your plan | My view |
|---|---|
| Remove **School Programs** | ✓ Agree. It is a thin page with the wrong founding year. Redirect its URL to the new "For Schools" page |
| Remove **School Registration** | ✓ Agree, but **don't lose the lead form.** Replace it with one short "Request a proposal" form, linked from every school page |
| Add **AITEP, ITEC Training, Robotics & IoT** under Train the Trainer | ✓ Agree. Also give each one its own page (already designed), so each can rank on Google for its own search terms |

## Recommended menu

**Home · For Schools · For Students · Impact · About · Blog · [Request a proposal]** (button)

- **For Schools** (dropdown):
  - ITEC Curriculum (Grades 1–8)
  - Robotics & IoT
  - AITEP: AI for Teachers
  - Teacher Training (Train the Trainer), covering ITEC, AITEP, and Robotics & IoT
  - Monitoring & Evaluation
  - Books & LMS
- **For Students:** Techno Camps (Scratch, Programming, Generative AI and other courses), plus registration
- **Impact:** projects and collaborations, government partners, case studies and student projects
- **About:** company story, team, regional partners (IdeoMetriX in Gilgit-Baltistan, Max-Academia in Punjab)

## Clean up page addresses (URLs)

Current addresses are confusing (for example, the M&E page lives at `/teacher-training-2/`). Move each page to a clear address, and set a **301 redirect** from the old one so you keep whatever Google ranking it has. The free *Redirection* plugin or Yoast Premium can do this.

| Current URL | New URL |
|---|---|
| /early-age-curriculum/ | /itec-curriculum/ |
| /teacher-training/ | /teacher-training/ (keep; becomes the hub for the three training programs) |
| /teacher-training-2/ | /monitoring-and-evaluation/ |
| /techno-camps/ | /coding-camps-for-kids/ |
| /school-programs/ | redirect to /for-schools/ |
| /school-registration/ | redirect to /request-a-proposal/ |
| (new) | /aitep-ai-training-for-teachers/ |
| (new) | /robotics-iot-for-schools/ |

---

# 3. Homepage

The homepage currently has **no main heading**, an image-only slider, two partner logos and parent testimonials. A principal can't tell within a few seconds what you do. Rebuild it in this order:

1. **Hero:** one clear headline, for example *"Coding, AI and robotics education for schools, built to create technology producers."* Add two buttons: **For Schools – Request a proposal** and **For Parents – View camps**.
2. **Who we help:** two or three audience cards (Schools, Students & Parents, Teachers), each linking to its section.
3. **Programs:** ITEC, Robotics & IoT, AITEP, Teacher Training, M&E and Camps. One line and a link each.
4. **Proof:** your confirmed figures (**50,000+ students, 5,000+ teachers, 50+ schools**), government partners (FDE, Sindh Education Foundation, Gilgit-Baltistan Education), STEM.org accreditation, and the NIC award.
5. **Student projects:** photos or short videos of robots, games and apps students built. This is the strongest proof of "Technology Producers".
6. **Testimonials:** principal quotes (you said these are available) alongside parent quotes.
7. **Partner and client logos**, with permission.
8. **Final call to action:** the request form plus a WhatsApp button.

*Note: the four program pages carry no company statistics, as you asked. The homepage is the right place for them.*

---

# 4. On-page SEO: what Google sees today

## Problems found

| Check | Finding |
|---|---|
| **Main heading (H1)** | **Missing** on Home, Services, Digitized Curriculum and Projects. Google uses the H1 to understand what a page is about |
| **Page titles** | Generic: "Home - The Technoknowledge", "Books - The Technoknowledge". No search terms in them |
| **Meta descriptions** (the text shown under your link in Google) | Broken or auto-generated. Home shows "The Technoknowledge Official Partners, Max-Academia and Ideomatrix". Books shows a YouTube link. Techno Camps shows "Tools: Scratch Jr. & Scratch". The Blog has none |
| **Image alt text** | Many images have none, e.g., **19 of 47 on the homepage** and 12 of 24 on Teacher Training. You lose image search traffic and accessibility |
| **Sitemap and robots.txt** | ✓ Working (Yoast SEO is installed). The sitemap also lists Elementor and ElementsKit internal items; exclude those in Yoast settings |
| **Structured data** | Generic Organization/Article markup only. Add Course data for the camps and FAQ data for the question sections |

**Good news:** Yoast SEO is already installed, so all of this can be fixed from inside WordPress without new tools.

## Recommended page titles and descriptions

Enter these in the Yoast box on each page. Titles should stay under about 60 characters and descriptions under about 155.

| Page | SEO title | Meta description |
|---|---|---|
| Home | Coding, AI & Robotics Education for Schools in Pakistan \| Technoknowledge | Full-year coding, AI and robotics curriculum, teacher training and coding camps for Grades 1–8. Serving schools across Pakistan since 2021. |
| ITEC Curriculum | Coding Curriculum for Schools, Grades 1–8 \| ITEC \| Technoknowledge | A full-year computer and coding curriculum with books, LMS and trained teachers. Scratch, App Inventor, HTML and Python. Request a proposal. |
| Robotics & IoT | Robotics & IoT Program for Schools in Pakistan \| Technoknowledge | Arduino robotics curriculum, real student projects and hands-on teacher training for Grades 1–8. Get a proposal for your school. |
| AITEP | AI Training for Teachers in Pakistan \| AITEP \| Technoknowledge | Train your teachers to plan lessons, make tests and prepare materials with AI, in minutes, not hours. Book a free Starter session. |
| Teacher Training | Teacher Training: Coding, AI & Robotics \| Technoknowledge | Hands-on Train the Trainer programs for schools: ITEC coding, AITEP AI training, and Robotics & IoT. On campus and online. |
| M&E | School Monitoring & Evaluation Services \| Technoknowledge | Quarterly, bi-annual or annual M&E: assessments, school visits, interviews and grade-wise progress reports with recommendations. |
| Coding Camps | Coding Camps for Kids in Pakistan: Scratch, Python & AI \| Technoknowledge | Online summer and winter coding camps for ages 6–17: Scratch, programming and Generative AI. Group or 1-to-1 classes. |
| Contact | Contact Technoknowledge \| Karachi, Pakistan | Call or WhatsApp +92 345 8205013, email info@thetechnoknowledge.com, or visit us in DHA Phase 6, Karachi. |

## Search terms to target (one main term per page)

| Audience | Page | Main and related search terms |
|---|---|---|
| Schools | ITEC | coding curriculum for schools Pakistan · computer curriculum grades 1–8 · ICT books for schools Pakistan |
| Schools | Robotics & IoT | robotics curriculum for schools Pakistan · robotics teacher training · STEM program for schools Karachi |
| Schools | AITEP | AI training for teachers Pakistan · AI for teachers workshop · generative AI training for schools |
| Schools | Teacher Training | coding teacher training Pakistan · train the trainer coding |
| Schools | M&E | school monitoring and evaluation services Pakistan |
| Parents | Camps | coding classes for kids Karachi / Pakistan · Scratch classes for kids · Python course for kids online · summer coding camp Karachi · AI course for kids Pakistan |

*Search volumes were not measured here. Check them free in **Google Keyword Planner**, and after a few weeks see which terms bring visitors in **Google Search Console**.*

## On every page

- Use **one H1** that contains the page's main search term.
- Mention the term naturally in the first paragraph, in a subheading and in the image alt text.
- **Link pages to each other.** The ITEC page links to Teacher Training and M&E. Blog posts link to the relevant program page.
- Add **alt text** to every image, describing it, for example "Grade 7 students testing a line-following robot".
- Add the **FAQ section** to each program page (already in the new designs) and turn on FAQ structured data in Yoast.

---

# 5. Getting visible beyond your website

## 5.1 Google Business Profile (highest impact for local searches)

- **Set up and verify** a profile for your DHA Phase 6, Karachi address, or claim it if one already exists.
- **Profile details:** choose categories such as *Educational consultant*, *Computer training school* and *Educational institution*. Add services (ITEC, Robotics, AITEP, Camps), photos and opening hours, and link the website.
- **Reviews:** ask partner principals and camp parents to leave Google reviews. Reply to every one.
- **Updates:** post monthly, for example new camp dates, a training completed, or a student project.

## 5.2 Links from trusted websites (backlinks)

You already have strong mentions that may not link to you. Ask each one to add a link to your website:

- **Federal Directorate of Education:** its July 2024 article on Technoknowledge training 50 primary teachers ([fde.gov.pk](https://fde.gov.pk/2024/07/26/empowering-educators-technoknowledge-completes-training-for-50-primary-school-teachers-under-fde/)). A government website link is very valuable.
- **Zindagi Trust:** its news item on the coding program with KFC and Technoknowledge ([zindagitrust.org](https://zindagitrust.org/news_detail/41?Zindagi_Trust_Launches_coding_Program_In_Collaboration_With_Kfc_=&_Technoknowledge=)).
- **Others to ask:**
  - partner schools (a "Technology partner" mention on their sites)
  - IdeoMetriX and Max-Academia
  - STEM.org's accredited directory
  - the National Incubation Center (NIC)
- **Press:** pitch a story to TechJuice and ProPakistani. Competitors (TechTree, STEMverse, Stemers) have all been covered. A good hook is "How Pakistani schools can prepare for the new national AI & Robotics curriculum", with AITEP as the example.

## 5.3 Blog: write for the people who buy

Current posts target general "coding for kids" topics, where global platforms dominate. Shift to topics principals and Pakistani parents search for, and publish **two posts a month**:

- How schools can implement Pakistan's national AI & Robotics curriculum (Grades 1–12)
- What a robotics lab for a Pakistani school needs, and what it costs
- How teachers can use AI to plan lessons (with a free template, which also captures leads)
- Case study: [School name], one year of ITEC (with results data)
- Student project spotlights: Smart Dustbin, Blind Stick, Scratch games
- Summer coding camps in Karachi: what kids build in 24 hours

## 5.4 YouTube and social media

- **YouTube:** post short videos of student projects and teacher training sessions, and embed them on the matching pages. Videos keep visitors on pages longer and can rank on Google.
- **Make social handles consistent.** The website links to X as @technoknowledg4, while other materials use @technoknowledge4.
- **Add Instagram and LinkedIn links** to the website footer. LinkedIn matters most for reaching principals and school owners.

---

# 6. Measure what works

- **Google Search Console** (free): submit the sitemap, check indexing and security, and see which searches bring visitors.
- **Google Analytics 4** (free): track **form submissions and WhatsApp button clicks** as conversions, so you know which pages bring school leads.
- **Speed:** test each page on **PageSpeed Insights** (free). Common fixes for Elementor sites are compressing images to WebP, adding a caching plugin and removing unused plugins. Speed was not measured in this review.
- **Monthly check:** leads by source, top search terms, and new Google reviews.

---

# 7. Action plan

| Priority | Actions |
|---|---|
| **Immediate (0–30 days)** | Remove the spam link and secure WordPress · correct facts and camp prices · remove the lorem ipsum page and fix the broken link · deactivate WooCommerce if unused · set up Search Console and GA4 · create or claim the Google Business Profile · fix page titles, meta descriptions and H1s on existing pages |
| **Short-term (1–3 months)** | New menu and URL redirects · publish the AITEP, ITEC, Robotics & IoT, M&E and Teacher Training pages · rebuild the homepage · add alt text · one "Request a proposal" form · ask FDE, Zindagi Trust and partners for links · start 2 blog posts a month · collect principal testimonials and Google reviews |
| **Medium-term (3–12 months)** | Publish case studies with results data · build a student project gallery and YouTube videos · press coverage (TechJuice, ProPakistani) · review Search Console every month and expand pages that start ranking |

*Realistic expectation: technical fixes and Google Business Profile results can show within weeks. Ranking for competitive terms such as "coding classes for kids" usually takes 3–6 months of steady content and links.*
