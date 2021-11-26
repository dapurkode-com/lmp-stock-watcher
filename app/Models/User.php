<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Notifications\DatabaseNotificationCollection;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;

/**
 * App\Models\User
 *
 * @property int $id
 * @property string $name
 * @property string $email
 * @property Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $remember_token
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read DatabaseNotificationCollection|DatabaseNotification[] $notifications
 * @property-read int|null $notifications_count
 * @method static Builder|User newModelQuery()
 * @method static Builder|User newQuery()
 * @method static Builder|User query()
 * @method static Builder|User whereCreatedAt($value)
 * @method static Builder|User whereEmail($value)
 * @method static Builder|User whereEmailVerifiedAt($value)
 * @method static Builder|User whereId($value)
 * @method static Builder|User whereName($value)
 * @method static Builder|User wherePassword($value)
 * @method static Builder|User whereRememberToken($value)
 * @method static Builder|User whereUpdatedAt($value)
 * @mixin Eloquent
 * @method static Builder online()
 */
class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * Get online user
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeOnline(Builder $query): Builder
    {
        return $query->whereRaw("EXISTS(SELECT * FROM sessions WHERE user_id = users.id)");
    }

    /**
     *
     * @param string $watchableType
     * @return MorphToMany
     */
    public function watchlist(string $watchableType): MorphToMany
    {
        switch ($watchableType) {
            case 'us-stock':
                return $this->morphedByMany(WatchlistStockUs::class, 'watchable');

            case 'idx-stock':
                return $this->morphedByMany(WatchlistStockIdx::class, 'watchable');

            case 'crypto':
                return $this->morphedByMany(WatchlistStockCrypto::class, 'watchable');

            default:
                return $this->morphedByMany(WatchlistStockCommodity::class, 'watchable');
        }
    }

    /**
     *
     * @param string $holdableType
     * @return MorphToMany|Holdable
     */
    public function holdList(string $holdableType): MorphToMany
    {
        switch ($holdableType) {
            case 'us-stock':
                return $this->morphedByMany(WatchlistStockUs::class, 'holdable')->withPivot(['amount', 'unit']);

            case 'idx-stock':
                return $this->morphedByMany(WatchlistStockIdx::class, 'holdable')->withPivot(['amount', 'unit']);

            case 'crypto':
                return $this->morphedByMany(WatchlistStockCrypto::class, 'holdable')->withPivot(['amount', 'unit']);

            default:
                return $this->morphedByMany(WatchlistStockCommodity::class, 'holdable')->withPivot(['amount', 'unit']);
        }
    }
}
