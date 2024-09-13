<?php

header("Access-Control-Allow-Origin: *"); // Adjust as needed
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit();
}



include('db-connection.php'); // Adjust the path as necessary

// Check if required POST data is received
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get POST data
    $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;

    if ($post_id > 0) {
        // Check if the like already exists for this user and post
        $stmt = $conn->prepare("SELECT id FROM likes WHERE post_id = ?");
        $stmt->bind_param("i", $post_id);
        $stmt->execute();
        $stmt->store_result();

        $like_exists = $stmt->num_rows > 0 ? true : false;

        // Fetch the total count of likes for the post
        $stmt = $conn->prepare("SELECT COUNT(*) AS like_count FROM likes WHERE post_id = ?");
        $stmt->bind_param("i", $post_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $like_count = $result->fetch_assoc()['like_count'];

        // Return the response as JSON
        echo json_encode([
            "like_exists" => $like_exists,
            "like_count" => $like_count
        ]);
    } else {
        echo json_encode([
            "error" => "Invalid input"
        ]);
    }
} else {
    echo json_encode([
        "error" => "Invalid request method"
    ]);
}

?>
