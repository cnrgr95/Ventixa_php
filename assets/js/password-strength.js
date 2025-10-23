// Çeviri fonksiyonu
function getTranslation(key) {
    // Sayfa yüklendiğinde çevirileri al
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

// Şifre güvenlik kontrolü
function checkPasswordStrength(password) {
    const requirements = {
        length: password.length >= 12,
        lowercase: /[a-z]/.test(password),
        uppercase: /[A-Z]/.test(password),
        number: /[0-9]/.test(password),
        symbol: /[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(password)
    };
    
    let score = 0;
    Object.values(requirements).forEach(req => {
        if (req) score++;
    });
    
    let strength = 'weak';
    let strengthText = getTranslation('messages.password_weak') || 'Weak Password';
    let strengthColor = '#e74c3c';
    
    if (score === 5) {
        strength = 'very_strong';
        strengthText = getTranslation('messages.password_very_strong') || 'Very Strong Password';
        strengthColor = '#27ae60';
    } else if (score >= 4) {
        strength = 'strong';
        strengthText = getTranslation('messages.password_strong') || 'Strong Password';
        strengthColor = '#2ecc71';
    } else if (score >= 3) {
        strength = 'medium';
        strengthText = getTranslation('messages.password_medium') || 'Medium Password';
        strengthColor = '#f39c12';
    }
    
    return {
        strength: strength,
        strengthText: strengthText,
        strengthColor: strengthColor,
        requirements: requirements,
        score: score
    };
}

// Şifre güvenlik göstergesi oluştur
function createPasswordStrengthIndicator() {
    const passwordInput = document.getElementById('password');
    const confirmPasswordInput = document.getElementById('confirm_password');
    
    if (!passwordInput) return;
    
    // Şifre güvenlik container'ı oluştur (gereksinimler ve güvenlik göstergesi birlikte)
    const strengthContainer = document.createElement('div');
    strengthContainer.className = 'password-strength-container';
    strengthContainer.innerHTML = `
        <div class="password-requirements" id="passwordRequirements"></div>
        <div class="password-strength-bar">
            <div class="password-strength-fill" id="strengthFill"></div>
        </div>
        <div class="password-strength-text" id="strengthText"></div>
    `;
    
    // Şifre input'unun üstüne ekle
    passwordInput.parentNode.parentNode.parentNode.insertBefore(strengthContainer, passwordInput.parentNode.parentNode);
    
    // Şifre değişikliklerini dinle
    passwordInput.addEventListener('input', function() {
        updatePasswordStrength(this.value);
        checkPasswordMatch(); // Şifre değiştiğinde de eşleşme kontrolü yap
    });
    
    // Şifre tekrar kontrolü
    if (confirmPasswordInput) {
        confirmPasswordInput.addEventListener('input', function() {
            checkPasswordMatch();
        });
    }
}

// Şifre güvenlik güncelleme
function updatePasswordStrength(password) {
    const strength = checkPasswordStrength(password);
    const strengthFill = document.getElementById('strengthFill');
    const strengthText = document.getElementById('strengthText');
    const requirements = document.getElementById('passwordRequirements');
    const updateButton = document.querySelector('button[type="submit"]');
    
    if (!strengthFill || !strengthText || !requirements) return;
    
    // Güvenlik barını güncelle
    strengthFill.style.width = (strength.score * 20) + '%';
    strengthFill.style.backgroundColor = strength.strengthColor;
    
    // Güvenlik metnini güncelle
    strengthText.textContent = strength.strengthText;
    strengthText.style.color = strength.strengthColor;
    
    // Gereksinimleri göster
    const reqItems = Object.entries(strength.requirements).map(([key, met]) => {
        const icons = {
            length: '📏',
            lowercase: '🔤',
            uppercase: '🔠',
            number: '🔢',
            symbol: '🔣'
        };
        
        const labels = {
            length: getTranslation('messages.password_length') || 'At least 12 characters',
            lowercase: getTranslation('messages.password_lowercase') || 'At least one lowercase letter',
            uppercase: getTranslation('messages.password_uppercase') || 'At least one uppercase letter',
            number: getTranslation('messages.password_number') || 'At least one number',
            symbol: getTranslation('messages.password_symbol') || 'At least one special character'
        };
        
        return `
            <div class="requirement-item ${met ? 'met' : 'not-met'}">
                <span class="requirement-icon">${icons[key]}</span>
                <span class="requirement-text">${labels[key]}</span>
                <span class="requirement-check">${met ? '✓' : '✗'}</span>
            </div>
        `;
    }).join('');
    
    const requirementsTitle = getTranslation('messages.password_must_contain') || 'Password must contain:';
    requirements.innerHTML = `
        <div class="requirements-title">${requirementsTitle}</div>
        <div class="requirements-list">${reqItems}</div>
    `;
    
    // Şifre güncelle butonunu kontrol et
    updateButtonState(strength.score === 5);
}

// Buton durumunu güncelle
function updateButtonState(isValid) {
    const updateButton = document.querySelector('button[type="submit"]');
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirm_password').value;
    
    if (!updateButton) return;
    
    // Hem şifre kriterleri hem de eşleşme kontrolü
    const passwordsMatch = password === confirmPassword && password.length > 0;
    const allRequirementsMet = isValid && passwordsMatch;
    
    if (allRequirementsMet) {
        // Buton aktif
        updateButton.disabled = false;
        updateButton.classList.remove('disabled');
        updateButton.style.opacity = '1';
        updateButton.style.cursor = 'pointer';
    } else {
        // Buton pasif
        updateButton.disabled = true;
        updateButton.classList.add('disabled');
        updateButton.style.opacity = '0.5';
        updateButton.style.cursor = 'not-allowed';
    }
}

// Şifre eşleşme kontrolü
function checkPasswordMatch() {
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirm_password').value;
    const confirmInput = document.getElementById('confirm_password');
    
    if (!confirmInput) return;
    
    // Şifre eşleşme göstergesi oluştur
    let matchIndicator = document.getElementById('passwordMatchIndicator');
    if (!matchIndicator) {
        matchIndicator = document.createElement('div');
        matchIndicator.id = 'passwordMatchIndicator';
        matchIndicator.className = 'password-match-indicator';
        confirmInput.parentNode.parentNode.appendChild(matchIndicator);
    }
    
    if (confirmPassword.length === 0) {
        // Şifre tekrar alanı boşsa göstergi gizle
        matchIndicator.style.display = 'none';
        confirmInput.style.borderColor = '#e1e5e9';
        confirmInput.style.backgroundColor = '#fff';
    } else if (password === confirmPassword) {
        // Şifreler eşleşiyor
        matchIndicator.innerHTML = `
            <div class="match-indicator success">
                <i class="fas fa-check-circle"></i>
                <span>${getTranslation('messages.passwords_match') || 'Passwords match'}</span>
            </div>
        `;
        matchIndicator.style.display = 'block';
        confirmInput.style.borderColor = '#27ae60';
        confirmInput.style.backgroundColor = '#f0fff4';
    } else {
        // Şifreler eşleşmiyor
        matchIndicator.innerHTML = `
            <div class="match-indicator error">
                <i class="fas fa-times-circle"></i>
                <span>${getTranslation('messages.passwords_not_match') || 'Passwords do not match'}</span>
            </div>
        `;
        matchIndicator.style.display = 'block';
        confirmInput.style.borderColor = '#e74c3c';
        confirmInput.style.backgroundColor = '#fdf2f2';
    }
    
    // Buton durumunu güncelle
    const strength = checkPasswordStrength(password);
    updateButtonState(strength.score === 5);
}

// Sayfa yüklendiğinde şifre güvenlik göstergesini başlat
document.addEventListener('DOMContentLoaded', function() {
    createPasswordStrengthIndicator();
    
    // Başlangıçta butonu pasif yap
    const updateButton = document.querySelector('button[type="submit"]');
    if (updateButton) {
        updateButton.disabled = true;
        updateButton.classList.add('disabled');
        updateButton.style.opacity = '0.5';
        updateButton.style.cursor = 'not-allowed';
    }
});
