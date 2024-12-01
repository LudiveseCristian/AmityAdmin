<?php
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

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// Initialize variables
$totalPatients = 0;
$checkupDates = array();

// Query to get the total number of patients
$query = "SELECT COUNT(*) AS totalPatients FROM patients";
$result = mysqli_query($conn, $query);

// Query to get checkup dates along with patient names
$queryDates = "SELECT name, checkup_date FROM patients WHERE checkup_date IS NOT NULL ORDER BY checkup_date ASC";
$resultDates = mysqli_query($conn, $queryDates);

if ($resultDates) {
    while ($row = mysqli_fetch_assoc($resultDates)) {
        $checkupDates[] = array(
            'name' => $row['name'],
            'checkup_date' => $row['checkup_date']
        );
    }
} else {
    echo "Error: " . mysqli_error($conn);
}


if ($result) {
    $row = mysqli_fetch_assoc($result);
    $totalPatients = $row['totalPatients'];
} else {
    echo "Error: " . mysqli_error($conn);
}

// Query to get checkup dates
$queryDates = "SELECT checkup_date FROM patients WHERE checkup_date IS NOT NULL";
$resultDates = mysqli_query($conn, $queryDates);

if ($resultDates) {
    while ($row = mysqli_fetch_assoc($resultDates)) {
        $checkupDates[] = $row['checkup_date'];
    }
} else {
    echo "Error: " . mysqli_error($conn);
}

