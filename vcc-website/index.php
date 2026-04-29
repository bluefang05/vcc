<?php
/**
 * VCC Website - Main Entry Point
 * Virtual Communication Connection
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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars(getSiteSetting('site_title', SITE_TITLE)); ?> | Virtual Call Center & Outsourcing</title>
    <meta name="description" content="<?php echo htmlspecialchars(getSiteSetting('meta_description', 'Professional virtual call center and communications outsourcing in the Dominican Republic.')); ?>">
    <link rel="stylesheet" href="styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <!-- Header / Navigation -->
    <header class="header">
        <nav class="nav container">
            <div class="logo">
                <img src="<?php echo htmlspecialchars(getSiteSetting('logo_url', 'assets/logo.svg')); ?>" alt="<?php echo htmlspecialchars(getSiteSetting('logo_alt', 'VCC Logo')); ?>" class="logo-img">
            </div>
            <ul class="nav-menu">
                <li><a href="#home">Home</a></li>
                <li><a href="#about">About</a></li>
                <li><a href="#services">Services</a></li>
                <li><a href="#values">Values</a></li>
                <li><a href="#contact">Contact</a></li>
            </ul>
            <a href="<?php echo htmlspecialchars(getSiteSetting('whatsapp_number', 'https://wa.me/18095866653')); ?>" class="btn-primary nav-cta">WhatsApp</a>
        </nav>
    </header>

    <!-- Hero Section -->
    <section id="home" class="hero">
        <div class="hero-overlay"></div>
        <div class="container hero-content">
            <h1 class="hero-title"><?php echo htmlspecialchars(getSiteSetting('hero_title', 'Virtual Communication Connection')); ?></h1>
            <p class="hero-subtitle"><?php echo htmlspecialchars(getSiteSetting('hero_subtitle', 'Virtual Call Center & Communications Outsourcing')); ?></p>
            <p class="hero-description"><?php echo htmlspecialchars(getSiteSetting('hero_description', 'We connect companies with their clients intelligently, humanly, and efficiently')); ?></p>
            <div class="hero-buttons">
                <a href="#services" class="btn-primary">Our Services</a>
                <a href="<?php echo htmlspecialchars(getSiteSetting('contact_whatsapp', 'https://wa.me/18095866653')); ?>" class="btn-secondary">Contact Us</a>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section id="about" class="about section">
        <div class="container">
            <h2 class="section-title"><?php echo htmlspecialchars(getSiteSetting('about_title', 'About Us')); ?></h2>
            <div class="about-content">
                <div class="about-text">
                    <p class="about-intro">
                        <?php echo getSiteSetting('about_intro', '<strong>Virtual Communication Connection (VCC)</strong> is a next-generation virtual call center in the Dominican Republic.'); ?>
                    </p>
                    <p>
                        <?php echo getSiteSetting('about_description_1', 'We enable companies to delegate all their communications <strong>(calls, chats, WhatsApp, emails)</strong> professionally, scalably, and at lower cost.'); ?>
                    </p>
                    <p>
                        <?php echo getSiteSetting('about_description_2', 'We have native operators in <strong>Spanish, English, and Creole</strong>, <strong>VoIP</strong> technology, <strong>integrated CRM</strong>, and real-time reporting.'); ?>
                    </p>
                    <p class="about-tagline"><em><?php echo htmlspecialchars(getSiteSetting('about_tagline', 'More than a call center, we are your remote extension.')); ?></em></p>
                </div>
                <div class="about-cards">
                    <div class="info-card">
                        <div class="card-icon">🎯</div>
                        <h3><?php echo htmlspecialchars(getSiteSetting('mission_title', 'Mission')); ?></h3>
                        <p><?php echo htmlspecialchars(getSiteSetting('mission_content', 'We connect companies with their clients intelligently, humanly, and efficiently. We manage customer service, sales, support, and follow-up so you can focus on growing your business.')); ?></p>
                    </div>
                    <div class="info-card">
                        <div class="card-icon">🚀</div>
                        <h3><?php echo htmlspecialchars(getSiteSetting('vision_title', 'Vision')); ?></h3>
                        <p><?php echo htmlspecialchars(getSiteSetting('vision_content', 'To be the leading virtual communications outsourcing company in the Dominican Republic and the Caribbean by 2030, combining the best technology with a human touch.')); ?></p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Services Section -->
    <section id="services" class="services section">
        <div class="container">
            <h2 class="section-title"><?php echo htmlspecialchars(getSiteSetting('services_title', 'Our Services')); ?></h2>
            <p class="section-subtitle"><?php echo htmlspecialchars(getSiteSetting('services_subtitle', 'Complete communication solutions for your business')); ?></p>
            <div class="services-intro">
                <p><?php echo getSiteSetting('services_intro_1', 'We provide comprehensive front office and back office support process management through various contact channels including phone, in-person, and virtual interactions. Our services support the entire customer relationship cycle in both outsourcing and insourcing modalities.'); ?></p>
                <p><?php echo getSiteSetting('services_intro_2', '<strong>Our bilingual team speaks 4 languages:</strong> English, French, Creole, and Spanish.'); ?></p>
            </div>
            <div class="services-grid">
                <div class="service-card">
                    <div class="service-icon"><?php echo htmlspecialchars(getSiteSetting('service_1_icon', '📞')); ?></div>
                    <h3><?php echo htmlspecialchars(getSiteSetting('service_1_title', 'Customer Service')); ?></h3>
                    <p><?php echo htmlspecialchars(getSiteSetting('service_1_desc', 'Professional management of inquiries, complaints, and general support to keep your customers satisfied. Our bilingual agents ensure clear communication in English, French, Creole, and Spanish.')); ?></p>
                </div>
                <div class="service-card">
                    <div class="service-icon"><?php echo htmlspecialchars(getSiteSetting('service_2_icon', '🔧')); ?></div>
                    <h3><?php echo htmlspecialchars(getSiteSetting('service_2_title', 'Technical Support')); ?></h3>
                    <p><?php echo htmlspecialchars(getSiteSetting('service_2_desc', 'Specialized technical assistance including backend and/or frontend programming support to resolve issues and ensure operational continuity.')); ?></p>
                </div>
                <div class="service-card">
                    <div class="service-icon"><?php echo htmlspecialchars(getSiteSetting('service_3_icon', '💼')); ?></div>
                    <h3><?php echo htmlspecialchars(getSiteSetting('service_3_title', 'Sales & Account Management')); ?></h3>
                    <p><?php echo htmlspecialchars(getSiteSetting('service_3_desc', 'Proactive telephone sales strategies, account management, and market research to expand your customer base and revenue.')); ?></p>
                </div>
                <div class="service-card">
                    <div class="service-icon"><?php echo htmlspecialchars(getSiteSetting('service_4_icon', '💬')); ?></div>
                    <h3><?php echo htmlspecialchars(getSiteSetting('service_4_title', 'Communication Channels')); ?></h3>
                    <p><?php echo htmlspecialchars(getSiteSetting('service_4_desc', 'Professional administration of phone, chat, WhatsApp, email, and social media channels with multilingual support.')); ?></p>
                </div>
                <div class="service-card">
                    <div class="service-icon"><?php echo htmlspecialchars(getSiteSetting('service_5_icon', '📊')); ?></div>
                    <h3><?php echo htmlspecialchars(getSiteSetting('service_5_title', 'Business Process Support')); ?></h3>
                    <p><?php echo htmlspecialchars(getSiteSetting('service_5_desc', 'Document administration, translations, interpretation services, customer follow-up, delivery, and installation services as required.')); ?></p>
                </div>
                <div class="service-card">
                    <div class="service-icon"><?php echo htmlspecialchars(getSiteSetting('service_6_icon', '🌙')); ?></div>
                    <h3><?php echo htmlspecialchars(getSiteSetting('service_6_title', '24/7 Call Center')); ?></h3>
                    <p><?php echo htmlspecialchars(getSiteSetting('service_6_desc', 'Continuous coverage without interruptions, because your business never sleeps. Available in all 4 supported languages.')); ?></p>
                </div>
            </div>
        </div>
    </section>

    <!-- Values Section -->
    <section id="values" class="values section">
        <div class="container">
            <h2 class="section-title"><?php echo htmlspecialchars(getSiteSetting('values_title', 'Our Values')); ?></h2>
            <div class="values-grid">
                <div class="value-item">
                    <div class="value-number"><?php echo htmlspecialchars(getSiteSetting('value_1_number', '01')); ?></div>
                    <h3><?php echo htmlspecialchars(getSiteSetting('value_1_title', 'Authentic Connection')); ?></h3>
                    <p><?php echo htmlspecialchars(getSiteSetting('value_1_desc', 'We build genuine relationships between your company and your customers.')); ?></p>
                </div>
                <div class="value-item">
                    <div class="value-number"><?php echo htmlspecialchars(getSiteSetting('value_2_number', '02')); ?></div>
                    <h3><?php echo htmlspecialchars(getSiteSetting('value_2_title', '24/7 Efficiency')); ?></h3>
                    <p><?php echo htmlspecialchars(getSiteSetting('value_2_desc', 'We operate without pause to guarantee constant attention.')); ?></p>
                </div>
                <div class="value-item">
                    <div class="value-number"><?php echo htmlspecialchars(getSiteSetting('value_3_number', '03')); ?></div>
                    <h3><?php echo htmlspecialchars(getSiteSetting('value_3_title', 'Total Transparency')); ?></h3>
                    <p><?php echo htmlspecialchars(getSiteSetting('value_3_desc', 'Clear and honest communication in every interaction.')); ?></p>
                </div>
                <div class="value-item">
                    <div class="value-number"><?php echo htmlspecialchars(getSiteSetting('value_4_number', '04')); ?></div>
                    <h3><?php echo htmlspecialchars(getSiteSetting('value_4_title', 'Constant Innovation')); ?></h3>
                    <p><?php echo htmlspecialchars(getSiteSetting('value_4_desc', 'Always updated with the latest communications technology.')); ?></p>
                </div>
                <div class="value-item">
                    <div class="value-number"><?php echo htmlspecialchars(getSiteSetting('value_5_number', '05')); ?></div>
                    <h3><?php echo htmlspecialchars(getSiteSetting('value_5_title', 'Human Excellence')); ?></h3>
                    <p><?php echo htmlspecialchars(getSiteSetting('value_5_desc', 'The human factor makes the difference in every conversation.')); ?></p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta section">
        <div class="container cta-content">
            <h2><?php echo htmlspecialchars(getSiteSetting('cta_headline', '"Your professional connection, without limits"')); ?></h2>
            <p><?php echo htmlspecialchars(getSiteSetting('cta_subheadline', 'Take your customer service to the next level with VCC')); ?></p>
            <a href="<?php echo htmlspecialchars(getSiteSetting('contact_whatsapp', 'https://wa.me/18095866653')); ?>" class="btn-primary btn-large"><?php echo htmlspecialchars(getSiteSetting('cta_button_text', 'Write Now 🚀')); ?></a>
        </div>
    </section>

    <!-- Contact Section -->
    <section id="contact" class="contact section">
        <div class="container">
            <h2 class="section-title"><?php echo htmlspecialchars(getSiteSetting('contact_title', 'Contact')); ?></h2>
            <div class="contact-grid">
                <div class="contact-info">
                    <div class="contact-item">
                        <div class="contact-icon">📍</div>
                        <div>
                            <h4>Address</h4>
                            <p><?php echo nl2br(htmlspecialchars(getSiteSetting('company_address', 'Margaria Mears 18<br>Puerto Plata, Dominican Republic 57000'))); ?></p>
                        </div>
                    </div>
                    <div class="contact-item">
                        <div class="contact-icon">📞</div>
                        <div>
                            <h4>Phone</h4>
                            <p><?php echo htmlspecialchars(getSiteSetting('company_phone', '+1 809-586-6653')); ?></p>
                        </div>
                    </div>
                    <div class="contact-item">
                        <div class="contact-icon">💬</div>
                        <div>
                            <h4>WhatsApp</h4>
                            <p><?php echo htmlspecialchars(getSiteSetting('whatsapp_availability', 'Available 24/7')); ?></p>
                        </div>
                    </div>
                </div>
                <div class="contact-form-wrapper">
                    <form class="contact-form" id="contactForm" action="contact-handler.php" method="POST">
                        <h3><?php echo htmlspecialchars(getSiteSetting('contact_form_title', 'Send us a message')); ?></h3>
                        <input type="text" name="name" placeholder="Full name" required>
                        <input type="email" name="email" placeholder="Email address" required>
                        <input type="tel" name="phone" placeholder="Phone">
                        <input type="text" name="subject" placeholder="Subject">
                        <textarea name="message" placeholder="How can we help you?" rows="4" required></textarea>
                        <button type="submit" class="btn-primary btn-full"><?php echo htmlspecialchars(getSiteSetting('contact_button_text', 'Send Message')); ?></button>
                        <div class="form-message"></div>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container footer-content">
            <div class="footer-brand">
                <img src="<?php echo htmlspecialchars(getSiteSetting('logo_url', 'assets/logo.svg')); ?>" alt="<?php echo htmlspecialchars(getSiteSetting('logo_alt', 'VCC Logo')); ?>" class="footer-logo">
                <p><?php echo htmlspecialchars(getSiteSetting('site_title', 'Virtual Communication Connection')); ?></p>
                <p><?php echo htmlspecialchars(getSiteSetting('site_tagline', 'Virtual Call Center & Communications Outsourcing')); ?></p>
            </div>
            <div class="footer-links">
                <h4>Quick Links</h4>
                <ul>
                    <li><a href="#home">Home</a></li>
                    <li><a href="#about">About</a></li>
                    <li><a href="#services">Services</a></li>
                    <li><a href="#contact">Contact</a></li>
                    <?php if (getSiteSetting('show_blog_link', 'false') === 'true'): ?>
                    <li><a href="blog.php">Blog</a></li>
                    <?php endif; ?>
                </ul>
            </div>
            <div class="footer-contact">
                <h4>Contact</h4>
                <p>📍 <?php echo htmlspecialchars(getSiteSetting('footer_location', 'Puerto Plata, RD')); ?></p>
                <p>📞 <?php echo htmlspecialchars(getSiteSetting('company_phone', '+1 809-586-6653')); ?></p>
                <?php if (getSiteSetting('social_facebook')): ?>
                <p><a href="<?php echo htmlspecialchars(getSiteSetting('social_facebook')); ?>" target="_blank" rel="noopener">Facebook</a></p>
                <?php endif; ?>
                <?php if (getSiteSetting('social_instagram')): ?>
                <p><a href="<?php echo htmlspecialchars(getSiteSetting('social_instagram')); ?>" target="_blank" rel="noopener">Instagram</a></p>
                <?php endif; ?>
                <?php if (getSiteSetting('social_linkedin')): ?>
                <p><a href="<?php echo htmlspecialchars(getSiteSetting('social_linkedin')); ?>" target="_blank" rel="noopener">LinkedIn</a></p>
                <?php endif; ?>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; <?php echo date('Y'); ?> <?php echo htmlspecialchars(getSiteSetting('site_title', 'Virtual Communication Connection')); ?>. All rights reserved.</p>
        </div>
    </footer>

    <script src="script.js"></script>
</body>
</html>
