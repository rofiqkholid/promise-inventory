<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Auth;

use App\Http\Controllers\Inventory\CoilCenterController;
use App\Http\Controllers\Inventory\MaterialSpecController;
use App\Http\Controllers\Inventory\UnitController;
use App\Http\Controllers\Inventory\RankController;
use App\Http\Controllers\Inventory\SupplierController;
use App\Http\Controllers\Inventory\TransactionCategoryController;
use App\Http\Controllers\Inventory\InventoryProductController;
use App\Http\Controllers\Inventory\InventoryTransactionController;
use App\Http\Controllers\Inventory\StockMonitoringController;
use App\Http\Controllers\Inventory\TransactionHistoryController;
use App\Http\Controllers\Inventory\AutoPrController;
use App\Http\Controllers\DashboardController;

Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('dashboard');
    }
    return app(AuthController::class)->showLoginForm();
})->name('login');

Route::post('/login', [AuthController::class, 'login'])->name('login_post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
Route::get('/forget', [AuthController::class, 'forgetPassword'])->name('forget_password');

// Inventory System Routes (Role-based)
Route::middleware(['auth', 'inventory.role'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    #Region Inventory Master (Admin, Approver)
    Route::middleware(['inventory.role:admin,approver'])->group(function () {
        Route::get('/inventory/master', function () {
            return view('inventory.master-data.index');
        })->name('inventory.master');

        // Coil Center
        Route::get('/inventory/coil-center/data', [CoilCenterController::class, 'data'])->name('inventory.coilCenter.data');
        Route::prefix('inventory/master/coil-center')->name('inventory.coilCenter.')->group(function () {
            Route::post('/', [CoilCenterController::class, 'store'])->name('store');
            Route::get('/{id}', [CoilCenterController::class, 'show'])->name('show');
            Route::put('/{id}', [CoilCenterController::class, 'update'])->name('update');
            Route::delete('/{id}', [CoilCenterController::class, 'destroy'])->name('destroy');
        });

        // Material Spec
        Route::get('/inventory/material-spec/data', [MaterialSpecController::class, 'data'])->name('inventory.materialSpec.data');
        Route::resource('inventory/master/material-spec', MaterialSpecController::class)->names('inventory.materialSpec')->parameters(['material-spec' => 'materialSpec'])->except(['create', 'edit', 'index']);

        // Unit
        Route::get('/inventory/unit/data', [UnitController::class, 'data'])->name('inventory.unit.data');
        Route::resource('inventory/master/unit', UnitController::class)->names('inventory.unit')->except(['create', 'edit', 'index']);

        // Rank
        Route::get('/inventory/rank/data', [RankController::class, 'data'])->name('inventory.rank.data');
        Route::resource('inventory/master/rank', RankController::class)->names('inventory.rank')->except(['create', 'edit', 'index']);

        // Supplier
        Route::get('/inventory/supplier/data', [SupplierController::class, 'data'])->name('inventory.supplier.data');
        Route::get('/inventory/supplier/global', [SupplierController::class, 'getGlobal'])->name('inventory.supplier.getGlobal');
        Route::get('/inventory/supplier/global/{id}', [SupplierController::class, 'getGlobalDetail'])->name('inventory.supplier.getGlobalDetail');
        Route::resource('inventory/master/supplier', SupplierController::class)->names('inventory.supplier')->parameters(['supplier' => 'supplier'])->except(['create', 'edit', 'index']);

        // Transaction Category
        Route::get('/inventory/transaction-category/data', [TransactionCategoryController::class, 'data'])->name('inventory.transactionCategory.data');
        Route::resource('inventory/master/transaction-category', TransactionCategoryController::class)->names('inventory.transactionCategory')->parameters(['transaction-category' => 'transactionCategory'])->except(['create', 'edit', 'index']);



        // User Access Management
        Route::get('/inventory/user-access', [\App\Http\Controllers\Inventory\UserAccessController::class, 'index'])->name('inventory.userAccess.index');
        Route::get('/inventory/user-access/data', [\App\Http\Controllers\Inventory\UserAccessController::class, 'data'])->name('inventory.userAccess.data');
        Route::get('/inventory/user-access/search', [\App\Http\Controllers\Inventory\UserAccessController::class, 'searchUsers'])->name('inventory.userAccess.search');
        Route::get('/inventory/user-access/{id}', [\App\Http\Controllers\Inventory\UserAccessController::class, 'getUserRole'])->name('inventory.userAccess.get');
        Route::post('/inventory/user-access', [\App\Http\Controllers\Inventory\UserAccessController::class, 'store'])->name('inventory.userAccess.store');
        Route::delete('/inventory/user-access/{id}', [\App\Http\Controllers\Inventory\UserAccessController::class, 'destroy'])->name('inventory.userAccess.destroy');

        // User Specific Menu Permissions
        Route::get('/inventory/user-menus/{userId}', [\App\Http\Controllers\Inventory\UserAccessController::class, 'userMenuData'])->name('inventory.userMenus.data');
        Route::post('/inventory/user-menus', [\App\Http\Controllers\Inventory\UserAccessController::class, 'updateUserMenu'])->name('inventory.userMenus.update');

        // Role Management Extensions
        Route::get('/inventory/roles/data', [\App\Http\Controllers\Inventory\UserAccessController::class, 'roleData'])->name('inventory.roles.data');
        Route::get('/inventory/roles/{id}', [\App\Http\Controllers\Inventory\UserAccessController::class, 'getRole'])->name('inventory.roles.get');
        Route::post('/inventory/roles', [\App\Http\Controllers\Inventory\UserAccessController::class, 'storeRole'])->name('inventory.roles.store');
        Route::delete('/inventory/roles/{id}', [\App\Http\Controllers\Inventory\UserAccessController::class, 'destroyRole'])->name('inventory.roles.destroy');
        
        // Role Menu Permissions
        Route::get('/inventory/role-menus/{roleId}', [\App\Http\Controllers\Inventory\UserAccessController::class, 'roleMenuData'])->name('inventory.roleMenus.data');
        Route::post('/inventory/role-menus', [\App\Http\Controllers\Inventory\UserAccessController::class, 'updateRoleMenu'])->name('inventory.roleMenus.update');

        // User Management Extensions
        Route::get('/inventory/users/data', [\App\Http\Controllers\Inventory\UserAccessController::class, 'userData'])->name('inventory.users.data');
        Route::get('/inventory/users/{id}', [\App\Http\Controllers\Inventory\UserAccessController::class, 'getUser'])->name('inventory.users.get');
        Route::post('/inventory/users', [\App\Http\Controllers\Inventory\UserAccessController::class, 'storeUser'])->name('inventory.users.store');
        Route::delete('/inventory/users/{id}', [\App\Http\Controllers\Inventory\UserAccessController::class, 'destroyUser'])->name('inventory.users.destroy');

        // Menu Management Extensions
        Route::get('/inventory/menus/data', [\App\Http\Controllers\Inventory\UserAccessController::class, 'menuData'])->name('inventory.menus.data');
        Route::get('/inventory/menus/{id}', [\App\Http\Controllers\Inventory\UserAccessController::class, 'getMenu'])->name('inventory.menus.get');
        Route::post('/inventory/menus', [\App\Http\Controllers\Inventory\UserAccessController::class, 'storeMenu'])->name('inventory.menus.store');
        Route::delete('/inventory/menus/{id}', [\App\Http\Controllers\Inventory\UserAccessController::class, 'destroyMenu'])->name('inventory.menus.destroy');
    });
    #Endregion

    // Inventory Product (All Roles)
    Route::get('/inventory/product', [InventoryProductController::class, 'index'])->name('inventory.product');
    Route::get('/inventory/product/data', [InventoryProductController::class, 'data'])->name('inventory.product.data');
    Route::get('/inventory/product/dropdown-data', [InventoryProductController::class, 'getDropdownData'])->name('inventory.product.dropdownData');
    Route::get('/inventory/product/get-products', [InventoryProductController::class, 'getProducts'])->name('inventory.product.getProducts');
    Route::get('/inventory/product/get-customer', [InventoryProductController::class, 'getCustomers'])->name('inventory.product.getCustomers');
    Route::get('/inventory/product/get-model', [InventoryProductController::class, 'getModels'])->name('inventory.product.getModels');
    Route::get('/inventory/product/used-revisions/{productId}', [InventoryProductController::class, 'getUsedRevisions'])->name('inventory.product.usedRevisions');
    Route::get('/inventory/product/{inventoryProduct}/print', [InventoryProductController::class, 'printLabel'])->name('inventory.product.print');
    Route::resource('inventory/product', InventoryProductController::class)->names('inventory.product')->parameters(['product' => 'inventoryProduct'])->except(['create', 'edit', 'index']);

    // Inventory Transaction (Admin, Approver, Operator)
    Route::middleware(['inventory.role:admin,approver,operator'])->group(function () {
        Route::get('/inventory/transaction', [InventoryTransactionController::class, 'index'])->name('inventory.transaction');
        Route::get('/inventory/transaction/data', [InventoryTransactionController::class, 'data'])->name('inventory.transaction.data');
        Route::post('/inventory/transaction/store', [InventoryTransactionController::class, 'store'])->name('inventory.transaction.store');
        Route::get('/inventory/transaction/categories', [InventoryTransactionController::class, 'getCategories'])->name('inventory.transaction.categories');
    });

    // Stock Monitoring (All Roles)
    Route::get('/inventory/stock-monitoring', [StockMonitoringController::class, 'index'])->name('inventory.stockMonitoring');
    Route::get('/inventory/stock-monitoring/data', [StockMonitoringController::class, 'data'])->name('inventory.stockMonitoring.data');
    Route::get('/inventory/stock-monitoring/log/{id}', [StockMonitoringController::class, 'getStoLog'])->name('inventory.stockMonitoring.getStoLog');
    Route::get('/inventory/stock-monitoring/{inventoryProduct}/print-balance', [StockMonitoringController::class, 'printBalanceLabel'])->name('inventory.stockMonitoring.printBalance');

    // Stock Opname (STO) (Admin, Approver, Checker, Operator)
    Route::middleware(['inventory.role:admin,approver,checker,operator'])->prefix('inventory/sto')->name('inventory.sto.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Inventory\StoController::class, 'index'])->name('index');
        Route::post('/', [\App\Http\Controllers\Inventory\StoController::class, 'store'])->name('store');
        Route::get('/{id}', [\App\Http\Controllers\Inventory\StoController::class, 'show'])->name('show');
        Route::get('/{id}/details-data', [\App\Http\Controllers\Inventory\StoController::class, 'detailsData'])->name('detailsData');
        Route::post('/{id}/scan', [\App\Http\Controllers\Inventory\StoController::class, 'scan'])->name('scan');
        Route::post('/{id}/save-count', [\App\Http\Controllers\Inventory\StoController::class, 'saveCount'])->name('saveCount');
        Route::delete('/{id}/detail/{detailId}', [\App\Http\Controllers\Inventory\StoController::class, 'deleteDetail'])->name('deleteDetail');
        Route::post('/{id}/submit-for-check', [\App\Http\Controllers\Inventory\StoController::class, 'submitForCheck'])->name('submitForCheck');
        Route::post('/{id}/verify', [\App\Http\Controllers\Inventory\StoController::class, 'verify'])->name('verify');
        Route::post('/{id}/reject', [\App\Http\Controllers\Inventory\StoController::class, 'reject'])->name('reject');
        Route::post('/{id}/finalize', [\App\Http\Controllers\Inventory\StoController::class, 'finalize'])->name('finalize');
        Route::post('/{id}/reopen', [\App\Http\Controllers\Inventory\StoController::class, 'reopen'])->name('reopen');
        Route::get('/{id}/export-excel', [\App\Http\Controllers\Inventory\StoController::class, 'exportExcel'])->name('exportExcel');
    });

    // VAVE Analysis (Admin, Approver, Checker, Viewer)
    Route::middleware(['inventory.role:admin,approver,checker,viewer'])->prefix('inventory/vave')->name('inventory.vave.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Inventory\VaveAnalysisController::class, 'index'])->name('index');
        Route::get('/data', [\App\Http\Controllers\Inventory\VaveAnalysisController::class, 'data'])->name('data');
        Route::get('/rfq/{id}', [\App\Http\Controllers\Inventory\VaveAnalysisController::class, 'showRfq'])->name('showRfq');
        Route::post('/rfq', [\App\Http\Controllers\Inventory\VaveAnalysisController::class, 'storeRfq'])->name('storeRfq');
        Route::get('/comparison/{id}', [\App\Http\Controllers\Inventory\VaveAnalysisController::class, 'getComparison'])->name('getComparison');
        Route::get('/comparison/{id}/export', [\App\Http\Controllers\Inventory\VaveAnalysisController::class, 'exportExcel'])->name('export');
        Route::get('/summary-export', [\App\Http\Controllers\Inventory\VaveAnalysisController::class, 'exportSummary'])->name('exportSummary');
        Route::delete('/rfq/{id}', [\App\Http\Controllers\Inventory\VaveAnalysisController::class, 'destroyRfq'])->name('destroyRfq');
    });

    // Auto PR (Admin, Approver, Checker)
    Route::middleware(['inventory.role:admin,approver,checker'])->prefix('inventory/auto-pr')->name('inventory.autoPr.')->group(function () {
        Route::get('/', [AutoPrController::class, 'index'])->name('index');
        Route::get('/data', [AutoPrController::class, 'data'])->name('data');
    });

    // Transaction History (Admin, Approver, Checker)
    Route::middleware(['inventory.role:admin,approver,checker'])->group(function () {
        Route::get('transaction-history', [TransactionHistoryController::class, 'index'])->name('transactionHistory');
        Route::get('transaction-history/getData', [TransactionHistoryController::class, 'getData'])->name('transactionHistory.getData');
        Route::get('transaction-history/{id}/edit', [TransactionHistoryController::class, 'edit'])->name('transactionHistory.edit');
        Route::put('transaction-history/{id}', [TransactionHistoryController::class, 'update'])->name('transactionHistory.update');
    });

});
# endregion

#Region Dashboard API
Route::post('/api/data/models', [DashboardController::class, 'getModels'])->name('api.data.models');
Route::post('/api/data/customers', [DashboardController::class, 'getCustomers'])->name('api.data.customers');
Route::get('/api/data/statuses/{type}', [DashboardController::class, 'getStatuses'])->name('api.data.statuses');
#End region