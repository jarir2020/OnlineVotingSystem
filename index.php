<?php
session_start();

// Fetch DB settings from config.json
$config = json_decode(file_get_contents('config.json'), true);
$db_host = $config['host'];
$db_name = $config['dbname'];
$db_user = $config['username'];
$db_pass = $config['password'];

try {
    // Create a new PDO connection
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if (!empty($username) && !empty($password)) {
        // Fetch the user from the database
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username"); //Db te khujtese
        $stmt->execute(['username' => $username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);  ///Fetch kora data store kortese

        if ($user && password_verify($password, $user['password'])) {
            // Correct credentials, start the session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            header("Location: dashboard.php"); // Redirect to the dashboard
            exit();
        } else {
            $error = "Invalid username or password!";
        }
    } else {
        $error = "Please fill in both fields!";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login - Online Voting System</title>
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/login.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="top-right">
    <a style="text-decoration:none; color:white" href="dynamic_results.php">Last Vote Update</a>
</div>
    <div class="container h-100">
        <div class="d-flex justify-content-center h-100">
            <div class="user_card">
                <div class="d-flex justify-content-center">
                    <div class="brand_logo_container">
                        <img src="assets/images/logo.png" class="brand_logo" alt="Logo">
                    </div>
                </div>

                <div class="d-flex justify-content-center form_container">
                    <form method="POST" action="">
                        <div class="input-group mb-3">

                            <input type="text" name="username" class="form-control input_user" value="" placeholder="username" required>
                        </div>
                        <div class="input-group mb-2">

                            <input type="password" name="password" class="form-control input_pass" value="" placeholder="password" required>
                        </div>
                        <div class="form-group">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="customControlInline">
                                <label class="custom-control-label text-white" for="customControlInline">Remember me</label>
                            </div>
                        </div>
                        <?php if (isset($error)) { ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php } ?>
                        <div class="d-flex justify-content-center mt-3 login_container">
                            <button type="submit" class="btn login_btn">Login</button>
                        </div>
                    </form>
                </div>
                
                <div class="mt-4">
                    <div class="d-flex justify-content-center links text-white">
                        Don't have an account?&nbsp;<a style="text-decoration:none;" href="signup.php" class="ml-2">Sign Up</a>
                    </div>
                    <div class="d-flex justify-content-center links">
                    <a style="text-decoration:none;" href="forgot_password.php">Forgot your password?</a>
                    </div>
                    <div class="d-flex justify-content-center links text-white">
                        Are you an admin?&nbsp;<a style="text-decoration:none;" href="admin_login.php" class="ml-2">Login here</a>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <script src="assets/js/jquery.min.js"></script>
    <script src="assets/js/bootstrap.min.js"></script>
</body>
</html>

