
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

<body class="bg-gray-50 min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
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
    @yield('scripts')

    <!-- Language Switcher JavaScript -->
    <script>
        function changeLanguage(locale, flag) {
            // Zobrazit loading overlay
            const overlay = document.getElementById('language-loading-overlay');
            const flagAnimation = document.getElementById('flag-animation');
            const languageText = document.getElementById('language-text');

            // Ujistit se, že overlay je nad vším
            overlay.style.zIndex = '9999';
            overlay.style.position = 'fixed';
            overlay.style.top = '0';
            overlay.style.left = '0';
            overlay.style.right = '0';
            overlay.style.bottom = '0';

            overlay.classList.remove('hidden');

            // Najít název jazyka
            const languageNames = {
                'cs': 'čeština',
                'en': 'angličtina',
                'de': 'němčina',
                'sk': 'slovenština',
                'pl': 'polština'
            };

            languageText.textContent = `Měním na ${languageNames[locale] || locale}...`;

            // Animace změny vlajky s morphing efektem
            flagAnimation.classList.add('flag-morph');

                        setTimeout(() => {
                flagAnimation.textContent = flag;
            }, 600); // Změna vlajky uprostřed animace

            setTimeout(() => {
                flagAnimation.classList.remove('flag-morph');
            }, 1200);

            fetch('/api/languages/set', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ locale: locale })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                                // Delší pauza pro animaci
            setTimeout(() => {
                window.location.reload();
            }, 1000);
                } else {
                    console.error('Chyba při změně jazyka:', data.message);
                    overlay.classList.add('hidden');
                }
            })
            .catch(error => {
                console.error('Chyba při změně jazyka:', error);
                overlay.classList.add('hidden');
            });
        }
    </script>

    <!-- Global Loading JavaScript -->
    <script>
        // Globální loading state
        window.setLoading = function(show, text = 'Načítání...') {
            const overlay = document.getElementById('global-loading-overlay');
            const loadingText = document.getElementById('global-loading-text');

            if (show) {
                // Ujistit se, že overlay je nad vším
                overlay.style.zIndex = '9999';
                overlay.style.position = 'fixed';
                overlay.style.top = '0';
                overlay.style.left = '0';
                overlay.style.right = '0';
                overlay.style.bottom = '0';

                loadingText.textContent = text;
                overlay.classList.remove('hidden');
            } else {
                overlay.classList.add('hidden');
            }
        };

        // Helper funkce pro rychlé použití
        window.showLoading = function(text = 'Načítání...', autoHide = null) {
            window.setLoading(true, text);

            // Automatické skrytí
            if (autoHide) {
                const milliseconds = parseTimeToMilliseconds(autoHide);
                setTimeout(() => window.setLoading(false), milliseconds);
            }
        };

        window.hideLoading = function() {
            window.setLoading(false);
        };

        // Funkce pro parsování času (stejná jako v PHP)
        function parseTimeToMilliseconds(timeString) {
            timeString = timeString.trim().toLowerCase();

            // Regex pro parsování čísla a jednotky
            const match = timeString.match(/^(\d+(?:\.\d+)?)\s*(ms|s|m|h|d)$/);
            if (match) {
                const value = parseFloat(match[1]);
                const unit = match[2];

                switch(unit) {
                    case 'ms': return Math.floor(value);
                    case 's': return Math.floor(value * 1000);
                    case 'm': return Math.floor(value * 60 * 1000);
                    case 'h': return Math.floor(value * 60 * 60 * 1000);
                    case 'd': return Math.floor(value * 24 * 60 * 60 * 1000);
                    default: return 1000;
                }
            }

            // Fallback
            return 1000;
        }
    </script>
</body>
</html>
