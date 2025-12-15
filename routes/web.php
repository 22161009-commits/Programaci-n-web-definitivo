<?php

use App\Http\Controllers\ProductController;
use App\Http\Controllers\SaleController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Rutas para Productos
Route::resource('products', ProductController::class);

// Rutas para Ventas
// Rutas específicas primero (antes de las rutas con parámetros)
Route::get('/sales', [SaleController::class, 'index'])->name('sales.index');
Route::get('/sales/pos', [SaleController::class, 'pos'])->name('sales.pos');
Route::get('/sales/search', [SaleController::class, 'searchProducts'])->name('sales.search');
Route::post('/sales', [SaleController::class, 'store'])->name('sales.store');
// Rutas con parámetro al final
Route::get('/sales/{sale}', [SaleController::class, 'show'])->name('sales.show');
Route::get('/sales/{sale}/ticket', [SaleController::class, 'ticket'])->name('sales.ticket');