// Close database connection
mysqli_close($conn);
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Dashboard</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet"/>
    <style>
        /* Reset and General Styles */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', sans-serif;
            color: #333;
            background-color: #f8f9fa;
            height: 100vh;
            overflow: hidden;
        }
        a { color: inherit; text-decoration: none; }

        /* Sidebar Styles */
        #sidebar {
            width: 280px;
            background-color: #ffffff;
            padding-top: 20px;
            border-right: 1px solid #e6e6e6;
            position: fixed;
            height: 100%;
            display: flex;
            flex-direction: column;
        }
        #sidebar .sidebar-header {
            font-size: 1.5rem;
            font-weight: 600;
            text-align: center;
            margin-bottom: 30px;
        }
        #sidebar ul {
            list-style-type: none;
            padding: 0;
        }
        #sidebar ul li a {
            display: flex;
            align-items: center;
            padding: 15px 20px;
            color: #333;
            font-weight: 500;
            transition: all 0.3s;
        }
        #sidebar ul li a:hover, #sidebar ul li a.active {
            background-color: #e3f2fd;
            color: #007bff;
        }
        #sidebar ul li a i { margin-right: 10px; }

        /* Main Content Styles */
        .main-content {
            margin-left: 280px;
            padding: 20px;
            overflow-y: auto;
            height: 100vh;
            background-color: #f4f6f9;
        }
        .welcome {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 20px;
        }
        .welcome span {
            color: #007bff;
        }
        
        /* Cards Styles */
        .card {
            background-color: #ffffff;
            border: 1px solid #e6e6e6;
            border-radius: 8px;
            padding: 30px;
            margin-top: 20px;
            margin-bottom: 20px;
            box-shadow: 0px 4px 12px rgba(0, 0, 0, 0.05);
        }
        .card h2 {
            font-size: 1rem;
            font-weight: 600;
            color: #333;
            margin-bottom: 10px;
        }
        .stats {
            display: flex;
            justify-content: space-between;
            gap: 10px;
            flex-wrap: wrap;
        }
        .stat-card {
            background-color: #e3f2fd;
            padding: 15px;
            border-radius: 8px;
            text-align: center;
            flex: 1;
            min-width: 150px;
        }
        .stat-card h3 { font-size: 1.25rem; color: #007bff; }
        .stat-card p { font-size: 0.9rem; color: #333; }

        /* Chart Section */
        .chart-section {
            background-color: #ffffff;
            border: 1px solid #e6e6e6;
            border-radius: 8px;
            padding: 20px;
            height: 400px;
        }

        /* Date and Calendar Section */
        .calendar {
            padding: 20px;
            border: 1px solid #e6e6e6;
            border-radius: 8px;
            background-color: #ffffff;
            
        }
        /* Customize card background and add shadow for better visual appeal */
.card.calendar {
    background-color: #f9f9f9; /* Light background for the calendar card */
}

/* Add a hover effect for list items */
#checkup-list .list-group-item:hover {
    background-color: #e7f3ff; /* Light blue background on hover */
    cursor: pointer; /* Show pointer cursor on hover */
}


        /* Hide Footer */
        .footer {
            display: none;
        }
    </style>
</head>
<body>

<div>
    <!-- Sidebar -->
    <nav id="sidebar">
        <div class="sidebar-header">
        <img src="Logo.png" alt="Amity Logo" style="width: 55px; height: 50px; margin-right: 10px; vertical-align: middle;">
        Amity
    </div>
        <ul>
            <li><a href="Dashboard.php" class="active"><i class="fas fa-tachometer-alt"></i> Overview</a></li>
            <li><a href="#calendar-card"><i class="fas fa-user-injured"></i> Patient</a></li>
            
            <li><a href="MedicalRecords.php"><i class="fas fa-file-medical"></i> Medical Records</a></li>
            <li><a href="login.php"><i class="fas fa-sign-out-alt"></i> Log Out</a></li>
        </ul>
    </nav>

    <!-- Main Content -->
    <div class="main-content">
        <div class="welcome">
            Welcome back, <span>Doctor</span> 👋
        </div>
        <div class="stats">
            <div class="stat-card">
                <h3>10,525</h3>
                <p>Overall Visitors</p>
            </div>
            <div class="stat-card">
                <h3><?php echo $totalPatients; ?></h3>
                <p>Total Patients</p>
            </div>
            <div class="stat-card">
                <h3>523</h3>
                <p>Surgery</p>
            </div>
        </div>
        
        <div class="card chart-section">
            <h2>Patient Statistics</h2>
            <canvas id="myChart"></canvas>
        </div>

        <div class="card calendar shadow-sm" id="calendar-card">
    <div class="card-body">
        <h2 class="card-title text-primary mb-4">Patients</h2>
        <ul id="checkup-list" class="list-group">
            <!-- Checkup dates will be inserted here -->
        </ul>
    </div>
</div>
</div>


<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
<script>
    // Initialize Chart (example)
    var ctx = document.getElementById('myChart').getContext('2d');
    var myChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'],
            datasets: [{
                label: 'Patients',
                data: [200, 300, 400, 250, 350, 280, 300],
                backgroundColor: '#007bff'
            }]
        },
        options: { responsive: true, maintainAspectRatio: false }
    });
  
  // PHP array to JavaScript
var checkupDates = <?php echo json_encode($checkupDates); ?>;

// Function to initialize the calendar and mark checkup dates
document.addEventListener("DOMContentLoaded", function() {
    const checkupList = document.getElementById('checkup-list');

    // Sort checkup dates by the checkup date (ascending order)
    checkupDates.sort(function(a, b) {
        return new Date(a.checkup_date) - new Date(b.checkup_date); // Ascending order by date
    });

    // Limit to the first 10 checkup dates
    var limitedCheckups = checkupDates.slice(0, 10);

    // Function to format date to "Month Day, Year" (e.g., October 23, 2024)
    function formatDate(dateStr) {
        const options = { year: 'numeric', month: 'long', day: 'numeric' };
        const date = new Date(dateStr);
        return date.toLocaleDateString('en-US', options); // Change 'en-US' for localization
    }

    // Display the nearest 10 checkup dates
    limitedCheckups.forEach(function(item) {
        var li = document.createElement('li');
        li.classList.add('list-group-item', 'd-flex', 'justify-content-between', 'align-items-center', 'border', 'border-light', 'rounded', 'mb-2');
        li.innerHTML = `${item.name} <span class="text-muted">${formatDate(item.checkup_date)}</span>`;
        checkupList.appendChild(li);
    });
});

</script>
</body>
</html>
