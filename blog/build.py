"""Technoknowledge blog builder.
Turns the Markdown posts in blog/posts/ into
  frontend/pages/blog.html          (the list page)
  frontend/pages/blog/<slug>.html   (one page per post)
Then run:  node scripts/sync-components.mjs   (adds the header and footer)

Needs Python 3 and the "markdown" package:  pip install markdown
Run from the project folder:              python blog/build.py
"""
import os, re, json, html, glob, math, datetime
import markdown

HERE = os.path.dirname(os.path.abspath(__file__))
SRC = os.path.join(HERE, 'posts')
FE = os.path.join(HERE, '..', 'frontend')
SITE = 'https://thetechnoknowledge.com'
os.makedirs(FE + '/pages/blog', exist_ok=True)

ic = lambda d, w='2': '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="%s" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">%s</svg>' % (w, d)
CATS = {  # name: (slug, colour class, icon)
  'For schools': ('for-schools', 'navy', ic('<path d="M3 21h18M5 21V10l7-5 7 5v11M9 21v-6h6v6"/>')),
  'For teachers': ('for-teachers', 'orange', ic('<rect x="3" y="4" width="18" height="12" rx="2"/><path d="M7 20h10M12 16v4M7 9h6M7 12h4"/>')),
  'Parents': ('parents', 'green', ic('<circle cx="9" cy="7" r="3"/><circle cx="17" cy="9" r="2.4"/><path d="M3 20a6 6 0 0 1 12 0M14.5 20a4.5 4.5 0 0 1 7 -3.7"/>')),
  'Getting started': ('getting-started', 'sky', ic('<path d="m8 8-5 4 5 4M16 8l5 4-5 4M14 4l-4 16"/>')),
  'Coding & games': ('coding-games', 'yellow', ic('<rect x="2" y="7" width="20" height="11" rx="4"/><path d="M7 11v3M5.5 12.5h3M15 12h.01M18 13.5h.01"/>')),
  'Future skills': ('future-skills', 'blue', ic('<path d="M12 3l1.8 4.7L18.5 9.5l-4.7 1.8L12 16l-1.8-4.7L5.5 9.5l4.7-1.8z"/><path d="M19 15l.8 2.2L22 18l-2.2.8L19 21l-.8-2.2L16 18l2.2-.8z"/>')),
  'Digital creativity': ('digital-creativity', 'red', ic('<path d="M12 22a10 10 0 1 1 10-10c0 2.8-2.2 4-4 4h-2a2 2 0 0 0-1.5 3.3A1.6 1.6 0 0 1 12 22z"/><circle cx="7.5" cy="10.5" r="1"/><circle cx="12" cy="7" r="1"/><circle cx="16.5" cy="10.5" r="1"/>')),
}
ORDER = list(CATS)
LINKS = {'camps': 'techno-camps.html', 'itec': 'itec-curriculum.html', 'books': 'books.html', 'lms': 'lms.html',
         'aitep': 'aitep.html', 'robotics': 'robotics-iot.html', 'mne': 'monitoring-evaluation.html',
         'proposal': 'request-proposal.html', 'contact': 'contact.html'}
