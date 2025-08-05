<?php $__env->startSection('title', 'Zákazníci - Arcadia CRM'); ?>

<?php $__env->startSection('content'); ?>
<div class="bg-white shadow rounded-lg">
    <div class="px-4 py-5 sm:p-6">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-gray-900">Zákazníci</h2>
            <a href="/customers/create" class="btn-primary">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                Nový zákazník
            </a>
        </div>

        <!-- Filtry -->
        <div class="mb-6">
            <div class="flex flex-col sm:flex-row gap-4">
                <div class="flex-1">
                    <input type="text" placeholder="Hledat zákazníky..." class="form-input">
                </div>
                <select class="form-input w-full sm:w-48">
                    <option>Všechny kategorie</option>
                    <option>Firmy</option>
                    <option>Osoby</option>
                </select>
            </div>
        </div>

        <!-- Tabulka zákazníků -->
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Název
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Email
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Telefon
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Kategorie
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Akce
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if(isset($customers) && count($customers) > 0): ?>
                        <?php $__currentLoopData = $customers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $customer): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10">
                                        <div class="h-10 w-10 rounded-full bg-primary-100 flex items-center justify-center">
                                            <span class="text-primary-600 font-medium"><?php echo e(substr($customer->getName(), 0, 1)); ?></span>
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900"><?php echo e($customer->getName()); ?></div>
                                        <div class="text-sm text-gray-500"><?php echo e($customer->getCompany() ?? 'Osoba'); ?></div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900"><?php echo e($customer->getEmail()); ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900"><?php echo e($customer->getPhone()); ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full <?php echo e($customer->getCategory() === 'company' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800'); ?>">
                                    <?php echo e($customer->getCategory() === 'company' ? 'Firma' : 'Osoba'); ?>

                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex space-x-2">
                                    <a href="/customers/<?php echo e($customer->getId()); ?>" class="text-primary-600 hover:text-primary-900">Zobrazit</a>
                                    <a href="/customers/<?php echo e($customer->getId()); ?>/edit" class="text-gray-600 hover:text-gray-900">Upravit</a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                                Žádní zákazníci nebyli nalezeni
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Paginace -->
        <?php if(isset($pagination)): ?>
        <div class="mt-6 flex items-center justify-between 2">
            <div class="text-sm text-gray-700">
                Zobrazeno <?php echo e($pagination->from); ?> - <?php echo e($pagination->to); ?> z <?php echo e($pagination->total); ?> zákazníků
            </div>
            <div class="flex space-x-2">
                <?php if($pagination->currentPage > 1): ?>
                    <a href="?page=<?php echo e($pagination->currentPage - 1); ?>" class="btn-secondary">Předchozí</a>
                <?php endif; ?>
                <?php if($pagination->currentPage < $pagination->lastPage): ?>
                    <a href="?page=<?php echo e($pagination->currentPage + 1); ?>" class="btn-secondary">Další</a>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>
<?php $__env->stopSection(); ?> 
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /custom/repositories/private/Arcadia/resources/views/customers/index.blade.php ENDPATH**/ ?>