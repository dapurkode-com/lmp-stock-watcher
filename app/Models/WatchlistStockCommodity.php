<?php

namespace App\Models;

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
 * @method static \Illuminate\Database\Eloquent\Builder|WatchlistStockCommodity newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|WatchlistStockCommodity newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|WatchlistStockCommodity query()
 * @method static \Illuminate\Database\Eloquent\Builder|WatchlistStockCommodity whereChange($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WatchlistStockCommodity whereCurrentPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WatchlistStockCommodity whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WatchlistStockCommodity whereLastUpdated($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WatchlistStockCommodity whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WatchlistStockCommodity wherePercentChange($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WatchlistStockCommodity wherePrevDayClosePrice($value)
 * @mixin \Eloquent
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\User[] $watchableUsers
 * @property-read int|null $watchable_users_count
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
}
