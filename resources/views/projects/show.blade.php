@extends('layouts.app')

@section('title', $project->getName())

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="flex justify-between items-start mb-8">
        <div class="flex items-center">
            <a href="/projects" class="text-blue-600 hover:text-blue-800 mr-4">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div>
                <h1 class="text-3xl font-bold text-gray-900">{{ $project->getName() }}</h1>
                <div class="flex items-center gap-4 mt-2">
                    <span class="px-3 py-1 text-sm font-medium rounded-full
                        @if($project->getStatus() === 'active') bg-green-100 text-green-800
                        @elseif($project->getStatus() === 'completed') bg-blue-100 text-blue-800
                        @elseif($project->getStatus() === 'on_hold') bg-yellow-100 text-yellow-800
                        @else bg-red-100 text-red-800
                        @endif">
                        {{ ucfirst($project->getStatus()) }}
                    </span>
                    <span class="px-3 py-1 text-sm font-medium rounded-full
                        @if($project->getPriority() === 'high') bg-red-100 text-red-800
                        @elseif($project->getPriority() === 'medium') bg-yellow-100 text-yellow-800
                        @else bg-green-100 text-green-800
                        @endif">
                        {{ ucfirst($project->getPriority()) }} priorita
                    </span>
                    @if($project->isOverdue())
                    <span class="px-3 py-1 text-sm font-medium rounded-full bg-red-100 text-red-800">
                        <i class="fas fa-exclamation-triangle mr-1"></i>Po termínu
                    </span>
                    @endif
                </div>
            </div>
        </div>
        <div class="flex gap-2">
            @if($moduleManager->hasPermission('projects', 'edit'))
            <a href="/projects/{{ $project->getId() }}/edit"
               class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md font-medium transition-colors">
                <i class="fas fa-edit mr-2"></i>Upravit
            </a>
            @endif
            @if($moduleManager->hasPermission('projects', 'delete'))
            <button onclick="deleteProject({{ $project->getId() }})"
                    class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-md font-medium transition-colors">
                <i class="fas fa-trash mr-2"></i>Smazat
            </button>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Hlavní obsah -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Popis -->
            @if($project->getDescription())
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Popis projektu</h3>
                <p class="text-gray-700">{{ $project->getDescription() }}</p>
            </div>
            @endif

            <!-- Progress a metriky -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Průběh projektu</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    @if($project->getEstimatedHours())
                    <div>
                        <div class="flex justify-between text-sm text-gray-600 mb-2">
                            <span>Průběh práce</span>
                            <span>{{ round($project->getProgress(), 1) }}%</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-3 mb-2">
                            <div class="bg-blue-600 h-3 rounded-full" style="width: {{ $project->getProgress() }}%"></div>
                        </div>
                        <div class="text-sm text-gray-500">
                            {{ $project->getActualHours() ?? 0 }} / {{ $project->getEstimatedHours() }} hodin
                        </div>
                    </div>
                    @endif

                    @if($project->getBudget())
                    <div>
                        <div class="flex justify-between text-sm text-gray-600 mb-2">
                            <span>Využití rozpočtu</span>
                            <span>{{ round($project->getBudgetUtilization(), 1) }}%</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-3 mb-2">
                            <div class="bg-green-600 h-3 rounded-full" style="width: {{ $project->getBudgetUtilization() }}%"></div>
                        </div>
                        <div class="text-sm text-gray-500">
                            {{ number_format($project->getActualCost() ?? 0, 0, ',', ' ') }} / {{ number_format($project->getBudget(), 0, ',', ' ') }} Kč
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Události -->
            @if($moduleManager->getSetting('projects', 'enable_events', true) && count($events) > 0)
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Události</h3>
                    @if($moduleManager->hasPermission('projects', 'manage_events'))
                    <button class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                        <i class="fas fa-plus mr-1"></i>Přidat událost
                    </button>
                    @endif
                </div>
                <div class="space-y-4">
                    @foreach($events as $event)
                    <div class="flex items-start gap-4 p-4 bg-gray-50 rounded-lg">
                        <div class="flex-shrink-0">
                            <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                                <i class="fas fa-{{ $event->getTypeIcon() }} text-blue-600"></i>
                            </div>
                        </div>
                        <div class="flex-1">
                            <div class="flex justify-between items-start">
                                <div>
                                    <h4 class="font-medium text-gray-900">{{ $event->getTitle() }}</h4>
                                    @if($event->getDescription())
                                    <p class="text-sm text-gray-600 mt-1">{{ $event->getDescription() }}</p>
                                    @endif
                                </div>
                                <span class="px-2 py-1 text-xs font-medium rounded-full
                                    @if($event->getStatus() === 'completed') bg-green-100 text-green-800
                                    @elseif($event->getStatus() === 'in_progress') bg-blue-100 text-blue-800
                                    @elseif($event->getStatus() === 'pending') bg-yellow-100 text-yellow-800
                                    @else bg-red-100 text-red-800
                                    @endif">
                                    {{ ucfirst($event->getStatus()) }}
                                </span>
                            </div>
                            <div class="flex items-center text-sm text-gray-500 mt-2">
                                <i class="fas fa-calendar mr-1"></i>
                                <span>{{ $event->getEventDate()->format('d.m.Y H:i') }}</span>
                                @if($event->isOverdue())
                                <span class="ml-2 text-red-600">
                                    <i class="fas fa-exclamation-triangle mr-1"></i>Po termínu
                                </span>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Časové záznamy -->
            @if($moduleManager->getSetting('projects', 'enable_time_tracking', true) && count($timeEntries) > 0)
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Časové záznamy</h3>
                    <button class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                        <i class="fas fa-plus mr-1"></i>Přidat záznam
                    </button>
                </div>
                <div class="space-y-3">
                    @foreach($timeEntries as $entry)
                    <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                        <div>
                            <p class="font-medium text-gray-900">{{ $entry->getDescription() }}</p>
                            <p class="text-sm text-gray-500">
                                {{ $entry->getStartTime()->format('d.m.Y H:i') }} -
                                {{ $entry->getEndTime() ? $entry->getEndTime()->format('H:i') : 'běží' }}
                            </p>
                        </div>
                        <div class="text-right">
                            <p class="font-medium text-gray-900">{{ $entry->getDurationFormatted() }}</p>
                            @if($entry->getTotalCost())
                            <p class="text-sm text-gray-500">{{ number_format($entry->getTotalCost(), 0, ',', ' ') }} Kč</p>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Soubory -->
            @if($moduleManager->getSetting('projects', 'enable_file_attachments', true) && count($files) > 0)
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Soubory</h3>
                    <button class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                        <i class="fas fa-upload mr-1"></i>Nahrát soubor
                    </button>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach($files as $file)
                    <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg">
                        <div class="flex-shrink-0">
                            <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-{{ $file->getIcon() }} text-blue-600"></i>
                            </div>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="font-medium text-gray-900 truncate">{{ $file->getOriginalName() }}</p>
                            <p class="text-sm text-gray-500">{{ $file->getFileSizeFormatted() }}</p>
                        </div>
                        <div class="flex gap-2">
                            <a href="{{ $file->getPreviewUrl() }}" class="text-blue-600 hover:text-blue-800">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ $file->getDownloadUrl() }}" class="text-green-600 hover:text-green-800">
                                <i class="fas fa-download"></i>
                            </a>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Základní informace -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Základní informace</h3>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Zákazník</label>
                        <p class="text-gray-900">{{ $project->getCustomer()->getName() }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Manažer</label>
                        <p class="text-gray-900">{{ $project->getManager()->getName() }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Datum začátku</label>
                        <p class="text-gray-900">{{ $project->getStartDate()->format('d.m.Y') }}</p>
                    </div>
                    @if($project->getEndDate())
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Datum ukončení</label>
                        <p class="text-gray-900">{{ $project->getEndDate()->format('d.m.Y') }}</p>
                    </div>
                    @endif
                    @if($project->getBudget())
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Rozpočet</label>
                        <p class="text-gray-900">{{ number_format($project->getBudget(), 0, ',', ' ') }} Kč</p>
                    </div>
                    @endif
                    @if($project->getEstimatedHours())
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Odhadované hodiny</label>
                        <p class="text-gray-900">{{ $project->getEstimatedHours() }} h</p>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Mini stránka -->
            @if($project->getMiniPageSlug() && $moduleManager->getSetting('projects', 'enable_mini_pages', true))
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Mini stránka</h3>
                <p class="text-sm text-gray-600 mb-4">Veřejná stránka pro zobrazení informací o projektu</p>
                <a href="/projects/mini/{{ $project->getMiniPageSlug() }}"
                   target="_blank"
                   class="inline-flex items-center text-blue-600 hover:text-blue-800">
                    <i class="fas fa-external-link-alt mr-2"></i>
                    Zobrazit mini stránku
                </a>
            </div>
            @endif

            <!-- Rychlé akce -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Rychlé akce</h3>
                <div class="space-y-3">
                    <button class="w-full text-left p-3 bg-blue-50 hover:bg-blue-100 rounded-lg transition-colors">
                        <i class="fas fa-clock mr-2 text-blue-600"></i>
                        Spustit časomíru
                    </button>
                    <button class="w-full text-left p-3 bg-green-50 hover:bg-green-100 rounded-lg transition-colors">
                        <i class="fas fa-calendar-plus mr-2 text-green-600"></i>
                        Přidat událost
                    </button>
                    <button class="w-full text-left p-3 bg-purple-50 hover:bg-purple-100 rounded-lg transition-colors">
                        <i class="fas fa-file-invoice mr-2 text-purple-600"></i>
                        Vytvořit fakturu
                    </button>
                    <button class="w-full text-left p-3 bg-orange-50 hover:bg-orange-100 rounded-lg transition-colors">
                        <i class="fas fa-envelope mr-2 text-orange-600"></i>
                        Odeslat e-mail
                    </button>
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
