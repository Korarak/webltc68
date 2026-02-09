<?php
require 'config.php';

$username = 'korarak';
$password = password_hash('@dm1nLoeitech', PASSWORD_DEFAULT);

$stmt = $mysqli->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
$stmt->bind_param("ss", $username, $password);
$stmt->execute();

echo "✅ User created: admin / 1234";
