<?php
// src/Web/Views/chat.php - Refactored with Alpine.js

$pageTitle = 'Chat - OpenAI Webchat';
$page = 'chat';
ob_start();

// Get messages from the current thread
$messages = !empty($currentThread) ? Thread::getMessages($currentThread['id']) : [];
?>

<!-- Chat Page with Alpine.js Components -->
<div
    x-data="{
        init() {
            // Initialize chat store with server data
            $store.chat.init(
                <?= $currentThread['id'] ?? 'null' ?>,
                <?= $selectedAgentId ? $selectedAgentId : 'null' ?>,
                <?= count($messages) ?>
            );
            
            // Initialize developer mode metadata badges
            $store.developer.updateMetadataBadges();
        }
    }"
    class="mx-auto max-w-(--breakpoint-2xl) p-4 md:p-6">

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
                            <?= htmlspecialchars($currentThread['title'] ?? 'New Chat') ?>
                        </h1>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            <span x-text="$store.chat.messageCount"></span> messages • AI-powered conversation
                        </p>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <!-- Thread Sidebar Toggle -->
                <button
                    @click="$store.chat.toggleSidebar()"
                    class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200 lg:hidden">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>

                <!-- New Thread Button -->
                <div x-data="threadManager()">
                    <button
                        @click="createNewThread()"
                        class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200">
                        <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        New Chat
                    </button>
                </div>

                <!-- Agent Selection -->
                <?php if (!empty($availableAgents)): ?>
                    <div x-data="agentSelector()" class="flex items-center gap-2">
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Agent:</label>
                        <div class="relative">
                            <select
                                x-ref="agentSelect"
                                x-model="selectedAgentId"
                                @change="selectAgent()"
                                class="appearance-none rounded-lg border border-gray-300 bg-white px-3 py-2 pr-8 text-sm shadow-theme-xs focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
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
                                    <path d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" />
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
            x-show="$store.chat.showThreadSidebar"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 transform -translate-x-4"
            x-transition:enter-end="opacity-100 transform translate-x-0"
            class="lg:col-span-1 space-y-6">

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
                        <div class="divide-y divide-gray-100 dark:divide-gray-800 max-h-96 overflow-y-auto custom-scrollbar" x-data="threadManager()">
                            <?php foreach ($threads as $thread): ?>
                                <div
                                    class="px-6 py-4 hover:bg-gray-50 dark:hover:bg-gray-800/50 cursor-pointer transition-colors <?= $thread['id'] == ($currentThread['id'] ?? 0) ? 'bg-brand-50 dark:bg-brand-900/20' : '' ?>"
                                    @click="switchToThread(<?= $thread['id'] ?>)">
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
                                                <?= $thread['message_count'] ?? 0 ?> messages
                                                <?php if (isset($thread['last_message_at'])): ?>
                                                    • <?= date('M j', strtotime($thread['last_message_at'])) ?>
                                                <?php endif; ?>
                                            </p>
                                        </div>
                                        <?php if ($thread['id'] == ($currentThread['id'] ?? 0)): ?>
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
                <div class="rounded-2xl border border-gray-200 bg-white shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]" x-data="threadManager()">
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
                                    @click="testAgent(<?= $agent->getId() ?>)"
                                    class="flex w-full items-center gap-3 rounded-lg border border-gray-200 bg-white p-3 text-left text-sm hover:bg-gray-50 hover:border-gray-300 transition-all dark:border-gray-700 dark:bg-gray-800 dark:hover:bg-gray-700 dark:hover:border-gray-600">
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
                        <!-- Online Status -->
                        <span class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800 dark:bg-green-900/20 dark:text-green-400">
                            <span class="mr-1.5 h-2 w-2 rounded-full bg-green-400 dark:bg-green-500"></span>
                            Online
                        </span>

                        <!-- Developer Mode Toggle -->
                        <div x-data="developerMode()" class="flex items-center gap-1.5">
                            <span class="text-xs font-medium text-gray-600 dark:text-gray-400">Dev</span>
                            <button
                                @click="toggle()"
                                type="button"
                                :class="toggleClasses"
                                class="relative inline-flex h-4 w-7 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-1"
                                role="switch"
                                :aria-checked="isEnabled">
                                <span class="sr-only">Enable developer mode</span>
                                <span
                                    :class="indicatorClasses"
                                    class="pointer-events-none inline-block h-3 w-3 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"></span>
                            </button>
                            <svg :class="iconClasses" class="w-3 h-3 transition-colors duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"></path>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Messages Area -->
                <div class="flex-1 overflow-y-auto p-6 space-y-6 custom-scrollbar" id="messages-container">
                    <?php if (empty($messages)): ?>
                        <div class="text-center py-12" x-data="chatMessage()">
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
                                <button @click="message = 'Hello! How can you help me today?'; $refs.messageInput?.focus()" class="inline-flex items-center rounded-lg bg-gray-100 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700">
                                    👋 Say hello
                                </button>
                                <button @click="message = 'What can you do?'; $refs.messageInput?.focus()" class="inline-flex items-center rounded-lg bg-gray-100 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700">
                                    🤔 Ask about capabilities
                                </button>
                                <button @click="message = 'Help me with a task'; $refs.messageInput?.focus()" class="inline-flex items-center rounded-lg bg-gray-100 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700">
                                    🚀 Get help
                                </button>
                            </div>
                        </div>
                    <?php else: ?>
                        <?php foreach ($messages as $message): ?>
                            <?php
                            // Skip system messages in the UI
                            if ($message['role'] === 'system') continue;

                            // Get timestamp - handle both old and new format
                            $timestamp = '';
                            if (isset($message['timestamp'])) {
                                $timestamp = date('g:i A', strtotime($message['timestamp']));
                            } elseif (isset($message['created_at'])) {
                                $timestamp = date('g:i A', strtotime($message['created_at']));
                            }
                            ?>
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

                                        <!-- Message Metadata -->
                                        <?php if (isset($message['agent_name']) || isset($message['model']) || isset($message['tools_called']) || isset($message['token_usage'])): ?>
                                            <div class="message-metadata mt-2 pt-2 border-t border-gray-100 dark:border-gray-700" 
                                                 x-show="$store.developer.isEnabled"
                                                 x-transition>
                                                <div class="flex flex-wrap gap-2 text-xs text-gray-500 dark:text-gray-400">

                                                    <!-- Agent name badge -->
                                                    <?php if (isset($message['agent_name'])): ?>
                                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-brand-100 dark:bg-brand-900/20 rounded text-brand-700 dark:text-brand-300">
                                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                                            </svg>
                                                            <?= htmlspecialchars($message['agent_name']) ?>
                                                        </span>
                                                    <?php endif; ?>

                                                    <!-- Model badge -->
                                                    <?php if (isset($message['model'])): ?>
                                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-gray-100 dark:bg-gray-800 rounded">
                                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"></path>
                                                            </svg>
                                                            <?= htmlspecialchars($message['model']) ?>
                                                        </span>
                                                    <?php endif; ?>

                                                    <!-- Additional metadata badges can be added here -->
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="mt-2 flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400 <?= $message['role'] === 'user' ? 'justify-end' : '' ?>">
                                        <span><?= $message['role'] === 'user' ? 'You' : 'Assistant' ?></span>
                                        <?php if ($timestamp): ?>
                                            <span>•</span>
                                            <span><?= $timestamp ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <!-- Message Input -->
                <div class="border-t border-gray-100 dark:border-gray-800 p-4">
                    <div x-data="chatMessage()">
                        <form @submit.prevent="sendMessage()" class="flex items-end gap-2">
                            <!-- Attachment Button -->
                            <div x-data="attachmentMenu()" class="flex-shrink-0 relative">
                                <button
                                    @click="toggleMenu()"
                                    @click.outside="closeMenu()"
                                    type="button"
                                    class="w-10 h-10 rounded-full bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 flex items-center justify-center text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                    </svg>
                                </button>

                                <!-- Attachment Menu -->
                                <div x-show="showMenu" 
                                     x-transition:enter="transition ease-out duration-100"
                                     x-transition:enter-start="opacity-0 scale-95"
                                     x-transition:enter-end="opacity-100 scale-100"
                                     x-transition:leave="transition ease-in duration-75"
                                     x-transition:leave-start="opacity-100 scale-100"
                                     x-transition:leave-end="opacity-0 scale-95"
                                     class="absolute bottom-12 left-0 bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 py-2 z-50 min-w-48">
                                    <button @click="handleAttachment('document')" class="w-full px-4 py-2 text-left text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center gap-3">
                                        <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                        </svg>
                                        <span>Document</span>
                                    </button>
                                    <button @click="handleAttachment('image')" class="w-full px-4 py-2 text-left text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center gap-3">
                                        <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                        </svg>
                                        <span>Photograph</span>
                                    </button>
                                    <button @click="handleAttachment('camera')" class="w-full px-4 py-2 text-left text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center gap-3">
                                        <svg class="w-4 h-4 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        </svg>
                                        <span>Camera</span>
                                    </button>
                                </div>
                            </div>

                            <!-- Message Input Container -->
                            <div class="flex-1 relative">
                                <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm focus-within:border-brand-500 focus-within:ring-2 focus-within:ring-brand-500/20 transition-all duration-200">
                                    <textarea
                                        x-ref="messageInput"
                                        x-model="message"
                                        @input="autoResize()"
                                        @keydown="handleKeyDown($event)"
                                        :disabled="$store.chat.isLoading"
                                        rows="1"
                                        class="block w-full resize-none bg-transparent px-4 py-3 text-sm text-gray-900 dark:text-gray-100 placeholder-gray-500 dark:placeholder-gray-400 border-0 focus:outline-none focus:ring-0"
                                        placeholder="Type a message..."
                                        style="min-height: 44px; max-height: 120px;"></textarea>
                                </div>
                            </div>

                            <!-- Audio/Send Button -->
                            <div class="flex-shrink-0">
                                <button
                                    x-ref="audioButton"
                                    @click="handleAudioButton()"
                                    :disabled="$store.chat.isLoading"
                                    type="button"
                                    class="w-10 h-10 rounded-full bg-brand-500 hover:bg-brand-600 flex items-center justify-center text-white transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2 shadow-sm disabled:opacity-50 disabled:cursor-not-allowed">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"></path>
                                    </svg>
                                </button>
                            </div>
                        </form>

                        <!-- Typing Indicator -->
                        <div x-show="$store.chat.isLoading" 
                             x-transition:enter="transition ease-out duration-300"
                             x-transition:enter-start="opacity-0 transform translate-y-2"
                             x-transition:enter-end="opacity-100 transform translate-y-0"
                             class="mt-2 px-4 text-xs text-gray-500 dark:text-gray-400">
                            <span class="flex items-center gap-1">
                                <div class="flex space-x-1">
                                    <div class="w-1 h-1 bg-gray-400 rounded-full animate-bounce"></div>
                                    <div class="w-1 h-1 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0.1s"></div>
                                    <div class="w-1 h-1 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0.2s"></div>
                                </div>
                              
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>



