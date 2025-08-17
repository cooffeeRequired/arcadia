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
                    <button onclick="refreshStatus()"
                            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md font-medium transition-colors flex items-center">
                        <i class="fas fa-sync-alt mr-2"></i>Obnovit
                    </button>
                    <button onclick="showLogModal()"
                           class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-md font-medium transition-colors flex items-center">
                        <i class="fas fa-list mr-2"></i>Log
                    </button>
                </div>
            </div>
        </div>

        <!-- Statistiky -->
        <div class="mt-8 grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
            <div class="bg-white overflow-hidden shadow rounded-lg border-1">
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

            <div class="bg-white overflow-hidden shadow rounded-lg border-1 border-green-500 text-green-400">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-check h-6 w-6"></i>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-green-500 truncate">Zapnuté</dt>
                                <dd class="text-lg font-medium text-green-900" id="enabledModules">-</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow rounded-lg border-1 border-red-500 text-red-400">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-times h-6 w-6"></i>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-red-500 truncate">Vypnuté</dt>
                                <dd class="text-lg font-medium text-red-900" id="disabledModules">-</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow rounded-lg border-1 border-amber-400 text-amber-400">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-exclamation-triangle h-6 w-6"></i>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-amber-500 truncate">S problémy</dt>
                                <dd class="text-lg font-medium text-amber-900" id="modulesWithIssues">-</dd>
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
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Modul
                                    </th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Stav
                                    </th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Popis
                                    </th>
                                    <th scope="col" class="relative px-6 py-3">
                                        <span class="sr-only">Akce</span>
                                    </th>
                                </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($modules as $moduleName => $module)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0 h-10 w-10">
                                                    <div @class([
                                                        'h-10 w-10 rounded-lg flex items-center justify-center',
                                                        'bg-green-100 text-green-600' => $module['config']['is_available'] ?? false,
                                                        'bg-orange-100 text-orange-600' => ($module['config']['is_enabled'] ?? false) && !($module['config']['is_available'] ?? false),
                                                        'bg-gray-100 text-gray-600' => !($module['config']['is_enabled'] ?? false) && !($module['config']['is_available'] ?? false)
                                                    ])>
                                                        <i class="fas fa-cube"></i>
                                                    </div>
                                                </div>
                                                <div class="ml-4">
                                                    <div class="text-sm font-medium text-gray-900">{{ ucfirst($moduleName) }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                        <span @class([
                                            'inline-flex px-2 py-1 text-xs font-semibold rounded-full',
                                            'bg-green-100 text-green-800' => $module['config']['is_available'] ?? false,
                                            'bg-orange-100 text-orange-800' => ($module['config']['is_enabled'] ?? false) && ($module['config']['has_missing_dependencies'] ?? false),
                                            'bg-yellow-100 text-yellow-800' => ($module['config']['is_enabled'] ?? false) && !($module['config']['is_installed'] ?? false),
                                            'bg-gray-100 text-gray-800' => !($module['config']['is_enabled'] ?? false)
                                        ])>
                                            @if($module['config']['is_available'] ?? false)
                                                Dostupný
                                            @elseif($module['config']['is_enabled'] ?? false)
                                                @if($module['config']['has_missing_dependencies'] ?? false)
                                                    Nedostupný
                                                @elseif(!($module['config']['is_installed'] ?? false))
                                                    Nenainstalován
                                                @else
                                                    Nedostupný
                                                @endif
                                            @else
                                                Vypnutý
                                            @endif
                                        </span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="text-sm text-gray-900">
                                                @if($module['config']['is_available'] ?? false)
                                                    Modul je dostupný a funkční
                                                @elseif($module['config']['is_enabled'] ?? false)
                                                    @if(!($module['config']['is_installed'] ?? false))
                                                        Modul je povolen, ale není nainstalován
                                                    @elseif($module['config']['has_missing_dependencies'] ?? false)
                                                        Modul je povolen, ale má chybějící závislosti
                                                    @else
                                                        Modul je povolen, ale není dostupný
                                                    @endif
                                                @else
                                                    Modul je vypnutý
                                                @endif
                                            </div>
                                            @if(!empty($module['config']['missing_dependencies'] ?? []))
                                                <div class="text-sm text-red-600 mt-1">
                                                    <i class="fas fa-exclamation-triangle mr-1"></i>
                                                    Chybějící
                                                    závislosti: {{ implode(', ', $module['config']['missing_dependencies']) }}
                                                </div>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <div class="flex items-center justify-end space-x-2">
                                                <button onclick="showModuleDetail('{{ $moduleName }}')"
                                                   class="text-blue-600 hover:text-blue-900"
                                                   title="Zobrazit detail">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <a href="/settings/modules/{{ $moduleName }}/edit"
                                                   class="text-green-600 hover:text-green-900"
                                                   title="Upravit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <button onclick="showModuleToggle('{{ $moduleName }}', '{{ ($module['config']['is_enabled'] ?? false) ? 'Vypnout' : 'Zapnout' }}')"
                                                        @class([
                                                            'text-red-600 hover:text-red-900' => $module['config']['is_enabled'] ?? false,
                                                            'text-green-600 hover:text-green-900' => !($module['config']['is_enabled'] ?? false)
                                                        ])
                                                        title="{{ ($module['config']['is_enabled'] ?? false) ? 'Vypnout' : 'Zapnout' }} modul">
                                                    <i class="fas fa-{{ ($module['config']['is_enabled'] ?? false) ? 'power-off' : 'play' }}"></i>
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
@push('scripts')
    <script>
        function refreshStatus() {
            $.ajax({
                url: '/settings/modules/api/status',
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                },
                success: function(data) {
                    if (data.success) {
                        data = data.data
                        const {
                            enabled_modules,
                            available_modules,
                            modules_with_issues,
                            total_enabled,
                            total_available,
                            total_issues
                        } = data;

                        const Sum = total_available + total_issues;


                        $('#totalModules').text(Sum);
                        $('#availableModules').text(total_available);
                        $('#enabledModules').text(total_enabled);
                        $('#disabledModules').text(total_available - total_enabled - total_issues);
                        $('#modulesWithIssues').text(total_issues);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error:', error);
                }
            });
        }

        function showModuleDetail(moduleName) {
            loadModal({
                title: 'Detail modulu: ' + moduleName,
                url: '/settings/modules/' + moduleName,
                size: 'large',
                onLoad: function(modal) {
                    console.log('Detail modulu načten');
                }
            });
        }

        function showModuleToggle(moduleName, action) {
            const isEnabled = action === 'Vypnout';
            const actionText = isEnabled ? 'vypnout' : 'zapnout';

            loadModal({
                title: action + ' modul: ' + moduleName,
                content: '<div class="text-center">' +
                    '<div class="mb-4">' +
                        '<i class="fas fa-' + (isEnabled ? 'power-off' : 'play') + ' text-4xl ' + (isEnabled ? 'text-red-500' : 'text-green-500') + '"></i>' +
                    '</div>' +
                    '<h3 class="text-lg font-medium text-gray-900 mb-2">Opravdu chcete ' + actionText + ' modul "' + moduleName + '"?</h3>' +
                    '<p class="text-gray-600 mb-4">' +
                        (isEnabled ? 'Modul bude deaktivován a nebude dostupný v systému.' : 'Modul bude aktivován a bude dostupný v systému.') +
                    '</p>' +
                    '<div class="bg-gray-50 rounded-lg p-4 text-sm text-gray-700">' +
                        '<strong>Upozornění:</strong> Tato akce může ovlivnit funkčnost systému.' +
                    '</div>' +
                '</div>',
                size: 'small',
                footer: '<button type="button" class="btn-secondary" onclick="hideModal()">Zrušit</button>' +
                    '<button type="button" class="btn-primary ' + (isEnabled ? 'bg-red-600 hover:bg-red-700' : 'bg-green-600 hover:bg-green-700') + '" onclick="confirmModuleToggle(\'' + moduleName + '\')">' +
                        action +
                    '</button>'
            });
        }

        function showLogModal() {
            loadModal({
                title: 'Log modulů',
                url: '/settings/modules/log',
                size: 'xlarge',
                onLoad: function(modal) {
                    console.log('Log modulů načten');
                }
            });
        }

        function confirmModuleToggle(moduleName) {
            $.ajax({
                url: '/settings/modules/' + moduleName + '/toggle',
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Content-Type': 'application/json',
                },
                success: function(data) {
                    if (data.success) {
                        hideModal();
                        location.reload();
                    } else {
                        alert('Chyba při změně stavu modulu: ' + data.message);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error:', error);
                    alert('Došlo k chybě při změně stavu modulu.');
                }
            });
        }

        // Načtení statistik při načtení stránky
        $(document).ready(function() {
            refreshStatus();
        });
    </script>
@endpush
 @endsection
