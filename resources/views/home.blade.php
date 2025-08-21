@extends('layouts.app')

@section('title', i18('messages.dashboard') . ' - Arcadia CRM')

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Header Section - podobný HeaderUI -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 mx-6 mt-6 mb-8">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <div class="flex-shrink-0">
                        <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg flex items-center justify-center shadow-sm">
                            <i class="fas fa-chart-line text-white text-lg"></i>
                        </div>
                    </div>
                    <div>
                        <div class="flex items-center space-x-3">
                            <h1 class="text-2xl font-bold text-gray-900">{{ i18('messages.dashboard') }}</h1>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                <i class="fas fa-info-circle mr-1"></i>{{ i18('messages.welcome_to_arcadia') }}
                            </span>
                        </div>
                        <div class="flex items-center space-x-4 mt-1">
                            <p class="text-sm text-gray-600">
                                <i class="fas fa-info-circle mr-1"></i>{{ i18('messages.intelligent_crm_system') }}
                            </p>
                        </div>
                    </div>
                </div>
                <div class="flex items-center space-x-3">
                    <a href="/customers/create" class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 border border-transparent rounded-lg focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 shadow-sm cursor-pointer">
                        <i class="fas fa-user-plus mr-2"></i>{{ i18('messages.create') }} {{ i18('messages.customer') }}
                    </a>
                    <a href="/deals/create" class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-gradient-to-r from-emerald-600 to-emerald-700 hover:from-emerald-700 hover:to-emerald-800 border border-transparent rounded-lg focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 transition-all duration-200 shadow-sm cursor-pointer">
                        <i class="fas fa-plus mr-2"></i>{{ i18('messages.create') }} {{ i18('messages.deal') }}
                    </a>
                    <a href="/contacts/create" class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-gradient-to-r from-purple-600 to-purple-700 hover:from-purple-700 hover:to-purple-800 border border-transparent rounded-lg focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 transition-all duration-200 shadow-sm cursor-pointer">
                        <i class="fas fa-calendar-plus mr-2"></i>{{ i18('messages.create') }} {{ i18('messages.contact') }}
                    </a>
                </div>
            </div>
        </div>

        <!-- Stats Section - podobný HeaderUI stats -->
        <div class="px-6 py-3 bg-gray-50 rounded-b-lg">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-6">
                    <div class="flex items-center space-x-2">
                        <span class="text-sm font-medium text-gray-700">{{ i18('messages.customers') }}:</span>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                            {{ $customersCount ?? 0 }}
                        </span>
                    </div>
                    <div class="flex items-center space-x-2">
                        <span class="text-sm font-medium text-gray-700">{{ i18('messages.contacts') }}:</span>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800">
                            {{ $contactsCount ?? 0 }}
                        </span>
                    </div>
                    <div class="flex items-center space-x-2">
                        <span class="text-sm font-medium text-gray-700">{{ i18('messages.deals') }}:</span>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                            {{ $dealsCount ?? 0 }}
                        </span>
                    </div>
                    <div class="flex items-center space-x-2">
                        <span class="text-sm font-medium text-gray-700">{{ i18('messages.total_value') }}:</span>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                            {{ number_format($totalValue ?? 0, 0, ',', ' ') }} {{ i18('messages.currency_czk') }}
                        </span>
                    </div>
                </div>
                <div class="flex items-center space-x-2 text-sm text-gray-500">
                    <i class="fas fa-clock mr-1"></i>
                    <span>{{ i18('messages.last_update') }}: {{ date('d.m.Y H:i') }}</span>
                </div>
            </div>
        </div>
    </div>

        <!-- Main Dashboard Content -->
    <div class="px-6 py-8 sm:px-8 lg:px-12">
        <div class="mx-auto max-w-7xl">

            <!-- Charts Section - podobný TableUI -->
            <div class="grid grid-cols-1 xl:grid-cols-3 gap-8 mb-8">
                <!-- Deals Chart -->
                <div class="xl:col-span-2 bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                    <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900">{{ i18('messages.analytical_overview') }}</h3>
                                <p class="text-sm text-gray-600 mt-1">{{ i18('messages.deal_value_development') }}</p>
                            </div>
                            <div class="flex items-center space-x-3">
                                <select class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    <option>{{ i18('messages.last_30_days') }}</option>
                                    <option>{{ i18('messages.last_90_days') }}</option>
                                    <option>{{ i18('messages.this_year') }}</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="p-6">
                        <div class="h-80">
                            <canvas id="dealsChart" class="w-full h-full"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Contacts Chart -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                    <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">{{ i18('messages.contacts') }}</h3>
                            <p class="text-sm text-gray-600 mt-1">{{ i18('messages.contact_type_distribution') }}</p>
                        </div>
                    </div>
                    <div class="p-6">
                        <div class="h-64">
                            <canvas id="contactsChart" class="w-full h-full"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Activities and Recent Items -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Recent Activities -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                    <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900">{{ i18('messages.recent_activities') }}</h3>
                                <p class="text-sm text-gray-600 mt-1">{{ i18('messages.latest_events_in_crm') }}</p>
                            </div>
                            <a href="/activities" class="text-blue-600 hover:text-blue-700 text-sm font-medium">
                                {{ i18('messages.view_all') }}
                            </a>
                        </div>
                    </div>
                    <div class="p-6">
                        <div class="space-y-4 max-h-[400px] overflow-y-auto custom-scrollbar">
                            @if(isset($recentActivities) && count($recentActivities) > 0)
                                @foreach($recentActivities as $activity)
                                <div class="flex items-start space-x-4 p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors group cursor-pointer">
                                    <div class="flex-shrink-0 mt-1">
                                        <div class="w-10 h-10 rounded-lg flex items-center justify-center shadow-sm
                                            @if($activity->getType() === 'contact')
                                                bg-blue-100 text-blue-600
                                            @elseif($activity->getType() === 'deal')
                                                bg-green-100 text-green-600
                                            @else
                                                bg-purple-100 text-purple-600
                                            @endif">
                                            <i class="fas text-sm
                                                @if($activity->getType() === 'contact')
                                                    fa-comment
                                                @elseif($activity->getType() === 'deal')
                                                    fa-handshake
                                                @else
                                                    fa-user
                                                @endif">
                                            </i>
                                        </div>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center justify-between">
                                            <p class="text-sm font-semibold text-gray-900 truncate">{{ $activity->getTitle() }}</p>
                                            <p class="text-xs text-gray-500 whitespace-nowrap ml-2">{{ $activity->getCreatedAt()->diffForHumans() }}</p>
                                        </div>
                                        <p class="text-sm text-gray-600 mt-1">
                                            @if($activity->getCustomer())
                                                <a href="/customers/{{ $activity->getCustomer()->getId() }}" class="hover:text-blue-600 font-medium">
                                                    {{ $activity->getCustomer()->getName() }}
                                                </a>
                                            @endif
                                            @if($activity->getDeal())
                                                @if($activity->getCustomer()) • @endif
                                                <a href="/deals/{{ $activity->getDeal()->getId() }}" class="hover:text-blue-600 font-medium">
                                                    {{ $activity->getDeal()->getTitle() }}
                                                </a>
                                            @endif
                                        </p>
                                        @if($activity->getDescription())
                                            <p class="text-xs text-gray-500 mt-2 line-clamp-2">{{ $activity->getDescription() }}</p>
                                        @endif
                                    </div>
                                    <div class="opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                                        @if($activity->getType() === 'contact' && $activity->getContact())
                                            <a href="/contacts/{{ $activity->getContact()->getId() }}"
                                               class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-blue-100 text-blue-600 hover:bg-blue-200 transition-colors"
                                               title="{{ i18('messages.view') }}">
                                                <i class="fas fa-arrow-right text-xs"></i>
                                            </a>
                                        @elseif($activity->getType() === 'deal' && $activity->getDeal())
                                            <a href="/deals/{{ $activity->getDeal()->getId() }}"
                                               class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-emerald-100 text-emerald-600 hover:bg-emerald-200 transition-colors"
                                               title="{{ i18('messages.view') }}">
                                                <i class="fas fa-arrow-right text-xs"></i>
                                            </a>
                                        @endif
                                    </div>
                                </div>
                                @endforeach
                            @else
                                <div class="text-center py-12">
                                    <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                        <i class="fas fa-clock text-gray-400 text-xl"></i>
                                    </div>
                                    <p class="text-gray-500 font-medium">{{ i18('messages.no_recent_activities') }}</p>
                                    <p class="text-gray-400 text-sm mt-1">{{ i18('messages.activities_will_appear_here') }}</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Quick Access Panel -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                    <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">{{ i18('messages.quick_actions') }}</h3>
                            <p class="text-sm text-gray-600 mt-1">{{ i18('messages.most_used_functions') }}</p>
                        </div>
                    </div>
                    <div class="p-6">
                        <div class="space-y-4">
                            <!-- Recent Customers -->
                            <div class="p-4 bg-blue-50 rounded-lg border border-blue-200">
                                <div class="flex items-center justify-between mb-3">
                                    <h4 class="font-semibold text-blue-900">{{ i18('messages.recent_customers') }}</h4>
                                    <a href="/customers" class="text-blue-600 hover:text-blue-700 text-sm">{{ i18('messages.view_all') }}</a>
                                </div>
                                <div class="space-y-2 max-h-32 overflow-y-auto">
                                    @if(isset($customers) && count($customers) > 0)
                                        @foreach(array_slice($customers, 0, 3) as $customer)
                                        <div class="flex items-center space-x-3 p-2 bg-white rounded-lg hover:bg-gray-50 transition-colors cursor-pointer" onclick="window.location.href='/customers/{{ $customer->getId() }}'">
                                            <div class="w-8 h-8 bg-blue-500 rounded-lg flex items-center justify-center text-white text-xs font-bold">
                                                {{ substr($customer->getName(), 0, 1) }}
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <p class="text-sm font-medium text-gray-900 truncate">{{ $customer->getName() }}</p>
                                                <p class="text-xs text-gray-500 truncate">{{ $customer->getCompany() ?: i18('messages.private_person') }}</p>
                                            </div>
                                        </div>
                                        @endforeach
                                    @else
                                        <p class="text-sm text-gray-500">{{ i18('messages.no_customers_found') }}</p>
                                    @endif
                                </div>
                            </div>

                            <!-- Recent Deals -->
                            <div class="p-4 bg-emerald-50 rounded-lg border border-emerald-200">
                                <div class="flex items-center justify-between mb-3">
                                    <h4 class="font-semibold text-emerald-900">{{ i18('messages.active_deals') }}</h4>
                                    <a href="/deals" class="text-emerald-600 hover:text-emerald-700 text-sm">{{ i18('messages.view_all') }}</a>
                                </div>
                                <div class="space-y-2 max-h-32 overflow-y-auto">
                                    @if(isset($deals) && count($deals) > 0)
                                        @foreach(array_slice($deals, 0, 3) as $deal)
                                        <div class="flex items-center justify-between p-2 bg-white rounded-lg hover:bg-gray-50 transition-colors cursor-pointer" onclick="window.location.href='/deals/{{ $deal->getId() }}'">
                                            <div class="flex-1 min-w-0">
                                                <p class="text-sm font-medium text-gray-900 truncate">{{ $deal->getTitle() }}</p>
                                                <p class="text-xs text-gray-500">{{ number_format($deal->getValue(), 0, ',', ' ') }} {{ i18('messages.currency_czk') }}</p>
                                            </div>
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                                                @if($deal->getStatus() === 'active')
                                                    bg-emerald-100 text-emerald-800
                                                @else
                                                    bg-gray-100 text-gray-800
                                                @endif">
                                                {{ $deal->getStatus() === 'active' ? i18('messages.active') : i18('messages.inactive') }}
                                            </span>
                                        </div>
                                        @endforeach
                                    @else
                                        <p class="text-sm text-gray-500">{{ i18('messages.no_deals_found') }}</p>
                                    @endif
                                </div>
                            </div>

                            <!-- Recent Contacts -->
                            <div class="p-4 bg-purple-50 rounded-lg border border-purple-200">
                                <div class="flex items-center justify-between mb-3">
                                    <h4 class="font-semibold text-purple-900">{{ i18('messages.recent_contacts') }}</h4>
                                    <a href="/contacts" class="text-purple-600 hover:text-purple-700 text-sm">{{ i18('messages.view_all') }}</a>
                                </div>
                                <div class="space-y-2 max-h-32 overflow-y-auto">
                                    @if(isset($contacts) && count($contacts) > 0)
                                        @foreach(array_slice($contacts, 0, 3) as $contact)
                                        <div class="flex items-center space-x-3 p-2 bg-white rounded-lg hover:bg-gray-50 transition-colors cursor-pointer" onclick="window.location.href='/contacts/{{ $contact->getId() }}'">
                                            <div class="w-8 h-8 bg-purple-500 rounded-lg flex items-center justify-center text-white">
                                                <i class="fas fa-phone text-xs"></i>
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <p class="text-sm font-medium text-gray-900 truncate">{{ $contact->getSubject() }}</p>
                                                <p class="text-xs text-gray-500">{{ $contact->getContactDate()->format('d.m.Y') }}</p>
                                            </div>
                                        </div>
                                        @endforeach
                                    @else
                                        <p class="text-sm text-gray-500">{{ i18('messages.no_contacts_found') }}</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Custom Styles for Scrollbar -->
    <style>
        .custom-scrollbar::-webkit-scrollbar {
            width: 6px;
        }
        .custom-scrollbar::-webkit-scrollbar-track {
            background: #f1f5f9;
            border-radius: 3px;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 3px;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
        .line-clamp-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
    </style>
</div>

@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Modern chart configuration
    Chart.defaults.font.family = '"Inter", "system-ui", "sans-serif"';
    Chart.defaults.font.size = 12;
    Chart.defaults.color = '#64748b';

    // Prepare real data from PHP variables
    const dealsData = {!! json_encode($activeDeals ?? []) !!};
    const contactsData = {!! json_encode($recentContacts ?? []) !!};

    // Process deals data for chart with last 6 months
    const dealsByMonth = {};
    const currentDate = new Date();

    // Initialize last 6 months
    for (let i = 5; i >= 0; i--) {
        const date = new Date(currentDate.getFullYear(), currentDate.getMonth() - i, 1);
        const monthKey = date.toLocaleString('cs-CZ', { month: 'short' });
        dealsByMonth[monthKey] = 0;
    }

    // Aggregate real deals data
    dealsData.forEach(deal => {
        const date = new Date(deal.created_at || deal.createdAt);
        const month = date.toLocaleString('cs-CZ', { month: 'short' });
        if (dealsByMonth.hasOwnProperty(month)) {
            dealsByMonth[month] += (deal.value || 0) * ((deal.probability || 100) / 100);
        }
    });

    // Process real contacts data for chart
    const contactsByType = contactsData.reduce((acc, contact) => {
        const type = contact.type || 'other';
        acc[type] = (acc[type] || 0) + 1;
        return acc;
    }, {});

    // Modern Deals Chart
    const dealsCtx = document.getElementById('dealsChart');
    if (dealsCtx) {
        new Chart(dealsCtx, {
            type: 'line',
            data: {
                labels: Object.keys(dealsByMonth),
                datasets: [{
                    label: '{{ i18("messages.deal_value") }}',
                    data: Object.values(dealsByMonth),
                    borderColor: '#3b82f6',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#3b82f6',
                    pointBorderColor: '#ffffff',
                    pointBorderWidth: 2,
                    pointRadius: 6,
                    pointHoverRadius: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    intersect: false,
                    mode: 'index'
                },
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        titleColor: '#ffffff',
                        bodyColor: '#ffffff',
                        borderColor: '#3b82f6',
                        borderWidth: 1,
                        cornerRadius: 8,
                        displayColors: false,
                        callbacks: {
                            label: function(context) {
                                return new Intl.NumberFormat('cs-CZ', {
                                    style: 'currency',
                                    currency: 'CZK',
                                    maximumFractionDigits: 0
                                }).format(context.parsed.y);
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            color: '#64748b',
                            font: {
                                weight: '500'
                            }
                        }
                    },
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: '#f1f5f9',
                            borderDash: [3, 3]
                        },
                        ticks: {
                            color: '#64748b',
                            callback: function(value) {
                                return new Intl.NumberFormat('cs-CZ', {
                                    style: 'currency',
                                    currency: 'CZK',
                                    maximumFractionDigits: 0,
                                    notation: 'compact'
                                }).format(value);
                            }
                        }
                    }
                }
            }
        });
    }

    // Modern Contacts Chart
    const contactsCtx = document.getElementById('contactsChart');
    if (contactsCtx) {
        const contactTypes = {
            'email': '{{ i18("messages.email") }}',
            'phone': '{{ i18("messages.phone") }}',
            'meeting': '{{ i18("messages.meeting") }}',
            'other': '{{ i18("messages.other") }}'
        };

        const chartData = Object.keys(contactsByType).map(type => ({
            label: contactTypes[type] || type,
            value: contactsByType[type]
        }));

        new Chart(contactsCtx, {
            type: 'doughnut',
            data: {
                labels: chartData.map(item => item.label),
                datasets: [{
                    data: chartData.map(item => item.value),
                    backgroundColor: [
                        '#3b82f6', // blue
                        '#10b981', // emerald
                        '#8b5cf6', // violet
                        '#f59e0b'  // amber
                    ],
                    borderWidth: 0,
                    hoverBorderWidth: 3,
                    hoverBorderColor: '#ffffff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '60%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 20,
                            usePointStyle: true,
                            pointStyle: 'circle',
                            font: {
                                weight: '500'
                            }
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        titleColor: '#ffffff',
                        bodyColor: '#ffffff',
                        borderColor: '#3b82f6',
                        borderWidth: 1,
                        cornerRadius: 8,
                        displayColors: true,
                        callbacks: {
                            label: function(context) {
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = ((context.parsed / total) * 100).toFixed(1);
                                return `${context.label}: ${context.parsed} (${percentage}%)`;
                            }
                        }
                    }
                }
            }
        });
    }

    // Smooth scroll for internal links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });

    // Auto-refresh data every 5 minutes
    setInterval(() => {
        window.location.reload();
    }, 300000);
});
</script>
@endsection
