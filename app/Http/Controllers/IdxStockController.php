<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class IdxStockController extends Controller
{
    public function index()
    {
        return response()->view('idx-stock-list');
    }
}
