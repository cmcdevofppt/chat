<?php
// index.php - Login page
session_start();

if (isset($_COOKIE['username'])) {
    $_SESSION['username'] = $_COOKIE['username'];
    header('Location: chat.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    if (!empty($username)) {
        $_SESSION['username'] = htmlspecialchars($username);
        setcookie('username', $username, time() + 3600 * 24 * 30); // 30 days
        header('Location: chat.php');
        exit;
    } else {
        $error = "Please enter a username.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>Sign in · Chat</title>
    
    <!-- Bootstrap Icons CDN -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <!-- SF Pro font system (Apple's typography) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;14..32,400;14..32,500;14..32,600;14..32,700&display=swap" rel="stylesheet">
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'SF Pro Text', 'Helvetica Neue', sans-serif;
            background: #f5f5f7;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        /* Main container with subtle depth - matches chat.php */
        .login-container {
            max-width: 480px;
            width: 100%;
            background: #ffffff;
            border-radius: 32px;
            box-shadow: 0 0 0 1px rgba(0, 0, 0, 0.02), 0 20px 40px -12px rgba(0, 0, 0, 0.08);
            animation: fadeScaleIn 0.4s cubic-bezier(0.2, 0.9, 0.4, 1.1);
        }
        
        @keyframes fadeScaleIn {
            from {
                opacity: 0;
                transform: scale(0.96) translateY(10px);
            }
            to {
                opacity: 1;
                transform: scale(1) translateY(0);
            }
        }
        
        /* Inner content */
        .login-content {
            padding: 48px 40px;
        }
        
        /* Header styles matching chat.php welcome text */
        .welcome-text {
            font-size: 34px;
            font-weight: 600;
            letter-spacing: -0.5px;
            background: linear-gradient(135deg, #1d1c1e 0%, #3a3a3e 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 8px;
        }
        
        .subhead {
            font-size: 15px;
            color: #8e8e93;
            margin-bottom: 32px;
        }
        
        /* Form styling */
        .form-group {
            margin-bottom: 24px;
        }
        
        .form-label {
            display: block;
            font-size: 14px;
            font-weight: 500;
            color: #1d1c1e;
            margin-bottom: 8px;
        }
        
        .form-input {
            width: 100%;
            padding: 12px 16px;
            background: #f5f5f7;
            border: 1px solid transparent;
            border-radius: 14px;
            font-family: inherit;
            font-size: 15px;
            font-weight: 400;
            color: #1d1c1e;
            transition: all 0.2s ease;
        }
        
        .form-input:focus {
            outline: none;
            background: #ffffff;
            border-color: #007aff;
            box-shadow: 0 0 0 3px rgba(0, 122, 255, 0.1);
        }
        
        .form-input::placeholder {
            color: #8e8e93;
        }
        
        /* Error message styling - matches chat bubble aesthetics */
        .error-message {
            background: #ffe7e7;
            border-radius: 14px;
            padding: 12px 16px;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 14px;
            color: #ff3b30;
            animation: slideDown 0.3s ease;
        }
        
        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .error-message i {
            font-size: 18px;
        }
        
        /* Submit button - matches send button styling */
        .submit-button {
            width: 100%;
            padding: 14px 24px;
            background: #1d1c1e;
            border: none;
            border-radius: 30px;
            color: #ffffff;
            font-family: inherit;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        
        .submit-button:hover {
            background: #007aff;
            transform: scale(1.02);
            box-shadow: 0 4px 12px rgba(0, 122, 255, 0.3);
        }
        
        .submit-button:active {
            transform: scale(0.98);
        }
        
        /* Decorative footer */
        .footer-note {
            margin-top: 32px;
            text-align: center;
            font-size: 12px;
            color: #8e8e93;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        
        .footer-note i {
            font-size: 12px;
            opacity: 0.6;
        }
        
        /* Responsive */
        @media (max-width: 560px) {
            .login-content {
                padding: 32px 24px;
            }
            
            .welcome-text {
                font-size: 28px;
            }
            
            .subhead {
                font-size: 14px;
            }
        }
        
        /* Optional: decorative gradient border effect */
        .login-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, #007aff, #5856d6, #ff9f0a);
            border-radius: 32px 32px 0 0;
        }
        
        .login-container {
            position: relative;
            overflow: hidden;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-content">
            <!-- Header -->
            <div>
                <h1 class="welcome-text">Welcome back</h1>
                <p class="subhead">Enter your username to join the conversation</p>
            </div>
            
            <!-- Error message -->
            <?php if (isset($error)): ?>
                <div class="error-message">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    <span><?php echo htmlspecialchars($error); ?></span>
                </div>
            <?php endif; ?>
            
            <!-- Login form -->
            <form method="post">
                <div class="form-group">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" 
                           name="username" 
                           id="username"
                           required
                           class="form-input"
                           placeholder="e.g., alex, jordan, sam"
                           autocomplete="off"
                           value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
                </div>
                
                <button type="submit" class="submit-button">
                    <span>Join chat</span>
                    <i class="bi bi-arrow-right"></i>
                </button>
            </form>
            
            <!-- Footer -->
            <div class="footer-note">
                <i class="bi bi-chat-dots"></i>
                <span>Real‑time messaging • Reply to threads</span>
            </div>
        </div>
    </div>
</body>
</html>