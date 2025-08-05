<?php $__env->startSection('title', 'Nový zákazník - Arcadia CRM'); ?>

<?php $__env->startSection('content'); ?>
<div class="bg-white shadow rounded-lg">
    <div class="px-4 py-5 sm:p-6">
        <div class="mb-6">
            <h2 class="text-2xl font-bold text-gray-900">Nový zákazník</h2>
            <p class="mt-1 text-sm text-gray-600">Vyplňte údaje o novém zákazníkovi</p>
        </div>

        <form action="/customers" method="POST" class="space-y-6">
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <!-- Kategorie -->
                <div class="sm:col-span-2">
                    <label class="form-label">Kategorie</label>
                    <div class="mt-2 space-y-2">
                        <div class="flex items-center">
                            <input type="radio" id="person" name="category" value="person" class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300">
                            <label for="person" class="ml-3 text-sm text-gray-700">Osoba</label>
                        </div>
                        <div class="flex items-center">
                            <input type="radio" id="company" name="category" value="company" class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300">
                            <label for="company" class="ml-3 text-sm text-gray-700">Firma</label>
                        </div>
                    </div>
                </div>

                <!-- Jméno/Název -->
                <div>
                    <label for="name" class="form-label">Jméno / Název</label>
                    <input type="text" id="name" name="name" required class="form-input">
                </div>

                <!-- Společnost (pouze pro firmy) -->
                <div id="company-field" class="hidden">
                    <label for="company" class="form-label">Společnost</label>
                    <input type="text" id="company" name="company" class="form-input">
                </div>

                <!-- Email -->
                <div>
                    <label for="email" class="form-label">Email</label>
                    <input type="email" id="email" name="email" required class="form-input">
                </div>

                <!-- Telefon -->
                <div>
                    <label for="phone" class="form-label">Telefon</label>
                    <input type="tel" id="phone" name="phone" class="form-input">
                </div>

                <!-- Adresa -->
                <div class="sm:col-span-2">
                    <label for="address" class="form-label">Adresa</label>
                    <textarea id="address" name="address" rows="3" class="form-input"></textarea>
                </div>

                <!-- Poznámky -->
                <div class="sm:col-span-2">
                    <label for="notes" class="form-label">Poznámky</label>
                    <textarea id="notes" name="notes" rows="4" class="form-input"></textarea>
                </div>
            </div>

            <!-- Tlačítka -->
            <div class="flex justify-end space-x-3">
                <a href="/customers" class="btn-secondary">
                    Zrušit
                </a>
                <button type="submit" class="btn-primary">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    Vytvořit zákazníka
                </button>
            </div>
        </form>
    </div>
</div>

<?php $__env->startPush('scripts'); ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const categoryInputs = document.querySelectorAll('input[name="category"]');
    const companyField = document.getElementById('company-field');
    const companyInput = document.getElementById('company');

    function toggleCompanyField() {
        const selectedCategory = document.querySelector('input[name="category"]:checked');
        if (selectedCategory && selectedCategory.value === 'company') {
            companyField.classList.remove('hidden');
            companyInput.required = true;
        } else {
            companyField.classList.add('hidden');
            companyInput.required = false;
        }
    }

    categoryInputs.forEach(input => {
        input.addEventListener('change', toggleCompanyField);
    });
});
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?> 
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /custom/repositories/private/Arcadia/resources/views/customers/create.blade.php ENDPATH**/ ?>