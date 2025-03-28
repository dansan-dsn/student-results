<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include database configuration
require 'config/db.php';

// Ensure session variables are set
if (!isset($_SESSION['user_role']) || !isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$error = '';
$success = '';
$role = $_SESSION['user_role']; // Store role in variable for easier access

if (isset($_POST['setup_btn'])) {
    // Validate and sanitize input
    $name = trim($_POST['name'] ?? '');
    $date_of_birth = $_POST['date_of_birth'] ?? '';
    $gender = $_POST['gender'] ?? '';
    $userId = $_SESSION['user_id'];
    
    // Basic validation
    if (empty($name) || empty($date_of_birth) || empty($gender)) {
        $error = "Please fill all required fields";
    } else {
        try {
            if ($role == 'student') {
                // Student validation
                $reg_no = trim($_POST['reg_no'] ?? '');
                $nationality = trim($_POST['nationality'] ?? '');
                $student_no = trim($_POST['student_no'] ?? '');
                
                if (empty($reg_no) || empty($nationality) || empty($student_no)) {
                    $error = "Please fill all student fields";
                } else {
                    $stmt = $dbh->prepare("INSERT INTO students (studentId, name, date_of_birth, gender, reg_no, nationality, student_no) VALUES (:studentId, :name, :date_of_birth, :gender, :reg_no, :nationality, :student_no)");
                    $stmt->bindParam(':studentId', $userId);
                    $stmt->bindParam(':name', $name);
                    $stmt->bindParam(':date_of_birth', $date_of_birth);
                    $stmt->bindParam(':gender', $gender);
                    $stmt->bindParam(':reg_no', $reg_no);
                    $stmt->bindParam(':nationality', $nationality);
                    $stmt->bindParam(':student_no', $student_no);
                    
                    if ($stmt->execute()) {
                        $_SESSION['studentId'] = $userId;
                        $_SESSION['success'] = "Student registered successfully!";
                        header('Location: index.php');
                        exit;
                    }
                }
            } elseif ($role == 'staff') {
                // Staff validation
                $rank = trim($_POST['rank'] ?? '');
                
                if (empty($rank)) {
                    $error = "Please fill all staff fields";
                } else {
                    $stmt = $dbh->prepare("INSERT INTO staff (staffId, name, date_of_birth, gender, rank) VALUES (:staffId, :name, :date_of_birth, :gender, :rank)");
                    $stmt->bindParam(':staffId', $userId);
                    $stmt->bindParam(':name', $name);
                    $stmt->bindParam(':date_of_birth', $date_of_birth);
                    $stmt->bindParam(':gender', $gender);
                    $stmt->bindParam(':rank', $rank);
                    
                    if ($stmt->execute()) {
                        $_SESSION['staffId'] = $userId;
                        $_SESSION['success'] = "Staff registered successfully!";
                        header('Location: index.php');
                        exit;
                    }
                }
            }
        } catch (PDOException $e) {
            $error = "Database error: " . $e->getMessage();
        }
    }
}

// Display success message from session if set
if (isset($_SESSION['success'])) {
    $success = $_SESSION['success'];
    unset($_SESSION['success']);
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Setup | Student Results System</title>
    <!-- icon links -->
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <!-- bootstrap links -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- custom css links -->
    <link rel="stylesheet" href="assets/styles/auth.css">
</head>
<body>
<div class="auth-container">
    <div class="auth-card">
        <div class="auth-header">
            <h2>Continue Setup</h2>
            <p>Provide more details about your <span style="font-weight: bold; color: #dbdde1;"><?php echo htmlspecialchars($role); ?></span> account</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($error); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($success); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <form method="POST" class="auth-form">
            <!-- Common Fields -->
            <div class="row">
                <div class="col-md-12">
                    <div class="input-group">
                        <label for="name">Full Name</label>
                        <div class="input-field">
                            <input type="text" class="form-control" id="name" name="name" placeholder="Full Name" 
                                   value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>" required>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="input-group">
                        <label for="date_of_birth">Date of Birth</label>
                        <div class="input-field">
                            <input type="date" class="form-control" id="date_of_birth" name="date_of_birth" 
                                   value="<?php echo isset($_POST['date_of_birth']) ? htmlspecialchars($_POST['date_of_birth']) : ''; ?>" required>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="input-group">
                        <label for="gender">Gender</label>
                        <div class="input-field">
                            <select class="form-control" id="gender" name="gender" required>
                                <option value="male" <?php echo (isset($_POST['gender']) && $_POST['gender'] == 'male') ? 'selected' : ''; ?>>Male</option>
                                <option value="female" <?php echo (isset($_POST['gender']) && $_POST['gender'] == 'female') ? 'selected' : ''; ?>>Female</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Role-Specific Fields -->
            <?php if ($role == 'student'): ?>
                <div class="row">
                    <div class="col-md-6">
                        <div class="input-group">
                            <label for="reg_no">Registration Number</label>
                            <div class="input-field">
                                <input type="text" class="form-control" id="reg_no" name="reg_no" 
                                       value="<?php echo isset($_POST['reg_no']) ? htmlspecialchars($_POST['reg_no']) : ''; ?>" placeholder="Registration number" required>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="input-group">
                            <label for="nationality">Nationality</label>
                            <div class="input-field">
                                <input type="text" class="form-control" id="nationality" name="nationality" 
                                       value="<?php echo isset($_POST['nationality']) ? htmlspecialchars($_POST['nationality']) : ''; ?>" placeholder="Nationality" required>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="input-group">
                            <label for="student_no">Student Number</label>
                            <div class="input-field">
                                <input type="text" class="form-control" id="student_no" name="student_no" 
                                       value="<?php echo isset($_POST['student_no']) ? htmlspecialchars($_POST['student_no']) : ''; ?>" placeholder="Student number" required>
                            </div>
                        </div>
                    </div>
                </div>
            <?php elseif ($role == 'staff'): ?>
                <div class="row">
                    <div class="col-md-12">
                        <div class="input-group">
                            <label for="rank">Rank</label>
                            <div class="input-field">
                                <input type="text" class="form-control" id="rank" name="rank" 
                                       value="<?php echo isset($_POST['rank']) ? htmlspecialchars($_POST['rank']) : ''; ?>" placeholder="Rank" required>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <button type="submit" name="setup_btn" class="auth-btn">Submit</button>
        </form>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>