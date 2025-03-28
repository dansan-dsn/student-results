<?php
try {
    $dbh = new PDO('mysql:host=localhost;dbname=student_results', 'root', '');
    
    // Set the PDO error mode to exception for better error handling
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
} catch (PDOException $e) {
    echo $e->getMessage();
}
?>
