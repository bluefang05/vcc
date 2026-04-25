<?php
/**
 * VCC CMS - One-Click Installer
 * Upload this file to your server and visit it in your browser.
 * It will create the entire site structure, database, and admin panel.
 */

session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Simple Router for the Installer Steps
$step = isset($_GET['step']) ? (int)$_GET['step'] : 1;
$message = '';
$error = '';

// Handle Form Submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($step === 2) {
        // Validate DB Credentials
        $host = trim($_POST['db_host']);
        $name = trim($_POST['db_name']);
        $user = trim($_POST['db_user']);
        $pass = $_POST['db_pass'];
        
        try {
            $pdo = new PDO("mysql:host=$host;dbname=$name;charset=utf8mb4", $user, $pass);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Test permissions
            $pdo->query("SHOW TABLES");
            
            $_SESSION['db_config'] = [
                'host' => $host,
                'name' => $name,
                'user' => $user,
                'pass' => $pass
            ];
            header("Location: ?step=3");
            exit;
        } catch (PDOException $e) {
            $error = "Database Connection Failed: " . $e->getMessage();
        }
    } elseif ($step === 3) {
        // Create Admin & Install Files
        if (!isset($_SESSION['db_config'])) {
            header("Location: ?step=2");
            exit;
        }

        $admin_user = trim($_POST['admin_user']);
        $admin_email = trim($_POST['admin_email']);
        $admin_pass = $_POST['admin_pass'];
        $admin_confirm = $_POST['admin_confirm'];

        if ($admin_pass !== $admin_confirm) {
            $error = "Passwords do not match.";
        } elseif (strlen($admin_pass) < 6) {
            $error = "Password must be at least 6 characters.";
        } elseif (empty($admin_user) || empty($admin_email)) {
            $error = "All fields are required.";
        } else {
            try {
                $cfg = $_SESSION['db_config'];
                $pdo = new PDO("mysql:host={$cfg['host']};dbname={$cfg['name']};charset=utf8mb4", $cfg['user'], $cfg['pass']);
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                // 1. Create Tables
                $sql = "
                CREATE TABLE IF NOT EXISTS users (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    username VARCHAR(50) UNIQUE NOT NULL,
                    email VARCHAR(100) UNIQUE NOT NULL,
                    password_hash VARCHAR(255) NOT NULL,
                    role ENUM('super_admin', 'admin', 'editor', 'agent') DEFAULT 'admin',
                    status ENUM('active', 'inactive') DEFAULT 'active',
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                );

                CREATE TABLE IF NOT EXISTS settings (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    setting_key VARCHAR(50) UNIQUE NOT NULL,
                    setting_value TEXT,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                );

                CREATE TABLE IF NOT EXISTS translations (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    lang_code VARCHAR(5) NOT NULL,
                    trans_key VARCHAR(100) NOT NULL,
                    trans_value TEXT,
                    UNIQUE KEY unique_lang_key (lang_code, trans_key)
                );

                CREATE TABLE IF NOT EXISTS blog_posts (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    title VARCHAR(255) NOT NULL,
                    slug VARCHAR(255) UNIQUE NOT NULL,
                    content LONGTEXT,
                    excerpt TEXT,
                    meta_title VARCHAR(255),
                    meta_description TEXT,
                    meta_keywords VARCHAR(255),
                    status ENUM('draft', 'published') DEFAULT 'draft',
                    author_id INT,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE SET NULL
                );

                CREATE TABLE IF NOT EXISTS media_library (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    file_name VARCHAR(255) NOT NULL,
                    file_path VARCHAR(255) NOT NULL,
                    file_type VARCHAR(50),
                    uploaded_by INT,
                    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                );

                CREATE TABLE IF NOT EXISTS contacts (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    name VARCHAR(100),
                    email VARCHAR(100),
                    phone VARCHAR(20),
                    message TEXT,
                    status ENUM('new', 'read', 'archived') DEFAULT 'new',
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                );

                CREATE TABLE IF NOT EXISTS activity_log (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    user_id INT,
                    action VARCHAR(255),
                    ip_address VARCHAR(45),
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                );
                ";

                foreach (explode(';', $sql) as $statement) {
                    if (trim($statement)) {
                        $pdo->exec($statement);
                    }
                }

                // 2. Create Admin User
                $hash = password_hash($admin_pass, PASSWORD_BCRYPT);
                $stmt = $pdo->prepare("INSERT INTO users (username, email, password_hash, role) VALUES (?, ?, ?, 'super_admin')");
                $stmt->execute([$admin_user, $admin_email, $hash]);

                // 3. Seed Default Settings
                $settings = [
                    ['site_title', 'Virtual Communication Connection'],
                    ['site_url', 'http://' . $_SERVER['HTTP_HOST']],
                    ['contact_email', $admin_email],
                    ['whatsapp_number', '+18095866653'],
                    ['ga_tracking_id', ''],
                    ['maintenance_mode', '0'],
                    ['default_lang', 'en']
                ];
                $stmt = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value=?");
                foreach ($settings as $s) {
                    $stmt->execute([$s[0], $s[1], $s[1]]);
                }

                // 4. Seed Translations (Basic EN/ES)
                $translations = [
                    ['en', 'hero_title', 'Connect Your Business to the World'],
                    ['es', 'hero_title', 'Conecta Tu Negocio con el Mundo'],
                    ['en', 'hero_subtitle', 'Professional call center and virtual communication solutions.'],
                    ['es', 'hero_subtitle', 'Soluciones profesionales de call center y comunicación virtual.'],
                    ['en', 'nav_home', 'Home'],
                    ['es', 'nav_home', 'Inicio'],
                    ['en', 'nav_services', 'Services'],
                    ['es', 'nav_services', 'Servicios'],
                    ['en', 'nav_blog', 'Blog'],
                    ['es', 'nav_blog', 'Blog'],
                    ['en', 'nav_contact', 'Contact'],
                    ['es', 'nav_contact', 'Contacto'],
                    ['en', 'btn_get_started', 'Get Started'],
                    ['es', 'btn_get_started', 'Empezar'],
                    ['en', 'footer_rights', '© 2024 VCC. All rights reserved.'],
                    ['es', 'footer_rights', '© 2024 VCC. Todos los derechos reservados.']
                ];
                $stmt = $pdo->prepare("INSERT IGNORE INTO translations (lang_code, trans_key, trans_value) VALUES (?, ?, ?)");
                foreach ($translations as $t) {
                    $stmt->execute($t);
                }

                // 5. Generate Config File Content
                $configContent = "<?php
define('DB_HOST', '{$cfg['host']}');
define('DB_NAME', '{$cfg['name']}');
define('DB_USER', '{$cfg['user']}');
define('DB_PASS', '{$cfg['pass']}');
define('SITE_URL', 'http://" . $_SERVER['HTTP_HOST'] . "');
define('ADMIN_DIR', 'vcc-portal');

try {
    \$pdo = new PDO(\"mysql:host=\".DB_HOST.\";dbname=\".DB_NAME.\";charset=utf8mb4\", DB_USER, DB_PASS);
    \$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException \$e) {
    die(\"Database connection failed.\");
}

session_start();

function isLoggedIn() {
    return isset(\$_SESSION['user_id']) && isset(\$_SESSION['user_role']);
}

function checkRole(\$allowedRoles = []) {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
    if (!empty(\$allowedRoles) && !in_array(\$_SESSION['user_role'], \$allowedRoles)) {
        die('Access Denied: Insufficient permissions.');
    }
}

function getSetting(\$key, \$default = '') {
    global \$pdo;
    static \$cache = [];
    if (!isset(\$cache[\$key])) {
        \$stmt = \$pdo->prepare(\"SELECT setting_value FROM settings WHERE setting_key = ?\");
        \$stmt->execute([\$key]);
        \$res = \$stmt->fetch(PDO::FETCH_ASSOC);
        \$cache[\$key] = \$res ? \$res['setting_value'] : \$default;
    }
    return \$cache[\$key];
}

function t(\$key, \$lang = null) {
    global \$pdo;
    if (\$lang === null) \$lang = getSetting('default_lang', 'en');
    static \$cache = [];
    \$cacheKey = \"{\$lang}:{\$key}\";
    if (!isset(\$cache[\$cacheKey])) {
        \$stmt = \$pdo->prepare(\"SELECT trans_value FROM translations WHERE lang_code = ? AND trans_key = ?\");
        \$stmt->execute([\$lang, \$key]);
        \$res = \$stmt->fetch(PDO::FETCH_ASSOC);
        \$cache[\$cacheKey] = \$res ? \$res['trans_value'] : \$key;
    }
    return \$cache[\$cacheKey];
}
?>";

                // 6. Create Directory Structure and Files
                $baseDir = __DIR__;
                $adminDir = $baseDir . '/vcc-portal';
                $uploadsDir = $baseDir . '/uploads';
                
                if (!is_dir($adminDir)) mkdir($adminDir, 0755, true);
                if (!is_dir($uploadsDir)) mkdir($uploadsDir, 0755, true);
                if (!is_dir($uploadsDir . '/blog')) mkdir($uploadsDir . '/blog', 0755, true);
                if (!is_dir($uploadsDir . '/logo')) mkdir($uploadsDir . '/logo', 0755, true);

                file_put_contents($baseDir . '/config.php', $configContent);

                // Create .htaccess for security
                $htaccess = "Options -Indexes
<FilesMatch \"\.(env|log|sql|sh|inc)$\">
Order allow,deny
Deny from all
</FilesMatch>";
                file_put_contents($baseDir . '/.htaccess', $htaccess);
                file_put_contents($adminDir . '/.htaccess', $htaccess);

                // Create Frontend Index
                $indexContent = '<?php
require_once "config.php";

// Maintenance Mode Check
if (getSetting("maintenance_mode") == "1" && !isLoggedIn()) {
    http_response_code(503);
    echo "<h1>Site Under Maintenance</h1><p>We will be back shortly.</p>";
    exit;
}

$lang = isset($_GET["lang"]) ? $_GET["lang"] : getSetting("default_lang", "en");
$_SESSION["current_lang"] = $lang;

// Simple Router
$page = isset($_GET["p"]) ? $_GET["p"] : "home";
?>
<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo getSetting("site_title"); ?></title>
    <link rel="stylesheet" href="style.css">
    <!-- SEO Meta -->
    <meta name="description" content="<?php echo t("hero_subtitle"); ?>">
</head>
<body>
    <header>
        <div class="container nav-wrapper">
            <div class="logo">
                <span class="logo-icon">🎧</span> VCC
            </div>
            <nav>
                <a href="?p=home&lang=<?php echo $lang; ?>"><?php echo t("nav_home"); ?></a>
                <a href="?p=services&lang=<?php echo $lang; ?>"><?php echo t("nav_services"); ?></a>
                <a href="?p=blog&lang=<?php echo $lang; ?>"><?php echo t("nav_blog"); ?></a>
                <a href="?p=contact&lang=<?php echo $lang; ?>"><?php echo t("nav_contact"); ?></a>
                <select onchange="window.location.href=\'?p=<?php echo $page; ?>&lang=\'+this.value" style="margin-left:15px; padding:5px;">
                    <option value="en" <?php echo $lang=="en"?"selected":""; ?>>EN</option>
                    <option value="es" <?php echo $lang=="es"?"selected":""; ?>>ES</option>
                </select>
            </nav>
        </div>
    </header>

    <main>
        <?php
        if ($page == "home") {
            echo \'<section class="hero">\';
            echo \'<h1>\' . t("hero_title") . \'</h1>\';
            echo \'<p>\' . t("hero_subtitle") . \'</p>\';
            echo \'<a href="?p=contact&lang=\'.$lang.\'" class="btn">\'.t("btn_get_started").\'</a>\';
            echo \'</section>\';
            
            echo \'<section class="container"><h2>Our Services</h2><div class="grid">\';
            // Mock services for now, would pull from DB in full version
            $services = ["Inbound Calls", "Outbound Sales", "Tech Support", "Virtual Assistant"];
            foreach($services as $s) echo \'<div class="card"><h3>\'.$s.\'</h3></div>\';
            echo \'</div></section>\';
        } elseif ($page == "contact") {
            echo \'<section class="container"><h2>\'.t("nav_contact").\'</h2>\';
            if ($_SERVER["REQUEST_METHOD"] == "POST") {
                $name = htmlspecialchars($_POST["name"]);
                $email = htmlspecialchars($_POST["email"]);
                $msg = htmlspecialchars($_POST["message"]);
                $stmt = $pdo->prepare("INSERT INTO contacts (name, email, message) VALUES (?, ?, ?)");
                $stmt->execute([$name, $email, $msg]);
                echo "<p style=\'color:green\'>Message sent successfully!</p>";
            }
            echo \'<form method="POST"><input type="text" name="name" placeholder="Name" required><br>
                  <input type="email" name="email" placeholder="Email" required><br>
                  <textarea name="message" placeholder="Message" required></textarea><br>
                  <button type="submit">Send Message</button></form>\';
            echo \'</section>\';
        } elseif ($page == "blog") {
            echo \'<section class="container"><h2>\'.t("nav_blog").\'</h2>\';
            $posts = $pdo->query("SELECT * FROM blog_posts WHERE status=\'published\' ORDER BY created_at DESC LIMIT 5")->fetchAll();
            if(empty($posts)) echo "<p>No posts yet.</p>";
            foreach($posts as $post) {
                echo \'<article class="card"><h3>\'.htmlspecialchars($post["title"]).\'</h3><p>\'.htmlspecialchars($post["excerpt"]).\'</p></article>\';
            }
            echo \'</section>\';
        } else {
            echo \'<section class="container"><h2>Page Not Found</h2></section>\';
        }
        ?>
    </main>

    <footer>
        <div class="container">
            <p><?php echo t("footer_rights"); ?></p>
            <p><small><a href="vcc-portal/login.php">Admin Login</a></small></p>
        </div>
    </footer>
</body>
</html>';
                file_put_contents($baseDir . '/index.php', $indexContent);

                // Create Basic CSS
                $cssContent = "body { font-family: sans-serif; margin: 0; line-height: 1.6; color: #333; }
                header { background: #0a2540; color: white; padding: 1rem 0; }
                .container { max-width: 1100px; margin: 0 auto; padding: 0 20px; }
                .nav-wrapper { display: flex; justify-content: space-between; align-items: center; }
                nav a { color: white; text-decoration: none; margin-left: 20px; }
                .hero { background: linear-gradient(135deg, #0a2540, #00d4d4); color: white; text-align: center; padding: 100px 20px; }
                .btn { background: #00d4d4; color: #0a2540; padding: 10px 20px; text-decoration: none; font-weight: bold; border-radius: 5px; display: inline-block; margin-top: 20px;}
                .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-top: 20px; }
                .card { border: 1px solid #ddd; padding: 20px; border-radius: 8px; background: white; }
                form input, form textarea { width: 100%; padding: 10px; margin-bottom: 10px; box-sizing: border-box; }
                form button { background: #0a2540; color: white; border: none; padding: 10px 20px; cursor: pointer; }
                footer { background: #f4f4f4; text-align: center; padding: 20px; margin-top: 50px; }";
                file_put_contents($baseDir . '/style.css', $cssContent);

                // Create Admin Login
                $loginContent = '<?php
require_once "../config.php";
if (isLoggedIn()) { header("Location: dashboard.php"); exit; }

$error = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user = trim($_POST["username"]);
    $pass = $_POST["password"];
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? AND status = \'active\'");
    $stmt->execute([$user]);
    $u = $stmt->fetch();
    
    if ($u && password_verify($pass, $u["password_hash"])) {
        $_SESSION["user_id"] = $u["id"];
        $_SESSION["user_role"] = $u["role"];
        $_SESSION["username"] = $u["username"];
        
        // Log activity
        $log = $pdo->prepare("INSERT INTO activity_log (user_id, action, ip_address) VALUES (?, ?, ?)");
        $log->execute([$u["id"], "Login", $_SERVER["REMOTE_ADDR"]]);
        
        header("Location: dashboard.php");
        exit;
    } else {
        $error = "Invalid credentials.";
    }
}
?>
<!DOCTYPE html>
<html>
<head><title>VCC Admin Login</title>
<style>
    body { font-family: sans-serif; background: #f0f2f5; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
    .login-box { background: white; padding: 40px; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); width: 300px; }
    input { width: 100%; padding: 10px; margin: 10px 0; box-sizing: border-box; border: 1px solid #ccc; border-radius: 4px; }
    button { width: 100%; padding: 10px; background: #0a2540; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 16px; }
    button:hover { background: #0d335d; }
    .error { color: red; font-size: 14px; text-align: center; }
</style>
</head>
<body>
    <div class="login-box">
        <h2 style="text-align:center; color:#0a2540;">VCC Portal</h2>
        <?php if($error) echo "<p class=\'error\'>$error</p>"; ?>
        <form method="POST">
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Login</button>
        </form>
    </div>
</body>
</html>';
                file_put_contents($adminDir . '/login.php', $loginContent);

                // Create Admin Dashboard
                $dashContent = '<?php
require_once "../config.php";
checkRole();

// Simple Stats
$userCount = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$msgCount = $pdo->query("SELECT COUNT(*) FROM contacts WHERE status=\'new\'")->fetchColumn();
$postCount = $pdo->query("SELECT COUNT(*) FROM blog_posts")->fetchColumn();
?>
<!DOCTYPE html>
<html>
<head><title>Dashboard - VCC</title>
<style>
    body { font-family: sans-serif; margin: 0; display: flex; min-height: 100vh; background: #f4f6f9; }
    .sidebar { width: 250px; background: #0a2540; color: white; padding: 20px; }
    .sidebar a { display: block; color: #bdc3c7; text-decoration: none; padding: 10px 0; border-bottom: 1px solid #1c3d5a; }
    .sidebar a:hover { color: white; }
    .main { flex: 1; padding: 40px; }
    .stats { display: flex; gap: 20px; margin-bottom: 30px; }
    .stat-card { background: white; padding: 20px; border-radius: 8px; flex: 1; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
    .stat-card h3 { margin: 0; color: #7f8c8d; font-size: 14px; }
    .stat-card p { font-size: 24px; font-weight: bold; color: #0a2540; margin: 10px 0 0; }
    .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
    .logout { color: #e74c3c; text-decoration: none; }
</style>
</head>
<body>
    <div class="sidebar">
        <h2>VCC Portal</h2>
        <a href="dashboard.php">Dashboard</a>
        <a href="messages.php">Messages (<?php echo $msgCount; ?>)</a>
        <a href="blog.php">Blog Posts</a>
        <a href="users.php">User Management</a>
        <a href="settings.php">Settings & SEO</a>
        <a href="../index.php" target="_blank">View Site</a>
        <a href="logout.php" class="logout" style="margin-top:20px; border-color:#c0392b;">Logout</a>
    </div>
    <div class="main">
        <div class="header">
            <h1>Welcome, <?php echo htmlspecialchars($_SESSION["username"]); ?></h1>
        </div>
        <div class="stats">
            <div class="stat-card"><h3>Total Users</h3><p><?php echo $userCount; ?></p></div>
            <div class="stat-card"><h3>New Messages</h3><p><?php echo $msgCount; ?></p></div>
            <div class="stat-card"><h3>Blog Posts</h3><p><?php echo $postCount; ?></p></div>
        </div>
        <div class="content">
            <h2>Quick Actions</h2>
            <p>Use the sidebar to manage your content, view contact messages, or adjust SEO settings.</p>
            <p><strong>Security Tip:</strong> Remember to change your password regularly.</p>
        </div>
    </div>
</body>
</html>';
                file_put_contents($adminDir . '/dashboard.php', $dashContent);

                // Create Logout
                $logoutContent = '<?php
session_start();
session_destroy();
header("Location: login.php");
exit;
?>';
                file_put_contents($adminDir . '/logout.php', $logoutContent);
                
                // Create Placeholder files for other sections
                file_put_contents($adminDir . '/messages.php', '<?php require_once "../config.php"; checkRole(); echo "<h1>Messages Module</h1><p>List of contacts would appear here.</p>"; ?>');
                file_put_contents($adminDir . '/blog.php', '<?php require_once "../config.php"; checkRole(); echo "<h1>Blog Manager</h1><p>Create and edit posts here.</p>"; ?>');
                file_put_contents($adminDir . '/users.php', '<?php require_once "../config.php"; checkRole(["super_admin"]); echo "<h1>User Management</h1><p>Only Super Admins can see this.</p>"; ?>');
                file_put_contents($adminDir . '/settings.php', '<?php require_once "../config.php"; checkRole(); echo "<h1>Settings</h1><p>Manage SEO, Translations, and Maintenance Mode here.</p>"; ?>');

                // Success!
                $_SESSION['install_complete'] = true;
                header("Location: ?step=4");
                exit;

            } catch (Exception $e) {
                $error = "Installation Error: " . $e->getMessage();
            }
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VCC CMS Installer</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; background: #f0f2f5; color: #333; line-height: 1.6; margin: 0; padding: 0; }
        .installer-container { max-width: 600px; margin: 50px auto; background: white; padding: 40px; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
        h1 { color: #0a2540; text-align: center; margin-bottom: 30px; }
        .step-indicator { display: flex; justify-content: space-between; margin-bottom: 30px; border-bottom: 2px solid #eee; padding-bottom: 10px; }
        .step { font-weight: bold; color: #ccc; }
        .step.active { color: #00d4d4; }
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 8px; font-weight: 600; color: #555; }
        input[type="text"], input[type="password"], input[type="email"] { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; font-size: 16px; }
        input:focus { border-color: #00d4d4; outline: none; }
        .btn-install { background: #0a2540; color: white; border: none; padding: 15px; width: 100%; font-size: 18px; border-radius: 4px; cursor: pointer; transition: background 0.3s; }
        .btn-install:hover { background: #00d4d4; color: #0a2540; font-weight: bold; }
        .alert { padding: 15px; background: #ffebee; color: #c62828; border-radius: 4px; margin-bottom: 20px; border-left: 5px solid #c62828; }
        .success-box { text-align: center; padding: 20px; background: #e8f5e9; color: #2e7d32; border-radius: 4px; }
        .code-block { background: #f4f4f4; padding: 10px; border-radius: 4px; font-family: monospace; word-break: break-all; margin: 10px 0; }
        small { color: #777; display: block; margin-top: 5px; }
    </style>
</head>
<body>

<div class="installer-container">
    <h1>VCC CMS Installation</h1>
    
    <div class="step-indicator">
        <span class="step <?php echo $step===1?'active':'';?>">1. Welcome</span>
        <span class="step <?php echo $step===2?'active':'';?>">2. Database</span>
        <span class="step <?php echo $step===3?'active':'';?>">3. Admin Setup</span>
        <span class="step <?php echo $step===4?'active':'';?>">4. Finish</span>
    </div>

    <?php if ($error): ?>
        <div class="alert"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <?php if ($step === 1): ?>
        <h2>Welcome to VCC CMS</h2>
        <p>This installer will set up your entire website, including the public frontend, the secure admin portal (<code>/vcc-portal/</code>), and the database.</p>
        <p><strong>Before you begin, ensure you have:</strong></p>
        <ul>
            <li>A MySQL Database created.</li>
            <li>Database Username and Password.</li>
            <li>Write permissions in this directory.</li>
        </ul>
        <p>The installer will create a <strong>Super Admin</strong> account. Please have your desired credentials ready.</p>
        <br>
        <a href="?step=2" class="btn-install" style="display:block; text-align:center; text-decoration:none;">Start Installation</a>

    <?php elseif ($step === 2): ?>
        <h2>Database Configuration</h2>
        <form method="POST">
            <div class="form-group">
                <label>Database Host</label>
                <input type="text" name="db_host" value="localhost" required>
                <small>Usually "localhost"</small>
            </div>
            <div class="form-group">
                <label>Database Name</label>
                <input type="text" name="db_name" required>
            </div>
            <div class="form-group">
                <label>Database Username</label>
                <input type="text" name="db_user" required>
            </div>
            <div class="form-group">
                <label>Database Password</label>
                <input type="password" name="db_pass">
            </div>
            <button type="submit" class="btn-install">Test Connection & Next</button>
        </form>

    <?php elseif ($step === 3): ?>
        <h2>Create Admin Account</h2>
        <p>Define the credentials for the main administrator. <strong>Do not forget these.</strong></p>
        <form method="POST">
            <div class="form-group">
                <label>Admin Username</label>
                <input type="text" name="admin_user" required placeholder="e.g. admin_john">
            </div>
            <div class="form-group">
                <label>Admin Email</label>
                <input type="email" name="admin_email" required>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="admin_pass" required minlength="6">
            </div>
            <div class="form-group">
                <label>Confirm Password</label>
                <input type="password" name="admin_confirm" required>
            </div>
            <button type="submit" class="btn-install">Install VCC CMS</button>
        </form>

    <?php elseif ($step === 4): ?>
        <div class="success-box">
            <h2>Installation Complete! 🎉</h2>
            <p>Your VCC CMS has been successfully installed.</p>
            
            <p><strong>Next Steps:</strong></p>
            <ol style="text-align:left; display:inline-block;">
                <li>For security, <strong>delete this installer file</strong> immediately.</li>
                <li>Log in to your admin panel.</li>
                <li>Configure your SEO settings and add content.</li>
            </ol>
            
            <br><br>
            <a href="vcc-portal/login.php" class="btn-install" style="display:inline-block; width:auto; text-decoration:none;">Go to Admin Login</a>
            
            <form method="POST" action="" style="margin-top:20px;">
                <button type="button" onclick="if(confirm('Delete installer file? This is recommended for security.')){window.location='?cleanup=true'}" class="btn-install" style="background:#c0392b; width:auto; padding:10px 20px;">Delete Installer File</button>
            </form>
            <?php
            if (isset($_GET['cleanup'])) {
                if (unlink(__FILE__)) {
                    echo "<p style='color:green; margin-top:10px;'>Installer file deleted successfully.</p>";
                } else {
                    echo "<p style='color:red; margin-top:10px;'>Could not delete file. Please delete <code>".basename(__FILE__)."</code> manually.</p>";
                }
            }
            ?>
        </div>
    <?php endif; ?>

</div>

</body>
</html>
