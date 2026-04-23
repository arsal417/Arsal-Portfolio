<?php
// ============================================================
//  CONTACT FORM HANDLER
//  Saves submission to DB + emails Arsalan + auto-reply to client
// ============================================================
require_once __DIR__ . '/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') fail('Method not allowed.', 405);

// --- GET INPUT (supports both JSON body and form-data) ---
$raw = json_decode(file_get_contents('php://input'), true);
$d   = $raw ?? $_POST;

$name    = clean($d['name']    ?? '');
$email   = filter_var(trim($d['email'] ?? ''), FILTER_VALIDATE_EMAIL);
$service = clean($d['service'] ?? '');
$budget  = clean($d['budget']  ?? '');
$message = clean($d['message'] ?? '');

// --- VALIDATE ---
if (!$name)    fail('Name is required.');
if (!$email)   fail('A valid email address is required.');
if (!$message) fail('Message is required.');

// --- SAVE TO DATABASE ---
$db   = getDB();
$stmt = $db->prepare("
    INSERT INTO contacts (name, email, service, budget, message, ip_address)
    VALUES (:name, :email, :service, :budget, :message, :ip)
");
$stmt->execute([
    ':name'    => $name,
    ':email'   => $email,
    ':service' => $service,
    ':budget'  => $budget,
    ':message' => $message,
    ':ip'      => $_SERVER['REMOTE_ADDR'] ?? '',
]);
$contactId = $db->lastInsertId();

// --- EMAIL TO ARSALAN (notification) ---
$adminHtml = "
<!DOCTYPE html><html><body style='margin:0;padding:0;background:#080808;font-family:sans-serif;'>
<div style='max-width:600px;margin:0 auto;background:#111;border:1px solid #2a2a2a;border-radius:16px;overflow:hidden;'>
  <div style='background:linear-gradient(135deg,#d4a847,#f0c866);padding:28px 36px;'>
    <h1 style='margin:0;color:#000;font-size:1.4rem;letter-spacing:-0.02em;'>📩 New Project Inquiry</h1>
    <p style='margin:6px 0 0;color:rgba(0,0,0,0.7);font-size:0.85rem;'>Received on " . date('d M Y, H:i') . "</p>
  </div>
  <div style='padding:36px;'>
    <table style='width:100%;border-collapse:collapse;'>
      <tr>
        <td style='padding:10px 0;color:#888;font-size:0.8rem;text-transform:uppercase;letter-spacing:0.1em;width:130px;'>Name</td>
        <td style='padding:10px 0;color:#fff;font-weight:600;'>" . $name . "</td>
      </tr>
      <tr style='border-top:1px solid #222;'>
        <td style='padding:10px 0;color:#888;font-size:0.8rem;text-transform:uppercase;letter-spacing:0.1em;'>Email</td>
        <td style='padding:10px 0;color:#d4a847;'><a href='mailto:" . $email . "' style='color:#d4a847;'>" . $email . "</a></td>
      </tr>
      <tr style='border-top:1px solid #222;'>
        <td style='padding:10px 0;color:#888;font-size:0.8rem;text-transform:uppercase;letter-spacing:0.1em;'>Service</td>
        <td style='padding:10px 0;color:#fff;'>" . ($service ?: '—') . "</td>
      </tr>
      <tr style='border-top:1px solid #222;'>
        <td style='padding:10px 0;color:#888;font-size:0.8rem;text-transform:uppercase;letter-spacing:0.1em;'>Budget</td>
        <td style='padding:10px 0;color:#fff;'>" . ($budget ?: '—') . "</td>
      </tr>
      <tr style='border-top:1px solid #222;'>
        <td style='padding:10px 0;color:#888;font-size:0.8rem;text-transform:uppercase;letter-spacing:0.1em;vertical-align:top;'>Message</td>
        <td style='padding:10px 0;color:#ccc;line-height:1.7;'>" . nl2br($message) . "</td>
      </tr>
    </table>
    <div style='margin-top:28px;padding-top:24px;border-top:1px solid #222;'>
      <a href='" . SITE_URL . "/admin/' style='display:inline-block;background:linear-gradient(135deg,#d4a847,#f0c866);color:#000;padding:12px 28px;border-radius:100px;text-decoration:none;font-weight:700;font-size:0.85rem;'>Open Admin Panel →</a>
    </div>
  </div>
  <div style='padding:20px 36px;border-top:1px solid #1a1a1a;'>
    <p style='margin:0;color:#555;font-size:0.75rem;'>Arsalan Abbas Portfolio · Contact #" . $contactId . "</p>
  </div>
</div></body></html>";

sendMail(ADMIN_EMAIL, "New Inquiry from $name — " . SITE_NAME, $adminHtml);

// --- AUTO-REPLY TO CLIENT ---
$clientHtml = "
<!DOCTYPE html><html><body style='margin:0;padding:0;background:#f5f5f5;font-family:sans-serif;'>
<div style='max-width:600px;margin:0 auto;background:#fff;border-radius:16px;overflow:hidden;box-shadow:0 4px 40px rgba(0,0,0,0.1);'>
  <div style='background:#080808;padding:36px;text-align:center;'>
    <div style='display:inline-block;background:linear-gradient(135deg,#d4a847,#f0c866);width:52px;height:52px;border-radius:12px;line-height:52px;font-size:1.4rem;font-weight:800;color:#000;margin-bottom:16px;'>AA</div>
    <h1 style='margin:0;color:#fff;font-size:1.5rem;letter-spacing:-0.02em;'>Message Received!</h1>
    <p style='margin:10px 0 0;color:#888;font-size:0.9rem;'>I'll get back to you within 24 hours.</p>
  </div>
  <div style='padding:40px 36px;'>
    <p style='color:#333;font-size:1rem;line-height:1.7;'>Hi <strong>" . $name . "</strong>,</p>
    <p style='color:#555;font-size:0.95rem;line-height:1.8;'>Thank you for reaching out! I've received your inquiry" . ($service ? " regarding <strong>$service</strong>" : '') . " and I'm excited to learn more about your project.</p>
    <p style='color:#555;font-size:0.95rem;line-height:1.8;'>I'll review your message and get back to you <strong>within 24 hours</strong>. If it's urgent, you can also reach me directly on WhatsApp.</p>
    <div style='background:#fafafa;border:1px solid #eee;border-radius:12px;padding:20px 24px;margin:28px 0;'>
      <p style='margin:0 0 8px;color:#999;font-size:0.75rem;text-transform:uppercase;letter-spacing:0.1em;'>Your message summary</p>
      <p style='margin:0;color:#333;font-size:0.9rem;line-height:1.6;'>" . (strlen($message) > 200 ? substr($message, 0, 200) . '…' : $message) . "</p>
    </div>
    <p style='color:#555;font-size:0.95rem;'>Talk soon,<br/><strong style='color:#000;'>Arsalan Abbas</strong><br/><span style='color:#d4a847;font-size:0.85rem;'>Video Editor & Software Engineer</span></p>
  </div>
  <div style='background:#080808;padding:20px 36px;text-align:center;'>
    <p style='margin:0;color:#555;font-size:0.75rem;'>© 2026 Arsalan Abbas · <a href='" . SITE_URL . "' style='color:#d4a847;text-decoration:none;'>Portfolio</a></p>
  </div>
</div></body></html>";

sendMail($email, "Got your message, " . $name . "! — Arsalan Abbas", $clientHtml, ADMIN_EMAIL);

ok(['message' => 'Message sent successfully! I\'ll be in touch within 24 hours.', 'id' => $contactId]);
