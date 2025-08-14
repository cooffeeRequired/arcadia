<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $project->getName() }} - Projekt</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .progress-bar {
            transition: width 0.3s ease;
        }
    </style>
</head>
<body class="bg-gray-50">
    <div class="min-h-screen">
        <!-- Header -->
        <header class="bg-white shadow-sm border-b border-gray-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center py-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-10 h-10 bg-blue-600 rounded-lg flex items-center justify-center">
                                <i class="fas fa-folder text-white"></i>
                            </div>
                        </div>
                        <div class="ml-4">
                            <h1 class="text-2xl font-bold text-gray-900">{{ $project->getName() }}</h1>
                            <p class="text-gray-600">Projekt pro {{ $project->getCustomer()->getName() }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-4">
                        <span class="px-3 py-1 text-sm font-medium rounded-full
                            @if($project->getStatus() === 'active') bg-green-100 text-green-800
                            @elseif($project->getStatus() === 'completed') bg-blue-100 text-blue-800
                            @elseif($project->getStatus() === 'on_hold') bg-yellow-100 text-yellow-800
                            @else bg-red-100 text-red-800
                            @endif">
                            {{ ucfirst($project->getStatus()) }}
                        </span>
                        @if($project->isOverdue())
                        <span class="px-3 py-1 text-sm font-medium rounded-full bg-red-100 text-red-800">
                            <i class="fas fa-exclamation-triangle mr-1"></i>Po termínu
                        </span>
                        @endif
                    </div>
                </div>
            </div>
        </header>

        <!-- Main content -->
        <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Hlavní obsah -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Popis -->
                    @if($project->getDescription())
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <h2 class="text-lg font-medium text-gray-900 mb-4">O projektu</h2>
                        <p class="text-gray-700 leading-relaxed">{{ $project->getDescription() }}</p>
                    </div>
                    @endif

                    <!-- Progress -->
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <h2 class="text-lg font-medium text-gray-900 mb-4">Průběh projektu</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            @if($project->getEstimatedHours())
                            <div>
                                <div class="flex justify-between text-sm text-gray-600 mb-2">
                                    <span>Průběh práce</span>
                                    <span>{{ round($project->getProgress(), 1) }}%</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-3 mb-2">
                                    <div class="bg-blue-600 h-3 rounded-full progress-bar" style="width: {{ $project->getProgress() }}%"></div>
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
                                    <div class="bg-green-600 h-3 rounded-full progress-bar" style="width: {{ $project->getBudgetUtilization() }}%"></div>
                                </div>
                                <div class="text-sm text-gray-500">
                                    {{ number_format($project->getActualCost() ?? 0, 0, ',', ' ') }} / {{ number_format($project->getBudget(), 0, ',', ' ') }} Kč
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>

                    <!-- Vlastní obsah -->
                    @if($project->getMiniPageContent())
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <h2 class="text-lg font-medium text-gray-900 mb-4">Aktuality</h2>
                        <div class="prose max-w-none">
                            {!! $project->getMiniPageContent() !!}
                        </div>
                    </div>
                    @endif
                </div>

                <!-- Sidebar -->
                <div class="space-y-6">
                    <!-- Základní informace -->
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Informace o projektu</h3>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-500">Zákazník</label>
                                <p class="text-gray-900">{{ $project->getCustomer()->getName() }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-500">Manažer projektu</label>
                                <p class="text-gray-900">{{ $project->getManager()->getName() }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-500">Datum začátku</label>
                                <p class="text-gray-900">{{ $project->getStartDate()->format('d.m.Y') }}</p>
                            </div>
                            @if($project->getEndDate())
                            <div>
                                <label class="block text-sm font-medium text-gray-500">Plánované ukončení</label>
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

                    <!-- Kontakt -->
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Kontakt</h3>
                        <div class="space-y-3">
                            <div class="flex items-center">
                                <i class="fas fa-user text-gray-400 mr-3"></i>
                                <span class="text-gray-700">{{ $project->getManager()->getName() }}</span>
                            </div>
                            <div class="flex items-center">
                                <i class="fas fa-envelope text-gray-400 mr-3"></i>
                                <span class="text-gray-700">{{ $project->getManager()->getEmail() }}</span>
                            </div>
                            @if($project->getManager()->getPhone())
                            <div class="flex items-center">
                                <i class="fas fa-phone text-gray-400 mr-3"></i>
                                <span class="text-gray-700">{{ $project->getManager()->getPhone() }}</span>
                            </div>
                            @endif
                        </div>
                    </div>

                    <!-- Status -->
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Stav projektu</h3>
                        <div class="space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="text-gray-600">Status</span>
                                <span class="px-2 py-1 text-xs font-medium rounded-full
                                    @if($project->getStatus() === 'active') bg-green-100 text-green-800
                                    @elseif($project->getStatus() === 'completed') bg-blue-100 text-blue-800
                                    @elseif($project->getStatus() === 'on_hold') bg-yellow-100 text-yellow-800
                                    @else bg-red-100 text-red-800
                                    @endif">
                                    {{ ucfirst($project->getStatus()) }}
                                </span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-gray-600">Priorita</span>
                                <span class="px-2 py-1 text-xs font-medium rounded-full
                                    @if($project->getPriority() === 'high') bg-red-100 text-red-800
                                    @elseif($project->getPriority() === 'medium') bg-yellow-100 text-yellow-800
                                    @else bg-green-100 text-green-800
                                    @endif">
                                    {{ ucfirst($project->getPriority()) }}
                                </span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-gray-600">Poslední aktualizace</span>
                                <span class="text-gray-900">{{ $project->getUpdatedAt()->format('d.m.Y') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>

        <!-- Footer -->
        <footer class="bg-white border-t border-gray-200 mt-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
                <div class="text-center text-gray-500">
                    <p>&copy; {{ date('Y') }} {{ $project->getCustomer()->getName() }}. Všechna práva vyhrazena.</p>
                    <p class="mt-2 text-sm">Tato stránka byla vygenerována automaticky pro projekt "{{ $project->getName() }}"</p>
                </div>
            </div>
        </footer>
    </div>

    <script>
        // Animace progress barů
        document.addEventListener('DOMContentLoaded', function() {
            const progressBars = document.querySelectorAll('.progress-bar');
            progressBars.forEach(bar => {
                const width = bar.style.width;
                bar.style.width = '0%';
                setTimeout(() => {
                    bar.style.width = width;
                }, 500);
            });
        });
    </script>
</body>
</html>
