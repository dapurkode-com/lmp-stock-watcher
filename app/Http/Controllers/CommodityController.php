<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CommodityController extends Controller
{
    public function index()
    {
        return response()->view('commodity-list');
    }

    public function holdable(): Response
    {
        return response()->view('hold-commodity-list');
    }
}
