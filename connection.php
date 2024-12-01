<?php
// Database connection
$servername = "localhost";
$username = "u843230181_Amity2";
$password = "Amitydb123";
$dbname = "u843230181_Amitydb2";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die(json_encode(array("success" => "0", "message" => "Database connection failed.")));
}