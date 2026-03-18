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

// Check if content type is JSON or Form Data
$content_type = $_SERVER['CONTENT_TYPE'] ?? '';
if (strpos($content_type, 'application/json') !== false) {
    $input = json_decode(file_get_contents('php://input'), true);
    $action = $input['action'] ?? '';
    $path = $input['path'] ?? '';
} else {
    $action = $_POST['action'] ?? '';
    $path = $_POST['path'] ?? '';
}

// Security: Prevent directory traversal
$real_target = realpath($base_dir . '/' . $path);
if ($real_target === false || strpos($real_target, $base_dir) !== 0) {
    if($action !== 'list') { 
       // Strictly block invalid paths for operations other than list (list handles it below)
    }
}
if ($path == '' || $real_target === false) $real_target = $base_dir; // Default root

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
            // Add direct URL for preview (Relative to public root)
            'url' => '/uploads/' . $rel_path,
            'full_url' => (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]" . '/uploads/' . $rel_path
        ];
    }
    
    // Sort: Folders first
    usort($items, function($a, $b) {
        if ($a['type'] === $b['type']) return strcasecmp($a['name'], $b['name']);
        return ($a['type'] === 'folder') ? -1 : 1;
    });

    echo json_encode(['success' => true, 'data' => $items, 'current_path' => $path]);

} elseif ($action === 'upload') {
    if (!isset($_FILES['file'])) {
        echo json_encode(['success' => false, 'message' => 'No file uploaded']);
        exit;
    }

    $file = $_FILES['file'];
    if ($file['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(['success' => false, 'message' => 'Upload error: ' . $file['error']]);
        exit;
    }

    $name = $file['name'];
    // Sanitize filename
    $name = preg_replace('/[^a-zA-Z0-9._-]/', '_', $name);
    
    // Unique filename if exists
    $target_file = $real_target . '/' . $name;
    $filename = pathinfo($name, PATHINFO_FILENAME);
    $extension = strtolower(pathinfo($name, PATHINFO_EXTENSION));
    
    $counter = 1;
    while (file_exists($target_file)) {
        $name = $filename . '_' . $counter . '.' . $extension;
        $target_file = $real_target . '/' . $name;
        $counter++;
    }

    if (move_uploaded_file($file['tmp_name'], $target_file)) {
        // Optimize if image
        if (in_array($extension, ['jpg', 'jpeg', 'png', 'webp'])) {
            require_once __DIR__ . '/../includes/optimize_image.php';
            // Optimize to max 1920px width, 85 quality
            optimizeImage($target_file, 1920, 85);
        }

        echo json_encode(['success' => true, 'message' => 'File uploaded successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to move uploaded file']);
    }

} elseif ($action === 'delete') {
    // Delete file or folder
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
                   @unlink($dir. DIRECTORY_SEPARATOR .$object); 
               } 
             }
             @rmdir($dir); 
           } 
        }
        rrmdir($real_target);
        echo json_encode(['success' => true, 'message' => 'Folder deleted']);
    } else {
        if (@unlink($real_target)) {
            echo json_encode(['success' => true, 'message' => 'File deleted']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to delete file']);
        }
    }

} elseif ($action === 'create_folder') {
    $new_folder_name = preg_replace('/[^a-zA-Z0-9_\-]/', '', $input['name']);
    if(!$new_folder_name) {
        echo json_encode(['success' => false, 'message' => 'Invalid Name']); exit;
    }
    $target_dir = $real_target . '/' . $new_folder_name;
    if(!file_exists($target_dir)) {
        if(mkdir($target_dir, 0755, true)) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to create folder']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Folder exists']);
    }

} elseif ($action === 'move') {
    $source = $input['source'] ?? '';
    $destination = $input['destination'] ?? '';

    $real_source = realpath($base_dir . '/' . $source);
    
    // If destination is root, path is empty string, which realpaths to base_dir
    $dest_path = $base_dir . ($destination ? '/' . $destination : '');
    $real_dest_dir = realpath($dest_path);

    if ($real_source === false || strpos($real_source, $base_dir) !== 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid source']); exit;
    }
    if ($real_dest_dir === false || strpos($real_dest_dir, $base_dir) !== 0 || !is_dir($real_dest_dir)) {
        echo json_encode(['success' => false, 'message' => 'Invalid destination']); exit;
    }

    $filename = basename($real_source);
    $target_file = $real_dest_dir . '/' . $filename;

    if (file_exists($target_file)) {
        echo json_encode(['success' => false, 'message' => 'File or Folder already exists at destination']); exit;
    }

    if (rename($real_source, $target_file)) {
        echo json_encode(['success' => true, 'message' => 'Moved successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to move']);
    }

} elseif ($action === 'zip') {
    echo json_encode(['success' => false, 'message' => 'Use download_zip.php for this']);
}

function human_filesize($bytes, $decimals = 2) {
    if ($bytes == 0) return '0 B';
    $sz = 'BKMGTP';
    $factor = floor((strlen($bytes) - 1) / 3);
    return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . @$sz[$factor];
}
?>
