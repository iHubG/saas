<?php
session_start();
include 'db.php';

$error_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $sql = "SELECT * FROM users WHERE username=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();

        if (password_verify($password, $row['password'])) {
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['username'] = $row['username'];
            $_SESSION['role'] = $row['role']; // Store user role in session

            $roleName = ucfirst($row['role']); 
            $logData = "$roleName {$row['name']} logged in."; 
            $stmt = $conn->prepare("INSERT INTO user_logs (log_data) VALUES (?)");
            $stmt->bind_param("s", $logData); 
            $stmt->execute(); 

            switch ($row['role']) {
                case 'admin':
                    header("Location: admin_dashboard.php");
                    break;
                case 'instructor':
                    header("Location: teacher_dashboard.php");
                    break;
                case 'student':
                    header("Location: student_dashboard.php");
                    break;
                default:
                    header("Location: dashboard.php");
                    break;
            }
            exit();
        } else {
            $error_message = "Invalid password.";
        }
    } else {
        $error_message = "No user found.";
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .container {
            max-width: 500px;
            margin-top: 5rem;
            background-color: #fff;
            border: 1px solid #ccc;
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0px 0px 10px 0px rgba(0,0,0,0.1);
        }
        .input-group-text {
            cursor: pointer;
        }
        .password-toggle-icon {
            cursor: pointer;
        }
        .error {
            color: red;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="text-center mb-4">Login</h1>
        <form action="login.php" method="POST">
            <div class="row justify-content-center">
                <div class="col-12 col-lg-8">
                    <div class="input-group mb-3">
                        <input type="text" class="form-control" name="username" placeholder="Username" required autocomplete="off">
                    </div>
                    <div class="input-group mb-3">
                        <input type="password" class="form-control" name="password" id="password" placeholder="Password" required autocomplete="off">
                        <span class="input-group-text password-toggle-icon" id="togglePasswordIcon">
                            <i class="bi bi-eye" id="togglePassword"></i>
                        </span>
                    </div>
                    <button type="submit" class="btn btn-primary col-12">Login</button>
                </div>
            </div>
        </form>
        <?php
        if (isset($error_message)) {
            echo '<p class="error text-center">' . $error_message . '</p>';
        }
        ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('togglePasswordIcon').addEventListener('click', function() {
                var passwordInput = document.getElementById('password');
                var icon = this.querySelector('i');

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
</body>
</html>
