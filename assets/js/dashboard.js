// Dashboard JavaScript

// Çeviri fonksiyonu
function getTranslation(key) {
    if (typeof window.translations === 'undefined') {
        return null;
    }

    const keys = key.split('.');
    let value = window.translations;

    for (let k of keys) {
        if (value && typeof value === 'object' && k in value) {
            value = value[k];
        } else {
            return null;
        }
    }

    return value;
}

document.addEventListener('DOMContentLoaded', function() {
    // Sidebar toggle for mobile
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebar = document.querySelector('.sidebar');
    
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', function() {
            sidebar.classList.toggle('open');
        });
    }
    
    // Dil değiştirme
    window.changeLanguage = function(lang) {
        const url = new URL(window.location);
        url.searchParams.set('lang', lang);
        window.location.href = url.toString();
    };
    
    // Auto-hide alerts
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = '0';
            setTimeout(() => {
                alert.remove();
            }, 300);
        }, 5000);
    });
    
    // Smooth scrolling for anchor links
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
    
    // Logout confirmation
    const logoutBtn = document.querySelector('.logout-btn');
    if (logoutBtn) {
        logoutBtn.addEventListener('click', function(e) {
            e.preventDefault();
            showLogoutConfirmation();
        });
    }
    
    // Initialize tooltips (if using a tooltip library)
    if (typeof tippy !== 'undefined') {
        tippy('[data-tippy-content]');
    }
    
    // Real-time clock
    function updateClock() {
        const now = new Date();
        const timeString = now.toLocaleTimeString();
        const clockElement = document.getElementById('clock');
        if (clockElement) {
            clockElement.textContent = timeString;
        }
    }
    
    // Update clock every second
    setInterval(updateClock, 1000);
    updateClock();
    
    // Auto-refresh stats (if needed)
    function refreshStats() {
        // This would typically make an AJAX call to get updated stats
        console.log('Refreshing stats...');
    }
    
    // Refresh stats every 5 minutes
    setInterval(refreshStats, 5 * 60 * 1000);
    
    // Handle form submissions with loading states
    document.querySelectorAll('form').forEach(form => {
        form.addEventListener('submit', function() {
            const submitBtn = this.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
            }
        });
    });
    
    // Keyboard shortcuts
    document.addEventListener('keydown', function(e) {
        // Ctrl/Cmd + K for search
        if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
            e.preventDefault();
            const searchInput = document.querySelector('input[type="search"]');
            if (searchInput) {
                searchInput.focus();
            }
        }
        
        // Escape to close modals
        if (e.key === 'Escape') {
            const openModal = document.querySelector('.modal.show');
            if (openModal) {
                openModal.classList.remove('show');
            }
        }
    });
    
    // Lazy loading for images
    if ('IntersectionObserver' in window) {
        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src;
                    img.classList.remove('lazy');
                    imageObserver.unobserve(img);
                }
            });
        });
        
        document.querySelectorAll('img[data-src]').forEach(img => {
            imageObserver.observe(img);
        });
    }
    
    // Initialize charts (if using Chart.js)
    if (typeof Chart !== 'undefined') {
        // Chart initialization code would go here
        console.log('Charts initialized');
    }
    
    // Handle responsive table
    const tables = document.querySelectorAll('table');
    tables.forEach(table => {
        if (table.scrollWidth > table.clientWidth) {
            table.classList.add('scrollable');
        }
    });
    
    // Add loading state to buttons
    document.querySelectorAll('.btn-loading').forEach(btn => {
        btn.addEventListener('click', function() {
            this.classList.add('loading');
            this.disabled = true;
            
            // Re-enable after 3 seconds (or when the action completes)
            setTimeout(() => {
                this.classList.remove('loading');
                this.disabled = false;
            }, 3000);
        });
    });
    
    // Initialize date pickers (if using a date picker library)
    if (typeof flatpickr !== 'undefined') {
        flatpickr('.datepicker', {
            dateFormat: 'Y-m-d',
            allowInput: true
        });
    }
    
    // Handle file uploads with preview
    document.querySelectorAll('input[type="file"]').forEach(input => {
        input.addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                const preview = document.getElementById(this.dataset.preview);
                if (preview) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        preview.src = e.target.result;
                        preview.style.display = 'block';
                    };
                    reader.readAsDataURL(file);
                }
            }
        });
    });
    
    // Initialize notification system
    window.showNotification = function(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        notification.innerHTML = `
            <div class="notification-content">
                <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'}"></i>
                <span>${message}</span>
                <button class="notification-close">&times;</button>
            </div>
        `;
        
        document.body.appendChild(notification);
        
        // Auto remove after 5 seconds
        setTimeout(() => {
            notification.remove();
        }, 5000);
        
        // Close button
        notification.querySelector('.notification-close').addEventListener('click', () => {
            notification.remove();
        });
    };
    
    // Initialize tooltips for help text
    document.querySelectorAll('[data-help]').forEach(element => {
        element.addEventListener('mouseenter', function() {
            const helpText = this.dataset.help;
            const tooltip = document.createElement('div');
            tooltip.className = 'tooltip';
            tooltip.textContent = helpText;
            document.body.appendChild(tooltip);
            
            const rect = this.getBoundingClientRect();
            tooltip.style.left = rect.left + 'px';
            tooltip.style.top = (rect.top - tooltip.offsetHeight - 5) + 'px';
        });
        
        element.addEventListener('mouseleave', function() {
            const tooltip = document.querySelector('.tooltip');
            if (tooltip) {
                tooltip.remove();
            }
        });
    });
});

