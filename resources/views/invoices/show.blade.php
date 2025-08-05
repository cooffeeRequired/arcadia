@extends('layouts.app')

@section('content')
<div class="px-4 sm:px-6 lg:px-8">
    <div class="mb-6">
        <a href="/invoices" class="text-blue-600 hover:text-blue-900 text-sm font-medium">
            ← Zpět na faktury
        </a>
    </div>

    <div class="bg-white shadow overflow-hidden sm:rounded-lg">
        <div class="px-4 py-5 sm:px-6">
            <div class="flex justify-between items-center">
                <div>
                    <h3 class="text-lg leading-6 font-medium text-gray-900">Faktura {{ $invoice->getInvoiceNumber() }}</h3>
                    <p class="mt-1 max-w-2xl text-sm text-gray-500">{{ $invoice->getCustomer()->getName() }}</p>
                </div>
                <div class="flex space-x-3">
                    <a href="/invoices/{{ $invoice->getId() }}/pdf" target="_blank" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                        <svg class="-ml-1 mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Stáhnout PDF
                    </a>
                    <a href="/invoices/{{ $invoice->getId() }}/edit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Upravit
                    </a>
                </div>
            </div>
        </div>
        <div class="border-t border-gray-200">
            <dl>
                <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-sm font-medium text-gray-500">Zákazník</dt>
                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                        <a href="/customers/{{ $invoice->getCustomer()->getId() }}" class="text-blue-600 hover:text-blue-900">
                            {{ $invoice->getCustomer()->getName() }}
                        </a>
                    </dd>
                </div>
                <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-sm font-medium text-gray-500">Datum vystavení</dt>
                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ $invoice->getIssueDate()->format('d.m.Y') }}</dd>
                </div>
                <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-sm font-medium text-gray-500">Datum splatnosti</dt>
                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ $invoice->getDueDate()->format('d.m.Y') }}</dd>
                </div>
                <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-sm font-medium text-gray-500">Status</dt>
                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                        @php
                            $statusColors = [
                                'draft' => 'bg-gray-100 text-gray-800',
                                'sent' => 'bg-blue-100 text-blue-800',
                                'paid' => 'bg-green-100 text-green-800',
                                'overdue' => 'bg-red-100 text-red-800',
                                'cancelled' => 'bg-yellow-100 text-yellow-800'
                            ];
                            $statusLabels = [
                                'draft' => 'Koncept',
                                'sent' => 'Odeslána',
                                'paid' => 'Zaplacena',
                                'overdue' => 'Po splatnosti',
                                'cancelled' => 'Zrušena'
                            ];
                        @endphp
                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $statusColors[$invoice->getStatus()] ?? 'bg-gray-100 text-gray-800' }}">
                            {{ $statusLabels[$invoice->getStatus()] ?? $invoice->getStatus() }}
                        </span>
                    </dd>
                </div>
                <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-sm font-medium text-gray-500">DPH</dt>
                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ $invoice->getTaxRate() }}%</dd>
                </div>
                <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-sm font-medium text-gray-500">Měna</dt>
                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ $invoice->getCurrency() }}</dd>
                </div>
                @if($invoice->getNotes())
                <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-sm font-medium text-gray-500">Poznámky</dt>
                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ $invoice->getNotes() }}</dd>
                </div>
                @endif
            </dl>
        </div>
    </div>

    <!-- Položky faktury -->
    <div class="mt-8">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Položky faktury</h3>
        <div class="bg-white shadow overflow-hidden sm:rounded-lg">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Popis</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Množství</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jednotka</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cena za jednotku</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Celkem</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($invoice->getItems() as $item)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $item->getDescription() }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $item->getQuantity() }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $item->getUnit() }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ number_format($item->getUnitPrice(), 2, ',', ' ') }} {{ $invoice->getCurrency() }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ number_format($item->getTotal(), 2, ',', ' ') }} {{ $invoice->getCurrency() }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Součty -->
    <div class="mt-8">
        <div class="bg-white shadow sm:rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <div class="flex justify-end">
                    <div class="w-64">
                        <dl class="space-y-2">
                            <div class="flex justify-between">
                                <dt class="text-sm font-medium text-gray-500">Mezisoučet:</dt>
                                <dd class="text-sm text-gray-900">{{ number_format($invoice->getSubtotal(), 2, ',', ' ') }} {{ $invoice->getCurrency() }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-sm font-medium text-gray-500">DPH ({{ $invoice->getTaxRate() }}%):</dt>
                                <dd class="text-sm text-gray-900">{{ number_format($invoice->getTaxAmount(), 2, ',', ' ') }} {{ $invoice->getCurrency() }}</dd>
                            </div>
                            <div class="flex justify-between border-t border-gray-200 pt-2">
                                <dt class="text-base font-medium text-gray-900">Celkem k úhradě:</dt>
                                <dd class="text-base font-medium text-gray-900">{{ number_format($invoice->getTotal(), 2, ',', ' ') }} {{ $invoice->getCurrency() }}</dd>
                            </div>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Akce -->
    <div class="mt-6 flex space-x-3">
        <form method="POST" action="/invoices/{{ $invoice->getId() }}/delete" class="inline" onsubmit="return confirm('Opravdu chcete smazat tuto fakturu?')">
            <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                Smazat fakturu
            </button>
        </form>
    </div>
</div>
@endsection 