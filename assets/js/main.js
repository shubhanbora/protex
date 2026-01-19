// Simple mobile navigation with side drawer
document.addEventListener('DOMContentLoaded', function() {
    // Mobile navigation toggle
    window.toggleMobileMenu = function() {
        const navMenu = document.getElementById('navMenu');
        const navOverlay = document.getElementById('navOverlay');
        const toggleBtn = document.querySelector('.mobile-menu-toggle span');
        const mobileHeader = document.querySelector('.mobile-menu-header');
        const mobileProfile = document.querySelector('.mobile-profile-section');
        
        if (navMenu) {
            const isActive = navMenu.classList.contains('active');
            
            if (!isActive) {
                // Open menu
                navMenu.classList.add('active');
                navOverlay.classList.add('active');
                if (toggleBtn) toggleBtn.textContent = '✕';
                
                // Show mobile-only elements
                if (mobileHeader) mobileHeader.style.display = 'flex';
                if (mobileProfile) mobileProfile.style.display = 'block';
                
                // Prevent body scroll
                document.body.style.overflow = 'hidden';
            } else {
                // Close menu
                closeMobileMenu();
            }
        }
    };
    
    // Close mobile menu
    window.closeMobileMenu = function() {
        const navMenu = document.getElementById('navMenu');
        const navOverlay = document.getElementById('navOverlay');
        const toggleBtn = document.querySelector('.mobile-menu-toggle span');
        const mobileHeader = document.querySelector('.mobile-menu-header');
        const mobileProfile = document.querySelector('.mobile-profile-section');
        
        if (navMenu) {
            navMenu.classList.remove('active');
            navOverlay.classList.remove('active');
            if (toggleBtn) toggleBtn.textContent = '≡';
            
            // Hide mobile-only elements
            if (mobileHeader) mobileHeader.style.display = 'none';
            if (mobileProfile) mobileProfile.style.display = 'none';
            
            // Restore body scroll
            document.body.style.overflow = '';
        }
    };
    
    // Close mobile menu when clicking on a link
    const navLinks = document.querySelectorAll('.nav-menu a');
    navLinks.forEach(link => {
        link.addEventListener('click', function() {
            // Don't close for profile dropdown toggle
            if (!this.classList.contains('profile-icon')) {
                closeMobileMenu();
            }
        });
    });
    
    // Focus search function for mobile
    window.focusSearchOnProducts = function() {
        // Close mobile menu first
        closeMobileMenu();
        // Small delay to ensure page loads, then focus search
        setTimeout(() => {
            const searchInput = document.querySelector('.search-input-main');
            if (searchInput) {
                searchInput.focus();
                searchInput.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }, 100);
    };
    
    // Profile dropdown toggle
    window.toggleProfileDropdown = function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        const profileDropdown = document.querySelector('.profile-dropdown');
        if (profileDropdown) {
            profileDropdown.classList.toggle('active');
        }
    };
    
    // Close dropdown when clicking outside
    document.addEventListener('click', function(e) {
        const profileDropdown = document.querySelector('.profile-dropdown');
        if (profileDropdown && !profileDropdown.contains(e.target)) {
            profileDropdown.classList.remove('active');
        }
    });
    
    // Profile dropdown toggle - Updated for mobile
    window.toggleProfileDropdown = function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        const profileDropdown = document.querySelector('.profile-dropdown');
        if (profileDropdown) {
            profileDropdown.classList.toggle('active');
        }
    };
    
    // Profile dropdown toggle
    const profileDropdown = document.querySelector('.profile-dropdown');
    const profileIcon = document.querySelector('.profile-icon');
    
    if (profileIcon && profileDropdown) {
        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (!profileDropdown.contains(e.target)) {
                profileDropdown.classList.remove('active');
            }
        });
        
        // Close dropdown when clicking a menu item
        const dropdownLinks = profileDropdown.querySelectorAll('.dropdown-menu a');
        dropdownLinks.forEach(link => {
            link.addEventListener('click', function() {
                profileDropdown.classList.remove('active');
            });
        });
    }
    
    // Animate page load
    anime({
        targets: '.main-content',
        opacity: [0, 1],
        translateY: [20, 0],
        duration: 800,
        easing: 'easeOutQuad'
    });

    // Animate product cards
    anime({
        targets: '.product-card',
        opacity: [0, 1],
        translateY: [30, 0],
        delay: anime.stagger(100),
        duration: 600,
        easing: 'easeOutQuad'
    });

    // Button hover animation
    const buttons = document.querySelectorAll('.btn');
    buttons.forEach(btn => {
        btn.addEventListener('mouseenter', function() {
            anime({
                targets: this,
                scale: 1.05,
                duration: 300,
                easing: 'easeOutQuad'
            });
        });
        
        btn.addEventListener('mouseleave', function() {
            anime({
                targets: this,
                scale: 1,
                duration: 300,
                easing: 'easeOutQuad'
            });
        });
    });

    // Cart update animation
    window.animateCartUpdate = function() {
        anime({
            targets: '.fa-shopping-cart',
            scale: [1, 1.3, 1],
            duration: 500,
            easing: 'easeInOutQuad'
        });
    };

    // Modal animations
    window.showModal = function(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.style.display = 'block';
            anime({
                targets: modal.querySelector('.modal-content'),
                opacity: [0, 1],
                scale: [0.8, 1],
                duration: 400,
                easing: 'easeOutQuad'
            });
        }
    };

    window.hideModal = function(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            anime({
                targets: modal.querySelector('.modal-content'),
                opacity: [1, 0],
                scale: [1, 0.8],
                duration: 300,
                easing: 'easeInQuad',
                complete: function() {
                    modal.style.display = 'none';
                }
            });
        }
    };

    // Form validation animation
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            const inputs = this.querySelectorAll('.form-control');
            let hasError = false;
            
            inputs.forEach(input => {
                if (input.hasAttribute('required') && !input.value.trim()) {
                    hasError = true;
                    input.style.borderColor = 'var(--danger-color)';
                    anime({
                        targets: input,
                        translateX: [-10, 10, -10, 10, 0],
                        duration: 400,
                        easing: 'easeInOutQuad'
                    });
                } else {
                    input.style.borderColor = 'var(--border-color)';
                }
            });
            
            if (hasError) {
                e.preventDefault();
            }
        });
    });

    // Smooth scroll
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
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
});

