<?php
session_start(); 
include '../../DB_connection/db.php';
$allowed_roles = ['Admin'];
require '../../Logout_Login/Restricted.php';



$db = new Database();
$conn = $db->getConnection();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $futureText = $_POST['future_text'];
    $visionText = $_POST['vision_text'];
    $missionText = $_POST['mission_text'];

    // Handle image uploads
    $uploadDir = 'img/';
    $images = ['image1', 'image2', 'image3'];
    $uploaded = [];

    foreach ($images as $img) {
        if (isset($_FILES[$img]) && $_FILES[$img]['error'] === UPLOAD_ERR_OK) {
            $fileTmpPath = $_FILES[$img]['tmp_name'];
            $fileName = basename($_FILES[$img]['name']);
            $dest = $uploadDir . $fileName;

            if (move_uploaded_file($fileTmpPath, $dest)) {
                $uploaded[$img] = $dest;
            }
        }
    }

    // Update future section
    $future = $conn->prepare("UPDATE about_content SET content = ?, image1 = COALESCE(?, image1), image2 = COALESCE(?, image2), image3 = COALESCE(?, image3) WHERE section = 'future'");
    $future->execute([
        $futureText,
        $uploaded['image1'] ?? null,
        $uploaded['image2'] ?? null,
        $uploaded['image3'] ?? null
    ]);

    // Update vision
    $vision = $conn->prepare("UPDATE about_content SET content = ? WHERE section = 'vision'");
    $vision->execute([$visionText]);

    // Update mission
    $mission = $conn->prepare("UPDATE about_content SET content = ? WHERE section = 'mission'");
    $mission->execute([$missionText]);

    $success = true;
}

// Get current data
function getSection($conn, $section) {
    $stmt = $conn->prepare("SELECT * FROM about_content WHERE section = ?");
    $stmt->execute([$section]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

$future = getSection($conn, 'future');
$vision = getSection($conn, 'vision');
$mission = getSection($conn, 'mission');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit About Page | Admin</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f5f5f5;
            padding: 40px;
        }

        h2 {
            color: #333;
        }

        .container {
            max-width: 900px;
            margin: auto;
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 0 12px rgba(0,0,0,0.1);
        }

        textarea {
            width: 100%;
            min-height: 120px;
            margin-bottom: 20px;
            padding: 10px;
            font-size: 15px;
        }

        input[type="file"] {
            margin-bottom: 10px;
        }

        .img-preview {
            display: flex;
            gap: 20px;
            margin-bottom: 20px;
        }

        .img-preview img {
            height: 100px;
            object-fit: cover;
            border: 1px solid #ccc;
            padding: 5px;
            border-radius: 4px;
        }

        button {
            padding: 10px 20px;
            background: #007BFF;
            color: white;
            border: none;
            font-size: 16px;
            border-radius: 4px;
            cursor: pointer;
        }

        button:hover {
            background: #0056b3;
        }

        .success {
            background: #d4edda;
            color: #155724;
            padding: 12px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
<div class="container">
    <h2>Edit About Page Content</h2>

    <?php if (!empty($success)): ?>
        <div class="success">Content updated successfully!</div>
    <?php endif; ?>

    <form action="" method="POST" enctype="multipart/form-data">
        <!-- Future Protection -->
        <h3>Future Protection Text</h3>
        <textarea name="future_text"><?= htmlspecialchars($future['content']) ?></textarea>

        <div class="img-preview">
            <div>
                <label>Image 1:</label><br>
                <img src="<?= $future['image1'] ?>" alt=""><br>
                <input type="file" name="image1">
            </div>
            <div>
                <label>Image 2:</label><br>
                <img src="<?= $future['image2'] ?>" alt=""><br>
                <input type="file" name="image2">
            </div>
            <div>
                <label>Image 3:</label><br>
                <img src="<?= $future['image3'] ?>" alt=""><br>
                <input type="file" name="image3">
            </div>
        </div>

        <!-- Vision -->
        <h3>Vision</h3>
        <textarea name="vision_text"><?= htmlspecialchars($vision['content']) ?></textarea>

        <!-- Mission -->
        <h3>Mission</h3>
        <textarea name="mission_text"><?= htmlspecialchars($mission['content']) ?></textarea>

        <button type="submit">Save Changes</button>
    </form>
</div>
</body>
</html>
