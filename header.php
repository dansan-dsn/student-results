<?php
include('config/db.php');
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
</head>
<body>
<div id="app">
    <div>
    <!-- Navbar Section -->
    <header>
        <nav class="navbar navbar-dark">
            <div class="container-fluid">
                <form class="d-flex ms-auto">
                    <input class="search-input" placeholder="Search">
                </form>
                <button class="btn" type="button">
                    <i class='bx bxs-bell' style='color:#818898; font-size: 2rem'></i>
                </button>
                <button class="btn" type="button">
                    <i class='bx bxs-user-circle' style='color:#818898; font-size: 2rem'></i>
                </button>
            </div>
        </nav>
    </header>

    <!-- Sidebar (Aside) Section -->
    <aside>
    <h5 class="logo-title">Results</h5>
    <ul>
        <!-- Dashboard -->
        <li class="<?php echo ($_GET['page'] == 'dashboard' || !isset($_GET['page'])) ? 'active' : ''; ?>">
            <a href="index.php">
                <i class='bx bxs-dashboard'></i> Dashboard
            </a>
        </li>

        <!-- Results (with submenu) -->
        <li class="<?php echo (in_array($_GET['page'], ['view-results', 'semester-results'])) ? 'active' : ''; ?>">
            <a href="javascript:void(0);" onclick="toggleSubmenu('results')">
                <i class='bx bxs-report'></i> Results
                <i class='bx bx-chevron-down' style='float: right;' id="results-chevron"></i>
            </a>
            <ul id="results-submenu" class="submenu" style="display: <?php echo ($_GET['page'] == 'view-results' || $_GET['page'] == 'semester-results') ? 'block' : 'none'; ?>;">
                <li class="<?php echo ($_GET['page'] == 'view-results') ? 'active' : ''; ?>">
                    <a href="?page=view-results">View Results</a>
                </li>
                <li class="<?php echo ($_GET['page'] == 'semester-results') ? 'active' : ''; ?>">
                    <a href="?page=semester-results">Semester Summary</a>
                </li>
            </ul>
        </li>

        <!-- Reports (with submenu) -->
        <li class="<?php echo (in_array($_GET['page'], ['generate-reports', 'export-results'])) ? 'active' : ''; ?>">
            <a href="javascript:void(0);" onclick="toggleSubmenu('reports')">
                <i class='bx bxs-file-pdf'></i> Reports
                <i class='bx bx-chevron-down' style='float: right;' id="reports-chevron"></i>
            </a>
            <ul id="reports-submenu" class="submenu" style="display: <?php echo ($_GET['page'] == 'generate-reports' || $_GET['page'] == 'export-results') ? 'block' : 'none'; ?>;">
                <li class="<?php echo ($_GET['page'] == 'generate-reports') ? 'active' : ''; ?>">
                    <a href="?page=generate-reports">Generate Reports</a>
                </li>
                <li class="<?php echo ($_GET['page'] == 'export-results') ? 'active' : ''; ?>">
                    <a href="?page=export-results">Export to Excel</a>
                </li>
            </ul>
        </li>

        <!-- Complaints (with submenu) -->
        <li class="<?php echo (in_array($_GET['page'], ['submit-complaint', 'complaint-status'])) ? 'active' : ''; ?>">
            <a href="javascript:void(0);" onclick="toggleSubmenu('complaints')">
                <i class='bx bxs-message-alt-error'></i> Complaints
                <i class='bx bx-chevron-down' style='float: right;' id="complaints-chevron"></i>
            </a>
            <ul id="complaints-submenu" class="submenu" style="display: <?php echo ($_GET['page'] == 'submit-complaint' || $_GET['page'] == 'complaint-status') ? 'block' : 'none'; ?>;">
                <li class="<?php echo ($_GET['page'] == 'submit-complaint') ? 'active' : ''; ?>">
                    <a href="?page=submit-complaint">Submit Complaint</a>
                </li>
                <li class="<?php echo ($_GET['page'] == 'complaint-status') ? 'active' : ''; ?>">
                    <a href="?page=complaint-status">Check Status</a>
                </li>
            </ul>
        </li>

        <!-- Room Management (with submenu) -->
        <li class="<?php echo (in_array($_GET['page'], ['assign-seats', 'view-allocations'])) ? 'active' : ''; ?>">
            <a href="javascript:void(0);" onclick="toggleSubmenu('room-management')">
                <i class='bx bxs-building-house'></i> Room Management
                <i class='bx bx-chevron-down' style='float: right;' id="room-management-chevron"></i>
            </a>
            <ul id="room-management-submenu" class="submenu" style="display: <?php echo ($_GET['page'] == 'assign-seats' || $_GET['page'] == 'view-allocations') ? 'block' : 'none'; ?>;">
                <li class="<?php echo ($_GET['page'] == 'assign-seats') ? 'active' : ''; ?>">
                    <a href="?page=assign-seats">Assign Seats</a>
                </li>
                <li class="<?php echo ($_GET['page'] == 'view-allocations') ? 'active' : ''; ?>">
                    <a href="?page=view-allocations">View Allocations</a>
                </li>
            </ul>
        </li>

        <!-- Logout -->
        <li>
            <a href="logout.php">
                <i class='bx bx-log-out'></i> Logout
            </a>
        </li>
    </ul>
</aside>

    <!-- Main Content Section -->
    <div class="logo-title2">
        <h2 class="page-heading" style="font-weight: bolder">{{ pageTitle }}</h2>
    </div>
    </div>