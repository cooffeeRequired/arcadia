<?php $__env->startSection('content'); ?>
<div class="px-4 sm:px-6 lg:px-8">
    <div class="sm:flex sm:items-center">
        <div class="sm:flex-auto">
            <h1 class="text-xl font-semibold text-gray-900">Faktury</h1>
            <p class="mt-2 text-sm text-gray-700">Seznam všech faktur a jejich stavů.</p>
        </div>
        <div class="mt-4 sm:mt-0 sm:ml-16 sm:flex-none">
            <a href="/invoices/create" class="inline-flex items-center justify-center rounded-md border border-transparent bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 sm:w-auto">
                Nová faktura
            </a>
        </div>
    </div>
    
    <div class="mt-8 flex flex-col">
        <div class="-my-2 -mx-4 overflow-x-auto sm:-mx-6 lg:-mx-8">
            <div class="inline-block min-w-full py-2 align-middle md:px-6 lg:px-8">
                <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 md:rounded-lg">
                    <table class="min-w-full divide-y divide-gray-300">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Číslo faktury</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Zákazník</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Datum vystavení</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Datum splatnosti</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Částka</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th scope="col" class="relative px-6 py-3">
                                    <span class="sr-only">Akce</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php if(isset($invoices) && count($invoices) > 0): ?>
                                <?php $__currentLoopData = $invoices; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $invoice): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        <a href="/invoices/<?php echo e($invoice->getId()); ?>" class="text-blue-600 hover:text-blue-900">
                                            <?php echo e($invoice->getInvoiceNumber()); ?>

                                        </a>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <a href="/customers/<?php echo e($invoice->getCustomer()->getId()); ?>" class="text-blue-600 hover:text-blue-900">
                                            <?php echo e($invoice->getCustomer()->getName()); ?>

                                        </a>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo e($invoice->getIssueDate()->format('d.m.Y')); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo e($invoice->getDueDate()->format('d.m.Y')); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo e(number_format($invoice->getTotal(), 2, ',', ' ')); ?> <?php echo e($invoice->getCurrency()); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php
                                            $statusColors = [
                                                'draft' => 'bg-gray-100 text-gray-800',
                                                'sent' => 'bg-blue-100 text-blue-800',
                                                'paid' => 'bg-green-100 text-green-800',
                                                'overdue' => 'bg-red-100 text-red-800',
                                                'cancelled' => 'bg-yellow-100 text-yellow-800'
                                            ];
                                            $statusLabels = [
                                                'draft' => 'Koncept',
                                                'sent' => 'Odeslána',
                                                'paid' => 'Zaplacena',
                                                'overdue' => 'Po splatnosti',
                                                'cancelled' => 'Zrušena'
                                            ];
                                        ?>
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full <?php echo e($statusColors[$invoice->getStatus()] ?? 'bg-gray-100 text-gray-800'); ?>">
                                            <?php echo e($statusLabels[$invoice->getStatus()] ?? $invoice->getStatus()); ?>

                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <a href="/invoices/<?php echo e($invoice->getId()); ?>/pdf" target="_blank" class="text-green-600 hover:text-green-900 mr-3">PDF</a>
                                        <a href="/invoices/<?php echo e($invoice->getId()); ?>" class="text-blue-600 hover:text-blue-900 mr-3">Zobrazit</a>
                                        <a href="/invoices/<?php echo e($invoice->getId()); ?>/edit" class="text-indigo-600 hover:text-indigo-900">Upravit</a>
                                    </td>
                                </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="px-6 py-4 text-center text-sm text-gray-500">
                                        Žádné faktury nebyly nalezeny.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?> 
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /custom/repositories/private/Arcadia/resources/views/invoices/index.blade.php ENDPATH**/ ?>