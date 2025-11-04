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
        'title' => 'Department Management',
        'department_list' => 'Department List',
        'add_department' => 'Add Department',
        'back_to_dashboard' => 'Back to Dashboard',
        'department_name' => 'Department Name',
        'department_email' => 'Department Email',
        'created_at' => 'Created At',
        'actions' => 'Actions',
        'edit' => 'Edit',
        'delete' => 'Delete',
        'update' => 'Update',
        'cancel' => 'Cancel',
        'update_success' => 'Department updated successfully!',
        'update_error' => 'Error updating department.',
        'delete_success' => 'Department deleted successfully!',
        'delete_error' => 'Error deleting department.',
        'delete_confirm' => 'Are you sure you want to delete this department? This action cannot be undone.',
        'edit_department' => 'Edit Department',
        'logout' => 'Logout',
        'no_departments' => 'No departments found',
        'lang_en' => 'EN',
        'lang_am' => 'AM',
        'search' => 'Search departments...',
    ],
    'am' => [
        'title' => 'የስራ ክፍል አስተዳደር',
        'department_list' => 'የስራ ክፍል ዝርዝር',
        'add_department' => 'የስራ ክፍል ያክሉ',
        'back_to_dashboard' => 'ወደ ዳሽቦርድ ተመለስ',
        'department_name' => 'የስራ ክፍል ስም',
        'department_email' => 'የስራ ክፍል ኢሜይል',
        'created_at' => 'የተፈጠረበት ቀን',
        'actions' => 'ተግባሮች',
        'edit' => 'አርትዕ',
        'delete' => 'ሰርዝ',
        'update' => 'አዘምን',
        'cancel' => 'ይቅር',
        'update_success' => 'የስራ ክፍል በተሳካ ሁኔታ ተስተካክሏል!',
        'update_error' => 'የስራ ክፍል ማዘመን አልተሳካም።',
        'delete_success' => 'የስራ ክፍል በተሳካ ሁኔታ ተሰርዟል!',
        'delete_error' => 'የስራ ክፍል ማስወገድ አልተሳካም።',
        'delete_confirm' => 'ይህን የስራ ክፍል ለማስወገድ እርግጠኛ ነዎት? ይህ ተግባር ሊመለስ አይችልም።',
        'edit_department' => 'የስራ ክፍል አርትዕ',
        'logout' => 'ውጣ',
        'no_departments' => 'ምንም የስራ ክፍል አልተገኘም',
        'lang_en' => 'EN',
        'lang_am' => 'AM',
        'search' => 'የስራ ክፍሎችን ይፈልጉ...',
    ]
][$lang];

$success_message = '';
$error_message = '';

// Handle delete action
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    
    // Check if department is being used by any user
    $stmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE department = (SELECT name FROM departments WHERE id = ?)");
    $stmt->execute([$delete_id]);
    $user_count = $stmt->fetchColumn();
    
    if ($user_count > 0) {
        $_SESSION['error_message'] = 'Cannot delete department. There are users assigned to this department.';
    } else {
        $stmt = $conn->prepare("DELETE FROM departments WHERE id = ?");
        if ($stmt->execute([$delete_id])) {
            $_SESSION['success_message'] = $text['delete_success'];
        } else {
            $_SESSION['error_message'] = $text['delete_error'];
        }
    }
    
    header("Location: department_list.php?lang=" . $lang);
    exit();
}

// Handle update action
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_department'])) {
    $department_id = $_POST['department_id'];
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);

    // Check if department name already exists (excluding current department)
    $stmt = $conn->prepare("SELECT id FROM departments WHERE name = ? AND id != ?");
    $stmt->execute([$name, $department_id]);
    
    if ($stmt->fetch()) {
        $error_message = 'Department name already exists!';
    } else {
        // Check if department email already exists (excluding current department)
        $stmt = $conn->prepare("SELECT id FROM departments WHERE email = ? AND id != ?");
        $stmt->execute([$email, $department_id]);
        
        if ($stmt->fetch()) {
            $error_message = 'Department email already exists!';
        } else {
            // Update department
            $stmt = $conn->prepare("UPDATE departments SET name = ?, email = ? WHERE id = ?");
            
            if ($stmt->execute([$name, $email, $department_id])) {
                $_SESSION['success_message'] = $text['update_success'];
                header("Location: department_list.php?lang=" . $lang);
                exit();
            } else {
                $error_message = $text['update_error'];
            }
        }
    }
}

// Get search parameter
$search = $_GET['search'] ?? '';

// Build query with search filter
if (!empty($search)) {
    $stmt = $conn->prepare("SELECT * FROM departments WHERE name LIKE ? OR email LIKE ? ORDER BY created_at DESC");
    $stmt->execute(["%$search%", "%$search%"]);
} else {
    $stmt = $conn->prepare("SELECT * FROM departments ORDER BY created_at DESC");
    $stmt->execute();
}

