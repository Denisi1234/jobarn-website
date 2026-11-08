<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

$dataFile = __DIR__ . '/../data/slides.json';
if (!file_exists($dataFile)) {
    $dataFile = __DIR__ . '/../assets/ecommerce/slides.json';
}

$slides = [];
if (file_exists($dataFile)) {
    $decoded = @json_decode((string)@file_get_contents($dataFile), true);
    if (is_array($decoded)) {
        $slides = $decoded;
    }
}

usort($slides, function ($a, $b) {
    return (int)($a['order'] ?? 0) - (int)($b['order'] ?? 0);
});

echo json_encode(array_values($slides), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
exit;
