<?php
session_start();
if (empty($_SESSION['admin_logged_in'])) { header('Location: login.php'); exit(); }
require_once __DIR__ . '/../api/config.php';
header('Content-Type: text/html; charset=UTF-8');

$db = getDB();

// Handle reply POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reply_text'], $_POST['contact_id'])) {
    $cid   = (int)$_POST['contact_id'];
    $reply = clean($_POST['reply_text']);

    if ($reply && $cid) {
        // Fetch contact email
        $c = $db->prepare("SELECT * FROM contacts WHERE id = :id");
        $c->execute([':id' => $cid]);
        $contact = $c->fetch();

        if ($contact) {
            // Save reply
            $ins = $db->prepare("INSERT INTO replies (contact_id, reply_text) VALUES (:cid, :txt)");
            $ins->execute([':cid' => $cid, ':txt' => $reply]);

            // Update status
            $db->prepare("UPDATE contacts SET status='replied' WHERE id=:id")->execute([':id' => $cid]);

            // Send email reply
            $html = "
            <!DOCTYPE html><html><body style='font-family:sans-serif;background:#f5f5f5;padding:20px;'>
            <div style='max-width:600px;margin:0 auto;background:#fff;border-radius:12px;overflow:hidden;box-shadow:0 4px 20px rgba(0,0,0,0.1);'>
              <div style='background:#080808;padding:28px 36px;'>
                <div style='display:inline-block;background:linear-gradient(135deg,#d4a847,#f0c866);width:44px;height:44px;border-radius:10px;line-height:44px;text-align:center;font-weight:800;color:#000;font-size:1rem;'>AA</div>
              </div>
              <div style='padding:36px;'>
                <p style='color:#333;'>Hi <strong>" . htmlspecialchars($contact['name']) . "</strong>,</p>
                <div style='background:#fafafa;border-left:3px solid #d4a847;padding:16px 20px;margin:20px 0;border-radius:0 8px 8px 0;'>
                  " . nl2br(htmlspecialchars($reply)) . "
                </div>
                <p style='color:#555;'>Best regards,<br/><strong>Arsalan Abbas</strong><br/><span style='color:#d4a847;'>Video Editor & Software Engineer</span></p>
              </div>
            </div></body></html>";

            sendMail($contact['email'], "Re: Your inquiry — Arsalan Abbas", $html, ADMIN_EMAIL);
            $replySuccess = "Reply sent to {$contact['email']}";
        }
    }
}

// Handle mark-as-read
if (isset($_GET['mark_read'])) {
    $db->prepare("UPDATE contacts SET status='read' WHERE id=:id")->execute([':id' => (int)$_GET['mark_read']]);
    header('Location: contacts.php'); exit();
}

// Handle delete
if (isset($_GET['delete'])) {
    $db->prepare("DELETE FROM contacts WHERE id=:id")->execute([':id' => (int)$_GET['delete']]);
    header('Location: contacts.php'); exit();
}

// Single contact view
$viewContact = null;
$contactReplies = [];
if (isset($_GET['id'])) {
    $stmt = $db->prepare("SELECT * FROM contacts WHERE id=:id");
    $stmt->execute([':id' => (int)$_GET['id']]);
    $viewContact = $stmt->fetch();
    if ($viewContact && $viewContact['status'] === 'new') {
        $db->prepare("UPDATE contacts SET status='read' WHERE id=:id")->execute([':id' => $viewContact['id']]);
        $viewContact['status'] = 'read';
    }
    if ($viewContact) {
        $rStmt = $db->prepare("SELECT * FROM replies WHERE contact_id=:id ORDER BY sent_at ASC");
        $rStmt->execute([':id' => $viewContact['id']]);
        $contactReplies = $rStmt->fetchAll();
    }
}

