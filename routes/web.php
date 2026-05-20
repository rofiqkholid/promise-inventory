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
use App\Http\Controllers\Inventory\Material\StoDashboardController;
use App\Http\Controllers\Inventory\Material\VaveAnalysisController;
use App\Http\Controllers\Inventory\Material\ProjectVaveDashboardController;
use App\Http\Controllers\Inventory\Material\RegularVaveDashboardController;
use App\Http\Controllers\Inventory\Material\ProjectVaveAnalysisController;
use App\Http\Controllers\Inventory\Material\RegularVaveAnalysisController;
use App\Http\Controllers\Inventory\Material\RevisionController;
use App\Http\Controllers\Inventory\Material\VaveBaseSuffixController;
use App\Http\Controllers\Inventory\Material\DebugEpicorController;


use App\Http\Controllers\Inventory\Tool\ToolDashboardController;
use App\Http\Controllers\Inventory\Tool\ToolCategoryController;
use App\Http\Controllers\Inventory\Tool\ToolMasterController;

use App\Http\Controllers\Inventory\Tool\ToolLocationController;
use App\Http\Controllers\Inventory\Tool\ToolFastStockController;
use App\Http\Controllers\Inventory\Tool\ToolSlowBatchController;
use App\Http\Controllers\Inventory\Tool\ToolStoController;
use App\Http\Controllers\Inventory\Tool\ToolSketchController;
use App\Http\Controllers\Inventory\Tool\ToolInformationController;

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
    if (request()->has('redirect')) {
        session()->put('url.intended', request()->get('redirect'));
    }
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
        return redirect()->intended(route('dashboard'));
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
            Route::post('/product/update-action-status/{id}', [InventoryProductController::class, 'updateActionStatus'])->name('product.updateActionStatus');
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

        // Tool Inventory Grouped Routes
        Route::prefix('inventory/tool')->name('inventory.tool.')->group(function () {
            // Dashboard
            Route::get('/dashboard', [ToolDashboardController::class, 'index'])->name('dashboard');
            Route::post('/dashboard/update-action-status/{id}', [ToolDashboardController::class, 'updateActionStatus'])->name('dashboard.updateActionStatus');

            // Master — Category
            Route::resource('category', ToolCategoryController::class)->except(['create', 'edit', 'show']);

            // Master — Tool Specification
            Route::resource('master', ToolMasterController::class)->except(['create', 'edit', 'show']);

            // Master — Sketch
            Route::prefix('sketch')->name('sketch.')->group(function () {
                Route::get('/', [ToolSketchController::class, 'index'])->name('index');
                Route::post('/', [ToolSketchController::class, 'store'])->name('store');
                Route::get('/{id}/edit', [ToolSketchController::class, 'edit'])->name('edit');
                Route::post('/{id}/update', [ToolSketchController::class, 'update'])->name('update');
                Route::delete('/{id}', [ToolSketchController::class, 'destroy'])->name('destroy');
                Route::get('/by-category/{categoryId}', [ToolSketchController::class, 'getByCategory'])->name('getByCategory');
            });

            // Master — Location
            Route::get('/location', [ToolLocationController::class, 'index'])->name('location.index');
            Route::resource('location', ToolLocationController::class)->except(['create', 'edit', 'show', 'index']);

            // Operational — Fast Moving Stock (IN / OUT / List)
            Route::prefix('fast-stock')->name('fast-stock.')->group(function () {
                Route::get('/', [ToolFastStockController::class, 'index'])->name('index');
                Route::post('/', [ToolFastStockController::class, 'store'])->name('store');        // IN
                Route::post('/out', [ToolFastStockController::class, 'out'])->name('out');          // OUT
                Route::get('/history', [ToolFastStockController::class, 'history'])->name('history');
                Route::get('/print-qr/{id}', [ToolMasterController::class, 'printQr'])->name('printQr');
            });

            // Operational — Slow Moving Batches
            Route::prefix('slow-batch')->name('slow-batch.')->group(function () {
                Route::get('/', [ToolSlowBatchController::class, 'index'])->name('index');
                Route::get('/next-id', [ToolSlowBatchController::class, 'getNextId'])->name('nextId');
                Route::post('/', [ToolSlowBatchController::class, 'store'])->name('store');
                Route::put('/{id}', [ToolSlowBatchController::class, 'update'])->name('update');
                Route::get('/total-asset', [ToolSlowBatchController::class, 'totalAssetValue'])->name('totalAsset');
                Route::get('/print-qr/{id}', [ToolSlowBatchController::class, 'printQr'])->name('printQr');
            });

            // STO — Unified (Header-Detail)
            Route::prefix('sto')->name('sto.')->group(function () {
                Route::get('/', [ToolStoController::class, 'index'])->name('index');
                Route::post('/', [ToolStoController::class, 'store'])->name('store');
                Route::get('/preview-code', [ToolStoController::class, 'previewCode'])->name('previewCode');
                Route::get('/get-current-stock', [ToolStoController::class, 'getCurrentStock'])->name('getCurrentStock');
                Route::get('/{id}', [ToolStoController::class, 'show'])->name('show');
                Route::post('/{id}/submit', [ToolStoController::class, 'submit'])->name('submit');
                Route::post('/{id}/approve', [ToolStoController::class, 'approve'])->name('approve');
                Route::post('/{id}/reject', [ToolStoController::class, 'reject'])->name('reject');
                Route::post('/{id}/reopen', [ToolStoController::class, 'reopen'])->name('reopen');
                Route::put('/{id}/update-event', [ToolStoController::class, 'updateEvent'])->name('updateEvent');
                Route::delete('/{id}/delete-event', [ToolStoController::class, 'deleteEvent'])->name('deleteEvent');
                
                // Detail management
                Route::post('/{id}/item-fast', [ToolStoController::class, 'addItemFast'])->name('addItemFast');
                Route::post('/{id}/item-slow', [ToolStoController::class, 'addItemSlow'])->name('addItemSlow');
                Route::delete('/{id}/item-fast/{itemId}', [ToolStoController::class, 'deleteItemFast'])->name('deleteItemFast');
                Route::delete('/{id}/item-slow/{itemId}', [ToolStoController::class, 'deleteItemSlow'])->name('deleteItemSlow');
            });

            // Master — Tool Information Search
            Route::prefix('information')->name('information.')->group(function () {
                Route::get('/', [ToolInformationController::class, 'index'])->name('index');
                Route::get('/search', [ToolInformationController::class, 'search'])->name('search');
                Route::get('/{id}', [ToolInformationController::class, 'show'])->name('show');
            });
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
    Route::middleware(['inventory.role:admin,approver,checker,operator,pic,viewer'])->prefix('inventory/sto')->name('inventory.sto.')->group(function () {
        Route::get('/', [StoController::class, 'index'])->name('index');
        Route::get('/get-preview-code', [StoController::class, 'previewCode'])->name('previewCode');
        
        // STO Dashboard (aggregate analytics) - Must be above /{id} to avoid collision
        Route::get('/dashboard', [StoDashboardController::class, 'index'])->name('dashboard');
        Route::get('/dashboard/event-trend', [StoDashboardController::class, 'eventTrendData'])->name('dashboard.eventTrend');
        Route::get('/dashboard/correction-log', [StoDashboardController::class, 'correctionLogByModel'])->name('dashboard.correctionLog');
        Route::get('/dashboard/correction-log/{modelName}', [StoDashboardController::class, 'correctionLogDetail'])->name('dashboard.correctionLogDetail');

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
        Route::get('/{id}/pareto-by-model', [StoDashboardController::class, 'paretoByModel'])->name('dashboard.paretoByModel');
    });

    // VAVE Analysis (Admin, Approver, Checker, Viewer) - Monolithic (Keep for compatibility if needed, but structured via new routes below)
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

    // Project VAVE Analysis
    Route::middleware(['inventory.role:admin,approver,checker,viewer'])->prefix('inventory/project-vave-analysis')->name('inventory.projectVaveAnalysis.')->group(function () {
        Route::get('/', [ProjectVaveAnalysisController::class, 'index'])->name('index');
        Route::get('/data', [ProjectVaveAnalysisController::class, 'data'])->name('data');
        Route::get('/base/{id}', [ProjectVaveAnalysisController::class, 'showBase'])->name('showBase');
        Route::post('/base', [ProjectVaveAnalysisController::class, 'storeBase'])->name('storeBase');
        Route::get('/comparison/{id}', [ProjectVaveAnalysisController::class, 'getComparison'])->name('getComparison');
        Route::get('/comparison/{id}/export', [ProjectVaveAnalysisController::class, 'exportExcel'])->name('export');
        Route::get('/summary-export', [ProjectVaveAnalysisController::class, 'exportSummary'])->name('exportSummary');
        Route::get('/get-bases', [ProjectVaveAnalysisController::class, 'getBases'])->name('getBases');
        Route::get('/download-template', [ProjectVaveAnalysisController::class, 'downloadTemplate'])->name('downloadTemplate');
        Route::match(['post', 'put'], '/import-data', [ProjectVaveAnalysisController::class, 'importExcel'])->name('importExcel');
        Route::delete('/base/{id}', [ProjectVaveAnalysisController::class, 'destroyBase'])->name('destroyBase');
    });

    // Regular VAVE Analysis
    Route::middleware(['inventory.role:admin,approver,checker,viewer'])->prefix('inventory/regular-vave-analysis')->name('inventory.regularVaveAnalysis.')->group(function () {
        Route::get('/', [RegularVaveAnalysisController::class, 'index'])->name('index');
        Route::get('/data', [RegularVaveAnalysisController::class, 'data'])->name('data');
        Route::get('/base/{id}', [RegularVaveAnalysisController::class, 'showBase'])->name('showBase');
        Route::post('/base', [RegularVaveAnalysisController::class, 'storeBase'])->name('storeBase');
        Route::get('/comparison/{id}', [RegularVaveAnalysisController::class, 'getComparison'])->name('getComparison');
        Route::get('/comparison/{id}/export', [RegularVaveAnalysisController::class, 'exportExcel'])->name('export');
        Route::get('/summary-export', [RegularVaveAnalysisController::class, 'exportSummary'])->name('exportSummary');
        Route::get('/get-bases', [RegularVaveAnalysisController::class, 'getBases'])->name('getBases');
        Route::get('/download-template', [RegularVaveAnalysisController::class, 'downloadTemplate'])->name('downloadTemplate');
        Route::match(['post', 'put'], '/import-data', [RegularVaveAnalysisController::class, 'importExcel'])->name('importExcel');
        Route::delete('/base/{id}', [RegularVaveAnalysisController::class, 'destroyBase'])->name('destroyBase');
    });

    // Project VAVE Gap Benefit Dashboard (Admin, Approver, Checker, Viewer)
    Route::middleware(['inventory.role:admin,approver,checker,viewer'])->prefix('inventory/vave-dashboard-project')->name('inventory.projectVaveDashboard.')->group(function () {
        Route::get('/', [ProjectVaveDashboardController::class, 'index'])->name('index');
        Route::get('/chart-data', [ProjectVaveDashboardController::class, 'chartData'])->name('chartData');
        Route::get('/pareto-data', [ProjectVaveDashboardController::class, 'paretoData'])->name('paretoData');
    });

    // Regular VAVE Gap Benefit Dashboard (Admin, Approver, Checker, Viewer)
    Route::middleware(['inventory.role:admin,approver,checker,viewer'])->prefix('inventory/vave-dashboard-regular')->name('inventory.regularVaveDashboard.')->group(function () {
        Route::get('/', [RegularVaveDashboardController::class, 'index'])->name('index');
        Route::get('/chart-data', [RegularVaveDashboardController::class, 'chartData'])->name('chartData');
        Route::get('/pareto-data', [RegularVaveDashboardController::class, 'paretoData'])->name('paretoData');
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

    // Temporary Debug Route
    Route::get('/inventory/debug/epicor', [DebugEpicorController::class, 'index'])->name('inventory.debug.epicor');
    Route::get('/inventory/debug/epicor/data', [DebugEpicorController::class, 'data'])->name('inventory.debug.epicor.data');
    Route::get('/inventory/debug/epicor/export', [DebugEpicorController::class, 'export'])->name('inventory.debug.epicor.export');


});

# endregion

#Region Dashboard API
Route::post('/api/data/models', [DashboardController::class, 'getModels'])->name('api.data.models');
Route::post('/api/data/customers', [DashboardController::class, 'getCustomers'])->name('api.data.customers');
Route::get('/api/data/statuses/{type}', [DashboardController::class, 'getStatuses'])->name('api.data.statuses');
Route::get('/api/dashboard/drilldown', [DashboardController::class, 'chartDrilldown'])->name('api.dashboard.drilldown');
Route::get('/api/tool/dashboard/drilldown', [\App\Http\Controllers\Inventory\Tool\ToolDashboardController::class, 'chartDrilldown'])->name('api.tool.dashboard.drilldown');
#End region