<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'OpenAI Webchat' ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <link rel="stylesheet" href="/assets/css/style.css">
    <script>
        // Suppress Tailwind production warning
        const originalWarn = console.warn;
        console.warn = function(...args) {
            const message = args[0] ? args[0].toString() : '';
            if (message.includes('tailwindcss.com should not be used in production')) {
                return;
            }
            originalWarn.apply(console, args);
        };

        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'brand': {
                            50: '#f0f9ff',
                            100: '#e0f2fe',
                            200: '#bae6fd',
                            300: '#7dd3fc',
                            400: '#38bdf8',
                            500: '#0ea5e9',
                            600: '#0284c7',
                            700: '#0369a1',
                            800: '#075985',
                            900: '#0c4a6e',
                            950: '#082f49'
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
                        },
                        'success': {
                            50: '#f0fdf4',
                            100: '#dcfce7',
                            200: '#bbf7d0',
                            300: '#86efac',
                            400: '#4ade80',
                            500: '#22c55e',
                            600: '#16a34a',
                            700: '#15803d',
                            800: '#166534',
                            900: '#14532d'
                        },
                        'warning': {
                            50: '#fffbeb',
                            100: '#fef3c7',
                            200: '#fde68a',
                            300: '#fcd34d',
                            400: '#fbbf24',
                            500: '#f59e0b',
                            600: '#d97706',
                            700: '#b45309',
                            800: '#92400e',
                            900: '#78350f'
                        },
                        'error': {
                            50: '#fef2f2',
                            100: '#fee2e2',
                            200: '#fecaca',
                            300: '#fca5a5',
                            400: '#f87171',
                            500: '#ef4444',
                            600: '#dc2626',
                            700: '#b91c1c',
                            800: '#991b1b',
                            900: '#7f1d1d'
                        }
                    },
                    fontSize: {
                        'theme-xs': ['0.75rem', '1rem'],
                        'theme-sm': ['0.875rem', '1.25rem'],
                        'theme-base': ['1rem', '1.5rem'],
                        'theme-lg': ['1.125rem', '1.75rem'],
                        'theme-xl': ['1.25rem', '1.75rem'],
                        'title-sm': ['1.5rem', '2rem'],
                        'title-md': ['1.875rem', '2.25rem'],
                        'title-lg': ['2.25rem', '2.5rem'],
                        'title-xl': ['3rem', '1'],
                        'title-2xl': ['3.75rem', '1']
                    },
                    boxShadow: {
                        'theme-xs': '0 1px 2px 0 rgb(0 0 0 / 0.05)',
                        'theme-sm': '0 1px 3px 0 rgb(0 0 0 / 0.1), 0 1px 2px -1px rgb(0 0 0 / 0.1)',
                        'theme-md': '0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1)',
                        'theme-lg': '0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1)',
                        'theme-xl': '0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1)',
                    },
                    animation: {
                        'fade-in': 'fadeIn 0.5s ease-in-out',
                        'slide-in': 'slideIn 0.3s ease-out',
                        'bounce-slow': 'bounce 2s infinite',
                    },
                    keyframes: {
                        fadeIn: {
                            '0%': {
                                opacity: '0'
                            },
                            '100%': {
                                opacity: '1'
                            },
                        },
                        slideIn: {
                            '0%': {
                                transform: 'translateX(-100%)'
                            },
                            '100%': {
                                transform: 'translateX(0)'
                            },
                        }
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
        scrollTop: false
    }"
    x-init="
        darkMode = JSON.parse(localStorage.getItem('darkMode') || 'false');
        $watch('darkMode', value => localStorage.setItem('darkMode', JSON.stringify(value)));
        
        // Handle scroll detection
        window.addEventListener('scroll', () => {
            scrollTop = window.pageYOffset > 0;
        });
    "
    :class="{'dark bg-gray-900': darkMode === true}"
    class="bg-gray-50 min-h-screen">
    <!-- Page Wrapper -->
    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar -->
        <?php if (isset($_SESSION['user_id'])): ?>
            <aside
                :class="sidebarToggle ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
                class="fixed left-0 top-0 z-50 flex h-screen w-[290px] flex-col overflow-y-hidden border-r border-gray-200 bg-white duration-300 ease-linear dark:border-gray-800 dark:bg-black lg:static lg:translate-x-0"
                @click.outside="sidebarToggle = false">
                <!-- Sidebar Header -->
                <div class="flex items-center justify-between gap-2 px-5 pb-7 pt-8">
                    <a href="/dashboard" class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-lg bg-brand-500 flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                            </svg>
                        </div>
                        <span class="text-xl font-bold text-gray-900 dark:text-white">OpenAI Chat</span>
                    </a>
                    <button
                        @click="sidebarToggle = !sidebarToggle"
                        class="lg:hidden p-1.5 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800">
                        <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <!-- Navigation -->
                <nav class="flex-1 px-5 py-4 overflow-y-auto">
                    <div class="space-y-2">
                        <!-- Menu Group -->
                        <div class="mb-6">
                            <h3 class="mb-3 text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                Menu
                            </h3>
                            <ul class="space-y-1">
                                <li>
                                    <a
                                        href="/dashboard"
                                        class="group flex items-center rounded-lg px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 hover:text-gray-900 dark:text-gray-300 dark:hover:bg-gray-800 dark:hover:text-white <?= isset($page) && $page === 'dashboard' ? 'bg-brand-50 text-brand-700 border-r-2 border-brand-500 dark:bg-brand-900/20 dark:text-brand-400' : '' ?>">
                                        <svg class="mr-3 h-5 w-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                        </svg>
                                        Dashboard
                                    </a>
                                </li>

                                <li>
                                    <a
                                        href="/agents"
                                        class="group flex items-center rounded-lg px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 hover:text-gray-900 dark:text-gray-300 dark:hover:bg-gray-800 dark:hover:text-white <?= isset($page) && $page === 'agents' ? 'bg-brand-50 text-brand-700 border-r-2 border-brand-500 dark:bg-brand-900/20 dark:text-brand-400' : '' ?>">
                                        <svg class="mr-3 h-5 w-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                        </svg>
                                        Agents
                                    </a>
                                </li>

                                <li>
                                    <a
                                        href="/chat"
                                        class="group flex items-center rounded-lg px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 hover:text-gray-900 dark:text-gray-300 dark:hover:bg-gray-800 dark:hover:text-white <?= isset($page) && $page === 'chat' ? 'bg-brand-50 text-brand-700 border-r-2 border-brand-500 dark:bg-brand-900/20 dark:text-brand-400' : '' ?>">
                                        <svg class="mr-3 h-5 w-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                                        </svg>
                                        Chat
                                    </a>
                                </li>
                            </ul>
                        </div>

                        <!-- Support Group -->
                        <div>
                            <h3 class="mb-3 text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                Account
                            </h3>
                            <ul class="space-y-1">
                                <li>
                                    <div class="flex items-center rounded-lg px-3 py-2 text-sm">
                                        <div class="mr-3 h-8 w-8 rounded-full bg-brand-100 dark:bg-brand-900 flex items-center justify-center">
                                            <svg class="h-4 w-4 text-brand-600 dark:text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                            </svg>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-medium text-gray-900 dark:text-white truncate">
                                                <?= htmlspecialchars($_SESSION['username'] ?? 'User') ?>
                                            </p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                                Online
                                            </p>
                                        </div>
                                    </div>
                                </li>
                                <li>
                                    <a
                                        href="/logout"
                                        class="group flex items-center rounded-lg px-3 py-2 text-sm font-medium text-red-700 hover:bg-red-50 hover:text-red-900 dark:text-red-400 dark:hover:bg-red-900/20">
                                        <svg class="mr-3 h-5 w-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                                        </svg>
                                        Logout
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </nav>
            </aside>
        <?php endif; ?>

        <!-- Main Content Area -->
        <div class="relative flex flex-1 flex-col overflow-y-auto overflow-x-hidden">
            <!-- Mobile Sidebar Toggle -->
            <?php if (isset($_SESSION['user_id'])): ?>
                <button
                    @click="sidebarToggle = !sidebarToggle"
                    class="fixed top-4 left-4 z-40 lg:hidden rounded-lg bg-white p-2 shadow-theme-sm dark:bg-gray-800">
                    <svg class="w-6 h-6 text-gray-600 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>
            <?php endif; ?>

            <!-- Header -->
            <?php if (isset($_SESSION['user_id'])): ?>
                <header
                    class="sticky top-0 z-30 border-b border-gray-200 bg-white/80 backdrop-blur-sm px-4 py-4 dark:border-gray-800 dark:bg-gray-900/80 lg:px-6"
                    :class="{'shadow-theme-sm': scrollTop}">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-4">
                            <h1 class="text-lg font-semibold text-gray-900 dark:text-white">
                                <?= $pageTitle ?? 'OpenAI Webchat' ?>
                            </h1>
                        </div>

                        <div class="flex items-center gap-3">
                            <!-- Dark Mode Toggle -->
                            <button
                                @click="darkMode = !darkMode"
                                class="rounded-lg bg-gray-100 p-2 text-gray-600 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 transition-colors"
                                title="Toggle dark mode">
                                <svg x-show="!darkMode" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path>
                                </svg>
                                <svg x-show="darkMode" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path>
                                </svg>
                            </button>

                            <!-- Status Indicator -->
                            <div class="flex items-center gap-2">
                                <div class="h-2 w-2 rounded-full bg-green-400"></div>
                                <span class="text-sm text-gray-600 dark:text-gray-400">Online</span>
                            </div>
                        </div>
                    </div>
                </header>
            <?php endif; ?>

            <!-- Main Content -->
            <main class="flex-1 <?= isset($_SESSION['user_id']) ? 'p-4 lg:p-6' : '' ?>">
                <?= $content ?? '' ?>
            </main>
        </div>
    </div>

    <!-- Loading Overlay -->
    <div
        x-show="!loaded"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        class="fixed inset-0 z-50 flex items-center justify-center bg-white dark:bg-gray-900">
        <div class="flex flex-col items-center gap-4">
            <div class="w-12 h-12 border-4 border-brand-200 border-t-brand-500 rounded-full animate-spin dark:border-gray-700 dark:border-t-brand-400"></div>
            <p class="text-sm text-gray-600 dark:text-gray-400">Loading...</p>
        </div>
    </div>

