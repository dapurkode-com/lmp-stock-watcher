<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

/**
 * App\Models\WatchlistStockIdx
 *
 * @property int $id
 * @property string $symbol
 * @property string $name
 * @property float|null $prev_day_close_price
 * @property float|null $current_price
 * @property float|null $change
 * @property float|null $percent_change
 * @property string|null $last_updated
 * @method static \Illuminate\Database\Eloquent\Builder|WatchlistStockIdx newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|WatchlistStockIdx newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|WatchlistStockIdx query()
 * @method static \Illuminate\Database\Eloquent\Builder|WatchlistStockIdx whereChange($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WatchlistStockIdx whereCurrentPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WatchlistStockIdx whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WatchlistStockIdx whereLastUpdated($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WatchlistStockIdx whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WatchlistStockIdx wherePercentChange($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WatchlistStockIdx wherePrevDayClosePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WatchlistStockIdx whereSymbol($value)
 * @mixin \Eloquent
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\User[] $watchableUsers
 * @property-read int|null $watchable_users_count
 */
class WatchlistStockIdx extends Model
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
