<?php
session_start();
include 'db.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Fetch list of instructors
$sql = "SELECT * FROM users WHERE role='instructor'";
$result = $conn->query($sql);

// Handle delete operation
if (isset($_POST['delete_instructor'])) {
    $instructor_id = $_POST['instructor_id'];

    // Delete instructor from database
    $delete_sql = "DELETE FROM users WHERE id=?";
    $stmt = $conn->prepare($delete_sql);
    $stmt->bind_param("i", $instructor_id);

    if ($stmt->execute()) {
        // Also delete related classes taught by the instructor
        $delete_classes_sql = "DELETE FROM classes WHERE teacher_id=?";
        $stmt_classes = $conn->prepare($delete_classes_sql);
        $stmt_classes->bind_param("i", $instructor_id);
        $stmt_classes->execute();

        $success_message = "Instructor deleted successfully";
        header("Location: {$_SERVER['PHP_SELF']}");
        exit();
    } else {
        $error_message = "Error deleting instructor: " . $conn->error;
    }
}

// Handle update operation
if (isset($_POST['update_instructor'])) {
    $instructor_id = $_POST['instructor_id'];
    $instructor_name = $_POST['instructor_name'];
    $instructor_email = $_POST['instructor_email'];

    // Update instructor information in database
    $update_sql = "UPDATE users SET name=?, email=? WHERE id=?";
    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param("ssi", $instructor_name, $instructor_email, $instructor_id);

    if ($stmt->execute()) {
        $success_message = "Instructor updated successfully";
        header("Location: {$_SERVER['PHP_SELF']}");
        exit();
    } else {
        $error_message = "Error updating instructor: " . $conn->error;
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registered Instructors</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
</head>
<body>
    <nav class="navbar navbar-expand-lg bg-dark-subtle">
        <div class="container-fluid">
            <a class="navbar-brand d-none-lg d-flex" href="admin_dashboard.php">Admin</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarTogglerDemo02" aria-controls="navbarTogglerDemo02" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarTogglerDemo02">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link" href="admin_dashboard.php">Admin Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="view_instructors.php">Registered Instructors</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="view_students.php">Registered Students</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="admin_settings.php">Settings</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="user_logs.php">User Logs</a>
                    </li>
                </ul>
                <div class="d-flex align-items-center justify-content-between">
                    <p class="mb-0">Hello, <?php echo $_SESSION['username']; ?>!</p>                 
                    <a href="logout.php" class="btn btn-danger ms-3">Logout</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="container">
        <h1 class="my-4">Registered Instructors</h1>

        <?php if (isset($error_message)) : ?>
            <div class="alert alert-danger" role="alert">
                <?php echo $error_message; ?>
            </div>
        <?php endif; ?>

        <?php if (isset($success_message)) : ?>
            <div class="alert alert-success" role="alert">
                <?php echo $success_message; ?>
            </div>
        <?php endif; ?>

        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0) : ?>
                        <?php while ($row = $result->fetch_assoc()) : ?>
                            <tr>
                                <td>
                                    <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST">
                                        <input type="hidden" name="instructor_id" value="<?php echo $row['id']; ?>">
                                        <input type="text" name="instructor_name" value="<?php echo htmlspecialchars($row['name']); ?>" class="form-control" required autocomplete="off">
                                </td>
                                <td>
                                    <input type="email" name="instructor_email" value="<?php echo htmlspecialchars($row['email']); ?>" class="form-control" required autocomplete="off">
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <button type="submit" name="update_instructor" class="btn btn-success btn-sm">Update</button>
                                        <a href="view_classes_admin.php?id=<?php echo $row['id']; ?>" class="btn btn-primary btn-sm ms-1">Manage</a>
                                    </div>
                                    </form>
                                    <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST" class="ms-1">
                                        <input type="hidden" name="instructor_id" value="<?php echo $row['id']; ?>">
                                        <button type="submit" class="btn btn-danger btn-sm mt-1" name="delete_instructor">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else : ?>
                        <tr>
                            <td colspan="3">No instructors found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <a href="instructor_register.php" class="btn btn-primary mt-3">Register Instructor</a>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>
