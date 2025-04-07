<?php
include('config/db.php');

if (isset($_POST['register_btn'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $role = $_POST['role'];

    // Validate password match
    if ($password !== $confirm_password) {
        $error = "Passwords do not match!";
    } else {
        // Check if email already exists
        $stmt = $dbh->prepare("SELECT email FROM users WHERE email = :email");
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $error = "Email already registered!";
        } else {
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Insert new user
            $stmt = $dbh->prepare("INSERT INTO users (email, password, role) VALUES (:email, :password, :role)");
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':password', $hashed_password);
            $stmt->bindParam(':role', $role);

            if ($stmt->execute()) {
                $success = "Successful! Redirecting to login...";
                header("refresh:3;url=login.php");
            } else {
                $error = "Registration failed. Please try again.";
            }
        }
    }
}
?>

    <!doctype html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport"
              content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
        <meta http-equiv="X-UA-Compatible" content="ie=edge">
        <title>Register | Student Results System</title>
        <!-- icon links -->
        <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
        <!-- bootstrap links -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <!-- custom css links -->
        <link rel="stylesheet" href="assets/styles/auth.css">
         <!-- vue link -->
        <script src="https://cdn.jsdelivr.net/npm/vue@3.2.47/dist/vue.global.js"></script>
    </head>
    <body>
    <div class="auth-container" id="app">
        <div class="auth-card">
            <div class="auth-header">
               <!-- <i class='bx bxs-user-plus auth-icon'></i> -->
                <h2>Create Account</h2>
                <p>Register to access the student results</p>
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
            
            <?php if (isset($success)): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>

            <form method="POST" class="auth-form">

                <div class="input-group">
                    <label for="email">Email</label>
                    <div class="input-field">
                        <i class='bx bxs-envelope main-icon'></i>
                        <input type="email" id="email" name="email" placeholder="example@university.edu" required>
                    </div>
                </div>

                <div class="input-group">
                    <label for="password">Password</label>
                    <div class="input-field">
                        <i class='bx bxs-lock main-icon'></i>
                        <input type="password" id="password" name="password" placeholder="••••••••" required>
                        <i class='bx bx-hide show-icon' ></i>
                    </div>
                </div>

                <div class="input-group">
                    <label for="confirm_password">Confirm Password</label>
                    <div class="input-field">
                        <i class='bx bxs-lock main-icon'></i>
                        <input type="password" id="confirm_password" name="confirm_password" placeholder="••••••••" required>
                        <i class='bx bx-hide show-icon' style="display: none;"></i>
                    </div>
                </div>

                <div class="input-group">
                    <label for="role">Account Type</label>
                    <div class="input-field">
                        <i class='bx bxs-group main-icon'></i>
                        <select id="role" name="role" required>
                            <option value="" disabled selected>Select your role</option>
                            <option value="student">Student</option>
                            <option value="staff">Staff</option>
                        </select>
                    </div>
                </div>

                <button type="submit" name="register_btn" class="auth-btn">Register</button>

                <div class="auth-footer">
                    <p>Already have an account? <a href="login">Sign in</a></p>
                </div>
            </form>
        </div>
    </div>

    <script>
document.addEventListener('DOMContentLoaded', function() {
    // Get the first eye icon and both password fields
    const togglePassword = document.querySelector('.show-icon');
    const password = document.getElementById('password');
    const confirm_password = document.getElementById('confirm_password');

    // Only set up the toggle if all elements exist
    if (togglePassword && password && confirm_password) {
        togglePassword.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Determine the new type based on the main password field
            const newType = password.type === 'password' ? 'text' : 'password';
            
            // Toggle both fields' types
            password.type = newType;
            confirm_password.type = newType;
            
            // Toggle all eye icons (both will stay in sync)
            document.querySelectorAll('.show-icon').forEach(icon => {
                icon.classList.toggle('bx-hide');
                icon.classList.toggle('bx-show');
            });
        });
    }
});
</script>
<script src="assets/js/header.js"></script>
</body>
</html>