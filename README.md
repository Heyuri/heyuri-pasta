## Installation

### Requirements
- PHP 8.1+
- MySQL 5.7+ / MariaDB 10.3+
- Composer

---

### 1. Clone the repository

```bash
git clone https://github.com/Heyuri/heyuri-pasta.git /var/www/sites/yoursite/heyuri-pasta
cd /var/www/sites/yoursite/heyuri-pasta
```

---

### 2. Install dependencies

```bash
composer install --no-dev
```

---

### 3. Configure

Edit `private/config.ini` with your database credentials, site name, and public URL:

```ini
[site]
name = My Pasta Site

[database]
database_name     = pasta
database_user     = pasta_user
database_password = yourpassword
database_host     = localhost
database_port     = 3306

[server]
url      = "/"
home_url = "/"
```

---

### 4. Create the MySQL database and user

```sql
CREATE DATABASE pasta CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'pasta_user'@'localhost' IDENTIFIED BY 'yourpassword';
GRANT ALL PRIVILEGES ON pasta.* TO 'pasta_user'@'localhost';
FLUSH PRIVILEGES;
```

---

### 5. Run the installer

Creates the tables and a default admin account (`admin` / `admin`).

```bash
php private/install.php
```

> **Log in at `?route=moderateRoute` and change the default password immediately** via the Account page in [Moderate].

---

### 6. Set ownership and permissions

Replace `youruser` with your deploy user and `www-data` with your web server group.

```bash
chown -R youruser:www-data /var/www/sites/yoursite/heyuri-pasta

# Directories: owner rwx, group rx, others nothing
find /var/www/sites/yoursite/heyuri-pasta -type d -exec chmod 750 {} \;

# Files: owner rw, group r, others nothing
find /var/www/sites/yoursite/heyuri-pasta -type f -exec chmod 640 {} \;

# private/ needs group write so PHP can append to audit.log
chmod 770 /var/www/sites/yoursite/heyuri-pasta/private
```

---

### 7. Block web access to `private/`

**Nginx** — add inside your `server {}` block:

```nginx
location ^~ /private/ {
    deny all;
    return 404;
}
```

**Apache** — create `private/.htaccess` and add to its contents:

```apache
Require all denied
```

---

### 8. Set up the expiry cron job

Pastes with a TTL are pruned every 5 minutes. Run `crontab -e` and add:

```
*/5 * * * * php /var/www/sites/yoursite/heyuri-pasta/private/cron/pruneExpiredPastes.php >> /var/www/sites/yoursite/heyuri-pasta/private/prune.log 2>&1
```