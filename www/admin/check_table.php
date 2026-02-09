<?php
include('db_news.php');
$result = $conn->query("DESCRIBE attachments");
while($row = $result->fetch_assoc()) {
    print_r($row);
}
?>
