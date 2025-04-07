<?php
ob_start();
include('header.php');

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'staff') {
    header("Location: login.php");
    exit();
}

// Filters
$academic_year = $_GET['academic_year'] ?? '';
$semester = $_GET['semester'] ?? '';
$course_id = $_GET['course_id'] ?? '';
$search = $_GET['search'] ?? '';

// Fetch results with joins
$query = "
    SELECT 
        r.academic_year,
        r.semester,
        s.studentId,
        s.name as student_name,
        s.reg_no,
        c.course_name,
        c.course_code,
        cu.name as unit_name,
        cu.code as unit_code,
        r.course_work,
        r.exam
    FROM results r
    JOIN students s ON r.studentId = s.studentId
    JOIN course c ON s.course = c.id
    JOIN course_unit cu ON r.code = cu.id
    WHERE 1=1
";

$params = [];
if (!empty($academic_year)) {
    $query .= " AND r.academic_year = :academic_year";
    $params[':academic_year'] = $academic_year;
}
if (!empty($semester)) {
    $query .= " AND r.semester = :semester";
    $params[':semester'] = $semester;
}
if (!empty($course_id)) {
    $query .= " AND s.course = :course_id";
    $params[':course_id'] = $course_id;
}
if (!empty($search)) {
    $query .= " AND (s.name LIKE :search OR s.reg_no LIKE :search)";
    $params[':search'] = "%$search%";
}

$query .= " ORDER BY r.academic_year DESC, r.semester ASC, s.reg_no, cu.code";

$stmt = $dbh->prepare($query);
foreach ($params as $key => $val) {
    $stmt->bindValue($key, $val);
}
$stmt->execute();
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Group by Academic Year > Semester > Student
$grouped = [];
foreach ($results as $row) {
    $year = $row['academic_year'];
    $sem = $row['semester'];
    $sid = $row['studentId'];

    if (!isset($grouped[$year])) {
        $grouped[$year] = [];
    }
    if (!isset($grouped[$year][$sem])) {
        $grouped[$year][$sem] = [];
    }
    if (!isset($grouped[$year][$sem][$sid])) {
        $grouped[$year][$sem][$sid] = [
            'name' => $row['student_name'],
            'reg_no' => $row['reg_no'],
            'course_code' => $row['course_code'],
            'results' => [],
        ];
    }

    $grouped[$year][$sem][$sid]['results'][] = [
        'unit_code' => $row['unit_code'],
        'unit_name' => $row['unit_name'],
        'course_work' => $row['course_work'],
        'exam' => $row['exam'],
        'total' => $row['course_work'] + $row['exam'],
    ];
}

function calculateGrade($total) {
    if ($total >= 70) return 'A';
    if ($total >= 60) return 'B';
    if ($total >= 50) return 'C';
    if ($total >= 40) return 'D';
    return 'F';
}

$years = $dbh->query("SELECT DISTINCT academic_year FROM results ORDER BY academic_year DESC")->fetchAll();
$courses = $dbh->query("SELECT id, course_name FROM course")->fetchAll();
?>

<main class="container">
    <div class="main-view">
        <h2 class="mb-4">Student Results</h2>
        <!-- Filters -->
        <form method="GET" class="row g-3 mb-4">
            <div class="col-md-3">
                <label>Academic Year</label>
                <select name="academic_year" class="form-select">
                    <option value="">All Years</option>
                    <?php foreach ($years as $y): ?>
                        <option value="<?= $y['academic_year'] ?>" <?= $academic_year == $y['academic_year'] ? 'selected' : '' ?>>
                            <?= $y['academic_year'] ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label>Semester</label>
                <select name="semester" class="form-select">
                    <option value="">All</option>
                    <option value="1" <?= $semester == '1' ? 'selected' : '' ?>>Semester 1</option>
                    <option value="2" <?= $semester == '2' ? 'selected' : '' ?>>Semester 2</option>
                </select>
            </div>
            <div class="col-md-3">
                <label>Course</label>
                <select name="course_id" class="form-select">
                    <option value="">All Courses</option>
                    <?php foreach ($courses as $c): ?>
                        <option value="<?= $c['id'] ?>" <?= $course_id == $c['id'] ? 'selected' : '' ?>>
                            <?= $c['course_name'] ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label>Search</label>
                <input type="text" name="search" class="form-control" placeholder="Name or Reg No" value="<?= htmlspecialchars($search) ?>">
            </div>
            <div class="col-md-1 d-flex align-items-end">
                <button class="btn btn-primary btn-lg w-100">Filter</button>
            </div>
        </form>
        <!-- Results Grouped by Academic Year and Semester -->
        <?php if (!empty($grouped)): ?>
            <?php foreach ($grouped as $year => $semesters): ?>
                <?php foreach ($semesters as $sem => $students): ?>
                    <h5 class="mt-4 bg-light p-2 border-start text-dark fw-bold border-primary border-3">
                        Academic Year: <?= $year ?> | Semester: <?= $sem ?>
                    </h5>
                    <?php foreach ($students as $sid => $student): ?>
                        <div class="card mb-3 shadow-sm">
                            <div class="card-header bg-dark text-white text-uppercase">
                                <strong><?= $student['name'] ?></strong> (<?= $student['reg_no'] ?>) - <?= $student['course_code'] ?>
                            </div>
                            <div class="card-body p-0">
                                <table class="table table-bordered mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="fw-bold text-secondary">Unit Code</th>
                                            <th class="fw-bold text-secondary">Unit Name</th>
                                            <th class="fw-bold text-secondary">Course Work</th>
                                            <th class="fw-bold text-secondary">Exam</th>
                                            <th class="fw-bold text-secondary">Total</th>
                                            <th class="fw-bold text-secondary">Grade</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($student['results'] as $res): ?>
                                            <tr>
                                                <td><?= $res['unit_code'] ?></td>
                                                <td><?= $res['unit_name'] ?></td>
                                                <td><?= $res['course_work'] ?></td>
                                                <td><?= $res['exam'] ?></td>
                                                <td><strong><?= $res['total'] ?></strong></td>
                                                <td><?= calculateGrade($res['total']) ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endforeach; ?>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="alert alert-info text-center">No results found for the selected filters.</div>
        <?php endif; ?>
    </div>
</main>

<?php include('footer.php'); ob_end_flush(); ?>
