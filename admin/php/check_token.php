<?php
$tokenPath = __DIR__ . '/token.json';

if (file_exists($tokenPath)) {
    echo "✅ token.json exists at: " . realpath($tokenPath);
    echo "<pre>" . file_get_contents($tokenPath) . "</pre>";
} else {
    echo "❌ No token.json found here: " . $tokenPath;
}
