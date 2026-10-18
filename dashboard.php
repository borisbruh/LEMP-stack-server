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

// Get the logged-in user's username & email from the session
$username = $_SESSION['username'];
$email = $_SESSION['email'];
$id = $_SESSION['id'];
$perms = isset($_SESSION['perms']) ? (int)$_SESSION['perms'] : 0;


// Check perms of logged in user
if ($perms != 10) {
    // If not admin, redirect to the user page
    header("Location: user.php");
    exit; // Stop further script execution
}
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
                <p>Welcome: <strong><?= htmlspecialchars($username) ?></strong></p>
                <p>Logged in as: <br><strong><?= htmlspecialchars($email) ?></strong></p>
                <button style="width:60%;margin-left:20%;margin-right:20%" class="logout-btn" onclick="confirmLogout()">Sign Out</button>
        </div>

        <!-- Displaying the Admin Panel -->
        <?php
        if ($perms !== 10) {
            echo '<div style="position: absolute; right: 32px; top: 50%;">You do not have permission to view the admin panel.</div>';
        } else {
        ?>

            <div style="position: fixed; right: 16px; top: 25%; width: 35%; padding: 8px;background-color: #f5f5f5; border: 2px solid #000;">
                <h3>Admin Panel</h3>

                <?php
                if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                    if (isset($_POST['add_user'])) {
                        $username = $_POST['username'];
                        $email = $_POST['email'];
                        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
                        $perms_post = $_POST['perms'];
                        $full_name = $_POST['full_name'];
                        $contact_details = $_POST['contact_details'];
                        $stmt = $pdo->prepare("INSERT INTO users (username, email, password, perms, full_name, contact_details) VALUES (?, ?, ?, ?, ?, ?)");
                        if ((!filter_var($email, FILTER_VALIDATE_EMAIL))) {
                                echo "<p style='color: red;'>Invalid email format, new user not added.";
                        } elseif ($stmt->execute([$username, $email, $password, $perms_post, $full_name, $contact_details])) {
                                echo "<p style='color: green;'>User added successfully!</p>";
                        } else {
                                echo "<p style='color: red;'>Failed to add user.</p>";
                        }
                    }

                    if (isset($_POST['delete_user'])) {
                        $user_id = $_POST['id'];

                        if ($user_id == $id) {
                                echo "<p style='color: red;'>Cannot delete your own account ID: {$user_id}. Deletion failed.</p>";
                        } else {
                                $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
                                $stmt->execute([$user_id]);

                                if ($stmt->rowCount() > 0) {
                                        echo "<p style='color: green;'>User deleted successfully!</p>";
                                } else {
                                        echo "<p style='color: red;'>No user found with ID {$user_id}. Deletion failed.</p>";
                                }
                        }
                    }

                    if (isset($_POST['edit'])) {
                        $userId = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
                        $column = $_POST['column'] ?? '';
                        $newValue = $_POST['value'] ?? '';
                    
                        // Only allow columns you intend users to edit.
                        $allowedColumns = ['username', 'email', 'full_name', 'contact_details', 'perms'];
                    
                        if (!$userId || !in_array($column, $allowedColumns, true)) {
                            echo "<p style='color: red;'>Invalid user ID or column.</p>";
                        } elseif ($userId == $id) {
                            echo "<p style='color: red;'>Cannot edit your own account.</p>";
                        } else {
                            $stmt = $pdo->prepare("UPDATE users SET `$column` = ? WHERE id = ?");
                            $stmt->execute([$newValue, $userId]);
                    
                            if ($stmt->rowCount() > 0) {
                                echo "<p style='color: green;'>User updated successfully.</p>";
                            } else {
                                echo "<p style='color: red;'>No row was changed. The user may not exist, or the value may already be the same.</p>";
                            }
                        }
                    }

                }
                ?>

                <div class="mycontainer">

                <div>

                        <h4>Add User</h4>

                        <form method="POST">
                                <label for="username">Username:</label><br>
                                <input type="text" name="username" placeholder="user2" required><br><br>
                            
                                <label for="email">Email:</label><br>
                                <input type="email" name="email" placeholder="user@example.com" required><br><br>
                            
                                <label for="password">Password:</label><br>
                                <input type="password" name="password" placeholder="passw0rd" required><br><br>
                            
                                <label for="perms">Permissions (0-10):</label><br>
                                <input type="number" name="perms" min="0" max="10" placeholder="0" required><br><br>

                                <label for="full_name">Full Name:</label><br>
                                <input type="text" name="full_name" placeholder="John Smith" required><br><br>

                                <label for="contact_details">Contact Details:</label><br>
                                <input type="text" name="contact_details" placeholder="098 765 4321" required><br><br>
                            
                                <button type="submit" name="add_user">Add User</button>
                        </form>

                </div>

                <div>

                        <h4>Delete User</h4>

                        <form method="POST">
                                <label for="id">ID:</label><br>
                                <input type="id" name="id" placeholder="2" required><br><br>
                                <button type="submit" name="delete_user">Delete User</button>
                        </form>

                </div>

                <div>

                <h4>Edit User</h4>
                
                <form method="POST">
                    <label for="id">User ID:</label><br>
                    <input type="number" name="id" id="id" min="1" max="999999" placeholder="2" required><br><br>
                
                    <label for="column">Column to edit:</label><br>
                    <select name="column" id="column" required>
                        <option value="username">Username</option>
                        <option value="email">Email</option>
                        <option value="full_name">Full name</option>
                        <option value="contact_details">Contact details</option>
                        <option value="perms">Permissions</option>
                    </select><br><br>
                
                    <label for="value" id="valueLabel">New value:</label><br>
                    <input type="text" name="value" id="value" maxlength="64" required><br><br>
                
                    <button type="submit" name="edit">Edit User</button>
                </form>
                
                <script>
                const columnSelect = document.getElementById('column');
                const valueInput = document.getElementById('value');
                const valueLabel = document.getElementById('valueLabel');
                
                function updateValueInput() {
                    const settings = {
                        username:          { type: 'text',  maxLength: 64 },
                        email:              { type: 'email', maxLength: 128 },
                        full_name:          { type: 'text',  maxLength: 150 },
                        contact_details:    { type: 'text',  maxLength: 256 },
                        perms:              { type: 'number', min: 0, max: 10 }
                    };
                
                    const setting = settings[columnSelect.value];
                
                    valueInput.type = setting.type;
                    valueInput.removeAttribute('maxlength');
                    valueInput.removeAttribute('min');
                    valueInput.removeAttribute('max');
                
                    if (setting.maxLength) valueInput.maxLength = setting.maxLength;
                    if (setting.min !== undefined) valueInput.min = setting.min;
                    if (setting.max !== undefined) valueInput.max = setting.max;
                
                    valueLabel.textContent =
                        columnSelect.value === 'perms' ? 'New permissions value:' : 'New value:';
                }
                
                columnSelect.addEventListener('change', updateValueInput);
                updateValueInput();
                </script>


                </div>

                </div>

            </div>
            <?php
        }
        ?>

        <h2>Dashboard</h2>

        <?php
        $stmt = $pdo->query('SELECT * FROM users');

        if ($stmt->rowCount() > 0) {
                echo "<table border='1' cellpadding='8' cellspacing='0' style='width:60%;border-collapse: collapse;'>";
                echo "<thead><tr><th>ID</th><th>Email</th><th>Username</th>";

                if ($perms === 10) {
                        echo "<th>Perms</th>";
                }

                echo "<th>Full name</th><th>Contact Details</th>";

                echo "</tr></thead>";
                echo "<tbody>";

                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        echo "<tr>";
                        echo "<td>{$row['id']}</td>";
                        echo "<td>{$row['email']}</td>";
                        echo "<td>{$row['username']}</td>";

                        if ($perms === 10) {
                        echo "<td>{$row['perms']}</td>";
                        }
                        
                        echo "<td>{$row['full_name']}</td>";
                        echo "<td>{$row['contact_details']}</td>";
                    
                        echo "</tr>";
                }

                echo "</tbody></table>";
        } else {
                echo "No users found. (how tf did u log in?)";
        }
        ?>

        <?php if ($perms === 10): ?>
        <div id="chat-box">

            <div id="chat-header">
                Chat
            </div>

            <div id="chat-messages">
                <div class="chat-loading">
                    Loading messages...
                </div>
            </div>

            <form id="chat-form">
                <input
                    type="text"
                    id="chat-input"
                    name="message"
                    maxlength="128"
                    autocomplete="off"
                    placeholder="Type a message..."
                    required
                >

                <button type="submit">Send</button>
            </form>

            <div id="chat-error"></div>

        </div>
        <?php else: ?>
            <div style="margin-top: 16px;">You do not have permission to view the chat.</div>
        <?php endif; ?>

        <script>
                function confirmLogout() {
                        if (confirm("Are you sure you want to log out?")) {
                                window.location.href = "logout.php";
                        }
                }

                let lastMessageId = 0;
                let chatLoading = false;

                function escapeHtml(text) {
                    const div = document.createElement('div');
                    div.textContent = text;
                    return div.innerHTML;
                }

                function addChatMessage(message, scroll = true) {
                    const container = document.getElementById('chat-messages');
                    if (!container) return;

                    const div = document.createElement('div');
                    div.className = 'chat-message';

                    const username = escapeHtml(message.user);
                    const text = escapeHtml(message.message);
                    const time = new Date(message.time.replace(' ', 'T'));

                    let timeText = '';
                    if (!isNaN(time.getTime())) {
                        timeText = time.toLocaleTimeString([], {
                            hour: '2-digit',
                            minute: '2-digit'
                        });
                    }

                    div.innerHTML =
                        '<span class="chat-username">' + username + ':</span> ' +
                        '<span class="chat-text">' + text + '</span>' +
                        '<span class="chat-time">' + timeText + '</span>';

                    container.appendChild(div);

                    if (scroll) {
                        container.scrollTop = container.scrollHeight;
                    }
                }

                async function loadInitialMessages() {
                    try {
                        const response = await fetch('chat_api.php');
                        if (!response.ok) throw new Error('Failed to load chat.');

                        const data = await response.json();
                        if (!data.success) throw new Error(data.error || 'Failed to load chat.');

                        const container = document.getElementById('chat-messages');
                        if (!container) return;

                        container.innerHTML = '';

                        data.messages.forEach(message => {
                            addChatMessage(message, false);
                            if (Number(message.msgid) > lastMessageId) {
                                lastMessageId = Number(message.msgid);
                            }
                        });

                        container.scrollTop = container.scrollHeight;
                    } catch (error) {
                        const container = document.getElementById('chat-messages');
                        if (container) {
                            container.innerHTML = '<div class="chat-loading">Unable to load chat.</div>';
                        }
                        console.error(error);
                    }
                }

                async function checkForNewMessages() {
                    if (chatLoading) return;
                    chatLoading = true;

                    try {
                        const response = await fetch('chat_api.php?after=' + encodeURIComponent(lastMessageId), {
                            cache: 'no-store'
                        });

                        if (!response.ok) throw new Error('Failed to check chat.');

                        const data = await response.json();
                        if (!data.success) throw new Error(data.error || 'Failed to check chat.');

                        data.messages.forEach(message => {
                            addChatMessage(message, true);
                            if (Number(message.msgid) > lastMessageId) {
                                lastMessageId = Number(message.msgid);
                            }
                        });
                    } catch (error) {
                        console.error(error);
                    } finally {
                        chatLoading = false;
                    }
                }

                const chatForm = document.getElementById('chat-form');
                if (chatForm) {
                    chatForm.addEventListener('submit', async function(event) {
                        event.preventDefault();

                        const input = document.getElementById('chat-input');
                        const errorBox = document.getElementById('chat-error');
                        const message = input.value;

                        errorBox.textContent = '';

                        if (message.length === 0) return;
                        if (message.length > 128) {
                            errorBox.textContent = 'Message must be 128 characters or fewer.';
                            return;
                        }

                        if (!/^[0-9A-Za-z .,!?]+$/.test(message)) {
                            errorBox.textContent = 'Only numbers, letters, spaces, periods, commas, ! and ? are allowed.';
                            return;
                        }

                        try {
                            const formData = new FormData();
                            formData.append('message', message);

                            const response = await fetch('chat_api.php', {
                                method: 'POST',
                                body: formData
                            });

                            const data = await response.json();
                            if (!response.ok || !data.success) {
                                errorBox.textContent = data.error || 'Failed to send message.';
                                return;
                            }

                            input.value = '';
                            await checkForNewMessages();
                            input.focus();
                        } catch (error) {
                            console.error(error);
                            errorBox.textContent = 'Could not send message.';
                        }
                    });
                }

                if (document.getElementById('chat-messages')) {
                    loadInitialMessages();
                    setInterval(checkForNewMessages, 20000);
                }
        </script>

</body>
</html>
