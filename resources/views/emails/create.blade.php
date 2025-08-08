@extends('layouts.app')

@section('title', 'Nový e-mail - Arcadia CRM')

@section('styles')
<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
<style>
    .ql-editor {
        min-height: 200px;
    }
</style>
@endsection

@section('content')
<div class="bg-white shadow rounded-lg">
    <div class="px-4 py-5 sm:p-6">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-gray-900">Nový e-mail</h2>
            <a href="/emails" class="btn-secondary">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Zpět
            </a>
        </div>

        <form action="/emails" method="POST" enctype="multipart/form-data">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Hlavní obsah -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Předmět -->
                    <div>
                        <label for="subject" class="block text-sm font-medium text-gray-700 mb-2">
                            Předmět *
                        </label>
                        <input type="text" name="subject" id="subject" required
                               class="form-input w-full" placeholder="Zadejte předmět e-mailu">
                    </div>

                    <!-- Editor -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Obsah e-mailu *
                        </label>
                        <div id="editor" class="border border-gray-300 rounded-md"></div>
                        <input type="hidden" name="body" id="body-input">
                    </div>

                    <!-- Přílohy -->
                    <div>
                        <label for="attachments" class="block text-sm font-medium text-gray-700 mb-2">
                            Přílohy
                        </label>
                        <input type="file" name="attachments[]" id="attachments" multiple
                               class="form-input w-full" accept=".pdf,.doc,.docx,.txt,.jpg,.jpeg,.png">
                        <p class="mt-1 text-sm text-gray-500">
                            Podporované formáty: PDF, DOC, DOCX, TXT, JPG, PNG (max. 10MB)
                        </p>
                    </div>
                </div>

                <!-- Postranní panel -->
                <div class="space-y-6">
                    <!-- Odesílatel -->
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Odesílatel</h3>

                        <div class="space-y-4">
                            <div>
                                <label for="from_name" class="block text-sm font-medium text-gray-700 mb-1">
                                    Jméno
                                </label>
                                <input type="text" name="from_name" id="from_name"
                                       class="form-input w-full" placeholder="Vaše jméno">
                            </div>

                            <div>
                                <label for="from_email" class="block text-sm font-medium text-gray-700 mb-1">
                                    E-mail *
                                </label>
                                <input type="email" name="from_email" id="from_email" required
                                       class="form-input w-full" placeholder="vas@email.cz">
                            </div>
                        </div>
                    </div>

                    <!-- Příjemci -->
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Příjemci</h3>

                        <div class="space-y-4">
                            <div>
                                <label for="to_emails" class="block text-sm font-medium text-gray-700 mb-1">
                                    Komu *
                                </label>
                                <input type="text" name="to_emails" id="to_emails" required
                                       class="form-input w-full" placeholder="email@example.com, email2@example.com">
                            </div>

                            <div>
                                <label for="cc_emails" class="block text-sm font-medium text-gray-700 mb-1">
                                    Kopie (CC)
                                </label>
                                <input type="text" name="cc_emails" id="cc_emails"
                                       class="form-input w-full" placeholder="email@example.com">
                            </div>

                            <div>
                                <label for="bcc_emails" class="block text-sm font-medium text-gray-700 mb-1">
                                    Skrytá kopie (BCC)
                                </label>
                                <input type="text" name="bcc_emails" id="bcc_emails"
                                       class="form-input w-full" placeholder="email@example.com">
                            </div>
                        </div>
                    </div>

                    <!-- Šablony -->
                    @if(isset($templates) && count($templates) > 0)
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Šablony</h3>

                        <select name="template_id" id="template_id" class="form-input w-full">
                            <option value="">Vyberte šablonu...</option>
                            @foreach($templates as $template)
                                <option value="{{ $template->getId() }}">{{ $template->getName() }}</option>
                            @endforeach
                        </select>
                    </div>
                    @endif

                    <!-- Zákazníci -->
                    @if(isset($customers) && count($customers) > 0)
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Související</h3>

                        <div class="space-y-4">
                            <div>
                                <label for="customer_id" class="block text-sm font-medium text-gray-700 mb-1">
                                    Zákazník
                                </label>
                                <select name="customer_id" id="customer_id" class="form-input w-full">
                                    <option value="">Vyberte zákazníka...</option>
                                    @foreach($customers as $customer)
                                        <option value="{{ $customer->getId() }}">{{ $customer->getName() }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Akce -->
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Akce</h3>

                        <div class="space-y-3">
                            <button type="submit" name="action" value="save_draft"
                                    class="w-full btn-secondary">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path>
                                </svg>
                                Uložit koncept
                            </button>

                            <button type="submit" name="action" value="send"
                                    class="w-full btn-primary">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                                </svg>
                                Odeslat
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Quill Editor -->
<script src="https://cdn.quilljs.com/1.3.6/quill.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Inicializace Quill editoru
    var quill = new Quill('#editor', {
        theme: 'snow',
        modules: {
            toolbar: [
                [{ 'header': [1, 2, 3, false] }],
                ['bold', 'italic', 'underline', 'strike'],
                [{ 'color': [] }, { 'background': [] }],
                [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                [{ 'align': [] }],
                ['link', 'image'],
                ['clean']
            ]
        },
        placeholder: 'Napište obsah e-mailu...'
    });

    // Synchronizace obsahu s hidden input
    quill.on('text-change', function() {
        document.getElementById('body-input').value = quill.root.innerHTML;
    });

    // Načtení šablony
    document.getElementById('template_id').addEventListener('change', function() {
        var templateId = this.value;
        if (templateId) {
            // Zde by byl AJAX request pro načtení šablony
            // Pro demonstraci použijeme placeholder
            quill.setText('Načítání šablony...');
        }
    });

    // Autocomplete pro e-maily
    var toEmailsInput = document.getElementById('to_emails');
    var customers = JSON.parse('@json($customers ?? [])');

    toEmailsInput.addEventListener('input', function() {
        var value = this.value.toLowerCase();
        var suggestions = customers.filter(function(customer) {
            return customer.name.toLowerCase().includes(value) ||
                   (customer.email && customer.email.toLowerCase().includes(value));
        });

        // Zde by byla implementace autocomplete dropdown
    });
});
</script>
@endsection
