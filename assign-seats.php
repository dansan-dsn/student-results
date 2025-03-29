<?php
ob_start();
include('header.php');

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

?>

<main class="container">
    <div class="main-view">
        <div class="department-container">
        <div class="department-header">
                <h2 class="department-title">
                    <i class='bx bx-building-house'></i> Assign Seats
                </h2>
                <div style="display: flex; gap: 5px;">
                    <button class="btn btn-sm btn-add-department" data-bs-toggle="modal" data-bs-target="#addRoomModal">
                    <i class='bx bx-plus-circle'></i> Add Room
                    </button>
                    <button class="btn btn-sm btn-add-department" style="background-color: #314173;" data-bs-toggle="modal" data-bs-target="#ViewRoomModal">
                     View Rooms
                    </button>
                </div>
            </div>
            
            <div class="row">
                <!-- Department/Exam Selection -->
                <div class="col-md-4">
                    <div class="card shadow-sm">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">Assignment Controls</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label for="examSelect" class="form-label">Select Exam</label>
                                <select class="form-select" id="examSelect">
                                    <option selected disabled>Choose an exam</option>
                                    <option value="1">CS101 - Final Exam</option>
                                    <option value="2">MATH201 - Midterm</option>
                                    <option value="3">PHY301 - Practical</option>
                                </select>
                            </div>
            
                            <div class="mb-3">
                                <label for="roomSelect" class="form-label">Select Room</label>
                                <select class="form-select" id="roomSelect">
                                    <option selected disabled>Choose a room</option>
                                    <option value="1">Grand Hall (Capacity: 120)</option>
                                    <option value="2">Science Block A (Capacity: 80)</option>
                                    <option value="3">Lecture Theater 3 (Capacity: 60)</option>
                                </select>
                            </div>
            
                            <div class="d-grid gap-2">
                                <button class="btn btn-primary" id="loadRoomBtn">Load Room Layout</button>
                                <button class="btn btn-success" id="autoAssignBtn">Auto-Assign Students</button>
                                <button class="btn btn-danger" id="clearAssignmentsBtn">Clear Assignments</button>
                            </div>
                        </div>
                    </div>
            
                    <div class="card shadow-sm mt-3">
                        <div class="card-header bg-info text-white">
                            <h5 class="mb-0">Unassigned Students</h5>
                        </div>
                        <div class="card-body" id="unassignedStudents">
                            <div class="list-group">
                                <!-- This will be populated dynamically -->
                                <div class="list-group-item">No students loaded</div>
                            </div>
                        </div>
                    </div>
                </div>
            
                <!-- Room Layout -->
                <div class="col-md-8">
                    <div class="card shadow-sm">
                        <div class="card-header bg-secondary text-white">
                            <h5 class="mb-0">Room Layout</h5>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-info" id="roomInstructions">
                                Select an exam and room to begin assignments
                            </div>
            
                            <!-- Visual Room Layout -->
                            <div class="room-layout-container" id="roomLayout" style="display: none;">
                                <div class="room-header text-center mb-3">
                                    <h4 id="roomTitle">Grand Hall</h4>
                                    <div class="d-flex justify-content-between">
                                        <span class="badge bg-primary">Front</span>
                                        <span class="badge bg-success">Capacity: <span id="roomCapacity">120</span></span>
                                    </div>
                                </div>
            
                                <!-- Seat Grid -->
                                <div class="seat-grid" id="seatGrid">
                                    <!-- Seats will be generated here dynamically -->
                                    <div class="text-center py-5 text-muted">No room selected</div>
                                </div>
            
                                <!-- Legend -->
                                <div class="mt-3">
                                    <div class="d-flex flex-wrap gap-2">
                                        <span class="badge bg-success">Available</span>
                                        <span class="badge bg-primary">Assigned</span>
                                        <span class="badge bg-secondary">Reserved</span>
                                        <span class="badge bg-danger">Disabled</span>
                                    </div>
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

<!-- View Room -->
<div class="modal fade" id="ViewRoomModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Room Data View</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                 <!-- Room Table -->
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