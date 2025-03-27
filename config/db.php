<?php
    try {
        $dbh = new PDO('mysql:host=localhost;dbname=student_results', 'root', '');
    } catch (PDOException $e) {
        echo $e->getMessage();
    }
?>