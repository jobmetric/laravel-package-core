<?php

namespace JobMetric\PackageCore\Models;

use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * JobMetric\PackageCore\Models\HasUuid
 *
 * @method static creating(Closure $param)
 * @method static where(string $string, string $uuid)
 */
trait HasUuid
{
    public static function bootHasUuid(): void
    {
        static::creating(function (Model $model) {
            if (empty($model->uuid)) {
                $model->uuid = (string)Str::uuid();
            }
        });
    }

    public static function findByUuid(string $uuid): ?Model
    {
        return static::where('uuid', $uuid)->first();
    }
}
