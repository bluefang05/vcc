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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo defined('SITE_TITLE') ? SITE_TITLE : 'VCC - Virtual Communication Connection'; ?> | Virtual Call Center & Outsourcing</title>
    <link rel="stylesheet" href="styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <!-- Header / Navigation -->
    <header class="header">
        <nav class="nav container">
            <div class="logo">
                <img src="assets/logo.svg" alt="VCC Logo" class="logo-img">
            </div>
            <ul class="nav-menu">
                <li><a href="#home">Home</a></li>
                <li><a href="#about">About</a></li>
                <li><a href="#services">Services</a></li>
                <li><a href="#values">Values</a></li>
                <li><a href="#contact">Contact</a></li>
            </ul>
            <a href="https://wa.me/18095866653" class="btn-primary nav-cta">WhatsApp</a>
        </nav>
    </header>

    <!-- Hero Section -->
    <section id="home" class="hero">
        <div class="hero-overlay"></div>
        <div class="container hero-content">
            <h1 class="hero-title">Virtual Communication Connection</h1>
            <p class="hero-subtitle">Virtual Call Center & Communications Outsourcing</p>
            <p class="hero-description">We connect companies with their clients intelligently, humanly, and efficiently</p>
            <div class="hero-buttons">
                <a href="#services" class="btn-primary">Our Services</a>
                <a href="https://wa.me/18095866653" class="btn-secondary">Contact Us</a>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section id="about" class="about section">
        <div class="container">
            <h2 class="section-title">About Us</h2>
            <div class="about-content">
                <div class="about-text">
                    <p class="about-intro">
                        <strong>Virtual Communication Connection (VCC)</strong> is a next-generation virtual call center in the Dominican Republic.
                    </p>
                    <p>
                        We enable companies to delegate all their communications <strong>(calls, chats, WhatsApp, emails)</strong> professionally, scalably, and at lower cost.
                    </p>
                    <p>
                        We have native operators in <strong>Spanish, English, and Creole</strong>, <strong>VoIP</strong> technology, <strong>integrated CRM</strong>, and real-time reporting.
                    </p>
                    <p class="about-tagline"><em>More than a call center, we are your remote extension.</em></p>
                </div>
                <div class="about-cards">
                    <div class="info-card">
                        <div class="card-icon">🎯</div>
                        <h3>Mission</h3>
                        <p>We connect companies with their clients intelligently, humanly, and efficiently. We manage customer service, sales, support, and follow-up so you can focus on growing your business.</p>
                    </div>
                    <div class="info-card">
                        <div class="card-icon">🚀</div>
                        <h3>Vision</h3>
                        <p>To be the leading virtual communications outsourcing company in the Dominican Republic and the Caribbean by 2030, combining the best technology with a human touch.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Services Section -->
    <section id="services" class="services section">
        <div class="container">
            <h2 class="section-title">Our Services</h2>
            <p class="section-subtitle">Complete communication solutions for your business</p>
            <div class="services-grid">
                <div class="service-card">
                    <div class="service-icon">📞</div>
                    <h3>Customer Service</h3>
                    <p>Professional management of inquiries, complaints, and general support to keep your customers satisfied.</p>
                </div>
                <div class="service-card">
                    <div class="service-icon">🔧</div>
                    <h3>Technical Support</h3>
                    <p>Specialized technical assistance to resolve issues and ensure operational continuity.</p>
                </div>
                <div class="service-card">
                    <div class="service-icon">💼</div>
                    <h3>Sales & Telemarketing</h3>
                    <p>Proactive telephone sales strategies to expand your customer base and revenue.</p>
                </div>
                <div class="service-card">
                    <div class="service-icon">💬</div>
                    <h3>WhatsApp & Social Media Management</h3>
                    <p>Professional administration of your most important digital channels.</p>
                </div>
                <div class="service-card">
                    <div class="service-icon">📊</div>
                    <h3>Customer Follow-up</h3>
                    <p>Strategic follow-up to retain customers and maximize business opportunities.</p>
                </div>
                <div class="service-card">
                    <div class="service-icon">🌙</div>
                    <h3>24/7 Call Center</h3>
                    <p>Continuous coverage without interruptions, because your business never sleeps.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Values Section -->
    <section id="values" class="values section">
        <div class="container">
            <h2 class="section-title">Our Values</h2>
            <div class="values-grid">
                <div class="value-item">
                    <div class="value-number">01</div>
                    <h3>Authentic Connection</h3>
                    <p>We build genuine relationships between your company and your customers.</p>
                </div>
                <div class="value-item">
                    <div class="value-number">02</div>
                    <h3>24/7 Efficiency</h3>
                    <p>We operate without pause to guarantee constant attention.</p>
                </div>
                <div class="value-item">
                    <div class="value-number">03</div>
                    <h3>Total Transparency</h3>
                    <p>Clear and honest communication in every interaction.</p>
                </div>
                <div class="value-item">
                    <div class="value-number">04</div>
                    <h3>Constant Innovation</h3>
                    <p>Always updated with the latest communications technology.</p>
                </div>
                <div class="value-item">
                    <div class="value-number">05</div>
                    <h3>Human Excellence</h3>
                    <p>The human factor makes the difference in every conversation.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta section">
        <div class="container cta-content">
            <h2>"Your professional connection, without limits"</h2>
            <p>Take your customer service to the next level with VCC</p>
            <a href="https://wa.me/18095866653" class="btn-primary btn-large">Write Now 🚀</a>
        </div>
    </section>

    <!-- Contact Section -->
    <section id="contact" class="contact section">
        <div class="container">
            <h2 class="section-title">Contact</h2>
            <div class="contact-grid">
                <div class="contact-info">
                    <div class="contact-item">
                        <div class="contact-icon">📍</div>
                        <div>
                            <h4>Address</h4>
                            <p>Margaria Mears 18<br>Puerto Plata, Dominican Republic 57000</p>
                        </div>
                    </div>
                    <div class="contact-item">
                        <div class="contact-icon">📞</div>
                        <div>
                            <h4>Phone</h4>
                            <p>+1 809-586-6653</p>
                        </div>
                    </div>
                    <div class="contact-item">
                        <div class="contact-icon">💬</div>
                        <div>
                            <h4>WhatsApp</h4>
                            <p>Available 24/7</p>
                        </div>
                    </div>
                </div>
                <div class="contact-form-wrapper">
                    <form class="contact-form" id="contactForm">
                        <h3>Send us a message</h3>
                        <input type="text" name="name" placeholder="Full name" required>
                        <input type="email" name="email" placeholder="Email address" required>
                        <input type="tel" name="phone" placeholder="Phone">
                        <input type="text" name="subject" placeholder="Subject">
                        <textarea name="message" placeholder="How can we help you?" rows="4" required></textarea>
                        <button type="submit" class="btn-primary btn-full">Send Message</button>
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
                <img src="assets/logo.svg" alt="VCC Logo" class="footer-logo">
                <p>Virtual Communication Connection</p>
                <p>Virtual Call Center & Communications Outsourcing</p>
            </div>
            <div class="footer-links">
                <h4>Quick Links</h4>
                <ul>
                    <li><a href="#home">Home</a></li>
                    <li><a href="#about">About</a></li>
                    <li><a href="#services">Services</a></li>
                    <li><a href="#contact">Contact</a></li>
                </ul>
            </div>
            <div class="footer-contact">
                <h4>Contact</h4>
                <p>📍 Puerto Plata, RD</p>
                <p>📞 +1 809-586-6653</p>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2026 Virtual Communication Connection. All rights reserved.</p>
        </div>
    </footer>

    <script src="script.js"></script>
</body>
</html>