<!-- Toast Notifications Container - FIXED VERSION -->
<div 
    id="toast-container" 
    class="fixed top-4 right-4 z-50 space-y-3 pointer-events-none"
    x-data="{ toasts: [] }"
    @show-toast.window="toasts.push({id: Date.now(), type: $event.detail.type, message: $event.detail.message}); setTimeout(() => toasts.shift(), 5000)"
>
    <template x-for="toast in toasts" :key="toast.id">
        <div 
            x-show="true"
            x-transition:enter="transition ease-out duration-300 transform"
            x-transition:enter-start="translate-x-full opacity-0"
            x-transition:enter-end="translate-x-0 opacity-100"
            x-transition:leave="transition ease-in duration-200 transform"
            x-transition:leave-start="translate-x-0 opacity-100"
            x-transition:leave-end="translate-x-full opacity-0"
            class="w-80 bg-white shadow-theme-lg rounded-lg pointer-events-auto ring-1 ring-black ring-opacity-5 overflow-hidden dark:bg-gray-800"
        >
            <div class="p-4">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <svg x-show="toast.type === 'success'" class="h-5 w-5 text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <svg x-show="toast.type === 'error'" class="h-5 w-5 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <svg x-show="toast.type === 'warning'" class="h-5 w-5 text-yellow-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                        </svg>
                    </div>
                    <div class="ml-3 w-0 flex-1">
                        <p class="text-sm font-medium text-gray-900 dark:text-white leading-5" x-text="toast.message"></p>
                    </div>
                    <div class="ml-4 flex-shrink-0 flex">
                        <button 
                            @click="toasts = toasts.filter(t => t.id !== toast.id)"
                            class="bg-white rounded-md inline-flex text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-500 dark:bg-gray-800 dark:hover:text-gray-300"
                        >
                            <span class="sr-only">Close</span>
                            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </template>
</div>

    <!-- Global JavaScript utilities -->
    <script>
        // Global toast function
        window.showToast = function(type, message) {
            window.dispatchEvent(new CustomEvent('show-toast', {
                detail: {
                    type,
                    message
                }
            }));
        };

        // Global loading state
        window.setLoading = function(loading) {
            const event = new CustomEvent('set-loading', {
                detail: {
                    loading
                }
            });
            window.dispatchEvent(event);
        };

        // Enhanced fetch with error handling
        window.apiRequest = async function(url, options = {}) {
            const defaultOptions = {
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            };

            try {
                const response = await fetch(url, {
                    ...defaultOptions,
                    ...options,
                    headers: {
                        ...defaultOptions.headers,
                        ...options.headers
                    }
                });

                if (!response.ok) {
                    const errorText = await response.text();
                    throw new Error(`HTTP ${response.status}: ${errorText}`);
                }

                return await response.json();
            } catch (error) {
                console.error('API Request failed:', error);
                showToast('error', error.message || 'Request failed');
                throw error;
            }
        };
    </script>
</body>

</html>