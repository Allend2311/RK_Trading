// Admin Dashboard JavaScript
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

    // Form validation
    const productForm = document.querySelector('.product-form');
    if (productForm) {
        productForm.addEventListener('submit', function(e) {
            let hasErrors = false;

            // Clear previous errors
            document.querySelectorAll('.error').forEach(el => el.remove());

            // Get form values
            const name = document.getElementById('name').value.trim();
            const price = document.getElementById('price').value.trim();
            const category = document.getElementById('category').value;
            const stock = document.getElementById('stock').value.trim();
            const description = document.getElementById('description').value.trim();

            // Validate name
            if (!name) {
                showError('name', 'Product name is required');
                hasErrors = true;
            } else if (name.length < 3) {
                showError('name', 'Product name must be at least 3 characters');
                hasErrors = true;
            }

            // Validate price
            if (!price) {
                showError('price', 'Price is required');
                hasErrors = true;
            } else if (isNaN(parseFloat(price)) || parseFloat(price) <= 0) {
                showError('price', 'Price must be greater than 0');
                hasErrors = true;
            }

            // Validate category
            if (!category) {
                showError('category', 'Please select a category');
                hasErrors = true;
            }

            // Validate stock
            if (!stock) {
                showError('stock', 'Stock quantity is required');
                hasErrors = true;
            } else if (isNaN(parseInt(stock)) || parseInt(stock) < 0) {
                showError('stock', 'Stock cannot be negative');
                hasErrors = true;
            }

            // Validate description
            if (!description) {
                showError('description', 'Product description is required');
                hasErrors = true;
            } else if (description.length < 10) {
                showError('description', 'Description must be at least 10 characters');
                hasErrors = true;
            }

            if (hasErrors) {
                e.preventDefault();
            }
        });
    }

    // Clear form function
    window.clearForm = function() {
        const form = document.querySelector('.product-form');
        if (form) {
            form.reset();
            // Clear any error messages
            document.querySelectorAll('.error').forEach(el => el.remove());
        }
    };

    // Show error function
    function showError(fieldId, message) {
        const field = document.getElementById(fieldId);
        if (field) {
            const formGroup = field.closest('.form-group');
            const errorElement = document.createElement('span');
            errorElement.className = 'error';
            errorElement.textContent = message;
            formGroup.appendChild(errorElement);
        }
    }

    // Animate stat cards on load
    const statCards = document.querySelectorAll('.stat-card');
    statCards.forEach((card, index) => {
        card.style.animationDelay = `${index * 0.1}s`;
    });

    // Animate section cards
    const sectionCards = document.querySelectorAll('.section-card');
    sectionCards.forEach((card, index) => {
        card.style.animationDelay = `${0.2 + index * 0.1}s`;
    });

    // Animate order cards
    const orderCards = document.querySelectorAll('.order-card');
    orderCards.forEach((card, index) => {
        card.style.animationDelay = `${index * 0.1}s`;
    });

    // Hover effects for stat cards
    statCards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-8px) scale(1.02)';
        });

        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0) scale(1)';
        });
    });

    // Hover effects for order items
    const orderItems = document.querySelectorAll('.order-item, .product-item');
    orderItems.forEach(item => {
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
                this.style.boxShadow = '0 4px 12px rgba(76, 175, 80, 0.3)';
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

    // Form input focus effects
    const formInputs = document.querySelectorAll('input, select, textarea');
    formInputs.forEach(input => {
        input.addEventListener('focus', function() {
            this.parentElement.style.transform = 'scale(1.02)';
            this.parentElement.style.transition = 'transform 0.2s ease';
        });

        input.addEventListener('blur', function() {
            this.parentElement.style.transform = 'scale(1)';
        });
    });

    // Success alert auto-hide
    const successAlert = document.querySelector('.success-alert');
    if (successAlert) {
        setTimeout(() => {
            successAlert.style.animation = 'slideInDown 0.3s ease reverse';
            setTimeout(() => {
                successAlert.remove();
            }, 300);
        }, 5000);
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

    // Add loading states for form submission
    if (productForm) {
        productForm.addEventListener('submit', function() {
            const submitBtn = this.querySelector('.btn-primary');
            if (submitBtn) {
                submitBtn.innerHTML = `
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="animate-spin">
                        <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle>
                        <path d="M4 12a8 8 0 0 1 8-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 0 1 4 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Adding Product...
                `;
                submitBtn.disabled = true;
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

    // Add tooltips for status badges
    const statusBadges = document.querySelectorAll('.status-badge');
    statusBadges.forEach(badge => {
        badge.setAttribute('title', `Status: ${badge.textContent.trim()}`);
    });
});
