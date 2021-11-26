<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * App\Models\Holdable
 *
 * @property int $user_id
 * @property int $holdable_id
 * @property string $holdable_type
 * @property double $amount
 * @property int $unit
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Holdable newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Holdable newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Holdable query()
 * @mixin \Eloquent
 */
class Holdable extends Pivot
{
    use HasFactory;
}