// Filter
$filter  = $_GET['filter'] ?? 'all';
$where   = $filter !== 'all' ? "WHERE status='" . $db->quote($filter) . "'" : '';
$contacts = $db->query("SELECT * FROM contacts $where ORDER BY created_at DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Contacts — Admin</title>
  <link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=Inter:wght@400;500;600&family=Space+Mono&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="admin.css"/>
</head>
<body>
  <?php include 'partials/nav.php'; ?>

  <main class="admin-main">
    <?php if ($viewContact): ?>
    <!-- ===== SINGLE CONTACT VIEW ===== -->
    <div class="page-head">
      <div>
        <a href="contacts.php" class="back-link">← All Contacts</a>
        <h1 class="page-title">Inquiry #<?= $viewContact['id'] ?></h1>
      </div>
      <div style="display:flex;gap:10px;">
        <a href="contacts.php?delete=<?= $viewContact['id'] ?>" class="btn-sm btn-danger" onclick="return confirm('Delete this contact?')">Delete</a>
      </div>
    </div>

    <div class="contact-detail-grid">
      <!-- Info -->
      <div class="section-card">
        <h3 style="margin-bottom:20px;">Client Info</h3>
        <div class="detail-row"><span>Name</span><strong><?= htmlspecialchars($viewContact['name']) ?></strong></div>
        <div class="detail-row"><span>Email</span><a href="mailto:<?= $viewContact['email'] ?>" class="text-gold"><?= htmlspecialchars($viewContact['email']) ?></a></div>
        <div class="detail-row"><span>Service</span><?= htmlspecialchars($viewContact['service'] ?: '—') ?></div>
        <div class="detail-row"><span>Budget</span><?= htmlspecialchars($viewContact['budget'] ?: '—') ?></div>
        <div class="detail-row"><span>Status</span><span class="badge badge-<?= $viewContact['status'] ?>"><?= $viewContact['status'] ?></span></div>
        <div class="detail-row"><span>Date</span><?= date('d M Y, H:i', strtotime($viewContact['created_at'])) ?></div>
        <div class="detail-row"><span>IP</span><code><?= htmlspecialchars($viewContact['ip_address'] ?: '—') ?></code></div>
      </div>

      <!-- Message + Reply -->
      <div>
        <div class="section-card" style="margin-bottom:20px;">
          <h3>Message</h3>
          <div class="message-box"><?= nl2br(htmlspecialchars($viewContact['message'])) ?></div>
        </div>

        <?php if (!empty($contactReplies)): ?>
        <div class="section-card" style="margin-bottom:20px;">
          <h3>Previous Replies</h3>
          <?php foreach ($contactReplies as $rep): ?>
          <div class="reply-item">
            <div class="reply-meta">Sent <?= date('d M Y, H:i', strtotime($rep['sent_at'])) ?></div>
            <p><?= nl2br(htmlspecialchars($rep['reply_text'])) ?></p>
          </div>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <?php if (isset($replySuccess)): ?>
          <div class="alert alert-success"><?= $replySuccess ?></div>
        <?php endif; ?>

        <div class="section-card">
          <h3>Send Reply</h3>
          <form method="POST">
            <input type="hidden" name="contact_id" value="<?= $viewContact['id'] ?>"/>
            <div class="lf-field">
              <label>Your Reply</label>
              <textarea name="reply_text" rows="6" placeholder="Write your reply here..." required></textarea>
            </div>
            <button type="submit" class="btn-admin-gold">Send Reply via Email →</button>
          </form>
        </div>
      </div>
    </div>

    <?php else: ?>
    <!-- ===== CONTACTS LIST ===== -->
    <div class="page-head">
      <div>
        <h1 class="page-title">All Inquiries</h1>
        <p class="page-sub"><?= count($contacts) ?> total submissions</p>
      </div>
      <div class="filter-tabs">
        <a href="?filter=all"     class="<?= $filter==='all'     ?'active':'' ?>">All</a>
        <a href="?filter=new"     class="<?= $filter==='new'     ?'active':'' ?>">New</a>
        <a href="?filter=read"    class="<?= $filter==='read'    ?'active':'' ?>">Read</a>
        <a href="?filter=replied" class="<?= $filter==='replied' ?'active':'' ?>">Replied</a>
      </div>
    </div>

    <div class="section-card">
      <table class="admin-table">
        <thead>
          <tr><th>#</th><th>Name</th><th>Email</th><th>Service</th><th>Budget</th><th>Status</th><th>Date</th><th>Actions</th></tr>
        </thead>
        <tbody>
          <?php if (empty($contacts)): ?>
            <tr><td colspan="8" class="empty-row">No contacts found.</td></tr>
          <?php else: ?>
            <?php foreach ($contacts as $c): ?>
            <tr class="<?= $c['status']==='new' ? 'row-new' : '' ?>">
              <td class="mono">#<?= $c['id'] ?></td>
              <td><strong><?= htmlspecialchars($c['name']) ?></strong></td>
              <td class="text-gold"><?= htmlspecialchars($c['email']) ?></td>
              <td><?= htmlspecialchars($c['service'] ?: '—') ?></td>
              <td><?= htmlspecialchars($c['budget'] ?: '—') ?></td>
              <td><span class="badge badge-<?= $c['status'] ?>"><?= $c['status'] ?></span></td>
              <td class="mono text-muted"><?= date('d M Y', strtotime($c['created_at'])) ?></td>
              <td>
                <div style="display:flex;gap:6px;">
                  <a href="?id=<?= $c['id'] ?>" class="btn-xs">View & Reply</a>
                  <?php if ($c['status']==='new'): ?>
                  <a href="?mark_read=<?= $c['id'] ?>" class="btn-xs btn-muted">Mark Read</a>
                  <?php endif; ?>
                  <a href="?delete=<?= $c['id'] ?>" class="btn-xs btn-danger" onclick="return confirm('Delete?')">✕</a>
                </div>
              </td>
            </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
    <?php endif; ?>
  </main>
</body>
</html>
