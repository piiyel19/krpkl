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
    $user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
    $type = isset($_POST['type']) ? intval($_POST['type']) : 0; // 1 = text, 2 = img

    if ($user_id > 0 && ($type === 1 || $type === 2)) {
        // Prepare the SQL query based on the type
        if ($type == 1) {
            $stmt = $conn->prepare("
                                select 
                                *
                                from 
                                (
                                select 
                                @post_id := a.id as id, a.content, a.created_at,
                                (select image_url from images where post_id = @post_id limit 1) as img 
                                from posts as a
                                where 
                                a.status = 'active'
                                and 
                                a.user_id = ?
                                ) as main
                                where img is null
                                order by created_at desc
                              ");
        } else {
            $stmt = $conn->prepare("
                                select 
                                *
                                from 
                                (
                                select 
                                @post_id := a.id as id, a.content, a.created_at,
                                (select image_url from images where post_id = @post_id limit 1) as img 
                                from posts as a
                                where 
                                a.status = 'active'
                                and 
                                a.user_id = ?
                                ) as main
                                where img is not null
                                order by created_at desc
                              ");
        }
        
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $posts = [];

        while ($row = $result->fetch_assoc()) {
            $posts[] = $row;
        }

        // Return the posts as JSON
        echo json_encode($posts);
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
