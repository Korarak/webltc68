<?php
include 'middleware.php';

$base_dir = realpath(__DIR__ . '/../uploads');
$path = $_GET['path'] ?? '';

// Security Check
$real_target = realpath($base_dir . '/' . $path);
if ($real_target === false || strpos($real_target, $base_dir) !== 0) {
    die("Invalid Path");
}

if (!is_dir($real_target)) {
    die("Target is not a directory");
}

$zipname = ($path ? basename($path) : 'uploads_backup') . '_' . date('Ymd_His') . '.zip';

// Initialize Archive object
$zip = new ZipArchive();
// Create a temp file
$tmp_file = tempnam(sys_get_temp_dir(), 'zip');
if ($zip->open($tmp_file, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== TRUE) {
    die("Cannot create zip");
}

// Create recursive directory iterator
$files = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($real_target),
    RecursiveIteratorIterator::LEAVES_ONLY
);

foreach ($files as $name => $file) {
    // Skip directories (they would be added automatically)
    if (!$file->isDir()) {
        // Get real and relative path for current file
        $filePath = $file->getRealPath();
        $relativePath = substr($filePath, strlen($real_target) + 1);

        // Add current file to archive
        $zip->addFile($filePath, $relativePath);
    }
}

$zip->close();

// Stream the file
header('Content-Type: application/zip');
header('Content-disposition: attachment; filename='.$zipname);
header('Content-Length: ' . filesize($tmp_file));
readfile($tmp_file);

// Remove temp file
unlink($tmp_file);
?>
