@extends('layouts.app')

@section('title', 'Šablony modulu - ' . $module->getDisplayName())

@section('content')
<div class="px-4 sm:px-6 lg:px-8">
    <div class="bg-white shadow rounded-lg">
        <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
            <div class="flex items-center">
                <a href="/settings/modules" class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 mr-4">
                    <i class="fas fa-arrow-left mr-2"></i>Zpět
                </a>
                <h3 class="text-lg leading-6 font-medium text-gray-900">Šablony pro modul: {{ $module->getDisplayName() }}</h3>
            </div>
        </div>
        <div class="px-4 py-5 sm:p-6">
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                <div class="bg-white overflow-hidden shadow rounded-lg border border-gray-200">
                    <div class="px-4 py-5 sm:p-6">
                        <div class="flex items-center mb-4">
                            <div class="flex-shrink-0">
                                <i class="fas fa-code text-blue-500 text-lg"></i>
                            </div>
                            <div class="ml-3">
                                <h4 class="text-lg font-medium text-gray-900">Controller</h4>
                            </div>
                        </div>
                        <p class="text-sm text-gray-600 mb-4">
                            Základní šablona pro controller s CRUD operacemi
                        </p>
                        <button onclick="loadTemplate('controller')" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <i class="fas fa-eye mr-2"></i>Zobrazit šablonu
                        </button>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow rounded-lg border border-gray-200">
                    <div class="px-4 py-5 sm:p-6">
                        <div class="flex items-center mb-4">
                            <div class="flex-shrink-0">
                                <i class="fas fa-database text-green-500 text-lg"></i>
                            </div>
                            <div class="ml-3">
                                <h4 class="text-lg font-medium text-gray-900">Entity</h4>
                            </div>
                        </div>
                        <p class="text-sm text-gray-600 mb-4">
                            Šablona pro Doctrine entitu s základními vlastnostmi
                        </p>
                        <button onclick="loadTemplate('entity')" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                            <i class="fas fa-eye mr-2"></i>Zobrazit šablonu
                        </button>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow rounded-lg border border-gray-200">
                    <div class="px-4 py-5 sm:p-6">
                        <div class="flex items-center mb-4">
                            <div class="flex-shrink-0">
                                <i class="fas fa-code-branch text-yellow-500 text-lg"></i>
                            </div>
                            <div class="ml-3">
                                <h4 class="text-lg font-medium text-gray-900">Migration</h4>
                            </div>
                        </div>
                        <p class="text-sm text-gray-600 mb-4">
                            Šablona pro instalaci modulu s vytvořením tabulky
                        </p>
                        <button onclick="loadTemplate('migration')" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-yellow-600 hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500">
                            <i class="fas fa-eye mr-2"></i>Zobrazit šablonu
                        </button>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow rounded-lg border border-gray-200">
                    <div class="px-4 py-5 sm:p-6">
                        <div class="flex items-center mb-4">
                            <div class="flex-shrink-0">
                                <i class="fas fa-file-code text-purple-500 text-lg"></i>
                            </div>
                            <div class="ml-3">
                                <h4 class="text-lg font-medium text-gray-900">View</h4>
                            </div>
                        </div>
                        <p class="text-sm text-gray-600 mb-4">
                            Základní Blade šablona pro zobrazení seznamu
                        </p>
                        <button onclick="loadTemplate('view')" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-purple-600 hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500">
                            <i class="fas fa-eye mr-2"></i>Zobrazit šablonu
                        </button>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow rounded-lg border border-gray-200">
                    <div class="px-4 py-5 sm:p-6">
                        <div class="flex items-center mb-4">
                            <div class="flex-shrink-0">
                                <i class="fas fa-language text-indigo-500 text-lg"></i>
                            </div>
                            <div class="ml-3">
                                <h4 class="text-lg font-medium text-gray-900">Překlady</h4>
                            </div>
                        </div>
                        <p class="text-sm text-gray-600 mb-4">
                            Šablona pro jazykové mutace modulu
                        </p>
                        <button onclick="loadTemplate('translation')" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            <i class="fas fa-eye mr-2"></i>Zobrazit šablonu
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function loadTemplate(type) {
    const moduleName = '{{ $module->getName() }}';

    fetch(`/settings/templates/${moduleName}/${type}`, {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
        },
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showTemplateModal(type, data.template);
        } else {
            showToast('Chyba při načítání šablony: ' + data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error loading template:', error);
        showToast('Došlo k chybě při načítání šablony', 'error');
    });
}

function showTemplateModal(type, template) {
    const modal = document.createElement('div');
    modal.className = 'fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50';
    modal.innerHTML = `
        <div class="relative top-20 mx-auto p-5 border w-11/12 max-w-4xl shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Šablona - ${type.charAt(0).toUpperCase() + type.slice(1)}</h3>
                    <button onclick="closeTemplateModal()" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                <div class="bg-gray-900 text-green-400 p-4 rounded-lg font-mono text-sm h-96 overflow-y-auto">
                    <pre><code>${escapeHtml(template)}</code></pre>
                </div>
                <div class="flex justify-end space-x-3 mt-4">
                    <button onclick="closeTemplateModal()" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-500">
                        Zavřít
                    </button>
                    <button onclick="copyTemplate()" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <i class="fas fa-copy mr-2"></i>Kopírovat
                    </button>
                </div>
            </div>
        </div>
    `;
    document.body.appendChild(modal);
}

function closeTemplateModal() {
    const modal = document.querySelector('.fixed.inset-0.bg-gray-600');
    if (modal) {
        modal.remove();
    }
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function copyTemplate() {
    const codeElement = document.querySelector('.modal pre code, .fixed.inset-0 pre code');
    if (codeElement) {
        const text = codeElement.textContent;
        navigator.clipboard.writeText(text).then(function() {
            showToast('Šablona byla zkopírována do schránky', 'success');
        }).catch(function() {
            showToast('Chyba při kopírování', 'error');
        });
    }
}

function showToast(message, type) {
    const toast = document.createElement('div');
    toast.className = `fixed top-4 right-4 z-50 px-6 py-3 rounded-md text-white ${
        type === 'error' ? 'bg-red-600' : 'bg-green-600'
    }`;
    toast.textContent = message;
    document.body.appendChild(toast);

    setTimeout(() => {
        toast.remove();
    }, 3000);
}
</script>
@endpush
@endsection
