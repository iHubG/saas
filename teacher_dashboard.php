<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include 'db.php';

$user_id = $_SESSION['user_id'];
$sql = "SELECT name FROM users WHERE id='$user_id'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $_SESSION['name'] = $row['name'];
} else {
    $_SESSION['name'] = 'Instructor'; // Default name if not found
}

// Fetch subjects for the instructor from the database
$sql_subjects = "SELECT id, subject, subject_code, section, semester FROM classes WHERE teacher_id='$user_id'";
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
    <title>Instructor Dashboard</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        h5 {
            color: black;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg bg-dark-subtle">
        <div class="container-fluid">
            <a class="navbar-brand d-none-lg d-flex" href="admin_dashboard.php">Instructor</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarTogglerDemo02" aria-controls="navbarTogglerDemo02" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarTogglerDemo02">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link" href="teacher_dashboard.php">Instructor Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="student_register.php">Register Student</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="instructor_settings.php">Settings</a>
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
        <div class="row">
            <?php
            if (!empty($subjects)) {
                foreach ($subjects as $subject) {
                    ?>
                    <div class="col-md-4 mb-3">
                        <div class="card">
                            <div class="card-body text-start px-5 py-5">
                                <a class="text-decoration-none" href='subject_detail.php?id=<?= $subject['id'] ?>'>
                                    <h2 class="card-title"><?php echo htmlspecialchars($subject['subject']) ?></h2>
                                    <h5><?php echo htmlspecialchars($subject['subject_code']) ?></h5>
                                    <h5><?php echo htmlspecialchars($subject['section']) ?></h5>
                                    <h5><?php echo htmlspecialchars($subject['semester']) ?></h5>
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
                            <p class="card-text">No subjects added yet.</p>
                        </div>
                    </div>
                </div>
                <?php
            }
            ?>
        </div>
    </div>


    <script src="script.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>
