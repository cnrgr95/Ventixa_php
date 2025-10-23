<?php
session_start();

// Güvenlik kontrolü
if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit;
}

// Session'ı temizle
session_destroy();

// Remember me cookie'sini temizle
if (isset($_COOKIE['remember_token'])) {
    setcookie('remember_token', '', time() - 3600, '/');
}

// Ana sayfaya yönlendir
header('Location: ../index.php?message=logged_out');
exit;
?>
