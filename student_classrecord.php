<?php
session_start();

// Check if the user is logged in as a student
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: login.php");
    exit();
}

// Include database connection
include 'db.php';

// Get the subject ID from the URL
$subject_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch the subject details from the database
$sql = "SELECT * FROM classes WHERE id='$subject_id'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $subject = $result->fetch_assoc();
} else {
    echo "Subject not found.";
    exit();
}

// Fetch class records for the subject and current student
$user_id = $_SESSION['user_id'];
$class_records_sql = "SELECT cr.*, u.name AS student_name 
                      FROM class_records cr 
                      INNER JOIN users u ON cr.student_id = u.id
                      WHERE cr.subject_id='$subject_id' AND cr.student_id='$user_id'
                      ORDER BY u.name ASC";

$class_records_result = $conn->query($class_records_sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subject Detail</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
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

    <?php 
        // Fetch current total score values from class_records table
        $sql_totals = "SELECT * FROM class_records WHERE subject_id='$subject_id'";
        $result_totals = $conn->query($sql_totals);

        if ($result_totals->num_rows > 0) {
            $totals = $result_totals->fetch_assoc();
            $attendance_total = $totals['attendance_total'];
            $homework_total = $totals['homework_total'];
            $quiz_total = $totals['quiz_total'];
            $project_total = $totals['project_total'];
            $recitation_total = $totals['recitation_total'];
            $behavior_total = $totals['behavior_total'];
            $prelim_exam_total = $totals['prelim_exam_total'];
            $midterm_exam_total = $totals['midterm_exam_total']; // Corrected typo in field name
            $final_exam_total = $totals['final_exam_total'];
        } else {
            // Set default values if no records found (or handle accordingly)
            $attendance_total = 0;
            $homework_total = 0;
            $quiz_total = 0;
            $project_total = 0;
            $recitation_total = 0;
            $behavior_total = 0;
            $prelim_exam_total = 0;
            $midterm_exam_total = 0;
            $final_exam_total = 0;
        }

    ?>

    <div class="container-table">
        <h1>Subject: <?php echo htmlspecialchars($subject['subject']); ?> Class Record</h1>

        <!-- Display class records -->
        <h2></h2>
        <table>
            <tr>
                <th>Student Name</th>
                <th>Attendance</th>
                <th>Homework</th>
                <th>Quiz</th>
                <th>Project</th>
                <th>Recitation</th>
                <th>Behavior</th>
                <th>Prelim Exam</th>
                <th>Midterm Exam</th>
                <th>Final Exam</th>
                <th>Final Grade</th>
                <th>Remarks</th>
            </tr>
            <?php
            if ($class_records_result->num_rows > 0) {
                while ($record = $class_records_result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($record['student_name'] ?? 'N/A') . "</td>";
                    echo "<td>" . (isset($record['attendance']) ? htmlspecialchars($record['attendance']) : 'N/A') . '/' . $attendance_total; "</td>";
                    echo "<td>" . (isset($record['homework']) ? htmlspecialchars($record['homework']) : 'N/A') . '/' . $homework_total; "</td>";
                    echo "<td>" . (isset($record['quiz']) ? htmlspecialchars($record['quiz']) : 'N/A') . '/' . $quiz_total; "</td>";
                    echo "<td>" . (isset($record['project']) ? htmlspecialchars($record['project']) : 'N/A') . '/' . $project_total; "</td>";
                    echo "<td>" . (isset($record['recitation']) ? htmlspecialchars($record['recitation']) : 'N/A') . '/' . $recitation_total; "</td>";
                    echo "<td>" . (isset($record['behavior']) ? htmlspecialchars($record['behavior']) : 'N/A') . '/' . $behavior_total; "</td>";
                    echo "<td>" . (isset($record['prelim_exam']) ? htmlspecialchars($record['prelim_exam']) : 'N/A') . '/' . $prelim_exam_total; "</td>";
                    echo "<td>" . (isset($record['midterm_exam']) ? htmlspecialchars($record['midterm_exam']) : 'N/A') . '/' . $midterm_exam_total; "</td>";
                    echo "<td>" . (isset($record['final_exam']) ? htmlspecialchars($record['final_exam']) : 'N/A') . '/' . $final_exam_total; "</td>";
                    echo "<td>" . (isset($record['final_grade']) ? htmlspecialchars($record['final_grade']) : 'N/A') . "</td>";
                    echo "<td>" . (isset($record['remarks']) ? htmlspecialchars($record['remarks']) : 'N/A') . "</td>";     
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='9'>No class records found for this subject.</td></tr>";
            }
            ?>
        </table>
      
    </div>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
