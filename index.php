<?php
session_start();

// CSRF token oluştur
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Dil ayarları
$lang = isset($_GET['lang']) ? $_GET['lang'] : (isset($_SESSION['lang']) ? $_SESSION['lang'] : 'en');
$_SESSION['lang'] = $lang;

// Dil dosyasını yükle
$translations = [];
if (file_exists("lang/{$lang}.json")) {
    $translations = json_decode(file_get_contents("lang/{$lang}.json"), true);
}

// Eğer kullanıcı giriş yapmışsa dashboard'a yönlendir
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

// Başarı/hata mesajları
$message = '';
$message_type = '';

if (isset($_GET['success'])) {
    switch ($_GET['success']) {
        case 'password_reset':
            $message = $translations['messages']['password_reset_success'] ?? 'Your password has been successfully reset. You can now login.';
            $message_type = 'success';
            break;
        case 'logged_out':
            $message = $translations['messages']['logged_out'] ?? 'You have been successfully logged out.';
            $message_type = 'info';
            break;
    }
}

if (isset($_GET['error'])) {
    switch ($_GET['error']) {
        case 'invalid_token':
            $message = $translations['messages']['invalid_token'] ?? 'Invalid password reset link.';
            $message_type = 'error';
            break;
        case 'expired_token':
            $message = $translations['messages']['expired_token'] ?? 'Password reset link has expired.';
            $message_type = 'error';
            break;
    }
}

// Login sayfasını göster
include 'includes/login.php';
?>
