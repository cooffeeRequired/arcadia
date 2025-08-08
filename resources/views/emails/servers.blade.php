@extends('layouts.app')

@section('title', 'E-mailové servery - Arcadia CRM')

@section('content')
<div class="bg-white shadow rounded-lg">
    <div class="px-4 py-5 sm:p-6">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-gray-900">E-mailové servery</h2>
            <button onclick="openServerModal()" class="btn-primary">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                Nový server
            </button>
        </div>

        <!-- Seznam serverů -->
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Název
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Host
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Port
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Šifrování
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Stav
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Akce
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @if(isset($servers) && count($servers) > 0)
                        @foreach($servers as $server)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10">
                                        <div class="h-10 w-10 rounded-full bg-primary-100 flex items-center justify-center">
                                            <svg class="h-6 w-6 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01"></path>
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900">{{ $server->getName() }}</div>
                                        <div class="text-sm text-gray-500">{{ $server->getFromEmail() }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $server->getHost() }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $server->getPort() }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ strtoupper($server->getEncryption()) }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center space-x-2">
                                    @if($server->getIsActive())
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                            Aktivní
                                        </span>
                                    @else
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                                            Neaktivní
                                        </span>
                                    @endif
                                    @if($server->getIsDefault())
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                            Výchozí
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex space-x-2">
                                    <button onclick="editServer({{ $server->getId() }})"
                                            class="text-gray-600 hover:text-gray-900">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                        </svg>
                                    </button>
                                    <button onclick="testServer({{ $server->getId() }})"
                                            class="text-blue-600 hover:text-blue-900">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </button>
                                    <button onclick="deleteServer({{ $server->getId() }})"
                                            class="text-red-600 hover:text-red-900">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                                Žádné servery nebyly nalezeny
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal pro vytvoření/úpravu serveru -->
<div id="serverModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium text-gray-900" id="serverModalTitle">Nový server</h3>
                <button onclick="closeServerModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <form id="serverForm" action="/emails/servers" method="POST">
                <input type="hidden" name="server_id" id="server_id">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="server_name" class="block text-sm font-medium text-gray-700 mb-2">
                            Název serveru *
                        </label>
                        <input type="text" name="name" id="server_name" required
                               class="form-input w-full" placeholder="Např. Gmail SMTP">
                    </div>

                    <div>
                        <label for="server_host" class="block text-sm font-medium text-gray-700 mb-2">
                            Host *
                        </label>
                        <input type="text" name="host" id="server_host" required
                               class="form-input w-full" placeholder="smtp.gmail.com">
                    </div>

                    <div>
                        <label for="server_port" class="block text-sm font-medium text-gray-700 mb-2">
                            Port *
                        </label>
                        <input type="number" name="port" id="server_port" required
                               class="form-input w-full" placeholder="587">
                    </div>

                    <div>
                        <label for="server_encryption" class="block text-sm font-medium text-gray-700 mb-2">
                            Šifrování *
                        </label>
                        <select name="encryption" id="server_encryption" required class="form-input w-full">
                            <option value="">Vyberte šifrování...</option>
                            <option value="ssl">SSL</option>
                            <option value="tls">TLS</option>
                            <option value="none">Žádné</option>
                        </select>
                    </div>

                    <div>
                        <label for="server_username" class="block text-sm font-medium text-gray-700 mb-2">
                            Uživatelské jméno *
                        </label>
                        <input type="text" name="username" id="server_username" required
                               class="form-input w-full" placeholder="vas@email.cz">
                    </div>

                    <div>
                        <label for="server_password" class="block text-sm font-medium text-gray-700 mb-2">
                            Heslo *
                        </label>
                        <input type="password" name="password" id="server_password" required
                               class="form-input w-full" placeholder="Vaše heslo">
                    </div>

                    <div>
                        <label for="server_from_email" class="block text-sm font-medium text-gray-700 mb-2">
                            E-mail odesílatele *
                        </label>
                        <input type="email" name="from_email" id="server_from_email" required
                               class="form-input w-full" placeholder="vas@email.cz">
                    </div>

                    <div>
                        <label for="server_from_name" class="block text-sm font-medium text-gray-700 mb-2">
                            Jméno odesílatele *
                        </label>
                        <input type="text" name="from_name" id="server_from_name" required
                               class="form-input w-full" placeholder="Vaše jméno">
                    </div>
                </div>

                <div class="mt-4 space-y-3">
                    <div class="flex items-center">
                        <input type="checkbox" name="is_active" id="is_active" class="form-checkbox" checked>
                        <label for="is_active" class="ml-2 text-sm text-gray-700">
                            Aktivní server
                        </label>
                    </div>

                    <div class="flex items-center">
                        <input type="checkbox" name="is_default" id="is_default" class="form-checkbox">
                        <label for="is_default" class="ml-2 text-sm text-gray-700">
                            Nastavit jako výchozí server
                        </label>
                    </div>
                </div>

                <div class="flex justify-end space-x-3 mt-6">
                    <button type="button" onclick="closeServerModal()" class="btn-secondary">
                        Zrušit
                    </button>
                    <button type="submit" class="btn-primary">
                        Uložit server
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openServerModal() {
    document.getElementById('serverModal').classList.remove('hidden');
    document.getElementById('serverModalTitle').textContent = 'Nový server';
    document.getElementById('serverForm').reset();
}

function closeServerModal() {
    document.getElementById('serverModal').classList.add('hidden');
}

function editServer(id) {
    // Zde by byl AJAX request pro načtení serveru
    document.getElementById('serverModal').classList.remove('hidden');
    document.getElementById('serverModalTitle').textContent = 'Upravit server';
    document.getElementById('server_id').value = id;
}

function testServer(id) {
    // Zde by byl AJAX request pro testování serveru
    alert('Testování serveru...');
}

function deleteServer(id) {
    if (confirm('Opravdu chcete smazat tento server?')) {
        // Zde by byl AJAX request pro smazání serveru
        window.location.href = '/emails/servers/' + id + '/delete';
    }
}
</script>
@endsection
