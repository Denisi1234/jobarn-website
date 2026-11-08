<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

$dataFile = __DIR__ . '/../data/products.json';

if (!file_exists($dataFile)) {
    // Fallback to assets if data/products.json is missing
    $dataFile = __DIR__ . '/../assets/ecommerce/products.json';
}

if (file_exists($dataFile)) {
    echo file_get_contents($dataFile);
} else {
    echo json_encode([]);
}
exit;
