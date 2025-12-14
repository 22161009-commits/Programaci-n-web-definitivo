<?php

use App\Http\Controllers\SaleController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Rutas para POS (Punto de Venta)
Route::get('/sales/pos', [SaleController::class, 'pos'])->name('sales.pos');
Route::get('/sales/search', [SaleController::class, 'searchProducts'])->name('sales.search');
Route::post('/sales', [SaleController::class, 'store'])->name('sales.store');
