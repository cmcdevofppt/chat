<?php
// chat_realtime.php - Real-time chat using fetch API (polling)
session_start();
if (!isset($_SESSION['username']) && !isset($_COOKIE['username'])) {
    header('Location: index.php');
    exit;
}

if (!isset($_SESSION['username']) && isset($_COOKIE['username'])) {
    $_SESSION['username'] = $_COOKIE['username'];
}

require 'db.php';
$username = $_SESSION['username'];
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $message = trim($_POST['message'] ?? '');
    if ($message !== '') {
        $stmt = $pdo->prepare("INSERT INTO messages (username, message) VALUES (?, ?)");
        $stmt->execute([$username, $message]);
        header('Location: chat.php');
        exit;
    } else {
        $error = 'Message cannot be empty.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>Real-Time Chat</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(15px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        .message-new {
            animation: slideIn 0.3s ease-out forwards;
        }
        /* Custom scrollbar */
        #messages::-webkit-scrollbar {
            width: 6px;
        }
        #messages::-webkit-scrollbar-track {
            background: #1f2937;
            border-radius: 10px;
        }
        #messages::-webkit-scrollbar-thumb {
            background: #4b5563;
            border-radius: 10px;
        }
        #messages::-webkit-scrollbar-thumb:hover {
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
                    💬 Real‑Time Chat
                </h1>
                <p class="text-gray-400 text-sm mt-1">
                    Welcome, <span class="font-semibold text-purple-300"><?php echo htmlspecialchars($username); ?></span>
                </p>
            </div>
            <a 
               class="px-4 py-2 bg-gray-700 hover:bg-gray-600 text-gray-200 rounded-lg transition-colors duration-200 text-sm font-medium shadow-md hover:shadow-lg">
                Dev 205 
            </a>
        </div>

        <!-- Messages container -->
        <div id="messages" class="flex-1 overflow-y-auto my-4 space-y-3 pr-2"></div>

        <!-- Error message -->
        <?php if ($error): ?>
            <div class="bg-red-500/10 border border-red-500/30 text-red-300 rounded-xl p-3 mb-3 text-sm text-center animate-pulse">
                ⚠️ <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <!-- Send message form -->
        <form id="sendForm" method="post" action="chat.php" class="mt-2 pt-2 border-t border-gray-700">
            <div class="flex gap-2">
                <input type="text" 
                       name="message" 
                       id="messageInput"
                       placeholder="Type your message..." 
                       required
                       autocomplete="off"
                       class="flex-1 px-4 py-3 bg-gray-700/50 border border-gray-600 rounded-xl text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all duration-200">
                <button type="submit" 
                        class="px-6 py-3 bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 text-white font-semibold rounded-xl shadow-lg hover:shadow-purple-500/25 transform hover:scale-[1.02] transition-all duration-200">
                    Send
                </button>
            </div>
        </form>
    </div>

    <script>
        const messagesContainer = document.getElementById('messages');
        // Store the IDs (or a combined key) of messages we've already displayed
        let displayedIds = new Set();

        async function fetchMessages() {
            try {
                const res = await fetch('api.php');
                if (!res.ok) throw new Error('Network response not ok');
                const data = await res.json();
                if (data.status !== 'ok') throw new Error(data.message || 'API error');

                const messages = data.messages;
                if (!messages || messages.length === 0) return;

                // For each message, check if it's new
                messages.forEach(msg => {
                    // Create a unique key (using id if available, else username+timestamp)
                    const key = msg.id || `${msg.username}_${msg.created_at}_${msg.message}`;
                    if (!displayedIds.has(key)) {
                        displayedIds.add(key);
                        appendMessage(msg);
                    }
                });

                // Always scroll to bottom when new messages are appended
                messagesContainer.scrollTop = messagesContainer.scrollHeight;
                // Repeat briefly to ensure rendering is complete on slow devices
                setTimeout(() => {
                    messagesContainer.scrollTop = messagesContainer.scrollHeight;
                }, 50);
            } catch (err) {
                console.error('Fetch error', err);
            }
        }

        function appendMessage(msg) {
            const isCurrentUser = (msg.username === '<?php echo addslashes($username); ?>');
            const msgTime = new Date(msg.created_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });

            const messageDiv = document.createElement('div');
            messageDiv.className = `flex ${isCurrentUser ? 'justify-end' : 'justify-start'} message-new`;

            const bubbleHtml = `
                <div class="max-w-[85%] md:max-w-[70%] lg:max-w-[60%]">
                    ${!isCurrentUser ? `<div class="text-xs text-gray-400 mb-1 ml-2">${escapeHtml(msg.username)}</div>` : ''}
                    <div class="${isCurrentUser 
                        ? 'bg-gradient-to-r from-purple-600 to-pink-600 text-white rounded-t-2xl rounded-bl-2xl' 
                        : 'bg-gray-700 text-gray-100 rounded-t-2xl rounded-br-2xl'} 
                        px-4 py-2 shadow-md break-words">
                        ${escapeHtml(msg.message)}
                    </div>
                    <div class="text-xs text-gray-500 mt-1 ${isCurrentUser ? 'text-right mr-2' : 'ml-2'}">
                        ${msgTime}
                    </div>
                </div>
            `;
            messageDiv.innerHTML = bubbleHtml;
            messagesContainer.appendChild(messageDiv);
        }

        // Helper to escape HTML to prevent XSS
        function escapeHtml(str) {
            if (!str) return '';
            return str.replace(/[&<>]/g, function(m) {
                if (m === '&') return '&amp;';
                if (m === '<') return '&lt;';
                if (m === '>') return '&gt;';
                return m;
            }).replace(/[\uD800-\uDBFF][\uDC00-\uDFFF]/g, function(c) {
                return c;
            });
        }

        // Initial load and polling every 2 seconds
        fetchMessages();
        setInterval(fetchMessages, 2000);
    </script>
</body>
</html>