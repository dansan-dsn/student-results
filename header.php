<?php
include('config/db.php');
include('notification_functions.php');

session_start();

$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['user_role'];
$user_name = 'User';

 // Mark all notifications as read when landing on a page from notification
if (isset($_GET['mark_all_read'])) {
    markAllRelatedNotificationsAsRead($dbh, $user_id, basename($_SERVER['PHP_SELF']));
    // Remove the parameter from URL
    header("Location: ".str_replace('?mark_all_read=1', '', $_SERVER['REQUEST_URI']));
    exit();
}

if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role'])) {
    echo 'Redirecting to login .....';
    header('Location: login.php');
    exit;
}


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

    <!-- custom css links -->
    <link rel="stylesheet" href="assets/styles/styles.css">
    <link rel="stylesheet" href="assets/styles/dashboard.css" >
    <link rel="stylesheet" href="assets/styles/profile.css">
    <link rel="stylesheet" href="assets/styles/departments.css" >
    <link rel="stylesheet" href="assets/styles/assign.css">
    <link rel="stylesheet" href="assets/styles/complaints.css">
    <link rel="stylesheet" href="assets/styles/allocations.css">
</head>
<body>
<div id="app">
    <div>
    <<!-- Navbar Section -->
    <header>
        <nav class="custom-navbar">

            <div class="container-fluid navbar-container">
                
                <div class="navbar-right">
                    <!-- notifications Dropdown -->
                    <div class="dropdown">
                        <button class="btn-notification dropdown-toggle" type="button" id="notificationDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bx bxs-bell"></i>
                            <?php
                            $unread_count = $dbh->query("SELECT COUNT(*) as count FROM notifications WHERE user_id = $user_id AND is_read = FALSE")->fetch(PDO::FETCH_OBJ)->count;
                            if ($unread_count > 0): ?>
                                <span class="notification-badge"><?= $unread_count ?></span>
                            <?php endif; ?>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="notificationDropdown">
                            <li><h6 class="dropdown-header">Recent Notifications</h6></li>
                            <?php
                            $notifications = $dbh->query("SELECT * FROM notifications WHERE user_id = $user_id ORDER BY created_at DESC LIMIT 3")->fetchAll(PDO::FETCH_OBJ);
                            
                            if (empty($notifications)): ?>
                                <li><a class="dropdown-item" href="#">No notifications</a></li>
                            <?php else: ?>
                                <?php foreach ($notifications as $notification): 
                                    $short_message = strlen($notification->message) > 50 
                                        ? substr($notification->message, 0, 50) . '...' 
                                        : $notification->message;
                                ?>
                                    <li>
                                        <a class="dropdown-item <?= $notification->is_read ? '' : 'fw-bold' ?>" 
                                        href="<?= $notification->related_url ?>?mark_all_read=1"
                                        onclick="markSingleNotificationAsRead(<?= $notification->id ?>, this)">
                                            <?= htmlspecialchars($short_message) ?>
                                            <small class="text-muted d-block"><?= date('M j, g:i a', strtotime($notification->created_at)) ?></small>
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                            <?php endif; ?>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item text-center" href="#" data-bs-toggle="offcanvas" data-bs-target="#notificationsOffcanvas">
                                    See All Notifications
                                </a>
                            </li>
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

    <!-- Notifications Offcanvas -->
    <div class="offcanvas offcanvas-end" tabindex="-1" id="notificationsOffcanvas" aria-labelledby="notificationsOffcanvasLabel">
        <div class="offcanvas-header d-flex justify-content-between">
            <h5 class="offcanvas-title text-Light fw-bold" id="notificationsOffcanvasLabel">Your Notifications</h5>
            <div class="">
                <form method="POST" action="mark_notifications_read.php" class="d-inline">
                    <button type="submit" class="btn btn-sm btn-primary">Mark All as Read</button>
                </form>
                <!-- <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button> -->
            </div>
        </div>
        <div class="offcanvas-body p-2">
            <div class="list-group list-group-flush">
                <?php
                $stmt = $dbh->prepare("SELECT * FROM notifications WHERE user_id = :user_id ORDER BY created_at DESC");
                $stmt->execute([':user_id' => $user_id]);
                $all_notifications = $stmt->fetchAll(PDO::FETCH_OBJ);
                
                if (empty($all_notifications)): ?>
                    <div class="alert alert-info m-3">You don't have any notifications yet</div>
                <?php else: ?>
                    <?php foreach ($all_notifications as $notification): ?>
                        <a href="<?= $notification->related_url ?>" 
                        class="list-group-item list-group-item-action rounded border-0 mb-2 <?= $notification->is_read ? 'bg-read' : 'bg-notification' ?>"
                        onclick="markSingleNotificationAsRead(<?= $notification->id ?>, this)">
                            <div class="d-flex w-100 justify-content-between">
                                <p class="mb-1"><?= htmlspecialchars($notification->message) ?></p>
                            </div>
                            <small class="text-secondary fw-bold"><?= date('M j, g:i a', strtotime($notification->created_at)) ?></small>
                        </a>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <aside>
        <h5 class="logo-title">Results System</h5>
    
        <!-- Dashboard Section -->
        <div class="menu-section">
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

        <!-- Course Section -->
        <div class="menu-section">
            <ul>
                <li class="<?php echo (in_array(basename($_SERVER['PHP_SELF']), ['course.php', 'course_units.php'])) ? 'active' : ''; ?>">
                    <a href="javascript:void(0);" onclick="toggleSubmenu('course')">
                        <i class='bx bx-unite'></i> Course
                        <i class='bx bx-chevron-down' id="course-chevron"></i>
                    </a>
                    <ul id="course-submenu" class="submenu" style="display: <?php echo (in_array(basename($_SERVER['PHP_SELF']), ['course.php', 'course_units.php'])) ? 'block' : 'none'; ?>;">
                        <li class="<?php echo (basename($_SERVER['PHP_SELF']) == 'course.php') ? 'active' : ''; ?>">
                            <a href="course.php">Main Courses</a>
                        </li>
                        <li class="<?php echo (basename($_SERVER['PHP_SELF']) == 'course_units.php') ? 'active' : ''; ?>">
                            <a href="course_units.php">Course Units</a>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>

        <!-- Reports Section -->
        <div class="menu-section">
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
                        <!-- <li class="<?php echo (basename($_SERVER['PHP_SELF']) == 'export-results.php') ? 'active' : ''; ?>">
                            <a href="export-results.php">Export to Excel</a>
                        </li> -->
                    </ul>
                </li>
            </ul>
        </div>

        <!-- Complaints Section -->
        <div class="menu-section">
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

        <!-- Management Section -->
        <div class="menu-section">
            <ul>
                <li class="<?php echo (in_array(basename($_SERVER['PHP_SELF']), ['assign-seats.php', 'view-allocations.php', 'departments.php'])) ? 'active' : ''; ?>">
                    <a href="javascript:void(0);" onclick="toggleSubmenu('room-management')">
                        <i class='bx bxs-building-house'></i> Management
                        <i class='bx bx-chevron-down' id="room-management-chevron"></i>
                    </a>
                    <ul id="room-management-submenu" class="submenu" style="display: <?php echo (in_array(basename($_SERVER['PHP_SELF']), ['assign-seats.php', 'view-allocations.php', 'departments.php'])) ? 'block' : 'none'; ?>;">
                        <li class="<?php echo (basename($_SERVER['PHP_SELF']) == 'assign-seats.php') ? 'active' : ''; ?>">
                            <a href="assign-seats.php">Assign Rooms</a>
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