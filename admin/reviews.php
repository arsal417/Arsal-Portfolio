<?php
session_start();
if (empty($_SESSION['admin_logged_in'])) { header('Location: login.php'); exit(); }
require_once __DIR__ . '/../api/config.php';
header('Content-Type: text/html; charset=UTF-8');

$db = getDB();

// Approve / Reject / Delete
if (isset($_GET['approve'])) {
    $db->prepare("UPDATE reviews SET status='approved' WHERE id=:id")->execute([':id' => (int)$_GET['approve']]);
    header('Location: reviews.php'); exit();
}
if (isset($_GET['reject'])) {
    $db->prepare("UPDATE reviews SET status='rejected' WHERE id=:id")->execute([':id' => (int)$_GET['reject']]);
    header('Location: reviews.php'); exit();
}
if (isset($_GET['delete'])) {
    $db->prepare("DELETE FROM reviews WHERE id=:id")->execute([':id' => (int)$_GET['delete']]);
    header('Location: reviews.php'); exit();
}

$filter  = $_GET['filter'] ?? 'all';
$where   = $filter !== 'all' ? "WHERE status='$filter'" : '';
$reviews = $db->query("SELECT * FROM reviews $where ORDER BY created_at DESC")->fetchAll();

$counts = $db->query("SELECT status, COUNT(*) as n FROM reviews GROUP BY status")->fetchAll(PDO::FETCH_KEY_PAIR);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Reviews — Admin</title>
  <link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=Inter:wght@400;500;600&family=Space+Mono&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="admin.css"/>
</head>
<body>
  <?php include 'partials/nav.php'; ?>

  <main class="admin-main">
    <div class="page-head">
      <div>
        <h1 class="page-title">Client Reviews</h1>
        <p class="page-sub">Approve reviews to display them live on your portfolio.</p>
      </div>
      <div class="filter-tabs">
        <a href="?filter=all"      class="<?= $filter==='all'      ?'active':'' ?>">All (<?= array_sum($counts) ?>)</a>
        <a href="?filter=pending"  class="<?= $filter==='pending'  ?'active':'' ?>">Pending (<?= $counts['pending'] ?? 0 ?>)</a>
        <a href="?filter=approved" class="<?= $filter==='approved' ?'active':'' ?>">Approved (<?= $counts['approved'] ?? 0 ?>)</a>
        <a href="?filter=rejected" class="<?= $filter==='rejected' ?'active':'' ?>">Rejected (<?= $counts['rejected'] ?? 0 ?>)</a>
      </div>
    </div>

    <div class="reviews-manage-grid">
      <?php if (empty($reviews)): ?>
        <div class="section-card"><p class="empty-row">No reviews found.</p></div>
      <?php else: ?>
        <?php foreach ($reviews as $r): ?>
        <div class="review-manage-card <?= $r['status'] === 'approved' ? 'approved' : ($r['status'] === 'rejected' ? 'rejected' : '') ?>">
          <div class="rmc-header">
            <div class="rpc-av"><?= strtoupper(substr($r['name'], 0, 2)) ?></div>
            <div class="rmc-info">
              <strong><?= htmlspecialchars($r['name']) ?></strong>
              <span><?= htmlspecialchars($r['role'] ?: $r['email']) ?></span>
            </div>
            <span class="badge badge-<?= $r['status'] ?>"><?= $r['status'] ?></span>
          </div>

          <div class="stars rmc-stars"><?= str_repeat('★', $r['rating']) . str_repeat('☆', 5 - $r['rating']) ?></div>

          <p class="rmc-text">"<?= htmlspecialchars($r['review_text']) ?>"</p>

          <div class="rmc-meta">
            <span><?= date('d M Y, H:i', strtotime($r['created_at'])) ?></span>
            <span><?= htmlspecialchars($r['email']) ?></span>
          </div>

          <div class="rmc-actions">
            <?php if ($r['status'] !== 'approved'): ?>
              <a href="?approve=<?= $r['id'] ?>" class="btn-xs btn-green">✓ Approve</a>
            <?php endif; ?>
            <?php if ($r['status'] !== 'rejected'): ?>
              <a href="?reject=<?= $r['id'] ?>" class="btn-xs btn-muted">✕ Reject</a>
            <?php endif; ?>
            <a href="?delete=<?= $r['id'] ?>" class="btn-xs btn-danger" onclick="return confirm('Delete this review?')">Delete</a>
          </div>
        </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </main>
</body>
</html>
