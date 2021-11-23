<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

/**
 * App\Models\WatchlistStockUs
 *
 * @property int $id
 * @property string $symbol
 * @property string $name
 * @property float|null $prev_day_close_price
 * @property float|null $current_price
 * @property float|null $change
 * @property float|null $percent_change
 * @property string|null $last_updated
 * @method static \Illuminate\Database\Eloquent\Builder|WatchlistStockUs newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|WatchlistStockUs newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|WatchlistStockUs query()
 * @method static \Illuminate\Database\Eloquent\Builder|WatchlistStockUs whereChange($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WatchlistStockUs whereCurrentPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WatchlistStockUs whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WatchlistStockUs whereLastUpdated($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WatchlistStockUs whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WatchlistStockUs wherePercentChange($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WatchlistStockUs wherePrevDayClosePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WatchlistStockUs whereSymbol($value)
 * @mixin \Eloquent
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\User[] $watchableUsers
 * @property-read int|null $watchable_users_count
 */
class WatchlistStockUs extends Model
{
    /**
     * Timestamp (created_at, updated_at)
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'symbol',
        'name',
        'prev_day_close_price',
        'current_price',
        'change',
        'percent_change',
        'last_updated'
    ];

    /**
     * @return MorphToMany|User[]
     */
    public function watchableUsers(): MorphToMany
    {
        return $this->morphToMany(User::class, 'watchable');
    }
}
