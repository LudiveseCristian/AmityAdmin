<?php
// Start the session
session_start();

// Enable error reporting for debugging purposes
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


// Database connection
$servername = "localhost";
$username = "u843230181_Amity2";
$password = "Amitydb123";
$dbname = "u843230181_Amitydb2";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die(json_encode(array("success" => "0", "message" => "Database connection failed.")));

}
// Initialize variables
$success = '';
$error = '';

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get the username and password from the form
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Debugging - Check if POST is working
    if (empty($username) || empty($password)) {
        $error = "Username or Password is missing.";
        error_log("Missing fields");
    } else {
        // Check if the username already exists
        $sql = "SELECT * FROM websiteLogin WHERE username = ? LIMIT 1";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $error = "Username already exists.";
            } else {
                // Hash the password
                $hashed_password = password_hash($password, PASSWORD_BCRYPT);

                // Insert the new user into the database
                $sql = "INSERT INTO websiteLogin (username, password) VALUES (?, ?)";
                if ($stmt = $conn->prepare($sql)) {
                    $stmt->bind_param("ss", $username, $hashed_password);
                    if ($stmt->execute()) {
                        $success = "Registration successful. You can now log in.";
                        // Redirect to the dashboard only if successful
                        header("Location: dashboard.php");
                        exit; // Ensure no further code is executed after redirection
                    } else {
                        $error = "Failed to register.";
                        error_log("Database error: " . $stmt->error); // Log DB error
                    }
                } else {
                    $error = "Failed to prepare the statement.";
                    error_log("Statement preparation error: " . $conn->error); // Log preparation error
                }
            }

            // Close the statement
            $stmt->close();
        } else {
            $error = "Query preparation failed.";
            error_log("Query preparation failed: " . $conn->error); // Log query error
        }
    }

    // Close the connection
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up</title>
    <link rel="stylesheet" href="CSS/Login.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,100..1000;1,9..40,100..1000&display=swap" rel="stylesheet">
</head>
<body>
    <div class="container">
        <h1>Sign Up</h1>
        <p>Enter your details to create an account.</p>

        <!-- Display success or error messages -->
        <?php if ($error != ''): ?>
            <p style="color:red;"><?php echo $error; ?></p>
        <?php endif; ?>

        <?php if ($success != ''): ?>
            <p style="color:green;"><?php echo $success; ?></p>
        <?php endif; ?>

        <form action="" method="POST">
            <input type="text" id="username" name="username" required placeholder="Username"><br><br>
            <input type="password" id="password" name="password" required placeholder="Password"><br><br>
            <button type="submit">Sign Up</button>
        </form>

        <p>Already have an account? <a href="Login.php">Log in here</a>.</p>
    </div>
    <div class="trademark">© 2024 Amity Medical Clinic. All Rights Reserved.</div>
    <script src="JS/Login.js"></script>
</body>
</html>
