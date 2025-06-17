<?php
// src/Web/Views/layout.php - Complete layout with theme flicker fix
?>
<!DOCTYPE html>
<html lang="en" class="h-full">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($title ?? 'OpenAI Webchat') ?></title>
  <link rel="icon" type="image/x-icon" href="/assets/favicon.ico">

  <!-- Critical CSS for preventing flash -->
  <style>
    /* Prevent flash of unstyled content */
    .theme-transition-disable * {
      transition: none !important;
    }
    
    /* Hide content until Alpine.js loads to prevent layout shift */
    [x-cloak] { 
      display: none !important; 
    }
  </style>

  <!-- Theme Manager - Load FIRST to prevent flickering -->
  <script src="/assets/js/theme-manager.js"></script>

  <!-- Tailwind CSS with JIT -->
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      content: ["./src/**/*.{html,js,php}"],
      theme: {
        extend: {
          colors: {
            brand: {
              50: "#eff6ff",
              100: "#dbeafe",
              200: "#bfdbfe",
              300: "#93c5fd",
              400: "#60a5fa",
              500: "#3b82f6",
              600: "#2563eb",
              700: "#1d4ed8",
              800: "#1e40af",
              900: "#1e3a8a",
            },
            success: {
              50: "#ecfdf5",
              100: "#d1fae5",
              200: "#a7f3d0",
              300: "#6ee7b7",
              400: "#34d399",
              500: "#10b981",
              600: "#059669",
              700: "#047857",
              800: "#065f46",
              900: "#064e3b",
            },
            warning: {
              50: "#fffbeb",
              100: "#fef3c7",
              200: "#fde68a",
              300: "#fcd34d",
              400: "#fbbf24",
              500: "#f59e0b",
              600: "#d97706",
              700: "#b45309",
              800: "#92400e",
              900: "#78350f",
            },
            error: {
              50: "#fef2f2",
              100: "#fee2e2",
              200: "#fecaca",
              300: "#fca5a5",
              400: "#f87171",
              500: "#ef4444",
              600: "#dc2626",
              700: "#b91c1c",
              800: "#991b1b",
              900: "#7f1d1d",
            },
          },
          fontSize: {
            "theme-xs": ["0.75rem", "1rem"],
            "theme-sm": ["0.875rem", "1.25rem"],
            "theme-base": ["1rem", "1.5rem"],
            "theme-lg": ["1.125rem", "1.75rem"],
            "theme-xl": ["1.25rem", "1.75rem"],
            "title-sm": ["1.5rem", "2rem"],
            "title-md": ["1.875rem", "2.25rem"],
            "title-lg": ["2.25rem", "2.5rem"],
            "title-xl": ["3rem", "1"],
            "title-2xl": ["3.75rem", "1"],
          },
          boxShadow: {
            "theme-xs": "0 1px 2px 0 rgb(0 0 0 / 0.05)",
            "theme-sm": "0 1px 3px 0 rgb(0 0 0 / 0.1), 0 1px 2px -1px rgb(0 0 0 / 0.1)",
            "theme-md": "0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1)",
            "theme-lg": "0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1)",
            "theme-xl": "0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1)",
          },
          animation: {
            "fade-in": "fadeIn 0.5s ease-in-out",
            "slide-in": "slideIn 0.3s ease-out",
            "bounce-slow": "bounce 2s infinite",
          },
          keyframes: {
            fadeIn: {
              "0%": { opacity: "0" },
              "100%": { opacity: "1" },
            },
            slideIn: {
              "0%": { transform: "translateX(-100%)" },
              "100%": { transform: "translateX(0)" },
            },
          },
        },
      },
      darkMode: "class",
    };
  </script>

  <!-- Alpine.js -->
  <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

  <!-- Custom CSS -->
  <link rel="stylesheet" href="/assets/css/style.css">
</head>

