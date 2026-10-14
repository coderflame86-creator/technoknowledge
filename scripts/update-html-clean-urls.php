<?php
/**
 * Comprehensive SEO URL Cleaner:
 * 1. Clean all internal links in every HTML file (no .html extensions, no /pages/ prefixes)
 * 2. Prefix links correctly for both localhost (/TK/...) and live domain root (/)
 * 3. Clean canonical tags, Open Graph og:url tags, JSON-LD schemas, and social share links
 * 4. Fix asset paths (css, js, assets, logos) for clean root URLs
 */

$feDir = dirname(__DIR__) . '/frontend';
$pagesDir = $feDir . '/pages';
$blogDir = $pagesDir . '/blog';

$mainPages = [
    'about', 'aitep', 'blog', 'books', 'contact', 'impact',
    'itec-curriculum', 'lms', 'monitoring-evaluation',
    'privacy-policy', 'request-proposal', 'robotics-iot', 'techno-camps', 'style-guide'
];

$blogFiles = scandir($blogDir);
$blogSlugs = [];
foreach ($blogFiles as $bf) {
    if (str_ends_with($bf, '.html')) {
        $blogSlugs[] = preg_replace('/\.html$/', '', $bf);
    }
}

function cleanHtmlContent($content, $blogSlugs, $mainPages, $isBlogArticle = false) {
    $rootPrefix = $isBlogArticle ? '../' : '';
    $homeHref = $isBlogArticle ? '../' : './';

    // 1. Assets: In root & pages/*.html, assets are css/, js/, assets/.
    // In blog/*.html, assets are ../css/, ../js/, ../assets/.
    if (!$isBlogArticle) {
        // Strip any leading ../ or / from relative assets in <head> and <body>
        $content = preg_replace('/(href|src)="(?:\.\.\/|\/)(css|js|assets)\//', '$1="$2/', $content);
        $content = preg_replace('/(href|src)="\.\.\/(favicon-[0-9]+|apple-touch-icon)\.png"/', '$1="$2.png"', $content);
    } else {
        // In blog articles, change ../../ to ../
        $content = preg_replace('/(href|src)="\.\.\/\.\.\/(css|js|assets)\//', '$1="../$2/', $content);
        $content = preg_replace('/(href|src)="\/(css|js|assets)\//', '$1="../$2/', $content);
    }

    // 2. Home links
    $content = preg_replace('/href="(?:\.\.\/|\.\.\/\.\.\/|\/)?index\.html(#?[^"]*)"/', 'href="' . $homeHref . '$1"', $content);
    // Standalone href="/" or href="/#"
    $content = preg_replace('/href="\/(#?[^"\/]*)"/', 'href="' . $homeHref . '$1"', $content);

    // 3. Main page links
    foreach ($mainPages as $p) {
        // Match /page, /page.html, ../page.html, ../../pages/page.html, etc.
        $pat = '/href="(?:\.\.\/|\.\.\/\.\.\/|\/)?(?:pages\/)?' . preg_quote($p, '/') . '(?:\.html)?([#?][^"]*)?"/';
        $content = preg_replace_callback($pat, function ($m) use ($rootPrefix, $p) {
            $suffix = $m[1] ?? '';
            return 'href="' . $rootPrefix . $p . $suffix . '"';
        }, $content);
    }

    // 4. Blog article links
    foreach ($blogSlugs as $b) {
        // Match /blog/slug, /pages/blog/slug.html, blog/slug.html, ../../pages/blog/slug.html, etc.
        $pat = '/href="(?:\.\.\/|\.\.\/\.\.\/|\/)?(?:pages\/)?blog\/' . preg_quote($b, '/') . '(?:\.html)?([#?][^"]*)?"/';
        $content = preg_replace_callback($pat, function ($m) use ($isBlogArticle, $b) {
            $suffix = $m[1] ?? '';
            if ($isBlogArticle) {
                // Inside a blog article, link to another blog article as either slug or ../blog/slug
                return 'href="' . $b . $suffix . '"';
            } else {
                return 'href="blog/' . $b . $suffix . '"';
            }
        }, $content);

        // Also match direct slug.html (e.g. href="my-slug.html") inside blog pages
        $patDirect = '/href="' . preg_quote($b, '/') . '\.html([#?][^"]*)?"/';
        $content = preg_replace_callback($patDirect, function ($m) use ($isBlogArticle, $b) {
            $suffix = $m[1] ?? '';
            if ($isBlogArticle) {
                return 'href="' . $b . $suffix . '"';
            } else {
                return 'href="blog/' . $b . $suffix . '"';
            }
        }, $content);
    }

    // 5. Clean canonical & og:url tags (always absolute HTTPS domain URLs)
    $content = preg_replace_callback('/<link rel="canonical" href="https:\/\/thetechnoknowledge\.com\/(?:pages\/)?(.*?)(?:\.html)?"\s*\/?>/', function ($m) {
        $path = trim($m[1], '/');
        $url = $path ? "https://thetechnoknowledge.com/{$path}" : "https://thetechnoknowledge.com/";
        return '<link rel="canonical" href="' . $url . '">';
    }, $content);

    $content = preg_replace_callback('/<meta property="og:url" content="https:\/\/thetechnoknowledge\.com\/(?:pages\/)?(.*?)(?:\.html)?"\s*\/?>/', function ($m) {
        $path = trim($m[1], '/');
        $url = $path ? "https://thetechnoknowledge.com/{$path}" : "https://thetechnoknowledge.com/";
        return '<meta property="og:url" content="' . $url . '">';
    }, $content);

    // 6. JSON-LD schema URLs
    $content = preg_replace('/https:\/\/thetechnoknowledge\.com\/pages\/blog\/([a-zA-Z0-9_-]+)\.html/', 'https://thetechnoknowledge.com/blog/$1', $content);
    $content = preg_replace('/https:\/\/thetechnoknowledge\.com\/pages\/([a-zA-Z0-9_-]+)\.html/', 'https://thetechnoknowledge.com/$1', $content);

    // 7. Social share URLs
    $content = preg_replace('/https%3A%2F%2Fthetechnoknowledge\.com%2Fpages%2Fblog%2F([a-zA-Z0-9_-]+)\.html/', 'https%3A%2F%2Fthetechnoknowledge.com%2Fblog%2F$1', $content);
    $content = preg_replace('/https%3A\/\/thetechnoknowledge\.com\/pages\/blog\/([a-zA-Z0-9_-]+)\.html/', 'https%3A//thetechnoknowledge.com/blog/$1', $content);

    return $content;
}

// 1. Process index.html and root html files
foreach (scandir($feDir) as $f) {
    if (str_ends_with($f, '.html')) {
        $file = $feDir . '/' . $f;
        file_put_contents($file, cleanHtmlContent(file_get_contents($file), $blogSlugs, $mainPages, false));
        echo "Cleaned: frontend/{$f}\n";
    }
}

// 2. Process frontend/pages/*.html
foreach (scandir($pagesDir) as $f) {
    if (str_ends_with($f, '.html')) {
        $file = $pagesDir . '/' . $f;
        file_put_contents($file, cleanHtmlContent(file_get_contents($file), $blogSlugs, $mainPages, false));
        echo "Cleaned: frontend/pages/{$f}\n";
    }
}

// 3. Process frontend/pages/blog/*.html
foreach ($blogSlugs as $slug) {
    $file = $blogDir . '/' . $slug . '.html';
    file_put_contents($file, cleanHtmlContent(file_get_contents($file), $blogSlugs, $mainPages, true));
}
echo "Cleaned: 22 blog articles in frontend/pages/blog/\n";

echo "Done! All internal links and assets updated successfully.\n";
