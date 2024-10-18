<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php"); // Redirect to login if not logged in
    exit();
}

// Fetch DB settings from config.json
$config = json_decode(file_get_contents('config.json'), true);
$pdo = new PDO("mysql:host={$config['host']};dbname={$config['dbname']}", $config['username'], $config['password']);

// Fetch current vote results
$results_stmt = $pdo->query("SELECT vote, COUNT(*) AS votes FROM vote GROUP BY vote");
$results = $results_stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle new poll creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_poll'])) {
    $party_name = $_POST['party_name'];
    if (!empty($party_name)) {
        $stmt = $pdo->prepare("INSERT INTO polls (party_name) VALUES (:party_name)");
        $stmt->execute(['party_name' => $party_name]);
    }
}

// Handle poll deletion
if (isset($_POST['delete_poll'])) {
    $poll_id = $_POST['poll_id'];
    $stmt = $pdo->prepare("DELETE FROM polls WHERE id = :id");
    $stmt->execute(['id' => $poll_id]);
}

// Fetch current polls
$polls_stmt = $pdo->query("SELECT id, party_name FROM polls");
$polls = $polls_stmt->fetchAll(PDO::FETCH_ASSOC);

// Set new vote date
if (isset($_POST['set_vote_date'])) {
    $new_vote_date = $_POST['vote_date'];
    
    // Begin a transaction
    $pdo->beginTransaction();
    
    try {
        // Delete existing entries
        $stmt = $pdo->prepare("DELETE FROM next_vote_date");
        $stmt->execute();
        
        // Insert new vote date
        $stmt = $pdo->prepare("INSERT INTO next_vote_date (next_vote_date) VALUES (:vote_date)");
        $stmt->execute(['vote_date' => $new_vote_date]);
        
        // Commit the transaction
        $pdo->commit();
    } catch (Exception $e) {
        // Rollback the transaction in case of error
        $pdo->rollBack();
        echo "Failed to set new vote date: " . $e->getMessage();
    }
}

// Clear previous vote results
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['clear_votes'])) {
    $stmt = $pdo->prepare("DELETE FROM vote");
    $stmt->execute();
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
    <style>
        body {
            background-color: black;
            color: white;
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
        }
        h1, h2, ul {
            text-align: center;
            color: #ffcc00; /* Gold color for headings */
        }
        li{
            list-style: none;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border: 1px solid #444; /* Darker border for better contrast */
        }
        th {
            background-color: #333; /* Darker background for headers */
        }
        tbody tr:nth-child(even) {
            background-color: #222; /* Darker rows for better readability */
        }
        form {
            margin-bottom: 20px;
            text-align: center; /* Center forms */
        }
        input[type="text"],
        input[type="date"] {
            padding: 10px;
            width: 250px;
            border: 1px solid #444;
            border-radius: 4px;
            background-color: #555; /* Input background color */
            color: white;
        }
        input[type="text"]::placeholder,
        input[type="date"]::placeholder {
            color: #bbb; /* Placeholder color */
        }
        button {
            padding: 10px 15px;
            background-color: #ffcc00; /* Button color */
            border: none;
            border-radius: 4px;
            color: black;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        button:hover {
            background-color: #e6b800; /* Darker shade on hover */
        }
        a {
            color: #ffcc00; /* Link color */
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline; /* Underline on hover */
        }
    </style>
</head>
<body>
    <h1>Admin Dashboard</h1>
    
    <h2>Current Vote Results</h2>
    <table>
        <thead>
            <tr>
                <th>Party</th>
                <th>Votes</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($results as $result): ?>
                <tr>
                    <td><?php echo htmlspecialchars($result['vote']); ?></td>
                    <td><?php echo htmlspecialchars($result['votes']); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    
    <h2>Create New Poll</h2>
    <form method="POST" action="">
        <input type="text" name="party_name" placeholder="Party Name" required>
        <button type="submit" name="create_poll">Create Poll</button>
    </form>

    <h2>Delete Poll</h2>
    <h2>Existing Polls</h2>
    <ul>
        <?php foreach ($polls as $poll): ?>
            <li style="margin-bottom:4px;">
                <?php echo htmlspecialchars($poll['party_name']); ?>
                <form method="POST" action="" style="display:inline;">
                    <input type="hidden" name="poll_id" value="<?php echo $poll['id']; ?>">
                    <button type="submit" name="delete_poll">Delete</button>
                </form>
            </li>
        <?php endforeach; ?>
    </ul>
    
    <h2>Set New Vote Date</h2>
    <form method="POST" action="">
        <input type="date" name="vote_date" required>
        <button type="submit" name="set_vote_date">Set Vote Date</button>
    </form>

    <h2>Clear Previous Vote Results</h2>
    <form method="POST" action="">
        <button type="submit" name="clear_votes">Clear Votes</button>
    </form>

    <center><button><a style="color:black; text-decoration:none;" href="logout.php">Logout</a></button></center>
</body>
</html>
