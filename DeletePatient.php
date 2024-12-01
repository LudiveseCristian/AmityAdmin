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

// Check if the ID is provided
if (isset($_GET['id'])) {
    $patient_id = $_GET['id'];

    // Prepare the delete statement
    $sql = "DELETE FROM patients WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $patient_id);

    if ($stmt->execute()) {
        echo json_encode(array("success" => "1", "message" => "Record deleted successfully."));
    } else {
        echo json_encode(array("success" => "0", "message" => "Error deleting record."));
    }

    $stmt->close();
} else {
    echo json_encode(array("success" => "0", "message" => "No ID provided."));
}

$conn->close();
?>
