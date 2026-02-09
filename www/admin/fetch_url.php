<?php
// www/admin/fetch_url.php

// Set headers for JSON response
header('Content-Type: application/json');

// Check if URL is provided
if (!isset($_GET['url'])) {
    echo json_encode(['success' => 0, 'error' => 'No URL provided']);
    exit;
}

$url = $_GET['url'];

// Validate URL
if (!filter_var($url, FILTER_VALIDATE_URL)) {
    echo json_encode(['success' => 0, 'error' => 'Invalid URL']);
    exit;
}

// Function to fetch content using cURL
function fetchUrlContent($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36');
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Ease SSL strictness for potential dev/local issues
    $data = curl_exec($ch);
    $error = curl_error($ch);
    curl_close($ch);

    if ($error) {
        return false;
    }
    return $data;
}

$html = fetchUrlContent($url);

if (!$html) {
    echo json_encode(['success' => 0, 'error' => 'Failed to fetch URL']);
    exit;
}

// Parse HTML
$doc = new DOMDocument();
@$doc->loadHTML($html); // Suppress warnings for malformed HTML

$xpath = new DOMXPath($doc);

// Helper to get meta content
function getMetaContent($xpath, $property) {
    $nodes = $xpath->query('//meta[@property="' . $property . '"]/@content');
    if ($nodes->length > 0) {
        return $nodes->item(0)->nodeValue;
    }
    // Try name attribute if property fails (e.g. description)
    $nodes = $xpath->query('//meta[@name="' . $property . '"]/@content');
    if ($nodes->length > 0) {
        return $nodes->item(0)->nodeValue;
    }
    return '';
}

// Extract Metadata
$title = '';
$description = '';
$image = '';

// Title
$titleNodes = $xpath->query('//meta[@property="og:title"]/@content');
if ($titleNodes->length > 0) {
    $title = $titleNodes->item(0)->nodeValue;
} else {
    $titleTags = $doc->getElementsByTagName('title');
    if ($titleTags->length > 0) {
        $title = $titleTags->item(0)->nodeValue;
    }
}

// Description
$description = getMetaContent($xpath, 'og:description');
if (!$description) {
    $description = getMetaContent($xpath, 'description');
}

// Image
$image = getMetaContent($xpath, 'og:image');

// Build Response
$response = [
    'success' => 1,
    'link' => [
        'title' => trim($title),
        'description' => trim($description),
        'image' => [
            'url' => trim($image)
        ],
        'site_name' => parse_url($url, PHP_URL_HOST)
    ]
];

echo json_encode($response);
