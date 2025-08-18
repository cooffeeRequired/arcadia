@extends('layouts.app')

@section('content')
<div class="px-4 sm:px-6 lg:px-8">
    <div class="sm:flex sm:items-center">
        <div class="sm:flex-auto">
            <h1 class="text-xl font-semibold text-gray-900">@i18('settings')</h1>
            <p class="mt-2 text-sm text-gray-700">Správa nastavení aplikace a systému.</p>
        </div>
    </div>

    <!-- Navigace nastavení -->
    <div class="mt-8">
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <a href="/settings/profile" class="relative rounded-lg border border-gray-300 bg-white px-6 py-5 shadow-sm flex items-center space-x-3 hover:border-gray-400 focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-indigo-500">
                <div class="flex-shrink-0">
                    <svg class="h-6 w-6 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <span class="absolute inset-0" aria-hidden="true"></span>
                    <p class="text-sm font-medium text-gray-900">@i18('user_profile')</p>
                    <p class="text-sm text-gray-500">Správa osobních údajů</p>
                </div>
            </a>

            <a href="/settings/system" class="relative rounded-lg border border-gray-300 bg-white px-6 py-5 shadow-sm flex items-center space-x-3 hover:border-gray-400 focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-indigo-500">
                <div class="flex-shrink-0">
                    <svg class="h-6 w-6 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <span class="absolute inset-0" aria-hidden="true"></span>
                    <p class="text-sm font-medium text-gray-900">@i18('system_info')</p>
                    <p class="text-sm text-gray-500">Informace o systému a verzi</p>
                </div>
            </a>
        </div>
    </div>

    <!-- Základní nastavení -->
    <div class="mt-8">
        <div class="bg-white shadow sm:rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900">@i18('basic_settings')</h3>
                <p class="mt-1 max-w-2xl text-sm text-gray-500">Základní konfigurace aplikace.</p>

                <form method="POST" action="/settings" class="mt-6 space-y-6">
                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                        <div>
                            <label for="company_name" class="block text-sm font-medium text-gray-700">@i18('company_name')</label>
                            <input type="text" name="company_name" id="company_name" value="{{ $settings['company_name'] ?? '' }}" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                        </div>

                        <div>
                            <label for="company_email" class="block text-sm font-medium text-gray-700">@i18('company_email')</label>
                            <input type="email" name="company_email" id="company_email" value="{{ $settings['company_email'] ?? '' }}" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                        </div>

                        <div>
                            <label for="company_phone" class="block text-sm font-medium text-gray-700">@i18('company_phone')</label>
                            <input type="text" name="company_phone" id="company_phone" value="{{ $settings['company_phone'] ?? '' }}" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                        </div>

                        <div>
                            <label for="default_currency" class="block text-sm font-medium text-gray-700">@i18('default_currency')</label>
                            <select id="default_currency" name="default_currency" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                                <option value="CZK" {{ ($settings['default_currency'] ?? '') === 'CZK' ? 'selected' : '' }}>CZK</option>
                                <option value="EUR" {{ ($settings['default_currency'] ?? '') === 'EUR' ? 'selected' : '' }}>EUR</option>
                                <option value="USD" {{ ($settings['default_currency'] ?? '') === 'USD' ? 'selected' : '' }}>USD</option>
                            </select>
                        </div>

                        <div>
                            <label for="date_format" class="block text-sm font-medium text-gray-700">@i18('date_format')</label>
                            <select id="date_format" name="date_format" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                                <option value="d.m.Y" {{ ($settings['date_format'] ?? '') === 'd.m.Y' ? 'selected' : '' }}>DD.MM.YYYY</option>
                                <option value="Y-m-d" {{ ($settings['date_format'] ?? '') === 'Y-m-d' ? 'selected' : '' }}>YYYY-MM-DD</option>
                                <option value="m/d/Y" {{ ($settings['date_format'] ?? '') === 'm/d/Y' ? 'selected' : '' }}>MM/DD/YYYY</option>
                            </select>
                        </div>

                        <div>
                            <label for="pagination_per_page" class="block text-sm font-medium text-gray-700">@i18('items_per_page')</label>
                            <input type="number" name="pagination_per_page" id="pagination_per_page" value="{{ $settings['pagination_per_page'] ?? 20 }}" min="5" max="100" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                        </div>

                        <div class="sm:col-span-2">
                            <label for="company_address" class="block text-sm font-medium text-gray-700">@i18('company_address')</label>
                            <textarea id="company_address" name="company_address" rows="3" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">{{ $settings['company_address'] ?? '' }}</textarea>
                        </div>
                    </div>

                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <input id="auto_refresh_enabled" name="auto_refresh_enabled" type="checkbox" {{ ($settings['auto_refresh_enabled'] ?? false) ? 'checked' : '' }} class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                            <label for="auto_refresh_enabled" class="ml-2 block text-sm text-gray-900">
                                @i18('auto_refresh_enabled')
                            </label>
                        </div>
                        <div class="flex items-center">
                            <input id="notifications_enabled" name="notifications_enabled" type="checkbox" {{ ($settings['notifications_enabled'] ?? false) ? 'checked' : '' }} class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                            <label for="notifications_enabled" class="ml-2 block text-sm text-gray-900">
                                @i18('notifications_enabled')
                            </label>
                        </div>
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            @i18('save_settings')
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
