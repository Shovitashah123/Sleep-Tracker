<?php
$rootPath = '../';
require_once '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

$userId = $_SESSION['user_id'];
$recordId = $_GET['id'] ?? null;
$error = '';
$success = '';

if (!$recordId) {
    header("Location: history.php");
    exit;
}

$stmt = $conn->prepare("SELECT * FROM sleep_records WHERE id = ? AND user_id = ?");
$stmt->execute([$recordId, $userId]);
$record = $stmt->fetch();

if (!$record) {
    header("Location: history.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sleep_time  = $_POST['sleep_time'] ?? '';
    $wakeup_time = $_POST['wakeup_time'] ?? '';
    $quality     = $_POST['quality'] ?? '';
    $notes       = $_POST['notes'] ?? '';

    if (empty($sleep_time) || empty($wakeup_time) || empty($quality)) {
        $error = 'Please fill in all required fields.';
    } else {
        $sleepTs  = strtotime($sleep_time);
        $wakeupTs = strtotime($wakeup_time);
        if ($wakeupTs <= $sleepTs) {
            $error = 'Wake-up time must be after sleep time.';
        } else {
            $duration = round(($wakeupTs - $sleepTs) / 3600, 2);
            try {
                $updateStmt = $conn->prepare("UPDATE sleep_records SET sleep_time = ?, wakeup_time = ?, sleep_quality = ?, notes = ?, duration = ? WHERE id = ? AND user_id = ?");
                $updateStmt->execute([$sleep_time, $wakeup_time, $quality, $notes, $duration, $recordId, $userId]);
                $success = 'Record updated successfully!';
                $record = ['id' => $recordId, 'sleep_time' => $sleep_time, 'wakeup_time' => $wakeup_time, 'sleep_quality' => $quality, 'notes' => $notes, 'duration' => $duration];
            } catch (PDOException $e) {
                $error = 'Error updating record: ' . $e->getMessage();
            }
        }
    }
}

$pageTitle = 'Edit Sleep Record';
include '../includes/header.php';
?>
<?php include '../includes/navbar.php'; ?>
<div class="wrapper">
    <div class="page-header">
        <div>
            <div class="page-title">Edit Sleep Record</div>
        </div>
        <a href="../dashboard.php" class="btn btn-outline">← Back to Dashboard</a>
    </div>

    <div class="card" style="max-width:600px">
        <?php if ($success): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-row">
                <div class="form-group">
                    <label>Sleep Time</label>
                    <input type="datetime-local" name="sleep_time" value="<?= str_replace(' ', 'T', $record['sleep_time']) ?>" required>
                </div>
                <div class="form-group">
                    <label>Wake Up Time</label>
                    <input type="datetime-local" name="wakeup_time" value="<?= str_replace(' ', 'T', $record['wakeup_time']) ?>" required>
                </div>
            </div>

            <div class="form-group">
                <label>Sleep Quality</label>
                <select name="quality" required>
                    <option value="">-- Select Quality --</option>
                    <option value="Excellent" <?= $record['sleep_quality'] === 'Excellent' ? 'selected' : '' ?>>Excellent</option>
                    <option value="Good" <?= $record['sleep_quality'] === 'Good' ? 'selected' : '' ?>>Good</option>
                    <option value="Fair" <?= $record['sleep_quality'] === 'Fair' ? 'selected' : '' ?>>Fair</option>
                    <option value="Poor" <?= $record['sleep_quality'] === 'Poor' ? 'selected' : '' ?>>Poor</option>
                </select>
            </div>

            <div class="form-group">
                <label>Notes (Optional)</label>
                <textarea name="notes" placeholder="Any additional notes about your sleep..." rows="4"><?= htmlspecialchars($record['notes'] ?? '') ?></textarea>
            </div>

            <button type="submit" class="btn btn-primary">Update Record</button>
        </form>
    </div>
</div>

</body>
</html>