<?php
ob_start();
include('header.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['add_course'])) {
            // Add new course
            $stmt = $dbh->prepare("INSERT INTO course (course_name, course_code) VALUES (:course_name, :course_code)");
            $stmt->execute([
                ':course_name' => $_POST['course_name'],
                ':course_code' => $_POST['course_code']
            ]);
            header("Location: course.php?status=success&message=Created successful");
            exit();
            
        } elseif (isset($_POST['edit_course'])) {
            // Update course
            $stmt = $dbh->prepare("UPDATE course SET course_name = :course_name, course_code = :course_code WHERE id = :id");
            $stmt->execute([
                ':id' => $_POST['course_id'],
                ':course_name' => $_POST['course_name'],
                ':course_code' => $_POST['course_code']
            ]);
            header("Location: course.php?status=success&message=Updated successfully");
            exit();
            
        } elseif (isset($_POST['delete_course'])) {
            // Delete course
            $stmt = $dbh->prepare("DELETE FROM course WHERE id = :id");
            $stmt->execute([':id' => $_POST['course_id']]);
        }
        
    } catch (PDOException $e) {
        header("Location: course.php?status=error&message=Database error: " . $e->getMessage());
    }
}

// Get all courses using PDO objects
$courses = $dbh->query("SELECT * FROM course ORDER BY course_name")->fetchAll(PDO::FETCH_OBJ);
?>

<main class="container">

    <div class="main-view">
        <div class="department-container">
            <!-- Department Header with Add Button -->
            <div class="department-header">
                <h2 class="department-title">
                    <i class='bx bx-building-house'></i> Main Courses
                </h2>
                <button class="btn btn-add-department" data-bs-toggle="modal" data-bs-target="#addCourseModal">
                    <i class='bx bx-plus'></i> Add Course
                </button>
            </div>

            <!-- Department Table -->
            <div class="table-responsive">
                <table class="table table-dark table-hover table-striped">
                    <thead>
                        <tr>
                            <th scope="col">No.</th>
                            <th scope="col">Course Code</th>
                            <th scope="col">Course Name</th>
                            <th scope="col">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($courses) > 0): ?>
                            <?php foreach ($courses as $index => $course): ?>
                            <tr>
                                <th scope="row"><?= $index + 1 ?></th>
                                <td><?= htmlspecialchars($course->course_code) ?></td>
                                <td><?= htmlspecialchars($course->course_name) ?></td>
                                <td>
                                    <button class="btn btn-sm btn-primary me-1 edit-course" 
                                            data-id="<?= $course->id ?>" 
                                            data-code="<?= htmlspecialchars($course->course_code) ?>" 
                                            data-name="<?= htmlspecialchars($course->course_name) ?>" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#editCourseModal">
                                        <i class='bx bx-edit'></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger delete-course" 
                                            data-id="<?= $course->id ?>" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#deleteCourseModal">
                                        <i class='bx bx-trash'></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="text-center">No course found</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>

<!-- Add Course Modal -->
<div class="modal fade" id="addCourseModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Course</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="">
                <div class="modal-body">
                    
                    <div class="mb-3">
                        <label for="courseName" class="form-label">Course Name</label>
                        <input type="text" class="form-control" id="courseName" name="course_name" placeholder="Name" required>
                    </div>

                    <div class="col-md-12 mb-3">
                        <label for="courseCode" class="form-label">Course Code</label>
                        <input type="text" class="form-control" id="courseCode" name="course_code" placeholder="Code" required>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" name="add_course" class="btn btn-primary">Save Course</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Course Modal -->
<div class="modal fade" id="editCourseModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Course</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="">
                <div class="modal-body">
                    <input type="hidden" id="editCourseId" name="course_id">
                    
                    <div class="mb-3">
                        <label for="editCourseName" class="form-label">Course Name</label>
                        <input type="text" class="form-control" id="editCourseName" name="course_name" required>
                    </div>

                    <div class="col-md-12 mb-3">
                        <label for="editCourseCode" class="form-label">Course Code</label>
                        <input type="text" class="form-control" id="editCourseCode" name="course_code" required>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" name="edit_course" class="btn btn-primary">Update Course</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteCourseModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="">
                <div class="modal-body">
                    <p>Are you sure you want to delete this course?</p>
                    <input type="hidden" id="deleteCourseId" name="course_id">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="delete_course" class="btn btn-danger deleteBtn">Delete</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
include('footer.php');
?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Edit course - populate modal with data
    document.querySelectorAll('.edit-course').forEach(button => {
        button.addEventListener('click', function() {
            document.getElementById('editCourseId').value = this.getAttribute('data-id');
            document.getElementById('editCourseCode').value = this.getAttribute('data-code');
            document.getElementById('editCourseName').value = this.getAttribute('data-name');
        });
    });

    // Delete course - set the ID
    document.querySelectorAll('.delete-course').forEach(button => {
        button.addEventListener('click', function() {
            document.getElementById('deleteCourseId').value = this.getAttribute('data-id');
        });
    });
});
</script>