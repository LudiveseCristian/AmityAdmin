<?php
// Include TCPDF
require_once 'libs/TCPDF-main/tcpdf.php';

// Database connection
$servername = "localhost";
$username = "u843230181_Amity2";
$password = "Amitydb123";
$dbname = "u843230181_Amitydb2";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die(json_encode(array("success" => "0", "message" => "Database connection failed.")));
}

// Pagination and sorting variables
$limit = 10; // Records per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Sorting variables
$orderBy = isset($_GET['sort']) ? $_GET['sort'] : 'name';
$orderDirection = isset($_GET['dir']) ? $_GET['dir'] : 'ASC';

// Search filter
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Check if an ID is provided in the URL to fetch a specific patient
$patient_id = isset($_GET['id']) ? $_GET['id'] : null;

// Construct SQL query with pagination, sorting, and filtering
if ($patient_id) {
    // Fetch details for the specific patient
    $sql = "SELECT * FROM patients WHERE id = ? LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $patient_id);
} elseif ($search) {
    $sql = "SELECT * FROM patients WHERE name LIKE ? ORDER BY $orderBy $orderDirection LIMIT ? OFFSET ?";
    $searchTerm = "%" . $search . "%";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sii", $searchTerm, $limit, $offset);
} else {
    // No search, return all records
    $sql = "SELECT * FROM patients ORDER BY $orderBy $orderDirection LIMIT ? OFFSET ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $limit, $offset);
}

$stmt->execute();
$result = $stmt->get_result();

// Get total number of records for pagination
if ($search) {
    $totalSql = "SELECT COUNT(*) as total FROM patients WHERE name LIKE ?";
    $totalStmt = $conn->prepare($totalSql);
    $totalStmt->bind_param("s", $searchTerm);
} else {
    $totalSql = "SELECT COUNT(*) as total FROM patients";
    $totalStmt = $conn->prepare($totalSql);
}

$totalStmt->execute();
$totalResult = $totalStmt->get_result();
$totalRow = $totalResult->fetch_assoc();
$totalRecords = $totalRow['total'];
$totalPages = ceil($totalRecords / $limit);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Medical Records</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="CSS/MedicalRecords.css"/>
    <style>
        .hidden { 
            display: none; 
        }
        @media print {
            .btn, .pagination, footer, .form-inline {
                display: none !important;
            }
            .print-section {
                display: none !important;
            }
            .print-layout {
                display: block !important;
            }
        }
    </style>
