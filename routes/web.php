<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\EmailController;

Route::get('/', function () {
    return redirect('/checkout');
});

// Módulo 1 y 2: Stripe Checkout y Webhook
Route::get('/checkout', [PaymentController::class, 'showCheckout'])->name('checkout.index');
Route::post('/create-payment-intent', [PaymentController::class, 'createPaymentIntent'])->name('payment.intent');
Route::post('/stripe/webhook', [PaymentController::class, 'handleWebhook'])->name('stripe.webhook');

// Módulo 3: SendGrid Email
Route::get('/email', [EmailController::class, 'index'])->name('email.index');
Route::post('/email', [EmailController::class, 'send'])->name('email.send');