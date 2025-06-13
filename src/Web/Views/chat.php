<?php 
$pageTitle = 'Chat - OpenAI Webchat';
ob_start(); 
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'brand': {
                            50: '#f0f9ff',
                            500: '#3b82f6',
                            600: '#2563eb',
                            700: '#1d4ed8',
                            800: '#1e40af',
                            900: '#1e3a8a',
                            950: '#172554'
                        },
                        'gray': {
                            50: '#f9fafb',
                            100: '#f3f4f6',
                            200: '#e5e7eb',
                            300: '#d1d5db',
                            400: '#9ca3af',
                            500: '#6b7280',
                            600: '#4b5563',
                            700: '#374151',
                            800: '#1f2937',
                            900: '#111827'
                        }
                    },
                    fontSize: {
                        'title-sm': ['1.5rem', '2rem'],
                        'title-md': ['1.875rem', '2.25rem'],
                        'title-xl': ['2.25rem', '2.5rem'],
                        'title-2xl': ['3rem', '1']
                    },
                    boxShadow: {
                        'theme-xs': '0 1px 2px 0 rgb(0 0 0 / 0.05)',
                        'theme-sm': '0 1px 3px 0 rgb(0 0 0 / 0.1), 0 1px 2px -1px rgb(0 0 0 / 0.1)',
                    }
                }
            },
            darkMode: 'class'
        };
    </script>
</head>
<body 
    x-data="{ 
        darkMode: false, 
        sidebarToggle: false, 
        loaded: true,
        currentThreadId: <?= $currentThread['id'] ?>,
        currentAgentId: <?= $selectedAgentId ? $selectedAgentId : 'null' ?>
    }"
    x-init="
        darkMode = JSON.parse(localStorage.getItem('darkMode') || 'false');
        $watch('darkMode', value => localStorage.setItem('darkMode', JSON.stringify(value)));
    "
    :class="{'dark bg-gray-900': darkMode === true}"
    class="bg-gray-50"
