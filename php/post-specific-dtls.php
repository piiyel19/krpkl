<?php

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

// Handle preflight request for CORS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit();
}

// Database connection
include('db-connection.php'); // Adjust the path

// Get the post_id from POST data
$post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;

if ($post_id <= 0) {
    echo json_encode(['error' => 'Invalid post ID']);
    exit();
}

// Query to get the post details
$query = "
    SELECT 
        posts.id as post_id, 
        posts.content, 
        users.name as name, 
        users.avatar as avatar,
        DATE_FORMAT(posts.created_at, '%d/%m/%Y %h:%i %p') as created_date,
        (SELECT GROUP_CONCAT(image_url) FROM images WHERE images.post_id = posts.id) AS images,
        (SELECT COUNT(*) FROM likes WHERE likes.post_id = posts.id) AS likes
    FROM posts
    INNER JOIN users ON posts.user_id = users.id
    WHERE posts.id = ?
    ORDER BY posts.created_at DESC
";

// Prepare the statement
$stmt = $conn->prepare($query);

if (!$stmt) {
    echo json_encode(['error' => 'Failed to prepare statement']);
    exit();
}

// Bind the post_id value
$stmt->bind_param('i', $post_id);

// Execute the statement
$stmt->execute();

// Get the result
$result = $stmt->get_result();

// Fetch post and format it into an array
$posts = [];
while ($row = $result->fetch_assoc()) {
    // Handle NULL case before exploding
    if ($row['images'] !== null) {
        $row['images'] = explode(',', $row['images']); // Convert image list to an array
    } else {
        $row['images'] = []; // If no images, set to an empty array
    }
    $posts[] = $row;
}

// Close statement and connection
$stmt->close();
$conn->close();

// Return the JSON response
echo json_encode($posts);
