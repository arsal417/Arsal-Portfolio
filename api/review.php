<?php
// ============================================================
//  REVIEW / TESTIMONIAL SUBMISSION HANDLER
// ============================================================
require_once __DIR__ . '/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') fail('Method not allowed.', 405);

$raw  = json_decode(file_get_contents('php://input'), true);
$d    = $raw ?? $_POST;

$name   = clean($d['name']   ?? '');
$email  = filter_var(trim($d['email'] ?? ''), FILTER_VALIDATE_EMAIL);
$role   = clean($d['role']   ?? '');
$rating = max(1, min(5, (int)($d['rating'] ?? 5)));
$text   = clean($d['review'] ?? '');

if (!$name)  fail('Name is required.');
if (!$email) fail('A valid email is required.');
if (!$text)  fail('Review text is required.');

$db   = getDB();
$stmt = $db->prepare("
    INSERT INTO reviews (name, email, role, rating, review_text)
    VALUES (:name, :email, :role, :rating, :text)
");
$stmt->execute([
    ':name'   => $name,
    ':email'  => $email,
    ':role'   => $role,
    ':rating' => $rating,
    ':text'   => $text,
]);

// Notify admin
$html = "
<div style='font-family:sans-serif;max-width:500px;margin:0 auto;background:#111;color:#fff;padding:32px;border-radius:12px;'>
  <h2 style='color:#d4a847;'>⭐ New Review Submitted</h2>
  <p><strong>From:</strong> $name ($email)</p>
  <p><strong>Role:</strong> $role</p>
  <p><strong>Rating:</strong> " . str_repeat('★', $rating) . str_repeat('☆', 5 - $rating) . "</p>
  <p><strong>Review:</strong><br/>$text</p>
  <p style='margin-top:20px;'><a href='" . SITE_URL . "/admin/reviews.php' style='background:#d4a847;color:#000;padding:10px 22px;border-radius:100px;text-decoration:none;font-weight:700;'>Review in Admin →</a></p>
</div>";
sendMail(ADMIN_EMAIL, "New Review from $name — " . SITE_NAME, $html);

ok(['message' => 'Thank you for your review! It will appear after approval.']);
