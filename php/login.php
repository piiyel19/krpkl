<?php

header("Access-Control-Allow-Origin: *"); // Adjust as needed
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit();
}


include('db-connection.php');


// Retrieve the data from the request
$inputUsername = $_POST['username'] ?? null;
$inputPassword = $_POST['password'] ?? null;

// Validate input
if (empty($inputUsername) || empty($inputPassword)) {
    http_response_code(400); // Bad Request
    echo json_encode(["error" => "Username and password fields are required."]);
    $conn->close();
    exit();
}

// Sanitize input
$inputUsername = $conn->real_escape_string($inputUsername);

// Query to check if the user exists
$sql = "SELECT id, username, password_hash, name FROM users WHERE username = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $inputUsername);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    // User not found
    // http_response_code(401); // Unauthorized
    echo json_encode(["error" => "Invalid username or password."]);
    $stmt->close();
    $conn->close();
    exit();
}

// User found, check the password
$user = $result->fetch_assoc();
$storedPassword = $user['password_hash']; // Password from the database

$inputPassword = hash('sha256', $inputPassword);


//var_dump($inputPassword); 
//var_dump($storedPassword);
// Direct password comparison
if ($inputPassword === $storedPassword) {
    // Password is correct
    echo json_encode(array(
            "status" => "success",
            "message" => "Login successful",
            "data" => array(
                "user_id" => $user['id'],
            	"username" => $user['username'],
            	"name" => $user['name']
            )
        ));
} else {
    // Password is incorrect
    // http_response_code(401); // Unauthorized
    echo json_encode(array(
            "status" => "error",
            "message" => "Username and password are incorrect."
        ));
}

// Close connections
$stmt->close();
$conn->close();
?>
