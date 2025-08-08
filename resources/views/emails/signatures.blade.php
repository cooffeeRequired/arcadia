@extends('layouts.app')

@section('title', 'E-mailové podpisy - Arcadia CRM')

@section('styles')
<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
<style>
    .ql-editor {
        min-height: 150px;
    }
</style>
@endsection

@section('content')
<div class="bg-white shadow rounded-lg">
    <div class="px-4 py-5 sm:p-6">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-gray-900">E-mailové podpisy</h2>
            <button onclick="openSignatureModal()" class="btn-primary">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                Nový podpis
            </a>
        </div>

        <!-- Seznam podpisů -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @if(isset($signatures) && count($signatures) > 0)
                @foreach($signatures as $signature)
                <div class="bg-gray-50 rounded-lg p-6 border border-gray-200">
                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <h3 class="text-lg font-medium text-gray-900">{{ $signature->getName() }}</h3>
                            @if($signature->getIsDefault())
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                    Výchozí
                                </span>
                            @endif
                        </div>
                        <div class="flex space-x-2">
                            <button onclick="editSignature({{ $signature->getId() }})"
                                    class="text-gray-600 hover:text-gray-900">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                            </button>
                            <button onclick="deleteSignature({{ $signature->getId() }})"
                                    class="text-red-600 hover:text-red-900">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                    <div class="prose prose-sm max-w-none">
                        {!! $signature->getContent() !!}
                    </div>
                    <div class="mt-4 text-sm text-gray-500">
                        Vytvořeno: {{ $signature->getCreatedAt()->format('d.m.Y H:i') }}
                    </div>
                </div>
                @endforeach
            @else
                <div class="col-span-full text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">Žádné podpisy</h3>
                    <p class="mt-1 text-sm text-gray-500">Začněte vytvořením prvního e-mailového podpisu.</p>
                    <div class="mt-6">
                        <button onclick="openSignatureModal()" class="btn-primary">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            Vytvořit podpis
                        </button>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Modal pro vytvoření/úpravu podpisu -->
<div id="signatureModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium text-gray-900" id="modalTitle">Nový podpis</h3>
                <button onclick="closeSignatureModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <form id="signatureForm" action="/emails/signatures" method="POST">
                <input type="hidden" name="signature_id" id="signature_id">

                <div class="space-y-4">
                    <div>
                        <label for="signature_name" class="block text-sm font-medium text-gray-700 mb-2">
                            Název podpisu *
                        </label>
                        <input type="text" name="name" id="signature_name" required
                               class="form-input w-full" placeholder="Např. Standardní podpis">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Obsah podpisu *
                        </label>
                        <div id="signatureEditor" class="border border-gray-300 rounded-md"></div>
                        <input type="hidden" name="content" id="signature_content">
                    </div>

                    <div class="flex items-center">
                        <input type="checkbox" name="is_default" id="is_default" class="form-checkbox">
                        <label for="is_default" class="ml-2 text-sm text-gray-700">
                            Nastavit jako výchozí podpis
                        </label>
                    </div>
                </div>

                <div class="flex justify-end space-x-3 mt-6">
                    <button type="button" onclick="closeSignatureModal()" class="btn-secondary">
                        Zrušit
                    </button>
                    <button type="submit" class="btn-primary">
                        Uložit podpis
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Quill Editor -->
<script src="https://cdn.quilljs.com/1.3.6/quill.min.js"></script>
<script>
var signatureQuill;

document.addEventListener('DOMContentLoaded', function() {
    // Inicializace Quill editoru pro podpis
    signatureQuill = new Quill('#signatureEditor', {
        theme: 'snow',
        modules: {
            toolbar: [
                ['bold', 'italic', 'underline'],
                [{ 'color': [] }],
                [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                ['link'],
                ['clean']
            ]
        },
        placeholder: 'Napište obsah podpisu...'
    });

    // Synchronizace obsahu s hidden input
    signatureQuill.on('text-change', function() {
        document.getElementById('signature_content').value = signatureQuill.root.innerHTML;
    });
});

function openSignatureModal() {
    document.getElementById('signatureModal').classList.remove('hidden');
    document.getElementById('modalTitle').textContent = 'Nový podpis';
    document.getElementById('signatureForm').reset();
    signatureQuill.setText('');
}

function closeSignatureModal() {
    document.getElementById('signatureModal').classList.add('hidden');
}

function editSignature(id) {
    // Zde by byl AJAX request pro načtení podpisu
    document.getElementById('signatureModal').classList.remove('hidden');
    document.getElementById('modalTitle').textContent = 'Upravit podpis';
    document.getElementById('signature_id').value = id;
}

function deleteSignature(id) {
    if (confirm('Opravdu chcete smazat tento podpis?')) {
        // Zde by byl AJAX request pro smazání podpisu
        window.location.href = '/emails/signatures/' + id + '/delete';
    }
}
</script>
@endsection
