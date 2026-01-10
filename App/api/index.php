<?php

// Force error reporting immediately
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Check if public index exists
$publicIndex = __DIR__ . '/../public/index.php';
if (!file_exists($publicIndex)) {
    die("Error: public/index.php not found at: " . $publicIndex);
}

// Check if vendor exists (did composer run?)
$vendor = __DIR__ . '/../vendor/autoload.php';
if (!file_exists($vendor)) {
    die("Error: vendor/autoload.php not found. Composer install failed.");
}

// Forward to Laravel
require $publicIndex;