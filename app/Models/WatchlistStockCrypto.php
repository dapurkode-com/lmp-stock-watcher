<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

/**
 * App\Models\WatchlistStockCrypto
 *
 * @property int $id
 * @property string $symbol
 * @property string $name
 * @property float|null $prev_day_close_price
 * @property float|null $current_price
 * @property float|null $percent_change_1h
 * @property float|null $percent_change_24h
 * @property string|null $last_updated
 * @method static \Illuminate\Database\Eloquent\Builder|WatchlistStockCrypto newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|WatchlistStockCrypto newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|WatchlistStockCrypto query()
 * @method static \Illuminate\Database\Eloquent\Builder|WatchlistStockCrypto whereCurrentPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WatchlistStockCrypto whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WatchlistStockCrypto whereLastUpdated($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WatchlistStockCrypto whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WatchlistStockCrypto wherePercentChange1h($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WatchlistStockCrypto wherePercentChange24h($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WatchlistStockCrypto wherePrevDayClosePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WatchlistStockCrypto whereSymbol($value)
 * @mixin \Illuminate\Database\Eloquent\Builder
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\User[] $watchableUsers
 * @property-read int|null $watchable_users_count
 */
class WatchlistStockCrypto extends Model
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
        'percent_change_1h',
        'percent_change_24h',
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
