<?php
include 'middleware.php';
include '../condb/condb.php';   

if (isset($_GET['id'])) {
    $menu_id = $_GET['id'];

    // Delete menu and associated sub-menus
    $stmt = $mysqli4->prepare("DELETE FROM menus WHERE menu_id = ?");
    $stmt->bind_param("i", $menu_id);
    $stmt->execute();
    $stmt->close();

    header("Location: topmenu_manage.php");
}
?>
