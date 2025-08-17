@extends('layouts.app')

@section('title', 'Editace modulu: ' . $module->getDisplayName() . ' - Arcadia')

@section('content')
    <div class="px-4 sm:px-6 lg:px-8">
        <!-- Enhanced Header -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-8">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg flex items-center justify-center shadow-sm">
                                <i class="fas fa-puzzle-piece text-white text-lg"></i>
                            </div>
                        </div>
                        <div>
                            <div class="flex items-center space-x-3">
                                <h1 class="text-2xl font-bold text-gray-900">Editace modulu</h1>
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                                    <i class="fas fa-cog mr-1"></i>{{ $module->getName() }}
                                </span>
                            </div>
                            <div class="flex items-center space-x-4 mt-1">
                                <p class="text-sm text-gray-600">
                                    <i class="fas fa-info-circle mr-1"></i>{{ $module->getDisplayName() }}
                                </p>
                                <p class="text-sm text-gray-500">
                                    <i class="fas fa-code-branch mr-1"></i>Verze {{ $module->getVersion() }}
                                </p>
                                @if($module->getAuthor())
                                <p class="text-sm text-gray-500">
                                    <i class="fas fa-user mr-1"></i>{{ $module->getAuthor() }}
                                </p>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center space-x-3">
                        <a href="/settings/modules" class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200">
                            <i class="fas fa-arrow-left mr-2"></i>Zpět na seznam
                        </a>
                        <button onclick="saveAllChanges()" class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-gradient-to-r from-blue-600 to-blue-700 border border-transparent rounded-lg hover:from-blue-700 hover:to-blue-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 shadow-sm">
                            <i class="fas fa-save mr-2"></i>Uložit všechny změny
                        </button>
                    </div>
                </div>
            </div>

            <!-- Module Status Bar -->
            <div class="px-6 py-3 bg-gray-50 rounded-b-lg">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-6">
                        <div class="flex items-center space-x-2">
                            <span class="text-sm font-medium text-gray-700">Stav:</span>
                            @if($module->isEnabled())
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    <i class="fas fa-check-circle mr-1"></i>Povolen
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                    <i class="fas fa-times-circle mr-1"></i>Vypnut
                                </span>
                            @endif
                        </div>
                        <div class="flex items-center space-x-2">
                            <span class="text-sm font-medium text-gray-700">Instalace:</span>
                            @if($module->isInstalled())
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    <i class="fas fa-download mr-1"></i>Nainstalován
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                    <i class="fas fa-exclamation-triangle mr-1"></i>Nenainstalován
                                </span>
                            @endif
                        </div>
                    </div>
                    <div class="flex items-center space-x-2 text-sm text-gray-500">
                        <i class="fas fa-clock mr-1"></i>
                        <span>Poslední úprava: {{ date('d.m.Y H:i') }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Enhanced Tabs -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
            <div id="module-tabs" class="module-tabs">
                <ul class="flex border-b border-gray-200">
                    <li class="mr-1">
                        <a href="#tab-basic" class="tab-link">
                            <i class="fas fa-info-circle mr-2"></i>Základní informace
                        </a>
                    </li>
                    <li class="mr-1">
                        <a href="#tab-controllers" class="tab-link">
                            <i class="fas fa-cogs mr-2"></i>Controllers
                        </a>
                    </li>
                    <li class="mr-1">
                        <a href="#tab-entities" class="tab-link">
                            <i class="fas fa-database mr-2"></i>Entities
                        </a>
                    </li>
                    <li class="mr-1">
                        <a href="#tab-migrations" class="tab-link">
                            <i class="fas fa-code-branch mr-2"></i>Migrations
                        </a>
                    </li>
                </ul>

                <!-- Základní informace -->
                <div id="tab-basic" class="tab-content">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Základní informace</h3>
                        <form id="basic-form" class="space-y-6">
                            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                                <div>
                                    <label for="name" class="block text-sm font-medium text-gray-700">Název modulu</label>
                                    <input type="text" id="name" name="name" value="{{ $module->getName() }}"
                                           class="mt-1 block w-full" readonly>
                                    <p class="mt-1 text-sm text-gray-500">Název modulu nelze změnit</p>
                                </div>

                                <div>
                                    <label for="display_name" class="block text-sm font-medium text-gray-700">Zobrazovaný název</label>
                                    <input type="text" id="display_name" name="display_name"
                                           value="{{ $module->getDisplayName() }}" class="mt-1 block w-full">
                                </div>

                                <div>
                                    <label for="version" class="block text-sm font-medium text-gray-700">Verze</label>
                                    <input type="text" id="version" name="version"
                                           value="{{ $module->getVersion() }}" class="mt-1 block w-full">
                                </div>

                                <div>
                                    <label for="author" class="block text-sm font-medium text-gray-700">Autor</label>
                                    <input type="text" id="author" name="author"
                                           value="{{ $module->getAuthor() }}" class="mt-1 block w-full">
                                </div>

                                <div>
                                    <label for="icon" class="block text-sm font-medium text-gray-700">Ikona</label>
                                    <input type="text" id="icon" name="icon"
                                           value="{{ $module->getIcon() }}" class="mt-1 block w-full"
                                           placeholder="fas fa-cube">
                                </div>

                                <div>
                                    <label for="color" class="block text-sm font-medium text-gray-700">Barva</label>
                                    <input type="color" id="color" name="color"
                                           value="{{ $module->getColor() ?? '#3b82f6' }}" class="mt-1 block w-full h-10">
                                </div>

                                <div>
                                    <label for="sort_order" class="block text-sm font-medium text-gray-700">Pořadí</label>
                                    <input type="number" id="sort_order" name="sort_order"
                                           value="{{ $module->getSortOrder() ?? 0 }}" class="mt-1 block w-full">
                                </div>

                                <div class="sm:col-span-2">
                                    <label for="description" class="block text-sm font-medium text-gray-700">Popis</label>
                                    <textarea id="description" name="description" rows="3"
                                              class="mt-1 block w-full">{{ $module->getDescription() }}</textarea>
                                </div>
                            </div>

                            <div class="flex items-center justify-between pt-4 border-t border-gray-200">
                                <div class="flex items-center space-x-4">
                                    <div class="flex items-center">
                                        <input type="checkbox" id="is_enabled" name="is_enabled"
                                               {{ $module->isEnabled() ? 'checked' : '' }} class="mr-2">
                                        <label for="is_enabled" class="text-sm font-medium text-gray-700">Modul je povolen</label>
                                    </div>
                                    <div class="flex items-center">
                                        <input type="checkbox" id="is_installed" name="is_installed"
                                               {{ $module->isInstalled() ? 'checked' : '' }} class="mr-2" readonly>
                                        <label for="is_installed" class="text-sm font-medium text-gray-700">Modul je nainstalován</label>
                                    </div>
                                </div>
                                <button type="button" onclick="saveBasicInfo()" class="btn-primary">
                                    <i class="fas fa-save mr-2"></i>Uložit změny
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Controllers -->
                <div id="tab-controllers" class="tab-content">
                    <div class="p-6">
                        <!-- Header -->
                        <div class="flex items-center justify-between mb-6">
                            <div>
                                <h3 class="text-lg font-medium text-gray-900">Controllers</h3>
                                <p class="text-sm text-gray-600 mt-1">Správa controllerů modulu {{ $module->getName() }}</p>
                            </div>
                            <div class="flex items-center space-x-3">
                                <button onclick="addNewController()" class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-gradient-to-r from-green-600 to-green-700 border border-transparent rounded-lg hover:from-green-700 hover:to-green-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-all duration-200 shadow-sm">
                                    <i class="fas fa-plus mr-2"></i>Nový Controller
                                </button>
                                <button onclick="refreshControllers()" class="inline-flex items-center px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200">
                                    <i class="fas fa-sync-alt"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Controllers List -->
                        <div class="bg-white rounded-lg border border-gray-200 shadow-sm">
                            <div class="px-6 py-4 border-b border-gray-200">
                                <div class="flex items-center justify-between">
                                    <h4 class="text-sm font-medium text-gray-900">Dostupné Controllery</h4>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800" id="controllers-count">
                                        0 controllerů
                                    </span>
                                </div>
                            </div>

                            <div id="controllers-list" class="divide-y divide-gray-200">
                                <!-- Controllers will be loaded here -->
                                <div class="px-6 py-8 text-center">
                                    <i class="fas fa-code text-gray-400 text-3xl mb-3"></i>
                                    <p class="text-sm text-gray-500">Žádné controllery nebyly nalezeny</p>
                                    <button onclick="addNewController()" class="mt-3 inline-flex items-center px-3 py-1.5 text-sm font-medium text-blue-600 hover:text-blue-700">
                                        <i class="fas fa-plus mr-1"></i>Přidat první controller
                                    </button>
                                </div>
                            </div>
                        </div>


                    </div>
                </div>

                <!-- Entities -->
                <div id="tab-entities" class="tab-content">
                    <div class="p-6">
                        <!-- Header -->
                        <div class="flex items-center justify-between mb-6">
                            <div>
                                <h3 class="text-lg font-medium text-gray-900">Entities</h3>
                                <p class="text-sm text-gray-600 mt-1">Správa entit modulu {{ $module->getName() }}</p>
                            </div>
                            <div class="flex items-center space-x-3">
                                <button onclick="addNewEntity()" class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-gradient-to-r from-purple-600 to-purple-700 border border-transparent rounded-lg hover:from-purple-700 hover:to-purple-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 transition-all duration-200 shadow-sm">
                                    <i class="fas fa-plus mr-2"></i>Nová Entity
                                </button>
                                <button onclick="refreshEntities()" class="inline-flex items-center px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200">
                                    <i class="fas fa-sync-alt"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Entities List -->
                        <div class="bg-white rounded-lg border border-gray-200 shadow-sm">
                            <div class="px-6 py-4 border-b border-gray-200">
                                <div class="flex items-center justify-between">
                                    <h4 class="text-sm font-medium text-gray-900">Dostupné Entity</h4>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800" id="entities-count">
                                        0 entit
                                    </span>
                                </div>
                            </div>

                            <div id="entities-list" class="divide-y divide-gray-200">
                                <!-- Entities will be loaded here -->
                                <div class="px-6 py-8 text-center">
                                    <i class="fas fa-database text-gray-400 text-3xl mb-3"></i>
                                    <p class="text-sm text-gray-500">Žádné entity nebyly nalezeny</p>
                                    <button onclick="addNewEntity()" class="mt-3 inline-flex items-center px-3 py-1.5 text-sm font-medium text-purple-600 hover:text-purple-700">
                                        <i class="fas fa-plus mr-1"></i>Přidat první entity
                                    </button>
                                </div>
                            </div>
                        </div>


                    </div>
                </div>

                <!-- Migrations -->
                <div id="tab-migrations" class="tab-content">
                    <div class="p-6">
                        <!-- Header -->
                        <div class="flex items-center justify-between mb-6">
                            <div>
                                <h3 class="text-lg font-medium text-gray-900">Migrations</h3>
                                <p class="text-sm text-gray-600 mt-1">Správa migrací modulu {{ $module->getName() }}</p>
                            </div>
                            <div class="flex items-center space-x-3">
                                <button onclick="addNewMigration()" class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-gradient-to-r from-orange-600 to-orange-700 border border-transparent rounded-lg hover:from-orange-700 hover:to-orange-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 transition-all duration-200 shadow-sm">
                                    <i class="fas fa-plus mr-2"></i>Nová Migration
                                </button>
                                <button onclick="runAllMigrations()" class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-gradient-to-r from-green-600 to-green-700 border border-transparent rounded-lg hover:from-green-700 hover:to-green-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-all duration-200 shadow-sm">
                                    <i class="fas fa-play mr-2"></i>Spustit vše
                                </button>
                                <button onclick="refreshMigrations()" class="inline-flex items-center px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200">
                                    <i class="fas fa-sync-alt"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Migration Status -->
                        <div class="bg-white rounded-lg border border-gray-200 shadow-sm mb-6">
                            <div class="px-6 py-4 border-b border-gray-200">
                                <h4 class="text-sm font-medium text-gray-900">Stav migrací</h4>
                            </div>
                            <div class="p-6">
                                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                    <div class="bg-blue-50 rounded-lg p-4">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0">
                                                <i class="fas fa-list text-blue-600 text-lg"></i>
                                            </div>
                                            <div class="ml-3">
                                                <p class="text-sm font-medium text-blue-900" id="total-migrations">0</p>
                                                <p class="text-xs text-blue-700">Celkem migrací</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="bg-green-50 rounded-lg p-4">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0">
                                                <i class="fas fa-check text-green-600 text-lg"></i>
                                            </div>
                                            <div class="ml-3">
                                                <p class="text-sm font-medium text-green-900" id="ran-migrations">0</p>
                                                <p class="text-xs text-green-700">Spuštěné</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="bg-yellow-50 rounded-lg p-4">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0">
                                                <i class="fas fa-clock text-yellow-600 text-lg"></i>
                                            </div>
                                            <div class="ml-3">
                                                <p class="text-sm font-medium text-yellow-900" id="pending-migrations">0</p>
                                                <p class="text-xs text-yellow-700">Čekající</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="bg-red-50 rounded-lg p-4">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0">
                                                <i class="fas fa-exclamation-triangle text-red-600 text-lg"></i>
                                            </div>
                                            <div class="ml-3">
                                                <p class="text-sm font-medium text-red-900" id="failed-migrations">0</p>
                                                <p class="text-xs text-red-700">Chyby</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Migrations List -->
                        <div class="bg-white rounded-lg border border-gray-200 shadow-sm">
                            <div class="px-6 py-4 border-b border-gray-200">
                                <div class="flex items-center justify-between">
                                    <h4 class="text-sm font-medium text-gray-900">Dostupné Migrace</h4>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800" id="migrations-count">
                                        0 migrací
                                    </span>
                                </div>
                            </div>

                            <div id="migrations-list" class="divide-y divide-gray-200">
                                <!-- Migrations will be loaded here -->
                                <div class="px-6 py-8 text-center">
                                    <i class="fas fa-code-branch text-gray-400 text-3xl mb-3"></i>
                                    <p class="text-sm text-gray-500">Žádné migrace nebyly nalezeny</p>
                                    <button onclick="addNewMigration()" class="mt-3 inline-flex items-center px-3 py-1.5 text-sm font-medium text-orange-600 hover:text-orange-700">
                                        <i class="fas fa-plus mr-1"></i>Přidat první migraci
                                    </button>
                                </div>
                            </div>
                        </div>


                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <!-- Monaco Editor CDN -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/monaco-editor/0.45.0/min/vs/loader.min.js"></script>
    <script>
        // Inicializace Monaco Editor
        require.config({ paths: { vs: 'https://cdnjs.cloudflare.com/ajax/libs/monaco-editor/0.45.0/min/vs' } });
        let monacoEditor = null;
        let currentFilePath = null;
        let currentEditorType = 'controller';
        $(function() {
            // Vlastní implementace tabs s Tailwind styly
            initCustomTabs();
        });

        function initCustomTabs() {
            const tabLinks = document.querySelectorAll('.tab-link');
            const tabContents = document.querySelectorAll('.tab-content');

            // Skryjeme všechny tab obsahy kromě prvního
            tabContents.forEach((content, index) => {
                if (index === 0) {
                    content.classList.remove('hidden');
                } else {
                    content.classList.add('hidden');
                }
            });

            // Nastavíme první tab jako aktivní
            if (tabLinks.length > 0) {
                tabLinks[0].classList.add('tab-active');
            }

            // Přidáme event listenery
            tabLinks.forEach((link, index) => {
                link.addEventListener('click', (e) => {
                    e.preventDefault();

                    // Odstraníme aktivní třídu ze všech tabů
                    tabLinks.forEach(l => l.classList.remove('tab-active'));
                    tabContents.forEach(c => c.classList.add('hidden'));

                    // Přidáme aktivní třídu na kliknutý tab
                    link.classList.add('tab-active');

                    // Zobrazíme odpovídající obsah
                    const targetId = link.getAttribute('href');
                    const targetContent = document.querySelector(targetId);
                    if (targetContent) {
                        targetContent.classList.remove('hidden');
                    }
                });
            });
        }

        function saveBasicInfo() {
            const formData = new FormData($('#basic-form')[0]);
            const data = {};

            // Převedení FormData na objekt
            formData.forEach((value, key) => {
                data[key] = value;
            });

            // Přidat checkbox hodnoty
            data.is_enabled = $('#is_enabled').is(':checked');
            data.is_installed = $('#is_installed').is(':checked');

            $.ajax({
                url: '/settings/modules/{{ $module->getName() }}',
                method: 'POST',
                data: JSON.stringify(data),
                contentType: 'application/json',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                },
                success: function(response) {
                    if (response.success) {
                        // Zobrazit success toast
                        showToast('Základní informace byly úspěšně uloženy', 'success');
                    } else {
                        showToast('Chyba při ukládání: ' + response.message, 'error');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error:', error);
                    showToast('Došlo k chybě při ukládání', 'error');
                }
            });
        }

        function saveAllChanges() {
            saveBasicInfo();
        }

        // ===== FRONTEND TOAST NOTIFICATIONS =====

        // Globální proměnné pro toast systém
        let toastContainer = null;
        let toastCounter = 0;
        let toastPosition = 'bottom-right';

        // Inicializace toast containeru
        function initToastContainer() {
            if (toastContainer) return;

            // Vytvoření containeru pokud neexistuje
            toastContainer = document.getElementById('toast-container');
            if (!toastContainer) {
                toastContainer = document.createElement('div');
                toastContainer.id = 'toast-container';
                toastContainer.style.cssText = `
                    position: fixed;
                    z-index: 9999;
                    bottom: 1rem;
                    right: 1rem;
                    max-width: 400px;
                    pointer-events: none;
                `;
                document.body.appendChild(toastContainer);
            }
        }

        // Hlavní funkce pro zobrazení toast notifikace
        function showToastFE(message, type = 'info', duration = 5000, options = {}) {
            initToastContainer();

            const toastId = 'toast_' + Date.now() + '_' + (++toastCounter);
            const position = options.position || toastPosition;

            // Nastavení pozice containeru
            const containerStyle = getContainerStyle(position);
            toastContainer.style.cssText = containerStyle;

            // Vytvoření toast elementu
            const toastElement = createToastElement(toastId, type, message, position);
            toastContainer.appendChild(toastElement);

            // Animace zobrazení
            setTimeout(() => {
                toastElement.style.visibility = 'visible';
                toastElement.style.transition = 'all 0.5s cubic-bezier(0.215, 0.61, 0.355, 1)';

                setTimeout(() => {
                    toastElement.style.opacity = '1';
                    toastElement.style.transform = 'translateY(0)';
                }, 50);
            }, 100);

            // Auto hide
            if (duration > 0) {
                setTimeout(() => {
                    removeToastFE(toastId);
                }, duration);
            }

            // Close button event
            const closeButton = toastElement.querySelector('.toast-close');
            if (closeButton) {
                closeButton.addEventListener('click', () => {
                    removeToastFE(toastId);
                });
            }

            return toastId;
        }

        // Odstranění toast notifikace
        function removeToastFE(toastId) {
            const toastElement = document.getElementById(toastId);
            if (!toastElement) return;

            toastElement.style.transition = 'all 0.3s cubic-bezier(0.55, 0.055, 0.675, 0.19)';
            toastElement.style.opacity = '0';
            toastElement.style.transform = 'translateY(-20px)';

            setTimeout(() => {
                if (toastElement.parentNode) {
                    toastElement.parentNode.removeChild(toastElement);
                }
            }, 300);
        }

        // Vytvoření toast elementu
        function createToastElement(id, type, message, position) {
            const { bgColor, borderClass, iconColor, icon, title } = getToastStyles(type);

            const toastElement = document.createElement('div');
            toastElement.id = id;
            toastElement.className = `${bgColor} border-l-4 ${borderClass} p-4 mb-4 shadow-lg rounded-lg max-w-sm w-full`;
            toastElement.style.cssText = `
                visibility: hidden;
                opacity: 0;
                transform: translateY(-20px);
                pointer-events: auto;
                margin-bottom: 0.5rem;
            `;
            toastElement.setAttribute('data-position', position);

            toastElement.innerHTML = `
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <div class="${iconColor}">
                            ${icon}
                        </div>
                    </div>
                    <div class="ml-3 flex-1">
                        <div class="text-sm font-medium text-gray-900 mb-1">${title}</div>
                        <div class="text-sm text-gray-600 break-words whitespace-normal">${escapeHtml(message)}</div>
                    </div>
                    <button class="toast-close ml-auto pl-3 -my-1.5 -mr-1.5 bg-transparent rounded-lg focus:ring-2 focus:ring-gray-400 p-1.5 hover:bg-gray-200 inline-flex h-8 w-8 text-gray-500 hover:text-gray-700 focus:outline-none">
                        <span class="sr-only">Zavřít</span>
                        <svg class="w-5 h-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                    </button>
                </div>
            `;

            return toastElement;
        }

        // Získání stylů podle typu toast
        function getToastStyles(type) {
            const styles = {
                success: {
                    bgColor: 'bg-green-50',
                    borderClass: 'border-green-500',
                    iconColor: 'text-green-600',
                    icon: '<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>',
                    title: 'Úspěch'
                },
                error: {
                    bgColor: 'bg-red-50',
                    borderClass: 'border-red-500',
                    iconColor: 'text-red-600',
                    icon: '<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path></svg>',
                    title: 'Chyba'
                },
                warning: {
                    bgColor: 'bg-yellow-50',
                    borderClass: 'border-yellow-500',
                    iconColor: 'text-yellow-500',
                    icon: '<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path></svg>',
                    title: 'Upozornění'
                },
                info: {
                    bgColor: 'bg-blue-50',
                    borderClass: 'border-blue-500',
                    iconColor: 'text-blue-600',
                    icon: '<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path></svg>',
                    title: 'Informace'
                }
            };

            return styles[type] || styles.info;
        }

        // Získání stylu containeru podle pozice
        function getContainerStyle(position) {
            const baseStyle = 'position: fixed; z-index: 9999; max-width: 400px; pointer-events: none;';

            switch (position) {
                case 'top-right':
                    return baseStyle + ' top: 1rem; right: 1rem;';
                case 'top-left':
                    return baseStyle + ' top: 1rem; left: 1rem;';
                case 'bottom-left':
                    return baseStyle + ' bottom: 1rem; left: 1rem;';
                case 'center':
                    return baseStyle + ' top: 50%; left: 50%; transform: translate(-50%, -50%);';
                default:
                    return baseStyle + ' bottom: 1rem; right: 1rem;';
            }
        }

        // Escape HTML pro bezpečné zobrazení
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        // Rychlé metody pro různé typy toast
        function showToastSuccess(message, duration = 5000, options = {}) {
            return showToastFE(message, 'success', duration, options);
        }

        function showToastError(message, duration = 5000, options = {}) {
            return showToastFE(message, 'error', duration, options);
        }

        function showToastWarning(message, duration = 5000, options = {}) {
            return showToastFE(message, 'warning', duration, options);
        }

        function showToastInfo(message, duration = 5000, options = {}) {
            return showToastFE(message, 'info', duration, options);
        }

        // Rychlé aliasy
        function showToastOk(message, duration = 5000, options = {}) {
            return showToastSuccess(message, duration, options);
        }

        function showToastFail(message, duration = 5000, options = {}) {
            return showToastError(message, duration, options);
        }

        function showToastWarn(message, duration = 5000, options = {}) {
            return showToastWarning(message, duration, options);
        }

        // Nastavení pozice toast
        function setToastPosition(position) {
            toastPosition = position;
            if (toastContainer) {
                toastContainer.style.cssText = getContainerStyle(position);
            }
        }

        // Vyčištění všech toast
        function clearAllToasts() {
            if (toastContainer) {
                toastContainer.innerHTML = '';
            }
        }

        // Kompatibilita s původní funkcí
        function showToast(message, type = 'info') {
            return showToastFE(message, type);
        }

        // ===== CONTROLLERS FUNCTIONS =====

        // Načtení controllerů při otevření tabu
        function loadControllers() {
            const moduleName = '{{ $module->getName() }}';

            $.ajax({
                url: `/settings/modules/${moduleName}/controllers`,
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                },
                success: function(response) {
                    if (response.success) {
                        renderControllersList(response.controllers);
                    } else {
                        showToast('Chyba při načítání controllerů: ' + response.message, 'error');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error loading controllers:', error);
                    showToast('Došlo k chybě při načítání controllerů', 'error');
                }
            });
        }

        // Vykreslení seznamu controllerů
        function renderControllersList(controllers) {
            const container = document.getElementById('controllers-list');
            const countElement = document.getElementById('controllers-count');

            if (!controllers || controllers.length === 0) {
                container.innerHTML = `
                    <div class="px-6 py-8 text-center">
                        <i class="fas fa-code text-gray-400 text-3xl mb-3"></i>
                        <p class="text-sm text-gray-500">Žádné controllery nebyly nalezeny</p>
                        <button onclick="addNewController()" class="mt-3 inline-flex items-center px-3 py-1.5 text-sm font-medium text-blue-600 hover:text-blue-700">
                            <i class="fas fa-plus mr-1"></i>Přidat první controller
                        </button>
                    </div>
                `;
                countElement.textContent = '0 controllerů';
                return;
            }

            countElement.textContent = `${controllers.length} controller${controllers.length > 1 ? 'ů' : ''}`;

            container.innerHTML = controllers.map(controller => `
                <div class="px-6 py-4 hover:bg-gray-50 transition-colors duration-200">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-code text-blue-600"></i>
                            </div>
                            <div>
                                <h5 class="text-sm font-medium text-gray-900">${controller.name}</h5>
                                <p class="text-xs text-gray-500">${controller.namespace}</p>
                            </div>
                        </div>
                        <div class="flex items-center space-x-2">
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium ${controller.enabled ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}">
                                <i class="fas ${controller.enabled ? 'fa-check' : 'fa-times'} mr-1"></i>
                                ${controller.enabled ? 'Aktivní' : 'Neaktivní'}
                            </span>
                            <div class="flex items-center space-x-1">
                                <button onclick="editController('${controller.name}')" class="inline-flex items-center p-1.5 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded transition-colors duration-200" title="Upravit">
                                    <i class="fas fa-edit text-sm"></i>
                                </button>
                                <button onclick="openFileEditor('${controller.name}')" class="inline-flex items-center p-1.5 text-gray-400 hover:text-green-600 hover:bg-green-50 rounded transition-colors duration-200" title="Editor souborů">
                                    <i class="fas fa-file-code text-sm"></i>
                                </button>
                                <button onclick="deleteController('${controller.name}')" class="inline-flex items-center p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded transition-colors duration-200" title="Smazat">
                                    <i class="fas fa-trash text-sm"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="mt-3 flex items-center space-x-4 text-xs text-gray-500">
                        <span><i class="fas fa-clock mr-1"></i>Vytvořeno: ${controller.created_at}</span>
                        <span><i class="fas fa-file-code mr-1"></i>${controller.methods_count} metod</span>
                        <span><i class="fas fa-code-branch mr-1"></i>${controller.lines_count} řádků</span>
                    </div>
                </div>
            `).join('');
        }

        // Přidání nového controlleru
        function addNewController() {
            loadModal({
                title: 'Nový Controller',
                content: `
                    <form id="add-controller-form" class="space-y-4">
                        <div>
                            <label for="controller-name" class="block text-sm font-medium text-gray-700">Název Controlleru</label>
                            <input type="text" id="controller-name" name="name" class="mt-1 block w-full" placeholder="ExampleController" required>
                            <p class="mt-1 text-sm text-gray-500">Název bez přípony "Controller"</p>
                        </div>

                        <div>
                            <label for="controller-namespace" class="block text-sm font-medium text-gray-700">Namespace</label>
                            <input type="text" id="controller-namespace" name="namespace" class="mt-1 block w-full" placeholder="Modules\Example\Controllers" value="Modules\{{ $module->getName() }}\Controllers">
                        </div>

                        <div>
                            <label for="controller-extends" class="block text-sm font-medium text-gray-700">Rozšiřuje</label>
                            <select id="controller-extends" name="extends" class="mt-1 block w-full">
                                <option value="Core\Controllers\BaseController">BaseController</option>
                                <option value="Core\Controllers\ApiController">ApiController</option>
                                <option value="Core\Controllers\AdminController">AdminController</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Základní metody</label>
                            <div class="space-y-2">
                                <label class="flex items-center">
                                    <input type="checkbox" name="methods[]" value="index" class="mr-2" checked>
                                    <span class="text-sm text-gray-700">index() - Seznam záznamů</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" name="methods[]" value="show" class="mr-2" checked>
                                    <span class="text-sm text-gray-700">show() - Zobrazení detailu</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" name="methods[]" value="create" class="mr-2">
                                    <span class="text-sm text-gray-700">create() - Vytvoření nového</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" name="methods[]" value="store" class="mr-2">
                                    <span class="text-sm text-gray-700">store() - Uložení nového</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" name="methods[]" value="edit" class="mr-2">
                                    <span class="text-sm text-gray-700">edit() - Editace</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" name="methods[]" value="update" class="mr-2">
                                    <span class="text-sm text-gray-700">update() - Aktualizace</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" name="methods[]" value="destroy" class="mr-2">
                                    <span class="text-sm text-gray-700">destroy() - Smazání</span>
                                </label>
                            </div>
                        </div>
                    </form>
                `,
                size: 'medium',
                footer: '<button type="button" class="btn-secondary" onclick="hideModal()">Zrušit</button>' +
                        '<button type="button" class="btn-primary" onclick="createController()">' +
                        '<i class="fas fa-plus mr-2"></i>Vytvořit Controller</button>'
            });
        }

        // Vytvoření nového controlleru
        function createController() {
            const form = document.getElementById('add-controller-form');
            const formData = new FormData(form);
            const data = {};

            formData.forEach((value, key) => {
                if (key === 'methods[]') {
                    if (!data[key]) data[key] = [];
                    data[key].push(value);
                } else {
                    data[key] = value;
                }
            });

            const moduleName = '{{ $module->getName() }}';

            $.ajax({
                url: `/settings/modules/${moduleName}/controllers`,
                method: 'POST',
                data: JSON.stringify(data),
                contentType: 'application/json',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                },
                success: function(response) {
                    if (response.success) {
                        showToast('Controller byl úspěšně vytvořen', 'success');
                        hideModal();
                        loadControllers();
                    } else {
                        showToast('Chyba při vytváření controlleru: ' + response.message, 'error');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error creating controller:', error);
                    showToast('Došlo k chybě při vytváření controlleru', 'error');
                }
            });
        }

                        // Otevření moderního file editoru s Monaco
        function openFileEditor(name, type = 'controller') {
            const moduleName = '{{ $module->getName() }}';
            currentEditorType = type; // Nastavení typu editoru

            let endpoint = `/settings/modules/${moduleName}/${type}s/${name}/files`;

            $.ajax({
                url: endpoint,
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                },
                success: function(response) {
                    if (response.success) {
                        loadModal({
                            title: `Moderní Editor - ${name}`,
                            content: `
                                <div class="flex h-[80vh] bg-gray-900 rounded-lg overflow-hidden">
                                    <!-- File Tree Sidebar -->
                                    <div class="w-80 bg-gray-800 border-r border-gray-700 flex flex-col">
                                        <!-- Header -->
                                        <div class="px-4 py-3 border-b border-gray-700">
                                            <div class="flex items-center justify-between">
                                                <h4 class="text-sm font-medium text-gray-200">Soubory</h4>
                                                <div class="flex items-center space-x-1">
                                                    <button onclick="refreshFileTree()" class="inline-flex items-center p-1 text-gray-400 hover:text-white hover:bg-gray-700 rounded transition-colors duration-200" title="Obnovit">
                                                        <i class="fas fa-sync-alt text-xs"></i>
                                                    </button>
                                                    <button onclick="createNewFile()" class="inline-flex items-center p-1 text-gray-400 hover:text-white hover:bg-gray-700 rounded transition-colors duration-200" title="Nový soubor">
                                                        <i class="fas fa-plus text-xs"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- File Tree -->
                                        <div class="flex-1 overflow-y-auto">
                                            <div class="p-3">
                                                <div id="file-tree" class="space-y-1">
                                                    ${renderModernFileTreeHTML(response.files)}
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Status Bar -->
                                        <div class="px-4 py-2 border-t border-gray-700 bg-gray-900">
                                            <div class="flex items-center justify-between text-xs text-gray-400">
                                                <span id="file-count">${response.files.length} souborů</span>
                                                <span id="current-file-info">Vyberte soubor</span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Monaco Editor -->
                                    <div class="flex-1 flex flex-col">
                                        <!-- Editor Toolbar -->
                                        <div class="px-4 py-2 bg-gray-800 border-b border-gray-700 flex items-center justify-between">
                                            <div class="flex items-center space-x-4">
                                                <div class="flex items-center space-x-2">
                                                    <span class="text-sm font-medium text-gray-200" id="current-file-name">Vyberte soubor</span>
                                                    <span class="text-xs text-gray-400" id="file-stats"></span>
                                                </div>
                                                <div class="flex items-center space-x-1">
                                                    <span class="text-xs text-gray-500">|</span>
                                                    <span class="text-xs text-gray-400" id="cursor-position">Ln 1, Col 1</span>
                                                </div>
                                            </div>
                                            <div class="flex items-center space-x-2">
                                                <button onclick="formatCode()" class="inline-flex items-center px-3 py-1 text-xs font-medium text-gray-300 hover:text-white hover:bg-gray-700 rounded transition-colors duration-200" title="Ctrl+D">
                                                    <i class="fas fa-magic mr-1"></i>Formátovat
                                                </button>
                                                <button onclick="toggleWordWrap()" class="inline-flex items-center px-3 py-1 text-xs font-medium text-gray-300 hover:text-white hover:bg-gray-700 rounded transition-colors duration-200">
                                                    <i class="fas fa-text-width mr-1"></i>Word Wrap
                                                </button>
                                                <button onclick="toggleMinimap()" class="inline-flex items-center px-3 py-1 text-xs font-medium text-gray-300 hover:text-white hover:bg-gray-700 rounded transition-colors duration-200">
                                                    <i class="fas fa-map mr-1"></i>Minimap
                                                </button>
                                                <div class="flex items-center space-x-1 text-xs text-gray-500">
                                                    <span>Ctrl+S</span>
                                                    <span>|</span>
                                                    <span>Ctrl+F</span>
                                                    <span>|</span>
                                                    <span>Ctrl+D</span>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Editor Container -->
                                        <div class="flex-1 relative">
                                            <div id="monaco-editor-container" class="w-full h-full"></div>
                                            <div id="editor-loading" class="absolute inset-0 bg-gray-900 flex items-center justify-center">
                                                <div class="text-center">
                                                    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500 mx-auto mb-3"></div>
                                                    <p class="text-sm text-gray-400">Načítání editoru...</p>
                                                </div>
                                            </div>
                                            <div id="editor-placeholder" class="absolute inset-0 bg-gray-900 flex items-center justify-center">
                                                <div class="text-center">
                                                    <i class="fas fa-file-code text-gray-600 text-4xl mb-4"></i>
                                                    <p class="text-lg text-gray-400 mb-2">Moderní Code Editor</p>
                                                    <p class="text-sm text-gray-500">Vyberte soubor z levého panelu pro začátek editace</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            `,
                            size: 'fullscreen',
                            footer: `
                                <div class="flex items-center justify-between w-full">
                                    <div class="flex items-center space-x-2 text-sm text-gray-400">
                                        <span id="save-status">Připraveno k uložení</span>
                                        <span id="file-size"></span>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <button type="button" class="btn-secondary" onclick="hideModal()">
                                            <i class="fas fa-times mr-1"></i>Zavřít
                                        </button>
                                        <button type="button" class="btn-primary" onclick="saveFile()" id="save-button">
                                            <i class="fas fa-save mr-1"></i>Uložit
                                        </button>
                                    </div>
                                </div>
                            `
                        });

                        // Inicializace Monaco Editor po zobrazení modalu
                        setTimeout(() => {
                            initMonacoEditor();
                        }, 100);

                        // Cleanup při zavření modalu
                        const modal = document.querySelector('.modal');
                        if (modal) {
                            const observer = new MutationObserver(function(mutations) {
                                mutations.forEach(function(mutation) {
                                    if (mutation.type === 'attributes' && mutation.attributeName === 'style') {
                                        if (modal.style.display === 'none') {
                                            cleanupMonacoEditor();
                                            observer.disconnect();
                                        }
                                    }
                                });
                            });
                            observer.observe(modal, { attributes: true });
                        }
                    } else {
                        showToast('Chyba při načítání souborů: ' + response.message, 'error');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error loading files:', error);
                    showToast('Došlo k chybě při načítání souborů', 'error');
                }
            });
        }

        // Vykreslení moderního file tree HTML
        function renderModernFileTreeHTML(files) {
            return files.map(file => `
                <div class="file-item cursor-pointer p-2 rounded hover:bg-gray-700 transition-colors duration-200 group" onclick="loadFileInMonaco('${file.path}')">
                    <div class="flex items-center space-x-2">
                        <i class="fas ${getFileIcon(file.type)} text-gray-400 group-hover:text-blue-400 text-sm"></i>
                        <span class="text-sm text-gray-300 group-hover:text-white">${file.name}</span>
                        <span class="text-xs text-gray-500 ml-auto">${file.size || '0'} B</span>
                        ${file.placeholder ? '<span class="text-xs text-yellow-400">(nový)</span>' : ''}
                    </div>
                </div>
            `).join('');
        }

        // Získání ikony podle typu souboru
        function getFileIcon(type) {
            const icons = {
                'php': 'fa-file-code',
                'js': 'fa-file-code',
                'css': 'fa-file-code',
                'html': 'fa-file-code',
                'blade': 'fa-file-code',
                'json': 'fa-file-code',
                'sql': 'fa-database',
                'md': 'fa-file-alt',
                'txt': 'fa-file-text',
                'default': 'fa-file'
            };
            return icons[type] || icons.default;
        }

        // Inicializace Monaco Editor
        function initMonacoEditor() {
            require(['vs/editor/editor.main'], function() {
                // Skryjeme loading overlay
                document.getElementById('editor-loading').style.display = 'none';
                document.getElementById('editor-placeholder').style.display = 'block';

                // Konfigurace Monaco Editor
                monaco.editor.defineTheme('arcadia-dark', {
                    base: 'vs-dark',
                    inherit: true,
                    rules: [
                        { token: 'comment', foreground: '6A9955' },
                        { token: 'keyword', foreground: '569CD6' },
                        { token: 'string', foreground: 'CE9178' },
                        { token: 'number', foreground: 'B5CEA8' },
                        { token: 'type', foreground: '4EC9B0' }
                    ],
                    colors: {
                        'editor.background': '#1F2937',
                        'editor.foreground': '#F9FAFB',
                        'editor.lineHighlightBackground': '#374151',
                        'editor.selectionBackground': '#3B82F6',
                        'editor.inactiveSelectionBackground': '#4B5563'
                    }
                });

                // Vytvoření editoru
                monacoEditor = monaco.editor.create(document.getElementById('monaco-editor-container'), {
                    value: '// Vyberte soubor pro začátek editace\n// Select a file to start editing',
                    language: 'php',
                    theme: 'arcadia-dark',
                    automaticLayout: true,
                    fontSize: 14,
                    fontFamily: 'JetBrains Mono, Consolas, Monaco, monospace',
                    lineNumbers: 'on',
                    roundedSelection: false,
                    scrollBeyondLastLine: false,
                    readOnly: false,
                    cursorStyle: 'line',
                    minimap: {
                        enabled: true,
                        side: 'right'
                    },
                    wordWrap: 'off',
                    folding: true,
                    foldingStrategy: 'indentation',
                    showFoldingControls: 'always',
                    renderLineHighlight: 'all',
                    selectOnLineNumbers: true,
                    glyphMargin: true,
                    useTabStops: false,
                    tabSize: 4,
                    insertSpaces: true,
                    detectIndentation: true,
                    trimAutoWhitespace: true,
                    largeFileOptimizations: true,
                    suggest: {
                        showKeywords: true,
                        showSnippets: true,
                        showClasses: true,
                        showFunctions: true,
                        showVariables: true,
                        showModules: true,
                        showProperties: true,
                        showEvents: true,
                        showOperators: true,
                        showUnits: true,
                        showValues: true,
                        showConstants: true,
                        showEnums: true,
                        showEnumMembers: true,
                        showColors: true,
                        showFiles: true,
                        showReferences: true,
                        showFolders: true,
                        showTypeParameters: true,
                        showWords: true
                    }
                });

                // Event listeners
                monacoEditor.onDidChangeCursorPosition(function(e) {
                    updateCursorPosition(e.position);
                });

                monacoEditor.onDidChangeModelContent(function(e) {
                    updateSaveStatus('Neuloženo');
                    updateFileStats();
                });

                // Klávesové zkratky
                monacoEditor.addCommand(monaco.KeyMod.CtrlCmd | monaco.KeyCode.KeyS, function() {
                    saveFile();
                });

                monacoEditor.addCommand(monaco.KeyMod.CtrlCmd | monaco.KeyCode.KeyF, function() {
                    monacoEditor.getAction('actions.find').run();
                });

                monacoEditor.addCommand(monaco.KeyMod.CtrlCmd | monaco.KeyMod.Shift | monaco.KeyCode.KeyF, function() {
                    monacoEditor.getAction('editor.action.startFindReplaceAction').run();
                });

                monacoEditor.addCommand(monaco.KeyMod.CtrlCmd | monaco.KeyCode.KeyD, function() {
                    formatCode();
                });

                // Skryjeme placeholder
                document.getElementById('editor-placeholder').style.display = 'none';
            });
        }

                // Načtení souboru do Monaco Editor
        function loadFileInMonaco(filePath) {

            if (!monacoEditor) {
                showToast('Editor není připraven', 'error');
                return;
            }

            const startsWithUppercase = str => /^[A-Z]/.test(str);

            const moduleName = '{{ $module->getName() }}';

            // Zvýraznění aktivního souboru
            document.querySelectorAll('.file-item').forEach(item => {
                item.classList.remove('bg-blue-600', 'text-white');
                item.classList.add('hover:bg-gray-700');
            });

            event.currentTarget.classList.remove('hover:bg-gray-700');
            event.currentTarget.classList.add('bg-blue-600', 'text-white');

            if (startsWithUppercase(filePath)) {
                const first = filePath.substring(0, 1).toLowerCase();
                filePath = first + filePath.substring(1, filePath.length)
            }

            $.ajax({
                url: `/settings/modules/${moduleName}/files/read`,
                method: 'POST',
                data: JSON.stringify({ path: filePath }),
                contentType: 'application/json',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                },
                success: function(response) {
                    if (response.success) {
                        currentFilePath = filePath;

                        // Nastavení jazyka podle přípony
                        const language = getLanguageFromExtension(filePath);
                        monaco.editor.setModelLanguage(monacoEditor.getModel(), language);

                        // Nastavení obsahu (pokud je soubor prázdný, zobrazíme template)
                        let content = response.content;
                        if (!content || content.trim() === '') {
                            content = generateControllerTemplate(filePath);
                        }

                        monacoEditor.setValue(content);

                        // Aktualizace UI
                        document.getElementById('current-file-name').textContent = filePath.split('/').pop();
                        document.getElementById('file-stats').textContent = `${response.lines || 1} řádků`;
                        document.getElementById('current-file-info').textContent = filePath.split('/').pop();
                        document.getElementById('file-size').textContent = `${formatFileSize(content.length)}`;
                        document.getElementById('save-status').textContent = 'Neuloženo';

                        // Skryjeme placeholder
                        document.getElementById('editor-placeholder').style.display = 'none';

                        // Focus na editor
                        monacoEditor.focus();
                    } else {
                        showToast('Chyba při načítání souboru: ' + response.message, 'error');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error loading file:', error);
                    showToast('Došlo k chybě při načítání souboru', 'error');
                }
            });
        }

        // Uložení souboru z Monaco Editor
        function saveFile() {
            if (!monacoEditor || !currentFilePath) {
                showToast('Není vybrán žádný soubor', 'error');
                return;
            }

            const content = monacoEditor.getValue();
            const moduleName = '{{ $module->getName() }}';

            // Určení typu podle aktuálního kontextu
            let saveEndpoint = `/settings/modules/${moduleName}/files/write`;
            let saveData = {
                path: currentFilePath,
                content: content
            };

            // Použití specifických endpointů podle typu
            if (currentEditorType === 'entity') {
                const entityName = currentFilePath.split('/')[1]?.replace('.php', '') || 'entity';
                saveEndpoint = `/settings/modules/${moduleName}/entities/${entityName}/files/save`;
            } else if (currentEditorType === 'migration') {
                const migrationFile = currentFilePath.split('/').pop();
                saveEndpoint = `/settings/modules/${moduleName}/migrations/${migrationFile}/files/save`;
            }

            // Disable save button během ukládání
            const saveButton = document.getElementById('save-button');
            const originalText = saveButton.innerHTML;
            saveButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i>Ukládám...';
            saveButton.disabled = true;

            $.ajax({
                url: saveEndpoint,
                method: 'POST',
                data: JSON.stringify(saveData),
                contentType: 'application/json',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                },
                success: function(response) {
                    if (response.success) {
                        showToast('Soubor byl úspěšně uložen', 'success');
                        updateSaveStatus('Uloženo');
                        updateFileStats();
                    } else {
                        showToast('Chyba při ukládání souboru: ' + response.message, 'error');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error saving file:', error);
                    showToast('Došlo k chybě při ukládání souboru', 'error');
                },
                complete: function() {
                    // Obnovení save button
                    saveButton.innerHTML = originalText;
                    saveButton.disabled = false;
                }
            });
        }

        // Pomocné funkce pro Monaco Editor
        function updateCursorPosition(position) {
            document.getElementById('cursor-position').textContent = `Ln ${position.lineNumber}, Col ${position.column}`;
        }

        function updateSaveStatus(status) {
            document.getElementById('save-status').textContent = status;
        }

        function updateFileStats() {
            if (monacoEditor) {
                const model = monacoEditor.getModel();
                const lines = model.getLineCount();
                const words = monacoEditor.getValue().split(/\s+/).filter(word => word.length > 0).length;
                document.getElementById('file-stats').textContent = `${lines} řádků, ${words} slov`;
            }
        }

        function formatFileSize(bytes) {
            if (bytes === 0) return '0 B';
            const k = 1024;
            const sizes = ['B', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }

        function getLanguageFromExtension(filePath) {
            const ext = filePath.split('.').pop().toLowerCase();
            const languageMap = {
                'php': 'php',
                'js': 'javascript',
                'ts': 'typescript',
                'html': 'html',
                'css': 'css',
                'scss': 'scss',
                'json': 'json',
                'xml': 'xml',
                'sql': 'sql',
                'md': 'markdown',
                'txt': 'plaintext',
                'blade': 'php'
            };
            return languageMap[ext] || 'plaintext';
        }

        function formatCode() {
            if (!monacoEditor) return;

            // Jednoduché formátování pro PHP
            const content = monacoEditor.getValue();
            const language = monaco.editor.getModelLanguage(monacoEditor.getModel());

            if (language === 'php') {
                // Základní formátování PHP
                let formatted = content
                    .replace(/\s*{\s*/g, ' {\n    ')
                    .replace(/\s*}\s*/g, '\n}\n')
                    .replace(/;\s*/g, ';\n    ')
                    .replace(/\n\s*\n\s*\n/g, '\n\n');

                monacoEditor.setValue(formatted);
                showToast('Kód byl naformátován', 'success');
            } else {
                // Pro ostatní jazyky použijeme built-in formátování
                monacoEditor.getAction('editor.action.formatDocument').run();
                showToast('Kód byl naformátován', 'success');
            }
        }

        function toggleWordWrap() {
            if (!monacoEditor) return;

            const currentWrap = monacoEditor.getOption(monaco.editor.EditorOption.wordWrap);
            const newWrap = currentWrap === 'off' ? 'on' : 'off';
            monacoEditor.updateOptions({ wordWrap: newWrap });

            const button = event.currentTarget;
            if (newWrap === 'on') {
                button.classList.add('bg-blue-600', 'text-white');
                button.classList.remove('text-gray-300', 'hover:text-white', 'hover:bg-gray-700');
            } else {
                button.classList.remove('bg-blue-600', 'text-white');
                button.classList.add('text-gray-300', 'hover:text-white', 'hover:bg-gray-700');
            }
        }

        function toggleMinimap() {
            if (!monacoEditor) return;

            const currentMinimap = monacoEditor.getOption(monaco.editor.EditorOption.minimap).enabled;
            monacoEditor.updateOptions({
                minimap: { enabled: !currentMinimap, side: 'right' }
            });

            const button = event.currentTarget;
            if (!currentMinimap) {
                button.classList.add('bg-blue-600', 'text-white');
                button.classList.remove('text-gray-300', 'hover:text-white', 'hover:bg-gray-700');
            } else {
                button.classList.remove('bg-blue-600', 'text-white');
                button.classList.add('text-gray-300', 'hover:text-white', 'hover:bg-gray-700');
            }
        }

        function refreshFileTree() {
            // TODO: Implementovat obnovení file tree
            showToast('Obnovení file tree bude implementováno', 'info');
        }

        function createNewFile() {
            // TODO: Implementovat vytvoření nového souboru
            showToast('Vytvoření nového souboru bude implementováno', 'info');
        }

        // Generování controller template
        function generateControllerTemplate(filePath) {

        }

        // Cleanup při zavření modalu
        function cleanupMonacoEditor() {
            if (monacoEditor) {
                monacoEditor.dispose();
                monacoEditor = null;
            }
            currentFilePath = null;
            currentEditorType = 'controller';
        }

        // Obnovení controllerů
        function refreshControllers() {
            loadControllers();
            showToast('Controllery byly obnoveny', 'success');
        }

        // Editace controlleru
        function editController(controllerName) {
            // TODO: Implementovat editaci controlleru
            showToast('Editace controlleru bude implementována', 'info');
        }

        // Smazání controlleru
        function deleteController(controllerName) {
            if (!confirm(`Opravdu chcete smazat controller "${controllerName}"?`)) {
                return;
            }

            const moduleName = '{{ $module->getName() }}';

            $.ajax({
                url: `/settings/modules/${moduleName}/controllers/${controllerName}`,
                method: 'DELETE',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                },
                success: function(response) {
                    if (response.success) {
                        showToast('Controller byl úspěšně smazán', 'success');
                        loadControllers();
                    } else {
                        showToast('Chyba při mazání controlleru: ' + response.message, 'error');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error deleting controller:', error);
                    showToast('Došlo k chybě při mazání controlleru', 'error');
                }
            });
        }

        // Načtení controllerů při otevření controllers tabu
        document.addEventListener('DOMContentLoaded', function() {
            // Načteme controllery při prvním otevření tabu
            const controllersTab = document.querySelector('a[href="#tab-controllers"]');
            if (controllersTab) {
                controllersTab.addEventListener('click', function() {
                    loadControllers();
                });
            }

            // Načteme entity při otevření entities tabu
            const entitiesTab = document.querySelector('a[href="#tab-entities"]');
            if (entitiesTab) {
                entitiesTab.addEventListener('click', function() {
                    loadEntities();
                });
            }

            // Načteme migrace při otevření migrations tabu
            const migrationsTab = document.querySelector('a[href="#tab-migrations"]');
            if (migrationsTab) {
                migrationsTab.addEventListener('click', function() {
                    loadMigrations();
                });
            }
        });

        // ===== ENTITIES FUNCTIONS =====

        // Načtení entit
        function loadEntities() {
            const moduleName = '{{ $module->getName() }}';

            $.ajax({
                url: `/settings/modules/${moduleName}/entities`,
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                },
                success: function(response) {
                    if (response.success) {
                        renderEntitiesList(response.entities);
                    } else {
                        showToast('Chyba při načítání entit: ' + response.message, 'error');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error loading entities:', error);
                    showToast('Došlo k chybě při načítání entit', 'error');
                }
            });
        }

        // Vykreslení seznamu entit
        function renderEntitiesList(entities) {
            const container = document.getElementById('entities-list');
            const countElement = document.getElementById('entities-count');

            if (!entities || entities.length === 0) {
                container.innerHTML = `
                    <div class="px-6 py-8 text-center">
                        <i class="fas fa-database text-gray-400 text-3xl mb-3"></i>
                        <p class="text-sm text-gray-500">Žádné entity nebyly nalezeny</p>
                        <button onclick="addNewEntity()" class="mt-3 inline-flex items-center px-3 py-1.5 text-sm font-medium text-purple-600 hover:text-purple-700">
                            <i class="fas fa-plus mr-1"></i>Přidat první entity
                        </button>
                    </div>
                `;
                countElement.textContent = '0 entit';
                return;
            }

            countElement.textContent = `${entities.length} entit${entities.length > 1 ? 'y' : ''}`;

            container.innerHTML = entities.map(entity => `
                <div class="px-6 py-4 hover:bg-gray-50 transition-colors duration-200">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-database text-purple-600"></i>
                            </div>
                            <div>
                                <h5 class="text-sm font-medium text-gray-900">${entity.name}</h5>
                                <p class="text-xs text-gray-500">${entity.namespace}</p>
                            </div>
                        </div>
                        <div class="flex items-center space-x-2">
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium ${entity.table_exists ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}">
                                <i class="fas ${entity.table_exists ? 'fa-check' : 'fa-times'} mr-1"></i>
                                ${entity.table_exists ? 'Tabulka existuje' : 'Tabulka chybí'}
                            </span>
                            <div class="flex items-center space-x-1">
                                <button onclick="editEntity('${entity.name}')" class="inline-flex items-center p-1.5 text-gray-400 hover:text-purple-600 hover:bg-purple-50 rounded transition-colors duration-200" title="Upravit">
                                    <i class="fas fa-edit text-sm"></i>
                                </button>
                                <button onclick="openFileEditor('${entity.name}', 'entity')" class="inline-flex items-center p-1.5 text-gray-400 hover:text-green-600 hover:bg-green-50 rounded transition-colors duration-200" title="Editor souborů">
                                    <i class="fas fa-file-code text-sm"></i>
                                </button>
                                <button onclick="deleteEntity('${entity.name}')" class="inline-flex items-center p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded transition-colors duration-200" title="Smazat">
                                    <i class="fas fa-trash text-sm"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="mt-3 flex items-center space-x-4 text-xs text-gray-500">
                        <span><i class="fas fa-table mr-1"></i>Tabulka: ${entity.table}</span>
                        <span><i class="fas fa-clock mr-1"></i>Vytvořeno: ${entity.created_at}</span>
                        <span><i class="fas fa-columns mr-1"></i>${entity.columns_count} sloupců</span>
                        <span><i class="fas fa-code-branch mr-1"></i>${entity.lines_count} řádků</span>
                    </div>
                </div>
            `).join('');
        }

        // Přidání nové entity
        function addNewEntity() {
            loadModal({
                title: 'Nová Entity',
                content: `
                    <form id="add-entity-form" class="space-y-4">
                        <div>
                            <label for="entity-name" class="block text-sm font-medium text-gray-700">Název Entity</label>
                            <input type="text" id="entity-name" name="name" class="mt-1 block w-full" placeholder="Example" required>
                            <p class="mt-1 text-sm text-gray-500">Název entity v jednotném čísle</p>
                        </div>

                        <div>
                            <label for="entity-table" class="block text-sm font-medium text-gray-700">Název tabulky</label>
                            <input type="text" id="entity-table" name="table" class="mt-1 block w-full" placeholder="examples" required>
                            <p class="mt-1 text-sm text-gray-500">Název databázové tabulky</p>
                        </div>

                        <div>
                            <label for="entity-namespace" class="block text-sm font-medium text-gray-700">Namespace</label>
                            <input type="text" id="entity-namespace" name="namespace" class="mt-1 block w-full" placeholder="Modules\Example\Models" value="Modules\{{ $module->getName() }}\Models">
                        </div>

                        <div>
                            <label for="entity-extends" class="block text-sm font-medium text-gray-700">Rozšiřuje</label>
                            <select id="entity-extends" name="extends" class="mt-1 block w-full">
                                <option value="Core\Models\BaseModel">BaseModel</option>
                                <option value="Core\Models\User">User</option>
                                <option value="Core\Models\TimestampsModel">TimestampsModel</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Základní vlastnosti</label>
                            <div class="space-y-2">
                                <label class="flex items-center">
                                    <input type="checkbox" name="properties[]" value="id" class="mr-2" checked disabled>
                                    <span class="text-sm text-gray-700">id - Primární klíč</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" name="properties[]" value="timestamps" class="mr-2" checked>
                                    <span class="text-sm text-gray-700">created_at, updated_at</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" name="properties[]" value="soft_deletes" class="mr-2">
                                    <span class="text-sm text-gray-700">deleted_at - Soft deletes</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" name="properties[]" value="fillable" class="mr-2" checked>
                                    <span class="text-sm text-gray-700">$fillable - Mass assignment</span>
                                </label>
                            </div>
                        </div>
                    </form>
                `,
                size: 'medium',
                footer: '<button type="button" class="btn-secondary" onclick="hideModal()">Zrušit</button>' +
                        '<button type="button" class="btn-primary" onclick="createEntity()">' +
                        '<i class="fas fa-plus mr-2"></i>Vytvořit Entity</button>'
            });
        }

        // Vytvoření nové entity
        function createEntity() {
            const form = document.getElementById('add-entity-form');
            const formData = new FormData(form);
            const data = {};

            formData.forEach((value, key) => {
                if (key === 'properties[]') {
                    if (!data[key]) data[key] = [];
                    data[key].push(value);
                } else {
                    data[key] = value;
                }
            });

            const moduleName = '{{ $module->getName() }}';

            $.ajax({
                url: `/settings/modules/${moduleName}/entities`,
                method: 'POST',
                data: JSON.stringify(data),
                contentType: 'application/json',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                },
                success: function(response) {
                    if (response.success) {
                        showToast('Entity byla úspěšně vytvořena', 'success');
                        hideModal();
                        loadEntities();
                    } else {
                        showToast('Chyba při vytváření entity: ' + response.message, 'error');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error creating entity:', error);
                    showToast('Došlo k chybě při vytváření entity', 'error');
                }
            });
        }

        // Obnovení entit
        function refreshEntities() {
            loadEntities();
            showToast('Entity byly obnoveny', 'success');
        }

        // Editace entity
        function editEntity(entityName) {
            // TODO: Implementovat editaci entity
            showToast('Editace entity bude implementována', 'info');
        }

        // Smazání entity
        function deleteEntity(entityName) {
            if (!confirm(`Opravdu chcete smazat entity "${entityName}"?`)) {
                return;
            }

            const moduleName = '{{ $module->getName() }}';

            $.ajax({
                url: `/settings/modules/${moduleName}/entities/${entityName}`,
                method: 'DELETE',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                },
                success: function(response) {
                    if (response.success) {
                        showToast('Entity byla úspěšně smazána', 'success');
                        loadEntities();
                    } else {
                        showToast('Chyba při mazání entity: ' + response.message, 'error');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error deleting entity:', error);
                    showToast('Došlo k chybě při mazání entity', 'error');
                }
            });
        }

        // ===== MIGRATIONS FUNCTIONS =====

        // Načtení migrací
        function loadMigrations() {
            const moduleName = '{{ $module->getName() }}';

            $.ajax({
                url: `/settings/modules/${moduleName}/migrations`,
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                },
                success: function(response) {
                    if (response.success) {
                        renderMigrationsList(response.migrations);
                        updateMigrationStatus(response.status);
                    } else {
                        showToast('Chyba při načítání migrací: ' + response.message, 'error');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error loading migrations:', error);
                    showToast('Došlo k chybě při načítání migrací', 'error');
                }
            });
        }

        // Aktualizace stavu migrací
        function updateMigrationStatus(status) {
            document.getElementById('total-migrations').textContent = status.total || 0;
            document.getElementById('ran-migrations').textContent = status.ran || 0;
            document.getElementById('pending-migrations').textContent = status.pending || 0;
            document.getElementById('failed-migrations').textContent = status.failed || 0;
        }

        // Vykreslení seznamu migrací
        function renderMigrationsList(migrations) {
            const container = document.getElementById('migrations-list');
            const countElement = document.getElementById('migrations-count');

            if (!migrations || migrations.length === 0) {
                container.innerHTML = `
                    <div class="px-6 py-8 text-center">
                        <i class="fas fa-code-branch text-gray-400 text-3xl mb-3"></i>
                        <p class="text-sm text-gray-500">Žádné migrace nebyly nalezeny</p>
                        <button onclick="addNewMigration()" class="mt-3 inline-flex items-center px-3 py-1.5 text-sm font-medium text-orange-600 hover:text-orange-700">
                            <i class="fas fa-plus mr-1"></i>Přidat první migraci
                        </button>
                    </div>
                `;
                countElement.textContent = '0 migrací';
                return;
            }

            countElement.textContent = `${migrations.length} migrací`;

            container.innerHTML = migrations.map(migration => `
                <div class="px-6 py-4 hover:bg-gray-50 transition-colors duration-200">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 ${getMigrationStatusColor(migration.status)} rounded-lg flex items-center justify-center">
                                <i class="fas ${getMigrationStatusIcon(migration.status)} text-white"></i>
                            </div>
                            <div>
                                <h5 class="text-sm font-medium text-gray-900">${migration.name}</h5>
                                <p class="text-xs text-gray-500">${migration.file}</p>
                            </div>
                        </div>
                        <div class="flex items-center space-x-2">
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium ${getMigrationStatusBadge(migration.status)}">
                                <i class="fas ${getMigrationStatusIcon(migration.status)} mr-1"></i>
                                ${getMigrationStatusText(migration.status)}
                            </span>
                            <div class="flex items-center space-x-1">
                                ${migration.status === 'pending' ? `
                                    <button onclick="runMigration('${migration.file}')" class="inline-flex items-center p-1.5 text-gray-400 hover:text-green-600 hover:bg-green-50 rounded transition-colors duration-200" title="Spustit">
                                        <i class="fas fa-play text-sm"></i>
                                    </button>
                                ` : ''}
                                ${migration.status === 'ran' ? `
                                    <button onclick="rollbackMigration('${migration.file}')" class="inline-flex items-center p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded transition-colors duration-200" title="Rollback">
                                        <i class="fas fa-undo text-sm"></i>
                                    </button>
                                ` : ''}
                                <button onclick="viewMigrationLog('${migration.file}')" class="inline-flex items-center p-1.5 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded transition-colors duration-200" title="Log">
                                    <i class="fas fa-file-alt text-sm"></i>
                                </button>
                                <button onclick="openFileEditor('${migration.file}', 'migration')" class="inline-flex items-center p-1.5 text-gray-400 hover:text-green-600 hover:bg-green-50 rounded transition-colors duration-200" title="Editor souborů">
                                    <i class="fas fa-file-code text-sm"></i>
                                </button>
                                <button onclick="deleteMigration('${migration.file}')" class="inline-flex items-center p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded transition-colors duration-200" title="Smazat">
                                    <i class="fas fa-trash text-sm"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="mt-3 flex items-center space-x-4 text-xs text-gray-500">
                        <span><i class="fas fa-clock mr-1"></i>Vytvořeno: ${migration.created_at}</span>
                        <span><i class="fas fa-code-branch mr-1"></i>${migration.lines_count} řádků</span>
                        ${migration.status === 'ran' ? `<span><i class="fas fa-calendar-check mr-1"></i>Spuštěno: ${migration.ran_at}</span>` : ''}
                        ${migration.status === 'failed' ? `<span><i class="fas fa-exclamation-triangle mr-1"></i>Chyba: ${migration.error}</span>` : ''}
                    </div>
                </div>
            `).join('');
        }

        // Získání barvy podle stavu migrace
        function getMigrationStatusColor(status) {
            const colors = {
                'pending': 'bg-yellow-500',
                'ran': 'bg-green-500',
                'failed': 'bg-red-500',
                'rolled_back': 'bg-gray-500'
            };
            return colors[status] || 'bg-gray-500';
        }

        // Získání ikony podle stavu migrace
        function getMigrationStatusIcon(status) {
            const icons = {
                'pending': 'fa-clock',
                'ran': 'fa-check',
                'failed': 'fa-times',
                'rolled_back': 'fa-undo'
            };
            return icons[status] || 'fa-question';
        }

        // Získání badge podle stavu migrace
        function getMigrationStatusBadge(status) {
            const badges = {
                'pending': 'bg-yellow-100 text-yellow-800',
                'ran': 'bg-green-100 text-green-800',
                'failed': 'bg-red-100 text-red-800',
                'rolled_back': 'bg-gray-100 text-gray-800'
            };
            return badges[status] || 'bg-gray-100 text-gray-800';
        }

        // Získání textu podle stavu migrace
        function getMigrationStatusText(status) {
            const texts = {
                'pending': 'Čekající',
                'ran': 'Spuštěná',
                'failed': 'Chyba',
                'rolled_back': 'Vrácená'
            };
            return texts[status] || 'Neznámý';
        }

        // Přidání nové migrace
        function addNewMigration() {
            loadModal({
                title: 'Nová Migration',
                content: `
                    <form id="add-migration-form" class="space-y-4">
                        <div>
                            <label for="migration-name" class="block text-sm font-medium text-gray-700">Název Migration</label>
                            <input type="text" id="migration-name" name="name" class="mt-1 block w-full" placeholder="create_examples_table" required>
                            <p class="mt-1 text-sm text-gray-500">Popisný název migrace</p>
                        </div>

                        <div>
                            <label for="migration-type" class="block text-sm font-medium text-gray-700">Typ migrace</label>
                            <select id="migration-type" name="type" class="mt-1 block w-full" onchange="updateMigrationTemplate()">
                                <option value="create_table">Vytvoření tabulky</option>
                                <option value="modify_table">Úprava tabulky</option>
                                <option value="drop_table">Smazání tabulky</option>
                                <option value="add_column">Přidání sloupce</option>
                                <option value="modify_column">Úprava sloupce</option>
                                <option value="drop_column">Smazání sloupce</option>
                                <option value="add_index">Přidání indexu</option>
                                <option value="drop_index">Smazání indexu</option>
                                <option value="custom">Vlastní SQL</option>
                            </select>
                        </div>

                        <div id="table-name-group">
                            <label for="migration-table" class="block text-sm font-medium text-gray-700">Název tabulky</label>
                            <input type="text" id="migration-table" name="table" class="mt-1 block w-full" placeholder="examples">
                        </div>

                        <div id="custom-sql-group" class="hidden">
                            <label for="migration-sql" class="block text-sm font-medium text-gray-700">Vlastní SQL</label>
                            <textarea id="migration-sql" name="sql" rows="4" class="mt-1 block w-full font-mono text-sm" placeholder="-- Zde napište vlastní SQL kód"></textarea>
                        </div>
                    </form>
                `,
                size: 'medium',
                footer: '<button type="button" class="btn-secondary" onclick="hideModal()">Zrušit</button>' +
                        '<button type="button" class="btn-primary" onclick="createMigration()">' +
                        '<i class="fas fa-plus mr-2"></i>Vytvořit Migration</button>'
            });
        }

        // Aktualizace šablony migrace
        function updateMigrationTemplate() {
            const type = document.getElementById('migration-type').value;
            const tableGroup = document.getElementById('table-name-group');
            const sqlGroup = document.getElementById('custom-sql-group');

            if (type === 'custom') {
                tableGroup.classList.add('hidden');
                sqlGroup.classList.remove('hidden');
            } else {
                tableGroup.classList.remove('hidden');
                sqlGroup.classList.add('hidden');
            }
        }

        // Vytvoření nové migrace
        function createMigration() {
            const form = document.getElementById('add-migration-form');
            const formData = new FormData(form);
            const data = {};

            formData.forEach((value, key) => {
                data[key] = value;
            });

            const moduleName = '{{ $module->getName() }}';

            $.ajax({
                url: `/settings/modules/${moduleName}/migrations`,
                method: 'POST',
                data: JSON.stringify(data),
                contentType: 'application/json',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                },
                success: function(response) {
                    if (response.success) {
                        showToast('Migration byla úspěšně vytvořena', 'success');
                        hideModal();
                        loadMigrations();
                    } else {
                        showToast('Chyba při vytváření migrace: ' + response.message, 'error');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error creating migration:', error);
                    showToast('Došlo k chybě při vytváření migrace', 'error');
                }
            });
        }

        // Spuštění migrace
        function runMigration(migrationFile) {
            const moduleName = '{{ $module->getName() }}';

            $.ajax({
                url: `/settings/modules/${moduleName}/migrations/${migrationFile}/run`,
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                },
                success: function(response) {
                    if (response.success) {
                        showToast('Migration byla úspěšně spuštěna', 'success');
                        loadMigrations();
                    } else {
                        showToast('Chyba při spouštění migrace: ' + response.message, 'error');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error running migration:', error);
                    showToast('Došlo k chybě při spouštění migrace', 'error');
                }
            });
        }

        // Rollback migrace
        function rollbackMigration(migrationFile) {
            if (!confirm(`Opravdu chcete vrátit migraci "${migrationFile}"?`)) {
                return;
            }

            const moduleName = '{{ $module->getName() }}';

            $.ajax({
                url: `/settings/modules/${moduleName}/migrations/${migrationFile}/rollback`,
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                },
                success: function(response) {
                    if (response.success) {
                        showToast('Migration byla úspěšně vrácena', 'success');
                        loadMigrations();
                    } else {
                        showToast('Chyba při vracení migrace: ' + response.message, 'error');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error rolling back migration:', error);
                    showToast('Došlo k chybě při vracení migrace', 'error');
                }
            });
        }

        // Spuštění všech migrací
        function runAllMigrations() {
            if (!confirm('Opravdu chcete spustit všechny čekající migrace?')) {
                return;
            }

            const moduleName = '{{ $module->getName() }}';

            $.ajax({
                url: `/settings/modules/${moduleName}/migrations/run-all`,
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                },
                success: function(response) {
                    if (response.success) {
                        showToast('Všechny migrace byly úspěšně spuštěny', 'success');
                        loadMigrations();
                    } else {
                        showToast('Chyba při spouštění migrací: ' + response.message, 'error');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error running all migrations:', error);
                    showToast('Došlo k chybě při spouštění migrací', 'error');
                }
            });
        }

                // Zobrazení logu migrace
        function viewMigrationLog(migrationFile) {
            const moduleName = '{{ $module->getName() }}';

            $.ajax({
                url: `/settings/modules/${moduleName}/migrations/${migrationFile}/log`,
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                },
                success: function(response) {
                    if (response.success) {
                        loadModal({
                            title: `Migration Log - ${migrationFile}`,
                            content: `
                                <div class="bg-gray-900 text-green-400 p-4 rounded-lg font-mono text-sm h-96 overflow-y-auto">
                                    ${response.log}
                                </div>
                            `,
                            size: 'large',
                            footer: '<button type="button" class="btn-secondary" onclick="hideModal()">Zavřít</button>'
                        });
                    } else {
                        showToast('Chyba při načítání logu: ' + response.message, 'error');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error loading migration log:', error);
                    showToast('Došlo k chybě při načítání logu', 'error');
                }
            });
        }

        // Obnovení migrací
        function refreshMigrations() {
            loadMigrations();
            showToast('Migrace byly obnoveny', 'success');
        }

        // Smazání migrace
        function deleteMigration(migrationFile) {
            if (!confirm(`Opravdu chcete smazat migraci "${migrationFile}"?`)) {
                return;
            }

            const moduleName = '{{ $module->getName() }}';

            $.ajax({
                url: `/settings/modules/${moduleName}/migrations/${migrationFile}`,
                method: 'DELETE',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                },
                success: function(response) {
                    if (response.success) {
                        showToast('Migration byla úspěšně smazána', 'success');
                        loadMigrations();
                    } else {
                        showToast('Chyba při mazání migrace: ' + response.message, 'error');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error deleting migration:', error);
                    showToast('Došlo k chybě při mazání migrace', 'error');
                }
            });
        }
    </script>
    @endpush
@endsection
