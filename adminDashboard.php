<?php
session_start();
include("db.php");

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

$lang = isset($_GET['lang']) ? $_GET['lang'] : 'en';

$text = [
    'en' => [
        'dashboard' => 'Dashboard',
        'welcome' => 'Welcome Back',
        'user_management' => 'User Management',
        'add_user' => 'Add User',
        'logout' => 'Logout',
        'total_users' => 'Total Users',
        'active_users' => 'Active Users',
        'new_users' => 'New Users This Month',
        'pending_requests' => 'Pending Requests',
        'recent_activity' => 'Recent Activity',
        'user_statistics' => 'User Statistics',
        'department_stats' => 'Department Statistics',
        'quick_actions' => 'Quick Actions',
        'view_all' => 'View All',
        'system_overview' => 'System Overview',
        'admin_users' => 'Admin Users',
        'director_users' => 'Director Users',
        'chief_officers' => 'Chief Officers',
        'total_departments' => 'Total Departments'
    ],
    'am' => [
        'dashboard' => 'ዳሽቦርድ',
        'welcome' => 'እንኳን ደህና መጡ',
        'user_management' => 'የተጠቃሚ አስተዳደር',
        'add_user' => 'ተጠቃሚ አክል',
        'logout' => 'ውጣ',
        'total_users' => 'ጠቅላላ ተጠቃሚዎች',
        'active_users' => 'ንቁ ተጠቃሚዎች',
        'new_users' => 'በዚህ ወር አዲስ ተጠቃሚዎች',
        'pending_requests' => 'በጥበቃ ላይ ያሉ ጥያቄዎች',
        'recent_activity' => 'የቅርብ ሁኔታ',
        'user_statistics' => 'የተጠቃሚ ስታቲስቲክስ',
        'department_stats' => 'የወረዳ ስታቲስቲክስ',
        'quick_actions' => 'ፈጣን እርምጃዎች',
        'view_all' => 'ሁሉንም ይመልከቱ',
        'system_overview' => 'የስርዓት አጠቃላይ እይታ',
        'admin_users' => 'አስተዳዳሪ ተጠቃሚዎች',
        'director_users' => 'ዳይሬክተር ተጠቃሚዎች',
        'chief_officers' => 'ሃላፊ ኦፊሰሎች',
        'total_departments' => 'ጠቅላላ ወረዳዎች'
    ]
][$lang];

// Get user statistics
try {
    // Total users
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM users");
    $stmt->execute();
    $total_users = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Active users (assuming all are active for now)
    $active_users = $total_users;

    // New users this month
    $stmt = $conn->prepare("SELECT COUNT(*) as new_users FROM users WHERE MONTH(created_at) = MONTH(CURRENT_DATE()) AND YEAR(created_at) = YEAR(CURRENT_DATE())");
    $stmt->execute();
    $new_users = $stmt->fetch(PDO::FETCH_ASSOC)['new_users'];

    // Users by position
    $stmt = $conn->prepare("SELECT position, COUNT(*) as count FROM users GROUP BY position");
    $stmt->execute();
    $users_by_position = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Total departments
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM departments");
    $stmt->execute();
    $total_departments = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Recent users (last 5)
    $stmt = $conn->prepare("SELECT name, position, created_at FROM users ORDER BY created_at DESC LIMIT 5");
    $stmt->execute();
    $recent_users = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    // Handle error
    $total_users = 0;
    $active_users = 0;
    $new_users = 0;
    $total_departments = 0;
    $users_by_position = [];
    $recent_users = [];
}
?>

