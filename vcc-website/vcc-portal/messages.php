<?php
/**
 * Messages Management - View contact form submissions
 */
session_start();
require_once '../config.php';
requireLogin();

$pdo = getDBConnection();
$pageTitle = 'Messages';

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $messageId = (int)$_POST['message_id'];
    $action = $_POST['action'];
    
    if ($action === 'mark_read') {
        $stmt = $pdo->prepare("UPDATE contact_messages SET status = 'read' WHERE id = ?");
        $stmt->execute([$messageId]);
        logActivity('message_marked_read', "Message ID $messageId marked as read");
    } elseif ($action === 'mark_archived') {
        $stmt = $pdo->prepare("UPDATE contact_messages SET status = 'archived' WHERE id = ?");
        $stmt->execute([$messageId]);
        logActivity('message_archived', "Message ID $messageId archived");
    } elseif ($action === 'delete') {
        $stmt = $pdo->prepare("DELETE FROM contact_messages WHERE id = ?");
        $stmt->execute([$messageId]);
        logActivity('message_deleted', "Message ID $messageId deleted");
    }
    
    header('Location: messages.php');
    exit;
}

// Get filter
$filter = $_GET['filter'] ?? 'all';
$search = trim($_GET['search'] ?? '');

// Build query
$where = [];
$params = [];

if ($filter === 'new') {
    $where[] = "status = 'new'";
} elseif ($filter === 'read') {
    $where[] = "status = 'read'";
} elseif ($filter === 'archived') {
    $where[] = "status = 'archived'";
}

if (!empty($search)) {
    $where[] = "(name LIKE ? OR email LIKE ? OR subject LIKE ?)";
    $searchParam = "%$search%";
    $params = array_merge($params, [$searchParam, $searchParam, $searchParam]);
}

$whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

$stmt = $pdo->prepare("SELECT * FROM contact_messages $whereClause ORDER BY created_at DESC");
$stmt->execute($params);
$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Count by status
$statusCounts = [
    'all' => count($messages),
    'new' => 0,
    'read' => 0,
    'archived' => 0
];

foreach ($messages as $msg) {
    if (isset($statusCounts[$msg['status']])) {
        $statusCounts[$msg['status']]++;
    }
}

include 'includes/header.php';
?>

