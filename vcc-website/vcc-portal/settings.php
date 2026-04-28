<?php
/**
 * VCC CMS - Settings Page
 */
session_start();
require_once '../config.php';

// Require login
requireLogin();

$pdo = getDBConnection();
$errors = [];
$success = false;

// Get current settings
$stmt = $pdo->query("SELECT * FROM settings");
$settingsRows = $stmt->fetchAll(PDO::FETCH_ASSOC);
$settings = [];
foreach ($settingsRows as $row) {
    $settings[$row['setting_key']] = $row['setting_value'];
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($_POST as $key => $value) {
        if (strpos($key, 'setting_') === 0) {
            $settingKey = str_replace('setting_', '', $key);
            $stmt = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?");
            $stmt->execute([$settingKey, $value, $value]);
        }
    }
    
    logActivity($pdo, $_SESSION['admin_id'], 'updated_settings', "Updated site settings");
    header('Location: settings.php?success=1');
    exit;
}

$pageTitle = 'Settings';
include 'includes/header.php';
?>

<div class="dashboard-content">
    <div class="page-header">
        <h1>Site Settings</h1>
        <p>Configure your website settings</p>
    </div>
    
    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success">Settings saved successfully!</div>
    <?php endif; ?>
    
    <form method="POST" class="settings-form">
        <div class="settings-sections">
            <!-- General Settings -->
            <div class="settings-card">
                <h2>General Information</h2>
                
                <div class="form-group">
                    <label for="setting_site_title">Site Title</label>
                    <input type="text" id="setting_site_title" name="setting_site_title" value="<?php echo htmlspecialchars($settings['site_title'] ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label for="setting_site_description">Site Description</label>
                    <textarea id="setting_site_description" name="setting_site_description" rows="3"><?php echo htmlspecialchars($settings['site_description'] ?? ''); ?></textarea>
                </div>
                
                <div class="form-group">
                    <label for="setting_admin_email">Admin Email</label>
                    <input type="email" id="setting_admin_email" name="setting_admin_email" value="<?php echo htmlspecialchars($settings['admin_email'] ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label for="setting_default_lang">Default Language</label>
                    <select id="setting_default_lang" name="setting_default_lang">
                        <option value="en" <?php echo ($settings['default_lang'] ?? '') === 'en' ? 'selected' : ''; ?>>English</option>
                        <option value="es" <?php echo ($settings['default_lang'] ?? '') === 'es' ? 'selected' : ''; ?>>Spanish</option>
                    </select>
                </div>
            </div>
            
            <!-- SEO Settings -->
            <div class="settings-card">
                <h2>SEO Settings</h2>
                
                <div class="form-group">
                    <label for="setting_meta_keywords">Meta Keywords</label>
                    <input type="text" id="setting_meta_keywords" name="setting_meta_keywords" value="<?php echo htmlspecialchars($settings['meta_keywords'] ?? ''); ?>" placeholder="keyword1, keyword2, keyword3">
                </div>
                
                <div class="form-group">
                    <label for="setting_meta_author">Meta Author</label>
                    <input type="text" id="setting_meta_author" name="setting_meta_author" value="<?php echo htmlspecialchars($settings['meta_author'] ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label for="setting_google_analytics">Google Analytics ID</label>
                    <input type="text" id="setting_google_analytics" name="setting_google_analytics" value="<?php echo htmlspecialchars($settings['google_analytics'] ?? ''); ?>" placeholder="UA-XXXXXXXXX-X">
                </div>
            </div>
            
            <!-- Contact Settings -->
            <div class="settings-card">
                <h2>Contact Settings</h2>
                
                <div class="form-group">
                    <label for="setting_contact_email">Contact Form Email</label>
                    <input type="email" id="setting_contact_email" name="setting_contact_email" value="<?php echo htmlspecialchars($settings['contact_email'] ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label for="setting_company_address">Company Address</label>
                    <textarea id="setting_company_address" name="setting_company_address" rows="3"><?php echo htmlspecialchars($settings['company_address'] ?? ''); ?></textarea>
                </div>
                
                <div class="form-group">
                    <label for="setting_phone_number">Phone Number</label>
                    <input type="text" id="setting_phone_number" name="setting_phone_number" value="<?php echo htmlspecialchars($settings['phone_number'] ?? ''); ?>">
                </div>
            </div>
            
            <!-- Social Media -->
            <div class="settings-card">
                <h2>Social Media Links</h2>
                
                <div class="form-group">
                    <label for="setting_facebook_url">Facebook URL</label>
                    <input type="url" id="setting_facebook_url" name="setting_facebook_url" value="<?php echo htmlspecialchars($settings['facebook_url'] ?? ''); ?>" placeholder="https://facebook.com/yourpage">
                </div>
                
                <div class="form-group">
                    <label for="setting_twitter_url">Twitter/X URL</label>
                    <input type="url" id="setting_twitter_url" name="setting_twitter_url" value="<?php echo htmlspecialchars($settings['twitter_url'] ?? ''); ?>" placeholder="https://twitter.com/yourhandle">
                </div>
                
                <div class="form-group">
                    <label for="setting_linkedin_url">LinkedIn URL</label>
                    <input type="url" id="setting_linkedin_url" name="setting_linkedin_url" value="<?php echo htmlspecialchars($settings['linkedin_url'] ?? ''); ?>" placeholder="https://linkedin.com/company/yourcompany">
                </div>
                
                <div class="form-group">
                    <label for="setting_instagram_url">Instagram URL</label>
                    <input type="url" id="setting_instagram_url" name="setting_instagram_url" value="<?php echo htmlspecialchars($settings['instagram_url'] ?? ''); ?>" placeholder="https://instagram.com/yourprofile">
                </div>
            </div>
        </div>
        
        <div class="form-actions">
            <button type="submit" class="btn-save">Save Settings</button>
        </div>
    </form>
</div>

<style>
    .settings-form {
        max-width: 900px;
    }
    
    .settings-sections {
        display: flex;
        flex-direction: column;
        gap: 25px;
    }
    
    .settings-card {
        background: white;
        padding: 30px;
        border-radius: 12px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }
    
    .settings-card h2 {
        font-size: 1.2rem;
        color: var(--primary);
        margin-bottom: 25px;
        padding-bottom: 15px;
        border-bottom: 2px solid #f0f0f0;
    }
    
    .form-group {
        margin-bottom: 20px;
    }
    
    .form-group:last-child {
        margin-bottom: 0;
    }
    
    .form-group label {
        display: block;
        font-weight: 600;
        color: var(--primary);
        margin-bottom: 8px;
        font-size: 0.9rem;
    }
    
    .form-group input[type="text"],
    .form-group input[type="email"],
    .form-group input[type="url"],
    .form-group textarea,
    .form-group select {
        width: 100%;
        padding: 12px 15px;
        border: 1px solid #ddd;
        border-radius: 6px;
        font-size: 0.95rem;
        font-family: inherit;
        transition: border-color 0.3s;
    }
    
    .form-group input:focus,
    .form-group textarea:focus,
    .form-group select:focus {
        outline: none;
        border-color: var(--secondary);
    }
    
    .form-group textarea {
        resize: vertical;
        min-height: 80px;
    }
    
    .form-actions {
        margin-top: 30px;
        display: flex;
        justify-content: flex-end;
    }
    
    .btn-save {
        padding: 14px 35px;
        background: var(--secondary);
        color: var(--primary);
        border: none;
        border-radius: 6px;
        font-weight: 600;
        font-size: 1rem;
        cursor: pointer;
        transition: opacity 0.3s;
    }
    
    .btn-save:hover {
        opacity: 0.9;
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
