<?php
include __DIR__ . '/../condb/condb.php';

// Check if column exists
$check = $mysqli3->query("SHOW COLUMNS FROM personel_data LIKE 'education_detail'");
if ($check->num_rows == 0) {
    // Add column
    $sql = "ALTER TABLE personel_data ADD COLUMN education_detail VARCHAR(255) DEFAULT NULL AFTER education_level_id";
    if ($mysqli3->query($sql)) {
        echo "Column 'education_detail' added successfully.";
    } else {
        echo "Error adding column: " . $mysqli3->error;
    }
} else {
    echo "Column 'education_detail' already exists.";
}
?>
