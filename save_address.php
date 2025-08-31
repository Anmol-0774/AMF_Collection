<?php
session_start();
require "db.php";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    header('Content-Type: application/json');
    
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'message' => 'User not logged in']);
        exit;
    }
    
    $user_id = $_SESSION['user_id'];
    $recipient_name = mysqli_real_escape_string($conn, $_POST['recipient_name']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $province = mysqli_real_escape_string($conn, $_POST['province']);
    $district = mysqli_real_escape_string($conn, $_POST['district']);
    $city = mysqli_real_escape_string($conn, $_POST['city']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);
    $landmark = mysqli_real_escape_string($conn, $_POST['landmark']);
    $address_category = mysqli_real_escape_string($conn, $_POST['address_category']);
    $save_address = isset($_POST['save_address']);
    
    // Validate required fields
    if (empty($recipient_name) || empty($phone) || empty($province) || 
        empty($district) || empty($city) || empty($address) || empty($address_category)) {
        echo json_encode(['success' => false, 'message' => 'All required fields must be filled']);
        exit;
    }
    
    if ($save_address) {
        // Save to database
        $stmt = $conn->prepare("INSERT INTO user_addresses (user_id, recipient_name, phone, province, district, city, address, landmark, address_category) 
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("issssssss", $user_id, $recipient_name, $phone, $province, $district, $city, $address, $landmark, $address_category);
        
        if ($stmt->execute()) {
            $address_id = $conn->insert_id;
            echo json_encode(['success' => true, 'address_id' => $address_id]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
        }
    } else {
        // Just return success without saving to database
        echo json_encode(['success' => true, 'address_id' => 'temp']);
    }
}
?>