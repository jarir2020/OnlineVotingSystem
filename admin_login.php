<?php
session_start();
if (isset($_SESSION['admin_id'])) {
    header("Location: admin_dashboard.php"); // Redirect to dashboard if logged in
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $config = json_decode(file_get_contents('config.json'), true);
    $pdo = new PDO("mysql:host={$config['host']};dbname={$config['dbname']}", $config['username'], $config['password']);
    
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    $stmt = $pdo->prepare("SELECT * FROM admin WHERE username = :username");
    $stmt->execute(['username' => $username]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($admin && password_verify($password, $admin['password'])) {
        $_SESSION['admin_id'] = $admin['id'];
        header("Location: admin_dashboard.php");
        exit();
    } else {
        echo "Invalid username or password.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Login - Online Voting System</title>
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/login.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container h-100">
        <div class="d-flex justify-content-center h-100">
            <div class="user_card">
                <div class="d-flex justify-content-center">
                    <div class="brand_logo_container">
                        <img src="assets/images/logo.png" class="brand_logo" alt="Logo"></img>
                        <div style="margin-top:10px"></div>
                        <span style="color:white">Admin Login</span>
                    </div>
                </div>
                
                <div class="d-flex justify-content-center form_container">
                    <form method="POST" action="">
                        <div class="input-group mb-3">

                            <input type="text" name="username" class="form-control input_user" placeholder="Username" required>
                        </div>
                        <div class="input-group mb-2">

                            <input type="password" name="password" class="form-control input_pass" placeholder="Password" required>
                        </div>
                        <div class="d-flex justify-content-center mt-3 login_container">
                            <button type="submit" class="btn login_btn">Login</button>
                        </div>
                    </form>
                </div>

                <div class="mt-4">
                    <div class="d-flex justify-content-center links text-white">
                        Are you user?&nbsp;<a style="text-decoration:none;" href="index.php" class="ml-2">Login here</a>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <script src="assets/js/jquery.min.js"></script>
    <script src="assets/js/bootstrap.min.js"></script>
</body>
</html>

