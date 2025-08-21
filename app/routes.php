<?php

use App\Controllers\AuthController;
use App\Controllers\ContactController;
use App\Controllers\CustomerController;
use App\Controllers\DealController;
use App\Controllers\EmailController;
use App\Controllers\HomeController;
use App\Controllers\InvoiceController;
use App\Controllers\LanguageController;
use App\Controllers\ProjectController;
use App\Controllers\ReportController;
use App\Controllers\SettingsController;
use App\Controllers\TemplateController;
use App\Controllers\WorkflowController;
use App\Controllers\AjaxController;
use App\Controllers\DemoController;
use Core\Facades\Container;
use Core\Http\Request;
use Core\Routing\Router;

$router = new Router();



// Auth routes (guest middleware - dostupné nepřihlášeným)
$router->group(['middleware' => ['guest']], function (Router $router) {
    $router->get('/login', [AuthController::class, 'showLogin'])->name('auth.login');
    $router->post('/login', [AuthController::class, 'login'])->name('auth.login.post');
    $router->get('/register', [AuthController::class, 'showRegister'])->name('auth.register');
    $router->post('/register', [AuthController::class, 'register'])->name('auth.register.post');
});

// Chráněné routy (auth middleware - vyžadují přihlášení)
$router->group(['middleware' => ['auth']], function (Router $router) {
    // Logout
    $router->get('/logout', [AuthController::class, 'logout'])->name('auth.logout');

    // Domovská stránka
    $router->get('/', [HomeController::class, 'index'])->name('home');
    $router->get('/home', [HomeController::class, 'index'])->name('home.index');
    $router->get('/test-external-session', [HomeController::class, 'testExternalSession'])->name('home.test-session');

    // Zákazníci
    $router->group(['prefix' => '/customers'], function (Router $router) {
        $router->get('', [CustomerController::class, 'index'])->name('customers.index'); // -> no projde.
        $router->get('/create', [CustomerController::class, 'create'])->name('customers.create');
        $router->post('/', [CustomerController::class, 'store'])->name('customers.store');
        $router->get('/{id}', [CustomerController::class, 'show'])->name('customers.show'); // -> projde
        $router->get('/{id}/edit', [CustomerController::class, 'edit'])->name('customers.edit');
        $router->post('/{id}', [CustomerController::class, 'update'])->name('customers.update');
        $router->post('/{id}/delete', [CustomerController::class, 'delete'])->name('customers.delete');
        $router->post('/bulk-delete', [CustomerController::class, 'bulkDelete'])->name('customers.bulk-delete');
    });

    // Kontakty
    $router->group(['prefix' => '/contacts'], function (Router $router) {
        $router->get('/', [ContactController::class, 'index'])->name('contacts.index');
        $router->get('/create', [ContactController::class, 'create'])->name('contacts.create');
        $router->post('/', [ContactController::class, 'store'])->name('contacts.store');
        $router->get('/{id}', [ContactController::class, 'show'])->name('contacts.show');
        $router->get('/{id}/edit', [ContactController::class, 'edit'])->name('contacts.edit');
        $router->post('/{id}', [ContactController::class, 'update'])->name('contacts.update');
        $router->post('/{id}/delete', [ContactController::class, 'delete'])->name('contacts.delete');
        $router->post('/bulk-delete', [ContactController::class, 'bulkDelete'])->name('contacts.bulk-delete');
    });

    // Obchody
    $router->group(['prefix' => '/deals'], function (Router $router) {
        $router->get('/', [DealController::class, 'index'])->name('deals.index');
        $router->get('/create', [DealController::class, 'create'])->name('deals.create');
        $router->get('/{id}', [DealController::class, 'show'])->name('deals.show');
        $router->get('/{id}/edit', [DealController::class, 'edit'])->name('deals.edit');
        $router->post('/', [DealController::class, 'store'])->name('deals.store');
        $router->post('/{id}', [DealController::class, 'update'])->name('deals.update');
        $router->post('/{id}/delete', [DealController::class, 'delete'])->name('deals.delete');
        $router->post('/bulk-delete', [DealController::class, 'bulkDelete'])->name('deals.bulk-delete');
    });

    // Reporty
    $router->group(['prefix' => '/reports'], function (Router $router) {
        $router->get('/', [ReportController::class, 'index'])->name('reports.index');
        $router->get('/customers', [ReportController::class, 'customers'])->name('reports.customers');
        $router->get('/deals', [ReportController::class, 'deals'])->name('reports.deals');
        $router->get('/contacts', [ReportController::class, 'contacts'])->name('reports.contacts');
    });

    // Faktury
    $router->group(['prefix' => '/invoices'], function (Router $router) {
        $router->get('/', [InvoiceController::class, 'index'])->name('invoices.index');
        $router->get('/create', [InvoiceController::class, 'create'])->name('invoices.create');
        $router->post('/', [InvoiceController::class, 'store'])->name('invoices.store');
        $router->get('/{id}', [InvoiceController::class, 'show'])->name('invoices.show');
        $router->get('/{id}/edit', [InvoiceController::class, 'edit'])->name('invoices.edit');
        $router->post('/{id}', [InvoiceController::class, 'update'])->name('invoices.update');
        $router->post('/{id}/delete', [InvoiceController::class, 'delete'])->name('invoices.delete');
        $router->get('/{id}/pdf', [InvoiceController::class, 'pdf'])->name('invoices.pdf');
    });

    // Nastavení
    $router->group(['prefix' => '/settings'], function (Router $router) {
        $router->get('/', [SettingsController::class, 'index'])->name('settings.index');
        $router->post('/', [SettingsController::class, 'update'])->name('settings.update');
        $router->get('/profile', [SettingsController::class, 'profile'])->name('settings.profile');
        $router->post('/profile', [SettingsController::class, 'updateProfile'])->name('settings.profile.update');
        $router->get('/system', [SettingsController::class, 'system'])->name('settings.system');
        $router->post('/clear-cache', [SettingsController::class, 'clearCache'])->name('settings.clear-cache');
        $router->post('/optimize-opcache', [SettingsController::class, 'optimizeOpCache'])->name('settings.optimize-opcache');
        $router->post('/create-backup', [SettingsController::class, 'createBackup'])->name('settings.create-backup');
        $router->get('/logs', [SettingsController::class, 'systemLogs'])->name('settings.logs');
        $router->post('/check-integrity', [SettingsController::class, 'checkIntegrity'])->name('settings.check-integrity');
    });

    // Moduly
    $router->group(['prefix' => '/settings/modules'], function (Router $router) {
        $router->get('/', [SettingsController::class, 'modules'])->name('settings.modules.index');
        $router->any('/{moduleName}', [SettingsController::class, 'moduleDetail'])->name('settings.modules.show');
        $router->any('/{moduleName}/install', [SettingsController::class, 'installModule'])->name('settings.modules.install');
        $router->any('/{moduleName}/uninstall', [SettingsController::class, 'uninstallModule'])->name('settings.modules.uninstall');
        $router->any('/{moduleName}/enable', [SettingsController::class, 'enableModule'])->name('settings.modules.enable');
        $router->any('/{moduleName}/disable', [SettingsController::class, 'disableModule'])->name('settings.modules.disable');
        $router->any('/sync', [SettingsController::class, 'syncModules'])->name('settings.modules.sync');
    });

    // Šablony
    $router->group(['prefix' => '/settings/templates'], function (Router $router) {
        $router->get('/{moduleName}', [TemplateController::class, 'index'])->name('settings.templates.index');
        $router->get('/{moduleName}/controller', [TemplateController::class, 'controller'])->name('settings.templates.controller');
        $router->get('/{moduleName}/entity', [TemplateController::class, 'entity'])->name('settings.templates.entity');
        $router->get('/{moduleName}/migration', [TemplateController::class, 'migration'])->name('settings.templates.migration');
        $router->get('/{moduleName}/view', [TemplateController::class, 'view'])->name('settings.templates.view');
        $router->get('/{moduleName}/translation', [TemplateController::class, 'translation'])->name('settings.templates.translation');
    });

    // Workflow
    $router->group(['prefix' => '/workflows'], function (Router $router) {
        $router->get('/', [WorkflowController::class, 'index'])->name('workflows.index');
        $router->get('/create', [WorkflowController::class, 'create'])->name('workflows.create');
        $router->post('/', [WorkflowController::class, 'store'])->name('workflows.store');
        $router->get('/{id}', [WorkflowController::class, 'show'])->name('workflows.show');
        $router->get('/{id}/edit', [WorkflowController::class, 'edit'])->name('workflows.edit');
        $router->post('/{id}', [WorkflowController::class, 'update'])->name('workflows.update');
        $router->post('/{id}/delete', [WorkflowController::class, 'delete'])->name('workflows.delete');
        $router->post('/{id}/toggle', [WorkflowController::class, 'toggle'])->name('workflows.toggle');
        $router->get('/{id}/test', [WorkflowController::class, 'test'])->name('workflows.test');
    });

    // E-maily
    $router->group(['prefix' => '/emails'], function (Router $router) {
        $router->get('/', [EmailController::class, 'index'])->name('emails.index');
        $router->get('/create', [EmailController::class, 'create'])->name('emails.create');
        $router->post('/', [EmailController::class, 'store'])->name('emails.store');
        $router->get('/{id}', [EmailController::class, 'show'])->name('emails.show');
        $router->get('/{id}/edit', [EmailController::class, 'edit'])->name('emails.edit');
        $router->post('/{id}', [EmailController::class, 'update'])->name('emails.update');
        $router->post('/{id}/delete', [EmailController::class, 'delete'])->name('emails.delete');
        $router->post('/{id}/send', [EmailController::class, 'send'])->name('emails.send');
        $router->get('/templates', [EmailController::class, 'templates'])->name('emails.templates');
        $router->get('/signatures', [EmailController::class, 'signatures'])->name('emails.signatures');
        $router->get('/servers', [EmailController::class, 'servers'])->name('emails.servers');
    });

    // Projekty
    $router->group(['prefix' => '/projects'], function (Router $router) {
        $router->get('/', [ProjectController::class, 'index'])->name('projects.index');
        $router->get('/create', [ProjectController::class, 'create'])->name('projects.create');
        $router->post('/', [ProjectController::class, 'store'])->name('projects.store');
        $router->get('/{id}', [ProjectController::class, 'show'])->name('projects.show');
        $router->get('/{id}/edit', [ProjectController::class, 'edit'])->name('projects.edit');
        $router->post('/{id}', [ProjectController::class, 'update'])->name('projects.update');
        $router->post('/{id}/delete', [ProjectController::class, 'delete'])->name('projects.delete');
        $router->get('/{id}/api', [ProjectController::class, 'apiShow'])->name('projects.api.show');
        $router->get('/mini/{slug}', [ProjectController::class, 'miniPage'])->name('projects.mini-page');
    });

    // AJAX routy pro TableUI
    $router->any('/__render', [AjaxController::class, 'render'])->name('ajax.render');

    // Demo routy pro TableUI
    $router->get('/demo/table', [DemoController::class, 'tableDemo'])->name('demo.table');
    $router->get('/demo/ajax-table', [DemoController::class, 'ajaxTableDemo'])->name('demo.ajax-table');
});


$router->get('/lang/{locale}', [LanguageController::class, 'switch'])->name('language.switch');

$router->group(['prefix' => '/api'], function (Router $router) {
    $router->get('/languages', [LanguageController::class, 'getSupportedLanguages'])->name('api.languages');
    $router->get('/languages/current', [LanguageController::class, 'getCurrentLanguage'])->name('api.languages.current');
    $router->get('/languages/missing', [LanguageController::class, 'getMissingTranslations'])->name('api.languages.missing');
    $router->get('/languages/export/{locale}', [LanguageController::class, 'export'])->name('api.languages.export');
    $router->post('/languages/import', [LanguageController::class, 'import'])->name('api.languages.import');
    $router->post('/languages/set', [LanguageController::class, 'setUserLanguage'])->name('api.languages.set');
});


// Spuštění aplikace
$request = Request::getInstance();
Container::set(Router::class, $router, ['router']);
$router->dispatch($request);
