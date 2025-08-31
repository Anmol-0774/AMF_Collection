<?php
$conn = new mysqli("localhost","root","","amf_collection");
if($conn->connect_error) { die("Connection failed: ".$conn->connect_error); }

$result = $conn->query("SELECT SUM(quantity) as total FROM cart");
$row = $result->fetch_assoc();
echo $row['total'] ? $row['total'] : 0;

$conn->close();
?>
