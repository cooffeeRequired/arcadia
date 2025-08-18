@extends('layouts.app')

@section('title', 'Projects')

@section('content')
<div class="px-4 sm:px-6 lg:px-8">
    <div class="sm:flex sm:items-center">
        <div class="sm:flex-auto">
            <h1 class="text-xl font-semibold text-gray-900">@i18('projects')</h1>
            <p class="mt-2 text-sm text-gray-700">Správa a přehled všech projektů</p>
        </div>
        <div class="mt-4 sm:mt-0 sm:ml-16 sm:flex-none">
            @if($moduleManager->hasPermission('projects', 'create'))
            <a href="/projects/create" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md font-medium transition-colors flex items-center">
                <i class="fas fa-plus mr-2"></i>Nový projekt
            </a>
            @endif
        </div>
    </div>

    <!-- Filtry -->
    <div class="mt-8">
        <h2 class="text-lg font-medium text-gray-900 mb-4">Filtry</h2>
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <form method="GET" action="/projects" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Vyhledávání</label>
                    <input type="text" name="q" value="{{ $filters['query'] }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                           placeholder="Název nebo popis...">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Stav</label>
                    <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Všechny stavy</option>
                        <option value="active" {{ $filters['status'] === 'active' ? 'selected' : '' }}>Aktivní</option>
                        <option value="completed" {{ $filters['status'] === 'completed' ? 'selected' : '' }}>Dokončené</option>
                        <option value="on_hold" {{ $filters['status'] === 'on_hold' ? 'selected' : '' }}>Pozastavené</option>
                        <option value="cancelled" {{ $filters['status'] === 'cancelled' ? 'selected' : '' }}>Zrušené</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Priorita</label>
                    <select name="priority" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Všechny priority</option>
                        <option value="high" {{ $filters['priority'] === 'high' ? 'selected' : '' }}>Vysoká</option>
                        <option value="medium" {{ $filters['priority'] === 'medium' ? 'selected' : '' }}>Střední</option>
                        <option value="low" {{ $filters['priority'] === 'low' ? 'selected' : '' }}>Nízká</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Zákazník</label>
                    <select name="customer_id" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">@i18('all_customers')</option>
                        @foreach($customers as $customer)
                        <option value="{{ $customer->getId() }}" {{ $filters['customer_id'] == $customer->getId() ? 'selected' : '' }}>
                            {{ $customer->getName() }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="md:col-span-4 flex gap-2">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md font-medium transition-colors">
                        <i class="fas fa-search mr-2"></i>Filtrovat
                    </button>
                    <a href="/projects" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-md font-medium transition-colors">
                        <i class="fas fa-times mr-2"></i>Vymazat
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Seznam projektů -->
    <div class="mt-8">
        <h2 class="text-lg font-medium text-gray-900 mb-4">Seznam projektů</h2>
        <div class="flex flex-col">
            <div class="-my-2 -mx-4 overflow-x-auto sm:-mx-6 lg:-mx-8">
                <div class="inline-block min-w-full py-2 align-middle md:px-6 lg:px-8">
                    <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 md:rounded-lg">
                        <table class="min-w-full divide-y divide-gray-300">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Projekt</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Zákazník</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stav</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Priorita</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Termín</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rozpočet</th>
                                    <th scope="col" class="relative px-6 py-3">
                                        <span class="sr-only">Akce</span>
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($projects as $project)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10">
                                                <div class="h-10 w-10 rounded-lg flex items-center justify-center bg-blue-100">
                                                    <i class="fas fa-folder text-blue-600"></i>
                                                </div>
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900">
                                                    <a href="/projects/{{ $project->getId() }}" class="hover:text-blue-600">
                                                        {{ $project->getName() }}
                                                    </a>
                                                </div>
                                                @if($project->getDescription())
                                                <div class="text-sm text-gray-500 truncate max-w-xs">{{ $project->getDescription() }}</div>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <a href="/customers/{{ $project->getCustomer()->getId() }}" class="hover:text-blue-600">
                                            {{ $project->getCustomer()->getName() }}
                                        </a>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full
                                            @if($project->getStatus() === 'active') bg-green-100 text-green-800
                                            @elseif($project->getStatus() === 'completed') bg-blue-100 text-blue-800
                                            @elseif($project->getStatus() === 'on_hold') bg-yellow-100 text-yellow-800
                                            @else bg-red-100 text-red-800
                                            @endif">
                                            {{ ucfirst($project->getStatus()) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full
                                            @if($project->getPriority() === 'high') bg-red-100 text-red-800
                                            @elseif($project->getPriority() === 'medium') bg-yellow-100 text-yellow-800
                                            @else bg-green-100 text-green-800
                                            @endif">
                                            {{ ucfirst($project->getPriority()) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $project->getStartDate()->format('d.m.Y') }} - {{ $project->getEndDate() ? $project->getEndDate()->format('d.m.Y') : 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        @if($project->getBudget())
                                            {{ number_format($project->getBudget(), 0, ',', ' ') }} Kč
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <div class="flex items-center justify-end space-x-2">
                                            @if($moduleManager->hasPermission('projects', 'edit'))
                                            <a href="/projects/{{ $project->getId() }}/edit"
                                               class="text-blue-600 hover:text-blue-900"
                                               title="Upravit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            @endif
                                            @if($moduleManager->hasPermission('projects', 'delete'))
                                            <button onclick="deleteProject({{ $project->getId() }})"
                                                    class="text-red-600 hover:text-red-900"
                                                    title="Smazat">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-12 text-center">
                                        <div class="text-center">
                                            <i class="fas fa-folder-open text-4xl text-gray-400 mb-4"></i>
                                                            <h3 class="text-lg font-medium text-gray-900 mb-2">@i18('no_projects')</h3>
                <p class="text-gray-600 mb-6">@i18('no_projects_created_yet')</p>
                                            @if($moduleManager->hasPermission('projects', 'create'))
                                            <a href="/projects/create" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-md font-medium transition-colors">
                                                <i class="fas fa-plus mr-2"></i>Vytvořit první projekt
                                            </a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function deleteProject(projectId) {
    if (confirm('Opravdu chcete smazat tento projekt?')) {
        fetch(`/projects/${projectId}/delete`, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Content-Type': 'application/json',
            },
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.href = data.data.redirect;
            } else {
                alert('Chyba při mazání projektu: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Došlo k chybě při mazání projektu.');
        });
    }
}
</script>
@endsection
