@extends('layouts.app')

@section('content')
<div class="px-4 sm:px-6 lg:px-8">
    <div class="mb-6">
        <a href="/settings" class="text-blue-600 hover:text-blue-900 text-sm font-medium">
            ← Zpět na nastavení
        </a>
    </div>

    <div class="sm:flex sm:items-center">
        <div class="sm:flex-auto">
            <h1 class="text-xl font-semibold text-gray-900">Systémové informace</h1>
            <p class="mt-2 text-sm text-gray-700">Informace o systému, verzi a konfiguraci.</p>
        </div>
    </div>

    <div class="mt-8">
        <div class="bg-white shadow overflow-hidden sm:rounded-lg">
            <div class="px-4 py-5 sm:px-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900">Systémové údaje</h3>
                <p class="mt-1 max-w-2xl text-sm text-gray-500">Technické informace o aplikaci a prostředí.</p>
            </div>
            <div class="border-t border-gray-200">
                <dl>
                    <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">PHP verze</dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ $systemInfo['php_version'] ?? 'Neznámá' }}</dd>
                    </div>
                    <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">Doctrine ORM verze</dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ $systemInfo['doctrine_version'] ?? 'Neznámá' }}</dd>
                    </div>
                    <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">Databázový driver</dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ $systemInfo['database_driver'] ?? 'Neznámý' }}</dd>
                    </div>
                    <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">Cache driver</dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ $systemInfo['cache_driver'] ?? 'Neznámý' }}</dd>
                    </div>
                    <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">Prostředí</dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ $systemInfo['environment'] ?? 'Neznámé' }}</dd>
                    </div>
                    <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">Debug mód</dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ ($systemInfo['debug_mode'] ?? false) ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                {{ ($systemInfo['debug_mode'] ?? false) ? 'Zapnutý' : 'Vypnutý' }}
                            </span>
                        </dd>
                    </div>
                    <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">Poslední záloha</dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ $systemInfo['last_backup'] ? $systemInfo['last_backup']->format('d.m.Y H:i') : 'Neznámá' }}</dd>
                    </div>
                    <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">Využití disku</dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ $systemInfo['disk_usage'] ?? 'Neznámé' }}</dd>
                    </div>
                    <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">Využití paměti</dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ $systemInfo['memory_usage'] ?? 'Neznámé' }}</dd>
                    </div>
                    <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">OPcache</dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ ($systemInfo['opcache_enabled'] ?? false) ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ ($systemInfo['opcache_enabled'] ?? false) ? 'Aktivní' : 'Neaktivní' }}
                            </span>
                            @if($systemInfo['opcache_enabled'] && $systemInfo['opcache_status'])
                                <div class="mt-2 text-xs text-gray-500">
                                    <div>Paměť: {{ number_format($systemInfo['opcache_status']['memory_usage']['used_memory'] / 1024 / 1024, 2) }} MB / {{ number_format($systemInfo['opcache_status']['memory_usage']['free_memory'] / 1024 / 1024, 2) }} MB</div>
                                    <div>Hit rate: {{ number_format($systemInfo['opcache_status']['opcache_statistics']['opcache_hit_rate'], 2) }}%</div>
                                    <div>Kompilované soubory: {{ $systemInfo['opcache_status']['opcache_statistics']['num_cached_scripts'] }}</div>
                                </div>
                            @endif
                        </dd>
                    </div>
                </dl>
            </div>
        </div>
    </div>

    <!-- Systémové akce -->
    <div class="mt-8">
        <div class="bg-white shadow sm:rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900">Systémové akce</h3>
                <p class="mt-1 max-w-2xl text-sm text-gray-500">Správa systému a údržba.</p>
                
                <div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <form method="POST" action="/settings/clear-cache" class="relative rounded-lg border border-gray-300 bg-white px-6 py-5 shadow-sm flex items-center space-x-3 hover:border-gray-400 focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-indigo-500">
                        <div class="flex-shrink-0">
                            <svg class="h-6 w-6 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <button type="submit" class="absolute inset-0 w-full h-full text-left">
                                <span class="sr-only">Vymazat cache</span>
                            </button>
                            <p class="text-sm font-medium text-gray-900">Vymazat cache</p>
                            <p class="text-sm text-gray-500">Vyčistit všechny cache soubory (zachová složky)</p>
                        </div>
                    </form>

                    @if($systemInfo['opcache_enabled'])
                    <form method="POST" action="/settings/optimize-opcache" class="relative rounded-lg border border-gray-300 bg-white px-6 py-5 shadow-sm flex items-center space-x-3 hover:border-gray-400 focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-indigo-500">
                        <div class="flex-shrink-0">
                            <svg class="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <button type="submit" class="absolute inset-0 w-full h-full text-left">
                                <span class="sr-only">Optimalizovat OPcache</span>
                            </button>
                            <p class="text-sm font-medium text-gray-900">Optimalizovat OPcache</p>
                            <p class="text-sm text-gray-500">Předkompilovat PHP soubory pro lepší výkon</p>
                        </div>
                    </form>
                    @endif

                    <form method="POST" action="/settings/create-backup" class="relative rounded-lg border border-gray-300 bg-white px-6 py-5 shadow-sm flex items-center space-x-3 hover:border-gray-400 focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-indigo-500">
                        <div class="flex-shrink-0">
                            <svg class="h-6 w-6 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" />
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <button type="submit" class="absolute inset-0 w-full h-full text-left">
                                <span class="sr-only">Vytvořit zálohu</span>
                            </button>
                            <p class="text-sm font-medium text-gray-900">Vytvořit zálohu</p>
                            <p class="text-sm text-gray-500">Zálohovat databázi a soubory</p>
                        </div>
                    </form>

                    <form method="POST" action="/settings/check-integrity" class="relative rounded-lg border border-gray-300 bg-white px-6 py-5 shadow-sm flex items-center space-x-3 hover:border-gray-400 focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-indigo-500">
                        <div class="flex-shrink-0">
                            <svg class="h-6 w-6 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <button type="submit" class="absolute inset-0 w-full h-full text-left">
                                <span class="sr-only">Kontrola integrity</span>
                            </button>
                            <p class="text-sm font-medium text-gray-900">Kontrola integrity</p>
                            <p class="text-sm text-gray-500">Zkontrolovat integritu databáze</p>
                        </div>
                    </form>

                    <a href="/settings/logs" class="relative rounded-lg border border-gray-300 bg-white px-6 py-5 shadow-sm flex items-center space-x-3 hover:border-gray-400 focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-indigo-500">
                        <div class="flex-shrink-0">
                            <svg class="h-6 w-6 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <span class="absolute inset-0" aria-hidden="true"></span>
                            <p class="text-sm font-medium text-gray-900">Logy systému</p>
                            <p class="text-sm text-gray-500">Zobrazit systémové logy</p>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 