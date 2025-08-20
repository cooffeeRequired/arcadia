@extends('layouts.app')

@section('title', 'Správa modulů')

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
                            <h1 class="text-2xl font-bold text-gray-900">Správa modulů</h1>
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                                <i class="fas fa-cog mr-1"></i>{{ count($modules ?? []) }} modulů
                            </span>
                        </div>
                        <div class="flex items-center space-x-4 mt-1">
                            <p class="text-sm text-gray-600">
                                <i class="fas fa-info-circle mr-1"></i>Zapínání, vypínání a konfigurace modulů systému
                            </p>
                        </div>
                    </div>
                </div>
                <div class="flex items-center space-x-3">
                    <a href="/settings/modules/sync" class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-gradient-to-r from-blue-600 to-blue-700 border border-transparent rounded-lg hover:from-blue-700 hover:to-blue-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 shadow-sm">
                        <i class="fas fa-sync-alt mr-2"></i>Synchronizovat
                    </a>
                </div>
            </div>
        </div>

        <!-- Module Status Bar -->
        <div class="px-6 py-3 bg-gray-50 rounded-b-lg">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-6">
                    <div class="flex items-center space-x-2">
                        <span class="text-sm font-medium text-gray-700">Celkem:</span>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                            {{ count($modules ?? []) }} modulů
                        </span>
                    </div>
                    <div class="flex items-center space-x-2">
                        <span class="text-sm font-medium text-gray-700">Aktivní:</span>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                            {{ count(array_filter($modules ?? [], fn($m) => $m->isInstalled() && $m->isEnabled())) }} modulů
                        </span>
                    </div>
                    <div class="flex items-center space-x-2">
                        <span class="text-sm font-medium text-gray-700">Neinstalované:</span>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                            {{ count(array_filter($modules ?? [], fn($m) => !$m->isInstalled())) }} modulů
                        </span>
                    </div>
                </div>
                <div class="flex items-center space-x-2 text-sm text-gray-500">
                    <i class="fas fa-clock mr-1"></i>
                    <span>Poslední aktualizace: {{ date('d.m.Y H:i') }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Enhanced Tabs -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
        <div id="modules-tabs" class="module-tabs">
            <ul class="flex border-b border-gray-200 overflow-x-auto">
                <li class="mr-1 flex-shrink-0">
                    <a href="#tab-all" class="tab-link">
                        <i class="fas fa-th-large mr-2"></i>Všechny moduly
                    </a>
                </li>
                <li class="mr-1 flex-shrink-0">
                    <a href="#tab-active" class="tab-link">
                        <i class="fas fa-check-circle mr-2"></i>Aktivní
                    </a>
                </li>
                <li class="mr-1 flex-shrink-0">
                    <a href="#tab-inactive" class="tab-link">
                        <i class="fas fa-pause-circle mr-2"></i>Vypnuté
                    </a>
                </li>
                <li class="mr-1 flex-shrink-0">
                    <a href="#tab-uninstalled" class="tab-link">
                        <i class="fas fa-download mr-2"></i>Neinstalované
                    </a>
                </li>
            </ul>

            <!-- Všechny moduly -->
            <div id="tab-all" class="tab-content">
                <div class="p-6">
                    @if(empty($modules))
                        <div class="text-center py-12">
                            <i class="fas fa-puzzle-piece text-gray-400 text-6xl mb-4"></i>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">Žádné moduly nebyly nalezeny</h3>
                            <p class="text-gray-500">Přidejte moduly do složky modules/ a klikněte na Synchronizovat</p>
                        </div>
                    @else
                        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                            @foreach($modules as $module)
                                @include('settings.modules.partials.module-card', ['module' => $module])
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            <!-- Aktivní moduly -->
            <div id="tab-active" class="tab-content">
                <div class="p-6">
                    @php
                        $activeModules = array_filter($modules ?? [], fn($m) => $m->isInstalled() && $m->isEnabled());
                    @endphp
                    @if(empty($activeModules))
                        <div class="text-center py-12">
                            <i class="fas fa-check-circle text-gray-400 text-6xl mb-4"></i>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">Žádné aktivní moduly</h3>
                            <p class="text-gray-500">Nainstalujte a zapněte moduly pro zobrazení v této sekci</p>
                        </div>
                    @else
                        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                            @foreach($activeModules as $module)
                                @include('settings.modules.partials.module-card', ['module' => $module])
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            <!-- Vypnuté moduly -->
            <div id="tab-inactive" class="tab-content">
                <div class="p-6">
                    @php
                        $inactiveModules = array_filter($modules ?? [], fn($m) => $m->isInstalled() && !$m->isEnabled());
                    @endphp
                    @if(empty($inactiveModules))
                        <div class="text-center py-12">
                            <i class="fas fa-pause-circle text-gray-400 text-6xl mb-4"></i>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">Žádné vypnuté moduly</h3>
                            <p class="text-gray-500">Všechny nainstalované moduly jsou aktivní</p>
                        </div>
                    @else
                        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                            @foreach($inactiveModules as $module)
                                @include('settings.modules.partials.module-card', ['module' => $module])
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            <!-- Neinstalované moduly -->
            <div id="tab-uninstalled" class="tab-content">
                <div class="p-6">
                    @php
                        $uninstalledModules = array_filter($modules ?? [], fn($m) => !$m->isInstalled());
                    @endphp
                    @if(empty($uninstalledModules))
                        <div class="text-center py-12">
                            <i class="fas fa-download text-gray-400 text-6xl mb-4"></i>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">Žádné neinstalované moduly</h3>
                            <p class="text-gray-500">Všechny dostupné moduly jsou již nainstalovány</p>
                        </div>
                    @else
                        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                            @foreach($uninstalledModules as $module)
                                @include('settings.modules.partials.module-card', ['module' => $module])
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Inicializace tabs při načtení stránky
document.addEventListener('DOMContentLoaded', function() {
    initCustomTabs();
});

// Vlastní implementace tabs s Tailwind styly
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

function toggleDropdown(moduleName) {
    const dropdown = document.getElementById('dropdown-' + moduleName);
    const allDropdowns = document.querySelectorAll('[id^="dropdown-"]');

    // Zavřít všechny ostatní dropdowny
    allDropdowns.forEach(d => {
        if (d.id !== 'dropdown-' + moduleName) {
            d.classList.add('hidden');
        }
    });

    // Přepnout aktuální dropdown
    dropdown.classList.toggle('hidden');
}

// Zavřít dropdowny při kliknutí mimo
document.addEventListener('click', function(event) {
    if (!event.target.closest('[onclick*="toggleDropdown"]')) {
        document.querySelectorAll('[id^="dropdown-"]').forEach(dropdown => {
            dropdown.classList.add('hidden');
        });
    }
});
</script>
@endsection
