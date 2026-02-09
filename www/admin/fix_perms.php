<?php
// Fix permissions for uploads directory
$dir = __DIR__ . '/../uploads/ltc_personal/';
if (is_dir($dir)) {
    echo "Directory found: $dir\n";
    if (chmod($dir, 0755)) {
        echo "Directory chmod 0755 success.\n";
    } else {
        echo "Directory chmod failed.\n";
    }
    
    $files = glob($dir . '*');
    foreach ($files as $file) {
        if (is_file($file)) {
            if (chmod($file, 0644)) {
                // Success
            } else {
                echo "Failed to chmod file: " . basename($file) . "\n";
            }
        }
    }
    echo "Permissions fixed.";
} else {
    echo "Directory not found.";
}
?>
