<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $__env->yieldContent('title', 'Arcadia CRM'); ?></title>
    <link href="/css/app.css" rel="stylesheet">
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <?php echo $__env->yieldContent('styles'); ?>
</head>
<body class="bg-gray-50 min-h-screen">
    <!-- Mobile menu button -->
    <div class="lg:hidden fixed top-4 left-4 z-50">
        <button id="mobile-menu-button" class="p-2 rounded-md text-gray-600 hover:text-gray-900 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-blue-500">
            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
        </button>
    </div>

    <!-- Sidebar -->
    <div id="sidebar" class="fixed inset-y-0 left-0 z-40 w-64 bg-white shadow-lg transform -translate-x-full lg:translate-x-0 transition-transform duration-300 ease-in-out">
        <!-- Logo -->
        <div class="flex items-center justify-center h-16 px-4 border-b border-gray-200">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <svg class="h-8 w-8 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                </div>
                <div class="ml-3">
                    <h1 class="text-xl font-bold text-gray-900">Arcadia CRM</h1>
                </div>
            </div>
        </div>

        <!-- Navigation -->
        <nav class="mt-8 px-4">
            <div class="space-y-1">
                <!-- Dashboard -->
                <a href="/" class="group flex items-center px-3 py-2 text-sm font-medium rounded-md <?php echo e(request()->is('/') ? 'bg-blue-100 text-blue-900' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'); ?>">
                    <svg class="mr-3 h-5 w-5 <?php echo e(request()->is('/') ? 'text-blue-500' : 'text-gray-400 group-hover:text-gray-500'); ?>" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5a2 2 0 012-2h4a2 2 0 012 2v6H8V5z" />
                    </svg>
                    Dashboard
                </a>

                <!-- Customers -->
                <a href="/customers" class="group flex items-center px-3 py-2 text-sm font-medium rounded-md <?php echo e(request()->is('customers*') ? 'bg-blue-100 text-blue-900' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'); ?>">
                    <svg class="mr-3 h-5 w-5 <?php echo e(request()->is('customers*') ? 'text-blue-500' : 'text-gray-400 group-hover:text-gray-500'); ?>" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z" />
                    </svg>
                    Zákazníci
                </a>

                <!-- Contacts -->
                <a href="/contacts" class="group flex items-center px-3 py-2 text-sm font-medium rounded-md <?php echo e(request()->is('contacts*') ? 'bg-blue-100 text-blue-900' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'); ?>">
                    <svg class="mr-3 h-5 w-5 <?php echo e(request()->is('contacts*') ? 'text-blue-500' : 'text-gray-400 group-hover:text-gray-500'); ?>" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                    </svg>
                    Kontakty
                </a>

                <!-- Deals -->
                <a href="/deals" class="group flex items-center px-3 py-2 text-sm font-medium rounded-md <?php echo e(request()->is('deals*') ? 'bg-blue-100 text-blue-900' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'); ?>">
                    <svg class="mr-3 h-5 w-5 <?php echo e(request()->is('deals*') ? 'text-blue-500' : 'text-gray-400 group-hover:text-gray-500'); ?>" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                    </svg>
                    Obchody
                </a>

                <!-- Invoices -->
                <a href="/invoices" class="group flex items-center px-3 py-2 text-sm font-medium rounded-md <?php echo e(request()->is('invoices*') ? 'bg-blue-100 text-blue-900' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'); ?>">
                    <svg class="mr-3 h-5 w-5 <?php echo e(request()->is('invoices*') ? 'text-blue-500' : 'text-gray-400 group-hover:text-gray-500'); ?>" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Faktury
                </a>

                <!-- Reports -->
                <a href="/reports" class="group flex items-center px-3 py-2 text-sm font-medium rounded-md <?php echo e(request()->is('reports*') ? 'bg-blue-100 text-blue-900' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'); ?>">
                    <svg class="mr-3 h-5 w-5 <?php echo e(request()->is('reports*') ? 'text-blue-500' : 'text-gray-400 group-hover:text-gray-500'); ?>" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                    Reporty
                </a>

                <!-- Settings -->
                <a href="/settings" class="group flex items-center px-3 py-2 text-sm font-medium rounded-md <?php echo e(request()->is('settings*') ? 'bg-blue-100 text-blue-900' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'); ?>">
                    <svg class="mr-3 h-5 w-5 <?php echo e(request()->is('settings*') ? 'text-blue-500' : 'text-gray-400 group-hover:text-gray-500'); ?>" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    Nastavení
                </a>
            </div>
        </nav>

        <!-- User Profile -->
        <div class="absolute bottom-0 left-0 right-0 p-4 border-t border-gray-200">
            <?php if(isset($_SESSION['user_id'])): ?>
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="h-8 w-8 rounded-full bg-blue-600 flex items-center justify-center">
                                <span class="text-sm font-medium text-white"><?php echo e(substr($_SESSION['user_name'] ?? 'U', 0, 1)); ?></span>
                            </div>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-gray-700"><?php echo e($_SESSION['user_name'] ?? 'Uživatel'); ?></p>
                            <p class="text-xs text-gray-500"><?php echo e($_SESSION['user_email'] ?? ''); ?></p>
                        </div>
                    </div>
                    <a href="/logout" class="text-gray-400 hover:text-gray-600" title="Odhlásit se">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                    </a>
                </div>
            <?php else: ?>
                <div class="text-center">
                    <a href="/login" class="text-blue-600 hover:text-blue-500 text-sm font-medium">Přihlásit se</a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Overlay for mobile -->
    <div id="sidebar-overlay" class="fixed inset-0 z-30 bg-gray-600 bg-opacity-75 lg:hidden hidden"></div>

    <!-- Toast Notifications -->
    <?php echo render_toasts(); ?>


    <!-- Main Content -->
    <div class="lg:pl-64">
        <main class="py-6 px-4 sm:px-6 lg:px-8">
            <?php echo $__env->yieldContent('content'); ?>
        </main>
    </div>

    <?php echo $__env->yieldPushContent('scripts'); ?>
    <?php echo $__env->yieldContent('scripts'); ?>

    <!-- Toast JavaScript -->
    <?php echo render_toast_script(); ?>


    <!-- Sidebar JavaScript -->
    <script>
        // Mobile menu toggle
        const mobileMenuButton = document.getElementById('mobile-menu-button');
        const sidebar = document.getElementById('sidebar');
        const sidebarOverlay = document.getElementById('sidebar-overlay');

        function toggleSidebar() {
            const isOpen = !sidebar.classList.contains('-translate-x-full');
            
            if (isOpen) {
                sidebar.classList.add('-translate-x-full');
                sidebarOverlay.classList.add('hidden');
            } else {
                sidebar.classList.remove('-translate-x-full');
                sidebarOverlay.classList.remove('hidden');
            }
        }

        mobileMenuButton.addEventListener('click', toggleSidebar);
        sidebarOverlay.addEventListener('click', toggleSidebar);

        // Close sidebar on window resize if screen becomes large
        window.addEventListener('resize', () => {
            if (window.innerWidth >= 1024) {
                sidebar.classList.remove('-translate-x-full');
                sidebarOverlay.classList.add('hidden');
            }
        });
    </script>
</body>
</html> <?php /**PATH /custom/repositories/private/Arcadia/resources/views/layouts/app.blade.php ENDPATH**/ ?>