// Add to cart with animation
function addToCart(productId) {
    console.log('Adding to cart:', productId);
    
    // Use the BASE_URL set in header.php
    const baseUrl = window.BASE_URL || window.location.origin;
    const apiPath = baseUrl + '/api/cart.php';
    
    console.log('Base URL:', baseUrl);
    console.log('API Path:', apiPath);
    
    fetch(apiPath, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            action: 'add',
            product_id: productId
        })
    })
    .then(response => {
        console.log('Response status:', response.status);
        return response.json();
    })
    .then(data => {
        console.log('Response data:', data);
        if (data.success) {
            animateCartUpdate();
            showNotification('Product added to cart!', 'success');
        } else {
            showNotification(data.message || 'Failed to add to cart', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('An error occurred: ' + error.message, 'error');
    });
}

// Show notification
function showNotification(message, type = 'success') {
    const notification = document.createElement('div');
    notification.className = `alert alert-${type}`;
    notification.textContent = message;
    notification.style.position = 'fixed';
    notification.style.top = '20px';
    notification.style.right = '20px';
    notification.style.zIndex = '9999';
    notification.style.minWidth = '250px';
    
    document.body.appendChild(notification);
    
    anime({
        targets: notification,
        opacity: [0, 1],
        translateX: [50, 0],
        duration: 400,
        easing: 'easeOutQuad'
    });
    
    setTimeout(() => {
        anime({
            targets: notification,
            opacity: [1, 0],
            translateX: [0, 50],
            duration: 400,
            easing: 'easeInQuad',
            complete: function() {
                notification.remove();
            }
        });
    }, 3000);
}
// Password visibility toggle function - Global scope
window.togglePasswordVisibility = function(passwordFieldId, toggleIconId) {
    console.log('Toggle function called:', passwordFieldId, toggleIconId);
    
    const passwordField = document.getElementById(passwordFieldId);
    const toggleIcon = document.getElementById(toggleIconId);
    
    console.log('Elements found:', passwordField, toggleIcon);
    
    if (passwordField && toggleIcon) {
        if (passwordField.type === 'password') {
            passwordField.type = 'text';
            toggleIcon.textContent = '🙈';
            toggleIcon.style.color = '#007bff';
            console.log('Password shown');
        } else {
            passwordField.type = 'password';
            toggleIcon.textContent = '👁️';
            toggleIcon.style.color = '#666';
            console.log('Password hidden');
        }
        
        // Add a small animation
        if (typeof anime !== 'undefined') {
            anime({
                targets: toggleIcon,
                scale: [1, 1.2, 1],
                duration: 200,
                easing: 'easeInOutQuad'
            });
        }
    } else {
        console.error('Elements not found:', {
            passwordField: passwordField,
            toggleIcon: toggleIcon
        });
    }
};