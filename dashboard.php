<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); // Redirect to login if not logged in
    exit();
}

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

$user_id = $_SESSION['user_id'];

// Check the global next vote date
$stmt = $pdo->query("SELECT next_vote_date FROM next_vote_date LIMIT 1");
$next_vote_date_row = $stmt->fetch(PDO::FETCH_ASSOC);
$next_vote_date = new DateTime($next_vote_date_row['next_vote_date']);
$current_date = new DateTime();

// If the current date is not the next_vote_date, show a message
if ($current_date->format('Y-m-d') !== $next_vote_date->format('Y-m-d')) {
    echo '<body style="background: black;">';
    echo "<div style='width: 50%; margin: 100px auto; padding: 20px; border-radius: 10px; background-color: white; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); text-align: center;'>";
    echo "<h2 style='border: 0px solid green;'>VOTING IS ONLY ALLOWED ON " . $next_vote_date->format('Y-m-d') . "!</h2>";
    echo "
        <button style='background-color: transparent; border: 0px solid green; padding: 10px 20px; cursor: pointer;'>
            <a href='logout.php' style='text-decoration: none; color: green;'><h2 style='border: 0px solid green;'>Logout</h2></a>
        </button>
      ";
    echo "</div>";
    echo "</body>";

    exit();
}

// Check if the user has already voted
$stmt = $pdo->prepare("SELECT vote, voted_at FROM vote WHERE user_id = :user_id");
$stmt->execute(['user_id' => $user_id]);
$vote = $stmt->fetch(PDO::FETCH_ASSOC);

if ($vote) {
    // If the user has already voted, show the message
    echo '<body style="background: black;">';
    echo "<div style='width: 50%; margin: 100px auto; padding: 20px; border-radius: 10px; background-color: white; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); text-align: center;'>";
    echo "<h2 style='border: 0px solid green;'>YOUR VOTE IS ALREADY TAKEN! COME BACK When Next Date is Announced!</h2>";
    echo "
        <button style='background-color: transparent; border: 0px solid green; padding: 10px 20px; cursor: pointer;'>
            <a href='logout.php' style='text-decoration: none; color: green;'><h2 style='border: 0px solid green;'>Logout</h2></a>
        </button>
      ";
    echo "</div>";
    echo "</body>";

    exit();
}

// Handle vote submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['vote'])) {
    $vote_option = $_POST['vote'];
    
    // Insert the user's vote into the database
    $stmt = $pdo->prepare("INSERT INTO vote (user_id, vote) VALUES (:user_id, :vote)");
    $stmt->execute(['user_id' => $user_id, 'vote' => $vote_option]);
    echo '<body style="background: black;">';
    echo "<div style='width: 50%; margin: 100px auto; padding: 20px; border-radius: 10px; background-color: white; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); text-align: center;'>";
    echo "<h2 style='border: 0px solid green;'>Your vote for $vote_option has been submitted!</h2>";
    echo "
    <button style='background-color: transparent; border: 0px solid green; padding: 10px 20px; cursor: pointer;'>
        <a href='logout.php' style='text-decoration: none; color: green;'><h2 style='border: 0px solid green;'>Logout</h2></a>
    </button>
  ";
  echo "</div>";
  echo "</body>";
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard - Vote</title>
    <style>
        body {
            background: black;
            font-family: Arial, sans-serif;
        }
        .container {
            width: 50%;
            margin: 100px auto;
            padding: 20px;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        .radio-group {
            display: flex;
            justify-content: center; /* Center the radio buttons */
            flex-wrap: wrap; /* Wrap to the next line if the screen is too small */
            margin-bottom: 20px; /* Space below the radio buttons */
        }
        .radio-group label {
            margin: 0 20px; /* Space between the radio buttons */
            cursor: pointer; /* Change cursor on hover */
        }
        button {
            background-color: transparent;
            border: 2px solid green;
            padding: 10px 20px;
            cursor: pointer;
            color: green;
            font-size: 16px;
        }
        button:hover {
            background-color: green;
            color: white;
        }
        a {
            text-decoration: none;
            color: green;
            display: inline-block;
            margin-top: 20px;
        }
        a:hover {
            color: darkgreen;
        }
    </style>
</head>
<body>

<?php

$polls_stmt = $pdo->query("SELECT id, party_name FROM polls WHERE is_active = 1");
$polls = $polls_stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container">
    <h1>Vote for Your Party</h1>

    <form method="POST" action="">
        <div class="radio-group">
            <?php foreach ($polls as $poll): ?>
                <label>
                    <input type="radio" name="vote" value="<?php echo htmlspecialchars($poll['party_name']); ?>" required>
                    <?php echo htmlspecialchars($poll['party_name']); ?>
                </label>
            <?php endforeach; ?>
        </div>
        
        <button type="submit">Submit Vote</button>
    </form>

    <a href="logout.php">Logout</a>
</div>

<script src="assets/js/jquery.min.js"></script>
<script src="assets/js/bootstrap.min.js"></script>
</body>
</html>
