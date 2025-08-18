
<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Arcadia CRM')</title>
    <link href="{{ asset('app.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.14.1/themes/base/jquery-ui.css">
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
        <main class="py-6 px-4 sm:px-6 lg:px-8 min-h-screen">
            @yield('content')
        </main>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <script src="https://code.jquery.com/ui/1.14.1/jquery-ui.min.js" integrity="sha256-AlTido85uXPlSyyaZNsjJXeCs07eSv3r43kyCVc8ChI=" crossorigin="anonymous"></script>

    @stack('scripts')
    @yield('scripts')
    <?php if (getenv('APP_ENV') !== 'development'): ?>
        <script src="{{ asset('js/main.js') }}"></script>
    <?php endif; ?>
    <!-- Toast Notification JavaScript -->
    {!! render_toast_scripts() !!}

    <!-- Global Modal JavaScript -->
    <script>
        // Global Modal System
        class GlobalModal {
            constructor() {
                this.modal = document.getElementById('global-modal');
                this.container = this.modal.querySelector('.modal-container');
                this.title = document.getElementById('modal-title');
                this.body = document.getElementById('modal-body');
                this.footer = document.getElementById('modal-footer');
                this.closeBtn = document.getElementById('modal-close');
                this.cancelBtn = document.getElementById('modal-cancel');

                this.bindEvents();
            }

            bindEvents() {
                // Zavření modalu kliknutím na overlay
                this.modal.addEventListener('click', (e) => {
                    if (e.target === this.modal) {
                        this.hide();
                    }
                });

                // Zavření modalu kliknutím na tlačítko zavřít
                this.closeBtn.addEventListener('click', () => this.hide());
                this.cancelBtn.addEventListener('click', () => this.hide());

                // Zavření modalu klávesou Escape
                document.addEventListener('keydown', (e) => {
                    if (e.key === 'Escape' && this.isVisible()) {
                        this.hide();
                    }
                });
            }

            show() {
                this.modal.classList.add('show');
                document.body.style.overflow = 'hidden';
            }

            hide() {
                this.modal.classList.remove('show');
                document.body.style.overflow = '';
            }

            isVisible() {
                return this.modal.classList.contains('show');
            }

            setTitle(title) {
                this.title.textContent = title;
            }

            setContent(content) {
                this.body.innerHTML = content;
            }

            setFooter(footer) {
                this.footer.innerHTML = footer;
            }

            showLoading() {
                this.body.innerHTML = '<div class="modal-loading">Načítání...</div>';
            }

            loadModal(options = {}) {
                const {
                    title = 'Název modalu',
                    url = null,
                    content = null,
                    footer = null,
                    size = 'medium',
                    onLoad = null,
                    onClose = null
                } = options;

                // Nastavení velikosti modalu
                this.container.className = 'modal-container';
                if (size === 'small') {
                    this.container.style.width = '400px';
                } else if (size === 'large') {
                    this.container.style.width = '800px';
                } else if (size === 'xlarge') {
                    this.container.style.width = '1200px';
                } else if (size === 'fullscreen') {
                    this.container.style.width = '100%';
                } else {
                    this.container.style.width = '600px';
                }

                this.setTitle(title);

                if (footer) {
                    this.setFooter(footer);
                } else {
                    this.setFooter('<button type="button" class="btn-secondary" id="modal-cancel">Zavřít</button>');
                }

                if (url) {
                    this.showLoading();
                    this.show();

                    // Načtení obsahu z URL
                    fetch(url)
                        .then(response => {
                            if (!response.ok) {
                                throw new Error('Network response was not ok');
                            }
                            return response.text();
                        })
                        .then(html => {
                            this.setContent(html);
                            if (onLoad) onLoad(this);
                        })
                        .catch(error => {
                            this.setContent(`<div class="text-red-600 p-4">Chyba při načítání: ${error.message}</div>`);
                        });
                } else if (content) {
                    this.setContent(content);
                    this.show();
                    if (onLoad) onLoad(this);
                } else {
                    this.showLoading();
                    this.show();
                }

                // Přidání callback pro zavření
                if (onClose) {
                    this.onCloseCallback = onClose;
                }

                // Přebindování tlačítek po změně obsahu
                setTimeout(() => {
                    const newCancelBtn = this.footer.querySelector('#modal-cancel');
                    if (newCancelBtn) {
                        newCancelBtn.addEventListener('click', () => {
                            this.hide();
                            if (this.onCloseCallback) this.onCloseCallback();
                        });
                    }
                }, 100);
            }
        }

        // Inicializace globálního modalu
        const globalModal = new GlobalModal();

        // Globální funkce pro použití v celé aplikaci
        window.loadModal = function(options) {
            globalModal.loadModal(options);
        };

        window.showModal = function(title, content, size = 'medium') {
            globalModal.loadModal({
                title: title,
                content: content,
                size: size
            });
        };

        window.hideModal = function() {
            globalModal.hide();
        };

        // Příklad použití:
        // loadModal({
        //     title: 'Můj modál',
        //     url: '/api/modal-content',
        //     size: 'large',
        //     onLoad: (modal) => console.log('Modál načten'),
        //     onClose: () => console.log('Modál zavřen')
        // });

        // showModal('Jednoduchý modál', '<p>Obsah modalu</p>', 'small');
    </script>

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
