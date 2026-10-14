<?php
/**
 * Root fallback: serve the frontend home page.
 */
$indexFile = __DIR__ . '/frontend/index.html';
if (file_exists($indexFile)) {
    readfile($indexFile);
    exit;
}

http_response_code(404);
echo "404 Not Found";
