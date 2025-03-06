<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../pages/login.php");
    exit();
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
        .chat-message { animation: fadeIn 0.3s ease-in; }
        .typing-indicator span { animation: bounce 1s infinite; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes bounce { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-5px); } }
        .tool-card:hover { transform: translateY(-3px); }
    </style>
</head>
<body class="bg-gray-50">
    <div class="flex h-screen">

        <!-- Sidebar -->
        <div class="w-80 bg-white border-r">
            <div class="p-4">
                <h4 class="text-sm font-semibold text-gray-600 mb-4">CAREER TOOLS</h4>
                <div class="space-y-3">
                    <div class="tool-card p-3 bg-blue-50 rounded-lg cursor-pointer">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-brain text-blue-600"></i>
                            </div>
                            <div>
                                <h5 class="font-medium">Skill Assessment</h5>
                                <p class="text-sm text-gray-600">Evaluate your abilities</p>
                            </div>
                        </div>
                    </div>
                    <div class="tool-card p-3 bg-green-50 rounded-lg cursor-pointer">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-route text-green-600"></i>
                            </div>
                            <div>
                                <h5 class="font-medium">Career Path</h5>
                                <p class="text-sm text-gray-600">Explore opportunities</p>
                            </div>
                        </div>
                    </div>
                    <div class="tool-card p-3 bg-purple-50 rounded-lg cursor-pointer">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-file-alt text-purple-600"></i>
                            </div>
                            <div>
                                <h5 class="font-medium">Resume Review</h5>
                                <p class="text-sm text-gray-600">Get AI feedback</p>
                            </div>
                        </div>
                    </div>
                    <div class="tool-card p-3 bg-yellow-50 rounded-lg cursor-pointer">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 bg-yellow-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-user-tie text-yellow-600"></i>
                            </div>
                            <div>
                                <h5 class="font-medium">Interview Prep</h5>
                                <p class="text-sm text-gray-600">Practice with AI</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="p-4 border-t mt-4">
                <h4 class="text-sm font-semibold text-gray-600 mb-4">RECENT SESSIONS</h4>
                <div class="space-y-3">
                    <div class="flex items-center space-x-3 text-sm text-gray-600 cursor-pointer hover:bg-gray-50 p-2 rounded">
                        <i class="fas fa-history"></i>
                        <span>Career Path Analysis</span>
                    </div>
                    <div class="flex items-center space-x-3 text-sm text-gray-600 cursor-pointer hover:bg-gray-50 p-2 rounded">
                        <i class="fas fa-history"></i>
                        <span>Skills Assessment</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Chat Area -->
        <div class="flex-1 flex flex-col">
            <div class="bg-white border-b p-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                            <i class="fas fa-robot text-blue-600"></i>
                        </div>
                        <div>
                            <h2 class="font-semibold">CareerBot</h2>
                            <p class="text-sm text-green-600">Online</p>
                        </div>
                    </div>
                    <div class="relative">
                        <a href="../services/dashboard.php" id="settingsLink" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                            </svg>
                        </a>
                        <div id="settingsCard" class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg hidden z-50 border border-gray-200">
                            <div class="p-4">
                                <a href="../../profile/profile.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">View Profile</a>
                                <a href="../../profile/profile_edit.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Edit Profile</a>
                                <div class="border-t border-gray-200"></div>
                                <a href="../pages/logout.php" class="block px-4 py-2 text-sm text-red-700 hover:bg-red-100">Logout</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex-1 overflow-y-auto p-4" id="chatContainer"></div>

            <div class="bg-white border-t p-4">
                <div class="flex items-center space-x-4">
                    <button class="text-gray-400 hover:text-gray-600"><i class="fas fa-paperclip"></i></button>
                    <input type="text" id="messageInput" placeholder="Type your message..." class="flex-1 border rounded-lg px-4 py-2 focus:outline-none focus:border-blue-500">
                    <button id="sendButton" class="bg-blue-600 text-white rounded-lg px-4 py-2 hover:bg-blue-700"><i class="fas fa-paper-plane"></i></button>
                </div>
            </div>
        </div>
    </div>

       <script>
    class CareerBot {
        constructor() {
            this.chatContainer = document.getElementById('chatContainer');
            this.messageInput = document.getElementById('messageInput');
            this.initEventListeners();
            this.showWelcome();
        }

        initEventListeners() {
            document.getElementById('sendButton').addEventListener('click', () => this.handleSend());
            this.messageInput.addEventListener('keypress', (e) => {
                if (e.key === 'Enter') this.handleSend();
            });
            
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
                this.addMessage(response);
            } catch (error) {
                this.addMessage("⚠️ Connection error. Please try again.");
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
                .then(response => this.addMessage(response))
                .catch(() => this.addMessage("Failed to start tool"))
                .finally(() => this.hideTyping());
        }

       async fetchResponse(message) {
    try {
        const response = await fetch('/smartcareer/pages/services/api.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            credentials: 'include',
            body: JSON.stringify({ message })
        });

        const text = await response.text(); // Get raw text first for debugging
        console.log('Raw API Response:', text); // Log raw response

        if (!response.ok) {
            const error = JSON.parse(text); // Try parsing as JSON
            throw new Error(error.error || 'Request failed');
        }

        const data = JSON.parse(text); // Parse the valid JSON
        return data.response.replace(/\n/g, '<br>');
    } catch (error) {
        console.error('API Error:', error.message);
        return `⚠️ Error: ${error.message}. Please try again.`;
    }
}

        addMessage(content, isUser = false) {
            const messageDiv = document.createElement('div');
            messageDiv.className = `chat-message flex ${isUser ? 'justify-end' : 'justify-start'} mb-4`;
            messageDiv.innerHTML = `
                <div class="${isUser ? 'bg-blue-600 text-white' : 'bg-gray-100'} rounded-lg px-4 py-2 max-w-md">
                    ${content}
                </div>
            `;
            this.chatContainer.appendChild(messageDiv);
            this.chatContainer.scrollTop = this.chatContainer.scrollHeight;
        }

        showTyping() {
            if (document.getElementById('typingIndicator')) return;
            
            const typingDiv = document.createElement('div');
            typingDiv.id = 'typingIndicator';
            typingDiv.className = 'chat-message flex justify-start mb-4';
            typingDiv.innerHTML = `
                <div class="bg-gray-100 rounded-lg px-4 py-2">
                    <div class="typing-indicator">
                        <span>.</span><span>.</span><span>.</span>
                    </div>
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
                this.addMessage("Welcome to CareerGPT!<br>How can I assist with your career goals today?");
            }, 500);
        }
    }

    // Initialize when ready
    document.addEventListener('DOMContentLoaded', () => new CareerBot());
    </script>
</body>
</html>