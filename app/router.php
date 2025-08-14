<?php

use App\Controllers\AuthController;
use App\Controllers\ContactController;
use App\Controllers\CustomerController;
use App\Controllers\DealController;
use App\Controllers\EmailController;
use App\Controllers\ErrorController;
use App\Controllers\HomeController;
use App\Controllers\InvoiceController;
use App\Controllers\ReportController;
use App\Controllers\SettingsController;

use App\Controllers\WorkflowController;
use Core\Routing\Router;

$router = new Router();

// Auth routes (guest middleware - dostupné nepřihlášeným)
$router->group(['middleware' => ['guest']], function (Router $router) {
    $router->get('/login', [AuthController::class, 'showLogin']);
    $router->post('/login', [AuthController::class, 'login']);
    $router->get('/register', [AuthController::class, 'showRegister']);
    $router->post('/register', [AuthController::class, 'register']);
});

// Chráněné routy (auth middleware - vyžadují přihlášení)
$router->group(['middleware' => ['auth']], function (Router $router) {
    // Logout
    $router->get('/logout', [AuthController::class, 'logout']);

    // Domovská stránka
    $router->get('/', [HomeController::class, 'index']);
    $router->get('/home', [HomeController::class, 'index']);
    $router->get('/test-external-session', [HomeController::class, 'testExternalSession']);

    // Zákazníci
    $router->group(['prefix' => '/customers'], function (Router $router) {
        $router->get('', [CustomerController::class, 'index']);
        $router->get('/create', [CustomerController::class, 'create']);
        $router->post('', [CustomerController::class, 'store']);
        $router->get('/{id}', [CustomerController::class, 'show']);
        $router->get('/{id}/edit', [CustomerController::class, 'edit']);
        $router->post('/{id}', [CustomerController::class, 'update']);
        $router->post('/{id}/delete', [CustomerController::class, 'delete']);
        $router->post('/bulk-delete', [CustomerController::class, 'bulkDelete']);
    });

    // Kontakty
    $router->group(['prefix' => '/contacts'], function (Router $router) {
        $router->get('', [ContactController::class, 'index']);
        $router->get('/create', [ContactController::class, 'create']);
        $router->post('', [ContactController::class, 'store']);
        $router->get('/{id}', [ContactController::class, 'show']);
        $router->get('/{id}/edit', [ContactController::class, 'edit']);
        $router->post('/{id}', [ContactController::class, 'update']);
        $router->post('/{id}/delete', [ContactController::class, 'delete']);
        $router->post('/bulk-delete', [ContactController::class, 'bulkDelete']);
    });

    // Obchody
    $router->group(['prefix' => '/deals'], function (Router $router) {
        $router->get('', [DealController::class, 'index']);
        $router->get('/create', [DealController::class, 'create']);
        $router->post('', [DealController::class, 'store']);
        $router->get('/{id}', [DealController::class, 'show']);
        $router->get('/{id}/edit', [DealController::class, 'edit']);
        $router->post('/{id}', [DealController::class, 'update']);
        $router->post('/{id}/delete', [DealController::class, 'delete']);
        $router->post('/bulk-delete', [DealController::class, 'bulkDelete']);
    });

    // Reporty
    $router->group(['prefix' => '/reports'], function (Router $router) {
        $router->get('', [ReportController::class, 'index']);
        $router->get('/customers', [ReportController::class, 'customers']);
        $router->get('/deals', [ReportController::class, 'deals']);
        $router->get('/contacts', [ReportController::class, 'contacts']);
    });

    // Faktury
    $router->group(['prefix' => '/invoices'], function (Router $router) {
        $router->get('', [InvoiceController::class, 'index']);
        $router->get('/create', [InvoiceController::class, 'create']);
        $router->post('', [InvoiceController::class, 'store']);
        $router->get('/{id}', [InvoiceController::class, 'show']);
        $router->get('/{id}/edit', [InvoiceController::class, 'edit']);
        $router->post('/{id}', [InvoiceController::class, 'update']);
        $router->post('/{id}/delete', [InvoiceController::class, 'delete']);
        $router->get('/{id}/pdf', [InvoiceController::class, 'pdf']);
    });

    // Nastavení
    $router->group(['prefix' => '/settings'], function (Router $router) {
        $router->get('', [SettingsController::class, 'index']);
        $router->post('', [SettingsController::class, 'update']);
        $router->get('/profile', [SettingsController::class, 'profile']);
        $router->post('/profile', [SettingsController::class, 'updateProfile']);
        $router->get('/system', [SettingsController::class, 'system']);
        $router->post('/clear-cache', [SettingsController::class, 'clearCache']);
        $router->post('/optimize-opcache', [SettingsController::class, 'optimizeOpCache']);
        $router->post('/create-backup', [SettingsController::class, 'createBackup']);
        $router->get('/logs', [SettingsController::class, 'systemLogs']);
        $router->post('/check-integrity', [SettingsController::class, 'checkIntegrity']);
    });

    // Workflow
    $router->group(['prefix' => '/workflows'], function (Router $router) {
        $router->get('', [WorkflowController::class, 'index']);
        $router->get('/create', [WorkflowController::class, 'create']);
        $router->post('', [WorkflowController::class, 'store']);
        $router->get('/{id}', [WorkflowController::class, 'show']);
        $router->get('/{id}/edit', [WorkflowController::class, 'edit']);
        $router->post('/{id}', [WorkflowController::class, 'update']);
        $router->post('/{id}/delete', [WorkflowController::class, 'delete']);
        $router->post('/{id}/toggle', [WorkflowController::class, 'toggle']);
        $router->get('/{id}/test', [WorkflowController::class, 'test']);
    });

    // E-maily
    $router->group(['prefix' => '/emails'], function (Router $router) {
        $router->get('', [EmailController::class, 'index']);
        $router->get('/create', [EmailController::class, 'create']);
        $router->post('', [EmailController::class, 'store']);
        $router->get('/{id}', [EmailController::class, 'show']);
        $router->get('/{id}/edit', [EmailController::class, 'edit']);
        $router->post('/{id}', [EmailController::class, 'update']);
        $router->post('/{id}/delete', [EmailController::class, 'delete']);
        $router->post('/{id}/send', [EmailController::class, 'send']);
        $router->get('/templates', [EmailController::class, 'templates']);
        $router->get('/signatures', [EmailController::class, 'signatures']);
        $router->get('/servers', [EmailController::class, 'servers']);
    });
});



// Chybové stránky
$router->notFound([ErrorController::class, 'notFound']);
$router->serverError([ErrorController::class, 'serverError']);
$router->error(403, [ErrorController::class, 'forbidden']);

// Spuštění aplikace
try {
    $router->dispatch($_SERVER['REQUEST_URI']);
} catch (Exception $e) {

}
