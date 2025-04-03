<?php
ob_start();
include('header.php');

// Authentication - Staff Only
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'staff') {
    header("Location: login.php");
    exit();
}

// Initialize filter variables from GET parameters
$academic_year = $_GET['academic_year'] ?? '';
$semester = $_GET['semester'] ?? '';
$course_id = $_GET['course_id'] ?? '';
$search = $_GET['search'] ?? '';

// Base query with joins - will show ALL results by default
$query = "SELECT 
    r.id as result_id,
    r.course_work,
    r.exam,
    cu.code as unit_code,
    cu.name as unit_name,
    s.studentId,
    s.name as student_name,
    s.reg_no,
    s.academic_year,
    s.semester,
    s.year_of_study,
    c.id as course_id,
    c.course_name,
    c.course_code
FROM results r
LEFT JOIN course_unit cu ON r.code = cu.id
LEFT JOIN students s ON r.studentId = s.studentId
LEFT JOIN course c ON s.course = c.id
WHERE 1=1";  // This WHERE 1=1 allows easy addition of filters

// Add filters dynamically only if they have values
$params = [];
if (!empty($academic_year)) {
    $query .= " AND s.academic_year = :academic_year";
    $params[':academic_year'] = $academic_year;
}
if (!empty($semester)) {
    $query .= " AND s.semester = :semester";
    $params[':semester'] = $semester;
}
if (!empty($course_id)) {
    $query .= " AND s.course = :course_id";
    $params[':course_id'] = $course_id;
}
if (!empty($search)) {
    $query .= " AND (s.name LIKE :search OR s.reg_no LIKE :search OR cu.name LIKE :search OR c.course_name LIKE :search)";
    $params[':search'] = "%$search%";
}

$query .= " ORDER BY s.reg_no, cu.code";

// Debugging: Uncomment to see the generated query
// echo "<pre>Query: $query</pre>";
// echo "<pre>Params: "; print_r($params); echo "</pre>";

// Execute query
try {
    $stmt = $dbh->prepare($query);
    
    // Bind parameters only if they exist
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_OBJ);
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

// Get filter options for dropdowns
$years = $dbh->query("SELECT DISTINCT academic_year FROM students ORDER BY academic_year DESC")->fetchAll();
$courses = $dbh->query("SELECT id, course_name FROM course")->fetchAll();

function calculateGrade($total) {
    if ($total >= 70) return 'A';
    if ($total >= 60) return 'B';
    if ($total >= 50) return 'C';
    if ($total >= 40) return 'D';
    return 'F';
}

?>

<main class="container">
    <div class="main-view">
        <div>
            <div class="row mb-4">
                <div class="col-md-6">
                    <h2>Student Results Management</h2>
                </div>
            </div>
            
            <!-- Filter Form - Independent filters -->
            <form method="GET" class="mb-4">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Academic Year</label>
                        <select name="academic_year" class="form-select">
                            <option value="">All Years</option>
                            <?php foreach ($years as $year): ?>
                                <option value="<?= htmlspecialchars($year['academic_year']) ?>" 
                                    <?= $year['academic_year'] == $academic_year ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($year['academic_year']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Semester</label>
                        <select name="semester" class="form-select">
                            <option value="">All</option>
                            <option value="1" <?= $semester == '1' ? 'selected' : '' ?>>Semester 1</option>
                            <option value="2" <?= $semester == '2' ? 'selected' : '' ?>>Semester 2</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Course</label>
                        <select name="course_id" class="form-select">
                            <option value="">All Courses</option>
                            <?php foreach ($courses as $course): ?>
                                <option value="<?= htmlspecialchars($course['id']) ?>" 
                                    <?= $course['id'] == $course_id ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($course['course_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Search</label>
                        <input type="text" name="search" class="form-control" 
                               placeholder="Student, RegNo, or Unit..." 
                               value="<?= htmlspecialchars($search) ?>">
                    </div>
                    <div class="col-md-1 d-flex flex-column align-items-start justify-content-end">
                        <button type="submit" class="btn btn-sm btn-primary mb-2">Filter</button>
                        <a href="student-results.php" class="btn btn-sm btn-secondary">Reset</a>
                    </div>
                </div>
            </form>

            <!-- Results Table -->
            <div class="table-responsive">
                <table class="table table-dark table-hover table-striped">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Reg No</th>
                            <th>Student</th>
                            <th>Course</th>
                            <th>Unit Name</th>
                            <th>CW</th>
                            <th>Exam</th>
                            <th>Total</th>
                            <th>Year</th>
                            <th>Sem</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($results) > 0): ?>
                            <?php foreach ($results as $index => $result): 
                                $total = $result->course_work + $result->exam;
                                $grade = calculateGrade($total); // You need to implement this function
                            ?>
                            <tr>
                                <td><?= $index + 1 ?></td>
                                <td><?= htmlspecialchars($result->reg_no) ?></td>
                                <td><?= htmlspecialchars($result->student_name) ?></td>
                                <td><?= htmlspecialchars($result->course_code) ?></td>
                                <td><?= htmlspecialchars($result->unit_name) ?></td>
                                <td><?= $result->course_work ?></td>
                                <td><?= $result->exam ?></td>
                                <td><strong><?= $total ?></strong></td>
                                <td><?= htmlspecialchars($result->academic_year) ?></td>
                                <td><?= htmlspecialchars($result->semester) ?></td>
                                <td>
                                    <button class="btn btn-sm btn-primary edit-result"
                                            data-id="<?= $result->result_id ?>"
                                            data-cw="<?= $result->course_work ?>"
                                            data-exam="<?= $result->exam ?>"
                                            data-bs-toggle="modal"
                                            data-bs-target="#editModal">
                                            <i class='bx bx-trash'></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="12" class="text-center">No results found</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Edit Modal -->
            <div class="modal fade" id="editModal" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <form method="POST">
                            <div class="modal-header">
                                <h5 class="modal-title">Edit Result</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <input type="hidden" name="result_id" id="editResultId">
                                <div class="mb-3">
                                    <label class="form-label">Course Work</label>
                                    <input type="number" name="course_work" id="editCourseWork" class="form-control" min="0" max="100" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Exam Score</label>
                                    <input type="number" name="exam" id="editExam" class="form-control" min="0" max="100" required>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" name="edit_result" class="btn btn-primary">Save Changes</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<script>
// Handle edit modal population
document.querySelectorAll('.edit-result').forEach(btn => {
    btn.addEventListener('click', function() {
        document.getElementById('editResultId').value = this.dataset.id;
        document.getElementById('editCourseWork').value = this.dataset.cw;
        document.getElementById('editExam').value = this.dataset.exam;
    });
});
</script>

<?php
ob_end_flush();
include('footer.php');