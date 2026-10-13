# pages/

One HTML file per page.

**Built:** itec-curriculum.html · aitep.html · robotics-iot.html · monitoring-evaluation.html ·
books.html · lms.html · techno-camps.html · request-proposal.html · contact.html · about.html · impact.html · blog.html

**Also built:** privacy-policy.html · ../404.html (the "page not found" page, at the site root)

Every page: one `<h1>`, unique `<title>` and meta description (see `docs/deployment.md`, SEO).

## Blog posts

`pages/blog/<slug>.html`, one file per post. Posts are written in Markdown and built into HTML
(see `blog/README.md` in the project folder). Old WordPress addresses and their new pages are listed in
`docs/blog-redirects.txt`: add these as 301 redirects at go-live.
