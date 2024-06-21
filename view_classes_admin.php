<?php
session_start();
include 'db.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Check if instructor ID is provided in the URL
if (isset($_GET['id'])) {
    $instructor_id = $_GET['id'];
} else {
    header("Location: admin_dashboard.php");
    exit();
}

// Fetch instructor's name based on provided ID
$sql_instructor = "SELECT name FROM users WHERE id=? AND role='instructor'";
$stmt = $conn->prepare($sql_instructor);
$stmt->bind_param("i", $instructor_id);
$stmt->execute();
$result_instructor = $stmt->get_result();

if ($result_instructor->num_rows > 0) {
    $row_instructor = $result_instructor->fetch_assoc();
    $instructor_name = $row_instructor['name'];
} else {
    header("Location: admin_dashboard.php");
    exit();
}

$error_message = '';
$success_message = '';

// Handle form submission for adding, updating, or deleting a subject
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    if ($action === 'add_subject') {
        $subject = $_POST['subject'];
        $subject_code = $_POST['subject_code'];
        $section = $_POST['section'];
        $semester = $_POST['semester'];
        $subject_type = $_POST['subject_type'];

        // Validate input fields
        if (!empty($subject) && !empty($subject_code) && !empty($section) && !empty($semester) && !empty($subject_type)) {
            // Insert subject into database
            $insert_sql = "INSERT INTO classes (subject, subject_code, section, semester, subject_type, teacher_id) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($insert_sql);
            $stmt->bind_param("sssssi", $subject, $subject_code, $section, $semester, $subject_type, $instructor_id);

            if ($stmt->execute()) {
                $success_message = "Subject added successfully";
            } else {
                $error_message = "Error adding subject: " . $conn->error;
            }
        } else {
            $error_message = "Please fill in all fields";
        }
    } elseif ($action === 'update_subject') {
        $subject_id = $_POST['subject_id'];
        $subject = $_POST['subject'];
        $subject_code = $_POST['subject_code'];
        $section = $_POST['section'];
        $semester = $_POST['semester'];
        $subject_type = $_POST['subject_type'];

        // Validate input fields
        if (!empty($subject) && !empty($subject_code) && !empty($section) && !empty($semester) && !empty($subject_type)) {
            // Update subject in database
            $update_sql = "UPDATE classes SET subject=?, subject_code=?, section=?, semester=?, subject_type=? WHERE id=? AND teacher_id=?";
            $stmt = $conn->prepare($update_sql);
            $stmt->bind_param("sssssii", $subject, $subject_code, $section, $semester, $subject_type, $subject_id, $instructor_id);

            if ($stmt->execute()) {
                $success_message = "Subject updated successfully";
            } else {
                $error_message = "Error updating subject: " . $conn->error;
            }
        } else {
            $error_message = "Please fill in all fields";
        }
    } elseif ($action === 'delete_subject') {
        $subject_id = $_POST['subject_id'];

        // Delete subject from database
        $delete_sql = "DELETE FROM classes WHERE id=? AND teacher_id=?";
        $stmt = $conn->prepare($delete_sql);
        $stmt->bind_param("ii", $subject_id, $instructor_id);

        if ($stmt->execute()) {
            $success_message = "Subject deleted successfully";
        } else {
            $error_message = "Error deleting subject: " . $conn->error;
        }
    } elseif ($action === 'edit_subject') {
        $subject_id = $_POST['edit_subject_id'];
        // Fetch subject details to populate edit form
        $fetch_sql = "SELECT subject, subject_code, section, semester, subject_type FROM classes WHERE id=? AND teacher_id=?";
        $stmt = $conn->prepare($fetch_sql);
        $stmt->bind_param("ii", $subject_id, $instructor_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $edit_subject = [
                'id' => $subject_id,
                'subject' => $row['subject'],
                'subject_code' => $row['subject_code'],
                'section' => $row['section'],
                'semester' => $row['semester'],
                'subject_type' => $row['subject_type']

            ];
        } else {
            $error_message = "Subject not found";
        }
    }
}

