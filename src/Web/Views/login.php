<?php 
$pageTitle = 'Login - OpenAI Webchat';
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
                        }
                    },
                    fontSize: {
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
        showPassword: false,
        isLoading: false
    }"
    x-init="
        darkMode = JSON.parse(localStorage.getItem('darkMode') || 'false');
        $watch('darkMode', value => localStorage.setItem('darkMode', JSON.stringify(value)));
    "
    :class="{'dark': darkMode === true}"
    class="bg-gray-50 dark:bg-gray-900 min-h-screen"
>
    <!-- Background Pattern -->
    <div class="absolute inset-0 bg-grid-gray-900/[0.04] bg-[size:20px_20px] dark:bg-grid-white/[0.04]"></div>
    
    <!-- Header -->
    <header class="relative z-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex items-center justify-between">
                <!-- Logo -->
                <a href="/" class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-brand-500 flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                        </svg>
                    </div>
                    <span class="text-xl font-bold text-gray-900 dark:text-white">OpenAI Webchat</span>
                </a>

                <!-- Dark Mode Toggle -->
                <button
                    @click="darkMode = !darkMode"
                    class="rounded-lg bg-white/80 backdrop-blur-sm p-2 text-gray-600 hover:bg-white hover:text-gray-700 dark:bg-gray-800/80 dark:text-gray-300 dark:hover:bg-gray-800 dark:hover:text-gray-200 transition-colors shadow-theme-sm border border-gray-200/50 dark:border-gray-700/50"
                    title="Toggle dark mode"
                >
                    <svg x-show="!darkMode" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path>
                    </svg>
                    <svg x-show="darkMode" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path>
                    </svg>
                </button>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="relative z-10 flex-1 flex items-center justify-center px-4 sm:px-6 lg:px-8">
        <div class="w-full max-w-md">
            <!-- Card Container -->
            <div class="bg-white/80 backdrop-blur-sm dark:bg-gray-900/80 rounded-2xl shadow-theme-xl border border-gray-200/50 dark:border-gray-700/50 p-8">
                <!-- Header -->
                <div class="text-center mb-8">
                    <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-brand-100 dark:bg-brand-900/20 mb-6">
                        <svg class="h-8 w-8 text-brand-600 dark:text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                    </div>
                    
                    <h1 class="text-title-md font-bold text-gray-900 dark:text-white mb-2">
                        Welcome back
                    </h1>
                    <p class="text-gray-600 dark:text-gray-400">
                        Sign in to your account to continue
                    </p>
                </div>
                
                <!-- Error Message -->
                <?php if (isset($error) && $error): ?>
                <div class="mb-6 rounded-lg bg-red-50 border border-red-200 p-4 dark:bg-red-900/20 dark:border-red-800">
                    <div class="flex items-center">
                        <svg class="h-5 w-5 text-red-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                        </svg>
                        <p class="text-sm text-red-700 dark:text-red-400">
                            <?= htmlspecialchars($error) ?>
                        </p>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Login Form -->
                <form action="/login" method="POST" class="space-y-6" @submit="isLoading = true">
                    <!-- Username Field -->
                    <div>
                        <label for="username" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Username
                        </label>
                        <div class="relative">
                            <input 
                                id="username" 
                                name="username" 
                                type="text" 
                                required 
                                value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                                class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-3 pl-10 text-gray-900 placeholder-gray-400 shadow-theme-xs focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:placeholder-gray-500"
                                placeholder="Enter your username"
                                autocomplete="username"
                            >
                            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Password Field -->
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Password
                        </label>
                        <div class="relative">
                            <input 
                                id="password" 
                                name="password" 
                                :type="showPassword ? 'text' : 'password'"
                                required 
                                class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-3 pl-10 pr-10 text-gray-900 placeholder-gray-400 shadow-theme-xs focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:placeholder-gray-500"
                                placeholder="Enter your password"
                                autocomplete="current-password"
                            >
                            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                </svg>
                            </div>
                            <button 
                                type="button"
                                @click="showPassword = !showPassword"
                                class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
                            >
                                <svg x-show="!showPassword" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                                <svg x-show="showPassword" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21"></path>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <button 
                        type="submit" 
                        :disabled="isLoading"
                        class="w-full flex items-center justify-center rounded-lg bg-brand-600 px-4 py-3 text-base font-semibold text-white shadow-theme-sm hover:bg-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2 disabled:opacity-70 disabled:cursor-not-allowed transition-colors"
                    >
                        <svg x-show="isLoading" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span x-text="isLoading ? 'Signing in...' : 'Sign in'"></span>
                    </button>
                    
                    <!-- Sign Up Link -->
                    <div class="text-center">
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            Don't have an account? 
                            <a href="/register" class="font-medium text-brand-600 hover:text-brand-500 dark:text-brand-400 dark:hover:text-brand-300 transition-colors">
                                Sign up
                            </a>
                        </p>
                    </div>
                </form>
            </div>
            
            <!-- Demo Account Info -->
            <div class="mt-8 bg-blue-50/80 backdrop-blur-sm border border-blue-200/50 rounded-2xl p-6 dark:bg-blue-900/20 dark:border-blue-800/50">
                <div class="flex items-start gap-4">
                    <div class="flex-shrink-0">
                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-100 dark:bg-blue-900/40">
                            <svg class="h-5 w-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="flex-1 min-w-0">
                        <h3 class="text-sm font-semibold text-blue-800 dark:text-blue-200 mb-2">
                            Demo Account Available
                        </h3>
                        <p class="text-sm text-blue-700 dark:text-blue-300 mb-3">
                            Try all features without creating an account. Perfect for testing and exploration.
                        </p>
                        <div class="grid grid-cols-2 gap-3 text-sm">
                            <div class="flex items-center justify-between bg-blue-100/50 dark:bg-blue-900/30 px-3 py-2 rounded-lg">
                                <span class="text-blue-700 dark:text-blue-300">Username:</span>
                                <span class="font-mono font-medium text-blue-900 dark:text-blue-100">demo</span>
                            </div>
                            <div class="flex items-center justify-between bg-blue-100/50 dark:bg-blue-900/30 px-3 py-2 rounded-lg">
                                <span class="text-blue-700 dark:text-blue-300">Password:</span>
                                <span class="font-mono font-medium text-blue-900 dark:text-blue-100">password</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="relative z-10 mt-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="text-center">
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    &copy; 2025 OpenAI Webchat. Built for learning and demonstration.
                </p>
                <div class="mt-2 flex items-center justify-center gap-4 text-xs text-gray-400 dark:text-gray-500">
                    <span>Powered by</span>
                    <div class="flex items-center gap-2">
                        <span class="px-2 py-1 bg-gray-100 dark:bg-gray-800 rounded text-xs">PHP 8.4</span>
                        <span class="px-2 py-1 bg-gray-100 dark:bg-gray-800 rounded text-xs">OpenAI</span>
                        <span class="px-2 py-1 bg-gray-100 dark:bg-gray-800 rounded text-xs">TailwindCSS</span>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <!-- Auto-fill demo credentials on load -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Auto-focus username field
            document.getElementById('username').focus();
            
            // Add quick demo fill functionality
            document.addEventListener('keydown', function(e) {
                // Press Ctrl/Cmd + D to auto-fill demo credentials
                if ((e.ctrlKey || e.metaKey) && e.key === 'd') {
                    e.preventDefault();
                    document.getElementById('username').value = 'demo';
                    document.getElementById('password').value = 'password';
                    document.getElementById('password').focus();
                }
            });
            
            // Add Enter key navigation
            document.getElementById('username').addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    document.getElementById('password').focus();
                }
            });
        });
    </script>
</body>
</html>

<?php 
$content = ob_get_clean();
echo $content;
?>