<?php
session_start();
require_once 'database.php';

// CSRF token oluştur
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Dil ayarları
$lang = isset($_GET['lang']) ? $_GET['lang'] : (isset($_SESSION['lang']) ? $_SESSION['lang'] : 'en');
$_SESSION['lang'] = $lang;

// Dil dosyasını yükle
$translations = [];
if (file_exists("../lang/{$lang}.json")) {
    $translations = json_decode(file_get_contents("../lang/{$lang}.json"), true);
}

// Eğer kullanıcı giriş yapmışsa dashboard'a yönlendir
if (isset($_SESSION['user_id'])) {
    header('Location: ../dashboard.php');
    exit;
}

// Login sayfasını göster
include 'login.php';
?>
