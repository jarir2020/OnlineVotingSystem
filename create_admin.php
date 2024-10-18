<?php
// Fetch DB settings from config.json
$config = json_decode(file_get_contents('config.json'), true);
$pdo = new PDO("mysql:host={$config['host']};dbname={$config['dbname']}", $config['username'], $config['password']);

// Admin credentials
$username = 'admin@voting';
$password = 'admin@voting'; // Replace with your desired password
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

// Prepare and execute the SQL statement
$stmt = $pdo->prepare("INSERT INTO admin (username, password) VALUES (:username, :password)");
$stmt->bindParam(':username', $username);
$stmt->bindParam(':password', $hashedPassword);

if ($stmt->execute()) {
    echo "Admin user created successfully.";
} else {
    echo "Error creating admin user.";
}
?>
