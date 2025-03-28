<?php
$_GET['page'] = 'departments';
include('header.php');

$user_id = $_SESSION['user_id'];
$role = strtolower($_SESSION['user_role'] ?? '');
$user_email = '';

try {
    $stmt = $dbh->prepare("SELECT email FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user_data = $stmt->fetch(PDO::FETCH_OBJ);
    $user_email = $user_data->email ?? "";
}catch(PDOException $e){
    error_log("Database error (users table): " . $e->getMessage());
}

// Fetch data by role
$role_data = [];
try {
    if($role === 'student') {
        $stmt = $dbh->prepare("SELECT * FROM students WHERE studentId = ?");
    } elseif ($role === 'staff') {
        $stmt = $dbh->prepare("SELECT * FROM staff WHERE staffId = ?");
    }

    if (isset($stmt)) {
        $stmt->execute([$user_id]);
        $role_data = $stmt->fetch(PDO::FETCH_OBJ) ?: [];
    }
} catch (PDOException $e) {
    error_log("Database error (role table): " . $e->getMessage());
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
                            <th scope="col">Name</th>
                            <th scope="col">Head of Department</th>
                            <th scope="col">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <th scope="row">1</th>
                            <td>Computer Science</td>
                            <td>Dr. John Smith</td>
                            <td>
                                <button class="btn btn-sm btn-primary me-1">
                                    <i class='bx bx-edit'></i> Edit
                                </button>
                                <button class="btn btn-sm btn-danger">
                                    <i class='bx bx-trash'></i> Delete
                                </button>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">2</th>
                            <td>Electrical Engineering</td>
                            <td>Prof. Sarah Johnson</td>
                            <td>
                                <button class="btn btn-sm btn-primary me-1">
                                    <i class='bx bx-edit'></i> Edit
                                </button>
                                <button class="btn btn-sm btn-danger">
                                    <i class='bx bx-trash'></i> Delete
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>

<!-- Add Department Modal (Structure only) -->
<div class="modal fade" id="addDepartmentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <!-- Modal content would go here -->
        </div>
    </div>
</div>

<!-- edit profile modal -->
<div class="modal fade" id="editProfileModal" tabindex="-1" aria-labelledby="editProfileModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editProfileModalLabel">Edit Profile</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="profileEditForm" method="POST" action="update_profile.php">
                    <input type="hidden" name="user_id" value="<?= $user_id ?>">
                    <input type="hidden" name="user_role" value="<?= $role ?>">
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="firstName" class="form-label">First Name</label>
                            <input type="text" class="form-control" id="firstName" name="first_name" 
                                   value="<?= htmlspecialchars(explode(' ', $role_data->name ?? '')[0] ?? '') ?>">
                        </div>
                        <div class="col-md-6">
                            <label for="lastName" class="form-label">Last Name</label>
                            <input type="text" class="form-control" id="lastName" name="last_name" 
                                   value="<?= htmlspecialchars(explode(' ', $role_data->name ?? '')[1] ?? '') ?>">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" 
                               value="<?= htmlspecialchars($user_email) ?>">
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="dob" class="form-label">Date of Birth</label>
                            <input type="date" class="form-control" id="dob" name="date_of_birth" 
                                   value="<?= htmlspecialchars($role_data->date_of_birth ?? '') ?>">
                        </div>
                        <div class="col-md-6">
                            <label for="gender" class="form-label">Gender</label>
                            <select class="form-select" id="gender" name="gender">
                                <option value="Male" <?= ($role_data->gender ?? '') == 'Male' ? 'selected' : '' ?>>Male</option>
                                <option value="Female" <?= ($role_data->gender ?? '') == 'Female' ? 'selected' : '' ?>>Female</option>
                                <option value="Other" <?= ($role_data->gender ?? '') == 'Other' ? 'selected' : '' ?>>Other</option>
                            </select>
                        </div>
                    </div>
                    
                    <?php if ($role == 'student'): ?>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="studentNo" class="form-label">Student Number</label>
                            <input type="text" class="form-control" id="studentNo" name="student_no" 
                                   value="<?= htmlspecialchars($role_data->student_no ?? '') ?>" readonly>
                        </div>
                        <div class="col-md-6">
                            <label for="regNo" class="form-label">Registration Number</label>
                            <input type="text" class="form-control" id="regNo" name="reg_no" 
                                   value="<?= htmlspecialchars($role_data->reg_no ?? '') ?>">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="nationality" class="form-label">Nationality</label>
                        <input type="text" class="form-control" id="nationality" name="nationality" 
                               value="<?= htmlspecialchars($role_data->nationality ?? '') ?>">
                    </div>
                    
                    <?php elseif ($role == 'staff'): ?>
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label for="department" class="form-label">Department</label>
                            <select class="form-select" id="department" name="department">
                                <?php
                                // Fetch departments from database
                                $depts = [];
                                try {
                                    $stmt = $dbh->query("SELECT * FROM departments");
                                    $depts = $stmt->fetchAll(PDO::FETCH_OBJ);
                                } catch (PDOException $e) {
                                    error_log("Department fetch error: " . $e->getMessage());
                                }
                                
                                foreach ($depts as $dept) {
                                    $selected = ($dept->id == ($role_data->deptId ?? '')) ? 'selected' : '';
                                    echo "<option value='{$dept->id}' $selected>{$dept->name}</option>";
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="rank" class="form-label">Rank/Position</label>
                        <input type="text" class="form-control" id="rank" name="rank" 
                               value="<?= htmlspecialchars($role_data->rank ?? '') ?>">
                    </div>
                    <?php endif; ?>
                    
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>


<?php
include('footer.php');
?>