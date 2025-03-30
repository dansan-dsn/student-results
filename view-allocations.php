<?php
ob_start();
include('header.php');

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
        JOIN room r on ra.room_id = r.id
        JOIN course_unit cu ON ra.course_unit_id = cu.id
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
            <!-- Department Header with Add Button -->
            <div class="department-header">
                <h2 class="department-title">
                    <i class='bx bx-building-house'></i> Examination Room Allocations
                </h2>
            </div>

             <!-- Department Table -->
             <div class="table-responsive">
                <table class="table table-dark table-hover table-striped">
                    <thead>
                        <tr>
                            <th scope="col">No.</th>
                            <th scope="col">Examination</th>
                            <th scope="col">Room</th>
                            <th scope="col">Date and Time</th>
                            <th scope="col">status</th>
                            <th scope="col">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($allocations) > 0): ?>
                            <?php foreach ($allocations as $index => $allocation): ?>
                            <tr>
                                <th scope="row"><?= $index + 1 ?></th>
                                <td><?= htmlspecialchars($allocation->course_name) ?></td>
                                <td><?= htmlspecialchars($allocation->room_name) ?></td>
                                <td><?= htmlspecialchars($allocation->date) ?> <span style="color: red; font-weight: bold">[ <?= htmlspecialchars($allocation->start_time) ?> ]</span></td>
                                <td>
                                    <?php
                                        $badgeClass = "";
                                        switch($allocation->status) {
                                            case 'complete':
                                                $badgeClass = 'text-bg-success'; // Blue for accepted
                                                break;
                                            default:
                                                $badgeClass = 'text-bg-primary'; // Grey for default
                                                break;
                                        }
                                    ?>
                                    <span class="badge <?= $badgeClass?> " style='font-size: 12px'><?= htmlspecialchars($allocation->status); ?></span></br>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-primary me-1 edit-allocation" 
                                            data-id="<?= $allocation->id ?>" 
                                            data-exam="<?= $allocation->course_unit_id ?>" 
                                            data-room="<?= $allocation->room_id ?>"
                                            data-date="<?= htmlspecialchars($allocation->date) ?>" 
                                            data-start-time="<?= htmlspecialchars($allocation->start_time) ?>"
                                            data-bs-toggle="modal" 
                                            data-bs-target="#editAllocationModal">
                                        <i class='bx bx-edit'></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger delete-allocation" 
                                            data-id="<?= $allocation->id ?>" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#deleteAllocationModal">
                                        <i class='bx bx-trash'></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" class="text-center">No Allocation found</td>
                                </tr>
                            <?php endif; ?>
                    </tbody>
                </table>
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