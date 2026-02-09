<?php
include('db_news.php');

$sql = "ALTER TABLE attachments ADD COLUMN sort_order INT DEFAULT 0";

if ($conn->query($sql) === TRUE) {
    echo "Column 'sort_order' added successfully";
} else {
    echo "Error adding column: " . $conn->error;
}

$conn->close();
?>
