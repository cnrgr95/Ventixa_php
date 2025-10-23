-- Ventixa Çok Dilli Proje Veritabanı
-- MySQL veritabanı oluşturma scripti

CREATE DATABASE IF NOT EXISTS ventixa_multilang CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE ventixa_multilang;

-- Kullanıcı grupları tablosu
CREATE TABLE user_groups (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    permissions JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Kullanıcılar tablosu
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    avatar VARCHAR(255),
    group_id INT NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    email_verified BOOLEAN DEFAULT FALSE,
    last_login TIMESTAMP NULL,
    remember_token VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (group_id) REFERENCES user_groups(id) ON DELETE RESTRICT
);

-- Dil ayarları tablosu
CREATE TABLE user_languages (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    language_code VARCHAR(5) NOT NULL DEFAULT 'en',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Şifre sıfırlama tokenları
CREATE TABLE password_resets (
    id INT PRIMARY KEY AUTO_INCREMENT,
    email VARCHAR(255) NOT NULL,
    token VARCHAR(255) NOT NULL,
    expires_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ip_address VARCHAR(45),
    user_agent TEXT,
    device_info TEXT,
    browser_info TEXT,
    is_used BOOLEAN DEFAULT FALSE,
    used_at TIMESTAMP NULL
);

-- Login geçmişi
CREATE TABLE login_history (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    ip_address VARCHAR(45),
    user_agent TEXT,
    success BOOLEAN NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Varsayılan kullanıcı gruplarını ekle
INSERT INTO user_groups (name, description, permissions) VALUES
('admin', 'Sistem Yöneticisi', '{"all": true}'),
('agency', 'Acente', '{"bookings": true, "tours": true, "customers": true}'),
('guide', 'Rehber', '{"tours": true, "customers": true}'),
('customer', 'Müşteri', '{"bookings": true}');

-- Varsayılan admin kullanıcısı (şifre: admin123)
INSERT INTO users (email, password, first_name, last_name, group_id) VALUES
('admin@ventixa.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin', 'User', 1);

-- Varsayılan acente kullanıcısı (şifre: agency123)
INSERT INTO users (email, password, first_name, last_name, group_id) VALUES
('agency@ventixa.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Agency', 'Manager', 2);

-- Varsayılan rehber kullanıcısı (şifre: guide123)
INSERT INTO users (email, password, first_name, last_name, group_id) VALUES
('guide@ventixa.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Tour', 'Guide', 3);
