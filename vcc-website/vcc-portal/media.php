<?php
/**
 * VCC CMS - Media Library
 */
session_start();
require_once '../config.php';

// Require login
requireLogin();

$pdo = getDBConnection();
$errors = [];
$success = false;

// Handle file upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    $file = $_FILES['file'];
    $title = trim($_POST['title'] ?? $file['name']);
    $alt_text = trim($_POST['alt_text'] ?? '');
    
    // Validate file
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'application/pdf'];
    $maxSize = 5 * 1024 * 1024; // 5MB
    
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errors[] = "Upload error occurred";
    } elseif (!in_array($file['type'], $allowedTypes)) {
        $errors[] = "Invalid file type. Allowed: JPG, PNG, GIF, WEBP, PDF";
    } elseif ($file['size'] > $maxSize) {
        $errors[] = "File too large. Maximum size: 5MB";
    }
    
    if (empty($errors)) {
        // Create uploads directory if it doesn't exist
        $uploadDir = __DIR__ . '/../uploads/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        // Generate unique filename
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid() . '_' . time() . '.' . $extension;
        $filepath = $uploadDir . $filename;
        $webpath = '/uploads/' . $filename;
        
        if (move_uploaded_file($file['tmp_name'], $filepath)) {
            // Save to database
            $stmt = $pdo->prepare("INSERT INTO media_library (filename, filepath, title, alt_text, file_type, file_size) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $file['name'],
                $webpath,
                $title,
                $alt_text,
                $file['type'],
                $file['size']
            ]);
            
            $mediaId = $pdo->lastInsertId();
            logActivity($pdo, $_SESSION['admin_id'], 'uploaded_media', "Uploaded media '$title' (ID: $mediaId)");
            
            header('Location: media.php?success=1');
            exit;
        } else {
            $errors[] = "Failed to save file";
        }
    }
}

// Handle delete
if (isset($_GET['delete']) && isset($_GET['id'])) {
    $stmt = $pdo->prepare("SELECT * FROM media_library WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    $media = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($media) {
        $filepath = __DIR__ . '/../' . $media['filepath'];
        if (file_exists($filepath)) {
            unlink($filepath);
        }
        
        $stmt = $pdo->prepare("DELETE FROM media_library WHERE id = ?");
        $stmt->execute([$_GET['id']]);
        logActivity($pdo, $_SESSION['admin_id'], 'deleted_media', "Deleted media ID {$_GET['id']}");
    }
    
    header('Location: media.php?deleted=1');
    exit;
}

// Get all media
$stmt = $pdo->query("SELECT * FROM media_library ORDER BY created_at DESC");
$mediaFiles = $stmt->fetchAll(PDO::FETCH_ASSOC);

$pageTitle = 'Media Library';
include 'includes/header.php';
?>

