<?php
session_start();
include("db.php");

$lang = isset($_GET['lang']) ? $_GET['lang'] : 'en';

$text = [
    'en' => [
        'login' => 'Login',
        'welcome_back' => 'Welcome Back',
        'subtitle' => 'Enter your credentials to access your account',
        'username' => 'Username',
        'password' => 'Password',
        'remember_me' => 'Remember me',
        'forgot_password' => 'Forgot password?',
        'sign_in' => 'Sign In',
        'dont_have_account' => "Don't have an account?",
        'contact_admin' => 'Contact admin',
        'lang_en' => 'EN',
        'lang_am' => 'AM',
        'error' => 'Invalid username or password',
    ],
    'am' => [
        'login' => 'ግባ',
        'welcome_back' => 'እንኳን ደህና መጡ',
        'subtitle' => 'ወደ መለያዎ ለመግባት የይለፍ ቃልዎን ያስገቡ',
        'username' => 'የተጠቃሚ ስም',
        'password' => 'የይለፍ ቃል',
        'remember_me' => 'አስታውሰኝ',
        'forgot_password' => 'የይለፍ ቃል ረሳኽ?',
        'sign_in' => 'ግባ',
        'dont_have_account' => "መለያ የሎትም?",
        'contact_admin' => 'አስተዳዳሪን ያግኙ',
        'lang_en' => 'EN',
        'lang_am' => 'AM',
        'error' => 'የተሳሳተ የተጠቃሚ ስም ወይም የይለፍ ቃል',
    ]
][$lang];

