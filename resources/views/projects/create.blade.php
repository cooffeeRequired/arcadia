@extends('layouts.app')

@section('title', 'Nový projekt')

@section('content')
<div class="px-4 sm:px-6 lg:px-8">
    <div class="mb-6">
        <a href="/projects" class="text-blue-600 hover:text-blue-900 text-sm font-medium">
            ← Zpět na projekty
        </a>
    </div>

    <div class="sm:flex sm:items-center">
        <div class="sm:flex-auto">
            <h1 class="text-xl font-semibold text-gray-900">Nový projekt</h1>
            <p class="mt-2 text-sm text-gray-700">Vytvořte nový projekt pro zákazníka</p>
        </div>
    </div>

    <div class="mt-8">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
            <form id="projectForm" class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Základní informace -->
                    <div class="md:col-span-2">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Základní informace</h3>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Název projektu <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="name" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                               placeholder="Zadejte název projektu">
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Popis</label>
                        <textarea name="description" rows="3"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                  placeholder="Popis projektu..."></textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Zákazník <span class="text-red-500">*</span>
                        </label>
                        <select name="customer_id" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Vyberte zákazníka</option>
                            @foreach($customers as $customer)
                            <option value="{{ $customer->getId() }}">{{ $customer->getName() }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Manažer projektu <span class="text-red-500">*</span>
                        </label>
                        <select name="manager_id" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Vyberte manažera</option>
                            @foreach($users as $user)
                            <option value="{{ $user->getId() }}">{{ $user->getName() }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Termíny -->
                    <div class="md:col-span-2">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Termíny</h3>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Datum začátku <span class="text-red-500">*</span>
                        </label>
                        <input type="date" name="start_date" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Datum ukončení</label>
                        <input type="date" name="end_date"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>

                    <!-- Nastavení -->
                    <div class="md:col-span-2">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Nastavení</h3>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Stav</label>
                        <select name="status"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="active">Aktivní</option>
                            <option value="on_hold">Pozastavené</option>
                            <option value="completed">Dokončené</option>
                            <option value="cancelled">Zrušené</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Priorita</label>
                        <select name="priority"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="low">Nízká</option>
                            <option value="medium" selected>Střední</option>
                            <option value="high">Vysoká</option>
                        </select>
                    </div>

                    <!-- Rozpočet a čas -->
                    <div class="md:col-span-2">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Rozpočet a čas</h3>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Rozpočet (Kč)</label>
                        <input type="number" name="budget" step="0.01" min="0"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                               placeholder="0">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Odhadované hodiny</label>
                        <input type="number" name="estimated_hours" min="0"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                               placeholder="0">
                    </div>
                </div>

                <div class="flex justify-end gap-4 mt-8 pt-6 border-t border-gray-200">
                    <a href="/projects"
                       class="px-6 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 transition-colors">
                        Zrušit
                    </a>
                    <button type="submit"
                            class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-md font-medium transition-colors">
                        <i class="fas fa-save mr-2"></i>Vytvořit projekt
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.getElementById('projectForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const formData = new FormData(this);
    const data = Object.fromEntries(formData.entries());

    fetch('/projects', {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.href = data.data.redirect;
        } else {
            alert('Chyba při vytváření projektu: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Došlo k chybě při vytváření projektu.');
    });
});
</script>
@endsection
