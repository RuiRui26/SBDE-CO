<?php
include '../../DB_connection/db.php';

$db = new Database();
$conn = $db->getConnection();

if (isset($_POST['save'])) {
    $title = $_POST['title'];
    $details = $_POST['details'];
    $type = $_POST['type'];

    $stmt = $conn->prepare("INSERT INTO requirements (title, details, type) VALUES (?, ?, ?)");
    $stmt->execute([$title, $details, $type]);

    header("Location: requirements_setting.php");
    exit();
}

if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM requirements WHERE id = ?");
    $stmt->execute([$id]);

    header("Location: requirements_setting.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Requirements | Admin</title>
    <link rel="icon" type="image/png" href="img2/logo.png">
    <link rel="stylesheet" href="css/setting_pages.css">
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f4f7fc;
            padding: 40px;
        }

        .container {
            max-width: 1000px;
            margin: auto;
            background: #fff;
            padding: 30px 40px;
            border-radius: 10px;
            box-shadow: 0 4px 16px rgba(0,0,0,0.1);
        }

        h2, h3 {
            color: #333;
            margin-bottom: 10px;
        }

        textarea, input[type="text"], select {
            width: 100%;
            padding: 12px;
            font-size: 15px;
            margin-top: 8px;
            border-radius: 6px;
            border: 1px solid #ccc;
            margin-bottom: 20px;
        }

        button {
            background: #007BFF;
            color: white;
            border: none;
            padding: 12px 25px;
            font-size: 16px;
            border-radius: 6px;
            cursor: pointer;
        }

        button:hover {
            background: #0056b3;
        }

        .success {
            background-color: #d4edda;
            padding: 15px;
            border-left: 5px solid #28a745;
            margin-bottom: 20px;
            border-radius: 6px;
            color: #155724;
        }

        table {
            width: 100%;
            margin-top: 25px;
            border-collapse: collapse;
            background-color: white;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        td, th {
            padding: 12px;
            border: 1px solid #ccc;
            text-align: left;
        }

        th {
            background-color: #f1f1f1;
        }

        a {
            color: #007bff;
            text-decoration: none;
            margin: 0 5px;
        }

        a:hover {
            text-decoration: underline;
        }

        .back-link {
            margin-top: 20px;
            display: block;
            text-decoration: none;
            font-weight: bold;
            color: #007BFF;
        }

        .back-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Edit Insurance Requirements</h2>

    <!-- Form Section -->
    <form method="POST">
        <h3>Requirement Title</h3>
        <input type="text" name="title" placeholder="Requirement Title" required>

        <h3>Requirement Details</h3>
        <textarea name="details" placeholder="Requirement Details" required></textarea>

        <h3>Requirement Type</h3>
        <select name="type" required>
            <option value="apply-insurance">Apply Insurance</option>
            <option value="for-retrieval">Lost Document</option>
        </select>

        <button type="submit" name="save">Add Requirement</button>
    </form>

    <!-- Success Message -->
    <?php if (!empty($success)): ?>
        <div class="success">Requirement added successfully!</div>
    <?php endif; ?>

    <!-- Back Link -->
    <a href="page_management.php" class="back-link">&larr; Back to Page Management</a>

    <!-- Requirements Table -->
    <?php
    $stmt = $conn->query("SELECT * FROM requirements");
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "<table><tr><th>Title</th><th>Type</th><th>Actions</th></tr>";
    foreach ($results as $row) {
        echo "<tr>
            <td>" . htmlspecialchars($row['title']) . "</td>
            <td>" . htmlspecialchars($row['type']) . "</td>
            <td>
                <a href='requirements_setting.php?delete=" . $row['id'] . "' onclick='return confirm(\"Are you sure you want to delete this requirement?\")'>Delete</a>
            </td>
        </tr>";
    }
    echo "</table>";
    ?>
</div>

</body>
</html>
