<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BannerController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductStoreController;
use App\Http\Controllers\ProductSaleController;
use App\Http\Controllers\TopicController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\CartController;


Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// === CHECKOUT ===
Route::middleware('auth:sanctum')->post('checkout', [OrderController::class, 'checkout']);

// === LỊCH SỬ & CHI TIẾT ĐƠN HÀNG NGƯỜI DÙNG ===
Route::middleware('auth:sanctum')->prefix('orders')->group(function () {
    Route::get('history', [OrderController::class, 'history']);
    Route::get('detail/{id}', [OrderController::class, 'detail']);
});

// === PROFILE NGƯỜI DÙNG ===
Route::middleware('auth:sanctum')->prefix('user')->group(function () {
    Route::get('profile', [UserController::class, 'profile']);
    Route::post('profile/update', [UserController::class, 'updateProfile']);
});


Route::apiResources([
    'banner'      => BannerController::class,
    'category'    => CategoryController::class,
    'contact'     => ContactController::class,
    'menu'        => MenuController::class,
    'product'     => ProductController::class,
    'productstore' => ProductStoreController::class,
    'productsale' => ProductSaleController::class,
    'topic'       => TopicController::class,
    'post'        => PostController::class,
    'user'        => UserController::class,
    'order'       => OrderController::class,
    'setting'     => SettingController::class,
]);

Route::prefix('products')->group(function () {
    Route::get('new/{limit?}', [ProductController::class, 'product_new']); 
    Route::get('sale/{limit?}', [ProductController::class, 'product_sale']); 
    Route::get('all/{limit?}', [ProductController::class, 'product_all']); 
    Route::get('{slug}', [ProductController::class, 'product_by_slug']); 
    Route::get('category/{slug}', [ProductController::class, 'product_by_category']);
});


Route::middleware('auth:sanctum')->prefix('cart')->group(function () {
    Route::get('/', [\App\Http\Controllers\CartController::class, 'index']); 
    Route::post('add', [\App\Http\Controllers\CartController::class, 'add']); 
    Route::put('update/{id}', [\App\Http\Controllers\CartController::class, 'update']);
    Route::delete('remove/{id}', [\App\Http\Controllers\CartController::class, 'remove']);
    Route::delete('clear', [\App\Http\Controllers\CartController::class, 'clear']);
});



Route::get('banner-slider', [BannerController::class, 'slider']);
Route::get('post-new', [PostController::class, 'postNew']);
Route::get('posts', [PostController::class, 'indexClient']);
Route::get('posts/{slug}', [PostController::class, 'showClient']);
Route::get('categories', [CategoryController::class, 'index']);
Route::get('setting', [SettingController::class, 'getClientSetting']);
Route::get('menus', [MenuController::class, 'getClientMenu']);






////////////////////////////// ROUTES ADMIN //////////////////////////////////////

