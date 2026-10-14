<?php
/**
 * Technoknowledge - Hostinger Deployment Packager
 * Packages frontend root files and backend into a single zip ready for public_html.
 */

$root = dirname(__DIR__);
$zipFile = $root . '/hostinger_public_html_ready.zip';

if (file_exists($zipFile)) {
    @unlink($zipFile);
}

echo "Packaging website for Hostinger...\n";

$zip = new ZipArchive();
if ($zip->open($zipFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
    die("Failed to create ZIP file.\n");
}

// 1. Add root server files
if (file_exists($root . '/.htaccess')) {
    $zip->addFile($root . '/.htaccess', '.htaccess');
}
if (file_exists($root . '/index.php')) {
    $zip->addFile($root . '/index.php', 'index.php');
}
if (file_exists($root . '/blog/index.php')) {
    $zip->addFile($root . '/blog/index.php', 'blog/index.php');
}
echo "- Added root .htaccess, index.php, and blog/index.php\n";

// 2. Add all frontend files
$frontendDir = $root . '/frontend';
$files = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($frontendDir, RecursiveDirectoryIterator::SKIP_DOTS),
    RecursiveIteratorIterator::SELF_FIRST
);

foreach ($files as $file) {
    $filePath = $file->getRealPath();
    $relativePath = 'frontend/' . substr($filePath, strlen($frontendDir) + 1);
    $relativePath = str_replace('\\', '/', $relativePath);

    if ($file->isDir()) {
        $zip->addEmptyDir($relativePath);
    } else {
        $zip->addFile($filePath, $relativePath);
    }
}
echo "- Added frontend files to root of ZIP\n";

// 2. Add backend folder
$backendDir = $root . '/backend';
$backendFiles = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($backendDir, RecursiveDirectoryIterator::SKIP_DOTS),
    RecursiveIteratorIterator::SELF_FIRST
);

foreach ($backendFiles as $file) {
    $filePath = $file->getRealPath();
    $relativePath = 'backend/' . substr($filePath, strlen($backendDir) + 1);
    $relativePath = str_replace('\\', '/', $relativePath);

    // Skip local environment files and local sqlite database
    if (str_ends_with($relativePath, '/.env') || str_ends_with($relativePath, '.sqlite') || str_contains($relativePath, '/tests/')) {
        continue;
    }

    if ($file->isDir()) {
        $zip->addEmptyDir($relativePath);
    } else {
        $zip->addFile($filePath, $relativePath);
    }
}
echo "- Added backend files to /backend in ZIP\n";

$zip->close();
echo "Done! Generated: " . basename($zipFile) . " (" . round(filesize($zipFile) / (1024 * 1024), 2) . " MB)\n";
