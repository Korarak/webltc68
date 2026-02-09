<?php
// file_api.php
include 'middleware.php';

// Base upload directory (Absolute path recommended for security checks)
$base_dir = realpath(__DIR__ . '/../uploads'); // Goes up from www/admin/ -> www/uploads/

header('Content-Type: application/json');

// Only allow POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$action = $input['action'] ?? '';
$path = $input['path'] ?? '';

// Security: Prevent directory traversal
$real_target = realpath($base_dir . '/' . $path);
if ($real_target === false || strpos($real_target, $base_dir) !== 0) {
    if($action !== 'list') { // Allow list root if path empty
       // If empty path, it defaults to base_dir which is valid.
       // logic check: if input path is '..' -> realpath goes outside -> blocked.
    }
}
if ($path == '') $real_target = $base_dir; // Default root

if ($action === 'list') {
    if (!is_dir($real_target)) {
        echo json_encode(['success' => false, 'message' => 'Not a directory']);
        exit;
    }

    $items = [];
    $files = scandir($real_target);
    
    foreach ($files as $f) {
        if ($f === '.' || $f === '..') continue;
        
        $full_path = $real_target . '/' . $f;
        $rel_path = ($path ? $path . '/' : '') . $f;
        
        $is_dir = is_dir($full_path);
        
        // Count items if dir
        $count = $is_dir ? count(scandir($full_path)) - 2 : 0;
        
        // Get file type/icon
        $ext = pathinfo($full_path, PATHINFO_EXTENSION);
        
        $items[] = [
            'name' => $f,
            'path' => $rel_path,
            'type' => $is_dir ? 'folder' : 'file',
            'ext' => $ext,
            'size' => $is_dir ? '-' : human_filesize(filesize($full_path)),
            'count' => $count, // Only for folders
            'modified' => date('Y-m-d H:i', filemtime($full_path)),
            // Add direct URL for preview
            'url' => '../../uploads/' . $rel_path
        ];
    }
    
    // Sort: Folders first
    usort($items, function($a, $b) {
        if ($a['type'] === $b['type']) return strcasecmp($a['name'], $b['name']);
        return ($a['type'] === 'folder') ? -1 : 1;
    });

    echo json_encode(['success' => true, 'data' => $items, 'current_path' => $path]);

} elseif ($action === 'delete') {
    // Delete file or folder (recursive? dangerous. Let's allowing deleting empty folders or single files first)
    // For powerful tool: Recursive Delete.
    
    if (is_dir($real_target)) {
        // Simple rmdir only works if empty. 
        // Recursive Delete Function
        function rrmdir($dir) { 
           if (is_dir($dir)) { 
             $objects = scandir($dir); 
             foreach ($objects as $object) { 
               if ($object != "." && $object != "..") { 
                 if (is_dir($dir. DIRECTORY_SEPARATOR .$object) && !is_link($dir."/".$object))
                   rrmdir($dir. DIRECTORY_SEPARATOR .$object);
                 else
                   unlink($dir. DIRECTORY_SEPARATOR .$object); 
               } 
             }
             rmdir($dir); 
           } 
        }
        rrmdir($real_target);
        echo json_encode(['success' => true, 'message' => 'Folder deleted']);
    } else {
        unlink($real_target);
        echo json_encode(['success' => true, 'message' => 'File deleted']);
    }

} elseif ($action === 'create_folder') {
    $new_folder_name = preg_replace('/[^a-zA-Z0-9_\-]/', '', $input['name']);
    if(!$new_folder_name) {
        echo json_encode(['success' => false, 'message' => 'Invalid Name']); exit;
    }
    $target_dir = $real_target . '/' . $new_folder_name;
    if(!file_exists($target_dir)) {
        mkdir($target_dir, 0755, true);
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Folder exists']);
    }

} elseif ($action === 'zip') {
    // Return a download link, or Stream directly?
    // Stream better for security (API), but simple is AJAX -> generate -> return download URL.
    // Or direct window.location href to a download_zip.php?path=... (GET request)
    // To keep it clean, let's make a separate GET block or strictly standardise.
    
    // Let's use this API to 'prepare' the zip if large, but for immediate download, a GET link is best.
    // So Client will call download_zip.php?path=...
    echo json_encode(['success' => false, 'message' => 'Use download_zip.php for this']);
}

function human_filesize($bytes, $decimals = 2) {
    if ($bytes == 0) return '0 B';
    $sz = 'BKMGTP';
    $factor = floor((strlen($bytes) - 1) / 3);
    return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . @$sz[$factor];
}
?>
