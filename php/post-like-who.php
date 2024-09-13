<?php 

header("Access-Control-Allow-Origin: *"); // Adjust as needed
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// Handle preflight requests for CORS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit();
}

include('db-connection.php'); // Adjust the path as necessary

// Check if required POST data is received
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get POST data
    $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;

    // Proceed if valid post_id is provided
    if ($post_id > 0) {
        // Prepare the SQL query to fetch users who liked the post
        $sql = "
            SELECT 
                b.id, b.name, b.avatar, DATE_FORMAT(a.created_at,'%d/%m/%Y %H:%i %p') as created_at
            FROM likes AS a
            JOIN users AS b ON a.user_id = b.id
            WHERE a.post_id = ?
            ORDER BY a.created_at DESC
        ";

        // Prepare the statement to prevent SQL injection
        if ($stmt = $conn->prepare($sql)) {
            // Bind the post_id to the SQL query
            $stmt->bind_param("i", $post_id);

            // Execute the query
            $stmt->execute();

            // Get the result
            $result = $stmt->get_result();

            // Fetch all rows as an associative array
            $users = $result->fetch_all(MYSQLI_ASSOC);

            // Return the result as a JSON response
            echo json_encode($users);

            // Close the statement
            $stmt->close();
        } else {
            // If SQL statement failed to prepare
            http_response_code(500);
            echo json_encode(["error" => "Failed to prepare SQL statement."]);
        }
    } else {
        // If no valid post_id is provided
        http_response_code(400); // Bad Request
        echo json_encode(["error" => "Invalid post ID."]);
    }
} else {
    // If the request method is not POST
    http_response_code(405); // Method Not Allowed
    echo json_encode(["error" => "POST request required."]);
}
?>
