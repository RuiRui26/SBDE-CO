<?php
require_once '../DB_connection/db.php'; 
header("Content-Type: application/json");
session_start();

$response = ["success" => false, "message" => "An error occurred."];

try {
    $database = new Database();
    $db = $database->getConnection();

    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        $first_name = trim($_POST["first_name"] ?? "");
        $last_name = trim($_POST["last_name"] ?? "");
        $email = trim($_POST["email"] ?? "");
        $contact_number = trim($_POST["contact_number"] ?? "");
        $street_address = trim($_POST["street_address"] ?? "");
        $zip_code = trim($_POST["zip_code"] ?? "");
        $city = trim($_POST["city"] ?? "");
        $barangay = trim($_POST["barangay"] ?? "");
        $password = $_POST["password"] ?? "";
        $birthday = $_POST["birthday"] ?? "";

        $address = $street_address . ", " . $barangay . ", " . $city . " " . $zip_code;

        // Validate fields
        if (empty($first_name) || empty($last_name) || empty($email) || empty($contact_number) || empty($address) || empty($password) || empty($birthday)) {
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

        $birthDate = DateTime::createFromFormat('Y-m-d', $birthday);
        if (!$birthDate) {
            $response["message"] = "Invalid birthday format.";
            echo json_encode($response);
            exit;
        }

        $today = new DateTime();
        $age = $today->diff($birthDate)->y;

        if ($age < 18) {
            $response["message"] = "You must be at least 18 years old to register.";
            echo json_encode($response);
            exit;
        }

        // Check for duplicates
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

        // Insert into users table
        $stmtUser = $db->prepare("INSERT INTO users (name, email, contact_number, password, role) 
                                  VALUES (:name, :email, :contact_number, :password, 'Client')");
        $stmtUser->execute([
            ":name" => $first_name . " " . $last_name,
            ":email" => $email,
            ":contact_number" => $contact_number,
            ":password" => $hashed_password
        ]);

        $user_id = $db->lastInsertId();

        if ($user_id) {
            // Insert into clients table (now using separate first_name and last_name)
            $stmtClient = $db->prepare("INSERT INTO clients (first_name, last_name, email, contact_number, address, birthday, user_id) 
                                        VALUES (:first_name, :last_name, :email, :contact_number, :address, :birthday, :user_id)");
            $stmtClient->execute([
                ":first_name" => $first_name,
                ":last_name" => $last_name,
                ":email" => $email,
                ":contact_number" => $contact_number,
                ":address" => $address,
                ":birthday" => $birthday,
                ":user_id" => $user_id
            ]);

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
