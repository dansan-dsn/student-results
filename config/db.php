<?php
try {
    // Create a new PDO instance with exception handling mode
    $dbh = new PDO('mysql:host=localhost;dbname=student_results', 'root', '');
    
    // Set the PDO error mode to exception for better error handling
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // echo "Database connection successful!";
} catch (PDOException $e) {
    // Display a user-friendly message
    // echo "Sorry, there was an error connecting to the database. Please try again later.";
    
    // Log the detailed error message to a log file (you can set the log file path)
    // error_log("Database connection error: " . $e->getMessage(), 3, "error_log.txt");
    echo $e->getMessage();
}
?>
