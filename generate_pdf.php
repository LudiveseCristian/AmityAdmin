<?php
// Include TCPDF
require_once 'libs/TCPDF-main/tcpdf.php';

// Database connection details
$servername = "localhost";
$username = "u843230181_Amity2";
$password = "Amitydb123";
$dbname = "u843230181_Amitydb2";

// Create a new database connection
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Check if the 'id' parameter is set
if (isset($_GET['id'])) {
    $patientId = trim($_GET['id']); // Expecting the ID to be formatted as YYMM-XXXXX

    // Log the received ID and GET parameters for debugging
    error_log("Received patient ID: " . $patientId);
    error_log("Received GET parameters: " . print_r($_GET, true));

    // Validate the ID format
    if (preg_match('/^\d{2}\d{2}-\d{5}$/', $patientId)) {
        // Prepare the SQL statement to fetch patient details
        $sql = "SELECT * FROM patients WHERE id = ?";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            die("Prepare failed: " . $conn->error);
        }

        // Bind the patient ID parameter
        $stmt->bind_param("s", $patientId); // Bind as string
        $stmt->execute();
        $result = $stmt->get_result();

        // Check if any patient data is found
        if ($result->num_rows > 0) {
            $patientData = $result->fetch_assoc();

            // Create new PDF document
            $pdf = new TCPDF();

            // Set document information
            $pdf->SetCreator(PDF_CREATOR);
            $pdf->SetAuthor('Your Application Name');
            $pdf->SetTitle('Patient Details');

            // Set default header and footer data
            $pdf->setPrintHeader(false);
            $pdf->setPrintFooter(false);

            // Add a page
            $pdf->AddPage();

            // Set font for the title
            $pdf->SetFont('helvetica', 'B', 16);
            $pdf->Cell(0, 10, 'Patient Details', 0, 1, 'C');

            // Set font for the details
            $pdf->SetFont('helvetica', '', 12);

            // Add patient details to the PDF
            $pdf->Cell(0, 10, 'ID: ' . htmlspecialchars($patientData['id']), 0, 1);
            $pdf->Cell(0, 10, 'Name: ' . htmlspecialchars($patientData['name']), 0, 1);
            $pdf->Cell(0, 10, 'Address: ' . htmlspecialchars($patientData['address']), 0, 1);
            $pdf->Cell(0, 10, 'Phone: ' . htmlspecialchars($patientData['phone']), 0, 1);
            $pdf->Cell(0, 10, 'Gender: ' . htmlspecialchars($patientData['gender']), 0, 1);
            $pdf->Cell(0, 10, 'Status: ' . htmlspecialchars($patientData['status']), 0, 1);
            $pdf->Cell(0, 10, 'Birthday: ' . date("m/d/Y", strtotime($patientData['birthday'])), 0, 1);
            $pdf->Cell(0, 10, 'Vital Signs: ' . htmlspecialchars($patientData['vital_signs']), 0, 1);
            $pdf->Cell(0, 10, 'Checkup Date: ' . date("m/d/Y", strtotime($patientData['checkup_date'])), 0, 1);

            // Output PDF document
            $pdf->Output('patient_details.pdf', 'D'); // 'D' to download
        } else {
            echo "No patient found with that ID.";
        }
    } else {
        error_log("Invalid ID format for: " . $patientId);
        echo "Invalid ID format.";
    }
} else {
    echo "No ID provided.";
}

// Close the prepared statement and database connection
if (isset($stmt)) {
    $stmt->close();
}
$conn->close();
?>
