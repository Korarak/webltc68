<?php
//www/condb/condb.php
$host = getenv('MYSQL_HOST');
$user = getenv('MYSQL_USER');
$password = getenv('MYSQL_PASSWORD');
$db1 = getenv('MYSQL_DATABASE1');
$db2 = getenv('MYSQL_DATABASE2');
$db3 = getenv('MYSQL_DATABASE3');
$db4 = getenv('MYSQL_DATABASE4');

// Connect to database 1
$mysqli1 = new mysqli($host, $user, $password, $db1);
if ($mysqli1->connect_error) {
    die("Connection failed: " . $mysqli1->connect_error);
}
//echo "Connected successfully to $db1\n";

// Connect to database 2
$mysqli2 = new mysqli($host, $user, $password, $db2);
if ($mysqli2->connect_error) {
    die("Connection failed: " . $mysqli2->connect_error);
}
//echo "Connected successfully to $db2\n";

// Connect to database 3
$mysqli3 = new mysqli($host, $user, $password, $db3);
if ($mysqli3->connect_error) {
    die("Connection failed: " . $mysqli3->connect_error);
}
//echo "Connected successfully to $db3\n";

// Connect to database 4
$mysqli4 = new mysqli($host, $user, $password, $db4);
if ($mysqli4->connect_error) {
    die("Connection failed: " . $mysqli4->connect_error);
}
//echo "Connected successfully to $db4\n";
// ✅ ตั้งค่า charset เป็น utf8mb4
$mysqli4->set_charset("utf8mb4");
//echo "Connected successfully to $db4\n";
?>
