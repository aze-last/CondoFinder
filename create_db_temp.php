<?php
$host = '127.0.0.1';
$user = 'root';
$pass = ''; // Try empty first
$port = 3306;
$db = 'condo_finder';

echo "Attempting to connect...\n";

try {
    $pdo = new PDO("mysql:host=$host;port=$port", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$db`");
    echo "SUCCESS:EMPTY_PASSWORD\n";
} catch (PDOException $e) {
    echo "Failed with empty password: " . $e->getMessage() . "\n";
    try {
        $pass = 'root'; // Try root
        $pdo = new PDO("mysql:host=$host;port=$port", $user, $pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `$db`");
        echo "SUCCESS:ROOT_PASSWORD\n";
    } catch (PDOException $e2) {
        echo "Failed with root password: " . $e2->getMessage() . "\n";
        exit(1);
    }
}
