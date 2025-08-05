@extends('layouts.app')

@section('content')
<div class="px-4 sm:px-6 lg:px-8">
    <div class="mb-6">
        <a href="/deals/{{ $deal->getId() }}" class="text-blue-600 hover:text-blue-900 text-sm font-medium">
            ← Zpět na detail obchodu
        </a>
    </div>

    <div class="bg-white shadow sm:rounded-lg">
        <div class="px-4 py-5 sm:p-6">
            <h3 class="text-lg leading-6 font-medium text-gray-900">Upravit obchod</h3>
            <p class="mt-1 max-w-2xl text-sm text-gray-500">Upravte informace o obchodu.</p>
            
            <form method="POST" action="/deals/{{ $deal->getId() }}" class="mt-6 space-y-6">
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                    <div>
                        <label for="customer_id" class="block text-sm font-medium text-gray-700">Zákazník *</label>
                        <select id="customer_id" name="customer_id" required class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                            <option value="">Vyberte zákazníka</option>
                            @if(isset($customers) && count($customers) > 0)
                                @foreach($customers as $customer)
                                    <option value="{{ $customer->getId() }}" {{ $deal->getCustomer()->getId() == $customer->getId() ? 'selected' : '' }}>
                                        {{ $customer->getName() }}
                                    </option>
                                @endforeach
                            @endif
                        </select>
                    </div>

                    <div>
                        <label for="stage" class="block text-sm font-medium text-gray-700">Fáze *</label>
                        <select id="stage" name="stage" required class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                            <option value="">Vyberte fázi</option>
                            <option value="prospecting" {{ $deal->getStage() === 'prospecting' ? 'selected' : '' }}>Prospekce</option>
                            <option value="qualification" {{ $deal->getStage() === 'qualification' ? 'selected' : '' }}>Kvalifikace</option>
                            <option value="proposal" {{ $deal->getStage() === 'proposal' ? 'selected' : '' }}>Nabídka</option>
                            <option value="negotiation" {{ $deal->getStage() === 'negotiation' ? 'selected' : '' }}>Vyjednávání</option>
                            <option value="closed_won" {{ $deal->getStage() === 'closed_won' ? 'selected' : '' }}>Uzavřeno - výhra</option>
                            <option value="closed_lost" {{ $deal->getStage() === 'closed_lost' ? 'selected' : '' }}>Uzavřeno - prohra</option>
                        </select>
                    </div>

                    <div class="sm:col-span-2">
                        <label for="title" class="block text-sm font-medium text-gray-700">Název obchodu *</label>
                        <input type="text" name="title" id="title" value="{{ $deal->getTitle() }}" required class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                    </div>

                    <div>
                        <label for="value" class="block text-sm font-medium text-gray-700">Hodnota (Kč)</label>
                        <input type="number" name="value" id="value" value="{{ $deal->getValue() }}" min="0" step="0.01" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                    </div>

                    <div>
                        <label for="probability" class="block text-sm font-medium text-gray-700">Pravděpodobnost (%)</label>
                        <input type="number" name="probability" id="probability" value="{{ $deal->getProbability() }}" min="0" max="100" step="0.1" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                    </div>

                    <div>
                        <label for="expected_close_date" class="block text-sm font-medium text-gray-700">Očekávané datum uzavření</label>
                        <input type="date" name="expected_close_date" id="expected_close_date" value="{{ $deal->getExpectedCloseDate() ? $deal->getExpectedCloseDate()->format('Y-m-d') : '' }}" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                    </div>

                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                        <select id="status" name="status" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                            <option value="active" {{ $deal->getStatus() === 'active' ? 'selected' : '' }}>Aktivní</option>
                            <option value="closed" {{ $deal->getStatus() === 'closed' ? 'selected' : '' }}>Uzavřený</option>
                        </select>
                    </div>

                    <div class="sm:col-span-2">
                        <label for="description" class="block text-sm font-medium text-gray-700">Popis</label>
                        <textarea id="description" name="description" rows="3" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">{{ $deal->getDescription() }}</textarea>
                    </div>
                </div>

                <div class="flex justify-end space-x-3">
                    <a href="/deals/{{ $deal->getId() }}" class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Zrušit
                    </a>
                    <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Uložit změny
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection 