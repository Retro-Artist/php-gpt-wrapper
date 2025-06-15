<?php 
$pageTitle = 'Chat - OpenAI Webchat';
$page = 'chat'; // For sidebar active state
ob_start(); 
?>

<!-- Chat Page with Consistent Design -->
<div 
    x-data="{ 
        currentThreadId: <?= $currentThread['id'] ?>,
        currentAgentId: <?= $selectedAgentId ? $selectedAgentId : 'null' ?>,
        isLoading: false,
        showThreadSidebar: true
    }"
    class="mx-auto max-w-(--breakpoint-2xl) p-4 md:p-6"
>
    <!-- Page Header -->
    <div class="mb-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-4">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-2xl bg-brand-100 dark:bg-brand-900/20 flex items-center justify-center shadow-theme-xs">
                        <svg class="w-6 h-6 text-brand-600 dark:text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-title-md font-bold text-gray-800 dark:text-white/90">
                            <?= htmlspecialchars($currentThread['title']) ?>
                        </h1>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            AI-powered conversation with intelligent agents
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="flex items-center gap-3">
                <!-- Thread Sidebar Toggle -->
                <button 
                    @click="showThreadSidebar = !showThreadSidebar"
                    class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200 lg:hidden"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>
                
                <!-- New Thread Button -->
                <button 
                    id="new-thread-btn"
                    class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200"
                >
                    <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    New Chat
                </button>
                
                <!-- Agent Selection -->
                <?php if (!empty($availableAgents)): ?>
                <div class="flex items-center gap-2">
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Agent:</label>
                    <div class="relative">
                        <select 
                            id="agent-select" 
                            class="appearance-none rounded-lg border border-gray-300 bg-white px-3 py-2 pr-8 text-sm shadow-theme-xs focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300"
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
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-4">
        <!-- Thread Sidebar -->
        <div 
            x-show="showThreadSidebar" 
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 transform -translate-x-4"
            x-transition:enter-end="opacity-100 transform translate-x-0"
            class="lg:col-span-1 space-y-6"
        >
            <!-- Recent Conversations Card -->
            <div class="rounded-2xl border border-gray-200 bg-white shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="flex items-center justify-between px-6 py-5">
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">
                        Conversations
                    </h3>
                    <span class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-800 dark:bg-gray-800 dark:text-gray-400">
                        <?= count($threads) ?>
                    </span>
                </div>
                <div class="border-t border-gray-100 dark:border-gray-800">
                    <?php if (empty($threads)): ?>
                        <div class="px-6 py-8 text-center">
                            <svg class="mx-auto h-8 w-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                            </svg>
                            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">No conversations yet</p>
                        </div>
                    <?php else: ?>
                        <div class="divide-y divide-gray-100 dark:divide-gray-800 max-h-96 overflow-y-auto custom-scrollbar">
                            <?php foreach ($threads as $thread): ?>
                            <div 
                                class="px-6 py-4 hover:bg-gray-50 dark:hover:bg-gray-800/50 cursor-pointer transition-colors <?= $thread['id'] == $currentThread['id'] ? 'bg-brand-50 dark:bg-brand-900/20' : '' ?>"
                                data-thread-id="<?= $thread['id'] ?>"
                                onclick="switchToThread(<?= $thread['id'] ?>)"
                            >
                                <div class="flex items-center space-x-3">
                                    <div class="flex-shrink-0">
                                        <div class="h-8 w-8 rounded-full bg-gray-100 dark:bg-gray-800 flex items-center justify-center">
                                            <svg class="h-4 w-4 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <p class="text-sm font-medium text-gray-900 dark:text-white/90 truncate">
                                            <?= htmlspecialchars($thread['title']) ?>
                                        </p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">
                                            <?= $thread['message_count'] ?? 0 ?> messages • <?= date('M j', strtotime($thread['created_at'])) ?>
                                        </p>
                                    </div>
                                    <?php if ($thread['id'] == $currentThread['id']): ?>
                                    <div class="flex-shrink-0">
                                        <div class="h-2 w-2 rounded-full bg-brand-500"></div>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Quick Agent Test Card -->
            <?php if (!empty($availableAgents)): ?>
            <div class="rounded-2xl border border-gray-200 bg-white shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="px-6 py-5">
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">
                        Quick Test
                    </h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Test your agents instantly
                    </p>
                </div>
                <div class="border-t border-gray-100 dark:border-gray-800 px-6 py-4">
                    <div class="space-y-3">
                        <?php foreach (array_slice($availableAgents, 0, 3) as $agent): ?>
                        <button 
                            onclick="testAgent(<?= $agent->getId() ?>)"
                            class="flex w-full items-center gap-3 rounded-lg border border-gray-200 bg-white p-3 text-left text-sm hover:bg-gray-50 hover:border-gray-300 transition-all dark:border-gray-700 dark:bg-gray-800 dark:hover:bg-gray-700 dark:hover:border-gray-600"
                        >
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 rounded-full bg-brand-100 dark:bg-brand-900/20 flex items-center justify-center">
                                    <svg class="w-4 h-4 text-brand-600 dark:text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="font-medium text-gray-900 dark:text-white truncate">
                                    <?= htmlspecialchars($agent->getName()) ?>
                                </p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    <?= count($agent->getTools()) ?> tools available
                                </p>
                            </div>
                        </button>
                        <?php endforeach; ?>
                    </div>
                    <div class="mt-4 pt-4 border-t border-gray-100 dark:border-gray-800">
                        <a href="/agents" class="text-sm font-medium text-brand-600 hover:text-brand-500 dark:text-brand-400">
                            Manage all agents →
                        </a>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Main Chat Area -->
        <div class="lg:col-span-3">
            <div class="rounded-2xl border border-gray-200 bg-white shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] h-[calc(100vh-200px)] flex flex-col">
                <!-- Chat Header -->
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-gray-800">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-brand-100 dark:bg-brand-900/20 flex items-center justify-center">
                            <svg class="w-5 h-5 text-brand-600 dark:text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-sm font-semibold text-gray-900 dark:text-white/90">
                                Active Conversation
                            </h2>
                            <p id="agent-status" class="text-xs text-gray-500 dark:text-gray-400">
                                Default assistant
                            </p>
                        </div>
                    </div>
                    
                    <div class="flex items-center gap-2">
                        <span class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800 dark:bg-green-900/20 dark:text-green-400">
                            <span class="mr-1.5 h-2 w-2 rounded-full bg-green-400 dark:bg-green-500"></span>
                            Online
                        </span>
                    </div>
                </div>

                <!-- Messages Area -->
                <div class="flex-1 overflow-y-auto p-6 space-y-6 custom-scrollbar" id="messages-container">
                    <?php if (empty($messages)): ?>
                        <div class="text-center py-12">
                            <div class="w-16 h-16 rounded-2xl bg-brand-100 dark:bg-brand-900/20 flex items-center justify-center mx-auto mb-4">
                                <svg class="w-8 h-8 text-brand-600 dark:text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                                </svg>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white/90 mb-2">
                                Start a conversation
                            </h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">
                                Send a message to begin chatting with your AI assistant
                            </p>
                            <div class="flex flex-wrap justify-center gap-2">
                                <button onclick="addSampleMessage('Hello! How can you help me today?')" class="inline-flex items-center rounded-lg bg-gray-100 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700">
                                    👋 Say hello
                                </button>
                                <button onclick="addSampleMessage('What can you do?')" class="inline-flex items-center rounded-lg bg-gray-100 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700">
                                    🤔 Ask about capabilities
                                </button>
                                <button onclick="addSampleMessage('Help me with a task')" class="inline-flex items-center rounded-lg bg-gray-100 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700">
                                    🚀 Get help
                                </button>
                            </div>
                        </div>
                    <?php else: ?>
                        <?php foreach ($messages as $message): ?>
                        <div class="flex items-start gap-4 <?= $message['role'] === 'user' ? 'flex-row-reverse' : '' ?>">
                            <!-- Avatar -->
                            <div class="flex-shrink-0">
                                <div class="w-10 h-10 rounded-full <?= $message['role'] === 'user' ? 'bg-brand-500' : 'bg-gray-500' ?> flex items-center justify-center shadow-theme-xs">
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
                                        <p class="text-gray-900 dark:text-gray-100 leading-relaxed m-0">
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
                    <?php endif; ?>
                </div>

                <!-- Message Input -->
                <div class="border-t border-gray-100 dark:border-gray-800 p-6">
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
                                    class="absolute bottom-2 right-2 rounded-lg bg-brand-500 p-2 text-white shadow-theme-xs hover:bg-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed transition-all"
                                    :disabled="isLoading"
                                >
                                    <svg x-show="!isLoading" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                                    </svg>
                                    <svg x-show="isLoading" class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
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
        
        // Set loading state
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
            
            // Show toast notification
            if (window.showToast) {
                window.showToast('error', 'Failed to send message');
            }
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
                <div class="w-10 h-10 rounded-full ${avatarClass} flex items-center justify-center shadow-theme-xs">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        ${avatarIcon}
                    </svg>
                </div>
            </div>
            <div class="flex-1 max-w-3xl">
                <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-theme-xs dark:border-gray-800 dark:bg-gray-800 ${messageClass}">
                    <div class="prose prose-sm max-w-none dark:prose-invert">
                        <p class="text-gray-900 dark:text-gray-100 leading-relaxed m-0">
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
                <div class="w-10 h-10 rounded-full bg-gray-500 flex items-center justify-center shadow-theme-xs">
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
            if (window.showToast) {
                window.showToast('error', 'Failed to create new thread');
            }
        }
    });
    
    // Focus message input on load
    messageInput.focus();
    
    // Scroll to bottom of messages
    if (messagesContainer) {
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }
});

// Global functions
function switchToThread(threadId) {
    window.location.href = `/chat?thread=${threadId}`;
}

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
    document.getElementById('message-input').value = randomMessage;
    document.getElementById('message-input').focus();
}

function addSampleMessage(message) {
    document.getElementById('message-input').value = message;
    document.getElementById('message-input').focus();
}
</script>

<?php 
$content = ob_get_clean();
include __DIR__ . '/layout.php';
?>