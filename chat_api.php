<?php

session_start();

header('Content-Type: application/json; charset=utf-8');

// --------------------------------------------------
// Check login
// --------------------------------------------------

if (
    !isset($_SESSION['logged_in']) ||
    $_SESSION['logged_in'] !== true
) {
    http_response_code(401);

    echo json_encode([
        'success' => false,
        'error' => 'Not logged in.'
    ]);

    exit;
}

$username = $_SESSION['username'];

// --------------------------------------------------
// Chat database credentials
// --------------------------------------------------

$host = 'localhost';
$db   = 'chat_database';
$user = 'user';
$pass = 'passw0rd';
$charset = 'utf8mb4';

// --------------------------------------------------
// PDO
// --------------------------------------------------

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {

    http_response_code(500);

    echo json_encode([
        'success' => false,
        'error' => 'Database connection failed.'
    ]);

    exit;
}

// --------------------------------------------------
// GET messages
// --------------------------------------------------

if ($_SERVER['REQUEST_METHOD'] === 'GET') {

    /*
     * If "after" is supplied, only return messages
     * newer than that msgid.
     *
     * Otherwise return the last 10 messages.
     */

    $after = filter_input(INPUT_GET, 'after', FILTER_VALIDATE_INT);

    if ($after !== false && $after !== null && $after >= 0) {

        $stmt = $pdo->prepare("
            SELECT msgid, `time`, `user`, message
            FROM messages
            WHERE msgid > ?
            ORDER BY msgid ASC
            LIMIT 100
        ");

        $stmt->execute([$after]);

    } else {

        $stmt = $pdo->query("
            SELECT msgid, `time`, `user`, message
            FROM (
                SELECT msgid, `time`, `user`, message
                FROM messages
                ORDER BY msgid DESC
                LIMIT 10
            ) AS recent
            ORDER BY msgid ASC
        ");
    }

    $messages = $stmt->fetchAll();

    echo json_encode([
        'success' => true,
        'messages' => $messages
    ]);

    exit;
}

// --------------------------------------------------
// POST message
// --------------------------------------------------

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $message = $_POST['message'] ?? '';

    // Remove carriage returns/newlines just in case
    $message = str_replace(["\r", "\n"], '', $message);

    /*
     * Allowed:
     * 0-9
     * A-Z
     * a-z
     * space
     * .
     * ,
     * !
     * ?
     *
     * 1-128 characters
     */
    if (!preg_match('/\A[0-9A-Za-z .,!?]{1,128}\z/D', $message)) {

        http_response_code(400);

        echo json_encode([
            'success' => false,
            'error' => 'Message contains invalid characters or is too long.'
        ]);

        exit;
    }

    // --------------------------------------------------
    // Insert
    // --------------------------------------------------

    $stmt = $pdo->prepare("
        INSERT INTO messages (`time`, `user`, message)
        VALUES (CURRENT_TIMESTAMP, ?, ?)
    ");

    $stmt->execute([
        $username,
        $message
    ]);

    echo json_encode([
        'success' => true,
        'msgid' => $pdo->lastInsertId()
    ]);

    exit;
}

// --------------------------------------------------
// Unsupported method
// --------------------------------------------------

http_response_code(405);

echo json_encode([
    'success' => false,
    'error' => 'Method not allowed.'
]);
