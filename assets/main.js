// ============================================================
// FILE: assets/main.js
// PURPOSE: Frontend interactions — hamburger, cart, etc.
// BIT3208 - Advanced Web Design and Development
// LOGBOOK: Week 3 - Fig 1: JavaScript Form Validation
// ============================================================

document.addEventListener('DOMContentLoaded', () => {

    // ── Hamburger menu ──────────────────────────────────────
    const hamburger = document.getElementById('hamburger');
    const navLinks  = document.getElementById('navLinks');
    if (hamburger && navLinks) {
        hamburger.addEventListener('click', () => {
            navLinks.classList.toggle('open');
            hamburger.classList.toggle('active');
        });
        // Close on outside click
        document.addEventListener('click', (e) => {
            if (!hamburger.contains(e.target) && !navLinks.contains(e.target)) {
                navLinks.classList.remove('open');
            }
        });
    }

    // ── Fade-up on scroll ──────────────────────────────────
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(e => {
            if (e.isIntersecting) {
                e.target.style.opacity = '1';
                e.target.style.transform = 'translateY(0)';
            }
        });
    }, { threshold: 0.15 });

    document.querySelectorAll('.product-card, .step-card, .stat-card').forEach(el => {
        el.style.opacity = '0';
        el.style.transform = 'translateY(24px)';
        el.style.transition = 'opacity .45s ease, transform .45s ease';
        observer.observe(el);
    });

    // ── Category pills filter (shop page) ──────────────────
    const pills = document.querySelectorAll('.cat-pill');
    pills.forEach(pill => {
        pill.addEventListener('click', () => {
            pills.forEach(p => p.classList.remove('active'));
            pill.classList.add('active');
            const cat = pill.dataset.cat;
            const url = new URL(window.location.href);
            if (cat === 'all') {
                url.searchParams.delete('cat');
            } else {
                url.searchParams.set('cat', cat);
            }
            window.location.href = url.toString();
        });
    });

    // ── Client-side form validation (Week 3 evidence) ──────
    const regForm = document.getElementById('registerForm');
    if (regForm) {
        regForm.addEventListener('submit', function(e) {
            const pass  = document.getElementById('password').value;
            const pass2 = document.getElementById('password2').value;
            const name  = document.getElementById('full_name').value.trim();
            const email = document.getElementById('email').value.trim();

            if (!name || !email || !pass || !pass2) {
                e.preventDefault();
                showAlert('All fields are required.', 'error');
                return;
            }
            if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                e.preventDefault();
                showAlert('Please enter a valid email address.', 'error');
                return;
            }
            if (pass.length < 6) {
                e.preventDefault();
                showAlert('Password must be at least 6 characters.', 'error');
                return;
            }
            if (pass !== pass2) {
                e.preventDefault();
                showAlert('Passwords do not match.', 'error');
                return;
            }
        });

        // Live password strength
        const pwInput = document.getElementById('password');
        const pwMeter = document.getElementById('pwStrength');
        if (pwInput && pwMeter) {
            pwInput.addEventListener('input', () => {
                const v = pwInput.value;
                let score = 0;
                if (v.length >= 6)  score++;
                if (v.length >= 10) score++;
                if (/[A-Z]/.test(v)) score++;
                if (/[0-9]/.test(v)) score++;
                if (/[^A-Za-z0-9]/.test(v)) score++;
                const labels = ['', 'Very Weak', 'Weak', 'Fair', 'Strong', 'Very Strong'];
                const colors = ['', '#EF4444','#F97316','#EAB308','#22C55E','#16A34A'];
                pwMeter.textContent = v.length ? labels[score] : '';
                pwMeter.style.color = colors[score];
            });
        }
    }

    // ── Login validation ───────────────────────────────────
    const loginForm = document.getElementById('loginForm');
    if (loginForm) {
        loginForm.addEventListener('submit', function(e) {
            const email = document.getElementById('email').value.trim();
            const pass  = document.getElementById('password').value;
            if (!email || !pass) {
                e.preventDefault();
                showAlert('Please fill in all fields.', 'error');
            }
        });
    }

    // ── Sell form validation ───────────────────────────────
    const sellForm = document.getElementById('sellForm');
    if (sellForm) {
        sellForm.addEventListener('submit', function(e) {
            const title = document.getElementById('title').value.trim();
            const price = parseFloat(document.getElementById('price').value);
            if (!title) {
                e.preventDefault();
                showAlert('Item title is required.', 'error');
                return;
            }
            if (isNaN(price) || price <= 0) {
                e.preventDefault();
                showAlert('Please enter a valid price.', 'error');
                return;
            }
        });
    }

    // ── Helper: show alert ─────────────────────────────────
    function showAlert(msg, type) {
        let existing = document.querySelector('.js-alert');
        if (existing) existing.remove();
        const div = document.createElement('div');
        div.className = `alert alert-${type} js-alert`;
        div.textContent = msg;
        const form = document.querySelector('form');
        if (form) form.prepend(div);
        setTimeout(() => div.remove(), 4000);
    }

    // ── Auto-dismiss PHP alerts ─────────────────────────────
    document.querySelectorAll('.alert').forEach(alert => {
        setTimeout(() => {
            alert.style.transition = 'opacity .5s';
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 500);
        }, 5000);
    });

});
