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

    // Validate the post_id
    if ($post_id > 0) {
        // Prepare the SQL query to fetch all comments for the given post_id
        $stmt = $conn->prepare("
                                    SELECT 
                                    a.id, 
                                    a.user_id, 
                                    a.content, 
                                    DATE_FORMAT(a.created_at, '%d/%m/%Y %h:%i %p') as created_date,
                                    b.avatar,
                                    b.name 
                                    FROM comments as a
                                    join users as b on a.user_id = b.id
                                    WHERE a.post_id = ? AND a.comment_delete = 0 ORDER BY a.created_at ASC
                              ");
        $stmt->bind_param("i", $post_id);
        
        // Execute the query
        $stmt->execute();
        
        // Get the result set
        $result = $stmt->get_result();

        // Fetch all comments as an associative array
        $comments = $result->fetch_all(MYSQLI_ASSOC);

        // Return the comments as a JSON response
        echo json_encode([
            'status' => 'success',
            'comments' => $comments
        ]);
    } else {
        // Invalid post_id
        echo json_encode([
            'status' => 'error',
            'message' => 'Invalid post ID.'
        ]);
    }

    $stmt->close();
} else {
    // Invalid request method
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid request method.'
    ]);
}

$conn->close();
?>
