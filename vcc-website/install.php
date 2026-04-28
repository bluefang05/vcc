<?php
/**
 * VCC Website Installer
 * A WordPress-like installation script for the VCC informative site
 */

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Get the directory where this script is located
$baseDir = __DIR__;

// Prevent direct access if already installed
if (file_exists($baseDir . '/config.php')) {
    die('The site is already installed. Delete config.php to reinstall.');
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['install_now'])) {
    $db_host = trim($_POST['db_host'] ?? 'localhost');
    $db_name = trim($_POST['db_name'] ?? '');
    $db_user = trim($_POST['db_user'] ?? '');
    $db_pass = $_POST['db_pass'] ?? '';
    $site_title = trim($_POST['site_title'] ?? 'Virtual Communication Connection');
    $site_url = trim($_POST['site_url'] ?? '');
    $admin_username = trim($_POST['admin_username'] ?? 'admin');
    $admin_password = $_POST['admin_password'] ?? '';
    $admin_email = trim($_POST['admin_email'] ?? '');
    
    // Validate required fields
    if (empty($db_name) || empty($db_user)) {
        $error = 'Database name and username are required.';
    } elseif (empty($admin_username) || empty($admin_password)) {
        $error = 'Admin username and password are required.';
    } else {
        // Test database connection
        try {
            $dsn = "mysql:host=$db_host;dbname=$db_name;charset=utf8mb4";
            $pdo = new PDO($dsn, $db_user, $db_pass);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Create tables if needed
            createTables($pdo, $admin_username, $admin_password, $admin_email);
            
            // Generate config file
            $configContent = generateConfig($db_host, $db_name, $db_user, $db_pass, $site_title, $site_url);
            
            if (file_put_contents($baseDir . '/config.php', $configContent)) {
                $success = true;
            } else {
                $error = 'Could not write config.php. Please check file permissions.';
            }
        } catch (PDOException $e) {
            $error = 'Database connection failed: ' . $e->getMessage();
        }
    }
}

