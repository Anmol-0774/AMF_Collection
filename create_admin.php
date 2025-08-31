<?php
require "../db.php";
 // your database connection

$name = "Anmol Atia";
$email = "ani.anmol03@gmail.com";
$password = "Admin@123"; // choose a strong password
$role = "admin";

// Hash the password
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

// Insert into users table
$stmt = $conn->prepare("INSERT INTO users (name,email,password,role,created_at) VALUES (?,?,?,?,NOW())");
$stmt->bind_param("ssss",$name,$email,$hashedPassword,$role);

if($stmt->execute()){
    echo "Admin user created successfully!";
} else {
    echo "Error: " . $stmt->error;
}
