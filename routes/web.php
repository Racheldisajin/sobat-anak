<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\MiniGameController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserActionController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ProductReviewController;
use App\Http\Controllers\UserAddressController;
use App\Http\Controllers\TestimonialController;
use App\Http\Controllers\AiChatController;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/products', [ProductController::class, 'index'])->name('products');
Route::get('/products/{id}', [ProductController::class, 'show'])->name('product.show');
Route::get('/artikel', [ArticleController::class, 'index'])->name('articles');
Route::get('/articles', [ArticleController::class, 'index']);
Route::get('/mini-games', [MiniGameController::class, 'index'])->name('mini-games');
Route::get('/mini-games/tap-tap-kuman', [MiniGameController::class, 'tapTapKuman'])->name('mini-games.tap-tap-kuman');
Route::get('/ai-chat', [AiChatController::class, 'page'])->name('ai-chat.page');
Route::get('/ai-chat/new', [AiChatController::class, 'newSession'])->name('ai-chat.new');
Route::get('/ai-chat/session/{session}', [AiChatController::class, 'page'])->name('ai-chat.session');
Route::delete('/ai-chat/session/{session}', [AiChatController::class, 'destroySession'])->name('ai-chat.session.destroy');
Route::get('/ai-chat/suggest', [AiChatController::class, 'suggest'])->name('ai-chat.suggest');
Route::post('/ai-chat', [AiChatController::class, 'ask'])->name('ai-chat.ask');


Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->name('password.request');
Route::post('/forgot-password', [AuthController::class, 'sendForgotPassword'])->name('password.email');
Route::get('/forgot-password/code', [AuthController::class, 'showResetCode'])->name('password.code');
Route::post('/forgot-password/code', [AuthController::class, 'verifyResetCode'])->name('password.code.verify');
Route::get('/forgot-password/new', [AuthController::class, 'showNewPassword'])->name('password.new');
Route::post('/forgot-password/new', [AuthController::class, 'updateNewPassword'])->name('password.new.update');
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->name('register.post');
Route::get('/register/verify', [AuthController::class, 'showRegisterVerify'])->name('register.verify');
Route::post('/register/verify', [AuthController::class, 'verifyRegister'])->name('register.verify.post');
Route::post('/register/resend-code', [AuthController::class, 'resendRegisterCode'])->name('register.resend');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
Route::get('/profile', [AuthController::class, 'profile'])->name('profile');
Route::get('/profile/rewards', [AuthController::class, 'rewardsPage'])->name('profile.rewards');
Route::patch('/profile', [AuthController::class, 'updateProfile'])->name('profile.update');

Route::post('/cart/add', [UserActionController::class, 'addCart'])->name('cart.add');
Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::patch('/cart/{cartItem}', [CartController::class, 'update'])->name('cart.update');
Route::delete('/cart/{cartItem}', [CartController::class, 'destroy'])->name('cart.destroy');
Route::get('/checkout', [CartController::class, 'checkout'])->name('checkout');

Route::post('/game/play', [UserActionController::class, 'playGame'])->name('game.play');
Route::post('/reward/redeem', [UserActionController::class, 'redeem'])->name('reward.redeem');


// Website testimonials / ulasan website
Route::get('/testimonials', [TestimonialController::class, 'index'])->name('testimonials.index');
Route::post('/testimonials', [TestimonialController::class, 'store'])->name('testimonials.store.user');
Route::post('/testimonials/{testimonial}/like', [TestimonialController::class, 'toggleLike'])->name('testimonials.like');

// Product reviews
Route::post('/products/{productId}/reviews', [ProductReviewController::class, 'store'])->name('review.store');
Route::delete('/products/{productId}/reviews', [ProductReviewController::class, 'destroy'])->name('review.destroy');

// User address
Route::post('/address', [UserAddressController::class, 'store'])->name('address.store');
Route::get('/address', [UserAddressController::class, 'get'])->name('address.get');

Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/articles', [AdminController::class, 'articles'])->name('articles');
    Route::get('/articles/create', [AdminController::class, 'createArticle'])->name('articles.create');
    Route::post('/articles', [AdminController::class, 'storeArticle'])->name('articles.store');
    Route::delete('/articles/bulk-delete', [AdminController::class, 'bulkDestroyArticles'])->name('articles.bulk-destroy');
    Route::get('/articles/{post}/edit', [AdminController::class, 'editArticle'])->name('articles.edit');
    Route::patch('/articles/{post}', [AdminController::class, 'updateArticle'])->name('articles.update');
    Route::delete('/articles/{post}', [AdminController::class, 'destroyArticle'])->name('articles.destroy');

    Route::get('/products', [AdminController::class, 'products'])->name('products');
    Route::post('/products', [AdminController::class, 'storeProduct'])->name('products.store');
    Route::patch('/products/{product}', [AdminController::class, 'updateProduct'])->name('products.update');
    Route::delete('/products/{product}', [AdminController::class, 'destroyProduct'])->name('products.destroy');

    Route::get('/rewards', [AdminController::class, 'rewards'])->name('rewards');
    Route::post('/rewards', [AdminController::class, 'storeReward'])->name('rewards.store');
    Route::delete('/rewards/{reward}', [AdminController::class, 'destroyReward'])->name('rewards.destroy');

    Route::get('/testimonials', [AdminController::class, 'testimonials'])->name('testimonials');
    Route::post('/testimonials', [AdminController::class, 'storeTestimonial'])->name('testimonials.store');
    Route::delete('/testimonials/{testimonial}', [AdminController::class, 'destroyTestimonial'])->name('testimonials.destroy');
});
