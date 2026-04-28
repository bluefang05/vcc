<?php
/**
 * User Management - VCC CMS Admin
 */
session_start();
require_once '../config.php';

// Require login and super_admin role
requireLogin();
checkRole(['super_admin']);

$pdo = getDBConnection();
$message = '';
$messageType = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];
        
        // Add new user
        if ($action === 'add') {
            $username = trim($_POST['username'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            $role = $_POST['role'] ?? 'editor';
            
            if (empty($username) || empty($email) || empty($password)) {
                $message = 'All fields are required.';
                $messageType = 'error';
            } elseif (strlen($password) < 6) {
                $message = 'Password must be at least 6 characters.';
                $messageType = 'error';
            } else {
                try {
                    // Check if username or email already exists
                    $stmt = $pdo->prepare("SELECT id FROM admin_users WHERE username = ? OR email = ?");
                    $stmt->execute([$username, $email]);
                    if ($stmt->fetch()) {
                        $message = 'Username or email already exists.';
                        $messageType = 'error';
                    } else {
                        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                        $stmt = $pdo->prepare("INSERT INTO admin_users (username, email, password, role) VALUES (?, ?, ?, ?)");
                        $stmt->execute([$username, $email, $hashedPassword, $role]);
                        
                        // Log activity
                        logActivity('user_created', "Created user: $username");
                        
                        $message = 'User created successfully!';
                        $messageType = 'success';
                    }
                } catch (Exception $e) {
                    $message = 'Error creating user: ' . $e->getMessage();
                    $messageType = 'error';
                }
            }
        }
        
        // Update user
        if ($action === 'update') {
            $userId = (int)($_POST['user_id'] ?? 0);
            $username = trim($_POST['username'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $role = $_POST['role'] ?? 'editor';
            $password = $_POST['password'] ?? '';
            
            if ($userId && !empty($username) && !empty($email)) {
                try {
                    if (!empty($password)) {
                        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                        $stmt = $pdo->prepare("UPDATE admin_users SET username = ?, email = ?, role = ?, password = ? WHERE id = ?");
                        $stmt->execute([$username, $email, $role, $hashedPassword, $userId]);
                    } else {
                        $stmt = $pdo->prepare("UPDATE admin_users SET username = ?, email = ?, role = ? WHERE id = ?");
                        $stmt->execute([$username, $email, $role, $userId]);
                    }
                    
                    // Log activity
                    logActivity('user_updated', "Updated user: $username");
                    
                    $message = 'User updated successfully!';
                    $messageType = 'success';
                } catch (Exception $e) {
                    $message = 'Error updating user: ' . $e->getMessage();
                    $messageType = 'error';
                }
            }
        }
        
        // Delete user
        if ($action === 'delete') {
            $userId = (int)($_POST['user_id'] ?? 0);
            
            if ($userId) {
                try {
                    // Prevent deleting yourself
                    if ($userId == $_SESSION['admin_id']) {
                        $message = 'You cannot delete your own account.';
                        $messageType = 'error';
                    } else {
                        $stmt = $pdo->prepare("DELETE FROM admin_users WHERE id = ?");
                        $stmt->execute([$userId]);
                        
                        // Log activity
                        logActivity('user_deleted', "Deleted user ID: $userId");
                        
                        $message = 'User deleted successfully!';
                        $messageType = 'success';
                    }
                } catch (Exception $e) {
                    $message = 'Error deleting user: ' . $e->getMessage();
                    $messageType = 'error';
                }
            }
        }
    }
}

// Get all users
try {
    $stmt = $pdo->query("SELECT * FROM admin_users ORDER BY created_at DESC");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $users = [];
    $message = 'Error loading users: ' . $e->getMessage();
    $messageType = 'error';
}

$pageTitle = 'User Management';
include 'includes/header.php';
?>

<div class="dashboard-content">
    <div class="page-header">
        <h1>User Management</h1>
        <p>Manage admin users and their roles</p>
    </div>
    
    <?php if ($message): ?>
        <div class="alert alert-<?php echo $messageType; ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>
    
    <!-- Add New User Button -->
    <div style="margin-bottom: 20px;">
        <button type="button" class="btn-primary" onclick="showAddModal()">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 18px; height: 18px; vertical-align: middle; margin-right: 8px;">
                <line x1="12" y1="5" x2="12" y2="19"></line>
                <line x1="5" y1="12" x2="19" y2="12"></line>
            </svg>
            Add New User
        </button>
    </div>
    
    <!-- Users Table -->
    <div class="panel">
        <div class="panel-body" style="padding: 0;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($users)): ?>
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 40px; color: #999;">No users found</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?php echo $user['id']; ?></td>
                                <td>
                                    <strong><?php echo htmlspecialchars($user['username']); ?></strong>
                                    <?php if ($user['id'] == $_SESSION['admin_id']): ?>
                                        <span style="background: var(--secondary); color: var(--primary); padding: 2px 8px; border-radius: 10px; font-size: 0.7rem; margin-left: 8px;">You</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td>
                                    <span class="badge badge-<?php 
                                        echo $user['role'] === 'super_admin' ? 'danger' : ($user['role'] === 'admin' ? 'warning' : 'success'); 
                                    ?>">
                                        <?php echo ucfirst(str_replace('_', ' ', $user['role'])); ?>
                                    </span>
                                </td>
                                <td><?php echo date('M j, Y', strtotime($user['created_at'])); ?></td>
                                <td>
                                    <button type="button" class="btn-small btn-edit" onclick="showEditModal(<?php echo htmlspecialchars(json_encode($user)); ?>)">
                                        Edit
                                    </button>
                                    <?php if ($user['id'] != $_SESSION['admin_id']): ?>
                                        <button type="button" class="btn-small btn-delete" onclick="confirmDelete(<?php echo $user['id']; ?>, '<?php echo htmlspecialchars($user['username']); ?>')">
                                            Delete
                                        </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add User Modal -->
<div id="addModal" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Add New User</h2>
            <button type="button" class="modal-close" onclick="closeModal('addModal')">&times;</button>
        </div>
        <form method="POST" action="">
            <input type="hidden" name="action" value="add">
            <div class="form-group">
                <label for="add_username">Username *</label>
                <input type="text" id="add_username" name="username" required>
            </div>
            <div class="form-group">
                <label for="add_email">Email *</label>
                <input type="email" id="add_email" name="email" required>
            </div>
            <div class="form-group">
                <label for="add_password">Password *</label>
                <input type="password" id="add_password" name="password" required minlength="6">
            </div>
            <div class="form-group">
                <label for="add_role">Role</label>
                <select id="add_role" name="role">
                    <option value="editor">Editor</option>
                    <option value="admin">Admin</option>
                    <option value="super_admin">Super Admin</option>
                </select>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary" onclick="closeModal('addModal')">Cancel</button>
                <button type="submit" class="btn-primary">Create User</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit User Modal -->
<div id="editModal" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Edit User</h2>
            <button type="button" class="modal-close" onclick="closeModal('editModal')">&times;</button>
        </div>
        <form method="POST" action="">
            <input type="hidden" name="action" value="update">
            <input type="hidden" id="edit_user_id" name="user_id">
            <div class="form-group">
                <label for="edit_username">Username *</label>
                <input type="text" id="edit_username" name="username" required>
            </div>
            <div class="form-group">
                <label for="edit_email">Email *</label>
                <input type="email" id="edit_email" name="email" required>
            </div>
            <div class="form-group">
                <label for="edit_password">New Password (leave blank to keep current)</label>
                <input type="password" id="edit_password" name="password" minlength="6">
            </div>
            <div class="form-group">
                <label for="edit_role">Role</label>
                <select id="edit_role" name="role">
                    <option value="editor">Editor</option>
                    <option value="admin">Admin</option>
                    <option value="super_admin">Super Admin</option>
                </select>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary" onclick="closeModal('editModal')">Cancel</button>
                <button type="submit" class="btn-primary">Update User</button>
            </div>
        </form>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="modal" style="display: none;">
    <div class="modal-content modal-sm">
        <div class="modal-header">
            <h2>Confirm Delete</h2>
            <button type="button" class="modal-close" onclick="closeModal('deleteModal')">&times;</button>
        </div>
        <div class="modal-body">
            <p>Are you sure you want to delete user <strong id="delete_username"></strong>?</p>
            <p style="color: var(--danger); margin-top: 15px;">This action cannot be undone.</p>
        </div>
        <form method="POST" action="">
            <input type="hidden" name="action" value="delete">
            <input type="hidden" id="delete_user_id" name="user_id">
            <div class="modal-footer">
                <button type="button" class="btn-secondary" onclick="closeModal('deleteModal')">Cancel</button>
                <button type="submit" class="btn-delete">Delete User</button>
            </div>
        </form>
    </div>
</div>

<style>
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
    .alert-error {
        background: rgba(220, 53, 69, 0.1);
        color: var(--danger);
        border: 1px solid var(--danger);
    }
    
    .panel {
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        overflow: hidden;
    }
    
    .data-table {
        width: 100%;
        border-collapse: collapse;
    }
    .data-table thead {
        background: #f8f9fa;
    }
    .data-table th {
        padding: 15px 20px;
        text-align: left;
        font-weight: 600;
        color: var(--primary);
        border-bottom: 2px solid #eee;
    }
    .data-table td {
        padding: 15px 20px;
        border-bottom: 1px solid #f0f0f0;
    }
    .data-table tbody tr:hover {
        background: #f8f9fa;
    }
    
    .badge {
        display: inline-block;
        padding: 4px 10px;
        border-radius: 12px;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: capitalize;
    }
    .badge-success { background: rgba(40, 167, 69, 0.1); color: var(--success); }
    .badge-warning { background: rgba(255, 193, 7, 0.1); color: #b58900; }
    .badge-danger { background: rgba(220, 53, 69, 0.1); color: var(--danger); }
    
    .btn-primary {
        background: var(--secondary);
        color: var(--primary);
        border: none;
        padding: 10px 20px;
        border-radius: 8px;
        cursor: pointer;
        font-weight: 600;
        transition: opacity 0.3s;
        display: inline-flex;
        align-items: center;
    }
    .btn-primary:hover { opacity: 0.9; }
    
    .btn-secondary {
        background: #e9ecef;
        color: var(--dark);
        border: none;
        padding: 10px 20px;
        border-radius: 8px;
        cursor: pointer;
        font-weight: 600;
        transition: opacity 0.3s;
    }
    .btn-secondary:hover { opacity: 0.9; }
    
    .btn-small {
        padding: 6px 12px;
        border-radius: 6px;
        font-size: 0.85rem;
        cursor: pointer;
        border: none;
        margin-right: 5px;
        transition: opacity 0.3s;
    }
    .btn-edit {
        background: rgba(0, 212, 212, 0.1);
        color: var(--secondary);
    }
    .btn-delete {
        background: rgba(220, 53, 69, 0.1);
        color: var(--danger);
    }
    
    /* Modal Styles */
    .modal {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.5);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 2000;
    }
    .modal-content {
        background: white;
        border-radius: 12px;
        width: 90%;
        max-width: 500px;
        max-height: 90vh;
        overflow-y: auto;
    }
    .modal-sm {
        max-width: 400px;
    }
    .modal-header {
        padding: 20px;
        border-bottom: 1px solid #eee;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .modal-header h2 {
        font-size: 1.3rem;
        color: var(--primary);
        margin: 0;
    }
    .modal-close {
        background: none;
        border: none;
        font-size: 1.5rem;
        cursor: pointer;
        color: #999;
        padding: 0;
        line-height: 1;
    }
    .modal-close:hover { color: var(--danger); }
    .modal-body {
        padding: 20px;
    }
    .modal-footer {
        padding: 20px;
        border-top: 1px solid #eee;
        display: flex;
        justify-content: flex-end;
        gap: 10px;
    }
    
    .form-group {
        padding: 15px 20px;
    }
    .form-group label {
        display: block;
        margin-bottom: 8px;
        font-weight: 600;
        color: var(--primary);
    }
    .form-group input,
    .form-group select {
        width: 100%;
        padding: 10px 15px;
        border: 1px solid #ddd;
        border-radius: 8px;
        font-size: 1rem;
    }
    .form-group input:focus,
    .form-group select:focus {
        outline: none;
        border-color: var(--secondary);
    }
</style>

<script>
function showAddModal() {
    document.getElementById('addModal').style.display = 'flex';
}

function showEditModal(user) {
    document.getElementById('edit_user_id').value = user.id;
    document.getElementById('edit_username').value = user.username;
    document.getElementById('edit_email').value = user.email;
    document.getElementById('edit_role').value = user.role;
    document.getElementById('edit_password').value = '';
    document.getElementById('editModal').style.display = 'flex';
}

function confirmDelete(userId, username) {
    document.getElementById('delete_user_id').value = userId;
    document.getElementById('delete_username').textContent = username;
    document.getElementById('deleteModal').style.display = 'flex';
}

function closeModal(modalId) {
    document.getElementById(modalId).style.display = 'none';
}

// Close modal when clicking outside
window.onclick = function(event) {
    if (event.target.classList.contains('modal')) {
        event.target.style.display = 'none';
    }
}
</script>

<?php include 'includes/footer.php'; ?>
