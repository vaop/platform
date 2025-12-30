# Installation Guide

This guide walks you through installing VAOP on shared hosting (cPanel, Plesk, DirectAdmin) or a VPS.

## Shared Hosting (cPanel)

Most VA owners use shared hosting. We recommend using a subdomain (e.g., `crew.yourva.com`) which makes setup easier.

### 1. Download VAOP

Download the latest release from the [releases page](https://github.com/vaop/platform/releases). Choose the `.zip` file.

### 2. Upload Files

1. Log into cPanel and open **File Manager**
2. Navigate to your home folder (`/home/yourusername/`)
3. Create a new folder called `vaop`
4. Open the `vaop` folder, upload the `.zip` file, and extract it
5. Make sure the files are directly in `/home/yourusername/vaop/` (not in a subfolder)

### 3. Create a Subdomain

1. In cPanel, go to **Domains** or **Subdomains**
2. Create a new subdomain (e.g., `crew.yourva.com`)
3. Set the **Document Root** to `/home/yourusername/vaop/public`
4. Save the subdomain

This points your subdomain directly to VAOP's public folder.

> **Tip:** You can also use your main domain. In **Domains**, edit your primary domain and change its document root to `/home/yourusername/vaop/public`.

**Alternative: Using a Symlink**

If your hosting doesn't allow changing the document root, you can use a symlink instead:

1. Rename `public_html` to `public_html_backup`
2. In cPanel **Terminal** (or via SSH), run:
   ```
   ln -s /home/yourusername/vaop/public /home/yourusername/public_html
   ```

### 4. Create a Database

1. In cPanel, go to **MySQL Databases**
2. Create a new database (e.g., `yourva_vaop`)
3. Create a database user with a strong password
4. Add the user to the database with **All Privileges**
5. Note down the database name, username, and password

### 5. Run the Installer

Visit your subdomain in a web browser (e.g., `https://crew.yourva.com`). The installer will guide you through:
- Verifying requirements
- Database configuration
- Setting up your admin account
- Configuring your VA settings

### 6. Set Up Scheduled Tasks (Cron)

In cPanel, go to **Cron Jobs** and add:

```
* * * * * php /home/yourusername/vaop/artisan schedule:run >> /dev/null 2>&1
```

Replace `yourusername` with your actual cPanel username.

---

## VPS / Dedicated Server

If you have SSH access:

### 1. Download and Extract

```bash
cd /var/www
wget https://github.com/vaop/platform/releases/latest/download/vaop.zip
unzip vaop.zip -d vaop
cd vaop
```

### 2. Set Permissions

```bash
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache
```

### 3. Configure Web Server

Point your web server to the `public` folder.

**Nginx example:**
```nginx
server {
    listen 80;
    server_name crew.yourva.com;
    root /var/www/vaop/public;

    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.4-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

### 4. Run Installer

Visit your domain to complete setup.

### 5. Set Up Cron

```bash
* * * * * cd /var/www/vaop && php artisan schedule:run >> /dev/null 2>&1
```

---

## Troubleshooting

**Blank page or 500 error?**
- Verify the document root points to the `public` folder
- Check file permissions on `storage` and `bootstrap/cache`
- Check `storage/logs/laravel.log` for error details

**Database connection failed?**
- Verify database name, username, and password are correct
- Make sure the database user has privileges on the database

**Need help?**
- Open an issue on [GitHub](https://github.com/vaop/platform/issues)
