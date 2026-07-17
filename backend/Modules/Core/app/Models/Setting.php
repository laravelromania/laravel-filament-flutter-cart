<?php

declare(strict_types=1);

namespace Modules\Core\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Dead-simple key/value store (YAGNI). One row per setting, e.g.
 * `shop.currency => RON`. Read it through the `setting()` helper, which caches
 * the whole table for the request.
 *
 * @property string $key
 * @property string|null $value
 */
class Setting extends Model
{
    protected $fillable = ['key', 'value'];

    public $timestamps = true;
}
