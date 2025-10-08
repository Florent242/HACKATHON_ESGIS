// Footer JavaScript - Hack & Stack
document.addEventListener('DOMContentLoaded', function() {
    
    // Initialiser les icônes Lucide
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }

    // Animation d'apparition progressive pour les sections
    function animateFooterSections() {
        const sections = document.querySelectorAll('.footer-section');
        
        const observer = new IntersectionObserver((entries) => {
            entries.forEach((entry, index) => {
                if (entry.isIntersecting) {
                    setTimeout(() => {
                        entry.target.style.opacity = '1';
                        entry.target.style.transform = 'translateY(0)';
                    }, index * 100);
                }
            });
        }, { threshold: 0.1 });

        sections.forEach(section => {
            section.style.opacity = '0';
            section.style.transform = 'translateY(20px)';
            section.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
            observer.observe(section);
        });
    }

    // Lancer l'animation des sections
    animateFooterSections();

    // Gestion des clics sur les liens sociaux
    const socialLinks = document.querySelectorAll('.social-link');
    socialLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            // Ajouter une animation de clic
            this.style.transform = 'scale(0.95)';
            setTimeout(() => {
                this.style.transform = 'scale(1.05)';
                setTimeout(() => {
                    this.style.transform = 'scale(1)';
                }, 150);
            }, 100);
        });
    });

    // Smooth scroll pour les liens d'ancre
    const footerLinks = document.querySelectorAll('.footer-link[href^="#"]');
    footerLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const targetId = this.getAttribute('href').substring(1);
            const targetElement = document.getElementById(targetId);
            
            if (targetElement) {
                targetElement.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });

    // Effet de parallaxe léger pour le footer (optionnel)
    function handleParallax() {
        const footer = document.querySelector('.footer-gradient');
        const scrolled = window.pageYOffset;
        const rate = scrolled * -0.1;
        
        if (footer) {
            footer.style.transform = `translateY(${rate}px)`;
        }
    }

    // Activer le parallaxe seulement sur desktop
    if (window.innerWidth > 768) {
        window.addEventListener('scroll', handleParallax);
    }
});