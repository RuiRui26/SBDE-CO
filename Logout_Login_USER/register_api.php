<?php
require_once '../DB_connection/db.php'; 
header("Content-Type: application/json");
session_start();

$response = ["success" => false, "message" => "An error occurred."];

try {
    $database = new Database();
    $db = $database->getConnection();

    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        // Get form data (adjusting for the updated field names in your form)
        $first_name = trim($_POST["first_name"] ?? "");
        $last_name = trim($_POST["last_name"] ?? "");
        $email = trim($_POST["email"] ?? "");
        $contact_number = trim($_POST["contact_number"] ?? "");
        $street_address = trim($_POST["street_address"] ?? "");
        $zip_code = trim($_POST["zip_code"] ?? "");
        $city = trim($_POST["city"] ?? "");
        $barangay = trim($_POST["barangay"] ?? "");
        $password = $_POST["password"] ?? "";

        // Combine address fields into a single address string
        $address = $street_address . ", " . $barangay . ", " . $city . " " . $zip_code;

        if (empty($first_name) || empty($last_name) || empty($email) || empty($contact_number) || empty($address) || empty($password)) {
            $response["message"] = "All fields are required.";
            echo json_encode($response);
            exit;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $response["message"] = "Invalid email format.";
            echo json_encode($response);
            exit;
        }

        if (!preg_match("/^[0-9]{11}$/", $contact_number)) {
            $response["message"] = "Invalid phone number. Must be exactly 11 digits.";
            echo json_encode($response);
            exit;
        }

        if (strlen($password) < 8) {
            $response["message"] = "Password must be at least 8 characters long.";
            echo json_encode($response);
            exit;
        }

        // Check if email or phone number already exists in `users`
        $checkStmt = $db->prepare("SELECT user_id FROM users WHERE email = :email OR contact_number = :contact_number");
        $checkStmt->execute([
            ":email" => $email,
            ":contact_number" => $contact_number
        ]);

        if ($checkStmt->rowCount() > 0) {
            $response["message"] = "Email or phone number already registered.";
            echo json_encode($response);
            exit;
        }

        // Start transaction
        $db->beginTransaction();

        $hashed_password = password_hash($password, PASSWORD_BCRYPT);

        // Insert into `users`
        $stmtUser = $db->prepare("INSERT INTO users (name, email, contact_number, password, role) 
                                  VALUES (:name, :email, :contact_number, :password, 'Client')");
        $stmtUser->execute([
            ":name" => $first_name . " " . $last_name,
            ":email" => $email,
            ":contact_number" => $contact_number,
            ":password" => $hashed_password
        ]);

        // Get the last inserted `user_id`
        $user_id = $db->lastInsertId();

        if ($user_id) {
            // Insert into `clients`
            $stmtClient = $db->prepare("INSERT INTO clients (full_name, email, contact_number, address, user_id) 
                                        VALUES (:full_name, :email, :contact_number, :address, :user_id)");
            $stmtClient->execute([
                ":full_name" => $first_name . " " . $last_name,
                ":email" => $email,
                ":contact_number" => $contact_number,
                ":address" => $address,
                ":user_id" => $user_id
            ]);

            // Commit transaction
            $db->commit();

            $response["success"] = true;
            $response["message"] = "Registration successful!";
        } else {
            $db->rollback();
            $response["message"] = "Failed to register user.";
        }
    } else {
        $response["message"] = "Invalid request method.";
    }
} catch (PDOException $e) {
    $db->rollback();
    error_log("Database error: " . $e->getMessage());
    $response["message"] = "An unexpected error occurred. Please try again later.";
}

echo json_encode($response);
?>
