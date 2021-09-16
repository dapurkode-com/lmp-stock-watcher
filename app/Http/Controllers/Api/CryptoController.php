<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WatchlistStockCrypto;
use Illuminate\Http\Request;

class CryptoController extends Controller
{
    public function index()
    {
        return response()->json(WatchlistStockCrypto::all());
    }
}
