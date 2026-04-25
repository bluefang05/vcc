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
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo defined('SITE_TITLE') ? SITE_TITLE : 'VCC - Virtual Communication Connection'; ?> | Call Center Virtual & Outsourcing</title>
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
                <li><a href="#inicio">Inicio</a></li>
                <li><a href="#nosotros">Nosotros</a></li>
                <li><a href="#servicios">Servicios</a></li>
                <li><a href="#valores">Valores</a></li>
                <li><a href="#contacto">Contacto</a></li>
            </ul>
            <a href="https://wa.me/18095866653" class="btn-primary nav-cta">WhatsApp</a>
        </nav>
    </header>

    <!-- Hero Section -->
    <section id="inicio" class="hero">
        <div class="hero-overlay"></div>
        <div class="container hero-content">
            <h1 class="hero-title">Virtual Communication Connection</h1>
            <p class="hero-subtitle">Call Center Virtual & Outsourcing de Comunicaciones</p>
            <p class="hero-description">Conectamos empresas con sus clientes de forma inteligente, humana y eficiente</p>
            <div class="hero-buttons">
                <a href="#servicios" class="btn-primary">Nuestros Servicios</a>
                <a href="https://wa.me/18095866653" class="btn-secondary">Contáctanos</a>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section id="nosotros" class="about section">
        <div class="container">
            <h2 class="section-title">Acerca de Nosotros</h2>
            <div class="about-content">
                <div class="about-text">
                    <p class="about-intro">
                        <strong>Virtual Communication Connection (VCC)</strong> es un call center virtual de nueva generación en República Dominicana.
                    </p>
                    <p>
                        Permitimos a las empresas delegar toda su comunicación <strong>(llamadas, chats, WhatsApp, emails)</strong> de forma profesional, escalable y a menor costo.
                    </p>
                    <p>
                        Contamos con operadores nativos en <strong>español, inglés y creole</strong>, tecnología <strong>VoIP</strong>, <strong>CRM integrado</strong> y reportes en tiempo real.
                    </p>
                    <p class="about-tagline"><em>Más que un call center, somos tu extensión remota.</em></p>
                </div>
                <div class="about-cards">
                    <div class="info-card">
                        <div class="card-icon">🎯</div>
                        <h3>Misión</h3>
                        <p>Conectamos empresas con sus clientes de forma inteligente, humana y eficiente. Gestionamos su atención al cliente, ventas, soporte y seguimiento para que se enfoquen en hacer crecer su negocio.</p>
                    </div>
                    <div class="info-card">
                        <div class="card-icon">🚀</div>
                        <h3>Visión</h3>
                        <p>Ser la empresa líder de outsourcing de comunicaciones virtuales en República Dominicana y el Caribe para el año 2030, combinando la mejor tecnología con el toque humano.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Services Section -->
    <section id="servicios" class="services section">
        <div class="container">
            <h2 class="section-title">Nuestros Servicios</h2>
            <p class="section-subtitle">Soluciones completas de comunicación para tu empresa</p>
            <div class="services-grid">
                <div class="service-card">
                    <div class="service-icon">📞</div>
                    <h3>Atención al Cliente</h3>
                    <p>Gestión profesional de consultas, reclamos y soporte general para mantener a tus clientes satisfechos.</p>
                </div>
                <div class="service-card">
                    <div class="service-icon">🔧</div>
                    <h3>Soporte Técnico</h3>
                    <p>Asistencia técnica especializada para resolver problemas y garantizar la continuidad operativa.</p>
                </div>
                <div class="service-card">
                    <div class="service-icon">💼</div>
                    <h3>Ventas y Telemarketing</h3>
                    <p>Estrategias proactivas de venta telefónica para aumentar tu cartera de clientes y revenue.</p>
                </div>
                <div class="service-card">
                    <div class="service-icon">💬</div>
                    <h3>Gestión de WhatsApp y Redes</h3>
                    <p>Administración profesional de tus canales digitales más importantes.</p>
                </div>
                <div class="service-card">
                    <div class="service-icon">📊</div>
                    <h3>Seguimiento de Clientes</h3>
                    <p>Follow-up estratégico para fidelizar clientes y maximizar oportunidades de negocio.</p>
                </div>
                <div class="service-card">
                    <div class="service-icon">🌙</div>
                    <h3>Call Center 24/7</h3>
                    <p>Cobertura continua sin interrupciones, porque tu negocio nunca duerme.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Values Section -->
    <section id="valores" class="values section">
        <div class="container">
            <h2 class="section-title">Nuestros Valores</h2>
            <div class="values-grid">
                <div class="value-item">
                    <div class="value-number">01</div>
                    <h3>Conexión Auténtica</h3>
                    <p>Construimos relaciones genuinas entre tu empresa y tus clientes.</p>
                </div>
                <div class="value-item">
                    <div class="value-number">02</div>
                    <h3>Eficiencia 24/7</h3>
                    <p>Operamos sin pausa para garantizar atención constante.</p>
                </div>
                <div class="value-item">
                    <div class="value-number">03</div>
                    <h3>Transparencia Total</h3>
                    <p>Comunicación clara y honesta en cada interacción.</p>
                </div>
                <div class="value-item">
                    <div class="value-number">04</div>
                    <h3>Innovación Constante</h3>
                    <p>Siempre actualizados con la última tecnología en comunicaciones.</p>
                </div>
                <div class="value-item">
                    <div class="value-number">05</div>
                    <h3>Excelencia Humana</h3>
                    <p>El factor humano marca la diferencia en cada conversación.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta section">
        <div class="container cta-content">
            <h2>"Tu conexión profesional, sin límites"</h2>
            <p>Lleva tu atención al cliente al siguiente nivel con VCC</p>
            <a href="https://wa.me/18095866653" class="btn-primary btn-large">Escribe Ahora 🚀</a>
        </div>
    </section>

    <!-- Contact Section -->
    <section id="contacto" class="contact section">
        <div class="container">
            <h2 class="section-title">Contacto</h2>
            <div class="contact-grid">
                <div class="contact-info">
                    <div class="contact-item">
                        <div class="contact-icon">📍</div>
                        <div>
                            <h4>Dirección</h4>
                            <p>Margaria Mears 18<br>Puerto Plata, Dominican Republic 57000</p>
                        </div>
                    </div>
                    <div class="contact-item">
                        <div class="contact-icon">📞</div>
                        <div>
                            <h4>Teléfono</h4>
                            <p>+1 809-586-6653</p>
                        </div>
                    </div>
                    <div class="contact-item">
                        <div class="contact-icon">💬</div>
                        <div>
                            <h4>WhatsApp</h4>
                            <p>Disponible 24/7</p>
                        </div>
                    </div>
                </div>
                <div class="contact-form-wrapper">
                    <form class="contact-form" id="contactForm">
                        <h3>Envíanos un mensaje</h3>
                        <input type="text" name="name" placeholder="Nombre completo" required>
                        <input type="email" name="email" placeholder="Correo electrónico" required>
                        <input type="tel" name="phone" placeholder="Teléfono">
                        <input type="text" name="subject" placeholder="Asunto">
                        <textarea name="message" placeholder="¿Cómo podemos ayudarte?" rows="4" required></textarea>
                        <button type="submit" class="btn-primary btn-full">Enviar Mensaje</button>
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
                <p>Call Center Virtual & Outsourcing de Comunicaciones</p>
            </div>
            <div class="footer-links">
                <h4>Enlaces Rápidos</h4>
                <ul>
                    <li><a href="#inicio">Inicio</a></li>
                    <li><a href="#nosotros">Nosotros</a></li>
                    <li><a href="#servicios">Servicios</a></li>
                    <li><a href="#contacto">Contacto</a></li>
                </ul>
            </div>
            <div class="footer-contact">
                <h4>Contacto</h4>
                <p>📍 Puerto Plata, RD</p>
                <p>📞 +1 809-586-6653</p>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2026 Virtual Communication Connection. Todos los derechos reservados.</p>
        </div>
    </footer>

    <script src="script.js"></script>
</body>
</html>
