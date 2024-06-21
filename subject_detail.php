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

// Handle form submission for adding class records
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_name = $_POST['name'];

    // Validate and sanitize input (you should add more validation as needed)
    $student_name = mysqli_real_escape_string($conn, $student_name);

    // Check if the student exists in the users table and is a student
    $check_student_sql = "SELECT id FROM users WHERE name='$student_name' AND role='student'";
    $check_student_result = $conn->query($check_student_sql);

    if ($check_student_result->num_rows > 0) {
        // Student found, retrieve student ID
        $student_data = $check_student_result->fetch_assoc();
        $student_id = $student_data['id'];

        // Insert class record into the database
        $insert_sql = "INSERT INTO class_records (subject_id, teacher_id, student_id, student_name) 
                       VALUES ('$subject_id', '{$_SESSION['user_id']}', '$student_id', '$student_name')";
        if ($conn->query($insert_sql) === TRUE) {
            $success_message = "Student added successfully";
        } else {
            $error_message = "Error adding student: " . $conn->error;
        }
    } else {
        $error_message = "Student '$student_name' not found or is not registered as a student.";
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
            width: 95%;
            margin: 20px auto;
            background: #fff;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
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
            margin-top: 20px;
            padding: 10px 20px;
            background-color: #5cb85c;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
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
<a href="teacher_dashboard.php" class="btn btn-secondary my-2 mx-2">Back to Classes</a>
    <div class="container-table">
        <h1>Subject Detail: <?php echo htmlspecialchars($subject['subject']); ?></h1>

        <!-- Display success or error messages -->
        <?php if (isset($success_message)) echo "<div class='message success'>$success_message</div>"; ?>
        <?php if (isset($error_message)) echo "<div class='message error'>$error_message</div>"; ?>

        <!-- Form to add a student -->
        <form action="subject_detail.php?id=<?php echo $subject_id; ?>" method="POST">
            <label for="name">Name:</label>
            <input type="text" id="name" name="name" class="w-75" required autocomplete="off">
            <button type="submit">Add Student</button>
        </form>

        <!-- Display class records -->
        <h2>Class Records</h2>
        <table>
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
            </tr>
            <?php
            if ($class_records_result->num_rows > 0) {
                while ($record = $class_records_result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($record['student_name']) . "</td>";
                    echo "<td>" . htmlspecialchars($record['attendance']) . "</td>";
                    echo "<td>" . htmlspecialchars($record['quiz']) . "</td>";
                    echo "<td>" . htmlspecialchars($record['project']) . "</td>";
                    echo "<td>" . htmlspecialchars($record['recitation']) . "</td>";
                    echo "<td>" . htmlspecialchars($record['behavior']) . "</td>";
                    echo "<td>" . htmlspecialchars($record['prelim_exam']) . "</td>";
                    echo "<td>" . htmlspecialchars($record['midterm_exam']) . "</td>";
                    echo "<td>" . htmlspecialchars($record['final_exam']) . "</td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='9'>No class records found.</td></tr>";
            }
            ?>
        </table>
      
    </div>
</body>
</html>
