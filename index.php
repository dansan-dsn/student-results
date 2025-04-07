<?php
include('header.php');

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'staff') {
    header("Location: login.php");
    exit();
}
?>

<main class="container">
    <div class="main-view">
        <!-- Dashboard Header -->
        <div class="dashboard-header mb-4">
            <h2 class="page-title">
                Staff Management Dashboard
            </h2>
        </div>

        <!-- Metrics Section -->
        <div class="row mb-4">
                <div class="col-md-4">
                    <div class="metric-card">
                        <div class="metric-icon bg-primary"><i class='bx bxs-user'></i></div>
                        <div class="metric-info">
                            <h5>Total Students</h5><h3>245</h3><span class="text-muted">Enrolled this term</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="metric-card">
                        <div class="metric-icon bg-info"><i class='bx bxs-edit'></i></div>
                        <div class="metric-info">
                            <h5>Results Submitted</h5><h3>1,024</h3><span>Across all departments</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="metric-card">
                        <div class="metric-icon bg-warning"><i class='bx bxs-alarm-exclamation'></i></div>
                        <div class="metric-info">
                            <h5>Pending Complaints</h5><h3>7</h3><span class="text-danger">Awaiting response</span>
                        </div>
                    </div>
                </div>
        </div>

            <!-- Staff Specific Content -->
            <div class="card mb-4">
                <div class="card-header"><h4>Recent Student Activities</h4></div>
                <div class="card-body">
                    <ul>
                        <li>John Doe submitted a complaint (Apr 2)</li>
                        <li>New results uploaded for CS202</li>
                        <li>Course registration for Semester 2 ends Apr 15</li>
                    </ul>
                </div>
            </div>
    </div>
</main>

<?php include('footer.php'); ?>
