@extends('layouts.app')

@section('title', 'Systémové Logy')

@section('content')
<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <!-- Header -->
    <div class="px-4 py-6 sm:px-0">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900">Systémové Logy</h1>
                <p class="mt-1 text-sm text-gray-500">Přehled systémových logů a chybových zpráv</p>
            </div>
            <a href="/settings/system" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <svg class="-ml-1 mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Zpět na systém
            </a>
        </div>
    </div>

    <!-- Logs -->
    @if(empty($logs))
        <div class="bg-white shadow sm:rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <div class="text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">Žádné logy</h3>
                    <p class="mt-1 text-sm text-gray-500">Nebyly nalezeny žádné log soubory.</p>
                </div>
            </div>
        </div>
    @else
        @foreach($logs as $logKey => $log)
            <div class="bg-white shadow sm:rounded-lg mb-6">
                <div class="px-4 py-5 sm:p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">{{ $log['name'] }}</h3>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                            {{ count($log['entries']) }} záznamů
                        </span>
                    </div>
                    
                    @if(empty($log['entries']))
                        <div class="text-center py-4">
                            <p class="text-sm text-gray-500">Žádné záznamy v tomto logu</p>
                        </div>
                    @else
                        <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 md:rounded-lg">
                            <table class="min-w-full divide-y divide-gray-300">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Čas
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Úroveň
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Zpráva
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($log['entries'] as $entry)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                @if($entry['timestamp'])
                                                    {{ $entry['timestamp'] }}
                                                @else
                                                    <span class="text-gray-400">N/A</span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                @php
                                                    $levelColors = [
                                                        'error' => 'bg-red-100 text-red-800',
                                                        'warning' => 'bg-yellow-100 text-yellow-800',
                                                        'info' => 'bg-blue-100 text-blue-800',
                                                        'debug' => 'bg-gray-100 text-gray-800',
                                                        'critical' => 'bg-red-100 text-red-800',
                                                        'alert' => 'bg-orange-100 text-orange-800',
                                                        'emergency' => 'bg-red-100 text-red-800'
                                                    ];
                                                    $color = $levelColors[$entry['level']] ?? 'bg-gray-100 text-gray-800';
                                                @endphp
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $color }}">
                                                    {{ strtoupper($entry['level']) }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 text-sm text-gray-900">
                                                <div class="max-w-md truncate" title="{{ $entry['line'] }}">
                                                    {{ $entry['line'] }}
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="mt-4 text-xs text-gray-500">
                            <p>Soubor: {{ $log['file'] }}</p>
                        </div>
                    @endif
                </div>
            </div>
        @endforeach
    @endif
</div>
@endsection 