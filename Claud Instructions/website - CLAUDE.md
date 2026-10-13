# CLAUDE.md: Technoknowledge Website

Instructions for Claude (and any AI assistant) working in this project. Read this first, every session.

## 1. Project purpose

Website for **Technoknowledge (SMC-Pvt) Ltd.**, a Pakistani EdTech company. Tagline: **"Creating Technology Producers."**
It sells coding, AI and robotics education to:

1. **Schools (main audience):** principals, school owners, NGOs and government programs
2. **Parents and students:** Techno Camps, live online classes
3. **Teachers:** teacher training through their schools

The site must make offerings easy to understand and buy, and turn visitors into leads (proposal requests and camp registrations).
Look and feel: **modern, professional, interactive, mobile-first.** It must not look AI-generated, cheap or old-fashioned.

## 2. Simple architecture

```
frontend/  static HTML + CSS + vanilla JS (no framework, no build step)
   └─ js/api.js ──POST JSON──► backend/ (form API) ──► email / WhatsApp / database
data/      single source of truth for facts (company, menu, programs, courses, testimonials)
docs/      api-contract.md (form endpoints), deployment.md
prompts/   brand voice + content prompt
```

- **Pages:** `frontend/index.html` (Home) and `frontend/pages/*.html`
- **Shared parts:** `frontend/components/` (header, footer). Copied into every page by `node scripts/sync-components.mjs`
- **CSS load order:** `variables.css` → `base.css` → `layout.css` → `components.css` → `pages/<page>.css`
- **Only `frontend/js/api.js` talks to the backend.** Endpoints must match `docs/api-contract.md`.
- **The backend is built by the developer.** Do not build it unless asked.

## 3. Coding rules

**HTML**
- Semantic HTML5. Exactly **one `<h1>` per page**, plus a unique `<title>` and meta description.
- Real `<button>`, `<a href>`, `<label>` + `<input>`. Never put click handlers on a `div`.
- Every image needs `alt` text, `width`/`height`, and `loading="lazy"` when it is below the first screen.
- Image paths point to `frontend/assets/images/<folder>/<name>.webp` so the developer can swap photos by keeping file names.

**CSS**
- Use the variables in `css/variables.css` for every colour, font and radius. **No hard-coded hex colours elsewhere.**
- Mobile-first. Breakpoints: 640px and 1100px.
- Class names: simple kebab-case, e.g. `.course-card`, `.course-card__title`.
- Shared styles go in `components.css`. One-page styles go in `css/pages/<page>.css`.
- No CSS frameworks (Bootstrap, Tailwind) unless the owner asks.

**JavaScript**
- Plain vanilla JS with `defer` (no ES modules, so pages open from a folder too). Each file adds a function to `window.TK`. No jQuery. No heavy libraries.
- Every widget must work with the keyboard and respect `prefers-reduced-motion`.
- The page must still be readable if JavaScript fails.

**Content**
- Take facts **only** from `data/*.json`. Never invent figures, prices, partners, testimonials or awards.
- Anything unconfirmed: use a visible placeholder such as `[TODO: …]`.
- Follow `prompts/brand-voice.md`: short, confident, benefit-first. Avoid "unlock your potential" and jargon.
- All content is in **English**.

**Design quality ("must not look AI-generated or cheap")**
- No gradient blobs, emoji icons, stock-photo clichés or generic filler text.
- Use real Technoknowledge photos (placeholders until supplied). Keep spacing consistent and the typography hierarchy clear.
- Brand: navy `#13294B`, yellow `#FFC21A`, green `#2E8B57`. Fonts: Sora (headings) and Nunito Sans (body).
- Logo: use only `assets/images/logos/tk-logo.svg` (the owner's official artwork) exactly as supplied. Never recolour, redraw or make a white version. Do not change site colours when placing it.

**Naming**
- Files and folders: lowercase-with-dashes (`techno-camps.html`, `line-following-robot.webp`).

## 4. Security rules

- **Never commit secrets.** API keys, SMTP passwords and tokens belong only in `.env` and `backend/.env`, which are git-ignored. Only `.env.example` files hold placeholder names.
- **Never put secret keys in front-end code.** Only public IDs (GA4, reCAPTCHA *site* key) may appear there.
- **Validate on the backend.** Front-end validation is for user experience only.
- Forms need reCAPTCHA or Turnstile, rate limiting, and CORS restricted to `ALLOWED_ORIGIN`.
- Never use `innerHTML` with user input. Use `textContent`.
- External links use `rel="noopener noreferrer"`. No third-party scripts except Google Fonts, GA4 and reCAPTCHA.
- Children's data (names, ages) is personal data: collect only what the form needs, and never log it publicly.
- Never publish children's photos without the owner confirming parental permission.

## 5. Scope rule: change only what the task needs

- **Modify only the files needed for the current task.** Do not reformat, rename, reorganise or "improve" other files.
- Before editing, say which files you will change. If another file needs a change, ask first.
- Do not create new folders or files unless the task needs them. Follow the existing structure.
- Do not change design tokens, the shared header/footer or `data/` unless the task is about them.
- When a fact changes, update `data/` first, then only the pages that show that fact.

## 6. Token-saving rules

- Read only the files relevant to the task. Do not scan the whole project.
- Use `README.md` files and this file to find things instead of opening many files.
- Edit with small, targeted changes. Do not rewrite whole files to change a few lines.
- Do not paste whole files back into the chat. Summarise what changed (file + section).
- Reuse existing components and CSS classes before writing new ones.
- Keep replies short: what changed, where, and anything the owner must confirm.
- Ask one clear question when a requirement is unclear, rather than building several versions.

## 7. Key facts (full details in `data/`)

- Founded January 2021 · 50,000+ students · 5,000+ teachers · 50+ schools
- **Programs:**
  - ITEC (Grades 1–8)
  - AI Curriculum & AITEP
  - Robotics & IoT (VEX Grades 1–5, Arduino Grades 6–8)
  - Monitoring & Evaluation
  - Books
  - LMS
- **Camps:** online, 1-to-1 all year (PKR 21,999 per course) or group in term breaks only (PKR 12,999 per course)
- **Contact:** +92 322 2055185 · WhatsApp +92 345 8205013 · info@thetechnoknowledge.com
