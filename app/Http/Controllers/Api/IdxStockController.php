<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WatchlistStockIdx;
use Illuminate\Http\Request;

class IdxStockController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return response()->json(WatchlistStockIdx::all());
    }
}
