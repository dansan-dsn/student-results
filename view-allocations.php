<?php
ob_start();
include('header.php');

// Mark all related notifications as read when landing on this page
if (isset($_GET['mark_all_read'])) {
    markAllRelatedNotificationsAsRead($dbh, $_SESSION['user_id'], 'view-allocations.php');
    
    // Remove the parameter from URL
    header("Location: view-allocations.php");
    exit();
}

// Rest of your code...

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    try{
        if(isset($_POST['edit_allocation'])){
            // Update allocation
            $stmt = $dbh->prepare("UPDATE room_allocation SET course_unit_id = :course_unit_id, room_id = :room_id, date = :date, start_time = :start_time WHERE id = :id");
            $stmt->execute([
                ':id' => $_POST['id'],
                ':course_unit_id' => $_POST['course_unit_id'],
                ':room_id' => $_POST['room_id'],
                ':date' => $_POST['date'],
                ':start_time' => $_POST['start_time'],
            ]);
            header("Location: view-allocations.php?status=success&message=Updated successfully");
            exit();

        } elseif (isset($_POST['delete_allocation'])) {
            // Delete allocation
            $stmt = $dbh->prepare("DELETE FROM room_allocation WHERE id = :id");
            $stmt->execute([':id' => $_POST['id']]);
            header("Location: view-allocations.php?status=success&message=Deleted successfully");
            exit();
        }
    }catch(PDOException $e){
        header("Location: view-allocations.php?status=error&message=Database error: " . $e->getMessage());
    }
}

