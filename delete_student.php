<?php
session_start();

// Check if the user is logged in, if not redirect to login page
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Include database connection
include 'db.php';

// Check if student ID is provided in the URL
if (isset($_GET['id'])) {
    $student_id = intval($_GET['id']);

    // Check if the student record belongs to the current user (instructor)
    $check_permission_sql = "SELECT * FROM class_records WHERE id='$student_id' AND teacher_id='{$_SESSION['user_id']}'";
    $result = $conn->query($check_permission_sql);

    if ($result->num_rows > 0) {
        // Delete the student record from the database
        $delete_sql = "DELETE FROM class_records WHERE id='$student_id'";
        if ($conn->query($delete_sql) === TRUE) {
            header("Location: subject_detail.php?id={$_SESSION['subject_id']}&deleted=true");
            exit();
        } else {
            echo "Error deleting student: " . $conn->error;
            exit();
        }
    } else {
        echo "You don't have permission to delete this student.";
        exit();
    }
} else {
    echo "Student ID not provided.";
    exit();
}
?>
