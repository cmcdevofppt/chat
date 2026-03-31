<?php
// chat.php - Chat page
session_start();
if (!isset($_SESSION['username'])) {
    header('Location: index.php');
    exit;
}

require 'db.php';

$username = $_SESSION['username'];
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $message = trim($_POST['message']);
    if (!empty($message)) {
        $stmt = $pdo->prepare("INSERT INTO messages (username, message) VALUES (?, ?)");
        $stmt->execute([$username, $message]);
    }
}

// Fetch messages
$stmt = $pdo->query("SELECT username, message, created_at FROM messages ORDER BY created_at ASC");
$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>Chat Room</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Custom animations -->
    <style>
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .message-animate {
            animation: fadeIn 0.3s ease-out forwards;
        }
        /* Custom scrollbar for dark theme */
        .messages-container::-webkit-scrollbar {
            width: 6px;
        }
        .messages-container::-webkit-scrollbar-track {
            background: #1f2937;
            border-radius: 10px;
        }
        .messages-container::-webkit-scrollbar-thumb {
            background: #4b5563;
            border-radius: 10px;
        }
        .messages-container::-webkit-scrollbar-thumb:hover {
            background: #6b7280;
        }
    </style>
</head>
<body class="bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 min-h-screen font-sans antialiased">

    <div class="flex flex-col h-screen max-w-6xl mx-auto p-4">
        <!-- Header -->
        <div class="flex flex-wrap items-center justify-between gap-3 py-4 border-b border-gray-700">
            <div>
                <h1 class="text-2xl md:text-3xl font-bold bg-gradient-to-r from-purple-400 to-pink-400 bg-clip-text text-transparent">
                    💬 Chat Room
                </h1>
                <p class="text-gray-400 text-sm mt-1">
                    Welcome, <span class="font-semibold text-purple-300"><?php echo htmlspecialchars($username); ?></span>
                </p>
            </div>
            <a 
               class="px-4 py-2 bg-gray-700 hover:bg-gray-600 text-gray-200 rounded-lg transition-colors duration-200 text-sm font-medium shadow-md hover:shadow-lg">
                Excellence dev 205 
            </a>
        </div>

        <!-- Messages container (scrollable) -->
        <div id="messages" class="messages-container flex-1 overflow-y-auto my-4 space-y-3 pr-2">
            <?php if (empty($messages)): ?>
                <div class="text-center text-gray-500 py-10 animate-pulse">
                    ✨ No messages yet. Be the first to say something!
                </div>
            <?php else: ?>
                <?php foreach ($messages as $msg): ?>
                    <?php
                        $isCurrentUser = ($msg['username'] === $username);
                        $msgTime = date('H:i', strtotime($msg['created_at']));
                    ?>
                    <div class="flex <?php echo $isCurrentUser ? 'justify-end' : 'justify-start'; ?> message-animate">
                        <div class="max-w-[85%] md:max-w-[70%] lg:max-w-[60%]">
                            <?php if (!$isCurrentUser): ?>
                                <div class="text-xs text-gray-400 mb-1 ml-2"><?php echo htmlspecialchars($msg['username']); ?></div>
                            <?php endif; ?>
                            <div class="<?php echo $isCurrentUser 
                                ? 'bg-gradient-to-r from-purple-600 to-pink-600 text-white rounded-t-2xl rounded-bl-2xl' 
                                : 'bg-gray-700 text-gray-100 rounded-t-2xl rounded-br-2xl'; ?> 
                                px-4 py-2 shadow-md break-words">
                                <?php echo htmlspecialchars($msg['message']); ?>
                            </div>
                            <div class="text-xs text-gray-500 mt-1 <?php echo $isCurrentUser ? 'text-right mr-2' : 'ml-2'; ?>">
                                <?php echo $msgTime; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Error message (if any) -->
        <?php if ($error): ?>
            <div class="bg-red-500/10 border border-red-500/30 text-red-300 rounded-xl p-3 mb-3 text-sm text-center animate-pulse">
                ⚠️ <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <!-- Message input form -->
        <form method="post" class="mt-2 pt-2 border-t border-gray-700">
            <div class="flex gap-2">
                <input type="text" 
                       name="message" 
                       placeholder="Type your message..." 
                       required
                       class="flex-1 px-4 py-3 bg-gray-700/50 border border-gray-600 rounded-xl text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all duration-200">
                <button type="submit" 
                        class="px-6 py-3 bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 text-white font-semibold rounded-xl shadow-lg hover:shadow-purple-500/25 transform hover:scale-[1.02] transition-all duration-200">
                    Send
                </button>
            </div>
        </form>
    </div>

    <!-- Auto-scroll to bottom on page load (optional, no logic change) -->
    <script>
        // Auto-scroll to the latest message (purely cosmetic)
        const messagesContainer = document.querySelector('.messages-container');
        if (messagesContainer) {
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
        }
    </script>

</body>
</html>