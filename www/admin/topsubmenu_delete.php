<?php
include 'middleware.php';
include '../condb/condb.php';   

if (isset($_GET['id'])) {
    $submenu_id = $_GET['id'];

    // Delete sub-menu
    $stmt = $mysqli4->prepare("DELETE FROM sub_menus WHERE submenu_id = ?");
    $stmt->bind_param("i", $submenu_id);
    $stmt->execute();
    $stmt->close();

    header("Location: topmenu_manage.php");
}
?>
