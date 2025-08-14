@extends('layouts.app')

@section('title', 'Správa modulů - Arcadia')

@section('content')
<div class="px-4 sm:px-6 lg:px-8">
    <div class="sm:flex sm:items-center">
        <div class="sm:flex-auto">
            <h1 class="text-xl font-semibold text-gray-900">Správa modulů</h1>
            <p class="mt-2 text-sm text-gray-700">Zapínání, vypínání a konfigurace modulů systému</p>
        </div>
        <div class="mt-4 sm:mt-0 sm:ml-16 sm:flex-none">
            <div class="flex gap-2">
                <button onclick="refreshStatus()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md font-medium transition-colors flex items-center">
                    <i class="fas fa-sync-alt mr-2"></i>Obnovit
                </button>
                <a href="/settings/modules/log" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-md font-medium transition-colors flex items-center">
                    <i class="fas fa-list mr-2"></i>Log
                </a>
            </div>
        </div>
    </div>

    <!-- Statistiky -->
    <div class="mt-8 grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-cube h-6 w-6 text-gray-400"></i>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Celkem modulů</dt>
                            <dd class="text-lg font-medium text-gray-900" id="totalModules">-</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-check h-6 w-6 text-gray-400"></i>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Dostupné</dt>
                            <dd class="text-lg font-medium text-gray-900" id="availableModules">-</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-times h-6 w-6 text-gray-400"></i>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Vypnuté</dt>
                            <dd class="text-lg font-medium text-gray-900" id="disabledModules">-</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-exclamation-triangle h-6 w-6 text-gray-400"></i>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">S problémy</dt>
                            <dd class="text-lg font-medium text-gray-900" id="modulesWithIssues">-</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Seznam modulů -->
    <div class="mt-8">
        <h2 class="text-lg font-medium text-gray-900 mb-4">Moduly systému</h2>
        <div class="flex flex-col">
            <div class="-my-2 -mx-4 overflow-x-auto sm:-mx-6 lg:-mx-8">
                <div class="inline-block min-w-full py-2 align-middle md:px-6 lg:px-8">
                    <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 md:rounded-lg">
                        <table class="min-w-full divide-y divide-gray-300">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Modul</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stav</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Popis</th>
                                    <th scope="col" class="relative px-6 py-3">
                                        <span class="sr-only">Akce</span>
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($modules as $moduleName => $config)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10">
                                                <div class="h-10 w-10 rounded-lg flex items-center justify-center
                                                    @if($config['available']) bg-green-100 text-green-600
                                                    @elseif($config['enabled']) bg-orange-100 text-orange-600
                                                    @else bg-gray-100 text-gray-600 @endif">
                                                    <i class="fas fa-cube"></i>
                                                </div>
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900">{{ ucfirst($moduleName) }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full
                                            @if($config['available']) bg-green-100 text-green-800
                                            @elseif($config['enabled']) bg-orange-100 text-orange-800
                                            @else bg-gray-100 text-gray-800 @endif">
                                            @if($config['available'])
                                                Dostupný
                                            @elseif($config['enabled'])
                                                Nedostupný
                                            @else
                                                Vypnutý
                                            @endif
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm text-gray-900">
                                            @if($config['available'])
                                                Modul je dostupný a funkční
                                            @elseif($config['enabled'])
                                                Modul je povolen, ale má chybějící závislosti
                                            @else
                                                Modul je vypnutý
                                            @endif
                                        </div>
                                        @if(!empty($config['missing_dependencies']))
                                        <div class="text-sm text-red-600 mt-1">
                                            <i class="fas fa-exclamation-triangle mr-1"></i>
                                            Chybějící závislosti: {{ implode(', ', $config['missing_dependencies']) }}
                                        </div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <div class="flex items-center justify-end space-x-2">
                                            <a href="/settings/modules/{{ $moduleName }}"
                                               class="text-blue-600 hover:text-blue-900"
                                               title="Zobrazit detail">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="/settings/modules/{{ $moduleName }}/edit"
                                               class="text-green-600 hover:text-green-900"
                                               title="Upravit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button onclick="toggleModule('{{ $moduleName }}')"
                                                    class="text-{{ $config['enabled'] ? 'red' : 'green' }}-600 hover:text-{{ $config['enabled'] ? 'red' : 'green' }}-900"
                                                    title="{{ $config['enabled'] ? 'Vypnout' : 'Zapnout' }} modul">
                                                <i class="fas fa-{{ $config['enabled'] ? 'power-off' : 'play' }}"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Moduly s problémy -->
    @if(!empty($modulesWithIssues))
    <div class="mt-8">
        <h2 class="text-lg font-medium text-gray-900 mb-4">Moduly s problémy</h2>
        <div class="bg-orange-50 border border-orange-200 rounded-lg p-6">
            <div class="flex items-center mb-4">
                <div class="flex-shrink-0">
                    <i class="fas fa-exclamation-triangle text-orange-600"></i>
                </div>
                <div class="ml-3">
                    <h3 class="text-lg font-medium text-orange-800">Chybějící závislosti</h3>
                    <p class="text-orange-700 text-sm">Následující moduly mají chybějící závislosti:</p>
                </div>
            </div>
            <div class="space-y-3">
                @foreach($modulesWithIssues as $moduleName => $missingDeps)
                <div class="bg-white rounded-lg p-3 border border-orange-200">
                    <div class="font-medium text-orange-800">{{ ucfirst($moduleName) }}</div>
                    <div class="text-sm text-orange-700 mt-1">
                        Chybějící: {{ implode(', ', $missingDeps) }}
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif
</div>

<script>
function refreshStatus() {
    fetch('/settings/modules/api/status', {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
        },
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('totalModules').textContent = data.data.total_modules;
            document.getElementById('availableModules').textContent = data.data.available_count;
            document.getElementById('disabledModules').textContent = data.data.total_modules - data.data.available_count;
            document.getElementById('modulesWithIssues').textContent = data.data.issues_count;
        }
    })
    .catch(error => {
        console.error('Error:', error);
    });
}

function toggleModule(moduleName) {
    const action = confirm(`Opravdu chcete změnit stav modulu "${moduleName}"?`);
    if (!action) return;

    fetch(`/settings/modules/${moduleName}/toggle`, {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Content-Type': 'application/json',
        },
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Chyba při změně stavu modulu: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Došlo k chybě při změně stavu modulu.');
    });
}

// Načtení statistik při načtení stránky
document.addEventListener('DOMContentLoaded', function() {
    refreshStatus();
});
</script>
@endsection
