<?php

use App\Http\Controllers\NotificationController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ArtistController;
use App\Http\Controllers\PortfolioController;
use App\Http\Controllers\CommissionController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\FaqController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\MessageController;

Route::get('/', fn() => redirect()->route('artist.profile'));

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->name('register.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout.post');

Route::get('/profile',   [ArtistController::class,    'index'])->name('artist.profile');
Route::get('/portfolio', [PortfolioController::class,  'index'])->name('portfolio.index');
Route::get('/faq',       [FaqController::class,        'index'])->name('faq.index');
Route::get('/reviews',   [ReviewController::class,     'index'])->name('reviews.index');
Route::get('/reviews/manage', [ReviewController::class,'manage'])->name('reviews.manage');

// Commission
Route::get('/commission',              [CommissionController::class, 'index'])->name('commission.index');
Route::get('/commission/manage',       [CommissionController::class, 'manageIndex'])->name('commission.manage');
Route::get('/commission/create',       [CommissionController::class, 'create'])->name('commission.create');
Route::post('/commission',             [CommissionController::class, 'store'])->name('commission.store');
Route::get('/commission/{id}',         [CommissionController::class, 'show'])->name('commission.show');
Route::get('/commission/{id}/order',   [CommissionController::class, 'orderForm'])->name('commission.order.form');
Route::post('/commission/order',       [CommissionController::class, 'orderStore'])->name('commission.order.store');
Route::get('/commission/{id}/edit',    [CommissionController::class, 'edit'])->name('commission.edit');
Route::post('/commission/{id}/update', [CommissionController::class, 'update'])->name('commission.update');
Route::post('/commission/{id}/delete', [CommissionController::class, 'destroy'])->name('commission.delete');
Route::post('/commission/{id}/toggle', [CommissionController::class, 'toggleStatus'])->name('commission.toggle');

// Orders
Route::get('/orders/artist',                    [OrderController::class, 'artist'])->name('orders.artist');
Route::get('/orders/client',                    [OrderController::class, 'client'])->name('orders.client');
Route::post('/orders/{id}/confirm',             [OrderController::class, 'confirm'])->name('orders.confirm');
Route::post('/orders/{id}/reject',              [OrderController::class, 'reject'])->name('orders.reject');
Route::post('/orders/{id}/mark-done',           [OrderController::class, 'markDone'])->name('orders.markdone');
Route::post('/orders/{id}/confirm-payment',     [OrderController::class, 'confirmPayment'])->name('orders.confirm-payment');
Route::post('/orders/{id}/cancel',              [OrderController::class, 'cancel'])->name('orders.cancel');
Route::post('/orders/{id}/upload-payment',      [OrderController::class, 'uploadPayment'])->name('orders.upload-payment');

// Chat
Route::get('/chat',         [MessageController::class, 'index'])->name('chat.index');
Route::post('/chat/send',   [MessageController::class, 'send'])->name('chat.send');
Route::get('/chat/fetch',   [MessageController::class, 'fetch'])->name('chat.fetch');
Route::post('/chat/delete', [MessageController::class, 'deleteMessage'])->name('chat.delete');
Route::post('/chat/block',  [MessageController::class, 'blockUser'])->name('chat.block');

// Reviews
Route::get('/reviews',              [ReviewController::class, 'index'])->name('reviews.index');
Route::get('/reviews/manage',       [ReviewController::class, 'manage'])->name('reviews.manage');
Route::get('/reviews/create/{orderId}', [ReviewController::class, 'create'])->name('reviews.create');
Route::post('/reviews',             [ReviewController::class, 'store'])->name('reviews.store');
Route::post('/reviews/{id}/approve',[ReviewController::class, 'approve'])->name('reviews.approve');
Route::post('/reviews/{id}/reject', [ReviewController::class, 'reject'])->name('reviews.reject');
Route::post('/reviews/{id}/delete', [ReviewController::class, 'destroy'])->name('reviews.delete');

// Portfolio
Route::get('/portfolio',             [PortfolioController::class, 'index'])->name('portfolio.index');
Route::post('/portfolio/{id}/view',  [PortfolioController::class, 'view'])->name('portfolio.view');
Route::get('/portfolio/manage',      [PortfolioController::class, 'manage'])->name('portfolio.manage');
Route::get('/portfolio/create',      [PortfolioController::class, 'create'])->name('portfolio.create');
Route::post('/portfolio',            [PortfolioController::class, 'store'])->name('portfolio.store');
Route::get('/portfolio/{id}/edit',   [PortfolioController::class, 'edit'])->name('portfolio.edit');
Route::post('/portfolio/{id}/update',[PortfolioController::class, 'update'])->name('portfolio.update');
Route::post('/portfolio/{id}/delete',[PortfolioController::class, 'destroy'])->name('portfolio.delete');
Route::post('/portfolio/{id}/toggle',[PortfolioController::class, 'toggleStatus'])->name('portfolio.toggle');
Route::get('/portfolio/{id}', [PortfolioController::class, 'show'])->name('portfolio.show');

Route::get('/profile',        [ArtistController::class, 'index'])->name('artist.profile');
Route::post('/profile/update',[ArtistController::class, 'update'])->name('artist.update');

Route::get('/faq',                  [FaqController::class, 'index'])->name('faq.index');
Route::get('/faq/manage',           [FaqController::class, 'manage'])->name('faq.manage');
Route::post('/faq',                 [FaqController::class, 'store'])->name('faq.store');
Route::post('/faq/{id}/update',     [FaqController::class, 'update'])->name('faq.update');
Route::post('/faq/{id}/toggle',     [FaqController::class, 'toggle'])->name('faq.toggle');
Route::post('/faq/{id}/delete',     [FaqController::class, 'destroy'])->name('faq.delete');

Route::get('/forgot-password', [AuthController::class, 'forgotForm'])->name('password.request');
Route::post('/forgot-password', [AuthController::class, 'forgotSend'])->name('password.email');
Route::get('/reset-password/{token}', [AuthController::class, 'resetForm'])->name('password.reset');
Route::post('/reset-password', [AuthController::class, 'resetUpdate'])->name('password.update');

Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
Route::post('/notifications/read-all', [NotificationController::class, 'readAll'])->name('notifications.readAll');
Route::get('/notifications/{id}/read', [NotificationController::class, 'read'])->name('notifications.read');