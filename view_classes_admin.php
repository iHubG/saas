<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

if (isset($_GET['id'])) {
    $instructor_id = $_GET['id'];
} else {
    header("Location: admin_dashboard.php");
    exit();
}

$sql_instructor = "SELECT name FROM users WHERE id='$instructor_id' AND role='instructor'";
$result_instructor = $conn->query($sql_instructor);

if ($result_instructor->num_rows > 0) {
    $row_instructor = $result_instructor->fetch_assoc();
    $instructor_name = $row_instructor['name'];
} else {
    header("Location: admin_dashboard.php");
    exit();
}

$error_message = '';
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['subject']) && !empty($_POST['subject'])) {
        $subject = $_POST['subject'];

        $insert_sql = "INSERT INTO classes (subject, teacher_id) VALUES ('$subject', '$instructor_id')";
        if ($conn->query($insert_sql) === TRUE) {
            $success_message = "Subject added successfully";
        } else {
            $error_message = "Error adding subject: " . $conn->error;
        }
    } else {
        $error_message = "Please enter a subject";
    }
}

$sql_subjects = "SELECT subject FROM classes WHERE teacher_id='$instructor_id'";
$result_subjects = $conn->query($sql_subjects);
$subjects = [];

if ($result_subjects->num_rows > 0) {
    while ($row_subject = $result_subjects->fetch_assoc()) {
        $subjects[] = $row_subject['subject'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Classes for <?php echo $instructor_name; ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <style>
        .container {
            max-width: 800px;
            margin-top: 50px;
        }
        .add-subject {
            margin-bottom: 30px;
        }
        .error-message {
            color: red;
        }
        .success-message {
            color: green;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="mt-5 mb-4">Manage Classes for <?php echo $instructor_name; ?></h1>

        <?php if (!empty($error_message)) : ?>
            <div class="alert alert-danger"><?php echo $error_message; ?></div>
        <?php endif; ?>
        <?php if (!empty($success_message)) : ?>
            <div class="alert alert-success"><?php echo $success_message; ?></div>
        <?php endif; ?>

        <div class="add-subject">
            <h3>Add Subject</h3>
            <form action="" method="POST">
                <div class="input-group mb-3">
                    <input type="text" class="form-control" name="subject" placeholder="Subject Name" required>
                    <button type="submit" class="btn btn-primary">Add Subject</button>
                </div>
            </form>
        </div>

        <h2>Subjects</h2>
        <ul class="list-group mb-4">
            <?php if (!empty($subjects)) : ?>
                <?php foreach ($subjects as $subject) : ?>
                    <li class="list-group-item"><?php echo $subject; ?></li>
                <?php endforeach; ?>
            <?php else : ?>
                <li class="list-group-item">No subjects added yet.</li>
            <?php endif; ?>
        </ul>

        <a href="view_instructors.php" class="btn btn-secondary">Back to Instructors</a>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>
