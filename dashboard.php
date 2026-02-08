<?php //dashboard.php    the dashboard page

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

// Check if the user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    // If not logged in, redirect to the login page
    header("Location: login.php");
    exit; // Stop further script execution
}

// Get the logged-in user's email from the session
$email = $_SESSION['email'];
$id = $_SESSION['id'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>Dashboard</title>
	
	<link rel="stylesheet" href="color.css">
	
	<style>
	
        /* Simple styling for the top-right user display and sign out button */
        .logout-btn {
		background-color: #f44336; /* Red color for the sign-out button */
		color: white;
		padding: 10px 20px;
		border: none;
		cursor: pointer;
		border-radius: 5px;
        }
        .logout-btn:hover {
		background-color: #d32f2f; /* Darker red when hovered */
        }

	
	</style>
</head>
<body>
	<!-- Displaying the logged-in user's email at the top right -->
	<div class="div" style="width:auto;min-width:200px;position: absolute; top: 0px; right: 0px">
		<p>Logged in as: <br><strong><?= htmlspecialchars($email) ?></strong></p>
		<button style="width:60%;margin-left:20%;margin-right:20%" class="logout-btn" onclick="confirmLogout()">Sign Out</button>
	</div>
	
	<!-- Displaying the Admin Panel -->
	<?php
	// Check if the user is allowed to view the admin panel

	// Assuming $perms is set to the logged-in user's permission level
	$loggedInUserPerms = $_SESSION['perms'];  // Adjust this based on how your session works

	if ($loggedInUserPerms != 0) {
	    // If the user doesn't have perms == 0, hide the admin panel
	    echo '<div style="position: absolute; right: 32px; top: 50%;">You do not have permission to view the admin panel.</div>';
	} else {
	    // Admin Panel visible to users with perms == 0
	?>
	
	    <div style="position: fixed; right: 16px; top: 25%; width: 35%; padding: 8px;background-color: #f5f5f5; border: 2px solid #000;">
		<h3>Admin Panel</h3>

		<?php
		// Handle POST requests for adding, deleting, and editing rows

		if ($_SERVER['REQUEST_METHOD'] == 'POST') {
		    // Check what action is being performed
		    if (isset($_POST['add_user'])) {
			// Add a new user
			$email = $_POST['email'];
			$password = password_hash($_POST['password'], PASSWORD_DEFAULT);
			$perms = $_POST['perms'];
			$stmt = $pdo->prepare("INSERT INTO users (email, password, perms) VALUES (?, ?, ?)");
			if ($stmt->execute([$email, $password, $perms])) {
				echo "<p style='color: green;'>User added successfully!</p>";
			} else {
				echo "<p style='color: red;'>Failed to add user.</p>";
			}
		    }

			if (isset($_POST['delete_user'])) {
				// Get the user ID to delete
				$user_id = $_POST['id'];
				
				if ($user_id == $id) {
					echo "<p style='color: red;'>Cannot delete your own account ID: {$user_id}. Deletion failed.</p>";
				} else {

					// Prepare the DELETE query
					$stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");

					// Execute the query
					$stmt->execute([$user_id]);

					// Check how many rows were affected
					if ($stmt->rowCount() > 0) {
						echo "<p style='color: green;'>User deleted successfully!</p>";
					} else {
						echo "<p style='color: red;'>No user found with ID {$user_id}. Deletion failed.</p>";
					}
				}
			}

			if (isset($_POST['edit_perms'])) {
			
				// Get the user ID and the new permissions
				$user_id = $_POST['id'];
				$new_perms = $_POST['perms'];
				
				if ($user_id == $id) {
					echo "<p style='color: red;'>Cannot edit your own account ID: {$user_id}. Permissions not updated.</p>";
				} else {
				
					// Prepare the UPDATE query
					$stmt = $pdo->prepare("UPDATE users SET perms = ? WHERE id = ?");

					// Execute the query
					$stmt->execute([$new_perms, $user_id]);

					// Check how many rows were affected
					if ($stmt->rowCount() > 0) {
						echo "<p style='color: green;'>Permissions updated successfully!</p>";
					} else {
						echo "<p style='color: red;'>No user found with ID: {$user_id}. Permissions not updated.</p>";
					}
				}
			}
		    
		}

		// Display the form for adding a new user
		?>
		
		<div class="mycontainer">
		
		<div>
		
			<h4>Add User</h4>
			
			<form method="POST">
				<label for="email">Email:</labe><br>
				<input type="email" name="email" placeholder="user@example.com" required><br><br>
				<label for="password">Password:</labe><br>
				<input type="password" name="password" placeholder="passw0rd" required><br><br>
				<label for="perms">Permissions (0 or 1):</label><br>
				<input type="number" name="perms" min="0" max="1" placeholder="1" required><br><br>
				<button type="submit" name="add_user">Add User</button>
			</form>

		</div>
		
		<div>
		
			<h4>Delete User</h4>
			
			<form method="POST">
				<label for="id">ID:</labe><br>
				<input type="id" name="id" placeholder="2" required><br><br>
				<button type="submit" name="delete_user">Delete User</button>
			</form>

		</div>
		
		<div>
		
			<h4>Edit User Perms</h4>
			
			<form method="POST">
				<label for="id">ID:</labe><br>
				<input type="id" name="id" placeholder="2" required><br><br>
				<label for="perms">Permissions (0 or 1):</label><br>
				<input type="number" name="perms" min="0" max="1" placeholder="1" required><br><br>
				<button type="submit" name="edit_perms">Edit User Perms</button>
			</form>

		</div>
		
		</div> <!--mycontainer-->
		
	    </div>
	    <?php
	}
	?>

	<h2>Dashboard</h2>
	    

	
	<?php
	
//Debugging output
//echo '<pre style="">';
//print_r($_SESSION);
//echo '</pre>';
	
	$perms = $_SESSION['perms'];

	// Fetch users from the database
	$stmt = $pdo->query('SELECT * FROM users');

	// Check if there are any results
	if ($stmt->rowCount() > 0) {
	
		// Output table header
		echo "<table border='1' cellpadding='8' cellspacing='0' style='width:60%;border-collapse: collapse;'>";
		echo "<thead><tr><th>ID</th><th>Email</th>";

		// Show password and perms columns only if logged-in user has perms 0
		if ($perms === 0) {
			echo "<th>Password (Hashed)</th><th>Perms</th>";
		}

		echo "</tr></thead>";
		echo "<tbody>";

		// Loop through the results and output each row
		while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
			echo "<tr>";
			echo "<td>{$row['id']}</td>";
			echo "<td>{$row['email']}</td>";

			// Show password and perms only if logged-in user has perms 0
			if ($perms === 0) {
			echo "<td>{$row['password']}</td>";
			echo "<td>{$row['perms']}</td>";
			}

			echo "</tr>";
		}

		echo "</tbody></table>";
	} else {
		echo "No users found.";
	}
	
	
	
	?>

	<script>
		function confirmLogout() {
			if (confirm("Are you sure you want to log out?")) {
				window.location.href = "logout.php";
			}
		}
	</script>

</body>
</html>
