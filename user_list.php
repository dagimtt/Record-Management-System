<?php
session_start();
include("db.php");

// Check if user is logged in and is an admin
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || $_SESSION['position'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$lang = isset($_GET['lang']) ? $_GET['lang'] : 'en';

$text = [
    'en' => [
        'title' => 'User Management',
        'user_list' => 'User List',
        'add_user' => 'Add User',
        'back_to_dashboard' => 'Back to Dashboard',
        'name' => 'Name',
        'username' => 'Username',
        'position' => 'Position',
        'department' => 'Department',
        'created_at' => 'Created At',
        'actions' => 'Actions',
        'edit' => 'Edit',
        'delete' => 'Delete',
        'update' => 'Update',
        'cancel' => 'Cancel',
        'admin' => 'Administrator',
        'director' => 'Director',
        'update_success' => 'User updated successfully!',
        'update_error' => 'Error updating user.',
        'delete_success' => 'User deleted successfully!',
        'delete_error' => 'Error deleting user.',
        'delete_confirm' => 'Are you sure you want to delete this user?',
        'edit_user' => 'Edit User',
        'change_password' => 'Change Password',
        'new_password' => 'New Password',
        'confirm_password' => 'Confirm Password',
        'keep_current' => 'Leave blank to keep current password',
        'logout' => 'Logout',
        'no_users' => 'No users found',
        'lang_en' => 'EN',
        'lang_am' => 'AM',
        'search' => 'Search users...',
        'filter' => 'Filter',
        'all_positions' => 'All Positions',
        'all_departments' => 'All Departments',
    ],
    'am' => [
        'title' => 'የተጠቃሚ አስተዳደር',
        'user_list' => 'የተጠቃሚ ዝርዝር',
        'add_user' => 'ተጠቃሚ ያክሉ',
        'back_to_dashboard' => 'ወደ ዳሽቦርድ ተመለስ',
        'name' => 'ስም',
        'username' => 'የተጠቃሚ ስም',
        'position' => 'ስራ',
        'department' => 'የስራ ክፍል',
        'created_at' => 'የተፈጠረበት ቀን',
        'actions' => 'ተግባሮች',
        'edit' => 'አርትዕ',
        'delete' => 'ሰርዝ',
        'update' => 'አዘምን',
        'cancel' => 'ይቅር',
        'admin' => 'አስተዳዳሪ',
        'director' => 'ዳይሬክተር',
        'update_success' => 'ተጠቃሚ በተሳካ ሁኔታ ተስተካክሏል!',
        'update_error' => 'ተጠቃሚ ማዘመን አልተሳካም።',
        'delete_success' => 'ተጠቃሚ በተሳካ ሁኔታ ተሰርዟል!',
        'delete_error' => 'ተጠቃሚ ማስወገድ አልተሳካም።',
        'delete_confirm' => 'ይህን ተጠቃሚ ለማስወገድ እርግጠኛ ነዎት?',
        'edit_user' => 'ተጠቃሚ አርትዕ',
        'change_password' => 'የይለፍ ቃል ይቀይሩ',
        'new_password' => 'አዲስ የይለፍ ቃል',
        'confirm_password' => 'የይለፍ ቃል አረጋግጥ',
        'keep_current' => 'የአሁኑን የይለፍ ቃል ለመጠቀም ባዶ ይተዉ',
        'logout' => 'ውጣ',
        'no_users' => 'ምንም ተጠቃሚ አልተገኘም',
        'lang_en' => 'EN',
        'lang_am' => 'AM',
        'search' => 'ተጠቃሚዎችን ይፈልጉ...',
        'filter' => 'አጣራ',
        'all_positions' => 'ሁሉም ስራ',
        'all_departments' => 'ሁሉም የስራ ክፍል',
    ]
][$lang];

// Define available departments
$departments = [
    'HR' => 'Human Resources',
    'IT' => 'Information Technology',
    'Finance' => 'Finance',
    'Operations' => 'Operations',
    'Marketing' => 'Marketing',
    'Sales' => 'Sales'
];

$success_message = '';
$error_message = '';

// Handle delete action
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    
    // Prevent admin from deleting themselves
    if ($delete_id != $_SESSION['user_id']) {
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        if ($stmt->execute([$delete_id])) {
            $_SESSION['success_message'] = $text['delete_success'];
        } else {
            $_SESSION['error_message'] = $text['delete_error'];
        }
    } else {
        $_SESSION['error_message'] = 'You cannot delete your own account!';
    }
    
    header("Location: user_list.php?lang=" . $lang);
    exit();
}