I_CLOCK = ic('<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>')
I_CAL = ic('<rect x="3" y="5" width="18" height="16" rx="2"/><path d="M16 3v4M8 3v4M3 10h18"/>')
I_WA = ic('<path d="M3 21l1.6-4.8A8.5 8.5 0 1 1 8 19.6z"/><path d="M9 9.5c0 3 2.5 5.5 5.5 5.5l1.2-1.2-1.8-1-1 .8a4 4 0 0 1-2.5-2.5l.8-1-1-1.8z"/>')
I_FB = '<svg viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M14 8h3V4h-3c-2.8 0-4.5 1.8-4.5 4.6V11H7v4h2.5v9h4v-9h3l.5-4h-3.5V9c0-.6.4-1 1-1z"/></svg>'
I_IN = '<svg viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M5 3.5A2 2 0 1 1 5 7.5a2 2 0 0 1 0-4zM3.3 9h3.4v11.5H3.3zM9.3 9h3.2v1.6h.1c.5-.9 1.6-1.9 3.4-1.9 3.6 0 4.3 2.4 4.3 5.5v6.3h-3.4v-5.6c0-1.3 0-3-1.9-3s-2.1 1.4-2.1 2.9v5.7H9.3z"/></svg>'
I_LINK = ic('<path d="M10 14a4 4 0 0 0 5.7 0l3-3a4 4 0 0 0-5.7-5.7l-1 1"/><path d="M14 10a4 4 0 0 0-5.7 0l-3 3a4 4 0 0 0 5.7 5.7l1-1"/>')
I_ARROW = ic('<path d="M5 12h14M13 6l6 6-6 6"/>')
I_SEARCH = ic('<circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/>')

def parse(path):
    raw = open(path, encoding='utf8').read()
    head, body = raw.split('\n---\n', 1)
    meta = {}
    for line in head.strip().splitlines():
        k, v = line.split(':', 1); meta[k.strip()] = v.strip()
    meta['slug'] = os.path.basename(path)[:-3]
    meta['md'] = body.strip()
    return meta

posts = sorted((parse(p) for p in glob.glob(SRC + '/*.md')), key=lambda m: (m['date'], m['title']), reverse=True)
by_slug = {p['slug']: p for p in posts}

def fix_links(md, prefix):
    def rep(m):
        key = m.group(1)
        if key.startswith('post:'):
            s = key[5:]; assert s in by_slug, s
            return '](%s%s.html)' % ('' if prefix == '../' else 'blog/', s)
        return '](%s%s)' % (prefix, LINKS[key])
    return re.sub(r'\]\(TK:([a-z:0-9-]+)\)', rep, md)

def fmt_date(d):
    x = datetime.date.fromisoformat(d)
    return '%d %s' % (x.day, x.strftime('%B %Y'))

def slugify(t):
    return re.sub(r'[^a-z0-9]+', '-', t.lower()).strip('-')[:60]

def cover(p, size=''):
    slug, col, icon = CATS[p['cat']]
    return ('<div class="pcover pcover--%s%s" aria-hidden="true"><span class="pcover__brace">{</span><span class="pcover__icon">%s</span>'
            '<span class="pcover__brace pcover__brace--r">}</span></div>') % (col, size, icon)

for p in posts:
    words = len(re.sub(r'[#*\[\]()>`-]', ' ', p['md']).split())
    p['mins'] = max(2, math.ceil(words / 220))
    p['words'] = words

