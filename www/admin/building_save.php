<?php
include 'middleware.php';
require_once '../config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? '';
    $name = $_POST['name'] ?? '';
    $responsible = $_POST['responsible'] ?? '';
    $capacity = $_POST['capacity'] ?? '';
    $equipment = $_POST['equipment'] ?? '';
    $description = $_POST['description'] ?? '';
    
    // Default color and mock coordinates
    $color = '#cccccc';
    
    if ($id) {
        // Update
        $stmt = $mysqli->prepare("UPDATE buildings SET name=?, responsible=?, capacity=?, equipment=?, description=? WHERE id=?");
        $stmt->bind_param("sssssi", $name, $responsible, $capacity, $equipment, $description, $id);
        $stmt->execute();
    } else {
        // Insert
        $x = 0; $y = 0; $w = 100; $h = 100;
        $stmt = $mysqli->prepare("INSERT INTO buildings (name, responsible, capacity, equipment, description, coord_x, coord_y, width, height, color) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssiiiis", $name, $responsible, $capacity, $equipment, $description, $x, $y, $w, $h, $color);
        $stmt->execute();
    }
    
    header('Location: building_manage.php');
    exit;
}
?>
