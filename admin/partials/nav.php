<?php
$current = basename($_SERVER['PHP_SELF']);
$newContacts = (isset($db)) ? $db->query("SELECT COUNT(*) FROM contacts WHERE status='new'")->fetchColumn() : 0;
$pendingRev  = (isset($db)) ? $db->query("SELECT COUNT(*) FROM reviews WHERE status='pending'")->fetchColumn() : 0;
?>
<aside class="admin-sidebar">
  <div class="sidebar-brand">
    <div class="sb-logo">AA</div>
    <div>
      <span class="sb-title">Admin Panel</span>
      <span class="sb-sub">Arsalan Abbas</span>
    </div>
  </div>

  <nav class="sidebar-nav">
    <a href="index.php" class="sn-link <?= $current==='index.php' ? 'active':'' ?>">
      <span>📊</span> Dashboard
    </a>
    <a href="contacts.php" class="sn-link <?= $current==='contacts.php' ? 'active':'' ?>">
      <span>📩</span> Inquiries
      <?php if ($newContacts > 0): ?>
        <span class="sn-badge"><?= $newContacts ?></span>
      <?php endif; ?>
    </a>
    <a href="reviews.php" class="sn-link <?= $current==='reviews.php' ? 'active':'' ?>">
      <span>⭐</span> Reviews
      <?php if ($pendingRev > 0): ?>
        <span class="sn-badge"><?= $pendingRev ?></span>
      <?php endif; ?>
    </a>
  </nav>

  <div class="sidebar-footer">
    <a href="../index.html" target="_blank" class="sn-link">
      <span>↗</span> View Portfolio
    </a>
    <a href="logout.php" class="sn-link sn-logout">
      <span>🚪</span> Logout
    </a>
  </div>
</aside>