<body
  x-data="{ 
        sidebarToggle: false, 
        loaded: true,
        scrollTop: false
    }"
  x-init="
        // Remove transition disable after Alpine loads
        document.body.classList.remove('theme-transition-disable');
        
        // Handle scroll detection
        window.addEventListener('scroll', () => {
            scrollTop = window.pageYOffset > 0;
        });
    "
  class="bg-gray-50 dark:bg-gray-900 min-h-screen theme-transition-disable"
  x-cloak>

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
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
              </svg>
            </div>
            <span class="text-xl font-bold text-gray-900 dark:text-white">OpenAI Chat</span>
          </a>
          <button
            @click="sidebarToggle = !sidebarToggle"
            class="lg:hidden p-1.5 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800">
            <svg
              class="w-5 h-5 text-gray-500"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24">
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M6 18L18 6M6 6l12 12"></path>
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
                    class="group flex items-center rounded-lg px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 hover:text-gray-900 dark:text-gray-300 dark:hover:bg-gray-800 dark:hover:text-white">
                    <svg
                      class="mr-3 h-5 w-5 flex-shrink-0"
                      fill="none"
                      stroke="currentColor"
                      viewBox="0 0 24 24">
                      <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                    Dashboard
                  </a>
                </li>

                <li>
                  <a
                    href="/chat"
                    class="group flex items-center rounded-lg px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 hover:text-gray-900 dark:text-gray-300 dark:hover:bg-gray-800 dark:hover:text-white">
                    <svg
                      class="mr-3 h-5 w-5 flex-shrink-0"
                      fill="none"
                      stroke="currentColor"
                      viewBox="0 0 24 24">
                      <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                    </svg>
                    Chat
                  </a>
                </li>

                <li>
                  <a
                    href="/agents"
                    class="group flex items-center rounded-lg px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 hover:text-gray-900 dark:text-gray-300 dark:hover:bg-gray-800 dark:hover:text-white">
                    <svg
                      class="mr-3 h-5 w-5 flex-shrink-0"
                      fill="none"
                      stroke="currentColor"
                      viewBox="0 0 24 24">
                      <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"></path>
                    </svg>
                    Agents
                  </a>
                </li>
              </ul>
            </div>

            <!-- User Profile Section -->
            <div class="border-t border-gray-200 dark:border-gray-800 pt-6">
              <h3 class="mb-3 text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                Account
              </h3>
              <ul class="space-y-1">
                <li>
                  <div class="group flex items-center rounded-lg px-3 py-2">
                    <div class="flex h-8 w-8 items-center justify-center rounded-full bg-brand-100 dark:bg-brand-900/20">
                      <span class="text-sm font-medium text-brand-600 dark:text-brand-400">
                        <?= strtoupper(substr($_SESSION['full_name'] ?? 'User', 0, 1)) ?>
                      </span>
                    </div>
                    <div class="ml-3 min-w-0 flex-1">
                      <p class="text-sm font-medium text-gray-900 dark:text-white truncate">
                        <?= htmlspecialchars($_SESSION['full_name'] ?? 'User') ?>
                      </p>
                      <p class="text-xs text-gray-500 dark:text-gray-400">Online</p>
                    </div>
                  </div>
                </li>
                <li>
                  <a
                    href="/logout"
                    class="group flex items-center rounded-lg px-3 py-2 text-sm font-medium text-red-700 hover:bg-red-50 hover:text-red-900 dark:text-red-400 dark:hover:bg-red-900/20">
                    <svg
                      class="mr-3 h-5 w-5 flex-shrink-0"
                      fill="none"
                      stroke="currentColor"
                      viewBox="0 0 24 24">
                      <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
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
          <svg
            class="w-6 h-6 text-gray-600 dark:text-gray-300"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24">
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M4 6h16M4 12h16M4 18h16"></path>
          </svg>
        </button>
      <?php endif; ?>

      <!-- Header -->
      <?php if (isset($_SESSION['user_id'])): ?>
        <header
          class="sticky top-0 z-30 border-b border-gray-200 bg-white/80 backdrop-blur-sm px-4 py-4 dark:border-gray-800 dark:bg-gray-900/80 lg:px-6 glass-effect"
          :class="{'shadow-theme-sm': scrollTop}">
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
              <h1 class="text-lg font-semibold text-gray-900 dark:text-white">
                <?= $pageTitle ?? 'OpenAI Webchat' ?>
              </h1>
            </div>
            
            <div class="flex items-center gap-4">
              <!-- Dark Mode Toggle -->
              <button
                @click="$store.theme.toggle()"
                class="rounded-lg bg-gray-100 dark:bg-gray-700 p-2 text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors"
                title="Toggle dark mode">
                <svg x-show="!$store.theme.darkMode" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path>
                </svg>
                <svg x-show="$store.theme.darkMode" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path>
                </svg>
              </button>

              <!-- User Menu -->
              <div class="relative" x-data="{ open: false }">
                <button
                  @click="open = !open"
                  class="flex items-center gap-2 rounded-lg bg-gray-100 dark:bg-gray-700 px-3 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
                  <div class="flex h-6 w-6 items-center justify-center rounded-full bg-brand-100 dark:bg-brand-900/20">
                    <span class="text-xs font-medium text-brand-600 dark:text-brand-400">
                      <?= strtoupper(substr($_SESSION['full_name'] ?? 'User', 0, 1)) ?>
                    </span>
                  </div>
                  <span class="hidden sm:block"><?= htmlspecialchars($_SESSION['full_name'] ?? 'User') ?></span>
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                  </svg>
                </button>

                <!-- Dropdown Menu -->
                <div
                  x-show="open"
                  @click.outside="open = false"
                  x-transition:enter="transition ease-out duration-100"
                  x-transition:enter-start="transform opacity-0 scale-95"
                  x-transition:enter-end="transform opacity-100 scale-100"
                  x-transition:leave="transition ease-in duration-75"
                  x-transition:leave-start="transform opacity-100 scale-100"
                  x-transition:leave-end="transform opacity-0 scale-95"
                  class="absolute right-0 mt-2 w-48 rounded-lg bg-white dark:bg-gray-800 shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none z-50">
                  <div class="py-1">
                    <a href="/dashboard" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">
                      Dashboard
                    </a>
                    <a href="/agents" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">
                      Manage Agents
                    </a>
                    <hr class="border-gray-200 dark:border-gray-700">
                    <a href="/logout" class="block px-4 py-2 text-sm text-red-700 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20">
                      Sign out
                    </a>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </header>
      <?php endif; ?>

      <!-- Page Content -->
      <main class="flex-1 overflow-y-auto">
        <?= $content ?>
      </main>
    </div>
  </div>

  <!-- Success/Error Messages -->
  <?php if (isset($_SESSION['success'])): ?>
    <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)" 
         class="fixed top-4 right-4 z-50 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded shadow-lg">
      <div class="flex items-center justify-between">
        <span><?= htmlspecialchars($_SESSION['success']) ?></span>
        <button @click="show = false" class="ml-2 text-green-500 hover:text-green-700">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
          </svg>
        </button>
      </div>
    </div>
    <?php unset($_SESSION['success']); ?>
  <?php endif; ?>

  <?php if (isset($_SESSION['error'])): ?>
    <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)" 
         class="fixed top-4 right-4 z-50 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded shadow-lg">
      <div class="flex items-center justify-between">
        <span><?= htmlspecialchars($_SESSION['error']) ?></span>
        <button @click="show = false" class="ml-2 text-red-500 hover:text-red-700">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
          </svg>
        </button>
      </div>
    </div>
    <?php unset($_SESSION['error']); ?>
  <?php endif; ?>

</body>
</html>