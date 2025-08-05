@extends('layouts.app')

@section('content')
<div class="px-4 sm:px-6 lg:px-8">
    <div class="mb-6">
        <a href="/reports" class="text-blue-600 hover:text-blue-900 text-sm font-medium">
            ← Zpět na reporty
        </a>
    </div>

    <div class="sm:flex sm:items-center">
        <div class="sm:flex-auto">
            <h1 class="text-xl font-semibold text-gray-900">Report obchodů</h1>
            <p class="mt-2 text-sm text-gray-700">Analýza obchodů a příležitostí.</p>
        </div>
    </div>

    <!-- Statistiky podle fáze -->
    <div class="mt-8">
        <h2 class="text-lg font-medium text-gray-900 mb-4">Statistiky podle fáze</h2>
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
            @if(isset($stageStats) && count($stageStats) > 0)
                @foreach($stageStats as $stage => $stats)
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-6 w-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">{{ ucfirst($stage) }}</dt>
                                    <dd class="text-lg font-medium text-gray-900">{{ $stats['count'] }}</dd>
                                    <dd class="text-sm text-gray-500">{{ number_format($stats['value'], 0, ',', ' ') }} Kč</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            @else
                <div class="col-span-full text-center text-sm text-gray-500">
                    Žádné obchody nebyly nalezeny.
                </div>
            @endif
        </div>
    </div>
    
    <!-- Seznam obchodů -->
    <div class="mt-8">
        <h2 class="text-lg font-medium text-gray-900 mb-4">Seznam všech obchodů</h2>
        <div class="flex flex-col">
            <div class="-my-2 -mx-4 overflow-x-auto sm:-mx-6 lg:-mx-8">
                <div class="inline-block min-w-full py-2 align-middle md:px-6 lg:px-8">
                    <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 md:rounded-lg">
                        <table class="min-w-full divide-y divide-gray-300">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Zákazník</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Název</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Hodnota</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fáze</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pravděpodobnost</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th scope="col" class="relative px-6 py-3">
                                        <span class="sr-only">Akce</span>
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @if(isset($deals) && count($deals) > 0)
                                    @foreach($deals as $deal)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            <a href="/customers/{{ $deal->getCustomer()->getId() }}" class="text-blue-600 hover:text-blue-900">
                                                {{ $deal->getCustomer()->getName() }}
                                            </a>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $deal->getTitle() }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            @if($deal->getValue())
                                                {{ number_format($deal->getValue(), 0, ',', ' ') }} Kč
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $deal->getStage() }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            @if($deal->getProbability())
                                                {{ $deal->getProbability() }}%
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $deal->getStatus() === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                                {{ $deal->getStatus() === 'active' ? 'Aktivní' : 'Uzavřený' }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <a href="/deals/{{ $deal->getId() }}" class="text-blue-600 hover:text-blue-900">Zobrazit</a>
                                        </td>
                                    </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="7" class="px-6 py-4 text-center text-sm text-gray-500">
                                            Žádné obchody nebyly nalezeny.
                                        </td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 