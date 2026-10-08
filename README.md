# LEMP-stack-server

Linux, nginx, MariaDB/Mysql, php stack server.

This repo has php code for a full registering and login system with password hashing before storing and a dashboard with a live global chat when logged in.

Almost a MVC in straight php.

Also a sqlinj.php page which sql injection can occur, aka how you definitely dont wanna code a login page.









- - -

# Get Started

a few things first:
you will need sudo (or the necessary/required perms to do everything)
this is for an ubuntu machine so some cmds like "apt" and package names like "mysql-server" may differ on your distro
note that all of the following is for a http only server
if u have apache/apache2 already on your machine, it may help to disable apache
if u have issues or get stuck, consult a LLM, i recommend https://duck.ai , AI helped myself a lot

### Setting up Nginx
u will need to set up nginx and the configuration files for it which should be at this directory:
```text
/etc/nginx/
```

you will need to get the default nginx page working before continuing, there are many good yt vids to help you with that and many more places where it can describe how to setup and nginx server way better than i can. https://nginx.org/en/docs/


the 3 files that are important are:
nginx.conf, sites-enable/default and sites-available/default

in here the file "nginx.conf" is the same as default, so no need to change it

for the other 2 you can just copy the follow example of both identical files for /etc/nginx/sites-enabled/default and /etc/nginx/sites-available/default:
```text
server {
    listen 80 default_server;
    listen [::]:80 default_server;
    
    root /var/www/html;

    # Add index.php to the list if you are using PHP
    index index.html index.htm index.nginx-debian.html index.php;

    server_name _;

    location / {
        # First attempt to serve request as file, then
        # as directory, then fall back to displaying a 404.
        try_files $uri $uri/ =404;
    }

    # pass PHP scripts to FastCGI server
    #
    location ~ \.php$ {
        include snippets/fastcgi-php.conf;

        # With php-fpm (or other unix sockets):
        fastcgi_pass unix:/run/php/php8.3-fpm.sock;
    }
}
```
mostly the default with a few small additions

and that should be nginx ready to go

you can just check if the syntax is all good:
```bash
sudo nginx -t
```

and reload nginx via:
```bash
sudo systemctl reload nginx
```







- - -

### Setting up PHP

get php, version being used here is php8.3, your version may different just change the number for the cmds:
```bash

```












- - -

### Setting up the MySQL databases

get mysql:
```bash
sudo apt-get install mysql-server
```

now we will setup the mysql db:

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
and also give perms to "user" to that db and flush perms similarly as above and you can exit the mysql terminal

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
now the chat db is created and is ready to store chat



WIP