<!-- Load Alpine.js Chat Components -->
<script>
    // public/assets/js/chat-components.js
// Alpine.js components for chat functionality

document.addEventListener('alpine:init', () => {
    // Chat State Store
    Alpine.store('chat', {
        currentThreadId: null,
        currentAgentId: null,
        isLoading: false,
        messageCount: 0,
        showThreadSidebar: true,
        isRecording: false,
        
        init(threadId, agentId, messageCount) {
            this.currentThreadId = threadId;
            this.currentAgentId = agentId;
            this.messageCount = messageCount;
        },
        
        setAgent(agentId) {
            this.currentAgentId = agentId;
        },
        
        toggleSidebar() {
            this.showThreadSidebar = !this.showThreadSidebar;
        },
        
        incrementMessageCount() {
            this.messageCount += 2; // User + Assistant
        }
    });

    // Developer Mode Store
    Alpine.store('developer', {
        isEnabled: localStorage.getItem('developerMode') === 'true',
        
        toggle() {
            this.isEnabled = !this.isEnabled;
            localStorage.setItem('developerMode', this.isEnabled.toString());
            this.updateMetadataBadges();
        },
        
        updateMetadataBadges() {
            const metadataElements = document.querySelectorAll('.message-metadata');
            metadataElements.forEach(element => {
                if (this.isEnabled) {
                    element.classList.remove('hidden');
                } else {
                    element.classList.add('hidden');
                }
            });
        }
    });

    // Chat Message Component
    Alpine.data('chatMessage', () => ({
        message: '',
        
        init() {
            this.$nextTick(() => {
                this.$refs.messageInput?.focus();
                this.scrollToBottom();
                this.autoResize();
            });
        },
        
        async sendMessage() {
            const message = this.message.trim();
            if (!message) return;
            
            const store = Alpine.store('chat');
            store.isLoading = true;
            
            try {
                // Add user message to UI
                this.addMessageToUI('user', message);
                this.message = '';
                this.resetInputHeight();
                this.showTypingIndicator();
                
                // Determine API endpoint
                let url, requestBody;
                if (store.currentAgentId) {
                    url = `/api/agents/${store.currentAgentId}/run`;
                    requestBody = {
                        message: message,
                        threadId: store.currentThreadId
                    };
                } else {
                    url = `/api/threads/${store.currentThreadId}/messages`;
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
                
                if (!response.ok) throw new Error(`HTTP ${response.status}`);
                
                const result = await response.json();
                this.hideTypingIndicator();
                
                if (result.response) {
                    const metadata = this.extractMetadata(result);
                    this.addMessageToUI('assistant', result.response, metadata);
                    store.incrementMessageCount();
                } else {
                    throw new Error('No response received');
                }
                
            } catch (error) {
                this.hideTypingIndicator();
                console.error('Error sending message:', error);
                this.addMessageToUI('assistant', 'Sorry, I encountered an error processing your message. Please try again.');
                this.showToast('error', 'Failed to send message');
            } finally {
                store.isLoading = false;
                this.$refs.messageInput?.focus();
            }
        },
        
        handleKeyDown(event) {
            if (event.key === 'Enter' && !event.shiftKey) {
                event.preventDefault();
                this.sendMessage();
            }
        },
        
        autoResize() {
            const input = this.$refs.messageInput;
            if (!input) return;
            
            input.style.height = 'auto';
            const newHeight = Math.min(input.scrollHeight, 120);
            input.style.height = newHeight + 'px';
            
            this.updateAudioButton();
        },
        
        resetInputHeight() {
            const input = this.$refs.messageInput;
            if (input) {
                input.style.height = 'auto';
                this.updateAudioButton();
            }
        },
        
        updateAudioButton() {
            const hasText = this.message.trim().length > 0;
            const button = this.$refs.audioButton;
            if (!button) return;
            
            if (hasText) {
                this.transformToSendButton(button);
            } else {
                this.resetAudioButton(button);
            }
        },
        
        transformToSendButton(button) {
            button.classList.remove('bg-brand-500', 'hover:bg-brand-600');
            button.classList.add('bg-green-500', 'hover:bg-green-600');
            button.title = 'Send message (Enter)';
            button.innerHTML = `
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                </svg>
            `;
        },
        
        resetAudioButton(button) {
            button.classList.remove('bg-green-500', 'hover:bg-green-600', 'bg-red-500', 'hover:bg-red-600', 'animate-pulse');
            button.classList.add('bg-brand-500', 'hover:bg-brand-600');
            button.title = 'Send audio message';
            button.innerHTML = `
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"></path>
                </svg>
            `;
        },
        
        handleAudioButton() {
            const hasText = this.message.trim().length > 0;
            
            if (hasText) {
                this.sendMessage();
            } else {
                this.handleAudioRecording();
            }
        },
        
        handleAudioRecording() {
            const store = Alpine.store('chat');
            
            if (!store.isRecording) {
                store.isRecording = true;
                this.startRecordingUI();
                
                // Auto-stop after 5 seconds
                setTimeout(() => {
                    if (store.isRecording) {
                        this.stopRecording();
                    }
                }, 5000);
            } else {
                this.stopRecording();
            }
        },
        
        startRecordingUI() {
            const button = this.$refs.audioButton;
            if (!button) return;
            
            button.classList.remove('bg-brand-500', 'hover:bg-brand-600');
            button.classList.add('bg-red-500', 'hover:bg-red-600', 'animate-pulse');
            button.title = 'Stop recording';
            button.innerHTML = `
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                    <rect x="6" y="6" width="12" height="12" rx="2"/>
                </svg>
            `;
            
            this.showRecordingStatus();
        },
        
        stopRecording() {
            const store = Alpine.store('chat');
            store.isRecording = false;
            
            this.resetAudioButton(this.$refs.audioButton);
            this.hideRecordingStatus();
            this.showToast('info', 'Audio message feature coming soon!');
        },
        
        showRecordingStatus() {
            // Implementation for showing recording status
        },
        
        hideRecordingStatus() {
            // Implementation for hiding recording status
        },
        
        extractMetadata(result) {
            const metadata = {};
            if (result.agent_name) metadata.agent_name = result.agent_name;
            if (result.model) metadata.model = result.model;
            if (result.model_used) metadata.model_used = result.model_used;
            if (result.tools_used) metadata.tools_used = result.tools_used;
            if (result.token_usage) metadata.token_usage = result.token_usage;
            if (result.agent_id) metadata.agent_id = result.agent_id;
            if (result.tools_available) metadata.tools_available = result.tools_available;
            if (result.run_id) metadata.run_id = result.run_id;
            if (result.execution_duration_ms) metadata.execution_duration_ms = result.execution_duration_ms;
            return metadata;
        },
        
        addMessageToUI(role, content, metadata = null) {
            // Message UI creation logic - this would be moved to a separate method
            // or handled by Alpine.js templating
            window.chatUI.addMessage(role, content, metadata);
        },
        
        showTypingIndicator() {
            window.chatUI.showTypingIndicator();
        },
        
        hideTypingIndicator() {
            window.chatUI.hideTypingIndicator();
        },
        
        scrollToBottom() {
            const container = document.getElementById('messages-container');
            if (container) {
                container.scrollTop = container.scrollHeight;
            }
        },
        
        showToast(type, message) {
            if (window.showToast) {
                window.showToast(type, message);
            }
        }
    }));

    // Agent Selection Component
    Alpine.data('agentSelector', () => ({
        selectedAgentId: null,
        
        init() {
            this.selectedAgentId = Alpine.store('chat').currentAgentId;
        },
        
        selectAgent() {
            const store = Alpine.store('chat');
            store.setAgent(this.selectedAgentId);
            this.updateAgentStatus();
        },
        
        updateAgentStatus() {
            const agentStatus = document.getElementById('agent-status');
            if (!agentStatus) return;
            
            if (this.selectedAgentId) {
                const selectedOption = this.$refs.agentSelect?.querySelector(`option[value="${this.selectedAgentId}"]`);
                if (selectedOption) {
                    agentStatus.textContent = `Using agent: ${selectedOption.textContent}`;
                    agentStatus.className = 'text-xs text-brand-600 dark:text-brand-400';
                }
            } else {
                agentStatus.textContent = 'Default OpenAI assistant';
                agentStatus.className = 'text-xs text-gray-500 dark:text-gray-400';
            }
        }
    }));

    // Attachment Menu Component
    Alpine.data('attachmentMenu', () => ({
        showMenu: false,
        
        toggleMenu() {
            this.showMenu = !this.showMenu;
        },
        
        closeMenu() {
            this.showMenu = false;
        },
        
        handleAttachment(type) {
            let message = '';
            switch (type) {
                case 'document':
                    message = '📄 Document upload feature coming soon! You\'ll be able to upload PDFs, Word docs, and more for AI analysis.';
                    break;
                case 'image':
                    message = '🖼️ Image & video upload coming soon! Share visual content for AI analysis and discussion.';
                    break;
                case 'camera':
                    message = '📸 Camera feature coming soon! Take photos directly in the chat for instant AI analysis.';
                    break;
            }
            
            if (window.showToast) {
                window.showToast('info', message);
            }
            
            this.closeMenu();
        }
    }));

    // Thread Management Component
    Alpine.data('threadManager', () => ({
        async createNewThread() {
            try {
                const response = await fetch('/api/threads', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({
                        title: 'New Chat'
                    })
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
        },
        
        switchToThread(threadId) {
            window.location.href = `/chat?thread=${threadId}`;
        },
        
        testAgent(agentId) {
            const store = Alpine.store('chat');
            store.setAgent(agentId);
            
            // Update the agent selector
            const agentSelect = document.getElementById('agent-select');
            if (agentSelect) {
                agentSelect.value = agentId;
            }
            
            // Add a test message
            const testMessages = [
                "Hello! I'd like to test your capabilities.",
                "What tools do you have available?",
                "Can you help me with a quick task?",
                "Tell me about your specialties."
            ];
            
            const randomMessage = testMessages[Math.floor(Math.random() * testMessages.length)];
            const messageInput = document.getElementById('message-input');
            if (messageInput) {
                messageInput.value = randomMessage;
                messageInput.focus();
            }
        }
    }));

    // Developer Mode Component
    Alpine.data('developerMode', () => ({
        get isEnabled() {
            return Alpine.store('developer').isEnabled;
        },
        
        toggle() {
            Alpine.store('developer').toggle();
        },
        
        get toggleClasses() {
            return this.isEnabled 
                ? 'bg-green-500' 
                : 'bg-gray-200 dark:bg-gray-700';
        },
        
        get indicatorClasses() {
            return this.isEnabled 
                ? 'translate-x-3' 
                : 'translate-x-0';
        },
        
        get iconClasses() {
            return this.isEnabled 
                ? 'text-green-500' 
                : 'text-gray-400';
        }
    }));
});

// Chat UI Helper Class
class ChatUI {
    constructor() {
        this.messagesContainer = document.getElementById('messages-container');
    }
    
    addMessage(role, content, metadata = null) {
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
        
        const avatarIcon = role === 'user' ?
            `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>` :
            `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>`;
        
        const metadataBadgesHtml = this.buildMetadataBadges(metadata, role);
        
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
                        ${this.formatMessageContent(content)}
                    </div>
                    ${metadataBadgesHtml}
                </div>
                <div class="mt-1 flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400 ${timeClass}">
                    <span>${role === 'user' ? 'You' : 'Assistant'}</span>
                    <span>•</span>
                    <span>${timeString}</span>
                </div>
            </div>
        `;
        
        if (this.messagesContainer) {
            this.messagesContainer.appendChild(messageDiv);
            this.messagesContainer.scrollTop = this.messagesContainer.scrollHeight;
        }
    }
    
    buildMetadataBadges(metadata, role) {
        if (!metadata || role !== 'assistant') return '';
        
        const badges = [];
        const developer = Alpine.store('developer');
        const hiddenClass = developer && !developer.isEnabled ? 'hidden' : '';
        
        // Agent name badge
        if (metadata.agent_name) {
            badges.push(`
                <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-brand-100 dark:bg-brand-900/20 rounded text-brand-700 dark:text-brand-300">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                    </svg>
                    ${this.escapeHtml(metadata.agent_name)}
                </span>
            `);
        }
        
        // Additional badge logic here...
        
        if (badges.length === 0) return '';
        
        return `
            <div class="message-metadata mt-2 pt-2 border-t border-gray-100 dark:border-gray-700 ${hiddenClass}">
                <div class="flex flex-wrap gap-2 text-xs text-gray-500 dark:text-gray-400">
                    ${badges.join('')}
                </div>
            </div>
        `;
    }
    
    formatMessageContent(content) {
        return content.replace(/\n/g, '<br>');
    }
    
    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    showTypingIndicator() {
        this.hideTypingIndicator(); // Remove existing
        
        const typingDiv = document.createElement('div');
        typingDiv.id = 'typing-indicator';
        typingDiv.className = 'flex items-start gap-4';
        typingDiv.innerHTML = `
            <div class="flex-shrink-0">
                <div class="w-10 h-10 rounded-full bg-gray-500 flex items-center justify-center shadow-theme-xs">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
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
        
        if (this.messagesContainer) {
            this.messagesContainer.appendChild(typingDiv);
            this.messagesContainer.scrollTop = this.messagesContainer.scrollHeight;
        }
    }
    
    hideTypingIndicator() {
        const indicator = document.getElementById('typing-indicator');
        if (indicator) {
            indicator.remove();
        }
    }
}

// Initialize ChatUI globally
window.chatUI = new ChatUI();
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/layout.php';
?>