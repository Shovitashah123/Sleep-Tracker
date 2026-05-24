<?php
$rootPath = '../';
require_once '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

$userId = $_SESSION['user_id'];
$success = '';
$error = '';

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
                $stmt = $conn->prepare("INSERT INTO sleep_records (user_id, sleep_time, wakeup_time, sleep_quality, notes, duration) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$userId, $sleep_time, $wakeup_time, $quality, $notes, $duration]);
                $success = 'Sleep record added successfully!';
            } catch (PDOException $e) {
                $error = 'Error adding record: ' . $e->getMessage();
            }
        }
    }
}

$pageTitle = 'Log Sleep';
include '../includes/header.php';
?>
<?php include '../includes/navbar.php'; ?>
<div class="wrapper">
    <div class="page-header">
        <div>
            <div class="page-title">Log Tonight's Sleep</div>
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
                    <input type="datetime-local" name="sleep_time" required>
                </div>
                <div class="form-group">
                    <label>Wake Up Time</label>
                    <input type="datetime-local" name="wakeup_time" required>
                </div>
            </div>

            <div class="form-group">
                <label>Sleep Quality</label>
                <select name="quality" required>
                    <option value="">-- Select Quality --</option>
                    <option value="Excellent">Excellent</option>
                    <option value="Good">Good</option>
                    <option value="Fair">Fair</option>
                    <option value="Poor">Poor</option>
                </select>
            </div>

            <div class="form-group">
                <label>Notes (Optional)</label>
                <textarea name="notes" placeholder="Any additional notes about your sleep..." rows="4"></textarea>
            </div>

            <button type="submit" class="btn btn-primary">Log Sleep</button>
        </form>
    </div>
</div>

</body>
</html>