// Fetch subjects and related information taught by the instructor
$sql_subjects = "SELECT id, subject, subject_code, section, semester, subject_type FROM classes WHERE teacher_id=?";
$stmt = $conn->prepare($sql_subjects);
$stmt->bind_param("i", $instructor_id);
$stmt->execute();
$result_subjects = $stmt->get_result();
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
    <title>Manage Classes for <?php echo htmlspecialchars($instructor_name); ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <style>
        .container {
            margin-top: 50px;
        }
        .add-subject, .edit-subject {
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
        <h1 class="mt-0 mb-4">Manage Classes for <?php echo htmlspecialchars($instructor_name); ?></h1>

        <?php if (!empty($error_message)) : ?>
            <div class="alert alert-danger"><?php echo $error_message; ?></div>
        <?php endif; ?>
        <?php if (!empty($success_message)) : ?>
            <div class="alert alert-success"><?php echo $success_message; ?></div>
        <?php endif; ?>

        <!-- Add Subject Form -->
        <div class="add-subject">
            <h3>Add Subject</h3>
            <form action="" method="POST">
                <input type="hidden" name="action" value="add_subject">
                <div class="mb-3">
                    <input type="text" class="form-control" name="subject" placeholder="Subject Name" required autocomplete="off">
                </div>
                <div class="mb-3">
                    <input type="text" class="form-control" name="subject_code" placeholder="Subject Code" required autocomplete="off">
                </div>
                <div class="mb-3">
                    <input type="text" class="form-control" name="section" placeholder="Course & Section" required autocomplete="off">
                </div>
                <div class="mb-3">
                    <input type="text" class="form-control" name="semester" placeholder="Semester" required autocomplete="off">
                </div>
                <div class="mb-3">
                    <input type="text" class="form-control" name="subject_type" placeholder="Subject Type 'major' or 'minor'" required autocomplete="off">
                </div>
                <button type="submit" class="btn btn-primary">Add Subject</button>
            </form>
        </div>

        <!-- Edit Subject Form -->
        <?php if (!empty($edit_subject)) : ?>
            <div class="edit-subject">
                <h3>Edit Subject</h3>
                <form action="" method="POST">
                    <input type="hidden" name="action" value="update_subject">
                    <input type="hidden" name="subject_id" value="<?php echo $edit_subject['id']; ?>">
                    <div class="mb-3">
                        <input type="text" class="form-control" name="subject" placeholder="Subject Name" value="<?php echo htmlspecialchars($edit_subject['subject']); ?>" required autocomplete="off">
                    </div>
                    <div class="mb-3">
                        <input type="text" class="form-control" name="subject_code" placeholder="Subject Code" value="<?php echo htmlspecialchars($edit_subject['subject_code']); ?>" required autocomplete="off">
                    </div>
                    <div class="mb-3">
                        <input type="text" class="form-control" name="section" placeholder="Course & Section" value="<?php echo htmlspecialchars($edit_subject['section']); ?>" required autocomplete="off">
                    </div>
                    <div class="mb-3">
                        <input type="text" class="form-control" name="semester" placeholder="Semester" value="<?php echo htmlspecialchars($edit_subject['semester']); ?>" required autocomplete="off">
                    </div>
                    <div class="mb-3">
                        <input type="text" class="form-control" name="subject_type" placeholder="Subject Type 'major' or 'minor'" value="<?php echo htmlspecialchars($edit_subject['subject_type']); ?>" required autocomplete="off">
                    </div>
                    <button type="submit" class="btn btn-primary">Update Subject</button>
                </form>
            </div>
        <?php endif; ?>

        <!-- Subjects List -->
        <h2>Subjects</h2>
        <ul class="list-group mb-4">
            <?php if (!empty($subjects)) : ?>
                <?php foreach ($subjects as $subject) : ?>
                    <li class="list-group-item">
                        <strong class="fs-5"><?php echo htmlspecialchars($subject['subject']); ?></strong><br>
                        Subject Code: <?php echo htmlspecialchars($subject['subject_code']); ?><br>
                        Section: <?php echo htmlspecialchars($subject['section']); ?><br>
                        Semester: <?php echo htmlspecialchars($subject['semester']); ?><br>
                        Subject Type: <?php echo htmlspecialchars($subject['subject_type']); ?><br>
                        <form action="" method="POST" class="d-inline">
                            <input type="hidden" name="action" value="edit_subject">
                            <input type="hidden" name="edit_subject_id" value="<?php echo $subject['id']; ?>">
                            <button type="submit" class="btn btn-sm btn-primary mt-1">Edit</button>
                        </form>
                        <button type="button" class="btn btn-sm btn-danger mt-1" data-bs-toggle="modal" data-bs-target="#deleteSubjectModal<?php echo $subject['id']; ?>">
                            Delete
                        </button>
                        <!-- Delete Subject Modal -->
                        <div class="modal fade" id="deleteSubjectModal<?php echo $subject['id']; ?>" tabindex="-1" aria-labelledby="deleteSubjectModalLabel<?php echo $subject['id']; ?>" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="deleteSubjectModalLabel<?php echo $subject['id']; ?>">Confirm Deletion</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        Are you sure you want to delete the subject: <strong><?php echo htmlspecialchars($subject['subject']); ?></strong>?
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                        <form action="" method="POST" class="d-inline">
                                            <input type="hidden" name="action" value="delete_subject">
                                            <input type="hidden" name="subject_id" value="<?php echo $subject['id']; ?>">
                                            <button type="submit" class="btn btn-danger">Delete</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </li>
                <?php endforeach; ?>
            <?php else : ?>
                <li class="list-group-item">No subjects added yet.</li>
            <?php endif; ?>
        </ul>

     
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>
