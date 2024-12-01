<?php
// Start the session
session_start();

// Database connection
$servername = "localhost";
$dbUsername = "u843230181_Amity2"; // Renamed to avoid conflict with form variable
$dbPassword = "Amitydb123";
$dbname = "u843230181_Amitydb2";

$conn = new mysqli($servername, $dbUsername, $dbPassword, $dbname);

if ($conn->connect_error) {
    die(json_encode(array("success" => "0", "message" => "Database connection failed.")));
}

// Initialize variables
$error = '';

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get the username and password from the form
    $inputUsername = $_POST['username'];
    $inputPassword = $_POST['password'];

    // Prepare and execute the SQL query
    $sql = "SELECT * FROM websiteLogin WHERE username = ? LIMIT 1";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("s", $inputUsername);
        $stmt->execute();
        $result = $stmt->get_result();
        
        // Check if the user exists
        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();

            // Verify the password (assuming the password is hashed)
            if (password_verify($inputPassword, $user['password'])) {
                // Store user information in the session
                $_SESSION['username'] = $user['username'];

                // Corrected redirect line
                header("Location: Dashboard.php");
                exit();
            } else {
                $error = "Invalid password.";
            }
        } else {
            $error = "Invalid username.";
        }

        // Close the prepared statement
        $stmt->close();
    } else {
        $error = "Failed to prepare the statement.";
    }
}

// Close the connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="CSS/Login.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,100..1000;1,9..40,100..1000&display=swap" rel="stylesheet">
</head>
<body>
    <div class="container">
        <h1>Log In</h1>
        <p>Enter your email and password to sign in!</p>

        <?php if ($error != ''): ?>
            <p style="color:red;"><?php echo $error; ?></p>
        <?php endif; ?>

        <form action="" method="POST">
            <input type="text" id="username" name="username" required placeholder="Username"><br><br>
            <input type="password" id="password" name="password" required placeholder="Password"><br><br>
            <div class="checkbox">
                <input type="checkbox" id="remember-me" name="remember-me">
                <label for="remember-me">Keep me logged in</label>
                <div class="forgot-password">
                    <a href="ForgotE.html">Forgot password?</a>
                </div>
            </div>
            <button type="submit">Sign In</button>
        </form>
    </div>
    <div class="trademark">© 2024 Amity Medical Clinic. All Rights Reserved.</div>
</body>
</html>
