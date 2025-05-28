<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\SaleController;
use App\Http\Controllers\Admin\ClientController;
use App\Http\Controllers\Admin\BrandController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\SpecificationController;
use App\Http\Controllers\Admin\SizeController;
use App\Http\Controllers\Admin\QuickSaleController; // Changed from Client to Admin
use App\Http\Controllers\Client\SaleController as ClientSaleController;
use App\Http\Controllers\HomeController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('welcome');
});

// Authentication routes
Auth::routes();

// Redirect after login based on user role
Route::get('/home', [HomeController::class, 'index'])->name('home');

// Admin routes with proper namespace and named route group
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    // Dashboard
    Route::get('/', function () {
        return view('admin.dashboard');
    })->name('dashboard');

    Route::resource('brands', BrandController::class);
    Route::resource('categories', CategoryController::class);
    Route::resource('specifications', SpecificationController::class);
    Route::resource('sizes', SizeController::class);

    // Status summary route (must come before sales resource)
    Route::get('sales/status-summary', function() {
        $statusCounts = \App\Models\Sale::select('status')
            ->selectRaw('count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        return response()->json($statusCounts);
    })->name('sales.status-summary');

    // Products autocomplete (must come before products resource)
    Route::get('products/autocomplete', [ProductController::class, 'autocomplete'])->name('products.autocomplete');

    // Invoice generation (must come before sales resource)
    Route::get('sales/{sale}/invoice', [SaleController::class, 'generateInvoice'])->name('sales.invoice');

    // Quick Sales routes - now with correct naming convention and controller reference
    Route::get('quicksales/create', [QuickSaleController::class, 'create'])->name('quicksales.create');
    Route::post('quicksales', [QuickSaleController::class, 'store'])->name('quicksales.store');

    // Resource routes
    Route::resource('products', ProductController::class);
    Route::resource('sales', SaleController::class);
    Route::resource('clients', ClientController::class);
});

// Client routes with proper namespace and naming
Route::middleware(['auth', 'client'])->prefix('client')->name('client.')->group(function () {
    // Dashboard
    Route::get('/', function () {
        return view('client.dashboard');
    })->name('dashboard');

    // Sales routes for clients
    Route::get('sales', [ClientSaleController::class, 'index'])->name('sales.index');
    Route::get('sales/{sale}', [ClientSaleController::class, 'show'])->name('sales.show');
});
