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
    <title>Login | Chat Room</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Custom animation -->
    <style>
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        .animate-fade-up {
            animation: fadeInUp 0.6s ease-out forwards;
        }
    </style>
</head>
<body class="bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 min-h-screen flex items-center justify-center p-4 font-sans antialiased">

    <div class="w-full max-w-md animate-fade-up">
        <!-- Card -->
        <div class="bg-gray-800/50 backdrop-blur-sm rounded-2xl shadow-2xl border border-gray-700 p-8 md:p-10 transition-all duration-300 hover:shadow-purple-500/10">
            
            <!-- Header -->
            <div class="text-center mb-8">
                <h1 class="text-4xl md:text-5xl font-extrabold bg-gradient-to-r from-purple-400 to-pink-400 bg-clip-text text-transparent">
                    Welcome
                </h1>
                <p class="text-gray-400 mt-2">Enter your username to join the chat</p>
            </div>

            <!-- Error message -->
            <?php if (isset($error)): ?>
                <div class="bg-red-500/10 border border-red-500/30 text-red-300 rounded-xl p-3 mb-6 text-sm text-center animate-pulse">
                    ⚠️ <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <!-- Login form -->
            <form method="post" class="space-y-6">
                <div>
                    <label for="username" class="block text-gray-300 font-medium mb-2">Username</label>
                    <input type="text" 
                           name="username" 
                           id="username"
                           required
                           class="w-full px-4 py-3 bg-gray-700/50 border border-gray-600 rounded-xl text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all duration-200"
                           placeholder="e.g., Alex, Jordan, Sam"
                           autocomplete="off">
                </div>

                <button type="submit" 
                        class="w-full py-3 px-4 bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 text-white font-semibold rounded-xl shadow-lg hover:shadow-purple-500/25 transform hover:scale-[1.02] transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 focus:ring-offset-gray-900">
                    Enter Chat →
                </button>
            </form>

            <!-- Optional decorative footer -->
            <div class="mt-8 text-center text-gray-500 text-xs">
                Join the conversation • Be yourself
            </div>
        </div>
    </div>

</body>
</html>