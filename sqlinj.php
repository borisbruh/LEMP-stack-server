<?php //sqlinj.php    how defintly not to make a login page

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// --- Start session to handle messages ---
session_start();

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

// --- Handle form submission ---
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

	$email = $_POST['email'];
	$password = $_POST['password'];

	// --- Basic validation ---
	if (empty($email) || empty($password)) {
	
		$_SESSION['message'] = "Email and password are required";
		
	//} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
	
		//$message = "Invalid email format";
		
	} else {

		// Directly inserting email into SQL query (vulnerable to SQL injection)
		$query = "SELECT password FROM users WHERE email = " . $email;  // SQL injection risk here
		
		$_SESSION['query'] = $query;

		// Execute the query
		$result = $pdo->query($query);

		// Check if the query returned a result
		if ($result) {
		
		    	$user = $result->fetch();

		    	if ($user && password_verify($password, $user['password'])) {
		    
				$_SESSION['message'] = "✅ Login successful";
				$_SESSION['result'] = print_r($user, true); // Store the fetched data in session (as a string)
				
		    	} else {

				$_SESSION['message'] = "❌ Invalid email or password";
				$_SESSION['result'] = print_r($user, true); // Store the fetched data in session (as a string)
				
		    	}
		    
		} else {
			
			$_SESSION['message'] = "❌ Error executing query";
			$_SESSION['result'] = 'Error executing query: ' . implode(', ', $pdo->errorInfo()); // Show error info if query failed
		    
		}
    	}
    
	// After processing the form, redirect to the same page (GET request)
	header("Location: " . $_SERVER['PHP_SELF']);
	exit; // Make sure to stop further execution
    
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
</head>
<body>
	<form method="POST" action="">
		<label for="email">Email:</label>
		<input type="text" id="email" name="email" required>
		<br>
		<label for="password">Password:</label>
		<input type="password" id="password" name="password" required>
		<br>
		<button type="submit">Login</button>
	</form>
	
	<!-- Display message after form submission -->
	<p>
		<?php
			// Show the message if it's available in the session
			if (isset($_SESSION['message'])) {
				echo $_SESSION['message'];
				// Clear the message after showing it
				unset($_SESSION['message']);
			}
		?>
	</p>

	<!-- Display debugging information -->
	<h3>Query Debugging:</h3>
	<p><strong>SQL Query:</strong></p>
	<pre><?php echo $_SESSION['query'] ?? 'No query executed'; ?></pre>

	<p><strong>Query Result:</strong></p>
	<pre><?php echo $_SESSION['result'] ?? 'No result available'; ?></pre>
	
</body>
</html>
