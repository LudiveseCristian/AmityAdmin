<?php
$host = "localhost"; 
$dbname = "u843230181_Amitydb2"; 
$username = "u843230181_Amity2"; 
$password = "Amitydb123"; 

try {
    // Establish the connection to the database
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Check if the request method is POST
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        
        // Collect all form data with basic validation
        $name = trim($_POST['name']);
        $address = trim($_POST['address']);
        $phone = trim($_POST['phone']);
        $gender = trim($_POST['gender']);
        $status = trim($_POST['status']);
        $birthday = trim($_POST['birthday']);
        $checkup_date = trim($_POST['checkup_date']);

        // Collect the vital signs data
        $blood_pressure = trim($_POST['blood_pressure']);
        $pulse_rate = trim($_POST['pulse_rate']);
        $resp_rate = trim($_POST['resp_rate']);
        $weight = trim($_POST['weight']);
        $temperature = trim($_POST['temperature']);

        // Collect the additional fields
        $cc = trim($_POST['cc']);
        $pe = trim($_POST['pe']);
        $dx = trim($_POST['dx']);
        $meds = trim($_POST['meds']);
        $labs = trim($_POST['labs']);

        // Concatenate all vital signs and additional fields into a single string
        $vital_signs = "Blood Pressure: $blood_pressure mmHg, Pulse Rate: $pulse_rate bpm, Resp Rate: $resp_rate breaths/min, 
                        Weight: $weight kg, Temperature: $temperature °C, CC: $cc, PE: $pe, DX: $dx, Meds: $meds, Labs: $labs";

        // Generate the custom ID format (YYMM-XXXXX)
        $year = date('y'); // Last two digits of the year
        $month = date('m'); // Current month

        // Fetch the last ID from the database
        $stmt_last_id = $conn->query("SELECT id FROM patients ORDER BY id DESC LIMIT 1");
        $last_row = $stmt_last_id->fetch(PDO::FETCH_ASSOC);

        // Initialize the order number
        if ($last_row) {
            // Extract the last order number
            $last_id_parts = explode('-', $last_row['id']);
            $last_order_number = (int) (count($last_id_parts) == 2 ? $last_id_parts[1] : 0);
        } else {
            $last_order_number = 0; // Start from 0 if no records exist
        }

        // Increment the order number for the new record
        $new_order_number = $last_order_number + 1;

        // Create the new ID formatted as YYMM-XXXXX
        $new_id = sprintf("%02d%02d-%05d", $year, $month, $new_order_number);

        // Prepare the SQL insert query
        $sql = "INSERT INTO patients (id, name, address, phone, gender, status, birthday, vital_signs, checkup_date, created_at, updated_at) 
                VALUES (:id, :name, :address, :phone, :gender, :status, :birthday, :vital_signs, :checkup_date, NOW(), NOW())";

        // Prepare the statement
        $stmt = $conn->prepare($sql);

        // Bind the parameters to the prepared statement
        $stmt->bindParam(':id', $new_id);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':address', $address);
        $stmt->bindParam(':phone', $phone);
        $stmt->bindParam(':gender', $gender);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':birthday', $birthday);
        $stmt->bindParam(':vital_signs', $vital_signs);
        $stmt->bindParam(':checkup_date', $checkup_date);

        // Execute the query and check for success
        if ($stmt->execute()) {
            http_response_code(200); // OK
            echo json_encode(['success' => true, 'message' => 'Patient added successfully']);
        } else {
            http_response_code(400); // Bad Request
            echo json_encode(['success' => false, 'message' => 'Failed to add patient']);
        }
    }
} catch (PDOException $e) {
    // Return error message if something goes wrong
    http_response_code(500); // Internal Server Error
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
