<?php

use App\Http\Controllers\BlogController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\CouponValidationController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\OrderTrackingController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ProductReviewController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\UserAccountController;
use App\Http\Controllers\UserAuthController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class);

Route::get('/shop', [ShopController::class, 'index'])->name('shop.index');
Route::get('/shop/c/{category:slug}', [ShopController::class, 'category'])->name('shop.category');
Route::get('/shop/b/{brand:slug}', [ShopController::class, 'brand'])->name('shop.brand');
Route::get('/shop/{product:slug}', [ShopController::class, 'show'])->name('shop.show');

Route::get('/blog', [BlogController::class, 'index'])->name('blog.index');
Route::get('/blog/c/{category:slug}', [BlogController::class, 'category'])->name('blog.category');
Route::get('/blog/{blog:slug}', [BlogController::class, 'show'])->name('blog.show');

Route::get('/page/{page:slug}', [PageController::class, 'show'])->name('page.show');

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

Route::get('/wishlist', fn() => inertia('Wishlist'))->name('wishlist');

Route::get('/checkout', [CheckoutController::class, 'show'])->name('checkout');
Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');
Route::get('/order/confirmed/{orderNumber}', function (string $orderNumber) {
    $order = \App\Models\Order::where('order_number', $orderNumber)->firstOrFail();
    return inertia('OrderConfirmed', ['order' => [
        'order_number'   => $order->order_number,
        'customer_name'  => $order->customer_name,
        'total'          => $order->total,
        'payment_method' => $order->payment_method,
        'status'         => $order->status,
    ]]);
})->name('order.confirm');

Route::get('/login', [UserAuthController::class, 'showLogin'])->name('login');
Route::post('/login', [UserAuthController::class, 'login'])->name('login.post');
Route::get('/register', [UserAuthController::class, 'showRegister'])->name('register');
Route::post('/register', [UserAuthController::class, 'register'])->name('register.post');
Route::post('/logout', [UserAuthController::class, 'logout'])->name('logout');
