<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

$defaults = [
    'siteName' => 'JOBARN',
    'siteTagline' => 'Professional Tech, Ergonomics, Photo & Video Store Tanzania',
    'contactEmail' => 'info@jobarn.co.tz',
    'contactPhone' => '+255 716-026-781',
    'contactAddress' => "SkyCity Mall, 1st Floor\nOff Mlimani City, University Road\nDar es Salaam, Tanzania",
    'footerText' => '© 2026 Jobarn General Trading Company LTD, All Rights Reserved',
    'facebook' => '',
    'instagram' => '',
    'twitter' => '',
    'whatsapp' => '255716026781',
    'youtube' => '',
    'linkedin' => ''
];

$dataFile = __DIR__ . '/../data/settings.json';
if (!file_exists($dataFile)) {
    $dataFile = __DIR__ . '/../assets/ecommerce/settings.json';
}

$saved = [];
if (file_exists($dataFile)) {
    $decoded = @json_decode((string)@file_get_contents($dataFile), true);
    if (is_array($decoded)) {
        $saved = $decoded;
    }
}

echo json_encode(array_merge($defaults, $saved), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
exit;
