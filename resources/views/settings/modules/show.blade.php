@extends('layouts.app')

@section('title', 'Detail modulu - ' . $module->getDisplayName())

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
                            <h1 class="text-2xl font-bold text-gray-900">Detail modulu</h1>
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
                    @if(!$module->isInstalled())
                        <a href="/settings/modules/{{ $module->getName() }}/install" class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-gradient-to-r from-green-600 to-green-700 border border-transparent rounded-lg hover:from-green-700 hover:to-green-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-all duration-200 shadow-sm">
                            <i class="fas fa-download mr-2"></i>Nainstalovat
                        </a>
                    @else
                        @if($module->isEnabled())
                            <a href="/settings/modules/{{ $module->getName() }}/disable" class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-gradient-to-r from-yellow-600 to-yellow-700 border border-transparent rounded-lg hover:from-yellow-700 hover:to-yellow-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500 transition-all duration-200 shadow-sm">
                                <i class="fas fa-toggle-off mr-2"></i>Vypnout
                            </a>
                        @else
                            <a href="/settings/modules/{{ $module->getName() }}/enable" class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-gradient-to-r from-green-600 to-green-700 border border-transparent rounded-lg hover:from-green-700 hover:to-green-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-all duration-200 shadow-sm">
                                <i class="fas fa-toggle-on mr-2"></i>Zapnout
                            </a>
                        @endif
                        <a href="/settings/modules/{{ $module->getName() }}/uninstall" class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-gradient-to-r from-red-600 to-red-700 border border-transparent rounded-lg hover:from-red-700 hover:to-red-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-all duration-200 shadow-sm">
                            <i class="fas fa-trash mr-2"></i>Odinstalovat
                        </a>
                    @endif
                </div>
            </div>
        </div>

        <!-- Module Status Bar -->
        <div class="px-6 py-3 bg-gray-50 rounded-b-lg">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-6">
                    <div class="flex items-center space-x-2">
                        <span class="text-sm font-medium text-gray-700">Stav:</span>
                        @if($module->isInstalled())
                            @if($module->isEnabled())
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    <i class="fas fa-check-circle mr-1"></i>Povolen
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                    <i class="fas fa-times-circle mr-1"></i>Vypnut
                                </span>
                            @endif
                        @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                <i class="fas fa-exclamation-triangle mr-1"></i>Nenainstalován
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
                    <span>Poslední úprava: {{ $module->getUpdatedAt()->format('d.m.Y H:i:s') }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Enhanced Tabs -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
        <div id="module-detail-tabs" class="module-tabs">
            <ul class="flex border-b border-gray-200 overflow-x-auto">
                <li class="mr-1 flex-shrink-0">
                    <a href="#tab-overview" class="tab-link">
                        <i class="fas fa-info-circle mr-2"></i>Přehled
                    </a>
                </li>
                <li class="mr-1 flex-shrink-0">
                    <a href="#tab-controllers" class="tab-link">
                        <i class="fas fa-cogs mr-2"></i>Controllers
                    </a>
                </li>
                <li class="mr-1 flex-shrink-0">
                    <a href="#tab-entities" class="tab-link">
                        <i class="fas fa-database mr-2"></i>Entities
                    </a>
                </li>
                <li class="mr-1 flex-shrink-0">
                    <a href="#tab-migrations" class="tab-link">
                        <i class="fas fa-code-branch mr-2"></i>Migrations
                    </a>
                </li>
                <li class="mr-1 flex-shrink-0">
                    <a href="#tab-files" class="tab-link">
                        <i class="fas fa-file-code mr-2"></i>Soubory
                    </a>
                </li>
            </ul>

            <!-- Přehled -->
            <div id="tab-overview" class="tab-content">
                <div class="p-6">
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                        <!-- Základní informace -->
                        <div class="lg:col-span-2">
                            <div class="bg-gray-50 rounded-lg p-6">
                                <h4 class="text-lg font-medium text-gray-900 mb-4">Základní informace</h4>
                                <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Název</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ $module->getName() }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Zobrazovaný název</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ $module->getDisplayName() }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Verze</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ $module->getVersion() }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Autor</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ $module->getAuthor() ?? 'Neznámý' }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Stav</dt>
                                        <dd class="mt-1">
                                            @if($module->isInstalled())
                                                @if($module->isEnabled())
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                        <i class="fas fa-check-circle mr-1"></i>Aktivní
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                        <i class="fas fa-pause-circle mr-1"></i>Vypnutý
                                                    </span>
                                                @endif
                                            @else
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                    <i class="fas fa-times-circle mr-1"></i>Neinstalovaný
                                                </span>
                                            @endif
                                        </dd>
                                    </div>
                                    @if($module->getInstallDate())
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Datum instalace</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ $module->getInstallDate()->format('d.m.Y H:i:s') }}</dd>
                                    </div>
                                    @endif
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Poslední aktualizace</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ $module->getUpdatedAt()->format('d.m.Y H:i:s') }}</dd>
                                    </div>
                                </dl>

                                @if($module->getDependencies())
                                <div class="mt-6">
                                    <h5 class="text-sm font-medium text-gray-900 mb-3">Závislosti</h5>
                                    <ul class="space-y-2">
                                        @foreach($module->getDependencies() as $dependency)
                                            <li class="flex items-center text-sm text-gray-600">
                                                <i class="fas fa-link text-blue-500 mr-2"></i>{{ $dependency }}
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                                @endif

                                @if($module->getPermissions())
                                <div class="mt-6">
                                    <h5 class="text-sm font-medium text-gray-900 mb-3">Oprávnění</h5>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                        @foreach($module->getPermissions() as $action => $roles)
                                            <div class="flex items-center justify-between">
                                                <span class="text-sm font-medium text-gray-700">{{ ucfirst($action) }}:</span>
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                    {{ implode(', ', $roles) }}
                                                </span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>

                        <!-- Statistiky -->
                        <div>
                            <div class="bg-gray-50 rounded-lg p-6">
                                <h4 class="text-lg font-medium text-gray-900 mb-4">Statistiky</h4>
                                <div class="space-y-4">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0">
                                                <i class="fas fa-code text-blue-500 text-lg"></i>
                                            </div>
                                            <div class="ml-3">
                                                <p class="text-sm font-medium text-gray-900">Controllery</p>
                                                <p class="text-2xl font-semibold text-blue-600">{{ count($module->getControllers() ?? []) }}</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0">
                                                <i class="fas fa-database text-green-500 text-lg"></i>
                                            </div>
                                            <div class="ml-3">
                                                <p class="text-sm font-medium text-gray-900">Entity</p>
                                                <p class="text-2xl font-semibold text-green-600">{{ count($module->getEntities() ?? []) }}</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0">
                                                <i class="fas fa-code-branch text-yellow-500 text-lg"></i>
                                            </div>
                                            <div class="ml-3">
                                                <p class="text-sm font-medium text-gray-900">Migrace</p>
                                                <p class="text-2xl font-semibold text-yellow-600">{{ count($module->getMigrations() ?? []) }}</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0">
                                                <i class="fas fa-file-code text-purple-500 text-lg"></i>
                                            </div>
                                            <div class="ml-3">
                                                <p class="text-sm font-medium text-gray-900">Views</p>
                                                <p class="text-2xl font-semibold text-purple-600">{{ count($module->getViews() ?? []) }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Controllers -->
            <div id="tab-controllers" class="tab-content">
                <div class="p-6">
                    @if($module->getControllers())
                        <div class="bg-white shadow overflow-hidden sm:rounded-md">
                            <ul class="divide-y divide-gray-200">
                                @foreach($module->getControllers() as $controller)
                                <li class="px-6 py-4">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0">
                                                <i class="fas fa-code text-blue-500"></i>
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900">{{ $controller->getName() }}</div>
                                                <div class="text-sm text-gray-500">{{ $controller->getNamespace() }}</div>
                                            </div>
                                        </div>
                                        <div class="flex items-center space-x-4">
                                            <span class="text-sm text-gray-500">{{ count($controller->getMethods() ?? []) }} metod</span>
                                            @if($controller->isEnabled())
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                    Aktivní
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                    Neaktivní
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </li>
                                @endforeach
                            </ul>
                        </div>
                    @else
                        <div class="text-center py-12">
                            <i class="fas fa-code text-gray-400 text-6xl mb-4"></i>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">Žádné controllery</h3>
                            <p class="text-gray-500">Tento modul nemá žádné controllery</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Entities -->
            <div id="tab-entities" class="tab-content">
                <div class="p-6">
                    @if($module->getEntities())
                        <div class="bg-white shadow overflow-hidden sm:rounded-md">
                            <ul class="divide-y divide-gray-200">
                                @foreach($module->getEntities() as $entity)
                                <li class="px-6 py-4">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0">
                                                <i class="fas fa-database text-green-500"></i>
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900">{{ $entity->getName() }}</div>
                                                <div class="text-sm text-gray-500">{{ $entity->getNamespace() }}</div>
                                                <div class="text-sm text-gray-400">Tabulka: {{ $entity->getTableName() }}</div>
                                            </div>
                                        </div>
                                        <div class="flex items-center space-x-4">
                                            <span class="text-sm text-gray-500">{{ count($entity->getProperties() ?? []) }} sloupců</span>
                                            @if($entity->isTableExists())
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                    Existuje
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                    Neexistuje
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </li>
                                @endforeach
                            </ul>
                        </div>
                    @else
                        <div class="text-center py-12">
                            <i class="fas fa-database text-gray-400 text-6xl mb-4"></i>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">Žádné entity</h3>
                            <p class="text-gray-500">Tento modul nemá žádné entity</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Migrations -->
            <div id="tab-migrations" class="tab-content">
                <div class="p-6">
                    @if($module->getMigrations())
                        <div class="bg-white shadow overflow-hidden sm:rounded-md">
                            <ul class="divide-y divide-gray-200">
                                @foreach($module->getMigrations() as $migration)
                                <li class="px-6 py-4">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0">
                                                @switch($migration->getType())
                                                    @case('install')
                                                        <i class="fas fa-download text-green-500"></i>
                                                        @break
                                                    @case('uninstall')
                                                        <i class="fas fa-trash text-red-500"></i>
                                                        @break
                                                    @default
                                                        <i class="fas fa-code-branch text-blue-500"></i>
                                                @endswitch
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900">{{ $migration->getName() }}</div>
                                                <div class="text-sm text-gray-500">{{ $migration->getFile() }}</div>
                                                @if($migration->getRanAt())
                                                <div class="text-sm text-gray-400">Spuštěno: {{ $migration->getRanAt()->format('d.m.Y H:i:s') }}</div>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="flex items-center space-x-4">
                                            @switch($migration->getStatus())
                                                @case('pending')
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                        <i class="fas fa-clock mr-1"></i>Čeká
                                                    </span>
                                                    @break
                                                @case('ran')
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                        <i class="fas fa-check mr-1"></i>Spuštěno
                                                    </span>
                                                    @break
                                                @case('failed')
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                        <i class="fas fa-times mr-1"></i>Chyba
                                                    </span>
                                                    @break
                                                @default
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                        {{ $migration->getStatus() }}
                                                    </span>
                                            @endswitch
                                        </div>
                                    </div>
                                </li>
                                @endforeach
                            </ul>
                        </div>
                    @else
                        <div class="text-center py-12">
                            <i class="fas fa-code-branch text-gray-400 text-6xl mb-4"></i>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">Žádné migrace</h3>
                            <p class="text-gray-500">Tento modul nemá žádné migrace</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Soubory -->
            <div id="tab-files" class="tab-content">
                <div class="p-6">
                    <div class="text-center py-12">
                        <i class="fas fa-file-code text-gray-400 text-6xl mb-4"></i>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">Správa souborů</h3>
                        <p class="text-gray-500">Zde bude implementována správa souborů modulu</p>
                        <button class="mt-4 inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-lg">
                            <i class="fas fa-folder-open mr-2"></i>Procházet soubory
                        </button>
                    </div>
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
</script>
@endsection

