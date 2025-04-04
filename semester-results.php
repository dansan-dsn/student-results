<?php
include('header.php');

// Authentication check
if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] !== 'student')) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch data
$stmt = $dbh->prepare("
    SELECT
        r.*,
        cu.name AS course_unit_name,
        cu.credit_units,
        cu.code AS unit_code,
        st.name AS student_name,
        st.reg_no,
        st.course AS course_id,
        co.course_name,
        en.academic_year,
        en.year_of_study,
        en.semester
    FROM results r
    LEFT JOIN course_unit cu ON r.code = cu.id
    LEFT JOIN students st ON r.studentId = st.studentId
    LEFT JOIN course co ON st.course = co.id
    LEFT JOIN enrollments en ON st.studentId = en.studentId
    WHERE r.studentId = :user_id
    ORDER BY cu.name ASC
");
$stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$stmt->execute();
$results = $stmt->fetchAll(PDO::FETCH_OBJ);

// Calculate grades and totals
$total_credit_hours = 0;
$total_grade_points = 0;

foreach ($results as $result) {
    $result->total_score = $result->course_work + $result->exam;
    $result->grade = calculateGrade($result->total_score);
    $result->grade_point = calculateGradePoint($result->grade);
    $result->quality_point = $result->credit_units * $result->grade_point;
    
    $total_credit_hours += $result->credit_units;
    $total_grade_points += $result->quality_point;
}

$gpa = $total_credit_hours > 0 ? $total_grade_points / $total_credit_hours : 0;
?>

<main class="container">
    <div class="main-view">
        <div class="result-slip-container">
            <!-- Printable Result Slip -->
            <div class="result-slip">
                <!-- Header with Institution Info -->
                <div class="institution-header text-center mb-4">
                    <!-- <img src="/path/to/logo-light.png" alt="Institution Logo" class="institution-logo" style="height: 80px;"> -->
                    <h1 class="institution-name">UNIVERSITY OF MINE</h1>
                    <p class="institution-address">123 Education Street, Knowledge City</p>
                    <h2 class="document-title">OFFICIAL RESULT SLIP</h2>
                </div>
        
                <!-- Student Information Section -->
                <div class="student-info-section mb-4">
                    <table class="info-table">
                        <tr>
                            <td><strong>Student Name:</strong> <span class="text-secondary"><?= htmlspecialchars($results[0]->student_name ?? '') ?></span></td>
                            <td><strong>Registration No:</strong> <span class="text-secondary"><?= htmlspecialchars($results[0]->reg_no ?? '') ?></span></td>
                        </tr>
                        <tr>
                            <td><strong>Program:</strong> <span class="text-secondary"><?= htmlspecialchars($results[0]->course_name ?? '') ?></span></td>
                                
                            <td><strong>Academic Year:</strong> <span class="text-secondary"><?= htmlspecialchars($results[0]->academic_year ?? '') ?></span></td>
                        </tr>
                        <tr>
                        <td><strong>Year of Study:</strong> <span class="text-secondary"><?= htmlspecialchars($results[0]->year_of_study ?? '') ?></span></td>
                        <td><strong>Semester:</strong> <span class="text-secondary"><?= htmlspecialchars($results[0]->semester ?? '') ?></span></td>
                        </tr>
                    </table>
                </div>
        
                <!-- Results Table -->
                <div class="results-table mb-4">
                    <table>
                        <thead>
                            <tr class="table-header">
                                <th >Code</th>
                                <th>Course Unit</th>
                                <th>Credit Hours</th>
                                <th>Course Work</th>
                                <th>Exam</th>
                                <th>Total</th>
                                <th>Grade</th>
                                <th>GP</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($results as $result): ?>
                            <tr>
                                <td style="padding: 10px 15px; font-family: SFMono-Regular, Menlo, Monaco, Consolas, 'Liberation Mono', 'Courier New', monospace;"><?= htmlspecialchars($result->unit_code) ?></td>
                                <td style="padding: 10px 15px;"><?= htmlspecialchars($result->course_unit_name) ?></td>
                                <td style="padding: 10px 15px; text-align: center;"><?= htmlspecialchars($result->credit_units) ?></td>
                                <td style="padding: 10px 15px; text-align: center;"><?= htmlspecialchars($result->course_work) ?></td>
                                <td style="padding: 10px 15px; text-align: center;"><?= htmlspecialchars($result->exam) ?></td>
                                <td style="padding: 10px 15px; text-align: center; font-weight: bold;"><?= htmlspecialchars($result->total_score) ?></td>
                                <td style="padding: 10px 15px; text-align: center; font-weight: bold; 
                                    <?php 
                                    switch(strtolower($result->grade)) {
                                        case 'a': echo 'color: #2ecc71'; break;
                                        case 'b': echo 'color: #3498db'; break;
                                        case 'c': echo 'color: #f39c12'; break;
                                        case 'd': echo 'color: #e67e22'; break;
                                        case 'f': echo 'color: #e74c3c'; break;
                                        default: echo 'color: white';
                                    }
                                    ?>">
                                    <?= htmlspecialchars($result->grade) ?>
                                </td>
                                <td style="padding: 10px 15px; text-align: center;"><?= number_format($result->grade_point, 1) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
        
                <!-- Summary Section -->
                <div class="summary-section">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="summary-table">
                                <tr>
                                    <td><strong>Total Credit Hours:</strong></td>
                                    <td class="highlight-text"><?= $total_credit_hours ?></td>
                                </tr>
                                <tr>
                                    <td><strong>GPA:</strong></td>
                                    <td class="highlight-text"><?= number_format($gpa, 2) ?></td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6 text-right">
                            <div class="signature-area">
                                <p class="signature-line">_________________________</p>
                                <p><strong>Registrar's Signature</strong></p>
                                <p>Date: <?= date('d/m/Y') ?></p>
                            </div>
                        </div>
                    </div>
                </div>
        
                <!-- Footer Note -->
                <div class="footer-note mt-4">
                    <p class="text-center small">
                        This is an official document. Any alteration renders it invalid.<br>
                        For verification, please contact the Academic Registrar's Office.
                    </p>
                </div>
            </div>
        
            <!-- Print Button -->
            <div class="text-center mt-3">
                <!-- <button onclick="window.print()" class="btn print-button">
                    <i class="fas fa-print"></i> Print Result Slip
                </button> -->
            </div>
        </div>
    </div>
</main>

<?php
include('footer.php');

// Grade calculation functions
function calculateGrade($score) {
    if ($score >= 80) return 'A';
    if ($score >= 70) return 'B';
    if ($score >= 60) return 'C';
    if ($score >= 50) return 'D';
    return 'F';
}

function calculateGradePoint($grade) {
    switch ($grade) {
        case 'A': return 5.0;
        case 'B': return 4.0;
        case 'C': return 3.0;
        case 'D': return 2.0;
        default: return 0.0;
    }
}
?>