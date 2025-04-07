<?php
include('header.php');

// Check if the user is logged in as a staff member
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'staff') {
    header("Location: login.php");
    exit();
}

// Fetching metrics from the database using $dbh

// Total students enrolled in the current term
$total_students_query = "SELECT COUNT(*) AS total_students FROM enrollments WHERE academic_year = '2025'"; // Replace with your current academic year logic
$total_students_result = $dbh->query($total_students_query);
$total_students = $total_students_result->fetch(PDO::FETCH_ASSOC)['total_students'];

// Total results submitted (assuming this counts all results entries)
$results_submitted_query = "SELECT COUNT(*) AS results_submitted FROM results";
$results_submitted_result = $dbh->query($results_submitted_query);
$results_submitted = $results_submitted_result->fetch(PDO::FETCH_ASSOC)['results_submitted'];

// Pending complaints
$pending_complaints_query = "SELECT COUNT(*) AS pending_complaints FROM complaints WHERE status = 'pending'";
$pending_complaints_result = $dbh->query($pending_complaints_query);
$pending_complaints = $pending_complaints_result->fetch(PDO::FETCH_ASSOC)['pending_complaints'];

// Total staff count
$total_staff_query = "SELECT COUNT(*) AS total_staff FROM staff";
$total_staff_result = $dbh->query($total_staff_query);
$total_staff = $total_staff_result->fetch(PDO::FETCH_ASSOC)['total_staff'];

// Total departments
$total_departments_query = "SELECT COUNT(*) AS total_departments FROM department";
$total_departments_result = $dbh->query($total_departments_query);
$total_departments = $total_departments_result->fetch(PDO::FETCH_ASSOC)['total_departments'];

// Total courses offered
$total_courses_query = "SELECT COUNT(*) AS total_courses FROM course";
$total_courses_result = $dbh->query($total_courses_query);
$total_courses = $total_courses_result->fetch(PDO::FETCH_ASSOC)['total_courses'];

?>

<main class="container">
    <div class="main-view">
        <!-- Dashboard Header -->
        <div class="dashboard-header mb-4">
            <h2 class="page-title">Staff Management Dashboard</h2>
        </div>

        <!-- Metrics Section -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="metric-card card shadow-sm">
                    <div class="metric-icon bg-primary mb-3"><i class='bx bxs-user'></i></div>
                    <div class="metric-info">
                        <h5 class="fs-4 text-center">Total Students</h5>
                        <h3 class="text-white"><?php echo $total_students; ?></h3>
                        <span class="text-muted">Enrolled this term</span>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="metric-card card shadow-sm">
                    <div class="metric-icon bg-info mb-3"><i class='bx bxs-edit'></i></div>
                    <div class="metric-info">
                        <h5 class="fs-4 text-center">Results Submitted</h5>
                        <h3 class="text-white"><?php echo $results_submitted; ?></h3>
                        <span class="text-muted">Across all departments</span>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="metric-card card shadow-sm">
                    <div class="metric-icon bg-warning mb-3"><i class='bx bxs-alarm-exclamation'></i></div>
                    <div class="metric-info">
                        <h5 class="fs-4 text-center">Pending Complaints</h5>
                        <h3 class="text-white"><?php echo $pending_complaints; ?></h3>
                        <span class="text-danger">Awaiting response</span>
                    </div>
                </div>
</div>
        </div>

        <!-- Additional Metrics Section -->
        <div class="row mb-4">
        <div class="col-md-4">
                <div class="metric-card card shadow-sm">
                    <div class="metric-icon bg-success mb-3"><i class='bx bxs-user-check'></i></div>
                    <div class="metric-info">
                        <h5 class="fs-4 text-center">Total Staff</h5>
                        <h3 class="text-white text-center"><?php echo $total_staff; ?></h3>
                        <span class="text-muted">Including teaching and non-teaching staff</span>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="metric-card card shadow-sm">
                    <div class="metric-icon bg-danger mb-3"><i class='bx bxs-school'></i></div>
                    <div class="metric-info">
                        <h5 class="fs-4 text-center">Total Departments</h5>
                        <h3 class="text-white"><?php echo $total_departments; ?></h3>
                        <span class="text-muted">Departments across the university</span>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="metric-card card shadow-sm">
                    <div class="metric-icon bg-light mb-3"><i class='bx bxs-book fs-3 text-dark'></i></div>
                    <div class="metric-info">
                        <h5 class="fs-4 text-center">Total Courses Offered</h5>
                        <h3 class="text-white"><?php echo $total_courses; ?></h3>
                        <span class="text-muted">Across all departments</span>
                    </div>
                </div>
            </div>
        </div>

    </div>
</main>

<?php include('footer.php'); ?>
