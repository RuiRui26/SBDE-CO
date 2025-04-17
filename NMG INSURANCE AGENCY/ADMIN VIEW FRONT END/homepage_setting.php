<?php
session_start(); 
$allowed_roles = ['Admin'];
require '../../Logout_Login/Restricted.php';
include '../../DB_connection/db.php';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    foreach ($_POST['content'] as $section => $content) {
        $stmt = $pdo->prepare("INSERT INTO page_content (page_name, section_name, content) 
                              VALUES ('index', ?, ?)
                              ON DUPLICATE KEY UPDATE content = ?");
        $stmt->execute([$section, $content, $content]);
    }
    $success = "Homepage content updated successfully!";
}

// Get current content
$stmt = $pdo->prepare("SELECT section_name, content FROM page_content WHERE page_name = 'index.php'");
$stmt->execute();
$sections = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Homepage Settings</title>
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="css/page_management.css">
    <!-- Include CKEditor for rich text editing -->
    <script src="https://cdn.ckeditor.com/4.16.2/standard/ckeditor.js"></script>
</head>
<body>
    <?php include 'sidebar.php'; ?>

    <div class="main-content">
        <h1>Homepage Settings</h1>
        
        <?php if (isset($success)): ?>
            <div class="alert success"><?= $success ?></div>
        <?php endif; ?>

        <form method="post" class="page-edit-form">
            <div class="form-section">
                <h3>Main Heading</h3>
                <textarea name="content[main_heading]" class="ckeditor"><?= $sections['main_heading'] ?? 'Drive with Confidence, Ensure with Trust.' ?></textarea>
            </div>

            <div class="form-section">
                <h3>Main Paragraph</h3>
                <textarea name="content[main_paragraph]" class="ckeditor"><?= $sections['main_paragraph'] ?? 'A trusted non-life insurance to ensure the safety coverage of vehicle accidents. To give a bright future ahead within the road.' ?></textarea>
            </div>

            <div class="form-section">
                <h3>View Requirements Button Text</h3>
                <input type="text" name="content[view_req_text]" value="<?= $sections['view_req_text'] ?? 'View Requirements' ?>">
            </div>

            <div class="form-section">
                <h3>Apply Here Button Text</h3>
                <input type="text" name="content[apply_here_text]" value="<?= $sections['apply_here_text'] ?? 'Apply Here' ?>">
            </div>

            <button type="submit" class="btn-save">Save Changes</button>
        </form>
    </div>

    <script>
        // Initialize all CKEditor instances
        document.querySelectorAll('.ckeditor').forEach(textarea => {
            CKEDITOR.replace(textarea.name);
        });
    </script>
</body>
</html>