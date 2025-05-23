<?php
ob_start();
include('header.php');

$user_id = $_SESSION['user_id'];
$role = strtolower($_SESSION['user_role'] ?? '');
$user_email = '';
$success_msg = '';
$error_msg = '';

try {
    $stmt = $dbh->prepare("SELECT email FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user_data = $stmt->fetch(PDO::FETCH_OBJ);
    $user_email = $user_data->email ?? "";
}catch(PDOException $e){
    error_log("Database error (users table): " . $e->getMessage());
    $error_msg = "Error loading user data";
    header("Location: profile.php?status=success&error=$error_msg");
    exit();
}

// Fetch data by role
$role_data = [];
try {
    if($role === 'student') {

        $stmt = $dbh->prepare("
        SELECT std.*, dp.department_name AS dpt_name, c.course_name AS course_name
        FROM students std
        LEFT JOIN department dp ON std.department = dp.id
        LEFT JOIN course c ON std.course = c.id
        WHERE studentId = ?"
    );

    $stmt->execute([$user_id]);

    $role_data = $stmt->fetch(PDO::FETCH_OBJ);
    error_log("DEBUG: Student data: " . print_r($role_data, true));

    if(!$role_data) {
        die("DEBUG: No student record found for ID: $user_id");
    }

    } elseif ($role === 'staff') {

        $stmt = $dbh->prepare("
        SELECT st.*, dp.department_name AS dpt_name
        FROM staff st
        LEFT JOIN department dp ON st.department = dp.id
        WHERE staffId = ?"
    );

    $stmt->execute([$user_id]);
    $role_data = $stmt->fetch(PDO::FETCH_OBJ);

    } else {
        throw new Exception("Invalid user role: $role");
    }

} catch (PDOException $e) {
    error_log("Database error (role table): " . $e->getMessage());
    $error_msg = "Error loading profile data";
    // Temporarily add this before the redirect to see the actual query error
    die("Database Error: " . $e->getMessage() . " | Role: $role | User ID: $user_id");
    header("Location: profile.php?status=success&error=$error_msg");
    exit();
}

// department access
try {
    $department = $dbh->query("SELECT id, department_name FROM department");
    $dept = $department->fetchAll(PDO::FETCH_OBJ);
} catch(PDOException $e) {
    error_log("Database error (department table): " . $e->getMessage());
    $error_msg = "Error loading department data";
    header("Location: profile.php?status=success&error=$error_msg");
    exit();
}

// course access
try {
    $courses = $dbh->query("SELECT id, course_name FROM course")->fetchAll(PDO::FETCH_OBJ);
}catch(PDOException $e) {
    error_log("Database error (course table): " . $e->getMessage());
    $error_msg = "Error loading course data";
    header("Location: profile.php?status=success&error=$error_msg");
    exit();
}

// enroll
if (isset($_POST['enroll'])) {
    $academic_year = $_POST['academic_year'];
    $semester = $_POST['semester'];
    $year_of_study = $_POST['year_of_study'];

    try {
        // First check if already enrolled for this semester/year
        $stmt = $dbh->prepare("SELECT * FROM enrollments WHERE studentId = ? AND academic_year = ? AND semester = ? AND year_of_study = ?");
        $stmt->execute([$user_id, $academic_year, $semester, $year_of_study]);
        
        if ($stmt->rowCount() > 0) {
            $error_msg = "Already enrolled for this academic year, semester and year of study.";
            header("Location: profile.php?status=error&message=$error_msg");
            exit();
        }

        // Get the most recent enrollment
        $stmt = $dbh->prepare("SELECT * FROM enrollments WHERE studentId = ? ORDER BY year_of_study DESC, semester DESC LIMIT 1");
        $stmt->execute([$user_id]);
        $last_enrollment = $stmt->fetch(PDO::FETCH_OBJ);

        if ($last_enrollment) {
            // Check if results exist for the last enrollment
            $stmt = $dbh->prepare("SELECT * FROM results WHERE studentId = ? AND year_of_study = ? AND semester = ?");
            $stmt->execute([$user_id, $last_enrollment->year_of_study, $last_enrollment->semester]);
            $has_results = $stmt->rowCount() > 0;

            if (!$has_results) {
                $error_msg = "You must have results for Year {$last_enrollment->year_of_study}, Semester {$last_enrollment->semester} before enrolling.";
                header("Location: profile.php?status=error&message=$error_msg");
                exit();
            }

            // Check if new enrollment follows correct progression
            $valid = false;

            if ($last_enrollment->year_of_study == 1 && $last_enrollment->semester == 1 && 
                $year_of_study == 1 && $semester == 2) {
                $valid = true;
            }
            elseif ($last_enrollment->year_of_study == 1 && $last_enrollment->semester == 2 && 
                    $year_of_study == 2 && $semester == 1) {
                $valid = true;
            }
            elseif ($last_enrollment->year_of_study == 2 && $last_enrollment->semester == 1 && 
                    $year_of_study == 2 && $semester == 2) {
                $valid = true;
            }
            elseif ($last_enrollment->year_of_study == 2 && $last_enrollment->semester == 2 && 
                    $year_of_study == 3 && $semester == 1) {
                $valid = true;
            }
            elseif ($last_enrollment->year_of_study == 3 && $last_enrollment->semester == 1 && 
                    $year_of_study == 3 && $semester == 2) {
                $valid = true;
            }

            if (!$valid) {
                $error_msg = "Invalid enrollment progression. Please follow the correct sequence.";
                header("Location: profile.php?status=error&message=$error_msg");
                exit();
            }

        } else {
            // First enrollment: must be Year 1 Semester 1
            if ($year_of_study != 1 || $semester != 1) {
                $error_msg = "First enrollment must be Year 1 Semester 1.";
                header("Location: profile.php?status=error&message=$error_msg");
                exit();
            }
        }

        // All checks passed – Insert new enrollment
        $stmt = $dbh->prepare("INSERT INTO enrollments (studentId, academic_year, semester, year_of_study) VALUES (?, ?, ?, ?)");
        $stmt->execute([$user_id, $academic_year, $semester, $year_of_study]);

        $success_msg = "Successfully enrolled!";
        header("Location: profile.php?status=success&message=$success_msg");
        exit();

    } catch (PDOException $e) {
        error_log("Database error (enrollment): " . $e->getMessage());
        $error_msg = "Error enrolling in course: " . $e->getMessage();
        header("Location: profile.php?status=error&message=$error_msg");
        exit();
    }
}


// Handle Profile Update
if (isset($_POST['update_profile'])) {
    try{
        $dbh->beginTransaction();

        // update the email
        $new_email = $_POST['email'];
        $stmt = $dbh->prepare('UPDATE users SET email = ? WHERE id = ?');
        $stmt->execute([$new_email, $user_id]);

        // update the role based features
        $new_name = trim($_POST['first_name'] . ' ' . $_POST['last_name']);
        $full_name = explode(' ', $new_name);
        $date_of_birth = $_POST['date_of_birth'];
        $gender = $_POST['gender'];

        if($role === 'staff') {
            $department = $_POST['department'];
            $rank = $_POST['rank'];

            $stmt = $dbh->prepare('UPDATE staff SET name = ?, date_of_birth = ?, gender = ?, department = ?, rank = ? WHERE staffId = ?');
            $stmt->execute([$new_name, $date_of_birth, $gender, $department, $rank, $user_id]);
        } elseif($role === 'student') {
            $reg_no = $_POST['reg_no'];
            $student_no = $_POST['student_no'];
            $nationality = $_POST['nationality'];

            $stmt = $dbh->prepare('UPDATE students SET name = ?, student_no = ?, reg_no = ?, course = ?, department = ?, date_of_birth = ?, gender = ?, nationality = ? WHERE studentId = ?');
            $stmt->execute([$new_name, $student_no, $reg_no, $_POST['course'], $_POST['department'], $date_of_birth, $gender, $nationality, $user_id]);
        }
        $dbh->commit();
        $success_msg = "Profile successfully updated!";
        header("Location: profile.php?status=success&message=$success_msg");
        exit();

    } catch (PDOException $e) {
        $dbh->rollBack();
        error_log("Database error" . $e->getMessage());
        $error_msg = $e->getMessage();
        header("Location: profile.php?status=error&message=$error_msg");
        exit();
    }
}

// change password
if(isset($_POST['change_password'])){
    $current_pwd = $_POST['current_password'];
    $new_pwd = $_POST['new_password'];
    $confirm_pwd = $_POST['confirm_password'];

    $stmt = $dbh->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_OBJ);
    if($user && password_verify($current_pwd, $user->password)){
        if($new_pwd === $confirm_pwd){
            $hashed_new_pwd = password_hash($new_pwd, PASSWORD_DEFAULT);
            $stmt = $dbh->prepare("UPDATE users SET password = ? WHERE id =?");
            $stmt->execute([$hashed_new_pwd, $user_id]);
            $success_msg = "Password successfully changed!";
            header("Location: profile.php?status=success&message=$success_msg");
            exit();
        } else {
            $error_msg = "New password and confirmation do not match.";
            header("Location: profile.php?status=error&message=$error_msg");
            exit();
        }
    } else {
        $error_msg = "Current password is incorrect.";
        header("Location: profile.php?status=error&message=$error_msg");
        exit();
    }

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
                    <h2 class="profile-name"><?= htmlspecialchars($role_data->name) ?: 'N/A'?></h2>
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
                        <span class="detail-value"><?= htmlspecialchars($role_data->name) ?: 'N/A'?></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Email:</span>
                        <span class="detail-value"><?= htmlspecialchars($user_email) ?: 'N/A'?></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Date of Birth:</span>
                        <span class="detail-value"><?= htmlspecialchars($role_data->date_of_birth) ?: 'N/A'?></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Gender:</span>
                        <span class="detail-value"><?= htmlspecialchars($role_data->gender) ?: 'N/A'?></span>
                    </div>
                </div>

                <!-- Student Specific Fields -->
                <?php if ($role == 'student'): ?>
                <div class="detail-section student-fields">
                    <h3 class="section-title">Academic Information</h3>
                    <div class="detail-row">
                        <span class="detail-label">Student Number:</span>
                        <span class="detail-value"><?= htmlspecialchars($role_data->student_no) ?: 'N/A'?></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Registration Number:</span>
                        <span class="detail-value"><?= htmlspecialchars($role_data->reg_no) ?: 'N/A'?></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Course:</span>
                        <span class="detail-value"><?= htmlspecialchars($role_data->course_name) ?: 'N/A'?></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Department:</span>
                        <span class="detail-value"><?= htmlspecialchars($role_data->dpt_name) ?: 'N/A'?></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Nationality:</span>
                        <span class="detail-value"><?= htmlspecialchars($role_data->nationality) ?: 'N/A'?></span>
                    </div>
                </div>


                <!-- Staff Specific Fields -->
                <?php elseif ($role == 'staff'): ?>
                <div class="detail-section staff-fields">
                    <h3 class="section-title">Employment Information</h3>
                    <div class="detail-row">
                        <span class="detail-label">Department:</span>
                        <span class="detail-value"><?= htmlspecialchars($role_data->dpt_name) ?: 'N/A'?></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Rank/Position:</span>
                        <span class="detail-value"><?= htmlspecialchars($role_data->rank) ?: 'N/A'?></span>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <!-- Action Buttons -->
            <div class="profile-actions">
                <button class="btn-edit" data-bs-toggle="modal" data-bs-target="#editProfileModal">
                    <i class='bx bx-edit'></i> Edit Profile
                </button>
                <?php if($role == "student"):?>
                <button class="btn-sm btn-change-password bg-secondary text-light" data-bs-toggle="modal" data-bs-target="#enrollModal">
                    <i class='bx bx-repost'></i> Enroll
                </button>
                <?php endif;?>
                <button class="btn-sm btn-change-password" data-bs-toggle="offcanvas" data-bs-target="#changePasswordOffcanvas">
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
                <form method="POST" action="">
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
                            </select>
                        </div>
                    </div>
                    
                    <?php if ($role == 'student'): ?>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="studentNo" class="form-label">Student Number</label>
                            <input type="text" class="form-control" id="studentNo" name="student_no" 
                                   value="<?= htmlspecialchars($role_data->student_no ?? '') ?>">
                        </div>
                        <div class="col-md-6">
                            <label for="regNo" class="form-label">Registration Number</label>
                            <input type="text" class="form-control" id="regNo" name="reg_no" 
                                   value="<?= htmlspecialchars($role_data->reg_no ?? '') ?>">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label for="course" class="form-label">Course</label>
                            <select class="form-select" id="course" name="course">
                                <option value="" selected disabled>Select Course</option>
                                <?php foreach($courses as $course): ?>
                                    <option value="<?=$course->id; ?>" <?= ($role_data->course == $course->id) ? 'selected' : '';?> > <?= htmlspecialchars($course->course_name);?> </option>
                                <?php endforeach;?>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="nationality" class="form-label">Nationality</label>
                        <input type="text" class="form-control" id="nationality" name="nationality" 
                               value="<?= htmlspecialchars($role_data->nationality ?? '') ?>">
                    </div>
                    <?php endif; ?>

                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label for="department" class="form-label">Department</label>
                            <select class="form-select" id="department" name="department">
                                <option value="" selected disabled>Select Department</option>
                                <?php foreach($dept as $dpt): ?>
                                    <option value="<?=$dpt->id; ?>" <?= ($role_data->department == $dpt->id) ? 'selected' : '';?> > <?= htmlspecialchars($dpt->department_name);?> </option>
                                <?php endforeach;?>
                            </select>
                        </div>
                    </div>
                    
                    <?php if ($role == 'staff'): ?>
                    
                    <div class="mb-3">
                        <label for="rank" class="form-label">Rank/Position</label>
                        <input type="text" class="form-control" id="rank" name="rank" 
                               value="<?= htmlspecialchars($role_data->rank ?? '') ?>">
                    </div>
                    <?php endif; ?>
                    
                    <div class="modal-footer">
                        <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-sm btn-primary" name="update_profile">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- enroll modal -->
<div class="modal fade" id="enrollModal" tabindex="-1" aria-labelledby="editEnrollModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editEnrollModalLabel">Enroll Now</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form method="POST" action="">
                    <input type="hidden" name="user_id" value="<?= $user_id ?>">
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="academic_year" class="form-label">Academic Year</label>
                            <input type="text" class="form-control" id="academic_year" name="academic_year" placeholder="2024 or 2024/2025" required>
                        </div>
                        <div class="col-md-6">
                            <label for="semester" class="form-label">Semester</label>
                            <select name="semester" class="form-select" id="semester" required>
                                <option value="" selected disabled>Select Semester</option>
                                <option value="1">Semester 1</option>
                                <option value="2">Semester 2</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="year_of_study" class="form-label">Year of Study</label>
                        <select name="year_of_study" class="form-select" id="year_of_study" required>
                            <option value="" selected disabled>Select Year of Study</option>
                            <option value="1">Year 1</option>
                            <option value="2">Year 2</option>
                            <option value="3">Year 3</option>
                        </select>
                    </div>
                    
                    <div class="modal-footer">
                        <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-sm btn-primary" name="enroll">Enroll</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- change password offcanvas -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="changePasswordOffcanvas" aria-labelledby="changePasswordOffcanvasLabel">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title" id="changePasswordOffcanvasLabel">Change Password</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <form id="changePasswordForm" method="POST" action="">
            <input type="hidden" name="user_id" value="<?= $user_id ?>">
            
            <div class="mb-3">
                <label for="currentPassword" class="form-label">Current Password</label>
                <input type="password" class="form-control" id="currentPassword" name="current_password" placeholder="••••••••••••••••" required>
                <div class="invalid-feedback">Please enter your current password</div>
            </div>
            
            <div class="mb-3">
                <label for="newPassword" class="form-label">New Password</label>
                <input type="password" class="form-control" id="newPassword" name="new_password" placeholder="••••••••••••••••" required>
                <div class="invalid-feedback">Please enter a new password</div>
                <small class="form-text text-muted">Minimum 8 characters, at least 1 uppercase, 1 lowercase, and 1 number</small>
            </div>
            
            <div class="mb-3">
                <label for="confirmPassword" class="form-label">Confirm New Password</label>
                <input type="password" class="form-control" id="confirmPassword" name="confirm_password" placeholder="••••••••••••••••" required>
                <div class="invalid-feedback">Passwords must match</div>
            </div>
            
            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-sm btn-primary" name="change_password">Update Password</button>
            </div>
        </form>
    </div>
</div>

<?php
ob_end_flush();
include('footer.php');
?>