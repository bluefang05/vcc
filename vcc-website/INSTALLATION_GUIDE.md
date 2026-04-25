# VCC CMS Installation Guide

## Quick Start

1. **Upload Files**: Upload all files to your web server
2. **Access Installer**: Visit `http://yourdomain.com/install.php`
3. **Fill Form**: Enter database credentials and admin account info
4. **Click Install**: Press the "🚀 Install VCC CMS" button
5. **Login**: You'll be redirected to `http://yourdomain.com/vcc-portal/login.php`

## Admin Panel Access

- **URL**: `http://yourdomain.com/vcc-portal/`
- **Default Username**: (whatever you set during installation)
- **Default Password**: (whatever you set during installation)

## Important Notes

### Security
- The admin panel is located at `/vcc-portal/` (not `/admin/`) for security
- Change default credentials immediately after installation
- Delete `install.php` after successful installation

### Database Requirements
- MySQL 5.7+ or MariaDB 10.2+
- Database user must have CREATE, INSERT, UPDATE, DELETE permissions

### File Permissions
- Server must be able to write `config.php` in the root directory
- `uploads/` folder must be writable (755 or 777 permissions)

## Features Included

✅ Multi-user system with roles (Super Admin, Admin, Editor, Author)
✅ Blog management with SEO fields
✅ Media library
✅ Contact form messages (stored in database, not email)
✅ Multi-language support (English/Spanish)
✅ Google Analytics integration
✅ Automated database backups
✅ Maintenance mode
✅ Activity logging
✅ Secure authentication with password hashing

## Troubleshooting

### Button Not Working
- Ensure JavaScript is enabled in your browser
- Check that all required fields are filled
- Verify database credentials are correct
- Check server error logs

### Config.php Not Created
- Ensure root directory is writable
- Manually create config.php using the template shown after installation

### Cannot Access Admin Panel
- Clear browser cache and cookies
- Ensure you're accessing `/vcc-portal/` not `/admin/`
- Check if maintenance mode is enabled in settings

## Support

For technical support, contact the development team.
