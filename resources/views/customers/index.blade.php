@extends('layouts.app')

@section('title', 'Zákazníci - Arcadia CRM')

@section('content')
<div class="bg-white shadow rounded-lg">
    <div class="px-4 py-5 sm:p-6">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-gray-900">Zákazníci</h2>
            <a href="/customers/create" class="btn-primary">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                Nový zákazník
            </a>
        </div>

        <!-- Filtry -->
        <div class="mb-6">
            <div class="flex flex-col sm:flex-row gap-4">
                <div class="flex-1">
                    <input type="text" placeholder="Hledat zákazníky..." class="form-input">
                </div>
                <select class="form-input w-full sm:w-48">
                    <option>Všechny kategorie</option>
                    <option>Firmy</option>
                    <option>Osoby</option>
                </select>
            </div>
        </div>

        <!-- Tabulka zákazníků -->
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Název
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Email
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Telefon
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Kategorie
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Akce
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @if(isset($customers) && count($customers) > 0)
                        @foreach($customers as $customer)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10">
                                        <div class="h-10 w-10 rounded-full bg-primary-100 flex items-center justify-center">
                                            <span class="text-primary-600 font-medium">{{ substr($customer->getName(), 0, 1) }}</span>
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900">{{ $customer->getName() }}</div>
                                        <div class="text-sm text-gray-500">{{ $customer->getCompany() ?? 'Osoba' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $customer->getEmail() }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $customer->getPhone() }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $customer->getCategory() === 'company' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800' }}">
                                    {{ $customer->getCategory() === 'company' ? 'Firma' : 'Osoba' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex space-x-2">
                                    <a href="/customers/{{ $customer->getId() }}" class="text-primary-600 hover:text-primary-900">Zobrazit</a>
                                    <a href="/customers/{{ $customer->getId() }}/edit" class="text-gray-600 hover:text-gray-900">Upravit</a>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                                Žádní zákazníci nebyli nalezeni
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>

        <!-- Paginace -->
        @if(isset($pagination))
        <div class="mt-6 flex items-center justify-between 2">
            <div class="text-sm text-gray-700">
                Zobrazeno {{ $pagination->from }} - {{ $pagination->to }} z {{ $pagination->total }} zákazníků
            </div>
            <div class="flex space-x-2">
                @if($pagination->currentPage > 1)
                    <a href="?page={{ $pagination->currentPage - 1 }}" class="btn-secondary">Předchozí</a>
                @endif
                @if($pagination->currentPage < $pagination->lastPage)
                    <a href="?page={{ $pagination->currentPage + 1 }}" class="btn-secondary">Další</a>
                @endif
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
