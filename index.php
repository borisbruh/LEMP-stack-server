<?php	//index.php	just to see if everything works

$host = 'localhost';
$db   = 'test_database';	//name of the sql db
$user = 'user';			//name of sql db user that has perms to acces the db
$pass = 'passw0rd';)		//password for the user
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

try {
    $pdo = new PDO($dsn, $user, $pass);
    echo "<h2>✅ Connected to MariaDB successfully!</h2>";

    // Fetch users
    $stmt = $pdo->query('SELECT * FROM users');
    echo "<ul>";
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "<li>ID: {$row['id']}, Name: {$row['name']}</li>";
    }
    echo "</ul>";

} catch (PDOException $e) {
    echo "<h2>❌ Connection failed:</h2>";
    echo $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<body>
	<br><br>
	<a href="login.php">Login page</a>
</body>
</html>