<!DOCTYPE html>
<html lang="<?= htmlspecialchars($lang) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($text['dashboard']) ?> - ICS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            background: #f8fafc;
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar Styles */
        .sidebar {
            width: 260px;
            background: linear-gradient(180deg, #1e3a8a 0%, #2563eb 100%);
            color: white;
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            overflow-y: auto;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
        }

        .logo {
            padding: 25px 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            font-size: 18px;
            font-weight: 600;
            display: flex;
            align-items: center;
        }

        .logo i {
            font-size: 20px;
        }

        .sidebar ul {
            list-style: none;
            padding: 20px 0;
        }

        .sidebar ul li {
            margin: 5px 0;
        }

        .sidebar ul li a {
            display: flex;
            align-items: center;
            padding: 12px 20px;
            color: rgba(255, 255, 255, 0.9);
            text-decoration: none;
            transition: all 0.3s ease;
            border-left: 3px solid transparent;
        }

        .sidebar ul li a:hover {
            background: rgba(255, 255, 255, 0.1);
            color: white;
            border-left-color: rgba(255, 255, 255, 0.3);
        }

        .sidebar ul li a.active {
            background: rgba(255, 255, 255, 0.15);
            color: white;
            border-left-color: white;
        }

        .sidebar ul li a i {
            width: 20px;
            margin-right: 12px;
            font-size: 16px;
        }

        .sidebar ul li a span {
            font-size: 14px;
            font-weight: 500;
        }

        /* Main Content */
        .main-content {
            flex: 1;
            margin-left: 260px;
            padding: 20px;
        }

        /* Header */
        .header {
            background: white;
            padding: 20px 30px;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
            margin-bottom: 25px;
            display: flex;
            justify-content: between;
            align-items: center;
        }

        .welcome-section h1 {
            color: #1e293b;
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 5px;
        }

        .welcome-section p {
            color: #64748b;
            font-size: 14px;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .user-avatar {
            width: 45px;
            height: 45px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 16px;
        }

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 20px;
            margin-bottom: 25px;
        }

        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
            border-left: 4px solid #2563eb;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.12);
        }

        .stat-card.blue { border-left-color: #2563eb; }
        .stat-card.green { border-left-color: #10b981; }
        .stat-card.orange { border-left-color: #f59e0b; }
        .stat-card.purple { border-left-color: #8b5cf6; }

        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 15px;
            font-size: 20px;
        }

        .blue .stat-icon { background: #dbeafe; color: #2563eb; }
        .green .stat-icon { background: #dcfce7; color: #10b981; }
        .orange .stat-icon { background: #fef3c7; color: #f59e0b; }
        .purple .stat-icon { background: #f3e8ff; color: #8b5cf6; }

        .stat-number {
            font-size: 32px;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 5px;
        }

        .stat-label {
            color: #64748b;
            font-size: 14px;
            font-weight: 500;
        }

        /* Charts and Tables Grid */
        .content-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 25px;
            margin-bottom: 25px;
        }

        .chart-container, .recent-activity, .quick-actions {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
        }

        .section-header {
            display: flex;
            justify-content: between;
            align-items: center;
            margin-bottom: 20px;
        }

        .section-title {
            font-size: 18px;
            font-weight: 600;
            color: #1e293b;
        }

        .view-all {
            color: #2563eb;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
        }

        .view-all:hover {
            text-decoration: underline;
        }

        /* Recent Activity */
        .activity-list {
            list-style: none;
        }

        .activity-item {
            display: flex;
            align-items: center;
            padding: 12px 0;
            border-bottom: 1px solid #f1f5f9;
        }

        .activity-item:last-child {
            border-bottom: none;
        }

        .activity-icon {
            width: 36px;
            height: 36px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 12px;
            font-size: 14px;
        }

        .activity-content {
            flex: 1;
        }

        .activity-text {
            font-size: 14px;
            color: #1e293b;
            margin-bottom: 2px;
        }

        .activity-time {
            font-size: 12px;
            color: #64748b;
        }

        /* Quick Actions */
        .actions-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }

        .action-btn {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 20px 10px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            text-decoration: none;
            color: #475569;
            transition: all 0.3s ease;
        }

        .action-btn:hover {
            background: #2563eb;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
        }

        .action-icon {
            font-size: 20px;
            margin-bottom: 8px;
        }

        .action-text {
            font-size: 12px;
            font-weight: 500;
            text-align: center;
        }

        /* Responsive */
        @media (max-width: 1024px) {
            .content-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 70px;
            }
            
            .sidebar .logo span,
            .sidebar ul li a span {
                display: none;
            }
            
            .sidebar ul li a {
                justify-content: center;
                padding: 15px;
            }
            
            .sidebar ul li a i {
                margin-right: 0;
                font-size: 18px;
            }
            
            .main-content {
                margin-left: 70px;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 480px) {
            .main-content {
                padding: 15px;
            }
            
            .header {
                padding: 15px 20px;
            }
            
            .welcome-section h1 {
                font-size: 24px;
            }
        }
    </style>
</head>
<body>
    <!-- Fixed Sidebar -->
    <div class="sidebar">
        <div class="logo">
            <i class="fas fa-users me-2"></i><?= htmlspecialchars($text['user_management']) ?>
        </div>
        <ul>
            <li><a href="adminDashboard.php" class="active"><i class="fas fa-tachometer-alt"></i><span><?= htmlspecialchars($text['dashboard']) ?></span></a></li>
            <li><a href="addUser.php"><i class="fas fa-user-plus"></i><span><?= htmlspecialchars($text['add_user']) ?></span></a></li>
            <li><a href="user_list.php"><i class="fas fa-list"></i><span>User List</span></a></li>
            <li><a href="addDepartment.php"><i class="fas fa-building"></i><span>Add Department</span></a></li>
            <li><a href="department_list.php"><i class="fas fa-list"></i><span>Department List</span></a></li>
            <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i><span><?= htmlspecialchars($text['logout']) ?></span></a></li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Header -->
        <div class="header">
            <div class="welcome-section">
                <h1><?= htmlspecialchars($text['welcome']) ?>, <?= htmlspecialchars($_SESSION['name'] ?? 'Admin') ?>!</h1>
                <p><?= htmlspecialchars($text['system_overview']) ?></p>
            </div>
            <div class="user-info">
                <div class="user-avatar">
                    <?= strtoupper(substr($_SESSION['name'] ?? 'A', 0, 1)) ?>
                </div>
                <div>
                    <div style="font-weight: 600; color: #1e293b;"><?= htmlspecialchars($_SESSION['name'] ?? 'Admin') ?></div>
                    <div style="font-size: 12px; color: #64748b;"><?= htmlspecialchars($_SESSION['position'] ?? 'Administrator') ?></div>
                </div>
            </div>
        </div>

        <!-- Stats Grid -->
        <div class="stats-grid">
            <div class="stat-card blue">
                <div class="stat-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-number"><?= $total_users ?></div>
                <div class="stat-label"><?= htmlspecialchars($text['total_users']) ?></div>
            </div>
            
            <div class="stat-card green">
                <div class="stat-icon">
                    <i class="fas fa-user-check"></i>
                </div>
                <div class="stat-number"><?= $active_users ?></div>
                <div class="stat-label"><?= htmlspecialchars($text['active_users']) ?></div>
            </div>
            
            <div class="stat-card orange">
                <div class="stat-icon">
                    <i class="fas fa-user-plus"></i>
                </div>
                <div class="stat-number"><?= $new_users ?></div>
                <div class="stat-label"><?= htmlspecialchars($text['new_users']) ?></div>
            </div>
            
            <div class="stat-card purple">
                <div class="stat-icon">
                    <i class="fas fa-building"></i>
                </div>
                <div class="stat-number"><?= $total_departments ?></div>
                <div class="stat-label"><?= htmlspecialchars($text['total_departments']) ?></div>
            </div>
        </div>

        <!-- Charts and Content -->
        <div class="content-grid">
            <!-- Left Column - Chart -->
            <div class="chart-container">
                <div class="section-header">
                    <h3 class="section-title"><?= htmlspecialchars($text['user_statistics']) ?></h3>
                    <a href="user_list.php" class="view-all"><?= htmlspecialchars($text['view_all']) ?></a>
                </div>
                <canvas id="userChart" height="250"></canvas>
            </div>

            <!-- Right Column - Recent Activity & Quick Actions -->
            <div style="display: flex; flex-direction: column; gap: 25px;">
                <!-- Recent Activity -->
                <div class="recent-activity">
                    <div class="section-header">
                        <h3 class="section-title"><?= htmlspecialchars($text['recent_activity']) ?></h3>
                        <a href="#" class="view-all"><?= htmlspecialchars($text['view_all']) ?></a>
                    </div>
                    <ul class="activity-list">
                        <?php foreach($recent_users as $user): ?>
                        <li class="activity-item">
                            <div class="activity-icon" style="background: #dbeafe; color: #2563eb;">
                                <i class="fas fa-user-plus"></i>
                            </div>
                            <div class="activity-content">
                                <div class="activity-text">
                                    <strong><?= htmlspecialchars($user['name']) ?></strong> added as <?= htmlspecialchars($user['position']) ?>
                                </div>
                                <div class="activity-time">
                                    <?= date('M j, Y', strtotime($user['created_at'])) ?>
                                </div>
                            </div>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <!-- Quick Actions -->
                <div class="quick-actions">
                    <div class="section-header">
                        <h3 class="section-title"><?= htmlspecialchars($text['quick_actions']) ?></h3>
                    </div>
                    <div class="actions-grid">
                        <a href="addUser.php" class="action-btn">
                            <i class="fas fa-user-plus action-icon"></i>
                            <span class="action-text">Add User</span>
                        </a>
                        <a href="user_list.php" class="action-btn">
                            <i class="fas fa-list action-icon"></i>
                            <span class="action-text">View Users</span>
                        </a>
                        <a href="addDepartment.php" class="action-btn">
                            <i class="fas fa-building action-icon"></i>
                            <span class="action-text">Add Department</span>
                        </a>
                        <a href="department_list.php" class="action-btn">
                            <i class="fas fa-list action-icon"></i>
                            <span class="action-text">View Departments</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // User Statistics Chart
        const ctx = document.getElementById('userChart').getContext('2d');
        const userChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Admin', 'Director', 'Chief Officer'],
                datasets: [{
                    label: 'Users by Position',
                    data: [
                        <?= array_sum(array_map(function($item) { return $item['position'] === 'admin' ? $item['count'] : 0; }, $users_by_position)) ?>,
                        <?= array_sum(array_map(function($item) { return $item['position'] === 'director' ? $item['count'] : 0; }, $users_by_position)) ?>,
                        <?= array_sum(array_map(function($item) { return $item['position'] === 'chief officer' ? $item['count'] : 0; }, $users_by_position)) ?>
                    ],
                    backgroundColor: [
                        '#2563eb',
                        '#10b981',
                        '#f59e0b'
                    ],
                    borderColor: [
                        '#1d4ed8',
                        '#059669',
                        '#d97706'
                    ],
                    borderWidth: 1,
                    borderRadius: 6
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: '#f1f5f9'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
    </script>
</body>
</html>