<?php
/**
 * If Apache routes /blog/ directly into this physical directory,
 * serve the blog main page.
 */
$blogFile = __DIR__ . '/../frontend/pages/blog.html';
if (file_exists($blogFile)) {
    readfile($blogFile);
    exit;
}

http_response_code(404);
echo "404 Not Found";
