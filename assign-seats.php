<?php
ob_start();
include('header.php');

$status = 'pending';

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['add_room'])) {
            // Add new department
            $stmt = $dbh->prepare("INSERT INTO room (room_name, capacity) VALUES (:room_name, :capacity)");
            $stmt->execute([
                ':room_name' => $_POST['room_name'],
                ':capacity' => $_POST['capacity']
            ]);
            header("Location: assign-seats.php?status=success&message=Created successfully!");
            exit();
        } elseif( isset($_POST['delete_room'])){
            $stmt = $dbh->prepare("DELETE FROM room WHERE id = :id");
            $stmt->execute([':id' => $_POST['id']]);
            header('Location: assign-seats.php?status=success&message=Deleted successfully!');
            exit();
        }elseif(isset($_POST['edit_room'])){
            $stmt = $dbh->prepare("UPDATE room SET room_name = :room_name, capacity = :capacity WHERE id = :id ");
            $stmt->execute([
                ':room_name' => $_POST['room_name'],
                ':capacity' => $_POST['capacity'],
                ':id' => $_POST['id']
            ]);
            header('Location: assign-seats.php?status=success&message=Updated successfully!');
            exit();
        }elseif(isset($_POST['assign_room'])){
            $stmt = $dbh->prepare("INSERT INTO room_allocation (course_unit, room, date, start_time) VALUES (:course_unit, :room, :date, :start_time)");
            $success_insersation = $stmt->execute([
                ':course_unit' => $_POST['course_unit'],
                ':room' => $_POST['room'],
                ':date' => $_POST['date'],
                ':start_time' => $_POST['start_time'],
            ]);

            if($success_insersation) {
                $stmt = $dbh->prepare("UPDATE room_allocation SET status = :status WHERE course_unit = :course_unit AND room = :room AND date = :date AND start_time = :start_time");
                $stmt->execute([
                    ':status' => $status,
                    ':course_unit' => $_POST['course_unit'],
                    ':room' => $_POST['room'],
                    ':date' => $_POST['date'],
                    ':start_time' => $_POST['start_time'],
                ]);
            }
            
            header("Location: view-allocations.php?status=success&message=Assigned successfully!");
            exit();
        }

    }catch(PDOException $e) {
        $error_msg = $e->getMessage();
        header("Location: assign-seats.php?status=error&message=$error_msg");
        exit();
    }
}

// fetch room data
try {
    $rooms = $dbh->query("SELECT * FROM room ORDER BY room_name")->fetchAll(PDO::FETCH_OBJ);
}catch (PDOException $e){
    $error_msg = $e->getMessage();
    header("Location: assign-seats.php?status=error&message=$error_msg");
    exit();
}

