<?php
// BC106294 Tracy
// Database connection configuration
$host = 'localhost';
$dbname = 'isom3012';
$username = 'root';
$password = '';
$charset = 'utf8mb4';

try {
    // Create PDO connection
    $dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];
    
    $pdo = new PDO($dsn, $username, $password, $options);
    
    // Set character set
    $pdo->exec("SET NAMES utf8mb4");
} catch (PDOException $e) {
    // Handle connection error
    die("Database connection failed: " . $e->getMessage());
} 