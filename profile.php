<?php
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
        <div class="profile-container">
            <!-- Profile Header -->
            <div class="profile-header">
                <div class="profile-avatar">
                    <i class='bx bxs-user-circle'></i>
                </div>
                <div class="profile-info">
                    <h2 class="profile-name"><?= htmlspecialchars($role_data->name)?></h2>
                    <p class="profile-role">

                        <?php if ($role == 'student'): ?>
                            <span class="role-badge student">Student</span>
                        <?php elseif ($role == 'staff'): ?>
                            <span class="role-badge staff">Staff</span>
                        <?php endif; ?>

                    </p>
                </div>
            </div>

            <!-- Profile Details -->
            <div class="profile-details">
                <!-- Common Fields (for both student and staff) -->
                <div class="detail-section">
                    <h3 class="section-title">Personal Information</h3>
                    <div class="detail-row">
                        <span class="detail-label">Full Name:</span>
                        <span class="detail-value"><?= htmlspecialchars($role_data->name)?></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Email:</span>
                        <span class="detail-value"><?= htmlspecialchars($user_email)?></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Date of Birth:</span>
                        <span class="detail-value"><?= htmlspecialchars($role_data->date_of_birth)?></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Gender:</span>
                        <span class="detail-value"><?= htmlspecialchars($role_data->gender)?></span>
                    </div>
                </div>

                <!-- Student Specific Fields -->
                <?php if ($role == 'student'): ?>
                <div class="detail-section student-fields">
                    <h3 class="section-title">Academic Information</h3>
                    <div class="detail-row">
                        <span class="detail-label">Student Number:</span>
                        <span class="detail-value"><?= htmlspecialchars($role_data->student_no)?></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Registration Number:</span>
                        <span class="detail-value"><?= htmlspecialchars($role_data->reg_no)?></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Nationality:</span>
                        <span class="detail-value"><?= htmlspecialchars($role_data->nationality)?></span>
                    </div>
                </div>


                <!-- Staff Specific Fields -->
                <?php elseif ($role == 'staff'): ?>
                <div class="detail-section staff-fields">
                    <h3 class="section-title">Employment Information</h3>
                    <div class="detail-row">
                        <span class="detail-label">Staff ID:</span>
                        <span class="detail-value"><?= htmlspecialchars($role_data->staffId)?></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Department:</span>
                        <span class="detail-value"><?= htmlspecialchars($role_data->deptId) ?: 'N/A'?></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Rank/Position:</span>
                        <span class="detail-value"><?= htmlspecialchars($role_data->rank)?></span>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <!-- Action Buttons -->
            <div class="profile-actions">
                <button class="btn-edit" data-bs-toggle="modal" data-bs-target="#editProfileModal">
                    <i class='bx bx-edit'></i> Edit Profile
                </button>
                <button class="btn-change-password">
                    <i class='bx bx-lock-alt'></i> Change Password
                </button>
            </div>
        </div>
    </div>
</main>

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