$departments = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
        }

        .wrapper {
            display: flex;
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

        .content {
            margin-left: 250px;
            flex-grow: 1;
            padding: 20px;
            width: calc(100% - 250px);
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
            margin-bottom: 20px;
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
            text-align: left;
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

        .alert {
            border-radius: 8px;
            border: none;
            padding: 12px 16px;
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

        .table-responsive {
            border-radius: 0 0 12px 12px;
            overflow: hidden;
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
            }
            
            .content {
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
            
            .header .btn-group {
                align-self: flex-end;
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
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="logo">
                <i class="fas fa-building me-2"></i><?= htmlspecialchars($text['title']) ?>
            </div>
            <ul>
                <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i><span>Dashboard</span></a></li>
                <li><a href="addDepartment.php"><i class="fas fa-plus-circle"></i><span><?= htmlspecialchars($text['add_department']) ?></span></a></li>
                <li><a href="department_list.php" class="active"><i class="fas fa-list"></i><span><?= htmlspecialchars($text['department_list']) ?></span></a></li>
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
                        <h4 class="m-0"><?= htmlspecialchars($text['department_list']) ?></h4>
                        <p class="text-muted m-0">Manage system departments</p>
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

            <!-- Search Section -->
            <div class="filter-section">
                <form method="GET" action="">
                    <input type="hidden" name="lang" value="<?= $lang ?>">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-8">
                            <label class="form-label"><?= htmlspecialchars($text['search']) ?></label>
                            <input type="text" 
                                   class="form-control" 
                                   name="search" 
                                   value="<?= htmlspecialchars($search) ?>" 
                                   placeholder="<?= htmlspecialchars($text['search']) ?>">
                        </div>
                        <div class="col-md-4">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-search me-2"></i>Search
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Departments Table -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>
                        <i class="fas fa-list me-2"></i><?= htmlspecialchars($text['department_list']) ?>
                        <span class="badge bg-light text-dark ms-2"><?= count($departments) ?></span>
                    </span>
                    <a href="addDepartment.php?lang=<?= $lang ?>" class="btn btn-light btn-sm">
                        <i class="fas fa-plus-circle me-2"></i><?= htmlspecialchars($text['add_department']) ?>
                    </a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped mb-0">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th><?= htmlspecialchars($text['department_name']) ?></th>
                                    <th><?= htmlspecialchars($text['department_email']) ?></th>
                                    <th><?= htmlspecialchars($text['created_at']) ?></th>
                                    <th><?= htmlspecialchars($text['actions']) ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($departments) > 0): ?>
                                    <?php foreach ($departments as $index => $department): ?>
                                    <tr>
                                        <td><?= $index + 1 ?></td>
                                        <td><?= htmlspecialchars($department['name']) ?></td>
                                        <td><?= htmlspecialchars($department['email']) ?></td>
                                        <td><?= date('M j, Y', strtotime($department['created_at'])) ?></td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <button type="button" 
                                                        class="btn btn-warning btn-sm" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#editModal<?= $department['id'] ?>">
                                                    <i class="fas fa-edit"></i> <?= htmlspecialchars($text['edit']) ?>
                                                </button>
                                                <a href="department_list.php?lang=<?= $lang ?>&delete_id=<?= $department['id'] ?>" 
                                                   class="btn btn-danger btn-sm" 
                                                   onclick="return confirm('<?= htmlspecialchars($text['delete_confirm']) ?>')">
                                                    <i class="fas fa-trash"></i> <?= htmlspecialchars($text['delete']) ?>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>

                                    <!-- Edit Modal -->
                                    <div class="modal fade" id="editModal<?= $department['id'] ?>" tabindex="-1">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content">
                                                <div class="modal-header bg-primary text-white">
                                                    <h5 class="modal-title">
                                                        <i class="fas fa-edit me-2"></i><?= htmlspecialchars($text['edit_department']) ?>
                                                    </h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <form method="POST" action="">
                                                    <div class="modal-body">
                                                        <input type="hidden" name="department_id" value="<?= $department['id'] ?>">
                                                        <input type="hidden" name="update_department" value="1">
                                                        
                                                        <div class="mb-3">
                                                            <label class="form-label"><?= htmlspecialchars($text['department_name']) ?> *</label>
                                                            <input type="text" 
                                                                   class="form-control" 
                                                                   name="name" 
                                                                   value="<?= htmlspecialchars($department['name']) ?>" 
                                                                   required>
                                                        </div>
                                                        
                                                        <div class="mb-3">
                                                            <label class="form-label"><?= htmlspecialchars($text['department_email']) ?> *</label>
                                                            <input type="email" 
                                                                   class="form-control" 
                                                                   name="email" 
                                                                   value="<?= htmlspecialchars($department['email']) ?>" 
                                                                   required>
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
                                        <td colspan="5" class="text-center py-4">
                                            <i class="fas fa-building fa-3x text-muted mb-3 d-block"></i>
                                            <?= htmlspecialchars($text['no_departments']) ?>
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
        });
    </script>
</body>
</html>