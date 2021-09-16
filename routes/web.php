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

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/home', function () {
    return view('home');
})->name('home')->middleware('auth');

Route::middleware(['auth'])->group(function () {
    Route::get('us-stock-list', [\App\Http\Controllers\StockUsController::class, 'index']);
    Route::get('idx-stock-list', [\App\Http\Controllers\IdxStockController::class, 'index']);
    Route::get('crypto-list', [\App\Http\Controllers\CryptoController::class, 'index']);
});
