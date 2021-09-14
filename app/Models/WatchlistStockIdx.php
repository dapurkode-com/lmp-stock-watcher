<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WatchlistStockIdx extends Model
{
    use HasFactory;
    protected $fillable = ['symbol', 'name', 'prev_price', 'current_price', 'change', 'percent_change', 'is_active'];
}
