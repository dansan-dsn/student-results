<?php
function markNotificationAsRead($dbh, $notification_id, $user_id) {
    try {
        $stmt = $dbh->prepare("UPDATE notifications SET is_read = TRUE WHERE id = :id AND user_id = :user_id");
        return $stmt->execute([':id' => $notification_id, ':user_id' => $user_id]);
    } catch(PDOException $e) {
        error_log("Error marking notification as read: " . $e->getMessage());
        return false;
    }
}

function markAllRelatedNotificationsAsRead($dbh, $user_id, $page_url) {
    try {
        $stmt = $dbh->prepare("UPDATE notifications SET is_read = TRUE WHERE user_id = :user_id AND related_url = :url");
        return $stmt->execute([':user_id' => $user_id, ':url' => $page_url]);
    } catch(PDOException $e) {
        error_log("Error marking related notifications as read: " . $e->getMessage());
        return false;
    }
}