<?php
session_start();

// Check if the user is logged in as a student
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: login.php");
    exit();
}

// Include database connection
include 'db.php';

// Fetch student's enrolled subjects
$user_id = $_SESSION['user_id'];

$sql_subjects = "SELECT c.id, c.subject, c.subject_code, c.section, c.semester 
                 FROM classes c
                 INNER JOIN class_records cr ON cr.subject_id = c.id
                 WHERE cr.student_id = $user_id";

$result_subjects = $conn->query($sql_subjects);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        h5 {
            color: black;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg bg-dark-subtle">
        <div class="container-fluid">
            <a class="navbar-brand d-none-lg d-flex" href="student_dashboard.php">Student</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarTogglerDemo02" aria-controls="navbarTogglerDemo02" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarTogglerDemo02">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link" href="student_dashboard.php">Student Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="student_settings.php">Settings</a>
                    </li>
                </ul>
                <div class="d-flex align-items-center justify-content-between">
                    <p class="mb-0">Hello, <?php echo $_SESSION['username']; ?>!</p>                 
                    <a href="logout.php" class="btn btn-danger ms-3">Logout</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="container mt-5">
        <h2 class="mb-2">Enrolled Subjects</h2>
        <div class="row">
            <?php
            if ($result_subjects->num_rows > 0) {
                while ($row = $result_subjects->fetch_assoc()) {
                    ?>
                    <div class="col-md-4 mb-3">
                        <div class="card">
                            <div class="card-body text-start px-5 py-5">
                                <a class="text-decoration-none" href='student_classrecord.php?id=<?= $row['id'] ?>'>
                                    <h2 class="card-title"><?php echo htmlspecialchars($row['subject']) ?></h2>
                                    <h5><?php echo htmlspecialchars($row['subject_code']) ?></h5>
                                    <h5><?php echo htmlspecialchars($row['section']) ?></h5>
                                    <h5><?php echo htmlspecialchars($row['semester']) ?></h5>
                                </a>
                            </div>
                        </div>
                    </div>
                    <?php
                }
            } else {
                ?>
                <div class="col">
                    <div class="card">
                        <div class="card-body">
                            <p class="card-text">No subjects enrolled yet.</p>
                        </div>
                    </div>
                </div>
                <?php
            }
            ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
