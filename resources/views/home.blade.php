@extends('layouts.app')

@section('title', 'Domů - Arcadia')

@section('styles')
<link href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css" rel="stylesheet">
@endsection

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Header Section -->
    <div class="bg-gradient-to-r from-blue-600 to-indigo-600 text-white p-8 rounded-lg shadow-lg mb-8">
        <div class="text-center">
            <h2 class="text-4xl font-extrabold mb-4">
                Vítejte v Arcadia
            </h2>
            <p class="text-xl opacity-90">
                Inteligentní CRM systém pro moderní business
            </p>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mt-8">
            <div class="bg-white/10 backdrop-blur-md p-6 rounded-xl">
                <div class="text-3xl font-bold">{{ $customersCount ?? 0 }}</div>
                <div class="text-sm opacity-75">Zákazníků</div>
            </div>
            <div class="bg-white/10 backdrop-blur-md p-6 rounded-xl">
                <div class="text-3xl font-bold">{{ $contactsCount ?? 0 }}</div>
                <div class="text-sm opacity-75">Kontaktů</div>
            </div>
            <div class="bg-white/10 backdrop-blur-md p-6 rounded-xl">
                <div class="text-3xl font-bold">{{ $dealsCount ?? 0 }}</div>
                <div class="text-sm opacity-75">Obchodů</div>
            </div>
            <div class="bg-white/10 backdrop-blur-md p-6 rounded-xl">
                <div class="text-3xl font-bold">{{ number_format($totalValue ?? 0, 0, ',', ' ') }} Kč</div>
                <div class="text-sm opacity-75">Celková hodnota</div>
            </div>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 xl:grid-cols-2 gap-8 px-4">
        <!-- Charts Section -->
        <div class="bg-white rounded-lg shadow-lg p-6" id="charts-widget">
            <h3 class="text-xl font-semibold mb-6">Analytický přehled</h3>
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div>
                    <canvas id="dealsChart"></canvas>
                </div>
                <div>
                    <canvas id="contactsChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Recent Activities -->
        <div class="bg-white rounded-lg shadow-lg p-6" id="activities-widget">
            <h3 class="text-xl font-semibold mb-6">Poslední aktivity</h3>
            <div class="space-y-4 max-h-[400px] overflow-y-auto">
                @if(isset($recentActivities) && count($recentActivities) > 0)
                    @foreach($recentActivities as $activity)
                    <div class="flex items-center space-x-4 p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition group">
                        <div class="flex-shrink-0">
                            <div class="w-10 h-10 rounded-full flex items-center justify-center
                                @if($activity->getType() === 'contact')
                                    bg-blue-100 text-blue-600
                                @elseif($activity->getType() === 'deal')
                                    bg-green-100 text-green-600
                                @else
                                    bg-purple-100 text-purple-600
                                @endif">
                                <i class="fas
                                    @if($activity->getType()  === 'contact')
                                        fa-comment
                                    @elseif($activity->getType()  === 'deal')
                                        fa-handshake
                                    @else
                                        fa-user
                                    @endif">
                                </i>
                            </div>
                        </div>
                        <div class="flex-1">
                            <div class="flex items-center justify-between">
                                <p class="text-sm font-semibold">{{ $activity->getTitle() }}</p>
                                <p class="text-xs text-gray-500">{{ $activity->getCreatedAt()->diffForHumans() }}</p>
                            </div>
                            <p class="text-xs text-gray-600 mt-1">
                                @if($activity->getCustomer())
                                    <a href="/customers/{{ $activity->getCustomer()->getId() }}" class="hover:text-blue-600">
                                        {{ $activity->getCustomer()->getName() }}
                                    </a>
                                @endif
                                @if($activity->getDeal())
                                    @if($activity->getCustomer()) - @endif
                                    <a href="/deals/{{ $activity->getDeal()->getId() }}" class="hover:text-blue-600">
                                        {{ $activity->getDeal()->getTitle() }}
                                    </a>
                                @endif
                            </p>
                            @if($activity->getDescription())
                                <p class="text-xs text-gray-500 mt-1">{{ $activity->getDescription() }}</p>
                            @endif
                        </div>
                        <div class="hidden group-hover:flex items-center space-x-2">
                            @if($activity->getType() === 'contact' && $activity->getContact())
                                <a href="/contacts/{{ $activity->getContact()->getId() }}"
                                   class="text-sm text-gray-500 hover:text-blue-600"
                                   title="Zobrazit detail">
                                    <i class="fas fa-eye"></i>
                                </a>
                            @elseif($activity->getType() === 'deal' && $activity->getDeal())
                                <a href="/deals/{{ $activity->getDeal()->getId() }}"
                                   class="text-sm text-gray-500 hover:text-blue-600"
                                   title="Zobrazit detail">
                                    <i class="fas fa-eye"></i>
                                </a>
                            @endif
                        </div>
                    </div>
                    @endforeach
                @else
                    <p class="text-gray-500 text-center">Žádné nedávné aktivity</p>
                @endif
            </div>
        </div>
    </div>

    <!-- Quick Access Panels -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-8 px-4" id="quick-access">
        @foreach(['customers', 'contacts', 'deals'] as $type)
        <div class="bg-white rounded-lg shadow-lg" id="{{ $type }}-widget">
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold">{{ ucfirst($type) }}</h3>
                    <div class="flex items-center space-x-2">
                        <div class="relative" x-data="{ open: false }">
                            <button @click="open = !open" class="text-gray-400 hover:text-gray-600">
                                <i class="fas fa-cog"></i>
                            </button>
                            <!-- Bulk Actions Dropdown -->
                            <div x-show="open" @click.away="open = false" class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg z-50">
                                <div class="py-1">
                                    @if(isset($bulkActions) && isset($bulkActions[$type]))
                                        @foreach($bulkActions[$type] as $action)
                                        <a href="{{ $action['url'] }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                            {{ $action['label'] }}
                                        </a>
                                        @endforeach
                                    @else
                                        <div class="px-4 py-2 text-sm text-gray-500">Žádné akce</div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <button class="text-blue-600 hover:text-blue-800">
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>
                </div>
                <div class="space-y-3 max-h-[300px] overflow-y-auto">
                    <!-- Dynamic content for each type -->
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>

