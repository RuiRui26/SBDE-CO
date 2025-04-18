<?php
include '../../DB_connection/db.php';
$db = new Database();
$conn = $db->getConnection();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add'])) {
        // Add new benefit
        $title = $_POST['title'];
        $description = $_POST['description'];

        // Handle image upload
        $image = $_FILES['image']['name'];
        $targetDir = "uploads/";
        $targetFile = $targetDir . basename($image);
        $uploadOk = 1;
        $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

        // Check if the image file is a real image
        if (isset($_POST['submit'])) {
            $check = getimagesize($_FILES['image']['tmp_name']);
            if ($check !== false) {
                $uploadOk = 1;
            } else {
                $uploadOk = 0;
                echo "File is not an image.";
            }
        }

        // Check if file already exists
        if (file_exists($targetFile)) {
            $uploadOk = 0;
            echo "Sorry, file already exists.";
        }

        // Check file size
        if ($_FILES['image']['size'] > 500000) {  // 500KB max size
            $uploadOk = 0;
            echo "Sorry, your file is too large.";
        }

        // Allow only certain file formats
        if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif") {
            $uploadOk = 0;
            echo "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
        }

        // Attempt to upload the image if no errors
        if ($uploadOk == 0) {
            echo "Sorry, your file was not uploaded.";
        } else {
            if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
                // Insert into database
                $query = "INSERT INTO benefits_contents (title, description, image) VALUES (?, ?, ?)";
                $stmt = $conn->prepare($query);
                $stmt->execute([$title, $description, $targetFile]);
            } else {
                echo "Sorry, there was an error uploading your file.";
            }
        }
    } elseif (isset($_POST['edit'])) {
        // Edit existing benefit
        $id = $_POST['id'];
        $title = $_POST['title'];
        $description = $_POST['description'];

        // Handle image upload if a new image is provided
        if ($_FILES['image']['name']) {
            $image = $_FILES['image']['name'];
            $targetDir = "uploads/";
            $targetFile = $targetDir . basename($image);
            $uploadOk = 1;
            $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

            // Check if the image file is a real image
            if (isset($_POST['submit'])) {
                $check = getimagesize($_FILES['image']['tmp_name']);
                if ($check !== false) {
                    $uploadOk = 1;
                } else {
                    $uploadOk = 0;
                    echo "File is not an image.";
                }
            }

            // Check if file already exists
            if (file_exists($targetFile)) {
                $uploadOk = 0;
                echo "Sorry, file already exists.";
            }

            // Check file size
            if ($_FILES['image']['size'] > 500000) {  // 500KB max size
                $uploadOk = 0;
                echo "Sorry, your file is too large.";
            }

            // Allow only certain file formats
            if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif") {
                $uploadOk = 0;
                echo "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
            }

            // Attempt to upload the image if no errors
            if ($uploadOk == 0) {
                echo "Sorry, your file was not uploaded.";
            } else {
                if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
                    // Update the record with the new image
                    $query = "UPDATE benefits_contents SET title = ?, description = ?, image = ? WHERE id = ?";
                    $stmt = $conn->prepare($query);
                    $stmt->execute([$title, $description, $targetFile, $id]);
                } else {
                    echo "Sorry, there was an error uploading your file.";
                }
            }
        } else {
            // Update without changing the image
            $query = "UPDATE benefits_contents SET title = ?, description = ? WHERE id = ?";
            $stmt = $conn->prepare($query);
            $stmt->execute([$title, $description, $id]);
        }
    }
}

$benefits = $conn->query("SELECT * FROM benefits_contents")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Manage Benefits</title>
    <link rel="stylesheet" href="css/setting_pages.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f5f5f5;
            padding: 40px;
        }

        .container {
            max-width: 1000px;
            margin: auto;
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 0 12px rgba(0,0,0,0.1);
        }

        h2 {
            margin-bottom: 20px;
            color: #333;
        }

        .insurance-block {
            margin-bottom: 30px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 20px;
        }

        label {
            display: block;
            margin: 10px 0 5px;
            font-weight: bold;
        }

        input[type="text"], textarea {
            width: 100%;
            padding: 10px;
            margin-bottom: 10px;
            font-size: 15px;
        }

        textarea {
            min-height: 80px;
        }

        input[type="file"] {
            margin-top: 5px;
        }

        .img-preview {
            margin: 10px 0;
        }

        .img-preview img {
            max-height: 100px;
            border-radius: 5px;
            border: 1px solid #ccc;
        }

        .actions {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }

        .actions button, .back-link {
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            font-size: 15px;
            cursor: pointer;
        }

        .save-btn {
            background-color: #007bff;
            color: white;
        }

        .add-btn {
            background-color: #28a745;
            color: white;
        }

        .delete-btn {
            background-color: #dc3545;
            color: white;
        }

        .back-link {
            background: none;
            color: #007bff;
            text-decoration: none;
            font-weight: bold;
        }

        .back-link:hover {
            text-decoration: underline;
        }
</style>

</head>
<body>
    <h1>Manage Benefits</h1>
    
    <form method="POST" enctype="multipart/form-data">
        <h3>Add New Benefit</h3>
        <input type="text" name="title" placeholder="Benefit Title" required><br>
        <textarea name="description" placeholder="Benefit Description" required></textarea><br>
        <input type="file" name="image" required><br>
        <button type="submit" name="add">Add Benefit</button>
    </form>

    <h3>Existing Benefits</h3>
    <table>
        <tr>
            <th>Title</th>
            <th>Description</th>
            <th>Image</th>
            <th>Actions</th>
        </tr>
        <?php foreach ($benefits as $benefit): ?>
            <tr>
                <td><?= $benefit['title'] ?></td>
                <td><?= $benefit['description'] ?></td>
                <td><img src="<?= $benefit['image'] ?>" alt="<?= $benefit['title'] ?>" width="100"></td>
                <td>
                    <form method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="id" value="<?= $benefit['id'] ?>">
                        <input type="text" name="title" value="<?= $benefit['title'] ?>" required><br>
                        <textarea name="description" required><?= $benefit['description'] ?></textarea><br>
                        <input type="file" name="image"><br>
                        <button type="submit" name="edit">Update</button>
                        <a href="page_management.php" style="
        display: inline-block;
        margin-bottom: 20px;
        text-decoration: none;
        color: #007BFF;
        font-weight: bold;
    ">&larr; Back to Page Management</a>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>
