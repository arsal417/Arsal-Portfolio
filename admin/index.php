<?php
session_start();
if (empty($_SESSION['admin_logged_in'])) { header('Location: login.php'); exit(); }
require_once __DIR__ . '/../api/config.php';
header('Content-Type: text/html; charset=UTF-8');

$db = getDB();

// Stats
$totalContacts  = $db->query("SELECT COUNT(*) FROM contacts")->fetchColumn();
$newContacts    = $db->query("SELECT COUNT(*) FROM contacts WHERE status='new'")->fetchColumn();
$totalReviews   = $db->query("SELECT COUNT(*) FROM reviews")->fetchColumn();
$pendingReviews = $db->query("SELECT COUNT(*) FROM reviews WHERE status='pending'")->fetchColumn();
$totalReplies   = $db->query("SELECT COUNT(*) FROM replies")->fetchColumn();

// Recent contacts
$recentContacts = $db->query("SELECT * FROM contacts ORDER BY created_at DESC LIMIT 5")->fetchAll();
// Recent reviews
$recentReviews  = $db->query("SELECT * FROM reviews ORDER BY created_at DESC LIMIT 4")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Dashboard — Arsalan Abbas Admin</title>
  <link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=Inter:wght@400;500;600&family=Space+Mono&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="admin.css"/>
</head>
<body>
  <?php include 'partials/nav.php'; ?>

  <main class="admin-main">
    <div class="page-head">
      <div>
        <h1 class="page-title">Dashboard</h1>
        <p class="page-sub">Welcome back, <strong><?= htmlspecialchars($_SESSION['admin_username']) ?></strong> — here's your overview.</p>
      </div>
      <a href="../index.html" class="btn-sm" target="_blank">View Site ↗</a>
    </div>

    <!-- STAT CARDS -->
    <div class="stat-grid">
      <div class="stat-card">
        <div class="sc-icon">📩</div>
        <div class="sc-val"><?= $totalContacts ?></div>
        <div class="sc-lbl">Total Inquiries</div>
      </div>
      <div class="stat-card gold">
        <div class="sc-icon">🔔</div>
        <div class="sc-val"><?= $newContacts ?></div>
        <div class="sc-lbl">New (Unread)</div>
      </div>
      <div class="stat-card">
        <div class="sc-icon">⭐</div>
        <div class="sc-val"><?= $totalReviews ?></div>
        <div class="sc-lbl">Total Reviews</div>
      </div>
      <div class="stat-card <?= $pendingReviews > 0 ? 'warn' : '' ?>">
        <div class="sc-icon">⏳</div>
        <div class="sc-val"><?= $pendingReviews ?></div>
        <div class="sc-lbl">Pending Reviews</div>
      </div>
      <div class="stat-card">
        <div class="sc-icon">✉️</div>
        <div class="sc-val"><?= $totalReplies ?></div>
        <div class="sc-lbl">Replies Sent</div>
      </div>
    </div>

    <!-- RECENT CONTACTS -->
    <div class="section-card">
      <div class="sc-head">
        <h2>Recent Inquiries</h2>
        <a href="contacts.php" class="btn-sm">View All →</a>
      </div>
      <table class="admin-table">
        <thead>
          <tr>
            <th>#</th><th>Name</th><th>Email</th><th>Service</th><th>Budget</th><th>Status</th><th>Date</th><th>Action</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($recentContacts)): ?>
            <tr><td colspan="8" class="empty-row">No inquiries yet.</td></tr>
          <?php else: ?>
            <?php foreach ($recentContacts as $c): ?>
            <tr>
              <td class="mono">#<?= $c['id'] ?></td>
              <td><strong><?= htmlspecialchars($c['name']) ?></strong></td>
              <td class="text-gold"><?= htmlspecialchars($c['email']) ?></td>
              <td><?= htmlspecialchars($c['service'] ?: '—') ?></td>
              <td><?= htmlspecialchars($c['budget'] ?: '—') ?></td>
              <td><span class="badge badge-<?= $c['status'] ?>"><?= $c['status'] ?></span></td>
              <td class="mono text-muted"><?= date('d M Y', strtotime($c['created_at'])) ?></td>
              <td><a href="contacts.php?id=<?= $c['id'] ?>" class="btn-xs">View</a></td>
            </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

    <!-- RECENT REVIEWS -->
    <div class="section-card">
      <div class="sc-head">
        <h2>Recent Reviews</h2>
        <a href="reviews.php" class="btn-sm">View All →</a>
      </div>
      <div class="reviews-preview-grid">
        <?php if (empty($recentReviews)): ?>
          <p class="empty-row">No reviews yet.</p>
        <?php else: ?>
          <?php foreach ($recentReviews as $r): ?>
          <div class="review-preview-card">
            <div class="rpc-top">
              <div class="rpc-av"><?= strtoupper(substr($r['name'], 0, 2)) ?></div>
              <div>
                <strong><?= htmlspecialchars($r['name']) ?></strong>
                <span><?= htmlspecialchars($r['role'] ?: '') ?></span>
              </div>
              <span class="badge badge-<?= $r['status'] ?>" style="margin-left:auto"><?= $r['status'] ?></span>
            </div>
            <p>"<?= htmlspecialchars(substr($r['review_text'], 0, 120)) ?>…"</p>
            <div class="stars"><?= str_repeat('★', $r['rating']) . str_repeat('☆', 5 - $r['rating']) ?></div>
          </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>
  </main>
</body>
</html>
