@php use Core\Helpers\TranslationHelper; @endphp

<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Arcadia CRM')</title>
    <link href="{{ asset('app.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.14.1/themes/base/jquery-ui.css">
    <script src="//unpkg.com/alpinejs" defer></script>
    <style>
        [x-cloak] { display: none !important; }
    </style>
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

    <!-- Global Modal System -->
    <div id="global-modal" class="modal-overlay">
        <div class="modal-container">
            <div class="modal-header">
                <h3 class="modal-title" id="modal-title">Název modalu</h3>
                <button class="modal-close" id="modal-close" type="button">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body" id="modal-body">
                <div class="modal-loading">Načítání...</div>
            </div>
            <div class="modal-footer" id="modal-footer">
                <button type="button" class="btn-secondary" id="modal-cancel">
                    Zavřít
                </button>
            </div>
        </div>
    </div>

    <!-- Toast Notification System -->
    {!! render_toasts() !!}

    <!-- Language Loading Overlay -->
    <div id="language-loading-overlay" class="fixed inset-0 z-[9999] hidden flex items-center justify-center">
        <div class="absolute inset-0 backdrop-blur-animate"></div>
        <div class="bg-white rounded-2xl p-8 shadow-2xl flex flex-col items-center space-y-4 loading-card relative z-10">
            <div id="flag-animation" class="text-6xl">
                🌐
            </div>
            <div id="language-text" class="text-gray-600 font-medium">Měním jazyk...</div>
            <div class="w-8 h-8 border-4 border-blue-200 border-t-blue-600 rounded-full animate-spin"></div>
        </div>
    </div>

    <!-- Global Loading Overlay -->
    <div id="global-loading-overlay" class="fixed inset-0 z-[9999] hidden flex items-center justify-center">
        <div class="absolute inset-0 backdrop-blur-animate"></div>
        <div class="bg-white rounded-2xl p-8 shadow-2xl flex flex-col items-center space-y-4 loading-card relative z-10">
            <div class="w-16 h-16 border-4 border-blue-200 border-t-blue-600 rounded-full animate-spin"></div>
            <div id="global-loading-text" class="text-gray-600 font-medium">Načítání...</div>
        </div>
    </div>

    <!-- Main Content -->
    <div id="main-content" class="lg:pl-80 transition-all duration-300 ease-in-out">
        <main class="min-h-screen">
            @include('components.breadcrumbs')
            <div class="py-6 px-4 sm:px-6 lg:px-8">
                @yield('content')
            </div>
        </main>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <script src="https://code.jquery.com/ui/1.14.1/jquery-ui.min.js" integrity="sha256-AlTido85uXPlSyyaZNsjJXeCs07eSv3r43kyCVc8ChI=" crossorigin="anonymous"></script>

    @stack('scripts')
    @yield('scripts')
    <script src="{{ asset('js/main.js') }}"></script>

    <!-- Language Switcher JavaScript -->
    <script src="{{ asset('js/language-switcher.js') }}"></script>
    <!-- Global Modal JavaScript -->
    <script src="{{ asset('js/modals.js') }}"></script>

    <!-- Global Loading JavaScript -->
    <script src="{{ asset('js/loading.js') }}"></script>

    <!-- Mobile menu JavaScript -->
    <script src="{{ asset('js/mobile.js') }}"></script>

    {!! render_toast_scripts() !!}

    @php $langs = TranslationHelper::getAllTranslations(); @endphp
    <script>
        function format(str, ...args) {
            return (str + '').replace(/{(\d+)}/g, (match, index) => args[index] ?? match);
        }
        window['__i18'] = @json($langs);
        window['i18'] = function(translate, ...params) {
            const __ = window['__i18'][translate];
            return format(__, ...params);
        }
    </script>
    @yield('scripts')
</body>
</html>
