<?php
include('config/db.php');

session_start();

if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role'])) {
    echo 'Redirecting to login .....';
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['user_role'];
$user_name = 'User';

try {
    if($user_role == 'staff') {
        $query = "SELECT name FROM staff WHERE staffId = :user_id";
    } elseif ($user_role == 'student') {
        $query = "SELECT name FROM students WHERE studentId = :user_id";
    } else {
        $query = null;
    }
    
    $stmt = $dbh->prepare($query);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();

    if($stmt->rowCount() > 0) {
        $user = $stmt->fetch(PDO::FETCH_OBJ);
        $full_name = htmlspecialchars($user->name);

        $name_split = explode(' ', $full_name);
        $user_name = reset($name_split);
    }
}catch(PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    $user_name = 'User';
}


?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Result System</title>
    <!-- icon links -->
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <!-- bootstrap links -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <!-- vue link -->
    <script src="https://cdn.jsdelivr.net/npm/vue@3.2.47/dist/vue.global.js"></script>
    <!-- custom css links -->
    <link rel="stylesheet" href="assets/styles/styles.css">
    <link rel="stylesheet" href="assets/styles/dashboard.css" >
    <link rel="stylesheet" href="assets/styles/profile.css">
    <link rel="stylesheet" href="assets/styles/departments.css" >
