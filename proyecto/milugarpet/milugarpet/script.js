document.addEventListener('DOMContentLoaded', function() {
    
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, observerOptions);

    const animatedElements = document.querySelectorAll('.service-card, .stat, .product-card');
    animatedElements.forEach(el => {
        el.style.opacity = '0';
        el.style.transform = 'translateY(30px)';
        el.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
        observer.observe(el);
    });

    const serviceCards = document.querySelectorAll('.service-card');
    serviceCards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-10px) scale(1.02)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0) scale(1)';
        });
    });

    const stats = document.querySelectorAll('.stat-number');
    const animateCounter = (element, target) => {
        let current = 0;
        const increment = target / 100;
        const timer = setInterval(() => {
            current += increment;
            if (current >= target) {
                element.textContent = target + (target === 500 ? '+' : target === 5 ? '' : '');
                clearInterval(timer);
            } else {
                element.textContent = Math.floor(current) + (target === 500 ? '+' : target === 5 ? '' : '');
            }
        }, 20);
    };

    const statsObserver = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const text = entry.target.textContent;
                if (text.includes('500')) {
                    animateCounter(entry.target, 500);
                } else if (text.includes('5')) {
                    animateCounter(entry.target, 5);
                } else if (text.includes('24')) {
                    entry.target.textContent = '24/7';
                }
                statsObserver.unobserve(entry.target);
            }
        });
    }, observerOptions);

    stats.forEach(stat => {
        statsObserver.observe(stat);
    });

    const heroTitle = document.querySelector('.hero-title');
    if (heroTitle) {
        const text = heroTitle.textContent;
        heroTitle.textContent = '';
        let i = 0;
        const typeWriter = () => {
            if (i < text.length) {
                heroTitle.textContent += text.charAt(i);
                i++;
                setTimeout(typeWriter, 100);
            }
        };
        setTimeout(typeWriter, 1000);
    }

    const links = document.querySelectorAll('a[href^="#"]');
    links.forEach(link => {
        link.addEventListener('click', function(e) {
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

    window.addEventListener('scroll', function() {
        const scrolled = window.pageYOffset;
        const hero = document.querySelector('.hero');
        if (hero) {
            hero.style.transform = `translateY(${scrolled * 0.5}px)`;
        }
    });

    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            const inputs = this.querySelectorAll('input[required], select[required], textarea[required]');
            let isValid = true;
            
            inputs.forEach(input => {
                if (!input.value.trim()) {
                    isValid = false;
                    input.style.borderColor = '#dc3545';
                    input.addEventListener('input', function() {
                        this.style.borderColor = '#e0e0e0';
                    });
                }
            });
            
            if (!isValid) {
                e.preventDefault();
                alert('Por favor, completa todos los campos requeridos.');
            }
        });
    });

    const buttons = document.querySelectorAll('button[type="submit"]');
    buttons.forEach(button => {
        button.addEventListener('click', function() {
            const originalText = this.innerHTML;
            this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Procesando...';
            this.disabled = true;
            
            setTimeout(() => {
                this.innerHTML = originalText;
                this.disabled = false;
            }, 2000);
        });
    });

    const createFloatingPaw = () => {
        const paw = document.createElement('div');
        paw.innerHTML = '🐾';
        paw.style.position = 'fixed';
        paw.style.left = Math.random() * window.innerWidth + 'px';
        paw.style.top = window.innerHeight + 'px';
        paw.style.fontSize = '20px';
        paw.style.opacity = '0.3';
        paw.style.pointerEvents = 'none';
        paw.style.zIndex = '1';
        paw.style.transition = 'all 10s linear';
        
        document.body.appendChild(paw);
        
        setTimeout(() => {
            paw.style.top = '-50px';
            paw.style.transform = 'rotate(360deg)';
        }, 100);
        
        setTimeout(() => {
            paw.remove();
        }, 10000);
    };

    setInterval(createFloatingPaw, 5000);
});
