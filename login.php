<?php //login.php    the login page

session_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// --- Database credentials ---
$host = 'localhost';
$db   = 'acc_db';      //name of the sql db
$user = 'user';        //name of sql db user that has perms to acces the db
$pass = 'passw0rd';    //password for the user
$charset = 'utf8mb4';

// --- PDO setup ---
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// --- Handling form submission ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

	$email = trim($_POST['email'] ?? '');
	$password = $_POST['password'] ?? '';

	// --- Basic validation ---
	if (empty($email) || empty($password)) {

		$message = "Email and password are required";
		
	} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

		$message = "Invalid email format";
		
	} else {

		$stmt = $pdo->prepare("SELECT password FROM users WHERE email = ?");
		$stmt->execute([$email]);
		$user = $stmt->fetch();
		
		$stmt = $pdo->prepare("SELECT perms FROM users WHERE email = ?");
		$stmt->execute([$email]);
		$perms = $stmt->fetch();
		
		$stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
		$stmt->execute([$email]);
		$id = $stmt->fetch();

		if ($user && password_verify($password, $user['password'])) {
		
			// --- Successful login, start session ---
			$_SESSION['id'] = $id['id'];         		// Store id in session
			$_SESSION['email'] = $email;         	// Store email in session
			$_SESSION['logged_in'] = true;       	// Set login status to true
			$_SESSION['perms'] = $perms['perms'];	// Store user perms in session

			// --- Redirect to dashboard ---
			header("Location: dashboard.php");
			exit; // Making sure to exit after the redirect to stop further execution
			
		} else {
		
			$message = "Invalid email or password";

		}
	}
}



// Debugging output
//echo '<pre>';
//print_r($_SESSION);
//echo '</pre>';



// Display the message if it's set
if (isset($_SESSION['message'])) {
	$message = $_SESSION['message'];
	unset($_SESSION['message']);  // Remove the message from the session after displaying it
}


?>



<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<title>Login</title>
	
	<link rel="stylesheet" href="color.css">
	
</head>

<body>
	
	<br>
	<div class="div">
	
	<h2>Login</h2><br>
	
	<form style="margin:auto;" method="POST" action="">
		
		<label for="email">Email:</label><br>
		<input type="email" name="email" placeholder="user@example.com" required><br><br>

		<label for="password">Password:</label><br>
		<input type="password" name="password" placeholder="passw0rd" required><br><br>

		<button style="margin:auto;" type="submit">Login</button>
	</form>
	
	<br>
	
	<?php if (!empty($message)) : ?>
		<p style="color: <?= strpos($message, 'successful') !== false ? 'green' : 'red' ?>;">
			<?= htmlspecialchars($message) ?>
		</p>
	<?php endif; ?>

	<br><br>
	<a href="register.php">Register page</a>
	
	</div>

</body>
</html>
