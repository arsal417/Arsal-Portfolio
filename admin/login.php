<?php
session_start();
require_once __DIR__ . '/../api/config.php';
header('Content-Type: text/html; charset=UTF-8');

// Already logged in
if (!empty($_SESSION['admin_logged_in'])) {
    header('Location: index.php'); exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    $db   = getDB();
    $stmt = $db->prepare("SELECT * FROM admins WHERE username = :u LIMIT 1");
    $stmt->execute([':u' => $username]);
    $admin = $stmt->fetch();

    if ($admin && password_verify($password, $admin['password'])) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_username']  = $admin['username'];
        header('Location: index.php'); exit();
    } else {
        $error = 'Incorrect username or password.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Admin Login — Arsalan Abbas</title>
  <link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=Inter:wght@400;500;600&family=Space+Mono&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="admin.css"/>
</head>
<body class="login-body">
  <div class="login-wrap">
    <div class="login-card">
      <div class="login-logo">AA</div>
      <h1>Admin Panel</h1>
      <p class="login-sub">Sign in to manage your portfolio</p>

      <?php if ($error): ?>
        <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>

      <form method="POST" class="login-form">
        <div class="lf-field">
          <label>Username</label>
          <input type="text" name="username" placeholder="arsalan" required autocomplete="username"/>
        </div>
        <div class="lf-field">
          <label>Password</label>
          <input type="password" name="password" placeholder="••••••••" required autocomplete="current-password"/>
        </div>
        <button type="submit" class="btn-admin-gold">Sign In →</button>
      </form>

      <a href="../index.html" class="login-back">← Back to Portfolio</a>
    </div>
  </div>
</body>
</html>
