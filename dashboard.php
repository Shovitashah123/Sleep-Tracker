<?php
$rootPath = '';
require_once 'config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: auth/login.php");
    exit;
}

$userId = $_SESSION['user_id'];
$userName = $_SESSION['user_name'];

$stmt = $conn->prepare("SELECT COUNT(*) as cnt FROM sleep_records WHERE user_id = ?");
$stmt->execute([$userId]);
$totalRecords = $stmt->fetch()['cnt'];

$stmt = $conn->prepare("SELECT AVG(duration) as avg_dur FROM sleep_records WHERE user_id = ?");
$stmt->execute([$userId]);
$avgDuration = round($stmt->fetch()['avg_dur'] ?? 0, 1);

$stmt = $conn->prepare("SELECT AVG(duration) as avg_dur FROM sleep_records WHERE user_id = ? AND sleep_time >= DATE_SUB(NOW(), INTERVAL 7 DAY)");
$stmt->execute([$userId]);
$weekAvg = round($stmt->fetch()['avg_dur'] ?? 0, 1);

$stmt = $conn->prepare("SELECT sleep_quality, COUNT(*) as cnt FROM sleep_records WHERE user_id = ? GROUP BY sleep_quality ORDER BY cnt DESC LIMIT 1");
$stmt->execute([$userId]);
$qualRow = $stmt->fetch();
$topQuality = $qualRow['sleep_quality'] ?? 'N/A';

$recentStmt = $conn->prepare("SELECT * FROM sleep_records WHERE user_id = ? ORDER BY sleep_time DESC LIMIT 5");
$recentStmt->execute([$userId]);
$recentRows = $recentStmt->fetchAll();

$pageTitle = 'Dashboard';
include 'includes/header.php';
?>
<?php include 'includes/navbar.php'; ?>
<div class="wrapper">
    <div class="welcome-banner">
        <div class="welcome-text">
            <h2>Good <?= date('H') < 12 ? 'morning' : (date('H') < 18 ? 'afternoon' : 'evening') ?>, <?= htmlspecialchars($userName) ?> 👋</h2>
            <p>Here's a summary of your sleep habits</p>
        </div>
        <a href="sleep/add.php" class="btn btn-outline">+ Log Tonight's Sleep</a>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-label">Total Records</div>
            <div class="stat-value"><?= $totalRecords ?></div>
            <div class="stat-sub">All time</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Avg Sleep Duration</div>
            <div class="stat-value"><?= $avgDuration ?><small style="font-size:1rem;color:var(--muted)">h</small></div>
            <div class="stat-sub">All time average</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">7-Day Average</div>
            <div class="stat-value"><?= $weekAvg ?><small style="font-size:1rem;color:var(--muted)">h</small></div>
            <div class="stat-sub">Last 7 days</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Top Quality</div>
            <div class="stat-value" style="font-size:1.4rem"><?= $topQuality ?></div>
            <div class="stat-sub">Most common rating</div>
        </div>
    </div>

    <div class="card">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.3rem">
            <div class="card-title" style="margin:0">Recent Sleep Records</div>
            <a href="sleep/history.php" class="btn btn-outline btn-sm">View All</a>
        </div>

        <?php if (count($recentRows) > 0): ?>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Sleep Time</th>
                        <th>Wake Up</th>
                        <th>Duration</th>
                        <th>Quality</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($recentRows as $row):
                    $quality = $row['sleep_quality'];
                    $badgeClass = strtolower($quality);
                ?>
                    <tr>
                        <td><?= date('M d, Y', strtotime($row['sleep_time'])) ?></td>
                        <td><?= date('h:i A', strtotime($row['sleep_time'])) ?></td>
                        <td><?= date('h:i A', strtotime($row['wakeup_time'])) ?></td>
                        <td><?= $row['duration'] ?> hrs</td>
                        <td><span class="badge badge-<?= $badgeClass ?>"><?= $quality ?></span></td>
                        <td>
                            <div class="td-actions">
                                <a href="sleep/edit.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-edit">Edit</a>
                                <a href="sleep/delete.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-delete" onclick="return confirm('Delete this record?')">Delete</a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <div class="empty-state">
            <div class="icon">🌙</div>
            <h3>No sleep records yet</h3>
            <p>Start by logging your first sleep entry</p>
            <br>
            <a href="sleep/add.php" class="btn btn-outline">Add Sleep Record</a>
        </div>
        <?php endif; ?>
    </div>
</div>
</body>
</html>