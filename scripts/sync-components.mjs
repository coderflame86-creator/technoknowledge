// =========================================================
// Technoknowledge: sync-components.mjs
// Copies the shared header and footer (frontend/components/)
// into every page, between these markers:
//   <!-- @header:start --> ... <!-- @header:end -->
//   <!-- @footer:start --> ... <!-- @footer:end -->
// It also fixes link paths for pages/ and marks the current
// menu item. Pages stay plain HTML: fast, SEO-friendly, and
// they work without JavaScript.
//
// Run from the project folder after editing the menu/footer:
//   node scripts/sync-components.mjs
// =========================================================
import { readFileSync, writeFileSync, readdirSync, existsSync } from 'node:fs';
import { join, basename } from 'node:path';

const FRONTEND = new URL('../frontend/', import.meta.url).pathname.replace(/^\/([A-Za-z]:)/, '$1');
const header = readFileSync(join(FRONTEND, 'components/header.html'), 'utf8');
const footer = readFileSync(join(FRONTEND, 'components/footer.html'), 'utf8');

const stripComment = (html) => html.replace(/^<!--[\s\S]*?-->\s*/, '');

function render(partial, root, page) {
  let html = stripComment(partial).replaceAll('{{root}}', root);
  // mark links for this page as current
  html = html.replace(new RegExp(`data-page="${page}"`, 'g'), `data-page="${page}" aria-current="page"`);
  // mark the top-level menu group that contains this page
  html = html.replace(/class="nav__item" data-pages="([^"]*)"/g, (m, list) =>
    list.split(/\s+/).includes(page) ? `class="nav__item is-current" data-pages="${list}"` : m);
  return html.trim();
}

function inject(file, root, pageName) {
  const page = pageName || basename(file, '.html');
  let html = readFileSync(file, 'utf8');
  let changed = false;
  for (const [name, partial] of [['header', header], ['footer', footer]]) {
    const re = new RegExp(`(<!-- @${name}:start -->)[\\s\\S]*?(<!-- @${name}:end -->)`);
    if (re.test(html)) {
      html = html.replace(re, `$1\n${render(partial, root, page)}\n$2`);
      changed = true;
    }
  }
  if (changed) { writeFileSync(file, html); console.log('updated', file.replace(FRONTEND, '')); }
}

// home-level pages
for (const f of readdirSync(FRONTEND)) if (f.endsWith('.html')) inject(join(FRONTEND, f), '');
// inner pages
const pagesDir = join(FRONTEND, 'pages');
if (existsSync(pagesDir)) for (const f of readdirSync(pagesDir)) if (f.endsWith('.html')) inject(join(pagesDir, f), '../');
// blog posts (pages/blog/*.html): two levels deep, and the Blog menu item is current
const blogDir = join(FRONTEND, 'pages/blog');
if (existsSync(blogDir)) for (const f of readdirSync(blogDir)) if (f.endsWith('.html')) inject(join(blogDir, f), '../../', 'blog');
