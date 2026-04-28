<?php
/**
 * VCC CMS - Edit Post
 */
session_start();
require_once '../config.php';

// Require login
requireLogin();

$pdo = getDBConnection();
$errors = [];

// Get post ID
$postId = $_GET['id'] ?? 0;

// Get post data
$stmt = $pdo->prepare("SELECT * FROM blog_posts WHERE id = ?");
$stmt->execute([$postId]);
$post = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$post) {
    header('Location: posts.php?error=not_found');
    exit;
}

// Get categories
$stmt = $pdo->query("SELECT * FROM categories ORDER BY name");
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $excerpt = trim($_POST['excerpt'] ?? '');
    $category_id = $_POST['category_id'] ?? null;
    $status = $_POST['status'] ?? 'draft';
    $featured_image = $_POST['featured_image'] ?? null;
    
    // Validation
    if (empty($title)) {
        $errors[] = "Title is required";
    }
    if (empty($content)) {
        $errors[] = "Content is required";
    }
    
    if (empty($errors)) {
        $slug = createSlug($title);
        
        $stmt = $pdo->prepare("UPDATE blog_posts SET title = ?, slug = ?, content = ?, excerpt = ?, category_id = ?, status = ?, featured_image = ? WHERE id = ?");
        $stmt->execute([
            $title,
            $slug,
            $content,
            $excerpt,
            $category_id ?: null,
            $status,
            $featured_image,
            $postId
        ]);
        
        logActivity($pdo, $_SESSION['admin_id'], 'updated_post', "Updated post '$title' (ID: $postId)");
        
        header('Location: posts.php?success=1');
        exit;
    }
} else {
    // Pre-fill form with existing data
    $_POST = [
        'title' => $post['title'],
        'content' => $post['content'],
        'excerpt' => $post['excerpt'] ?? '',
        'category_id' => $post['category_id'],
        'status' => $post['status'],
        'featured_image' => $post['featured_image'] ?? ''
    ];
}

$pageTitle = 'Edit Post';
include 'includes/header.php';
?>

<div class="dashboard-content">
    <div class="page-header">
        <h1>Edit Post</h1>
        <p>Update your blog post</p>
    </div>
    
    <?php if (!empty($errors)): ?>
        <div class="alert alert-error">
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?php echo htmlspecialchars($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
    
    <form method="POST" class="post-form">
        <div class="form-grid">
            <div class="main-content-area">
                <div class="form-card">
                    <label for="title">Title *</label>
                    <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($_POST['title'] ?? ''); ?>" required placeholder="Enter post title">
                </div>
                
                <div class="form-card">
                    <label for="content">Content *</label>
                    <textarea id="content" name="content" rows="15" required placeholder="Write your post content here..."><?php echo htmlspecialchars($_POST['content'] ?? ''); ?></textarea>
                </div>
            </div>
            
            <div class="sidebar-area">
                <div class="form-card">
                    <h3>Publish</h3>
                    <div class="form-group">
                        <label for="status">Status</label>
                        <select id="status" name="status">
                            <option value="draft" <?php echo ($_POST['status'] ?? '') === 'draft' ? 'selected' : ''; ?>>Draft</option>
                            <option value="published" <?php echo ($_POST['status'] ?? '') === 'published' ? 'selected' : ''; ?>>Published</option>
                        </select>
                    </div>
                    <div class="form-actions">
                        <button type="submit" class="btn-primary btn-full">Update Post</button>
                        <a href="posts.php" class="btn-secondary btn-full" style="display:block;text-decoration:none;">Cancel</a>
                    </div>
                </div>
                
                <div class="form-card">
                    <h3>Category</h3>
                    <div class="form-group">
                        <label for="category_id">Select Category</label>
                        <select id="category_id" name="category_id">
                            <option value="">Uncategorized</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo $cat['id']; ?>" <?php echo ($_POST['category_id'] ?? '') == $cat['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($cat['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div class="form-card">
                    <h3>Excerpt</h3>
                    <div class="form-group">
                        <label for="excerpt">Short Description</label>
                        <textarea id="excerpt" name="excerpt" rows="4" placeholder="Brief summary of the post"><?php echo htmlspecialchars($_POST['excerpt'] ?? ''); ?></textarea>
                    </div>
                </div>
                
                <div class="form-card">
                    <h3>Featured Image</h3>
                    <div class="form-group">
                        <label for="featured_image">Image URL</label>
                        <input type="text" id="featured_image" name="featured_image" value="<?php echo htmlspecialchars($_POST['featured_image'] ?? ''); ?>" placeholder="/path/to/image.jpg">
                        <small>Enter the path to your featured image</small>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<style>
    .form-grid {
        display: grid;
        grid-template-columns: 1fr 350px;
        gap: 20px;
    }
    
    .main-content-area {
        display: flex;
        flex-direction: column;
        gap: 20px;
    }
    
    .sidebar-area {
        display: flex;
        flex-direction: column;
        gap: 20px;
    }
    
    .form-card {
        background: white;
        padding: 25px;
        border-radius: 12px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }
    
    .form-card h3 {
        font-size: 1rem;
        color: var(--primary);
        margin-bottom: 15px;
        padding-bottom: 10px;
        border-bottom: 1px solid #eee;
    }
    
    .form-card label {
        display: block;
        font-weight: 600;
        color: var(--primary);
        margin-bottom: 8px;
        font-size: 0.9rem;
    }
    
    .form-card input[type="text"],
    .form-card textarea,
    .form-card select {
        width: 100%;
        padding: 12px 15px;
        border: 1px solid #ddd;
        border-radius: 6px;
        font-size: 0.95rem;
        font-family: inherit;
        transition: border-color 0.3s;
    }
    
    .form-card input:focus,
    .form-card textarea:focus,
    .form-card select:focus {
        outline: none;
        border-color: var(--secondary);
    }
    
    .form-card textarea {
        resize: vertical;
        min-height: 100px;
    }
    
    .form-group {
        margin-bottom: 20px;
    }
    
    .form-group:last-child {
        margin-bottom: 0;
    }
    
    .form-group small {
        display: block;
        margin-top: 5px;
        color: #999;
        font-size: 0.8rem;
    }
    
    .form-actions {
        display: flex;
        flex-direction: column;
        gap: 10px;
        margin-top: 20px;
    }
    
    .btn-primary {
        padding: 12px 20px;
        background: var(--secondary);
        color: var(--primary);
        border: none;
        border-radius: 6px;
        font-weight: 600;
        cursor: pointer;
        text-decoration: none;
        display: inline-block;
        text-align: center;
    }
    
    .btn-secondary {
        padding: 12px 20px;
        background: #e9ecef;
        color: var(--dark);
        border: none;
        border-radius: 6px;
        font-weight: 600;
        cursor: pointer;
        text-decoration: none;
        display: inline-block;
        text-align: center;
    }
    
    .btn-full {
        width: 100%;
    }
    
    .alert {
        padding: 15px 20px;
        border-radius: 8px;
        margin-bottom: 20px;
    }
    
    .alert-error {
        background: rgba(220, 53, 69, 0.1);
        color: var(--danger);
        border: 1px solid var(--danger);
    }
    
    .alert-error ul {
        margin: 0;
        padding-left: 20px;
    }
    
    @media (max-width: 1024px) {
        .form-grid {
            grid-template-columns: 1fr;
        }
        
        .sidebar-area {
            order: -1;
        }
    }
</style>

<?php include 'includes/footer.php'; ?>
