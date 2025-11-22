// Order Tracking JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // Animate timeline items on load
    const timelineItems = document.querySelectorAll('.timeline-item');
    timelineItems.forEach((item, index) => {
        item.style.animationDelay = `${index * 0.1}s`;
        item.style.opacity = '1';
    });

    // Form submission handling
    const trackingForm = document.querySelector('.tracking-form');
    if (trackingForm) {
        trackingForm.addEventListener('submit', function(e) {
            const submitBtn = this.querySelector('.btn-primary');
            if (submitBtn) {
                const originalText = submitBtn.innerHTML;
                submitBtn.innerHTML = `
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="animate-spin">
                        <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle>
                        <path d="M4 12a8 8 0 0 1 8-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 0 1 4 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Tracking...
                `;
                submitBtn.disabled = true;

                // Re-enable after form submission
                setTimeout(() => {
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                }, 2000);
            }
        });
    }

    // Input focus effects
    const orderInput = document.getElementById('order_number');
    if (orderInput) {
        orderInput.addEventListener('focus', function() {
            this.parentElement.style.transform = 'scale(1.02)';
            this.parentElement.style.transition = 'transform 0.2s ease';
        });

        orderInput.addEventListener('blur', function() {
            this.parentElement.style.transform = 'scale(1)';
        });

        // Auto-uppercase input
        orderInput.addEventListener('input', function() {
            this.value = this.value.toUpperCase();
        });
    }

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

    // Timeline item hover effects
    timelineItems.forEach(item => {
        item.addEventListener('mouseenter', function() {
            const content = this.querySelector('.timeline-content');
            if (content) {
                content.style.transform = 'translateX(8px)';
                content.style.boxShadow = '0 8px 24px rgba(0, 0, 0, 0.15)';
            }
        });

        item.addEventListener('mouseleave', function() {
            const content = this.querySelector('.timeline-content');
            if (content) {
                content.style.transform = 'translateX(0)';
                content.style.boxShadow = '';
            }
        });
    });

    // Detail card hover effects
    const detailCards = document.querySelectorAll('.detail-card');
    detailCards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-4px)';
            this.style.boxShadow = '0 8px 24px rgba(0, 0, 0, 0.15)';
        });

        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
            this.style.boxShadow = '';
        });
    });

    // Support button interactions
    const supportButtons = document.querySelectorAll('.support-buttons .btn');
    supportButtons.forEach(button => {
        button.addEventListener('click', function() {
            if (this.textContent.includes('Email Support')) {
                alert('Email support feature coming soon!');
            } else if (this.textContent.includes('Live Chat')) {
                alert('Live chat feature coming soon!');
            }
        });
    });

    // Back button confirmation
    const backBtn = document.querySelector('.back-btn');
    if (backBtn) {
        backBtn.addEventListener('click', function(e) {
            if (confirm('Are you sure you want to go back?')) {
                // Continue with navigation
            } else {
                e.preventDefault();
            }
        });
    }

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

    // Keyboard navigation
    document.addEventListener('keydown', function(e) {
        // ESC to go back
        if (e.key === 'Escape') {
            if (confirm('Are you sure you want to go back?')) {
                window.location.href = '?back=1';
            }
        }

        // Enter to submit form if input is focused
        if (e.key === 'Enter' && document.activeElement === orderInput) {
            trackingForm.dispatchEvent(new Event('submit'));
        }
    });

    // Copy order number functionality
    const orderNumberElement = document.querySelector('.order-header h2');
    if (orderNumberElement) {
        orderNumberElement.addEventListener('click', function() {
            const orderNumber = this.textContent.replace('Order #', '');
            navigator.clipboard.writeText(orderNumber).then(function() {
                // Show temporary feedback
                const originalText = orderNumberElement.textContent;
                orderNumberElement.textContent = 'Copied!';
                orderNumberElement.style.color = '#4caf50';

                setTimeout(() => {
                    orderNumberElement.textContent = originalText;
                    orderNumberElement.style.color = '';
                }, 1000);
            });
        });

        // Add cursor pointer to indicate clickability
        orderNumberElement.style.cursor = 'pointer';
        orderNumberElement.title = 'Click to copy order number';
    }

    // Timeline progress animation
    function animateTimelineProgress() {
        const completedItems = document.querySelectorAll('.timeline-item.completed');
        completedItems.forEach((item, index) => {
            setTimeout(() => {
                const line = item.querySelector('.timeline-line');
                if (line) {
                    line.style.height = '100%';
                    line.style.transition = 'height 0.8s ease';
                }
            }, index * 200);
        });
    }

    // Trigger timeline animation after page load
    setTimeout(animateTimelineProgress, 500);

    // Add tooltips for status badges
    const statusBadges = document.querySelectorAll('.status-badge, .current-badge');
    statusBadges.forEach(badge => {
        badge.setAttribute('title', 'Current order status');
    });

    // Add tooltips for icons
    const icons = document.querySelectorAll('.timeline-icon svg');
    icons.forEach(icon => {
        const parentItem = icon.closest('.timeline-item');
        if (parentItem) {
            const title = parentItem.querySelector('h3').textContent;
            icon.setAttribute('title', title);
        }
    });

    // Error message auto-hide
    const errorMessage = document.querySelector('.error');
    if (errorMessage) {
        setTimeout(() => {
            errorMessage.style.animation = 'fadeOut 0.3s ease';
            setTimeout(() => {
                errorMessage.remove();
            }, 300);
        }, 5000);
    }

    // Add visual feedback for successful tracking
    const trackingResults = document.querySelector('.order-info-card');
    if (trackingResults) {
        trackingResults.style.animation += ', pulse 2s ease-in-out infinite';
    }
});
