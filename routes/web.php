<?php

use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\FakePaymentController;
use App\Http\Controllers\SeoController;
use App\Http\Controllers\StoreController;
use App\Models\Order;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', [StoreController::class, 'home'])->name('home');
Route::get('/categories/{category:slug}', [StoreController::class, 'category'])->name('categories.show');
Route::get('/products/{product:slug}', [StoreController::class, 'product'])->name('products.show');
Route::get('/cart', [CartController::class, 'show'])->name('cart.show');
Route::post('/cart/{variant}', [CartController::class, 'add'])->name('cart.add');
Route::delete('/cart/{variant}', [CartController::class, 'remove'])->name('cart.remove');
Route::get('/checkout', [CheckoutController::class, 'show'])->name('checkout.show');
Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');
Route::get('/payments/fake/{payment}', [FakePaymentController::class, 'show'])->name('payments.fake.show');
Route::post('/payments/fake/{payment}/pay', [FakePaymentController::class, 'pay'])->name('payments.fake.pay');
Route::post('/payments/fake/callback', [FakePaymentController::class, 'callback'])->withoutMiddleware([ValidateCsrfToken::class]);
Route::get('/orders/{order}/thank-you', fn (Order $order) => Inertia::render('ThankYou', ['order' => $order]))->name('orders.thank-you');
Route::get('/sitemap.xml', [SeoController::class, 'sitemap']);
Route::get('/robots.txt', [SeoController::class, 'robots']);