<div class="dashboard-content">
    <div class="page-header">
        <h1>Contact Messages</h1>
        <p>View and manage messages from your website contact form</p>
    </div>
    
    <!-- Filters -->
    <div class="messages-filters" style="background: white; padding: 20px; border-radius: 12px; margin-bottom: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.05);">
        <div style="display: flex; gap: 15px; flex-wrap: wrap; align-items: center;">
            <a href="messages.php?filter=all" class="filter-btn <?php echo $filter === 'all' ? 'active' : ''; ?>" style="padding: 8px 16px; border-radius: 6px; text-decoration: none; color: <?php echo $filter === 'all' ? 'white' : '#666'; ?>; background: <?php echo $filter === 'all' ? 'var(--secondary)' : '#f0f0f0'; ?>; font-weight: 600;">
                All (<?php echo count($messages); ?>)
            </a>
            <a href="messages.php?filter=new" class="filter-btn <?php echo $filter === 'new' ? 'active' : ''; ?>" style="padding: 8px 16px; border-radius: 6px; text-decoration: none; color: <?php echo $filter === 'new' ? 'white' : '#666'; ?>; background: <?php echo $filter === 'new' ? 'var(--secondary)' : '#f0f0f0'; ?>; font-weight: 600;">
                New (<?php echo array_sum(array_column(array_filter($messages, fn($m) => $m['status'] === 'new'), 'id') ? count(array_filter($messages, fn($m) => $m['status'] === 'new')) : 0; ?>)
            </a>
            <a href="messages.php?filter=read" class="filter-btn <?php echo $filter === 'read' ? 'active' : ''; ?>" style="padding: 8px 16px; border-radius: 6px; text-decoration: none; color: <?php echo $filter === 'read' ? 'white' : '#666'; ?>; background: <?php echo $filter === 'read' ? 'var(--secondary)' : '#f0f0f0'; ?>; font-weight: 600;">
                Read
            </a>
            <a href="messages.php?filter=archived" class="filter-btn <?php echo $filter === 'archived' ? 'active' : ''; ?>" style="padding: 8px 16px; border-radius: 6px; text-decoration: none; color: <?php echo $filter === 'archived' ? 'white' : '#666'; ?>; background: <?php echo $filter === 'archived' ? 'var(--secondary)' : '#f0f0f0'; ?>; font-weight: 600;">
                Archived
            </a>
            
            <form method="GET" style="margin-left: auto; display: flex; gap: 10px;">
                <input type="text" name="search" placeholder="Search messages..." value="<?php echo htmlspecialchars($search); ?>" style="padding: 8px 15px; border: 2px solid #e0e0e0; border-radius: 6px; font-size: 0.9rem; width: 250px;">
                <button type="submit" style="padding: 8px 20px; background: var(--primary); color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: 600;">Search</button>
                <?php if (!empty($search)): ?>
                    <a href="messages.php" style="padding: 8px 15px; background: #f0f0f0; color: #666; text-decoration: none; border-radius: 6px; font-weight: 600;">Clear</a>
                <?php endif; ?>
            </form>
        </div>
    </div>
    
    <!-- Messages List -->
    <div style="background: white; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); overflow: hidden;">
        <?php if (empty($messages)): ?>
            <div style="padding: 60px 20px; text-align: center; color: #999;">
                <svg style="width: 60px; height: 60px; margin: 0 auto 20px; opacity: 0.3;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                    <polyline points="22,6 12,13 2,6"></polyline>
                </svg>
                <h3>No messages found</h3>
                <p>There are no messages matching your criteria.</p>
            </div>
        <?php else: ?>
            <?php foreach ($messages as $msg): ?>
                <div class="message-row" style="padding: 20px; border-bottom: 1px solid #f0f0f0; <?php echo $msg['status'] === 'new' ? 'background: rgba(0, 212, 212, 0.05);' : ''; ?>">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 20px;">
                        <div style="flex: 1;">
                            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 8px;">
                                <span class="status-badge" style="padding: 3px 10px; border-radius: 10px; font-size: 0.75rem; font-weight: 600; text-transform: uppercase; background: <?php echo $msg['status'] === 'new' ? '#ffc107' : ($msg['status'] === 'read' ? '#28a745' : '#6c757d'); ?>; color: <?php echo $msg['status'] === 'new' ? '#000' : '#fff'; ?>;">
                                    <?php echo htmlspecialchars($msg['status']); ?>
                                </span>
                                <h3 style="font-size: 1.1rem; color: var(--primary); margin: 0;"><?php echo htmlspecialchars($msg['subject'] ?: '(No Subject)'); ?></h3>
                            </div>
                            <div style="display: flex; gap: 20px; margin-bottom: 15px; font-size: 0.9rem; color: #666;">
                                <span><strong>From:</strong> <?php echo htmlspecialchars($msg['name']); ?> (<?php echo htmlspecialchars($msg['email']); ?>)</span>
                                <span><strong>Date:</strong> <?php echo date('M j, Y g:i a', strtotime($msg['created_at'])); ?></span>
                            </div>
                            <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; color: #333; line-height: 1.6;">
                                <?php echo nl2br(htmlspecialchars($msg['message'])); ?>
                            </div>
                        </div>
                        
                        <div style="display: flex; flex-direction: column; gap: 8px;">
                            <?php if ($msg['status'] === 'new'): ?>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="message_id" value="<?php echo $msg['id']; ?>">
                                    <input type="hidden" name="action" value="mark_read">
                                    <button type="submit" style="padding: 8px 15px; background: #28a745; color: white; border: none; border-radius: 6px; cursor: pointer; font-size: 0.85rem; font-weight: 600;">Mark Read</button>
                                </form>
                            <?php endif; ?>
                            
                            <?php if ($msg['status'] !== 'archived'): ?>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="message_id" value="<?php echo $msg['id']; ?>">
                                    <input type="hidden" name="action" value="mark_archived">
                                    <button type="submit" style="padding: 8px 15px; background: #6c757d; color: white; border: none; border-radius: 6px; cursor: pointer; font-size: 0.85rem; font-weight: 600;">Archive</button>
                                </form>
                            <?php endif; ?>
                            
                            <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this message?');">
                                <input type="hidden" name="message_id" value="<?php echo $msg['id']; ?>">
                                <input type="hidden" name="action" value="delete">
                                <button type="submit" style="padding: 8px 15px; background: #dc3545; color: white; border: none; border-radius: 6px; cursor: pointer; font-size: 0.85rem; font-weight: 600;">Delete</button>
                            </form>
                            
                            <a href="https://wa.me/<?php echo preg_replace('/[^0-9]/', '', getSetting('whatsapp_number')); ?>?text=Hello <?php echo urlencode($msg['name']); ?>," target="_blank" style="padding: 8px 15px; background: #25D366; color: white; text-align: center; text-decoration: none; border-radius: 6px; font-size: 0.85rem; font-weight: 600;">
                                Reply on WhatsApp
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