# ---------------------------------------------------------------- post pages
POST_TPL = open(os.path.join(HERE, 'templates', 'post.html'), encoding='utf8').read()
for p in posts:
    md_text = fix_links(p['md'], '../')
    body = markdown.markdown(md_text, extensions=['sane_lists'], output_format='html')
    # ids on h2 for the table of contents
    toc = []
    def h2(m):
        text = re.sub(r'<[^>]+>', '', m.group(1))
        hid = slugify(html.unescape(text)) or 'section'
        while any(t[0] == hid for t in toc): hid += '-2'
        toc.append((hid, text))
        return '<h2 id="%s">%s</h2>' % (hid, m.group(1))
    body = re.sub(r'<h2>(.*?)</h2>', h2, body)
    # external links open in a new tab
    body = re.sub(r'<a href="(https?://[^"]+)">', r'<a href="\1" target="_blank" rel="noopener noreferrer">', body)
    # wrap tables (none expected) and code
    slug, col, icon = CATS[p['cat']]
    tocs = ''.join('<li><a href="#%s">%s</a></li>' % (i, t) for i, t in toc)
    # related: same category first, then newest
    rel = [q for q in posts if q is not p and q['cat'] == p['cat']][:3]
    rel += [q for q in posts if q is not p and q not in rel][:3 - len(rel)]
    relh = ''.join(
        '<li class="bcard bcard--sm"><a class="bcard__link" href="%s.html">%s<div class="bcard__body"><p class="bcard__cat">%s</p><h3>%s</h3><p class="bcard__meta">%s min read</p></div></a></li>'
        % (q['slug'], cover(q), q['cat'], html.escape(q['title']), q['mins']) for q in rel)
    school = p['cat'] in ('For schools', 'For teachers')
    if p['cat'] == 'For teachers':
        cta = ('Train your teachers with AITEP.', 'Start with a free 3-hour online Starter session for your staff.', '../aitep.html', 'Book a free Starter')
    elif school:
        cta = ('Bring coding, AI and robotics to your school.', 'Tell us your grades and number of students. We reply within one working day.', '../request-proposal.html', 'Request a proposal')
    else:
        cta = ('Let your child build, not just play.', 'Live online Techno Camps for ages 6 to 12+: Scratch, Python, Canva, app development and Generative AI.', '../techno-camps.html', 'See Techno Camps')
    url = '%s/pages/blog/%s.html' % (SITE, p['slug'])
    ld = {"@context": "https://schema.org", "@graph": [
        {"@type": "BlogPosting", "headline": p['title'], "description": p['desc'], "datePublished": p['date'],
         "dateModified": p.get('updated') or (max(p['date'], '2026-10-04') if p['date'] < '2026-01-01' else p['date']),
         "mainEntityOfPage": url, "url": url, "wordCount": p['words'], "articleSection": p['cat'],
         "author": {"@type": "Organization", "name": "Technoknowledge", "url": SITE + "/"},
         "publisher": {"@type": "Organization", "name": "Technoknowledge (SMC-Pvt) Ltd.",
                       "logo": {"@type": "ImageObject", "url": SITE + "/assets/images/logos/tk-icon-512.png"}}},
        {"@type": "BreadcrumbList", "itemListElement": [
            {"@type": "ListItem", "position": 1, "name": "Home", "item": SITE + "/"},
            {"@type": "ListItem", "position": 2, "name": "Blog", "item": SITE + "/pages/blog.html"},
            {"@type": "ListItem", "position": 3, "name": p['title'], "item": url}]}]}
    share_txt = html.escape(p['title'] + ' ' + url)
    from urllib.parse import quote
    rep = {
        'TITLE': html.escape(p['title']), 'DESC': html.escape(p['desc']), 'URL': url, 'LD': json.dumps(ld, indent=2, ensure_ascii=False),
        'CAT': p['cat'], 'CATSLUG': slug, 'COL': col, 'CATICON': icon, 'DATE': p['date'], 'DATESHOWN': p.get('updated') or p['date'], 'DATEF': ('Updated ' + fmt_date(p['updated'])) if p.get('updated') else fmt_date(p['date']),
        'MINS': str(p['mins']), 'BODY': body, 'TOC': tocs, 'RELATED': relh,
        'CTA_H': cta[0], 'CTA_P': cta[1], 'CTA_HREF': cta[2], 'CTA_BTN': cta[3],
        'WA_SHARE': 'https://wa.me/?text=' + quote(p['title'] + ' ' + url),
        'FB_SHARE': 'https://www.facebook.com/sharer/sharer.php?u=' + quote(url, safe=''),
        'IN_SHARE': 'https://www.linkedin.com/sharing/share-offsite/?url=' + quote(url, safe=''),
        'I_CLOCK': I_CLOCK, 'I_CAL': I_CAL, 'I_WA': I_WA, 'I_FB': I_FB, 'I_IN': I_IN, 'I_LINK': I_LINK, 'I_ARROW': I_ARROW,
        'COVER': cover(p, ' pcover--hero'),
    }
    t = POST_TPL
    for k, v in rep.items(): t = t.replace('@@%s@@' % k, v)
    if len(toc) < 2: t = re.sub(r'<nav class="toc".*?</nav>', '', t, flags=re.S)
    assert '@@' not in t, t[t.index('@@') - 40:t.index('@@') + 40]
    open('%s/pages/blog/%s.html' % (FE, p['slug']), 'w', encoding='utf8').write(t)

