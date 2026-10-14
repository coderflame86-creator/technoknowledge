<?php
// =========================================================
// Technoknowledge: make-sitemap.php
// Rebuilds frontend/sitemap.xml with clean SEO-friendly URLs.
// Run: php scripts/make-sitemap.php
// =========================================================

$fe = dirname(__DIR__) . '/frontend/';
$site = 'https://thetechnoknowledge.com/';
$skip = ['404.html' => true, 'style-guide.html' => true];

$urls = [];
$urls[] = [
    'loc' => $site,
    'lastmod' => date('Y-m-d', filemtime($fe . 'index.html')),
    'priority' => '1.0'
];

$pages = scandir($fe . 'pages');
sort($pages);
foreach ($pages as $f) {
    if (str_ends_with($f, '.html') && !isset($skip[$f])) {
        $slug = preg_replace('/\.html$/', '', $f);
        $urls[] = [
            'loc' => $site . $slug,
            'lastmod' => date('Y-m-d', filemtime($fe . 'pages/' . $f)),
            'priority' => '0.8'
        ];
    }
}

$blogDir = $fe . 'pages/blog';
if (is_dir($blogDir)) {
    $blogs = scandir($blogDir);
    sort($blogs);
    foreach ($blogs as $b) {
        if (str_ends_with($b, '.html')) {
            $slug = preg_replace('/\.html$/', '', $b);
            $urls[] = [
                'loc' => $site . 'blog/' . $slug,
                'lastmod' => date('Y-m-d', filemtime($blogDir . '/' . $b)),
                'priority' => '0.6'
            ];
        }
    }
}

$xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
$xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
foreach ($urls as $u) {
    $xml .= "  <url><loc>{$u['loc']}</loc><lastmod>{$u['lastmod']}</lastmod><priority>{$u['priority']}</priority></url>\n";
}
$xml .= "</urlset>\n";

file_put_contents($fe . 'sitemap.xml', $xml);
echo "Updated frontend/sitemap.xml with " . count($urls) . " clean SEO URLs.\n";
