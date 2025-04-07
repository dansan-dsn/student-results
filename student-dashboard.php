<?php
include('header.php');

// Check if the user is logged in and has the correct role
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'student') {
    header("Location: login.php");
    exit();
}

// Get student data from the database
$user_id = $_SESSION['user_id'];

// Fetch Student Data
$stmt = $dbh->prepare("SELECT * FROM students WHERE studentId = :studentId");
$stmt->execute([':studentId' => $user_id]);
$student = $stmt->fetch(PDO::FETCH_OBJ);

if (!$student) {
    die("Student record not found");
}

// Calculate passed courses (assuming pass mark is 50%)
$stmt = $dbh->prepare("SELECT COUNT(*) AS passed_courses FROM results 
                      WHERE studentId = :studentId 
                      AND (course_work + exam) >= 50");
$stmt->execute([':studentId' => $student->studentId]);
$passed_courses = $stmt->fetchColumn();

// Fetch Total Courses
$stmt = $dbh->prepare("SELECT COUNT(DISTINCT code) AS total_courses FROM results 
                     WHERE studentId = :studentId");
$stmt->execute([':studentId' => $student->studentId]);
$total_courses = $stmt->fetchColumn();

// Fetch Pending Results
$stmt = $dbh->prepare("SELECT COUNT(*) AS pending_results FROM results 
                       WHERE studentId = :studentId 
                       AND exam IS NULL");
$stmt->execute([':studentId' => $student->studentId]);
$pending_results = $stmt->fetchColumn();

// Fetch Active Complaints
$stmt = $dbh->prepare("SELECT COUNT(*) AS active_complaints FROM complaints 
                         WHERE reg_no = :reg_no 
                         AND status = 0");
$stmt->execute([':reg_no' => $student->studentId]);
$active_complaints = $stmt->fetchColumn();

// Fetch Recent Results with course unit names
$stmt = $dbh->prepare("SELECT r.*, cu.name AS course_name, cu.code AS course_code 
                      FROM results r
                      JOIN course_unit cu ON r.code = cu.id
                      WHERE r.studentId = :studentId 
                      ORDER BY r.created_at DESC LIMIT 5");
$stmt->execute([':studentId' => $student->studentId]);
$recent_results = $stmt->fetchAll(PDO::FETCH_OBJ);

$current_gpa = 3.5; 
?>

<main class="container">
    <div class="main-view">
        <!-- Dashboard Header -->
        <div class="dashboard-header mb-4">
            <h2 class="page-title text-center">Student Results Dashboard</h2>

            <!-- <div class="dashboard-actions">
                <button class="btn btn-primary">
                    <i class='bx bxs-download'></i> Export Results
                </button>
            </div> -->
        </div>

        <!-- Metrics Section -->
        <div class="row mb-4">
            <!-- Student Metrics -->
            <div class="col-md-3 col-sm-6">
                <div class="metric-card card shadow-sm">
                    <div class="metric-icon bg-primary text-white p-3 rounded-circle">
                        <i class='bx bxs-bar-chart-alt-2'></i>
                    </div>
                    <div class="metric-info text-center mt-3">
                        <h5>Current GPA</h5>
                        <h3 class="font-weight-bold text-light"><?php echo $current_gpa; ?></h3>
                        <span class="text-success">+0.2 from last term</span>
                    </div>
                </div>
            </div>

            <div class="col-md-3 col-sm-6">
                <div class="metric-card card shadow-sm">
                    <div class="metric-icon bg-success text-white p-3 rounded-circle">
                        <i class='bx bxs-check-circle'></i>
                    </div>
                    <div class="metric-info text-center mt-3">
                        <h5>Passed Courses</h5>
                        <h3 class="font-weight-bold text-light"><?php echo $passed_courses; ?></h3>
                        <span class="text-muted">of <span class="text-light fw-bold"><?php echo $total_courses; ?></span> total</span>
                    </div>
                </div>
            </div>

            <div class="col-md-3 col-sm-6">
                <div class="metric-card card shadow-sm">
                    <div class="metric-icon bg-warning text-white p-3 rounded-circle">
                        <i class='bx bxs-hourglass'></i>
                    </div>
                    <div class="metric-info text-center mt-3">
                        <h5>Pending Results</h5>
                        <h3 class="font-weight-bold text-light"><?php echo $pending_results; ?></h3>
                        <span class="text-muted">awaiting grading</span>
                    </div>
                </div>
            </div>

            <div class="col-md-3 col-sm-6">
                <div class="metric-card card shadow-sm">
                    <div class="metric-icon bg-danger text-white p-3 rounded-circle">
                        <i class='bx bxs-message-alt-error'></i>
                    </div>
                    <div class="metric-info text-center mt-3">
                        <h5>Active Complaints</h5>
                        <h3 class="font-weight-bold text-light"><?php echo $active_complaints; ?></h3>
                        <span class="text-danger">Under review</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Results -->
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4>Recent Results</h4>
                <a href="semester-results.php" class="btn btn-sm btn-outline-secondary">View All</a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-dark">
                        <thead>
                        <tr>
                            <th>Course Code</th>
                            <th>Course Name</th>
                            <th>Coursework</th>
                            <th>Exam</th>
                            <th>Total</th>
                            <th>Grade</th>
                            <th>Status</th>
                        </tr>
                        </thead>
                        <tbody>
                <?php foreach($recent_results as $result): 
                    $total = ($result->course_work ?? 0) + ($result->exam ?? 0);
                    $grade = calculateGrade($total);
                    $status = ($result->exam === null) ? 'Pending' : 'Completed';
                ?>
                <tr>
                    <td><?php echo htmlspecialchars($result->course_code); ?></td>
                    <td><?php echo htmlspecialchars($result->course_name); ?></td>
                    <td><?php echo $result->course_work ?? '-'; ?></td>
                    <td><?php echo $result->exam ?? '-'; ?></td>
                    <td><?php echo $total; ?></td>
                    <td><span class="badge bg-<?php echo getGradeClass($grade); ?>"><?php echo $grade; ?></span></td>
                    <td><span class="badge bg-<?php echo $status == 'Completed' ? 'success' : 'warning'; ?>"><?php echo $status; ?></span></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Quick Actions + Chart -->
        <div class="row">
            <div class="col-md-12">
                <div class="card mb-4">
                    <div class="card-header"><h4>Quick Actions</h4></div>
                    <div class="card-body">
                        <div class="row quick-actions">
                            <div class="col-6 mb-3">
                                <a href="semester-results" class="action-card">
                                    <i class='bx bxs-file-pdf'></i><span>Generate Transcript</span>
                                </a>
                            </div>
                            <div class="col-6 mb-3">
                                <a href="submit-complaint" class="action-card">
                                    <i class='bx bxs-message-alt-error'></i><span>Submit Complaint</span>
                                </a>
                            </div>
                            <div class="col-6 mb-3">
                                <a href="#" class="action-card">
                                    <i class='bx bxs-calendar'></i><span>Exam Timetable</span>
                                </a>
                            </div>
                            <div class="col-6 mb-3">
                                <a href="view-allocations" class="action-card">
                                    <i class='bx bxs-building'></i><span>Room Allocation</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</main>

<?php include('footer.php'); ?>

<?php
// Function to calculate grade based on marks
function calculateGrade($marks) {
    if ($marks >= 80) return 'A';
    if ($marks >= 70) return 'B';
    if ($marks >= 60) return 'C';
    if ($marks >= 50) return 'D';
    return 'F';
}

// Function to return the grade class for styling
function getGradeClass($grade) {
    switch ($grade) {
        case 'A': return 'success';
        case 'B': return 'primary';
        case 'C': return 'warning';
        case 'D': return 'info';
        default: return 'danger';
    }
}
?>