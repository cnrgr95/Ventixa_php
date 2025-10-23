<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $translations['login']['title'] ?? 'Login'; ?> - Ventixa</title>
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
                <p class="welcome-text"><?php echo $translations['messages']['welcome'] ?? 'Welcome to Ventixa'; ?></p>
            </div>
            
            <?php if (!empty($message)): ?>
                <div class="alert alert-<?php echo $message_type; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>
            
            <form class="login-form" id="loginForm" method="POST" action="includes/auth.php">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                <div class="form-group">
                    <div class="input-group">
                        <i class="fas fa-envelope input-icon"></i>
                        <input type="email" id="email" name="email" placeholder="<?php echo $translations['login']['email'] ?? 'E-Mail'; ?>" required autocomplete="email">
                    </div>
                </div>
                
                <div class="form-group">
                    <div class="input-group">
                        <i class="fas fa-lock input-icon"></i>
                        <input type="password" id="password" name="password" placeholder="<?php echo $translations['login']['password'] ?? 'Password'; ?>" required autocomplete="current-password">
                        <button type="button" class="password-toggle" id="passwordToggle">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>
                
                <div class="form-group">
                    <div class="input-group">
                        <i class="fas fa-globe input-icon"></i>
                        <select id="language" name="language" onchange="changeLanguage(this.value)">
                            <option value="en" <?php echo $lang == 'en' ? 'selected' : ''; ?>>English</option>
                            <option value="tr" <?php echo $lang == 'tr' ? 'selected' : ''; ?>>Türkçe</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-options">
                    <label class="checkbox-label">
                        <input type="checkbox" name="remember_me" id="remember_me">
                        <span class="checkmark"></span>
                        <?php echo $translations['login']['remember_me'] ?? 'Remember Me'; ?>
                    </label>
                    
                    <a href="#" class="forgot-password" onclick="showForgotPassword()">
                        <?php echo $translations['login']['forgot_password'] ?? 'Forgot Password?'; ?>
                    </a>
                </div>
                
                <button type="submit" class="login-button">
                    <i class="fas fa-sign-in-alt"></i>
                    <?php echo $translations['login']['login_button'] ?? 'Login'; ?>
                </button>
            </form>
            
            <div class="login-footer">
                <p><?php echo $translations['footer']['copyright'] ?? '© 2024 Ventixa. All rights reserved.'; ?></p>
            </div>
        </div>
        
        <!-- Şifre sıfırlama modal -->
        <div class="modal" id="forgotPasswordModal">
            <div class="modal-content">
                <div class="modal-header">
                    <h3><?php echo $translations['login']['forgot_password'] ?? 'Forgot Password?'; ?></h3>
                    <button class="close-modal" onclick="closeForgotPassword()">&times;</button>
                </div>
                <div class="modal-body">
                    <p><?php echo $translations['forgot_password']['description'] ?? 'Enter your email address and we\'ll send you a link to reset your password.'; ?></p>
                    <form id="forgotPasswordForm">
                        <div class="form-group">
                            <div class="input-group">
                                <i class="fas fa-envelope input-icon"></i>
                                <input type="email" name="reset_email" placeholder="<?php echo $translations['forgot_password']['email_placeholder'] ?? 'Enter your email'; ?>" required>
                            </div>
                        </div>
                        <button type="submit" class="btn-primary"><?php echo $translations['forgot_password']['send_button'] ?? 'Send Reset Link'; ?></button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <script src="assets/js/login.js"></script>
</body>
</html>
