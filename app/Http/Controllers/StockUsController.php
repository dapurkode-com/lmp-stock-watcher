<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

class StockUsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(): Response
    {
        return response()->view('us-stock-list');
    }

    public function holdable(): Response
    {
        return response()->view('hold-us-stock-list');
    }
}
