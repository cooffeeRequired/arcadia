@extends('layouts.app')

@section('content')
<div class="px-4 sm:px-6 lg:px-8">
    <div class="mb-6">
        <a href="/invoices" class="text-blue-600 hover:text-blue-900 text-sm font-medium">
            ← Zpět na faktury
        </a>
    </div>

    <div class="bg-white shadow sm:rounded-lg">
        <div class="px-4 py-5 sm:p-6">
            <h3 class="text-lg leading-6 font-medium text-gray-900">Nová faktura</h3>
            <p class="mt-1 max-w-2xl text-sm text-gray-500">Vytvořte novou fakturu pro zákazníka.</p>
            
            <form method="POST" action="/invoices" class="mt-6 space-y-6">
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                    <div>
                        <label for="customer_id" class="block text-sm font-medium text-gray-700">Zákazník *</label>
                        <select id="customer_id" name="customer_id" required class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                            <option value="">Vyberte zákazníka</option>
                            @if(isset($customers) && count($customers) > 0)
                                @foreach($customers as $customer)
                                    <option value="{{ $customer->getId() }}">{{ $customer->getName() }}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>

                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                        <select id="status" name="status" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                            <option value="draft">Koncept</option>
                            <option value="sent">Odeslána</option>
                            <option value="paid">Zaplacena</option>
                        </select>
                    </div>

                    <div>
                        <label for="issue_date" class="block text-sm font-medium text-gray-700">Datum vystavení</label>
                        <input type="date" name="issue_date" id="issue_date" value="{{ date('Y-m-d') }}" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                    </div>

                    <div>
                        <label for="due_date" class="block text-sm font-medium text-gray-700">Datum splatnosti</label>
                        <input type="date" name="due_date" id="due_date" value="{{ date('Y-m-d', strtotime('+30 days')) }}" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                    </div>

                    <div>
                        <label for="tax_rate" class="block text-sm font-medium text-gray-700">DPH (%)</label>
                        <input type="number" name="tax_rate" id="tax_rate" value="21" min="0" max="100" step="0.1" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                    </div>

                    <div>
                        <label for="currency" class="block text-sm font-medium text-gray-700">Měna</label>
                        <select id="currency" name="currency" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                            <option value="CZK">CZK</option>
                            <option value="EUR">EUR</option>
                            <option value="USD">USD</option>
                        </select>
                    </div>
                </div>

                <!-- Položky faktury -->
                <div class="mt-8">
                    <h4 class="text-lg font-medium text-gray-900 mb-4">Položky faktury</h4>
                    <div id="invoice-items">
                        <div class="invoice-item border border-gray-200 rounded-lg p-4 mb-4">
                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-5">
                                <div class="sm:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700">Popis *</label>
                                    <input type="text" name="items[0][description]" required class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Množství</label>
                                    <input type="number" name="items[0][quantity]" value="1" min="0" step="0.01" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Jednotka</label>
                                    <input type="text" name="items[0][unit]" value="ks" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Cena za jednotku</label>
                                    <input type="number" name="items[0][unit_price]" value="0" min="0" step="0.01" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                </div>
                            </div>
                        </div>
                    </div>
                    <button type="button" id="add-item" class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        <svg class="-ml-0.5 mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                        Přidat položku
                    </button>
                </div>

                <div>
                    <label for="notes" class="block text-sm font-medium text-gray-700">Poznámky</label>
                    <textarea id="notes" name="notes" rows="3" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"></textarea>
                </div>

                <div class="flex justify-end space-x-3">
                    <a href="/invoices" class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Zrušit
                    </a>
                    <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Vytvořit fakturu
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
let itemIndex = 1;

document.getElementById('add-item').addEventListener('click', function() {
    const container = document.getElementById('invoice-items');
    const newItem = document.createElement('div');
    newItem.className = 'invoice-item border border-gray-200 rounded-lg p-4 mb-4';
    
    newItem.innerHTML = `
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-5">
            <div class="sm:col-span-2">
                <label class="block text-sm font-medium text-gray-700">Popis *</label>
                <input type="text" name="items[${itemIndex}][description]" required class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Množství</label>
                <input type="number" name="items[${itemIndex}][quantity]" value="1" min="0" step="0.01" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Jednotka</label>
                <input type="text" name="items[${itemIndex}][unit]" value="ks" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Cena za jednotku</label>
                <input type="number" name="items[${itemIndex}][unit_price]" value="0" min="0" step="0.01" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
            </div>
        </div>
        <div class="mt-2">
            <button type="button" class="remove-item inline-flex items-center px-2 py-1 border border-red-300 shadow-sm text-xs font-medium rounded text-red-700 bg-white hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                <svg class="-ml-0.5 mr-1 h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
                Odstranit
            </button>
        </div>
    `;
    
    container.appendChild(newItem);
    itemIndex++;
    
    // Přidání event listeneru pro odstranění
    newItem.querySelector('.remove-item').addEventListener('click', function() {
        container.removeChild(newItem);
    });
});
</script>
@endpush
@endsection 