// Handle update action
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_user'])) {
    $user_id = $_POST['user_id'];
    $name = trim($_POST['name']);
    $username = trim($_POST['username']);
    $position = $_POST['position'];
    $department = $_POST['department'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Check if username already exists (excluding current user)
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
    $stmt->execute([$username, $user_id]);
    
    if ($stmt->fetch()) {
        $error_message = 'Username already exists!';
    } else {
        // If password is provided, update it
        if (!empty($new_password)) {
            if ($new_password === $confirm_password) {
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("UPDATE users SET name = ?, username = ?, position = ?, department = ?, password = ? WHERE id = ?");
                $result = $stmt->execute([$name, $username, $position, $department, $hashed_password, $user_id]);
            } else {
                $error_message = 'Passwords do not match!';
            }
        } else {
            // Update without changing password
            $stmt = $conn->prepare("UPDATE users SET name = ?, username = ?, position = ?, department = ? WHERE id = ?");
            $result = $stmt->execute([$name, $username, $position, $department, $user_id]);
        }
        
        if (isset($result) && $result) {
            $_SESSION['success_message'] = $text['update_success'];
            header("Location: user_list.php?lang=" . $lang);
            exit();
        } elseif (!isset($error_message)) {
            $error_message = $text['update_error'];
        }
    }
}

// Get filter parameters
$search = $_GET['search'] ?? '';
$filter_position = $_GET['position'] ?? '';
$filter_department = $_GET['department'] ?? '';

// Build query with filters
$query = "SELECT * FROM users WHERE 1=1";
$params = [];

if (!empty($search)) {
    $query .= " AND (name LIKE ? OR username LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if (!empty($filter_position)) {
    $query .= " AND position = ?";
    $params[] = $filter_position;
}

if (!empty($filter_department)) {
    $query .= " AND department = ?";
    $params[] = $filter_department;
}

$query .= " ORDER BY created_at DESC";

$stmt = $conn->prepare($query);
$stmt->execute($params);
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get success/error messages from session
if (isset($_SESSION['success_message'])) {
    $success_message = $_SESSION['success_message'];
    unset($_SESSION['success_message']);
}

