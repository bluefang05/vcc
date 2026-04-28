<?php
/**
 * VCC Blog Page
 * Displays all published blog posts
 */

// Check if site is installed
if (!file_exists('config.php')) {
    header('Location: install.php');
    exit;
}

require_once 'config.php';
define('VCC_INSTALLED', true);

// Fetch settings from database for dynamic content
$siteSettings = [];
try {
    $pdo = getDBConnection();
    $stmt = $pdo->query("SELECT setting_key, setting_value FROM settings");
    $siteSettings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
} catch (Exception $e) {
    // Use defaults if settings not available
}

// Helper to get setting with fallback
function getSiteSetting($key, $default = '') {
    global $siteSettings;
    return $siteSettings[$key] ?? $default;
}

// Get posts from database
$posts = [];
$categories = [];
try {
    $pdo = getDBConnection();
    
    // Get all categories
    $stmt = $pdo->query("SELECT * FROM blog_categories ORDER BY name ASC");
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get published posts with category info
    $categoryId = isset($_GET['category']) ? (int)$_GET['category'] : 0;
    
    if ($categoryId > 0) {
        $stmt = $pdo->prepare("
            SELECT p.*, c.name as category_name 
            FROM blog_posts p 
            LEFT JOIN blog_categories c ON p.category_id = c.id 
            WHERE p.status = 'published' AND p.category_id = ?
            ORDER BY p.created_at DESC
        ");
        $stmt->execute([$categoryId]);
    } else {
        $stmt = $pdo->query("
            SELECT p.*, c.name as category_name 
            FROM blog_posts p 
            LEFT JOIN blog_categories c ON p.category_id = c.id 
            WHERE p.status = 'published' 
            ORDER BY p.created_at DESC
        ");
    }
    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    // Error handling
}

$pageTitle = getSiteSetting('blog_title', 'Blog');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?> | <?php echo htmlspecialchars(getSiteSetting('site_title', 'VCC')); ?></title>
    <meta name="description" content="<?php echo htmlspecialchars(getSiteSetting('meta_description', 'Latest news and updates from VCC')); ?>">
    <link rel="stylesheet" href="styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .blog-header {
            background: linear-gradient(135deg, var(--primary) 0%, #1a3a5c 100%);
            color: white;
            padding: 80px 0;
            text-align: center;
        }
        .blog-header h1 {
            font-size: 3rem;
            margin-bottom: 10px;
        }
        .blog-header p {
            font-size: 1.2rem;
            opacity: 0.9;
        }
        .blog-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 60px 20px;
        }
        .blog-filters {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-bottom: 40px;
            flex-wrap: wrap;
        }
        .filter-btn {
            padding: 10px 20px;
            border: 2px solid var(--primary);
            background: white;
            color: var(--primary);
            border-radius: 25px;
            cursor: pointer;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        .filter-btn:hover,
        .filter-btn.active {
            background: var(--primary);
            color: white;
        }
        .posts-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 30px;
        }
        .post-card {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .post-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
        }
        .post-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
            background: #f0f0f0;
        }
        .post-content {
            padding: 25px;
        }
        .post-category {
            display: inline-block;
            background: var(--secondary);
            color: var(--primary);
            padding: 5px 12px;
            border-radius: 15px;
            font-size: 0.85rem;
            font-weight: 600;
            margin-bottom: 10px;
        }
        .post-title {
            font-size: 1.5rem;
            color: var(--primary);
            margin-bottom: 10px;
            line-height: 1.3;
        }
        .post-excerpt {
            color: #666;
            line-height: 1.6;
            margin-bottom: 15px;
        }
        .post-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 15px;
            border-top: 1px solid #eee;
            font-size: 0.9rem;
            color: #888;
        }
        .read-more {
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        .read-more:hover {
            color: var(--secondary);
        }
        .no-posts {
            text-align: center;
            padding: 60px 20px;
            color: #666;
        }
        .no-posts h3 {
            font-size: 1.8rem;
            margin-bottom: 10px;
            color: var(--primary);
        }
        @media (max-width: 768px) {
            .blog-header h1 {
                font-size: 2rem;
            }
            .posts-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <!-- Header / Navigation -->
    <header class="header">
        <nav class="nav container">
            <div class="logo">
                <img src="assets/logo.svg" alt="VCC Logo" class="logo-img">
            </div>
            <ul class="nav-menu">
                <li><a href="index.php#home">Home</a></li>
                <li><a href="index.php#about">About</a></li>
                <li><a href="index.php#services">Services</a></li>
                <li><a href="index.php#values">Values</a></li>
                <li><a href="index.php#contact">Contact</a></li>
            </ul>
            <a href="<?php echo htmlspecialchars(getSiteSetting('whatsapp_number', 'https://wa.me/18095866653')); ?>" class="btn-primary nav-cta">WhatsApp</a>
        </nav>
    </header>

    <!-- Blog Header -->
    <section class="blog-header">
        <div class="container">
            <h1><?php echo htmlspecialchars($pageTitle); ?></h1>
            <p><?php echo htmlspecialchars(getSiteSetting('blog_subtitle', 'Latest news, updates, and insights from our team')); ?></p>
        </div>
    </section>

    <!-- Blog Content -->
    <div class="blog-container">
        <!-- Category Filters -->
        <div class="blog-filters">
            <a href="blog.php" class="filter-btn <?php echo !isset($_GET['category']) ? 'active' : ''; ?>">All Posts</a>
            <?php foreach ($categories as $cat): ?>
                <a href="blog.php?category=<?php echo $cat['id']; ?>" class="filter-btn <?php echo (isset($_GET['category']) && $_GET['category'] == $cat['id']) ? 'active' : ''; ?>">
                    <?php echo htmlspecialchars($cat['name']); ?>
                </a>
            <?php endforeach; ?>
        </div>

        <!-- Posts Grid -->
        <?php if (empty($posts)): ?>
            <div class="no-posts">
                <h3>No posts yet</h3>
                <p>Check back soon for our latest updates!</p>
                <a href="index.php" class="btn-primary" style="margin-top: 20px; display: inline-block;">Back to Home</a>
            </div>
        <?php else: ?>
            <div class="posts-grid">
                <?php foreach ($posts as $post): ?>
                    <article class="post-card">
                        <?php if ($post['featured_image']): ?>
                            <img src="<?php echo htmlspecialchars($post['featured_image']); ?>" alt="<?php echo htmlspecialchars($post['title']); ?>" class="post-image">
                        <?php else: ?>
                            <div class="post-image" style="display: flex; align-items: center; justify-content: center; color: #ccc;">
                                <svg width="60" height="60" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1">
                                    <rect x="3" y="3" width="18" height="18" rx="2"/>
                                    <circle cx="8.5" cy="8.5" r="1.5"/>
                                    <path d="M21 15l-5-5L5 21"/>
                                </svg>
                            </div>
                        <?php endif; ?>
                        <div class="post-content">
                            <?php if ($post['category_name']): ?>
                                <span class="post-category"><?php echo htmlspecialchars($post['category_name']); ?></span>
                            <?php endif; ?>
                            <h2 class="post-title"><?php echo htmlspecialchars($post['title']); ?></h2>
                            <p class="post-excerpt">
                                <?php 
                                $excerpt = $post['excerpt'] ?? strip_tags($post['content']);
                                echo htmlspecialchars(substr($excerpt, 0, 150)) . (strlen($excerpt) > 150 ? '...' : '');
                                ?>
                            </p>
                            <div class="post-meta">
                                <span><?php echo date('M d, Y', strtotime($post['created_at'])); ?></span>
                                <a href="post.php?id=<?php echo $post['id']; ?>" class="read-more">Read More →</a>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <div class="container footer-content">
            <div class="footer-brand">
                <img src="assets/logo.svg" alt="VCC Logo" class="footer-logo">
                <p><?php echo htmlspecialchars(getSiteSetting('site_title', 'Virtual Communication Connection')); ?></p>
                <p><?php echo htmlspecialchars(getSiteSetting('site_tagline', 'Virtual Call Center & Communications Outsourcing')); ?></p>
            </div>
            <div class="footer-links">
                <h4>Quick Links</h4>
                <ul>
                    <li><a href="index.php#home">Home</a></li>
                    <li><a href="index.php#about">About</a></li>
                    <li><a href="index.php#services">Services</a></li>
                    <li><a href="index.php#contact">Contact</a></li>
                    <li><a href="blog.php">Blog</a></li>
                </ul>
            </div>
            <div class="footer-contact">
                <h4>Contact</h4>
                <p>📍 <?php echo htmlspecialchars(getSiteSetting('footer_location', 'Puerto Plata, RD')); ?></p>
                <p>📞 <?php echo htmlspecialchars(getSiteSetting('company_phone', '+1 809-586-6653')); ?></p>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; <?php echo date('Y'); ?> <?php echo htmlspecialchars(getSiteSetting('site_title', 'Virtual Communication Connection')); ?>. All rights reserved.</p>
        </div>
    </footer>

    <script src="script.js"></script>
</body>
</html>
