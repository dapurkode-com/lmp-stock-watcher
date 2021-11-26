<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Route::get('/', function () {
//     return view('welcome');
// });

Auth::routes();

Route::redirect('/', '/home', 301);

Route::get('/home', function () {
    return view('home');
})->name('home')->middleware('auth');

Route::middleware(['auth'])->group(function () {

    Route::prefix('watchlist')->group(function () {
        Route::get('us-stock', [\App\Http\Controllers\StockUsController::class, 'index']);
        Route::get('idx-stock', [\App\Http\Controllers\IdxStockController::class, 'index']);
        Route::get('crypto', [\App\Http\Controllers\CryptoController::class, 'index']);
        Route::get('commodity', [\App\Http\Controllers\CommodityController::class, 'index']);
    });

    Route::prefix('my-wallet')->group(function (){
        Route::get('us-stock', [\App\Http\Controllers\StockUsController::class, 'holdable']);
        Route::get('idx-stock', [\App\Http\Controllers\IdxStockController::class, 'holdable']);
        Route::get('crypto', [\App\Http\Controllers\CryptoController::class, 'holdable']);
        Route::get('commodity', [\App\Http\Controllers\CommodityController::class, 'holdable']);
    });
});
