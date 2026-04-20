<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Auth;

use App\Http\Controllers\Inventory\Material\CoilCenterController;
use App\Http\Controllers\Inventory\Material\MaterialSpecController;
use App\Http\Controllers\Inventory\Material\TransactionCategoryController;
use App\Http\Controllers\Inventory\Material\InventoryProductController;
use App\Http\Controllers\Inventory\Material\InventoryTransactionController;
use App\Http\Controllers\Inventory\Material\StockMonitoringController;
use App\Http\Controllers\Inventory\Material\TransactionHistoryController;
use App\Http\Controllers\Inventory\Material\PurchaseRequisitionController;
use App\Http\Controllers\Inventory\Material\ModelConfigController;
use App\Http\Controllers\Inventory\Material\DashboardController;
use App\Http\Controllers\Inventory\Material\StoController;
use App\Http\Controllers\Inventory\Material\VaveAnalysisController;
use App\Http\Controllers\Inventory\Material\RevisionController;
use App\Http\Controllers\Inventory\Material\VaveBaseSuffixController;

use App\Http\Controllers\Inventory\Tool\ToolDashboardController;
use App\Http\Controllers\Inventory\Tool\ToolCategoryController;
use App\Http\Controllers\Inventory\Tool\ToolMasterController;

use App\Http\Controllers\Inventory\Material\UnitController;
use App\Http\Controllers\Inventory\Material\RankController;
use App\Http\Controllers\Inventory\Material\SupplierController;
use App\Http\Controllers\Inventory\Material\LocationController;
use App\Http\Controllers\Inventory\ProfileController;
use App\Http\Controllers\Inventory\UserAccessController;

// Route for redirecting to Central SSO Portal
Route::get('/debug-sso', function () {
    return [
        'app' => 'inventory',
        'session_id' => session()->getId(),
        'cookie_val' => request()->cookie('promise_auth_session'),
        'auth_check' => Auth::check(),
        'user_id' => Auth::id(),
        'sessions_in_db' => DB::table('sessions')->where('id', session()->getId())->first(),
    ];
});

Route::get('/login', function () {
    return redirect(env('PORTAL_LOGIN_URL', 'https://promise.summitadyawinsa.co.id/login'));
})->name('login');

Route::get('/', function () {
    \Log::info('Inventory SSO Check', [
        'session_id' => session()->getId(),
        'cookie_val' => request()->cookie('promise_auth_session'),
        'auth_check' => Auth::check(),
        'user_id' => Auth::id(),
    ]);
    if (Auth::check()) {
        return redirect()->route('dashboard');
    }
    return redirect()->route('login');
});

Route::post('/login', function () { return redirect()->route('login'); })->name('login_post');
Route::post('/logout', function () { 
    Auth::logout();
    session()->invalidate();
    return redirect(env('PORTAL_LOGIN_URL', 'https://promise.summitadyawinsa.co.id/dev/login'));
})->name('logout');

// Public Scan Info (No Login Required)
Route::get('/inventory/stock-monitoring/scan-info/{id}', [StockMonitoringController::class, 'scanInfo'])->name('inventory.scanInfo');

