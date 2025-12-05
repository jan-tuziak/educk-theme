# educk-theme

educk-theme is a WordPress theme for [educk.pl](https://educk.pl) and [educk.org](https://educk.org).

## Creating a local copy of the website

ssh educkdesign@educkdesign.ssh.dhosting.pl

zip -r educkpl.zip . -x *.mp4 >/dev/null

mysqldump -h "educkdesign.mysql.dhosting.pl" -u "oe3osa_educkdfi" --password="cu1le2co8Iiv" "yaung4_educkdfi" > "educkpl.sql"

Copy files to local via FTP, and unzip the zip file

mysql -u root

```sql
CREATE DATABASE educk_pl_local DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE USER 'educk_local'@'localhost' IDENTIFIED BY 'super_secret_password';

GRANT ALL PRIVILEGES ON educk_pl_local.* TO 'educk_local'@'localhost';
FLUSH PRIVILEGES;

EXIT;
```

mysql -u educk_local -p educk_pl_local < educkpl.sql


In `wp-config.php` look for these lines and change them to:

```php
define( 'DB_NAME', 'educk_pl_local' );
define( 'DB_USER', 'educk_local' );
define( 'DB_PASSWORD', 'super_secret_password' );
define( 'DB_HOST', '127.0.0.1' ); // or 'localhost'
```

wp search-replace 'https://educk.pl' 'http://localhost:8000' --skip-columns=guid

wp search-replace 'http://educk.pl' 'http://localhost:8000' --skip-columns=guid

wp plugin deactivate really-simple-ssl

wp cache flush

php -S localhost:8000
