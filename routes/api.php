<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware(['auth'])->group(function () {

    Route::prefix('watchlist')->group(function (){
        Route::get('us-stocks', [\App\Http\Controllers\Api\UsStockController::class, 'index']);
        Route::get('idx-stocks', [\App\Http\Controllers\Api\IdxStockController::class, 'index']);
        Route::get('cryptos', [\App\Http\Controllers\Api\CryptoController::class, 'index']);
        Route::get('commodities', [\App\Http\Controllers\Api\CommodityController::class, 'index']);

        Route::get('get-resource-us-stock', [\App\Http\Controllers\Api\UsStockController::class, 'getResource']);
        Route::get('get-resource-idx-stock', [\App\Http\Controllers\Api\IdxStockController::class, 'getResource']);
        Route::get('get-resource-crypto', [\App\Http\Controllers\Api\CryptoController::class, 'getResource']);
        Route::get('get-resource-commodity', [\App\Http\Controllers\Api\CommodityController::class, 'getResource']);

        Route::post('store-us-stock', [\App\Http\Controllers\Api\UsStockController::class, 'store']);
        Route::post('store-idx-stock', [\App\Http\Controllers\Api\IdxStockController::class, 'store']);
        Route::post('store-crypto', [\App\Http\Controllers\Api\CryptoController::class, 'store']);
        Route::post('store-commodity', [\App\Http\Controllers\Api\CommodityController::class, 'store']);

        Route::post('remove-idx-stock', [\App\Http\Controllers\Api\IdxStockController::class, 'remove']);
        Route::post('remove-us-stock', [\App\Http\Controllers\Api\UsStockController::class, 'remove']);
        Route::post('remove-crypto', [\App\Http\Controllers\Api\CryptoController::class, 'remove']);
        Route::post('remove-commodity', [\App\Http\Controllers\Api\CommodityController::class, 'remove']);
    });

    Route::prefix('wallet')->group(function (){
        Route::get('us-stocks', [\App\Http\Controllers\Api\HoldUsStockController::class, 'index']);
        Route::get('idx-stocks', [\App\Http\Controllers\Api\HoldIdxStockController::class, 'index']);
        Route::get('cryptos', [\App\Http\Controllers\Api\HoldCryptoController::class, 'index']);
        Route::get('commodities', [\App\Http\Controllers\Api\HoldCommodityController::class, 'index']);

        Route::get('get-resource-us-stock', [\App\Http\Controllers\Api\HoldUsStockController::class, 'getResource']);
        Route::get('get-resource-idx-stock', [\App\Http\Controllers\Api\HoldIdxStockController::class, 'getResource']);
        Route::get('get-resource-crypto', [\App\Http\Controllers\Api\HoldCryptoController::class, 'getResource']);
        Route::get('get-resource-commodity', [\App\Http\Controllers\Api\HoldCommodityController::class, 'getResource']);

        Route::post('store-us-stock', [\App\Http\Controllers\Api\HoldUsStockController::class, 'store']);
        Route::post('store-idx-stock', [\App\Http\Controllers\Api\HoldIdxStockController::class, 'store']);
        Route::post('store-crypto', [\App\Http\Controllers\Api\HoldCryptoController::class, 'store']);
        Route::post('store-commodity', [\App\Http\Controllers\Api\HoldCommodityController::class, 'store']);

        Route::post('remove-us-stock', [\App\Http\Controllers\Api\HoldUsStockController::class, 'remove']);
        Route::post('remove-idx-stock', [\App\Http\Controllers\Api\HoldIdxStockController::class, 'remove']);
        Route::post('remove-crypto', [\App\Http\Controllers\Api\HoldCryptoController::class, 'remove']);
        Route::post('remove-commodity', [\App\Http\Controllers\Api\HoldCommodityController::class, 'remove']);
    });
});
