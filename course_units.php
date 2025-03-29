<?php
ob_start();
include('header.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['add_course_unit'])) {
            // Add new course units
            $stmt = $dbh->prepare("INSERT INTO course_unit (name, code, credit_units) VALUES (:name, :code, :credit_units)");
            $stmt->execute([
                ':name' => $_POST['name'],
                ':code' => $_POST['code'],
                ':credit_units' => $_POST['credit_units']
            ]);
            header("Location: course_units.php?status=success&message=Created successful");
            exit();
            
        } elseif (isset($_POST['edit_course_unit'])) {
            // Update course units
            $stmt = $dbh->prepare("UPDATE course_unit SET name = :name, code = :code, credit_units = :credit_units WHERE id = :id");
            $stmt->execute([
                ':id' => $_POST['id'],
                ':name' => $_POST['name'],
                ':code' => $_POST['code'],
                ':credit_units' => $_POST['credit_units']
            ]);
            header("Location: course_units.php?status=success&message=Updated successfully");
            exit();
            
        } elseif (isset($_POST['delete_course_unit'])) {
            // Delete course units
            $stmt = $dbh->prepare("DELETE FROM course_unit WHERE id = :id");
            $stmt->execute([':id' => $_POST['id']]);
        }
        
    } catch (PDOException $e) {
        header("Location: course_units.php?status=error&message=Database error: " . $e->getMessage());
    }
}

// Get all courses using PDO objects
$course_units = $dbh->query("SELECT * FROM course_unit ORDER BY name")->fetchAll(PDO::FETCH_OBJ);
?>

<main class="container">

    <div class="main-view">
        <div class="department-container">
            <!-- Department Header with Add Button -->
            <div class="department-header">
                <h2 class="department-title">
                    <i class='bx bx-building-house'></i> Course Units
                </h2>
                <button class="btn btn-sm btn-add-department" data-bs-toggle="modal" data-bs-target="#addCourseUnitModal">
                <i class='bx bx-plus-circle'></i>New Course Unit
                </button>
            </div>

            <!-- Department Table -->
            <div class="table-responsive">
                <table class="table table-dark table-hover table-striped">
                    <thead>
                        <tr>
                            <th scope="col">No.</th>
                            <th scope="col">Unit Code</th>
                            <th scope="col">Unit Name</th>
                            <th scope="col">Credit Units</th>
                            <th scope="col">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($course_units) > 0): ?>
                            <?php foreach ($course_units as $index => $course_unit): ?>
                            <tr>
                                <th scope="row"><?= $index + 1 ?></th>
                                <td><?= htmlspecialchars($course_unit->code) ?></td>
                                <td><?= htmlspecialchars($course_unit->name) ?></td>
                                <td><?= htmlspecialchars($course_unit->credit_units) ?></td>
                                <td>
                                    <button class="btn btn-sm btn-primary me-1 edit-course_unit" 
                                            data-id="<?= $course_unit->id ?>" 
                                            data-code="<?= htmlspecialchars($course_unit->code) ?>" 
                                            data-name="<?= htmlspecialchars($course_unit->name) ?>" 
                                            data-unit="<?= htmlspecialchars($course_unit->credit_units) ?>" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#editCourseUnitModal">
                                        <i class='bx bx-edit'></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger delete-course_unit" 
                                            data-id="<?= $course_unit->id ?>" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#deleteCourseUnitModal">
                                        <i class='bx bx-trash'></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="text-center">No course unit found</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>

<!-- Add Course Modal -->
<div class="modal fade" id="addCourseUnitModal" tabindex="-1" aria-hidden="true">
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
                        <input type="text" class="form-control" id="courseName" name="name" placeholder="Name" required>
                    </div>

                    <div class="col-md-12 mb-3">
                        <label for="courseCode" class="form-label">Course Code</label>
                        <input type="text" class="form-control" id="courseCode" name="code" placeholder="Code" required>
                    </div>

                    <div class="col-md-12 mb-3">
                        <label for="creditUnits" class="form-label">Credit Units</label>
                        <input type="text" class="form-control" id="creditUnits" name="credit_units" placeholder="Credit Units" required>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" name="add_course_unit" class="btn btn-sm btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Course Modal -->
<div class="modal fade" id="editCourseUnitModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Course</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="">
                <div class="modal-body">
                    <input type="hidden" id="editCourseUnitId" name="id">
                    
                    <div class="mb-3">
                        <label for="editCourseUnitName" class="form-label">Course Unit Name</label>
                        <input type="text" class="form-control" id="editCourseUnitName" name="name" required>
                    </div>

                    <div class="col-md-12 mb-3">
                        <label for="editCourseUnitCode" class="form-label">Course Unit Code</label>
                        <input type="text" class="form-control" id="editCourseUnitCode" name="code" required>
                    </div>

                    <div class="col-md-12 mb-3">
                        <label for="editCreditUnits" class="form-label">Credit Units</label>
                        <input type="text" class="form-control" id="editCreditUnits" name="credit_units" required>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" name="edit_course_unit" class="btn btn-sm btn-primary">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteCourseUnitModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="">
                <div class="modal-body">
                    <p>Are you sure you want to delete this course unit?</p>
                    <input type="hidden" id="deleteCourseUnitId" name="id">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="delete_course_unit" class="btn btn-sm btn-danger deleteBtn">Delete</button>
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
    // Edit course unit - populate modal with data
    document.querySelectorAll('.edit-course_unit').forEach(button => {
        button.addEventListener('click', function() {
            document.getElementById('editCourseUnitId').value = this.getAttribute('data-id');
            document.getElementById('editCourseUnitCode').value = this.getAttribute('data-code');
            document.getElementById('editCourseUnitName').value = this.getAttribute('data-name');
            document.getElementById('editCreditUnits').value = this.getAttribute('data-unit');
        });
    });

    // Delete course unit - set the ID
    document.querySelectorAll('.delete-course_unit').forEach(button => {
        button.addEventListener('click', function() {
            document.getElementById('deleteCourseUnitId').value = this.getAttribute('data-id');
        });
    });
});
</script>