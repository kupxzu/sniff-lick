<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
try {
    require __DIR__ . '/../vendor/autoload.php';
    echo "autoload OK\n";
    $app = require __DIR__ . '/../bootstrap/app.php';
    echo "bootstrap OK\n";
} catch (Throwable $e) {
    http_response_code(500);
    echo "BOOT FAIL: " . $e->getMessage();
}