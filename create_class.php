<?php
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Class</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <h1>Create New Class</h1>
        <form action="create_class.php" method="POST">
            <input type="text" name="class_name" placeholder="Class Name" required>
            <button type="submit">Create Class</button>
        </form>
    </div>
</body>
</html>
