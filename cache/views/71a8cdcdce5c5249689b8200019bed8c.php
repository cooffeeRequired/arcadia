<?php $__env->startSection('title', 'Domů - Arcadia'); ?>

<?php $__env->startSection('content'); ?>
<div class="bg-white overflow-hidden shadow rounded-lg">
    <div class="px-4 py-5 sm:p-6">
        <div class="text-center">
            <h2 class="text-3xl font-bold text-gray-900 mb-4">
                Vítejte v Arcadia
            </h2>
            <p class="text-lg text-gray-600 mb-8">
                Kompletní systém pro správu vztahů se zákazníky
            </p>

            <!-- Statistiky -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="bg-blue-50 p-6 rounded-lg">
                    <div class="text-2xl font-bold text-blue-600"><?php echo e($customersCount ?? 0); ?></div>
                    <div class="text-sm text-blue-500">Zákazníků</div>
                </div>
                <div class="bg-green-50 p-6 rounded-lg">
                    <div class="text-2xl font-bold text-green-600"><?php echo e($contactsCount ?? 0); ?></div>
                    <div class="text-sm text-green-500">Kontaktů</div>
                </div>
                <div class="bg-purple-50 p-6 rounded-lg">
                    <div class="text-2xl font-bold text-purple-600"><?php echo e($dealsCount ?? 0); ?></div>
                    <div class="text-sm text-purple-500">Obchodů</div>
                </div>
            </div>

            <!-- Akční tlačítka -->
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="/customers" class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Správa zákazníků
                </a>
                <a href="/contacts" class="inline-flex items-center px-6 py-3 border border-gray-300 text-base font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Správa kontaktů
                </a>
                <a href="/deals" class="inline-flex items-center px-6 py-3 border border-gray-300 text-base font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Správa obchodů
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Quick Access -->
<div class="mt-8 grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Zákazníci -->
    <div class="bg-white shadow rounded-lg">
        <div class="px-4 py-5 sm:p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900">Rychlý přístup - Zákazníci</h3>
                <div class="flex items-center space-x-2">
                    <button onclick="selectAll('customers')" class="text-sm text-blue-600 hover:text-blue-500">Vybrat vše</button>
                    <button onclick="bulkDelete('customers')" class="text-sm text-red-600 hover:text-red-500">Smazat vybrané</button>
                </div>
            </div>
            <div class="space-y-2 max-h-64 overflow-y-auto">
                <?php if(isset($customers) && count($customers) > 0): ?>
                    <?php $__currentLoopData = $customers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $customer): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="flex items-center space-x-3 p-2 hover:bg-gray-50 rounded">
                        <input type="checkbox" class="customer-checkbox" value="<?php echo e($customer->getId()); ?>" onchange="updateBulkActions()">
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900"><?php echo e($customer->getName()); ?></p>
                            <p class="text-sm text-gray-500"><?php echo e($customer->getEmail()); ?></p>
                        </div>
                        <a href="/customers/<?php echo e($customer->getId()); ?>" class="text-blue-600 hover:text-blue-500">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                            </svg>
                        </a>
                    </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                <?php else: ?>
                    <p class="text-gray-500 text-center py-4">Žádní zákazníci</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Kontakty -->
    <div class="bg-white shadow rounded-lg">
        <div class="px-4 py-5 sm:p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900">Rychlý přístup - Kontakty</h3>
                <div class="flex items-center space-x-2">
                    <button onclick="selectAll('contacts')" class="text-sm text-blue-600 hover:text-blue-500">Vybrat vše</button>
                    <button onclick="bulkDelete('contacts')" class="text-sm text-red-600 hover:text-red-500">Smazat vybrané</button>
                </div>
            </div>
            <div class="space-y-2 max-h-64 overflow-y-auto">
                <?php if(isset($contacts) && count($contacts) > 0): ?>
                    <?php $__currentLoopData = $contacts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $contact): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="flex items-center space-x-3 p-2 hover:bg-gray-50 rounded">
                        <input type="checkbox" class="contact-checkbox" value="<?php echo e($contact->getId()); ?>" onchange="updateBulkActions()">
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900"><?php echo e($contact->getSubject()); ?></p>
                            <p class="text-sm text-gray-500"><?php echo e($contact->getContactDate()->format('d.m.Y H:i')); ?></p>
                        </div>
                        <a href="/contacts/<?php echo e($contact->getId()); ?>" class="text-blue-600 hover:text-blue-500">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                            </svg>
                        </a>
                    </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                <?php else: ?>
                    <p class="text-gray-500 text-center py-4">Žádné kontakty</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Obchody -->
    <div class="bg-white shadow rounded-lg">
        <div class="px-4 py-5 sm:p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900">Rychlý přístup - Obchody</h3>
                <div class="flex items-center space-x-2">
                    <button onclick="selectAll('deals')" class="text-sm text-blue-600 hover:text-blue-500">Vybrat vše</button>
                    <button onclick="bulkDelete('deals')" class="text-sm text-red-600 hover:text-red-500">Smazat vybrané</button>
                </div>
            </div>
            <div class="space-y-2 max-h-64 overflow-y-auto">
                <?php if(isset($deals) && count($deals) > 0): ?>
                    <?php $__currentLoopData = $deals; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $deal): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="flex items-center space-x-3 p-2 hover:bg-gray-50 rounded">
                        <input type="checkbox" class="deal-checkbox" value="<?php echo e($deal->getId()); ?>" onchange="updateBulkActions()">
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900"><?php echo e($deal->getTitle()); ?></p>
                            <p class="text-sm text-gray-500"><?php echo e($deal->getValue() ? number_format($deal->getValue(), 0, ',', ' ') . ' Kč' : 'Neuvedeno'); ?></p>
                        </div>
                        <a href="/deals/<?php echo e($deal->getId()); ?>" class="text-blue-600 hover:text-blue-500">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                            </svg>
                        </a>
                    </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                <?php else: ?>
                    <p class="text-gray-500 text-center py-4">Žádné obchody</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Poslední aktivity -->
