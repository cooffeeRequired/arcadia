
<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Arcadia CRM')</title>
    <link href="{{ asset('app.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    @hmrClient
    @hmr('resources/js/app.js')
    @yield('styles')
</head>
<body class="bg-gray-50 min-h-screen">
    <!-- Mobile menu button -->
    <div class="lg:hidden fixed top-4 left-4 z-[9999]">
        <button id="mobile-menu-button" class="p-3 rounded-lg bg-white shadow-lg text-gray-600 hover:text-gray-900 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all duration-200 ease-in-out border border-gray-200">
            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
        </button>
    </div>

    <!-- Sidebar -->
    <div id="sidebar" class="fixed inset-y-0 left-0 z-40 w-80 bg-white shadow-xl transform -translate-x-full lg:translate-x-0 transition-transform duration-300 ease-in-out">
        @include('layouts.sidebar')
    </div>

    <!-- Blur overlay for mobile -->
    <div id="sidebar-overlay" class="fixed inset-0 z-30 bg-transparent backdrop-blur-sm lg:hidden hidden transition-all duration-300 ease-in-out"></div>

    <!-- Toast Notification System -->
    {!! render_toasts() !!}

    <!-- Main Content -->
    <div id="main-content" class="lg:pl-80 transition-all duration-300 ease-in-out">
        <main class="py-6 px-4 sm:px-6 lg:px-8 min-h-screen">
            @yield('content')
        </main>
    </div>

    @stack('scripts')
    @yield('scripts')
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <script src="https://code.jquery.com/ui/1.14.1/jquery-ui.min.js" integrity="sha256-AlTido85uXPlSyyaZNsjJXeCs07eSv3r43kyCVc8ChI=" crossorigin="anonymous"></script>
    <?php if (getenv('APP_ENV') !== 'development'): ?>
        <script src="{{ asset('js/main.js') }}"></script>
    <?php endif; ?>
    <!-- Toast Notification JavaScript -->
    {!! render_toast_scripts() !!}

    <!-- Mobile menu JavaScript -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const mobileMenuButton = document.getElementById('mobile-menu-button');
            const sidebar = document.getElementById('sidebar');
            const sidebarOverlay = document.getElementById('sidebar-overlay');

            if (mobileMenuButton && sidebar) {
                mobileMenuButton.addEventListener('click', function() {
                    sidebar.classList.toggle('-translate-x-full');
                    if (sidebarOverlay) {
                        sidebarOverlay.classList.toggle('hidden');
                    }
                });

                if (sidebarOverlay) {
                    sidebarOverlay.addEventListener('click', function() {
                        sidebar.classList.add('-translate-x-full');
                        sidebarOverlay.classList.add('hidden');
                    });
                }
            }
        });
    </script>
</body>
</html>
