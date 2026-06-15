<?php

use App\Http\Controllers\BlogController;
use App\Http\Controllers\LandingPageController;
use App\Http\Controllers\CourierLocationController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\CouponValidationController;
use App\Http\Controllers\FeedController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\OrderTrackingController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ProductReviewController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\UserAccountController;
use App\Http\Controllers\UserAuthController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class);

Route::get('/lp/{product:slug}', [LandingPageController::class, 'show'])->name('lp.show');

Route::get('/shop', [ShopController::class, 'index'])->name('shop.index');
Route::get('/shop/suggest', [ShopController::class, 'suggest'])->name('shop.suggest');
Route::get('/shop/c/{category:slug}', [ShopController::class, 'category'])->name('shop.category');
Route::get('/shop/b/{brand:slug}', [ShopController::class, 'brand'])->name('shop.brand');
Route::get('/shop/{product:slug}', [ShopController::class, 'show'])->name('shop.show');

Route::get('/blog', [BlogController::class, 'index'])->name('blog.index');
Route::get('/blog/c/{category:slug}', [BlogController::class, 'category'])->name('blog.category');
Route::get('/blog/{blog:slug}', [BlogController::class, 'show'])->name('blog.show');

Route::get('/page/{page:slug}', [PageController::class, 'show'])->name('page.show');

Route::get('/cart', fn() => \Inertia\Inertia::render('Cart'))->name('cart');

Route::get('/track', [OrderTrackingController::class, 'show'])->name('order.track');

Route::middleware('auth')->group(function () {
    Route::post('/comments', [CommentController::class, 'store'])->name('comments.store');
    Route::delete('/comments/{comment}', [CommentController::class, 'destroy'])->name('comments.destroy');

    // Product reviews
    Route::post('/reviews', [ProductReviewController::class, 'store'])->name('reviews.store');
    Route::delete('/reviews/{review}', [ProductReviewController::class, 'destroy'])->name('reviews.destroy');

    // User account
    Route::prefix('account')->name('account.')->group(function () {
        Route::get('/', [UserAccountController::class, 'dashboard'])->name('dashboard');
        Route::get('/orders', [UserAccountController::class, 'orders'])->name('orders');
        Route::get('/orders/{order}', [UserAccountController::class, 'orderShow'])->name('orders.show');
        Route::get('/reviews', [UserAccountController::class, 'reviews'])->name('reviews');
        Route::get('/address', [UserAccountController::class, 'address'])->name('address');
        Route::post('/address', [UserAccountController::class, 'storeAddress'])->name('address.store');
        Route::put('/address/{address}', [UserAccountController::class, 'updateAddress'])->name('address.update');
        Route::delete('/address/{address}', [UserAccountController::class, 'destroyAddress'])->name('address.destroy');
        Route::post('/profile', [UserAccountController::class, 'updateProfile'])->name('profile.update');
        Route::get('/refunds', [UserAccountController::class, 'refunds'])->name('refunds');
        Route::post('/refunds', [UserAccountController::class, 'storeRefund'])->name('refunds.store');
    });
});

Route::post('/coupon/validate', [CouponValidationController::class, 'validate'])->name('coupon.validate');

// Courier location + charge (public, throttled)
Route::middleware('throttle:60,1')->group(function () {
    Route::get('/api/courier/cities',          [CourierLocationController::class, 'cities'])->name('courier.cities');
    Route::get('/api/courier/zones/{cityId}',  [CourierLocationController::class, 'zones'])->name('courier.zones');
    Route::get('/api/courier/areas/{zoneId}',  [CourierLocationController::class, 'areas'])->name('courier.areas');
    Route::post('/api/courier/charge',         [CourierLocationController::class, 'charge'])->name('courier.charge');
});

Route::get('/wishlist', fn() => inertia('Wishlist'))->name('wishlist');
Route::get('/loyalty', [\App\Http\Controllers\LoyaltyPageController::class, 'show'])->name('loyalty');

Route::get('/checkout', [CheckoutController::class, 'show'])->name('checkout');
Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');
Route::get('/order/confirmed/{orderNumber}', function (string $orderNumber) {
    $order = \App\Models\Order::with('items')->where('order_number', $orderNumber)->firstOrFail();
    return inertia('OrderConfirmed', ['order' => [
        'order_number'   => $order->order_number,
        'customer_name'  => $order->customer_name,
        'total'          => $order->total,
        'payment_method' => $order->payment_method,
        'status'         => $order->status,
        'items'          => $order->items->map(fn($i) => [
            'product_name'  => $i->product_name,
            'variant_label' => $i->variant_label,
            'quantity'      => $i->quantity,
            'unit_price'    => $i->unit_price,
            'total'         => $i->total,
        ]),
    ]]);
})->name('order.confirm');

Route::get('/login', [UserAuthController::class, 'showLogin'])->name('login');
Route::post('/login', [UserAuthController::class, 'login'])->name('login.post');
Route::post('/login/otp/send',   [UserAuthController::class, 'sendOtp'])->middleware('throttle:5,1')->name('login.otp.send');
Route::post('/login/otp/verify', [UserAuthController::class, 'verifyOtp'])->middleware('throttle:10,1')->name('login.otp.verify');
Route::get('/register', [UserAuthController::class, 'showRegister'])->name('register');
Route::post('/register', [UserAuthController::class, 'register'])->name('register.post');
Route::post('/logout', [UserAuthController::class, 'logout'])->name('logout');

// Password reset
Route::get('/forgot-password', [UserAuthController::class, 'showForgotPassword'])->name('password.request');
Route::post('/forgot-password', [UserAuthController::class, 'sendResetLink'])->name('password.email');
Route::get('/reset-password/{token}', [UserAuthController::class, 'showResetPassword'])->name('password.reset');
Route::post('/reset-password', [UserAuthController::class, 'resetPassword'])->name('password.update');

// Product feeds
Route::get('/feeds/google-merchant.xml', [FeedController::class, 'googleMerchant'])->name('feeds.google-merchant');
Route::get('/feeds/facebook-catalog.json', [FeedController::class, 'facebookCatalog'])->name('feeds.facebook-catalog');

// Currency switcher
Route::get('/currency/{code}', function (string $code) {
    if (\App\Models\Currency::where('code', $code)->where('is_active', true)->exists()) {
        cookie()->queue(cookie()->forever('currency', strtoupper($code)));
    }
    return redirect()->back();
})->name('currency.switch');

// Locale switcher (sets cookie, redirects back)
Route::get('/locale/{code}', function (string $code) {
    $lang = \App\Models\Language::where('code', $code)->where('is_active', true)->first();
    if ($lang) {
        cookie()->queue(cookie()->forever('locale', $code));
    }
    return redirect()->back();
})->name('locale.switch');

// Robots.txt
Route::get('/robots.txt', function () {
    $sitemapUrl = url('sitemap.xml');
    $content    = "User-agent: *\nAllow: /\nDisallow: /admin/\nDisallow: /checkout\n\nSitemap: {$sitemapUrl}\n";
    return response($content, 200, ['Content-Type' => 'text/plain']);
});
