<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
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
 * @method static Builder|WatchlistStockCrypto newModelQuery()
 * @method static Builder|WatchlistStockCrypto newQuery()
 * @method static Builder|WatchlistStockCrypto query()
 * @method static Builder|WatchlistStockCrypto whereCurrentPrice($value)
 * @method static Builder|WatchlistStockCrypto whereId($value)
 * @method static Builder|WatchlistStockCrypto whereLastUpdated($value)
 * @method static Builder|WatchlistStockCrypto whereName($value)
 * @method static Builder|WatchlistStockCrypto wherePercentChange1h($value)
 * @method static Builder|WatchlistStockCrypto wherePercentChange24h($value)
 * @method static Builder|WatchlistStockCrypto wherePrevDayClosePrice($value)
 * @method static Builder|WatchlistStockCrypto whereSymbol($value)
 * @mixin Builder
 * @property-read Collection|User[] $watchableUsers
 * @property-read int|null $watchable_users_count
 * @property-read Collection|\App\Models\User[] $holdableUsers
 * @property-read int|null $holdable_users_count
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

    /**
     * @return MorphToMany|User[]
     */
    public function holdableUsers(): MorphToMany
    {
        return $this->morphToMany(User::class, 'holdable')->withPivot(['amount', 'unit']);
    }
}
