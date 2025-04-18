<?php
include '../../DB_connection/db.php'; // Your database connection

// Create a new Database object
$database = new Database();
$conn = $database->getConnection(); // Get PDO connection

// Fetch current content
$query = $conn->prepare("SELECT * FROM contact_content LIMIT 1");
$query->execute();
$content = $query->fetch(PDO::FETCH_ASSOC);

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $heading = htmlspecialchars($_POST['heading']);
    $description = htmlspecialchars($_POST['description']);
    $address = htmlspecialchars($_POST['address']);
    $hotline = htmlspecialchars($_POST['hotline']);
    $contact = htmlspecialchars($_POST['contact']);

    // Update query (removed map_embed)
    $update = $conn->prepare("UPDATE contact_content SET 
        heading = :heading,
        description = :description,
        address = :address,
        hotline = :hotline,
        contact = :contact
        WHERE id = :id");

    // Bind parameters (removed map_embed)
    $update->bindParam(':heading', $heading);
    $update->bindParam(':description', $description);
    $update->bindParam(':address', $address);
    $update->bindParam(':hotline', $hotline);
    $update->bindParam(':contact', $contact);
    $update->bindParam(':id', $content['id']);

    if ($update->execute()) {
        echo "<script>alert('Contact content updated successfully'); window.location.href='contact_setting.php';</script>";
        exit();
    } else {
        echo "<script>alert('Update failed.');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Contact Page Content</title>
    <link rel="stylesheet" href="css/setting_pages.css">
    <style>
     <!-- Replace the <style> section -->
    body {
        font-family: 'Segoe UI', sans-serif;
        background: #f4f7fc;
        padding: 40px;
    }

    form {
        max-width: 800px;
        margin: auto;
        background: #fff;
        padding: 30px 40px;
        border-radius: 10px;
        box-shadow: 0 4px 16px rgba(0,0,0,0.1);
    }

    h2 {
        text-align: center;
        margin-bottom: 25px;
        color: #333;
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
        margin-top: 6px;
        font-size: 15px;
    }

    textarea {
        height: 120px;
        resize: vertical;
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
        font-weight: bold;
        color: #007BFF;
        text-decoration: none;
    }

    .back-link:hover {
        text-decoration: underline;
    }
</style>

</head>
<body>

    <h2>Edit Contact Page Content</h2>

    <form method="POST" action="">
        <label>Page Heading:</label>
        <input type="text" name="heading" value="<?= htmlspecialchars($content['heading']) ?>" required>

        <label>Description:</label>
        <textarea name="description" required><?= htmlspecialchars($content['description']) ?></textarea>

        <label>Address:</label>
        <input type="text" name="address" value="<?= htmlspecialchars($content['address']) ?>" required>

        <label>Hotline Number:</label>
        <input type="text" name="hotline" value="<?= htmlspecialchars($content['hotline']) ?>" required>

        <label>Contact Number:</label>
        <input type="text" name="contact" value="<?= htmlspecialchars($content['contact']) ?>" required>

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
