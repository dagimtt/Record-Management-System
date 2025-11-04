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
        'title' => 'Add New User',
        'add_user' => 'Add User',
        'user_management' => 'User Management',
        'back_to_dashboard' => 'Back to Dashboard',
        'name' => 'Full Name',
        'username' => 'Username',
        'password' => 'Password',
        'confirm_password' => 'Confirm Password',
        'position' => 'Position',
        'department' => 'Department',
        'select_position' => 'Select Position',
        'select_department' => 'Select Department',
        'admin' => 'Administrator',
        'director' => 'Director',
        'add_success' => 'User added successfully!',
        'add_error' => 'Error adding user. Please try again.',
        'username_exists' => 'Username already exists!',
        'password_mismatch' => 'Passwords do not match!',
        'logout' => 'Logout',
        'required_field' => 'This field is required',
        'lang_en' => 'EN',
        'lang_am' => 'AM',
        'no_departments' => 'No departments available. Please add departments first.',
    ],
    'am' => [
        'title' => 'አዲስ ተጠቃሚ ያክሉ',
        'add_user' => 'ተጠቃሚ ያክሉ',
        'user_management' => 'የተጠቃሚ አስተዳደር',
        'back_to_dashboard' => 'ወደ ዳሽቦርድ ተመለስ',
        'name' => 'ሙሉ ስም',
        'username' => 'የተጠቃሚ ስም',
        'password' => 'የይለፍ ቃል',
        'confirm_password' => 'የይለፍ ቃል አረጋግጥ',
        'position' => 'ስራ',
        'department' => 'የስራ ክፍል',
        'select_position' => 'ስራ ይምረጡ',
        'select_department' => 'የስራ ክፍል ይምረጡ',
        'admin' => 'አስተዳዳሪ',
        'director' => 'ዳይሬክተር',
        'add_success' => 'ተጠቃሚ በተሳካ ሁኔታ ተጨምሯል!',
        'add_error' => 'ተጠቃሚ ማክም አልተሳካም። እባክዎ ደግመው ይሞክሩ።',
        'username_exists' => 'የተጠቃሚ ስም አስቀድሞ አለ!',
        'password_mismatch' => 'የይለፍ ቃላት አይዛመዱም!',
        'logout' => 'ውጣ',
        'required_field' => 'ይህ ማሞላት ያለበት ቦታ ነው',
        'lang_en' => 'EN',
        'lang_am' => 'AM',
        'no_departments' => 'ምንም የስራ ክፍል አልተገኘም። እባክዎ መጀመሪያ የስራ ክፍሎችን ያክሉ።',
    ]
][$lang];

$success_message = '';
$error_message = '';

