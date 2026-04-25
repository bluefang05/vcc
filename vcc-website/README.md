# VCC CMS - Complete Content Management System

A WordPress-like CMS built for Virtual Communication Connection (VCC) with full backend management, blog system, media library, and contact form handling.

## Features

### Frontend
- Responsive informative website
- Dynamic content from database
- Contact form with WhatsApp integration
- Blog section for SEO
- Brand colors (Deep Blue #0a2540, Cyan #00d4d4)

### Backend Admin Panel
- **Dashboard** - Site statistics and quick actions
- **User Roles** - Super Admin, Admin, Editor, Author
- **Blog Management** - Create, edit, delete posts with SEO fields
- **Media Library** - Upload and manage images/files
- **Messages** - View contact form submissions in backend (not email)
- **Settings** - Dynamically change site content
- **Activity Log** - Track all admin actions
- **Categories** - Organize blog posts

## Installation

1. **Upload Files**
   - Upload all files to your web server
   - Ensure the `uploads/` directory is writable (chmod 755 or 777)

2. **Run Installer**
   - Visit your domain (e.g., `https://yourdomain.com`)
   - You'll be automatically redirected to the installer
   - Or visit `https://yourdomain.com/install.php`

3. **Complete Installation Form**
   - Enter site information (title, URL)
   - Database credentials (host, name, user, password)
   - Create admin account (username, email, password)
   - Click "Install VCC CMS"

4. **Login to Admin**
   - After installation, you'll be redirected to `/admin/login.php`
   - Login with your admin credentials
   - Default: username you created during installation

## File Structure

```
vcc-website/
├── index.php              # Main frontend (dynamic content)
├── install.php            # Installation wizard
├── config.php             # Auto-generated configuration
├── contact-handler.php    # Contact form processor
├── script.js              # Frontend JavaScript
├── styles.css             # Frontend styles
├── README.md              # This file
├── uploads/               # Media uploads directory
├── assets/
│   └── logo.svg           # Site logo
└── admin/
    ├── index.php          # Dashboard
    ├── login.php          # Admin login
    ├── logout.php         # Logout handler
    ├── messages.php       # Contact messages manager
    ├── posts.php          # Blog posts manager (to be created)
    ├── media.php          # Media library (to be created)
    ├── settings.php       # Site settings (to be created)
    └── includes/
        ├── header.php     # Admin header/sidebar
        └── footer.php     # Admin footer
```

## Database Tables

- `admin_users` - Admin accounts with roles
- `blog_posts` - Blog articles with SEO fields
- `categories` - Post categories
- `post_categories` - Post-category relationships
- `media_library` - Uploaded files metadata
- `contact_messages` - Contact form submissions
- `settings` - Site configuration
- `activity_log` - Admin activity tracking

## Security Features

- Password hashing (bcrypt)
- Session-based authentication
- Role-based access control
- SQL injection prevention (prepared statements)
- XSS protection (htmlspecialchars)
- CSRF protection ready
- Activity logging

## User Roles

1. **Super Admin** - Full access including user management
2. **Admin** - Manage content, settings, messages
3. **Editor** - Create/edit/delete posts and media
4. **Author** - Create and edit own posts

## Customization

### Change Site Settings
Login to admin panel → Settings to modify:
- Site title and contact info
- Hero section text
- About content
- Social media links
- Phone and WhatsApp numbers

### Add Blog Posts
Admin → Posts → New Post
- Add title, content, excerpt
- Set featured image
- Configure SEO meta tags
- Assign categories

### Manage Messages
Admin → Messages
- View all contact form submissions
- Filter by status (New, Read, Archived)
- Mark as read/archive/delete
- Reply directly via WhatsApp

## Support

For questions or issues, contact:
- Email: info@vcc.com
- WhatsApp: +1 809-586-6653
- Address: Margaria mears 18, Puerto Plata, Dominican Republic 57000

## License

Proprietary - Virtual Communication Connection (VCC)
