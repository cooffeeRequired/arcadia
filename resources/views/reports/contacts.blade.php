@extends('layouts.app')

@section('content')
<div class="px-4 sm:px-6 lg:px-8">
    <div class="mb-6">
        <a href="/reports" class="text-blue-600 hover:text-blue-900 text-sm font-medium">
            ← Zpět na reporty
        </a>
    </div>

    <div class="sm:flex sm:items-center">
        <div class="sm:flex-auto">
            <h1 class="text-xl font-semibold text-gray-900">Report kontaktů</h1>
            <p class="mt-2 text-sm text-gray-700">Přehled kontaktů a komunikace se zákazníky.</p>
        </div>
    </div>

    <!-- Statistiky podle typu -->
    <div class="mt-8">
        <h2 class="text-lg font-medium text-gray-900 mb-4">Statistiky podle typu kontaktu</h2>
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
            @if(isset($typeStats) && count($typeStats) > 0)
                @foreach($typeStats as $type => $count)
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-6 w-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">{{ ucfirst($type) }}</dt>
                                    <dd class="text-lg font-medium text-gray-900">{{ $count }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            @else
                <div class="col-span-full text-center text-sm text-gray-500">
                    Žádné kontakty nebyly nalezeny.
                </div>
            @endif
        </div>
    </div>
    
    <!-- Seznam kontaktů -->
    <div class="mt-8">
        <h2 class="text-lg font-medium text-gray-900 mb-4">Seznam všech kontaktů</h2>
        <div class="flex flex-col">
            <div class="-my-2 -mx-4 overflow-x-auto sm:-mx-6 lg:-mx-8">
                <div class="inline-block min-w-full py-2 align-middle md:px-6 lg:px-8">
                    <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 md:rounded-lg">
                        <table class="min-w-full divide-y divide-gray-300">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Zákazník</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Typ</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Předmět</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Datum</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th scope="col" class="relative px-6 py-3">
                                        <span class="sr-only">Akce</span>
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @if(isset($contacts) && count($contacts) > 0)
                                    @foreach($contacts as $contact)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            <a href="/customers/{{ $contact->getCustomer()->getId() }}" class="text-blue-600 hover:text-blue-900">
                                                {{ $contact->getCustomer()->getName() }}
                                            </a>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $contact->getType() }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $contact->getSubject() }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $contact->getContactDate()->format('d.m.Y H:i') }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $contact->getStatus() === 'completed' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                                {{ $contact->getStatus() === 'completed' ? 'Dokončeno' : 'Probíhá' }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <a href="/contacts/{{ $contact->getId() }}" class="text-blue-600 hover:text-blue-900">Zobrazit</a>
                                        </td>
                                    </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">
                                            Žádné kontakty nebyly nalezeny.
                                        </td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 