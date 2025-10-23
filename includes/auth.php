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

// CSRF token kontrolü
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

// POST verilerini al
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$remember_me = isset($_POST['remember_me']);

// Validasyon
if (empty($email) || empty($password)) {
    echo json_encode([
        'success' => false, 
        'message' => $translations['login']['invalid_credentials'] ?? 'Invalid email or password'
    ]);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode([
        'success' => false, 
        'message' => $translations['login']['invalid_credentials'] ?? 'Invalid email or password'
    ]);
    exit;
}

try {
    // Kullanıcıyı veritabanından bul
    $user = $db->fetch(
        "SELECT u.*, ug.name as group_name, ug.permissions 
         FROM users u 
         JOIN user_groups ug ON u.group_id = ug.id 
         WHERE u.email = ? AND u.is_active = 1",
        [$email]
    );
    
    if (!$user) {
        // Başarısız login kaydı
        logLoginAttempt($email, false, 'User not found');
        echo json_encode([
            'success' => false, 
            'message' => $translations['login']['invalid_credentials'] ?? 'Invalid email or password'
        ]);
        exit;
    }
    
    // Şifre kontrolü
    if (!password_verify($password, $user['password'])) {
        // Başarısız login kaydı
        logLoginAttempt($user['id'], false, 'Invalid password');
        echo json_encode([
            'success' => false, 
            'message' => $translations['login']['invalid_credentials'] ?? 'Invalid email or password'
        ]);
        exit;
    }
    
    // Başarılı login
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
    $_SESSION['user_group'] = $user['group_name'];
    $_SESSION['user_permissions'] = json_decode($user['permissions'], true);
    $_SESSION['login_time'] = time();
    
    // Son giriş zamanını güncelle
    $db->query(
        "UPDATE users SET last_login = NOW() WHERE id = ?",
        [$user['id']]
    );
    
    // Başarılı login kaydı
    logLoginAttempt($user['id'], true, 'Login successful');
    
    // Beni hatırla seçeneği
    if ($remember_me) {
        $remember_token = bin2hex(random_bytes(32));
        $db->query(
            "UPDATE users SET remember_token = ? WHERE id = ?",
            [$remember_token, $user['id']]
        );
        
        // Cookie ayarla (30 gün)
        setcookie('remember_token', $remember_token, time() + (30 * 24 * 60 * 60), '/', '', true, true);
    }
    
    // Kullanıcı dil tercihini kaydet
    $existing_lang = $db->fetch(
        "SELECT id FROM user_languages WHERE user_id = ?",
        [$user['id']]
    );
    
    if ($existing_lang) {
        $db->query(
            "UPDATE user_languages SET language_code = ? WHERE user_id = ?",
            [$lang, $user['id']]
        );
    } else {
        $db->query(
            "INSERT INTO user_languages (user_id, language_code) VALUES (?, ?)",
            [$user['id'], $lang]
        );
    }
    
    echo json_encode([
        'success' => true, 
        'message' => $translations['login']['login_success'] ?? 'Login successful',
        'redirect' => 'dashboard.php'
    ]);
    
} catch (Exception $e) {
    error_log("Login error: " . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'message' => 'Bir hata oluştu. Lütfen tekrar deneyin.'
    ]);
}

// Login denemelerini kaydet
function logLoginAttempt($user_id_or_email, $success, $reason = '') {
    global $db;
    
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? '';
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    
    // Eğer user_id değilse email ise, user_id'yi bul
    $user_id = is_numeric($user_id_or_email) ? $user_id_or_email : null;
    
    if (!$user_id && !is_numeric($user_id_or_email)) {
        $user = $db->fetch("SELECT id FROM users WHERE email = ?", [$user_id_or_email]);
        $user_id = $user ? $user['id'] : null;
    }
    
    $db->query(
        "INSERT INTO login_history (user_id, ip_address, user_agent, success, created_at) 
         VALUES (?, ?, ?, ?, NOW())",
        [$user_id, $ip_address, $user_agent, $success ? 1 : 0]
    );
}
?>
