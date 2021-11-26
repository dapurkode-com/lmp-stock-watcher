<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CryptoController extends Controller
{
    public function index()
    {
        return response()->view('crypto');
    }

    public function holdable(): Response
    {
        return response()->view('hold-crypto-list');
    }
}
