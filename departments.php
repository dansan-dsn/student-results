<?php
include('header.php');

ob_start();

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['add_department'])) {
            // Add new department
            $stmt = $dbh->prepare("INSERT INTO department (department_name, department_head) VALUES (:name, :head)");
            $stmt->execute([
                ':name' => $_POST['department_name'],
                ':head' => $_POST['department_head']
            ]);
            
        } elseif (isset($_POST['edit_department'])) {
            // Update department
            $stmt = $dbh->prepare("UPDATE department SET department_name = :name, department_head = :head WHERE id = :id");
            $stmt->execute([
                ':id' => $_POST['department_id'],
                ':name' => $_POST['department_name'],
                ':head' => $_POST['department_head']
            ]);
            
        } elseif (isset($_POST['delete_department'])) {
            // Delete department
            $stmt = $dbh->prepare("DELETE FROM department WHERE id = :id");
            $stmt->execute([':id' => $_POST['department_id']]);
        }
        
        // Redirect to prevent form resubmission
        ob_end_clean();
        header("Location: ". strtok($_SERVER["REQUEST_URI"], '?'));
        exit();
        
    } catch (PDOException $e) {
        $error = "Database error: " . $e->getMessage();
        ob_end_flush();
    }
}

// Get all departments using PDO objects
$departments = $dbh->query("SELECT * FROM department ORDER BY department_name")->fetchAll(PDO::FETCH_OBJ);
ob_end_flush();
?>

<main class="container">
    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    
    <div class="main-view">
        <div class="department-container">
            <!-- Department Header with Add Button -->
            <div class="department-header">
                <h2 class="department-title">
                    <i class='bx bx-building-house'></i> Departments
                </h2>
                <button class="btn btn-add-department" data-bs-toggle="modal" data-bs-target="#addDepartmentModal">
                    <i class='bx bx-plus'></i> Add Department
                </button>
            </div>

            <!-- Department Table -->
            <div class="table-responsive">
                <table class="table table-dark table-hover table-striped">
                    <thead>
                        <tr>
                            <th scope="col">No.</th>
                            <th scope="col">Department Name</th>
                            <th scope="col">Department Head</th>
                            <th scope="col">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($departments) > 0): ?>
                            <?php foreach ($departments as $index => $dept): ?>
                            <tr>
                                <th scope="row"><?= $index + 1 ?></th>
                                <td><?= htmlspecialchars($dept->department_name) ?></td>
                                <td><?= htmlspecialchars($dept->department_head) ?></td>
                                <td>
                                    <button class="btn btn-sm btn-primary me-1 edit-department" 
                                            data-id="<?= $dept->id ?>" 
                                            data-name="<?= htmlspecialchars($dept->department_name) ?>" 
                                            data-head="<?= htmlspecialchars($dept->department_head) ?>" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#editDepartmentModal">
                                        <i class='bx bx-edit'></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger delete-department" 
                                            data-id="<?= $dept->id ?>" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#deleteDepartmentModal">
                                        <i class='bx bx-trash'></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="text-center">No departments found</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>

<!-- Add Department Modal -->
<div class="modal fade" id="addDepartmentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Department</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="">
                <div class="modal-body">
                    <div class="col-md-12 mb-3">
                        <label for="departmentName" class="form-label">Department Name</label>
                        <input type="text" class="form-control" id="departmentName" name="department_name" placeholder="Name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="departmentHead" class="form-label">Department Head</label>
                        <input type="text" class="form-control" id="departmentHead" name="department_head" placeholder="H.O.D" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" name="add_department" class="btn btn-primary">Save Department</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Department Modal -->
<div class="modal fade" id="editDepartmentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Department</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="">
                <div class="modal-body">
                    <input type="hidden" id="editDepartmentId" name="department_id">
                    
                    <div class="col-md-12 mb-3">
                        <label for="editDepartmentName" class="form-label">Department Name</label>
                        <input type="text" class="form-control" id="editDepartmentName" name="department_name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="editDepartmentHead" class="form-label">Department Head</label>
                        <input type="text" class="form-control" id="editDepartmentHead" name="department_head" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" name="edit_department" class="btn btn-primary">Update Department</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteDepartmentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="">
                <div class="modal-body">
                    <p>Are you sure you want to delete this department?</p>
                    <input type="hidden" id="deleteDepartmentId" name="department_id">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="delete_department" class="btn btn-danger">Delete</button>
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
    // Edit department - populate modal with data
    document.querySelectorAll('.edit-department').forEach(button => {
        button.addEventListener('click', function() {
            document.getElementById('editDepartmentId').value = this.getAttribute('data-id');
            document.getElementById('editDepartmentName').value = this.getAttribute('data-name');
            document.getElementById('editDepartmentHead').value = this.getAttribute('data-head');
        });
    });

    // Delete department - set the ID
    document.querySelectorAll('.delete-department').forEach(button => {
        button.addEventListener('click', function() {
            document.getElementById('deleteDepartmentId').value = this.getAttribute('data-id');
        });
    });
});
</script>