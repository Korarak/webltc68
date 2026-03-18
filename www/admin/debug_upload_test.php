<?php
// Standalone debug script for News Update & Upload logic
// Bypassing middleware.php for testing core logic

include 'db_news.php'; // Ensure this path is correct relative to where we run this

// Hardcoded ID for testing (Change to an existing ID)
$news_id = 1; 

echo "--- Starting Debug Test for News ID: $news_id ---\n";

// Mock Data
$title = "Debug Test Title " . date('H:i:s');
$content = "<p>Debug Content</p>";
$category_id = 0; // Test changing directly to 0

echo "Updating News...\n";
$stmt = $conn->prepare("UPDATE news SET title=?, content=?, category_id=? WHERE id=?");
if (!$stmt) die("Prepare failed: " . $conn->error);
$stmt->bind_param("ssii", $title, $content, $category_id, $news_id);

if($stmt->execute()) {
    echo "News Update Success.\n";
} else {
    echo "News Update Failed: " . $stmt->error . "\n";
}

// Mock Upload
echo "Testing File Upload Logic...\n";
// Create a dummy file to simulate upload
$dummy_file = 'test_upload_' . time() . '.txt';
file_put_contents($dummy_file, 'This is a test file content.');

// We can't use move_uploaded_file with local files not uploaded via HTTP.
// So we will simulate the logic with `rename` or `copy` instead of `move_uploaded_file`
// JUST for this test to see if permissions/path logic holds.

$sub_path = "news/" . date('Y') . "/" . date('m') . "/";
$target_dir = "../uploads/" . $sub_path; // Relative to admin/
echo "Target Dir: $target_dir\n";

if (!is_dir($target_dir)) {
    echo "Directory does not exist. Attempting to create...\n";
    if (mkdir($target_dir, 0755, true)) {
        echo "Directory created.\n";
    } else {
        echo "Failed to create directory. Check permissions.\n";
        // Check current user
        echo "Current User: " . get_current_user() . "\n";
        echo "Effective User ID: " . posix_geteuid() . "\n";
    }
} else {
    echo "Directory exists.\n";
}

$new_filename = uniqid() . '.txt';
$target_file = $target_dir . $new_filename;

echo "Attempting to copy dummy file to: $target_file\n";
if (copy($dummy_file, $target_file)) {
    echo "File Copy Success!\n";
    
    // DB Insert
    $f_type = 'text/plain';
    $f_size = 123;
    $db_path = "uploads/" . $sub_path . $new_filename;
    
    $conn->query("INSERT INTO attachments (news_id, file_name, file_path, file_type, file_size) VALUES ($news_id, '$dummy_file', '$db_path', '$f_type', $f_size)");
    echo "DB Insert Attachment Success.\n";
    
    // Cleanup
    unlink($target_file);
} else {
    echo "File Copy Failed.\n";
    echo "Error: " . print_r(error_get_last(), true) . "\n";
}

unlink($dummy_file);
echo "--- Test Complete ---\n";
?>
