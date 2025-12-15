<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProviderController;
use App\Http\Controllers\SaleController;
use Illuminate\Support\Facades\Route;

// Rutas públicas (sin autenticación)
Route::get('/', function () {
    return redirect()->route('login');
});

// Rutas de autenticación (solo para usuarios no autenticados)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
    Route::get('/register-admin', [AuthController::class, 'showRegisterAdminForm'])->name('register.admin');
    Route::post('/register-admin', [AuthController::class, 'registerAdmin'])->name('register.admin.store');
});

// Rutas protegidas (requieren autenticación)
Route::middleware('auth')->group(function () {
    // Dashboard
    Route::get('/dashboard', [AuthController::class, 'dashboard'])->name('dashboard');
    
    // Logout
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    
    // Rutas para Ventas (accesibles para admin y vendedor)
    Route::get('/sales', [SaleController::class, 'index'])->name('sales.index');
    Route::get('/sales/pos', [SaleController::class, 'pos'])->name('sales.pos');
    Route::get('/sales/search', [SaleController::class, 'searchProducts'])->name('sales.search');
    Route::post('/sales', [SaleController::class, 'store'])->name('sales.store');
    Route::get('/sales/{sale}', [SaleController::class, 'show'])->name('sales.show');
    Route::get('/sales/{sale}/ticket', [SaleController::class, 'ticket'])->name('sales.ticket');
    
    // Rutas solo para Administradores
    Route::middleware('role:admin')->group(function () {
        // Rutas para Productos
        Route::resource('products', ProductController::class);
        
        // Rutas para Proveedores
        Route::resource('providers', ProviderController::class);
    });
});
