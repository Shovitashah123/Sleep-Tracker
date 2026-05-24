<?php
$rootPath = '../';
require_once '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

$userId = $_SESSION['user_id'];
$deleted = $_GET['deleted'] ?? null;

$stmt = $conn->prepare("SELECT * FROM sleep_records WHERE user_id = ? ORDER BY sleep_time DESC");
$stmt->execute([$userId]);
$rows = $stmt->fetchAll();

$pageTitle = 'Sleep History';
include '../includes/header.php';
?>
<?php include '../includes/navbar.php'; ?>
<div class="wrapper">
    <div class="page-header">
        <div>
            <div class="page-title">Sleep History</div>
        </div>
        <a href="../dashboard.php" class="btn btn-outline">← Back to Dashboard</a>
    </div>

    <div class="card">
        <?php if ($deleted): ?>
            <div class="alert alert-success">Record deleted successfully!</div>
        <?php endif; ?>

        <?php if (count($rows) > 0): ?>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Sleep Time</th>
                        <th>Wake Up</th>
                        <th>Duration</th>
                        <th>Quality</th>
                        <th>Notes</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($rows as $row):
                    $quality = $row['sleep_quality'];
                    $badgeClass = strtolower($quality);
                ?>
                    <tr>
                        <td><?= date('M d, Y', strtotime($row['sleep_time'])) ?></td>
                        <td><?= date('h:i A', strtotime($row['sleep_time'])) ?></td>
                        <td><?= date('h:i A', strtotime($row['wakeup_time'])) ?></td>
                        <td><?= $row['duration'] ?> hrs</td>
                        <td><span class="badge badge-<?= $badgeClass ?>"><?= htmlspecialchars($quality) ?></span></td>
                        <td><?= htmlspecialchars(substr($row['notes'] ?? '', 0, 30)) ?></td>
                        <td>
                            <div class="td-actions">
                                <a href="edit.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-edit">Edit</a>
                                <a href="delete.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-delete" onclick="return confirm('Delete this record?')">Delete</a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <div class="empty-state">
            <div class="icon">😴</div>
            <h3>No sleep records yet</h3>
            <p>Start tracking your sleep by <a href="add.php" style="color: var(--accent2);">logging your first night</a></p>
        </div>
        <?php endif; ?>
    </div>
</div>

</body>
</html>