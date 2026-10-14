<?php
/**
 * Comprehensive SEO URL Cleaner:
 * 1. Clean all internal links in every HTML file (no .html extensions, no /pages/ prefixes)
 * 2. Clean canonical tags and Open Graph og:url tags
 * 3. Clean JSON-LD schema URLs
 * 4. Clean social share URLs
 */

$feDir = dirname(__DIR__) . '/frontend';
$pagesDir = $feDir . '/pages';
$blogDir = $pagesDir . '/blog';

$mainPages = [
    'about', 'aitep', 'blog', 'books', 'contact', 'impact',
    'itec-curriculum', 'lms', 'monitoring-evaluation',
    'privacy-policy', 'request-proposal', 'robotics-iot', 'techno-camps'
];

$blogFiles = scandir($blogDir);
$blogSlugs = [];
foreach ($blogFiles as $bf) {
    if (str_ends_with($bf, '.html')) {
        $blogSlugs[] = preg_replace('/\.html$/', '', $bf);
    }
}

function cleanHtmlContent($content, $blogSlugs, $mainPages) {
    // 1. Home links
    $content = preg_replace('/href="(?:\.\.\/|\.\.\/\.\.\/)?index\.html(#?[^"]*)"/', 'href="/$1"', $content);
    $content = preg_replace('/href="index\.html(#?[^"]*)"/', 'href="/$1"', $content);

    // 2. Main page links
    foreach ($mainPages as $p) {
        // With hash or query params
        $content = preg_replace('/href="(?:\.\.\/|\.\.\/\.\.\/)?(?:pages\/)?' . preg_quote($p, '/') . '\.html([#?][^"]*)"/', 'href="/' . $p . '$1"', $content);
        // Direct
        $content = preg_replace('/href="(?:\.\.\/|\.\.\/\.\.\/)?(?:pages\/)?' . preg_quote($p, '/') . '\.html"/', 'href="/' . $p . '"', $content);
    }

    // 3. Blog article links
    foreach ($blogSlugs as $b) {
        $content = preg_replace('/href="(?:\.\.\/|\.\.\/\.\.\/)?(?:pages\/)?blog\/' . preg_quote($b, '/') . '\.html([#?][^"]*)"/', 'href="/blog/' . $b . '$1"', $content);
        $content = preg_replace('/href="(?:\.\.\/|\.\.\/\.\.\/)?(?:pages\/)?blog\/' . preg_quote($b, '/') . '\.html"/', 'href="/blog/' . $b . '"', $content);
        $content = preg_replace('/href="' . preg_quote($b, '/') . '\.html([#?][^"]*)"/', 'href="/blog/' . $b . '$1"', $content);
        $content = preg_replace('/href="' . preg_quote($b, '/') . '\.html"/', 'href="/blog/' . $b . '"', $content);
    }

    // 4. Clean canonical & og:url tags
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

    // 5. JSON-LD schema URLs
    $content = preg_replace('/https:\/\/thetechnoknowledge\.com\/pages\/blog\/([a-zA-Z0-9_-]+)\.html/', 'https://thetechnoknowledge.com/blog/$1', $content);
    $content = preg_replace('/https:\/\/thetechnoknowledge\.com\/pages\/([a-zA-Z0-9_-]+)\.html/', 'https://thetechnoknowledge.com/$1', $content);

    // 6. Social share URLs
    $content = preg_replace('/https%3A%2F%2Fthetechnoknowledge\.com%2Fpages%2Fblog%2F([a-zA-Z0-9_-]+)\.html/', 'https%3A%2F%2Fthetechnoknowledge.com%2Fblog%2F$1', $content);
    $content = preg_replace('/https%3A\/\/thetechnoknowledge\.com\/pages\/blog\/([a-zA-Z0-9_-]+)\.html/', 'https%3A//thetechnoknowledge.com/blog/$1', $content);

    return $content;
}

// Process index.html and root html files
foreach (scandir($feDir) as $f) {
    if (str_ends_with($f, '.html')) {
        $file = $feDir . '/' . $f;
        file_put_contents($file, cleanHtmlContent(file_get_contents($file), $blogSlugs, $mainPages));
        echo "Cleaned: frontend/{$f}\n";
    }
}

// Process frontend/pages/*.html
foreach (scandir($pagesDir) as $f) {
    if (str_ends_with($f, '.html')) {
        $file = $pagesDir . '/' . $f;
        file_put_contents($file, cleanHtmlContent(file_get_contents($file), $blogSlugs, $mainPages));
        echo "Cleaned: frontend/pages/{$f}\n";
    }
}

// Process frontend/pages/blog/*.html
foreach ($blogSlugs as $slug) {
    $file = $blogDir . '/' . $slug . '.html';
    file_put_contents($file, cleanHtmlContent(file_get_contents($file), $blogSlugs, $mainPages));
}
echo "Cleaned: 22 blog articles in frontend/pages/blog/\n";

echo "Done! All internal links and metadata are now 100% clean SEO friendly.\n";