if (isset($_SESSION['error_message'])) {
    $error_message = $_SESSION['error_message'];
    unset($_SESSION['error_message']);
}
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($lang) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($text['title']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            background: #f8fafc;
            min-height: 100vh;
            overflow-x: hidden;
        }

        .wrapper {
            display: flex;
            min-height: 100vh;
        }

        /* Fixed Sidebar */
        .sidebar {
            width: 250px;
            background: #0f2a7a;
            color: #fff;
            position: fixed;
            left: 0;
            top: 0;
            height: 100vh;
            overflow-y: auto;
            transition: all 0.3s ease;
            box-shadow: 3px 0 10px rgba(0,0,0,0.1);
            z-index: 1000;
        }

        .sidebar .logo {
            padding: 20px;
            font-weight: bold;
            font-size: 20px;
            text-align: center;
            background: rgba(255,255,255,0.1);
            border-bottom: 1px solid rgba(255,255,255,0.2);
        }

        .sidebar ul { 
            list-style: none; 
            margin: 0; 
            padding: 0; 
        }

        .sidebar ul li a {
            display: flex;
            align-items: center;
            color: #fff;
            padding: 14px 20px;
            text-decoration: none;
            transition: background 0.2s;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }

        .sidebar ul li a:hover,
        .sidebar ul li a.active {
            background: rgba(255,255,255,0.15);
        }

        .sidebar ul li a i {
            width: 25px;
            font-size: 18px;
        }

        /* Main Content */
        .main-content {
            margin-left: 250px;
            flex: 1;
            padding: 20px;
            min-height: 100vh;
            background: #f8fafc;
        }

        .header {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
        }

        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        }

        .card-header {
            background: #0f2a7a;
            color: white;
            border-radius: 12px 12px 0 0 !important;
            padding: 20px;
            font-weight: 600;
        }

        .table thead th {
            background: #0f2a7a;
            color: white;
            border: none;
            padding: 15px;
            font-weight: 600;
        }

        .table tbody td {
            padding: 15px;
            vertical-align: middle;
            border-color: #f1f5f9;
        }

        .table-hover tbody tr:hover {
            background-color: rgba(15, 42, 122, 0.05);
        }

        .btn-primary {
            background: #0f2a7a;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            font-weight: 500;
            transition: all 0.2s ease;
        }

        .btn-primary:hover {
            background: #143bb0;
            transform: translateY(-1px);
        }

        .btn-warning {
            background: #f59e0b;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            font-weight: 500;
            transition: all 0.2s ease;
        }

        .btn-warning:hover {
            background: #d97706;
            transform: translateY(-1px);
        }

        .btn-danger {
            background: #dc3545;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            font-weight: 500;
            transition: all 0.2s ease;
        }

        .btn-danger:hover {
            background: #bb2d3b;
            transform: translateY(-1px);
        }

        .btn-outline-primary {
            border: 1.5px solid #0f2a7a;
            color: #0f2a7a;
            padding: 8px 16px;
            border-radius: 6px;
            font-weight: 500;
            transition: all 0.2s ease;
        }

        .btn-outline-primary:hover {
            background: #0f2a7a;
            color: white;
        }

        .badge-admin {
            background: #dc3545;
            color: white;
        }

        .badge-director {
            background: #198754;
            color: white;
        }

        .filter-section {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .form-control, .form-select {
            border: 1.5px solid #d1d5db;
            border-radius: 8px;
            padding: 10px 12px;
            font-size: 14px;
            transition: all 0.2s ease;
        }

        .form-control:focus, .form-select:focus {
            outline: none;
            border-color: #0f2a7a;
            box-shadow: 0 0 0 3px rgba(15, 42, 122, 0.1);
        }

        .alert {
            border-radius: 8px;
            border: none;
            padding: 12px 16px;
        }

        .table-responsive {
            border-radius: 0 0 12px 12px;
            overflow: hidden;
        }

        /* Mobile Responsive */
        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
                margin-bottom: 20px;
            }
            
            .main-content {
                margin-left: 0;
                width: 100%;
            }
            
            .table-responsive {
                font-size: 14px;
            }
            
            .btn-group .btn {
                font-size: 12px;
                padding: 6px 12px;
            }
        }

        @media (max-width: 576px) {
            .header {
                padding: 15px;
            }
            
            .header .d-flex {
                flex-direction: column;
                gap: 10px;
                align-items: flex-start !important;
            }
            
            .filter-section {
                padding: 15px;
            }
            
            .table td, .table th {
                padding: 8px 4px;
                font-size: 12px;
            }
            
            .btn-group .btn {
                font-size: 11px;
                padding: 4px 8px;
            }
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <!-- Fixed Sidebar -->
        <div class="sidebar">
            <div class="logo">
                <i class="fas fa-users me-2"></i><?= htmlspecialchars($text['title']) ?>
            </div>
            <ul>
                <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i><span>Dashboard</span></a></li>
                <li><a href="addUser.php"><i class="fas fa-user-plus"></i><span><?= htmlspecialchars($text['add_user']) ?></span></a></li>
                <li><a href="user_list.php" class="active"><i class="fas fa-list"></i><span><?= htmlspecialchars($text['user_list']) ?></span></a></li>
                <li><a href="addDepartment.php"><i class="fas fa-building"></i><span>Add Department</span></a></li>
                <li><a href="department_list.php"><i class="fas fa-list"></i><span>Department List</span></a></li>
                <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i><span><?= htmlspecialchars($text['logout']) ?></span></a></li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <div class="header">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="m-0"><?= htmlspecialchars($text['user_list']) ?></h4>
                        <p class="text-muted m-0">Manage system users</p>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <a href="?lang=en" class="btn btn-sm btn-outline-primary <?= $lang == 'en' ? 'active' : '' ?>"><?= $text['lang_en'] ?></a>
                        <a href="?lang=am" class="btn btn-sm btn-outline-primary <?= $lang == 'am' ? 'active' : '' ?>"><?= $text['lang_am'] ?></a>
                        <a href="dashboard.php" class="btn btn-outline-primary ms-2">
                            <i class="fas fa-arrow-left me-2"></i><?= htmlspecialchars($text['back_to_dashboard']) ?>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Messages -->
            <?php if (!empty($success_message)): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>
                    <?= htmlspecialchars($success_message) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if (!empty($error_message)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <?= htmlspecialchars($error_message) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- Filter Section -->
            <div class="filter-section">
                <form method="GET" action="">
                    <input type="hidden" name="lang" value="<?= $lang ?>">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-4">
                            <label class="form-label"><?= htmlspecialchars($text['search']) ?></label>
                            <input type="text" 
                                   class="form-control" 
                                   name="search" 
                                   value="<?= htmlspecialchars($search) ?>" 
                                   placeholder="<?= htmlspecialchars($text['search']) ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label"><?= htmlspecialchars($text['position']) ?></label>
                            <select class="form-select" name="position">
                                <option value=""><?= htmlspecialchars($text['all_positions']) ?></option>
                                <option value="admin" <?= $filter_position == 'admin' ? 'selected' : '' ?>><?= htmlspecialchars($text['admin']) ?></option>
                                <option value="director" <?= $filter_position == 'director' ? 'selected' : '' ?>><?= htmlspecialchars($text['director']) ?></option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label"><?= htmlspecialchars($text['department']) ?></label>
                            <select class="form-select" name="department">
                                <option value=""><?= htmlspecialchars($text['all_departments']) ?></option>
                                <?php foreach ($departments as $key => $value): ?>
                                    <option value="<?= $key ?>" <?= $filter_department == $key ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($value) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-filter me-2"></i><?= htmlspecialchars($text['filter']) ?>
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Users Table -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>
                        <i class="fas fa-users me-2"></i><?= htmlspecialchars($text['user_list']) ?>
                        <span class="badge bg-light text-dark ms-2"><?= count($users) ?></span>
                    </span>
                    <a href="addUser.php?lang=<?= $lang ?>" class="btn btn-light btn-sm">
                        <i class="fas fa-user-plus me-2"></i><?= htmlspecialchars($text['add_user']) ?>
                    </a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped mb-0">
                            <thead>
                                <tr>
                                    <th><?= htmlspecialchars($text['name']) ?></th>
                                    <th><?= htmlspecialchars($text['username']) ?></th>
                                    <th><?= htmlspecialchars($text['position']) ?></th>
                                    <th><?= htmlspecialchars($text['department']) ?></th>
                                    <th><?= htmlspecialchars($text['created_at']) ?></th>
                                    <th><?= htmlspecialchars($text['actions']) ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($users) > 0): ?>
                                    <?php foreach ($users as $user): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($user['name']) ?></td>
                                        <td><?= htmlspecialchars($user['username']) ?></td>
                                        <td>
                                            <span class="badge <?= $user['position'] == 'admin' ? 'badge-admin' : 'badge-director' ?>">
                                                <?= $user['position'] == 'admin' ? $text['admin'] : $text['director'] ?>
                                            </span>
                                        </td>
                                        <td><?= htmlspecialchars($user['department']) ?></td>
                                        <td><?= date('M j, Y', strtotime($user['created_at'])) ?></td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <button type="button" 
                                                        class="btn btn-warning btn-sm" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#editModal<?= $user['id'] ?>">
                                                    <i class="fas fa-edit"></i> <?= htmlspecialchars($text['edit']) ?>
                                                </button>
                                                <a href="user_list.php?lang=<?= $lang ?>&delete_id=<?= $user['id'] ?>" 
                                                   class="btn btn-danger btn-sm" 
                                                   onclick="return confirm('<?= htmlspecialchars($text['delete_confirm']) ?>')">
                                                    <i class="fas fa-trash"></i> <?= htmlspecialchars($text['delete']) ?>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>

                                    <!-- Edit Modal -->
                                    <div class="modal fade" id="editModal<?= $user['id'] ?>" tabindex="-1">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content">
                                                <div class="modal-header bg-primary text-white">
                                                    <h5 class="modal-title">
                                                        <i class="fas fa-user-edit me-2"></i><?= htmlspecialchars($text['edit_user']) ?>
                                                    </h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <form method="POST" action="">
                                                    <div class="modal-body">
                                                        <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                                        <input type="hidden" name="update_user" value="1">
                                                        
                                                        <div class="mb-3">
                                                            <label class="form-label"><?= htmlspecialchars($text['name']) ?> *</label>
                                                            <input type="text" 
                                                                   class="form-control" 
                                                                   name="name" 
                                                                   value="<?= htmlspecialchars($user['name']) ?>" 
                                                                   required>
                                                        </div>
                                                        
                                                        <div class="mb-3">
                                                            <label class="form-label"><?= htmlspecialchars($text['username']) ?> *</label>
                                                            <input type="text" 
                                                                   class="form-control" 
                                                                   name="username" 
                                                                   value="<?= htmlspecialchars($user['username']) ?>" 
                                                                   required>
                                                        </div>
                                                        
                                                        <div class="row">
                                                            <div class="col-md-6">
                                                                <div class="mb-3">
                                                                    <label class="form-label"><?= htmlspecialchars($text['position']) ?> *</label>
                                                                    <select class="form-select" name="position" required>
                                                                        <option value="admin" <?= $user['position'] == 'admin' ? 'selected' : '' ?>>
                                                                            <?= htmlspecialchars($text['admin']) ?>
                                                                        </option>
                                                                        <option value="director" <?= $user['position'] == 'director' ? 'selected' : '' ?>>
                                                                            <?= htmlspecialchars($text['director']) ?>
                                                                        </option>
                                                                    </select>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <div class="mb-3">
                                                                    <label class="form-label"><?= htmlspecialchars($text['department']) ?> *</label>
                                                                    <select class="form-select" name="department" required>
                                                                        <?php foreach ($departments as $key => $value): ?>
                                                                            <option value="<?= $key ?>" <?= $user['department'] == $key ? 'selected' : '' ?>>
                                                                                <?= htmlspecialchars($value) ?>
                                                                            </option>
                                                                        <?php endforeach; ?>
                                                                    </select>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        
                                                        <div class="mb-3">
                                                            <label class="form-label text-primary">
                                                                <i class="fas fa-key me-2"></i><?= htmlspecialchars($text['change_password']) ?>
                                                            </label>
                                                            <input type="password" 
                                                                   class="form-control" 
                                                                   name="new_password" 
                                                                   placeholder="<?= htmlspecialchars($text['new_password']) ?>">
                                                            <small class="text-muted"><?= htmlspecialchars($text['keep_current']) ?></small>
                                                        </div>
                                                        
                                                        <div class="mb-3">
                                                            <input type="password" 
                                                                   class="form-control" 
                                                                   name="confirm_password" 
                                                                   placeholder="<?= htmlspecialchars($text['confirm_password']) ?>">
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                                            <?= htmlspecialchars($text['cancel']) ?>
                                                        </button>
                                                        <button type="submit" class="btn btn-primary">
                                                            <i class="fas fa-save me-2"></i><?= htmlspecialchars($text['update']) ?>
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="text-center py-4">
                                            <i class="fas fa-users fa-3x text-muted mb-3 d-block"></i>
                                            <?= htmlspecialchars($text['no_users']) ?>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto-hide alerts after 5 seconds
        document.addEventListener('DOMContentLoaded', function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                setTimeout(() => {
                    if (alert.classList.contains('show')) {
                        const bsAlert = new bootstrap.Alert(alert);
                        bsAlert.close();
                    }
                }, 5000);
            });

            // Enhanced delete confirmation
            const deleteButtons = document.querySelectorAll('a[href*="delete_id"]');
            deleteButtons.forEach(button => {
                button.addEventListener('click', function(e) {
                    if (!confirm('<?= htmlspecialchars($text['delete_confirm']) ?>')) {
                        e.preventDefault();
                    }
                });
            });
        });
    </script>
</body>
</html>