<?php
/**
 * VCC CMS Admin Dashboard
 */
session_start();
require_once '../config.php';

// Require login
requireLogin();

// Get statistics
$pdo = getDBConnection();

// Total posts
$stmt = $pdo->query("SELECT COUNT(*) FROM blog_posts");
$totalPosts = $stmt->fetchColumn();

// Published posts
$stmt = $pdo->query("SELECT COUNT(*) FROM blog_posts WHERE status = 'published'");
$publishedPosts = $stmt->fetchColumn();

// Total messages
$stmt = $pdo->query("SELECT COUNT(*) FROM contact_messages");
$totalMessages = $stmt->fetchColumn();

// Unread messages
$stmt = $pdo->query("SELECT COUNT(*) FROM contact_messages WHERE status = 'new'");
$unreadMessages = $stmt->fetchColumn();

// Total media files
$stmt = $pdo->query("SELECT COUNT(*) FROM media_library");
$totalMedia = $stmt->fetchColumn();

// Recent activity
$stmt = $pdo->query("SELECT al.*, au.username FROM activity_log al LEFT JOIN admin_users au ON al.user_id = au.id ORDER BY al.created_at DESC LIMIT 10");
$recentActivity = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Recent messages
$stmt = $pdo->query("SELECT * FROM contact_messages ORDER BY created_at DESC LIMIT 5");
$recentMessages = $stmt->fetchAll(PDO::FETCH_ASSOC);

$pageTitle = 'Dashboard';
include 'includes/header.php';
?>

<div class="dashboard-content">
    <div class="page-header">
        <h1>Dashboard</h1>
        <p>Welcome back, <?php echo htmlspecialchars($_SESSION['admin_username']); ?>!</p>
    </div>
    
    <!-- Statistics Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon stat-icon-posts">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                    <polyline points="14 2 14 8 20 8"></polyline>
                    <line x1="16" y1="13" x2="8" y2="13"></line>
                    <line x1="16" y1="17" x2="8" y2="17"></line>
                    <polyline points="10 9 9 9 8 9"></polyline>
                </svg>
            </div>
            <div class="stat-details">
                <h3><?php echo $totalPosts; ?></h3>
                <p>Total Posts</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon stat-icon-published">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                    <polyline points="22 4 12 14.01 9 11.01"></polyline>
                </svg>
            </div>
            <div class="stat-details">
                <h3><?php echo $publishedPosts; ?></h3>
                <p>Published</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon stat-icon-messages">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                    <polyline points="22,6 12,13 2,6"></polyline>
                </svg>
            </div>
            <div class="stat-details">
                <h3><?php echo $totalMessages; ?></h3>
                <p>Total Messages</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon stat-icon-unread">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                </svg>
            </div>
            <div class="stat-details">
                <h3><?php echo $unreadMessages; ?></h3>
                <p>Unread Messages</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon stat-icon-media">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                    <circle cx="8.5" cy="8.5" r="1.5"></circle>
                    <polyline points="21 15 16 10 5 21"></polyline>
                </svg>
            </div>
            <div class="stat-details">
                <h3><?php echo $totalMedia; ?></h3>
                <p>Media Files</p>
            </div>
        </div>
    </div>
    
    <!-- Recent Activity & Messages -->
    <div class="dashboard-grid">
        <div class="dashboard-panel">
            <div class="panel-header">
                <h2>Recent Activity</h2>
            </div>
            <div class="panel-body">
                <?php if (empty($recentActivity)): ?>
                    <p class="no-data">No recent activity</p>
                <?php else: ?>
                    <ul class="activity-list">
                        <?php foreach ($recentActivity as $activity): ?>
                            <li class="activity-item">
                                <span class="activity-action"><?php echo htmlspecialchars($activity['action']); ?></span>
                                <span class="activity-user">by <?php echo htmlspecialchars($activity['username'] ?? 'System'); ?></span>
                                <span class="activity-time"><?php echo date('M j, g:i a', strtotime($activity['created_at'])); ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="dashboard-panel">
            <div class="panel-header">
                <h2>Recent Messages</h2>
                <a href="messages.php" class="btn-small">View All</a>
            </div>
            <div class="panel-body">
                <?php if (empty($recentMessages)): ?>
                    <p class="no-data">No messages yet</p>
                <?php else: ?>
                    <ul class="message-list">
                        <?php foreach ($recentMessages as $msg): ?>
                            <li class="message-item <?php echo $msg['status'] === 'new' ? 'unread' : ''; ?>">
                                <div class="message-info">
                                    <strong><?php echo htmlspecialchars($msg['name']); ?></strong>
                                    <span class="message-subject"><?php echo htmlspecialchars($msg['subject']); ?></span>
                                </div>
                                <span class="message-time"><?php echo date('M j, g:i a', strtotime($msg['created_at'])); ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Quick Actions -->
    <div class="quick-actions">
        <h2>Quick Actions</h2>
        <div class="actions-grid">
            <a href="post-new.php" class="action-card">
                <div class="action-icon">+</div>
                <span>New Post</span>
            </a>
            <a href="media.php" class="action-card">
                <div class="action-icon">📁</div>
                <span>Upload Media</span>
            </a>
            <a href="messages.php" class="action-card">
                <div class="action-icon">✉️</div>
                <span>View Messages</span>
            </a>
            <a href="settings.php" class="action-card">
                <div class="action-icon">⚙️</div>
                <span>Settings</span>
            </a>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
