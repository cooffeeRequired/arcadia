@php use Core\Helpers\TranslationHelper; @endphp

<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Arcadia CRM')</title>
    <link href="{{ asset('app.css') }}" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    @stack('styles')
    @yield('styles')
</head>

<body class="bg-gray-50 max-h-[80vh] flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
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
@yield('content')

@stack('scripts')
{!! render_toast_scripts() !!}


<!-- Language Switcher JavaScript -->
<script src="{{ asset('js/language-switcher.js') }}"></script>

<!-- Global Modal JavaScript -->
<script src="{{ asset('js/modals.js') }}"></script>

<!-- Global Loading JavaScript -->
<script src="{{ asset('js/loading.js') }}"></script>

@php $langs = TranslationHelper::getAllTranslations(); @endphp
<script>
    function format(str, ...args) {
        return (str + '').replace(/{(\d+)}/g, (match, index) => args[index] ?? match);
    }

    window['__i18'] = @json($langs);
    window['i18'] = function (translate, ...params) {
        const __ = window['__i18'][translate];
        return format(__, ...params);
    }
</script>
@yield('scripts')
</body>
</html>
