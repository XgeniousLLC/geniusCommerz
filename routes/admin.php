<?php

use App\Http\Controllers\Admin\AiController;
use App\Http\Controllers\Admin\LanguageController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\FraudController;
use App\Http\Controllers\Admin\LoyaltyController;
use App\Http\Controllers\Admin\SitemapController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\BlogCategoryController;
use App\Http\Controllers\Admin\BlogController as AdminBlogController;
use App\Http\Controllers\Admin\CouponController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\RefundController;
use App\Http\Controllers\Admin\OrderSettingsController;
use App\Http\Controllers\Admin\AttributeController;
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\BrandController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\FailedJobController;
use App\Http\Controllers\Admin\IntegrationController;
use App\Http\Controllers\Admin\InviteController;
use App\Http\Controllers\Admin\PageController as AdminPageController;
use App\Http\Controllers\Admin\MenuController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\MediaController;
use App\Http\Controllers\Admin\MediaFolderController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->name('admin.')->group(function () {

    // Auth
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Invite accept (public, token-gated)
    Route::get('/invite/{token}', [InviteController::class, 'show'])->name('invite.show');
    Route::post('/invite/{token}', [InviteController::class, 'accept'])->name('invite.accept');

    // Protected routes
    Route::middleware(['admin'])->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // Force-password-reset gate
        Route::get('/password/change', [AuthController::class, 'showChangePassword'])->name('password.change');
        Route::post('/password/change', [AuthController::class, 'changePassword'])->name('password.update');

        // Blog
        Route::resource('blogs', AdminBlogController::class)->except(['show']);
        Route::resource('blog-categories', BlogCategoryController::class)->except(['show', 'create', 'edit']);
        Route::post('/blog-categories/quick-create', [BlogCategoryController::class, 'quickCreate'])->name('blog-categories.quick-create');

        // Pages
        Route::resource('pages', AdminPageController::class);
        Route::post('/pages/analyze-seo', [AdminPageController::class, 'analyzeSEO'])->name('pages.analyze-seo');

        // Admin management
        Route::resource('admins', AdminController::class);
        Route::post('/admins/{admin}/change-password', [AdminController::class, 'changePassword'])->name('admins.change-password');
        Route::post('/admins/invite', [InviteController::class, 'send'])->name('admins.invite');

        // Profile
        Route::get('/profile/edit', [AdminController::class, 'editProfile'])->name('profile.edit');
        Route::post('/profile/update', [AdminController::class, 'updateProfile'])->name('profile.update');
        Route::get('/profile/change-password', [AdminController::class, 'showChangePassword'])->name('profile.change-password');
        Route::post('/profile/change-password', [AdminController::class, 'updatePassword'])->name('profile.update-password');

        // User management
        Route::resource('users', UserController::class);
        Route::post('/users/{user}/change-password', [UserController::class, 'changePassword'])->name('users.change-password');

        // Catalog
        Route::post('/brands/quick-create', [BrandController::class, 'quickCreate'])->name('brands.quick-create');
        Route::post('/categories/quick-create', [CategoryController::class, 'quickCreate'])->name('categories.quick-create');
        Route::resource('categories', CategoryController::class)->except(['show']);
        Route::resource('brands', BrandController::class)->except(['show']);
        Route::resource('products', ProductController::class)->except(['show']);
        Route::resource('attributes', AttributeController::class)->except(['show']);

        // Order settings
        Route::get('/order-settings', [OrderSettingsController::class, 'index'])->name('order-settings.index');
        Route::post('/order-settings', [OrderSettingsController::class, 'update'])->name('order-settings.update');

        // Orders
        Route::get('/orders/customers/search', [OrderController::class, 'customerSearch'])->name('orders.customers.search');
        Route::get('/orders/products/search', [OrderController::class, 'productSearch'])->name('orders.products.search');
        Route::resource('orders', OrderController::class)->only(['index','create','store','show','destroy']);
        Route::patch('/orders/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.update-status');
        Route::patch('/orders/{order}/address', [OrderController::class, 'updateAddress'])->name('orders.update-address');
        Route::get('/orders/{order}/print', [OrderController::class, 'printSlip'])->name('orders.print');
        Route::post('/orders/{order}/dispatch-courier', [OrderController::class, 'dispatchToCourier'])->name('orders.dispatch-courier');
        Route::post('/orders/{order}/refresh-courier-status', [OrderController::class, 'refreshCourierStatus'])->name('orders.refresh-courier-status');

        // Coupons
        Route::resource('coupons', CouponController::class)->except(['show']);

        // Loyalty / points
        Route::get('/loyalty', [LoyaltyController::class, 'settings'])->name('loyalty.settings');
        Route::post('/loyalty', [LoyaltyController::class, 'updateSettings'])->name('loyalty.update');
        Route::get('/loyalty/transactions', [LoyaltyController::class, 'index'])->name('loyalty.index');
        Route::post('/loyalty/adjust', [LoyaltyController::class, 'adjust'])->name('loyalty.adjust');

        // Refunds
        Route::get('/refunds', [RefundController::class, 'index'])->name('refunds.index');
        Route::get('/refunds/{refund}', [RefundController::class, 'show'])->name('refunds.show');
        Route::patch('/refunds/{refund}', [RefundController::class, 'update'])->name('refunds.update');

        // Menus
        Route::resource('menus', MenuController::class)->except(['show']);
        Route::post('/menus/{menu}/items', [MenuController::class, 'addItem'])->name('menus.items.store');
        Route::patch('/menus/items/{item}', [MenuController::class, 'updateItem'])->name('menus.items.update');
        Route::delete('/menus/items/{item}', [MenuController::class, 'destroyItem'])->name('menus.items.destroy');
        Route::post('/menus/{menu}/reorder', [MenuController::class, 'reorder'])->name('menus.reorder');

        // Roles & permissions
        Route::resource('roles', RoleController::class)->except(['show']);

        // Settings
        Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
        Route::post('/settings/{group}', [SettingsController::class, 'update'])->name('settings.update');

        // Integrations
        Route::get('/integrations', [IntegrationController::class, 'index'])->name('integrations.index');
        Route::get('/integrations/{integration}/edit', [IntegrationController::class, 'edit'])->name('integrations.edit');
        Route::put('/integrations/{integration}', [IntegrationController::class, 'update'])->name('integrations.update');
        Route::post('/integrations/{integration}/set-default', [IntegrationController::class, 'setDefault'])->name('integrations.set-default');

        // Audit log
        Route::get('/audit-log', [AuditLogController::class, 'index'])->name('audit.index');

        // Failed jobs
        Route::get('/failed-jobs', [FailedJobController::class, 'index'])->name('failed-jobs.index');
        Route::get('/failed-jobs/{uuid}', [FailedJobController::class, 'show'])->name('failed-jobs.show');
        Route::post('/failed-jobs/{uuid}/retry', [FailedJobController::class, 'retry'])->name('failed-jobs.retry');
        Route::post('/failed-jobs/retry-all', [FailedJobController::class, 'retryAll'])->name('failed-jobs.retry-all');
        Route::delete('/failed-jobs/{uuid}', [FailedJobController::class, 'destroy'])->name('failed-jobs.destroy');
        Route::delete('/failed-jobs', [FailedJobController::class, 'destroyAll'])->name('failed-jobs.destroy-all');

        // Reports
        Route::prefix('reports')->name('reports.')->group(function () {
            Route::get('/sales',            [ReportController::class, 'salesOverview'])->name('sales');
            Route::get('/profit',           [ReportController::class, 'profitAnalysis'])->name('profit');
            Route::get('/top-products',     [ReportController::class, 'topProducts'])->name('top-products');
            Route::get('/demand-trends',    [ReportController::class, 'demandTrends'])->name('demand-trends');
            Route::get('/category-revenue', [ReportController::class, 'categoryRevenue'])->name('category-revenue');
            Route::get('/inventory',        [ReportController::class, 'inventory'])->name('inventory');
            Route::get('/customers',        [ReportController::class, 'customers'])->name('customers');
            Route::get('/customer-ltv',     [ReportController::class, 'customerLtv'])->name('customer-ltv');
            Route::get('/orders',           [ReportController::class, 'ordersReport'])->name('orders');
            Route::get('/coupon-usage',     [ReportController::class, 'couponUsage'])->name('coupon-usage');
            Route::get('/refund-analysis',  [ReportController::class, 'refundAnalysis'])->name('refund-analysis');
            Route::get('/payment-trends',   [ReportController::class, 'paymentTrends'])->name('payment-trends');
            Route::get('/geographic',       [ReportController::class, 'geographic'])->name('geographic');
            Route::get('/export',           [ReportController::class, 'export'])->name('export');
        });

        // Sitemap
        Route::get('/sitemap', [SitemapController::class, 'index'])->name('sitemap.index');
        Route::post('/sitemap/generate', [SitemapController::class, 'generate'])->name('sitemap.generate');

        // Fraud check
        Route::post('/fraud-check', [FraudController::class, 'check'])->name('fraud.check');

        // AI generation
        Route::post('/ai/product-description', [AiController::class, 'generateProductDescription'])->name('ai.product-description');
        Route::post('/ai/blog-content',         [AiController::class, 'generateBlogContent'])->name('ai.blog-content');
        Route::post('/ai/meta-description',     [AiController::class, 'generateMetaDescription'])->name('ai.meta-description');

        // Languages & translations
        Route::get('/languages',                              [LanguageController::class, 'index'])->name('languages.index');
        Route::post('/languages',                             [LanguageController::class, 'store'])->name('languages.store');
        Route::get('/languages/{language}/edit',             [LanguageController::class, 'edit'])->name('languages.edit');
        Route::put('/languages/{language}',                  [LanguageController::class, 'update'])->name('languages.update');
        Route::delete('/languages/{language}',               [LanguageController::class, 'destroy'])->name('languages.destroy');
        Route::post('/languages/{language}/set-default',     [LanguageController::class, 'setDefault'])->name('languages.set-default');
        Route::post('/languages/{language}/translate-ai',    [LanguageController::class, 'translateWithAi'])->name('languages.translate-ai');

        // Media Library
        Route::get('/media', [MediaController::class, 'index'])->name('media.index');
        Route::get('/media/resolve', [MediaController::class, 'resolve'])->name('media.resolve');
        Route::post('/media/chunk', [MediaController::class, 'chunk'])->name('media.chunk');
        Route::put('/media/{media}', [MediaController::class, 'update'])->name('media.update');
        Route::delete('/media/{media}', [MediaController::class, 'destroy'])->name('media.destroy');
        Route::post('/media/folders', [MediaFolderController::class, 'store'])->name('media.folders.store');
        Route::put('/media/folders/{folder}', [MediaFolderController::class, 'update'])->name('media.folders.update');
        Route::delete('/media/folders/{folder}', [MediaFolderController::class, 'destroy'])->name('media.folders.destroy');

    });
});
