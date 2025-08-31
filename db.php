<?php
$host = "localhost";
$user = "root";       // XAMPP default username
$password = "";       // XAMPP default password
$dbname = "amf_collection"; // your database name

$conn = new mysqli($host, $user, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
//echo "Connected successfully"; // optional test
?>
