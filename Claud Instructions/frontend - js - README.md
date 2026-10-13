# js/

Plain JavaScript files loaded with `defer` (not ES modules), so pages also work when opened straight from a folder.
Each file adds one function to `window.TK`; `main.js` starts them.

| File | Purpose |
|---|---|
| `components/header.js` | Sticky header, desktop dropdowns, mobile menu |
| `components/reveal.js` | Fade-in on scroll (respects "reduce motion") |
| `main.js` | Starts everything on every page. Load it **last** |
| `api.js` | **The only file that sends form data** (set up in Step 7) |
| `pages/` | Small scripts used by one page only |
