<?php
session_start();

// Check if the user is logged in, if not redirect to login page
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Include database connection
include 'db.php';

// Get the subject ID from the URL
$subject_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch the subject details from the database
$sql = "SELECT * FROM classes WHERE id='$subject_id' AND teacher_id='{$_SESSION['user_id']}'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $subject = $result->fetch_assoc();
} else {
    echo "Subject not found or you do not have permission to view this subject.";
    exit();
}

// Handle form submissions for adding student, adding scores, and deleting student
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['addStudent'])) {
        // Adding a new student
        $student_name = isset($_POST['name']) ? $_POST['name'] : '';

        // Validate and sanitize input (you should add more validation as needed)
        $student_name = mysqli_real_escape_string($conn, $student_name);

        if (!empty($student_name)) {
            // Check if the student exists in the users table and is a student
            $check_student_sql = "SELECT id FROM users WHERE name='$student_name' AND role='student'";
            $check_student_result = $conn->query($check_student_sql);

            if ($check_student_result && $check_student_result->num_rows > 0) {
                // Student found, retrieve student ID
                $student_data = $check_student_result->fetch_assoc();
                $student_id = $student_data['id'];

                // Check if the student is already added to class_records for this subject
                $check_existing_sql = "SELECT * FROM class_records WHERE subject_id='$subject_id' AND teacher_id='{$_SESSION['user_id']}' AND student_id='$student_id'";
                $check_existing_result = $conn->query($check_existing_sql);

                if ($check_existing_result->num_rows == 0) {
                    // Insert class record into the database
                    $insert_student_sql = "INSERT INTO class_records (subject_id, teacher_id, student_id, student_name) 
                                           VALUES ('$subject_id', '{$_SESSION['user_id']}', '$student_id', '$student_name')";
                    if ($conn->query($insert_student_sql) === TRUE) {
                        $success_message = "Student added successfully";
                    } else {
                        $error_message = "Error adding student: " . $conn->error;
                    }
                } else {
                    $error_message = "Student '$student_name' already exists in the class records.";
                }
            } else {
                $error_message = "Student '$student_name' not found or is not registered as a student.";
            }
        } else {
            $error_message = "Student name cannot be empty.";
        }
    } elseif (isset($_POST['addScores'])) {
        $student_id = isset($_POST['student_id']) ? $_POST['student_id'] : '';
        $attendance = isset($_POST['attendance']) ? (int) $_POST['attendance'] : 0;
        $quiz = isset($_POST['quiz']) ? (int) $_POST['quiz'] : 0;
        $project = isset($_POST['project']) ? (int) $_POST['project'] : 0;
        $recitation = isset($_POST['recitation']) ? (int) $_POST['recitation'] : 0;
        $behavior = isset($_POST['behavior']) ? (int) $_POST['behavior'] : 0;
        $prelim_exam = isset($_POST['prelim_exam']) ? (int) $_POST['prelim_exam'] : 0;
        $midterm_exam = isset($_POST['midterm_exam']) ? (int) $_POST['midterm_exam'] : 0;
        $final_exam = isset($_POST['final_exam']) ? (int) $_POST['final_exam'] : 0;

        // Fetch existing scores to add to new values
        $fetch_scores_sql = "SELECT attendance, quiz, project, recitation, behavior, prelim_exam, midterm_exam, final_exam 
                            FROM class_records 
                            WHERE subject_id='$subject_id' AND teacher_id='{$_SESSION['user_id']}' AND student_id='$student_id'";
        $fetch_scores_result = $conn->query($fetch_scores_sql);

        if ($fetch_scores_result->num_rows > 0) {
            $existing_scores = $fetch_scores_result->fetch_assoc();
            
            // Calculate new scores by adding only the difference from existing scores
            $attendance = (int) $existing_scores['attendance'] + $attendance;
            $quiz = (int) $existing_scores['quiz'] + $quiz;
            $project = (int) $existing_scores['project'] + $project;
            $recitation = (int) $existing_scores['recitation'] + $recitation;
            $behavior = (int) $existing_scores['behavior'] + $behavior;
            $prelim_exam = (int) $existing_scores['prelim_exam'] + $prelim_exam;
            $midterm_exam = (int) $existing_scores['midterm_exam'] + $midterm_exam;
            $final_exam = (int) $existing_scores['final_exam'] + $final_exam;
        }

        // Update scores in the database for the existing student
        $update_sql = "UPDATE class_records 
                    SET attendance='$attendance', 
                        quiz='$quiz', 
                        project='$project', 
                        recitation='$recitation', 
                        behavior='$behavior', 
                        prelim_exam='$prelim_exam', 
                        midterm_exam='$midterm_exam', 
                        final_exam='$final_exam'
                    WHERE subject_id='$subject_id' AND teacher_id='{$_SESSION['user_id']}' AND student_id='$student_id'";
        
        if ($conn->query($update_sql) === TRUE) {
            $success_message = "Scores updated successfully";
        } else {
            $error_message = "Error updating scores: " . $conn->error;
        }
    } elseif (isset($_POST['deleteStudent'])) {
        // Deleting a student from the subject
        $student_id = isset($_POST['student_id']) ? $_POST['student_id'] : '';

        // Perform deletion from class_records table
        $delete_sql = "DELETE FROM class_records 
                       WHERE subject_id='$subject_id' AND teacher_id='{$_SESSION['user_id']}' AND student_id='$student_id'";
        
        if ($conn->query($delete_sql) === TRUE) {
            $success_message = "Student deleted successfully";
        } else {
            $error_message = "Error deleting student: " . $conn->error;
        }
    }
}