>
    <!-- Page Wrapper -->
    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar -->
        <aside
            :class="sidebarToggle ? 'translate-x-0 lg:w-[90px]' : '-translate-x-full'"
            class="fixed left-0 top-0 z-50 flex h-screen w-[290px] flex-col overflow-y-hidden border-r border-gray-200 bg-white px-5 duration-300 ease-linear dark:border-gray-800 dark:bg-black lg:static lg:translate-x-0"
            @click.outside="sidebarToggle = false"
        >
            <!-- Sidebar Header -->
            <div class="flex items-center justify-between gap-2 pb-7 pt-8">
                <a href="/chat" class="flex items-center">
                    <span class="text-xl font-bold text-gray-900 dark:text-white">OpenAI Chat</span>
                </a>
                <button 
                    @click="sidebarToggle = !sidebarToggle"
                    class="lg:hidden"
                >
                    <svg class="w-6 h-6 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <!-- New Thread Button -->
            <div class="mb-6">
                <button 
                    id="new-thread-btn"
                    class="flex w-full items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-3 text-sm font-medium text-white shadow-theme-xs hover:bg-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    New Chat
                </button>
            </div>

            <!-- Quick Agent Test -->
            <?php if (!empty($availableAgents)): ?>
            <div class="mb-6">
                <h3 class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-3">
                    Quick Agent Test
                </h3>
                <div class="space-y-2">
                    <?php foreach (array_slice($availableAgents, 0, 3) as $agent): ?>
                    <button 
                        onclick="testAgent(<?= $agent->getId() ?>)"
                        class="flex w-full items-center gap-3 rounded-lg border border-gray-200 bg-white p-3 text-left text-sm hover:bg-gray-50 hover:border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:hover:bg-gray-700 dark:hover:border-gray-600"
                    >
                        <div class="flex-shrink-0">
                            <div class="w-6 h-6 rounded-full bg-brand-100 dark:bg-brand-900 flex items-center justify-center">
                                <svg class="w-3 h-3 text-brand-600 dark:text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="font-medium text-gray-900 dark:text-white truncate">
                                <?= htmlspecialchars($agent->getName()) ?>
                            </p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                <?= count($agent->getTools()) ?> tools • Test now
                            </p>
                        </div>
                    </button>
                    <?php endforeach; ?>
                </div>
                <div class="mt-3">
                    <a href="/agents" class="text-xs text-brand-500 hover:text-brand-600 dark:text-brand-400">
                        View all agents →
                    </a>
                </div>
            </div>
            <?php endif; ?>

            <!-- Thread List -->
            <div class="flex-1 overflow-y-auto">
                <div class="mb-4">
                    <h3 class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-3">
                        Recent Conversations
                    </h3>
                </div>
                
                <div class="space-y-2">
                    <?php foreach ($threads as $thread): ?>
                    <div 
                        class="group cursor-pointer rounded-lg border border-transparent p-3 hover:bg-gray-50 hover:border-gray-200 dark:hover:bg-gray-800 dark:hover:border-gray-700 <?= $thread['id'] == $currentThread['id'] ? 'bg-brand-50 border-brand-200 dark:bg-brand-900/20 dark:border-brand-800' : '' ?>"
                        data-thread-id="<?= $thread['id'] ?>"
                        onclick="switchToThread(<?= $thread['id'] ?>)"
                    >
                        <div class="flex items-center gap-3">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 rounded-full bg-gray-100 dark:bg-gray-800 flex items-center justify-center">
                                    <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900 dark:text-white truncate">
                                    <?= htmlspecialchars($thread['title']) ?>
                                </p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    <?= $thread['message_count'] ?? 0 ?> messages • <?= date('M j', strtotime($thread['created_at'])) ?>
                                </p>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </aside>

        <!-- Main Content Area -->
        <div class="relative flex flex-1 flex-col overflow-y-auto overflow-x-hidden">
            <!-- Mobile Sidebar Toggle -->
            <button
                @click="sidebarToggle = !sidebarToggle"
                class="fixed top-4 left-4 z-40 lg:hidden rounded-lg bg-white p-2 shadow-theme-sm dark:bg-gray-800"
            >
                <svg class="w-6 h-6 text-gray-600 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                </svg>
            </button>

            <!-- Header -->
            <header class="border-b border-gray-200 bg-white px-4 py-4 dark:border-gray-800 dark:bg-gray-900 lg:px-6">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-brand-100 dark:bg-brand-900 flex items-center justify-center">
                                <svg class="w-5 h-5 text-brand-600 dark:text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                            <div>
                                <h1 class="text-lg font-semibold text-gray-900 dark:text-white">
                                    <?= htmlspecialchars($currentThread['title']) ?>
                                </h1>
                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                    AI-powered conversation
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-3">
                        <!-- Agent Selection -->
                        <?php if (!empty($availableAgents)): ?>
                        <div class="flex items-center gap-2">
                            <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Agent:</label>
                            <div class="relative">
                                <select 
                                    id="agent-select" 
                                    class="appearance-none rounded-lg border border-gray-300 bg-white px-3 py-2 pr-8 text-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300"
                                >
                                    <option value="">Default Assistant</option>
                                    <?php foreach ($availableAgents as $agent): ?>
                                    <option value="<?= $agent->getId() ?>" <?= $selectedAgentId == $agent->getId() ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($agent->getName()) ?>
                                        <?php if (!empty($agent->getTools())): ?>
                                            (<?= count($agent->getTools()) ?> tools)
                                        <?php endif; ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700 dark:text-gray-300">
                                    <svg class="h-4 w-4 fill-current" viewBox="0 0 20 20">
                                        <path d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"/>
                                    </svg>
                                </div>
                            </div>
                            <span id="agent-status" class="text-xs text-gray-500 dark:text-gray-400"></span>
                        </div>
                        <?php else: ?>
                        <div class="flex items-center gap-2">
                            <span class="text-sm text-gray-500 dark:text-gray-400">No agents available</span>
                            <a href="/agents" class="text-sm text-brand-500 hover:text-brand-600 dark:text-brand-400">Create one</a>
                        </div>
                        <?php endif; ?>

                        <!-- Dark Mode Toggle -->
                        <button
                            @click="darkMode = !darkMode"
                            class="rounded-lg bg-gray-100 p-2 text-gray-600 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700"
                        >
                            <svg x-show="!darkMode" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path>
                            </svg>
                            <svg x-show="darkMode" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path>
                            </svg>
                        </button>

                        <!-- Status Badge -->
                        <span class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800 dark:bg-green-900/20 dark:text-green-400">
                            <span class="mr-1.5 h-2 w-2 rounded-full bg-green-400 dark:bg-green-500"></span>
                            Online
                        </span>
                    </div>
                </div>
            </header>

            <!-- Messages Area -->
            <main class="flex-1 overflow-y-auto">
                <div class="mx-auto max-w-4xl px-4 py-6 lg:px-6">
                    <div class="space-y-6" id="messages-container">
                        <?php foreach ($messages as $message): ?>
                        <div class="flex items-start gap-4 <?= $message['role'] === 'user' ? 'flex-row-reverse' : '' ?>">
                            <!-- Avatar -->
                            <div class="flex-shrink-0">
                                <div class="w-10 h-10 rounded-full <?= $message['role'] === 'user' ? 'bg-brand-500' : 'bg-gray-500' ?> flex items-center justify-center">
                                    <?php if ($message['role'] === 'user'): ?>
                                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                        </svg>
                                    <?php else: ?>
                                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                        </svg>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Message Content -->
                            <div class="flex-1 max-w-3xl">
                                <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-theme-xs dark:border-gray-800 dark:bg-gray-800 <?= $message['role'] === 'user' ? 'bg-brand-50 border-brand-200 dark:bg-brand-900/20 dark:border-brand-800' : '' ?>">
                                    <div class="prose prose-sm max-w-none dark:prose-invert">
                                        <p class="text-gray-900 dark:text-gray-100 leading-relaxed">
                                            <?= nl2br(htmlspecialchars($message['content'])) ?>
                                        </p>
                                    </div>
                                </div>
                                <div class="mt-2 flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400 <?= $message['role'] === 'user' ? 'justify-end' : '' ?>">
                                    <span><?= $message['role'] === 'user' ? 'You' : 'Assistant' ?></span>
                                    <span>•</span>
                                    <span><?= date('g:i A', strtotime($message['created_at'])) ?></span>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </main>

            <!-- Message Input -->
            <footer class="border-t border-gray-200 bg-white px-4 py-4 dark:border-gray-800 dark:bg-gray-900 lg:px-6">
                <div class="mx-auto max-w-4xl">
                    <form id="message-form" class="flex items-end gap-3">
                        <div class="flex-1">
                            <div class="relative">
                                <textarea 
                                    id="message-input"
                                    rows="1"
                                    class="block w-full resize-none rounded-lg border border-gray-300 bg-white px-4 py-3 pr-12 text-sm text-gray-900 placeholder-gray-400 shadow-theme-xs focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:placeholder-gray-500"
                                    placeholder="Type your message..."
                                    style="min-height: 44px; max-height: 120px;"
                                ></textarea>
                                <button 
                                    type="submit"
                                    id="send-button"
                                    class="absolute bottom-2 right-2 rounded-lg bg-brand-500 p-2 text-white shadow-theme-xs hover:bg-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </footer>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const messageForm = document.getElementById('message-form');
            const messageInput = document.getElementById('message-input');
            const sendButton = document.getElementById('send-button');
            const messagesContainer = document.getElementById('messages-container');
            const newThreadBtn = document.getElementById('new-thread-btn');
            const agentSelect = document.getElementById('agent-select');
            
            let currentThreadId = <?= $currentThread['id'] ?>;
            let currentAgentId = <?= $selectedAgentId ? $selectedAgentId : 'null' ?>;
            
            // Auto-resize textarea
            messageInput.addEventListener('input', function() {
                this.style.height = 'auto';
                this.style.height = Math.min(this.scrollHeight, 120) + 'px';
            });
            
            // Send message on Enter (but allow Shift+Enter for new lines)
            messageInput.addEventListener('keydown', function(e) {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    messageForm.dispatchEvent(new Event('submit'));
                }
            });
            
            // Agent selection change
            if (agentSelect) {
                agentSelect.addEventListener('change', function() {
                    currentAgentId = this.value || null;
                    updateAgentStatus();
                });
                
                // Set initial status
                updateAgentStatus();
            }
            
            function updateAgentStatus() {
                const agentStatus = document.getElementById('agent-status');
                if (!agentStatus) return;
                
                if (currentAgentId) {
                    const selectedOption = agentSelect.querySelector(`option[value="${currentAgentId}"]`);
                    if (selectedOption) {
                        agentStatus.textContent = `Using: ${selectedOption.textContent.trim()}`;
                        agentStatus.className = 'text-xs text-brand-600 dark:text-brand-400 font-medium';
                    }
                } else {
                    agentStatus.textContent = 'Default OpenAI assistant';
                    agentStatus.className = 'text-xs text-gray-500 dark:text-gray-400';
                }
            }
            
            // Handle message submission
            messageForm.addEventListener('submit', async function(e) {
                e.preventDefault();
                
                const message = messageInput.value.trim();
                if (!message) return;
                
                // Disable form
                sendButton.disabled = true;
                messageInput.disabled = true;
                
                try {
                    // Add user message to UI immediately
                    addMessageToUI('user', message);
                    messageInput.value = '';
                    messageInput.style.height = 'auto';
                    
                    // Show typing indicator
                    showTypingIndicator();
                    
                    let url, requestBody;
                    
                    if (currentAgentId) {
                        url = `/api/agents/${currentAgentId}/run`;
                        requestBody = {
                            message: message,
                            threadId: currentThreadId
                        };
                    } else {
                        url = `/api/threads/${currentThreadId}/messages`;
                        requestBody = { message: message };
                    }
                    
                    const response = await fetch(url, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: JSON.stringify(requestBody)
                    });
                    
                    if (!response.ok) {
                        throw new Error(`HTTP ${response.status}`);
                    }
                    
                    const result = await response.json();
                    
                    hideTypingIndicator();
                    
                    if (result.response) {
                        addMessageToUI('assistant', result.response);
                    } else {
                        throw new Error('No response received');
                    }
                    
                } catch (error) {
                    hideTypingIndicator();
                    console.error('Error sending message:', error);
                    addMessageToUI('assistant', 'Sorry, I encountered an error processing your message. Please try again.');
                } finally {
                    sendButton.disabled = false;
                    messageInput.disabled = false;
                    messageInput.focus();
                }
            });
            
            // Add message to UI
            function addMessageToUI(role, content) {
                const messageDiv = document.createElement('div');
                messageDiv.className = `flex items-start gap-4 ${role === 'user' ? 'flex-row-reverse' : ''}`;
                
                const now = new Date();
                const timeString = now.toLocaleTimeString('en-US', { 
                    hour: 'numeric', 
                    minute: '2-digit',
                    hour12: true 
                });
                
                const avatarClass = role === 'user' ? 'bg-brand-500' : 'bg-gray-500';
                const messageClass = role === 'user' ? 'bg-brand-50 border-brand-200 dark:bg-brand-900/20 dark:border-brand-800' : '';
                const timeClass = role === 'user' ? 'justify-end' : '';
                
                const avatarIcon = role === 'user' 
                    ? '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>'
                    : '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>';
                
                messageDiv.innerHTML = `
                    <div class="flex-shrink-0">
                        <div class="w-10 h-10 rounded-full ${avatarClass} flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                ${avatarIcon}
                            </svg>
                        </div>
                    </div>
                    <div class="flex-1 max-w-3xl">
                        <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-theme-xs dark:border-gray-800 dark:bg-gray-800 ${messageClass}">
                            <div class="prose prose-sm max-w-none dark:prose-invert">
                                <p class="text-gray-900 dark:text-gray-100 leading-relaxed">
                                    ${content.replace(/\n/g, '<br>')}
                                </p>
                            </div>
                        </div>
                        <div class="mt-2 flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400 ${timeClass}">
                            <span>${role === 'user' ? 'You' : 'Assistant'}</span>
                            <span>•</span>
                            <span>${timeString}</span>
                        </div>
                    </div>
                `;
                
                messagesContainer.appendChild(messageDiv);
                messagesContainer.scrollTop = messagesContainer.scrollHeight;
            }
            
            // Show typing indicator
            function showTypingIndicator() {
                const existingIndicator = document.getElementById('typing-indicator');
                if (existingIndicator) {
                    existingIndicator.remove();
                }
                
                const typingDiv = document.createElement('div');
                typingDiv.id = 'typing-indicator';
                typingDiv.className = 'flex items-start gap-4';
                typingDiv.innerHTML = `
                    <div class="flex-shrink-0">
                        <div class="w-10 h-10 rounded-full bg-gray-500 flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="flex-1 max-w-3xl">
                        <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-theme-xs dark:border-gray-800 dark:bg-gray-800">
                            <div class="flex items-center space-x-1">
                                <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce"></div>
                                <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0.1s"></div>
                                <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0.2s"></div>
                            </div>
                        </div>
                    </div>
                `;
                
                messagesContainer.appendChild(typingDiv);
                messagesContainer.scrollTop = messagesContainer.scrollHeight;
            }
            
            function hideTypingIndicator() {
                const typingIndicator = document.getElementById('typing-indicator');
                if (typingIndicator) {
                    typingIndicator.remove();
                }
            }
            
            // New thread creation
            newThreadBtn.addEventListener('click', async function() {
                try {
                    const response = await fetch('/api/threads', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: JSON.stringify({ title: 'New Chat' })
                    });
                    
                    if (!response.ok) {
                        throw new Error('Failed to create thread');
                    }
                    
                    const newThread = await response.json();
                    window.location.href = `/chat?thread=${newThread.id}`;
                    
                } catch (error) {
                    console.error('Error creating thread:', error);
                }
            });
            
            // Focus message input on load
            messageInput.focus();
            
            // Scroll to bottom of messages
            if (messagesContainer) {
                messagesContainer.scrollTop = messagesContainer.scrollHeight;
            }
        });
        
        // Global function for thread switching
        function switchToThread(threadId) {
            window.location.href = `/chat?thread=${threadId}`;
        }
        
        // Global function for agent testing
        function testAgent(agentId) {
            // Update the agent selector
            const agentSelect = document.getElementById('agent-select');
            if (agentSelect) {
                agentSelect.value = agentId;
                currentAgentId = agentId;
                updateAgentStatus();
            }
            
            // Add a test message
            const testMessages = [
                "Hello! I'd like to test your capabilities.",
                "What tools do you have available?",
                "Can you help me with a quick task?",
                "Tell me about your specialties."
            ];
            
            const randomMessage = testMessages[Math.floor(Math.random() * testMessages.length)];
            messageInput.value = randomMessage;
            messageInput.focus();
            
            // Optionally auto-send the test message
            // messageForm.dispatchEvent(new Event('submit'));
        }
    </script>
</body>
</html>

<?php 
$content = ob_get_clean();
echo $content;
?>