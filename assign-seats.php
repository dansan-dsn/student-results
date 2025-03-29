<?php
include('header.php');
?>

<main class="container">
    <div class="main-view">
        <h2 class="mb-4">Examination Seat Assignment</h2>
        
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
</main>

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