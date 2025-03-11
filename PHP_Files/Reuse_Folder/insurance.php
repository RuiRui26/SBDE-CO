function uploadFile($file, $prefix, $userFullName, $uploadDir) {
    $allowedTypes = ["image/jpeg", "image/png", "image/jpg"];
    $maxFileSize = 2 * 1024 * 1024; // 2MB limit

    if ($file["error"] !== UPLOAD_ERR_OK) {
        throw new Exception("File upload error: " . $file["error"]);
    }

    if (!in_array($file["type"], $allowedTypes)) {
        throw new Exception("Invalid file type. Only JPG, JPEG, and PNG are allowed.");
    }

    if ($file["size"] > $maxFileSize) {
        throw new Exception("File size exceeds 2MB limit.");
    }

    // Extract file extension
    $fileExtension = pathinfo($file["name"], PATHINFO_EXTENSION);

    // Format file name: john_doe_OR_1710035000.jpg
    $fileName = $userFullName . "_" . strtoupper($prefix) . "_" . time() . "." . $fileExtension;
    $filePath = $uploadDir . $fileName;

    if (!move_uploaded_file($file["tmp_name"], $filePath)) {
        throw new Exception("Failed to upload file: $fileName");
    }

    return $fileName;
}
