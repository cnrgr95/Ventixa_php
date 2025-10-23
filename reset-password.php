<?php
session_start();
require_once 'includes/database.php';

// Token kontrolü
$token = $_GET['token'] ?? '';
if (empty($token)) {
    header('Location: index.php?error=invalid_token');
    exit;
}

// Token'ı veritabanından kontrol et
$reset_request = $db->fetch(
    "SELECT * FROM password_resets WHERE token = ? AND expires_at > NOW() AND is_used = FALSE",
    [$token]
);

if (!$reset_request) {
    header('Location: index.php?error=expired_token');
    exit;
}

// Dil ayarları
$lang = isset($_GET['lang']) ? $_GET['lang'] : (isset($_SESSION['lang']) ? $_SESSION['lang'] : 'en');
$_SESSION['lang'] = $lang;

// Dil dosyasını yükle
$translations = [];
if (file_exists("lang/{$lang}.json")) {
    $translations = json_decode(file_get_contents("lang/{$lang}.json"), true);
}

// Form gönderildi mi kontrol et
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    if (empty($new_password) || empty($confirm_password)) {
        $error = $translations['messages']['fill_all_fields'] ?? 'Please fill in all fields.';
    } elseif ($new_password !== $confirm_password) {
        $error = $translations['messages']['passwords_not_match'] ?? 'Passwords do not match.';
    } elseif (strlen($new_password) < 12) {
        $error = $translations['messages']['password_min_length'] ?? 'Password must be at least 12 characters.';
    } elseif (!preg_match('/[a-z]/', $new_password)) {
        $error = $translations['messages']['password_lowercase'] ?? 'Password must contain at least one lowercase letter.';
    } elseif (!preg_match('/[A-Z]/', $new_password)) {
        $error = $translations['messages']['password_uppercase'] ?? 'Password must contain at least one uppercase letter.';
    } elseif (!preg_match('/[0-9]/', $new_password)) {
        $error = $translations['messages']['password_number'] ?? 'Password must contain at least one number.';
    } elseif (!preg_match('/[!@#$%^&*()_+\-=\[\]{};\':"\\|,.<>\/?]/', $new_password)) {
        $error = $translations['messages']['password_symbol'] ?? 'Password must contain at least one special character.';
    } else {
        // Şifreyi güncelle
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        
        try {
            $db->query(
                "UPDATE users SET password = ? WHERE email = ?",
                [$hashed_password, $reset_request['email']]
            );
            
            // Token'ı kullanıldı olarak işaretle
            $db->query(
                "UPDATE password_resets SET is_used = TRUE, used_at = NOW() WHERE token = ?",
                [$token]
            );
            
            header('Location: index.php?success=password_reset');
            exit;
            
        } catch (Exception $e) {
            $error = $translations['messages']['error_occurred'] ?? 'An error occurred. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $translations['password_reset']['title'] ?? 'Password Reset'; ?> - Ventixa</title>
    <link rel="stylesheet" href="assets/css/login.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <div class="logo">
                    <img src="assets/img/logo/light_logo.svg" alt="Ventixa" class="logo-img">
                </div>
                <p class="welcome-text"><?php echo $translations['password_reset']['set_new_password'] ?? 'Set Your New Password'; ?></p>
            </div>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-error">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <form class="login-form" method="POST">
                <div class="form-group">
                    <div class="input-group">
                        <i class="fas fa-lock input-icon"></i>
                        <input type="password" id="password" name="password" placeholder="<?php echo $translations['password_reset']['new_password'] ?? 'New Password'; ?>" required autocomplete="new-password">
                        <button type="button" class="password-toggle" id="passwordToggle">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>
                
                <div class="form-group">
                    <div class="input-group">
                        <i class="fas fa-lock input-icon"></i>
                        <input type="password" id="confirm_password" name="confirm_password" placeholder="<?php echo $translations['password_reset']['confirm_password'] ?? 'Confirm Password'; ?>" required autocomplete="new-password">
                        <button type="button" class="password-toggle" id="confirmPasswordToggle">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>
                
                <button type="submit" class="login-button">
                    <i class="fas fa-save"></i>
                    <?php echo $translations['password_reset']['update_button'] ?? 'Update Password'; ?>
                </button>
            </form>
            
            <div class="login-footer">
                <p><a href="index.php"><?php echo $translations['password_reset']['back_to_login'] ?? 'Back to Login'; ?></a></p>
            </div>
        </div>
    </div>
    
    <script>
        // Şifre göster/gizle
        document.getElementById('passwordToggle').addEventListener('click', function() {
            const passwordInput = document.getElementById('password');
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            
            const icon = this.querySelector('i');
            icon.classList.toggle('fa-eye');
            icon.classList.toggle('fa-eye-slash');
        });
        
        document.getElementById('confirmPasswordToggle').addEventListener('click', function() {
            const passwordInput = document.getElementById('confirm_password');
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            
            const icon = this.querySelector('i');
            icon.classList.toggle('fa-eye');
            icon.classList.toggle('fa-eye-slash');
        });
        
        // Şifre eşleşme kontrolü artık JavaScript'te yapılıyor
        // Burada sadece backend validasyonu kalıyor
    </script>
    <script>
        // Çevirileri JavaScript'e aktar
        window.translations = <?php echo json_encode($translations); ?>;
    </script>
    <script src="assets/js/password-strength.js"></script>
</body>
</html>
