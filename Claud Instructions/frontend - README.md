# frontend/

Plain **HTML + CSS + JavaScript**. No framework, no build step: easy to host anywhere
and easy for any developer to edit.

| Folder | Contains |
|---|---|
| `index.html` | Home page (added during build) |
| `pages/` | All other pages |
| `components/` | Shared header, footer and form partials, loaded into every page |
| `css/` | Styles, loaded in this order: `variables` → `base` → `layout` → `components` → `pages/*` |
| `js/` | `main.js` (menus, sliders, tabs), `api.js` (forms → backend), `components/`, `pages/` |
| `assets/` | `images/`, `icons/`, `fonts/` |

**To update photos:** see `assets/images/README.md`.
**To connect forms:** set `API_BASE_URL` in `js/api.js`, then build the backend from `../docs/api-contract.md`.
