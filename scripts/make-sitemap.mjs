// =========================================================
// Technoknowledge: make-sitemap.mjs
// Rebuilds frontend/sitemap.xml from the pages in frontend/.
// <lastmod> = the date each page file was last changed.
// Run from the project folder after changing pages:
//   node scripts/make-sitemap.mjs
// =========================================================
import { readdirSync, statSync, writeFileSync, readFileSync, existsSync } from 'node:fs';
import { join } from 'node:path';

const FE = new URL('../frontend/', import.meta.url).pathname.replace(/^\/([A-Za-z]:)/, '$1');
const SITE = 'https://thetechnoknowledge.com/';
const SKIP = new Set(['404.html', 'style-guide.html']);

const day = (f) => statSync(f).mtime.toISOString().slice(0, 10);
const urls = [];
const add = (file, path, priority) => {
  const html = readFileSync(file, 'utf8');
  if (/<meta name="robots" content="[^"]*noindex/i.test(html)) return;   // never list noindex pages
  urls.push({ loc: SITE + path, lastmod: day(file), priority });
};
add(join(FE, 'index.html'), '', '1.0');
for (const f of readdirSync(join(FE, 'pages')).sort())
  if (f.endsWith('.html') && !SKIP.has(f)) add(join(FE, 'pages', f), 'pages/' + f, '0.8');
const blog = join(FE, 'pages/blog');
if (existsSync(blog)) for (const f of readdirSync(blog).sort())
  if (f.endsWith('.html')) add(join(blog, f), 'pages/blog/' + f, '0.6');

const xml = '<?xml version="1.0" encoding="UTF-8"?>\n<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">\n' +
  urls.map(u => `  <url><loc>${u.loc}</loc><lastmod>${u.lastmod}</lastmod><priority>${u.priority}</priority></url>`).join('\n') +
  '\n</urlset>\n';
writeFileSync(join(FE, 'sitemap.xml'), xml);
console.log('sitemap.xml:', urls.length, 'pages');
