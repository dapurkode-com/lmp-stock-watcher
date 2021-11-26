<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

class IdxStockController extends Controller
{
    public function index()
    {
        return response()->view('idx-stock-list');
    }

    public function holdable(): Response
    {
        return response()->view('hold-idx-stock-list');
    }
}
