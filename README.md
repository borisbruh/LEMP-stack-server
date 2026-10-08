# LEMP-stack-server

Linux, nginx, MariaDB/Mysql, php stack server.

This repo has the php code for a full registering and login system with password hashing before storing and a dashboard when loged in.

Almost a MVC in straight php.

Also a sqlinj.php page which sql injection can occur, aka how you definitly don twanna code a login page.











Get Started:

u will need to setup nginx and the conf file for it which should be at:
```text
/etc/nginx/
```


u will also need to setup the mysql db:

using sudo, go to the mysql terminal via:
```bash
sudo mysql
```

create db called "db":
```text
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
