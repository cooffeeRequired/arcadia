<?php

use App\Controllers\Api\ApiController;
use App\Controllers\AuthController;
use App\Controllers\ContactController;
use App\Controllers\CustomerController;
use App\Controllers\DealController;
use App\Controllers\ErrorController;
use App\Controllers\HomeController;
use App\Controllers\InvoiceController;
use App\Controllers\ReportController;
use App\Controllers\SettingsController;
use Core\Routing\Router;

$router = new Router();

// Auth routes
$router->get('/login', [AuthController::class, 'showLogin']);
$router->post('/login', [AuthController::class, 'login']);
$router->get('/register', [AuthController::class, 'showRegister']);
$router->post('/register', [AuthController::class, 'register']);
$router->get('/logout', [AuthController::class, 'logout']);

// Domovská stránka
$router->get('/', [HomeController::class, 'index']);
$router->get('/home', [HomeController::class, 'index']);

// Zákazníci
$router->get('/customers', [CustomerController::class, 'index']);
$router->get('/customers/create', [CustomerController::class, 'create']);
$router->post('/customers', [CustomerController::class, 'store']);
$router->get('/customers/{id}', [CustomerController::class, 'show']);
$router->get('/customers/{id}/edit', [CustomerController::class, 'edit']);
$router->post('/customers/{id}', [CustomerController::class, 'update']);
$router->post('/customers/{id}/delete', [CustomerController::class, 'delete']);
$router->post('/customers/bulk-delete', [CustomerController::class, 'bulkDelete']);

// Kontakty
$router->get('/contacts', [ContactController::class, 'index']);
$router->get('/contacts/create', [ContactController::class, 'create']);
$router->post('/contacts', [ContactController::class, 'store']);
$router->get('/contacts/{id}', [ContactController::class, 'show']);
$router->get('/contacts/{id}/edit', [ContactController::class, 'edit']);
$router->post('/contacts/{id}', [ContactController::class, 'update']);
$router->post('/contacts/{id}/delete', [ContactController::class, 'delete']);
$router->post('/contacts/bulk-delete', [ContactController::class, 'bulkDelete']);

// Obchody
$router->get('/deals', [DealController::class, 'index']);
$router->get('/deals/create', [DealController::class, 'create']);
$router->post('/deals', [DealController::class, 'store']);
$router->get('/deals/{id}', [DealController::class, 'show']);
$router->get('/deals/{id}/edit', [DealController::class, 'edit']);
$router->post('/deals/{id}', [DealController::class, 'update']);
$router->post('/deals/{id}/delete', [DealController::class, 'delete']);
$router->post('/deals/bulk-delete', [DealController::class, 'bulkDelete']);

// Reporty
$router->get('/reports', [ReportController::class, 'index']);
$router->get('/reports/customers', [ReportController::class, 'customers']);
$router->get('/reports/deals', [ReportController::class, 'deals']);
$router->get('/reports/contacts', [ReportController::class, 'contacts']);

// Faktury
$router->get('/invoices', [InvoiceController::class, 'index']);
$router->get('/invoices/create', [InvoiceController::class, 'create']);
$router->post('/invoices', [InvoiceController::class, 'store']);
$router->get('/invoices/{id}', [InvoiceController::class, 'show']);
$router->get('/invoices/{id}/edit', [InvoiceController::class, 'edit']);
$router->post('/invoices/{id}', [InvoiceController::class, 'update']);
$router->post('/invoices/{id}/delete', [InvoiceController::class, 'delete']);
$router->get('/invoices/{id}/pdf', [InvoiceController::class, 'pdf']);

// Nastavení
$router->get('/settings', [SettingsController::class, 'index']);
$router->post('/settings', [SettingsController::class, 'update']);
$router->get('/settings/profile', [SettingsController::class, 'profile']);
$router->post('/settings/profile', [SettingsController::class, 'updateProfile']);
$router->get('/settings/system', [SettingsController::class, 'system']);
$router->post('/settings/clear-cache', [SettingsController::class, 'clearCache']);
$router->post('/settings/optimize-opcache', [SettingsController::class, 'optimizeOpCache']);
$router->post('/settings/create-backup', [SettingsController::class, 'createBackup']);
$router->get('/settings/logs', [SettingsController::class, 'systemLogs']);
$router->post('/settings/check-integrity', [SettingsController::class, 'checkIntegrity']);

// API routes
$router->get('/api/check-updates', [ApiController::class, 'checkUpdates']);

// Chybové stránky
$router->notFound([ErrorController::class, 'notFound']);
$router->serverError([ErrorController::class, 'serverError']);
$router->error(403, [ErrorController::class, 'forbidden']);

// Spuštění aplikace
$router->dispatch($_SERVER['REQUEST_URI']);