// Logout confirmation popup
function showLogoutConfirmation() {
    const confirmationText = getTranslation('navigation.logout_confirmation') || 'Are you sure you want to logout?';
    const yesText = getTranslation('navigation.logout_confirm_yes') || 'Yes, Logout';
    const noText = getTranslation('navigation.logout_confirm_no') || 'Cancel';
    
    // Create modal overlay
    const overlay = document.createElement('div');
    overlay.className = 'logout-modal-overlay';
    overlay.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 10000;
        animation: fadeIn 0.3s ease;
    `;
    
    // Create modal
    const modal = document.createElement('div');
    modal.className = 'logout-modal';
    modal.style.cssText = `
        background: white;
        border-radius: 12px;
        padding: 30px;
        max-width: 400px;
        width: 90%;
        text-align: center;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        animation: slideIn 0.3s ease;
    `;
    
    // Create content
    modal.innerHTML = `
        <div style="margin-bottom: 20px;">
            <i class="fas fa-sign-out-alt" style="font-size: 48px; color: #e74c3c; margin-bottom: 15px;"></i>
            <h3 style="margin: 0 0 10px 0; color: #2c3e50; font-size: 1.3rem;">${confirmationText}</h3>
        </div>
        <div style="display: flex; gap: 15px; justify-content: center;">
            <button class="logout-confirm-btn logout-confirm-no" style="
                background: #95a5a6;
                color: white;
                border: none;
                padding: 12px 24px;
                border-radius: 8px;
                cursor: pointer;
                font-size: 1rem;
                font-weight: 500;
                transition: all 0.3s ease;
                flex: 1;
            ">${noText}</button>
            <button class="logout-confirm-btn logout-confirm-yes" style="
                background: #e74c3c;
                color: white;
                border: none;
                padding: 12px 24px;
                border-radius: 8px;
                cursor: pointer;
                font-size: 1rem;
                font-weight: 500;
                transition: all 0.3s ease;
                flex: 1;
            ">${yesText}</button>
        </div>
    `;
    
    // Add CSS animations
    const style = document.createElement('style');
    style.textContent = `
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        @keyframes slideIn {
            from { transform: translateY(-50px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        .logout-confirm-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }
        .logout-confirm-no:hover {
            background: #7f8c8d !important;
        }
        .logout-confirm-yes:hover {
            background: #c0392b !important;
        }
    `;
    document.head.appendChild(style);
    
    overlay.appendChild(modal);
    document.body.appendChild(overlay);
    
    // Event listeners
    modal.querySelector('.logout-confirm-no').addEventListener('click', function() {
        document.body.removeChild(overlay);
        document.head.removeChild(style);
    });
    
    modal.querySelector('.logout-confirm-yes').addEventListener('click', function() {
        // Redirect to logout
        window.location.href = 'includes/logout.php';
    });
    
    // Close on overlay click
    overlay.addEventListener('click', function(e) {
        if (e.target === overlay) {
            document.body.removeChild(overlay);
            document.head.removeChild(style);
        }
    });
    
    // Close on Escape key
    const handleEscape = function(e) {
        if (e.key === 'Escape') {
            document.body.removeChild(overlay);
            document.head.removeChild(style);
            document.removeEventListener('keydown', handleEscape);
        }
    };
    document.addEventListener('keydown', handleEscape);
}
