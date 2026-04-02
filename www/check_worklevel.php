<?php
require 'condb/condb.php';
$res = $mysqli3->query("SELECT * FROM worklevel");
while($row = $res->fetch_assoc()) {
    echo $row['id'] . ": " . $row['work_level_name'] . "\n";
}
?>
