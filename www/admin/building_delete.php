<?php
include 'middleware.php';
require_once '../config.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $stmt = $mysqli->prepare("DELETE FROM buildings WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
}

header('Location: building_manage.php');
exit;
?>