// fetch room data
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
        <div class="department-header">
                <h2 class="department-title">
                    <i class='bx bx-building-house'></i> Assign Rooms
                </h2>
                <div style="display: flex; gap: 5px;">
                    <button class="btn btn-sm btn-add-department" data-bs-toggle="modal" data-bs-target="#addRoomModal">
                    <i class='bx bx-plus-circle'></i> Add Room
                    </button>
                    <!-- <button class="btn btn-sm btn-add-department" style="background-color: #314173;" data-bs-toggle="modal" data-bs-target="#ViewRoomModal">
                     View Rooms
                    </button> -->
                </div>
            </div>
            
            <div class="row">
                <!-- Exam Selection -->
                    <div class="col-md-4">
                        <form method="POST" action="">
                            <div class="card shadow-sm">
                                <div class="card-header bg-primary text-white">
                                    <h5 class="mb-0">Assignment Controls</h5>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label for="course_unit" class="form-label">Select Exam</label>
                                        <select class="form-select" id="course_unit" name="course_unit">
                                            <option selected disabled>Choose an exam</option>
                                            <?php foreach($course_units as $unit): ?>
                                                <option value="<?=$unit->id; ?>"> <?= htmlspecialchars($unit->name);?> (<?= htmlspecialchars($unit->code);?>)</option>
                                            <?php endforeach;?>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label for="" class="form-label">Choose Room</label>
                                        <select class="form-select" id="" name="room">
                                            <option value="" selected disabled>Select room</option>
                                            <?php foreach($rooms as $room): ?>
                                                <option value="<?=$room->id; ?>"> <?= htmlspecialchars($room->room_name);?> </option>
                                            <?php endforeach;?>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label for="date" class="form-label">Start Date</label>
                                        <input type="date" class="form-control" id="date" name="date" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="start_time" class="form-label">Start Time</label>
                                        <input type="time" class="form-control" id="start_time" name="start_time" required>
                                    </div>
                                    <div class="d-grid gap-2">
                                        <button type="submit" class="btn btn-success" name="assign_room">Assign to Students</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
            
                <!-- Room Layout -->
                <div class="col-md-8">
                    <div class="card shadow-sm">
                        <div class="card-header bg-secondary text-white">
                            <h5 class="mb-0">Room Data Layout</h5>
                        </div>
                        <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-dark table-hover table-striped">
                                <thead>
                                    <tr>
                                        <th scope="col">No.</th>
                                        <th scope="col">Room Name</th>
                                        <th scope="col">Room Capacity</th>
                                        <th scope="col">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (count($rooms) > 0): ?>
                                        <?php foreach ($rooms as $index => $room): ?>
                                        <tr>
                                            <th scope="row"><?= $index + 1 ?></th>
                                            <td><?= htmlspecialchars($room->room_name) ?></td>
                                            <td><?= htmlspecialchars($room->capacity) ?></td>
                                            <td>
                                                <button class="btn btn-sm btn-primary me-1 edit-room" 
                                                        data-id="<?= $room->id ?>" 
                                                        data-name="<?= htmlspecialchars($room->room_name) ?>" 
                                                        data-capacity="<?= htmlspecialchars($room->capacity) ?>" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#editRoomModal">
                                                    <i class='bx bx-edit'></i>
                                                </button>
                                                <button class="btn btn-sm btn-danger delete-room" 
                                                        data-id="<?= $room->id ?>" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#deleteRoomModal">
                                                    <i class='bx bx-trash'></i>
                                                </button>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="4" class="text-center">No Room found</td>
                                            </tr>
                                        <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<!-- Add Department Modal -->
<div class="modal fade" id="addRoomModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Room</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="">
                <div class="modal-body">
                    <div class="col-md-12 mb-3">
                        <label for="roomName" class="form-label">Room Name</label>
                        <input type="text" class="form-control" id="roomName" name="room_name" placeholder="Name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="roomCapacity" class="form-label">Capacity</label>
                        <select class="form-select" id="roomCapacity" name="capacity">
                            <option value="" selected disabled>Select Room Capacity</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                            <option value="150">150</option>
                            <option value="200">200</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" name="add_room" class="btn btn-primary">Save Room</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- edit room -->
<div class="modal fade" id="editRoomModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Department</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="">
                <div class="modal-body">
                    <input type="hidden" id="editRoomId" name="id">
                    
                    <div class="col-md-12 mb-3">
                        <label for="editRoomName" class="form-label">Room Name</label>
                        <input type="text" class="form-control" id="editRoomName" name="room_name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="editCapacity" class="form-label">Room Capacity</label>
                        <select class="form-select" id="editCapacity" name="capacity">
                            <option value="" selected disabled>Select Room Capacity</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                            <option value="150">150</option>
                            <option value="200">200</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" name="edit_room" class="btn btn-sm btn-primary">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- delete room -->
<div class="modal fade" id="deleteRoomModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="">
                <div class="modal-body">
                    <p>Are you sure you want to delete this Room?</p>
                    <input type="hidden" id="deleteRoomId" name="id">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="delete_room" class="btn btn-sm btn-danger deleteBtn">Delete</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Assignment Modal -->
<div class="modal fade" id="assignmentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Assign Student to Seat</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Assigning to seat: <strong id="selectedSeatNumber">A1</strong></p>
                <div class="mb-3">
                    <label for="studentSelect" class="form-label">Select Student</label>
                    <select class="form-select" id="studentSelect">
                        <!-- Students will be populated here -->
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="confirmAssignment">Assign</button>
            </div>
        </div>
    </div>
</div>

<?php
include('footer.php');
?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Edit room - populate modal with data
    document.querySelectorAll('.edit-room').forEach(button => {
        button.addEventListener('click', function() {
            document.getElementById('editRoomId').value = this.getAttribute('data-id');
            document.getElementById('editRoomName').value = this.getAttribute('data-name');
            document.getElementById('editCapacity').value = this.getAttribute('data-capacity');
        });
    });

    // Delete room - set the ID
    document.querySelectorAll('.delete-room').forEach(button => {
        button.addEventListener('click', function() {
            document.getElementById('deleteRoomId').value = this.getAttribute('data-id');
        });
    });
});
</script>