// Inventory System Routes (Role-based)
Route::middleware(['auth', 'inventory.role'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Profile Routes
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile.index');
    Route::post('/profile/update', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/update-password', [ProfileController::class, 'updatePassword'])->name('profile.updatePassword');

    #Region Inventory Master (Admin, Approver)
    Route::middleware(['inventory.role:admin,approver,checker,operator,viewer,pic'])->group(function () {
        // Master Data Grouped Routes
        Route::prefix('inventory/master')->name('inventory.master.')->group(function () {
            // Monolithic /inventory/master redirected to first child (Product)
            Route::get('/', function () {
                return redirect()->route('inventory.master.product.index');
            })->name('index');

            // Product (Master Data)
            Route::get('/product', [InventoryProductController::class, 'index'])->name('product.index');
                        Route::get('/product/data', [InventoryProductController::class, 'data'])->name('product.data');
            Route::get('/product/export-excel', [InventoryProductController::class, 'exportExcel'])->name('product.exportExcel');
            Route::get('/product/download-template', [InventoryProductController::class, 'downloadTemplate'])->name('product.downloadTemplate');
            Route::match(['post', 'put'], '/product/import-data', [InventoryProductController::class, 'importExcel'])->name('product.importExcel');
            Route::match(['post', 'put'], '/product/read-sheets', [InventoryProductController::class, 'getSheetNames'])->name('product.getSheetNames');
            Route::get('/product/dropdown-data', [InventoryProductController::class, 'getDropdownData'])->name('product.dropdownData');
            Route::get('/product/get-products', [InventoryProductController::class, 'getProducts'])->name('product.getProducts');
            Route::get('/product/get-customer', [InventoryProductController::class, 'getCustomers'])->name('product.getCustomers');
            Route::get('/product/get-model', [InventoryProductController::class, 'getModels'])->name('product.getModels');
            Route::get('/product/latest-revision/{productId}', [InventoryProductController::class, 'getLatestRevision'])->name('product.latestRevision');
            Route::get('/product/{inventoryProduct}/print', [InventoryProductController::class, 'printLabel'])->name('product.print');
            Route::resource('product', InventoryProductController::class)->names('product')->parameters(['product' => 'inventoryProduct'])->except(['create', 'edit', 'index']);

            // Coil Center
            Route::get('/coil-center', [CoilCenterController::class, 'index'])->name('coilCenter.index');
            Route::get('/coil-center/data', [CoilCenterController::class, 'data'])->name('coilCenter.data');
            Route::get('/coil-center/{id}', [CoilCenterController::class, 'show'])->name('coilCenter.show');
            Route::post('/coil-center', [CoilCenterController::class, 'store'])->name('coilCenter.store');
            Route::put('/coil-center/{id}', [CoilCenterController::class, 'update'])->name('coilCenter.update');
            Route::delete('/coil-center/{id}', [CoilCenterController::class, 'destroy'])->name('coilCenter.destroy');

            // Material Spec
            Route::get('/material-spec', [MaterialSpecController::class, 'index'])->name('materialSpec.index');
            Route::get('/material-spec/data', [MaterialSpecController::class, 'data'])->name('materialSpec.data');
            Route::resource('material-spec', MaterialSpecController::class)->names('materialSpec')->parameters(['material-spec' => 'materialSpec'])->except(['create', 'edit', 'index']);

            // Unit
            Route::get('/unit', [UnitController::class, 'index'])->name('unit.index');
            Route::get('/unit/data', [UnitController::class, 'data'])->name('unit.data');
            Route::resource('unit', UnitController::class)->names('unit')->except(['create', 'edit', 'index']);

            // Rank
            Route::get('/rank', [RankController::class, 'index'])->name('rank.index');
            Route::get('/rank/data', [RankController::class, 'data'])->name('rank.data');
            Route::resource('rank', RankController::class)->names('rank')->except(['create', 'edit', 'index']);

            // Supplier
            Route::get('/supplier', [SupplierController::class, 'index'])->name('supplier.index');
            Route::get('/supplier/data', [SupplierController::class, 'data'])->name('supplier.data');
            Route::get('/supplier/global', [SupplierController::class, 'getGlobal'])->name('supplier.getGlobal');
            Route::get('/supplier/global/{id}', [SupplierController::class, 'getGlobalDetail'])->name('supplier.getGlobalDetail');
            Route::resource('supplier', SupplierController::class)->names('supplier')->parameters(['supplier' => 'supplier'])->except(['create', 'edit', 'index']);

            // Transaction Category
            Route::get('/transaction-category', [TransactionCategoryController::class, 'index'])->name('transactionCategory.index');
            Route::get('/transaction-category/data', [TransactionCategoryController::class, 'data'])->name('transactionCategory.data');
            Route::resource('transaction-category', TransactionCategoryController::class)->names('transactionCategory')->parameters(['transaction-category' => 'transactionCategory'])->except(['create', 'edit', 'index']);

            // Model Config (Bulk Settings)
            Route::get('/model-config', [ModelConfigController::class, 'index'])->name('modelConfig.index');
            Route::get('/model-config/data', [ModelConfigController::class, 'data'])->name('modelConfig.data');
            Route::post('/model-config/update-status', [ModelConfigController::class, 'updateStatus'])->name('modelConfig.updateStatus');

            // Location (Master Data)
            Route::get('/location', [LocationController::class, 'index'])->name('location.index');
            Route::get('/location/data', [LocationController::class, 'data'])->name('location.data');
            Route::resource('location', LocationController::class)->names('location')->except(['create', 'edit', 'index']);

            // Revision (Master Data)
            Route::get('/revision', [RevisionController::class, 'index'])->name('revision.index');
            Route::get('/revision/data', [RevisionController::class, 'data'])->name('revision.data');
            Route::resource('revision', RevisionController::class)->names('revision')->except(['create', 'edit', 'index']);

            // Vave Base Suffix (Master Data)
            Route::get('/vave-base-suffix', [VaveBaseSuffixController::class, 'index'])->name('vave-base-suffix.index');
            Route::get('/vave-base-suffix/data', [VaveBaseSuffixController::class, 'data'])->name('vave-base-suffix.data');
            Route::resource('vave-base-suffix', VaveBaseSuffixController::class)->names('vave-base-suffix')->except(['create', 'edit', 'index']);
        });

        // Tool Master Data Grouped Routes
        Route::prefix('inventory/tool')->name('inventory.tool.')->group(function () {
            // Dashboard
            Route::get('/dashboard', [ToolDashboardController::class, 'index'])->name('dashboard');
            
            // Category
            Route::resource('category', ToolCategoryController::class)->except(['create', 'edit', 'show']);
            // Master Specification
            Route::resource('master', ToolMasterController::class)->except(['create', 'edit', 'show']);
        });

        // User Access Management
        Route::get('/inventory/user-access', [UserAccessController::class, 'index'])->name('inventory.userAccess.index');
        Route::get('/inventory/user-access/data', [UserAccessController::class, 'data'])->name('inventory.userAccess.data');
        Route::get('/inventory/user-access/search', [UserAccessController::class, 'searchUsers'])->name('inventory.userAccess.search');
        Route::get('/inventory/user-access/{id}', [UserAccessController::class, 'getUserRole'])->name('inventory.userAccess.get');
        Route::post('/inventory/user-access', [UserAccessController::class, 'store'])->name('inventory.userAccess.store');
        Route::delete('/inventory/user-access/{id}', [UserAccessController::class, 'destroy'])->name('inventory.userAccess.destroy');

        // User Specific Menu Permissions
        Route::get('/inventory/user-menus/{userId}', [UserAccessController::class, 'userMenuData'])->name('inventory.userMenus.data');
        Route::post('/inventory/user-menus', [UserAccessController::class, 'updateUserMenu'])->name('inventory.userMenus.update');

        // Role Management Extensions
        Route::get('/inventory/roles/data', [UserAccessController::class, 'roleData'])->name('inventory.roles.data');
        Route::get('/inventory/roles/{id}', [UserAccessController::class, 'getRole'])->name('inventory.roles.get');
        Route::post('/inventory/roles', [UserAccessController::class, 'storeRole'])->name('inventory.roles.store');
        Route::delete('/inventory/roles/{id}', [UserAccessController::class, 'destroyRole'])->name('inventory.roles.destroy');
        
        // Role Menu Permissions
        Route::get('/inventory/role-menus/{roleId}', [UserAccessController::class, 'roleMenuData'])->name('inventory.roleMenus.data');
        Route::post('/inventory/role-menus', [UserAccessController::class, 'updateRoleMenu'])->name('inventory.roleMenus.update');

        // User Management Extensions
        Route::get('/inventory/users/data', [UserAccessController::class, 'userData'])->name('inventory.users.data');
        Route::get('/inventory/users/{id}', [UserAccessController::class, 'getUser'])->name('inventory.users.get');
        Route::post('/inventory/users', [UserAccessController::class, 'storeUser'])->name('inventory.users.store');
        Route::delete('/inventory/users/{id}', [UserAccessController::class, 'destroyUser'])->name('inventory.users.destroy');

        // Menu Management Extensions
        Route::get('/inventory/menus/data', [UserAccessController::class, 'menuData'])->name('inventory.menus.data');
        Route::get('/inventory/menus/{id}', [UserAccessController::class, 'getMenu'])->name('inventory.menus.get');
        Route::post('/inventory/menus', [UserAccessController::class, 'storeMenu'])->name('inventory.menus.store');
        Route::delete('/inventory/menus/{id}', [UserAccessController::class, 'destroyMenu'])->name('inventory.menus.destroy');
    });
    #Endregion


    // Inventory Transaction (Admin, Approver, Operator)
    Route::middleware(['inventory.role:admin,approver,operator'])->group(function () {
        Route::get('/inventory/transaction', [InventoryTransactionController::class, 'index'])->name('inventory.transaction');
        Route::get('/inventory/transaction/data', [InventoryTransactionController::class, 'data'])->name('inventory.transaction.data');
        Route::post('/inventory/transaction/store', [InventoryTransactionController::class, 'store'])->name('inventory.transaction.store');
        Route::get('/inventory/transaction/categories', [InventoryTransactionController::class, 'getCategories'])->name('inventory.transaction.categories');
        Route::get('/inventory/transaction/{id}/edit', [InventoryTransactionController::class, 'edit'])->name('inventory.transaction.edit');
        Route::put('/inventory/transaction/{id}', [InventoryTransactionController::class, 'update'])->name('inventory.transaction.update');
        Route::delete('/inventory/transaction/{id}', [InventoryTransactionController::class, 'destroy'])->name('inventory.transaction.destroy');
    });

    // Stock Monitoring (All Roles)
    Route::get('/inventory/stock-monitoring', [StockMonitoringController::class, 'index'])->name('inventory.stockMonitoring');
    Route::get('/inventory/stock-monitoring/data', [StockMonitoringController::class, 'data'])->name('inventory.stockMonitoring.data');
    Route::get('/inventory/stock-monitoring/export-excel', [StockMonitoringController::class, 'exportExcel'])->name('inventory.stockMonitoring.exportExcel');
    Route::get('/inventory/stock-monitoring/log/{id}', [StockMonitoringController::class, 'getStoLog'])->name('inventory.stockMonitoring.getStoLog');
    Route::get('/inventory/stock-monitoring/{inventoryProduct}/print-balance', [StockMonitoringController::class, 'printBalanceLabel'])->name('inventory.stockMonitoring.printBalance');

    // Stock Opname (STO) (Admin, Approver, Checker, Operator)
    Route::middleware(['inventory.role:admin,approver,checker,operator,pic'])->prefix('inventory/sto')->name('inventory.sto.')->group(function () {
        Route::get('/', [StoController::class, 'index'])->name('index');
        Route::get('/get-preview-code', [StoController::class, 'previewCode'])->name('previewCode');
        Route::post('/', [StoController::class, 'store'])->name('store');
        Route::get('/{id}', [StoController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [StoController::class, 'edit'])->name('edit');
        Route::put('/{id}', [StoController::class, 'update'])->name('update');
        Route::delete('/{id}', [StoController::class, 'destroy'])->name('destroy');
        Route::get('/{id}/details-data', [StoController::class, 'detailsData'])->name('detailsData');
        Route::post('/{id}/scan', [StoController::class, 'scan'])->name('scan');
        Route::post('/{id}/save-count', [StoController::class, 'saveCount'])->name('saveCount');
        Route::delete('/{id}/detail/{detailId}', [StoController::class, 'deleteDetail'])->name('deleteDetail');
        Route::post('/{id}/submit-for-check', [StoController::class, 'submitForCheck'])->name('submitForCheck');
        Route::post('/{id}/verify', [StoController::class, 'verify'])->name('verify');
        Route::post('/{id}/reject', [StoController::class, 'reject'])->name('reject');
        Route::post('/{id}/finalize', [StoController::class, 'finalize'])->name('finalize');
        Route::post('/{id}/reopen', [StoController::class, 'reopen'])->name('reopen');
        Route::get('/{id}/export-excel', [StoController::class, 'exportExcel'])->name('exportExcel');
    });

    // VAVE Analysis (Admin, Approver, Checker, Viewer)
    Route::middleware(['inventory.role:admin,approver,checker,viewer'])->prefix('inventory/vave')->name('inventory.vave.')->group(function () {
        Route::get('/', [VaveAnalysisController::class, 'index'])->name('index');
        Route::get('/data', [VaveAnalysisController::class, 'data'])->name('data');
        Route::get('/base/{id}', [VaveAnalysisController::class, 'showBase'])->name('showBase');
        Route::post('/base', [VaveAnalysisController::class, 'storeBase'])->name('storeBase');
        Route::get('/comparison/{id}', [VaveAnalysisController::class, 'getComparison'])->name('getComparison');
        Route::get('/comparison/{id}/export', [VaveAnalysisController::class, 'exportExcel'])->name('export');
        Route::get('/summary-export', [VaveAnalysisController::class, 'exportSummary'])->name('exportSummary');
        Route::get('/get-bases', [VaveAnalysisController::class, 'getBases'])->name('getBases');
        Route::get('/download-template', [VaveAnalysisController::class, 'downloadTemplate'])->name('downloadTemplate');
        Route::match(['post', 'put'], '/import-data', [VaveAnalysisController::class, 'importExcel'])->name('importExcel');
        Route::delete('/base/{id}', [VaveAnalysisController::class, 'destroyBase'])->name('destroyBase');
    });

    // Purchase Requisition (Admin, Approver, Checker)
    Route::middleware(['inventory.role:admin,approver,checker'])->prefix('inventory/purchase-requisition')->name('inventory.purchaseRequisition.')->group(function () {
        Route::get('/', [PurchaseRequisitionController::class, 'index'])->name('index');
        Route::get('/data', [PurchaseRequisitionController::class, 'data'])->name('data');
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