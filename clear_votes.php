<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php"); // Redirect to login if not logged in
    exit();
}

$config = json_decode(file_get_contents('config.json'), true);
$pdo = new PDO("mysql:host={$config['host']};dbname={$config['dbname']}", $config['username'], $config['password']);

$stmt = $pdo->prepare("DELETE FROM vote");
$stmt->execute();

header("Location: admin_dashboard.php");
exit();
