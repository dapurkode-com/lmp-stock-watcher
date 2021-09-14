<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WatchlistStockCrypto extends Model
{
    use HasFactory;
    protected $fillable = ['symbol', 'name', 'last', 'buy', 'sell', 'is_active',];
}
