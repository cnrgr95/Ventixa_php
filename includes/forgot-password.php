<?php
session_start();
require_once 'database.php';

// Dil ayarları
$lang = isset($_POST['language']) ? $_POST['language'] : (isset($_SESSION['lang']) ? $_SESSION['lang'] : 'en');
$_SESSION['lang'] = $lang;

// Dil dosyasını yükle
$translations = [];
if (file_exists("../lang/{$lang}.json")) {
    $translations = json_decode(file_get_contents("../lang/{$lang}.json"), true);
}

// POST verilerini al
$email = trim($_POST['reset_email'] ?? '');

// Validasyon
if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode([
        'success' => false, 
        'message' => 'Lütfen geçerli bir e-posta adresi girin.'
    ]);
    exit;
}

try {
    // Kullanıcıyı kontrol et
    $user = $db->fetch(
        "SELECT id, email, first_name FROM users WHERE email = ? AND is_active = 1",
        [$email]
    );
    
    if (!$user) {
        // Güvenlik için kullanıcı yoksa da başarılı mesaj göster
        echo json_encode([
            'success' => true, 
            'message' => 'Eğer bu e-posta adresi sistemimizde kayıtlıysa, şifre sıfırlama bağlantısı gönderildi.'
        ]);
        exit;
    }
    
    // Token oluştur
    $token = bin2hex(random_bytes(32));
    $expires_at = date('Y-m-d H:i:s', time() + (24 * 60 * 60)); // 24 saat sonra
    
    // Cihaz ve tarayıcı bilgilerini al
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? '';
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    
    // Cihaz bilgilerini parse et
    $device_info = parseDeviceInfo($user_agent);
    $browser_info = parseBrowserInfo($user_agent);
    
    // Bu kullanıcının aktif token'larını kapat (sadece 1 aktif token olmalı)
    $db->query(
        "UPDATE password_resets SET is_used = TRUE, used_at = NOW() 
         WHERE email = ? AND is_used = FALSE AND expires_at > NOW()",
        [$email]
    );
    
    // Yeni token kaydet
    $db->query(
        "INSERT INTO password_resets (email, token, expires_at, ip_address, user_agent, device_info, browser_info) VALUES (?, ?, ?, ?, ?, ?, ?)",
        [$email, $token, $expires_at, $ip_address, $user_agent, $device_info, $browser_info]
    );
    
    // E-posta gönder
    $reset_link = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']) . "/reset-password.php?token=" . $token;
    
    // E-posta içeriği
    $subject = "Ventixa - Şifre Sıfırlama";
    $message = "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; text-align: center; border-radius: 10px 10px 0 0; }
            .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px; }
            .button { display: inline-block; background: #667eea; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; margin: 20px 0; }
            .footer { text-align: center; margin-top: 20px; font-size: 12px; color: #666; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h2>Ventixa Şifre Sıfırlama</h2>
            </div>
            <div class='content'>
                <p>Merhaba,</p>
                <p>Hesabınız için şifre sıfırlama talebinde bulundunuz. Aşağıdaki bağlantıya tıklayarak yeni şifrenizi belirleyebilirsiniz:</p>
                <p style='text-align: center;'>
                    <a href='{$reset_link}' class='button'>Şifremi Sıfırla</a>
                </p>
                <p>Bu bağlantı 24 saat geçerlidir. Eğer bu talebi siz yapmadıysanız, bu e-postayı görmezden gelebilirsiniz.</p>
                <p>Bağlantı çalışmıyorsa, aşağıdaki adresi tarayıcınıza kopyalayın:</p>
                <p style='word-break: break-all; background: #eee; padding: 10px; border-radius: 5px;'>{$reset_link}</p>
            </div>
            <div class='footer'>
                <p>Bu e-posta otomatik olarak gönderilmiştir. Lütfen yanıtlamayın.</p>
                <p>&copy; 2024 Ventixa. Tüm hakları saklıdır.</p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: noreply@ventixa.com" . "\r\n";
    
    // E-posta gönder
    if (mail($email, $subject, $message, $headers)) {
        error_log("Password reset email sent to {$email}");
    } else {
        error_log("Failed to send password reset email to {$email}");
    }
    
    echo json_encode([
        'success' => true, 
        'message' => $translations['messages']['reset_email_sent'] ?? 'If this email address is registered in our system, a password reset link has been sent.'
    ]);
    
} catch (Exception $e) {
    error_log("Password reset error: " . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'message' => $translations['messages']['error_occurred'] ?? 'An error occurred. Please try again.'
    ]);
}

// Cihaz bilgilerini parse et
function parseDeviceInfo($user_agent) {
    $device = 'Unknown';
    
    if (preg_match('/Mobile|Android|iPhone|iPad|iPod|BlackBerry|Windows Phone/i', $user_agent)) {
        if (preg_match('/iPhone/i', $user_agent)) {
            $device = 'iPhone';
        } elseif (preg_match('/iPad/i', $user_agent)) {
            $device = 'iPad';
        } elseif (preg_match('/Android/i', $user_agent)) {
            $device = 'Android Mobile';
        } elseif (preg_match('/Windows Phone/i', $user_agent)) {
            $device = 'Windows Phone';
        } else {
            $device = 'Mobile Device';
        }
    } elseif (preg_match('/Windows/i', $user_agent)) {
        $device = 'Windows Desktop';
    } elseif (preg_match('/Macintosh/i', $user_agent)) {
        $device = 'Mac Desktop';
    } elseif (preg_match('/Linux/i', $user_agent)) {
        $device = 'Linux Desktop';
    }
    
    return $device;
}

// Tarayıcı bilgilerini parse et
function parseBrowserInfo($user_agent) {
    $browser = 'Unknown';
    
    if (preg_match('/Chrome/i', $user_agent) && !preg_match('/Edge/i', $user_agent)) {
        $browser = 'Chrome';
    } elseif (preg_match('/Firefox/i', $user_agent)) {
        $browser = 'Firefox';
    } elseif (preg_match('/Safari/i', $user_agent) && !preg_match('/Chrome/i', $user_agent)) {
        $browser = 'Safari';
    } elseif (preg_match('/Edge/i', $user_agent)) {
        $browser = 'Edge';
    } elseif (preg_match('/Opera/i', $user_agent)) {
        $browser = 'Opera';
    } elseif (preg_match('/Internet Explorer/i', $user_agent)) {
        $browser = 'Internet Explorer';
    }
    
    return $browser;
}
?>
