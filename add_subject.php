<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$instructor_id = $_POST['instructor'] ?? '';
$subject = $_POST['subject'] ?? '';

if ($instructor_id && $subject) {
    $insert_sql = "INSERT INTO classes (teacher_id, subject) VALUES ('$instructor_id', '$subject')";
    if ($conn->query($insert_sql) === TRUE) {
        // Redirect back to admin dashboard after adding subject
        header("Location: admin_dashboard.php");
        exit();
    } else {
        echo "Error: " . $conn->error;
    }
}
?>
