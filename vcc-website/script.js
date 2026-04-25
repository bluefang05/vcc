// ===== Smooth Scroll for Navigation Links =====
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            target.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }
    });
});

// ===== Header Scroll Effect =====
const header = document.querySelector('.header');
let lastScroll = 0;

window.addEventListener('scroll', () => {
    const currentScroll = window.pageYOffset;
    
    if (currentScroll > 100) {
        header.style.boxShadow = '0 4px 20px rgba(0,0,0,0.15)';
    } else {
        header.style.boxShadow = '0 2px 4px rgba(0,0,0,0.1)';
    }
    
    lastScroll = currentScroll;
});

// ===== Form Submission Handler =====
const contactForm = document.getElementById('contactForm');
if (contactForm) {
    contactForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const submitBtn = this.querySelector('button[type="submit"]');
        const messageDiv = this.querySelector('.form-message');
        const originalText = submitBtn.textContent;
        
        // Disable button and show loading state
        submitBtn.disabled = true;
        submitBtn.textContent = 'Enviando...';
        messageDiv.textContent = '';
        messageDiv.className = 'form-message';
        
        // Get form values
        const formData = new FormData(this);
        
        // Send to server
        fetch('contact-handler.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                messageDiv.textContent = data.message;
                messageDiv.classList.add('success');
                this.reset();
                
                // Optional: Open WhatsApp after successful submission
                setTimeout(() => {
                    if (data.whatsapp_url) {
                        window.open(data.whatsapp_url, '_blank');
                    }
                }, 1500);
            } else {
                messageDiv.textContent = data.message || 'Error al enviar el mensaje';
                messageDiv.classList.add('error');
            }
        })
        .catch(error => {
            messageDiv.textContent = 'Error de conexión. Por favor intenta nuevamente.';
            messageDiv.classList.add('error');
            console.error('Error:', error);
        })
        .finally(() => {
            submitBtn.disabled = false;
            submitBtn.textContent = originalText;
        });
    });
}

// ===== Intersection Observer for Animations =====
const observerOptions = {
    threshold: 0.1,
    rootMargin: '0px 0px -50px 0px'
};

const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.style.opacity = '1';
            entry.target.style.transform = 'translateY(0)';
        }
    });
}, observerOptions);

// Observe all cards and sections
document.querySelectorAll('.service-card, .value-item, .info-card').forEach(el => {
    el.style.opacity = '0';
    el.style.transform = 'translateY(30px)';
    el.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
    observer.observe(el);
});

// ===== Mobile Menu Toggle (Future Enhancement) =====
// Can be expanded to add hamburger menu for mobile devices

console.log('VCC Website Loaded Successfully! 🚀');
