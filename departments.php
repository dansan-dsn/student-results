<?php
ob_start();
include('header.php');

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
            header("Location: departments.php?status=success&message=Created successful");
            exit();
            
        } elseif (isset($_POST['edit_department'])) {
            // Update department
            $stmt = $dbh->prepare("UPDATE department SET department_name = :name, department_head = :head WHERE id = :id");
            $stmt->execute([
                ':id' => $_POST['department_id'],
                ':name' => $_POST['department_name'],
                ':head' => $_POST['department_head']
            ]);
            header("Location: departments.php?status=success&message=Updated successfully");
            exit();
            
        } elseif (isset($_POST['delete_department'])) {
            // Delete department
            $stmt = $dbh->prepare("DELETE FROM department WHERE id = :id");
            $stmt->execute([':id' => $_POST['department_id']]);
            header("Location: departments.php?status=success&message=Deleted successfully");
            exit();
        }
        
    } catch (PDOException $e) {
        header("Location: departments.php?status=error&message=Database error: " . $e->getMessage());
    }
}

// / Get all departments using PDO objects
try {
    $departments = $dbh->query("
    SELECT dp.*,
           st.name
    FROM department dp
    LEFT JOIN staff st ON dp.department_head = st.staffId
    ORDER BY department_name")->fetchAll(PDO::FETCH_OBJ);
} catch(PDOException $e) {
    error_log("Database error (department table): " . $e->getMessage());
    $error_msg = "Error loading staff data";
    header("Location: departments.php?status=success&error=$error_msg");
    exit();
}

// department access
try {
    $staffs = $dbh->query("SELECT staffId, name FROM staff")->fetchAll(PDO::FETCH_OBJ);
} catch(PDOException $e) {
    error_log("Database error (department table): " . $e->getMessage());
    $error_msg = "Error loading staff data";
    header("Location: departments.php?status=success&error=$error_msg");
    exit();
}
?>

<main class="container">
    <div class="main-view">
        <div class="department-container">
            <!-- Department Header with Add Button -->
            <div class="department-header">
                <h2 class="department-title">
                    <i class='bx bx-building-house'></i> Departments
                </h2>
                <button class="btn btn-sm btn-add-department" data-bs-toggle="modal" data-bs-target="#addDepartmentModal">
                <i class='bx bx-plus-circle'></i>New
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
                                <td><?= htmlspecialchars($dept->name) ?></td>
                                <td>
                                    <button class="btn btn-sm btn-primary me-1 edit-department" 
                                            data-id="<?= $dept->id ?>" 
                                            data-name="<?= htmlspecialchars($dept->department_name) ?>" 
                                            data-head="<?= $dept->department_head ?>" 
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
                        <select class="form-select" id="departmentHead" name="department_head">
                            <option value="" selected disabled>Select Department Head</option>
                            <?php foreach($staffs as $staff): ?>
                                <option value="<?=$staff->staffId; ?>"> <?= htmlspecialchars($staff->name);?> </option>
                            <?php endforeach;?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" name="add_department" class="btn btn-sm btn-primary">Save</button>
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
                        <select class="form-select" id="editDepartmentHead" name="department_head">
                            <option value="" selected disabled>Select Department Head</option>
                            <?php foreach($staffs as $staff): ?>
                                <option value="<?=$staff->staffId; ?>" <?= ($staff->staffId == $departments->department_head) ? 'selected' : '';?> > <?= htmlspecialchars($staff->name);?> </option>
                            <?php endforeach;?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" name="edit_department" class="btn btn-sm btn-primary">Update</button>
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
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="delete_department" class="btn btn-sm btn-danger deleteBtn">Delete</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
ob_end_flush();
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