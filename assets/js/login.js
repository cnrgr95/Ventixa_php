// Login Sayfası JavaScript
document.addEventListener('DOMContentLoaded', function() {
    const loginForm = document.getElementById('loginForm');
    const passwordToggle = document.getElementById('passwordToggle');
    const passwordInput = document.getElementById('password');
    const forgotPasswordForm = document.getElementById('forgotPasswordForm');
    
    // Şifre göster/gizle
    passwordToggle.addEventListener('click', function() {
        const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordInput.setAttribute('type', type);
        
        const icon = this.querySelector('i');
        icon.classList.toggle('fa-eye');
        icon.classList.toggle('fa-eye-slash');
    });
    
    // Form gönderimi
    loginForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const submitButton = this.querySelector('.login-button');
        
        // Loading durumu
        this.classList.add('loading');
        submitButton.disabled = true;
        
        // AJAX ile form gönderimi
        fetch('includes/auth.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert('success', data.message);
                setTimeout(() => {
                    window.location.href = 'dashboard.php';
                }, 1500);
            } else {
                showAlert('error', data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('error', 'An error occurred. Please try again.');
        })
        .finally(() => {
            this.classList.remove('loading');
            submitButton.disabled = false;
        });
    });
    
    // Şifre sıfırlama formu
    forgotPasswordForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const submitButton = this.querySelector('.btn-primary');
        
        submitButton.disabled = true;
        submitButton.textContent = 'Sending...';
        
        fetch('includes/forgot-password.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert('success', data.message);
                closeForgotPassword();
                this.reset();
            } else {
                showAlert('error', data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('error', 'An error occurred. Please try again.');
        })
        .finally(() => {
            submitButton.disabled = false;
        submitButton.textContent = 'Send Reset Link';
        });
    });
    
    // Dil değiştirme
    window.changeLanguage = function(lang) {
        const url = new URL(window.location);
        url.searchParams.set('lang', lang);
        window.location.href = url.toString();
    };
    
    // Şifre sıfırlama modal
    window.showForgotPassword = function() {
        document.getElementById('forgotPasswordModal').style.display = 'block';
    };
    
    window.closeForgotPassword = function() {
        document.getElementById('forgotPasswordModal').style.display = 'none';
    };
    
    // Modal dışına tıklayınca kapat
    window.addEventListener('click', function(e) {
        const modal = document.getElementById('forgotPasswordModal');
        if (e.target === modal) {
            modal.style.display = 'none';
        }
    });
    
    // ESC tuşu ile modal kapat
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeForgotPassword();
        }
    });
    
    // Alert mesajları göster
    function showAlert(type, message) {
        // Önceki alert'i kaldır
        const existingAlert = document.querySelector('.alert');
        if (existingAlert) {
            existingAlert.remove();
        }
        
        // Yeni alert oluştur
        const alert = document.createElement('div');
        alert.className = `alert alert-${type}`;
        alert.textContent = message;
        
        // Form'dan önce ekle
        const loginForm = document.getElementById('loginForm');
        loginForm.parentNode.insertBefore(alert, loginForm);
        
        // 5 saniye sonra kaldır
        setTimeout(() => {
            alert.remove();
        }, 5000);
    }
    
    // Form validasyonu
    function validateForm() {
        const email = document.getElementById('email').value;
        const password = document.getElementById('password').value;
        
        if (!email || !password) {
            showAlert('error', 'Please fill in all fields.');
            return false;
        }
        
    if (!isValidEmail(email)) {
        showAlert('error', 'Please enter a valid email address.');
        return false;
    }
    
    // Şifre güvenlik kontrolü
    if (password.length < 12) {
        showAlert('error', 'Password must be at least 12 characters.');
        return false;
    }
    
    if (!/[a-z]/.test(password)) {
        showAlert('error', 'Password must contain at least one lowercase letter.');
        return false;
    }
    
    if (!/[A-Z]/.test(password)) {
        showAlert('error', 'Password must contain at least one uppercase letter.');
        return false;
    }
    
    if (!/[0-9]/.test(password)) {
        showAlert('error', 'Password must contain at least one number.');
        return false;
    }
    
    if (!/[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(password)) {
        showAlert('error', 'Password must contain at least one special character.');
        return false;
    }
    
    return true;
    }
    
    // E-posta validasyonu
    function isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }
    
    // Enter tuşu ile form gönderimi
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Enter' && !e.target.matches('textarea')) {
            const form = e.target.closest('form');
            if (form && form.id === 'loginForm') {
                e.preventDefault();
                if (validateForm()) {
                    form.dispatchEvent(new Event('submit'));
                }
            }
        }
    });
    
    // Sayfa yüklendiğinde e-posta alanına odaklan
    document.getElementById('email').focus();
});
