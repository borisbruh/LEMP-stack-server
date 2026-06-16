<?php //register.php    teh account craetion page

session_start();

ini_set('display_errors', 1);
error_reporting(E_ALL);

$host = 'localhost';
$db   = 'acc_db';
$user = 'user';
$pass = 'passw0rd';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

try {
    $pdo = new PDO($dsn, $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("DB connection failed: " . $e->getMessage());
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $username = $_POST['username'];
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (!$username || !$email || !$password) {
            die("username, email and password required");

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $message = "Invalid email format";

    } else {
    

        // 🔐 hash password properly
        $hash = password_hash($password, PASSWORD_BCRYPT);

        try {
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
        $stmt->execute([$username, $email, $hash]);

        echo "✅ User registered successfully. <a href='login.php'>Login</a>";

        } catch (PDOException $e) {
        echo "❌ Error: " . $e->getMessage();
        }
    }
}


if (isset($_SESSION['message'])) {
        $message = $_SESSION['message'];
        unset($_SESSION['message']);  // Remove the message from the session after displaying it
}


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" href="color.css">
</head>
<body>

<br>
<div class="div">

<h2>Register</h2><br>

<form style="margin:auto;" method="POST" action="">

        <label for="username">Username:</label><br>
        <input type="username" name="username" placeholder="user5" required><br><br>

        <label for="email">Email:</label><br>
        <input type="email" name="email" placeholder="user@example.com" required><br><br>

        <label for="password">Password:</label><br>
        <input type="password" name="password" placeholder="$ecure_passw0rd" required><br><br>

        <button style="margin:auto;" type="submit">Register</button>
</form>

<br>

<?php if (!empty($message)) : ?>
        <p style="color: <?= strpos($message, 'successful') !== false ? 'green' : 'red' ?>;">
                <?= htmlspecialchars($message) ?>
        </p>
<?php endif; ?>

<br><br>
<a href="login.php">Login page</a>

</body>
</html>
