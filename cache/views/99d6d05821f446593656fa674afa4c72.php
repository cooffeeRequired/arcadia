<?php $__env->startSection('content'); ?>
<div class="px-4 sm:px-6 lg:px-8">
    <div class="mb-6">
        <a href="/settings" class="text-blue-600 hover:text-blue-900 text-sm font-medium">
            ← Zpět na nastavení
        </a>
    </div>

    <div class="sm:flex sm:items-center">
        <div class="sm:flex-auto">
            <h1 class="text-xl font-semibold text-gray-900">Profil uživatele</h1>
            <p class="mt-2 text-sm text-gray-700">Správa osobních údajů a nastavení účtu.</p>
        </div>
    </div>

    <div class="mt-8">
        <div class="bg-white shadow sm:rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900">Osobní údaje</h3>
                <p class="mt-1 max-w-2xl text-sm text-gray-500">Upravte své osobní údaje a kontaktní informace.</p>
                
                <form method="POST" action="/settings/profile" class="mt-6 space-y-6">
                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700">Jméno a příjmení</label>
                            <input type="text" name="name" id="name" value="<?php echo e($user['name'] ?? ''); ?>" required class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                        </div>

                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                            <input type="email" name="email" id="email" value="<?php echo e($user['email'] ?? ''); ?>" required class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                        </div>

                        <div>
                            <label for="role" class="block text-sm font-medium text-gray-700">Role</label>
                            <input type="text" name="role" id="role" value="<?php echo e($user['role'] ?? ''); ?>" readonly class="mt-1 bg-gray-50 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                        </div>

                        <div>
                            <label for="last_login" class="block text-sm font-medium text-gray-700">Poslední přihlášení</label>
                            <input type="text" name="last_login" id="last_login" value="<?php echo e($user['last_login'] ? $user['last_login']->format('d.m.Y H:i') : ''); ?>" readonly class="mt-1 bg-gray-50 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                        </div>

                        <div>
                            <label for="created_at" class="block text-sm font-medium text-gray-700">Účet vytvořen</label>
                            <input type="text" name="created_at" id="created_at" value="<?php echo e($user['created_at'] ? $user['created_at']->format('d.m.Y') : ''); ?>" readonly class="mt-1 bg-gray-50 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                        </div>
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Uložit změny
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Změna hesla -->
    <div class="mt-8">
        <div class="bg-white shadow sm:rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900">Změna hesla</h3>
                <p class="mt-1 max-w-2xl text-sm text-gray-500">Změňte své heslo pro zvýšení bezpečnosti.</p>
                
                <form method="POST" action="/settings/profile" class="mt-6 space-y-6">
                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                        <div>
                            <label for="current_password" class="block text-sm font-medium text-gray-700">Aktuální heslo</label>
                            <input type="password" name="current_password" id="current_password" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                        </div>

                        <div>
                            <label for="new_password" class="block text-sm font-medium text-gray-700">Nové heslo</label>
                            <input type="password" name="new_password" id="new_password" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                        </div>

                        <div class="sm:col-span-2">
                            <label for="confirm_password" class="block text-sm font-medium text-gray-700">Potvrzení nového hesla</label>
                            <input type="password" name="confirm_password" id="confirm_password" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                        </div>
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Změnit heslo
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?> 
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /custom/repositories/private/Arcadia/resources/views/settings/profile.blade.php ENDPATH**/ ?>