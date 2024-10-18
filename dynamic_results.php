<?php

// Fetch DB settings from config.json
$config = json_decode(file_get_contents('config.json'), true);
$pdo = new PDO("mysql:host={$config['host']};dbname={$config['dbname']}", $config['username'], $config['password']);
// Fetch current vote results
$results_stmt = $pdo->query("SELECT vote, COUNT(*) AS votes FROM vote GROUP BY vote");
$results = $results_stmt->fetchAll(PDO::FETCH_ASSOC);

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
    <h1>On Going Vote Results</h1>
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
    <div class="mt-4">
                    <div class="d-flex justify-content-center links text-white">
                        Are you user?&nbsp;<a style="text-decoration:none;" href="index.php" class="ml-2">Login here</a>
                    </div>
    </div>
            </body>
            </html>