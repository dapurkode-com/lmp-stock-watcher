<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CommodityController extends Controller
{
    public function index()
    {
        return response()->view('commodity-list');
    }
}
