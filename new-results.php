<?php
include('header.php');

// Check if user is authorized (lecturer/admin)
if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] !== 'staff')) {
    header("Location: login.php");
    exit();
}

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    if(isset($_POST['save_mrks'])){
        $stmt = $dbh->prepare("INSERT INTO results (code, studentId, course_work, exam) VALUES (:code, :studentId, :course_work, :exam) ");
        $stmt->execute([
            ':code' => $_POST['code'],
            ':studentId' => $_POST['studentId'],
            ':course_work' => $_POST['course_work'],
            ':exam' => $_POST['exam'],
        ]);
        header("Location: new-results.php?status=success&message=Added successful");
        exit();
    }
}

// Fetch courses units taught by this lecturer (example)
$lecturer_id = $_SESSION['user_id'];
$course_units = []; // This would come from your database query
try {
    $stmt = $dbh->query("SELECT * FROM course_unit");
    $course_units = $stmt->fetchAll(PDO::FETCH_OBJ);
} catch (PDOException $e) {
    $error = "Error fetching course units: " . $e->getMessage();
}

try{
    $students = $dbh->query("SELECT * FROM students")->fetchAll(PDO::FETCH_OBJ);
}catch(PDOException $e){
    $error = "Error";
}

?>

<main class="container">
    <div class="main-view">
        <div class="department-container">
            <div class="marks-entry-container">
                <div class="entry-header">
                    <h2><i class='bx bx-book-alt'></i> Enter Student Marks</h2>
                    <div class="header-actions">
                        <button class="btn btn-import" id="importMarksBtn">
                            <i class='bx bx-upload'></i> Bulk Import
                        </button>
                    </div>
                </div>

                <div class="marks-form-container">
                    <form id="marksEntryForm" method="POST">
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="course_unit">Course Unit</label>
                                <select id="course_unit" name="code" class="form-control search_select" data-placeholder="Search course unit.." required>
                                    <option></option>
                                    <?php foreach ($course_units as $unit): ?>
                                        <option value="<?= $unit->id ?>">
                                            <?= $unit->code ?> - <?= $unit->name ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label>Student</label>
                                <select class="form-control search_select" name="studentId" data-placeholder="Search student..." required>
                                    <option></option>
                                    <?php foreach ($students as $student): ?>
                                        <option value="<?= $student->studentId ?>"><?= $student->reg_no ?> - <?= $student->name ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="form-grid">
                            <div class="form-group">
                                <label for="courseworkMarks">Coursework (30%)</label>
                                <input type="number" id="courseworkMarks" name="course_work" 
                                       class="form-control" min="0" max="30" step="0.5" 
                                       placeholder="Enter coursework marks" required>
                            </div>

                            <div class="form-group">
                                <label for="examMarks">Exam (70%)</label>
                                <input type="number" id="examMarks" name="exam" 
                                       class="form-control" min="0" max="70" step="0.5" 
                                       placeholder="Enter exam marks" required>
                            </div>
                        </div>

                        <div class="form-footer">
                            <button type="reset" class="btn btn-reset">
                                <i class='bx bx-reset'></i> Clear Marks
                            </button>
                            <button type="submit" class="btn btn-submit" name="save_mrks">
                                <i class='bx bx-save'></i> Save Marks
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Recent Entries Preview -->
                <!-- <div class="recent-entries">
                    <h4>Recent Entries</h4>
                    <div class="entries-table">
                        <div class="table-header">
                            <span>Student ID</span>
                            <span>Course Unit</span>
                            <span>Coursework</span>
                            <span>Exam</span>
                            <span>Total</span>
                        </div>
                        <div class="table-body">
                            <div class="no-entries">
                                <i class='bx bx-info-circle'></i>
                                <p>No recent entries found</p>
                            </div>
                        </div>
                    </div>
                </div> -->
            </div>
        </div>
    </div>
</main>

<?php
include('footer.php');
?>

<script>
// Basic client-side validation
document.getElementById('marksEntryForm').addEventListener('submit', function(e) {
    const coursework = parseFloat(document.getElementById('courseworkMarks').value);
    const exam = parseFloat(document.getElementById('examMarks').value);
    
    if (coursework > 30) {
        alert('Coursework marks cannot exceed 30');
        e.preventDefault();
    }
    
    if (exam > 70) {
        alert('Exam marks cannot exceed 70');
        e.preventDefault();
    }
});

// select2
$(document).ready(function() {
    // Initialize Select2 with dark theme settings
    $('.search_select').each(function(){
        $(this).select2({
            placeholder: $(this).data('placeholder') || 'Search...',
            width: '100%',
            dropdownParent: $(this).closest('.marks-form-container'), // Proper positioning
            theme: 'default' // If you're using bootstrap4 theme
        });
        // select2({
        // placeholder: "Search course unit...",
        // theme: 'default',
        // width: '100%',
        // dropdownParent: $('.marks-form-container'),
        // templateResult: formatOption,
        // templateSelection: formatSelection
    });
    
    function formatOption(option) {
        if (!option.id) return option.text;
        return $('<span>').text(option.text);
    }
    
    function formatSelection(option) {
        if (!option.id) return option.text;
        return $('<span>').text(option.text);
    }
});
</script>
