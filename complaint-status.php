<?php
ob_start();
include('header.php');

// Check if user is lecturer
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'staff') {
    header("Location: login.php");
    exit();
}

$lecturer_id = $_SESSION['user_id'];

try {
    // Fetch complaints assigned to this lecturer
    $stmt = $dbh->prepare("
        SELECT c.*, s.name as student_name, s.reg_no
        FROM complaints c
        LEFT JOIN students s ON c.reg_no = s.studentId
        WHERE c.lecturer = :lecturer_id
        ORDER BY c.created_at DESC
    ");
    $stmt->execute([':lecturer_id' => $lecturer_id]);
    $complaints = $stmt->fetchAll(PDO::FETCH_OBJ);

} catch (PDOException $e) {
    $_SESSION['error'] = "Error fetching complaints: " . $e->getMessage();
    header("Location: complaint-status.php");
    exit();
}

?>

<main class="container">

    <div class="main-view">
        <div class="department-container">
        <div class="department-header">
                <h2 class="department-title">
                    Student Complaints
                </h2>
                <div style="display: flex; gap: 5px;">
                    <!-- <button class="btn btn-sm btn-add-department" data-bs-toggle="modal" data-bs-target="#addRoomModal">
                    <i class='bx bx-plus-circle'></i> Add Room
                    </button> -->
                </div>
            </div>
               <!-- example three -->
             <div>
                <div class="accordion" id="complaintsAccordion">
                    <?php foreach($complaints as $complaint): ?>
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="heading<?= $complaint->id ?>">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" 
                                    data-bs-target="#collapse<?= $complaint->id ?>" aria-expanded="false" 
                                    aria-controls="collapse<?= $complaint->id ?>">
                                <span class="complaint-title"><?= $complaint->reason ?></span>
                                <span class="badge bg-secondary ms-2"><?= $complaint->status ?></span>
                                <span class="text-muted ms-auto"><?= date('M j', strtotime($complaint->created_at)) ?></span>
                            </button>
                        </h2>
                        <div id="collapse<?= $complaint->id ?>" class="accordion-collapse collapse" 
                            aria-labelledby="heading<?= $complaint->id ?>" data-bs-parent="#complaintsAccordion">
                            <div class="accordion-body">
                                <p><strong>From:</strong> <?= $complaint->student_name ?> (<?= $complaint->reg_no ?>)</p>
                                <div class="complaint-content">
                                    <?= nl2br($complaint->details) ?>
                                </div>
                                <!-- <div class="mt-3">
                                    <textarea class="form-control response-field" placeholder="Type your response..."></textarea>
                                    <button class="btn btn-primary mt-2 btn-sm">Send Response</button>
                                </div> -->
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
             </div>
        </div>
    </div>
</main>

<!-- example one -->
            <!-- <div>
                <div class="complaints-container">
                    <div class="complaint-card">
                        <div class="card-header">
                            <h4>Complaint #</h4>
                            <span class="status-badge ?>">
                                
                            </span>
                        </div>
                        <div class="card-body">
                            <p><strong>From:</strong> ></p>
                            <p><strong>Reg No:</strong></p>
                            <p><strong>Reason:</strong> <></p>
                            <div class="complaint-details">
                                <p><></p>
                            </div>
                            <p class="text-muted">Submitted on </p>
                        </div>
                        <div class="card-footer">
                            <button class="btn btn-respond">Respond</button>
                            <button class="btn btn-resolve">Mark as Resolved</button>
                        </div>
                    </div>
                </div>
            </div> -->

<?php
include('footer.php');
?>