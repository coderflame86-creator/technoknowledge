# SEO ranking plan: coding, AI, robotics and bootcamps in Pakistan (5 Oct 2026)

## 0. SEO pass 2 — done on 5 Oct 2026 (night)
Script `fontwork/seo2.py`. Checked with an audit of all 38 pages: all JSON-LD parses, no duplicate titles/descriptions, no distorted images, navigation test 0 problems.
- **Shorter titles** (Google shows ~60 characters), keyword first: Home "Coding, AI & Robotics Education in Pakistan | Technoknowledge"; About, Blog, Books, Impact, LMS, M&E, Robotics shortened. Blog posts now end "| Technoknowledge" instead of "| Technoknowledge Blog".
- **Shorter meta descriptions** (≤160) on About, Books, Impact, LMS and the AI & Robotics curriculum guide.
- **BreadcrumbList schema** added to the 11 pages that only had a visible breadcrumb (ITEC, Robotics, AITEP, LMS, M&E, Books, Request a Proposal, Techno Camps, Blog, Contact, Privacy) — Google can show "Home › For Schools › ITEC" in results.
- **Course schema** for ITEC, Robotics & IoT and AITEP; **Service schema** for M&E and LMS (provider: Technoknowledge, area: Pakistan). **WebSite schema** on Home.
- **Image width/height** added to the 90 images on Home and Impact that lacked them (stops layout shift; Core Web Vitals).
- **Privacy page** now has Open Graph / Twitter tags.
- Still long (fine, keyword-led): 7 blog post titles 69–96 characters. Three old posts say "in 2025" in the title — owner may want to update to 2026 when the content is refreshed.

## 1. What is already done on the new site (audit of 37 pages)
- Every page: unique title and meta description, canonical URL, one H1, Open Graph + Twitter cards, `lang="en"`, alt text on 100% of images.
- Structured data: EducationalOrganization (Home, About, Contact), FAQPage (ITEC, AITEP, Robotics, LMS, M&E, Books, Request a Proposal), Course (Techno Camps, ITEC, Robotics, AITEP), Service (M&E, LMS), BreadcrumbList (all inner pages), BlogPosting (22 posts), Book list, WebSite.
- Fast by design: static HTML, WebP images, no WordPress plugins.
- `sitemap.xml` (36 URLs) and `robots.txt`; 1200×630 JPG share images.

## 2. Gaps found (first audit) — status
| Gap | Fix | Status |
|---|---|---|
| No `sitemap.xml` or `robots.txt` | Create both; submit in Search Console and Bing | ✅ created; submit at go-live |
| "Bootcamp" missing | Techno Camps title/H1/intro use "bootcamps" | ✅ |
| Program H1s were slogans | Keyword line in every program H1 (`h1-sub`) | ✅ |
| "Pakistan" missing from H1s | Added | ✅ |
| `og:image` was WebP | JPG share images | ✅ |
| URLs are `/pages/robotics-iot.html` | Optional clean URLs + 301s at go-live | open |

## 3. Who ranks today (single US-based check, 5 Oct 2026; results vary)
- **"coding classes for kids Pakistan":** Codingal city pages (Defence, Rawalpindi, Faisalabad), UTech, TeacherOn tutor lists, PNY Genius (Lahore Scratch course).
- **"robotics in Pakistan schools curriculum":** news about the national AI & Robotics curriculum for Grades 1–12 (TechJuice, CW Pakistan), NCC PDF, Taakhleeq STEM guide, IPEI.
- **"AI bootcamp Pakistan kids":** Codanics, atomcamp (NUST), Kaabil, Nucamp and Khired Kids listicles.
- Lessons: (1) competitors win with **dedicated landing pages per search** (city + course); (2) the robotics query is dominated by **news/guide content** — a strong explainer can win; (3) **"best … in Pakistan" listicles** rank, so getting listed in them is a quick win.

## 4. Keyword → page map
| Target search | Main page | Supporting content |
|---|---|---|
| coding in Pakistan / coding classes for kids Pakistan | Techno Camps (parents) | ITEC (schools); blog coding guides linked to Camps |
| AI in Pakistan / AI course for kids / AI training for teachers | AITEP (teachers) + Camps "Generative AI" course | Article: "Pakistan's AI & Robotics curriculum (Grades 1–12)" ✅ published |
| Robotics in Pakistan / robotics curriculum for schools | Robotics & IoT | Student project gallery (Impact), Grade 6–8 project sheets |
| Bootcamps in Pakistan / coding bootcamp for kids | Techno Camps | "15 Benefits of Coding Bootcamp for Kids" → Camps; seasonal posts "Summer coding bootcamp 2027" |
| coding robotics IoT curriculum Pakistan | ITEC + Robotics & IoT | Home H1 support line |

## 5. "First coding, robotics and IoT curriculum company in Pakistan"
- **Do not publish "first" unless it can be proven.** Pakistan Science Club has run robotics/STEM labs since 2008 (see 02_Competitor_Research). An unprovable "first" claim risks complaints and damages trust with principals.
- Nobody searches "first company", so it adds no ranking value. Use verifiable, distinctive claims instead:
  - "Contributed to Pakistan's Single National Curriculum for Computer Science (Grades 6–8)"
  - "Full-year coding, AI, robotics & IoT curriculum for Grades 1–8 — books, LMS and teacher training in one"
  - "Trusted by FDE, Sindh Education Foundation and the Ministry of Education, Gilgit-Baltistan"
  - "STEM.org Accredited Curriculum · Women-led · since 2021"

## 6. Off-site actions (these move rankings most — owner/team)
1. **Google Business Profile** (Phase 6, DHA, Karachi): categories, services, photos; ask partner principals and camp parents for reviews.
2. **Backlinks from existing partners:** FDE article (fde.gov.pk), Zindagi Trust news, SEF, GB Ministry, partner schools ("technology partner" mention), IdeoMetriX, Max-Academia, STEM.org directory, NIC.
3. **Listicles:** ask Khired Kids ("Best AI courses for kids in Pakistan"), Nucamp and similar to include Techno Camps.
4. **Press:** pitch TechJuice / ProPakistani: "How schools can implement the national AI & Robotics curriculum" with TK's SNC experience.
5. **YouTube + Instagram Reels:** student projects (the robotics collage photos are ideal); embed on Robotics & Camps pages.
6. **Directories:** Bing Places, LinkedIn company page, Facebook page — same name, phone and address everywhere.

## 7. Technical at go-live (Step 9)
- Search Console + Bing Webmaster: verify, submit sitemap, request indexing for 6 key pages; check Rich Results test (Course, FAQ, Breadcrumb).
- 301 redirects from old WordPress URLs (keep existing rankings).
- GA4 with conversions: form submissions and WhatsApp clicks.
- PageSpeed Insights check on Home, Camps, Robotics (the animations are CSS-only and light).

## 8. Priorities
- **Immediate (0–30 days):** go-live + Search Console/Bing + sitemap; GBP; ask 5–10 partners for a link.
- **Short-term (1–3 months):** 2 posts/month (bootcamp season, AI for teachers, robotics project write-ups); listicle outreach; reviews; refresh "2025" posts.
- **Medium-term (3–12 months):** city landing sections only where real (Karachi, Lahore, Islamabad, Gilgit); case studies with real M&E results; press; YouTube.
- Realistic expectation: Technoknowledge brand + long-tail terms in weeks; broad terms like "coding in Pakistan" take 3–6+ months of content and links. No one can guarantee position 1.
