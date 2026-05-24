<?php
$rootPath = '../';
require_once '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

$userId = $_SESSION['user_id'];
$recordId = $_GET['id'] ?? null;

if (!$recordId) {
    header("Location: history.php");
    exit;
}

$stmt = $conn->prepare("SELECT id FROM sleep_records WHERE id = ? AND user_id = ?");
$stmt->execute([$recordId, $userId]);

if (!$stmt->fetch()) {
    header("Location: history.php");
    exit;
}

$deleteStmt = $conn->prepare("DELETE FROM sleep_records WHERE id = ? AND user_id = ?");
$deleteStmt->execute([$recordId, $userId]);

// Redirect back to history
header("Location: history.php?deleted=1");
exit;
?>