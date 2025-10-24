<?php
$host = '127.0.0.1';      // or 'localhost'
$db   = 'test_db';        // your database name
$user = 'root';           // your MySQL username
$pass = '';               // your MySQL password 
$charset = 'utf8mb4';     // character encoding

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

try {
    $pdo = new PDO($dsn, $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Connected successfully!";
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
?>
