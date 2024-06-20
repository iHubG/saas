<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$sql = "SELECT * FROM users WHERE id=$user_id";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $admin = $result->fetch_assoc();
} else {
    echo "Admin not found!";
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if ($name !== $admin['name'] || $email !== $admin['email']) {
        if (!password_verify($current_password, $admin['password'])) {
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

        $update_sql = "UPDATE users SET name='$name', email='$email' $update_password WHERE id=$user_id";
        if ($conn->query($update_sql) === TRUE) {

            $admin['name'] = $name;
            $admin['email'] = $email;
            $success_message = "Admin information updated successfully";
        } else {
            $error_message = "Error updating admin information: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Settings</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
</head>
<body>
    <div class="container mt-5">
        <h1 class="mb-4">Admin Settings</h1>
        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger"><?php echo $error_message; ?></div>
        <?php elseif (isset($success_message)): ?>
            <div class="alert alert-success"><?php echo $success_message; ?></div>
        <?php endif; ?>
        <form action="" method="POST">
            <div class="mb-3">
                <label for="name" class="form-label">Name:</label>
                <input type="text" id="name" name="name" class="form-control" value="<?php echo htmlspecialchars($admin['name'] ?? '', ENT_QUOTES); ?>" required>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Email:</label>
                <input type="email" id="email" name="email" class="form-control" value="<?php echo htmlspecialchars($admin['email'] ?? '', ENT_QUOTES); ?>" required>
            </div>
            
            <label for="current_password" class="form-label">Current Password:</label>
            <div class="mb-3 input-group">
                <input type="password" id="current_password" name="current_password" class="form-control" required>
                <span class="input-group-text password-toggle-icon" id="toggleCurrentPasswordIcon">
                    <i class="bi bi-eye" id="toggleCurrentPassword"></i>
                </span>
            </div>

            <label for="new_password" class="form-label">New Password:</label>
            <div class="mb-3 input-group">
                <input type="password" id="new_password" name="new_password" class="form-control">
                <span class="input-group-text password-toggle-icon" id="toggleNewPasswordIcon">
                    <i class="bi bi-eye" id="toggleNewPassword"></i>
                </span>
            </div>

            <label for="confirm_password" class="form-label">Confirm Password:</label>
            <div class="mb-3 input-group">
                <input type="password" id="confirm_password" name="confirm_password" class="form-control">
                <span class="input-group-text password-toggle-icon" id="toggleConfirmPasswordIcon">
                    <i class="bi bi-eye" id="toggleConfirmPassword"></i>
                </span>
            </div>

            <button type="submit" class="btn btn-primary">Update</button>
        </form>
        <a href="admin_dashboard.php" class="mt-3 btn btn-secondary">Back to Dashboard</a>

    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('toggleCurrentPasswordIcon').addEventListener('click', function() {
                var passwordInput = document.getElementById('current_password');
                var icon = document.getElementById('toggleCurrentPassword');

                if (passwordInput.type === 'password') {
                    passwordInput.type = 'text';
                    icon.classList.remove('bi-eye');
                    icon.classList.add('bi-eye-slash');
                } else {
                    passwordInput.type = 'password';
                    icon.classList.remove('bi-eye-slash');
                    icon.classList.add('bi-eye');
                }
            });
        });

        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('toggleNewPasswordIcon').addEventListener('click', function() {
                var passwordInput = document.getElementById('new_password');
                var icon = document.getElementById('toggleNewPassword');

                if (passwordInput.type === 'password') {
                    passwordInput.type = 'text';
                    icon.classList.remove('bi-eye');
                    icon.classList.add('bi-eye-slash');
                } else {
                    passwordInput.type = 'password';
                    icon.classList.remove('bi-eye-slash');
                    icon.classList.add('bi-eye');
                }
            });
        });

        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('toggleConfirmPasswordIcon').addEventListener('click', function() {
                var passwordInput = document.getElementById('confirm_password');
                var icon = document.getElementById('toggleConfirmPassword');

                if (passwordInput.type === 'password') {
                    passwordInput.type = 'text';
                    icon.classList.remove('bi-eye');
                    icon.classList.add('bi-eye-slash');
                } else {
                    passwordInput.type = 'password';
                    icon.classList.remove('bi-eye-slash');
                    icon.classList.add('bi-eye');
                }
            });
        });

    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>
