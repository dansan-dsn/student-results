<?php
include('header.php');

// Sample role check: student or staff
$role = $_SESSION['user_role'];
?>

<main class="container">
    <div class="main-view">
        <!-- Dashboard Header -->
        <div class="dashboard-header mb-4">
            <h2 class="page-title">
                <?= ($role === 'staff') ? 'Staff Management Dashboard' : 'Student Results Dashboard' ?>
            </h2>

            <?php if ($role === 'student'): ?>
                <div class="dashboard-actions">
                    <button class="btn btn-primary">
                        <i class='bx bxs-download'></i> Export Results
                    </button>
                </div>
            <?php endif; ?>
        </div>

        <!-- Metrics Section -->
        <div class="row mb-4">
            <?php if ($role === 'student'): ?>
                <!-- Student Metrics -->
                <div class="col-md-3">
                    <div class="metric-card">
                        <div class="metric-icon bg-primary"><i class='bx bxs-bar-chart-alt-2'></i></div>
                        <div class="metric-info">
                            <h5>Current GPA</h5><h3>3.75</h3><span class="text-success">+0.2 from last term</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="metric-card">
                        <div class="metric-icon bg-success"><i class='bx bxs-check-circle'></i></div>
                        <div class="metric-info">
                            <h5>Passed Courses</h5><h3>12</h3><span>of 15 total</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="metric-card">
                        <div class="metric-icon bg-warning"><i class='bx bxs-hourglass'></i></div>
                        <div class="metric-info">
                            <h5>Pending Results</h5><h3>3</h3><span>2 courses grading</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="metric-card">
                        <div class="metric-icon bg-danger"><i class='bx bxs-message-alt-error'></i></div>
                        <div class="metric-info">
                            <h5>Active Complaints</h5><h3>1</h3><span class="text-danger">Under review</span>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <!-- Staff Metrics -->
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
            <?php endif; ?>
        </div>

        <!-- Conditional Content -->
        <?php if ($role === 'student'): ?>
            <!-- Recent Results -->
            <div class="card mb-4">
                <div class="card-header">
                    <h4>Recent Results</h4>
                    <a href="#" class="btn btn-sm btn-outline-secondary">View All</a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                            <tr>
                                <th>Course Code</th><th>Course Name</th><th>Coursework</th><th>Exam</th>
                                <th>Total</th><th>Grade</th><th>Status</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr>
                                <td>CS401</td><td>Advanced Database Systems</td><td>78</td><td>82</td>
                                <td>80</td><td><span class="badge bg-success">A</span></td>
                                <td><span class="badge bg-success">Completed</span></td>
                            </tr>
                            <tr>
                                <td>CS402</td><td>Software Engineering</td><td>65</td><td>70</td>
                                <td>68</td><td><span class="badge bg-primary">B+</span></td>
                                <td><span class="badge bg-success">Completed</span></td>
                            </tr>
                            <tr>
                                <td>CS403</td><td>Artificial Intelligence</td><td>-</td><td>-</td>
                                <td>-</td><td><span class="badge bg-secondary">-</span></td>
                                <td><span class="badge bg-warning">Pending</span></td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Quick Actions + Chart -->
            <div class="row">
                <div class="col-md-6">
                    <div class="card mb-4">
                        <div class="card-header"><h4>Quick Actions</h4></div>
                        <div class="card-body">
                            <div class="row quick-actions">
                                <div class="col-6 mb-3">
                                    <a href="#" class="action-card"><i class='bx bxs-file-pdf'></i><span>Generate Transcript</span></a>
                                </div>
                                <div class="col-6 mb-3">
                                    <a href="#" class="action-card"><i class='bx bxs-message-alt-error'></i><span>Submit Complaint</span></a>
                                </div>
                                <div class="col-6 mb-3">
                                    <a href="#" class="action-card"><i class='bx bxs-calendar'></i><span>Exam Timetable</span></a>
                                </div>
                                <div class="col-6 mb-3">
                                    <a href="#" class="action-card"><i class='bx bxs-building'></i><span>Room Allocation</span></a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Performance Chart -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header"><h4>Performance Trend</h4></div>
                        <div class="card-body">
                            <div class="chart-container" style="height: 250px;">
                                <canvas id="performanceChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php else: ?>
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
        <?php endif; ?>
    </div>
</main>

<?php include('footer.php'); ?>