<div class="dashboard-content">
    <div class="page-header">
        <h1>Media Library</h1>
        <p>Manage your images and files</p>
    </div>
    
    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success">File uploaded successfully!</div>
    <?php endif; ?>
    
    <?php if (isset($_GET['deleted'])): ?>
        <div class="alert alert-success">File deleted successfully!</div>
    <?php endif; ?>
    
    <?php if (!empty($errors)): ?>
        <div class="alert alert-error">
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?php echo htmlspecialchars($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
    
    <!-- Upload Form -->
    <div class="upload-section">
        <form method="POST" enctype="multipart/form-data" class="upload-form">
            <div class="form-row">
                <input type="file" name="file" id="file" required accept="image/*,.pdf">
                <input type="text" name="title" placeholder="File title (optional)">
                <input type="text" name="alt_text" placeholder="Alt text (optional)">
                <button type="submit" class="btn-upload">Upload</button>
            </div>
        </form>
    </div>
    
    <!-- Media Grid -->
    <div class="media-grid">
        <?php if (empty($mediaFiles)): ?>
            <div class="no-media">
                <p>No media files yet. Upload your first file!</p>
            </div>
        <?php else: ?>
            <?php foreach ($mediaFiles as $media): ?>
                <div class="media-item">
                    <div class="media-preview">
                        <?php if (strpos($media['file_type'], 'image') === 0): ?>
                            <img src="<?php echo htmlspecialchars($media['filepath']); ?>" alt="<?php echo htmlspecialchars($media['alt_text'] ?: $media['title']); ?>">
                        <?php else: ?>
                            <div class="file-icon">📄</div>
                        <?php endif; ?>
                    </div>
                    <div class="media-info">
                        <h4><?php echo htmlspecialchars($media['title']); ?></h4>
                        <p class="media-meta"><?php echo round($media['file_size'] / 1024, 1); ?> KB</p>
                        <p class="media-path"><?php echo htmlspecialchars($media['filepath']); ?></p>
                    </div>
                    <div class="media-actions">
                        <button class="btn-copy" onclick="copyToClipboard('<?php echo htmlspecialchars($media['filepath']); ?>')">Copy URL</button>
                        <a href="media.php?delete=1&id=<?php echo $media['id']; ?>" class="btn-delete" onclick="return confirm('Delete this file?')">Delete</a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<style>
    .upload-section {
        background: white;
        padding: 25px;
        border-radius: 12px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        margin-bottom: 30px;
    }
    
    .upload-form .form-row {
        display: flex;
        gap: 15px;
        align-items: center;
        flex-wrap: wrap;
    }
    
    .upload-form input[type="file"] {
        flex: 1;
        min-width: 200px;
    }
    
    .upload-form input[type="text"] {
        padding: 10px 15px;
        border: 1px solid #ddd;
        border-radius: 6px;
        font-size: 0.9rem;
        min-width: 150px;
    }
    
    .btn-upload {
        padding: 10px 25px;
        background: var(--secondary);
        color: var(--primary);
        border: none;
        border-radius: 6px;
        font-weight: 600;
        cursor: pointer;
    }
    
    .media-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
        gap: 20px;
    }
    
    .media-item {
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        overflow: hidden;
        transition: transform 0.3s;
    }
    
    .media-item:hover {
        transform: translateY(-3px);
    }
    
    .media-preview {
        height: 180px;
        background: #f8f9fa;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
    }
    
    .media-preview img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    
    .file-icon {
        font-size: 4rem;
    }
    
    .media-info {
        padding: 15px;
    }
    
    .media-info h4 {
        font-size: 0.95rem;
        color: var(--primary);
        margin-bottom: 5px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    
    .media-meta {
        font-size: 0.8rem;
        color: #999;
        margin-bottom: 5px;
    }
    
    .media-path {
        font-size: 0.75rem;
        color: #666;
        word-break: break-all;
    }
    
    .media-actions {
        padding: 15px;
        border-top: 1px solid #eee;
        display: flex;
        gap: 10px;
    }
    
    .btn-copy {
        flex: 1;
        padding: 8px;
        background: #e9ecef;
        color: var(--dark);
        border: none;
        border-radius: 5px;
        font-size: 0.8rem;
        cursor: pointer;
    }
    
    .btn-delete {
        padding: 8px 15px;
        background: rgba(220, 53, 69, 0.1);
        color: var(--danger);
        border: none;
        border-radius: 5px;
        font-size: 0.8rem;
        cursor: pointer;
        text-decoration: none;
    }
    
    .no-media {
        grid-column: 1 / -1;
        text-align: center;
        padding: 60px;
        color: #999;
        background: white;
        border-radius: 12px;
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
    
    .alert-error {
        background: rgba(220, 53, 69, 0.1);
        color: var(--danger);
        border: 1px solid var(--danger);
    }
    
    .alert-error ul {
        margin: 0;
        padding-left: 20px;
    }
</style>

<script>
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(function() {
        alert('URL copied to clipboard!');
    }).catch(function(err) {
        alert('Failed to copy URL');
    });
}
</script>

<?php include 'includes/footer.php'; ?>
