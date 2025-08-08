@extends('layouts.app')

@section('title', 'Vytvořit workflow')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Vytvořit workflow</h1>
            <p class="text-gray-600 mt-2">Nastavte automatizaci pro vaše business procesy</p>
        </div>
        <a href="/workflows" class="btn-secondary">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Zpět na workflow
        </a>
    </div>

    <form method="POST" action="/workflows" class="space-y-8">
        <!-- Základní informace -->
        <div class="card">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">Základní informace</h2>
            </div>
            <div class="p-6 space-y-6">
                <div>
                    <label for="name" class="form-label">Název workflow *</label>
                    <input type="text" id="name" name="name" value="{{ $old['name'] ?? '' }}"
                           class="form-input @if(isset($errors['name'])) border-red-500 @endif"
                           placeholder="Např. Uvítání nového zákazníka">
                    @if(isset($errors['name']))
                    <p class="mt-1 text-sm text-red-600">{{ $errors['name'] }}</p>
                    @endif
                </div>

                <div>
                    <label for="description" class="form-label">Popis</label>
                    <textarea id="description" name="description" rows="3"
                              class="form-input @if(isset($errors['description'])) border-red-500 @endif"
                              placeholder="Popis účelu workflow">{{ $old['description'] ?? '' }}</textarea>
                    @if(isset($errors['description']))
                    <p class="mt-1 text-sm text-red-600">{{ $errors['description'] }}</p>
                    @endif
                </div>

                <div class="flex items-center">
                    <input type="checkbox" id="is_active" name="is_active" value="1"
                           {{ isset($old['is_active']) ? 'checked' : '' }} class="form-checkbox">
                    <label for="is_active" class="ml-2 text-sm text-gray-700">Workflow je aktivní</label>
                </div>

                <div>
                    <label for="priority" class="form-label">Priorita</label>
                    <select id="priority" name="priority" class="form-input">
                        <option value="0" {{ ($old['priority'] ?? 0) == 0 ? 'selected' : '' }}>Nízká (0)</option>
                        <option value="5" {{ ($old['priority'] ?? 0) == 5 ? 'selected' : '' }}>Střední (5)</option>
                        <option value="10" {{ ($old['priority'] ?? 0) == 10 ? 'selected' : '' }}>Vysoká (10)</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Trigger -->
        <div class="card">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">Trigger (Spouštěč)</h2>
                <p class="text-sm text-gray-600 mt-1">Vyberte událost, která spustí workflow</p>
            </div>
            <div class="p-6">
                <div>
                    <label for="trigger_type" class="form-label">Typ triggeru *</label>
                    <select id="trigger_type" name="trigger_type" class="form-input @if(isset($errors['trigger_type'])) border-red-500 @endif">
                        <option value="">Vyberte trigger</option>
                        @foreach($triggerTypes as $value => $label)
                        <option value="{{ $value }}" {{ ($old['trigger_type'] ?? '') == $value ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                        @endforeach
                    </select>
                    @if(isset($errors['trigger_type']))
                    <p class="mt-1 text-sm text-red-600">{{ $errors['trigger_type'] }}</p>
                    @endif
                </div>

                <div class="mt-4">
                    <label for="trigger_config" class="form-label">Konfigurace triggeru (JSON)</label>
                    <textarea id="trigger_config" name="trigger_config" rows="4"
                              class="form-input font-mono text-sm"
                              placeholder='{"days_overdue": 30}'>{{ $old['trigger_config'] ?? '{}' }}</textarea>
                    <p class="mt-1 text-sm text-gray-500">Volitelné nastavení pro trigger (např. počet dní pro nezaplacené faktury)</p>
                </div>
            </div>
        </div>

        <!-- Podmínky -->
        <div class="card">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">Podmínky</h2>
                <p class="text-sm text-gray-600 mt-1">Nastavte podmínky, které musí být splněny</p>
            </div>
            <div class="p-6">
                <div id="conditions-container">
                    <div class="condition-item border rounded-lg p-4 mb-4">
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div>
                                <label class="form-label">Typ podmínky</label>
                                <select class="condition-type form-input">
                                    <option value="field_exists">Pole existuje</option>
                                    <option value="field_value">Hodnota pole</option>
                                    <option value="field_empty">Pole je prázdné</option>
                                    <option value="field_not_empty">Pole není prázdné</option>
                                </select>
                            </div>
                            <div>
                                <label class="form-label">Pole</label>
                                <input type="text" class="condition-field form-input" placeholder="email, status, value">
                            </div>
                            <div class="operator-container">
                                <label class="form-label">Operátor</label>
                                <select class="condition-operator form-input">
                                    <option value="equals">Rovná se</option>
                                    <option value="not_equals">Nerovná se</option>
                                    <option value="contains">Obsahuje</option>
                                    <option value="greater_than">Větší než</option>
                                    <option value="less_than">Menší než</option>
                                </select>
                            </div>
                            <div class="value-container">
                                <label class="form-label">Hodnota</label>
                                <input type="text" class="condition-value form-input" placeholder="Hodnota pro porovnání">
                            </div>
                        </div>
                        <button type="button" class="remove-condition mt-2 text-red-600 hover:text-red-800 text-sm">
                            <svg class="w-4 h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                            Odstranit podmínku
                        </button>
                    </div>
                </div>

                <button type="button" id="add-condition" class="btn-secondary">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    Přidat podmínku
                </button>

                <input type="hidden" name="conditions" id="conditions-input" value="{{ $old['conditions'] ?? '[]' }}">
            </div>
        </div>

        <!-- Akce -->
        <div class="card">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">Akce</h2>
                <p class="text-sm text-gray-600 mt-1">Nastavte akce, které se provedou</p>
            </div>
            <div class="p-6">
                <div id="actions-container">
                    <div class="action-item border rounded-lg p-4 mb-4">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="form-label">Typ akce</label>
                                <select class="action-type form-input">
                                    @foreach($actionTypes as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="form-label">Parametr 1</label>
                                <input type="text" class="action-param1 form-input" placeholder="Název, e-mail, template">
                            </div>
                            <div>
                                <label class="form-label">Parametr 2</label>
                                <input type="text" class="action-param2 form-input" placeholder="Hodnota, předmět, popis">
                            </div>
                        </div>
                        <button type="button" class="remove-action mt-2 text-red-600 hover:text-red-800 text-sm">
                            <svg class="w-4 h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                            Odstranit akci
                        </button>
                    </div>
                </div>

                <button type="button" id="add-action" class="btn-secondary">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    Přidat akci
                </button>

                <input type="hidden" name="actions" id="actions-input" value="{{ $old['actions'] ?? '[]' }}">
            </div>
        </div>

        <!-- Submit -->
        <div class="flex justify-end space-x-4">
            <a href="/workflows" class="btn-secondary">Zrušit</a>
            <button type="submit" class="btn-primary">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                Vytvořit workflow
            </button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Podmínky
    const conditionsContainer = document.getElementById('conditions-container');
    const addConditionBtn = document.getElementById('add-condition');
    const conditionsInput = document.getElementById('conditions-input');

    function updateConditions() {
        const conditions = [];
        document.querySelectorAll('.condition-item').forEach(item => {
            const type = item.querySelector('.condition-type').value;
            const field = item.querySelector('.condition-field').value;
            const operator = item.querySelector('.condition-operator').value;
            const value = item.querySelector('.condition-value').value;

            conditions.push({
                type: type,
                field: field,
                operator: operator,
                value: value
            });
        });
        conditionsInput.value = JSON.stringify(conditions);
    }

    function addCondition() {
        const conditionHtml = `
            <div class="condition-item border rounded-lg p-4 mb-4">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="form-label">Typ podmínky</label>
                        <select class="condition-type form-input">
                            <option value="field_exists">Pole existuje</option>
                            <option value="field_value">Hodnota pole</option>
                            <option value="field_empty">Pole je prázdné</option>
                            <option value="field_not_empty">Pole není prázdné</option>
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Pole</label>
                        <input type="text" class="condition-field form-input" placeholder="email, status, value">
                    </div>
                    <div class="operator-container">
                        <label class="form-label">Operátor</label>
                        <select class="condition-operator form-input">
                            <option value="equals">Rovná se</option>
                            <option value="not_equals">Nerovná se</option>
                            <option value="contains">Obsahuje</option>
                            <option value="greater_than">Větší než</option>
                            <option value="less_than">Menší než</option>
                        </select>
                    </div>
                    <div class="value-container">
                        <label class="form-label">Hodnota</label>
                        <input type="text" class="condition-value form-input" placeholder="Hodnota pro porovnání">
                    </div>
                </div>
                <button type="button" class="remove-condition mt-2 text-red-600 hover:text-red-800 text-sm">
                    <svg class="w-4 h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                    </svg>
                    Odstranit podmínku
                </button>
            </div>
        `;
        conditionsContainer.insertAdjacentHTML('beforeend', conditionHtml);
    }

    addConditionBtn.addEventListener('click', addCondition);
    conditionsContainer.addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-condition')) {
            e.target.closest('.condition-item').remove();
            updateConditions();
        }
    });

    conditionsContainer.addEventListener('change', updateConditions);
    conditionsContainer.addEventListener('input', updateConditions);

    // Akce
    const actionsContainer = document.getElementById('actions-container');
    const addActionBtn = document.getElementById('add-action');
    const actionsInput = document.getElementById('actions-input');

    function updateActions() {
        const actions = [];
        document.querySelectorAll('.action-item').forEach(item => {
            const type = item.querySelector('.action-type').value;
            const param1 = item.querySelector('.action-param1').value;
            const param2 = item.querySelector('.action-param2').value;

            actions.push({
                type: type,
                param1: param1,
                param2: param2
            });
        });
        actionsInput.value = JSON.stringify(actions);
    }

    function addAction() {
        const actionHtml = `
            <div class="action-item border rounded-lg p-4 mb-4">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="form-label">Typ akce</label>
                        <select class="action-type form-input">
                            @foreach($actionTypes as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Parametr 1</label>
                        <input type="text" class="action-param1 form-input" placeholder="Název, e-mail, template">
                    </div>
                    <div>
                        <label class="form-label">Parametr 2</label>
                        <input type="text" class="action-param2 form-input" placeholder="Hodnota, předmět, popis">
                    </div>
                </div>
                <button type="button" class="remove-action mt-2 text-red-600 hover:text-red-800 text-sm">
                    <svg class="w-4 h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                    </svg>
                    Odstranit akci
                </button>
            </div>
        `;
        actionsContainer.insertAdjacentHTML('beforeend', actionHtml);
    }

    addActionBtn.addEventListener('click', addAction);
    actionsContainer.addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-action')) {
            e.target.closest('.action-item').remove();
            updateActions();
        }
    });

    actionsContainer.addEventListener('change', updateActions);
    actionsContainer.addEventListener('input', updateActions);

    // Inicializace
    updateConditions();
    updateActions();
});
</script>
@endsection
