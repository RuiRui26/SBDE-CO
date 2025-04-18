<?php
include '../../DB_connection/db.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $conn = (new Database())->getConnection();  // Make sure $conn is properly initialized
    foreach ($_POST as $key => $value) {
        list($section, $content_key) = explode("__", $key);
        $stmt = $conn->prepare("UPDATE homepage_content SET content_value = :value WHERE content_key = :content_key AND section = :section");
        $stmt->bindParam(":value", $value);
        $stmt->bindParam(":content_key", $content_key);
        $stmt->bindParam(":section", $section);
        $stmt->execute();
    }
    echo "<script>alert('Homepage content updated successfully!'); window.location.href='homepage_setting.php';</script>";
    exit;
}

$conn = (new Database())->getConnection();  // Make sure $conn is initialized here as well
$stmt = $conn->query("SELECT * FROM homepage_content");
$content = [];
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $content[$row['section'] . "__" . $row['content_key']] = $row['content_value'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Homepage Content</title>
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
        background: white;
        padding: 30px 40px;
        border-radius: 10px;
        box-shadow: 0 4px 16px rgba(0,0,0,0.1);
    }

    h2 {
        text-align: center;
        color: #333;
        margin-bottom: 30px;
    }

    label {
        font-weight: bold;
        margin-top: 15px;
        display: block;
    }

    input[type="text"], textarea {
        width: 100%;
        padding: 12px;
        border-radius: 6px;
        border: 1px solid #ccc;
        font-size: 15px;
        margin-top: 8px;
        margin-bottom: 20px;
    }

    button {
        background: #007bff;
        color: white;
        padding: 12px 25px;
        font-size: 16px;
        border-radius: 6px;
        border: none;
        cursor: pointer;
        margin-top: 20px;
    }

    button:hover {
        background: #0056b3;
    }

    .back-link {
        display: inline-block;
        margin-top: 20px;
        text-decoration: none;
        color: #007BFF;
        font-weight: bold;
    }

    .back-link:hover {
        text-decoration: underline;
    }
</style>

    </style>
</head>
<body>
    <h2>Edit Homepage Content</h2>
    <form method="POST">
        <?php foreach ($content as $key => $value): ?>
            <label><?= ucfirst(str_replace("__", " - ", $key)) ?>:</label>
            <textarea name="<?= $key ?>" rows="3"><?= htmlspecialchars($value) ?></textarea><br>
        <?php endforeach; ?>
        <button type="submit">Save Changes</button>
        <a href="page_management.php" style="
        display: inline-block;
        margin-bottom: 20px;
        text-decoration: none;
        color: #007BFF;
        font-weight: bold;
    ">&larr; Back to Page Management</a>
    </form>
</body>
</html>
