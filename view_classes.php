<?php
session_start();

// Check if the user is logged in, if not redirect to login page
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Include database connection
include 'db.php';

// Fetch instructor's name from the database based on user ID
$user_id = $_SESSION['user_id'];
$sql = "SELECT name FROM Users WHERE id='$user_id'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $_SESSION['name'] = $row['name'];
} else {
    $_SESSION['name'] = 'Instructor'; // Default name if not found
}

// Fetch subjects for the instructor from the database
$sql_subjects = "SELECT id, subject FROM classes WHERE teacher_id='$user_id'";
$result_subjects = $conn->query($sql_subjects);
$subjects = [];

if ($result_subjects->num_rows > 0) {
    while ($row_subject = $result_subjects->fetch_assoc()) {
        $subjects[] = $row_subject;
    }
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
               <h2>Subjects</h2>
        <ul>
            <?php
            if (!empty($subjects)) {
                foreach ($subjects as $subject) {
                    echo "<li><a href='subject_detail.php?id={$subject['id']}'>{$subject['subject']}</a></li>";
                }
            } else {
                echo "<li>No subjects added yet.</li>";
            }
            ?>
        </ul>
<a href="dashboard.php">Go Back to Dashboard</a>
<br>
<br>
        <a href="logout.php">Logout</a>
    </div>
</body>
</html>
