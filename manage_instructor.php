<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$instructor_id = $_GET['id'] ?? '';
if (!$instructor_id) {
    header("Location: admin_dashboard.php"); // Redirect if ID is not provided
    exit();
}

$sql = "SELECT name FROM Users WHERE id='$instructor_id'";
$result = $conn->query($sql);
$instructor_name = ($result->num_rows > 0) ? $result->fetch_assoc()['name'] : '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $subject = $_POST['subject'] ?? '';

    if ($subject) {
        $insert_sql = "INSERT INTO classes (teacher_id, subject) VALUES ('$instructor_id', '$subject')";
        if ($conn->query($insert_sql) === TRUE) {
            $success_message = "Subject added successfully";
        } else {
            $error_message = "Error: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Instructor</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <h1>Manage Instructor: <?php echo $instructor_name; ?></h1>
        <?php if (isset($error_message)) { ?>
            <p class="error"><?php echo $error_message; ?></p>
        <?php } elseif (isset($success_message)) { ?>
            <p class="success"><?php echo $success_message; ?></p>
        <?php } ?>
        <form action="" method="POST">
            <label for="subject">Add Subject:</label>
            <input type="text" id="subject" name="subject" required>
            <button type="submit">Add Subject</button>
        </form>
        <a href="admin_dashboard.php">Back to Dashboard</a>
    </div>
</body>
</html>
