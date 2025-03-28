<?php
session_start();
include('config/db.php');

if(isset($_POST['login_btn'])){
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];

    // Check if email exists
    $stmt = $dbh->prepare("SELECT * FROM users WHERE email = :email");
    $stmt->bindParam(':email', $email);
    $stmt->execute();

    if($stmt->rowCount() > 0){
        $user = $stmt->fetch(PDO::FETCH_OBJ);

        // Verify password
        if(password_verify($password, $user->password)){
            // Start session and set user data
            $_SESSION['user_id'] = $user->id;
            $_SESSION['user_role'] = $user->role;

            if($user->role == 'student') {
                $stmt = $dbh->prepare("SELECT * FROM students WHERE studentId = :studentId AND student_no IS NOT NULL AND reg_no IS NOT NULL");
                $stmt->bindParam(':studentId', $user->id);
                $stmt->execute();

                if($stmt->rowCount() == 0){
                    $_SESSION['role'] = 'student';
                    header('Location: setup.php');
                    exit();
                }
            }else if ($user->role == 'staff') {
                $stmt = $dbh->prepare("SELECT * FROM staff WHERE staffId = :staffId AND rank IS NOT NULL");
                $stmt->bindParam(':staffId', $user->id);
                $stmt->execute();

                if($stmt->rowCount() == 0){
                    $_SESSION['role'] = 'staff';
                    header('Location: setup.php');
                    exit();
                }
            }
            header("Location: index.php");
            exit();
           
        } else {
            $error = "Invalid password!";
        }
    } else {
        $error = "Email not registered!";
    }
}
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Login | Student Results System</title>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/styles/auth.css">
     <!-- vue link -->
     <script src="https://cdn.jsdelivr.net/npm/vue@3.2.47/dist/vue.global.js"></script>
</head>
<body>
<div class="auth-container" id="app">
    <div class="auth-card">
        <div class="auth-header">
            <h2>Enter your Account</h2>
            <p>Welcome Back</p>
        </div>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show" v-if="isOpen">
                <?php echo $error; ?> 
                <span style="font-size: 1.5rem; position: absolute; top: 12px; right: 10px; cursor: pointer; "
                    onmouseover="this.style.color='red'" 
                    onmouseout="this.style.color=''"
                    v-if="isOpen"
                    @click="closeAlert()"
                >
                    <i class='bx bx-x'></i>
                </span>
            </div>
        <?php endif; ?> 

        <form method="POST" class="auth-form">
            <div class="input-group">
                <label for="email">Email</label>
                <div class="input-field">
                    <i class='bx bxs-envelope'></i>
                    <input type="email" id="email" name="email" placeholder="example@university.edu" required>
                </div>
            </div>

            <div class="input-group">
                <label for="password">Password</label>
                <div class="input-field">
                    <i class='bx bxs-lock-alt'></i>
                    <input type="password" id="password" name="password" placeholder="••••••••" required>
                    <i class='bx bxs-hide password-toggle'></i>
                </div>
            </div>

            <button type="submit" name="login_btn" class="auth-btn">Login</button>

            <div class="auth-footer">
                <p>Without an account? <a href="register.php">Register</a></p>
            </div>
        </form>
    </div>
</div>

<script src="assets/js/header.js">
    // Password toggle functionality
    document.querySelector('.password-toggle').addEventListener('click', function() {
        const passwordInput = document.getElementById('password');
        const icon = this;

        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            icon.classList.replace('bxs-hide', 'bxs-show');
        } else {
            passwordInput.type = 'password';
            icon.classList.replace('bxs-show', 'bxs-hide');
        }
    });
</script>
</body>
</html>
