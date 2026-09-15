<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Pos\PosDashboardController;
use App\Http\Controllers\Pos\PosApiController;

/*
|--------------------------------------------------------------------------
| POS Routes
|--------------------------------------------------------------------------
*/

// PWA Manifest (Must be outside auth to be fetched by browser seamlessly)
Route::get('/manifest.json', [PosDashboardController::class, 'manifest'])->name('manifest');

Route::middleware(['auth', 'pos_access'])->group(function () {
    Route::get('/', [PosDashboardController::class, 'index'])->name('dashboard');
    Route::get('/receipt-builder', [PosDashboardController::class, 'receiptBuilder'])->name('receipt-builder');
    Route::get('/returns', [PosDashboardController::class, 'returns'])->name('returns');
    
    // API Endpoints for the Offline React App
    Route::get('/api/locations', [PosApiController::class, 'getLocations']);
    Route::get('/api/coupons', [PosApiController::class, 'getCoupons']);
    Route::get('/api/catalog', [PosApiController::class, 'getCatalog']);
    Route::post('/api/orders', [PosApiController::class, 'submitOrder']);
    Route::get('/api/orders/search', [PosApiController::class, 'searchOrders']);
    Route::post('/api/orders/{orderId}/refund', [PosApiController::class, 'processRefund']);
});
