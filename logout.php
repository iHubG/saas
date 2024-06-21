<?php
session_start();

if (isset($_SESSION['user_id'])) {
    include 'db.php'; // Ensure db.php includes your mysqli connection
    
    $userId = $_SESSION['user_id'];
    $sql = "SELECT * FROM users WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        
        $roleName = ucfirst($row['role']); // Capitalize role name
        $logData = "$roleName {$row['name']} logged out."; // Log message: Role name logged out
        $stmt = $conn->prepare("INSERT INTO user_logs (log_data) VALUES (?)");
        $stmt->bind_param("s", $logData);
        $stmt->execute();
    }
}

// Destroy session and redirect to login page
session_destroy();
header("Location: login.php");
exit();
?>
