# components/

Master copies of the parts shared by every page:

- `header.html`: top contact bar, logo, main menu with dropdowns, mobile menu, "Request a proposal"
- `footer.html`: links, contact, social, floating WhatsApp button
- `page-template.html`: copy this to start a new page (head tags, CSS and JS already in place)

**How it works:** pages are plain HTML (fast, good for SEO, work without JavaScript).
Edit the header or footer **here once**, then run from the project folder:

```
node scripts/sync-components.mjs
```

The script copies them into every page between the `<!-- @header:start -->` / `<!-- @footer:start -->` markers,
fixes link paths for `pages/`, and highlights the current menu item.
