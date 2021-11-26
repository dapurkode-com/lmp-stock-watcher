<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
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
 * @method static Builder|WatchlistStockIdx newModelQuery()
 * @method static Builder|WatchlistStockIdx newQuery()
 * @method static Builder|WatchlistStockIdx query()
 * @method static Builder|WatchlistStockIdx whereChange($value)
 * @method static Builder|WatchlistStockIdx whereCurrentPrice($value)
 * @method static Builder|WatchlistStockIdx whereId($value)
 * @method static Builder|WatchlistStockIdx whereLastUpdated($value)
 * @method static Builder|WatchlistStockIdx whereName($value)
 * @method static Builder|WatchlistStockIdx wherePercentChange($value)
 * @method static Builder|WatchlistStockIdx wherePrevDayClosePrice($value)
 * @method static Builder|WatchlistStockIdx whereSymbol($value)
 * @mixin Eloquent
 * @property-read Collection|User[] $watchableUsers
 * @property-read int|null $watchable_users_count
 * @property-read Collection|\App\Models\User[] $holdableUsers
 * @property-read int|null $holdable_users_count
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

    /**
     * @return MorphToMany|User[]
     */
    public function holdableUsers(): MorphToMany
    {
        return $this->morphToMany(User::class, 'holdable')->withPivot(['amount', 'unit']);
    }
}