</head>
<body class="bg-light">
    <div class="container-fluid mt-5">
        <div class="d-flex justify-content-end mb-4">
            <a href="ChangeP.html" class="btn btn-primary mr-2">Change Password</a>
            <a href="Login.html" class="btn btn-danger mr-2">Logout</a>
            <a href="Dashboard.php" class="btn btn-secondary">Back</a>
        </div>
        <div class="card p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h4">Medical Records</h1>
                <div>
                    <form class="form-inline" method="GET" action="">
                        <input type="text" name="search" id="searchInput" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search..." class="form-control d-inline-block w-25">
                        <button class="btn btn-primary ml-2" type="submit">Search</button>
                        <button class="btn btn-primary ml-2" type="button" data-toggle="modal" data-target="#addPatientModal">Add Patient</button>
                    </form>
                </div>
            </div>

            <!-- Print Section Start -->
            <div id="printSection" class="print-section">
                <table class="table table-striped table-bordered" style="width: 100%;">
                    <thead class="thead-dark">
                        <tr>
                            <th>Name</th>
                            <th>Address</th>
                            <th>Phone</th>
                            <th>Gender</th>
                            <th>Status</th>
                            <th>Birthday</th>
                            <th>Vital Signs</th>
                            <th>Checkup Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result->num_rows > 0) {
                            while($row = $result->fetch_assoc()) { ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['name']); ?></td>
                                    <td><?php echo htmlspecialchars($row['address']); ?></td>
                                    <td><?php echo htmlspecialchars($row['phone']); ?></td>
                                    <td><?php echo htmlspecialchars($row['gender']); ?></td>
                                    <td><?php echo htmlspecialchars($row['status']); ?></td>
                                    <td><?php echo date("m/d/Y", strtotime($row['birthday'])); ?></td>
                                    <td><?php echo htmlspecialchars($row['vital_signs']); ?></td>
                                    <td><?php echo date("m/d/Y", strtotime($row['checkup_date'])); ?></td>
                                    <td>
                                        <div class="d-flex justify-content-start">
                                            <button class="btn btn-info btn-sm mr-2" onclick="printRecords('<?php echo htmlspecialchars($row['name']); ?>', '<?php echo htmlspecialchars($row['address']); ?>', '<?php echo htmlspecialchars($row['phone']); ?>', '<?php echo htmlspecialchars($row['gender']); ?>', '<?php echo htmlspecialchars($row['status']); ?>', '<?php echo date("m/d/Y", strtotime($row['birthday'])); ?>', '<?php echo htmlspecialchars($row['vital_signs']); ?>', '<?php echo date("m/d/Y", strtotime($row['checkup_date'])); ?>')">Print</button>
                                            <button class="btn btn-success btn-sm mr-2" onclick="downloadPDF('<?php echo $row['id']; ?>')">Download</button>
                                            <button class="btn btn-danger btn-sm" onclick="deleteRecord('<?php echo $row['id']; ?>')">Delete</button>
                                        </div>
                                    </td>
                                </tr>
                            <?php }
                        } else { ?>
                            <tr>
                                <td colspan="9" class="text-center">No records found.</td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
            <!-- Print Section End -->

            <!-- Hidden Print Layout -->
            <div id="printLayout" class="print-layout hidden">
                <h2>Patient Details</h2>
                <p><strong>Name:</strong> <span id="printName"></span></p>
                <p><strong>Address:</strong> <span id="printAddress"></span></p>
                <p><strong>Phone:</strong> <span id="printPhone"></span></p>
                <p><strong>Gender:</strong> <span id="printGender"></span></p>
                <p><strong>Status:</strong> <span id="printStatus"></span></p>
                <p><strong>Birthday:</strong> <span id="printBirthday"></span></p>
                <p><strong>Vital Signs:</strong> <span id="printVitalSigns"></span></p>
                <p><strong>Checkup Date:</strong> <span id="printCheckupDate"></span></p>
            </div>

            <!-- Add Patient Modal -->
<div class="modal fade" id="addPatientModal" tabindex="-1" role="dialog" aria-labelledby="addPatientModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addPatientModalLabel">Add New Patient</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="addPatientForm" method="POST" action="add_patient.php">
                    <div class="form-group">
                        <label for="patientName">Name</label>
                        <input type="text" class="form-control" id="patientName" name="name" required>
                    </div>
                    <div class="form-group">
                        <label for="patientAddress">Address</label>
                        <input type="text" class="form-control" id="patientAddress" name="address" required>
                    </div>
                    <div class="form-group">
                        <label for="patientPhone">Phone</label>
                        <input type="text" class="form-control" id="patientPhone" name="phone" required>
                    </div>
                    <div class="form-group">
                        <label for="patientGender">Gender</label>
                        <select class="form-control" id="patientGender" name="gender" required>
                            <option value="">Select Gender</option>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="patientStatus">Status</label>
                        <select class="form-control" id="patientStatus" name="status" required>
                            <option value="">Select status</option>
                            <option value="Single">Single</option>
                            <option value="Married">Married</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="patientBirthday">Birthday</label>
                        <input type="date" class="form-control" id="patientBirthday" name="birthday" required>
                    </div>

                    <!-- Vital Signs Inputs -->
                    <h6>Vital Signs</h6>
                    <div class="form-group">
                        <label for="bloodPressure">Blood Pressure</label>
                        <input type="text" class="form-control" id="blood_pressure" name="blood_pressure" placeholder="e.g., 120/80 mmHg" required>
                    </div>
                    <div class="form-group">
                        <label for="pulseRate">Pulse Rate</label>
                        <input type="number" class="form-control" id="pulse_rate" name="pulse_rate" placeholder="e.g., 72 bpm" required>
                    </div>
                    <div class="form-group">
                        <label for="respRate">Resp. Rate</label>
                        <input type="number" class="form-control" id="respRate" name="resp_rate" placeholder="e.g., 16 breaths/min" required>
                    </div>
                    <div class="form-group">
                        <label for="weight">Weight</label>
                        <input type="number" class="form-control" id="weight" name="weight" placeholder="e.g., 70 kg" required>
                    </div>
                    <div class="form-group">
                        <label for="temperature">Temperature</label>
                        <input type="text" class="form-control" id="temperature" name="temperature" placeholder="e.g., 36.5 °C" required>
                    </div>

                    <!-- Additional Fields -->
                    <div class="form-group">
                        <label for="cc">CC</label>
                        <input type="text" class="form-control" id="cc" name="cc" required>
                    </div>
                    <div class="form-group">
                        <label for="pe">PE</label>
                        <input type="text" class="form-control" id="pe" name="pe" required>
                    </div>
                    <div class="form-group">
                        <label for="dx">DX</label>
                        <input type="text" class="form-control" id="dx" name="dx" required>
                    </div>
                    <div class="form-group">
                        <label for="meds">Meds</label>
                        <input type="text" class="form-control" id="meds" name="meds" required>
                    </div>
                    <div class="form-group">
                        <label for="labs">Labs</label>
                        <input type="text" class="form-control" id="labs" name="labs" required>
                    </div>

                    <div class="form-group">
                        <label for="patientCheckupDate">Checkup Date</label>
                        <input type="date" class="form-control" id="patientCheckupDate" name="checkup_date" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Add Patient</button>
                </form>
            </div>
        </div>
    </div>
</div>


            <!-- Pagination -->
            <nav aria-label="Page navigation">
                <ul class="pagination justify-content-center">
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <li class="page-item <?php echo ($i === $page) ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo htmlspecialchars($search); ?>&sort=<?php echo $orderBy; ?>&dir=<?php echo $orderDirection; ?>"><?php echo $i; ?></a>
                        </li>
                    <?php endfor; ?>
                </ul>
            </nav>
        </div>
    </div>

    <footer class="text-center mt-4">
        <p>&copy; 2024 Your Company. All Rights Reserved.</p>
    </footer>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script>
        // Handle form submission for adding a patient
        // Assuming you're using jQuery for AJAX
            $('#addPatientForm').on('submit', function(event) {
                event.preventDefault();
                $.ajax({
                    type: 'POST',
                    url: $(this).attr('action'),
                    data: $(this).serialize(),
                    dataType: 'json',
                    success: function(response) {
                        if (response.success === "0") {
                            console.error("Error adding patient: " + response.message);
                        } else {
                            alert(response.message);
                            // Optionally, refresh the patient list or reset the form
                        }
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        console.error("AJAX error: " + textStatus + ': ' + errorThrown);
                    }
                });
            });


        // Function to print records
        function printRecords(name, address, phone, gender, status, birthday, vitalSigns, checkupDate) {
            document.getElementById('printName').innerText = name;
            document.getElementById('printAddress').innerText = address;
            document.getElementById('printPhone').innerText = phone;
            document.getElementById('printGender').innerText = gender;
            document.getElementById('printStatus').innerText = status;
            document.getElementById('printBirthday').innerText = birthday;
            document.getElementById('printVitalSigns').innerText = vitalSigns;
            document.getElementById('printCheckupDate').innerText = checkupDate;

            window.print();
        }

        // Function to download PDF (to be implemented)
        function downloadPDF(id) {
            // Your PDF download logic here
            window.location.href = 'generate_pdf.php?id=' + id;
        }

        // Function to delete record (to be implemented)
        function deleteRecord(patientId) {
    if (confirm("Are you sure you want to delete this record?")) {
        // Perform the delete operation via AJAX
        fetch(`DeletePatient.php?id=${patientId}`, {
            method: 'DELETE'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success === "1") {
                // Successfully deleted
                alert("Record deleted successfully.");
                // Optionally, refresh the page or remove the deleted row from the table
                location.reload(); // Or you can remove the row from the table dynamically
            } else {
                alert("Failed to delete the record: " + data.message);
            }
        })
        .catch(error => {
            console.error("Error:", error);
        });
    }
}

    </script>
</body>
</html>