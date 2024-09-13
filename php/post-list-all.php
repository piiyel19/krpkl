<?php

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

// Handle preflight request for CORS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit();
}

// Database connection
include('db-connection.php'); // Adjust the path

// Get the page and limit from POST data
$page = isset($_POST['page']) ? intval($_POST['page']) : 1;
$limit = isset($_POST['limit']) ? intval($_POST['limit']) : 3;
$offset = ($page - 1) * $limit;

// Query to get posts with pagination
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
    WHERE posts.status = 'active'
    ORDER BY posts.created_at DESC
    LIMIT ? OFFSET ?
";

// Prepare the statement
$stmt = $conn->prepare($query);
$stmt->bind_param('ii', $limit, $offset); // Bind the limit and offset values
$stmt->execute();
$result = $stmt->get_result();

// Fetch posts and format them into an array
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

// Return the JSON response
echo json_encode($posts);
