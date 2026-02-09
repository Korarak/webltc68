<?php
require '../config.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

if (!isset($_COOKIE['jwt_token'])) {
    header("Location: ../login.php");
    exit();
}

try {
    $decoded = JWT::decode($_COOKIE['jwt_token'], new Key($secret_key, 'HS256'));
} catch (Exception $e) {
    setcookie("jwt_token", "", time() - 3600, "/");
    header("Location: ../login.php");
    exit();
}
?>