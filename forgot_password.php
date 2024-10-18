<?php
session_start();
require 'vendor/autoload.php'; // Include PHPMailer

// PHPMailer setup
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_otp'])) {
    $email = $_POST['email'];

    // Generate a random 6-digit OTP
    $otp = rand(100000, 999999);

    // Store the OTP in session (or store it in the database if needed)
    $_SESSION['otp'] = $otp;
    $_SESSION['otp_email'] = $email;

    // Send OTP to the user's email using PHPMailer
    $mail = new PHPMailer(true);
    try {
        //Server settings
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com'; // Your SMTP server
        $mail->SMTPAuth = true;
        $mail->Username = 'matrimony.fallback.email@gmail.com'; // Your email
        $mail->Password = 'pzra hxsb bamn nwof'; // Your email password
        $mail->SMTPOptions = array(
            'ssl' => array(
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true
            )
        );
        $mail->SMTPSecure = "ssl";
        $mail->Port = 465;

        //Recipients
        $mail->setFrom("matrimony.fallback.email@gmail.com");
        $mail->addAddress($email);

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Your OTP for Password Reset';
        $mail->Body    = 'Your OTP is: <b>' . $otp . '</b>';

        $mail->send();
        $message = "OTP has been sent to your email."; // Set success message
    } catch (Exception $e) {
        $message = "OTP could not be sent. Mailer Error: {$mail->ErrorInfo}"; // Set error message
    }
}

// Handle OTP verification and password update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reset_password'])) {
    $otp_input = $_POST['otp'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Validate OTP
    if ($otp_input == $_SESSION['otp']) {
        // Check if passwords match
        if ($new_password === $confirm_password) {
            // Hash the new password
            $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);
            
            // Assuming you have a user table with an email column
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

            // Update the password in the database (use prepared statements)
            $stmt = $pdo->prepare("UPDATE users SET password = :password WHERE email = :email");
            $stmt->execute([
                'password' => $hashed_password,
                'email' => $_SESSION['otp_email']
            ]);

            $message = "Password has been reset successfully!";
            // Clear the OTP session data
            unset($_SESSION['otp']);
            unset($_SESSION['otp_email']);
        } else {
            $message = "Passwords do not match!";
        }
    } else {
        $message = "Invalid OTP!";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Forgot Password</title>
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <style>
        .form-group {
            margin-bottom: 15px;
        }
        #message {
            position: fixed;
            bottom: 20px;
            left: 20px;
            background-color: green;
            color: white;
            padding: 15px;
            border-radius: 5px;
            display: none; /* Initially hidden */
        }
    </style>
</head>
<body style="background-color:black; color:yellow;">
    <div class="container">
        <h2>Forgot Password</h2>
        <form method="POST" action="">
            <!-- Email input to send OTP -->
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" class="form-control" name="email" placeholder="Enter your email" required>
            </div>
            <button type="submit" name="send_otp" class="btn btn-primary">Send OTP</button>
        </form>
        
        <hr>

        <!-- OTP verification and password reset form -->
        <h3>Reset Your Password</h3>
        <form method="POST" action="">
            <div class="form-group">
                <label for="otp">OTP</label>
                <input type="text" class="form-control" name="otp" placeholder="Enter OTP" required>
            </div>
            <div class="form-group">
                <label for="new_password">New Password</label>
                <input type="password" class="form-control" name="new_password" placeholder="Enter new password" required>
            </div>
            <div class="form-group">
                <label for="confirm_password">Confirm New Password</label>
                <input type="password" class="form-control" name="confirm_password" placeholder="Confirm new password" required>
            </div>
            <button type="submit" name="reset_password" class="btn btn-primary">Reset Password</button>
            <button name="login" class="btn btn-primary"><a href="index.php" style="color:white; text-decoration:none;">Back To Login</a></button>
        </form>
        
    </div>

        <!-- Message box -->
        <div id="message">
        <?php echo $message; ?>
    </div>

    <script>
        // Show the message if it's not empty
        var message = "<?php echo $message; ?>";
        if (message !== "") {
            var messageBox = document.getElementById("message");
            messageBox.style.display = "block";
            setTimeout(function() {
                messageBox.style.display = "none";
            }, 3000); // Hide after 3 seconds
        }
    </script>
</body>
</html>