// fetch allocations
try{
    $allocations = $dbh->query("
        SELECT ra.*,
               r.room_name,
               cu.name AS course_name,
               cu.code AS course_code
        FROM room_allocation ra
        LEFT JOIN room r on ra.room_id = r.id
        LEFT JOIN course_unit cu ON ra.course_unit_id = cu.id
        ORDER BY ra.date DESC
    ")->fetchAll(PDO::FETCH_OBJ);
}catch(PDOException $e){
    $error_msg = $e->getMessage();
    header("Location: view-allocations.php?status=error&message=$error_msg");
    exit();
}

// fetch room data
try {
    $rooms = $dbh->query("SELECT * FROM room ORDER BY room_name")->fetchAll(PDO::FETCH_OBJ);
}catch (PDOException $e){
    $error_msg = $e->getMessage();
    header("Location: assign-seats.php?status=error&message=$error_msg");
    exit();
}

// fetch course units / examinations
try {
    $course_units = $dbh->query("SELECT * FROM course_unit ORDER BY name")->fetchAll(PDO::FETCH_OBJ);
}catch (PDOException $e){
    $error_msg = $e->getMessage();
    header("Location: assign-seats.php?status=error&message=$error_msg");
    exit();
}

?>

<main class="container">
    <div class="main-view">
        <div class="department-container">
            <!-- Enhanced Department Header -->
            <div class="department-header glassmorphism-header mb-4">
                <h2 class="department-title glow-text">
                    <i class='bx bx-building-house'></i> Examination Room Allocations
                </h2>
                <div class="header-actions">
                    <div class="search-box">
                        <i class='bx bx-search'></i>
                        <input type="text" placeholder="Search allocations...">
                    </div>
                </div>
            </div>

            <!-- Modern Card-Style Table Replacement -->
            <div class="allocation-cards">
                <?php if (count($allocations) > 0): ?>
                    <?php foreach ($allocations as $index => $allocation): ?>
                    <div class="allocation-card <?= $allocation->status === 'complete' ? 'completed' : '' ?>">
                        <div class="card-header">
                            <span class="card-index"><?= $index + 1 ?></span>
                            <h3 class="card-title">
                                <?= htmlspecialchars($allocation->course_name) ?> 
                                <span class="course-code">(<?= htmlspecialchars($allocation->course_code) ?>)</span>
                            </h3>
                            <span class="status-badge <?= $allocation->status === 'complete' ? 'completed' : 'pending' ?>">
                                <?= htmlspecialchars(ucfirst($allocation->status)) ?>
                            </span>
                        </div>
                        
                        <div class="card-body">
                            <div class="card-detail">
                                <i class='bx bx-map'></i>
                                <span><?= htmlspecialchars($allocation->room_name) ?></span>
                            </div>
                            
                            <div class="card-detail">
                                <i class='bx bx-calendar'></i>
                                <span><?= htmlspecialchars($allocation->date) ?></span>
                            </div>
                            
                            <div class="card-detail time-slot">
                                <i class='bx bx-time-five'></i>
                                <span class="highlight-time"><?= htmlspecialchars($allocation->start_time) ?></span>
                            </div>
                        </div>
                        
                        <div class="card-actions">
                            <button class="btn-action edit-allocation" 
                                    data-id="<?= $allocation->id ?>" 
                                    data-exam="<?= $allocation->course_unit_id ?>" 
                                    data-room="<?= $allocation->room_id ?>"
                                    data-date="<?= htmlspecialchars($allocation->date) ?>" 
                                    data-start-time="<?= htmlspecialchars($allocation->start_time) ?>"
                                    data-bs-toggle="modal" 
                                    data-bs-target="#editAllocationModal">
                                <i class='bx bx-edit'></i>
                                <span>Edit</span>
                            </button>
                            <button class="btn-action delete-allocation" 
                                    data-id="<?= $allocation->id ?>" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#deleteAllocationModal">
                                <i class='bx bx-trash'></i>
                                <span>Delete</span>
                            </button>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="no-allocations">
                        <i class='bx bx-folder-open'></i>
                        <p>No allocations found</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</main>

<!-- Edit assignment Modal -->
<div class="modal fade" id="editAllocationModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Allocations</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="">
                <div class="modal-body">
                    <input type="hidden" id="editAllocationId" name="id">
                    
                    <div class="mb-3">
                        <label for="editAllocationExam" class="form-label">Examination</label>
                        <select class="form-select" id="editAllocationExam" name="course_unit_id" required>
                            <option selected disabled>Choose an exam</option>
                            <?php foreach($course_units as $unit): ?>
                                <option value="<?=$unit->id; ?>"> <?= htmlspecialchars($unit->name);?> (<?= htmlspecialchars($unit->code);?>)</option>
                            <?php endforeach;?>
                        </select>
                    </div>

                    <div class="col-md-12 mb-3">
                        <label for="editAllocationRoom" class="form-label">Room</label>
                        <select class="form-select" id="editAllocationRoom" name="room_id" required>
                            <option value="" selected disabled>Select room</option>
                            <?php foreach($rooms as $room): ?>
                                <option value="<?=$room->id; ?>"> <?= htmlspecialchars($room->room_name);?> </option>
                            <?php endforeach;?>
                        </select>
                    </div>

                    <div class="col-md-12 mb-3">
                        <label for="editAllocationDate" class="form-label">Date</label>
                        <input type="date" class="form-control" id="editAllocationDate" name="date" required>
                    </div>

                    <div class="col-md-12 mb-3">
                        <label for="editAllocationTime" class="form-label">Time</label>
                        <input type="time" class="form-control" id="editAllocationTime" name="start_time" required>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" name="edit_allocation" class="btn btn-sm btn-primary">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteAllocationModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="">
                <div class="modal-body">
                    <p>Are you sure you want to delete this allocation?</p>
                    <input type="hidden" id="deleteAllocationId" name="id">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="delete_allocation" class="btn btn-sm btn-danger deleteBtn">Delete</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
include('footer.php');
?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Edit course - populate modal with data
    document.querySelectorAll('.edit-allocation').forEach(button => {
        button.addEventListener('click', function() {
            document.getElementById('editAllocationId').value = this.getAttribute('data-id');
            document.getElementById('editAllocationExam').value = this.getAttribute('data-exam');
            document.getElementById('editAllocationRoom').value = this.getAttribute('data-room');
            document.getElementById('editAllocationDate').value = this.getAttribute('data-date');
            document.getElementById('editAllocationTime').value = this.getAttribute('data-start-time');
        });
    });

    // Delete course - set the ID
    document.querySelectorAll('.delete-allocation').forEach(button => {
        button.addEventListener('click', function() {
            document.getElementById('deleteAllocationId').value = this.getAttribute('data-id');
        });
    });
});
</script>