// Fetch class records for the subject and arrange them alphabetically by student name
$class_records_sql = "SELECT * FROM class_records WHERE subject_id='$subject_id' AND teacher_id='{$_SESSION['user_id']}' ORDER BY student_name ASC";
$class_records_result = $conn->query($class_records_sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subject Detail</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .container-table {
            width: 100%;
            margin: 20px auto;
            padding: 20px;
        }
        h1, h2 {
            color: #333;
        }
        form {
            margin-bottom: 20px;
        }
        form label {
            display: block;
            margin-top: 10px;
        }
        form input[type="text"], 
        form input[type="number"], 
        form input[type="date"] {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        form button {
            padding: 5px 10px;
            background-color: #5cb85c;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
        }
        form button:hover {
            background-color: #4cae4c;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        table th, table td {
            padding: 10px;
            text-align: center;
        }
        table th {
            background-color: #f4f4f4;
        }
        a {
            text-decoration: none;
            color: #337ab7;
        }
        a:hover {
            text-decoration: underline;
        }
        .message {
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .success {
            background-color: #dff0d8;
            color: #3c763d;
        }
        .error {
            background-color: #f2dede;
            color: #a94442;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg bg-dark-subtle">
        <div class="container-fluid">
            <a class="navbar-brand d-none-lg d-flex" href="teacher_dashboard.php">Instructor</a>
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
    <div class="container-table">
        <h1>Subject: <?php echo htmlspecialchars($subject['subject']); ?> Class Record</h1>

        <!-- Display success or error messages -->
        <?php if (isset($success_message)) echo "<div class='message success'>$success_message</div>"; ?>
        <?php if (isset($error_message)) echo "<div class='message error'>$error_message</div>"; ?>

        <!-- Form to add a student -->
        <form action="subject_detail.php?id=<?php echo $subject_id; ?>" method="POST">
            <label for="name">Name:</label>
            <input type="text" id="name" name="name" class="w-75" required autocomplete="off">
            <button type="submit" name="addStudent" class="btn btn-primary">Add Student</button>
        </form>

        <table class="table">
            <thead>
                <tr>
                    <th>Student Name</th>
                    <th>Attendance</th>
                    <th>Quiz</th>
                    <th>Project</th>
                    <th>Recitation</th>
                    <th>Behavior</th>
                    <th>Prelim Exam</th>
                    <th>Midterm Exam</th>
                    <th>Final Exam</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($class_records_result->num_rows > 0) {
                    while ($record = $class_records_result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($record['student_name'] ?? 'N/A') . "</td>";
                        echo "<td>" . (isset($record['attendance']) ? htmlspecialchars($record['attendance']) : 'N/A') . "</td>";
                        echo "<td>" . (isset($record['quiz']) ? htmlspecialchars($record['quiz']) : 'N/A') . "</td>";
                        echo "<td>" . (isset($record['project']) ? htmlspecialchars($record['project']) : 'N/A') . "</td>";
                        echo "<td>" . (isset($record['recitation']) ? htmlspecialchars($record['recitation']) : 'N/A') . "</td>";
                        echo "<td>" . (isset($record['behavior']) ? htmlspecialchars($record['behavior']) : 'N/A') . "</td>";
                        echo "<td>" . (isset($record['prelim_exam']) ? htmlspecialchars($record['prelim_exam']) : 'N/A') . "</td>";
                        echo "<td>" . (isset($record['midterm_exam']) ? htmlspecialchars($record['midterm_exam']) : 'N/A') . "</td>";
                        echo "<td>" . (isset($record['final_exam']) ? htmlspecialchars($record['final_exam']) : 'N/A') . "</td>";
                        echo "<td>";
                        echo "<button type='button' class='btn btn-primary' data-bs-toggle='modal' data-bs-target='#scoreModal{$record['student_id']}'>Add Scores</button>";
                        echo " <form method='POST' action='subject_detail.php?id=$subject_id' style='display:inline-block;'>";
                        echo "<input type='hidden' name='student_id' value='{$record['student_id']}'>";
                        echo "<button type='submit' name='deleteStudent' class='btn btn-danger'>Delete</button>";
                        echo "</form>";
                        echo "</td>";
                        echo "</tr>";

                        // Modal for adding scores
                        echo "<div class='modal fade' id='scoreModal{$record['student_id']}' tabindex='-1' aria-labelledby='scoreModalLabel' aria-hidden='true'>";
                        echo "<div class='modal-dialog'>";
                        echo "<div class='modal-content'>";
                        echo "<div class='modal-header'>";
                        echo "<h5 class='modal-title' id='scoreModalLabel'>Add Scores for {$record['student_name']}</h5>";
                        echo "<button type='button' class='btn-close' data-bs-dismiss='modal' aria-label='Close'></button>";
                        echo "</div>";
                        echo "<form action='subject_detail.php?id=$subject_id' method='POST'>";
                        echo "<div class='modal-body'>";
                        echo "<input type='hidden' name='student_id' value='{$record['student_id']}'>";
                        echo "<div class='mb-3'>";
                        echo "<label for='attendance'>Attendance</label>";
                        echo "<input type='number' class='form-control' id='attendance' name='attendance'>";
                        echo "</div>";
                        echo "<div class='mb-3'>";
                        echo "<label for='quiz'>Quiz</label>";
                        echo "<input type='number' class='form-control' id='quiz' name='quiz'>";
                        echo "</div>";
                        echo "<div class='mb-3'>";
                        echo "<label for='project'>Project</label>";
                        echo "<input type='number' class='form-control' id='project' name='project'>";
                        echo "</div>";
                        echo "<div class='mb-3'>";
                        echo "<label for='recitation'>Recitation</label>";
                        echo "<input type='number' class='form-control' id='recitation' name='recitation'>";
                        echo "</div>";
                        echo "<div class='mb-3'>";
                        echo "<label for='behavior'>Behavior</label>";
                        echo "<input type='number' class='form-control' id='behavior' name='behavior'>";
                        echo "</div>";
                        echo "<div class='mb-3'>";
                        echo "<label for='prelim_exam'>Prelim Exam</label>";
                        echo "<input type='number' class='form-control' id='prelim_exam' name='prelim_exam'>";
                        echo "</div>";
                        echo "<div class='mb-3'>";
                        echo "<label for='midterm_exam'>Midterm Exam</label>";
                        echo "<input type='number' class='form-control' id='midterm_exam' name='midterm_exam'>";
                        echo "</div>";
                        echo "<div class='mb-3'>";
                        echo "<label for='final_exam'>Final Exam</label>";
                        echo "<input type='number' class='form-control' id='final_exam' name='final_exam'>";
                        echo "</div>";
                        echo "</div>";
                        echo "<div class='modal-footer'>";
                        echo "<button type='button' class='btn btn-secondary' data-bs-dismiss='modal'>Close</button>";
                        echo "<button type='submit' name='addScores' class='btn btn-primary'>Add Scores</button>";
                        echo "</div>";
                        echo "</form>";
                        echo "</div>";
                        echo "</div>";
                        echo "</div>";
                    }
                } else {
                    echo "<tr><td colspan='10'>No class records found.</td></tr>";
                }
                ?>
            </tbody>
        </table>
      
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>
