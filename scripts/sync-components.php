<?php
// =========================================================
// Technoknowledge: sync-components.php
// Copies the shared header and footer (frontend/components/)
// into every page, between these markers:
//   <!-- @header:start --> ... <!-- @header:end -->
//   <!-- @footer:start --> ... <!-- @footer:end -->
// =========================================================

$fe = dirname(__DIR__) . '/frontend';
$header = file_get_contents($fe . '/components/header.html');
$footer = file_get_contents($fe . '/components/footer.html');

function stripComment($html) {
    return preg_replace('/^<!--[\s\S]*?-->\s*/', '', $html);
}

function render($partial, $root, $page) {
    $html = str_replace('{{root}}', $root, stripComment($partial));
    // mark links for this page as current
    $html = str_replace("data-page=\"{$page}\"", "data-page=\"{$page}\" aria-current=\"page\"", $html);
    // mark top-level menu group that contains this page
    $html = preg_replace_callback('/class="nav__item" data-pages="([^"]*)"/', function ($m) use ($page) {
        $list = preg_split('/\s+/', $m[1]);
        return in_array($page, $list, true) ? "class=\"nav__item is-current\" data-pages=\"{$m[1]}\"" : $m[0];
    }, $html);
    return trim($html);
}

function inject($file, $root, $pageName = null) {
    global $header, $footer, $fe;
    $page = $pageName ?: basename($file, '.html');
    $html = file_get_contents($file);
    $changed = false;

    foreach (['header' => $header, 'footer' => $footer] as $name => $partial) {
        $re = '/(<!-- @' . $name . ':start -->)[\s\S]*?(<!-- @' . $name . ':end -->)/';
        if (preg_match($re, $html)) {
            $replacement = '$1' . "\n" . render($partial, $root, $page) . "\n" . '$2';
            $html = preg_replace($re, $replacement, $html);
            $changed = true;
        }
    }

    if ($changed) {
        file_put_contents($file, $html);
        echo "Updated: " . str_replace($fe, '', $file) . "\n";
    }
}

// 1. Home and root level pages
foreach (scandir($fe) as $f) {
    if (str_ends_with($f, '.html')) {
        inject($fe . '/' . $f, '');
    }
}

// 2. Inner pages in frontend/pages/
$pagesDir = $fe . '/pages';
if (is_dir($pagesDir)) {
    foreach (scandir($pagesDir) as $f) {
        if (str_ends_with($f, '.html')) {
            inject($pagesDir . '/' . $f, '../');
        }
    }
}

// 3. Blog articles in frontend/pages/blog/
$blogDir = $pagesDir . '/blog';
if (is_dir($blogDir)) {
    foreach (scandir($blogDir) as $f) {
        if (str_ends_with($f, '.html')) {
            inject($blogDir . '/' . $f, '../../', 'blog');
        }
    }
}

echo "All components synced successfully.\n";
