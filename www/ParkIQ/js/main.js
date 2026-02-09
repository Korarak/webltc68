document.addEventListener('DOMContentLoaded', () => {
    const mobileMenuBtn = document.getElementById('mobile-menu-btn');
    const mobileMenu = document.getElementById('mobile-menu');
    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
    const idle = window.requestIdleCallback || function (cb) {
        return setTimeout(cb, 1);
    };

    if (mobileMenuBtn && mobileMenu) {
        mobileMenuBtn.addEventListener('click', () => {
            const isHidden = mobileMenu.classList.toggle('hidden');
            mobileMenuBtn.setAttribute('aria-expanded', String(!isHidden));
        });

        mobileMenu.querySelectorAll('a').forEach(link => {
            link.addEventListener('click', () => {
                mobileMenu.classList.add('hidden');
                mobileMenuBtn.setAttribute('aria-expanded', 'false');
            });
        });
    }

    // Scroll progress indicator
    const scrollProgress = document.getElementById('scroll-progress');
    if (scrollProgress) {
        const updateProgress = () => {
            const scrolled = window.scrollY;
            const limit = document.body.scrollHeight - window.innerHeight;
            const percentage = limit > 0 ? (scrolled / limit) * 100 : 0;
            scrollProgress.style.width = `${Math.min(Math.max(percentage, 0), 100)}%`;
        };

        updateProgress();
        window.addEventListener('scroll', updateProgress, { passive: true });
        window.addEventListener('resize', updateProgress);
    }

    idle(() => {
        document.querySelectorAll('img:not([data-priority])').forEach(img => {
            if (!img.hasAttribute('loading')) {
                img.setAttribute('loading', 'lazy');
            }
            if (!img.hasAttribute('decoding')) {
                img.setAttribute('decoding', 'async');
            }
        });

        document.querySelectorAll('iframe:not([loading])').forEach(frame => {
            frame.setAttribute('loading', 'lazy');
        });
    });

    // Animate on scroll
    const animateElements = document.querySelectorAll('[data-animate]');
    if (!prefersReducedMotion.matches && 'IntersectionObserver' in window) {
        const observer = new IntersectionObserver(
            entries => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('animate-in');
                        observer.unobserve(entry.target);
                    }
                });
            },
            {
                threshold: 0.2,
                rootMargin: '0px 0px -10% 0px'
            }
        );

        animateElements.forEach(element => observer.observe(element));
    } else {
        animateElements.forEach(element => element.classList.add('animate-in'));
    }

    // Highlight active navigation link
    const currentPage = document.body.dataset.page;
    if (currentPage) {
        document.querySelectorAll('[data-nav]').forEach(link => {
            const isActive = link.dataset.nav === currentPage;
            link.classList.toggle('text-pink-primary', isActive);
            link.classList.toggle('font-semibold', isActive);
            if (isActive) {
                link.setAttribute('aria-current', 'page');
            } else {
                link.removeAttribute('aria-current');
            }
        });
    }

    // Contact form handling
    const contactForm = document.getElementById('contact-form');
    if (contactForm) {
        const statusMessage = document.getElementById('form-status');

        contactForm.addEventListener('submit', event => {
            event.preventDefault();

            const formData = {
                name: document.getElementById('name')?.value.trim(),
                email: document.getElementById('email')?.value.trim(),
                subject: document.getElementById('subject')?.value.trim(),
                message: document.getElementById('message')?.value.trim()
            };

            console.table(formData);

            contactForm.reset();

            if (statusMessage) {
                statusMessage.classList.add('is-visible');
                statusMessage.textContent = 'ขอบคุณสำหรับข้อความของคุณ! ทีมงานจะติดต่อกลับโดยเร็วที่สุด';
                setTimeout(() => statusMessage.classList.remove('is-visible'), 5000);
            } else {
                alert('ขอบคุณสำหรับข้อความของคุณ! เราจะติดต่อกลับโดยเร็วที่สุด');
            }
        });
    }

    // Accordion toggles
    document.querySelectorAll('[data-accordion-button]').forEach(button => {
        button.addEventListener('click', () => {
            const expanded = button.getAttribute('aria-expanded') === 'true';
            const panel = button.nextElementSibling;

            button.setAttribute('aria-expanded', String(!expanded));

            if (panel) {
                panel.classList.toggle('hidden', expanded);
                panel.classList.toggle('opacity-0', expanded);
                panel.classList.toggle('opacity-100', !expanded);
            }
        });
    });
});