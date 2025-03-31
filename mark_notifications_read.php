<?php
include('config/db.php');
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

include('notification_functions.php');

$success = markNotificationAsRead($dbh, $_GET['id'], $_SESSION['user_id']);

if ($success) {
    // Get updated unread count
    $unread_count = $dbh->query("SELECT COUNT(*) as count FROM notifications WHERE user_id = {$_SESSION['user_id']} AND is_read = FALSE")->fetch(PDO::FETCH_OBJ)->count;
    echo json_encode(['success' => true, 'unread_count' => $unread_count]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to mark notification as read']);
}