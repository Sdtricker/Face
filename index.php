<?php

function image_url_to_base64($image_url) {
    $image_data = file_get_contents($image_url);
    if ($image_data === false) {
        die(json_encode(["error" => "Error fetching image from URL: $image_url"]));
    }
    return base64_encode($image_data);
}

// API Key
$api_key = "SG_b5f8f712e9924783";

// API Endpoint
$url = "https://api.segmind.com/v1/sd2.1-faceswapper";

// Check API request type
if (!isset($_GET['function'])) {
    die(json_encode(["error" => "Missing 'function' parameter"]));
}

$function = $_GET['function'];

// Store face image
if ($function === "store_face") {
    if (!isset($_GET['face_img'])) {
        die(json_encode(["error" => "Missing 'face_img' parameter"]));
    }

    $face_img = $_GET['face_img'];
    
    // Generate a unique filename for each user
    $user_id = isset($_GET['user_id']) ? $_GET['user_id'] : uniqid(); 
    file_put_contents("face_{$user_id}.txt", $face_img);
    
    echo json_encode(["status" => "success", "message" => "Face image stored", "user_id" => $user_id]);
}

// Process face swap
elseif ($function === "create_task") {
    if (!isset($_GET['target_img']) || !isset($_GET['user_id'])) {
        die(json_encode(["error" => "Missing 'target_img' or 'user_id' parameter"]));
    }

    $user_id = $_GET['user_id'];
    $face_img = file_get_contents("face_{$user_id}.txt");
    if (!$face_img) {
        die(json_encode(["error" => "No stored face image found for user {$user_id}"]));
    }

    $target_img = $_GET['target_img'];

    // Convert URLs to Base64
    $input_face_image = image_url_to_base64($face_img);
    $target_face_image = image_url_to_base64($target_img);

    // API Request Data
    $data = [
        "input_face_image" => $input_face_image,
        "target_face_image" => $target_face_image,
        "file_type" => "png",
        "face_restore" => true
    ];

    // cURL Request Setup
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "x-api-key: $api_key",
        "Content-Type: application/json"
    ]);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    // Execute API Request
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($http_code == 200) {
        $output_file = "output_" . time() . ".png";
        file_put_contents($output_file, $response);

        // Adjust domain accordingly
        $image_url = "https://alipakcyber.pw/uploads/" . $output_file;

        echo json_encode(["status" => "success", "image_url" => $image_url]);
    } else {
        echo json_encode(["error" => "Face swap failed", "http_code" => $http_code]);
    }
}

// Check Task Status
elseif ($function === "check_task") {
    if (!isset($_GET['task_id'])) {
        die(json_encode(["error" => "Missing 'task_id' parameter"]));
    }

    // Simulating the task checking. Replace with actual task checking logic
    $task_id = $_GET['task_id'];

    // For simulation, assuming task is completed
    $image_url = "https://alipakcyber.pw/uploads/output_".time().".png"; // Change with actual result image URL
    
    echo json_encode(["status" => "success", "result_img" => $image_url]);
}

// Invalid function
else {
    echo json_encode(["error" => "Invalid function"]);
}
?>