<?php

namespace App\Models\Concerns;

use App\Support\IdObfuscator;
use Illuminate\Database\Eloquent\Model;

trait HasObfuscatedRouteKey
{
    public function getRouteKey(): mixed
    {
        return IdObfuscator::encode((int) $this->getKey(), static::class);
    }

    public function getObfuscatedIdAttribute(): string
    {
        return IdObfuscator::encode((int) $this->getKey(), static::class);
    }

    public function resolveRouteBinding($value, $field = null): ?Model
    {
        if ($field !== null) {
            return parent::resolveRouteBinding($value, $field);
        }

        $id = IdObfuscator::decode((string) $value, static::class);

        if ($id === null && (ctype_digit((string) $value) || is_int($value))) {
            $id = (int) $value;
        }

        if ($id === null) {
            abort(404);
        }

        return $this->where($this->getKeyName(), $id)->firstOrFail();
    }
}
