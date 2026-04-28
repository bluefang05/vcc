<?php
/**
 * VCC CMS Admin Header
 */
if (!isset($pageTitle)) $pageTitle = 'Admin';

// Get unread message count for badge
$unreadCount = 0;
if (isLoggedIn()) {
    try {
        $pdo = getDBConnection();
        $stmt = $pdo->query("SELECT COUNT(*) FROM contact_messages WHERE status = 'new'");
        $unreadCount = $stmt->fetchColumn();
    } catch (Exception $e) {}
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?> - VCC Admin</title>
    <style>
        :root {
            --primary: #0a2540;
            --secondary: #00d4d4;
            --success: #28a745;
            --warning: #ffc107;
            --danger: #dc3545;
            --light: #f8f9fa;
            --dark: #343a40;
            --sidebar-width: 260px;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f7fa;
            min-height: 100vh;
        }
        
        .admin-layout {
            display: flex;
            min-height: 100vh;
        }
        
        /* Sidebar */
        .sidebar {
            width: var(--sidebar-width);
            background: var(--primary);
            color: white;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
            z-index: 1000;
        }
        
        .sidebar-header {
            padding: 25px 20px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .sidebar-logo {
            width: 45px;
            height: 45px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 1.2rem;
        }
        
        .sidebar-title h2 {
            font-size: 1.3rem;
            margin-bottom: 3px;
        }
        
        .sidebar-title p {
            font-size: 0.8rem;
            opacity: 0.7;
        }
        
        .sidebar-menu {
            padding: 20px 0;
        }
        
        .menu-category {
            padding: 10px 20px 5px;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            opacity: 0.5;
            font-weight: 600;
        }
        
        .menu-item {
            display: flex;
            align-items: center;
            padding: 12px 20px;
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            transition: all 0.3s;
            gap: 12px;
        }
        
        .menu-item:hover,
        .menu-item.active {
            background: rgba(255,255,255,0.1);
            color: white;
            border-left: 3px solid var(--secondary);
        }
        
        .menu-item svg {
            width: 20px;
            height: 20px;
        }
        
        .menu-item.disabled {
            opacity: 0.4;
            pointer-events: none;
            cursor: not-allowed;
        }
        
        .menu-badge {
            margin-left: auto;
            background: var(--secondary);
            color: var(--primary);
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        
        /* Main Content */
        .main-content {
            flex: 1;
            margin-left: var(--sidebar-width);
            min-height: 100vh;
        }
        
        /* Top Bar */
        .top-bar {
            background: white;
            padding: 15px 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 100;
        }
        
        .top-bar-left {
            display: flex;
            align-items: center;
            gap: 20px;
        }
        
        .page-title h1 {
            font-size: 1.5rem;
            color: var(--primary);
        }
        
        .top-bar-right {
            display: flex;
            align-items: center;
            gap: 20px;
        }
        
        .user-menu {
            display: flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
        }
        
        .user-avatar {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
        }
        
        .user-info {
            line-height: 1.3;
        }
        
        .user-name {
            font-weight: 600;
            color: var(--primary);
            font-size: 0.9rem;
        }
        
        .user-role {
            font-size: 0.75rem;
            color: #666;
            text-transform: capitalize;
        }
        
        .logout-btn {
            background: none;
            border: none;
            color: var(--danger);
            cursor: pointer;
            font-size: 0.9rem;
            padding: 8px 15px;
            border-radius: 5px;
            transition: background 0.3s;
        }
        
        .logout-btn:hover {
            background: rgba(220, 53, 69, 0.1);
        }
        
        /* Dashboard Content */
        .dashboard-content {
            padding: 30px;
        }
        
        .page-header {
            margin-bottom: 30px;
        }
        
        .page-header h1 {
            font-size: 2rem;
            color: var(--primary);
            margin-bottom: 5px;
        }
        
        .page-header p {
            color: #666;
        }
        
        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            display: flex;
            align-items: center;
            gap: 20px;
            transition: transform 0.3s, box-shadow 0.3s;
        }
        
        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
        
        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .stat-icon svg {
            width: 30px;
            height: 30px;
        }
        
        .stat-icon-posts { background: rgba(0, 212, 212, 0.1); color: var(--secondary); }
        .stat-icon-published { background: rgba(40, 167, 69, 0.1); color: var(--success); }
        .stat-icon-messages { background: rgba(10, 37, 64, 0.1); color: var(--primary); }
        .stat-icon-unread { background: rgba(255, 193, 7, 0.1); color: var(--warning); }
        .stat-icon-media { background: rgba(220, 53, 69, 0.1); color: var(--danger); }
        
        .stat-details h3 {
            font-size: 2rem;
            color: var(--primary);
            margin-bottom: 3px;
        }
        
        .stat-details p {
            color: #666;
            font-size: 0.9rem;
        }
        
        /* Dashboard Grid */
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .dashboard-panel {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            overflow: hidden;
        }
        
        .panel-header {
            padding: 20px;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .panel-header h2 {
            font-size: 1.2rem;
            color: var(--primary);
        }
        
        .panel-body {
            padding: 20px;
        }
        
        .btn-small {
            background: var(--secondary);
            color: var(--primary);
            padding: 6px 15px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 0.85rem;
            font-weight: 600;
            transition: opacity 0.3s;
        }
        
        .btn-small:hover {
            opacity: 0.9;
        }
        
        .no-data {
            text-align: center;
            color: #999;
            padding: 30px;
        }
        
        .activity-list,
        .message-list {
            list-style: none;
        }
        
        .activity-item {
            padding: 12px 0;
            border-bottom: 1px solid #f0f0f0;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 0.9rem;
        }
        
        .activity-item:last-child {
            border-bottom: none;
        }
        
        .activity-action {
            font-weight: 600;
            color: var(--primary);
        }
        
        .activity-user {
            color: #666;
        }
        
        .activity-time {
            margin-left: auto;
            color: #999;
            font-size: 0.8rem;
        }
        
        .message-item {
            padding: 12px 0;
            border-bottom: 1px solid #f0f0f0;
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .message-item:last-child {
            border-bottom: none;
        }
        
        .message-item.unread {
            background: rgba(0, 212, 212, 0.05);
            margin: 0 -20px;
            padding: 12px 20px;
        }
        
        .message-info {
            flex: 1;
        }
        
        .message-subject {
            display: block;
            font-size: 0.85rem;
            color: #666;
            margin-top: 3px;
        }
        
        .message-time {
            color: #999;
            font-size: 0.8rem;
        }
        
        /* Quick Actions */
        .quick-actions {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .quick-actions h2 {
            font-size: 1.2rem;
            color: var(--primary);
            margin-bottom: 20px;
        }
        
        .actions-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
        }
        
        .action-card {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            text-decoration: none;
            color: var(--primary);
            transition: all 0.3s;
        }
        
        .action-card:hover {
            background: var(--secondary);
            color: white;
            transform: translateY(-3px);
        }
        
        .action-icon {
            font-size: 2rem;
            margin-bottom: 10px;
        }
        
        .action-card span {
            display: block;
            font-weight: 600;
            font-size: 0.9rem;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s;
            }
            
            .main-content {
                margin-left: 0;
            }
            
            .dashboard-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="admin-layout">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <div class="sidebar-logo">VCC</div>
                <div class="sidebar-title">
                    <h2>VCC CMS</h2>
                    <p>Admin Panel</p>
                </div>
            </div>
            
            <nav class="sidebar-menu">
                <div class="menu-category">Main</div>
                <a href="index.php" class="menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                        <polyline points="9 22 9 12 15 12 15 22"></polyline>
                    </svg>
                    Dashboard
                </a>
                
                <div class="menu-category">Content</div>
                <a href="posts.php" class="menu-item <?php echo strpos($_SERVER['PHP_SELF'], 'post') !== false ? 'active' : ''; ?>">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                        <polyline points="14 2 14 8 20 8"></polyline>
                        <line x1="16" y1="13" x2="8" y2="13"></line>
                        <line x1="16" y1="17" x2="8" y2="17"></line>
                    </svg>
                    Posts
                </a>
                <a href="categories.php" class="menu-item">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"></path>
                    </svg>
                    Categories
                </a>
                <a href="media.php" class="menu-item">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                        <circle cx="8.5" cy="8.5" r="1.5"></circle>
                        <polyline points="21 15 16 10 5 21"></polyline>
                    </svg>
                    Media Library
                </a>
                
                <div class="menu-category">Communication</div>
                <a href="messages.php" class="menu-item">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                        <polyline points="22,6 12,13 2,6"></polyline>
                    </svg>
                    Messages
                    <?php if ($unreadCount > 0): ?>
                        <span class="menu-badge"><?php echo $unreadCount; ?></span>
                    <?php endif; ?>
                </a>
                
                <div class="menu-category">System</div>
                <a href="settings.php" class="menu-item">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="3"></circle>
                        <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path>
                    </svg>
                    Settings
                </a>
                <a href="users.php" class="menu-item <?php echo hasRole(['super_admin']) ? '' : 'disabled'; ?>">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                        <circle cx="9" cy="7" r="4"></circle>
                        <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                        <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                    </svg>
                    Users
                </a>
                
                <div class="menu-category">Account</div>
                <a href="../index.php" target="_blank" class="menu-item">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path>
                        <polyline points="15 3 21 3 21 9"></polyline>
                        <line x1="10" y1="14" x2="21" y2="3"></line>
                    </svg>
                    View Site
                </a>
                <a href="logout.php" class="menu-item">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                        <polyline points="16 17 21 12 16 7"></polyline>
                        <line x1="21" y1="12" x2="9" y2="12"></line>
                    </svg>
                    Logout
                </a>
            </nav>
        </aside>
        
        <!-- Main Content -->
        <main class="main-content">
            <!-- Top Bar -->
            <div class="top-bar">
                <div class="top-bar-left">
                    <div class="page-title">
                        <h1><?php echo htmlspecialchars($pageTitle); ?></h1>
                    </div>
                </div>
                
                <div class="top-bar-right">
                    <div class="user-menu">
                        <div class="user-avatar">
                            <?php echo strtoupper(substr($_SESSION['admin_username'], 0, 1)); ?>
                        </div>
                        <div class="user-info">
                            <div class="user-name"><?php echo htmlspecialchars($_SESSION['admin_username']); ?></div>
                            <div class="user-role"><?php echo htmlspecialchars(str_replace('_', ' ', $_SESSION['admin_role'])); ?></div>
                        </div>
                    </div>
                    <button class="logout-btn" onclick="window.location.href='logout.php'">Logout</button>
                </div>
            </div>
