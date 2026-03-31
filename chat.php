<?php
// chat.php - Real‑time chat with reply functionality
session_start();
if (!isset($_SESSION['username']) && !isset($_COOKIE['username'])) {
    header('Location: index.php');
    exit;
}

if (!isset($_SESSION['username']) && isset($_COOKIE['username'])) {
    $_SESSION['username'] = $_COOKIE['username'];
}

$username = $_SESSION['username'];
$error = '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>Messages · Chat</title>
    
    <!-- Bootstrap Icons CDN -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <!-- SF Pro font system (Apple's typography) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;14..32,400;14..32,500;14..32,600;14..32,700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            /* Light mode variables */
            --bg-primary: #f5f5f7;
            --bg-secondary: #ffffff;
            --text-primary: #1d1c1e;
            --text-secondary: #8e8e93;
            --border-color: rgba(0, 0, 0, 0.06);
            --bubble-incoming: #e9e9ef;
            --bubble-outgoing: #1d1c1e;
            --bubble-outgoing-text: #ffffff;
            --input-bg: #f5f5f7;
            --shadow-color: rgba(0, 0, 0, 0.08);
            --scrollbar-thumb: rgba(0, 0, 0, 0.2);
            --scrollbar-thumb-hover: rgba(0, 0, 0, 0.3);
            --reply-context-bg: #f5f5f7;
        }
        
        /* Dark mode variables */
        body.dark {
            --bg-primary: #000000;
            --bg-secondary: #1c1c1e;
            --text-primary: #ffffff;
            --text-secondary: #8e8e93;
            --border-color: rgba(255, 255, 255, 0.1);
            --bubble-incoming: #2c2c2e;
            --bubble-outgoing: #0a84ff;
            --bubble-outgoing-text: #ffffff;
            --input-bg: #2c2c2e;
            --shadow-color: rgba(0, 0, 0, 0.3);
            --scrollbar-thumb: rgba(255, 255, 255, 0.2);
            --scrollbar-thumb-hover: rgba(255, 255, 255, 0.3);
            --reply-context-bg: #2c2c2e;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'SF Pro Text', 'Helvetica Neue', sans-serif;
            background: var(--bg-primary);
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            transition: background 0.3s ease, color 0.3s ease;
        }
        
        /* Main container with subtle depth */
        .chat-container {
            max-width: 1280px;
            margin: 0 auto;
            height: 100vh;
            display: flex;
            flex-direction: column;
            background: var(--bg-secondary);
            box-shadow: 0 0 0 1px rgba(0, 0, 0, 0.02), 0 20px 40px -12px var(--shadow-color);
        }
        
        /* Header with floating shadow */
        .chat-header {
            padding: 20px 24px;
            background: var(--bg-secondary);
            border-bottom: 1px solid var(--border-color);
            backdrop-filter: blur(10px);
            position: sticky;
            top: 0;
            z-index: 10;
        }
        
        @media (min-width: 768px) {
            .chat-header {
                padding: 24px 32px;
            }
        }
        
        /* Messages area - clean scrolling */
        .messages-area {
            flex: 1;
            overflow-y: auto;
            padding: 16px 20px;
            scroll-behavior: smooth;
        }
        
        @media (min-width: 768px) {
            .messages-area {
                padding: 24px 32px;
            }
        }
        
        .messages-area::-webkit-scrollbar {
            width: 6px;
        }
        
        .messages-area::-webkit-scrollbar-track {
            background: transparent;
        }
        
        .messages-area::-webkit-scrollbar-thumb {
            background: var(--scrollbar-thumb);
            border-radius: 100px;
        }
        
        .messages-area::-webkit-scrollbar-thumb:hover {
            background: var(--scrollbar-thumb-hover);
        }
        
        /* Message bubble styles */
        .message-row {
            display: flex;
            margin-bottom: 20px;
            animation: messageSlideIn 0.35s cubic-bezier(0.2, 0.9, 0.4, 1.1);
        }
        
        @media (min-width: 768px) {
            .message-row {
                margin-bottom: 24px;
            }
        }
        
        .message-row.outgoing {
            justify-content: flex-end;
        }
        
        @keyframes messageSlideIn {
            from {
                opacity: 0;
                transform: translateY(12px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .message-bubble {
            max-width: 85%;
            position: relative;
        }
        
        @media (min-width: 768px) {
            .message-bubble {
                max-width: 70%;
            }
        }
        
        .message-bubble.incoming .bubble-content {
            background: var(--bubble-incoming);
            color: var(--text-primary);
            border-radius: 21px 21px 21px 8px;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04), 0 2px 4px rgba(0, 0, 0, 0.02);
        }
        
        .message-bubble.outgoing .bubble-content {
            background: var(--bubble-outgoing);
            color: var(--bubble-outgoing-text);
            border-radius: 21px 21px 8px 21px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08), 0 1px 2px rgba(0, 0, 0, 0.04);
        }
        
        .bubble-content {
            padding: 10px 14px;
            font-size: 14px;
            font-weight: 400;
            line-height: 1.4;
            letter-spacing: -0.01em;
            word-break: break-word;
        }
        
        @media (min-width: 768px) {
            .bubble-content {
                padding: 12px 16px;
                font-size: 15px;
            }
        }
        
        /* Reply quote styling */
        .reply-quote {
            font-size: 12px;
            border-left: 2px solid var(--text-secondary);
            padding-left: 10px;
            margin-bottom: 8px;
            color: var(--text-secondary);
            font-weight: 400;
        }
        
        @media (min-width: 768px) {
            .reply-quote {
                font-size: 13px;
            }
        }
        
        .message-bubble.outgoing .reply-quote {
            border-left-color: rgba(255, 255, 255, 0.4);
            color: rgba(255, 255, 255, 0.7);
        }
        
        /* Sender name */
        .sender-name {
            font-size: 12px;
            font-weight: 500;
            margin-bottom: 4px;
            margin-left: 12px;
            color: var(--text-secondary);
            letter-spacing: -0.2px;
        }
        
        @media (min-width: 768px) {
            .sender-name {
                font-size: 13px;
            }
        }
        
        .message-bubble.outgoing .sender-name {
            display: none;
        }
        
        /* Timestamp */
        .timestamp {
            font-size: 10px;
            font-weight: 400;
            margin-top: 4px;
            margin-left: 12px;
            color: var(--text-secondary);
            letter-spacing: -0.2px;
        }
        
        @media (min-width: 768px) {
            .timestamp {
                font-size: 11px;
            }
        }
        
        .message-row.outgoing .timestamp {
            text-align: right;
            margin-right: 12px;
            margin-left: 0;
        }
        
        /* Reply button */
        .reply-action {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            margin-top: 6px;
            font-size: 10px;
            color: var(--text-secondary);
            cursor: pointer;
            transition: all 0.2s ease;
            background: none;
            border: none;
            font-family: inherit;
            padding: 4px 6px;
            border-radius: 20px;
        }
        
        @media (min-width: 768px) {
            .reply-action {
                gap: 6px;
                font-size: 11px;
                padding: 4px 8px;
            }
        }
        
        .reply-action:hover {
            color: #007aff;
            background: rgba(0, 122, 255, 0.08);
        }
        
        .message-bubble.outgoing .reply-action {
            color: rgba(255, 255, 255, 0.6);
        }
        
        .message-bubble.outgoing .reply-action:hover {
            color: #ffffff;
            background: rgba(255, 255, 255, 0.12);
        }
        
        /* Reply context bar */
        .reply-context {
            margin: 0 16px 12px 16px;
            padding: 10px 14px;
            background: var(--reply-context-bg);
            border-radius: 12px;
            font-size: 12px;
            color: var(--text-primary);
            border-left: 3px solid #007aff;
            animation: slideUp 0.25s ease;
        }
        
        @media (min-width: 768px) {
            .reply-context {
                margin: 0 32px 12px 32px;
                padding: 12px 16px;
                font-size: 13px;
            }
        }
        
        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(8px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        /* Input area */
        .input-area {
            padding: 16px 20px 20px;
            background: var(--bg-secondary);
            border-top: 1px solid var(--border-color);
        }
        
        @media (min-width: 768px) {
            .input-area {
                padding: 20px 32px 28px;
            }
        }
        
        .input-wrapper {
            display: flex;
            gap: 8px;
            align-items: flex-end;
        }
        
        @media (min-width: 768px) {
            .input-wrapper {
                gap: 12px;
            }
        }
        
        .message-input {
            flex: 1;
            padding: 10px 14px;
            background: var(--input-bg);
            border: 1px solid transparent;
            border-radius: 20px;
            font-family: inherit;
            font-size: 14px;
            font-weight: 400;
            color: var(--text-primary);
            resize: none;
            transition: all 0.2s ease;
            line-height: 1.4;
        }
        
        @media (min-width: 768px) {
            .message-input {
                padding: 12px 18px;
                font-size: 15px;
                border-radius: 24px;
            }
        }
        
        .message-input:focus {
            outline: none;
            background: var(--bg-secondary);
            border-color: #007aff;
            box-shadow: 0 0 0 3px rgba(0, 122, 255, 0.1);
        }
        
        .message-input::placeholder {
            color: var(--text-secondary);
        }
        
        .send-button {
            width: 38px;
            height: 38px;
            background: var(--bubble-outgoing);
            border: none;
            border-radius: 30px;
            color: var(--bubble-outgoing-text);
            font-size: 18px;
            cursor: pointer;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        @media (min-width: 768px) {
            .send-button {
                width: 44px;
                height: 44px;
                font-size: 20px;
            }
        }
        
        .send-button:hover {
            background: #007aff;
            transform: scale(1.02);
            box-shadow: 0 4px 12px rgba(0, 122, 255, 0.3);
        }
        
        .send-button:active {
            transform: scale(0.98);
        }
        
        /* Empty state */
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: var(--text-secondary);
        }
        
        @media (min-width: 768px) {
            .empty-state {
                padding: 60px 20px;
            }
        }
        
        .empty-state i {
            font-size: 40px;
            margin-bottom: 12px;
            opacity: 0.5;
        }
        
        @media (min-width: 768px) {
            .empty-state i {
                font-size: 48px;
                margin-bottom: 16px;
            }
        }
        
        /* Welcome text */
        .welcome-text {
            font-size: 24px;
            font-weight: 600;
            letter-spacing: -0.5px;
            background: linear-gradient(135deg, var(--text-primary) 0%, var(--text-secondary) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        @media (min-width: 768px) {
            .welcome-text {
                font-size: 28px;
            }
        }
        
        .subhead {
            font-size: 12px;
            color: var(--text-secondary);
            margin-top: 4px;
        }
        
        @media (min-width: 768px) {
            .subhead {
                font-size: 14px;
            }
        }
        
        /* Theme toggle button */
        .theme-toggle {
            background: none;
            border: none;
            font-size: 20px;
            cursor: pointer;
            padding: 8px;
            border-radius: 30px;
            transition: all 0.2s ease;
            color: var(--text-secondary);
        }
        
        .theme-toggle:hover {
            background: var(--input-bg);
            transform: scale(1.05);
        }
        
        /* Scroll to bottom button */
        .scroll-to-bottom {
            position: fixed;
            bottom: 100px;
            right: 20px;
            background: var(--bubble-outgoing);
            color: var(--bubble-outgoing-text);
            border: none;
            border-radius: 30px;
            padding: 10px 16px;
            font-size: 14px;
            cursor: pointer;
            display: none;
            align-items: center;
            gap: 8px;
            box-shadow: 0 2px 12px var(--shadow-color);
            transition: all 0.2s ease;
            z-index: 100;
        }
        
        @media (min-width: 768px) {
            .scroll-to-bottom {
                bottom: 120px;
                right: 30px;
                padding: 12px 20px;
            }
        }
        
        .scroll-to-bottom:hover {
            transform: translateY(-2px);
            background: #007aff;
        }
        
        .scroll-to-bottom.visible {
            display: flex;
        }
        
        /* Online indicator */
        .online-indicator {
            display: flex;
            align-items: center;
            gap: 4px;
        }
        
        @media (min-width: 768px) {
            .online-indicator {
                gap: 8px;
            }
        }
        
        .online-dot {
            font-size: 18px;
        }
        
        @media (min-width: 768px) {
            .online-dot {
                font-size: 24px;
            }
        }
        
        .online-text {
            font-size: 12px;
        }
        
        @media (min-width: 768px) {
            .online-text {
                font-size: 14px;
            }
        }
        
        /* Loading indicator */
        .sending-indicator {
            position: fixed;
            bottom: 100px;
            left: 50%;
            transform: translateX(-50%);
            background: var(--bubble-outgoing);
            color: var(--bubble-outgoing-text);
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 12px;
            display: none;
            align-items: center;
            gap: 8px;
            z-index: 100;
            box-shadow: 0 2px 8px var(--shadow-color);
        }
        
        .sending-indicator.visible {
            display: flex;
        }
    </style>
</head>
<body>
    <div class="chat-container">
        <!-- Header with theme toggle -->
        <div class="chat-header">
            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
                <div>
                    <h1 class="welcome-text">Messages DEV-EX-205</h1>
                    <p class="subhead">Welcome, <?php echo htmlspecialchars($username); ?></p>
                </div>
                <div style="display: flex; gap: 12px; align-items: center;">
                    <div class="online-indicator">
                        <i class="bi bi-dot online-dot" style="color: #34c759;"></i>
                        <span class="online-text" style="color: var(--text-secondary);">Connected</span>
                    </div>
                    <button id="themeToggle" class="theme-toggle">
                        <i class="bi bi-moon-stars"></i>
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Messages container -->
        <div id="messages" class="messages-area"></div>
        
        <!-- Reply context bar (hidden by default) -->
        <div id="replyContext" class="reply-context" style="display: none;">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div style="display: flex; align-items: center; gap: 8px; flex: 1;">
                    <i class="bi bi-reply-fill" style="font-size: 12px; color: #007aff;"></i>
                    <span id="replyContextText" style="flex: 1; font-size: 12px;"></span>
                </div>
                <button type="button" id="cancelReply" style="background: none; border: none; color: #007aff; font-size: 12px; font-weight: 500; cursor: pointer; padding: 4px 8px; border-radius: 8px;">
                    Cancel
                </button>
            </div>
        </div>
        
        <!-- Send message form -->
        <div class="input-area">
            <form id="sendForm">
                <input type="hidden" name="parent_id" id="parentId" value="">
                <div class="input-wrapper">
                    <input type="text"
                           name="message"
                           id="messageInput"
                           placeholder="Type a message..."
                           autocomplete="off"
                           class="message-input">
                    <button type="submit" class="send-button">
                        <i class="bi bi-arrow-up"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Scroll to bottom button -->
    <button id="scrollToBottom" class="scroll-to-bottom">
        <i class="bi bi-arrow-down"></i>
        <span>New messages</span>
    </button>
    
    <!-- Sending indicator -->
    <div id="sendingIndicator" class="sending-indicator">
        <i class="bi bi-send"></i>
        <span>Sending...</span>
    </div>
    
    <script>
        const messagesContainer = document.getElementById('messages');
        const sendForm = document.getElementById('sendForm');
        const messageInput = document.getElementById('messageInput');
        const parentIdInput = document.getElementById('parentId');
        const replyContextDiv = document.getElementById('replyContext');
        const replyContextText = document.getElementById('replyContextText');
        const cancelReplyBtn = document.getElementById('cancelReply');
        const scrollToBottomBtn = document.getElementById('scrollToBottom');
        const themeToggle = document.getElementById('themeToggle');
        const sendingIndicator = document.getElementById('sendingIndicator');
        
        let displayedIds = new Set();
        let isUserScrolling = false;
        let scrollTimeout = null;
        let isSending = false;
        
        // Theme management
        function initTheme() {
            const savedTheme = localStorage.getItem('theme');
            const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            
            if (savedTheme === 'dark' || (!savedTheme && prefersDark)) {
                document.body.classList.add('dark');
                updateThemeIcon(true);
            } else {
                document.body.classList.remove('dark');
                updateThemeIcon(false);
            }
        }
        
        function updateThemeIcon(isDark) {
            const icon = themeToggle.querySelector('i');
            if (isDark) {
                icon.className = 'bi bi-sun';
            } else {
                icon.className = 'bi bi-moon-stars';
            }
        }
        
        function toggleTheme() {
            const isDark = document.body.classList.toggle('dark');
            localStorage.setItem('theme', isDark ? 'dark' : 'light');
            updateThemeIcon(isDark);
        }
        
        themeToggle.addEventListener('click', toggleTheme);
        initTheme();
        
        // Check if user is at bottom of messages
        function isAtBottom() {
            const threshold = 100; // pixels from bottom
            return messagesContainer.scrollHeight - messagesContainer.scrollTop - messagesContainer.clientHeight <= threshold;
        }
        
        // Scroll to bottom smoothly
        function scrollToBottom() {
            messagesContainer.scrollTo({
                top: messagesContainer.scrollHeight,
                behavior: 'smooth'
            });
        }
        
        // Handle scroll events
        function handleScroll() {
            // Show/hide scroll to bottom button
            if (!isAtBottom() && messagesContainer.scrollHeight > messagesContainer.clientHeight) {
                scrollToBottomBtn.classList.add('visible');
            } else {
                scrollToBottomBtn.classList.remove('visible');
            }
            
            // Mark that user is scrolling
            isUserScrolling = true;
            clearTimeout(scrollTimeout);
            scrollTimeout = setTimeout(() => {
                isUserScrolling = false;
            }, 150);
        }
        
        messagesContainer.addEventListener('scroll', handleScroll);
        scrollToBottomBtn.addEventListener('click', () => {
            scrollToBottom();
            setTimeout(() => {
                scrollToBottomBtn.classList.remove('visible');
            }, 500);
        });
        
        function escapeHtml(str) {
            if (!str) return '';
            const div = document.createElement('div');
            div.textContent = str;
            return div.innerHTML;
        }
        
        function formatTime(dateString) {
            const date = new Date(dateString);
            const now = new Date();
            const isToday = date.toDateString() === now.toDateString();
            
            if (isToday) {
                return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
            } else {
                return date.toLocaleDateString([], { month: 'short', day: 'numeric' }) + ' at ' + 
                       date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
            }
        }
        
        function appendMessage(msg, preserveScroll = false) {
            const isCurrentUser = (msg.username === '<?php echo addslashes($username); ?>');
            const messageDiv = document.createElement('div');
            messageDiv.className = `message-row ${isCurrentUser ? 'outgoing' : ''}`;
            
            const messageBubble = document.createElement('div');
            messageBubble.className = `message-bubble ${isCurrentUser ? 'outgoing' : 'incoming'}`;
            
            let quoteHtml = '';
            if (msg.parent_id && msg.parent_username && msg.parent_message) {
                const shortMsg = msg.parent_message.length > 50 ? msg.parent_message.substring(0, 50) + '…' : msg.parent_message;
                quoteHtml = `
                    <div class="reply-quote">
                        <i class="bi bi-reply-fill" style="font-size: 10px; margin-right: 4px;"></i>
                        Replying to <strong>${escapeHtml(msg.parent_username)}</strong><br>
                        <span style="font-size: 11px;">${escapeHtml(shortMsg)}</span>
                    </div>
                `;
            }
            
            const senderHtml = !isCurrentUser ? `<div class="sender-name">${escapeHtml(msg.username)}</div>` : '';
            const timestampHtml = `<div class="timestamp">${formatTime(msg.created_at)}</div>`;
            
            messageBubble.innerHTML = `
                ${senderHtml}
                <div class="bubble-content">
                    ${quoteHtml}
                    <div style="display: flex; justify-content: space-between; align-items: flex-end; gap: 8px;">
                        <span style="flex: 1;">${escapeHtml(msg.message)}</span>
                        <button class="reply-action" data-message-id="${msg.id}" data-username="${escapeHtml(msg.username)}" data-message="${escapeHtml(msg.message.substring(0, 80))}">
                            <i class="bi bi-reply"></i>
                            <span style="display: none;">Reply</span>
                        </button>
                    </div>
                </div>
                ${timestampHtml}
            `;
            
            messageDiv.appendChild(messageBubble);
            messagesContainer.appendChild(messageDiv);
            
            // Scroll to bottom for new outgoing messages or if user was at bottom
            if (!preserveScroll && (isCurrentUser || isAtBottom())) {
                scrollToBottom();
            }
        }
        
        async function fetchMessages(appendOnly = false) {
            try {
                const res = await fetch('api.php');
                if (!res.ok) throw new Error('Network error');
                const data = await res.json();
                if (data.status !== 'ok') throw new Error(data.message);
                
                const messages = data.messages || [];
                const wasAtBottom = isAtBottom();
                
                if (messages.length === 0 && displayedIds.size === 0) {
                    messagesContainer.innerHTML = `
                        <div class="empty-state">
                            <i class="bi bi-chat-dots"></i>
                            <p>No messages yet</p>
                            <p style="font-size: 12px; margin-top: 8px;">Be the first to start a conversation</p>
                        </div>
                    `;
                } else if (messages.length > 0 && displayedIds.size === 0 && !appendOnly) {
                    messagesContainer.innerHTML = '';
                }
                
                let newMessagesAdded = false;
                let newMessages = [];
                
                messages.forEach(msg => {
                    if (!displayedIds.has(msg.id)) {
                        displayedIds.add(msg.id);
                        newMessages.push(msg);
                        newMessagesAdded = true;
                    }
                });
                
                // Append new messages in order
                if (newMessagesAdded) {
                    newMessages.forEach(msg => {
                        appendMessage(msg, true);
                    });
                    
                    // Smart scroll logic after all messages are added
                    if (wasAtBottom && !isUserScrolling) {
                        scrollToBottom();
                    } else if (!isUserScrolling && !wasAtBottom) {
                        scrollToBottomBtn.classList.add('visible');
                    }
                }
                
            } catch (err) {
                console.error('Fetch error:', err);
            }
        }
        
        async function sendMessage(message, parentId) {
            if (isSending) return;
            
            const formData = new FormData();
            formData.append('message', message);
            if (parentId) formData.append('parent_id', parentId);
            
            isSending = true;
            sendingIndicator.classList.add('visible');
            
            // Disable input while sending
            messageInput.disabled = true;
            const sendButton = document.querySelector('.send-button');
            sendButton.disabled = true;
            sendButton.style.opacity = '0.6';
            
            try {
                const res = await fetch('send_message.php', {
                    method: 'POST',
                    body: formData
                });
                const data = await res.json();
                if (data.status === 'ok') {
                    messageInput.value = '';
                    clearReplyContext();
                    
                    // Clear displayed IDs and fetch only new messages
                    // Don't clear the container to avoid flicker
                    const oldScrollHeight = messagesContainer.scrollHeight;
                    const oldScrollTop = messagesContainer.scrollTop;
                    const wasNearBottom = isAtBottom();
                    
                    // Fetch only new messages (append mode)
                    await fetchMessages(true);
                    
                    // If we were near bottom, scroll to bottom
                    if (wasNearBottom) {
                        setTimeout(() => scrollToBottom(), 50);
                    }
                } else {
                    alert('Error: ' + data.message);
                }
            } catch (err) {
                console.error('Send error:', err);
                alert('Could not send message');
            } finally {
                isSending = false;
                sendingIndicator.classList.remove('visible');
                messageInput.disabled = false;
                sendButton.disabled = false;
                sendButton.style.opacity = '';
                messageInput.focus();
            }
        }
        
        function clearReplyContext() {
            parentIdInput.value = '';
            replyContextDiv.style.display = 'none';
            replyContextText.innerText = '';
        }
        
        function setReplyContext(messageId, username, messageSnippet) {
            parentIdInput.value = messageId;
            const snippet = messageSnippet.length > 60 ? messageSnippet.substring(0, 60) + '…' : messageSnippet;
            replyContextText.innerHTML = `<i class="bi bi-reply-fill" style="font-size: 10px;"></i> Replying to <strong>${escapeHtml(username)}</strong>: ${escapeHtml(snippet)}`;
            replyContextDiv.style.display = 'block';
            messageInput.focus();
        }
        
        messagesContainer.addEventListener('click', (e) => {
            const replyBtn = e.target.closest('.reply-action');
            if (replyBtn) {
                const messageId = replyBtn.dataset.messageId;
                const username = replyBtn.dataset.username;
                const message = replyBtn.dataset.message;
                setReplyContext(messageId, username, message);
                e.preventDefault();
            }
        });
        
        cancelReplyBtn.addEventListener('click', () => {
            clearReplyContext();
        });
        
        sendForm.addEventListener('submit', (e) => {
            e.preventDefault();
            const message = messageInput.value.trim();
            if (message === '' || isSending) return;
            const parentId = parentIdInput.value;
            sendMessage(message, parentId);
        });
        
        messageInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                sendForm.dispatchEvent(new Event('submit'));
            }
        });
        
        // Initial load
        fetchMessages(false);
        setInterval(() => fetchMessages(true), 2000);
        
        // Check scroll position periodically to hide button
        setInterval(() => {
            if (isAtBottom()) {
                scrollToBottomBtn.classList.remove('visible');
            }
        }, 500);
    </script>
</body>
</html>