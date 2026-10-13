// =========================================================
// Technoknowledge: check-live.mjs  (run right after go-live, then monthly)
// Checks the LIVE website:
//   1. every page in sitemap.xml opens (status 200)
//   2. every old WordPress address in docs/redirect-map.txt sends a 301
//      to the right new page, and that page opens
//   3. http:// and www. versions redirect to https://thetechnoknowledge.com
//   4. a made-up address shows the 404 page with a real 404 status
// Needs Node 18+.  Run from the project folder:
//   node scripts/check-live.mjs
//   node scripts/check-live.mjs https://staging.example.com   (to test another address)
// =========================================================
import { readFileSync } from 'node:fs';

const BASE = (process.argv[2] || 'https://thetechnoknowledge.com').replace(/\/$/, '');
const root = new URL('../', import.meta.url).pathname.replace(/^\/([A-Za-z]:)/, '$1');
let problems = 0;
const bad = (msg) => { problems++; console.log('  ✗ ' + msg); };
const ok = (msg) => console.log('  ✓ ' + msg);

async function head(url) {
  const r = await fetch(url, { redirect: 'manual', headers: { 'User-Agent': 'TK-site-check' } });
  return { status: r.status, location: r.headers.get('location') };
}
const abs = (loc) => (loc && loc.startsWith('/') ? BASE + loc : loc);

console.log('\n1. Pages in sitemap.xml');
const sitemap = await (await fetch(BASE + '/sitemap.xml')).text();
const pages = [...sitemap.matchAll(/<loc>([^<]+)<\/loc>/g)].map(m => m[1].replace('https://thetechnoknowledge.com', BASE));
for (const u of pages) {
  const r = await head(u);
  r.status === 200 ? ok(u) : bad(`${u} → ${r.status}${r.location ? ' ' + r.location : ''}`);
}

console.log('\n2. Old WordPress addresses');
const map = readFileSync(root + 'docs/redirect-map.txt', 'utf8').split('\n')
  .filter(l => l.includes('->') && !l.startsWith('#')).map(l => l.split('->').map(s => s.trim()));
for (const [from, to] of map) {
  const r = await head(BASE + from);
  const want = BASE + to;
  if (r.status !== 301 && r.status !== 308) { bad(`${from} → ${r.status} (expected 301 to ${to})`); continue; }
  if (abs(r.location) !== want) { bad(`${from} → ${r.location} (expected ${to})`); continue; }
  const t = await head(want);
  t.status === 200 ? ok(`${from} → ${to}`) : bad(`${from} → ${to} but that page gives ${t.status}`);
}

console.log('\n3. One address only');
for (const u of [BASE.replace('https://', 'http://') + '/', BASE.replace('https://', 'https://www.') + '/']) {
  try {
    const r = await head(u);
    (r.status === 301 && abs(r.location) === BASE + '/') ? ok(`${u} → ${r.location}`) : bad(`${u} → ${r.status} ${r.location || ''}`);
  } catch (e) { bad(`${u} could not be opened (${e.cause?.code || e.message})`); }
}

console.log('\n4. Missing page');
const miss = await head(BASE + '/this-page-does-not-exist-' + Date.now());
miss.status === 404 ? ok('unknown address shows 404 page with status 404') : bad(`unknown address returned ${miss.status} (should be 404, not 200 or 302)`);

console.log(problems ? `\n${problems} problem(s) found.\n` : '\nAll good.\n');
process.exit(problems ? 1 : 0);
