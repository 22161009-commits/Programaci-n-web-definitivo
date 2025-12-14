<?php

use App\Http\Controllers\ProductController;
use App\Http\Controllers\SaleController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Rutas para Productos
Route::resource('products', ProductController::class);

// Rutas para POS (Punto de Venta)
// Rutas específicas primero (antes de las rutas con parámetros)
Route::get('/sales/pos', [SaleController::class, 'pos'])->name('sales.pos');
Route::get('/sales/search', [SaleController::class, 'searchProducts'])->name('sales.search');
Route::post('/sales', [SaleController::class, 'store'])->name('sales.store');
// Ruta con parámetro al final
Route::get('/sales/{sale}/ticket', [SaleController::class, 'ticket'])->name('sales.ticket');