@endsection

@section('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
$(document).ready(function() {
    // Make widgets draggable
    $("#quick-access > div").draggable({
        handle: "h3",
        containment: "parent",
        snap: true,
        snapMode: "outer",
        stack: "#quick-access div"
    });

    // Prepare data from deals
    const dealsData = @json($activeDeals ?? []);
    const contactsData = @json($recentContacts ?? []);

    // Process deals data for chart
    const dealsByMonth = dealsData.reduce((acc, deal) => {
        const date = new Date(deal.created_at);
        const month = date.toLocaleString('cs-CZ', { month: 'short' });
        acc[month] = (acc[month] || 0) + (deal.value * deal.probability);
        return acc;
    }, {});

    // Process contacts data for chart
    const contactsByType = contactsData.reduce((acc, contact) => {
        acc[contact.type] = (acc[contact.type] || 0) + 1;
        return acc;
    }, {});

    // Deals Chart
    const dealsCtx = document.getElementById('dealsChart').getContext('2d');
    new Chart(dealsCtx, {
        type: 'line',
        data: {
            labels: Object.keys(dealsByMonth),
            datasets: [{
                label: 'Hodnota obchodů (Kč)',
                data: Object.values(dealsByMonth),
                borderColor: 'rgb(59, 130, 246)',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom',
                },
                title: {
                    display: true,
                    text: 'Vývoj hodnoty obchodů'
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return new Intl.NumberFormat('cs-CZ', {
                                style: 'currency',
                                currency: 'CZK',
                                maximumFractionDigits: 0
                            }).format(value);
                        }
                    }
                }
            }
        }
    });

    // Contacts Chart
    const contactsCtx = document.getElementById('contactsChart').getContext('2d');
    const contactTypes = {
        'email': 'E-mail',
        'phone': 'Telefon',
        'meeting': 'Osobní schůzka'
    };

    new Chart(contactsCtx, {
        type: 'doughnut',
        data: {
            labels: Object.keys(contactsByType).map(type => contactTypes[type] || type),
            datasets: [{
                data: Object.values(contactsByType),
                backgroundColor: [
                    'rgb(59, 130, 246)',  // modrá
                    'rgb(16, 185, 129)',  // zelená
                    'rgb(139, 92, 246)'   // fialová
                ],
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom',
                },
                title: {
                    display: true,
                    text: 'Rozložení typů kontaktů'
                }
            }
        }
    });

    // Quick access panels data loading
    const loadPanelData = (type, data) => {
        const $panel = $(`#${type}-widget .space-y-3`);
        $panel.empty();

        const itemActions = @json($itemActions ?? []);

        data.forEach(item => {
            let html = '';
            switch(type) {
                case 'customers':
                    html = `
                        <div class="flex items-center p-3 bg-gray-50 rounded-lg hover:bg-gray-100 group">
                            <div class="flex-1">
                                <p class="font-medium">${item.name}</p>
                                <p class="text-sm text-gray-500">${item.company || 'Soukromá osoba'}</p>
                            </div>
                            <div class="hidden group-hover:flex items-center space-x-2">
                                ${itemActions[type] ? Object.entries(itemActions[type]).map(([action, config]) => `
                                    <a href="${config.url.replace('{id}', item.id)}"
                                       class="text-sm text-gray-500 hover:text-blue-600"
                                       title="${config.label}">
                                        <i class="fas fa-${action === 'edit' ? 'pen' : action === 'delete' ? 'trash' : 'eye'}"></i>
                                    </a>
                                `).join('') : ''}
                            </div>
                        </div>`;
                    break;
                case 'contacts':
                    html = `
                        <div class="flex items-center p-3 bg-gray-50 rounded-lg hover:bg-gray-100 group">
                            <div class="flex-1">
                                <p class="font-medium">${item.subject}</p>
                                <p class="text-sm text-gray-500">
                                    ${new Date(item.contact_date).toLocaleDateString('cs-CZ')}
                                    ${item.customer ? `- ${item.customer.name}` : ''}
                                </p>
                            </div>
                            <div class="hidden group-hover:flex items-center space-x-2">
                                ${itemActions[type] ? Object.entries(itemActions[type]).map(([action, config]) => `
                                    <a href="${config.url.replace('{id}', item.id)}"
                                       class="text-sm text-gray-500 hover:text-blue-600"
                                       title="${config.label}">
                                        <i class="fas fa-${action === 'edit' ? 'pen' : action === 'delete' ? 'trash' : 'eye'}"></i>
                                    </a>
                                `).join('') : ''}
                            </div>
                        </div>`;
                    break;
                case 'deals':
                    html = `
                        <div class="flex items-center p-3 bg-gray-50 rounded-lg hover:bg-gray-100 group">
                            <div class="flex-1">
                                <p class="font-medium">${item.title}</p>
                                <div class="flex items-center space-x-2 text-sm text-gray-500">
                                    <span>${new Intl.NumberFormat('cs-CZ', {
                                        style: 'currency',
                                        currency: 'CZK',
                                        maximumFractionDigits: 0
                                    }).format(item.value)}</span>
                                    ${item.customer ? `<span>- ${item.customer.name}</span>` : ''}
                                </div>
                            </div>
                            <div class="hidden group-hover:flex items-center space-x-2">
                                ${itemActions[type] ? Object.entries(itemActions[type]).map(([action, config]) => `
                                    <a href="${config.url.replace('{id}', item.id)}"
                                       class="text-sm text-gray-500 hover:text-blue-600"
                                       title="${config.label}">
                                        <i class="fas fa-${action === 'edit' ? 'pen' : action === 'delete' ? 'trash' : 'eye'}"></i>
                                    </a>
                                `).join('') : ''}
                            </div>
                        </div>`;
                    break;
            }
            $panel.append(html);
        });
    };

    // Load initial data
    loadPanelData('customers', @json($customers ?? []));
    loadPanelData('contacts', @json($contacts ?? []));
    loadPanelData('deals', @json($deals ?? []));

    // Save and load widget positions
    function saveWidgetPositions() {
        const positions = {};
        $("#quick-access > div").each(function() {
            const id = $(this).attr('id');
            const position = $(this).position();
            positions[id] = {
                top: position.top,
                left: position.left
            };
        });
        localStorage.setItem('widgetPositions', JSON.stringify(positions));
    }

    function loadWidgetPositions() {
        const positions = JSON.parse(localStorage.getItem('widgetPositions'));
        if (positions) {
            Object.keys(positions).forEach(id => {
                $(`#${id}`).css({
                    position: 'absolute',
                    top: positions[id].top,
                    left: positions[id].left
                });
            });
        }
    }

    // Event listeners
    $("#quick-access > div").on('dragstop', saveWidgetPositions);

    // Initialize positions
    loadWidgetPositions();
});
</script>
@endsection
