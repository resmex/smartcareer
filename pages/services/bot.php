<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../pages/login.php");
    exit();
}

// Fetch first_name if not set
if (!isset($_SESSION['first_name'])) {
    include '../../includes/connect.php'; // Assuming this file sets up $con
    $stmt = $con->prepare("SELECT first_name FROM users WHERE id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $_SESSION['first_name'] = $result->num_rows > 0 ? $result->fetch_assoc()['first_name'] : 'User';
    $stmt->close();
}
$firstName = $_SESSION['first_name'];

// Record session interactions
$_SESSION['chat_history'] = isset($_SESSION['chat_history']) ? $_SESSION['chat_history'] : [];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message'])) {
    $_SESSION['chat_history'][] = [
        'message' => $_POST['message'],
        'timestamp' => date('Y-m-d H:i:s'),
        'type' => 'user'
    ];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SmartCareer | AI Career Counseling</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .sidebar { position: sticky; top: 0; height: 100vh; overflow-y: auto; }
        .chat-message { animation: fadeIn 0.3s ease-in; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        .tool-card { transition: all 0.3s ease; }
        .tool-card:hover { transform: translateY(-3px); box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
        .file-upload { display: none; }
        .file-upload-label { cursor: pointer; }
        .grok-response { font-family: Arial, sans-serif; line-height: 1.6; }
        .grok-response strong { font-weight: 700; color: #1f2937; }
        .grok-response ul { list-style-type: disc; padding-left: 20px; }
        .header { background: #f9fafb; border-bottom: 1px solid #e5e7eb; }
        /* .sessions-container { max-height: 300px; overflow-y: auto; display: flex; flex-direction: column-reverse; } */
    </style>
</head>
<body class="bg-gray-50 font-sans antialiased">
    <div class="flex h-screen">

        <!-- Sidebar -->
        <aside class="w-80 bg-white border-r sidebar shadow-lg">
            <div class="p-6">
                <h4 class="text-sm font-semibold text-gray-600 uppercase mb-6">Career Tools</h4>
                <div class="space-y-4">
                    <div class="tool-card p-4 bg-blue-50 rounded-xl border border-blue-100 cursor-pointer">
                        <div class="flex items-center space-x-4">
                            <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                                <i class="fas fa-brain text-blue-600 text-xl"></i>
                            </div>
                            <div>
                                <h5 class="font-semibold text-gray-800">Skill Assessment</h5>
                                <p class="text-sm text-gray-600">Check what you’re good at</p>
                            </div>
                        </div>
                    </div>
                    <div class="tool-card p-4 bg-green-50 rounded-xl border border-green-100 cursor-pointer">
                        <div class="flex items-center space-x-4">
                            <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                                <i class="fas fa-route text-green-600 text-xl"></i>
                            </div>
                            <div>
                                <h5 class="font-semibold text-gray-800">Career Path</h5>
                                <p class="text-sm text-gray-600">Plan your next steps</p>
                            </div>
                        </div>
                    </div>
                    <div class="tool-card p-4 bg-purple-50 rounded-xl border border-purple-100 cursor-pointer">
                        <div class="flex items-center space-x-4">
                            <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center">
                                <i class="fas fa-file-alt text-purple-600 text-xl"></i>
                            </div>
                            <div>
                                <h5 class="font-semibold text-gray-800">Resume Review</h5>
                                <p class="text-sm text-gray-600">Fix your resume</p>
                            </div>
                        </div>
                    </div>
                    <div class="tool-card p-4 bg-yellow-50 rounded-xl border border-yellow-100 cursor-pointer">
                        <div class="flex items-center space-x-4">
                            <div class="w-12 h-12 bg-yellow-100 rounded-full flex items-center justify-center">
                                <i class="fas fa-user-tie text-yellow-600 text-xl"></i>
                            </div>
                            <div>
                                <h5 class="font-semibold text-gray-800">Interview Prep</h5>
                                <p class="text-sm text-gray-600">Get ready to talk on interview</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="p-6 border-t">
                <h4 class="text-sm font-semibold text-gray-600 uppercase mb-6">Recent Sessions</h4>
                <div class="space-y-3 sessions-container" id="recentSessions">
                    <?php
                    if (!empty($_SESSION['chat_history'])) {
                        $recent = array_slice(array_reverse($_SESSION['chat_history']), 0, 5);
                        foreach ($recent as $session) {
                            echo '<div class="flex items-center space-x-3 text-sm text-gray-600 hover:bg-gray-100 p-2 rounded-lg transition">';
                            echo '<i class="fas fa-history"></i>';
                            echo '<span>' . htmlspecialchars($session['message']) . ' - ' . $session['timestamp'] . '</span>';
                            echo '</div>';
                        }
                    } else {
                        echo '<p class="text-sm text-gray-500">No recent sessions yet.</p>';
                    }
                    ?>
                </div>
            </div>
        </aside>

        <!-- Main Chat Area -->
        <div class="flex-1 flex flex-col">
            <header class="header p-6">
                <div class="flex items-center justify-between max-w-7xl mx-auto">
                    <div class="flex items-center space-x-4">
                        <div class="w-12 h-12 bg-indigo-100 rounded-full flex items-center justify-center">
                            <i class="fas fa-robot text-indigo-600 text-xl"></i>
                        </div>
                        <div>
                            <h2 class="text-2xl font-extrabold text-gray-800">CareerBot</h2>
                            <p class="text-sm text-green-600">Online</p>
                        </div>
                    </div>
                    <div>
                        <a href="../services/dashboard.php" class="text-gray-600 hover:text-indigo-600 transition">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                            </svg>
                        </a>
                    </div>
                </div>
            </header>

            <div class="flex-1 overflow-y-auto p-6 bg-gray-50">
                <div class="max-w-4xl mx-auto" id="chatContainer"></div>
            </div>

            <footer class="bg-white border-t p-6 shadow-inner">
                <div class="max-w-4xl mx-auto flex items-center space-x-4">
                    <label for="fileUpload" class="file-upload-label text-gray-500 hover:text-gray-700 transition">
                        <i class="fas fa-paperclip text-xl"></i>
                    </label>
                    <input type="file" id="fileUpload" class="file-upload" accept=".pdf,.doc,.docx">
                    <input type="text" id="messageInput" placeholder="Ask CareerBot anything..." 
                           class="flex-1 border border-gray-300 rounded-full px-6 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <button id="sendButton" class="bg-indigo-600 text-white rounded-full px-6 py-3 hover:bg-indigo-700 transition">
                        <i class="fas fa-paper-plane"></i>
                    </button>
                </div>
            </footer>
        </div>
    </div>

    <script>
    class CareerBot {
        constructor() {
            this.chatContainer = document.getElementById('chatContainer');
            this.messageInput = document.getElementById('messageInput');
            this.fileUpload = document.getElementById('fileUpload');
            this.recentSessions = document.getElementById('recentSessions');
            this.initEventListeners();
            this.showWelcome();
        }

        initEventListeners() {
            document.getElementById('sendButton').addEventListener('click', () => this.handleSend());
            this.messageInput.addEventListener('keypress', (e) => {
                if (e.key === 'Enter') this.handleSend();
            });
            this.fileUpload.addEventListener('change', (e) => this.handleFileUpload(e));
            document.querySelectorAll('.tool-card').forEach(card => {
                card.addEventListener('click', () => {
                    const tool = card.querySelector('h5').textContent;
                    this.handleToolRequest(tool);
                });
            });
        }

        async handleSend() {
            const message = this.messageInput.value.trim();
            if (!message) return;

            this.messageInput.value = '';
            this.addMessage(message, true);
            this.showTyping();

            try {
                const response = await this.fetchResponse(message);
                this.addMessage(this.formatResponse(response));
            } catch (error) {
                this.addMessage("⚠️ Connection error. Please try again.");
            } finally {
                this.hideTyping();
            }
        }

        async handleFileUpload(event) {
            const file = event.target.files[0];
            if (!file) return;

            const formData = new FormData();
            formData.append('file', file);

            this.addMessage(`Uploading ${file.name}...`, true);
            this.showTyping();

            try {
                const response = await fetch('/smartcareer/pages/services/upload.php', {
                    method: 'POST',
                    body: formData,
                    credentials: 'include'
                });
                const data = await response.json();
                if (response.ok) {
                    this.addMessage(`File uploaded successfully!<br>${data.message}`);
                } else {
                    throw new Error(data.error || 'File upload failed');
                }
            } catch (error) {
                this.addMessage(`⚠️ Error: ${error.message}`);
            } finally {
                this.hideTyping();
            }
        }

        handleToolRequest(toolName) {
            const prompts = {
                'Skill Assessment': "Please help me assess my current skills and identify areas for improvement.",
                'Career Path': "Suggest potential career paths based on my background:",
                'Resume Review': "Please review my resume and provide feedback:",
                'Interview Prep': "Help me prepare for a job interview in the following field:"
            };
            this.addMessage(`Starting ${toolName}...`, true);
            this.showTyping();
            this.fetchResponse(prompts[toolName] || `I need help with ${toolName}`)
                .then(response => this.addMessage(this.formatResponse(response)))
                .catch(() => this.addMessage("Failed to start tool"))
                .finally(() => this.hideTyping());
        }

        async fetchResponse(message) {
            const response = await fetch('/smartcareer/pages/services/api.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                credentials: 'include',
                body: JSON.stringify({ message })
            });
            const text = await response.text();
            console.log('Raw API Response:', text);
            if (!response.ok) throw new Error(JSON.parse(text).error || 'Request failed');
            return JSON.parse(text).response;
        }

        formatResponse(response) {
            // Grok 3-like formatting
            response = response.replace(/### (.*)/g, '<strong>$1</strong><br>');
            response = response.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
            response = response.replace(/^\s*-\s*(.*)$/gm, '<li>$1</li>');
            response = response.replace(/(\n<li>.*<\/li>)+/g, '<ul>$&</ul>');
            response = response.replace(/\n/g, '<br>');
            return `<div class="grok-response">${response}</div>`;
        }

        addMessage(content, isUser = false) {
            const messageDiv = document.createElement('div');
            messageDiv.className = `chat-message flex ${isUser ? 'justify-end' : 'justify-start'} mb-6`;
            messageDiv.innerHTML = `
                <div class="${isUser ? 'bg-indigo-600 text-white' : 'bg-white border border-gray-200'} rounded-xl px-6 py-4 max-w-2xl shadow-md">
                    ${content}
                </div>
            `;
            this.chatContainer.appendChild(messageDiv);
            this.chatContainer.scrollTop = this.chatContainer.scrollHeight;

            // Record user message in session
            if (isUser) {
                fetch(window.location.href, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `message=${encodeURIComponent(content)}`
                });
            }
        }

        showTyping() {
            if (document.getElementById('typingIndicator')) return;
            const typingDiv = document.createElement('div');
            typingDiv.id = 'typingIndicator';
            typingDiv.className = 'chat-message flex justify-start mb-6';
            typingDiv.innerHTML = `
                <div class="bg-white rounded-xl px-6 py-4 shadow-md">
                    <div class="text-gray-500">Processing your request...</div>
                </div>
            `;
            this.chatContainer.appendChild(typingDiv);
        }

        hideTyping() {
            const typing = document.getElementById('typingIndicator');
            if (typing) typing.remove();
        }

        showWelcome() {
            setTimeout(() => {
                this.addMessage(`Hey <?php echo htmlspecialchars($firstName); ?>, I’m CareerBot—your AI career coach.<br>How can I assist you today?`);
            }, 500);
        }
    }

    document.addEventListener('DOMContentLoaded', () => new CareerBot());
    </script>
</body>
</html>