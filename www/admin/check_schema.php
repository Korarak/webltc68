<?php
include __DIR__ . '/../condb/condb.php';

echo "\n=== PERSONEL_DATA COLUMNS ===\n";
$cols = $mysqli3->query("SHOW COLUMNS FROM personel_data");
if ($cols) {
    while ($row = $cols->fetch_assoc()) {
        echo $row['Field'] . " - " . $row['Type'] . "\n";
    }
} else {
    echo "Error showing personel_data columns: " . $mysqli3->error;
}
?>
