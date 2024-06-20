<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include 'db.php';

$user_id = $_SESSION['user_id'];
$sql = "SELECT name FROM Users WHERE id='$user_id'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $_SESSION['name'] = $row['name'];
} else {
    $_SESSION['name'] = 'Instructor'; // Default name if not found
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <h1>Welcome to Dashboard</h1>
        <p>Hello, Instructor <?php echo $_SESSION['name']; ?>!</p>
        <ul>
<li><a href="view_classes.php">Manage Classes</a></li>
            <li><a href="instructor_settings.php">Settings</a></li>
        </ul>
        <a href="logout.php">Logout</a>
    </div>

</body>
</html>