Route::prefix('admin')->group(function () {

    // ---------- PRODUCT ----------
    Route::get('products', [ProductController::class, 'index']);
    Route::get('products/{id}', [ProductController::class, 'show']);
    Route::post('products', [ProductController::class, 'store']);
    Route::put('products/{id}', [ProductController::class, 'update']);
    Route::delete('products/{id}', [ProductController::class, 'destroy']);

    Route::post('attributes', [ProductController::class, 'createAttribute']);
    Route::get('attributes', [ProductController::class, 'getAllAttributes']);


    Route::get('products-trash', [ProductController::class, 'trash']);
    Route::put('products-restore/{id}', [ProductController::class, 'restore']);
    Route::delete('products-force/{id}', [ProductController::class, 'forceDelete']);


    // ---------- CATEGORY ----------
    Route::get('category', [CategoryController::class, 'index']);
    Route::get('category/{id}', [CategoryController::class, 'show']);
    Route::post('category', [CategoryController::class, 'store']);
    Route::put('category/{id}', [CategoryController::class, 'update']);
    Route::delete('category/{id}', [CategoryController::class, 'destroy']);

    Route::get('category-trash', [CategoryController::class, 'trash']);
    Route::put('category-restore/{id}', [CategoryController::class, 'restore']);
    Route::delete('category-force/{id}', [CategoryController::class, 'forceDelete']);

    // ---------- CONTACT ----------
    Route::get('contact', [ContactController::class, 'index']);
    Route::get('contact/{id}', [ContactController::class, 'show']);
    Route::post('contact', [ContactController::class, 'store']);
    Route::put('contact/{id}', [ContactController::class, 'update']);
    Route::delete('contact/{id}', [ContactController::class, 'destroy']);

    // ---------- BANNER ----------
    Route::get('banner', [BannerController::class, 'index']);
    Route::get('banner/{id}', [BannerController::class, 'show']);
    Route::post('banner', [BannerController::class, 'store']);
    Route::put('banner/{id}', [BannerController::class, 'update']);
    Route::delete('banner/{id}', [BannerController::class, 'destroy']);

    Route::get('banner-trash', [BannerController::class, 'trash']);
    Route::put('banner-restore/{id}', [BannerController::class, 'restore']);
    Route::delete('banner-force/{id}', [BannerController::class, 'forceDelete']);

    // ---------- POST ----------
    Route::get('post', [PostController::class, 'index']);
    Route::get('post/{id}', [PostController::class, 'show']);
    Route::post('post', [PostController::class, 'store']);
    Route::put('post/{id}', [PostController::class, 'update']);
    Route::delete('post/{id}', [PostController::class, 'destroy']);

    Route::get('post-trash', [PostController::class, 'trash']);
    Route::put('post-restore/{id}', [PostController::class, 'restore']);
    Route::delete('post-force/{id}', [PostController::class, 'forceDelete']);

    // ---------- TOPIC ----------
    Route::get('topics', [TopicController::class, 'index']);
    Route::get('topics/{id}', [TopicController::class, 'show']);
    Route::post('topics', [TopicController::class, 'store']);
    Route::put('topics/{id}', [TopicController::class, 'update']);
    Route::delete('topics/{id}', [TopicController::class, 'destroy']);

    Route::get('topics-trash', [TopicController::class, 'trash']);
    Route::put('topics-restore/{id}', [TopicController::class, 'restore']);
    Route::delete('topics-force/{id}', [TopicController::class, 'forceDelete']);

    // ---------- ORDER ----------
    Route::get('orders', [OrderController::class, 'index']);
    Route::get('orders/{id}', [OrderController::class, 'show']);
    Route::post('orders', [OrderController::class, 'store']);
    Route::put('orders/{id}', [OrderController::class, 'update']);
    Route::delete('orders/{id}', [OrderController::class, 'destroy']);

    Route::get('orders-trash', [OrderController::class, 'trash']);
    Route::put('orders-restore/{id}', [OrderController::class, 'restore']);
    Route::delete('orders-force/{id}', [OrderController::class, 'forceDelete']);

    // ---------- PRODUCT SALES ----------
    Route::get('product-sales', [ProductSaleController::class, 'index']);
    Route::get('product-sales/{id}', [ProductSaleController::class, 'show']);
    Route::post('product-sales', [ProductSaleController::class, 'store']);
    Route::put('product-sales/{id}', [ProductSaleController::class, 'update']);
    Route::delete('product-sales/{id}', [ProductSaleController::class, 'destroy']);

    // ---------- PRODUCT STORE ----------
    Route::get('product-stores', [ProductStoreController::class, 'index']);
    Route::get('product-stores/{id}', [ProductStoreController::class, 'show']);
    Route::post('product-stores', [ProductStoreController::class, 'store']);
    Route::put('product-stores/{id}', [ProductStoreController::class, 'update']);
    Route::delete('product-stores/{id}', [ProductStoreController::class, 'destroy']);

    Route::get('product-stores-trash', [ProductStoreController::class, 'trash']);
    Route::put('product-stores-restore/{id}', [ProductStoreController::class, 'restore']);
    Route::delete('product-stores-force/{id}', [ProductStoreController::class, 'forceDelete']);

    Route::get('users/check', [UserController::class, 'check']);
    Route::get('users', [UserController::class, 'index']);
    Route::post('users', [UserController::class, 'store']);
    Route::get('users/{id}', [UserController::class, 'show']);
    Route::put('users/{id}', [UserController::class, 'update']);
    Route::delete('users/{id}', [UserController::class, 'destroy']);

    // ---------- SETTING ----------
    Route::get('settings', [SettingController::class, 'index']);      
    Route::get('settings/{id}', [SettingController::class, 'show']); 
    Route::post('settings', [SettingController::class, 'store']);  
    Route::put('settings/{id}', [SettingController::class, 'update']); 
    Route::delete('settings/{id}', [SettingController::class, 'destroy']); 

    Route::get('menus', [MenuController::class, 'index']);        
    Route::get('menus/{id}', [MenuController::class, 'show']);   
    Route::post('menus', [MenuController::class, 'store']); 
    Route::put('menus/{id}', [MenuController::class, 'update']);
    Route::delete('menus/{id}', [MenuController::class, 'destroy']); 
});


// Đăng nhập / Đăng xuất
Route::post('login', [UserController::class, 'login']);
Route::middleware('auth:sanctum')->post('logout', [UserController::class, 'logout']);