function createTables($pdo, $admin_username, $admin_password, $admin_email) {
    // Create admin users table with roles
    $sql = "CREATE TABLE IF NOT EXISTS admin_users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) UNIQUE NOT NULL,
        password_hash VARCHAR(255) NOT NULL,
        email VARCHAR(255) NOT NULL,
        role ENUM('super_admin', 'admin', 'editor', 'author') DEFAULT 'admin',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        last_login TIMESTAMP NULL,
        is_active TINYINT(1) DEFAULT 1
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    
    $pdo->exec($sql);
    
    // Create contact messages table
    $sql = "CREATE TABLE IF NOT EXISTS contact_messages (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        email VARCHAR(255) NOT NULL,
        subject VARCHAR(255),
        message TEXT NOT NULL,
        status ENUM('new', 'read', 'replied', 'archived') DEFAULT 'new',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    
    $pdo->exec($sql);
    
    // Create blog posts table
    $sql = "CREATE TABLE IF NOT EXISTS blog_posts (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        slug VARCHAR(255) UNIQUE NOT NULL,
        excerpt TEXT,
        content LONGTEXT NOT NULL,
        featured_image VARCHAR(255),
        author_id INT,
        status ENUM('draft', 'published', 'scheduled') DEFAULT 'draft',
        published_at TIMESTAMP NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        meta_title VARCHAR(255),
        meta_description TEXT,
        meta_keywords VARCHAR(500),
        views INT DEFAULT 0,
        FOREIGN KEY (author_id) REFERENCES admin_users(id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    
    $pdo->exec($sql);
    
    // Create categories table
    $sql = "CREATE TABLE IF NOT EXISTS categories (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        slug VARCHAR(100) UNIQUE NOT NULL,
        description TEXT,
        parent_id INT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (parent_id) REFERENCES categories(id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    
    $pdo->exec($sql);
    
    // Create post_categories junction table
    $sql = "CREATE TABLE IF NOT EXISTS post_categories (
        post_id INT NOT NULL,
        category_id INT NOT NULL,
        PRIMARY KEY (post_id, category_id),
        FOREIGN KEY (post_id) REFERENCES blog_posts(id) ON DELETE CASCADE,
        FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    
    $pdo->exec($sql);
    
    // Create media library table
    $sql = "CREATE TABLE IF NOT EXISTS media_library (
        id INT AUTO_INCREMENT PRIMARY KEY,
        filename VARCHAR(255) NOT NULL,
        original_name VARCHAR(255) NOT NULL,
        file_path VARCHAR(500) NOT NULL,
        file_type VARCHAR(50) NOT NULL,
        file_size INT NOT NULL,
        mime_type VARCHAR(100),
        uploaded_by INT,
        uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        alt_text VARCHAR(255),
        caption TEXT,
        FOREIGN KEY (uploaded_by) REFERENCES admin_users(id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    
    $pdo->exec($sql);
    
    // Create settings table
    $sql = "CREATE TABLE IF NOT EXISTS settings (
        id INT AUTO_INCREMENT PRIMARY KEY,
        setting_key VARCHAR(100) UNIQUE NOT NULL,
        setting_value TEXT,
        setting_type VARCHAR(20) DEFAULT 'text',
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    
    $pdo->exec($sql);
    
    // Create activity log table
    $sql = "CREATE TABLE IF NOT EXISTS activity_log (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT,
        action VARCHAR(100) NOT NULL,
        description TEXT,
        ip_address VARCHAR(45),
        user_agent VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES admin_users(id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    
    $pdo->exec($sql);
    
    // Insert default admin user
    $password_hash = password_hash($admin_password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO admin_users (username, password_hash, email, role) VALUES (?, ?, ?, 'super_admin')");
    $stmt->execute([$admin_username, $password_hash, $admin_email ?: 'admin@vcc.com']);
    
    // Insert default settings
    $stmt = $pdo->prepare("INSERT IGNORE INTO settings (setting_key, setting_value, setting_type) VALUES (?, ?, ?)");
    $settings = [
        ['site_title', 'Virtual Communication Connection', 'text'],
        ['contact_email', 'info@vcc.com', 'email'],
        ['whatsapp_number', '+18095866653', 'text'],
        ['phone', '+1 809-586-6653', 'text'],
        ['address', 'Margaria mears 18, Puerto Plata, Dominican Republic 57000', 'text'],
        ['hero_title', 'Connecting You to the World', 'text'],
        ['hero_subtitle', 'Professional communication solutions for modern businesses', 'text'],
        ['about_content', 'VCC is dedicated to providing top-tier communication services...', 'textarea'],
        ['facebook_url', '', 'url'],
        ['instagram_url', '', 'url'],
        ['twitter_url', '', 'url'],
        ['linkedin_url', '', 'url'],
        ['default_lang', 'en', 'text'],
    ];
    
    foreach ($settings as $setting) {
        $stmt->execute($setting);
    }
    
    // Insert default categories
    $stmt = $pdo->prepare("INSERT IGNORE INTO categories (name, slug, description) VALUES (?, ?, ?)");
    $categories = [
        ['Company News', 'company-news', 'Latest updates from VCC'],
        ['Technology', 'technology', 'Tech trends and innovations'],
        ['Communication Tips', 'communication-tips', 'Tips for better communication'],
        ['Industry Insights', 'industry-insights', 'Analysis and insights'],
    ];
    
    foreach ($categories as $category) {
        $stmt->execute($category);
    }
}

function generateConfig($db_host, $db_name, $db_user, $db_pass, $site_title, $site_url) {
    $db_pass_escaped = str_replace("'", "\'", $db_pass);
    $site_title_escaped = str_replace("'", "\'", $site_title);
    
    return "<?php
/**
 * VCC Website Configuration File
 * 
 * This file was auto-generated by the installer.
 * Do not edit manually unless you know what you're doing.
 */

// Database Configuration
define('DB_HOST', '$db_host');
define('DB_NAME', '$db_name');
define('DB_USER', '$db_user');
define('DB_PASS', '$db_pass_escaped');

// Site Configuration
define('SITE_TITLE', '$site_title_escaped');
define('SITE_URL', '$site_url');

// Upload Directory
define('UPLOAD_DIR', __DIR__ . '/uploads/');
define('UPLOAD_URL', SITE_URL . '/uploads/');

// Security Keys (Change these for production!)
define('AUTH_KEY', '" . bin2hex(random_bytes(32)) . "');
define('SECURE_AUTH_KEY', '" . bin2hex(random_bytes(32)) . "');
define('LOGGED_IN_KEY', '" . bin2hex(random_bytes(32)) . "');

// Database Connection
function getDBConnection() {
    try {
        \$dsn = \"mysql:host=\" . DB_HOST . \";dbname=\" . DB_NAME . \";charset=utf8mb4\";
        \$pdo = new PDO(\$dsn, DB_USER, DB_PASS);
        \$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return \$pdo;
    } catch (PDOException \$e) {
        die('Database connection failed: ' . \$e->getMessage());
    }
}

// Helper function to get setting
function getSetting(\$key, \$default = '') {
    static \$settings = null;
    if (\$settings === null) {
        \$pdo = getDBConnection();
        \$stmt = \$pdo->query(\"SELECT setting_key, setting_value FROM settings\");
        \$settings = \$stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    }
    return \$settings[\$key] ?? \$default;
}

// Check if user is logged in
function isLoggedIn() {
    return isset(\$_SESSION['admin_id']) && isset(\$_SESSION['admin_role']);
}

// Check user role
function hasRole(\$requiredRoles) {
    if (!isLoggedIn()) return false;
    if (is_string(\$requiredRoles)) \$requiredRoles = [\$requiredRoles];
    return in_array(\$_SESSION['admin_role'], \$requiredRoles);
}

// Redirect to login if not authenticated
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: vcc-portal/login.php');
        exit;
    }
}

// Check user role and redirect if unauthorized
function checkRole($requiredRoles) {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
    if (is_string($requiredRoles)) $requiredRoles = [$requiredRoles];
    if (!in_array($_SESSION['admin_role'] ?? '', $requiredRoles)) {
        header('Location: index.php?error=unauthorized');
        exit;
    }
}

// Log activity
function logActivity(\$action, \$description = '') {
    if (!isLoggedIn()) return;
    \$pdo = getDBConnection();
    \$stmt = \$pdo->prepare(\"INSERT INTO activity_log (user_id, action, description, ip_address, user_agent) VALUES (?, ?, ?, ?, ?)\");
    \$stmt->execute([
        \$_SESSION['admin_id'],
        \$action,
        \$description,
        \$_SERVER['REMOTE_ADDR'] ?? '',
        \$_SERVER['HTTP_USER_AGENT'] ?? ''
    ]);
}

// Security: Prevent direct access to config
if (!defined('VCC_INSTALLED')) {
    define('VCC_INSTALLED', true);
}
";
}

// If installation successful, redirect to admin login
if ($success) {
    header('Location: vcc-portal/login.php?installed=1');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VCC Installation</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #0a2540 0%, #00d4d4 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .installer-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            max-width: 650px;
            width: 100%;
            overflow: hidden;
        }
        
        .installer-header {
            background: #0a2540;
            color: white;
            padding: 40px;
            text-align: center;
        }
        
        .installer-header h1 {
            font-size: 2.5rem;
            margin-bottom: 10px;
        }
        
        .installer-header p {
            opacity: 0.9;
            font-size: 1.1rem;
        }
        
        .installer-body {
            padding: 40px;
            max-height: 70vh;
            overflow-y: auto;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #0a2540;
        }
        
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s;
        }
        
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #00d4d4;
        }
        
        .form-group small {
            display: block;
            margin-top: 5px;
            color: #666;
            font-size: 0.85rem;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }
        
        .section-title {
            color: #00d4d4;
            font-size: 1.3rem;
            margin: 25px 0 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #e0e0e0;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .btn-install {
            background: linear-gradient(135deg, #0a2540, #00d4d4);
            color: white;
            border: none;
            padding: 15px 40px;
            font-size: 1.1rem;
            font-weight: 600;
            border-radius: 8px;
            cursor: pointer;
            width: 100%;
            transition: transform 0.3s, box-shadow 0.3s;
            margin-top: 20px;
        }
        
        .btn-install:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(0, 212, 212, 0.3);
        }
        
        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 25px;
        }
        
        .alert-error {
            background: #fee;
            color: #c00;
            border-left: 4px solid #c00;
        }
        
        .logo-preview {
            text-align: center;
            margin-bottom: 20px;
        }
        
        .logo-placeholder {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #0a2540, #00d4d4);
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 1.5rem;
        }
        
        .step-indicator {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
            padding: 0 10px;
        }
        
        .step {
            flex: 1;
            text-align: center;
            padding: 10px;
            background: #f5f5f5;
            margin: 0 5px;
            border-radius: 8px;
            font-size: 0.9rem;
            font-weight: 600;
            color: #666;
        }
        
        .step.active {
            background: #00d4d4;
            color: white;
        }
    </style>
</head>
<body>
    <div class="installer-container">
        <div class="installer-header">
            <div class="logo-preview">
                <div class="logo-placeholder">VCC</div>
            </div>
            <h1>Welcome to VCC</h1>
            <p>CMS Installation Wizard</p>
        </div>
        
        <div class="installer-body">
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <div class="step-indicator">
                <div class="step active">1. Site Info</div>
                <div class="step active">2. Database</div>
                <div class="step active">3. Admin Account</div>
            </div>
            
            <form method="POST" action="">
                <div class="section-title">🌐 Site Information</div>
                
                <div class="form-group">
                    <label for="site_title">Site Title</label>
                    <input type="text" id="site_title" name="site_title" value="Virtual Communication Connection" required>
                    <small>The name of your website</small>
                </div>
                
                <div class="form-group">
                    <label for="site_url">Site URL (Optional)</label>
                    <input type="url" id="site_url" name="site_url" placeholder="https://yourdomain.com">
                    <small>Your website address (leave empty for now)</small>
                </div>
                
                <div class="section-title">💾 Database Configuration</div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="db_host">Database Host</label>
                        <input type="text" id="db_host" name="db_host" value="localhost" required>
                        <small>Usually "localhost"</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="db_name">Database Name</label>
                        <input type="text" id="db_name" name="db_name" required>
                        <small>The name of your database</small>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="db_user">Database Username</label>
                        <input type="text" id="db_user" name="db_user" required>
                        <small>Your MySQL username</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="db_pass">Database Password</label>
                        <input type="password" id="db_pass" name="db_pass">
                        <small>Your MySQL password</small>
                    </div>
                </div>
                
                <div class="section-title">👤 Admin Account</div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="admin_username">Admin Username</label>
                        <input type="text" id="admin_username" name="admin_username" value="admin" required>
                        <small>Your login username</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="admin_email">Admin Email</label>
                        <input type="email" id="admin_email" name="admin_email" placeholder="admin@vcc.com">
                        <small>Your email address</small>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="admin_password">Admin Password</label>
                    <input type="password" id="admin_password" name="admin_password" required minlength="6">
                    <small>Choose a strong password (min 6 characters)</small>
                </div>
                
                <button type="submit" name="install_now" class="btn-install">🚀 Install VCC CMS</button>
            </form>
        </div>
    </div>
</body>
</html>
