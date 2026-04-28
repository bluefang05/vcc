<?php
/**
 * VCC CMS - Posts Management
 */
session_start();
require_once '../config.php';

// Require login
requireLogin();

$pdo = getDBConnection();

// Handle delete action
if (isset($_GET['delete']) && isset($_GET['id'])) {
    $stmt = $pdo->prepare("DELETE FROM blog_posts WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    logActivity($pdo, $_SESSION['admin_id'], 'deleted_post', "Deleted post ID {$_GET['id']}");
    header('Location: posts.php?deleted=1');
    exit;
}

// Get all posts
$search = $_GET['search'] ?? '';
$status = $_GET['status'] ?? 'all';

$sql = "SELECT * FROM blog_posts WHERE 1=1";
$params = [];

if ($search) {
    $sql .= " AND (title LIKE ? OR content LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($status !== 'all') {
    $sql .= " AND status = ?";
    $params[] = $status;
}

$sql .= " ORDER BY created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

$pageTitle = 'Posts';
include 'includes/header.php';
?>

<div class="dashboard-content">
    <div class="page-header">
        <h1>Posts</h1>
        <p>Manage your blog posts</p>
    </div>
    
    <?php if (isset($_GET['deleted'])): ?>
        <div class="alert alert-success">Post deleted successfully!</div>
    <?php endif; ?>
    
    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success">Post saved successfully!</div>
    <?php endif; ?>
    
    <!-- Filters -->
    <div class="filters-bar">
        <form method="GET" class="filters-form">
            <input type="text" name="search" placeholder="Search posts..." value="<?php echo htmlspecialchars($search); ?>" class="search-input">
            <select name="status" class="status-select">
                <option value="all" <?php echo $status === 'all' ? 'selected' : ''; ?>>All Status</option>
                <option value="published" <?php echo $status === 'published' ? 'selected' : ''; ?>>Published</option>
                <option value="draft" <?php echo $status === 'draft' ? 'selected' : ''; ?>>Draft</option>
            </select>
            <button type="submit" class="btn-filter">Filter</button>
        </form>
        <a href="post-new.php" class="btn-primary">+ New Post</a>
    </div>
    
    <!-- Posts Table -->
    <div class="table-container">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Author</th>
                    <th>Category</th>
                    <th>Status</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($posts)): ?>
                    <tr>
                        <td colspan="6" class="no-data">No posts found. <a href="post-new.php">Create your first post</a></td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($posts as $post): ?>
                        <tr>
                            <td>
                                <strong><?php echo htmlspecialchars($post['title']); ?></strong>
                            </td>
                            <td><?php echo htmlspecialchars($post['author_name'] ?? 'Admin'); ?></td>
                            <td>
                                <?php if ($post['category_id']): ?>
                                    <span class="badge"><?php echo htmlspecialchars($post['category_name'] ?? 'Uncategorized'); ?></span>
                                <?php else: ?>
                                    <span class="badge badge-secondary">Uncategorized</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="status-badge status-<?php echo $post['status']; ?>">
                                    <?php echo ucfirst($post['status']); ?>
                                </span>
                            </td>
                            <td><?php echo date('M j, Y', strtotime($post['created_at'])); ?></td>
                            <td class="actions">
                                <a href="post-edit.php?id=<?php echo $post['id']; ?>" class="btn-action btn-edit">Edit</a>
                                <a href="posts.php?delete=1&id=<?php echo $post['id']; ?>" class="btn-action btn-delete" onclick="return confirm('Are you sure you want to delete this post?')">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<style>
    .filters-bar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        gap: 15px;
        flex-wrap: wrap;
    }
    
    .filters-form {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }
    
    .search-input, .status-select {
        padding: 10px 15px;
        border: 1px solid #ddd;
        border-radius: 6px;
        font-size: 0.9rem;
    }
    
    .search-input {
        min-width: 250px;
    }
    
    .btn-filter {
        padding: 10px 20px;
        background: var(--primary);
        color: white;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        font-weight: 600;
    }
    
    .btn-primary {
        padding: 10px 20px;
        background: var(--secondary);
        color: var(--primary);
        text-decoration: none;
        border-radius: 6px;
        font-weight: 600;
        display: inline-block;
    }
    
    .table-container {
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        overflow: hidden;
    }
    
    .data-table {
        width: 100%;
        border-collapse: collapse;
    }
    
    .data-table th,
    .data-table td {
        padding: 15px 20px;
        text-align: left;
        border-bottom: 1px solid #eee;
    }
    
    .data-table th {
        background: #f8f9fa;
        font-weight: 600;
        color: var(--primary);
        font-size: 0.85rem;
        text-transform: uppercase;
    }
    
    .data-table tr:hover {
        background: #f8f9fa;
    }
    
    .data-table tr:last-child td {
        border-bottom: none;
    }
    
    .no-data {
        text-align: center;
        color: #999;
        padding: 40px !important;
    }
    
    .no-data a {
        color: var(--secondary);
    }
    
    .badge {
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
        background: rgba(0, 212, 212, 0.1);
        color: var(--primary);
    }
    
    .badge-secondary {
        background: #e9ecef;
        color: #666;
    }
    
    .status-badge {
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
    }
    
    .status-published {
        background: rgba(40, 167, 69, 0.1);
        color: var(--success);
    }
    
    .status-draft {
        background: rgba(255, 193, 7, 0.1);
        color: var(--warning);
    }
    
    .actions {
        display: flex;
        gap: 8px;
    }
    
    .btn-action {
        padding: 6px 12px;
        border-radius: 5px;
        text-decoration: none;
        font-size: 0.8rem;
        font-weight: 600;
    }
    
    .btn-edit {
        background: rgba(0, 212, 212, 0.1);
        color: var(--primary);
    }
    
    .btn-delete {
        background: rgba(220, 53, 69, 0.1);
        color: var(--danger);
    }
    
    .alert {
        padding: 15px 20px;
        border-radius: 8px;
        margin-bottom: 20px;
    }
    
    .alert-success {
        background: rgba(40, 167, 69, 0.1);
        color: var(--success);
        border: 1px solid var(--success);
    }
</style>

<?php include 'includes/footer.php'; ?>
