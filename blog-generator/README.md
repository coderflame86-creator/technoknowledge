# blog/

The blog is written in Markdown and built into plain HTML pages.

| Folder / file | What it is |
|---|---|
| `posts/` | One `.md` file per post. The file name is the web address, e.g. `teach-coding-to-kids.md` → `pages/blog/teach-coding-to-kids.html` |
| `templates/` | The HTML design for the list page and for a post page |
| `build.py` | Builds `frontend/pages/blog.html` and every post page |

## Add a new post

1. Copy any file in `posts/` and rename it, using lowercase words with dashes (this becomes the web address).
2. Edit the top section:

```
title: Your post title
date: 2026-11-15
desc: One or two sentences for Google and the post card (about 150 characters).
cat: For schools
---
Your post text in Markdown...
```

   `cat` must be one of: For schools · For teachers · Parents · Getting started · Coding & games · Future skills · Digital creativity

3. Write the post below the `---` line. Use `## Heading` for sections (they appear in "On this page"), `- ` for bullet points, and `**bold**`.
4. Link to our pages with these short codes: `[Techno Camps](TK:camps)`, `(TK:itec)`, `(TK:books)`, `(TK:lms)`, `(TK:aitep)`, `(TK:robotics)`, `(TK:mne)`, `(TK:proposal)`, `(TK:contact)`. Link to another post with `(TK:post:file-name)`.
5. From the project folder run:

```
pip install markdown          (first time only)
python blog/build.py
node scripts/sync-components.mjs
```

6. Upload the changed files in `frontend/pages/` to the website.

## Rules

- Facts about Technoknowledge (figures, prices, hours) must match `data/`.
- No emoji in posts. No made-up statistics or quotes.
- Posts from the old website keep their content. Their old addresses are in `docs/blog-redirects.txt` and must be set up as 301 redirects at go-live.
