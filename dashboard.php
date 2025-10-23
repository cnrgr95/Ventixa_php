<?php
session_start();
require_once 'includes/database.php';

// Güvenlik kontrolü
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

// Kullanıcı bilgilerini al
$user = $db->fetch(
    "SELECT u.*, ug.name as group_name, ug.permissions 
     FROM users u 
     JOIN user_groups ug ON u.group_id = ug.id 
     WHERE u.id = ?",
    [$_SESSION['user_id']]
);

if (!$user) {
    session_destroy();
    header('Location: index.php');
    exit;
}

// Dil ayarları
$lang = isset($_SESSION['lang']) ? $_SESSION['lang'] : 'en';

// Dil dosyasını yükle
$translations = [];
if (file_exists("lang/{$lang}.json")) {
    $translations = json_decode(file_get_contents("lang/{$lang}.json"), true);
}
?>
<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $translations['navigation']['dashboard'] ?? 'Dashboard'; ?> - Ventixa</title>
    <link rel="stylesheet" href="assets/css/dashboard.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <nav class="sidebar">
            <div class="sidebar-header">
                <div class="logo">
                    <img src="assets/img/logo/light_logo.svg" alt="Ventixa" class="logo-img">
                </div>
            </div>
            
            <ul class="sidebar-menu">
                <li class="active">
                    <a href="dashboard.php">
                        <i class="fas fa-tachometer-alt"></i>
                        <span><?php echo $translations['navigation']['dashboard'] ?? 'Dashboard'; ?></span>
                    </a>
                </li>
                
                <?php if ($user['group_name'] === 'admin'): ?>
                <li>
                    <a href="users.php">
                        <i class="fas fa-users"></i>
                        <span><?php echo $translations['navigation']['users'] ?? 'Users'; ?></span>
                    </a>
                </li>
                <?php endif; ?>
                
                <?php if (in_array($user['group_name'], ['admin', 'agency'])): ?>
                <li>
                    <a href="agencies.php">
                        <i class="fas fa-building"></i>
                        <span><?php echo $translations['navigation']['agencies'] ?? 'Agencies'; ?></span>
                    </a>
                </li>
                <?php endif; ?>
                
                <?php if (in_array($user['group_name'], ['admin', 'agency', 'guide'])): ?>
                <li>
                    <a href="guides.php">
                        <i class="fas fa-user-tie"></i>
                        <span><?php echo $translations['navigation']['guides'] ?? 'Guides'; ?></span>
                    </a>
                </li>
                <?php endif; ?>
                
                <li>
                    <a href="tours.php">
                        <i class="fas fa-map-marked-alt"></i>
                        <span><?php echo $translations['navigation']['tours'] ?? 'Tours'; ?></span>
                    </a>
                </li>
                
                <li>
                    <a href="bookings.php">
                        <i class="fas fa-calendar-check"></i>
                        <span><?php echo $translations['navigation']['bookings'] ?? 'Bookings'; ?></span>
                    </a>
                </li>
                
                <li>
                    <a href="settings.php">
                        <i class="fas fa-cog"></i>
                        <span><?php echo $translations['navigation']['settings'] ?? 'Settings'; ?></span>
                    </a>
                </li>
            </ul>
            
            <div class="sidebar-footer">
                <a href="includes/logout.php" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i>
                    <span><?php echo $translations['navigation']['logout'] ?? 'Logout'; ?></span>
                </a>
            </div>
        </nav>
        
        <!-- Main Content -->
        <main class="main-content">
            <header class="main-header">
                <div class="header-left">
                    <h1><?php echo $translations['navigation']['dashboard'] ?? 'Dashboard'; ?></h1>
                    <p><?php echo $translations['messages']['welcome'] ?? 'Welcome'; ?>, <?php echo htmlspecialchars($user['first_name']); ?>!</p>
                </div>
                
                <div class="header-right">
                    <div class="user-info">
                        <div class="user-avatar">
                            <?php if ($user['avatar']): ?>
                                <img src="<?php echo htmlspecialchars($user['avatar']); ?>" alt="Avatar">
                            <?php else: ?>
                                <i class="fas fa-user"></i>
                            <?php endif; ?>
                        </div>
                        <div class="user-details">
                            <span class="user-name"><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></span>
                            <span class="user-role"><?php echo $translations['user_groups'][$user['group_name']] ?? ucfirst($user['group_name']); ?></span>
                        </div>
                    </div>
                    
                    <div class="language-selector">
                        <select onchange="changeLanguage(this.value)">
                            <option value="en" <?php echo $lang == 'en' ? 'selected' : ''; ?>>English</option>
                            <option value="tr" <?php echo $lang == 'tr' ? 'selected' : ''; ?>>Türkçe</option>
                        </select>
                    </div>
                </div>
            </header>
            
            <div class="dashboard-content">
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="stat-info">
                            <h3>1,234</h3>
                            <p>Total Users</p>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                        <div class="stat-info">
                            <h3>567</h3>
                            <p>Active Bookings</p>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-map-marked-alt"></i>
                        </div>
                        <div class="stat-info">
                            <h3>89</h3>
                            <p>Available Tours</p>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-dollar-sign"></i>
                        </div>
                        <div class="stat-info">
                            <h3>$12,345</h3>
                            <p>Total Revenue</p>
                        </div>
                    </div>
                </div>
                
                <div class="content-grid">
                    <div class="content-card">
                        <h3>Recent Activity</h3>
                        <div class="activity-list">
                            <div class="activity-item">
                                <i class="fas fa-user-plus"></i>
                                <div class="activity-content">
                                    <p>New user registered</p>
                                    <span>2 minutes ago</span>
                                </div>
                            </div>
                            <div class="activity-item">
                                <i class="fas fa-calendar-plus"></i>
                                <div class="activity-content">
                                    <p>New booking created</p>
                                    <span>5 minutes ago</span>
                                </div>
                            </div>
                            <div class="activity-item">
                                <i class="fas fa-map-marked-alt"></i>
                                <div class="activity-content">
                                    <p>Tour updated</p>
                                    <span>10 minutes ago</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="content-card">
                        <h3>Quick Actions</h3>
                        <div class="quick-actions">
                            <a href="users.php" class="action-btn">
                                <i class="fas fa-user-plus"></i>
                                <span>Add User</span>
                            </a>
                            <a href="tours.php" class="action-btn">
                                <i class="fas fa-map-marked-alt"></i>
                                <span>Create Tour</span>
                            </a>
                            <a href="bookings.php" class="action-btn">
                                <i class="fas fa-calendar-plus"></i>
                                <span>New Booking</span>
                            </a>
                            <a href="settings.php" class="action-btn">
                                <i class="fas fa-cog"></i>
                                <span>Settings</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
    
    <script>
        // Çevirileri JavaScript'e aktar
        window.translations = <?php echo json_encode($translations); ?>;
    </script>
    <script src="assets/js/dashboard.js"></script>
</body>
</html>
