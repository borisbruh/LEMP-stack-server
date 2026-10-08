# LEMP-stack-server

Linux, nginx, MariaDB/Mysql, php stack server.

This repo has the php code for a full registering and login system with password hashing before storing and a dashboard when loged in.

Almost a MVC in straight php.

Also a sqlinj.php page which sql injection can occur, aka how you definitly don twanna code a login page.









- - -

# Get Started

u will need to setup nginx and the conf file for it which should be at:
```text
/etc/nginx/
```

- - -
### Setting up the mysql databases

u will also need to setup the mysql db:

using sudo, go to the mysql terminal via:
```bash
sudo mysql
```

create db called "db":
```sql
CREATE DATABASE db;
```

then we are going to give a user that isn't root, perms to db:
```sql
GRANT ALL PRIVILEGES ON db.* TO 'user'@'localhost';
```

then flush perms:
```sql
FLUSH PRIVILEGES;
```

then exit the mysql terminal;
```bash
exit
```

log back in as user "user" to db "db":
```bash
mysql -u user -p db
```
-u, --user=name         User for login if not current user.
-p, --password[=name]    Password to use when connecting to server. If password is not given it's asked from the tty.

now logged in as "user" you can create the table "users":
```sql
CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(64) NOT NULL,
  email VARCHAR(128) NOT NULL,
  hash VARCHAR(256) NOT NULL,
  perms INT NOT NULL DEFAULT 0
);
```
now the db is ready to take new acc registrations and you can exit the terminal now

now we will create the second db for all the chats so log back in as sudo:
```sql
CREATE DATABASE chat     CHARACTER SET utf8mb4     COLLATE utf8mb4_unicode_ci;
```
and also give perms to "user" to that db similarly as above and you can exit the mysql terminal

then we will log back in as "user" and create the table:
```sql
CREATE TABLE messages (
  msgid BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `time` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `user` VARCHAR(64) NOT NULL,
   message VARCHAR(128) NOT NULL,
   PRIMARY KEY (msgid),
   INDEX idx_msgid (msgid)
)
ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;
```
now the chat db is made and is ready
