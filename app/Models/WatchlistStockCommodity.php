<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

/**
 * App\Models\WatchlistStockCommodity
 *
 * @property int $id
 * @property string $name
 * @property float|null $prev_day_close_price
 * @property float|null $current_price
 * @property float|null $change
 * @property float|null $percent_change
 * @property string|null $last_updated
 * @method static Builder|WatchlistStockCommodity newModelQuery()
 * @method static Builder|WatchlistStockCommodity newQuery()
 * @method static Builder|WatchlistStockCommodity query()
 * @method static Builder|WatchlistStockCommodity whereChange($value)
 * @method static Builder|WatchlistStockCommodity whereCurrentPrice($value)
 * @method static Builder|WatchlistStockCommodity whereId($value)
 * @method static Builder|WatchlistStockCommodity whereLastUpdated($value)
 * @method static Builder|WatchlistStockCommodity whereName($value)
 * @method static Builder|WatchlistStockCommodity wherePercentChange($value)
 * @method static Builder|WatchlistStockCommodity wherePrevDayClosePrice($value)
 * @mixin Eloquent
 * @property-read Collection|User[] $watchableUsers
 * @property-read int|null $watchable_users_count
 * @property-read Collection|\App\Models\User[] $holdableUsers
 * @property-read int|null $holdable_users_count
 */
class WatchlistStockCommodity extends Model
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
        'name',
        'prev_price',
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