<div class="mt-8 bg-white shadow rounded-lg">
    <div class="px-4 py-5 sm:p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Poslední aktivity</h3>
        <div class="space-y-4">
            <?php if(isset($recentContacts) && count($recentContacts) > 0): ?>
                <?php $__currentLoopData = $recentContacts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $contact): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="flex items-center space-x-3 p-3 bg-gray-50 rounded-lg">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                            <span class="text-blue-600 text-sm font-medium"><?php echo e(substr($contact->getType(), 0, 1)); ?></span>
                        </div>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900"><?php echo e($contact->getSubject()); ?></p>
                        <p class="text-sm text-gray-500"><?php echo e($contact->getContactDate()->format('d.m.Y H:i')); ?></p>
                    </div>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <?php elseif(isset($recentDeals) && count($recentDeals) > 0): ?>
                <?php $__currentLoopData = $recentDeals; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $deal): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="flex items-center space-x-3 p-3 bg-gray-50 rounded-lg">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                            <span class="text-green-600 text-sm font-medium"><?php echo e(substr($deal->getTitle(), 0, 1)); ?></span>
                        </div>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900"><?php echo e($deal->getTitle()); ?></p>
                        <p class="text-sm text-gray-500"><?php echo e($deal->getCreatedAt()->format('d.m.Y H:i')); ?></p>
                    </div>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <?php else: ?>
                <p class="text-gray-500 text-center py-4">Zatím žádné aktivity</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Bulk Delete Modal -->
<div id="bulkDeleteModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3 text-center">
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100">
                <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
                </svg>
            </div>
            <h3 class="text-lg leading-6 font-medium text-gray-900 mt-4">Potvrdit smazání</h3>
            <div class="mt-2 px-7 py-3">
                <p class="text-sm text-gray-500">
                    Opravdu chcete smazat vybrané položky? Tato akce je nevratná.
                </p>
            </div>
            <div class="items-center px-4 py-3">
                <button id="confirmBulkDelete" class="px-4 py-2 bg-red-500 text-white text-base font-medium rounded-md w-24 mr-2 hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-red-300">
                    Smazat
                </button>
                <button onclick="closeBulkDeleteModal()" class="px-4 py-2 bg-gray-500 text-white text-base font-medium rounded-md w-24 hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-300">
                    Zrušit
                </button>
            </div>
        </div>
    </div>
</div>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
<script>
let currentBulkType = '';
let selectedIds = [];

function selectAll(type) {
    const checkboxes = document.querySelectorAll('.' + type + '-checkbox');
    const allChecked = Array.from(checkboxes).every(cb => cb.checked);

    checkboxes.forEach(checkbox => {
        checkbox.checked = !allChecked;
    });

    updateBulkActions();
}

function updateBulkActions() {
    const customerCheckboxes = document.querySelectorAll('.customer-checkbox:checked');
    const contactCheckboxes = document.querySelectorAll('.contact-checkbox:checked');
    const dealCheckboxes = document.querySelectorAll('.deal-checkbox:checked');

    selectedIds = {
        customers: Array.from(customerCheckboxes).map(cb => cb.value),
        contacts: Array.from(contactCheckboxes).map(cb => cb.value),
        deals: Array.from(dealCheckboxes).map(cb => cb.value)
    };
}

function bulkDelete(type) {
    const ids = selectedIds[type] || [];
    if (ids.length === 0) {
        alert('Vyberte alespoň jednu položku ke smazání.');
        return;
    }

    currentBulkType = type;
    document.getElementById('bulkDeleteModal').classList.remove('hidden');
}

function closeBulkDeleteModal() {
    document.getElementById('bulkDeleteModal').classList.add('hidden');
}

function confirmBulkDelete() {
    const ids = selectedIds[currentBulkType] || [];
    if (ids.length === 0) return;

    // Vytvoření formuláře pro POST request
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '/' + currentBulkType + '/bulk-delete';

    // Přidání CSRF tokenu (pokud existuje)
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    if (csrfToken) {
        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = '_token';
        csrfInput.value = csrfToken;
        form.appendChild(csrfInput);
    }

    // Přidání ID položek
    ids.forEach(id => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'ids[]';
        input.value = id;
        form.appendChild(input);
    });

    document.body.appendChild(form);
    form.submit();
}

// Event listeners
document.getElementById('confirmBulkDelete').addEventListener('click', confirmBulkDelete);

// Inicializace
document.addEventListener('DOMContentLoaded', function() {
    updateBulkActions();
});
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /custom/repositories/private/Arcadia/resources/views/home.blade.php ENDPATH**/ ?>