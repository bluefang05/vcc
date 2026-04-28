<?php
/**
 * VCC Single Post Page
 * Displays a single blog post
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

// Get post ID
$postId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($postId <= 0) {
    header('Location: blog.php');
    exit;
}

// Get post from database
$post = null;
$relatedPosts = [];
try {
    $pdo = getDBConnection();
    
    // Get the post
    $stmt = $pdo->prepare("
        SELECT p.*, c.name as category_name 
        FROM blog_posts p 
        LEFT JOIN blog_categories c ON p.category_id = c.id 
        WHERE p.id = ? AND p.status = 'published'
    ");
    $stmt->execute([$postId]);
    $post = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$post) {
        header('Location: blog.php');
        exit;
    }
    
    // Get related posts (same category, excluding current)
    $stmt = $pdo->prepare("
        SELECT p.*, c.name as category_name 
        FROM blog_posts p 
        LEFT JOIN blog_categories c ON p.category_id = c.id 
        WHERE p.status = 'published' AND p.category_id = ? AND p.id != ?
        ORDER BY p.created_at DESC 
        LIMIT 3
    ");
    $stmt->execute([$post['category_id'], $postId]);
    $relatedPosts = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    // Error handling
}

$pageTitle = $post['title'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?> | <?php echo htmlspecialchars(getSiteSetting('site_title', 'VCC')); ?></title>
    <meta name="description" content="<?php echo htmlspecialchars($post['excerpt'] ?? substr(strip_tags($post['content']), 0, 160)); ?>">
    <link rel="stylesheet" href="styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .post-header {
            background: linear-gradient(135deg, var(--primary) 0%, #1a3a5c 100%);
            color: white;
            padding: 100px 0 60px;
            text-align: center;
        }
        .post-header h1 {
            font-size: 2.8rem;
            margin-bottom: 20px;
            line-height: 1.2;
            max-width: 900px;
            margin-left: auto;
            margin-right: auto;
        }
        .post-meta {
            display: flex;
            justify-content: center;
            gap: 20px;
            opacity: 0.9;
            flex-wrap: wrap;
        }
        .post-meta span {
            display: flex;
            align-items: center;
            gap: 5px;
        }
        .post-category-badge {
            background: var(--secondary);
            color: var(--primary);
            padding: 5px 15px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.9rem;
        }
        .post-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 60px 20px;
        }
        .featured-image {
            width: 100%;
            max-height: 500px;
            object-fit: cover;
            border-radius: 10px;
            margin-bottom: 40px;
        }
        .post-body {
            font-size: 1.1rem;
            line-height: 1.8;
            color: #333;
        }
        .post-body h2, .post-body h3, .post-body h4 {
            color: var(--primary);
            margin-top: 40px;
            margin-bottom: 20px;
        }
        .post-body p {
            margin-bottom: 20px;
        }
        .post-body ul, .post-body ol {
            margin-bottom: 20px;
            padding-left: 30px;
        }
        .post-body li {
            margin-bottom: 10px;
        }
        .post-body blockquote {
            border-left: 4px solid var(--secondary);
            padding-left: 20px;
            margin: 30px 0;
            font-style: italic;
            color: #666;
        }
        .back-to-blog {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
            margin-bottom: 30px;
            transition: color 0.3s ease;
        }
        .back-to-blog:hover {
            color: var(--secondary);
        }
        .related-posts {
            background: #f8f9fa;
            padding: 60px 20px;
            margin-top: 60px;
        }
        .related-posts h3 {
            text-align: center;
            font-size: 2rem;
            color: var(--primary);
            margin-bottom: 40px;
        }
        .related-grid {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 30px;
        }
        @media (max-width: 768px) {
            .post-header h1 {
                font-size: 1.8rem;
            }
            .post-container {
                padding: 40px 15px;
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

    <!-- Post Header -->
    <section class="post-header">
        <div class="container">
            <h1><?php echo htmlspecialchars($post['title']); ?></h1>
            <div class="post-meta">
                <?php if ($post['category_name']): ?>
                    <span class="post-category-badge"><?php echo htmlspecialchars($post['category_name']); ?></span>
                <?php endif; ?>
                <span>📅 <?php echo date('F d, Y', strtotime($post['created_at'])); ?></span>
                <span>✍️ <?php echo htmlspecialchars($post['author'] ?? 'Admin'); ?></span>
            </div>
        </div>
    </section>

    <!-- Post Content -->
    <div class="post-container">
        <a href="blog.php" class="back-to-blog">← Back to Blog</a>
        
        <?php if ($post['featured_image']): ?>
            <img src="<?php echo htmlspecialchars($post['featured_image']); ?>" alt="<?php echo htmlspecialchars($post['title']); ?>" class="featured-image">
        <?php endif; ?>
        
        <article class="post-body">
            <?php echo $post['content']; ?>
        </article>
    </div>

    <!-- Related Posts -->
    <?php if (!empty($relatedPosts)): ?>
    <section class="related-posts">
        <h3>Related Posts</h3>
        <div class="related-grid">
            <?php foreach ($relatedPosts as $related): ?>
                <article class="post-card" style="background: white; border-radius: 10px; overflow: hidden; box-shadow: 0 5px 15px rgba(0,0,0,0.1);">
                    <?php if ($related['featured_image']): ?>
                        <img src="<?php echo htmlspecialchars($related['featured_image']); ?>" alt="<?php echo htmlspecialchars($related['title']); ?>" style="width: 100%; height: 180px; object-fit: cover;">
                    <?php else: ?>
                        <div style="width: 100%; height: 180px; background: #f0f0f0; display: flex; align-items: center; justify-content: center; color: #ccc;">
                            <svg width="50" height="50" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1">
                                <rect x="3" y="3" width="18" height="18" rx="2"/>
                                <circle cx="8.5" cy="8.5" r="1.5"/>
                                <path d="M21 15l-5-5L5 21"/>
                            </svg>
                        </div>
                    <?php endif; ?>
                    <div style="padding: 20px;">
                        <h4 style="color: var(--primary); margin-bottom: 10px; font-size: 1.2rem;"><?php echo htmlspecialchars($related['title']); ?></h4>
                        <p style="color: #666; font-size: 0.95rem; margin-bottom: 15px;">
                            <?php 
                            $excerpt = $related['excerpt'] ?? strip_tags($related['content']);
                            echo htmlspecialchars(substr($excerpt, 0, 100)) . (strlen($excerpt) > 100 ? '...' : '');
                            ?>
                        </p>
                        <a href="post.php?id=<?php echo $related['id']; ?>" style="color: var(--primary); text-decoration: none; font-weight: 600;">Read More →</a>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>

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
