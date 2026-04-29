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
                    <label for="setting_site_tagline">Site Tagline</label>
                    <input type="text" id="setting_site_tagline" name="setting_site_tagline" value="<?php echo htmlspecialchars($settings['site_tagline'] ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label for="setting_meta_description">Meta Description</label>
                    <textarea id="setting_meta_description" name="setting_meta_description" rows="3"><?php echo htmlspecialchars($settings['meta_description'] ?? ''); ?></textarea>
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
            
            <!-- Hero Section -->
            <div class="settings-card">
                <h2>Hero Section</h2>
                
                <div class="form-group">
                    <label for="setting_hero_title">Hero Title</label>
                    <input type="text" id="setting_hero_title" name="setting_hero_title" value="<?php echo htmlspecialchars($settings['hero_title'] ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label for="setting_hero_subtitle">Hero Subtitle</label>
                    <input type="text" id="setting_hero_subtitle" name="setting_hero_subtitle" value="<?php echo htmlspecialchars($settings['hero_subtitle'] ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label for="setting_hero_description">Hero Description</label>
                    <textarea id="setting_hero_description" name="setting_hero_description" rows="3"><?php echo htmlspecialchars($settings['hero_description'] ?? ''); ?></textarea>
                </div>
            </div>
            
            <!-- Logo Settings -->
            <div class="settings-card">
                <h2>Logo Settings</h2>
                
                <div class="form-group">
                    <label for="setting_logo_url">Logo URL/Path</label>
                    <input type="text" id="setting_logo_url" name="setting_logo_url" value="<?php echo htmlspecialchars($settings['logo_url'] ?? ''); ?>" placeholder="assets/logo.svg">
                </div>
                
                <div class="form-group">
                    <label for="setting_logo_alt">Logo Alt Text</label>
                    <input type="text" id="setting_logo_alt" name="setting_logo_alt" value="<?php echo htmlspecialchars($settings['logo_alt'] ?? ''); ?>" placeholder="VCC Logo">
                </div>
            </div>
            
            <!-- Services Section -->
            <div class="settings-card">
                <h2>Services Section</h2>
                
                <div class="form-group">
                    <label for="setting_services_title">Services Title</label>
                    <input type="text" id="setting_services_title" name="setting_services_title" value="<?php echo htmlspecialchars($settings['services_title'] ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label for="setting_services_subtitle">Services Subtitle</label>
                    <input type="text" id="setting_services_subtitle" name="setting_services_subtitle" value="<?php echo htmlspecialchars($settings['services_subtitle'] ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label for="setting_services_intro_1">Services Intro Text 1 (HTML allowed)</label>
                    <textarea id="setting_services_intro_1" name="setting_services_intro_1" rows="3"><?php echo htmlspecialchars($settings['services_intro_1'] ?? ''); ?></textarea>
                </div>
                
                <div class="form-group">
                    <label for="setting_services_intro_2">Services Intro Text 2 (HTML allowed)</label>
                    <textarea id="setting_services_intro_2" name="setting_services_intro_2" rows="3"><?php echo htmlspecialchars($settings['services_intro_2'] ?? ''); ?></textarea>
                </div>
                
                <hr style="margin: 25px 0; border: none; border-top: 1px solid #eee;">
                <h3>Service Cards</h3>
                
                <h4>Service 1</h4>
                <div class="form-group">
                    <label for="setting_service_1_icon">Service 1 Icon (Emoji)</label>
                    <input type="text" id="setting_service_1_icon" name="setting_service_1_icon" value="<?php echo htmlspecialchars($settings['service_1_icon'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label for="setting_service_1_title">Service 1 Title</label>
                    <input type="text" id="setting_service_1_title" name="setting_service_1_title" value="<?php echo htmlspecialchars($settings['service_1_title'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label for="setting_service_1_desc">Service 1 Description</label>
                    <textarea id="setting_service_1_desc" name="setting_service_1_desc" rows="3"><?php echo htmlspecialchars($settings['service_1_desc'] ?? ''); ?></textarea>
                </div>
                
                <h4>Service 2</h4>
                <div class="form-group">
                    <label for="setting_service_2_icon">Service 2 Icon (Emoji)</label>
                    <input type="text" id="setting_service_2_icon" name="setting_service_2_icon" value="<?php echo htmlspecialchars($settings['service_2_icon'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label for="setting_service_2_title">Service 2 Title</label>
                    <input type="text" id="setting_service_2_title" name="setting_service_2_title" value="<?php echo htmlspecialchars($settings['service_2_title'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label for="setting_service_2_desc">Service 2 Description</label>
                    <textarea id="setting_service_2_desc" name="setting_service_2_desc" rows="3"><?php echo htmlspecialchars($settings['service_2_desc'] ?? ''); ?></textarea>
                </div>
                
                <h4>Service 3</h4>
                <div class="form-group">
                    <label for="setting_service_3_icon">Service 3 Icon (Emoji)</label>
                    <input type="text" id="setting_service_3_icon" name="setting_service_3_icon" value="<?php echo htmlspecialchars($settings['service_3_icon'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label for="setting_service_3_title">Service 3 Title</label>
                    <input type="text" id="setting_service_3_title" name="setting_service_3_title" value="<?php echo htmlspecialchars($settings['service_3_title'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label for="setting_service_3_desc">Service 3 Description</label>
                    <textarea id="setting_service_3_desc" name="setting_service_3_desc" rows="3"><?php echo htmlspecialchars($settings['service_3_desc'] ?? ''); ?></textarea>
                </div>
                
                <h4>Service 4</h4>
                <div class="form-group">
                    <label for="setting_service_4_icon">Service 4 Icon (Emoji)</label>
                    <input type="text" id="setting_service_4_icon" name="setting_service_4_icon" value="<?php echo htmlspecialchars($settings['service_4_icon'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label for="setting_service_4_title">Service 4 Title</label>
                    <input type="text" id="setting_service_4_title" name="setting_service_4_title" value="<?php echo htmlspecialchars($settings['service_4_title'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label for="setting_service_4_desc">Service 4 Description</label>
                    <textarea id="setting_service_4_desc" name="setting_service_4_desc" rows="3"><?php echo htmlspecialchars($settings['service_4_desc'] ?? ''); ?></textarea>
                </div>
                
                <h4>Service 5</h4>
                <div class="form-group">
                    <label for="setting_service_5_icon">Service 5 Icon (Emoji)</label>
                    <input type="text" id="setting_service_5_icon" name="setting_service_5_icon" value="<?php echo htmlspecialchars($settings['service_5_icon'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label for="setting_service_5_title">Service 5 Title</label>
                    <input type="text" id="setting_service_5_title" name="setting_service_5_title" value="<?php echo htmlspecialchars($settings['service_5_title'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label for="setting_service_5_desc">Service 5 Description</label>
                    <textarea id="setting_service_5_desc" name="setting_service_5_desc" rows="3"><?php echo htmlspecialchars($settings['service_5_desc'] ?? ''); ?></textarea>
                </div>
                
                <h4>Service 6</h4>
                <div class="form-group">
                    <label for="setting_service_6_icon">Service 6 Icon (Emoji)</label>
                    <input type="text" id="setting_service_6_icon" name="setting_service_6_icon" value="<?php echo htmlspecialchars($settings['service_6_icon'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label for="setting_service_6_title">Service 6 Title</label>
                    <input type="text" id="setting_service_6_title" name="setting_service_6_title" value="<?php echo htmlspecialchars($settings['service_6_title'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label for="setting_service_6_desc">Service 6 Description</label>
                    <textarea id="setting_service_6_desc" name="setting_service_6_desc" rows="3"><?php echo htmlspecialchars($settings['service_6_desc'] ?? ''); ?></textarea>
                </div>
            </div>
            
            <!-- Values Section -->
            <div class="settings-card">
                <h2>Values Section</h2>
                
                <div class="form-group">
                    <label for="setting_values_title">Values Title</label>
                    <input type="text" id="setting_values_title" name="setting_values_title" value="<?php echo htmlspecialchars($settings['values_title'] ?? ''); ?>">
                </div>
                
                <hr style="margin: 25px 0; border: none; border-top: 1px solid #eee;">
                
                <h4>Value 1</h4>
                <div class="form-group">
                    <label for="setting_value_1_number">Value 1 Number</label>
                    <input type="text" id="setting_value_1_number" name="setting_value_1_number" value="<?php echo htmlspecialchars($settings['value_1_number'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label for="setting_value_1_title">Value 1 Title</label>
                    <input type="text" id="setting_value_1_title" name="setting_value_1_title" value="<?php echo htmlspecialchars($settings['value_1_title'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label for="setting_value_1_desc">Value 1 Description</label>
                    <textarea id="setting_value_1_desc" name="setting_value_1_desc" rows="3"><?php echo htmlspecialchars($settings['value_1_desc'] ?? ''); ?></textarea>
                </div>
                
                <h4>Value 2</h4>
                <div class="form-group">
                    <label for="setting_value_2_number">Value 2 Number</label>
                    <input type="text" id="setting_value_2_number" name="setting_value_2_number" value="<?php echo htmlspecialchars($settings['value_2_number'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label for="setting_value_2_title">Value 2 Title</label>
                    <input type="text" id="setting_value_2_title" name="setting_value_2_title" value="<?php echo htmlspecialchars($settings['value_2_title'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label for="setting_value_2_desc">Value 2 Description</label>
                    <textarea id="setting_value_2_desc" name="setting_value_2_desc" rows="3"><?php echo htmlspecialchars($settings['value_2_desc'] ?? ''); ?></textarea>
                </div>
                
                <h4>Value 3</h4>
                <div class="form-group">
                    <label for="setting_value_3_number">Value 3 Number</label>
                    <input type="text" id="setting_value_3_number" name="setting_value_3_number" value="<?php echo htmlspecialchars($settings['value_3_number'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label for="setting_value_3_title">Value 3 Title</label>
                    <input type="text" id="setting_value_3_title" name="setting_value_3_title" value="<?php echo htmlspecialchars($settings['value_3_title'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label for="setting_value_3_desc">Value 3 Description</label>
                    <textarea id="setting_value_3_desc" name="setting_value_3_desc" rows="3"><?php echo htmlspecialchars($settings['value_3_desc'] ?? ''); ?></textarea>
                </div>
                
                <h4>Value 4</h4>
                <div class="form-group">
                    <label for="setting_value_4_number">Value 4 Number</label>
                    <input type="text" id="setting_value_4_number" name="setting_value_4_number" value="<?php echo htmlspecialchars($settings['value_4_number'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label for="setting_value_4_title">Value 4 Title</label>
                    <input type="text" id="setting_value_4_title" name="setting_value_4_title" value="<?php echo htmlspecialchars($settings['value_4_title'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label for="setting_value_4_desc">Value 4 Description</label>
                    <textarea id="setting_value_4_desc" name="setting_value_4_desc" rows="3"><?php echo htmlspecialchars($settings['value_4_desc'] ?? ''); ?></textarea>
                </div>
                
                <h4>Value 5</h4>
                <div class="form-group">
                    <label for="setting_value_5_number">Value 5 Number</label>
                    <input type="text" id="setting_value_5_number" name="setting_value_5_number" value="<?php echo htmlspecialchars($settings['value_5_number'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label for="setting_value_5_title">Value 5 Title</label>
                    <input type="text" id="setting_value_5_title" name="setting_value_5_title" value="<?php echo htmlspecialchars($settings['value_5_title'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label for="setting_value_5_desc">Value 5 Description</label>
                    <textarea id="setting_value_5_desc" name="setting_value_5_desc" rows="3"><?php echo htmlspecialchars($settings['value_5_desc'] ?? ''); ?></textarea>
                </div>
            </div>
            
            <!-- CTA Section -->
            <div class="settings-card">
                <h2>Call To Action Section</h2>
                
                <div class="form-group">
                    <label for="setting_cta_headline">CTA Headline</label>
                    <input type="text" id="setting_cta_headline" name="setting_cta_headline" value="<?php echo htmlspecialchars($settings['cta_headline'] ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label for="setting_cta_subheadline">CTA Subheadline</label>
                    <input type="text" id="setting_cta_subheadline" name="setting_cta_subheadline" value="<?php echo htmlspecialchars($settings['cta_subheadline'] ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label for="setting_cta_button_text">CTA Button Text</label>
                    <input type="text" id="setting_cta_button_text" name="setting_cta_button_text" value="<?php echo htmlspecialchars($settings['cta_button_text'] ?? ''); ?>">
                </div>
            </div>
            
            <!-- Contact Section -->
            <div class="settings-card">
                <h2>Contact Section</h2>
                
                <div class="form-group">
                    <label for="setting_contact_title">Contact Title</label>
                    <input type="text" id="setting_contact_title" name="setting_contact_title" value="<?php echo htmlspecialchars($settings['contact_title'] ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label for="setting_contact_form_title">Contact Form Title</label>
                    <input type="text" id="setting_contact_form_title" name="setting_contact_form_title" value="<?php echo htmlspecialchars($settings['contact_form_title'] ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label for="setting_contact_button_text">Contact Button Text</label>
                    <input type="text" id="setting_contact_button_text" name="setting_contact_button_text" value="<?php echo htmlspecialchars($settings['contact_button_text'] ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label for="setting_company_address">Company Address</label>
                    <textarea id="setting_company_address" name="setting_company_address" rows="3"><?php echo htmlspecialchars($settings['company_address'] ?? ''); ?></textarea>
                </div>
                
                <div class="form-group">
                    <label for="setting_company_phone">Company Phone</label>
                    <input type="text" id="setting_company_phone" name="setting_company_phone" value="<?php echo htmlspecialchars($settings['company_phone'] ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label for="setting_whatsapp_availability">WhatsApp Availability</label>
                    <input type="text" id="setting_whatsapp_availability" name="setting_whatsapp_availability" value="<?php echo htmlspecialchars($settings['whatsapp_availability'] ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label for="setting_contact_whatsapp">Contact WhatsApp URL</label>
                    <input type="url" id="setting_contact_whatsapp" name="setting_contact_whatsapp" value="<?php echo htmlspecialchars($settings['contact_whatsapp'] ?? ''); ?>" placeholder="https://wa.me/18095866653">
                </div>
                
                <div class="form-group">
                    <label for="setting_whatsapp_number">WhatsApp Number URL (Navigation)</label>
                    <input type="url" id="setting_whatsapp_number" name="setting_whatsapp_number" value="<?php echo htmlspecialchars($settings['whatsapp_number'] ?? ''); ?>" placeholder="https://wa.me/18095866653">
                </div>
            </div>
            
            <!-- Footer Section -->
            <div class="settings-card">
                <h2>Footer Section</h2>
                
                <div class="form-group">
                    <label for="setting_footer_location">Footer Location</label>
                    <input type="text" id="setting_footer_location" name="setting_footer_location" value="<?php echo htmlspecialchars($settings['footer_location'] ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label for="setting_show_blog_link">Show Blog Link in Footer</label>
                    <select id="setting_show_blog_link" name="setting_show_blog_link">
                        <option value="true" <?php echo ($settings['show_blog_link'] ?? 'false') === 'true' ? 'selected' : ''; ?>>Yes</option>
                        <option value="false" <?php echo ($settings['show_blog_link'] ?? 'false') === 'false' ? 'selected' : ''; ?>>No</option>
                    </select>
                </div>
            </div>
            
            <!-- Social Media -->
            <div class="settings-card">
                <h2>Social Media Links</h2>
                
                <div class="form-group">
                    <label for="setting_social_facebook">Facebook URL</label>
                    <input type="url" id="setting_social_facebook" name="setting_social_facebook" value="<?php echo htmlspecialchars($settings['social_facebook'] ?? ''); ?>" placeholder="https://facebook.com/yourpage">
                </div>
                
                <div class="form-group">
                    <label for="setting_social_instagram">Instagram URL</label>
                    <input type="url" id="setting_social_instagram" name="setting_social_instagram" value="<?php echo htmlspecialchars($settings['social_instagram'] ?? ''); ?>" placeholder="https://instagram.com/yourprofile">
                </div>
                
                <div class="form-group">
                    <label for="setting_social_linkedin">LinkedIn URL</label>
                    <input type="url" id="setting_social_linkedin" name="setting_social_linkedin" value="<?php echo htmlspecialchars($settings['social_linkedin'] ?? ''); ?>" placeholder="https://linkedin.com/company/yourcompany">
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