// Fetch departments from database
$departments = [];
try {
    $stmt = $conn->prepare("SELECT name FROM departments ORDER BY name");
    $stmt->execute();
    $departments = $stmt->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
    $error_message = "Error fetching departments: " . $e->getMessage();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $position = $_POST['position'];
    $department = $_POST['department'];

    // Validate inputs
    if (empty($name) || empty($username) || empty($password) || empty($position) || empty($department)) {
        $error_message = $text['required_field'];
    } elseif ($password !== $confirm_password) {
        $error_message = $text['password_mismatch'];
    } elseif (empty($departments)) {
        $error_message = $text['no_departments'];
    } else {
        // Check if username already exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$username]);
        
        if ($stmt->fetch()) {
            $error_message = $text['username_exists'];
        } else {
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Insert new user
            $stmt = $conn->prepare("INSERT INTO users (name, username, password, position, department) VALUES (?, ?, ?, ?, ?)");
            
            if ($stmt->execute([$name, $username, $hashed_password, $position, $department])) {
                $success_message = $text['add_success'];
                // Clear form fields
                $_POST = array();
            } else {
                $error_message = $text['add_error'];
            }
        }
    }
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

        .form-label {
            font-weight: 500;
            color: #374151;
            margin-bottom: 8px;
        }

        .form-control, .form-select {
            border: 1.5px solid #d1d5db;
            border-radius: 8px;
            padding: 12px 16px;
            font-size: 14px;
            transition: all 0.2s ease;
        }

        .form-control:focus, .form-select:focus {
            outline: none;
            border-color: #0f2a7a;
            box-shadow: 0 0 0 3px rgba(15, 42, 122, 0.1);
        }

        .btn-primary {
            background: #0f2a7a;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.2s ease;
        }

        .btn-primary:hover {
            background: #143bb0;
            transform: translateY(-1px);
        }

        .btn-outline-primary {
            border: 1.5px solid #0f2a7a;
            color: #0f2a7a;
            padding: 12px 24px;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.2s ease;
        }

        .btn-outline-primary:hover {
            background: #0f2a7a;
            color: white;
        }

        .alert {
            border-radius: 8px;
            border: none;
            padding: 12px 16px;
        }

        .password-strength {
            height: 4px;
            border-radius: 2px;
            margin-top: 8px;
            transition: all 0.3s ease;
        }

        .strength-weak { background: #dc3545; width: 25%; }
        .strength-fair { background: #fd7e14; width: 50%; }
        .strength-good { background: #ffc107; width: 75%; }
        .strength-strong { background: #198754; width: 100%; }

        .department-warning {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
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
            
            .card-body {
                padding: 15px !important;
            }
            
            .btn {
                padding: 10px 16px !important;
            }
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <!-- Fixed Sidebar -->
        <div class="sidebar">
            <div class="logo">
                <i class="fas fa-users me-2"></i><?= htmlspecialchars($text['user_management']) ?>
            </div>
            <ul>
                <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i><span>Dashboard</span></a></li>
                <li><a href="addUser.php" class="active"><i class="fas fa-user-plus"></i><span><?= htmlspecialchars($text['add_user']) ?></span></a></li>
                <li><a href="user_list.php"><i class="fas fa-list"></i><span>User List</span></a></li>
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
                        <h4 class="m-0"><?= htmlspecialchars($text['title']) ?></h4>
                        <p class="text-muted m-0">Add new users to the system</p>
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

            <!-- Department Warning -->
            <?php if (empty($departments)): ?>
                <div class="department-warning">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-exclamation-triangle text-warning me-3 fa-2x"></i>
                        <div>
                            <h5 class="mb-1"><?= htmlspecialchars($text['no_departments']) ?></h5>
                            <p class="mb-0">You need to add departments before creating users.</p>
                            <a href="addDepartment.php?lang=<?= $lang ?>" class="btn btn-warning btn-sm mt-2">
                                <i class="fas fa-plus-circle me-2"></i>Add Department
                            </a>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Add User Form -->
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-user-plus me-2"></i><?= htmlspecialchars($text['add_user']) ?>
                </div>
                <div class="card-body p-4">
                    <form method="POST" action="">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="name" class="form-label">
                                        <i class="fas fa-user me-2"></i><?= htmlspecialchars($text['name']) ?> *
                                    </label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="name" 
                                           name="name" 
                                           value="<?= isset($_POST['name']) ? htmlspecialchars($_POST['name']) : '' ?>" 
                                           required 
                                           placeholder="Enter full name">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="username" class="form-label">
                                        <i class="fas fa-at me-2"></i><?= htmlspecialchars($text['username']) ?> *
                                    </label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="username" 
                                           name="username" 
                                           value="<?= isset($_POST['username']) ? htmlspecialchars($_POST['username']) : '' ?>" 
                                           required 
                                           placeholder="Enter username">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="password" class="form-label">
                                        <i class="fas fa-lock me-2"></i><?= htmlspecialchars($text['password']) ?> *
                                    </label>
                                    <input type="password" 
                                           class="form-control" 
                                           id="password" 
                                           name="password" 
                                           required 
                                           placeholder="Enter password"
                                           onkeyup="checkPasswordStrength(this.value)">
                                    <div id="password-strength" class="password-strength"></div>
                                    <small class="text-muted">Password must be at least 6 characters long</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="confirm_password" class="form-label">
                                        <i class="fas fa-lock me-2"></i><?= htmlspecialchars($text['confirm_password']) ?> *
                                    </label>
                                    <input type="password" 
                                           class="form-control" 
                                           id="confirm_password" 
                                           name="confirm_password" 
                                           required 
                                           placeholder="Confirm password">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="position" class="form-label">
                                        <i class="fas fa-briefcase me-2"></i><?= htmlspecialchars($text['position']) ?> *
                                    </label>
                                    <select class="form-select" id="position" name="position" required>
                                        <option value=""><?= htmlspecialchars($text['select_position']) ?></option>
                                        <option value="admin" <?= (isset($_POST['position']) && $_POST['position'] == 'admin') ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($text['admin']) ?>
                                        </option>
                                        <option value="director" <?= (isset($_POST['position']) && $_POST['position'] == 'director') ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($text['director']) ?>
                                        </option>
                                        <option value="chief director" <?= (isset($_POST['position']) && $_POST['position'] == 'chief director') ? 'selected' : '' ?>>
                                            Chief Director
                                        </option>
                                        <option value="chief officer" <?= (isset($_POST['position']) && $_POST['position'] == 'chief officer') ? 'selected' : '' ?>>
                                            Chief Officer
                                        </option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="department" class="form-label">
                                        <i class="fas fa-building me-2"></i><?= htmlspecialchars($text['department']) ?> *
                                    </label>
                                    <select class="form-select" id="department" name="department" required <?= empty($departments) ? 'disabled' : '' ?>>
                                        <option value=""><?= htmlspecialchars($text['select_department']) ?></option>
                                        <?php if (!empty($departments)): ?>
                                            <?php foreach ($departments as $department_name): ?>
                                                <option value="<?= htmlspecialchars($department_name) ?>" <?= (isset($_POST['department']) && $_POST['department'] == $department_name) ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($department_name) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </select>
                                    <?php if (empty($departments)): ?>
                                        <div class="form-text text-danger">
                                            <i class="fas fa-exclamation-circle me-1"></i>
                                            No departments available. Please add departments first.
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <button type="reset" class="btn btn-outline-secondary me-md-2">
                                <i class="fas fa-redo me-2"></i>Reset
                            </button>
                            <button type="submit" class="btn btn-primary" <?= empty($departments) ? 'disabled' : '' ?>>
                                <i class="fas fa-user-plus me-2"></i><?= htmlspecialchars($text['add_user']) ?>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function checkPasswordStrength(password) {
            const strengthBar = document.getElementById('password-strength');
            let strength = 0;
            
            if (password.length >= 6) strength++;
            if (password.length >= 8) strength++;
            if (/[A-Z]/.test(password)) strength++;
            if (/[0-9]/.test(password)) strength++;
            if (/[^A-Za-z0-9]/.test(password)) strength++;
            
            // Reset classes
            strengthBar.className = 'password-strength';
            
            // Add appropriate class
            if (password.length === 0) {
                strengthBar.style.width = '0%';
            } else if (strength <= 2) {
                strengthBar.classList.add('strength-weak');
            } else if (strength === 3) {
                strengthBar.classList.add('strength-fair');
            } else if (strength === 4) {
                strengthBar.classList.add('strength-good');
            } else {
                strengthBar.classList.add('strength-strong');
            }
        }

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
        });
    </script>
</body>
</html>