<?php
require 'vendor/autoload.php';

$mysqli = new mysqli(
  getenv('MYSQL_HOST') ?: 'db',
  getenv('MYSQL_USER') ?: 'user',
  getenv('MYSQL_PASSWORD') ?: 'password',
  getenv('MYSQL_DATABASE4') ?: 'ltc_web'
);
if ($mysqli->connect_error) {
    die("DB Error: " . $mysqli->connect_error);
}

$secret_key = getenv('MYSQL_DATABASE4');
$issuer = "http://localhost:8001";
