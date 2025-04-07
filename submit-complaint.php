<?php
ob_start();
include('header.php');

if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] !== 'student')) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['user_role'];
$reg_no = null;

try{
    if($role == "student") {
        $stmt = $dbh->prepare("SELECT reg_no FROM students WHERE studentId = :studentId");
        $stmt->execute([
            ':studentId' => $user_id
        ]);

        $student_data = $stmt->fetch(PDO::FETCH_OBJ);
        if($student_data) {
            $reg_no = $student_data->reg_no;
        }
    }
}catch(PDOException $e) {
    $error_msg = urlencode($e->getMessage());  // urlencode for URL safety
    header("Location: submit-complaint.php?error&message=$error_msg");
    exit();
}

try{
    $staffs = $dbh->query("SELECT staffId, name FROM staff")->fetchAll(PDO::FETCH_OBJ);
}catch(PDOException $e){
    $error_msg = urlencode($e->getMessage());  // urlencode for URL safety
    header("Location: submit-complaint.php?error&message=$error_msg");
    exit();
}

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    try{
        if(isset($_POST['complaint_btn'])){
            $stmt = $dbh->prepare("INSERT INTO complaints (reg_no, reason, details, lecturer, status) VALUES (:reg_no, :reason, :details, :lecturer, :status)");
            $stmt->execute([
                ':reg_no' => $_POST['id'],
                ':reason' => $_POST['reason'],
                ':details' => $_POST['details'],
                ':lecturer' => $_POST['lecturer'],
                ':status' =>  0
            ]);
            header("Location: submit-complaint.php?status=success&message=Sent successfully");
            exit();
        }

    }catch(PDOException $e) {
        $error_msg = urlencode($e->getMessage());  // urlencode for URL safety
        header("Location: submit-complaint.php?error&message=$error_msg");
        exit();
    }
}

?>

<main class="container">
    <div class="main-view">
        <div class="department-container">
            <div class="complaint-form">
                <h3>Student Complaint Form</h3>
                <form action="" method="POST">
                    <input type="hidden" value="<?= htmlspecialchars($user_id) ?>" name="id"/>
                    <div class="form-group mb-3">
                        <label style="color: #495057" for="reg_no">Registration Number:</label>
                        <input type="text" id="reg_no" value="<?= htmlspecialchars($reg_no) ?>" class="form-control" readonly/>
                    </div>
                    
                    <div class="form-group mb-3">
                        <label for="lecturer" style="color: #495057">Lecturer:</label>
                        <select id="lecturer" name="lecturer" required class="form-control">
                            <option value="" selected disabled>To whom</option>
                            <?php foreach($staffs as $staff):?>
                                <option value="<?= htmlspecialchars($staff->staffId) ?>" > <?= htmlspecialchars($staff->name); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group mb-3">
                        <label for="reason" style="color: #495057">Reason for Complaint:</label>
                        <select id="reason" name="reason" required class="form-control">
                            <option value="" selected disabled>Choose reason</option>
                            <option value="Faulty Marks">Faulty Marks</option>
                            <option value="Missing Marks">Missing Marks</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="details" style="color: #495057">Complaint Details:</label>
                        <textarea id="details" name="details" rows="4" required class="form-control"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary mt-2 btn-sm" name="complaint_btn">Submit Complaint</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</main>

<?php
include('footer.php');
?>