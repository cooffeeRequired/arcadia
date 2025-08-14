@extends('layouts.app')

@section('title', 'E-mailové šablony - Arcadia CRM')

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
            <h2 class="text-2xl font-bold text-gray-900">E-mailové šablony</h2>
            <button onclick="openTemplateModal()" class="btn-primary">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                Nová šablona
            </button>
        </div>

        <!-- Seznam šablon -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @if(isset($templates) && count($templates) > 0)
                @foreach($templates as $template)
                <div class="bg-gray-50 rounded-lg p-6 border border-gray-200">
                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <h3 class="text-lg font-medium text-gray-900">{{ $template->getName() }}</h3>
                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                {{ ucfirst($template->getCategory()) }}
                            </span>
                        </div>
                        <div class="flex space-x-2">
                            <button onclick="editTemplate({{ $template->getId() }})"
                                    class="text-gray-600 hover:text-gray-900">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                            </button>
                            <button onclick="deleteTemplate({{ $template->getId() }})"
                                    class="text-red-600 hover:text-red-900">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                    <div class="mb-3">
                        <strong>Předmět:</strong> {{ $template->getSubject() }}
                    </div>
                    <div class="prose prose-sm max-w-none">
                        {!! $template->getContent() !!}
                    </div>
                    <div class="mt-4 text-sm text-gray-500">
                        Vytvořeno: {{ $template->getCreatedAt()->format('d.m.Y H:i') }}
                    </div>
                </div>
                @endforeach
            @else
                <div class="col-span-full text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">Žádné šablony</h3>
                    <p class="mt-1 text-sm text-gray-500">Začněte vytvořením první e-mailové šablony.</p>
                    <div class="mt-6">
                        <button onclick="openTemplateModal()" class="btn-primary">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            Vytvořit šablonu
                        </button>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Modal pro vytvoření/úpravu šablony -->
<div id="templateModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium text-gray-900" id="modalTitle">Nová šablona</h3>
                <button onclick="closeTemplateModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <form id="templateForm" action="/emails/templates" method="POST">
                <input type="hidden" name="template_id" id="template_id">

                <div class="space-y-4">
                    <div>
                        <label for="template_name" class="block text-sm font-medium text-gray-700 mb-2">
                            Název šablony *
                        </label>
                        <input type="text" name="name" id="template_name" required
                               class="form-input w-full" placeholder="Např. Uvítací e-mail">
                    </div>

                    <div>
                        <label for="template_subject" class="block text-sm font-medium text-gray-700 mb-2">
                            Předmět e-mailu *
                        </label>
                        <input type="text" name="subject" id="template_subject" required
                               class="form-input w-full" placeholder="Např. Vítejte v Arcadia CRM">
                    </div>

                    <div>
                        <label for="template_category" class="block text-sm font-medium text-gray-700 mb-2">
                            Kategorie
                        </label>
                        <select name="category" id="template_category" class="form-select w-full">
                            <option value="welcome">Uvítací</option>
                            <option value="follow-up">Follow-up</option>
                            <option value="invoice">Faktury</option>
                            <option value="general">Obecné</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Obsah šablony *
                        </label>
                        <div id="templateEditor" class="border border-gray-300 rounded-md"></div>
                        <input type="hidden" name="content" id="template_content">
                    </div>
                </div>

                <div class="flex justify-end space-x-3 mt-6">
                    <button type="button" onclick="closeTemplateModal()" class="btn-secondary">
                        Zrušit
                    </button>
                    <button type="submit" class="btn-primary">
                        Uložit šablonu
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Quill Editor -->
<script src="https://cdn.quilljs.com/1.3.6/quill.min.js"></script>
<script>
var templateQuill;

document.addEventListener('DOMContentLoaded', function() {
    // Inicializace Quill editoru pro šablonu
    templateQuill = new Quill('#templateEditor', {
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
        placeholder: 'Napište obsah šablony...'
    });

    // Synchronizace obsahu s hidden input
    templateQuill.on('text-change', function() {
        document.getElementById('template_content').value = templateQuill.root.innerHTML;
    });
});

function openTemplateModal() {
    document.getElementById('templateModal').classList.remove('hidden');
    document.getElementById('modalTitle').textContent = 'Nová šablona';
    document.getElementById('templateForm').reset();
    templateQuill.setText('');
}

function closeTemplateModal() {
    document.getElementById('templateModal').classList.add('hidden');
}

function editTemplate(id) {
    // Zde by byl AJAX request pro načtení šablony
    document.getElementById('templateModal').classList.remove('hidden');
    document.getElementById('modalTitle').textContent = 'Upravit šablonu';
    document.getElementById('template_id').value = id;
}

function deleteTemplate(id) {
    if (confirm('Opravdu chcete smazat tuto šablonu?')) {
        // Zde by byl AJAX request pro smazání šablony
        window.location.href = '/emails/templates/' + id + '/delete';
    }
}
</script>
@endsection

