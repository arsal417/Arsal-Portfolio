<?php
// ============================================================
//  ARSALAN ABBAS PORTFOLIO — API CONFIGURATION
//  !! EDIT YOUR CREDENTIALS BELOW BEFORE UPLOADING !!
// ============================================================

// --- DATABASE ---
define('DB_HOST', 'localhost');
define('DB_NAME', 'arsalan_portfolio');
define('DB_USER', 'your_db_username');   // <-- change this
define('DB_PASS', 'your_db_password');   // <-- change this

// --- YOUR EMAIL (where notifications go) ---
define('ADMIN_EMAIL', 'arsalify@gmail.com'); // admin inbox
define('SITE_NAME',   'Arsalan Abbas Portfolio');
define('SITE_URL',    'https://yourdomain.com'); // <-- change this

// --- CORS & SECURITY ---
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// --- DB CONNECTION ---
function getDB(): PDO {
    static $pdo = null;
    if ($pdo) return $pdo;
    try {
        $pdo = new PDO(
            'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
            DB_USER, DB_PASS,
            [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]
        );
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Database connection failed.']);
        exit();
    }
    return $pdo;
}

// --- SEND EMAIL (native PHP mail — works on most hosts) ---
function sendMail(string $to, string $subject, string $htmlBody, string $replyTo = ''): bool {
    $headers  = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: text/html; charset=UTF-8\r\n";
    $headers .= "From: " . SITE_NAME . " <" . ADMIN_EMAIL . ">\r\n";
    if ($replyTo) $headers .= "Reply-To: $replyTo\r\n";
    $headers .= "X-Mailer: PHP/" . phpversion();
    return mail($to, $subject, $htmlBody, $headers);
}

// --- SANITIZE INPUT ---
function clean(string $val): string {
    return htmlspecialchars(strip_tags(trim($val)), ENT_QUOTES, 'UTF-8');
}

// --- JSON RESPONSE HELPERS ---
function ok(array $data = []): void {
    echo json_encode(array_merge(['success' => true], $data));
    exit();
}
function fail(string $message, int $code = 400): void {
    http_response_code($code);
    echo json_encode(['success' => false, 'message' => $message]);
    exit();
}
