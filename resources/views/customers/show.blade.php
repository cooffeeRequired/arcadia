@extends('layouts.app')

@section('title', $customer->getName() . ' - Arcadia CRM')

@section('content')
<div class="bg-white shadow rounded-lg">
    <div class="px-4 py-5 sm:p-6">
        <!-- Hlavička -->
        <div class="flex justify-between items-start mb-6">
            <div class="flex items-center">
                <div class="flex-shrink-0 h-16 w-16">
                    <div class="h-16 w-16 rounded-full bg-primary-100 flex items-center justify-center">
                        <span class="text-primary-600 text-2xl font-bold">{{ substr($customer->getName(), 0, 1) }}</span>
                    </div>
                </div>
                <div class="ml-4">
                    <h2 class="text-2xl font-bold text-gray-900">{{ $customer->getName() }}</h2>
                    @if($customer->getCompany())
                        <p class="text-lg text-gray-600">{{ $customer->getCompany() }}</p>
                    @endif
                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $customer->getCategory() === 'company' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800' }}">
                        {{ $customer->getCategory() === 'company' ? 'Firma' : 'Osoba' }}
                    </span>
                </div>
            </div>
            <div class="flex space-x-3">
                <a href="/customers/{{ $customer->getId() }}/edit" class="btn-secondary">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                    </svg>
                    Upravit
                </a>
                <button class="btn-primary">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                    </svg>
                    Kontaktovat
                </button>
            </div>
        </div>

        <!-- Informace -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Základní informace -->
            <div class="card">
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Základní informace</h3>
                    <dl class="space-y-4">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Email</dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                <a href="mailto:{{ $customer->getEmail() }}" class="text-primary-600 hover:text-primary-900">{{ $customer->getEmail() }}</a>
                            </dd>
                        </div>
                        @if($customer->getPhone())
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Telefon</dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                <a href="tel:{{ $customer->getPhone() }}" class="text-primary-600 hover:text-primary-900">{{ $customer->getPhone() }}</a>
                            </dd>
                        </div>
                        @endif
                        @if($customer->getAddress())
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Adresa</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $customer->getAddress() }}</dd>
                        </div>
                        @endif
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Vytvořeno</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $customer->getCreatedAt()->format('d.m.Y H:i') }}</dd>
                        </div>
                    </dl>
                </div>
            </div>

            <!-- Aktivity -->
            <div class="card">
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Poslední aktivity</h3>
                    <div class="space-y-4">
                        <div class="flex items-center space-x-3">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                                    <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900">Zákazník vytvořen</p>
                                <p class="text-sm text-gray-500">{{ $customer->getCreatedAt()->format('d.m.Y H:i') }}</p>
                            </div>
                        </div>
                        <div class="flex items-center space-x-3">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900">Profil zobrazen</p>
                                <p class="text-sm text-gray-500">Dnes 14:30</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Akce -->
        <div class="mt-6 flex justify-end space-x-3">
            <a href="/customers" class="btn-secondary">
                Zpět na seznam
            </a>
            <button class="btn-primary">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                Nový obchod
            </button>
        </div>
    </div>
</div>
@endsection 