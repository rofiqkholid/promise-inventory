<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Auth;

use App\Http\Controllers\Inventory\CoilCenterController;
use App\Http\Controllers\Inventory\MaterialSpecController;
use App\Http\Controllers\Inventory\UnitController;
use App\Http\Controllers\Inventory\RankController;
use App\Http\Controllers\Inventory\SubContractorController;
use App\Http\Controllers\Inventory\TransactionCategoryController;
use App\Http\Controllers\Inventory\PICController;
use App\Http\Controllers\Inventory\InventoryProductController;
use App\Http\Controllers\Inventory\InventoryTransactionController;
use App\Http\Controllers\Inventory\StockMonitoringController;

Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('dashboard');
    }
    return app(AuthController::class)->showLoginForm();
})->name('login');

Route::post('/login', [AuthController::class, 'login'])->name('login_post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Protected Routes
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');
});


#Region Inventory Master
Route::get('/inventory/master', function () {
    return view('inventory.inventory_master');
})->middleware(['auth'])->name('inventory.master');

// Coil Center
Route::get('/inventory/coil-center/data', [CoilCenterController::class, 'data'])->name('inventory.coilCenter.data');
Route::resource('inventory/master/coil-center', CoilCenterController::class)->names('inventory.coilCenter')->except(['create', 'edit', 'index']);

// Material Spec
Route::get('/inventory/material-spec/data', [MaterialSpecController::class, 'data'])->name('inventory.materialSpec.data');
Route::resource('inventory/master/material-spec', MaterialSpecController::class)->names('inventory.materialSpec')->except(['create', 'edit', 'index']);

// Unit
Route::get('/inventory/unit/data', [UnitController::class, 'data'])->name('inventory.unit.data');
Route::resource('inventory/master/unit', UnitController::class)->names('inventory.unit')->except(['create', 'edit', 'index']);

// Rank
Route::get('/inventory/rank/data', [RankController::class, 'data'])->name('inventory.rank.data');
Route::resource('inventory/master/rank', RankController::class)->names('inventory.rank')->except(['create', 'edit', 'index']);

// Sub Contractor
Route::get('/inventory/sub-contractor/data', [SubContractorController::class, 'data'])->name('inventory.subContractor.data');
Route::resource('inventory/master/sub-contractor', SubContractorController::class)->names('inventory.subContractor')->except(['create', 'edit', 'index']);

// Transaction Category
Route::get('/inventory/transaction-category/data', [TransactionCategoryController::class, 'data'])->name('inventory.transactionCategory.data');
Route::resource('inventory/master/transaction-category', TransactionCategoryController::class)->names('inventory.transactionCategory')->except(['create', 'edit', 'index']);

// PIC
Route::get('/inventory/pic/data', [PICController::class, 'data'])->name('inventory.pic.data');
Route::resource('inventory/master/pic', PICController::class)->names('inventory.pic')->except(['create', 'edit', 'index']);

// Inventory Product
Route::get('/inventory/product', [InventoryProductController::class, 'index'])->name('inventory.product');
Route::get('/inventory/product/data', [InventoryProductController::class, 'data'])->name('inventory.product.data');
Route::get('/inventory/product/dropdown-data', [InventoryProductController::class, 'getDropdownData'])->name('inventory.product.dropdownData');
Route::get('/inventory/product/get-products', [InventoryProductController::class, 'getProducts'])->name('inventory.product.getProducts');
Route::resource('inventory/product', InventoryProductController::class)->names('inventory.product')->parameters(['product' => 'inventoryProduct'])->except(['create', 'edit', 'index']);

// Inventory Transaction
Route::get('/inventory/transaction', [InventoryTransactionController::class, 'index'])->name('inventory.transaction');
Route::get('/inventory/transaction/data', [InventoryTransactionController::class, 'data'])->name('inventory.transaction.data');
Route::post('/inventory/transaction/store', [InventoryTransactionController::class, 'store'])->name('inventory.transaction.store');
Route::get('/inventory/transaction/categories', [InventoryTransactionController::class, 'getCategories'])->name('inventory.transaction.categories');

// Stock Monitoring
Route::get('/inventory/stock-monitoring', [StockMonitoringController::class, 'index'])->name('inventory.stockMonitoring');
Route::get('/inventory/stock-monitoring/data', [StockMonitoringController::class, 'data'])->name('inventory.stockMonitoring.data');
#End region