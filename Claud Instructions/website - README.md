# Technoknowledge Website

Website for **Technoknowledge (SMC-Pvt) Ltd.**: *Creating Technology Producers*.
Coding, AI and robotics education for schools and students across Pakistan.

> Status: **project structure only.** The front end will be built page by page.
> The developer then adds real photos and connects the backend (forms).

---

## 1. Folder map

```
technoknowledge-website/
├── README.md              ← you are here
├── .env.example           ← copy to .env and fill in (never commit .env)
├── .gitignore
│
├── prompts/               ← AI prompts + brand voice used to write page content
├── data/                  ← single source of truth: company facts, menu, programs, courses
│
├── frontend/              ← everything the visitor sees (HTML, CSS, JS, images)
│   ├── index.html         ← Home (added during build)
│   ├── pages/             ← all other pages (.html)
│   ├── components/        ← shared header, footer, forms (HTML partials)
│   ├── css/               ← styles: variables → base → layout → components → pages
│   ├── js/                ← interactivity + api.js (talks to the backend)
│   └── assets/            ← images, icons, fonts
│
├── backend/               ← form handling API (to be connected by the developer)
│   ├── .env.example
│   ├── src/               ← routes, controllers, services, models, middleware, config, utils
│   └── tests/
│
└── docs/                  ← API contract + deployment guide
```

## 2. How the parts connect

```
Visitor ──► frontend (HTML/CSS/JS) ──► js/api.js ──► backend API ──► email / WhatsApp / database
```

- The front end is **static** (plain HTML, CSS, JavaScript). No build tools needed.
- Every form (camp registration, request a proposal, AITEP Starter, contact) sends JSON
  to the backend through **one file: `frontend/js/api.js`**.
- The backend endpoints are defined in **`docs/api-contract.md`**. Build the backend to that contract
  and the forms work without any change to the front end.

## 3. For the developer: getting started

1. Open `frontend/index.html` in Chrome to view the site (or run any static server, e.g. VS Code *Live Server*).
2. Replace photos in `frontend/assets/images/` (see `frontend/assets/images/README.md` for sizes and names).
   Keep the **same file names** and the pages update automatically.
3. Build the backend in `backend/` following `docs/api-contract.md`.
4. Copy `.env.example` → `.env` (root and backend) and fill in the values.
5. Set `API_BASE_URL` in `frontend/js/api.js`.
6. Deploy using `docs/deployment.md`.

## 4. Rules that keep the project tidy

| Rule | Why |
|---|---|
| Facts (figures, prices, phone numbers) live in `data/` first | One place to update; pages stay consistent |
| Colours and fonts only in `frontend/css/variables.css` | Change the brand once, everywhere |
| Header and footer only in `frontend/components/` | Edit the menu once, every page updates |
| File and folder names: lowercase-with-dashes | Clean URLs and no broken links on Linux servers |
| Images: WebP, compressed (see image guide) | Fast pages on mobile data |
| Never commit `.env` or passwords | Security |

## 5. Pages (planned)

| Page | File |
|---|---|
| Home | `frontend/index.html` |
| ITEC Curriculum & Training | `frontend/pages/itec-curriculum.html` |
| AI Curriculum & AITEP | `frontend/pages/aitep.html` |
| Robotics & IoT | `frontend/pages/robotics-iot.html` |
| Monitoring & Evaluation | `frontend/pages/monitoring-evaluation.html` |
| Books | `frontend/pages/books.html` |
| LMS | `frontend/pages/lms.html` |
| Techno Camps (+ Register) | `frontend/pages/techno-camps.html` |
| Request a Proposal | `frontend/pages/request-proposal.html` |
| Impact | `frontend/pages/impact.html` |
| About | `frontend/pages/about.html` |
| Contact | `frontend/pages/contact.html` |
| Blog | `frontend/pages/blog.html` |
| 404 | `frontend/pages/404.html` |

## 6. Contact

info@thetechnoknowledge.com · +92 322 2055185 · WhatsApp +92 345 8205013
