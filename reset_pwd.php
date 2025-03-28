<?php
include('config/db.php');

if(isset($_POST['reset_btn'])){

    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];

    try{
        // Check if email exists
        $stmt = $dbh->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        if($stmt->rowCount() > 0){
            $user = $stmt->fetch(PDO::FETCH_OBJ);

            // hash new password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // update the password
            $stmt = $dbh->prepare("UPDATE users SET password = :password WHERE email = :email");
            $stmt->bindParam(':password', $hashed_password);
            $stmt->bindParam(':email', $email);

            if($stmt->execute()){
                $success = "Successfully Reset!";
                header("refresh:3;url=login.php");
            } else {
                $error = "Failed to reset!";
            }
        } else {
            $error = "Email not registered!";
        }

    } catch(PDOException $e) {
        echo $e->getMessage();
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
            <h2>Reset Account</h2>
            <p>We can still reset your password</p>
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
                <label for="email">Your Email</label>
                <div class="input-field">
                    <i class='bx bxs-envelope'></i>
                    <input type="email" id="email" name="email" placeholder="example@university.edu" required>
                </div>
            </div>

            <div class="input-group">
                <label for="password">New Password</label>
                <div class="input-field">
                    <i class='bx bxs-lock-alt'></i>
                    <input type="password" id="password" name="password" placeholder="••••••••" required>
                    <i class='bx bxs-hide password-toggle'></i>
                </div>
            </div>

            <button type="submit" name="reset_btn" class="auth-btn">Reset Password</button>

            <div class="auth-footer">
                <p> Without an account? <a href="login.php">Login</a></p>
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
