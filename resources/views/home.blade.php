@extends('layouts.app')

@section('title', 'Dashboard - Arcadia CRM')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-50">
    <!-- Modern Hero Section -->
    <div class="relative overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-r from-blue-600/90 to-indigo-700/90"></div>
        <div class="absolute inset-0 bg-[url('data:image/svg+xml,%3Csvg width="60" height="60" viewBox="0 0 60 60" xmlns="http://www.w3.org/2000/svg"%3E%3Cg fill="none" fill-rule="evenodd"%3E%3Cg fill="%23ffffff" fill-opacity="0.05"%3E%3Ccircle cx="30" cy="30" r="4"/%3E%3C/g%3E%3C/g%3E%3C/svg%3E')] opacity-20"></div>

        <div class="relative px-6 py-16 sm:px-8 lg:px-12">
            <div class="mx-auto max-w-7xl">
                <!-- Welcome Header -->
                <div class="text-center text-white mb-12">
                    <div class="inline-flex items-center mb-6">
                        <div class="w-16 h-16 bg-white/20 backdrop-blur-md rounded-2xl flex items-center justify-center mr-4">
                            <i class="fas fa-chart-line text-2xl text-white"></i>
                        </div>
                        <div class="text-left">
                            <h1 class="text-4xl lg:text-5xl font-bold tracking-tight">
                                Vítejte zpět!
                            </h1>
                            <p class="text-xl text-blue-100 mt-2">
                                Váš obchodní přehled na jednom místě
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Enhanced Stats Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div class="group">
                        <div class="bg-white/15 backdrop-blur-md border border-white/20 p-6 rounded-2xl hover:bg-white/20 transition-all duration-300 transform hover:-translate-y-1">
                            <div class="flex items-center justify-between">
                                <div>
                                    <div class="text-3xl font-bold text-white">{{ $customersCount ?? 0 }}</div>
                                    <div class="text-blue-100 text-sm font-medium mt-1">Zákazníků</div>
                                    <div class="text-xs text-blue-200 mt-2">
                                        <i class="fas fa-arrow-up mr-1"></i>+12% tento měsíc
                                    </div>
                                </div>
                                <div class="w-12 h-12 bg-blue-500/30 rounded-xl flex items-center justify-center">
                                    <i class="fas fa-users text-white text-xl"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="group">
                        <div class="bg-white/15 backdrop-blur-md border border-white/20 p-6 rounded-2xl hover:bg-white/20 transition-all duration-300 transform hover:-translate-y-1">
                            <div class="flex items-center justify-between">
                                <div>
                                    <div class="text-3xl font-bold text-white">{{ $contactsCount ?? 0 }}</div>
                                    <div class="text-blue-100 text-sm font-medium mt-1">Kontaktů</div>
                                    <div class="text-xs text-blue-200 mt-2">
                                        <i class="fas fa-arrow-up mr-1"></i>+8% tento týden
                                    </div>
                                </div>
                                <div class="w-12 h-12 bg-emerald-500/30 rounded-xl flex items-center justify-center">
                                    <i class="fas fa-phone text-white text-xl"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="group">
                        <div class="bg-white/15 backdrop-blur-md border border-white/20 p-6 rounded-2xl hover:bg-white/20 transition-all duration-300 transform hover:-translate-y-1">
                            <div class="flex items-center justify-between">
                                <div>
                                    <div class="text-3xl font-bold text-white">{{ $dealsCount ?? 0 }}</div>
                                    <div class="text-blue-100 text-sm font-medium mt-1">Aktivních obchodů</div>
                                    <div class="text-xs text-blue-200 mt-2">
                                        <i class="fas fa-arrow-up mr-1"></i>+15% tento měsíc
                                    </div>
                                </div>
                                <div class="w-12 h-12 bg-orange-500/30 rounded-xl flex items-center justify-center">
                                    <i class="fas fa-handshake text-white text-xl"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="group">
                        <div class="bg-white/15 backdrop-blur-md border border-white/20 p-6 rounded-2xl hover:bg-white/20 transition-all duration-300 transform hover:-translate-y-1">
                            <div class="flex items-center justify-between">
                                <div>
                                    <div class="text-2xl font-bold text-white">{{ number_format($totalValue ?? 0, 0, ',', ' ') }}</div>
                                    <div class="text-blue-100 text-sm font-medium mt-1">Kč celková hodnota</div>
                                    <div class="text-xs text-blue-200 mt-2">
                                        <i class="fas fa-arrow-up mr-1"></i>+22% tento měsíc
                                    </div>
                                </div>
                                <div class="w-12 h-12 bg-purple-500/30 rounded-xl flex items-center justify-center">
                                    <i class="fas fa-chart-pie text-white text-xl"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="flex flex-wrap gap-4 mt-8 justify-center">
                    <a href="/customers/create" class="inline-flex items-center px-6 py-3 bg-white/20 backdrop-blur-md border border-white/30 rounded-xl text-white font-medium hover:bg-white/30 transition-all duration-300 group">
                        <i class="fas fa-user-plus mr-2 group-hover:scale-110 transition-transform"></i>
                        Nový zákazník
                    </a>
                    <a href="/deals/create" class="inline-flex items-center px-6 py-3 bg-white/20 backdrop-blur-md border border-white/30 rounded-xl text-white font-medium hover:bg-white/30 transition-all duration-300 group">
                        <i class="fas fa-plus mr-2 group-hover:scale-110 transition-transform"></i>
                        Nový obchod
                    </a>
                    <a href="/contacts/create" class="inline-flex items-center px-6 py-3 bg-white/20 backdrop-blur-md border border-white/30 rounded-xl text-white font-medium hover:bg-white/30 transition-all duration-300 group">
                        <i class="fas fa-calendar-plus mr-2 group-hover:scale-110 transition-transform"></i>
                        Nový kontakt
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Dashboard Content -->
    <div class="px-6 py-8 sm:px-8 lg:px-12">
        <div class="mx-auto max-w-7xl">

            <!-- Charts Section -->
            <div class="grid grid-cols-1 xl:grid-cols-3 gap-8 mb-8">
                <!-- Deals Chart -->
                <div class="xl:col-span-2 bg-white/80 backdrop-blur-sm border border-gray-200/50 rounded-2xl shadow-xl p-8 hover:shadow-2xl transition-shadow duration-300">
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <h3 class="text-2xl font-bold text-gray-900">Analytický přehled</h3>
                            <p class="text-gray-600 mt-1">Přehled výkonnosti vašich obchodů</p>
                        </div>
                        <div class="flex items-center space-x-3">
                            <select class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <option>Posledních 30 dní</option>
                                <option>Posledních 90 dní</option>
                                <option>Tento rok</option>
                            </select>
                        </div>
                    </div>
                    <div class="h-80">
                        <canvas id="dealsChart" class="w-full h-full"></canvas>
                    </div>
                </div>

                <!-- Contacts Chart -->
                <div class="bg-white/80 backdrop-blur-sm border border-gray-200/50 rounded-2xl shadow-xl p-8 hover:shadow-2xl transition-shadow duration-300">
                    <div class="mb-6">
                        <h3 class="text-xl font-bold text-gray-900">Kontakty</h3>
                        <p class="text-gray-600 mt-1">Rozložení typů kontaktů</p>
                    </div>
                    <div class="h-64">
                        <canvas id="contactsChart" class="w-full h-full"></canvas>
                    </div>
                </div>
            </div>

            <!-- Activities and Recent Items -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Recent Activities -->
                <div class="bg-white/80 backdrop-blur-sm border border-gray-200/50 rounded-2xl shadow-xl p-8 hover:shadow-2xl transition-shadow duration-300">
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <h3 class="text-xl font-bold text-gray-900">Poslední aktivity</h3>
                            <p class="text-gray-600 mt-1">Nejnovější události ve vašem CRM</p>
                        </div>
                        <a href="/activities" class="text-blue-600 hover:text-blue-700 text-sm font-medium">
                            Zobrazit vše
                        </a>
                    </div>
                    <div class="space-y-4 max-h-[400px] overflow-y-auto custom-scrollbar">
                        @if(isset($recentActivities) && count($recentActivities) > 0)
                            @foreach($recentActivities as $activity)
                            <div class="flex items-start space-x-4 p-4 rounded-xl bg-gradient-to-r from-gray-50 to-gray-100/50 hover:from-blue-50 hover:to-indigo-100/50 transition-all duration-300 group cursor-pointer">
                                <div class="flex-shrink-0 mt-1">
                                    <div class="w-10 h-10 rounded-xl flex items-center justify-center shadow-md
                                        @if($activity->getType() === 'contact')
                                            bg-gradient-to-br from-blue-500 to-blue-600 text-white
                                        @elseif($activity->getType() === 'deal')
                                            bg-gradient-to-br from-emerald-500 to-emerald-600 text-white
                                        @else
                                            bg-gradient-to-br from-purple-500 to-purple-600 text-white
                                        @endif">
                                        <i class="fas text-sm
                                            @if($activity->getType() === 'contact')
                                                fa-comment-dots
                                            @elseif($activity->getType() === 'deal')
                                                fa-handshake
                                            @else
                                                fa-user-circle
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
                                           title="Zobrazit detail">
                                            <i class="fas fa-arrow-right text-xs"></i>
                                        </a>
                                    @elseif($activity->getType() === 'deal' && $activity->getDeal())
                                        <a href="/deals/{{ $activity->getDeal()->getId() }}"
                                           class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-emerald-100 text-emerald-600 hover:bg-emerald-200 transition-colors"
                                           title="Zobrazit detail">
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
                                <p class="text-gray-500 font-medium">Zatím žádné aktivity</p>
                                <p class="text-gray-400 text-sm mt-1">Aktivity se budou zobrazovat zde</p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Quick Access Panel -->
                <div class="bg-white/80 backdrop-blur-sm border border-gray-200/50 rounded-2xl shadow-xl p-8 hover:shadow-2xl transition-shadow duration-300">
                    <div class="mb-6">
                        <h3 class="text-xl font-bold text-gray-900">Rychlé akce</h3>
                        <p class="text-gray-600 mt-1">Nejčastěji používané funkce</p>
                    </div>

                    <div class="space-y-4">
                        <!-- Recent Customers -->
                        <div class="p-4 bg-gradient-to-r from-blue-50 to-blue-100/50 rounded-xl border border-blue-200/50">
                            <div class="flex items-center justify-between mb-3">
                                <h4 class="font-semibold text-blue-900">Nedávní zákazníci</h4>
                                <a href="/customers" class="text-blue-600 hover:text-blue-700 text-sm">Zobrazit vše</a>
                            </div>
                            <div class="space-y-2 max-h-32 overflow-y-auto">
                                @if(isset($customers) && count($customers) > 0)
                                    @foreach(array_slice($customers, 0, 3) as $customer)
                                    <div class="flex items-center space-x-3 p-2 bg-white/60 rounded-lg hover:bg-white/80 transition-colors cursor-pointer" onclick="window.location.href='/customers/{{ $customer->getId() }}'">
                                        <div class="w-8 h-8 bg-blue-500 rounded-lg flex items-center justify-center text-white text-xs font-bold">
                                            {{ substr($customer->getName(), 0, 1) }}
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-medium text-gray-900 truncate">{{ $customer->getName() }}</p>
                                            <p class="text-xs text-gray-500 truncate">{{ $customer->getCompany() ?: 'Soukromá osoba' }}</p>
                                        </div>
                                    </div>
                                    @endforeach
                                @else
                                    <p class="text-sm text-gray-500">Žádní zákazníci</p>
                                @endif
                            </div>
                        </div>

                        <!-- Recent Deals -->
                        <div class="p-4 bg-gradient-to-r from-emerald-50 to-emerald-100/50 rounded-xl border border-emerald-200/50">
                            <div class="flex items-center justify-between mb-3">
                                <h4 class="font-semibold text-emerald-900">Aktivní obchody</h4>
                                <a href="/deals" class="text-emerald-600 hover:text-emerald-700 text-sm">Zobrazit vše</a>
                            </div>
                            <div class="space-y-2 max-h-32 overflow-y-auto">
                                @if(isset($deals) && count($deals) > 0)
                                    @foreach(array_slice($deals, 0, 3) as $deal)
                                    <div class="flex items-center justify-between p-2 bg-white/60 rounded-lg hover:bg-white/80 transition-colors cursor-pointer" onclick="window.location.href='/deals/{{ $deal->getId() }}'">
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-medium text-gray-900 truncate">{{ $deal->getTitle() }}</p>
                                            <p class="text-xs text-gray-500">{{ number_format($deal->getValue(), 0, ',', ' ') }} Kč</p>
                                        </div>
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                                            @if($deal->getStatus() === 'active')
                                                bg-emerald-100 text-emerald-800
                                            @else
                                                bg-gray-100 text-gray-800
                                            @endif">
                                            {{ $deal->getStatus() === 'active' ? 'Aktivní' : 'Neaktivní' }}
                                        </span>
                                    </div>
                                    @endforeach
                                @else
                                    <p class="text-sm text-gray-500">Žádné obchody</p>
                                @endif
                            </div>
                        </div>

                        <!-- Recent Contacts -->
                        <div class="p-4 bg-gradient-to-r from-purple-50 to-purple-100/50 rounded-xl border border-purple-200/50">
                            <div class="flex items-center justify-between mb-3">
                                <h4 class="font-semibold text-purple-900">Nedávné kontakty</h4>
                                <a href="/contacts" class="text-purple-600 hover:text-purple-700 text-sm">Zobrazit vše</a>
                            </div>
                            <div class="space-y-2 max-h-32 overflow-y-auto">
                                @if(isset($contacts) && count($contacts) > 0)
                                    @foreach(array_slice($contacts, 0, 3) as $contact)
                                    <div class="flex items-center space-x-3 p-2 bg-white/60 rounded-lg hover:bg-white/80 transition-colors cursor-pointer" onclick="window.location.href='/contacts/{{ $contact->getId() }}'">
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
                                    <p class="text-sm text-gray-500">Žádné kontakty</p>
                                @endif
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

    // Prepare data from deals
    const dealsData = @json($activeDeals ?? []);
    const contactsData = @json($recentContacts ?? []);

    // Process deals data for chart with last 6 months
    const dealsByMonth = {};
    const currentDate = new Date();

    // Initialize last 6 months
    for (let i = 5; i >= 0; i--) {
        const date = new Date(currentDate.getFullYear(), currentDate.getMonth() - i, 1);
        const monthKey = date.toLocaleString('cs-CZ', { month: 'short' });
        dealsByMonth[monthKey] = 0;
    }

    // Aggregate deals data
    dealsData.forEach(deal => {
        const date = new Date(deal.created_at || deal.createdAt);
        const month = date.toLocaleString('cs-CZ', { month: 'short' });
        if (dealsByMonth.hasOwnProperty(month)) {
            dealsByMonth[month] += (deal.value || 0) * ((deal.probability || 100) / 100);
        }
    });

    // Process contacts data for chart
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
                    label: 'Hodnota obchodů',
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
            'email': 'E-mail',
            'phone': 'Telefon',
            'meeting': 'Schůzka',
            'other': 'Ostatní'
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
