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
        'title' => 'Add New Department',
        'department_management' => 'Department Management',
        'back_to_dashboard' => 'Back to Dashboard',
        'department_name' => 'Department Name',
        'department_email' => 'Department Email',
        'add_department' => 'Add Department',
        'department_list' => 'Department List',
        'add_success' => 'Department added successfully!',
        'add_error' => 'Error adding department. Please try again.',
        'name_exists' => 'Department name already exists!',
        'email_exists' => 'Department email already exists!',
        'invalid_email' => 'Please enter a valid email address',
        'logout' => 'Logout',
        'required_field' => 'This field is required',
        'lang_en' => 'EN',
        'lang_am' => 'AM',
        'name_placeholder' => 'Enter department name',
        'email_placeholder' => 'Enter department email address',
    ],
    'am' => [
        'title' => 'አዲስ የስራ ክፍል ያክሉ',
        'department_management' => 'የስራ ክፍል አስተዳደር',
        'back_to_dashboard' => 'ወደ ዳሽቦርድ ተመለስ',
        'department_name' => 'የስራ ክፍል ስም',
        'department_email' => 'የስራ ክፍል ኢሜይል',
        'add_department' => 'የስራ ክፍል ያክሉ',
        'department_list' => 'የስራ ክፍል ዝርዝር',
        'add_success' => 'የስራ ክፍል በተሳካ ሁኔታ ተጨምሯል!',
        'add_error' => 'የስራ ክፍል ማክም አልተሳካም። እባክዎ ደግመው ይሞክሩ።',
        'name_exists' => 'የስራ ክፍል ስም አስቀድሞ አለ!',
        'email_exists' => 'የስራ ክፍል ኢሜይል አስቀድሞ አለ!',
        'invalid_email' => 'እባክዎ ትክክለኛ ኢሜይል ያስገቡ',
        'logout' => 'ውጣ',
        'required_field' => 'ይህ ማሞላት ያለበት ቦታ ነው',
        'lang_en' => 'EN',
        'lang_am' => 'AM',
        'name_placeholder' => 'የስራ ክፍል ስም ያስገቡ',
        'email_placeholder' => 'የስራ ክፍል ኢሜይል ያስገቡ',
    ]
][$lang];

$success_message = '';
$error_message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);

    // Validate inputs
    if (empty($name) || empty($email)) {
        $error_message = $text['required_field'];
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = $text['invalid_email'];
    } else {
        // Check if department name already exists
        $stmt = $conn->prepare("SELECT id FROM departments WHERE name = ?");
        $stmt->execute([$name]);
        
        if ($stmt->fetch()) {
            $error_message = $text['name_exists'];
        } else {
            // Check if department email already exists
            $stmt = $conn->prepare("SELECT id FROM departments WHERE email = ?");
            $stmt->execute([$email]);
            
            if ($stmt->fetch()) {
                $error_message = $text['email_exists'];
            } else {
                // Insert new department
                $stmt = $conn->prepare("INSERT INTO departments (name, email) VALUES (?, ?)");
                
                if ($stmt->execute([$name, $email])) {
                    $success_message = $text['add_success'];
                    // Clear form fields
                    $_POST = array();
                } else {
                    $error_message = $text['add_error'];
                }
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
        }

        .sidebar {
            width: 250px;
            background: #0f2a7a;
            color: #fff;
            position: fixed;
            height: 100%;
            transition: all 0.3s ease;
            box-shadow: 3px 0 10px rgba(0,0,0,0.1);
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
        }

        .sidebar ul li a:hover,
        .sidebar ul li a.active {
            background: rgba(255,255,255,0.15);
        }

        .sidebar ul li a i {
            width: 25px;
            font-size: 18px;
        }

        .content {
            margin-left: 250px;
            padding: 20px;
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

        .form-control {
            border: 1.5px solid #d1d5db;
            border-radius: 8px;
            padding: 12px 16px;
            font-size: 14px;
            transition: all 0.2s ease;
        }

        .form-control:focus {
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

        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
            }
            
            .content {
                margin-left: 0;
            }
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="logo">
                <i class="fas fa-building me-2"></i><?= htmlspecialchars($text['department_management']) ?>
            </div>
            <ul>
                <li><a href="adminDashboard.php"><i class="fas fa-tachometer-alt"></i><span>Dashboard</span></a></li>
                <li><a href="addDepartment.php" class="active"><i class="fas fa-plus-circle"></i><span><?= htmlspecialchars($text['add_department']) ?></span></a></li>
                <li><a href="department_list.php"><i class="fas fa-list"></i><span><?= htmlspecialchars($text['department_list']) ?></span></a></li>
                <li><a href="addUser.php"><i class="fas fa-user-plus"></i><span>Add User</span></a></li>
                <li><a href="user_list.php"><i class="fas fa-users"></i><span>User List</span></a></li>
                <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i><span><?= htmlspecialchars($text['logout']) ?></span></a></li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="content">
            <div class="header">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="m-0"><?= htmlspecialchars($text['title']) ?></h4>
                        <p class="text-muted m-0">Add new departments to the system</p>
                    </div>
                    <div>
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

            <!-- Add Department Form -->
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-plus-circle me-2"></i><?= htmlspecialchars($text['add_department']) ?>
                </div>
                <div class="card-body p-4">
                    <form method="POST" action="">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="name" class="form-label">
                                        <i class="fas fa-building me-2"></i><?= htmlspecialchars($text['department_name']) ?> *
                                    </label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="name" 
                                           name="name" 
                                           value="<?= isset($_POST['name']) ? htmlspecialchars($_POST['name']) : '' ?>" 
                                           required 
                                           placeholder="<?= htmlspecialchars($text['name_placeholder']) ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="email" class="form-label">
                                        <i class="fas fa-envelope me-2"></i><?= htmlspecialchars($text['department_email']) ?> *
                                    </label>
                                    <input type="email" 
                                           class="form-control" 
                                           id="email" 
                                           name="email" 
                                           value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>" 
                                           required 
                                           placeholder="<?= htmlspecialchars($text['email_placeholder']) ?>">
                                </div>
                            </div>
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <button type="reset" class="btn btn-outline-secondary me-md-2">
                                <i class="fas fa-redo me-2"></i>Reset
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-plus-circle me-2"></i><?= htmlspecialchars($text['add_department']) ?>
                            </button>
                        </div>
                    </form>
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
        });
    </script>
</body>
</html>