$error = '';

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    // Validate credentials for both admin and director
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ? AND (position = 'admin' OR position = 'director' OR position = 'chief officer')");
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user && password_verify($password, $user['password'])) {
        // Set session variables based on user position
        if ($user['position'] === 'director') {
            $_SESSION['director_name'] = $user['name'];
            $_SESSION['director_username'] = $user['username'];
            $_SESSION['director_email'] = $user['email'];
            $_SESSION['director_position'] = $user['position'];
            $_SESSION['director_department'] = $user['department'];
            $_SESSION['logged_in'] = true;
            $_SESSION['user_type'] = 'director';
            
            header("Location: director_panel.php");
            exit();
        } else if ($user['position'] === 'admin') {
            $_SESSION['name'] = $user['name'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['position'] = $user['position'];
            $_SESSION['logged_in'] = true;
            $_SESSION['user_type'] = 'admin';
            
            header("Location: addUser.php");
            exit();
        }
        else if ($user['position'] === 'chief officer') {
            $_SESSION['name'] = $user['name'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['position'] = $user['position'];
            $_SESSION['logged_in'] = true;
            $_SESSION['user_type'] = 'admin';
            
            header("Location: dashboard.php");
            exit();
        }
    } else {
        $error = $text['error'];
    }
}
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($lang) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($text['login']) ?> - Director Portal</title>
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
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .login-container {
            background: white;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            width: 100%;
            max-width: 400px;
            overflow: hidden;
        }

        .login-header {
            background: #2563eb;
            color: white;
            padding: 30px;
            text-align: center;
        }

        .logo {
            width: 60px;
            height: 60px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            font-size: 24px;
        }

        .login-header h1 {
            font-weight: 600;
            font-size: 24px;
            margin-bottom: 5px;
        }

        .login-header p {
            opacity: 0.9;
            font-size: 14px;
        }

        .login-body {
            padding: 30px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            font-weight: 500;
            color: #374151;
            margin-bottom: 8px;
            font-size: 14px;
            display: block;
        }

        .form-control {
            width: 100%;
            padding: 12px 16px;
            border: 1.5px solid #d1d5db;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.2s ease;
            background: white;
        }

        .form-control:focus {
            outline: none;
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        .form-options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .remember-me {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #374151;
        }

        .checkbox {
            width: 16px;
            height: 16px;
            border-radius: 4px;
            border: 1.5px solid #d1d5db;
            cursor: pointer;
        }

        .forgot-password {
            color: #2563eb;
            text-decoration: none;
            font-weight: 500;
        }

        .forgot-password:hover {
            color: #1d4ed8;
        }

        .btn-login {
            width: 100%;
            background: #2563eb;
            color: white;
            border: none;
            padding: 12px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .btn-login:hover {
            background: #1d4ed8;
            transform: translateY(-1px);
        }

        .login-footer {
            text-align: center;
            margin-top: 25px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            color: #6b7280;
            font-size: 14px;
        }

        .login-footer a {
            color: #2563eb;
            text-decoration: none;
            font-weight: 500;
        }

        .language-switcher {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-bottom: 20px;
        }

        .lang-btn {
            background: white;
            border: 1.5px solid #d1d5db;
            color: #374151;
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.2s ease;
        }

        .lang-btn.active {
            background: #2563eb;
            border-color: #2563eb;
            color: white;
        }

        .lang-btn:hover {
            border-color: #2563eb;
        }

        .alert {
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            border: 1px solid;
            font-size: 14px;
            background: #fef2f2;
            border-color: #fecaca;
            color: #dc2626;
        }

        .input-icon {
            position: relative;
        }

        .input-icon i {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #9ca3af;
        }

        @media (max-width: 480px) {
            .login-container {
                border-radius: 12px;
            }

            .login-header {
                padding: 25px 20px;
            }

            .login-body {
                padding: 25px 20px;
            }

            .logo {
                width: 50px;
                height: 50px;
                font-size: 20px;
            }

            .login-header h1 {
                font-size: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <div class="logo">
                <i class="fas fa-envelope"></i>
            </div>
            <h1><?= htmlspecialchars($text['welcome_back']) ?></h1>
            <p><?= htmlspecialchars($text['subtitle']) ?></p>
        </div>

        <div class="login-body">
            <div class="language-switcher">
                <a href="?lang=en" class="lang-btn <?= $lang == 'en' ? 'active' : '' ?>"><?= $text['lang_en'] ?></a>
                <a href="?lang=am" class="lang-btn <?= $lang == 'am' ? 'active' : '' ?>"><?= $text['lang_am'] ?></a>
            </div>

            <?php if (!empty($error)): ?>
                <div class="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label class="form-label" for="username">
                        <i class="fas fa-user me-2"></i><?= htmlspecialchars($text['username']) ?>
                    </label>
                    <div class="input-icon">
                        <input type="text" 
                               class="form-control" 
                               id="username" 
                               name="username" 
                               placeholder="Enter your username" 
                               required
                               value="<?= isset($_POST['username']) ? htmlspecialchars($_POST['username']) : '' ?>">
                        <i class="fas fa-user"></i>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="password">
                        <i class="fas fa-lock me-2"></i><?= htmlspecialchars($text['password']) ?>
                    </label>
                    <div class="input-icon">
                        <input type="password" 
                               class="form-control" 
                               id="password" 
                               name="password" 
                               placeholder="••••••••" 
                               required>
                        <i class="fas fa-key"></i>
                    </div>
                </div>

                <div class="form-options">
                    <label class="remember-me">
                        <input type="checkbox" class="checkbox" name="remember">
                        <?= htmlspecialchars($text['remember_me']) ?>
                    </label>
                    <a href="#" class="forgot-password">
                        <?= htmlspecialchars($text['forgot_password']) ?>
                    </a>
                </div>

                <button type="submit" class="btn-login">
                    <i class="fas fa-sign-in-alt me-2"></i>
                    <?= htmlspecialchars($text['sign_in']) ?>
                </button>
            </form>

            <div class="login-footer">
                <p><?= htmlspecialchars($text['dont_have_account']) ?> 
                   <a href="#"><?= htmlspecialchars($text['contact_admin']) ?></a>
                </p>
            </div>
        </div>
    </div>

    <script>
        // Simple form enhancement
        document.addEventListener('DOMContentLoaded', function() {
            const inputs = document.querySelectorAll('.form-control');
            
            inputs.forEach(input => {
                input.addEventListener('focus', function() {
                    this.style.borderColor = '#2563eb';
                });
                
                input.addEventListener('blur', function() {
                    if (!this.value) {
                        this.style.borderColor = '#d1d5db';
                    }
                });
            });

            // Auto-focus on username field
            document.getElementById('username').focus();
        });
    </script>
</body>
</html>