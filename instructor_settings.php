<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'instructor') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$sql = "SELECT * FROM Users WHERE id=$user_id";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $instructor = $result->fetch_assoc();
} else {
    echo "Instructor not found!";
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $cellphone = $_POST['cellphone'];
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if ($name !== $instructor['name'] || $email !== $instructor['email'] || $cellphone !== $instructor['cellphone']) {
        if (!password_verify($current_password, $instructor['password'])) {
            $error_message = "Incorrect current password";
        }
    }

    if ($new_password !== '') {
        if ($new_password !== $confirm_password) {
            $error_message = "New password and confirm password do not match";
        } else {
            // Hash the new password
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $update_password = ", password='$hashed_password'";
        }
    } else {
        $update_password = '';
    }

    if (!isset($error_message)) {

        $update_sql = "UPDATE Users SET name='$name', email='$email', cellphone='$cellphone' $update_password WHERE id=$user_id";
        if ($conn->query($update_sql) === TRUE) {

            $instructor['name'] = $name;
            $instructor['email'] = $email;
            $instructor['cellphone'] = $cellphone;
            $success_message = "Instructor information updated successfully";
        } else {
            $error_message = "Error updating instructor information: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instructor Settings</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <h1>Instructor Settings</h1>
        <?php if (isset($error_message)) {
            echo '<p class="error">' . $error_message . '</p>';
        } elseif (isset($success_message)) {
            echo '<p class="success">' . $success_message . '</p>';
        } ?>
        <form action="" method="POST">
            <label for="name">Name:</label>
            <input type="text" id="name" name="name" value="<?php echo $instructor['name']; ?>" required>
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" value="<?php echo $instructor['email']; ?>" required>
            <label for="cellphone">Cellphone:</label>
            <input type="text" id="cellphone" name="cellphone" value="<?php echo $instructor['cellphone']; ?>" required>
            <label for="current_password">Current Password:</label>
            <input type="password" id="current_password" name="current_password" required>
            <label for="new_password">New Password:</label>
            <input type="password" id="new_password" name="new_password">
            <label for="confirm_password">Confirm Password:</label>
            <input type="password" id="confirm_password" name="confirm_password">
            <button type="submit">Update</button>
        </form>
        <a href="dashboard.php">Back to Dashboard</a>
    </div>
</body>
</html>
