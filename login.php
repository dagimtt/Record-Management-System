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
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ? AND (position = 'admin' OR position = 'director' OR position = 'chief officer' OR position = 'officer')");
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
            $_SESSION['user_type'] = 'chief officer';
           $_SESSION['user_id'] = $user['id'];
            
            header("Location: dashboard.php");
            exit();
        }
        else if ($user['position'] === 'officer') {
            $_SESSION['name'] = $user['name'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['position'] = $user['position'];
            $_SESSION['logged_in'] = true;
            $_SESSION['user_type'] = 'admin';
           $_SESSION['user_id'] = $user['id'];

            
            header("Location: new_letter.php");
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
        background: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.6)), url('img/background.png');
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 20px;
        position: relative;
    }

    body::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.4);
        z-index: -1;
    }

    .login-container {
        background: rgba(255, 255, 255, 0.95);
        border-radius: 20px;
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
        width: 500px;
        max-width: 90%;
        overflow: hidden;
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.2);
        /* Remove fixed height to allow content to determine height */
        min-height: 300px;
        display: flex;
        flex-direction: column;
    }

    .login-header {
        background: linear-gradient(135deg, #2563eb, #1d4ed8);
        color: white;
        padding: 20px 20px 15px;
        text-align: center;
        position: relative;
        flex-shrink: 0;
    }

    .logo-container {
        margin-bottom: 10px;
    }

    .logo {
        width: 60px;
        height: 60px;
        margin: 0 auto;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.2);
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        border: 3px solid rgba(255, 255, 255, 0.3);
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    }

    .logo img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .login-header h1 {
        font-weight: 600;
        font-size: 20px;
        margin-bottom: 5px;
        text-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
    }

    .login-header p {
        opacity: 0.9;
        font-size: 13px;
        max-width: 90%;
        margin: 0 auto;
        line-height: 1.3;
    }

    .login-body {
        padding: 20px 30px;
        flex: 1;
        display: flex;
        flex-direction: column;
        overflow: hidden;
    }

    .welcome-text {
        text-align: center;
        margin-bottom: 15px;
        color: #4b5563;
        font-size: 14px;
        flex-shrink: 0;
    }

    .language-switcher {
        display: flex;
        justify-content: center;
        gap: 10px;
        margin-bottom: 15px;
        flex-shrink: 0;
    }

    .lang-btn {
        background: white;
        border: 1.5px solid #d1d5db;
        color: #374151;
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 500;
        text-decoration: none;
        transition: all 0.3s ease;
    }

    .lang-btn.active {
        background: #2563eb;
        border-color: #2563eb;
        color: white;
        box-shadow: 0 2px 5px rgba(37, 99, 235, 0.3);
    }

    .lang-btn:hover {
        border-color: #2563eb;
        transform: translateY(-2px);
    }

    .alert {
        padding: 10px 14px;
        border-radius: 10px;
        margin-bottom: 15px;
        border: 1px solid;
        font-size: 13px;
        background: #fef2f2;
        border-color: #fecaca;
        color: #dc2626;
        display: flex;
        align-items: center;
        animation: shake 0.5s ease-in-out;
        flex-shrink: 0;
    }

    @keyframes shake {
        0%, 100% { transform: translateX(0); }
        25% { transform: translateX(-5px); }
        75% { transform: translateX(5px); }
    }

    .form-group {
        margin-bottom: 15px;
        flex-shrink: 0;
    }

    .form-label {
        font-weight: 500;
        color: #374151;
        margin-bottom: 6px;
        font-size: 13px;
        display: block;
    }

    .form-control {
        width: 100%;
        padding: 10px 14px;
        border: 1.5px solid #d1d5db;
        border-radius: 10px;
        font-size: 14px;
        transition: all 0.3s ease;
        background: white;
    }

    .form-control:focus {
        outline: none;
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        transform: translateY(-2px);
    }

    .form-options {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 15px;
        font-size: 13px;
        flex-shrink: 0;
    }

    .remember-me {
        display: flex;
        align-items: center;
        gap: 6px;
        color: #374151;
    }

    .checkbox {
        width: 14px;
        height: 14px;
        border-radius: 4px;
        border: 1.5px solid #d1d5db;
        cursor: pointer;
        position: relative;
    }

    .checkbox:checked::after {
        content: '✓';
        position: absolute;
        color: #2563eb;
        font-weight: bold;
        top: -2px;
        left: 2px;
        font-size: 12px;
    }

    .forgot-password {
        color: #2563eb;
        text-decoration: none;
        font-weight: 500;
        transition: color 0.2s ease;
        font-size: 13px;
    }

    .forgot-password:hover {
        color: #1d4ed8;
        text-decoration: underline;
    }

    .btn-login {
        width: 100%;
        background: linear-gradient(135deg, #2563eb, #1d4ed8);
        color: white;
        border: none;
        padding: 12px;
        border-radius: 10px;
        font-size: 15px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        box-shadow: 0 4px 10px rgba(37, 99, 235, 0.3);
        flex-shrink: 0;
    }

    .btn-login:hover {
        background: linear-gradient(135deg, #1d4ed8, #1e40af);
        transform: translateY(-2px);
        box-shadow: 0 6px 15px rgba(37, 99, 235, 0.4);
    }

    .btn-login:active {
        transform: translateY(0);
    }

    .login-footer {
        text-align: center;
        margin-top: 15px;
        padding-top: 15px;
        border-top: 1px solid #e5e7eb;
        color: #6b7280;
        font-size: 13px;
        flex-shrink: 0;
    }

    .login-footer a {
        color: #2563eb;
        text-decoration: none;
        font-weight: 500;
        transition: color 0.2s ease;
    }

    .login-footer a:hover {
        color: #1d4ed8;
        text-decoration: underline;
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
        transition: color 0.2s ease;
        font-size: 14px;
    }

    .form-control:focus + i {
        color: #2563eb;
    }

    @media (max-width: 768px) {
        .login-container {
            width: 95%;
            min-height: 450px;
        }

        .login-body {
            padding: 15px 20px;
        }

        .login-header {
            padding: 15px 15px 10px;
        }

        .logo {
            width: 50px;
            height: 50px;
        }

        .login-header h1 {
            font-size: 18px;
        }

        .login-header p {
            font-size: 12px;
        }
    }

    @media (max-width: 480px) {
        body {
            padding: 10px;
        }

        .login-container {
            border-radius: 15px;
            min-height: 420px;
        }

        .login-header {
            padding: 12px 12px 8px;
        }

        .login-body {
            padding: 12px 15px;
        }

        .logo {
            width: 45px;
            height: 45px;
        }

        .login-header h1 {
            font-size: 16px;
        }
        
        .form-control {
            padding: 8px 12px;
            font-size: 13px;
        }
        
        .btn-login {
            padding: 10px;
            font-size: 14px;
        }
    }
</style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <div class="logo-container">
                <div class="logo">
                    <img src="img/background.png" alt="Company Logo">
                </div>
                
            </div>
            <h1><?= htmlspecialchars($text['welcome_back']) ?></h1>
            <p><?= htmlspecialchars($text['subtitle']) ?></p>
        </div>

        <div class="login-body">
            <div class="welcome-text">
                <i class="fas fa-user-shield me-2"></i>Secure Login Portal
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
                   <a href="index.html">Home</a>
                </p>
            </div>
               <div class="language-switcher">
                <a href="?lang=en" class="lang-btn <?= $lang == 'en' ? 'active' : '' ?>"><?= $text['lang_en'] ?></a>
                <a href="?lang=am" class="lang-btn <?= $lang == 'am' ? 'active' : '' ?>"><?= $text['lang_am'] ?></a>
            </div>
        </div>
    </div>

    <script>
        // Enhanced form functionality
        document.addEventListener('DOMContentLoaded', function() {
            const inputs = document.querySelectorAll('.form-control');
            const checkboxes = document.querySelectorAll('.checkbox');
            
            // Input focus effects
            inputs.forEach(input => {
                input.addEventListener('focus', function() {
                    this.style.borderColor = '#2563eb';
                    this.parentElement.querySelector('i').style.color = '#2563eb';
                });
                
                input.addEventListener('blur', function() {
                    if (!this.value) {
                        this.style.borderColor = '#d1d5db';
                        this.parentElement.querySelector('i').style.color = '#9ca3af';
                    }
                });
            });

            // Checkbox styling
            checkboxes.forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    if (this.checked) {
                        this.style.backgroundColor = '#2563eb';
                        this.style.borderColor = '#2563eb';
                    } else {
                        this.style.backgroundColor = '';
                        this.style.borderColor = '#d1d5db';
                    }
                });
            });

            // Auto-focus on username field
            document.getElementById('username').focus();
            
            // Add subtle animation to login container
            const loginContainer = document.querySelector('.login-container');
            loginContainer.style.opacity = '0';
            loginContainer.style.transform = 'translateY(20px)';
            
            setTimeout(() => {
                loginContainer.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
                loginContainer.style.opacity = '1';
                loginContainer.style.transform = 'translateY(0)';
            }, 100);
        });
    </script>
</body>
</html>