<?php
session_start();
require_once 'includes/database.php';

// Admin kontrolü
if (!isset($_SESSION['user_id']) || $_SESSION['user_group'] !== 'admin') {
    header('Location: index.php');
    exit;
}

// Token listesini al
$tokens = $db->fetchAll(
    "SELECT pr.*, u.first_name, u.last_name 
     FROM password_resets pr 
     LEFT JOIN users u ON pr.email = u.email 
     ORDER BY pr.created_at DESC"
);

// İstatistikler
$stats = $db->fetchAll(
    "SELECT 
        COUNT(*) as total_tokens,
        SUM(CASE WHEN is_used = 1 THEN 1 ELSE 0 END) as used_tokens,
        SUM(CASE WHEN expires_at > NOW() AND is_used = 0 THEN 1 ELSE 0 END) as active_tokens,
        SUM(CASE WHEN expires_at < NOW() AND is_used = 0 THEN 1 ELSE 0 END) as expired_tokens,
        COUNT(DISTINCT email) as unique_users
     FROM password_resets"
)[0];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Reset Tokens - Ventixa Admin</title>
    <link rel="stylesheet" href="assets/css/dashboard.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
        }
        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            color: #2c3e50;
        }
        .stat-label {
            color: #7f8c8d;
            font-size: 0.9rem;
        }
        .table-container {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #e1e5e9;
        }
        th {
            background: #f8f9fa;
            font-weight: 600;
            color: #2c3e50;
        }
        .status-badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.8rem;
            font-weight: 500;
        }
        .status-active { background: #d4edda; color: #155724; }
        .status-used { background: #cce5ff; color: #004085; }
        .status-expired { background: #f8d7da; color: #721c24; }
        .token-preview {
            font-family: monospace;
            font-size: 0.8rem;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <nav class="sidebar">
            <div class="sidebar-header">
                <div class="logo">
                    <img src="assets/img/logo/light_logo.svg" alt="Ventixa" class="logo-img">
                </div>
            </div>
            <ul class="sidebar-menu">
                <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li class="active"><a href="admin-tokens.php"><i class="fas fa-key"></i> Reset Tokens</a></li>
                <li><a href="includes/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </nav>
        
        <main class="main-content">
            <header class="main-header">
                <div class="header-left">
                    <h1>Password Reset Tokens</h1>
                    <p>Manage password reset tokens and monitor usage</p>
                </div>
            </header>
            
            <div class="dashboard-content">
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-number"><?php echo $stats['total_tokens']; ?></div>
                        <div class="stat-label">Total Tokens</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number"><?php echo $stats['active_tokens']; ?></div>
                        <div class="stat-label">Active Tokens</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number"><?php echo $stats['used_tokens']; ?></div>
                        <div class="stat-label">Used Tokens</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number"><?php echo $stats['expired_tokens']; ?></div>
                        <div class="stat-label">Expired Tokens</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number"><?php echo $stats['unique_users']; ?></div>
                        <div class="stat-label">Unique Users</div>
                    </div>
                </div>
                
                <div class="table-container">
                    <h3>Token History</h3>
                    <table>
                        <thead>
                            <tr>
                                <th>Email</th>
                                <th>User</th>
                                <th>Token</th>
                                <th>Device</th>
                                <th>Browser</th>
                                <th>IP Address</th>
                                <th>Created</th>
                                <th>Expires</th>
                                <th>Status</th>
                                <th>Used At</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($tokens as $token): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($token['email']); ?></td>
                                <td>
                                    <?php if ($token['first_name']): ?>
                                        <?php echo htmlspecialchars($token['first_name'] . ' ' . $token['last_name']); ?>
                                    <?php else: ?>
                                        <span style="color: #999;">Not Found</span>
                                    <?php endif; ?>
                                </td>
                                <td class="token-preview"><?php echo substr($token['token'], 0, 16) . '...'; ?></td>
                                <td><?php echo htmlspecialchars($token['device_info'] ?? 'Unknown'); ?></td>
                                <td><?php echo htmlspecialchars($token['browser_info'] ?? 'Unknown'); ?></td>
                                <td><?php echo htmlspecialchars($token['ip_address'] ?? 'Unknown'); ?></td>
                                <td><?php echo date('Y-m-d H:i', strtotime($token['created_at'])); ?></td>
                                <td><?php echo date('Y-m-d H:i', strtotime($token['expires_at'])); ?></td>
                                <td>
                                    <?php
                                    $now = time();
                                    $expires = strtotime($token['expires_at']);
                                    
                                    if ($token['is_used']) {
                                        echo '<span class="status-badge status-used">Used</span>';
                                    } elseif ($expires > $now) {
                                        echo '<span class="status-badge status-active">Active</span>';
                                    } else {
                                        echo '<span class="status-badge status-expired">Expired</span>';
                                    }
                                    ?>
                                </td>
                                <td>
                                    <?php 
                                    if ($token['used_at']) {
                                        echo date('Y-m-d H:i', strtotime($token['used_at']));
                                    } else {
                                        echo '-';
                                    }
                                    ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