</head>
<body>
<div id="app">
    <div>
    <<!-- Navbar Section -->
    <header>
        <nav class="custom-navbar">
            <div class="container-fluid navbar-container">

            <!-- In your navbar-right div -->
                <div class="navbar-right">

                    <!-- Notifications Dropdown -->
                    <div class="dropdown">
                        <button class="btn-notification dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bx bxs-bell"></i>
                            <span class="notification-badge">3</span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><h6 class="dropdown-header">New Notifications</h6></li>
                            <li><a class="dropdown-item" href="#"><strong>Grade Updated</strong><br>Math 101 score changed</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-center" href="#">View All</a></li>
                        </ul>
                    </div>

                    <!-- User Dropdown -->
                    <div class="dropdown">
                        <button class="user-btn dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bx bxs-user-circle"></i> <span><?php echo $user_name;?></span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="profile.php"><i class="bx bx-user me-2"></i> Profile</a></li>
                            <li><a class="dropdown-item" href="#"><i class="bx bx-cog me-2"></i> Settings</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="logout.php"><i class="bx bx-log-out me-2"></i> Logout</a></li>
                        </ul>
                    </div>
                </div>
            </div>

        </nav>
    </header>

    <aside>
    <h5 class="logo-title">Results System</h5>
    
    <!-- Dashboard Section -->
    <div class="menu-section">
        <h6 class="menu-category">DASHBOARD</h6>
        <ul>
            <li class="<?php echo (basename($_SERVER['PHP_SELF']) == 'index.php' && empty($_GET['page'])) ? 'active' : ''; ?>">
                <a href="index.php">
                    <i class='bx bxs-dashboard'></i> Dashboard
                </a>
            </li>
        </ul>
    </div>

    <!-- Results Section -->
    <div class="menu-section">
        <h6 class="menu-category">RESULTS</h6>
        <ul>
            <li class="<?php echo (in_array(basename($_SERVER['PHP_SELF']), ['view-results.php', 'semester-results.php'])) ? 'active' : ''; ?>">
                <a href="javascript:void(0);" onclick="toggleSubmenu('results')">
                    <i class='bx bxs-report'></i> Results
                    <i class='bx bx-chevron-down' id="results-chevron"></i>
                </a>
                <ul id="results-submenu" class="submenu" style="display: <?php echo (in_array(basename($_SERVER['PHP_SELF']), ['view-results.php', 'semester-results.php'])) ? 'block' : 'none'; ?>;">
                    <li class="<?php echo (basename($_SERVER['PHP_SELF']) == 'view-results.php') ? 'active' : ''; ?>">
                        <a href="view-results.php">View Results</a>
                    </li>
                    <li class="<?php echo (basename($_SERVER['PHP_SELF']) == 'semester-results.php') ? 'active' : ''; ?>">
                        <a href="semester-results.php">Semester Summary</a>
                    </li>
                </ul>
            </li>
        </ul>
    </div>

    <!-- Reports Section -->
    <div class="menu-section">
        <h6 class="menu-category">REPORTS</h6>
        <ul>
            <li class="<?php echo (in_array(basename($_SERVER['PHP_SELF']), ['generate-reports.php', 'export-results.php'])) ? 'active' : ''; ?>">
                <a href="javascript:void(0);" onclick="toggleSubmenu('reports')">
                    <i class='bx bxs-file-pdf'></i> Reports
                    <i class='bx bx-chevron-down' id="reports-chevron"></i>
                </a>
                <ul id="reports-submenu" class="submenu" style="display: <?php echo (in_array(basename($_SERVER['PHP_SELF']), ['generate-reports.php', 'export-results.php'])) ? 'block' : 'none'; ?>;">
                    <li class="<?php echo (basename($_SERVER['PHP_SELF']) == 'generate-reports.php') ? 'active' : ''; ?>">
                        <a href="generate-reports.php">Generate Reports</a>
                    </li>
                    <li class="<?php echo (basename($_SERVER['PHP_SELF']) == 'export-results.php') ? 'active' : ''; ?>">
                        <a href="export-results.php">Export to Excel</a>
                    </li>
                </ul>
            </li>
        </ul>
    </div>

    <!-- Complaints Section -->
    <div class="menu-section">
        <h6 class="menu-category">COMPLAINTS</h6>
        <ul>
            <li class="<?php echo (in_array(basename($_SERVER['PHP_SELF']), ['submit-complaint.php', 'complaint-status.php'])) ? 'active' : ''; ?>">
                <a href="javascript:void(0);" onclick="toggleSubmenu('complaints')">
                    <i class='bx bxs-message-alt-error'></i> Complaints
                    <i class='bx bx-chevron-down' id="complaints-chevron"></i>
                </a>
                <ul id="complaints-submenu" class="submenu" style="display: <?php echo (in_array(basename($_SERVER['PHP_SELF']), ['submit-complaint.php', 'complaint-status.php'])) ? 'block' : 'none'; ?>;">
                    <li class="<?php echo (basename($_SERVER['PHP_SELF']) == 'submit-complaint.php') ? 'active' : ''; ?>">
                        <a href="submit-complaint.php">Submit Complaint</a>
                    </li>
                    <li class="<?php echo (basename($_SERVER['PHP_SELF']) == 'complaint-status.php') ? 'active' : ''; ?>">
                        <a href="complaint-status.php">Check Status</a>
                    </li>
                </ul>
            </li>
        </ul>
    </div>

    <!-- Room Management Section -->
    <div class="menu-section">
        <h6 class="menu-category">ROOM MANAGEMENT</h6>
        <ul>
            <li class="<?php echo (in_array(basename($_SERVER['PHP_SELF']), ['assign-seats.php', 'view-allocations.php', 'departments.php'])) ? 'active' : ''; ?>">
                <a href="javascript:void(0);" onclick="toggleSubmenu('room-management')">
                    <i class='bx bxs-building-house'></i> Management
                    <i class='bx bx-chevron-down' id="room-management-chevron"></i>
                </a>
                <ul id="room-management-submenu" class="submenu" style="display: <?php echo (in_array(basename($_SERVER['PHP_SELF']), ['assign-seats.php', 'view-allocations.php', 'departments.php'])) ? 'block' : 'none'; ?>;">
                    <li class="<?php echo (basename($_SERVER['PHP_SELF']) == 'assign-seats.php') ? 'active' : ''; ?>">
                        <a href="assign-seats.php">Assign Seats</a>
                    </li>
                    <li class="<?php echo (basename($_SERVER['PHP_SELF']) == 'view-allocations.php') ? 'active' : ''; ?>">
                        <a href="view-allocations.php">View Allocations</a>
                    </li>
                    <li class="<?php echo (basename($_SERVER['PHP_SELF']) == 'departments.php') ? 'active' : ''; ?>">
                        <a href="departments.php">Departments</a>
                    </li>
                </ul>
            </li>
        </ul>
    </div>

    <!-- Account Section -->
    <div class="menu-section">
        <h6 class="menu-category">ACCOUNT</h6>
        <ul>
            <li class="<?php echo (basename($_SERVER['PHP_SELF']) == 'profile.php') ? 'active' : ''; ?>">
                <a href="profile.php">
                    <i class="bx bx-user"></i> Profile
                </a>
            </li>
        </ul>
    </div>
</aside>

    </div>