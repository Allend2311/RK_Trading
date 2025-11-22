// Customer Dashboard JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // Tab switching functionality
    window.showTab = function(tabName) {
        // Hide all tab contents
        const tabContents = document.querySelectorAll('.tab-content');
        tabContents.forEach(content => {
            content.classList.remove('active');
        });

        // Remove active class from all tab buttons
        const tabButtons = document.querySelectorAll('.tab-btn');
        tabButtons.forEach(button => {
            button.classList.remove('active');
        });

        // Show selected tab content
        const selectedTab = document.getElementById(tabName);
        if (selectedTab) {
            selectedTab.classList.add('active');
        }

        // Add active class to clicked button
        const clickedButton = document.querySelector(`[onclick="showTab('${tabName}')"]`);
        if (clickedButton) {
            clickedButton.classList.add('active');
        }
    };

    // Track order function (placeholder)
    window.trackOrder = function() {
        alert('Order tracking feature coming soon!');
    };

    // Animate product cards on load
    const productCards = document.querySelectorAll('.product-card');
    productCards.forEach((card, index) => {
        card.style.animationDelay = `${index * 0.05}s`;
        card.style.opacity = '1';
    });

    // Animate cart items on load
    const cartItems = document.querySelectorAll('.cart-item');
    cartItems.forEach((item, index) => {
        item.style.animationDelay = `${index * 0.1}s`;
    });

    // Hover effects for product cards
    productCards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-8px) scale(1.02)';
        });

        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0) scale(1)';
        });
    });

    // Hover effects for cart items
    cartItems.forEach(item => {
        item.addEventListener('mouseenter', function() {
            this.style.transform = 'translateX(8px)';
        });

        item.addEventListener('mouseleave', function() {
            this.style.transform = 'translateX(0)';
        });
    });

    // Button hover effects
    const buttons = document.querySelectorAll('.btn');
    buttons.forEach(button => {
        button.addEventListener('mouseenter', function() {
            if (this.classList.contains('btn-primary')) {
                this.style.transform = 'translateY(-3px) scale(1.05)';
            } else if (this.classList.contains('btn-outline')) {
                this.style.boxShadow = '0 4px 12px rgba(0, 0, 0, 0.1)';
            }
        });

        button.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0) scale(1)';
            this.style.boxShadow = '';
        });
    });

    // Tab button hover effects
    const tabButtons = document.querySelectorAll('.tab-btn');
    tabButtons.forEach(button => {
        button.addEventListener('mouseenter', function() {
            if (!this.classList.contains('active')) {
                this.style.background = '#f8f9fa';
                this.style.color = '#333';
            }
        });

        button.addEventListener('mouseleave', function() {
            if (!this.classList.contains('active')) {
                this.style.background = 'transparent';
                this.style.color = '#6b7280';
            }
        });
    });

    // Search input focus effects
    const searchInput = document.querySelector('.search-input-group input');
    if (searchInput) {
        searchInput.addEventListener('focus', function() {
            this.parentElement.style.transform = 'scale(1.02)';
            this.parentElement.style.transition = 'transform 0.2s ease';
        });

        searchInput.addEventListener('blur', function() {
            this.parentElement.style.transform = 'scale(1)';
        });
    }

    // Filter button effects
    const filterButtons = document.querySelectorAll('.filter-btn');
    filterButtons.forEach(button => {
        button.addEventListener('mouseenter', function() {
            if (!this.classList.contains('active')) {
                this.style.borderColor = '#4caf50';
                this.style.color = '#4caf50';
            }
        });

        button.addEventListener('mouseleave', function() {
            if (!this.classList.contains('active')) {
                this.style.borderColor = '#e5e7eb';
                this.style.color = '#6b7280';
            }
        });
    });

    // Quantity button effects
    const quantityButtons = document.querySelectorAll('.quantity-btn');
    quantityButtons.forEach(button => {
        button.addEventListener('mouseenter', function() {
            this.style.background = '#4caf50';
            this.style.color = 'white';
        });

        button.addEventListener('mouseleave', function() {
            this.style.background = 'white';
            this.style.color = '#6b7280';
        });
    });

    // Success/error message auto-hide
    const messages = document.querySelectorAll('.message');
    messages.forEach(message => {
        setTimeout(() => {
            message.style.animation = 'slideInDown 0.3s ease reverse';
            setTimeout(() => {
                message.remove();
            }, 300);
        }, 5000);
    });

    // Smooth scrolling for any anchor links
    const anchorLinks = document.querySelectorAll('a[href^="#"]');
    anchorLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            const targetId = this.getAttribute('href');
            if (targetId !== '#') {
                e.preventDefault();
                const targetElement = document.querySelector(targetId);
                if (targetElement) {
                    targetElement.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            }
        });
    });

    // Add loading states for form submissions
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function() {
            const submitBtn = this.querySelector('.btn-primary, .quantity-btn, .btn');
            if (submitBtn && !submitBtn.classList.contains('quantity-btn')) {
                const originalText = submitBtn.innerHTML;
                submitBtn.innerHTML = `
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="animate-spin">
                        <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle>
                        <path d="M4 12a8 8 0 0 1 8-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 0 1 4 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Processing...
                `;
                submitBtn.disabled = true;

                // Re-enable after 2 seconds (in case of slow response)
                setTimeout(() => {
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                }, 2000);
            }
        });
    });

    // Add CSS for animate-spin if not present
    if (!document.querySelector('#animate-spin-style')) {
        const style = document.createElement('style');
        style.id = 'animate-spin-style';
        style.textContent = `
            @keyframes spin {
                to {
                    transform: rotate(360deg);
                }
            }
            .animate-spin {
                animation: spin 1s linear infinite;
            }
        `;
        document.head.appendChild(style);
    }

    // Keyboard navigation for tabs
    document.addEventListener('keydown', function(e) {
        if (e.key === 'ArrowLeft' || e.key === 'ArrowRight') {
            const activeTab = document.querySelector('.tab-btn.active');
            const allTabs = Array.from(document.querySelectorAll('.tab-btn'));
            const currentIndex = allTabs.indexOf(activeTab);

            let newIndex;
            if (e.key === 'ArrowLeft') {
                newIndex = currentIndex > 0 ? currentIndex - 1 : allTabs.length - 1;
            } else {
                newIndex = currentIndex < allTabs.length - 1 ? currentIndex + 1 : 0;
            }

            const newTab = allTabs[newIndex];
            const tabName = newTab.getAttribute('onclick').match(/showTab\('(.+)'\)/)[1];
            showTab(tabName);
        }
    });

    // Image lazy loading and error handling
    const images = document.querySelectorAll('img');
    images.forEach(img => {
        img.addEventListener('error', function() {
            this.src = 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjQiIGhlaWdodD0iMjQiIHZpZXdCb3g9IjAgMCAyNCAyNCIgZmlsbD0ibm9uZSIgc3Ryb2tlPSIjOWNhM2FmIiBzdHJva2Utd2lkdGg9IjIiPjxyZWN0IHg9IjMiIHk9IjMiIHdpZHRoPSIxOCIgaGVpZ2h0PSIxOCIgcng9IjIiIHJ5PSIyIj48L3JlY3Q+PGNpcmNsZSBjeD0iOSIgY3k9IjkiIHI9IjIiPjwvY2lyY2xlPjxwYXRoIGQ9Im0yMSAyMS00LjM1LTQuMzUiPjwvcGF0aD48L3N2Zz4=';
            this.alt = 'Image not available';
        });
    });

    // Add tooltips for ratings
    const ratings = document.querySelectorAll('.rating');
    ratings.forEach(rating => {
        rating.setAttribute('title', `Rating: ${rating.textContent.replace('⭐ ', '')}/5`);
    });

    // Add tooltips for stock badges
    const stockBadges = document.querySelectorAll('.stock-badge');
    stockBadges.forEach(badge => {
        badge.setAttribute('title', 'Available stock quantity');
    });

    // Auto-refresh cart count in tab (if needed)
    function updateCartCount() {
        const cartTab = document.querySelector('[onclick="showTab(\'cart\')"]');
        if (cartTab) {
            const cartCount = document.querySelectorAll('.cart-item').length;
            const existingText = cartTab.textContent.replace(/\(\d+\)/, '').trim();
            cartTab.innerHTML = `
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="8" cy="21" r="1"></circle>
                    <circle cx="19" cy="21" r="1"></circle>
                    <path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"></path>
                </svg>
                Cart ${cartCount > 0 ? `(${cartCount})` : ''}
            `;
        }
    }

    // Update cart count on page load
    updateCartCount();

    // Add visual feedback for successful actions
    const successMessage = document.querySelector('.success-message');
    if (successMessage) {
        // Add a subtle pulse animation
        successMessage.style.animation += ', pulse 2s ease-in-out infinite';
    }
});
