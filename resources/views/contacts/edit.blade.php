@extends('layouts.app')

@section('content')
<div class="px-4 sm:px-6 lg:px-8">
    <div class="mb-6">
        <a href="/contacts/{{ $contact->getId() }}" class="text-blue-600 hover:text-blue-900 text-sm font-medium">
            ← Zpět na detail kontaktu
        </a>
    </div>

    <div class="bg-white shadow sm:rounded-lg">
        <div class="px-4 py-5 sm:p-6">
            <h3 class="text-lg leading-6 font-medium text-gray-900">Upravit kontakt</h3>
            <p class="mt-1 max-w-2xl text-sm text-gray-500">Upravte informace o kontaktu.</p>
            
            <form method="POST" action="/contacts/{{ $contact->getId() }}" class="mt-6 space-y-6">
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                    <div>
                        <label for="customer_id" class="block text-sm font-medium text-gray-700">Zákazník *</label>
                        <select id="customer_id" name="customer_id" required class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                            <option value="">Vyberte zákazníka</option>
                            @if(isset($customers) && count($customers) > 0)
                                @foreach($customers as $customer)
                                    <option value="{{ $customer->getId() }}" {{ $contact->getCustomer()->getId() == $customer->getId() ? 'selected' : '' }}>
                                        {{ $customer->getName() }}
                                    </option>
                                @endforeach
                            @endif
                        </select>
                    </div>

                    <div>
                        <label for="type" class="block text-sm font-medium text-gray-700">Typ kontaktu *</label>
                        <select id="type" name="type" required class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                            <option value="">Vyberte typ</option>
                            <option value="phone" {{ $contact->getType() === 'phone' ? 'selected' : '' }}>Telefon</option>
                            <option value="email" {{ $contact->getType() === 'email' ? 'selected' : '' }}>Email</option>
                            <option value="meeting" {{ $contact->getType() === 'meeting' ? 'selected' : '' }}>Schůzka</option>
                            <option value="presentation" {{ $contact->getType() === 'presentation' ? 'selected' : '' }}>Prezentace</option>
                            <option value="other" {{ $contact->getType() === 'other' ? 'selected' : '' }}>Jiné</option>
                        </select>
                    </div>

                    <div class="sm:col-span-2">
                        <label for="subject" class="block text-sm font-medium text-gray-700">Předmět *</label>
                        <input type="text" name="subject" id="subject" value="{{ $contact->getSubject() }}" required class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                    </div>

                    <div class="sm:col-span-2">
                        <label for="description" class="block text-sm font-medium text-gray-700">Popis</label>
                        <textarea id="description" name="description" rows="3" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">{{ $contact->getDescription() }}</textarea>
                    </div>

                    <div>
                        <label for="contact_date" class="block text-sm font-medium text-gray-700">Datum kontaktu</label>
                        <input type="datetime-local" name="contact_date" id="contact_date" value="{{ $contact->getContactDate()->format('Y-m-d\TH:i') }}" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                    </div>

                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                        <select id="status" name="status" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                            <option value="completed" {{ $contact->getStatus() === 'completed' ? 'selected' : '' }}>Dokončeno</option>
                            <option value="pending" {{ $contact->getStatus() === 'pending' ? 'selected' : '' }}>Probíhá</option>
                        </select>
                    </div>
                </div>

                <div class="flex justify-end space-x-3">
                    <a href="/contacts/{{ $contact->getId() }}" class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
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