<?php
session_start();

// Fetch DB settings from config.json
$config = json_decode(file_get_contents('config.json'), true); // DB Credential Dhore antesi file theke.
$db_host = $config['host'];
$db_name = $config['dbname'];
$db_user = $config['username'];
$db_pass = $config['password'];


//Trying to connect the database
try {
    // Create a new PDO connection
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Handle the form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $email = trim($_POST['email']);
    
    // Basic form validation
    if (empty($username) || empty($password) || empty($email)) {
        $error = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } else {
        // Hash the password
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);
        
        // Check if the username or email is already taken
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = :username OR email = :email");
        $stmt->execute(['username' => $username, 'email' => $email]);
        $count = $stmt->fetchColumn();
        
        if ($count > 0) {
            $error = "Username or Email is already taken.";
        } else {
            // Insert the new user into the database
            $stmt = $pdo->prepare("INSERT INTO users (username, password, email) VALUES (:username, :password, :email)");
            $stmt->execute(['username' => $username, 'password' => $hashed_password, 'email' => $email]);
            
            $_SESSION['user_id'] = $pdo->lastInsertId(); // Store user ID in session
            header("Location: dashboard.php"); // Redirect to dashboard
            exit();
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Sign Up - Online Voting System</title>
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
                        <img src="assets/images/logo.png" class="brand_logo" alt="Logo">
                    </div>
                </div>

                <div class="d-flex justify-content-center form_container">
                    <form method="POST" action="signup.php">
                        <div class="input-group mb-3">

                            <input type="text" name="username" class="form-control input_user" value="" placeholder="username" required>
                        </div>
                        <div class="input-group mb-3">

                            <input type="email" name="email" class="form-control input_user" value="" placeholder="email" required>
                        </div>
                        <div class="input-group mb-2">

                            <input type="password" name="password" class="form-control input_pass" value="" placeholder="password" required>
                        </div>
                        
                        <?php if (isset($error)) { ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php } ?>
                        
                        <div class="d-flex justify-content-center mt-3 login_container">
                            <button type="submit" class="btn login_btn">Sign Up</button>
                        </div>
                    </form>
                </div>
                
                <div class="mt-4">
                    <div class="d-flex justify-content-center links text-white">
                        Already have an account?&nbsp;<a style="text-decoration:none;" href="index.php" class="ml-2">Login here</a>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <script src="assets/js/jquery.min.js"></script>
    <script src="assets/js/bootstrap.min.js"></script>
</body>
</html>
