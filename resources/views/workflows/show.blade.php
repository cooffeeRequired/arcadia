@extends('layouts.app')

@section('title', $workflow->getName())

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">{{ $workflow->getName() }}</h1>
            <p class="text-gray-600 mt-2">{{ $workflow->getDescription() }}</p>
        </div>
        <div class="flex space-x-2">
            <a href="/workflows/{{ $workflow->getId() }}/test" class="btn-secondary">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h1m4 0h1m-6 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                Test workflow
            </a>
            <a href="/workflows/{{ $workflow->getId() }}/edit" class="btn-primary">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                </svg>
                Upravit
            </a>
        </div>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="card">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Celkem spuštění</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['total_executions'] }}</p>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-green-100 text-green-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Úspěšné</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['successful'] }}</p>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-red-100 text-red-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Neúspěšné</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['failed'] }}</p>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-yellow-100 text-yellow-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Běží</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['running'] }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Workflow Details -->
        <div class="card">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">Detaily workflow</h2>
            </div>
            <div class="p-6 space-y-4">
                <div>
                    <label class="text-sm font-medium text-gray-500">Status</label>
                    <div class="mt-1">
                        @if($workflow->isActive())
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

                <div>
                    <label class="text-sm font-medium text-gray-500">Trigger</label>
                    <div class="mt-1">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                            {{ $workflow->getTriggerType() }}
                        </span>
                    </div>
                </div>

                <div>
                    <label class="text-sm font-medium text-gray-500">Priorita</label>
                    <div class="mt-1 text-sm text-gray-900">{{ $workflow->getPriority() }}</div>
                </div>

                <div>
                    <label class="text-sm font-medium text-gray-500">Vytvořeno</label>
                    <div class="mt-1 text-sm text-gray-900">{{ $workflow->getCreatedAt()->format('d.m.Y H:i') }}</div>
                </div>

                <div>
                    <label class="text-sm font-medium text-gray-500">Poslední aktualizace</label>
                    <div class="mt-1 text-sm text-gray-900">{{ $workflow->getUpdatedAt()->format('d.m.Y H:i') }}</div>
                </div>
            </div>
        </div>

        <!-- Conditions & Actions -->
        <div class="card">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">Podmínky a akce</h2>
            </div>
            <div class="p-6 space-y-6">
                <!-- Podmínky -->
                <div>
                    <h3 class="text-md font-medium text-gray-900 mb-3">Podmínky</h3>
                    @if(empty($workflow->getConditions()))
                    <p class="text-sm text-gray-500">Žádné podmínky nejsou nastaveny</p>
                    @else
                    <div class="space-y-2">
                        @foreach($workflow->getConditions() as $condition)
                        <div class="bg-gray-50 rounded-lg p-3">
                            <div class="text-sm">
                                <span class="font-medium">{{ $condition['type'] }}</span>
                                @if(isset($condition['field']))
                                - pole <code class="bg-gray-200 px-1 rounded">{{ $condition['field'] }}</code>
                                @endif
                                @if(isset($condition['operator']))
                                - operátor <code class="bg-gray-200 px-1 rounded">{{ $condition['operator'] }}</code>
                                @endif
                                @if(isset($condition['value']))
                                - hodnota <code class="bg-gray-200 px-1 rounded">{{ $condition['value'] }}</code>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @endif
                </div>

                <!-- Akce -->
                <div>
                    <h3 class="text-md font-medium text-gray-900 mb-3">Akce</h3>
                    @if(empty($workflow->getActions()))
                    <p class="text-sm text-gray-500">Žádné akce nejsou nastaveny</p>
                    @else
                    <div class="space-y-2">
                        @foreach($workflow->getActions() as $action)
                        <div class="bg-gray-50 rounded-lg p-3">
                            <div class="text-sm">
                                <span class="font-medium">{{ $action['type'] }}</span>
                                @if(isset($action['param1']))
                                - <code class="bg-gray-200 px-1 rounded">{{ $action['param1'] }}</code>
                                @endif
                                @if(isset($action['param2']))
                                - <code class="bg-gray-200 px-1 rounded">{{ $action['param2'] }}</code>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Executions -->
    <div class="card mt-8">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">Poslední spuštění</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Spuštěno</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Dokončeno</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Čas</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Chyba</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($executions as $execution)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($execution->getStatus() === 'completed')
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                Dokončeno
                            </span>
                            @elseif($execution->getStatus() === 'failed')
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                Neúspěšné
                            </span>
                            @elseif($execution->getStatus() === 'running')
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                Běží
                            </span>
                            @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                {{ ucfirst($execution->getStatus()) }}
                            </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $execution->getStartedAt()->format('d.m.Y H:i:s') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            @if($execution->getCompletedAt())
                            {{ $execution->getCompletedAt()->format('d.m.Y H:i:s') }}
                            @else
                            -
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $execution->getExecutionTimeMs() }}ms
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            @if($execution->getErrorMessage())
                            <span class="text-red-600">{{ $execution->getErrorMessage() }}</span>
                            @else
                            -
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if(count($executions) === 0)
        <div class="text-center py-8">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">Žádná spuštění</h3>
            <p class="mt-1 text-sm text-gray-500">Workflow zatím nebyl spuštěn.</p>
        </div>
        @endif
    </div>
</div>
@endsection
