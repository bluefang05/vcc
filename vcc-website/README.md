# VCC Website - Installation Guide

## Requirements
- PHP 7.4 or higher
- MySQL 5.7 or higher (or MariaDB)
- Web server (Apache, Nginx, etc.)

## Installation Steps

### 1. Upload Files
Upload all files from this directory to your web server's document root (e.g., `public_html`, `www`, or `htdocs`).

### 2. Create Database
1. Log in to your hosting control panel (cPanel, Plesk, etc.)
2. Create a new MySQL database
3. Create a database user and assign it to the database with full privileges
4. Note down: Database name, username, and password

### 3. Run Installer
1. Open your browser and navigate to your website URL (e.g., `http://yourdomain.com`)
2. You will be automatically redirected to the installation wizard (`install.php`)
3. Fill in the required information:
   - **Site Title**: Your website name
   - **Database Host**: Usually `localhost`
   - **Database Name**: The database you created
   - **Database Username**: The database user you created
   - **Database Password**: The database user's password
4. Click "Install VCC Website"

### 4. Complete
Once installation is successful:
- A `config.php` file will be created automatically
- Database tables will be created
- You'll be redirected to your new website

## File Structure
```
vcc-website/
├── index.php           # Main website file
├── install.php         # Installation wizard
├── contact-handler.php # Contact form processor
├── styles.css          # Stylesheet
├── script.js           # JavaScript functionality
├── config.php          # Auto-generated configuration (created during install)
├── assets/             # Images, logos, etc.
└── includes/           # PHP includes (future use)
```

## Database Tables Created
- `contact_messages` - Stores contact form submissions
- `settings` - Site configuration settings

## Security Notes
- After installation, consider setting `config.php` permissions to 444 (read-only)
- Delete `install.php` after successful installation for security
- Keep your database credentials secure

## Support
For issues or questions, contact: info@vcc.com

---
**Virtual Communication Connection (VCC)**
Call Center Virtual & Outsourcing de Comunicaciones
Puerto Plata, Dominican Republic
Phone: +1 809-586-6653