# ---------------------------------------------------------------- list page
LIST_TPL = open(os.path.join(HERE, 'templates', 'list.html'), encoding='utf8').read()
counts = {c: sum(1 for p in posts if p['cat'] == c) for c in ORDER}
chips = '<button class="topic" type="button" data-topic="all" aria-pressed="true">All <small>%d</small></button>' % len(posts)
chips += ''.join('<button class="topic topic--%s" type="button" data-topic="%s" aria-pressed="false">%s <small>%d</small></button>' % (CATS[c][1], CATS[c][0], html.escape(c), counts[c]) for c in ORDER if counts[c])
featured = by_slug['teaching-coding-ai-robotics-in-schools']
feat = ('<a class="feature-post reveal" href="blog/%s.html">%s<div class="feature-post__body"><p class="bcard__cat">%s · New</p><h2>%s</h2><p>%s</p>'
        '<p class="bcard__meta">%s %s · %s %s min read</p><span class="feature-post__more">Read the guide %s</span></div></a>') % (
    featured['slug'], cover(featured, ' pcover--feature'), featured['cat'], html.escape(featured['title']), html.escape(featured['desc']),
    I_CAL, fmt_date(featured['date']), I_CLOCK, featured['mins'], I_ARROW)
cards = ''
for i, p in enumerate(posts):
    slug, col, icon = CATS[p['cat']]
    text = (p['title'] + ' ' + p['desc'] + ' ' + p['cat']).lower()
    new = ' <span class="bcard__new">New</span>' if p['date'] >= '2026-01-01' else ''
    cards += ('        <li class="bcard reveal" data-delay="%d" data-post data-cat="%s" data-text="%s"><a class="bcard__link" href="blog/%s.html">%s'
              '<div class="bcard__body"><p class="bcard__cat">%s%s</p><h3>%s</h3><p class="bcard__desc">%s</p><p class="bcard__meta">%s · %s min read</p></div></a></li>\n') % (
        (i % 3) * 60, slug, html.escape(text, quote=True), p['slug'], cover(p), p['cat'], new, html.escape(p['title']), html.escape(p['desc']), ('Updated ' + fmt_date(p['updated'])) if p.get('updated') else fmt_date(p['date']), p['mins'])
ld = {"@context": "https://schema.org", "@type": "Blog", "name": "Technoknowledge Blog", "url": SITE + "/pages/blog.html",
      "publisher": {"@type": "Organization", "name": "Technoknowledge (SMC-Pvt) Ltd.", "url": SITE + "/"},
      "blogPost": [{"@type": "BlogPosting", "headline": p['title'], "datePublished": p['date'], "url": "%s/pages/blog/%s.html" % (SITE, p['slug'])} for p in posts]}
t = LIST_TPL
for k, v in {'LD': json.dumps(ld, indent=2, ensure_ascii=False), 'CHIPS': chips, 'FEATURED': feat, 'CARDS': cards, 'COUNT': str(len(posts)),
             'I_SEARCH': I_SEARCH, 'I_ARROW': I_ARROW}.items():
    t = t.replace('@@%s@@' % k, v)
assert '@@' not in t, t[t.index('@@') - 40:t.index('@@') + 40]
open(FE + '/pages/blog.html', 'w', encoding='utf8').write(t)

# redirect map for go-live (old WordPress URL -> new page)
rd = ['/%s/ -> /pages/blog/%s.html' % (p.get('old', p['slug']), p['slug']) for p in posts if p['date'] < '2026-01-01']
open(os.path.join(HERE, '..', 'docs', 'blog-redirects.txt'), 'w').write('\n'.join(rd) + '\n')
print(len(posts), 'posts;', counts)
