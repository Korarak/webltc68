<?php
require 'condb/condb.php';
$result = $mysqli3->query("SELECT id, fullname, profile_image FROM personel_data LIMIT 10");
while ($row = $result->fetch_assoc()) {
    echo "ID: " . $row['id'] . " | Name: " . $row['fullname'] . " | Image: " . $row['profile_image'] . "\n";
}
?>
