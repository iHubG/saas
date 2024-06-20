<?php
include 'db.php'; // Include your database connection

$password = 'your_password_here'; // Replace with the password you want to hash

$hashed_password = password_hash($password, PASSWORD_DEFAULT);

$user_id = 1; // Replace with the user ID of the account you want to update

$sql = "UPDATE Users SET password='$hashed_password' WHERE id=$user_id";

if ($conn->query($sql) === TRUE) {
    echo "Password hashed and updated successfully.";
} else {
    echo "Error updating password: " . $conn->error;
}

$conn->close();
?>
