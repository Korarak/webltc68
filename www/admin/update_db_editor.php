<?php
include 'middleware.php';
include '../condb/condb.php';

// Add editor_json column
$sql = "SHOW COLUMNS FROM web_pages LIKE 'editor_json'";
$result = $mysqli4->query($sql);
if ($result->num_rows == 0) {
    if ($mysqli4->query("ALTER TABLE web_pages ADD COLUMN editor_json LONGTEXT")) {
        echo "Added editor_json column successfully.<br>";
    } else {
        echo "Error adding editor_json column: " . $mysqli4->error . "<br>";
    }
} else {
    echo "editor_json column already exists.<br>";
}
echo "Database update check complete.";
?>
