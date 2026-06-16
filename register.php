<?php //register.php    the account registration page

ini_set('display_errors', 1);
error_reporting(E_ALL);

// --- Database credentials ---
$host = 'localhost';
$db   = 'acc_db';      //name of the sql db
$user = 'user';        //name of sql db user that has perms to acces the db
$pass = 'passw0rd';    //password for the user
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

try {
    $pdo = new PDO($dsn, $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("DB connection failed: " . $e->getMessage());
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $email= trim($_POST['email']);
    $password = $_POST['password'];

    if (!$email|| !$password) {
        die("email and password required");
    }

    // 🔐 hash password properly
    $hash = password_hash($password, PASSWORD_BCRYPT);

    try {
        $stmt = $pdo->prepare("INSERT INTO users (email, password) VALUES (?, ?)");
        $stmt->execute([$email, $hash]);

        echo "✅ User registered successfully. <a href='login.php'>Login</a>";

    } catch (PDOException $e) {
        echo "❌ Error: " . $e->getMessage();
    }
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

        <label for="email">Email:</label><br>
        <input type="email" name="email" placeholder="user@example.com" required><br><br>

        <label for="password">Password:</label><br>
        <input type="password" name="password" placeholder="passw0rd" required><br><br>

        <button style="margin:auto;" type="submit">Register</button>
</form>

<br>

<br><br>
<a href="login.php">Login page</a>

</body>
</html>
